<?php
/**
 * Multi-step checkout: service options, vouchers, AJAX booking.
 *
 * Service options are stored per service (meta _bn_options), one per line:
 *   Group | Item name | Short description | Price | Old price (optional)
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parsed option groups for a service. Falls back to a single item (the service itself).
 *
 * @param int $post_id Service ID.
 * @return array[] [ [ 'name' => 'Group', 'items' => [ [ id, name, desc, price, old ] ] ] ]
 */
function brightnest_service_options( $post_id ) {
	$raw    = (string) get_post_meta( $post_id, '_bn_options', true );
	$groups = array();

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( count( $parts ) < 4 || '' === $parts[1] ) {
			continue;
		}
		$group = '' !== $parts[0] ? $parts[0] : __( 'Packages', 'brightnest' );
		$price = (float) preg_replace( '/[^0-9.]/', '', $parts[3] );
		$old   = isset( $parts[4] ) ? (float) preg_replace( '/[^0-9.]/', '', $parts[4] ) : 0;
		if ( ! isset( $groups[ $group ] ) ) {
			$groups[ $group ] = array( 'name' => $group, 'items' => array() );
		}
		$groups[ $group ]['items'][] = array(
			// Same item can appear in several groups (e.g. Bestsellers); the id is shared so the cart stays in sync.
			'id'    => substr( md5( $parts[1] . '|' . $parts[2] . '|' . $price ), 0, 10 ),
			'name'  => $parts[1],
			'desc'  => $parts[2],
			'price' => $price,
			'old'   => $old > $price ? $old : 0,
		);
	}

	if ( ! $groups ) {
		$price    = (float) get_post_meta( $post_id, '_bn_price', true );
		$unit     = get_post_meta( $post_id, '_bn_price_unit', true );
		$groups[] = array(
			'name'  => get_the_title( $post_id ),
			'items' => array(
				array(
					'id'    => 'svc' . $post_id,
					'name'  => get_the_title( $post_id ),
					'desc'  => trim( ( $unit ? ucfirst( $unit ) . '. ' : '' ) . get_the_excerpt( $post_id ) ),
					'price' => $price,
					'old'   => 0,
				),
			),
		);
	}

	return array_values( $groups );
}

/**
 * Flat id => item map.
 */
function brightnest_service_items( $post_id ) {
	$map = array();
	foreach ( brightnest_service_options( $post_id ) as $group ) {
		foreach ( $group['items'] as $item ) {
			$map[ $item['id'] ] = $item;
		}
	}
	return $map;
}

/**
 * Vouchers from customizer: "CODE:20%" (percent) or "CODE:50" (fixed amount), one per line.
 *
 * @return array code => [ type, value ]
 */
function brightnest_vouchers() {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) brightnest_opt( 'vouchers' ) ) as $line ) {
		if ( ! preg_match( '/^\s*([A-Za-z0-9_-]+)\s*:\s*([0-9.]+)\s*(%?)\s*$/', $line, $m ) ) {
			continue;
		}
		$out[ strtoupper( $m[1] ) ] = array(
			'type'  => $m[3] ? 'percent' : 'fixed',
			'value' => (float) $m[2],
		);
	}
	return $out;
}

/**
 * Discount for a subtotal.
 */
function brightnest_voucher_discount( $code, $subtotal ) {
	$vouchers = brightnest_vouchers();
	$code     = strtoupper( trim( (string) $code ) );
	if ( '' === $code || ! isset( $vouchers[ $code ] ) || $subtotal <= 0 ) {
		return 0;
	}
	$v = $vouchers[ $code ];
	$d = 'percent' === $v['type'] ? $subtotal * $v['value'] / 100 : $v['value'];
	return round( min( $d, $subtotal ), 2 );
}

/**
 * Checkout page URL for a service.
 */
function brightnest_checkout_url( $service_id, $args = array() ) {
	$page = get_page_by_path( 'checkout' );
	if ( ! $page ) {
		return get_permalink( $service_id ) . '#bn-form';
	}
	return add_query_arg( array_merge( array( 'service' => (int) $service_id, 'step' => 1 ), $args ), get_permalink( $page ) );
}

