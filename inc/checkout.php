<?php
/**
 * Multi-step checkout: service options, vouchers, AJAX booking.
 *
 * Service options are stored per service (meta _bn_options), one per line:
 *   Group | Item name | Short description | Price | Old price (optional) | Photo URL (optional)
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
	// "Single price" services sell the service itself, whatever is left in the option list.
	$raw    = 'single' === get_post_meta( $post_id, '_bn_mode', true ) ? '' : (string) get_post_meta( $post_id, '_bn_options', true );
	$groups = array();
	// Popup details per option ("About" text + highlights), keyed by "name|desc" (see the admin editor).
	$details = json_decode( (string) get_post_meta( $post_id, '_bn_opt_details', true ), true );
	$details = is_array( $details ) ? $details : array();
	// Arabic site: Arabic option texts (same details store) and tab names (_bn_ar_tabs). Ids and prices stay English-based.
	$ar      = function_exists( 'brightnest_show_ar' ) && brightnest_show_ar();
	$ar_tabs = $ar ? json_decode( (string) get_post_meta( $post_id, '_bn_ar_tabs', true ), true ) : array();
	$ar_tabs = is_array( $ar_tabs ) ? $ar_tabs : array();
	$tab_img = json_decode( (string) get_post_meta( $post_id, '_bn_tab_imgs', true ), true );
	$tab_img = is_array( $tab_img ) ? $tab_img : array();
	$pick    = function ( $d, $field ) use ( $ar ) {
		if ( $ar && isset( $d[ $field . '_ar' ] ) && '' !== trim( (string) $d[ $field . '_ar' ] ) ) {
			return (string) $d[ $field . '_ar' ];
		}
		return isset( $d[ $field ] ) ? (string) $d[ $field ] : '';
	};

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( count( $parts ) < 4 || '' === $parts[1] ) {
			continue;
		}
		$group = '' !== $parts[0] ? $parts[0] : __( 'Packages', 'brightnest' );
		$price = (float) preg_replace( '/[^0-9.]/', '', $parts[3] );
		$old   = isset( $parts[4] ) ? (float) preg_replace( '/[^0-9.]/', '', $parts[4] ) : 0;
		if ( ! isset( $groups[ $group ] ) ) {
			$groups[ $group ] = array(
				'name'  => ! empty( $ar_tabs[ $group ] ) ? $ar_tabs[ $group ] : $group,
				'img'   => ! empty( $tab_img[ $group ] ) ? esc_url_raw( $tab_img[ $group ] ) : '', // Own tab picture, else the first option photo.
				'items' => array(),
			);
		}
		$d = isset( $details[ $parts[1] . '|' . $parts[2] ] ) ? $details[ $parts[1] . '|' . $parts[2] ] : array();
		$groups[ $group ]['items'][] = array(
			// Same item can appear in several groups (e.g. Bestsellers); the id is shared so the cart stays in sync.
			'id'    => substr( md5( $parts[1] . '|' . $parts[2] . '|' . $price ), 0, 10 ),
			// Survives base-price edits, so per-area prices stay attached to the option.
			'key'   => substr( md5( $parts[1] . '|' . $parts[2] ), 0, 10 ),
			'name'  => $ar && ! empty( $d['name_ar'] ) ? $d['name_ar'] : $parts[1],
			'desc'  => $ar && ! empty( $d['desc_ar'] ) ? $d['desc_ar'] : $parts[2],
			'price' => $price,
			'old'   => $old > $price ? $old : 0,
			'img'   => isset( $parts[5] ) ? esc_url_raw( $parts[5] ) : '',
			'about' => $pick( $d, 'about' ),
			'hl'    => brightnest_option_highlights( $pick( $d, 'hl' ) ),
		);
		// The tab photo is the first option photo in it.
		if ( empty( $groups[ $group ]['img'] ) && ! empty( $parts[5] ) ) {
			$groups[ $group ]['img'] = esc_url_raw( $parts[5] );
		}
	}

	if ( ! $groups ) {
		$price    = (float) get_post_meta( $post_id, '_bn_price', true );
		$unit     = get_post_meta( $post_id, '_bn_price_unit', true );
		$groups[] = array(
			'name'  => get_the_title( $post_id ),
			'items' => array(
				array(
					'id'    => 'svc' . $post_id,
					'key'   => 'svc' . $post_id,
					'name'  => get_the_title( $post_id ),
					'desc'  => trim( ( $unit ? ucfirst( $unit ) . '. ' : '' ) . get_the_excerpt( $post_id ) ),
					'price' => $price,
					'old'   => 0,
					'img'   => '',
					'about' => '',
					'hl'    => array(),
				),
			),
		);
	}

	return array_values( $groups );
}

/**
 * Highlights for the option popup. One per line, optionally "icon | Label"
 * (theme icon names such as shield, clock, star, check-circle, ac, cleaning).
 *
 * @param string $text Lines from the admin.
 * @return array[] [ [ 'svg' => icon markup, 'label' => text ] ]
 */
