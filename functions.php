<?php
/**
 * BrightNest theme functions.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

define( 'BRIGHTNEST_VERSION', '3.9.1' );
define( 'BRIGHTNEST_DIR', get_template_directory() );
define( 'BRIGHTNEST_URI', get_template_directory_uri() );

require BRIGHTNEST_DIR . '/inc/icons.php';
require BRIGHTNEST_DIR . '/inc/post-types.php';
require BRIGHTNEST_DIR . '/inc/meta-boxes.php';
require BRIGHTNEST_DIR . '/inc/customizer.php';
require BRIGHTNEST_DIR . '/inc/forms.php';
require BRIGHTNEST_DIR . '/inc/checkout.php';
require BRIGHTNEST_DIR . '/inc/payments.php';
require BRIGHTNEST_DIR . '/inc/settings.php';
if ( is_admin() ) {
	require BRIGHTNEST_DIR . '/inc/admin.php';
}
require BRIGHTNEST_DIR . '/inc/helpers.php';
require BRIGHTNEST_DIR . '/inc/demo-content.php';

/**
 * Theme setup.
 */
function brightnest_setup() {
	load_theme_textdomain( 'brightnest', BRIGHTNEST_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 220,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'brightnest-card', 640, 420, true );
	add_image_size( 'brightnest-hero', 1400, 900, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'brightnest' ),
			'footer'  => __( 'Footer Menu', 'brightnest' ),
		)
	);
}
add_action( 'after_setup_theme', 'brightnest_setup' );

/**
 * Scripts and styles.
 */
