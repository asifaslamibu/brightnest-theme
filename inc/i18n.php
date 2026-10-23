<?php
/**
 * Arabic version of the site (like justlife.com/ar-AE).
 *
 * - Arabic pages live under /ar/ (e.g. /ar/services/). The prefix is removed from the request
 *   before WordPress reads it, and added back to every front-end link through home_url().
 * - Arabic turns on the "ar" locale (theme strings from languages/ar.l10n.php) and right-to-left.
 * - Content is translated in the admin: an "Arabic" field next to each text setting, an Arabic tab
 *   in the service editor, Arabic names for categories, an "Arabic version" box on pages and posts.
 *   Anything left empty in Arabic shows the English text.
 * - The admin, AJAX bookings and stored records stay in English; AJAX only switches the messages.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Path of the site home, e.g. "/brightnestclean/".
 */
function brightnest_home_path() {
	$path = (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH );
	return trailingslashit( '/' . ltrim( $path, '/' ) );
}

/**
 * Work out the language once, before WordPress parses the request.
 */
function brightnest_detect_lang() {
	$GLOBALS['bn_lang']        = 'en';
	$GLOBALS['bn_request_uri'] = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( defined( 'WP_CLI' ) || wp_doing_cron() ) {
		return;
	}
	$gt = class_exists( 'GTranslate' ); // GTranslate does the Arabic (inc/gtranslate.php).
	if ( wp_doing_ajax() ) {
		if ( $gt ) {
			return;
		}
		// Our checkout calls come from an /ar/ page: answer in Arabic (messages, emails).
		$ref = isset( $_SERVER['HTTP_REFERER'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['HTTP_REFERER'] ), PHP_URL_PATH ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ( isset( $_REQUEST['bn_lang'] ) && 'ar' === $_REQUEST['bn_lang'] ) || 0 === strpos( $ref, brightnest_home_path() . 'ar/' ) || brightnest_home_path() . 'ar' === rtrim( $ref, '/' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$GLOBALS['bn_lang'] = 'ar';
		}
		return;
	}
	if ( is_admin() ) {
		return;
	}
	$base = brightnest_home_path();
	$uri  = $GLOBALS['bn_request_uri'];
	if ( 0 === strpos( $uri, $base . 'ar/' ) || 0 === strpos( $uri, $base . 'ar?' ) || $base . 'ar' === $uri ) {
		$rest                  = (string) substr( $uri, strlen( $base . 'ar' ) );
		$_SERVER['REQUEST_URI'] = $base . ltrim( $rest, '/' );
		if ( $gt ) {
			$GLOBALS['bn_ar_redirect'] = $_SERVER['REQUEST_URI']; // The English page (brightnest_gt_ar_redirect()).
		} else {
			$GLOBALS['bn_lang'] = 'ar';
		}
	}
}
brightnest_detect_lang();

function brightnest_lang() {
	return isset( $GLOBALS['bn_lang'] ) ? $GLOBALS['bn_lang'] : 'en';
}

function brightnest_is_ar() {
	return 'ar' === brightnest_lang();
}

/** Arabic content is shown on Arabic front-end pages (not in the admin or AJAX). */
function brightnest_show_ar() {
	return brightnest_is_ar() && ! is_admin();
}

/* Locale + direction -------------------------------------------------------- */

function brightnest_ar_locale( $locale ) {
	return brightnest_is_ar() ? 'ar' : $locale;
}
add_filter( 'determine_locale', 'brightnest_ar_locale', 99 );
add_filter( 'locale', 'brightnest_ar_locale', 99 );

function brightnest_ar_direction() {
	if ( brightnest_is_ar() && isset( $GLOBALS['wp_locale'] ) ) {
		$GLOBALS['wp_locale']->text_direction = 'rtl';
	}
}
add_action( 'after_setup_theme', 'brightnest_ar_direction', 0 );
add_action( 'init', 'brightnest_ar_direction', 0 );

/* Links ------------------------------------------------------------------- */

/**
 * Add or remove the /ar prefix on a front-end URL.
 *
 * @param string $url  Site URL.
 * @param string $lang ar|en.
 */
function brightnest_lang_url( $url, $lang ) {
	remove_filter( 'home_url', 'brightnest_ar_home_url', 10 );
	$home = untrailingslashit( home_url() );
	add_filter( 'home_url', 'brightnest_ar_home_url', 10, 2 );
	$rest = 0 === strpos( $url, $home ) ? substr( $url, strlen( $home ) ) : null;
	if ( null === $rest ) {
		return $url;
	}
	$rest = preg_replace( '#^/ar(?=/|\?|$)#', '', $rest );
	return $home . ( 'ar' === $lang ? '/ar' : '' ) . ( '' === $rest ? '/' : $rest );
}

function brightnest_ar_home_url( $url, $path = '' ) {
	// Not while WordPress reads the request: it compares the address with home_url().
	if ( ! brightnest_show_ar() || ! did_action( 'parse_request' ) || preg_match( '#^/?(wp-json|wp-admin|wp-login|wc-api|feed)#', (string) $path ) || false !== strpos( $url, 'rest_route=' ) ) {
		return $url;
	}
	return brightnest_lang_url( $url, 'ar' );
}
add_filter( 'home_url', 'brightnest_ar_home_url', 10, 2 );

/**
 * The page the visitor is on, in the other language (header switch).
 */
function brightnest_switch_url() {
	$scheme = is_ssl() ? 'https://' : 'http://';
	$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$url    = $scheme . $host . $GLOBALS['bn_request_uri'];
	return brightnest_lang_url( $url, brightnest_is_ar() ? 'en' : 'ar' );
}

// WordPress would "correct" /ar/… back to the English address in a loop.
function brightnest_ar_no_canonical( $redirect ) {
	return brightnest_is_ar() ? false : $redirect;
}
add_filter( 'redirect_canonical', 'brightnest_ar_no_canonical' );

/**
 * Tell search engines about both languages.
 */
function brightnest_hreflang() {
	if ( is_admin() || is_404() || class_exists( 'GTranslate' ) ) {
		return;
	}
	$scheme = is_ssl() ? 'https://' : 'http://';
	$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$here   = $scheme . $host . $GLOBALS['bn_request_uri'];
	printf( "<link rel=\"alternate\" hreflang=\"en\" href=\"%s\">\n", esc_url( brightnest_lang_url( $here, 'en' ) ) );
	printf( "<link rel=\"alternate\" hreflang=\"ar\" href=\"%s\">\n", esc_url( brightnest_lang_url( $here, 'ar' ) ) );
}
add_action( 'wp_head', 'brightnest_hreflang', 2 );

/* Translatable settings ----------------------------------------------------- */

/**
 * Setting keys whose text customers read (each gets an Arabic twin "key_ar").
 */
function brightnest_i18n_keys() {
	static $keys = null;
	if ( null === $keys ) {
		$skip = array( 'currency', 'cities', 'phone', 'phone2', 'whatsapp', 'email', 'booking_email', 'promo_code', 'greviews_name', 'greviews_embed', 'greviews_write_url', 'offer_code', 'offer_link', 'offer_style', 'offer_days', 'vouchers', 'country_code', 'facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok', 'slot_start', 'slot_end', 'lead_hours', 'days_ahead', 'slot_length', 'pay_online', 'remind_on', 'remind_hours', 'service_fee', 'min_amount', 'area_fees' );
		$keys = array();
		foreach ( brightnest_defaults() as $k => $v ) {
			if ( is_string( $v ) && ! in_array( $k, $skip, true ) && 0 !== strpos( $k, 'show_' ) && ! preg_match( '/(_image|_img_\d|^hero_image)$/', $k ) && ! preg_match( '/^stat_\d_num$/', $k ) ) {
				$keys[] = $k;
			}
		}
	}
	return $keys;
}

/**
 * Arabic text for a setting, or null to use the English one.
 */
function brightnest_opt_ar( $key ) {
	if ( ! brightnest_is_ar() || ! in_array( $key, brightnest_i18n_keys(), true ) ) {
		return null;
	}
	$ar = get_theme_mod( 'bn_' . $key . '_ar', '' );
	return is_string( $ar ) && '' !== trim( $ar ) ? $ar : null;
}

/**
 * City name for display (cities stay English inside, for the map and prices).
 */
function brightnest_city_label( $city ) {
	if ( ! brightnest_is_ar() ) {
		return $city;
	}
	// Arabic name set in BrightNest → Cities & Areas, else the old Settings list.
	if ( function_exists( 'brightnest_area_label' ) ) {
		$label = brightnest_area_label( $city );
		if ( $label !== $city ) {
			return $label;
		}
	}
	$en = brightnest_cities();
	$ar = array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_theme_mod( 'bn_cities_ar', '' ) ) );
	$i  = array_search( $city, $en, true );
	return false !== $i && ! empty( $ar[ $i ] ) ? $ar[ $i ] : $city;
}

