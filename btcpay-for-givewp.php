<?php
/**
 * Plugin Name: BTCPay for GiveWP
 * Plugin URI: https://docs.btcpayserver.org/GiveWP/
 * Description: BTCPay Server Bitcoin / Lightning Network payment gateway integration for GiveWP
 * Version: 1.0.0
 * Author: BTCPay Server integrations team
 * Author URI: https://btcpayserver.org
 * Text Domain: btcpay-for-givewp
 * Domain Path: /languages
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Requires PHP: 8.1
 * Requires at least: 6.0
 * Tested up to: 6.7
 * Requires Plugins: give
 * GiveWP tested up to: 3.22.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('BTCPAY_FOR_GIVEWP_VERSION', '1.0.0');
define('BTCPAY_FOR_GIVEWP_DIR', plugin_dir_path(__FILE__));
define('BTCPAY_FOR_GIVEWP_URL', plugin_dir_url(__FILE__));

// Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main plugin class
 */
final class BTCPayForGiveWP {
	/**
	 * @var BTCPayForGiveWP The single instance of this class
	 */
	private static $instance = null;

	/**
	 * Main Plugin Instance
	 *
	 * Ensures only one instance of the plugin exists in memory at any one time.
	 *
	 * @return BTCPayForGiveWP
	 */
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Plugin Constructor
	 */
	private function __construct() {
		// Load text domain
		add_action('init', [$this, 'loadTextdomain']);

		// Admin notice if Give is not active
		add_action('admin_notices', [$this, 'adminNotice']);
	}

	/**
	 * Initialize the plugin on give_init
	 * This is the main initialization method that sets up the gateway
	 */
	public function setupGateway() {
		// Check if Give is active
		if (!$this->isGiveActive()) {
			return;
		}

		// Register the payment gateway with GiveWP
		add_action('givewp_register_payment_gateway', function($paymentGatewayRegister) {
			$paymentGatewayRegister->registerGateway(BTCPayServer\Give\Gateway\BtcpayGateway::class);
		});

		// Initialize the settings class
		if (is_admin()) {
			BTCPayServer\Give\Admin\Settings::instance();
		}
	}

	/**
	 * Check if Give is active
	 *
	 * @return bool
	 */
	private function isGiveActive() {
		return class_exists('Give');
	}

	/**
	 * Load plugin text domain
	 */
	public function loadTextdomain() {
		load_plugin_textdomain(
			'btcpay-for-givewp',
			false,
			dirname(plugin_basename(__FILE__)) . '/languages'
		);
	}

	/**
	 * Admin notice for when Give is not active
	 */
	public function adminNotice() {
		if (!$this->isGiveActive()) {
			?>
            <div class="error">
                <p><?php esc_html_e('Give must be installed and activated for the BTCPay for GiveWP Gateway add-on to work.', 'btcpay-for-givewp'); ?></p>
            </div>
			<?php
		}
	}
}

/**
 * Returns the main instance of BTCPayForGiveWP
 *
 * @return BTCPayForGiveWP
 */
function BTCPayForGiveWP() {
	return BTCPayForGiveWP::instance();
}

// Initialize the plugin
BTCPayForGiveWP();

// Setup the gateway at the proper time
add_action('give_init', [BTCPayForGiveWP(), 'setupGateway']);