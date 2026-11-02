<?php
/**
 * Service pages (bn_info): content pages about a service — text, photo, FAQ, Google (SEO) — with no
 * booking of their own. A page can name one bookable service ("Booking service"): its Book button
 * then opens that service's checkout; without one it shows "Get a quote" (WhatsApp / call).
 * Bookable services stay in "Services" (bn_service). Both share the /services/name/ address.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

function brightnest_register_service_pages() {
	register_post_type(
		'bn_info',
		array(
			'labels'       => array(
				'name'          => __( 'Service Pages', 'brightnest' ),
				'singular_name' => __( 'Service Page', 'brightnest' ),
				'add_new_item'  => __( 'Add New Service Page', 'brightnest' ),
				'edit_item'     => __( 'Edit Service Page', 'brightnest' ),
				'all_items'     => __( 'Service Pages', 'brightnest' ),
				'menu_name'     => __( 'Service Pages', 'brightnest' ),
			),
			'public'       => true,
			'has_archive'  => 'our-services', // The page listing them all (archive-bn_info.php).
			'rewrite'      => array( 'slug' => 'services', 'with_front' => false ),
			'show_in_menu' => 'brightnest',
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'brightnest_register_service_pages', 11 ); // After Services, so /service/name/ tries a service first.

/**
 * /services/name/ is a bookable service or a service page: when no service has that name, look for a page.
 */
function brightnest_service_pages_request( $vars ) {
	if ( is_admin() ) {
		return $vars;
	}
	// Both types share the same address rule, so it may name either one: use the one that has that name.
	foreach ( array( 'bn_service' => 'bn_info', 'bn_info' => 'bn_service' ) as $from => $to ) {
		if ( empty( $vars[ $from ] ) || ! is_string( $vars[ $from ] ) ) {
			continue;
		}
		$name = $vars[ $from ];
		if ( get_page_by_path( $name, OBJECT, $from ) || ! get_page_by_path( $name, OBJECT, $to ) ) {
			return $vars;
		}
		unset( $vars[ $from ] );
		$vars[ $to ]       = $name;
		$vars['post_type'] = $to;
		$vars['name']      = $name;
		return $vars;
	}
	return $vars;
}
add_filter( 'request', 'brightnest_service_pages_request' );

/**
 * A service and a service page never get the same address name.
 */
function brightnest_service_pages_unique_slug( $slug, $post_id, $status, $type ) {
	if ( ! in_array( $type, array( 'bn_service', 'bn_info' ), true ) ) {
		return $slug;
	}
	$other = 'bn_service' === $type ? 'bn_info' : 'bn_service';
	$base  = $slug;
	$n     = 2;
	while ( ( $p = get_page_by_path( $slug, OBJECT, $other ) ) && (int) $p->ID !== (int) $post_id ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
		$slug = $base . '-' . $n++;
	}
	return $slug;
}
add_filter( 'wp_unique_post_slug', 'brightnest_service_pages_unique_slug', 10, 4 );

/**
 * The bookable service a page books (0 = none, or it is switched off).
 */
function brightnest_info_service( $post_id ) {
	$sid = (int) get_post_meta( $post_id, '_bn_book_service', true );
	return $sid && 'bn_service' === get_post_type( $sid ) && 'publish' === get_post_status( $sid ) && brightnest_service_active( $sid ) && ! brightnest_info_only( $sid ) ? $sid : 0;
}

/**
 * Homepage slider of a service page: "clean" or "maint" (set in its editor; by default from its name).
 */
function brightnest_home_group( $post_id ) {
	$g = (string) get_post_meta( $post_id, '_bn_home_group', true );
	if ( in_array( $g, array( 'clean', 'maint', 'none' ), true ) ) {
		return $g;
	}
	return false !== stripos( get_the_title( $post_id ), 'clean' ) ? 'clean' : 'maint';
}

/**
 * Service pages of one homepage slider, in their order.
 */