/* Posts, pages, services ---------------------------------------------------- */

function brightnest_ar_meta( $post_id, $key ) {
	$v = get_post_meta( $post_id, '_bn_ar_' . $key, true );
	return is_string( $v ) && '' !== trim( $v ) ? $v : null;
}

function brightnest_ar_the_title( $title, $post_id = 0 ) {
	if ( brightnest_show_ar() && $post_id ) {
		$ar = brightnest_ar_meta( $post_id, 'title' );
		if ( null !== $ar ) {
			return esc_html( $ar );
		}
	}
	return $title;
}
add_filter( 'the_title', 'brightnest_ar_the_title', 5, 2 );

function brightnest_ar_excerpt( $excerpt, $post = null ) {
	$post = get_post( $post );
	if ( brightnest_show_ar() && $post ) {
		$ar = brightnest_ar_meta( $post->ID, 'excerpt' );
		if ( null !== $ar ) {
			return $ar;
		}
	}
	return $excerpt;
}
add_filter( 'get_the_excerpt', 'brightnest_ar_excerpt', 5, 2 );

function brightnest_ar_content( $content ) {
	$post = get_post();
	if ( brightnest_show_ar() && $post ) {
		$ar = brightnest_ar_meta( $post->ID, 'content' );
		if ( null !== $ar ) {
			// Run the Arabic text through the usual content formatting once.
			remove_filter( 'the_content', 'brightnest_ar_content', 1 );
			$out = apply_filters( 'the_content', $ar );
			add_filter( 'the_content', 'brightnest_ar_content', 1 );
			return $out;
		}
	}
	return $content;
}
add_filter( 'the_content', 'brightnest_ar_content', 1 );

