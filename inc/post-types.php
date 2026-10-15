<?php
/**
 * Custom post types & taxonomies: Services, Service Categories, Reviews, Requests (bookings/enquiries).
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

function brightnest_register_post_types() {
	register_post_type(
		'bn_service',
		array(
			'labels'       => array(
				'name'          => __( 'Services', 'brightnest' ),
				'singular_name' => __( 'Service', 'brightnest' ),
				'add_new_item'  => __( 'Add New Service', 'brightnest' ),
				'edit_item'     => __( 'Edit Service', 'brightnest' ),
				'all_items'     => __( 'All Services', 'brightnest' ),
				'menu_name'     => __( 'Services', 'brightnest' ),
			),
			'public'       => true,
			'has_archive'  => 'services',
			'rewrite'      => array( 'slug' => 'service', 'with_front' => false ),
			'menu_icon'    => 'dashicons-admin-home',
			'menu_position'=> 5,
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		)
	);

	register_taxonomy(
		'bn_service_cat',
		'bn_service',
		array(
			'labels'            => array(
				'name'          => __( 'Service Categories', 'brightnest' ),
				'singular_name' => __( 'Service Category', 'brightnest' ),
				'add_new_item'  => __( 'Add New Category', 'brightnest' ),
				'menu_name'     => __( 'Categories', 'brightnest' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'services', 'with_front' => false ),
		)
	);

	register_post_type(
		'bn_review',
		array(
			'labels'       => array(
				'name'          => __( 'Reviews', 'brightnest' ),
				'singular_name' => __( 'Review', 'brightnest' ),
				'add_new_item'  => __( 'Add New Review', 'brightnest' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'menu_icon'    => 'dashicons-star-filled',
			'menu_position'=> 6,
			'supports'     => array( 'title', 'editor' ),
		)
	);

	register_post_type(
		'bn_request',
		array(
			'labels'          => array(
				'name'          => __( 'Bookings & Enquiries', 'brightnest' ),
				'singular_name' => __( 'Request', 'brightnest' ),
				'menu_name'     => __( 'Bookings', 'brightnest' ),
				'edit_item'     => __( 'View Request', 'brightnest' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-calendar-alt',
			'menu_position'   => 4,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'brightnest_register_post_types' );

/**
 * Admin list columns for requests.
 */
function brightnest_request_columns( $cols ) {
	return array(
		'cb'         => $cols['cb'],
		'title'      => __( 'Request', 'brightnest' ),
		'bn_type'    => __( 'Type', 'brightnest' ),
		'bn_phone'   => __( 'Phone', 'brightnest' ),
		'bn_when'    => __( 'Preferred date', 'brightnest' ),
		'bn_total'   => __( 'Total', 'brightnest' ),
		'bn_status'  => __( 'Status', 'brightnest' ),
		'date'       => $cols['date'],
	);
}
add_filter( 'manage_bn_request_posts_columns', 'brightnest_request_columns' );

function brightnest_request_column_content( $col, $post_id ) {
	switch ( $col ) {
		case 'bn_type':
			echo esc_html( ucfirst( get_post_meta( $post_id, '_bn_type', true ) ) );
			break;
		case 'bn_phone':
			$phone = get_post_meta( $post_id, '_bn_phone', true );
			echo $phone ? '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>' : '&mdash;';
			break;
		case 'bn_when':
			$date = get_post_meta( $post_id, '_bn_date', true );
			$time = get_post_meta( $post_id, '_bn_time', true );
			echo esc_html( trim( $date . ' ' . $time ) ) ?: '&mdash;';
			break;
		case 'bn_total':
			$total = get_post_meta( $post_id, '_bn_total', true );
			echo '' !== $total ? esc_html( brightnest_opt( 'currency' ) . ' ' . $total ) : '&mdash;';
			break;
		case 'bn_status':
			$status = get_post_meta( $post_id, '_bn_status', true ) ?: 'new';
			printf( '<span class="bn-status bn-status-%1$s">%2$s</span>', esc_attr( $status ), esc_html( ucfirst( $status ) ) );
			break;
	}
}
add_action( 'manage_bn_request_posts_custom_column', 'brightnest_request_column_content', 10, 2 );

function brightnest_admin_status_css() {
	$screen = get_current_screen();
	if ( $screen && 'bn_request' === $screen->post_type ) {
		echo '<style>.bn-status{padding:3px 10px;border-radius:99px;font-size:12px;font-weight:600;background:#e5e7eb}.bn-status-new{background:#dbeafe;color:#1d4ed8}.bn-status-confirmed{background:#dcfce7;color:#15803d}.bn-status-completed{background:#f3f4f6;color:#374151}.bn-status-cancelled{background:#fee2e2;color:#b91c1c}.bn-req-table th{text-align:left;width:160px;padding:8px 0;vertical-align:top}.bn-req-table td{padding:8px 0}</style>';
	}
}
add_action( 'admin_head', 'brightnest_admin_status_css' );
