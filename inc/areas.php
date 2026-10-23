<?php
/**
 * Service areas as a taxonomy: Services → Areas.
 *
 * Three levels: City → Zone → Area (e.g. Dubai → Marina, JBR & Coastal Communities → Dubai Marina).
 * A zone is optional: areas placed straight under a city are shown in a group named after the city,
 * and a city without areas is offered as one place. Each term has an order number, a "popular" flag
 * (quick picks at the top of the area picker) and an Arabic name.
 *
 * Names are the keys used by prices ("Areas & prices" per service) and area charges ("city|area"),
 * so renaming a city or area means setting its prices again.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

function brightnest_register_areas() {
	register_taxonomy(
		'bn_area',
		'bn_service',
		array(
			'labels'             => array(
				'name'          => __( 'Areas', 'brightnest' ),
				'singular_name' => __( 'Area', 'brightnest' ),
				'add_new_item'  => __( 'Add city, zone or area', 'brightnest' ),
				'edit_item'     => __( 'Edit area', 'brightnest' ),
				'parent_item'   => __( 'Inside (city or zone)', 'brightnest' ),
				'search_items'  => __( 'Search areas', 'brightnest' ),
				'menu_name'     => __( 'Areas', 'brightnest' ),
				'back_to_items' => __( '← Back to areas', 'brightnest' ),
			),
			'hierarchical'       => true,
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_tagcloud'      => false,
			'show_in_quick_edit' => false,
			'show_admin_column'  => false,
			'meta_box_cb'        => false, // Availability per service is set in the service's "Areas & prices" tab.
			'rewrite'            => false,
			'query_var'          => false,
		)
	);
}
add_action( 'init', 'brightnest_register_areas', 5 );

/**
 * Cities, zones and areas:
 * [ city => [ 'label', 'zones' => [ zone => [ 'label', 'areas' => [ [ 'name', 'label', 'popular' ] ] ] ] ] ].
 * Names stay English (keys for prices); "label" is the Arabic name on the Arabic site.
 */
