<?php
/**
 * Page cache (LiteSpeed Cache plugin / Hostinger LiteSpeed server).
 *
 * Prices and texts of a service show on many pages (checkout, services page, homepage, menus), but the
 * cache only clears the service's own page when it is saved. So every change an admin makes to services,
 * categories, areas, reviews or BrightNest settings clears the whole page cache.
 * The checkout is never cached: it holds the visitor's security code (nonce) and live prices.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clear every cached page (no-op without a cache plugin).
 */
function brightnest_purge_cache() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	do_action( 'litespeed_purge_all' ); // LiteSpeed Cache.
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush(); // Object cache, if any.
	}
}

function brightnest_purge_on_save( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( in_array( $post->post_type, array( 'bn_service', 'bn_review', 'page', 'post' ), true ) ) {
		brightnest_purge_cache();
	}
}
add_action( 'save_post', 'brightnest_purge_on_save', 99, 2 );
add_action( 'trashed_post', 'brightnest_purge_cache' );
add_action( 'deleted_post', 'brightnest_purge_cache' );

function brightnest_purge_on_term( $term_id, $tt_id, $taxonomy ) {
	if ( in_array( $taxonomy, array( 'bn_service_cat', 'bn_area' ), true ) ) {
		brightnest_purge_cache();
	}
}
add_action( 'created_term', 'brightnest_purge_on_term', 10, 3 );
add_action( 'edited_term', 'brightnest_purge_on_term', 10, 3 );
add_action( 'delete_term', 'brightnest_purge_on_term', 10, 3 );

// BrightNest → Settings and the Customizer save into the theme's options.
add_action( 'update_option_theme_mods_' . get_option( 'stylesheet' ), 'brightnest_purge_cache' );

/**
 * The checkout is never stored in the page cache.
 */
function brightnest_checkout_nocache() {
	if ( function_exists( 'brightnest_is_checkout' ) && brightnest_is_checkout() ) {
		do_action( 'litespeed_control_set_nocache', 'BrightNest checkout: live prices and security code' );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'brightnest_checkout_nocache', 0 );
