<?php
/**
 * Unfinished booking reminders.
 *
 * Two cases get one reminder email each, after Settings → Booking → "Send the reminder after":
 * 1. Draft: the customer chose options and typed their email on the checkout but never pressed Pay.
 *    The checkout saves a hidden draft (bn_draft); the email link reopens the checkout with
 *    the cart and details filled in.
 * 2. Awaiting payment: the booking was saved and the customer went to Ziina but did not pay.
 *    The email link starts a fresh Ziina payment for the same booking.
 *
 * Runs on WP-Cron every 15 minutes. A booking made later by the same email cancels the draft reminder.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hidden post type for checkout drafts (no admin screen).
 */
function brightnest_register_draft_type() {
	register_post_type(
		'bn_draft',
		array(
			'public'       => false,
			'show_ui'      => false,
			'rewrite'      => false,
			'query_var'    => false,
			'supports'     => array( 'title' ),
			'can_export'   => false,
		)
	);
}
add_action( 'init', 'brightnest_register_draft_type' );

/** Fields a draft keeps (same names as the checkout state). */
function brightnest_draft_fields() {
	return array( 'frequency', 'date', 'time', 'area', 'street', 'building', 'apartment', 'name', 'phone', 'email', 'message', 'address', 'voucher' );
}

/**
 * Draft post ID for a token, or 0.
 *
 * @param string $token Draft token.
 */
