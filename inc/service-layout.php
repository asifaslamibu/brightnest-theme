<?php
/**
 * Service page layout: which sections show, in what order, their headings and button texts.
 * Settings → Service page holds the common choice for every service; each service can change
 * any of it in its own editor ("Page layout" tab, saved in _bn_layout). Empty = the common one.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sections of the main column that can be moved, and the switches a service can change.
 */
function brightnest_sv_sections() {
	return array(
		'sv_overview' => __( 'About the service (page text)', 'brightnest' ),
		'sv_included' => __( "What's included", 'brightnest' ),
		'sv_prices'   => __( 'Prices', 'brightnest' ),
		'sv_faq'      => __( 'FAQ', 'brightnest' ),
	);
}

function brightnest_sv_switches() {
	return brightnest_sv_sections() + array(
		'sv_related'  => __( 'Related services', 'brightnest' ),
		'sv_whatsapp' => __( 'WhatsApp buttons', 'brightnest' ),
	);
}

/**
 * Texts a service can change: key => label.
 */
function brightnest_sv_text_fields() {
	return array(
		'sv_about_title'    => __( 'About section heading', 'brightnest' ),
		'sv_included_title' => __( '"What\'s included" heading', 'brightnest' ),
		'sv_prices_title'   => __( 'Prices heading', 'brightnest' ),
		'sv_faq_title'      => __( 'FAQ heading', 'brightnest' ),
		'sv_related_title'  => __( 'Related services heading', 'brightnest' ),
		'sv_from_label'     => __( 'Price label', 'brightnest' ),
		'sv_book_label'     => __( 'Book button', 'brightnest' ),
		'sv_wa_label'       => __( 'WhatsApp button (top)', 'brightnest' ),
		'sv_wa_side_label'  => __( 'WhatsApp button (side card)', 'brightnest' ),
		'sv_call_label'     => __( 'Call line (side card)', 'brightnest' ),
		'sv_price_book'     => __( 'Book button in the price list', 'brightnest' ),
		'sv_quote_label'    => __( 'Text when there is no price', 'brightnest' ),
		'sv_all_label'      => __( '"All services" button', 'brightnest' ),
		'sv_mbar_from'      => __( 'Price label (phone bar)', 'brightnest' ),
	);
}

/**
 * The layout a service saved for itself.
 */
function brightnest_sv_layout( $post_id ) {
	$l = $post_id ? get_post_meta( $post_id, '_bn_layout', true ) : array();
	return is_array( $l ) ? $l : array();
}

/**
 * Is a section on for this service (its own choice, else Settings → Service page)?
 */
function brightnest_sv_show( $sec, $post_id = 0 ) {
	$l = brightnest_sv_layout( $post_id );
	if ( isset( $l['show'][ $sec ] ) && in_array( $l['show'][ $sec ], array( '0', '1' ), true ) ) {
		return '1' === $l['show'][ $sec ];
	}
	return '0' !== (string) brightnest_opt( 'show_' . $sec );
}

/**
 * Order of the main sections for this service (its own, else the common one; missing ones at the end).
 *
 * @return string[]
 */
function brightnest_sv_order( $post_id = 0 ) {
	$all = array_keys( brightnest_sv_sections() );
	$l   = brightnest_sv_layout( $post_id );
	$raw = ! empty( $l['order'] ) ? $l['order'] : (string) get_theme_mod( 'bn_sv_order', '' );
	$got = array_values( array_intersect( array_filter( array_map( 'trim', explode( ',', $raw ) ) ), $all ) );
	return array_merge( $got, array_diff( $all, $got ) );
}

/**
 * Service editor → "Page layout" tab.
 */
