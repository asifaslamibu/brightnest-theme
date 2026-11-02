<?php
/**
 * Edit the homepage where it lives: Pages → Home (or "Edit Page" on the homepage) shows every
 * homepage setting — section order, texts, photos, lists, buttons — in one box and saves them
 * with the page. They are the same settings as BrightNest → Settings → Homepage.
 * The page's own text editor is hidden there: the homepage does not use it.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is this the page set as the homepage (Settings → Reading)?
 */
function brightnest_is_home_page_id( $post_id ) {
	$front = (int) get_option( 'page_on_front' );
	return $front && (int) $post_id === $front && 'page' === get_option( 'show_on_front' );
}

/**
 * The post being edited on post.php, or 0.
 */
function brightnest_home_editor_post() {
	global $pagenow;
	if ( 'post.php' !== $pagenow ) {
		return 0;
	}
	$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : ( isset( $_POST['post_ID'] ) ? absint( $_POST['post_ID'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification
	return brightnest_is_home_page_id( $id ) ? $id : 0;
}

/**
 * The homepage box, in place of the text editor.
 */
function brightnest_home_editor_box( $post_type, $post ) {
	if ( 'page' !== $post_type || ! $post || ! brightnest_is_home_page_id( $post->ID ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	add_meta_box( 'bn-home-editor', __( 'Homepage content', 'brightnest' ), 'brightnest_home_editor_html', 'page', 'normal', 'high' );
	// Boxes that do nothing on the homepage: its SEO and texts are in this box.
	remove_meta_box( 'bn-seo', 'page', 'normal' );
	remove_meta_box( 'bn-ar-version', 'page', 'normal' );
}
add_action( 'add_meta_boxes', 'brightnest_home_editor_box', 20, 2 );

function brightnest_home_editor_no_editor() {
	if ( brightnest_home_editor_post() ) {
		remove_post_type_support( 'page', 'editor' );
	}
}
add_action( 'load-post.php', 'brightnest_home_editor_no_editor' );

function brightnest_home_editor_assets( $hook ) {
	if ( 'post.php' !== $hook || ! brightnest_home_editor_post() ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'brightnest-admin', BRIGHTNEST_URI . '/assets/admin/admin.css', array(), BRIGHTNEST_VERSION );
	wp_enqueue_script( 'brightnest-settings', BRIGHTNEST_URI . '/assets/admin/settings.js', array(), BRIGHTNEST_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'brightnest_home_editor_assets' );

function brightnest_home_editor_html() {
	$schema = brightnest_settings_schema();
	wp_nonce_field( 'bn_home_editor', 'bn_home_editor_nonce' );
	?>
	<div class="bn-set bn-home-editor">
		<p class="bn-set-intro">
			<?php esc_html_e( 'Everything on the homepage: open a section, change it, then press Update. The order of the sections is in the first card.', 'brightnest' ); ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View homepage', 'brightnest' ); ?> ↗</a>
		</p>
		<p class="bn-set-fold-tools"><button type="button" class="button-link" data-fold-all="1"><?php esc_html_e( 'Open all', 'brightnest' ); ?></button> · <button type="button" class="button-link" data-fold-all="0"><?php esc_html_e( 'Close all', 'brightnest' ); ?></button></p>
		<?php foreach ( $schema['homepage'][2] as $title => $fields ) : ?>
			<details class="bn-set-card bn-set-fold" <?php echo isset( $fields['home_order'] ) ? 'open' : ''; ?>>
				<summary><h2><?php echo esc_html( $title ); ?></h2></summary>
				<div class="bn-set-grid">
					<?php foreach ( $fields as $key => $f ) { brightnest_settings_field( $key, $f ); } ?>
				</div>
			</details>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Saving the page saves the homepage settings.
 */
function brightnest_home_editor_save( $post_id ) {
	if ( ! brightnest_is_home_page_id( $post_id ) || wp_is_post_revision( $post_id ) || ! isset( $_POST['bn_home_editor_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bn_home_editor_nonce'] ) ), 'bn_home_editor' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$in = isset( $_POST['bn'] ) && is_array( $_POST['bn'] ) ? wp_unslash( $_POST['bn'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	brightnest_settings_save_groups( brightnest_settings_schema()['homepage'][2], $in );
}
add_action( 'save_post_page', 'brightnest_home_editor_save' );
