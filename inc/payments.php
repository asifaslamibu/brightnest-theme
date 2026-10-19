<?php
/**
 * Online payment for bookings through the Ziina gateway (WooCommerce plugin "Ziina").
 *
 * Flow: the checkout saves the booking (bn_request) as usual, then creates a hidden
 * WooCommerce order for the same total and hands it to the Ziina gateway, which returns
 * the hosted payment page URL. When Ziina confirms the payment, WooCommerce calls
 * payment_complete() and we mark the booking as paid. Success / cancel return to our
 * own checkout page instead of the (empty) WooCommerce cart checkout.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

const BRIGHTNEST_ONLINE_METHOD = 'Pay online';

/**
 * The enabled Ziina gateway instance, or null.
 *
 * @return WC_Payment_Gateway|null
 */
function brightnest_ziina_gateway() {
	if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways() ) {
		return null;
	}
	$gateways = WC()->payment_gateways()->payment_gateways();
	if ( empty( $gateways['ziina'] ) || 'yes' !== $gateways['ziina']->enabled ) {
		return null;
	}
	return $gateways['ziina'];
}

/**
 * Is online payment offered at checkout?
 */
function brightnest_online_payment_ready() {
	return '1' === (string) brightnest_opt( 'pay_online' ) && null !== brightnest_ziina_gateway();
}

/**
 * Create the WooCommerce order for a booking and start the Ziina payment.
 *
 * @param int   $request_id Booking (bn_request) ID.
 * @param array $meta       Booking meta as saved by brightnest_ajax_checkout().
 * @param int   $service_id Service ID.
 * @return string|WP_Error Ziina payment page URL.
 */
function brightnest_start_online_payment( $request_id, $meta, $service_id ) {
	$gateway = brightnest_ziina_gateway();
	if ( ! $gateway ) {
		return new WP_Error( 'bn_no_gateway', __( 'Online payment is temporarily unavailable. Please contact us on WhatsApp to book.', 'brightnest' ) );
	}

	// The shop is set to 0 decimals; booking totals (e.g. AED 235.20) must not be rounded.
	$decimals = function () {
		return 2;
	};
	add_filter( 'wc_get_price_decimals', $decimals );
	add_filter( 'pre_option_woocommerce_price_num_decimals', $decimals );

	try {
		$order = wc_create_order( array( 'created_via' => 'brightnest' ) );
		if ( is_wp_error( $order ) ) {
			return $order;
		}

		// Ziina charges in the booking currency (AED), whatever the shop currency is.
		$order->set_currency( strtoupper( (string) brightnest_opt( 'currency' ) ) );

		$fee = new WC_Order_Item_Fee();
		/* translators: 1: booking ref, 2: service */
		$fee->set_name( sprintf( __( 'Booking #%1$s — %2$s', 'brightnest' ), $meta['ref'], wp_strip_all_tags( html_entity_decode( get_the_title( $service_id ), ENT_QUOTES, 'UTF-8' ) ) ) );
		$fee->set_tax_status( 'none' );
		$fee->set_total( (float) $meta['total'] );
		$order->add_item( $fee );

		$parts = explode( ' ', trim( $meta['name'] ), 2 );
		$order->set_billing_first_name( $parts[0] );
		$order->set_billing_last_name( isset( $parts[1] ) ? $parts[1] : '' );
		$order->set_billing_phone( $meta['phone'] );
		if ( is_email( $meta['email'] ) ) {
			$order->set_billing_email( $meta['email'] );
		}
		$order->set_billing_address_1( $meta['address'] );
		$order->set_billing_city( $meta['area'] );
		$order->set_billing_country( 'AE' );
		$order->set_payment_method( $gateway->id );
		$order->set_payment_method_title( $gateway->get_title() );
		$order->set_customer_note( $meta['message'] );
		$order->update_meta_data( '_bn_request_id', (int) $request_id );
		$order->update_meta_data( '_bn_service_id', (int) $service_id );
		$order->calculate_totals( false );
		$order->set_status( 'pending' );
		$order->save();

		update_post_meta( $request_id, '_bn_order_id', $order->get_id() );
		update_post_meta( $request_id, '_bn_payment_status', 'pending' );

		$result = $gateway->process_payment( $order->get_id() );
	} catch ( Exception $e ) {
		return new WP_Error( 'bn_ziina', $e->getMessage() );
	} finally {
		remove_filter( 'wc_get_price_decimals', $decimals );
		remove_filter( 'pre_option_woocommerce_price_num_decimals', $decimals );
	}

	if ( empty( $result['redirect'] ) ) {
		return new WP_Error( 'bn_ziina', __( 'Could not start the payment. Please try again.', 'brightnest' ) );
	}
	return $result['redirect'];
}

/**
 * Booking linked to a WooCommerce order, or 0.
 *
 * @param WC_Order|int $order Order.
 */
function brightnest_order_request_id( $order ) {
	$order = $order instanceof WC_Order ? $order : wc_get_order( $order );
	return $order ? (int) $order->get_meta( '_bn_request_id' ) : 0;
}

/**
 * Payment confirmed by Ziina: mark the booking as paid and notify the owner.
 *
 * @param int $order_id Order ID.
 */