function brightnest_option_highlights( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$icon  = 'check-circle';
		$label = $line;
		if ( false !== strpos( $line, '|' ) ) {
			list( $maybe, $rest ) = array_map( 'trim', explode( '|', $line, 2 ) );
			$maybe = sanitize_key( $maybe );
			// Only a real theme icon counts (unknown names fall back to "home").
			if ( $maybe && false !== strpos( brightnest_icon( $maybe ), 'bn-icon-' . $maybe . '"' ) ) {
				$icon  = $maybe;
				$label = $rest;
			}
		}
		$out[] = array( 'svg' => brightnest_icon( $icon, 34 ), 'label' => $label );
	}
	return $out;
}

/**
 * Integer service meta with a default and limits.
 */
function brightnest_meta_int( $post_id, $key, $default, $min, $max ) {
	$v = get_post_meta( $post_id, $key, true );
	return '' === $v ? $default : max( $min, min( $max, (int) $v ) );
}

/**
 * Text service meta with a default.
 */
function brightnest_meta_text( $post_id, $key, $default ) {
	$v = trim( (string) get_post_meta( $post_id, $key, true ) );
	return '' === $v ? $default : $v;
}

/**
 * Hourly booking config (e.g. home cleaning: hours x professionals + optional materials),
 * or null when the service uses the option list. Every part is set per service.
 *
 * @param int $post_id Service ID.
 * @return array|null
 */
function brightnest_hourly_config( $post_id ) {
	if ( 'hourly' !== get_post_meta( $post_id, '_bn_mode', true ) ) {
		return null;
	}
	$rate = (float) get_post_meta( $post_id, '_bn_hourly_rate', true );
	if ( $rate <= 0 ) {
		$rate = (float) get_post_meta( $post_id, '_bn_price', true );
	}
	$h_min  = brightnest_meta_int( $post_id, '_bn_hours_min', 1, 1, 12 );
	$h_max  = max( $h_min, brightnest_meta_int( $post_id, '_bn_hours_max', 8, 1, 12 ) );
	$mat_on = '0' !== (string) get_post_meta( $post_id, '_bn_materials_on', true );
	return array(
		'rate'      => $rate,
		'materials' => $mat_on ? (float) get_post_meta( $post_id, '_bn_materials_rate', true ) : 0,
		'mat_on'    => $mat_on,
		'hours'     => range( $h_min, $h_max ),
		'pros'      => range( 1, brightnest_meta_int( $post_id, '_bn_pros_max', 4, 1, 10 ) ),
		'q_hours'   => brightnest_meta_text( $post_id, '_bn_q_hours', __( 'How many hours do you need your professional to stay?', 'brightnest' ) ),
		'q_pros'    => brightnest_meta_text( $post_id, '_bn_q_pros', __( 'How many professionals do you need?', 'brightnest' ) ),
		'q_mat'     => brightnest_meta_text( $post_id, '_bn_q_materials', __( 'Need cleaning materials?', 'brightnest' ) ),
		'mat_name'  => brightnest_meta_text( $post_id, '_bn_materials_name', __( 'Cleaning materials', 'brightnest' ) ),
	);
}

/**
 * "How often?" choices for a service: [ [ label, discount % ], … ]. Empty = question hidden.
 * Hourly services default to One time / Weekly / Every 2 weeks with no discount.
 *
 * @param int $post_id Service ID.
 * @return array[]
 */