/**
 * Create the checkout page if missing.
 */
function brightnest_ensure_checkout_page() {
	if ( get_page_by_path( 'checkout' ) ) {
		return;
	}
	$id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_name'   => 'checkout',
			'post_title'  => 'Checkout',
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', 'page-templates/checkout.php' );
	}
}
add_action( 'after_switch_theme', 'brightnest_ensure_checkout_page', 20 );

/**
 * Is the current request the checkout page?
 */
function brightnest_is_checkout() {
	return is_page_template( 'page-templates/checkout.php' );
}

function brightnest_checkout_body_class( $classes ) {
	if ( brightnest_is_checkout() ) {
		$classes[] = 'bn-checkout-page';
	}
	return $classes;
}
add_filter( 'body_class', 'brightnest_checkout_body_class' );

/**
 * Assets + data for checkout.
 */
function brightnest_checkout_assets() {
	if ( ! brightnest_is_checkout() ) {
		return;
	}
	wp_enqueue_style( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css', array(), '1.9.4' );
	wp_enqueue_script( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js', array(), '1.9.4', true );
	wp_enqueue_style( 'brightnest-checkout', BRIGHTNEST_URI . '/assets/css/checkout.css', array( 'brightnest-main' ), BRIGHTNEST_VERSION );
	wp_enqueue_script( 'brightnest-checkout', BRIGHTNEST_URI . '/assets/js/checkout.js', array( 'leaflet' ), BRIGHTNEST_VERSION, true );

	$service_id = isset( $_GET['service'] ) ? absint( $_GET['service'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $service_id || 'bn_service' !== get_post_type( $service_id ) ) {
		return;
	}

	$terms = get_the_terms( $service_id, 'bn_service_cat' );
	wp_localize_script(
		'brightnest-checkout',
		'BN_CHECKOUT',
		array(
			'ajax'     => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'bn_checkout' ),
			'currency' => brightnest_opt( 'currency' ),
			'cities'   => brightnest_cities(),
			'map'      => array(
				'lat' => (float) brightnest_opt( 'map_lat' ),
				'lng' => (float) brightnest_opt( 'map_lng' ),
			),
			'voucher'  => isset( $_GET['voucher'] ) ? strtoupper( sanitize_key( $_GET['voucher'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
			'vouchers' => brightnest_vouchers(),
			'service'  => array(
				'id'       => $service_id,
				'title'    => get_the_title( $service_id ),
				'url'      => get_permalink( $service_id ),
				'category' => ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '',
				'groups'   => brightnest_service_options( $service_id ),
			),
			'home'     => home_url( '/' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'brightnest_checkout_assets' );

/**
 * AJAX: create booking. Prices are recalculated on the server from the service options.
 */
function brightnest_ajax_checkout() {
	check_ajax_referer( 'bn_checkout', 'nonce' );

	$service_id = isset( $_POST['service'] ) ? absint( $_POST['service'] ) : 0;
	if ( ! $service_id || 'bn_service' !== get_post_type( $service_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Service not found.', 'brightnest' ) ), 400 );
	}

	$cart  = isset( $_POST['cart'] ) ? json_decode( wp_unslash( $_POST['cart'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$items = brightnest_service_items( $service_id );
	$lines = array();
	$sub   = 0;
	if ( is_array( $cart ) ) {
		foreach ( $cart as $id => $qty ) {
			$id  = sanitize_key( $id );
			$qty = min( 10, max( 0, (int) $qty ) );
			if ( $qty && isset( $items[ $id ] ) ) {
				$sub    += $items[ $id ]['price'] * $qty;
				$lines[] = $qty . ' x ' . $items[ $id ]['name'] . ' (' . brightnest_opt( 'currency' ) . ' ' . number_format( $items[ $id ]['price'] * $qty, 2 ) . ')';
			}
		}
	}
	if ( ! $lines ) {
		wp_send_json_error( array( 'message' => __( 'Please select at least one option.', 'brightnest' ) ), 400 );
	}

	$f = array();
	foreach ( array( 'name', 'phone', 'email', 'date', 'time', 'area', 'building', 'apartment', 'street', 'address', 'payment', 'voucher', 'frequency' ) as $k ) {
		$f[ $k ] = isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : '';
	}
	$f['message'] = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$f['email']   = sanitize_email( $f['email'] );
	$lat          = isset( $_POST['lat'] ) ? (float) $_POST['lat'] : 0;
	$lng          = isset( $_POST['lng'] ) ? (float) $_POST['lng'] : 0;

	if ( '' === $f['name'] || '' === $f['phone'] || '' === $f['date'] || '' === $f['time'] ) {
		wp_send_json_error( array( 'message' => __( 'Please complete all required fields.', 'brightnest' ) ), 400 );
	}

	$discount = brightnest_voucher_discount( $f['voucher'], $sub );
	$total    = max( 0, $sub - $discount );
	$ref      = 'BN' . wp_date( 'ymd' ) . strtoupper( wp_generate_password( 4, false, false ) );
	// The map label is only a fallback; the typed street is more precise.
	$address  = implode( ', ', array_filter( array( $f['apartment'], $f['building'], $f['street'] ? $f['street'] : $f['address'], $f['area'] ) ) );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'bn_request',
			'post_status' => 'publish',
			/* translators: 1: ref, 2: service, 3: name */
			'post_title'  => sprintf( __( '#%1$s %2$s - %3$s', 'brightnest' ), $ref, get_the_title( $service_id ), $f['name'] ),
		)
	);
	if ( ! $post_id || is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Could not save booking. Please try again.', 'brightnest' ) ), 500 );
	}

	$meta = array(
		'type'      => 'booking',
		'status'    => 'new',
		'ref'       => $ref,
		'service'   => get_the_title( $service_id ),
		'items'     => implode( "\n", $lines ),
		'subtotal'  => number_format( $sub, 2, '.', '' ),
		'discount'  => number_format( $discount, 2, '.', '' ),
		'total'     => number_format( $total, 2, '.', '' ),
		'voucher'   => $discount > 0 ? strtoupper( $f['voucher'] ) : '',
		'payment'   => $f['payment'],
		'frequency' => $f['frequency'],
		'name'      => $f['name'],
		'phone'     => $f['phone'],
		'email'     => $f['email'],
		'date'      => $f['date'],
		'time'      => $f['time'],
		'area'      => $f['area'],
		'address'   => $address,
		'message'   => $f['message'],
		'location'  => ( $lat && $lng ) ? $lat . ',' . $lng : '',
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $post_id, '_bn_' . $k, $v );
	}

	$to = brightnest_opt( 'booking_email' );
	if ( ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}
	$body = array();
	foreach ( $meta as $k => $v ) {
		if ( '' !== $v ) {
			$body[] = ucfirst( $k ) . ': ' . $v;
		}
	}
	if ( $meta['location'] ) {
		$body[] = 'Map: https://www.google.com/maps?q=' . $meta['location'];
	}
	$body[] = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
	wp_mail( $to, '[' . get_bloginfo( 'name' ) . '] New booking #' . $ref, implode( "\n", $body ) );

	if ( is_email( $f['email'] ) ) {
		wp_mail(
			$f['email'],
			/* translators: %s: ref */
			sprintf( __( 'Your booking request #%s', 'brightnest' ), $ref ),
			/* translators: 1: name, 2: service, 3: date, 4: time, 5: total */
			sprintf( __( "Hi %1\$s,\n\nThanks for booking %2\$s on %3\$s (%4\$s).\nTotal: %5\$s\n\nOur team will contact you shortly to confirm.\n", 'brightnest' ), $f['name'], get_the_title( $service_id ), $f['date'], $f['time'], brightnest_opt( 'currency' ) . ' ' . number_format( $total, 2 ) )
		);
	}

	wp_send_json_success(
		array(
			'ref'   => $ref,
			'total' => $total,
		)
	);
}
add_action( 'wp_ajax_bn_checkout', 'brightnest_ajax_checkout' );
add_action( 'wp_ajax_nopriv_bn_checkout', 'brightnest_ajax_checkout' );