function brightnest_sv_layout_panel( $post_id ) {
	$l      = brightnest_sv_layout( $post_id );
	$names  = brightnest_sv_switches();
	$select = function ( $sec ) use ( $l ) {
		$cur    = isset( $l['show'][ $sec ] ) ? (string) $l['show'][ $sec ] : '';
		$common = '0' !== (string) brightnest_opt( 'show_' . $sec ) ? __( 'On', 'brightnest' ) : __( 'Off', 'brightnest' );
		$out    = '<select name="bn_layout[show][' . esc_attr( $sec ) . ']">';
		/* translators: %s On or Off */
		$out .= '<option value=""' . selected( $cur, '', false ) . '>' . esc_html( sprintf( __( 'Like every service (%s)', 'brightnest' ), $common ) ) . '</option>';
		$out .= '<option value="1"' . selected( $cur, '1', false ) . '>' . esc_html__( 'Show on this service', 'brightnest' ) . '</option>';
		$out .= '<option value="0"' . selected( $cur, '0', false ) . '>' . esc_html__( 'Hide on this service', 'brightnest' ) . '</option>';
		return $out . '</select>';
	};
	?>
	<p class="bn-adm-help">
		<?php esc_html_e( 'How this service page looks. Leave anything as it is to use the common layout from Settings → Service page; change it here and it applies to this service only.', 'brightnest' ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=brightnest-settings&tab=service' ) ); ?>"><?php esc_html_e( 'Common layout', 'brightnest' ); ?> ↗</a>
	</p>
	<h4><?php esc_html_e( 'Sections', 'brightnest' ); ?></h4>
	<ol class="bn-secs" data-secs>
		<?php foreach ( brightnest_sv_order( $post_id ) as $sec ) : ?>
			<li class="bn-sec" draggable="true">
				<span class="bn-sec-grip dashicons dashicons-menu" aria-hidden="true"></span>
				<input type="hidden" name="bn_layout[order][]" value="<?php echo esc_attr( $sec ); ?>">
				<span class="bn-sec-name"><?php echo esc_html( $names[ $sec ] ); ?></span>
				<?php echo $select( $sec ); // phpcs:ignore WordPress.Security.EscapeOutput -- built above ?>
				<button type="button" class="button-link" data-sec-up aria-label="<?php esc_attr_e( 'Move up', 'brightnest' ); ?>"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
				<button type="button" class="button-link" data-sec-down aria-label="<?php esc_attr_e( 'Move down', 'brightnest' ); ?>"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
			</li>
		<?php endforeach; ?>
	</ol>
	<p class="bn-sv-lay-more">
		<?php foreach ( array( 'sv_related', 'sv_whatsapp' ) as $sec ) : ?>
			<label><span><?php echo esc_html( $names[ $sec ] ); ?></span> <?php echo $select( $sec ); // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
		<?php endforeach; ?>
	</p>
	<h4><?php esc_html_e( 'Headings and buttons', 'brightnest' ); ?></h4>
	<p class="bn-adm-help"><?php esc_html_e( 'Empty = the common text (shown in grey). {service} is replaced by the service name, {phone} by your phone number.', 'brightnest' ); ?></p>
	<div class="bn-sv-lay-texts">
		<?php foreach ( brightnest_sv_text_fields() as $key => $label ) : ?>
			<label><span><?php echo esc_html( $label ); ?></span>
				<input type="text" name="bn_layout[text][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( isset( $l['text'][ $key ] ) ? $l['text'][ $key ] : '' ); ?>" placeholder="<?php echo esc_attr( brightnest_sv_text( $key ) ); ?>">
			</label>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Save the "Page layout" tab. The order is kept only when it differs from the common one, so a
 * change to the common order still reaches every service that did not choose its own.
 *
 * @param int   $post_id Service.
 * @param array $in      Posted bn_layout (unslashed).
 */
function brightnest_sv_layout_save( $post_id, $in ) {
	$out = array( 'show' => array(), 'text' => array(), 'order' => '' );
	foreach ( array_keys( brightnest_sv_switches() ) as $sec ) {
		$v = isset( $in['show'][ $sec ] ) ? (string) $in['show'][ $sec ] : '';
		if ( in_array( $v, array( '0', '1' ), true ) ) {
			$out['show'][ $sec ] = $v;
		}
	}
	foreach ( array_keys( brightnest_sv_text_fields() ) as $key ) {
		$v = isset( $in['text'][ $key ] ) ? sanitize_text_field( $in['text'][ $key ] ) : '';
		if ( '' !== $v ) {
			$out['text'][ $key ] = $v;
		}
	}
	if ( ! empty( $in['order'] ) && is_array( $in['order'] ) ) {
		$order = array_values( array_intersect( array_map( 'sanitize_key', $in['order'] ), array_keys( brightnest_sv_sections() ) ) );
		if ( $order !== brightnest_sv_order( 0 ) ) {
			$out['order'] = implode( ',', $order );
		}
	}
	if ( ! $out['show'] && ! $out['text'] && '' === $out['order'] ) {
		delete_post_meta( $post_id, '_bn_layout' );
	} else {
		update_post_meta( $post_id, '_bn_layout', $out );
	}
}
