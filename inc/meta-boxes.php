<?php
/**
 * Meta boxes: service details, review details, request viewer, category icon.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

function brightnest_add_meta_boxes() {
	add_meta_box( 'bn_service_details', __( 'Service Details', 'brightnest' ), 'brightnest_service_meta_box', 'bn_service', 'normal', 'high' );
	add_meta_box( 'bn_review_details', __( 'Review Details', 'brightnest' ), 'brightnest_review_meta_box', 'bn_review', 'side' );
	add_meta_box( 'bn_request_details', __( 'Request Details', 'brightnest' ), 'brightnest_request_meta_box', 'bn_request', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'brightnest_add_meta_boxes' );

/**
 * Service fields.
 */
function brightnest_service_meta_box( $post ) {
	wp_nonce_field( 'bn_save_meta', 'bn_meta_nonce' );
	$price    = get_post_meta( $post->ID, '_bn_price', true );
	$unit     = get_post_meta( $post->ID, '_bn_price_unit', true );
	$duration = get_post_meta( $post->ID, '_bn_duration', true );
	$rating   = get_post_meta( $post->ID, '_bn_rating', true );
	$reviews  = get_post_meta( $post->ID, '_bn_review_count', true );
	$icon     = get_post_meta( $post->ID, '_bn_icon', true );
	$featured = get_post_meta( $post->ID, '_bn_featured', true );
	$includes = get_post_meta( $post->ID, '_bn_includes', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="bn_price"><?php esc_html_e( 'Starting price', 'brightnest' ); ?></label></th>
			<td>
				<input type="number" step="0.01" min="0" id="bn_price" name="bn_price" value="<?php echo esc_attr( $price ); ?>" class="small-text">
				<input type="text" name="bn_price_unit" value="<?php echo esc_attr( $unit ); ?>" placeholder="<?php esc_attr_e( 'per hour / per visit', 'brightnest' ); ?>">
				<p class="description"><?php esc_html_e( 'Shown as "From AED 99 per hour". Currency is set in Appearance > Customize > BrightNest.', 'brightnest' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bn_duration"><?php esc_html_e( 'Typical duration', 'brightnest' ); ?></label></th>
			<td><input type="text" id="bn_duration" name="bn_duration" value="<?php echo esc_attr( $duration ); ?>" placeholder="2 - 4 hours"></td>
		</tr>
		<tr>
			<th><label for="bn_rating"><?php esc_html_e( 'Rating / review count', 'brightnest' ); ?></label></th>
			<td>
				<input type="number" step="0.1" min="0" max="5" id="bn_rating" name="bn_rating" value="<?php echo esc_attr( $rating ); ?>" class="small-text">
				<input type="number" min="0" name="bn_review_count" value="<?php echo esc_attr( $reviews ); ?>" class="small-text" placeholder="0">
				<p class="description"><?php esc_html_e( 'Leave empty to hide rating on the card.', 'brightnest' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bn_icon"><?php esc_html_e( 'Icon (used when no featured image)', 'brightnest' ); ?></label></th>
			<td>
				<select id="bn_icon" name="bn_icon">
					<option value=""><?php esc_html_e( 'Use category icon', 'brightnest' ); ?></option>
					<?php foreach ( brightnest_service_icon_choices() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $icon, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Most popular', 'brightnest' ); ?></th>
			<td><label><input type="checkbox" name="bn_featured" value="1" <?php checked( $featured, '1' ); ?>> <?php esc_html_e( 'Show in "Most Popular Services" on the homepage', 'brightnest' ); ?></label></td>
		</tr>
		<tr>
			<th><label for="bn_includes"><?php esc_html_e( "What's included", 'brightnest' ); ?></label></th>
			<td>
				<textarea id="bn_includes" name="bn_includes" rows="6" class="large-text" placeholder="<?php esc_attr_e( 'One item per line', 'brightnest' ); ?>"><?php echo esc_textarea( $includes ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th><label for="bn_options"><?php esc_html_e( 'Checkout options', 'brightnest' ); ?></label></th>
			<td>
				<textarea id="bn_options" name="bn_options" rows="10" class="large-text code" placeholder="Bestsellers | 2 Hours, 1 Cleaner | Ideal for studios | 70 | 90"><?php echo esc_textarea( get_post_meta( $post->ID, '_bn_options', true ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One option per line: Tab name | Option name | Short description | Price | Old price (optional). Tabs appear in step 1 of checkout. Leave empty to sell the service itself at its starting price.', 'brightnest' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Review fields.
 */
function brightnest_review_meta_box( $post ) {
	wp_nonce_field( 'bn_save_meta', 'bn_meta_nonce' );
	$rating  = get_post_meta( $post->ID, '_bn_rating', true ) ?: 5;
	$service = get_post_meta( $post->ID, '_bn_service_name', true );
	?>
	<p><label for="bn_rating"><strong><?php esc_html_e( 'Stars (1-5)', 'brightnest' ); ?></strong></label><br>
		<input type="number" min="1" max="5" id="bn_rating" name="bn_rating" value="<?php echo esc_attr( $rating ); ?>" class="small-text"></p>
	<p><label for="bn_service_name"><strong><?php esc_html_e( 'Service used', 'brightnest' ); ?></strong></label><br>
		<input type="text" id="bn_service_name" name="bn_service_name" value="<?php echo esc_attr( $service ); ?>" class="widefat"></p>
	<p class="description"><?php esc_html_e( 'Title = customer name, content = review text.', 'brightnest' ); ?></p>
	<?php
}

/**
 * Request viewer (read-only details + status).
 */
function brightnest_request_meta_box( $post ) {
	wp_nonce_field( 'bn_save_meta', 'bn_meta_nonce' );
	$fields = array(
		'_bn_ref'       => __( 'Booking ref', 'brightnest' ),
		'_bn_type'      => __( 'Type', 'brightnest' ),
		'_bn_service'   => __( 'Service', 'brightnest' ),
		'_bn_items'     => __( 'Items', 'brightnest' ),
		'_bn_frequency' => __( 'Frequency', 'brightnest' ),
		'_bn_subtotal'  => __( 'Subtotal', 'brightnest' ),
		'_bn_voucher'   => __( 'Voucher', 'brightnest' ),
		'_bn_discount'  => __( 'Discount', 'brightnest' ),
		'_bn_total'     => __( 'Total', 'brightnest' ),
		'_bn_payment'   => __( 'Payment method', 'brightnest' ),
		'_bn_location'  => __( 'Map pin', 'brightnest' ),
		'_bn_name'    => __( 'Name', 'brightnest' ),
		'_bn_phone'   => __( 'Phone', 'brightnest' ),
		'_bn_email'   => __( 'Email', 'brightnest' ),
		'_bn_date'    => __( 'Preferred date', 'brightnest' ),
		'_bn_time'    => __( 'Preferred time', 'brightnest' ),
		'_bn_area'    => __( 'Area / City', 'brightnest' ),
		'_bn_address' => __( 'Address', 'brightnest' ),
		'_bn_message' => __( 'Notes / Message', 'brightnest' ),
	);
	$status = get_post_meta( $post->ID, '_bn_status', true ) ?: 'new';
	echo '<table class="bn-req-table">';
	foreach ( $fields as $key => $label ) {
		$val = get_post_meta( $post->ID, $key, true );
		if ( '' === $val ) {
			continue;
		}
		if ( '_bn_email' === $key ) {
			$val = '<a href="mailto:' . esc_attr( $val ) . '">' . esc_html( $val ) . '</a>';
		} elseif ( '_bn_location' === $key ) {
			$val = '<a href="' . esc_url( 'https://www.google.com/maps?q=' . $val ) . '" target="_blank" rel="noopener">' . esc_html( $val ) . ' ↗</a>';
		} elseif ( in_array( $key, array( '_bn_subtotal', '_bn_discount', '_bn_total' ), true ) ) {
			$val = esc_html( brightnest_opt( 'currency' ) . ' ' . $val );
		} elseif ( '_bn_phone' === $key ) {
			$val = '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $val ) ) . '">' . esc_html( $val ) . '</a>';
		} else {
			$val = nl2br( esc_html( $val ) );
		}
		printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $label ), $val ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</table>';
	?>
	<p><label for="bn_status"><strong><?php esc_html_e( 'Status', 'brightnest' ); ?></strong></label><br>
		<select name="bn_status" id="bn_status">
			<?php foreach ( array( 'new', 'confirmed', 'completed', 'cancelled' ) as $s ) : ?>
				<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
			<?php endforeach; ?>
		</select></p>
	<?php
}

/**
 * Save handler for all meta boxes.
 */
function brightnest_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['bn_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['bn_meta_nonce'] ), 'bn_save_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'bn_service' === $post->post_type ) {
		$text = array( 'bn_price_unit', 'bn_duration', 'bn_icon' );
		foreach ( $text as $f ) {
			update_post_meta( $post_id, '_' . $f, isset( $_POST[ $f ] ) ? sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) : '' );
		}
		update_post_meta( $post_id, '_bn_price', isset( $_POST['bn_price'] ) && '' !== $_POST['bn_price'] ? (float) $_POST['bn_price'] : '' );
		update_post_meta( $post_id, '_bn_rating', isset( $_POST['bn_rating'] ) && '' !== $_POST['bn_rating'] ? min( 5, max( 0, (float) $_POST['bn_rating'] ) ) : '' );
		update_post_meta( $post_id, '_bn_review_count', isset( $_POST['bn_review_count'] ) && '' !== $_POST['bn_review_count'] ? absint( $_POST['bn_review_count'] ) : '' );
		update_post_meta( $post_id, '_bn_featured', empty( $_POST['bn_featured'] ) ? '' : '1' );
		update_post_meta( $post_id, '_bn_includes', isset( $_POST['bn_includes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bn_includes'] ) ) : '' );
		update_post_meta( $post_id, '_bn_options', isset( $_POST['bn_options'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bn_options'] ) ) : '' );
	}

	if ( 'bn_review' === $post->post_type ) {
		update_post_meta( $post_id, '_bn_rating', isset( $_POST['bn_rating'] ) ? min( 5, max( 1, absint( $_POST['bn_rating'] ) ) ) : 5 );
		update_post_meta( $post_id, '_bn_service_name', isset( $_POST['bn_service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bn_service_name'] ) ) : '' );
	}

	if ( 'bn_request' === $post->post_type && isset( $_POST['bn_status'] ) ) {
		$status = sanitize_key( $_POST['bn_status'] );
		if ( in_array( $status, array( 'new', 'confirmed', 'completed', 'cancelled' ), true ) ) {
			update_post_meta( $post_id, '_bn_status', $status );
		}
	}
}
add_action( 'save_post', 'brightnest_save_meta', 10, 2 );

/*
 * Category icon field.
 */
function brightnest_cat_icon_add_field() {
	?>
	<div class="form-field">
		<label for="bn_cat_icon"><?php esc_html_e( 'Icon', 'brightnest' ); ?></label>
		<select name="bn_cat_icon" id="bn_cat_icon">
			<?php foreach ( brightnest_service_icon_choices() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}
add_action( 'bn_service_cat_add_form_fields', 'brightnest_cat_icon_add_field' );

function brightnest_cat_icon_edit_field( $term ) {
	$icon = get_term_meta( $term->term_id, 'bn_icon', true );
	?>
	<tr class="form-field">
		<th><label for="bn_cat_icon"><?php esc_html_e( 'Icon', 'brightnest' ); ?></label></th>
		<td>
			<select name="bn_cat_icon" id="bn_cat_icon">
				<?php foreach ( brightnest_service_icon_choices() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $icon, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<?php
}
add_action( 'bn_service_cat_edit_form_fields', 'brightnest_cat_icon_edit_field' );

function brightnest_cat_icon_save( $term_id ) {
	if ( isset( $_POST['bn_cat_icon'] ) && current_user_can( 'manage_categories' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- core term screens verify nonce.
		update_term_meta( $term_id, 'bn_icon', sanitize_key( $_POST['bn_cat_icon'] ) );
	}
}
add_action( 'created_bn_service_cat', 'brightnest_cat_icon_save' );
add_action( 'edited_bn_service_cat', 'brightnest_cat_icon_save' );