function brightnest_booking_paid( $order_id ) {
	$request_id = brightnest_order_request_id( $order_id );
	if ( ! $request_id || 'paid' === get_post_meta( $request_id, '_bn_payment_status', true ) ) {
		return;
	}
	$order = wc_get_order( $order_id );
	update_post_meta( $request_id, '_bn_payment_status', 'paid' );
	update_post_meta( $request_id, '_bn_transaction', $order->get_transaction_id() );

	$to = brightnest_opt( 'booking_email' );
	if ( ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}
	$ref = get_post_meta( $request_id, '_bn_ref', true );
	wp_mail(
		$to,
		/* translators: %s: booking ref */
		'[' . get_bloginfo( 'name' ) . '] ' . sprintf( __( 'Payment received for booking #%s', 'brightnest' ), $ref ),
		/* translators: 1: amount, 2: transaction id */
		sprintf( __( "Paid online: %1\$s\nZiina transaction: %2\$s\n\n%3\$s", 'brightnest' ), $order->get_currency() . ' ' . get_post_meta( $request_id, '_bn_total', true ), $order->get_transaction_id(), admin_url( 'post.php?post=' . $request_id . '&action=edit' ) )
	);

	$email = get_post_meta( $request_id, '_bn_email', true );
	if ( is_email( $email ) ) {
		wp_mail(
			$email,
			/* translators: %s: ref */
			sprintf( __( 'Your booking #%s is paid', 'brightnest' ), $ref ),
			/* translators: 1: name, 2: service, 3: date, 4: time, 5: total */
			sprintf( __( "Hi %1\$s,\n\nThanks for booking %2\$s on %3\$s (%4\$s).\nPaid online: %5\$s\n\nOur team will contact you shortly to confirm.\n", 'brightnest' ), get_post_meta( $request_id, '_bn_name', true ), get_post_meta( $request_id, '_bn_service', true ), get_post_meta( $request_id, '_bn_date', true ), get_post_meta( $request_id, '_bn_time', true ), $order->get_currency() . ' ' . get_post_meta( $request_id, '_bn_total', true ) )
		);
	}
}
add_action( 'woocommerce_payment_complete', 'brightnest_booking_paid' );

/**
 * Where our checkout lives for the booking behind the current Ziina return request.
 *
 * @param string $status paid|cancelled.
 * @return string Empty when the request is not about one of our orders.
 */
function brightnest_payment_return_url( $status ) {
	$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification -- Ziina verifies its own nonce.
	$order    = $order_id ? wc_get_order( $order_id ) : null;
	if ( ! $order || ! brightnest_order_request_id( $order ) ) {
		return '';
	}
	$service_id = (int) $order->get_meta( '_bn_service_id' );
	return add_query_arg(
		array(
			'bn_pay' => $status,
			'order'  => $order->get_id(),
			'key'    => $order->get_order_key(),
		),
		brightnest_checkout_url( $service_id, array( 'step' => 4 ) )
	);
}

/**
 * Successful Ziina payment → our "booking received" screen.
 */
function brightnest_order_received_url( $url, $order ) {
	if ( $order instanceof WC_Order && brightnest_order_request_id( $order ) ) {
		$service_id = (int) $order->get_meta( '_bn_service_id' );
		return add_query_arg(
			array(
				'bn_pay' => 'paid',
				'order'  => $order->get_id(),
				'key'    => $order->get_order_key(),
			),
			brightnest_checkout_url( $service_id, array( 'step' => 4 ) )
		);
	}
	return $url;
}
add_filter( 'woocommerce_get_checkout_order_received_url', 'brightnest_order_received_url', 10, 2 );

/**
 * Cancelled / failed Ziina payment returns to wc_get_checkout_url(): send it to our checkout.
 */
function brightnest_ziina_cancel_url( $url ) {
	$return = brightnest_payment_return_url( 'cancelled' );
	return $return ? $return : $url;
}
add_filter( 'woocommerce_get_checkout_url', 'brightnest_ziina_cancel_url' );

/**
 * Order + booking for a return link (?order=&key=), validated by the order key.
 *
 * @return array|null [ WC_Order, request_id ]
 */
function brightnest_payment_return() {
	if ( empty( $_GET['bn_pay'] ) || empty( $_GET['order'] ) || empty( $_GET['key'] ) || ! function_exists( 'wc_get_order' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return null;
	}
	$order = wc_get_order( absint( $_GET['order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $order || ! hash_equals( $order->get_order_key(), sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return null;
	}
	$request_id = brightnest_order_request_id( $order );
	return $request_id ? array( $order, $request_id ) : null;
}

/**
 * Bookings list / viewer: payment badge.
 */
function brightnest_payment_badge( $request_id ) {
	$status = get_post_meta( $request_id, '_bn_payment_status', true );
	if ( 'paid' === $status ) {
		return '<span class="bn-status bn-status-confirmed">' . esc_html__( 'Paid online', 'brightnest' ) . '</span>';
	}
	if ( 'pending' === $status ) {
		return '<span class="bn-status bn-status-cancelled">' . esc_html__( 'Awaiting payment', 'brightnest' ) . '</span>';
	}
	return '';
}

/**
 * Keep our booking orders out of the "Processing" queue noise: complete them right away.
 */
function brightnest_booking_order_status( $status, $order_id, $order ) {
	return ( $order instanceof WC_Order && brightnest_order_request_id( $order ) ) ? 'completed' : $status;
}
add_filter( 'woocommerce_payment_complete_order_status', 'brightnest_booking_order_status', 10, 3 );
