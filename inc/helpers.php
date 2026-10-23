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
	// Services → Areas (top level = cities); before any area exists, the old Settings list.
	$tree = function_exists( 'brightnest_area_tree' ) ? brightnest_area_tree() : array();
	if ( $tree ) {
		return array_keys( $tree );
	}
	$list = array_filter( array_map( 'trim', explode( "\n", (string) brightnest_opt( 'cities' ) ) ) );
	return $list ? array_values( $list ) : array( __( 'Dubai', 'brightnest' ) );
}

/**
 * Zones and areas of each served city (area dropdown, area charges in Settings → Booking):
 * [ city => [ zone => [ area, … ] ] ], from Services → Areas (see inc/areas.php).
 * Every area name is unique inside its city (charges are saved as "city|area").
 *
 * @return array
 */
function brightnest_area_zones() {
	$out = array();
	foreach ( brightnest_area_tree() as $city => $c ) {
		foreach ( $c['zones'] as $zone => $z ) {
			$out[ $city ][ $zone ] = array_column( $z['areas'], 'name' );
		}
	}
	if ( ! $out ) {
		foreach ( brightnest_cities() as $city ) {
			$out[ $city ] = array( $city => array( $city ) );
		}
	}
	return $out;
}

/**
 * Areas of each served city, without the zones: [ city => [ area, … ] ].
 *
 * @return array
 */
function brightnest_city_areas() {
	$out = array();
	foreach ( brightnest_area_zones() as $city => $zones ) {
		$out[ $city ] = array_values( array_unique( array_merge( ...array_values( $zones ) ) ) );
	}
	return $out;
}

/**
 * Service page text from Settings → Service page. While it is still the default, the theme's own
 * translated text is used (so the Arabic site stays Arabic without filling in the Arabic field).
 *
 * @param string $key Setting key, e.g. sv_prices_title.
 */
function brightnest_sv_text( $key ) {
	$value    = (string) brightnest_opt( $key );
	$defaults = brightnest_defaults();
	if ( isset( $defaults[ $key ] ) && $value === $defaults[ $key ] ) {
		if ( 'sv_about_title' === $key ) {
			return str_replace( '%s', '{service}', __( 'About our %s service', 'brightnest' ) ); // Existing translation.
		}
		return translate( $value, 'brightnest' ); // phpcs:ignore WordPress.WP.I18n
	}
	return $value;
}

/**
 * "5 min read" for a blog post (about 220 words a minute).
 *
 * @param int $post_id Post ID.
 */
function brightnest_reading_time( $post_id ) {
	$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
	$mins  = max( 1, (int) round( $words / 220 ) );
	/* translators: %d minutes */
	return sprintf( _n( '%d min read', '%d min read', $mins, 'brightnest' ), $mins );
}

/**
 * Heading of a blog archive: category or tag name, "Articles from September 2026", "Articles by Sara".
 */
function brightnest_blog_archive_title() {
	if ( is_category() || is_tag() ) {
		return single_term_title( '', false );
	}
	if ( is_year() ) {
		/* translators: %s year */
		return sprintf( __( 'Articles from %s', 'brightnest' ), get_the_date( 'Y' ) );
	}
	if ( is_month() || is_day() ) {
		/* translators: %s month and year */
		return sprintf( __( 'Articles from %s', 'brightnest' ), get_the_date( is_day() ? 'j F Y' : 'F Y' ) );
	}
	if ( is_author() ) {
		/* translators: %s author name */
		return sprintf( __( 'Articles by %s', 'brightnest' ), get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) ) );
	}
	return '';
}

/**
 * The blog archive page (every article by year and month), or '' when there is none.
 */
function brightnest_blog_archive_url() {
	$id = (int) get_option( 'bn_blog_archive_page' );
	return $id && 'publish' === get_post_status( $id ) ? get_permalink( $id ) : '';
}

/**
 * One time: make the "Blog archive" page under the blog page (/blogs/archive/).
 */
function brightnest_blog_archive_seed() {
	if ( get_option( 'bn_blog_archive_page' ) || ! get_option( 'page_for_posts' ) || ! current_user_can( 'publish_pages' ) ) {
		return;
	}
	$id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'Blog Archive',
			'post_name'   => 'archive',
			'post_parent' => (int) get_option( 'page_for_posts' ),
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', 'page-templates/blog-archive.php' );
		update_post_meta( $id, '_bn_ar_title', 'أرشيف المدونة' );
		update_option( 'bn_blog_archive_page', $id );
	}
}
add_action( 'admin_init', 'brightnest_blog_archive_seed' );

/**
 * Uploaded icon picture (same box as a line icon).
 */
function brightnest_icon_img( $url, $size ) {
	return sprintf( '<img class="bn-icon bn-icon-img" src="%1$s" width="%2$d" height="%2$d" alt="" loading="lazy" decoding="async">', esc_url( $url ), absint( $size ) );
}

/**
 * Picture of a service as an icon: its own uploaded image, else its category's, else the line icon.
 *
 * @param int $post_id Service ID.
 * @param int $size    Pixels.
 */
function brightnest_service_icon_html( $post_id, $size = 24 ) {
	$img = (string) get_post_meta( $post_id, '_bn_icon_img', true );
	if ( '' === $img && '' === (string) get_post_meta( $post_id, '_bn_icon', true ) ) {
		$terms = get_the_terms( $post_id, 'bn_service_cat' );
		$img   = $terms && ! is_wp_error( $terms ) ? (string) get_term_meta( $terms[0]->term_id, 'bn_icon_img', true ) : '';
	}
	return '' !== $img ? brightnest_icon_img( $img, $size ) : brightnest_icon( brightnest_service_icon( $post_id ), $size );
}

