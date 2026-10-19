<?php
/**
 * Booking & contact form handling. Submissions are saved as "Bookings & Enquiries"
 * in wp-admin and emailed to the site owner.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

function brightnest_handle_request() {
	$redirect = isset( $_POST['bn_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['bn_redirect'] ) ) : home_url( '/' );
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );
	$anchor   = '#bn-form';

	if ( ! isset( $_POST['bn_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['bn_nonce'] ), 'bn_request' ) ) {
		wp_safe_redirect( add_query_arg( 'bn_status', 'error', $redirect ) . $anchor );
		exit;
	}

	// Honeypot: bots fill hidden fields.
	if ( ! empty( $_POST['bn_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'bn_status', 'sent', $redirect ) . $anchor );
		exit;
	}

	$type = isset( $_POST['bn_type'] ) && 'contact' === $_POST['bn_type'] ? 'contact' : 'booking';
	$data = array();
	foreach ( array( 'name', 'phone', 'email', 'service', 'date', 'time', 'area', 'address' ) as $f ) {
		$data[ $f ] = isset( $_POST[ 'bn_' . $f ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'bn_' . $f ] ) ) : '';
	}
	$data['message'] = isset( $_POST['bn_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bn_message'] ) ) : '';
	$data['email']   = sanitize_email( $data['email'] );

	if ( '' === $data['name'] || '' === $data['phone'] || ( 'booking' === $type && '' === $data['service'] ) ) {
		wp_safe_redirect( add_query_arg( 'bn_status', 'missing', $redirect ) . $anchor );
		exit;
	}

	$title = 'booking' === $type
		/* translators: 1: service, 2: customer name */
		? sprintf( __( 'Booking: %1$s - %2$s', 'brightnest' ), $data['service'], $data['name'] )
		/* translators: %s: customer name */
		: sprintf( __( 'Enquiry from %s', 'brightnest' ), $data['name'] );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'bn_request',
			'post_status' => 'publish',
			'post_title'  => $title,
		)
	);

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_bn_type', $type );
		update_post_meta( $post_id, '_bn_status', 'new' );
		foreach ( $data as $k => $v ) {
			update_post_meta( $post_id, '_bn_' . $k, $v );
		}
	}

	$to = brightnest_opt( 'booking_email' );
	if ( ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}
	$lines = array();
	foreach ( $data as $k => $v ) {
		if ( '' !== $v ) {
			$lines[] = ucfirst( $k ) . ': ' . $v;
		}
	}
	$lines[] = '';
	$lines[] = admin_url( 'edit.php?post_type=bn_request' );
	$headers = array();
	if ( is_email( $data['email'] ) ) {
		$headers[] = 'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>';
	}
	wp_mail( $to, '[' . get_bloginfo( 'name' ) . '] ' . $title, implode( "\n", $lines ), $headers );

	wp_safe_redirect( add_query_arg( 'bn_status', 'sent', $redirect ) . $anchor );
	exit;
}
add_action( 'admin_post_nopriv_bn_request', 'brightnest_handle_request' );
add_action( 'admin_post_bn_request', 'brightnest_handle_request' );

/**
 * Status message after a form submit.
 */
