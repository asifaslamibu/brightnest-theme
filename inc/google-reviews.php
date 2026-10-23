<?php
/**
 * Google reviews (BrightNest → Settings → Homepage → Google reviews).
 *
 * Without an API key: the Google Maps embed of the business, plus "Write a review" and
 * "See all reviews on Google" buttons. With a Google Places API key (stored in Settings, never in
 * the theme): the rating, the number of reviews and up to 5 recent reviews come from Google,
 * cached for 12 hours. The old hand-typed reviews (bn_review) are no longer shown.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * The map embed address (the src of the iframe Google gives under "Share → Embed a map").
 */
function brightnest_greviews_embed() {
	$raw = trim( (string) brightnest_opt( 'greviews_embed' ) );
	if ( preg_match( '/src=["\']([^"\']+)["\']/i', $raw, $m ) ) {
		$raw = $m[1];
	}
	$raw = html_entity_decode( $raw );
	return 0 === strpos( $raw, 'https://www.google.com/maps/embed' ) ? $raw : '';
}

/**
 * Google's own number for the business (CID), read from the embed: "…!1s0x…:0x2ffb…!" → decimal.
 */
function brightnest_greviews_cid() {
	if ( preg_match( '/0x[0-9a-f]+(?::|%3A)0x([0-9a-f]+)/i', brightnest_greviews_embed(), $m ) && function_exists( 'gmp_init' ) ) {
		return gmp_strval( gmp_init( $m[1], 16 ), 10 );
	}
	if ( preg_match( '/0x[0-9a-f]+(?::|%3A)0x([0-9a-f]+)/i', brightnest_greviews_embed(), $m ) ) {
		// No GMP: build the decimal by hand (the number is too big for an integer).
		$dec = '0';
		foreach ( str_split( strtolower( $m[1] ) ) as $h ) {
			$dec = brightnest_greviews_mul_add( $dec, 16, hexdec( $h ) );
		}
		return $dec;
	}
	return '';
}

/**
 * Decimal string × small number + small number (for brightnest_greviews_cid()).
 */
function brightnest_greviews_mul_add( $dec, $mul, $add ) {
	$out   = '';
	$carry = $add;
	for ( $i = strlen( $dec ) - 1; $i >= 0; $i-- ) {
		$n     = (int) $dec[ $i ] * $mul + $carry;
		$out   = ( $n % 10 ) . $out;
		$carry = intdiv( $n, 10 );
	}
	while ( $carry > 0 ) {
		$out   = ( $carry % 10 ) . $out;
		$carry = intdiv( $carry, 10 );
	}
	return ltrim( $out, '0' ) === '' ? '0' : ltrim( $out, '0' );
}

/**
 * The business page on Google Maps (all reviews).
 */
function brightnest_greviews_maps_url() {
	$data = brightnest_greviews_data();
	if ( ! empty( $data['url'] ) ) {
		return $data['url'];
	}
	$cid = brightnest_greviews_cid();
	return $cid ? 'https://www.google.com/maps?cid=' . $cid : '';
}

/**
 * "Write a review": the link from Google Business Profile ("Ask for reviews"), else one built from the Place ID,
 * else the Maps page (it has a "Write a review" button too).
 */
function brightnest_greviews_write_url() {
	if ( brightnest_opt( 'greviews_write_url' ) ) {
		return brightnest_opt( 'greviews_write_url' );
	}
	$place = brightnest_greviews_place_id();
	return $place ? 'https://search.google.com/local/writereview?placeid=' . rawurlencode( $place ) : brightnest_greviews_maps_url();
}

/**
 * Place ID: typed in Settings, or found once with the API key from the business name.
 */
function brightnest_greviews_place_id() {
	$typed = trim( (string) get_theme_mod( 'bn_greviews_place_id', '' ) );
	return '' !== $typed ? $typed : (string) get_option( 'bn_greviews_found_place', '' );
}

/**
 * Rating, count and reviews from Google (Places API), cached for 12 hours. [] without a key or on an error.
 *
 * @return array { rating, count, url, reviews: [ { name, photo, link, rating, text, when } ] }
 */