/**
 * Picture of a category as an icon: uploaded image, else its line icon.
 *
 * @param int $term_id Category ID.
 * @param int $size    Pixels.
 */
function brightnest_cat_icon_html( $term_id, $size = 24 ) {
	$img = (string) get_term_meta( $term_id, 'bn_icon_img', true );
	if ( '' !== $img ) {
		return brightnest_icon_img( $img, $size );
	}
	$icon = get_term_meta( $term_id, 'bn_icon', true );
	return brightnest_icon( $icon ? $icon : 'home', $size );
}

/**
 * Admin: photo picker box (uses assets/admin/settings.js).
 */
function brightnest_admin_image_field( $name, $url, $empty ) {
	echo '<div class="bn-set-imgbox" data-image>';
	echo '<span class="bn-set-thumb" data-thumb data-empty="' . esc_attr( $empty ) . '">' . ( $url ? '<img src="' . esc_url( $url ) . '" alt="">' : esc_html( $empty ) ) . '</span>';
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $url ) . '" data-url>';
	echo '<button type="button" class="button" data-pick>' . esc_html__( 'Upload / choose image', 'brightnest' ) . '</button> ';
	echo '<button type="button" class="button-link bn-opt-del" data-clear>' . esc_html__( 'Remove', 'brightnest' ) . '</button>';
	echo '</div>';
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
 * "Starting from" price of a service, always from its real prices so every page agrees:
 * hourly services → the rate per professional per hour; option lists → the cheapest option;
 * "single price" services → the starting price typed in Basics.
 *
 * @param int $post_id Service ID.
 * @return array [ amount (float|''), unit (string) ]
 */
function brightnest_service_from( $post_id ) {
	$unit   = (string) get_post_meta( $post_id, '_bn_price_unit', true );
	$hourly = brightnest_hourly_config( $post_id );
	if ( $hourly && $hourly['rate'] > 0 ) {
		return array( (float) $hourly['rate'], '' !== $unit ? $unit : __( 'per hour', 'brightnest' ) );
	}
	if ( 'single' !== get_post_meta( $post_id, '_bn_mode', true ) && '' !== trim( (string) get_post_meta( $post_id, '_bn_options', true ) ) ) {
		$prices = array_filter( array_map( 'floatval', wp_list_pluck( brightnest_service_items( $post_id ), 'price' ) ) );
		if ( $prices ) {
			return array( min( $prices ), $unit );
		}
	}
	$price = get_post_meta( $post_id, '_bn_price', true );
	return array( '' === $price ? '' : (float) $price, $unit );
}

/**
 * Formatted "From AED 99 / hour" string.
 */
function brightnest_price( $post_id ) {
	list( $price, $unit ) = brightnest_service_from( $post_id );
	if ( '' === $price ) {
		return '';
	}
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
	foreach ( array( 'about-us', 'blogs', 'faq', 'contact' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			continue;
		}
		if ( 'blogs' === $slug && brightnest_blog_archive_url() ) {
			// Blogs, with the archive (every article by month) under it.
			echo '<li class="menu-item-has-children"><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a><ul class="sub-menu">';
			echo '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html__( 'Latest articles', 'brightnest' ) . '</a></li>';
			echo '<li><a href="' . esc_url( brightnest_blog_archive_url() ) . '">' . esc_html( get_the_title( (int) get_option( 'bn_blog_archive_page' ) ) ) . '</a></li>';
			echo '</ul></li>';
			continue;
		}
		echo '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a></li>';
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
	} elseif ( get_option( 'page_for_posts' ) && ( is_singular( 'post' ) || ( is_page() && (int) wp_get_post_parent_id( get_queried_object_id() ) === (int) get_option( 'page_for_posts' ) ) ) ) {
		// Blog post, or a page under the blog (the archive): Home / Blogs / title.
		$blog = (int) get_option( 'page_for_posts' );
		echo '<span>/</span><a href="' . esc_url( get_permalink( $blog ) ) . '">' . esc_html( get_the_title( $blog ) ) . '</a><span>/</span><span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( ( is_category() || is_tag() || is_date() || is_author() ) && get_option( 'page_for_posts' ) ) {
		// Blog archives: Home / Blogs / category, month or author.
		$blog = (int) get_option( 'page_for_posts' );
		echo '<span>/</span><a href="' . esc_url( get_permalink( $blog ) ) . '">' . esc_html( get_the_title( $blog ) ) . '</a><span>/</span><span aria-current="page">' . esc_html( brightnest_blog_archive_title() ) . '</span>';
	} elseif ( is_singular() ) {
		echo '<span>/</span><span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
		echo '<span>/</span><span aria-current="page">' . esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ) . '</span>';
	}
	echo '</nav>';
}

/**
 * Achievements band rows: [ number, label ].
 */
function brightnest_achievements() {
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) brightnest_opt( 'achievements' ) ) as $line ) {
		$p = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' !== $p[0] && isset( $p[1] ) ) {
			$rows[] = $p;
		}
	}
	return $rows;
}

/**
 * Social links.
 */
function brightnest_social_links() {
	$out = '';
	foreach ( array( 'facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok' ) as $net ) {
		$url = brightnest_opt( $net );
		if ( $url ) {
			$out .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( ucfirst( $net ) ) . '">' . brightnest_icon( $net, 18 ) . '</a>';
		}
	}
	return $out;
}