/* Categories ---------------------------------------------------------------- */

function brightnest_ar_term( $term ) {
	if ( brightnest_show_ar() && $term instanceof WP_Term && 'bn_service_cat' === $term->taxonomy ) {
		$name = get_term_meta( $term->term_id, 'bn_name_ar', true );
		$desc = get_term_meta( $term->term_id, 'bn_desc_ar', true );
		if ( $name ) {
			$term->name = $name;
		}
		if ( $desc ) {
			$term->description = $desc;
		}
	}
	return $term;
}
add_filter( 'get_term', 'brightnest_ar_term', 10 );

function brightnest_ar_terms( $terms ) {
	return is_array( $terms ) ? array_map( function ( $t ) { return $t instanceof WP_Term ? brightnest_ar_term( $t ) : $t; }, $terms ) : $terms;
}
add_filter( 'get_terms', 'brightnest_ar_terms', 10 );
add_filter( 'get_the_terms', 'brightnest_ar_terms', 10 );
add_filter( 'get_object_terms', 'brightnest_ar_terms', 10 );

/* Menus: custom links get an Arabic label field ---------------------------- */

function brightnest_ar_menu_item( $item ) {
	if ( brightnest_show_ar() && isset( $item->ID ) ) {
		$ar = get_post_meta( $item->ID, '_bn_ar_label', true );
		if ( $ar ) {
			$item->title = $ar;
		} elseif ( 'custom' === $item->type ) {
			$item->title = translate( $item->title, 'brightnest' ); // "Home", "Services"… from the theme translation.
		}
	}
	return $item;
}
add_filter( 'wp_setup_nav_menu_item', 'brightnest_ar_menu_item', 20 );