function brightnest_find_draft( $token ) {
	$token = preg_replace( '/[^A-Za-z0-9]/', '', (string) $token );
	if ( strlen( $token ) < 20 ) {
		return 0;
	}
	$ids = get_posts(
		array(
			'post_type'      => 'bn_draft',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_bn_token', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $token, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * AJAX: save / update the checkout draft (only once a valid email is typed).
 */
function brightnest_ajax_checkout_draft() {
	check_ajax_referer( 'bn_checkout', 'nonce' );
	if ( '1' !== (string) brightnest_opt( 'remind_on' ) ) {
		wp_send_json_success( array( 'token' => '' ) );
	}
	$service_id = isset( $_POST['service'] ) ? absint( $_POST['service'] ) : 0;
	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! $service_id || 'bn_service' !== get_post_type( $service_id ) || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'skip' ), 400 );
	}

	$token = isset( $_POST['token'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_POST['token'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$id    = brightnest_find_draft( $token );
	if ( ! $id ) {
		// New drafts are limited per visitor so the form cannot be used to send mail in bulk.
		$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$limit = 'bn_draft_ip_' . md5( $ip );
		$count = (int) get_transient( $limit );
		if ( $count >= 5 ) {
			wp_send_json_error( array( 'message' => 'limit' ), 429 );
		}
		set_transient( $limit, $count + 1, HOUR_IN_SECONDS );
		$token = wp_generate_password( 24, false, false );
		$id    = wp_insert_post( array( 'post_type' => 'bn_draft', 'post_status' => 'publish', 'post_title' => $email ) );
		if ( ! $id || is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => 'save' ), 500 );
		}
		update_post_meta( $id, '_bn_token', $token );
	} else {
		wp_update_post( array( 'ID' => $id, 'post_title' => $email ) ); // Bumps the modified time: the reminder waits again.
	}

	$data = array();
	foreach ( brightnest_draft_fields() as $k ) {
		$data[ $k ] = isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : '';
	}
	$data['email'] = $email;
	$data['lat']   = isset( $_POST['lat'] ) ? (float) $_POST['lat'] : 0;
	$data['lng']   = isset( $_POST['lng'] ) ? (float) $_POST['lng'] : 0;
	// Cart: option id => qty, or the hourly choice { hours, pros, materials }.
	$cart = isset( $_POST['cart'] ) ? json_decode( wp_unslash( $_POST['cart'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$clean = array();
	foreach ( is_array( $cart ) ? $cart : array() as $k => $v ) {
		$clean[ sanitize_key( $k ) ] = is_bool( $v ) ? $v : ( null === $v ? null : (int) $v );
	}
	$data['cart'] = $clean;

	update_post_meta( $id, '_bn_service_id', $service_id );
	update_post_meta( $id, '_bn_email', $email );
	update_post_meta( $id, '_bn_lang', brightnest_lang() ); // The reminder goes out in the language they used.
	update_post_meta( $id, '_bn_data', wp_slash( wp_json_encode( $data ) ) );
	wp_send_json_success( array( 'token' => $token ) );
}
add_action( 'wp_ajax_bn_checkout_draft', 'brightnest_ajax_checkout_draft' );
add_action( 'wp_ajax_nopriv_bn_checkout_draft', 'brightnest_ajax_checkout_draft' );

/**
 * The booking was made: its draft is no longer needed.
 *
 * @param string $token Draft token from the checkout.
 */
function brightnest_delete_draft( $token ) {
	$id = brightnest_find_draft( $token );
	if ( $id ) {
		wp_delete_post( $id, true );
	}
}

/**
 * Draft data for the checkout when it is opened from a reminder link (?bn_draft=token).
 *
 * @param int $service_id Service on the checkout.
 * @return array|null
 */
function brightnest_checkout_draft( $service_id ) {
	if ( empty( $_GET['bn_draft'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return null;
	}
	$id = brightnest_find_draft( wp_unslash( $_GET['bn_draft'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
	if ( ! $id || (int) get_post_meta( $id, '_bn_service_id', true ) !== (int) $service_id ) {
		return null;
	}
	$data = json_decode( (string) get_post_meta( $id, '_bn_data', true ), true );
	if ( ! is_array( $data ) ) {
		return null;
	}
	$data['token'] = get_post_meta( $id, '_bn_token', true );
	return $data;
}

/* -------------------------------------------------------------------------
 * Sending
 * ---------------------------------------------------------------------- */

function brightnest_cron_schedules( $s ) {
	$s['bn_15min'] = array( 'interval' => 15 * MINUTE_IN_SECONDS, 'display' => 'Every 15 minutes (BrightNest)' );
	return $s;
}
add_filter( 'cron_schedules', 'brightnest_cron_schedules' );

function brightnest_schedule_reminders() {
	if ( ! wp_next_scheduled( 'brightnest_send_reminders' ) ) {
		wp_schedule_event( time() + 300, 'bn_15min', 'brightnest_send_reminders' );
	}
}
add_action( 'init', 'brightnest_schedule_reminders' );
add_action( 'brightnest_send_reminders', 'brightnest_send_reminders' );

/**
 * Link that restarts the Ziina payment for a booking.
 *
 * @param int $request_id Booking ID.
 */
function brightnest_resume_payment_url( $request_id ) {
	return add_query_arg( array( 'bn_resume' => (int) $request_id, 'k' => substr( wp_hash( 'bn_resume|' . (int) $request_id ), 0, 20 ) ), home_url( '/' ) );
}

/**
 * Send one reminder email. Placeholders: {name} {service} {link} {phone} {site}.
 *
 * @return bool
 */
function brightnest_send_reminder_email( $email, $name, $service, $link ) {
	$vars = array(
		'{name}'    => $name ? $name : __( 'there', 'brightnest' ),
		'{service}' => $service,
		'{link}'    => $link,
		'{phone}'   => (string) brightnest_opt( 'phone' ),
		'{site}'    => get_bloginfo( 'name' ),
	);
	$subject = strtr( (string) brightnest_opt( 'remind_subject' ), $vars );
	$body    = strtr( (string) brightnest_opt( 'remind_body' ), $vars );
	if ( false === strpos( (string) brightnest_opt( 'remind_body' ), '{link}' ) ) {
		$body .= "\n\n" . $link; // The link is the point of the email; never lose it.
	}
	return wp_mail( $email, wp_strip_all_tags( $subject ), $body );
}

/**
 * Run $fn as if on the Arabic or English site (texts, service names, links).
 *
 * @param string   $lang ar|en.
 * @param callable $fn   Work to do.
 */
function brightnest_in_lang( $lang, $fn ) {
	$old               = brightnest_lang();
	$GLOBALS['bn_lang'] = 'ar' === $lang ? 'ar' : 'en';
	try {
		return $fn();
	} finally {
		$GLOBALS['bn_lang'] = $old;
	}
}

/**
 * At most one reminder per email address per day.
 */
function brightnest_reminder_allowed( $email ) {
	$key = 'bn_reminded_' . md5( strtolower( $email ) );
	if ( get_transient( $key ) ) {
		return false;
	}
	set_transient( $key, 1, DAY_IN_SECONDS );
	return true;
}

/**
 * Has this email booked since $since (unix time)? Then there is nothing to remind about.
 */
function brightnest_booked_since( $email, $since ) {
	return (bool) get_posts(
		array(
			'post_type'      => 'bn_request',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_bn_email', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery
			'date_query'     => array( array( 'after' => gmdate( 'Y-m-d H:i:s', $since ), 'column' => 'post_date_gmt' ) ),
		)
	);
}

/**
 * Cron: send due reminders and tidy old drafts.
 *
 * @return array Counts, for tests: [ drafts, payments ].
 */
function brightnest_send_reminders() {
	$sent = array( 'drafts' => 0, 'payments' => 0 );
	if ( '1' !== (string) brightnest_opt( 'remind_on' ) ) {
		return $sent;
	}
	$due  = gmdate( 'Y-m-d H:i:s', time() - max( 1, (int) brightnest_opt( 'remind_hours' ) ) * HOUR_IN_SECONDS );
	$week = gmdate( 'Y-m-d H:i:s', time() - 7 * DAY_IN_SECONDS );

	// 1. Checkout drafts.
	$drafts = get_posts(
		array(
			'post_type'      => 'bn_draft',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'meta_query'     => array( array( 'key' => '_bn_reminded', 'compare' => 'NOT EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'date_query'     => array( array( 'before' => $due, 'after' => $week, 'column' => 'post_modified_gmt' ) ),
		)
	);
	foreach ( $drafts as $d ) {
		update_post_meta( $d->ID, '_bn_reminded', time() ); // First, so a failing mail is never retried in a loop.
		$email      = get_post_meta( $d->ID, '_bn_email', true );
		$service_id = (int) get_post_meta( $d->ID, '_bn_service_id', true );
		$data       = json_decode( (string) get_post_meta( $d->ID, '_bn_data', true ), true );
		if ( ! is_email( $email ) || 'bn_service' !== get_post_type( $service_id ) || ! brightnest_service_active( $service_id ) ) {
			continue;
		}
		if ( brightnest_booked_since( $email, strtotime( $d->post_date_gmt . ' UTC' ) ) || ! brightnest_reminder_allowed( $email ) ) {
			continue;
		}
		$name = is_array( $data ) && ! empty( $data['name'] ) ? $data['name'] : '';
		$ok   = brightnest_in_lang(
			get_post_meta( $d->ID, '_bn_lang', true ),
			function () use ( $d, $email, $name, $service_id ) {
				$link = brightnest_checkout_url( $service_id, array( 'bn_draft' => get_post_meta( $d->ID, '_bn_token', true ) ) );
				$link = brightnest_lang_url( $link, brightnest_lang() );
				return brightnest_send_reminder_email( $email, $name, wp_strip_all_tags( html_entity_decode( get_the_title( $service_id ), ENT_QUOTES, 'UTF-8' ) ), $link );
			}
		);
		if ( $ok ) {
			$sent['drafts']++;
		}
	}

	// 2. Bookings that went to Ziina but were not paid.
	$pending = get_posts(
		array(
			'post_type'      => 'bn_request',
			'posts_per_page' => 20,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array( 'key' => '_bn_payment_status', 'value' => 'pending' ),
				array( 'key' => '_bn_reminded', 'compare' => 'NOT EXISTS' ),
			),
			'date_query'     => array( array( 'before' => $due, 'after' => $week, 'column' => 'post_date_gmt' ) ),
		)
	);
	foreach ( $pending as $r ) {
		update_post_meta( $r->ID, '_bn_reminded', time() );
		$email = get_post_meta( $r->ID, '_bn_email', true );
		// A booking day that has passed cannot be paid for any more.
		if ( ! is_email( $email ) || get_post_meta( $r->ID, '_bn_date', true ) < wp_date( 'Y-m-d' ) ) {
			continue;
		}
		if ( brightnest_booked_since( $email, strtotime( $r->post_date_gmt . ' UTC' ) + 1 ) || ! brightnest_reminder_allowed( $email ) ) {
			continue;
		}
		$ok = brightnest_in_lang(
			get_post_meta( $r->ID, '_bn_lang', true ),
			function () use ( $r, $email ) {
				return brightnest_send_reminder_email( $email, get_post_meta( $r->ID, '_bn_name', true ), get_post_meta( $r->ID, '_bn_service', true ), brightnest_resume_payment_url( $r->ID ) );
			}
		);
		if ( $ok ) {
			$sent['payments']++;
		}
	}

	// Drafts older than two weeks are of no use.
	$old = get_posts(
		array(
			'post_type'      => 'bn_draft',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'fields'         => 'ids',
			'date_query'     => array( array( 'before' => gmdate( 'Y-m-d H:i:s', time() - 14 * DAY_IN_SECONDS ), 'column' => 'post_modified_gmt' ) ),
		)
	);
	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
	return $sent;
}

/**
 * Reminder link for an unpaid booking: start a fresh Ziina payment, or reopen the checkout.
 */
function brightnest_resume_payment() {
	if ( empty( $_GET['bn_resume'] ) || empty( $_GET['k'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$id = absint( $_GET['bn_resume'] ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'bn_request' !== get_post_type( $id ) || ! hash_equals( substr( wp_hash( 'bn_resume|' . $id ), 0, 20 ), sanitize_text_field( wp_unslash( $_GET['k'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( get_post_type_archive_link( 'bn_service' ) );
		exit;
	}
	$order      = function_exists( 'wc_get_order' ) ? wc_get_order( (int) get_post_meta( $id, '_bn_order_id', true ) ) : null;
	$service_id = $order ? (int) $order->get_meta( '_bn_service_id' ) : 0;
	$fallback   = $service_id ? brightnest_checkout_url( $service_id ) : get_post_type_archive_link( 'bn_service' );

	if ( $order && 'paid' === get_post_meta( $id, '_bn_payment_status', true ) ) {
		wp_safe_redirect( brightnest_order_received_url( '', $order ) ); // Already paid: show the confirmation.
		exit;
	}
	$gateway = function_exists( 'brightnest_ziina_gateway' ) ? brightnest_ziina_gateway() : null;
	if ( $order && $gateway && $order->needs_payment() && get_post_meta( $id, '_bn_date', true ) >= wp_date( 'Y-m-d' ) ) {
		try {
			$result = $gateway->process_payment( $order->get_id() );
		} catch ( Exception $e ) {
			$result = array();
		}
		if ( ! empty( $result['redirect'] ) ) {
			wp_redirect( $result['redirect'] ); // phpcs:ignore WordPress.Security.SafeRedirect -- Ziina's hosted payment page.
			exit;
		}
	}
	wp_safe_redirect( $fallback );
	exit;
}
add_action( 'template_redirect', 'brightnest_resume_payment', 1 );
