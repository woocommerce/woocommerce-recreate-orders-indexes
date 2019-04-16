<?php
/**
 * Plugin setup
 *
 * @package WooCommerce\Recreate\Order\Indexes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
class WC_Recreate_Orders_Indexes {

	/**
	 * Init.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );

		if ( class_exists( 'WooCommerce' ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.6.0', '>=' ) ) {
			add_filter( 'woocommerce_debug_tools', array( __CLASS__, 'register_debug_tool' ) );
			add_action( 'wc_recreate_order_indexes', array( __CLASS__, 'create_order_indexes' ) );
		} else {
			add_action( 'admin_notices', array( __CLASS__, 'missing_dependency' ) );
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-recreate-orders-indexes', false, dirname( plugin_basename( WC_ROI_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Register debug tool.
	 *
	 * @param array $tools Debug tools data.
	 * @return array
	 */
	public static function register_debug_tool( $tools ) {
		$tools['add_order_indexes'] = array(
			'name'     => __( 'Order address indexes', 'woocommerce-recreate-orders-indexes' ),
			'button'   => __( 'Index orders', 'woocommerce-recreate-orders-indexes' ),
			'desc'     => __( 'This tool will add address indexes to orders that do not have them yet (for orders placed using WooCommerce 2.6 or early). This improves order search results.', 'woocommerce-recreate-orders-indexes' ),
			'callback' => array( __CLASS__, 'debug_tool_callback' ),
		);

		return $tools;
	}

	/**
	 * Debug tool callback.
	 *
	 * @return bool|string
	 */
	public static function debug_tool_callback() {
		global $wpdb;

		if ( self::queue_is_running() ) {
			return __( 'Already creating order indexes!', 'woocommerce-recreate-orders-indexes' );
		}

		$is_cli = defined( 'WP_CLI' ) && WP_CLI;

		$query = $wpdb->get_results(
			"
			SELECT posts.ID FROM {$wpdb->posts} AS posts
			INNER JOIN {$wpdb->postmeta} AS meta ON meta.post_id = posts.ID
			WHERE posts.post_type = 'shop_order'
			AND (meta.meta_key != '_billing_address_index' OR meta.meta_key != '_shipping_address_index')
			GROUP BY posts.ID
			",
			ARRAY_A
		);

		$queues = array_chunk( $query, 20 );

		if ( empty( $queues ) ) {
			return __( 'No orders require new indexes!', 'woocommerce-recreate-orders-indexes' );
		}

		foreach ( $queues as $index => $data ) {
			$ids = wp_list_pluck( $data, 'ID' );

			if ( $is_cli ) {
				self::create_order_indexes( $ids );
			} else {
				WC()->queue()->schedule_single(
					time() + $index,
					'wc_recreate_order_indexes',
					array(
						'ids' => $ids,
					),
					'wc_recreate_order_indexes'
				);
			}
		}

		return true;
	}

	/**
	 * Check if queue is running.
	 *
	 * @return bool
	 */
	protected static function queue_is_running() {
		$pending = WC()->queue()->search(
			array(
				'status'   => 'pending',
				'group'    => 'wc_recreate_order_indexes',
				'per_page' => 1,
			)
		);

		return (bool) count( $pending );
	}

	/**
	 * Create order indexes.
	 *
	 * @param array $ids List of order's ID.
	 */
	public static function create_order_indexes( $ids ) {
		if ( ! $ids || ! is_array( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			$order = wc_get_order( (int) $id );

			if ( $order ) {
				$order->update_meta_data( '_billing_address_index', wc_clean( implode( ' ', $order->get_address( 'billing' ) ) ) );
				$order->update_meta_data( '_shipping_address_index', wc_clean( implode( ' ', $order->get_address( 'shipping' ) ) ) );
				$order->save();
			}
		}
	}

	/**
	 * Missing dependency notice.
	 */
	public static function missing_dependency() {
		echo '<div class="error"><p>' . wp_kses_post( __( '<strong>WooCommerce Recreate Orders Indexes</strong> requires WooCoomerce 3.6 or later to work!', 'woocommerce-recreate-orders-indexes' ) ) . '</p></div>';
	}
}