function brightnest_ar_menu_field( $item_id ) {
	printf(
		'<p class="description description-wide"><label>%1$s<br><input type="text" class="widefat" dir="rtl" name="bn_ar_label[%2$d]" value="%3$s"></label></p>',
		esc_html__( 'Arabic label (العربية)', 'brightnest' ),
		(int) $item_id,
		esc_attr( get_post_meta( $item_id, '_bn_ar_label', true ) )
	);
}
add_action( 'wp_nav_menu_item_custom_fields', 'brightnest_ar_menu_field' );

function brightnest_ar_menu_save( $menu_id, $item_id ) {
	if ( isset( $_POST['bn_ar_label'][ $item_id ] ) && current_user_can( 'edit_theme_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- core checks the menu nonce.
		update_post_meta( $item_id, '_bn_ar_label', sanitize_text_field( wp_unslash( $_POST['bn_ar_label'][ $item_id ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
}
add_action( 'wp_update_nav_menu_item', 'brightnest_ar_menu_save', 10, 2 );

/* "Arabic version" box on pages, posts and reviews ------------------------- */

function brightnest_ar_meta_box() {
	foreach ( array( 'page', 'post', 'bn_review' ) as $type ) {
		add_meta_box( 'bn-ar-version', __( 'Arabic version (العربية)', 'brightnest' ), 'brightnest_ar_meta_box_html', $type, 'normal', 'low' );
	}
}
add_action( 'add_meta_boxes', 'brightnest_ar_meta_box' );

function brightnest_ar_meta_box_html( $post ) {
	wp_nonce_field( 'bn_ar_save', 'bn_ar_nonce' );
	echo '<p class="description">' . esc_html__( 'Shown on the Arabic site (/ar/). Leave empty to show the English text there.', 'brightnest' ) . '</p>';
	echo '<p><label><strong>' . esc_html__( 'Arabic title', 'brightnest' ) . '</strong><br><input type="text" class="widefat" dir="rtl" name="bn_ar[title]" value="' . esc_attr( get_post_meta( $post->ID, '_bn_ar_title', true ) ) . '"></label></p>';
	echo '<p><strong>' . esc_html__( 'Arabic content', 'brightnest' ) . '</strong></p>';
	wp_editor( (string) get_post_meta( $post->ID, '_bn_ar_content', true ), 'bn_ar_content', array( 'textarea_name' => 'bn_ar[content]', 'textarea_rows' => 10, 'media_buttons' => true ) );
}

function brightnest_ar_meta_save( $post_id ) {
	if ( ! isset( $_POST['bn_ar_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bn_ar_nonce'] ) ), 'bn_ar_save' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	$in = isset( $_POST['bn_ar'] ) && is_array( $_POST['bn_ar'] ) ? wp_unslash( $_POST['bn_ar'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	update_post_meta( $post_id, '_bn_ar_title', isset( $in['title'] ) ? sanitize_text_field( $in['title'] ) : '' );
	update_post_meta( $post_id, '_bn_ar_content', isset( $in['content'] ) ? wp_kses_post( $in['content'] ) : '' );
}
add_action( 'save_post', 'brightnest_ar_meta_save' );

/* Category Arabic name ------------------------------------------------------ */

function brightnest_ar_term_fields( $term ) {
	$name = $term instanceof WP_Term ? get_term_meta( $term->term_id, 'bn_name_ar', true ) : '';
	$desc = $term instanceof WP_Term ? get_term_meta( $term->term_id, 'bn_desc_ar', true ) : '';
	echo '<tr class="form-field"><th scope="row"><label for="bn-name-ar">' . esc_html__( 'Arabic name (العربية)', 'brightnest' ) . '</label></th><td><input id="bn-name-ar" dir="rtl" name="bn_name_ar" value="' . esc_attr( $name ) . '"></td></tr>';
	echo '<tr class="form-field"><th scope="row"><label for="bn-desc-ar">' . esc_html__( 'Arabic description', 'brightnest' ) . '</label></th><td><textarea id="bn-desc-ar" dir="rtl" name="bn_desc_ar" rows="3">' . esc_textarea( $desc ) . '</textarea></td></tr>';
}
add_action( 'bn_service_cat_edit_form_fields', 'brightnest_ar_term_fields' );

function brightnest_ar_term_save( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) || ! isset( $_POST['bn_name_ar'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- core checks the term nonce.
		return;
	}
	update_term_meta( $term_id, 'bn_name_ar', sanitize_text_field( wp_unslash( $_POST['bn_name_ar'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	update_term_meta( $term_id, 'bn_desc_ar', isset( $_POST['bn_desc_ar'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bn_desc_ar'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification
}
add_action( 'edited_bn_service_cat', 'brightnest_ar_term_save' );

/* Script strings ------------------------------------------------------------ */

/**
 * Words the scripts show (checkout, location picker). English → current language.
 */
function brightnest_js_strings() {
	$s = array(
		'Service Details', 'Date & Time', 'Address', 'Payment', 'Service', 'When', 'Total', 'Change', 'Continue', 'Pay securely', 'Confirm booking',
		'%d hour', '%d hours', '%d professional', '%d professionals', '%d item · Total', '%d items · Total', 'Add for %s', 'Update for %s',
		'Today, %s', 'Tomorrow, %s', '%1$s, %2$s', 'Today', 'Tmrw', 'Fully booked', 'Morning', 'Afternoon', 'Evening', 'AM', 'PM',
		'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
		'Voucher %s applied.', 'This voucher code is not valid.', 'Checking…', 'Booking…', 'Redirecting to secure payment…', 'Something went wrong. Please try again.',
		'This service is not available in the selected area.', 'The minimum booking amount is %s.', 'Choose how many hours you need.', 'Choose how many professionals you need.',
		'Tell us if you need cleaning materials.', 'Tap "Add" on at least one option.', 'Pick a time slot that suits you.', 'Please enter your area or street.',
		'Please enter the building or villa name.', 'Please enter the apartment or villa number.', 'Please enter your name.', 'Please enter a valid mobile number.',
		'This email does not look right.', 'Sorry, this service is not available right now.', 'Sorry, %1$s is not available in %2$s yet. Please choose another area.',
		'%s (change)', 'Location pinned', 'Pin your location on the map', 'Hi, I just booked %1$s (#%2$s) for %3$s, %4$s.',
		'Payment for booking #%s was not completed. Please try again or choose another payment method.', 'Not available in %s', 'Error', 'OK',
		'We do not currently operate in this area.', 'We could not check this location. Please check your connection and try again.',
	);
	$out = array();
	foreach ( $s as $en ) {
		$out[ $en ] = translate( $en, 'brightnest' ); // phpcs:ignore WordPress.WP.I18n
	}
	return $out;
}

/**
 * Shown city names, keyed by the English name.
 */
function brightnest_city_labels() {
	$out = array();
	foreach ( brightnest_cities() as $c ) {
		$out[ $c ] = brightnest_city_label( $c );
	}
	return $out;
}

/**
 * Hand the script words to the page (before main.js, which the other theme scripts load after).
 */
function brightnest_js_i18n() {
	wp_add_inline_script( 'brightnest-main','window.BN_I18N = ' . wp_json_encode( brightnest_is_ar() ? brightnest_js_strings() : new stdClass() ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'brightnest_js_i18n', 100 );
