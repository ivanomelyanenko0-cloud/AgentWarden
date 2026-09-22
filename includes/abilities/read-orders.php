<?php
/**
 * Read-only order abilities: `agentwarden/list-orders` and
 * `agentwarden/get-order`. HPOS-safe (uses `wc_get_orders()`/`wc_get_order()`,
 * never queries the `shop_order` post type directly, per WooCommerce's
 * High-Performance Order Storage).
 *
 * Deliberately excludes customer PII (name, email, address, phone) from both
 * the ability output and the audit log - see AGENTWARDEN_STRATEGY.md §1.1.
 * Only the customer's numeric user ID is exposed, same as a WooCommerce
 * REST API response scoped without the `read_private_orders`-level detail.
 * A dedicated customer-read ability (and the PII/consent policy it needs) is
 * intentionally deferred past this skeleton, not silently included here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function agwd_can_read_orders( $input = null ) {
	return current_user_can( 'edit_shop_orders' );
}

/**
 * @param WC_Order $order
 * @return array
 */
function agwd_format_order_summary( $order ) {
	return array(
		'id'          => $order->get_id(),
		'status'      => $order->get_status(),
		'total'       => $order->get_total(),
		'currency'    => $order->get_currency(),
		'date_created'=> $order->get_date_created() ? $order->get_date_created()->date( 'c' ) : null,
		'item_count'  => $order->get_item_count(),
		'customer_id' => $order->get_customer_id(),
	);
}

function agwd_ability_list_orders( $input ) {
	$input = is_array( $input ) ? $input : array();

	$args = array(
		'limit'   => min( 100, max( 1, (int) ( $input['per_page'] ?? 20 ) ) ),
		'page'    => max( 1, (int) ( $input['page'] ?? 1 ) ),
		'orderby' => 'date',
		'order'   => 'DESC',
		'return'  => 'objects',
	);
	if ( ! empty( $input['status'] ) ) {
		$args['status'] = sanitize_key( $input['status'] );
	}

	$orders = array_map( 'agwd_format_order_summary', wc_get_orders( $args ) );

	agwd_audit_log_record( AGWD_ABILITY_NAMESPACE . '/list-orders', 'order', 0, count( $orders ), true );

	return array(
		'orders' => $orders,
		'page'   => $args['page'],
	);
}

function agwd_ability_get_order( $input ) {
	$order_id = isset( $input['order_id'] ) ? (int) $input['order_id'] : 0;
	$order    = $order_id ? wc_get_order( $order_id ) : false;

	if ( ! $order ) {
		agwd_audit_log_record( AGWD_ABILITY_NAMESPACE . '/get-order', 'order', $order_id, 0, false );

		return new WP_Error(
			'agwd_order_not_found',
			__( 'No order exists with that ID.', 'agentwarden' ),
			array( 'status' => 404 )
		);
	}

	$summary          = agwd_format_order_summary( $order );
	$summary['items'] = array();
	foreach ( $order->get_items() as $item ) {
		$summary['items'][] = array(
			'product_id' => $item->get_product_id(),
			'name'       => $item->get_name(),
			'quantity'   => $item->get_quantity(),
			'subtotal'   => $item->get_subtotal(),
			'total'      => $item->get_total(),
		);
	}

	agwd_audit_log_record( AGWD_ABILITY_NAMESPACE . '/get-order', 'order', $order_id, 1, true );

	return $summary;
}

function agwd_register_order_abilities() {
	wp_register_ability(
		AGWD_ABILITY_NAMESPACE . '/list-orders',
		array(
			'label'               => __( 'List orders', 'agentwarden' ),
			'description'         => __( 'Lists WooCommerce orders (status, total, item count) without customer personal data, optionally filtered by status.', 'agentwarden' ),
			'category'            => AGWD_ABILITY_NAMESPACE,
			'execute_callback'    => 'agwd_ability_list_orders',
			'permission_callback' => 'agwd_can_read_orders',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'status'   => array(
						'type'        => 'string',
						'description' => __( 'Optional order status to filter by, e.g. "processing" or "completed".', 'agentwarden' ),
					),
					'per_page' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 100,
					),
					'page'     => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			'meta'                => array(
				'annotations' => array(
					'readonly'    => true,
					'destructive' => false,
					'idempotent'  => true,
				),
				'public'      => true,
			),
		)
	);

	wp_register_ability(
		AGWD_ABILITY_NAMESPACE . '/get-order',
		array(
			'label'               => __( 'Get order', 'agentwarden' ),
			'description'         => __( 'Fetches one WooCommerce order by ID, with its line items, but no customer personal data.', 'agentwarden' ),
			'category'            => AGWD_ABILITY_NAMESPACE,
			'execute_callback'    => 'agwd_ability_get_order',
			'permission_callback' => 'agwd_can_read_orders',
			'input_schema'        => array(
				'type'       => 'object',
				'properties' => array(
					'order_id' => array(
						'type'        => 'integer',
						'description' => __( 'The order ID to look up.', 'agentwarden' ),
					),
				),
				'required'   => array( 'order_id' ),
			),
			'meta'                => array(
				'annotations' => array(
					'readonly'    => true,
					'destructive' => false,
					'idempotent'  => true,
				),
				'public'      => true,
			),
		)
	);
}
add_action( 'wp_abilities_api_init', 'agwd_register_order_abilities' );
