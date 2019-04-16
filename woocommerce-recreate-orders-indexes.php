<?php
/**
 * Plugin Name: Recreate WooCommerce Orders Indexes
 * Plugin URI: https://woocommerce.com/
 * Description: This tool will add address indexes to orders that do not have them yet (for orders placed using WooCommerce 2.6 or early).
 * Version: 1.0.0
 * Author: Automattic
 * Author URI: https://woocommerce.com
 * Text Domain: woocommerce-recreate-orders-indexes
 * Domain Path: /i18n/languages/
 *
 * @package WooCommerce\Recreate\Order\Indexes
 */

defined( 'ABSPATH' ) || exit;

// Define WC_PLUGIN_FILE.
if ( ! defined( 'WC_ROI_PLUGIN_FILE' ) ) {
	define( 'WC_ROI_PLUGIN_FILE', __FILE__ );
}

// Include the main plugin class.
if ( ! class_exists( 'WooCommerce' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wc-recreate-orders-indexes.php';
}

add_action( 'plugins_loaded', array( 'WC_Recreate_Orders_Indexes', 'init' ) );