function brightnest_service_frequencies( $post_id ) {
	$saved = get_post_meta( $post_id, '_bn_freqs', true );
	if ( is_array( $saved ) ) {
		$out = array();
		foreach ( $saved as $row ) {
			if ( isset( $row[0] ) && '' !== trim( $row[0] ) ) {
				$out[] = array( trim( $row[0] ), max( 0, min( 90, (float) ( isset( $row[1] ) ? $row[1] : 0 ) ) ) );
			}
		}
		return $out;
	}
	if ( 'hourly' === get_post_meta( $post_id, '_bn_mode', true ) ) {
		return array( array( __( 'One time', 'brightnest' ), 0 ), array( __( 'Weekly', 'brightnest' ), 0 ), array( __( 'Every 2 weeks', 'brightnest' ), 0 ) );
	}
	return array();
}

/**
 * "How long should I book for?" guide rows: [ [ home size, duration ], … ] (hourly services).
 */
function brightnest_service_guide( $post_id ) {
	if ( 'hourly' !== get_post_meta( $post_id, '_bn_mode', true ) ) {
		return array();
	}
	$raw = trim( (string) get_post_meta( $post_id, '_bn_guide', true ) );
	if ( '-' === $raw ) {
		return array();
	}
	if ( '' === $raw ) {
		$raw = "Studio|2 hours\n1 bedroom|2–3 hours\n2 bedrooms|3–4 hours\n3 bedrooms|4–5 hours\n4+ bedrooms|5–7 hours";
	}
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$p = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' !== $p[0] && isset( $p[1] ) ) {
			$rows[] = $p;
		}
	}
	return $rows;
}

/**
 * Is the service switched on (Services list → On/Off)?
 */
function brightnest_service_active( $post_id ) {
	return '0' !== (string) get_post_meta( $post_id, '_bn_active', true );
}

/**
 * Switched-off services disappear from every list on the website (homepage, archives,
 * search, footer, forms). They stay editable in wp-admin.
 */
function brightnest_hide_inactive_services( $query ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	$type = (array) $query->get( 'post_type' );
	if ( ! in_array( 'bn_service', $type, true ) && ! $query->is_tax( 'bn_service_cat' ) ) {
		return;
	}
	$meta   = (array) $query->get( 'meta_query' );
	$meta[] = array(
		'relation' => 'OR',
		array( 'key' => '_bn_active', 'compare' => 'NOT EXISTS' ),
		array( 'key' => '_bn_active', 'value' => '0', 'compare' => '!=' ),
	);
	$query->set( 'meta_query', $meta );
}
add_action( 'pre_get_posts', 'brightnest_hide_inactive_services' );

/**
 * Per-area availability and pricing for a service, for every city in Settings → Business:
 * [ city => [ 'on' => bool, 'mode' => same|pct|own, 'adj' => percent,
 *             'prices' => [ option key => price ], 'rate' => hourly rate, 'materials' => materials rate ] ]
 * mode "same" = base prices, "pct" = base ± adj %, "own" = the area's own prices
 * (options left empty fall back to the base price).
 */
function brightnest_service_areas( $post_id ) {
	$saved = get_post_meta( $post_id, '_bn_areas', true );
	$saved = is_array( $saved ) ? $saved : array();
	$out   = array();
	foreach ( brightnest_cities() as $city ) {
		$row  = isset( $saved[ $city ] ) ? $saved[ $city ] : array();
		$adj  = isset( $row['adj'] ) ? max( -90, min( 500, (float) $row['adj'] ) ) : 0;
		$mode = isset( $row['mode'] ) && in_array( $row['mode'], array( 'same', 'pct', 'own' ), true ) ? $row['mode'] : ( $adj ? 'pct' : 'same' );
		$prices = array();
		foreach ( ( isset( $row['prices'] ) && is_array( $row['prices'] ) ) ? $row['prices'] : array() as $k => $v ) {
			if ( '' !== (string) $v && (float) $v > 0 ) {
				$prices[ sanitize_key( $k ) ] = round( (float) $v, 2 );
			}
		}
		$out[ $city ] = array(
			'on'        => ! isset( $row['on'] ) || '0' !== (string) $row['on'],
			'mode'      => $mode,
			'adj'       => 'pct' === $mode ? $adj : 0,
			'prices'    => 'own' === $mode ? $prices : array(),
			'rate'      => 'own' === $mode && ! empty( $row['rate'] ) ? round( (float) $row['rate'], 2 ) : 0,
			'materials' => 'own' === $mode && isset( $row['materials'] ) && '' !== (string) $row['materials'] ? round( (float) $row['materials'], 2 ) : null,
		);
	}
	return $out;
}