function brightnest_form_notice() {
	$status = isset( $_GET['bn_status'] ) ? sanitize_key( $_GET['bn_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'sent' === $status ) {
		return '<div class="bn-notice bn-notice-success" role="status">' . brightnest_icon( 'check-circle', 20 ) . '<span>' . esc_html__( 'Thank you! Your request has been received. Our team will contact you shortly to confirm.', 'brightnest' ) . '</span></div>';
	}
	if ( 'missing' === $status ) {
		return '<div class="bn-notice bn-notice-error" role="alert">' . esc_html__( 'Please fill in your name, phone number and the service you need.', 'brightnest' ) . '</div>';
	}
	if ( 'error' === $status ) {
		return '<div class="bn-notice bn-notice-error" role="alert">' . esc_html__( 'Something went wrong. Please try again or call us.', 'brightnest' ) . '</div>';
	}
	return '';
}

/**
 * Render the booking form.
 *
 * @param string $selected Pre-selected service title.
 */
function brightnest_booking_form( $selected = '' ) {
	$services = get_posts(
		array(
			'post_type'      => 'bn_service',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);
	$cities   = brightnest_cities();
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' ) . strtok( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/', '?' );
	?>
	<form class="bn-form" id="bn-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php echo brightnest_form_notice(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<input type="hidden" name="action" value="bn_request">
		<input type="hidden" name="bn_type" value="booking">
		<input type="hidden" name="bn_redirect" value="<?php echo esc_url( $redirect ); ?>">
		<?php wp_nonce_field( 'bn_request', 'bn_nonce' ); ?>
		<div class="bn-hp" aria-hidden="true"><input type="text" name="bn_website" tabindex="-1" autocomplete="off"></div>

		<div class="bn-field">
			<label for="bn_service"><?php esc_html_e( 'Service', 'brightnest' ); ?> *</label>
			<select id="bn_service" name="bn_service" required>
				<option value=""><?php esc_html_e( 'Select a service', 'brightnest' ); ?></option>
				<?php foreach ( $services as $sid ) : $t = get_the_title( $sid ); ?>
					<option value="<?php echo esc_attr( $t ); ?>" <?php selected( $selected, $t ); ?>><?php echo esc_html( $t ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="bn-field-row">
			<div class="bn-field">
				<label for="bn_date"><?php esc_html_e( 'Date', 'brightnest' ); ?></label>
				<input type="date" id="bn_date" name="bn_date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
			</div>
			<div class="bn-field">
				<label for="bn_time"><?php esc_html_e( 'Time', 'brightnest' ); ?></label>
				<select id="bn_time" name="bn_time">
					<option value=""><?php esc_html_e( 'Any time', 'brightnest' ); ?></option>
					<?php foreach ( array( '08:00 - 10:00', '10:00 - 12:00', '12:00 - 14:00', '14:00 - 16:00', '16:00 - 18:00', '18:00 - 20:00' ) as $slot ) : ?>
						<option><?php echo esc_html( $slot ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="bn-field-row">
			<div class="bn-field">
				<label for="bn_name"><?php esc_html_e( 'Full name', 'brightnest' ); ?> *</label>
				<input type="text" id="bn_name" name="bn_name" required autocomplete="name">
			</div>
			<div class="bn-field">
				<label for="bn_phone"><?php esc_html_e( 'Phone', 'brightnest' ); ?> *</label>
				<input type="tel" id="bn_phone" name="bn_phone" required autocomplete="tel">
			</div>
		</div>
		<div class="bn-field-row">
			<div class="bn-field">
				<label for="bn_email"><?php esc_html_e( 'Email', 'brightnest' ); ?></label>
				<input type="email" id="bn_email" name="bn_email" autocomplete="email">
			</div>
			<div class="bn-field">
				<label for="bn_area"><?php esc_html_e( 'City / Area', 'brightnest' ); ?></label>
				<select id="bn_area" name="bn_area">
					<?php foreach ( $cities as $city ) : ?>
						<option><?php echo esc_html( $city ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="bn-field">
			<label for="bn_address"><?php esc_html_e( 'Address', 'brightnest' ); ?></label>
			<input type="text" id="bn_address" name="bn_address" autocomplete="street-address" placeholder="<?php esc_attr_e( 'Building, apartment, street', 'brightnest' ); ?>">
		</div>
		<div class="bn-field">
			<label for="bn_message"><?php esc_html_e( 'Anything we should know?', 'brightnest' ); ?></label>
			<textarea id="bn_message" name="bn_message" rows="3" placeholder="<?php esc_attr_e( 'E.g. 2 bedroom apartment, need cleaning materials', 'brightnest' ); ?>"></textarea>
		</div>
		<button type="submit" class="bn-btn bn-btn-primary bn-btn-block"><?php esc_html_e( 'Request booking', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></button>
		<p class="bn-form-note"><?php echo brightnest_icon( 'shield', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'No payment now. We confirm price & timing before your visit.', 'brightnest' ); ?></p>
	</form>
	<?php
}

/**
 * Render the contact form.
 */
function brightnest_contact_form() {
	$redirect = get_permalink();
	?>
	<form class="bn-form" id="bn-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php echo brightnest_form_notice(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<input type="hidden" name="action" value="bn_request">
		<input type="hidden" name="bn_type" value="contact">
		<input type="hidden" name="bn_redirect" value="<?php echo esc_url( $redirect ); ?>">
		<?php wp_nonce_field( 'bn_request', 'bn_nonce' ); ?>
		<div class="bn-hp" aria-hidden="true"><input type="text" name="bn_website" tabindex="-1" autocomplete="off"></div>
		<div class="bn-field-row">
			<div class="bn-field">
				<label for="c_name"><?php esc_html_e( 'Full name', 'brightnest' ); ?> *</label>
				<input type="text" id="c_name" name="bn_name" required autocomplete="name">
			</div>
			<div class="bn-field">
				<label for="c_phone"><?php esc_html_e( 'Phone', 'brightnest' ); ?> *</label>
				<input type="tel" id="c_phone" name="bn_phone" required autocomplete="tel">
			</div>
		</div>
		<div class="bn-field">
			<label for="c_email"><?php esc_html_e( 'Email', 'brightnest' ); ?></label>
			<input type="email" id="c_email" name="bn_email" autocomplete="email">
		</div>
		<div class="bn-field">
			<label for="c_message"><?php esc_html_e( 'Message', 'brightnest' ); ?></label>
			<textarea id="c_message" name="bn_message" rows="5"></textarea>
		</div>
		<button type="submit" class="bn-btn bn-btn-primary"><?php esc_html_e( 'Send message', 'brightnest' ); ?></button>
	</form>
	<?php
}

/**
 * Short callback form (name, phone, service) used on the homepage.
 */
function brightnest_quick_form() {
	$services = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC', 'fields' => 'ids' ) );
	?>
	<form class="bn-qform" id="bn-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php echo brightnest_form_notice(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<input type="hidden" name="action" value="bn_request">
		<input type="hidden" name="bn_type" value="booking">
		<input type="hidden" name="bn_redirect" value="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php wp_nonce_field( 'bn_request', 'bn_nonce' ); ?>
		<div class="bn-hp" aria-hidden="true"><input type="text" name="bn_website" tabindex="-1" autocomplete="off"></div>
		<label class="screen-reader-text" for="bn-q-name"><?php esc_html_e( 'Your name', 'brightnest' ); ?></label>
		<input id="bn-q-name" name="bn_name" required autocomplete="name" placeholder="<?php esc_attr_e( 'Your name', 'brightnest' ); ?>">
		<label class="screen-reader-text" for="bn-q-phone"><?php esc_html_e( 'Mobile number', 'brightnest' ); ?></label>
		<input id="bn-q-phone" name="bn_phone" type="tel" required autocomplete="tel" placeholder="<?php esc_attr_e( 'Mobile number', 'brightnest' ); ?>">
		<label class="screen-reader-text" for="bn-q-service"><?php esc_html_e( 'Service', 'brightnest' ); ?></label>
		<select id="bn-q-service" name="bn_service" required>
			<option value=""><?php esc_html_e( 'Choose a service', 'brightnest' ); ?></option>
			<?php foreach ( $services as $sid ) : ?>
				<option><?php echo esc_html( wp_strip_all_tags( html_entity_decode( get_the_title( $sid ) ) ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="bn-btn bn-btn-primary"><?php esc_html_e( 'Request a call back', 'brightnest' ); ?></button>
	</form>
	<?php
}
