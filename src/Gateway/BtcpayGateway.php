<?php
namespace BTCPayServer\Give\Gateway;

use BTCPayServer\Client\Invoice;
use BTCPayServer\Client\InvoiceCheckoutOptions;
use BTCPayServer\Client\Webhook;
use BTCPayServer\Util\PreciseNumber;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;

/**
 * BTCPay Gateway for GiveWP
 */
class BtcpayGateway extends PaymentGateway
{
	/**
	 * Secure route methods for gateway callbacks
	 */
	public $secureRouteMethods = [
		'handlePaymentRedirect'
	];
	
	/**
	 * Route methods for gateway callbacks
	 */
	public $routeMethods = [
		'processWebhook'
	];
	
	/**
	 * @inheritDoc
	 */
	public static function id(): string
	{
		return 'btcpay-gateway';
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string
	{
		return self::id();
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string
	{
		return __('BTCPay Server Gateway', 'btcpay-for-givewp');
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel(): string
	{
		return __('Bitcoin / Lightning Network (via BTCPay)', 'btcpay-for-givewp');
	}
	
	public static function webhookUrl(): string
	{
		$instance = new static();
		return $instance->generateGatewayRouteUrl($instance->routeMethods[0]);
	}

	public static function redirectUrl(): string
	{
		$instance = new static();
		return $instance->generateSecureGatewayRouteUrl($instance->secureRouteMethods[0]);
	}

	/**
	 * Register scripts for the gateway
	 */
	public function enqueueScript(int $formId)
	{
		wp_enqueue_script(
			'btcpay-gateway',
			BTCPAY_FOR_GIVEWP_URL . 'assets/js/btcpay-gateway.js',
			['react', 'wp-element'],
			BTCPAY_FOR_GIVEWP_VERSION,
			true
		);
	}

	/**
	 * Settings for the gateway form
	 */
	public function formSettings(int $formId): array
	{
		return [
			'message' => __('You will be redirected to a payment page to complete the donation.', 'btcpay-for-givewp'),
		];
	}

	/**
	 * Legacy form field markup
	 */
	public function getLegacyFormFieldMarkup(int $formId, array $args): string
	{
		return "<div class='btcpay-gateway-help-text'>
            <p>" . __('You will be redirected to a payment page to complete the donation.', 'btcpay-for-givewp') . "</p>
        </div>";
	}

	/**
	 * Create a payment
	 */
	public function createPayment(Donation $donation, $gatewayData)
	{
		// Get BTCPay Server credentials from settings
		$btcpayUrl = give_get_option('btcpay_url');
		$storeId = give_get_option('btcpay_store_id');
		$apiKey = give_get_option('btcpay_api_key');

		// Generate return URL for after payment
		$returnUrl = $this->generateSecureGatewayRouteUrl(
			'handlePaymentRedirect',
			$donation->id,
			[
				'givewp-donation-id' => $donation->id,
				'givewp-success-url' => urlencode(give_get_success_page_uri()),
			]
		);

		// Create the invoice checkout options
		$checkoutOptions = new InvoiceCheckoutOptions();
		$checkoutOptions->setRedirectUrl($returnUrl);
		
		// Additional metadata for the invoice
		$metadata = [
			'orderUrl' => admin_url('edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . absint($donation->id))	
		];
		
		try {
			$client = new Invoice($btcpayUrl, $apiKey);
			
			$invoice = $client->createInvoice(
				$storeId,
				$donation->amount->getCurrency()->getCode(),
				PreciseNumber::ParseFloat($donation->amount->formatToDecimal()),
			    $donation->id,
				null,
				$metadata,
				$checkoutOptions
			);
			
			// Store the invoice ID in the donation as reference.
			$donation->gatewayTransactionId = 'invoice id: ' . $invoice->getId();
			$donation->save();
			
			return new RedirectOffsite($invoice->getCheckoutLink());
		} catch (\Exception $e) {
			throw new \Exception('Failed to create invoice on BTCPay: ' . esc_html($e->getMessage()));
		}
	}

	/**
	 * Handle the return from BTCPay Server
	 */
	protected function handlePaymentRedirect(array $queryParams): RedirectResponse
	{
		$donationId = (int) $queryParams['givewp-donation-id'];
		
		$successUrl = urldecode($queryParams['givewp-success-url']);
		
		DonationNote::create([
			'donationId' => $donationId,
			'content' => __('Donor returned via redirect link from BTCPay invoice payment page.', 'btcpay-for-givewp')
		]);

		return new RedirectResponse($successUrl);
	}
	
	public function processWebhook()
	{
		$rawData = file_get_contents('php://input');
		$payload = json_decode($rawData, false);

		// Validate webhook payload data
		// Note: getallheaders() CamelCases all headers for PHP-FPM/Nginx but for others maybe not, so "BTCPay-Sig" may becomes "Btcpay-Sig".
		$headers = getallheaders();
		foreach ($headers as $key => $value) {
			if (strtolower($key) === 'btcpay-sig') {
				$signature = $value;
			}
		}
		
		try {
			$webhookClient = new Webhook(give_get_option('btcpay_url'), give_get_option('btcpay_api_key'));
			// Validate the webhook request.
			if (!$webhookClient->isIncomingWebhookRequestValid($rawData, $signature, give_get_option('btcpay_wh_secret'))) {
				throw new \RuntimeException(
					'Invalid BTCPay Server payment webhook message received - signature did not match.'
				);
			}
		} catch (\Exception $e) {
			wp_die('Webhook request validation failed.');
		}
		
		// Load the donation reference from the payload
		$invoice = $this->loadInvoice($payload->invoiceId);
		$donationId = (int) $invoice->getData()['metadata']['orderId'];
		
		if ($donationId) {
			$donation = Donation::find($donationId);
			
			// Process webhook events
			switch ($payload->type) {
				case 'InvoiceReceivedPayment':
					// As soon as we receive a payment, we update donation status.
					$donation->status = DonationStatus::PROCESSING();
					
					DonationNote::create([
						'donationId' => $donationId,
						'content' => sprintf(
						    /* translators: %s: Invoice ID */
							__('BTCPay Webhook: (Partial) payment received but not confirmed. Invoice ID: ', 'btcpay-for-givewp'),
							$payload->invoiceId
						)
					]);
					break;
				case 'InvoiceSettled':
					// Handle invoice settled event
					$donation->status = DonationStatus::COMPLETE();
					
					DonationNote::create([
						'donationId' => $donationId,
						'content' => __('BTCPay Webhook: Payment complete (settled).', 'btcpay-for-givewp')
					]);
					break;
				case 'InvoiceExpired':
					// Handle invoice expired event
					if ($payload->partiallyPaid) {
						$donation->status = DonationStatus::PROCESSING();
						
						DonationNote::create([
							'donationId' => $donationId,
							'content' => __('BTCPay Webhook: Invoice expired BUT has a partial payment. NEEDS MANUAL CHECKING (on BTCPay).', 'btcpay-for-givewp')
						]);
					} else {
						$donation->status = DonationStatus::ABANDONED();

						DonationNote::create([
							'donationId' => $donationId,
							'content' => __('BTCPay Webhook: Invoice expired without any payment.', 'btcpay-for-givewp')
						]);
					}
					break;
				case 'InvoiceInvalid':
					// Handle invoice invalid event
					$donation->status = DonationStatus::FAILED();

					DonationNote::create([
						'donationId' => $donationId,
						'content' => __('Payment was set invalid manually on BTCPay.', 'btcpay-for-givewp')
					]);
					
					break;
				default:
					// Need to return http success to not break webhook delivery.
					return true;
			}
						
			// Save the donation
			$donation->save();
		} else {
			// Do not throw error here as we don't want to break the webhook delivery on BTCPay Server side.
			error_log('Invalid donation ID in webhook payload.');
		}
	}
	
	/**
	 * Load invoice from BTCPay Server
	 */
	public function loadInvoice(string $invoiceId): \BTCPayServer\Result\Invoice {
		try {
			$client = new Invoice(give_get_option('btcpay_url'), give_get_option('btcpay_api_key'));
			$invoice = $client->getInvoice(give_get_option('btcpay_store_id'), $invoiceId);
			return $invoice;
		} catch (\Exception $e) {
			throw new \Exception('Failed to load invoice from BTCPay: ' . esc_html($e->getMessage()));
		}
	}	

	/**
	 * @inheritDoc
	 */
	public function refundDonation(Donation $donation): PaymentRefunded
	{
		throw new \Exception('Refunds are not supported for BTCPay Server payments.');
	}
}