function brightnest_area_tree() {
	static $tree = null;
	if ( null !== $tree ) {
		return $tree;
	}
	if ( ! taxonomy_exists( 'bn_area' ) ) {
		return array(); // Too early (before init): do not cache.
	}
	$tree  = array();
	$terms = get_terms( array( 'taxonomy' => 'bn_area', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return $tree;
	}
	$kids = array();
	foreach ( $terms as $t ) {
		$kids[ (int) $t->parent ][] = $t;
	}
	$ar    = function_exists( 'brightnest_is_ar' ) && brightnest_is_ar();
	$name  = function ( $t ) {
		return html_entity_decode( $t->name, ENT_QUOTES, 'UTF-8' );
	};
	$label = function ( $t ) use ( $ar, $name ) {
		$a = $ar ? get_term_meta( $t->term_id, 'bn_name_ar', true ) : '';
		return $a ? $a : $name( $t );
	};
	$sort  = function ( $list ) use ( $name ) {
		usort(
			$list,
			function ( $a, $b ) use ( $name ) {
				$oa = (int) get_term_meta( $a->term_id, 'bn_order', true );
				$ob = (int) get_term_meta( $b->term_id, 'bn_order', true );
				return $oa === $ob ? strcasecmp( $name( $a ), $name( $b ) ) : $oa - $ob;
			}
		);
		return $list;
	};
	$place = function ( $t ) use ( $name, $label ) {
		return array(
			'name'    => $name( $t ),
			'label'   => $label( $t ),
			'popular' => (bool) get_term_meta( $t->term_id, 'bn_popular', true ),
		);
	};
	foreach ( $sort( isset( $kids[0] ) ? $kids[0] : array() ) as $city ) {
		$c      = array( 'label' => $label( $city ), 'zones' => array() );
		$direct = array();
		foreach ( $sort( isset( $kids[ $city->term_id ] ) ? $kids[ $city->term_id ] : array() ) as $child ) {
			if ( ! empty( $kids[ $child->term_id ] ) ) {
				$c['zones'][ $name( $child ) ] = array(
					'label' => $label( $child ),
					'areas' => array_map( $place, $sort( $kids[ $child->term_id ] ) ),
				);
			} else {
				$direct[] = $place( $child );
			}
		}
		if ( $direct || ! $c['zones'] ) {
			// Areas straight under the city (or none at all: the city itself is the place).
			$c['zones'][ $name( $city ) ] = array(
				'label' => $c['label'],
				'areas' => $direct ? $direct : array( $place( $city ) ),
			);
		}
		$tree[ $name( $city ) ] = $c;
	}
	return $tree;
}

/**
 * Shown name of a city, zone or area (Arabic on the Arabic site).
 *
 * @param string $name English name.
 */
function brightnest_area_label( $name ) {
	foreach ( brightnest_area_tree() as $city => $c ) {
		if ( $city === $name ) {
			return $c['label'];
		}
		foreach ( $c['zones'] as $zone => $z ) {
			if ( $zone === $name ) {
				return $z['label'];
			}
			foreach ( $z['areas'] as $a ) {
				if ( $a['name'] === $name ) {
					return $a['label'];
				}
			}
		}
	}
	return $name;
}

/* First run: fill the taxonomy from the list the theme used to have built in. -------- */

function brightnest_area_seed_list() {
	return array(
		'Dubai'     => array(
			'Old Dubai, Deira & Bur Dubai'                  => array( 'Abu Hail', 'Al Baraha', 'Al Fahidi', 'Al Garhoud', 'Al Hamriya', 'Al Hudaiba', 'Al Jaddaf', 'Al Jafiliya', 'Al Karama', 'Al Mamzar', 'Al Mankhool', 'Al Muraqqabat', 'Al Muteena', 'Al Raffa', 'Al Ras', 'Al Rigga', 'Al Sabkha', 'Al Satwa', 'Al Souk Al Kabeer', 'Bur Dubai', 'Deira', 'Dubai Healthcare City', 'Hor Al Anz', 'Naif', 'Oud Metha', 'Port Saeed', 'Umm Hurair', 'Umm Ramool' ),
			'Downtown, Business Bay & Central Dubai'        => array( 'Al Kifaf', 'Al Quoz', 'Al Quoz Industrial', 'Al Safa', 'Al Wasl', 'Business Bay', 'City Walk', 'DIFC', 'Downtown Dubai', 'Dubai Design District (d3)', 'Ras Al Khor', 'Sheikh Zayed Road', 'Trade Centre', 'Za\'abeel' ),
			'Marina, JBR & Coastal Communities'             => array( 'Al Sufouh', 'Bluewaters Island', 'Dubai Harbour', 'Dubai Internet City', 'Dubai Marina', 'Dubai Media City', 'Emaar Beachfront', 'Jumeirah 1', 'Jumeirah 2', 'Jumeirah 3', 'Jumeirah Bay', 'Jumeirah Beach Residence (JBR)', 'Jumeirah Lake Towers (JLT)', 'Knowledge Park', 'La Mer', 'Palm Jumeirah', 'Pearl Jumeirah', 'Umm Suqeim 1', 'Umm Suqeim 2', 'Umm Suqeim 3' ),
			'Al Barsha, JVC & Al Furjan'                    => array( 'Al Barsha 1', 'Al Barsha 2', 'Al Barsha 3', 'Al Barsha South', 'Al Furjan', 'Arjan', 'Barsha Heights (TECOM)', 'Discovery Gardens', 'Dubai Production City (IMPZ)', 'Dubai Science Park', 'Dubai Sports City', 'Dubai Studio City', 'Ibn Battuta', 'Jebel Ali Village', 'Jumeirah Village Circle (JVC)', 'Jumeirah Village Triangle (JVT)', 'Liwan', 'Majan', 'Motor City', 'The Gardens' ),
			'Villa Societies & Family Communities'          => array( 'Al Barari', 'Arabian Ranches', 'Arabian Ranches 2', 'Arabian Ranches 3', 'Cherrywoods', 'Damac Hills', 'Damac Hills 2', 'Dubai Hills Estate', 'Emirates Hills', 'Falcon City of Wonders', 'Jumeirah Golf Estates', 'Jumeirah Islands', 'Jumeirah Park', 'Living Legends', 'Mudon', 'Serena', 'The Greens', 'The Lakes', 'The Meadows', 'The Springs', 'The Valley', 'The Views', 'The Villa', 'Tilal Al Ghaf', 'Victory Heights', 'Villanova' ),
			'Mirdif, Warqa & North-East Dubai'              => array( 'Al Awir', 'Al Khawaneej 1', 'Al Khawaneej 2', 'Al Mizhar 1', 'Al Mizhar 2', 'Al Nahda 1', 'Al Nahda 2', 'Al Qusais 1', 'Al Qusais 2', 'Al Qusais 3', 'Al Rashidiya', 'Al Twar 1', 'Al Twar 2', 'Al Twar 3', 'Al Warqa 1', 'Al Warqa 2', 'Al Warqa 3', 'Al Warqa 4', 'Dubai Academic City', 'Dubai Festival City', 'Dubai Silicon Oasis', 'International City', 'Mirdif', 'Muhaisnah 1', 'Muhaisnah 2', 'Muhaisnah 3', 'Muhaisnah 4', 'Nad Al Hamar', 'Oud Al Muteena', 'Uptown Mirdif' ),
			'Dubai South, DIP & Outer Communities'          => array( 'Al Lisaili', 'Al Marmoom', 'Al Maktoum Airport Area', 'Al Yalayis', 'Dubai Investments Park (DIP)', 'Dubai South', 'Emaar South', 'Expo City Dubai', 'Green Community', 'Hatta', 'Jebel Ali Free Zone (JAFZA)', 'Jebel Ali Industrial', 'The Sustainable City' ),
			'Major Residential Developments & Communities' => array( 'Azizi Riviera', 'Damac Lagoons', 'District One', 'Dubai Creek Harbour', 'Dubai Islands', 'Dubai Land Residence Complex', 'Dubai Maritime City', 'Dubailand', 'Meydan', 'Mira', 'Mira Oasis', 'Mohammed Bin Rashid City (MBR City)', 'Nad Al Sheba', 'Rashid Yachts & Marina', 'Reem', 'Remraam', 'Sobha Hartland', 'Sobha Hartland 2', 'Town Square' ),
		),
		'Abu Dhabi' => array(
			'Abu Dhabi' => array( 'Al Bateen', 'Al Ghadeer', 'Al Khalidiyah', 'Al Maryah Island', 'Al Muroor', 'Al Mushrif', 'Al Nahyan', 'Al Raha Beach', 'Al Raha Gardens', 'Al Reef', 'Al Reem Island', 'Al Shamkha', 'Al Zahiyah', 'Baniyas', 'Corniche', 'Khalifa City', 'Masdar City', 'Mohammed Bin Zayed City', 'Mussafah', 'Saadiyat Island', 'Tourist Club Area', 'Yas Island' ),
		),
		'Sharjah'   => array(
			'Sharjah' => array( 'Abu Shagara', 'Al Azra', 'Al Butina', 'Al Khan', 'Al Majaz', 'Al Nabba', 'Al Nahda', 'Al Qadisiya', 'Al Qasimia', 'Al Rahmaniya', 'Al Suyoh', 'Al Taawun', 'Al Yarmook', 'Al Zahia', 'Aljada', 'Industrial Area', 'Muwaileh', 'Rolla', 'Tilal City', 'University City' ),
		),
	);
}

function brightnest_seed_areas() {
	if ( get_option( 'bn_areas_seeded' ) || ! taxonomy_exists( 'bn_area' ) ) {
		return;
	}
	update_option( 'bn_areas_seeded', 1 ); // Once, even if the admin later deletes everything.
	$existing = get_terms( array( 'taxonomy' => 'bn_area', 'hide_empty' => false, 'fields' => 'ids' ) );
	if ( $existing && ! is_wp_error( $existing ) ) {
		return;
	}
	$known   = brightnest_area_seed_list();
	$popular = array( 'Dubai Marina', 'Jumeirah Lake Towers (JLT)', 'Jumeirah Village Circle (JVC)', 'Downtown Dubai', 'Business Bay', 'Al Barsha 1', 'Mirdif', 'Palm Jumeirah', 'Jumeirah 1', 'Arabian Ranches', 'Al Reem Island', 'Al Majaz' );
	$cities  = array_filter( array_map( 'trim', explode( "\n", (string) brightnest_opt( 'cities' ) ) ) );
	$ar      = array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_theme_mod( 'bn_cities_ar', '' ) ) );
	$add     = function ( $name, $parent, $order ) {
		$r = wp_insert_term( $name, 'bn_area', array( 'parent' => $parent ) );
		if ( is_wp_error( $r ) ) {
			return 0;
		}
		update_term_meta( $r['term_id'], 'bn_order', $order );
		return (int) $r['term_id'];
	};
	foreach ( array_values( $cities ? $cities : array( 'Dubai' ) ) as $ci => $city ) {
		$cid = $add( $city, 0, $ci + 1 );
		if ( ! $cid ) {
			continue;
		}
		if ( ! empty( $ar[ $ci ] ) ) {
			update_term_meta( $cid, 'bn_name_ar', $ar[ $ci ] );
		}
		if ( empty( $known[ $city ] ) ) {
			continue;
		}
		$zi = 0;
		foreach ( $known[ $city ] as $zone => $areas ) {
			// A one-zone city whose zone has the city's name: areas go straight under the city.
			$parent = ( 1 === count( $known[ $city ] ) && $zone === $city ) ? $cid : $add( $zone, $cid, ++$zi );
			foreach ( $areas as $area ) {
				$aid = $add( $area, $parent, 0 );
				if ( $aid && in_array( $area, $popular, true ) ) {
					update_term_meta( $aid, 'bn_popular', 1 );
				}
			}
		}
	}
}
add_action( 'init', 'brightnest_seed_areas', 20 );