function brightnest_home_group_pages( $group ) {
	$ids = get_posts( array( 'post_type' => 'bn_info', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
	return array_values( array_filter( $ids, function ( $id ) use ( $group ) { return 'all' === $group ? 'none' !== brightnest_home_group( $id ) : brightnest_home_group( $id ) === $group; } ) );
}

/* Editor: "Booking service" and the page's own FAQ ---------------------------------------- */

function brightnest_service_pages_boxes() {
	add_meta_box( 'bn-info-booking', __( 'Booking', 'brightnest' ), 'brightnest_service_pages_booking_box', 'bn_info', 'side', 'high' );
	add_meta_box( 'bn-info-faq', __( 'FAQ for this page', 'brightnest' ), 'brightnest_service_pages_faq_box', 'bn_info', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'brightnest_service_pages_boxes' );

function brightnest_service_pages_booking_box( $post ) {
	wp_nonce_field( 'bn_info_save', 'bn_info_nonce' );
	$cur  = (int) get_post_meta( $post->ID, '_bn_book_service', true );
	$list = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	echo '<p><label for="bn-book-service"><strong>' . esc_html__( 'Booking service', 'brightnest' ) . '</strong></label></p>';
	echo '<select id="bn-book-service" name="bn_book_service" style="width:100%"><option value="0">' . esc_html__( '— None: "Get a quote" (WhatsApp) —', 'brightnest' ) . '</option>';
	foreach ( $list as $s ) {
		if ( brightnest_info_only( $s->ID ) ) {
			continue;
		}
		printf( '<option value="%1$d" %2$s>%3$s</option>', (int) $s->ID, selected( $cur, $s->ID, false ), esc_html( get_the_title( $s ) ) );
	}
	echo '</select>';
	echo '<p class="description">' . esc_html__( 'The page\'s Book now button opens this service\'s checkout, with its prices. Leave it on None for a "Get a quote" button.', 'brightnest' ) . '</p>';
	$g = brightnest_home_group( $post->ID );
	echo '<p><label for="bn-home-group"><strong>' . esc_html__( 'Homepage slider', 'brightnest' ) . '</strong></label></p><select id="bn-home-group" name="bn_home_group" style="width:100%">';
	foreach ( array( 'clean' => __( 'Cleaning services', 'brightnest' ), 'maint' => __( 'Maintenance services', 'brightnest' ), 'none' => __( 'Not on the homepage', 'brightnest' ) ) as $k => $v ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $k ), selected( $g, $k, false ), esc_html( $v ) );
	}
	echo '</select>';
}

function brightnest_service_pages_faq_box( $post ) {
	$faqs = get_post_meta( $post->ID, '_bn_faqs', true );
	echo '<p class="description">' . esc_html__( 'Questions about this service. They show first, then the general FAQs (BrightNest → Settings → FAQ).', 'brightnest' ) . '</p>';
	echo '<input type="hidden" name="bn_info_faqs_sent" value="1"><ol class="bn-faq-edit" data-faq-list>';
	foreach ( is_array( $faqs ) ? $faqs : array() as $i => $faq ) {
		printf(
			'<li><input name="bn_faqs[%1$d][0]" value="%2$s" placeholder="%4$s"><textarea name="bn_faqs[%1$d][1]" rows="2" placeholder="%5$s">%3$s</textarea><button type="button" class="button-link bn-opt-del" data-faq-del><span class="dashicons dashicons-trash"></span></button></li>',
			(int) $i,
			esc_attr( $faq[0] ),
			esc_textarea( $faq[1] ),
			esc_attr__( 'Question', 'brightnest' ),
			esc_attr__( 'Answer', 'brightnest' )
		);
	}
	echo '</ol><button type="button" class="button" data-faq-add data-name="bn_faqs"><span class="dashicons dashicons-plus"></span> ' . esc_html__( 'Add question', 'brightnest' ) . '</button>';
}

function brightnest_service_pages_save( $post_id ) {
	if ( ! isset( $_POST['bn_info_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['bn_info_nonce'] ), 'bn_info_save' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_bn_book_service', isset( $_POST['bn_book_service'] ) ? absint( $_POST['bn_book_service'] ) : 0 );
	if ( isset( $_POST['bn_home_group'] ) && in_array( $_POST['bn_home_group'], array( 'clean', 'maint', 'none' ), true ) ) {
		update_post_meta( $post_id, '_bn_home_group', sanitize_key( $_POST['bn_home_group'] ) );
	}
	if ( isset( $_POST['bn_info_faqs_sent'] ) ) {
		$faqs = array();
		foreach ( isset( $_POST['bn_faqs'] ) && is_array( $_POST['bn_faqs'] ) ? wp_unslash( $_POST['bn_faqs'] ) : array() as $row ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$q = isset( $row[0] ) ? sanitize_text_field( $row[0] ) : '';
			$a = isset( $row[1] ) ? sanitize_textarea_field( $row[1] ) : '';
			if ( '' !== $q && '' !== $a ) {
				$faqs[] = array( $q, $a );
			}
		}
		update_post_meta( $post_id, '_bn_faqs', $faqs );
	}
}
add_action( 'save_post_bn_info', 'brightnest_service_pages_save' );

function brightnest_service_pages_admin_assets( $hook ) {
	$screen = get_current_screen();
	if ( $screen && 'bn_info' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		wp_enqueue_style( 'brightnest-admin', BRIGHTNEST_URI . '/assets/admin/admin.css', array(), BRIGHTNEST_VERSION );
		wp_enqueue_script( 'brightnest-settings', BRIGHTNEST_URI . '/assets/admin/settings.js', array(), BRIGHTNEST_VERSION, true );
	}
}
add_action( 'admin_enqueue_scripts', 'brightnest_service_pages_admin_assets' );

/* List screen: which service each page books ------------------------------------------------ */

function brightnest_service_pages_columns( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'title' === $k ) {
			$out['bn_book'] = __( 'Booking service', 'brightnest' );
		}
	}
	return $out;
}
add_filter( 'manage_bn_info_posts_columns', 'brightnest_service_pages_columns' );

function brightnest_service_pages_column( $col, $post_id ) {
	if ( 'bn_book' === $col ) {
		$sid = brightnest_info_service( $post_id );
		echo $sid ? '<a href="' . esc_url( get_edit_post_link( $sid ) ) . '">' . esc_html( get_the_title( $sid ) ) . '</a>' : '<span style="color:#787c82">' . esc_html__( 'Get a quote', 'brightnest' ) . '</span>';
	}
}
add_action( 'manage_bn_info_posts_custom_column', 'brightnest_service_pages_column', 10, 2 );

/* One time: the "Info page only" services become service pages (same ID, text, photo, FAQ, SEO, URL). */

function brightnest_service_pages_flush() {
	if ( 'our-services' !== get_option( 'bn_info_archive' ) ) {
		update_option( 'bn_info_archive', 'our-services' );
		flush_rewrite_rules( false );
	}
}
add_action( 'init', 'brightnest_service_pages_flush', 101 );

function brightnest_service_pages_migrate() {
	if ( get_option( 'bn_service_pages_v1' ) ) {
		return;
	}
	update_option( 'bn_service_pages_v1', 1 );
	$ids = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids', 'meta_key' => '_bn_info_only', 'meta_value' => '1' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	foreach ( $ids as $id ) {
		set_post_type( $id, 'bn_info' );
		delete_post_meta( $id, '_bn_info_only' );
	}
	flush_rewrite_rules( false );
}
add_action( 'init', 'brightnest_service_pages_migrate', 99 );
