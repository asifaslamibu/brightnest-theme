<?php
/**
 * The cleaning guide: the long text that used to sit at the bottom of the homepage now has its own
 * page (page-templates/guide.php, /cleaning-services-dubai/). The homepage shows a short teaser of it
 * with a "Read the full guide" button (front-page.php).
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * The guide page ID, or 0.
 */
function brightnest_guide_id() {
	$id = (int) get_option( 'bn_guide_page' );
	return $id && 'publish' === get_post_status( $id ) ? $id : 0;
}

/**
 * The guide text with an id on every H2 and H3, and the list of them: [ html, [ [ id, text, level ], … ] ].
 * Numbered points ("1. …") get an id but stay out of the list.
 *
 * @param string $html Page content (already through the_content).
 */
function brightnest_guide_sections( $html ) {
	$list = array();
	$used = array();
	$html = preg_replace_callback(
		'#<h([23])([^>]*)>(.*?)</h\1>#is',
		function ( $m ) use ( &$list, &$used ) {
			$level = (int) $m[1];
			$attrs = $m[2];
			$text  = trim( wp_strip_all_tags( $m[3] ) );
			if ( preg_match( '/\sid=["\']([^"\']+)["\']/i', $attrs, $idm ) ) {
				$id = $idm[1];
			} else {
				$id   = sanitize_title( $text );
				$id   = $id ? $id : 'section';
				$n    = 2;
				$base = $id;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;
			if ( ! preg_match( '/^\d+\./', $text ) ) {
				$list[] = array( $id, $text, $level );
			}
			return '<h' . $level . $attrs . '>' . $m[3] . '</h' . $level . '>';
		},
		$html
	);
	return array( $html, $list );
}

/**
 * Short intro of the guide for the homepage: its first two paragraphs.
 *
 * @return string[] Paragraphs (plain text).
 */
function brightnest_guide_intro( $id ) {
	$content = (string) get_post_field( 'post_content', $id );
	if ( function_exists( 'brightnest_show_ar' ) && brightnest_show_ar() && get_post_meta( $id, '_bn_ar_content', true ) ) {
		$content = (string) get_post_meta( $id, '_bn_ar_content', true );
	}
	preg_match_all( '#<p[^>]*>(.*?)</p>#is', $content, $m );
	$out = array();
	foreach ( $m[1] as $p ) {
		$p = trim( wp_strip_all_tags( $p ) );
		if ( strlen( $p ) > 80 ) {
			$out[] = $p;
		}
		if ( count( $out ) >= 2 ) {
			break;
		}
	}
	return $out;
}

/**
 * One time: make the guide page from the homepage's long text, and take that text off the homepage.
 */
function brightnest_guide_seed() {
	if ( get_option( 'bn_guide_page' ) ) {
		return;
	}
	$front = (int) get_option( 'page_on_front' );
	$text  = $front ? trim( (string) get_post_field( 'post_content', $front ) ) : '';
	if ( '' === $text && function_exists( 'brightnest_home_article' ) ) {
		$text = brightnest_home_article();
	}
	// The first H2 is the page's own heading.
	$title = 'Professional Cleaning Services in Dubai';
	$text  = preg_replace( '#^\s*<h2[^>]*>\s*' . preg_quote( $title, '#' ) . '\s*</h2>\s*#i', '', $text );
	$id    = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => 'cleaning-services-dubai',
			'post_content' => $text,
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}
	update_option( 'bn_guide_page', $id );
	update_post_meta( $id, '_wp_page_template', 'page-templates/guide.php' );
	update_post_meta( $id, '_bn_seo_title', 'Professional Cleaning Services in Dubai: Complete Guide | Bright Nest Cleaning Co.' );
	update_post_meta( $id, '_bn_seo_desc', 'Why choose a professional cleaning company in Dubai, what cleaning and maid services cost, and how to book. A clear guide from Bright Nest Cleaning Co.' );
	update_post_meta( $id, '_bn_ar_title', 'خدمات التنظيف الاحترافية في دبي' );
	if ( $front ) {
		wp_update_post( array( 'ID' => $front, 'post_content' => '' ) ); // The homepage shows the teaser now.
	}
}
add_action( 'init', 'brightnest_guide_seed', 32 ); // After brightnest_home_seed() put the text on the homepage.
