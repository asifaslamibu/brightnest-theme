<?php
/**
 * "Google (SEO)" box on services, pages and posts: the title in Google / the browser tab, the meta
 * description and (services) the big heading on the page. Empty fields keep the normal title and excerpt.
 * Skipped when an SEO plugin is active (it has its own fields).
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

function brightnest_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'AIOSEO\\Plugin\\AIOSEO' ) || defined( 'SEOPRESS_VERSION' );
}

function brightnest_seo_meta_box() {
	foreach ( array( 'bn_service', 'page', 'post' ) as $type ) {
		add_meta_box( 'bn-seo', __( 'Google (SEO)', 'brightnest' ), 'brightnest_seo_meta_box_html', $type, 'normal', 'default' );
	}
}
add_action( 'add_meta_boxes', 'brightnest_seo_meta_box' );

function brightnest_seo_meta_box_html( $post ) {
	wp_nonce_field( 'bn_seo_save', 'bn_seo_nonce' );
	$title = (string) get_post_meta( $post->ID, '_bn_seo_title', true );
	$desc  = (string) get_post_meta( $post->ID, '_bn_seo_desc', true );
	$h1    = (string) get_post_meta( $post->ID, '_bn_h1', true );
	?>
	<p><label><strong><?php esc_html_e( 'Title in Google', 'brightnest' ); ?></strong><br>
		<input type="text" class="widefat" name="bn_seo[title]" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post ) ); ?>"></label>
		<span class="description"><?php esc_html_e( 'About 60 characters. Empty: the page title.', 'brightnest' ); ?></span></p>
	<p><label><strong><?php esc_html_e( 'Meta description', 'brightnest' ); ?></strong><br>
		<textarea class="widefat" rows="3" name="bn_seo[desc]"><?php echo esc_textarea( $desc ); ?></textarea></label>
		<span class="description"><?php esc_html_e( 'About 155 characters, shown under the title in Google. Empty: the excerpt.', 'brightnest' ); ?></span></p>
	<?php if ( 'post' !== $post->post_type ) : ?>
		<p><label><strong><?php esc_html_e( 'Main heading on the page (H1)', 'brightnest' ); ?></strong><br>
			<input type="text" class="widefat" name="bn_seo[h1]" value="<?php echo esc_attr( $h1 ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post ) ); ?>"></label>
			<span class="description"><?php esc_html_e( 'Empty: the page title. The title stays the short name used in menus and lists.', 'brightnest' ); ?></span></p>
	<?php endif; ?>
	<?php
}

function brightnest_seo_meta_save( $post_id ) {
	if ( ! isset( $_POST['bn_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bn_seo_nonce'] ) ), 'bn_seo_save' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	$in = isset( $_POST['bn_seo'] ) && is_array( $_POST['bn_seo'] ) ? wp_unslash( $_POST['bn_seo'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	update_post_meta( $post_id, '_bn_seo_title', isset( $in['title'] ) ? sanitize_text_field( $in['title'] ) : '' );
	update_post_meta( $post_id, '_bn_seo_desc', isset( $in['desc'] ) ? sanitize_textarea_field( $in['desc'] ) : '' );
	if ( isset( $in['h1'] ) ) {
		update_post_meta( $post_id, '_bn_h1', sanitize_text_field( $in['h1'] ) );
	}
}
add_action( 'save_post', 'brightnest_seo_meta_save' );

/**
 * Title in Google for a service, page or post (English site; the Arabic site keeps its Arabic titles).
 */
function brightnest_seo_document_title( $title ) {
	if ( brightnest_seo_plugin_active() || ! is_singular() || is_front_page() || ( function_exists( 'brightnest_is_ar' ) && brightnest_is_ar() ) ) {
		return $title;
	}
	$own = trim( (string) get_post_meta( get_queried_object_id(), '_bn_seo_title', true ) );
	return '' !== $own ? $own : $title;
}
add_filter( 'pre_get_document_title', 'brightnest_seo_document_title', 20 );

/**
 * Meta description typed in the box, or '' (English site only).
 */
function brightnest_seo_description() {
	if ( ! is_singular() || is_front_page() || ( function_exists( 'brightnest_is_ar' ) && brightnest_is_ar() ) ) {
		return '';
	}
	return trim( (string) get_post_meta( get_queried_object_id(), '_bn_seo_desc', true ) );
}

/**
 * The big heading of a service or page: the H1 typed in the box, else the title.
 */
function brightnest_page_h1( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$h1      = ( function_exists( 'brightnest_is_ar' ) && brightnest_is_ar() ) ? '' : trim( (string) get_post_meta( $post_id, '_bn_h1', true ) );
	return '' !== $h1 ? $h1 : get_the_title( $post_id );
}