/* Admin: order, popular and Arabic name on each term. ---------------------------------- */

function brightnest_area_fields_add() {
	?>
	<div class="form-field">
		<label for="bn-area-order"><?php esc_html_e( 'Order', 'brightnest' ); ?></label>
		<input id="bn-area-order" type="number" name="bn_order" value="0">
		<p><?php esc_html_e( 'Lower numbers come first. Areas with the same number are sorted A–Z.', 'brightnest' ); ?></p>
	</div>
	<div class="form-field"><label><input type="checkbox" name="bn_popular" value="1"> <?php esc_html_e( 'Popular area (quick pick at the top of the area picker)', 'brightnest' ); ?></label></div>
	<div class="form-field"><label for="bn-area-ar"><?php esc_html_e( 'Arabic name (العربية)', 'brightnest' ); ?></label><input id="bn-area-ar" dir="rtl" name="bn_name_ar" value=""></div>
	<?php brightnest_area_price_fields( 0, 'div' ); ?>
	<p class="description"><?php esc_html_e( 'Top level = a city. Inside a city: a zone (with areas inside it) or an area.', 'brightnest' ); ?></p>
	<?php
}
add_action( 'bn_area_add_form_fields', 'brightnest_area_fields_add' );

function brightnest_area_fields_edit( $term ) {
	?>
	<tr class="form-field">
		<th><label for="bn-area-order"><?php esc_html_e( 'Order', 'brightnest' ); ?></label></th>
		<td><input id="bn-area-order" type="number" name="bn_order" value="<?php echo esc_attr( (int) get_term_meta( $term->term_id, 'bn_order', true ) ); ?>"></td>
	</tr>
	<tr class="form-field">
		<th><?php esc_html_e( 'Popular', 'brightnest' ); ?></th>
		<td><label><input type="checkbox" name="bn_popular" value="1" <?php checked( (bool) get_term_meta( $term->term_id, 'bn_popular', true ) ); ?>> <?php esc_html_e( 'Quick pick at the top of the area picker', 'brightnest' ); ?></label></td>
	</tr>
	<tr class="form-field">
		<th><label for="bn-area-ar"><?php esc_html_e( 'Arabic name (العربية)', 'brightnest' ); ?></label></th>
		<td><input id="bn-area-ar" dir="rtl" name="bn_name_ar" value="<?php echo esc_attr( get_term_meta( $term->term_id, 'bn_name_ar', true ) ); ?>"></td>
	</tr>
	<?php brightnest_area_price_fields( $term->term_id, 'tr' ); ?>
	<?php
}

