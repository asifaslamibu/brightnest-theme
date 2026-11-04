<?php
/**
 * Designed layouts for two kinds of ordinary pages, built from the text typed in the page editor:
 * - About Us: the text split at its headings into an intro with a photo and checklist, a numbers band,
 *   feature cards, customer reviews (quote blocks) and a closing call to action.
 * - Legal pages (Terms, Privacy, Refund, Cookies): a contents list beside the text, numbered sections
 *   and a help box with the contact details from Settings.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * "about", "legal" or "" for a page.
 */
function brightnest_page_kind( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'page' !== $post->post_type ) {
		return '';
	}
	$about = brightnest_core_page( 'about-us' );
	if ( $about && (int) $about->ID === (int) $post->ID ) {
		return 'about';
	}
	foreach ( array( 'terms', 'privacy-policy-bn' ) as $slug ) {
		$p = brightnest_core_page( $slug );
		if ( $p && (int) $p->ID === (int) $post->ID ) {
			return 'legal';
		}
	}
	return preg_match( '/(^|-)(terms|privacy|refund|cookie)/', $post->post_name ) ? 'legal' : '';
}

/**
 * The page text as sections: array( array( 'title' => heading text or '', 'blocks' => parsed blocks ) ).
 * Text before the first heading is the first section, with an empty title.
 */
function brightnest_page_sections( $content ) {
	$sections = array( array( 'title' => '', 'blocks' => array() ) );
	foreach ( parse_blocks( $content ) as $block ) {
		if ( empty( $block['blockName'] ) && '' === trim( (string) $block['innerHTML'] ) ) {
			continue; // Blank space between blocks.
		}
		if ( 'core/heading' === $block['blockName'] ) {
			$sections[] = array( 'title' => trim( wp_strip_all_tags( $block['innerHTML'] ) ), 'blocks' => array() );
			continue;
		}
		$sections[ count( $sections ) - 1 ]['blocks'][] = $block;
	}
	if ( ! $sections[0]['blocks'] && '' === $sections[0]['title'] ) {
		array_shift( $sections );
	}
	return $sections;
}

/** Blocks of one type out of a list. */
function brightnest_blocks_of( $blocks, $name ) {
	return array_values(
		array_filter(
			$blocks,
			function ( $b ) use ( $name ) {
				return $name === $b['blockName'];
			}
		)
	);
}

/** Blocks rendered as HTML. */
function brightnest_render_blocks( $blocks ) {
	$out = '';
	foreach ( $blocks as $b ) {
		$out .= render_block( $b );
	}
	return $out;
}

/** List items of a list block, as HTML strings. */
function brightnest_list_items( $block ) {
	preg_match_all( '#<li[^>]*>(.*?)</li>#s', render_block( $block ), $m );
	return $m[1];
}

/** A quote block as array( text, name ). */
function brightnest_quote_parts( $block ) {
	$html = render_block( $block );
	$name = preg_match( '#<cite[^>]*>(.*?)</cite>#s', $html, $m ) ? trim( wp_strip_all_tags( $m[1] ) ) : '';
	$text = preg_replace( '#<cite[^>]*>.*?</cite>#s', '', $html );
	return array( trim( wp_strip_all_tags( $text ) ), $name );
}

/**
 * Legal pages: each h2 gets an id for the contents list. Returns array( html, list of array( id, text ) ).
 */
function brightnest_legal_toc( $html ) {
	$toc  = array();
	$used = array();
	$html = preg_replace_callback(
		'#<h2([^>]*)>(.*?)</h2>#s',
		function ( $m ) use ( &$toc, &$used ) {
			$text = trim( wp_strip_all_tags( $m[2] ) );
			$id   = sanitize_title( preg_replace( '/^\d+[.)]\s*/', '', $text ) );
			$id   = $id ? $id : 'section';
			$base = $id;
			$n    = 2;
			while ( isset( $used[ $id ] ) ) {
				$id = $base . '-' . $n++;
			}
			$used[ $id ] = true;
			$toc[]       = array( $id, preg_replace( '/^\d+[.)]\s*/', '', $text ) );
			$attrs       = preg_replace( '/\sid="[^"]*"/', '', $m[1] );
			return '<h2' . $attrs . ' id="' . esc_attr( $id ) . '">' . $m[2] . '</h2>';
		},
		$html
	);
	return array( $html, $toc );
}

function brightnest_page_layout_assets() {
	if ( is_page() && brightnest_page_kind() ) {
		wp_enqueue_style( 'brightnest-pages', BRIGHTNEST_URI . '/assets/css/pages.css', array( 'brightnest-typography' ), BRIGHTNEST_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'brightnest_page_layout_assets', 20 );