function brightnest_assets() {
	wp_enqueue_style( 'brightnest-fonts', 'https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;700&display=swap', array(), null );
	wp_enqueue_style( 'brightnest-main', BRIGHTNEST_URI . '/assets/css/main.css', array(), BRIGHTNEST_VERSION );
	wp_enqueue_style( 'brightnest-home', BRIGHTNEST_URI . '/assets/css/home.css', array( 'brightnest-main' ), BRIGHTNEST_VERSION );
	wp_enqueue_style( 'brightnest-components', BRIGHTNEST_URI . '/assets/css/components.css', array( 'brightnest-home' ), BRIGHTNEST_VERSION );
	if ( is_singular( 'bn_service' ) || is_post_type_archive( 'bn_service' ) || is_tax( 'bn_service_cat' ) || ( is_search() && 'bn_service' === get_query_var( 'post_type' ) ) ) {
		wp_enqueue_style( 'brightnest-service', BRIGHTNEST_URI . '/assets/css/service.css', array( 'brightnest-components' ), BRIGHTNEST_VERSION );
	}
	if ( is_front_page() ) {
		wp_enqueue_style( 'brightnest-front', BRIGHTNEST_URI . '/assets/css/front.css', array( 'brightnest-home' ), BRIGHTNEST_VERSION );
		wp_enqueue_style( 'brightnest-home-jl', BRIGHTNEST_URI . '/assets/css/home-jl.css', array( 'brightnest-front' ), BRIGHTNEST_VERSION );
		wp_enqueue_style( 'brightnest-home-jx', BRIGHTNEST_URI . '/assets/css/home-jx.css', array( 'brightnest-home-jl' ), BRIGHTNEST_VERSION );

	}
	$bn_hub_page = is_post_type_archive( 'bn_service' ) || is_tax( 'bn_service_cat' ) || ( is_search() && 'bn_service' === get_query_var( 'post_type' ) );
	if ( is_front_page() || $bn_hub_page || ( function_exists( 'brightnest_is_checkout' ) && brightnest_is_checkout() ) ) {
		// "Set my location" map picker (homepage, services hub, checkout).
		wp_enqueue_style( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css', array(), '1.9.4' );
		wp_enqueue_style( 'brightnest-location', BRIGHTNEST_URI . '/assets/css/location.css', array( 'brightnest-main' ), BRIGHTNEST_VERSION );
		wp_enqueue_script( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js', array(), '1.9.4', true );
		wp_enqueue_script( 'brightnest-location', BRIGHTNEST_URI . '/assets/js/location.js', array( 'leaflet' ), BRIGHTNEST_VERSION, true );
		wp_localize_script(
			'brightnest-location',
			'BN_LOC',
			array(
				'lat'    => (float) brightnest_opt( 'map_lat' ),
				'lng'    => (float) brightnest_opt( 'map_lng' ),
				'cities' => brightnest_cities(),
				'country' => strtolower( substr( preg_replace( '/[^A-Za-z]/', '', (string) brightnest_opt( 'country_code' ) ), 0, 2 ) ),
			)
		);
		if ( $bn_hub_page ) {
			wp_enqueue_style( 'brightnest-hub', BRIGHTNEST_URI . '/assets/css/hub.css', array( 'brightnest-components' ), BRIGHTNEST_VERSION );
		}
		wp_enqueue_script( 'brightnest-hub', BRIGHTNEST_URI . '/assets/js/hub.js', array( 'brightnest-location' ), BRIGHTNEST_VERSION, true );
		wp_localize_script(
			'brightnest-hub',
			'BN_HUB',
			array(
				'hub'   => get_post_type_archive_link( 'bn_service' ),
				'front' => is_front_page(),
			)
		);
	}
	wp_enqueue_script( 'brightnest-main', BRIGHTNEST_URI . '/assets/js/main.js', array(), BRIGHTNEST_VERSION, true );

	if ( is_singular( 'post' ) && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'brightnest_assets' );

/**
 * Preconnect to Google Fonts.
 */
/**
 * Justlife-style typography, loaded after every other stylesheet so it wins everywhere.
 */
function brightnest_typography_css() {
	wp_enqueue_style( 'brightnest-typography', BRIGHTNEST_URI . '/assets/css/typography.css', array( 'brightnest-main' ), BRIGHTNEST_VERSION );
}
add_action( 'wp_enqueue_scripts', 'brightnest_typography_css', 99 );

function brightnest_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'brightnest_resource_hints', 10, 2 );

/**
 * Widgets.
 */
function brightnest_widgets() {
	register_sidebar(
		array(
			'name'          => __( 'Blog Sidebar', 'brightnest' ),
			'id'            => 'sidebar-1',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'brightnest_widgets' );

/**
 * Show 12 services per archive page.
 */
function brightnest_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'bn_service' ) || $query->is_tax( 'bn_service_cat' ) || ( $query->is_search() && 'bn_service' === $query->get( 'post_type' ) ) ) {
		$query->set( 'posts_per_page', 24 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
}
add_action( 'pre_get_posts', 'brightnest_archive_query' );

/**
 * Favicon from the logo emblem, unless a Site Icon is set in the Customizer.
 */
function brightnest_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	$icon = esc_url( BRIGHTNEST_URI . '/assets/img/icon.png' );
	echo '<link rel="icon" type="image/png" sizes="512x512" href="' . $icon . '">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<link rel="apple-touch-icon" href="' . $icon . '">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}
add_action( 'wp_head', 'brightnest_favicon' );
add_action( 'admin_head', 'brightnest_favicon' );
add_action( 'login_head', 'brightnest_favicon' );

/**
 * Brand the WordPress login screen.
 */
function brightnest_login_style() {
	?>
	<style>
		body.login { background: #f4f5fb; }
		#login h1 a { background: url('<?php echo esc_url( BRIGHTNEST_URI . '/assets/img/logo.png' ); ?>') center / contain no-repeat; width: 300px; height: 84px; }
		body.login.wp-core-ui .button-primary { background: #cf4a00; border-color: #cf4a00; color: #fff; }
		body.login.wp-core-ui .button-primary:hover, body.login.wp-core-ui .button-primary:focus { background: #b33f00; border-color: #b33f00; }
		body.login .button.wp-hide-pw .dashicons { color: #262d72; }
		.login #backtoblog a, .login #nav a { color: #262d72; }
		.login input:focus { border-color: #262d72; box-shadow: 0 0 0 1px #262d72; }
	</style>
	<?php
}
add_action( 'login_enqueue_scripts', 'brightnest_login_style' );
add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return get_bloginfo( 'name' ); } );

/**
 * Basic meta description + Open Graph tags. Skipped when an SEO plugin is active.
 */
function brightnest_meta_tags() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'AIOSEO\\Plugin\\AIOSEO' ) || defined( 'SEOPRESS_VERSION' ) ) {
		return;
	}
	$title = wp_get_document_title();
	$desc  = '';
	$image = BRIGHTNEST_URI . '/assets/img/logo.png';
	if ( is_front_page() ) {
		$desc  = brightnest_opt( 'hero_subtitle' );
		$image = brightnest_img_opt( 'hero_img_1', 'about-1.jpg' );
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( preg_replace( '#<p class="bn-legal-note">.*?</p>#s', '', strip_shortcodes( $post->post_content ) ) ), 28, '…' );
		if ( has_post_thumbnail( $post ) ) {
			$image = get_the_post_thumbnail_url( $post, 'large' );
		}
	} elseif ( is_tax( 'bn_service_cat' ) ) {
		$desc = term_description();
	} elseif ( is_post_type_archive( 'bn_service' ) ) {
		$desc = __( 'Cleaning, maintenance and home services in the UAE. Compare prices and book online.', 'brightnest' );
	}
	$desc = trim( wp_strip_all_tags( (string) $desc ) );
	if ( '' === $desc ) {
		$desc = get_bloginfo( 'description' );
	}
	$url = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( wp_html_excerpt( $desc, 160, '…' ) ) . '">' . "\n";
	}
	echo '<meta property="og:type" content="' . ( is_singular( array( 'post' ) ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $desc ) {
		echo '<meta property="og:description" content="' . esc_attr( wp_html_excerpt( $desc, 200, '…' ) ) . '">' . "\n";
	}
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action( 'wp_head', 'brightnest_meta_tags', 2 );

/**
 * Service searches (?s=…&post_type=bn_service) use the services listing template.
 */
function brightnest_service_search_template( $template ) {
	if ( is_search() && 'bn_service' === get_query_var( 'post_type' ) ) {
		$found = locate_template( 'archive-bn_service.php' );
		if ( $found ) {
			return $found;
		}
	}
	return $template;
}
add_filter( 'template_include', 'brightnest_service_search_template' );

function brightnest_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'brightnest_excerpt_length' );

function brightnest_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'brightnest_excerpt_more' );