function brightnest_greviews_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$data = array();
	$key  = trim( (string) get_theme_mod( 'bn_greviews_key', '' ) );
	if ( '' === $key ) {
		return $data;
	}
	$lang  = function_exists( 'brightnest_is_ar' ) && brightnest_is_ar() ? 'ar' : 'en';
	$cache = 'bn_greviews_' . $lang . '_' . md5( $key . brightnest_greviews_place_id() );
	$hit   = get_transient( $cache );
	if ( is_array( $hit ) ) {
		$data = $hit;
		return $data;
	}
	$head  = array( 'X-Goog-Api-Key' => $key, 'Content-Type' => 'application/json' );
	$place = brightnest_greviews_place_id();
	if ( '' === $place ) {
		// Find the business once by its name (Settings → Business name, else the site title).
		$name = brightnest_opt( 'greviews_name' ) ? brightnest_opt( 'greviews_name' ) : get_bloginfo( 'name' ) . ' Dubai';
		$res  = wp_remote_post(
			'https://places.googleapis.com/v1/places:searchText',
			array( 'timeout' => 8, 'headers' => $head + array( 'X-Goog-FieldMask' => 'places.id' ), 'body' => wp_json_encode( array( 'textQuery' => $name ) ) )
		);
		$body = is_wp_error( $res ) ? array() : json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! empty( $body['places'][0]['id'] ) ) {
			$place = $body['places'][0]['id'];
			update_option( 'bn_greviews_found_place', $place, false );
		}
	}
	if ( '' === $place ) {
		set_transient( $cache, array(), HOUR_IN_SECONDS ); // Try again in an hour.
		return $data;
	}
	$res  = wp_remote_get(
		'https://places.googleapis.com/v1/places/' . rawurlencode( $place ) . '?languageCode=' . $lang,
		array( 'timeout' => 8, 'headers' => $head + array( 'X-Goog-FieldMask' => 'rating,userRatingCount,googleMapsUri,reviews' ) )
	);
	$body = is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ? array() : json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $body ) ) {
		set_transient( $cache, array(), HOUR_IN_SECONDS );
		return $data;
	}
	$data = array(
		'rating'  => isset( $body['rating'] ) ? (float) $body['rating'] : 0,
		'count'   => isset( $body['userRatingCount'] ) ? (int) $body['userRatingCount'] : 0,
		'url'     => isset( $body['googleMapsUri'] ) ? $body['googleMapsUri'] : '',
		'reviews' => array(),
	);
	foreach ( isset( $body['reviews'] ) ? (array) $body['reviews'] : array() as $r ) {
		$text = isset( $r['text']['text'] ) ? $r['text']['text'] : ( isset( $r['originalText']['text'] ) ? $r['originalText']['text'] : '' );
		if ( '' === trim( $text ) ) {
			continue;
		}
		$data['reviews'][] = array(
			'name'   => isset( $r['authorAttribution']['displayName'] ) ? $r['authorAttribution']['displayName'] : '',
			'photo'  => isset( $r['authorAttribution']['photoUri'] ) ? $r['authorAttribution']['photoUri'] : '',
			'link'   => isset( $r['authorAttribution']['uri'] ) ? $r['authorAttribution']['uri'] : '',
			'rating' => isset( $r['rating'] ) ? (int) $r['rating'] : 5,
			'text'   => $text,
			'when'   => isset( $r['relativePublishTimeDescription'] ) ? $r['relativePublishTimeDescription'] : '',
		);
	}
	set_transient( $cache, $data, 12 * HOUR_IN_SECONDS );
	return $data;
}

/**
 * New key or Place ID saved: fetch again.
 */
function brightnest_greviews_flush( $old, $new ) {
	foreach ( array( 'bn_greviews_key', 'bn_greviews_place_id', 'bn_greviews_name' ) as $k ) {
		if ( ( isset( $old[ $k ] ) ? $old[ $k ] : '' ) !== ( isset( $new[ $k ] ) ? $new[ $k ] : '' ) ) {
			global $wpdb;
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_bn\\_greviews\\_%' OR option_name LIKE '\\_transient\\_timeout\\_bn\\_greviews\\_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			if ( 'bn_greviews_name' === $k || 'bn_greviews_key' === $k ) {
				delete_option( 'bn_greviews_found_place' );
			}
			return;
		}
	}
}
add_action( 'update_option_theme_mods_' . get_option( 'stylesheet' ), 'brightnest_greviews_flush', 10, 2 );

/**
 * The hand-typed reviews are not used any more: keep them out of the admin menu.
 */
function brightnest_greviews_hide_manual( $args, $type ) {
	if ( 'bn_review' === $type ) {
		$args['show_in_menu'] = false;
	}
	return $args;
}
add_filter( 'register_post_type_args', 'brightnest_greviews_hide_manual', 10, 2 );
