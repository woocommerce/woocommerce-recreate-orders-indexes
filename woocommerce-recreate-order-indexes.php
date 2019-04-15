<?php
/**
 * Plugin Name: Recreate WooCommerce orders indexes
 * Plugin URI: https://woocommerce.com/
 * Description: This tool will add address indexes to orders that do not have them yet.
 * Version: 1.0.0
 * Author: Automattic
 * Author URI: https://woocommerce.com
 * Text Domain: wc-recreate-order-indexes
 * Domain Path: /i18n/languages/
 *
 * @package WooCommerce\Recreate\Order\Indexes
 */

defined( 'ABSPATH' ) || exit;

// Define WC_PLUGIN_FILE.
if ( ! defined( 'WC_ROI_PLUGIN_FILE' ) ) {
	define( 'WC_ROI_PLUGIN_FILE', __FILE__ );
}
