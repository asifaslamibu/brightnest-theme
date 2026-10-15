<?php
/**
 * Template helpers.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cities list from customizer.
 *
 * @return string[]
 */
function brightnest_cities() {
	$list = array_filter( array_map( 'trim', explode( "\n", (string) brightnest_opt( 'cities' ) ) ) );
	return $list ? array_values( $list ) : array( __( 'Dubai', 'brightnest' ) );
}

/**
 * Icon key for a service (own icon > first category icon > default).
 */
function brightnest_service_icon( $post_id ) {
	$icon = get_post_meta( $post_id, '_bn_icon', true );
	if ( $icon ) {
		return $icon;
	}
	$terms = get_the_terms( $post_id, 'bn_service_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$cat_icon = get_term_meta( $terms[0]->term_id, 'bn_icon', true );
		if ( $cat_icon ) {
			return $cat_icon;
		}
	}
	return 'cleaning';
}

/**
 * Formatted "From AED 99 / hour" string.
 */
function brightnest_price( $post_id ) {
	$price = get_post_meta( $post_id, '_bn_price', true );
	if ( '' === $price ) {
		return '';
	}
	$unit   = get_post_meta( $post_id, '_bn_price_unit', true );
	$amount = floor( (float) $price ) == $price ? number_format_i18n( $price ) : number_format_i18n( $price, 2 );
	$html   = '<span class="bn-price"><small>' . esc_html__( 'From', 'brightnest' ) . '</small> <strong>' . esc_html( brightnest_opt( 'currency' ) . ' ' . $amount ) . '</strong>';
	if ( $unit ) {
		$html .= ' <small>' . esc_html( $unit ) . '</small>';
	}
	return $html . '</span>';
}

/**
 * Star rating markup.
 */
function brightnest_stars( $rating, $count = '' ) {
	if ( '' === $rating || null === $rating ) {
		return '';
	}
	$rating = (float) $rating;
	$out    = '<span class="bn-rating" aria-label="' . esc_attr( sprintf( /* translators: %s rating */ __( 'Rated %s out of 5', 'brightnest' ), $rating ) ) . '">';
	$out   .= brightnest_icon( 'star', 15 ) . '<strong>' . esc_html( number_format_i18n( $rating, 1 ) ) . '</strong>';
	if ( '' !== $count && null !== $count ) {
		$out .= ' <span class="bn-muted">(' . esc_html( number_format_i18n( (int) $count ) ) . ')</span>';
	}
	return $out . '</span>';
}

/**
 * Deterministic soft gradient per term/post for image-less cards.
 */
function brightnest_tone( $seed ) {
	$tones = array( 'blue', 'teal', 'amber', 'violet', 'rose', 'green' );
	return $tones[ abs( crc32( (string) $seed ) ) % count( $tones ) ];
}

/**
 * Service card.
 */
function brightnest_service_card( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	get_template_part( 'template-parts/card', 'service', array( 'id' => $post_id ) );
}

/**
 * WhatsApp link.
 */
function brightnest_whatsapp_url( $text = '' ) {
	$num = preg_replace( '/\D/', '', (string) brightnest_opt( 'whatsapp' ) );
	if ( ! $num ) {
		return '';
	}
	return 'https://wa.me/' . $num . ( $text ? '?text=' . rawurlencode( $text ) : '' );
}

function brightnest_tel_url() {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', (string) brightnest_opt( 'phone' ) );
}

/**
 * Service categories ordered by our seeded order, then name.
 */
function brightnest_categories( $args = array() ) {
	$terms = get_terms(
		wp_parse_args(
			$args,
			array(
				'taxonomy'   => 'bn_service_cat',
				'hide_empty' => false,
				'parent'     => 0,
				'meta_key'   => 'bn_order', // phpcs:ignore WordPress.DB.SlowDBQuery
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
			)
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		// Fallback for categories without bn_order meta.
		$terms = get_terms( array( 'taxonomy' => 'bn_service_cat', 'hide_empty' => false, 'parent' => 0 ) );
	}
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Primary menu fallback: categories + core pages.
 */
function brightnest_menu_fallback() {
	echo '<ul class="bn-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'brightnest' ) . '</a></li>';
	echo '<li class="menu-item-has-children"><a href="' . esc_url( get_post_type_archive_link( 'bn_service' ) ) . '">' . esc_html__( 'Services', 'brightnest' ) . '</a><ul class="sub-menu">';
	foreach ( brightnest_categories() as $term ) {
		echo '<li><a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a></li>';
	}
	echo '</ul></li>';
	foreach ( array( 'about-us', 'faq', 'contact' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			echo '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a></li>';
		}
	}
	echo '</ul>';
}

/**
 * Breadcrumbs.
 */
function brightnest_breadcrumbs() {
	echo '<nav class="bn-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'brightnest' ) . '"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'brightnest' ) . '</a>';
	if ( is_singular( 'bn_service' ) ) {
		echo '<span>/</span><a href="' . esc_url( get_post_type_archive_link( 'bn_service' ) ) . '">' . esc_html__( 'Services', 'brightnest' ) . '</a>';
		$terms = get_the_terms( get_the_ID(), 'bn_service_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			echo '<span>/</span><a href="' . esc_url( get_term_link( $terms[0] ) ) . '">' . esc_html( $terms[0]->name ) . '</a>';
		}
		echo '<span>/</span><span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_tax( 'bn_service_cat' ) ) {
		echo '<span>/</span><a href="' . esc_url( get_post_type_archive_link( 'bn_service' ) ) . '">' . esc_html__( 'Services', 'brightnest' ) . '</a><span>/</span><span aria-current="page">' . esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_post_type_archive( 'bn_service' ) ) {
		echo '<span>/</span><span aria-current="page">' . esc_html__( 'Services', 'brightnest' ) . '</span>';
	} elseif ( is_singular() ) {
		echo '<span>/</span><span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	}
	echo '</nav>';
}

/**
 * Social links.
 */
function brightnest_social_links() {
	$out = '';
	foreach ( array( 'facebook', 'instagram', 'twitter', 'linkedin' ) as $net ) {
		$url = brightnest_opt( $net );
		if ( $url ) {
			$out .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( ucfirst( $net ) ) . '">' . brightnest_icon( $net, 18 ) . '</a>';
		}
	}
	return $out;
}