/**
 * Price multiplier for a service in an area (1 = base price), or null when it isn't offered there.
 */
function brightnest_area_factor( $post_id, $area ) {
	$areas = brightnest_service_areas( $post_id );
	if ( ! isset( $areas[ $area ] ) || ! $areas[ $area ]['on'] ) {
		return null;
	}
	return 1 + $areas[ $area ]['adj'] / 100;
}

/**
 * Unit price of one option in an area: the area's own price if set, else base × % change.
 */
function brightnest_area_item_price( $post_id, $area, $item ) {
	$areas = brightnest_service_areas( $post_id );
	$row   = isset( $areas[ $area ] ) ? $areas[ $area ] : null;
	if ( $row && isset( $item['key'], $row['prices'][ $item['key'] ] ) ) {
		return $row['prices'][ $item['key'] ];
	}
	return round( $item['price'] * ( $row ? 1 + $row['adj'] / 100 : 1 ), 2 );
}

/**
 * Hourly config for an area: own rate / materials rate replace the base ones when set.
 */
function brightnest_area_hourly( $post_id, $area, $config ) {
	$areas = brightnest_service_areas( $post_id );
	$row   = isset( $areas[ $area ] ) ? $areas[ $area ] : null;
	if ( ! $row ) {
		return $config;
	}
	$f                   = 1 + $row['adj'] / 100;
	$config['rate']      = $row['rate'] > 0 ? $row['rate'] : round( $config['rate'] * $f, 2 );
	$config['materials'] = null !== $row['materials'] ? $row['materials'] : round( $config['materials'] * $f, 2 );
	return $config;
}

/**
 * Fixed extra charge for an area (Settings → Booking → Area charges), 0 = none.
 */
function brightnest_area_fee( $area ) {
	$fees = brightnest_opt( 'area_fees' );
	return is_array( $fees ) && isset( $fees[ $area ] ) ? max( 0, (float) $fees[ $area ] ) : 0;
}

/**
 * Service fee added to every booking (Settings → Booking), 0 = free.
 */
function brightnest_service_fee() {
	return max( 0, (float) brightnest_opt( 'service_fee' ) );
}

/**
 * Price an hourly booking. Returns [ subtotal, lines ] or null when the selection is invalid.
 */
