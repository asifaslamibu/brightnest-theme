<?php
/**
 * BrightNest theme functions.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

define( 'BRIGHTNEST_VERSION', '1.1.0' );
define( 'BRIGHTNEST_DIR', get_template_directory() );
define( 'BRIGHTNEST_URI', get_template_directory_uri() );

require BRIGHTNEST_DIR . '/inc/icons.php';
require BRIGHTNEST_DIR . '/inc/post-types.php';
require BRIGHTNEST_DIR . '/inc/meta-boxes.php';
require BRIGHTNEST_DIR . '/inc/customizer.php';
require BRIGHTNEST_DIR . '/inc/forms.php';
require BRIGHTNEST_DIR . '/inc/checkout.php';
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
	wp_enqueue_style( 'brightnest-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'brightnest-main', BRIGHTNEST_URI . '/assets/css/main.css', array(), BRIGHTNEST_VERSION );
	wp_enqueue_script( 'brightnest-main', BRIGHTNEST_URI . '/assets/js/main.js', array(), BRIGHTNEST_VERSION, true );

	if ( is_singular( 'post' ) && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'brightnest_assets' );

/**
 * Preconnect to Google Fonts.
 */
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
	if ( $query->is_post_type_archive( 'bn_service' ) || $query->is_tax( 'bn_service_cat' ) ) {
		$query->set( 'posts_per_page', 12 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
}
add_action( 'pre_get_posts', 'brightnest_archive_query' );

function brightnest_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'brightnest_excerpt_length' );

function brightnest_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'brightnest_excerpt_more' );