/**
 * Price fields of a city / zone / area. Empty = take the value from the zone or city above it.
 *
 * @param int    $term_id 0 on the "add" form.
 * @param string $layout  'div' (add form) or 'tr' (edit form).
 */
function brightnest_area_price_fields( $term_id, $layout ) {
	$cur    = brightnest_opt( 'currency' );
	$fields = array(
		'bn_adj' => array(
			__( 'Price change for all services (%)', 'brightnest' ),
			'<input type="number" step="0.1" min="-90" max="500" name="bn_adj" id="bn-area-adj" placeholder="0" value="' . esc_attr( $term_id ? get_term_meta( $term_id, 'bn_adj', true ) : '' ) . '"> %',
			__( 'E.g. 10 = every service is 10% dearer here, -5 = 5% cheaper. Empty = same as the zone or city above. A service that sets its own prices for this place (service → Areas & prices) keeps them.', 'brightnest' ),
		),
		'bn_fee' => array(
			__( 'Extra charge per booking', 'brightnest' ),
			esc_html( $cur ) . ' <input type="number" step="0.01" min="0" name="bn_fee" id="bn-area-fee" placeholder="0" value="' . esc_attr( $term_id ? get_term_meta( $term_id, 'bn_fee', true ) : '' ) . '">',
			__( 'Added once to every booking here (e.g. a transport charge). Customers never see it named: it is part of the subtotal and total at the checkout. Your booking email and the booking record show it. Empty = same as the zone or city above; 0 = no charge.', 'brightnest' ),
		),
	);
	foreach ( $fields as $f ) {
		if ( 'tr' === $layout ) {
			echo '<tr class="form-field"><th>' . esc_html( $f[0] ) . '</th><td>' . $f[1] . '<p class="description">' . esc_html( $f[2] ) . '</p></td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built above with escaped values.
		} else {
			echo '<div class="form-field"><label>' . esc_html( $f[0] ) . '</label>' . $f[1] . '<p>' . esc_html( $f[2] ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
}
add_action( 'bn_area_edit_form_fields', 'brightnest_area_fields_edit' );

function brightnest_area_fields_save( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) || ! isset( $_POST['bn_order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- core checks the term nonce.
		return;
	}
	update_term_meta( $term_id, 'bn_order', (int) $_POST['bn_order'] ); // phpcs:ignore WordPress.Security.NonceVerification
	update_term_meta( $term_id, 'bn_popular', empty( $_POST['bn_popular'] ) ? '' : 1 ); // phpcs:ignore WordPress.Security.NonceVerification
	update_term_meta( $term_id, 'bn_name_ar', isset( $_POST['bn_name_ar'] ) ? sanitize_text_field( wp_unslash( $_POST['bn_name_ar'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification
	// Empty stays empty ("same as above"); numbers are kept in range.
	$adj = isset( $_POST['bn_adj'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['bn_adj'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$fee = isset( $_POST['bn_fee'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['bn_fee'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	update_term_meta( $term_id, 'bn_adj', '' === $adj ? '' : (string) max( -90, min( 500, round( (float) $adj, 2 ) ) ) );
	update_term_meta( $term_id, 'bn_fee', '' === $fee ? '' : (string) max( 0, round( (float) $fee, 2 ) ) );
}
add_action( 'created_bn_area', 'brightnest_area_fields_save' );
add_action( 'edited_bn_area', 'brightnest_area_fields_save' );

/* Admin list: order, popular and Arabic columns. */
function brightnest_area_columns( $cols ) {
	unset( $cols['description'], $cols['slug'], $cols['posts'] );
	$cols['bn_order']   = __( 'Order', 'brightnest' );
	$cols['bn_popular'] = __( 'Popular', 'brightnest' );
	$cols['bn_ar']      = __( 'Arabic', 'brightnest' );
	$cols['bn_price']   = __( 'Price change / charge', 'brightnest' );
	return $cols;
}
add_filter( 'manage_edit-bn_area_columns', 'brightnest_area_columns' );

function brightnest_area_column( $out, $col, $term_id ) {
	if ( 'bn_order' === $col ) {
		return (string) (int) get_term_meta( $term_id, 'bn_order', true );
	}
	if ( 'bn_popular' === $col ) {
		return get_term_meta( $term_id, 'bn_popular', true ) ? '★' : '';
	}
	if ( 'bn_ar' === $col ) {
		return esc_html( get_term_meta( $term_id, 'bn_name_ar', true ) );
	}
	if ( 'bn_price' === $col ) {
		$adj  = get_term_meta( $term_id, 'bn_adj', true );
		$fee  = get_term_meta( $term_id, 'bn_fee', true );
		$bits = array();
		if ( '' !== (string) $adj ) {
			$bits[] = ( (float) $adj > 0 ? '+' : '' ) . (float) $adj . '%';
		}
		if ( '' !== (string) $fee ) {
			$bits[] = '+' . brightnest_opt( 'currency' ) . ' ' . (float) $fee;
		}
		return $bits ? esc_html( implode( ' · ', $bits ) ) : '<span style="color:#a7aaad">' . esc_html__( 'as above', 'brightnest' ) . '</span>';
	}
	return $out;
}
add_filter( 'manage_bn_area_custom_column', 'brightnest_area_column', 10, 3 );