function brightnest_price_hourly( $config, $sel, $factor = 1 ) {
	$config['rate']      = round( $config['rate'] * $factor, 2 );
	$config['materials'] = round( $config['materials'] * $factor, 2 );
	$hours = isset( $sel['hours'] ) ? (int) $sel['hours'] : 0;
	$pros  = isset( $sel['pros'] ) ? (int) $sel['pros'] : 0;
	if ( ! in_array( $hours, $config['hours'], true ) || ! in_array( $pros, $config['pros'], true ) || ( $config['mat_on'] && ! isset( $sel['materials'] ) ) ) {
		return null;
	}
	$cur   = brightnest_opt( 'currency' );
	$base  = $config['rate'] * $hours * $pros;
	/* translators: 1: hours, 2: professionals */
	$lines = array( sprintf( _n( '%1$d hour', '%1$d hours', $hours, 'brightnest' ), $hours ) . ' x ' . sprintf( _n( '%d professional', '%d professionals', $pros, 'brightnest' ), $pros ) . ' (' . $cur . ' ' . number_format( $base, 2 ) . ')' );
	$sub   = $base;
	if ( $config['mat_on'] && ! empty( $sel['materials'] ) && $config['materials'] > 0 ) {
		$mat     = $config['materials'] * $hours * $pros;
		$sub    += $mat;
		$lines[] = $config['mat_name'] . ' (' . $cur . ' ' . number_format( $mat, 2 ) . ')';
	}
	return array( $sub, $lines );
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
	brightnest_map_assets();
	wp_enqueue_style( 'brightnest-location', BRIGHTNEST_URI . '/assets/css/location.css', array( 'brightnest-main' ), BRIGHTNEST_VERSION );
	wp_enqueue_style( 'brightnest-checkout', BRIGHTNEST_URI . '/assets/css/checkout.css', array( 'brightnest-location' ), BRIGHTNEST_VERSION );
	wp_enqueue_script( 'brightnest-checkout', BRIGHTNEST_URI . '/assets/js/checkout.js', array( 'brightnest-map' ), BRIGHTNEST_VERSION, true );

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
			'cityLabels' => brightnest_city_labels(), // Shown names (Arabic site).
			'country'  => strtolower( substr( preg_replace( '/[^A-Za-z]/', '', (string) brightnest_opt( 'country_code' ) ), 0, 2 ) ),
			'map'      => array(
				'lat' => (float) brightnest_opt( 'map_lat' ),
				'lng' => (float) brightnest_opt( 'map_lng' ),
			),
			'voucher'  => isset( $_GET['voucher'] ) ? strtoupper( sanitize_key( $_GET['voucher'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
			// Only codes the visitor already has (link or promo banner); others are checked on the server.
			'vouchers' => brightnest_public_vouchers(),
			// Opened from an "unfinished booking" email: cart and details to restore.
			'draft'    => function_exists( 'brightnest_checkout_draft' ) ? brightnest_checkout_draft( $service_id ) : null,
			'remind'   => '1' === (string) brightnest_opt( 'remind_on' ),
			'service'  => array(
				'id'       => $service_id,
				// Plain text: the script escapes it itself (get_the_title() returns entities like &#8217;).
				'title'    => wp_strip_all_tags( html_entity_decode( get_the_title( $service_id ), ENT_QUOTES, 'UTF-8' ) ),
				'url'      => get_post_type_archive_link( 'bn_service' ), // Back from step 1 → services hub.
				'category' => ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '',
				'groups'   => brightnest_service_options( $service_id ),
				'hourly'   => brightnest_hourly_config( $service_id ),
				'freqs'    => brightnest_service_frequencies( $service_id ),
				'areas'    => brightnest_service_areas( $service_id ),
				'active'   => brightnest_service_active( $service_id ),
			),
			'fee'      => brightnest_service_fee(),
			'feeLabel' => brightnest_opt( 'service_fee_label' ),
			'areaFees' => (object) array_map( 'brightnest_area_fee', array_combine( brightnest_cities(), brightnest_cities() ) ),
			'min'      => max( 2, (float) brightnest_opt( 'min_amount' ) ),
			'home'     => home_url( '/' ),
			'whatsapp' => brightnest_whatsapp_url(),
			'slots'    => array(
				'start' => (int) brightnest_opt( 'slot_start' ),
				'end'   => (int) brightnest_opt( 'slot_end' ),
				'lead'  => (int) brightnest_opt( 'lead_hours' ),
				'step'  => in_array( (int) brightnest_opt( 'slot_length' ), array( 30, 60, 90, 120 ), true ) ? (int) brightnest_opt( 'slot_length' ) : 60,
				'days'  => max( 1, (int) brightnest_opt( 'days_ahead' ) ),
			),
			'payments' => array_keys( brightnest_payment_methods() ),
			'online'   => BRIGHTNEST_ONLINE_METHOD,
			'paid'     => brightnest_checkout_return_state(),
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

	if ( ! brightnest_service_active( $service_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Sorry, this service is not available right now.', 'brightnest' ) ), 400 );
	}
	// Area: must be one of our cities and offered there; its price change applies to every price.
	$area   = isset( $_POST['area'] ) ? sanitize_text_field( wp_unslash( $_POST['area'] ) ) : '';
	$factor = brightnest_area_factor( $service_id, $area );
	if ( null === $factor ) {
		/* translators: %s: area */
		wp_send_json_error( array( 'message' => sprintf( __( 'Sorry, this service is not available in %s yet.', 'brightnest' ), $area ) ), 400 );
	}

	$cart  = isset( $_POST['cart'] ) ? json_decode( wp_unslash( $_POST['cart'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$items  = brightnest_service_items( $service_id );
	$lines  = array();
	$sub    = 0;
	$hourly = brightnest_hourly_config( $service_id );
	if ( $hourly ) {
		$priced = is_array( $cart ) ? brightnest_price_hourly( brightnest_area_hourly( $service_id, $area, $hourly ), $cart ) : null;
		if ( $priced ) {
			list( $sub, $lines ) = $priced;
		}
	} elseif ( is_array( $cart ) ) {
		foreach ( $cart as $id => $qty ) {
			$id  = sanitize_key( $id );
			$qty = min( 10, max( 0, (int) $qty ) );
			if ( $qty && isset( $items[ $id ] ) ) {
				$unit    = brightnest_area_item_price( $service_id, $area, $items[ $id ] );
				$sub    += $unit * $qty;
				$lines[] = $qty . ' x ' . $items[ $id ]['name'] . ' (' . brightnest_opt( 'currency' ) . ' ' . number_format( $unit * $qty, 2 ) . ')';
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
	// Only payment methods enabled in Settings → Booking are accepted.
	$bn_methods = array_keys( brightnest_payment_methods() );
	if ( ! in_array( isset( $_POST['payment'] ) ? wp_unslash( $_POST['payment'] ) : '', $bn_methods, true ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$_POST['payment'] = $bn_methods[0];
	}
	$f['payment'] = sanitize_text_field( wp_unslash( $_POST['payment'] ) );
	$f['message'] = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$f['email']   = sanitize_email( $f['email'] );
	$lat          = isset( $_POST['lat'] ) ? (float) $_POST['lat'] : 0;
	$lng          = isset( $_POST['lng'] ) ? (float) $_POST['lng'] : 0;

	if ( '' === $f['name'] || '' === $f['phone'] || '' === $f['date'] || '' === $f['time'] ) {
		wp_send_json_error( array( 'message' => __( 'Please complete all required fields.', 'brightnest' ) ), 400 );
	}
	// Online payment is the only method: don't save a booking that can't be paid.
	if ( ! brightnest_ziina_gateway() ) {
		wp_send_json_error( array( 'message' => __( 'Online payment is temporarily unavailable. Please contact us on WhatsApp to book.', 'brightnest' ) ), 503 );
	}

	// The day must be inside the booking window (today … Settings → days ahead).
	$today = wp_date( 'Y-m-d' );
	$last  = wp_date( 'Y-m-d', time() + max( 1, (int) brightnest_opt( 'days_ahead' ) ) * DAY_IN_SECONDS );
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $f['date'] ) || $f['date'] < $today || $f['date'] > $last ) {
		wp_send_json_error( array( 'message' => __( 'Please pick a date from the list.', 'brightnest' ) ), 400 );
	}

	// "How often?" — only the service's own choices; their discount comes off the subtotal.
	$freqs     = brightnest_service_frequencies( $service_id );
	$freq_pct  = 0;
	$freq_name = '';
	foreach ( $freqs as $fr ) {
		if ( strtolower( $fr[0] ) === strtolower( $f['frequency'] ) ) {
			$freq_name = $fr[0];
			$freq_pct  = $fr[1];
		}
	}
	if ( $freqs && '' === $freq_name ) {
		$freq_name = $freqs[0][0];
		$freq_pct  = $freqs[0][1];
	}
	$f['frequency'] = $freq_name;
	$freq_disc      = round( $sub * $freq_pct / 100, 2 );

	$discount = brightnest_voucher_discount( $f['voucher'], $sub - $freq_disc );
	$fee      = brightnest_service_fee();
	$area_fee = brightnest_area_fee( $area );
	$total    = round( max( 0, $sub - $freq_disc - $discount ) + $fee + $area_fee, 2 );
	$min      = max( 2, (float) brightnest_opt( 'min_amount' ) ); // Ziina's own minimum is AED 2.
	if ( $total < $min ) {
		/* translators: %s: minimum amount */
		wp_send_json_error( array( 'message' => sprintf( __( 'The minimum booking amount is %s.', 'brightnest' ), brightnest_opt( 'currency' ) . ' ' . number_format_i18n( $min, 2 ) ) ), 400 );
	}
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
	// The checkout draft became a booking: no "unfinished booking" reminder for it.
	if ( ! empty( $_POST['draft'] ) && function_exists( 'brightnest_delete_draft' ) ) {
		brightnest_delete_draft( wp_unslash( $_POST['draft'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}

	$meta = array(
		'type'      => 'booking',
		'status'    => 'new',
		'ref'       => $ref,
		'service'   => get_the_title( $service_id ),
		'items'     => implode( "\n", $lines ),
		'subtotal'  => number_format( $sub, 2, '.', '' ),
		'freq_discount' => $freq_disc > 0 ? number_format( $freq_disc, 2, '.', '' ) : '',
		'discount'  => number_format( $discount, 2, '.', '' ),
		'fee'       => $fee > 0 ? number_format( $fee, 2, '.', '' ) : '',
		'area_fee'  => $area_fee > 0 ? number_format( $area_fee, 2, '.', '' ) : '',
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
		'lang'      => function_exists( 'brightnest_lang' ) ? brightnest_lang() : 'en', // Site language the customer booked on.
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

	if ( BRIGHTNEST_ONLINE_METHOD === $f['payment'] ) {
		$redirect = brightnest_start_online_payment( $post_id, $meta, $service_id );
		if ( is_wp_error( $redirect ) ) {
			update_post_meta( $post_id, '_bn_payment_status', 'failed' );
			wp_send_json_error( array( 'message' => $redirect->get_error_message() ), 502 );
		}
		wp_send_json_success(
			array(
				'ref'      => $ref,
				'total'    => $total,
				'redirect' => $redirect,
			)
		);
	}

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

/**
 * Vouchers the visitor can already see: ?voucher= in the link and the promo banner code.
 */
function brightnest_public_vouchers() {
	$all  = brightnest_vouchers();
	$out  = array();
	$seen = array( strtoupper( (string) brightnest_opt( 'promo_code' ) ) );
	if ( isset( $_GET['voucher'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$seen[] = strtoupper( sanitize_key( $_GET['voucher'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	foreach ( $seen as $code ) {
		if ( isset( $all[ $code ] ) ) {
			$out[ $code ] = $all[ $code ];
		}
	}
	return (object) $out;
}

/**
 * AJAX: check one voucher code typed at checkout.
 */
function brightnest_ajax_voucher() {
	check_ajax_referer( 'bn_checkout', 'nonce' );
	$code     = isset( $_POST['code'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['code'] ) ) ) : '';
	$vouchers = brightnest_vouchers();
	if ( '' === $code || ! isset( $vouchers[ $code ] ) ) {
		wp_send_json_error( array( 'message' => __( 'This voucher code is not valid.', 'brightnest' ) ) );
	}
	wp_send_json_success( array( 'code' => $code, 'voucher' => $vouchers[ $code ] ) );
}
add_action( 'wp_ajax_bn_voucher', 'brightnest_ajax_voucher' );
add_action( 'wp_ajax_nopriv_bn_voucher', 'brightnest_ajax_voucher' );

/**
 * Returning from Ziina: what the checkout should show (null = normal checkout).
 */
function brightnest_checkout_return_state() {
	$ret = function_exists( 'brightnest_payment_return' ) ? brightnest_payment_return() : null;
	if ( ! $ret ) {
		return null;
	}
	list( $order, $request_id ) = $ret;
	return array(
		'paid'  => 'paid' === get_post_meta( $request_id, '_bn_payment_status', true ),
		'ref'   => get_post_meta( $request_id, '_bn_ref', true ),
		'total' => (float) get_post_meta( $request_id, '_bn_total', true ),
	);
}
add_action( 'wp_ajax_bn_checkout', 'brightnest_ajax_checkout' );
add_action( 'wp_ajax_nopriv_bn_checkout', 'brightnest_ajax_checkout' );
