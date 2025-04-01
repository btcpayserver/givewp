<?php
namespace BTCPayServer\Give\Admin;

use BTCPayServer\Client\Webhook;
use BTCPayServer\Give\Gateway\BtcpayGateway;

/**
 * BTCPay Settings Class
 */
class Settings {
	/**
	 * @var Settings
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance
	 */
	public static function instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Register the gateway settings section
		add_filter('give_get_sections_gateways', [$this, 'registerSections']);

		// Register the gateway settings fields
		add_filter('give_get_settings_gateways', [$this, 'registerSettings']);
        
		// Add validation for API credentials
		add_action('admin_init', [$this, 'handleSettingsSave']);
		add_action('admin_notices', [$this, 'displayApiValidationMessages']);
	}

	/**
	 * Register Gateway Section
	 */
	public function registerSections($sections) {
		$sections['btcpay-gateway'] = __('BTCPay Gateway', 'btcpay-for-givewp');
		return $sections;
	}

	/**
	 * Register Gateway Settings
	 */
	public function registerSettings($settings) {
		$current_section = give_get_current_setting_section();

		if ($current_section !== 'btcpay-gateway') {
			return $settings;
		}

		return [
			[
				'id' => 'btcpay_settings_title',
				'type' => 'title',
				'title' => __('BTCPay Server Gateway Settings', 'btcpay-for-givewp'),
			],
			[
				'id' => 'btcpay_url',
				'name' => __('BTCPay Server URL', 'btcpay-for-givewp'),
				'desc' => __('Enter the URL where you log into your BTCPay Server. e.g. https://mainnet.demo.btcpayserver.org', 'btcpay-for-givewp'),
				'type' => 'text',
				'default' => '',
				'sanitize_callback' => 'esc_url_raw',
			],
			[
				'id' => 'btcpay_store_id',
				'name' => __('Store ID', 'btcpay-for-givewp'),
				'desc' => __('Enter your BTCPay Server Store ID.', 'btcpay-for-givewp'),
				'type' => 'text',
				'default' => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			[
				'id' => 'btcpay_api_key',
				'name' => __('API Key', 'btcpay-for-givewp'),
				'desc' => __('Enter your BTCPay API key.', 'btcpay-for-givewp'),
				'type' => 'password',
				'default' => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			[
				'id' => 'btcpay_settings',
				'type' => 'sectionend',
			],
		];
	}

	/**
	 * Handle settings save and API validation
	 */
	public function handleSettingsSave() {
		if (!$this->isSettingsSaveRequest()) {
			return;
		}

		// Verify nonce
		if (!wp_verify_nonce($_POST['_give-save-settings'], 'give-save-settings')) {
			return;
		}

		// Check if we're in the BTCPay settings section
		if (empty($_GET['section']) || $_GET['section'] !== 'btcpay-gateway') {
			return;
		}

		$btcpay_url = isset($_POST['btcpay_url']) ? esc_url_raw($_POST['btcpay_url']) : '';
		$store_id = isset($_POST['btcpay_store_id']) ? sanitize_text_field($_POST['btcpay_store_id']) : '';
		$api_key = isset($_POST['btcpay_api_key']) ? sanitize_text_field($_POST['btcpay_api_key']) : '';

		// Only validate if all fields are filled
		if ($btcpay_url && $store_id && $api_key) {
			$validation_result = $this->validateApiCredentials($btcpay_url, $store_id, $api_key);

			if (is_wp_error($validation_result)) {
				set_transient('btcpay_api_validation_error', $validation_result->get_error_message(), 45);
			} else {
				set_transient('btcpay_api_validation_success', __('BTCPay for GiveWP: BTCPay Server API credentials verified successfully!', 'btcpay-for-givewp'), 45);
			}
            
            // Setup webhook if it does not exist.
            if ($this->webhookExists($btcpay_url, $store_id, $api_key)) {
                set_transient('btcpay_webhook_exists', __('BTCPay for GiveWP: Webhook already exists, no need to create it.', 'btcpay-for-givewp'), 45);
            } else {
                $webhook = $this->createWebhook($btcpay_url, $store_id, $api_key);
	            // Store webhook secret and other data for later use
	            give_update_option('btcpay_wh_id', $webhook->getId());
	            give_update_option('btcpay_wh_secret', $webhook->getSecret());
	            give_update_option('btcpay_wh_url', $webhook->getUrl());
	            set_transient('btcpay_webhook_created', __('BTCPay for GiveWP: Webhook successfully created.', 'btcpay-for-givewp'), 45);
            }
		}
	}

	/**
	 * Display API validation messages
	 */
	public function displayApiValidationMessages() {
		$error = get_transient('btcpay_api_validation_error');
		$whError = get_transient('btcpay_webhook_error');
		$success = get_transient('btcpay_api_validation_success');
		$whExists = get_transient('btcpay_webhook_exists');
		$whCreated = get_transient('btcpay_webhook_created');

		if ($error) {
			?>
            <div class="error">
                <p><?php echo esc_html($error); ?></p>
            </div>
			<?php
			delete_transient('btcpay_api_validation_error');
		}
        
        if ($whError) {
            ?>
            <div class="error">
                <p><?php echo esc_html($whError); ?></p>
            </div>
            <?php
            delete_transient('btcpay_webhook_error');
        }

		if ($success) {
			?>
            <div class="updated">
                <p><?php echo esc_html($success); ?></p>
            </div>
			<?php
			delete_transient('btcpay_api_validation_success');
		}
        
        if ($whExists) {
            ?>
            <div class="updated">
                <p><?php echo esc_html($whExists); ?></p>
            </div>
            <?php
            delete_transient('btcpay_webhook_exists');
        }
        
        if ($whCreated) {
            ?>
            <div class="updated">
                <p><?php echo esc_html($whCreated); ?></p>
            </div>
            <?php
            delete_transient('btcpay_webhook_created');
        }
	}

	/**
	 * Check if current request is for saving settings
	 */
	private function isSettingsSaveRequest(): bool {
		return (
			isset($_POST['_give-save-settings']) &&
			isset($_GET['page']) &&
			$_GET['page'] === 'give-settings'
		);
	}

	/**
	 * Validate BTCPay API credentials
	 */
	private function validateApiCredentials(string $url, string $store_id, string $api_key) {
		// Remove trailing slashes
		$url = rtrim($url, '/');

		// Test endpoint
		$test_endpoint = "$url/api/v1/stores/$store_id";

		$response = wp_remote_get($test_endpoint, [
			'headers' => [
				'Authorization' => "token $api_key",
				'Content-Type' => 'application/json',
			],
			'timeout' => 30,
		]);

		if (is_wp_error($response)) {
			return new \WP_Error(
				'btcpay_api_error',
				sprintf(
					__('Failed to connect to BTCPay Server: %s', 'btcpay-for-givewp'),
					$response->get_error_message()
				)
			);
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			return new \WP_Error(
				'btcpay_api_error',
				sprintf(
					__('Invalid API credentials. BTCPay Server returned: %s', 'btcpay-for-givewp'),
					wp_remote_retrieve_response_message($response)
				)
			);
		}

		return true;
	}

	/**
	 * Check if the webhook exists.
	 */
    public function webhookExists(string $btcpay_url, string $store_id, string $api_key) {
        try {
            $existingWebhook = give_get_option('btcpay_wh_id');
            if (!$existingWebhook) {
                return false;
            }
            
            $client = new Webhook($btcpay_url, $api_key);
            $webhooks = $client->getStoreWebhooks($store_id);
            foreach ($webhooks->all() as $webhook) {
                if ($webhook->getId() === $existingWebhook) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Create a webhook on BTCPay Server
     */
    public function createWebhook(string $url, string $store_id, string $api_key) {
        
        // Which webhook events to subscribe to
        $events = [
	        'InvoiceReceivedPayment',
	        'InvoicePaymentSettled',
	        'InvoiceProcessing',
	        'InvoiceExpired',
	        'InvoiceSettled',
	        'InvoiceInvalid'
        ];
        
        try {
            // Create the webhook 
            $client = new Webhook($url, $api_key);
            $webhook = $client->createWebhook(
                $store_id,
                BtcpayGateway::webhookUrl(),
                $events,
                null
            );
           
            return $webhook;
            
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}