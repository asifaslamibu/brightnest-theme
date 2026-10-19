<?php
/**
 * BrightNest → Settings: one admin page (tabs) for everything the owner edits.
 * Values are stored as theme mods (bn_*), so the Customizer keeps working too.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings schema: tab => [ label, icon, sections => [ title => [ key => [ type, label, help ] ] ] ].
 */
function brightnest_settings_schema() {
	return array(
		'business' => array(
			__( 'Business', 'brightnest' ),
			'store',
			array(
				__( 'Contact details', 'brightnest' ) => array(
					'phone'    => array( 'text', __( 'Main phone', 'brightnest' ), __( 'Shown in the header, footer and booking pages.', 'brightnest' ) ),
					'phone2'   => array( 'text', __( 'Second phone (optional)', 'brightnest' ), '' ),
					'whatsapp' => array( 'text', __( 'WhatsApp number', 'brightnest' ), __( 'Digits only with country code, e.g. 971569395733.', 'brightnest' ) ),
					'email'    => array( 'email', __( 'Public email', 'brightnest' ), '' ),
					'address'  => array( 'text', __( 'Address', 'brightnest' ), '' ),
					'hours'    => array( 'text', __( 'Working hours', 'brightnest' ), '' ),
				),
				__( 'Region', 'brightnest' ) => array(
					'cities'   => array( 'textarea', __( 'Cities / areas you serve', 'brightnest' ), __( 'One per line. Customers pick one at checkout.', 'brightnest' ) ),
					'country_code' => array( 'text', __( 'Country code (2 letters)', 'brightnest' ), __( 'e.g. AE for UAE. Map search and pinned locations are limited to this country.', 'brightnest' ) ),
					'currency' => array( 'text', __( 'Currency', 'brightnest' ), __( 'e.g. AED', 'brightnest' ) ),
					'map_lat'  => array( 'text', __( 'Map start latitude', 'brightnest' ), '' ),
					'map_lng'  => array( 'text', __( 'Map start longitude', 'brightnest' ), '' ),
					'google_maps_key' => array( 'text', __( 'Google Maps API key', 'brightnest' ), __( 'With a key the location picker uses Google Maps (map, search and addresses). In Google Cloud enable "Maps JavaScript API", "Geocoding API" and "Places API (New)", and limit the key to your website address. Leave empty to use the free OpenStreetMap map.', 'brightnest' ) ),
				),
				__( 'Footer', 'brightnest' ) => array(
					'footer_about' => array( 'textarea', __( 'Short company description', 'brightnest' ), '' ),
				),
			),
		),
		'booking'  => array(
			__( 'Booking', 'brightnest' ),
			'calendar-alt',
			array(
				__( 'Time slots', 'brightnest' ) => array(
					'slot_start' => array( 'hour', __( 'First slot starts at', 'brightnest' ), '' ),
					'slot_end'   => array( 'hour', __( 'Last slot ends at', 'brightnest' ), '' ),
					'lead_hours' => array( 'number', __( 'Same-day notice (hours)', 'brightnest' ), __( 'Slots earlier than this many hours from now are hidden.', 'brightnest' ) ),
					'days_ahead' => array( 'number', __( 'Days customers can book ahead', 'brightnest' ), '' ),
					'slot_length' => array( 'choice', __( 'Length of each time slot', 'brightnest' ), __( 'Customers pick an arrival window of this length.', 'brightnest' ), array( '30' => __( '30 minutes', 'brightnest' ), '60' => __( '1 hour', 'brightnest' ), '90' => __( '1.5 hours', 'brightnest' ), '120' => __( '2 hours', 'brightnest' ) ) ),
				),
				__( 'Area charges', 'brightnest' ) => array(
					'area_fees'       => array( 'areafees', __( 'Extra charge per area (added to every booking)', 'brightnest' ), __( 'E.g. a transport charge for areas far from your base. 0 = no charge. Areas come from Business → Cities / areas you serve. Per-service price changes are in each service → Areas & prices.', 'brightnest' ) ),
					'area_fee_label'  => array( 'text', __( 'Area charge — name on the bill', 'brightnest' ), '' ),
				),
				__( 'Prices & checkout', 'brightnest' ) => array(
					'service_fee'       => array( 'money', __( 'Service fee per booking', 'brightnest' ), __( '0 = shown as "Free".', 'brightnest' ) ),
					'service_fee_label' => array( 'text', __( 'Service fee — name', 'brightnest' ), '' ),
					'min_amount'        => array( 'money', __( 'Minimum booking amount', 'brightnest' ), __( 'Ziina needs at least 2.', 'brightnest' ) ),
					'checkout_why'      => array( 'textarea', __( '"Why choose us" box on the checkout', 'brightnest' ), __( 'One point per line.', 'brightnest' ) ),
				),
				__( 'Payment (Ziina — card, Apple Pay, Google Pay)', 'brightnest' ) => array(
					'pay_online_label' => array( 'text', __( 'Online — title', 'brightnest' ), '' ),
					'pay_online_text'  => array( 'text', __( 'Online — description', 'brightnest' ), '' ),
					'booking_note'     => array( 'textarea', __( 'Note under the pay button', 'brightnest' ), '' ),
				),
				__( 'Unfinished booking reminder', 'brightnest' ) => array(
					'remind_on'      => array( 'toggle', __( 'Email customers who did not finish their booking', 'brightnest' ), __( 'One email, when a customer filled in the checkout (with their email) but did not pay, or went to the payment page and did not pay. The link takes them straight back.', 'brightnest' ) ),
					'remind_hours'   => array( 'number', __( 'Send the reminder after (hours)', 'brightnest' ), __( 'Hours after the customer left the checkout. 1 to 48.', 'brightnest' ) ),
					'remind_subject' => array( 'text', __( 'Reminder subject', 'brightnest' ), __( 'You can use {name}, {service}, {site}.', 'brightnest' ) ),
					'remind_body'    => array( 'textarea', __( 'Reminder text', 'brightnest' ), __( 'You can use {name}, {service}, {link} (the button back to the booking), {phone}, {site}.', 'brightnest' ) ),
				),
				__( 'Notifications & vouchers', 'brightnest' ) => array(
					'booking_email' => array( 'email', __( 'Send new bookings to', 'brightnest' ), __( 'Leave empty to use the site admin email.', 'brightnest' ) ),
					'vouchers'      => array( 'textarea', __( 'Voucher codes', 'brightnest' ), __( 'One per line: CODE:20% (percent) or CODE:50 (fixed amount).', 'brightnest' ) ),
				),
			),
		),
		'homepage' => array(
			__( 'Homepage', 'brightnest' ),
			'admin-home',
			array(
				__( 'Hero', 'brightnest' ) => array(
					'hero_title'   => array( 'text', __( 'Headline', 'brightnest' ), __( 'Put | before the words to highlight in orange, e.g. "Cleaning in |Dubai".', 'brightnest' ) ),
					'loc_question' => array( 'text', __( 'Location question', 'brightnest' ), __( 'Shown above the "Set my location" field.', 'brightnest' ) ),
					'hero_img_1'   => array( 'image', __( 'Hero photo', 'brightnest' ), '' ),
				),
				__( 'Sections on/off', 'brightnest' ) => array(
					'show_banner'   => array( 'toggle', __( 'Photo banner', 'brightnest' ), '' ),
					'show_services' => array( 'toggle', __( 'Top home services', 'brightnest' ), '' ),
					'show_why'      => array( 'toggle', __( 'Reasons to love us', 'brightnest' ), '' ),
					'show_promise'  => array( 'toggle', __( 'Our promise', 'brightnest' ), '' ),
					'show_app'      => array( 'toggle', __( 'Book on WhatsApp', 'brightnest' ), '' ),
					'show_areas'    => array( 'toggle', __( 'Service areas & locations', 'brightnest' ), '' ),
				),
				__( 'Photo banner', 'brightnest' ) => array(
					'banner_title' => array( 'text', __( 'Banner heading', 'brightnest' ), '' ),
					'banner_text'   => array( 'textarea', __( 'Banner text', 'brightnest' ), '' ),
					'banner_points' => array( 'textarea', __( 'Banner ticks', 'brightnest' ), __( 'One per line. Leave empty to hide.', 'brightnest' ) ),
					'why_image'    => array( 'image', __( 'Banner photo', 'brightnest' ), '' ),
				),
				__( 'Top home services', 'brightnest' ) => array(
					'top_title' => array( 'text', __( 'Heading', 'brightnest' ), __( 'Popular services (star in the Services list) are shown first.', 'brightnest' ) ),
				),
				__( 'Reasons to love us', 'brightnest' ) => array(
					'love_title'  => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'love_sub'    => array( 'text', __( 'Sub-heading', 'brightnest' ), '' ),
					'love_points' => array( 'textarea', __( 'Reasons', 'brightnest' ), __( 'Four lines: Title|Description', 'brightnest' ) ),
				),
				__( 'Our promise', 'brightnest' ) => array(
					'promise_title' => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'promise_text'  => array( 'textarea', __( 'Text', 'brightnest' ), '' ),
					'promise_close' => array( 'text', __( 'Closing line (bold)', 'brightnest' ), '' ),
				),
				__( 'Book on WhatsApp', 'brightnest' ) => array(
					'app_title' => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'app_text'  => array( 'textarea', __( 'Text', 'brightnest' ), '' ),
				),
				__( 'Service areas & locations', 'brightnest' ) => array(
					'country' => array( 'text', __( 'Country', 'brightnest' ), __( 'The cities come from Business → Cities / areas you serve.', 'brightnest' ) ),
				),
			),
		),
		'faq'      => array( __( 'FAQ', 'brightnest' ), 'editor-help', array() ),
		'social'   => array(
			__( 'Social & numbers', 'brightnest' ),
			'share',
			array(
				__( 'Social links', 'brightnest' ) => array(
					'facebook'  => array( 'url', 'Facebook', '' ),
					'instagram' => array( 'url', 'Instagram', '' ),
					'linkedin'  => array( 'url', 'LinkedIn', '' ),
					'youtube'   => array( 'url', 'YouTube', '' ),
					'tiktok'    => array( 'url', 'TikTok', '' ),
					'twitter'   => array( 'url', 'X / Twitter', '' ),
				),
				__( 'Hero numbers', 'brightnest' ) => array(
					'stat_1_num'   => array( 'text', __( 'Number 1', 'brightnest' ), __( 'Use real figures only.', 'brightnest' ) ),
					'stat_1_label' => array( 'text', __( 'Label 1', 'brightnest' ), '' ),
					'stat_2_num'   => array( 'text', __( 'Number 2', 'brightnest' ), '' ),
					'stat_2_label' => array( 'text', __( 'Label 2', 'brightnest' ), '' ),
					'stat_3_num'   => array( 'text', __( 'Number 3', 'brightnest' ), '' ),
					'stat_3_label' => array( 'text', __( 'Label 3', 'brightnest' ), '' ),
				),
				__( 'Numbers band', 'brightnest' ) => array(
					'achievements' => array( 'textarea', __( 'Numbers', 'brightnest' ), __( 'One per line: number|label, e.g. 8k|Services provided', 'brightnest' ) ),
				),
				__( 'Promotion', 'brightnest' ) => array(
					'promo_title' => array( 'text', __( 'Offer title', 'brightnest' ), '' ),
					'promo_text'  => array( 'textarea', __( 'Offer text', 'brightnest' ), '' ),
					'promo_code'  => array( 'text', __( 'Code to show', 'brightnest' ), __( 'Must also exist in Booking → Voucher codes.', 'brightnest' ) ),
				),
			),
		),
	);
}

function brightnest_settings_menu() {
	add_submenu_page( 'brightnest', __( 'Settings', 'brightnest' ), __( 'Settings', 'brightnest' ), 'edit_theme_options', 'brightnest-settings', 'brightnest_settings_page' );
}
add_action( 'admin_menu', 'brightnest_settings_menu', 11 );

function brightnest_settings_assets( $hook ) {
	if ( false === strpos( (string) $hook, 'brightnest-settings' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'brightnest-admin', BRIGHTNEST_URI . '/assets/admin/admin.css', array(), BRIGHTNEST_VERSION );
	wp_enqueue_script( 'brightnest-settings', BRIGHTNEST_URI . '/assets/admin/settings.js', array(), BRIGHTNEST_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'brightnest_settings_assets' );

/**
 * Render one field.
 */
function brightnest_settings_field( $key, $f ) {
	list( $type, $label, $help ) = array_slice( $f, 0, 3 );
	$val  = brightnest_opt( $key );
	$name = 'bn[' . $key . ']';
	$id   = 'bn-set-' . $key;
	echo '<div class="bn-set-field bn-set-' . esc_attr( $type ) . '">';
	if ( 'toggle' === $type ) {
		printf(
			'<label class="bn-adm-switch"><input type="hidden" name="%1$s" value="0"><input type="checkbox" name="%1$s" value="1" %2$s><span class="bn-adm-switch-ui" aria-hidden="true"></span><span>%3$s</span></label>',
			esc_attr( $name ),
			checked( '1', (string) $val, false ),
			esc_html( $label )
		);
	} else {
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		switch ( $type ) {
			case 'textarea':
				echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="4">' . esc_textarea( $val ) . '</textarea>';
				break;
			case 'hour':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
				for ( $h = 0; $h <= 24; $h++ ) {
					printf( '<option value="%1$d" %2$s>%3$s</option>', (int) $h, selected( (int) $val, $h, false ), esc_html( 24 === $h ? '12 AM (midnight)' : gmdate( 'g A', $h * 3600 ) ) );
				}
				echo '</select>';
				break;
			case 'choice':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
				foreach ( $f[3] as $opt_val => $opt_label ) {
					printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $opt_val ), selected( (string) $val, (string) $opt_val, false ), esc_html( $opt_label ) );
				}
				echo '</select>';
				break;
			case 'money':
				echo '<input type="number" min="0" step="0.01" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '">';
				break;
			case 'areafees':
				$fees = is_array( $val ) ? $val : array();
				echo '<table class="bn-set-areas" id="' . esc_attr( $id ) . '"><tbody>';
				foreach ( brightnest_cities() as $city ) {
					printf(
						'<tr><th scope="row">%1$s</th><td><label><span>%2$s</span> <input type="number" min="0" step="0.01" name="%3$s" value="%4$s"></label></td></tr>',
						esc_html( $city ),
						esc_html( brightnest_opt( 'currency' ) ),
						esc_attr( $name . '[' . $city . ']' ),
						esc_attr( isset( $fees[ $city ] ) ? $fees[ $city ] : 0 )
					);
				}
				echo '</tbody></table>';
				break;
			case 'image':
				echo '<div class="bn-set-imgbox" data-image>';
				echo '<span class="bn-set-thumb" data-thumb>' . ( $val ? '<img src="' . esc_url( $val ) . '" alt="">' : esc_html__( 'Default photo', 'brightnest' ) ) . '</span>';
				echo '<input type="hidden" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '" data-url>';
				echo '<button type="button" class="button" data-pick>' . esc_html__( 'Choose photo', 'brightnest' ) . '</button> ';
				echo '<button type="button" class="button-link bn-opt-del" data-clear>' . esc_html__( 'Use default', 'brightnest' ) . '</button>';
				echo '</div>';
				break;
			default:
				$input = in_array( $type, array( 'number', 'email', 'url' ), true ) ? $type : 'text';
				echo '<input type="' . esc_attr( $input ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '"' . ( 'number' === $type ? ' min="0" max="60"' : '' ) . '>';
		}
	}
	if ( $help ) {
		echo '<small>' . esc_html( $help ) . '</small>';
	}
	// Arabic twin for text customers read (shown on the /ar/ site; empty = English).
	if ( brightnest_settings_has_ar( $key, $type ) ) {
		$ar_val = (string) get_theme_mod( 'bn_' . $key . '_ar', '' );
		echo '<div class="bn-set-ar"><label for="' . esc_attr( $id ) . '-ar">' . esc_html__( 'Arabic (العربية)', 'brightnest' ) . '</label>';
		if ( 'textarea' === $type ) {
			echo '<textarea dir="rtl" id="' . esc_attr( $id ) . '-ar" name="bn[' . esc_attr( $key ) . '_ar]" rows="4">' . esc_textarea( $ar_val ) . '</textarea>';
		} else {
			echo '<input type="text" dir="rtl" id="' . esc_attr( $id ) . '-ar" name="bn[' . esc_attr( $key ) . '_ar]" value="' . esc_attr( $ar_val ) . '">';
		}
		if ( 'cities' === $key ) {
			echo '<small>' . esc_html__( 'Arabic names in the same order, one per line.', 'brightnest' ) . '</small>';
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Does this setting get an Arabic field?
 */
function brightnest_settings_has_ar( $key, $type ) {
	return in_array( $type, array( 'text', 'textarea' ), true ) && ( 'cities' === $key || in_array( $key, brightnest_i18n_keys(), true ) );
}

function brightnest_settings_page() {
	$schema = brightnest_settings_schema();
	$tab    = isset( $_GET['tab'] ) && isset( $schema[ sanitize_key( $_GET['tab'] ) ] ) ? sanitize_key( $_GET['tab'] ) : 'business'; // phpcs:ignore WordPress.Security.NonceVerification
	$base   = admin_url( 'admin.php?page=brightnest-settings' );
	?>
	<div class="wrap bn-set">
		<h1><?php esc_html_e( 'Website settings', 'brightnest' ); ?></h1>
		<p class="bn-set-intro"><?php esc_html_e( 'Everything customers see — contact details, booking rules, homepage text and photos, FAQs — in one place.', 'brightnest' ); ?></p>
		<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'brightnest' ); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View website', 'brightnest' ); ?> ↗</a></p></div>
		<?php endif; ?>

		<nav class="bn-set-tabs">
			<?php foreach ( $schema as $k => $t ) : ?>
				<a class="<?php echo $k === $tab ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'tab', $k, $base ) ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $t[1] ); ?>"></span> <?php echo esc_html( $t[0] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bn-set-form">
			<input type="hidden" name="action" value="bn_save_settings">
			<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">
			<?php wp_nonce_field( 'bn_save_settings' ); ?>

			<?php if ( 'faq' === $tab ) : ?>
				<section class="bn-set-card">
					<h2><?php esc_html_e( 'Frequently asked questions', 'brightnest' ); ?></h2>
					<p class="bn-adm-help"><?php esc_html_e( 'Shown on every service page and on the FAQ page. Services can add their own questions in the service editor.', 'brightnest' ); ?></p>
					<ol class="bn-faq-edit" data-faq-list>
						<?php foreach ( brightnest_faqs() as $i => $faq ) : ?>
							<li>
								<input name="bn_faqs[<?php echo (int) $i; ?>][0]" value="<?php echo esc_attr( $faq[0] ); ?>" placeholder="<?php esc_attr_e( 'Question', 'brightnest' ); ?>">
								<textarea name="bn_faqs[<?php echo (int) $i; ?>][1]" rows="2" placeholder="<?php esc_attr_e( 'Answer', 'brightnest' ); ?>"><?php echo esc_textarea( $faq[1] ); ?></textarea>
								<?php $bn_far = (array) get_theme_mod( 'bn_faqs_ar', array() ); ?>
								<input dir="rtl" name="bn_faqs[<?php echo (int) $i; ?>][2]" value="<?php echo esc_attr( isset( $bn_far[ $i ][0] ) ? $bn_far[ $i ][0] : '' ); ?>" placeholder="<?php esc_attr_e( 'Arabic question (العربية)', 'brightnest' ); ?>">
								<textarea dir="rtl" name="bn_faqs[<?php echo (int) $i; ?>][3]" rows="2" placeholder="<?php esc_attr_e( 'Arabic answer', 'brightnest' ); ?>"><?php echo esc_textarea( isset( $bn_far[ $i ][1] ) ? $bn_far[ $i ][1] : '' ); ?></textarea>
								<button type="button" class="button-link bn-opt-del" data-faq-del><span class="dashicons dashicons-trash"></span></button>
							</li>
						<?php endforeach; ?>
					</ol>
					<button type="button" class="button" data-faq-add data-name="bn_faqs"><span class="dashicons dashicons-plus"></span> <?php esc_html_e( 'Add question', 'brightnest' ); ?></button>
				</section>
			<?php else : ?>
				<?php foreach ( $schema[ $tab ][2] as $title => $fields ) : ?>
					<section class="bn-set-card">
						<h2><?php echo esc_html( $title ); ?></h2>
						<div class="bn-set-grid">
							<?php foreach ( $fields as $key => $f ) { brightnest_settings_field( $key, $f ); } ?>
						</div>
					</section>
				<?php endforeach; ?>
			<?php endif; ?>

			<div class="bn-set-save">
				<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save changes', 'brightnest' ); ?></button>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Save handler.
 */
function brightnest_save_settings() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'brightnest' ) );
	}
	check_admin_referer( 'bn_save_settings' );
	$schema = brightnest_settings_schema();
	$tab    = isset( $_POST['tab'] ) && isset( $schema[ sanitize_key( $_POST['tab'] ) ] ) ? sanitize_key( $_POST['tab'] ) : 'business';

	if ( 'faq' === $tab ) {
		$faqs = array();
		$ar   = array();
		$raw  = isset( $_POST['bn_faqs'] ) && is_array( $_POST['bn_faqs'] ) ? wp_unslash( $_POST['bn_faqs'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( $raw as $row ) {
			$q = isset( $row[0] ) ? sanitize_text_field( $row[0] ) : '';
			$a = isset( $row[1] ) ? sanitize_textarea_field( $row[1] ) : '';
			if ( '' !== $q && '' !== $a ) {
				$faqs[] = array( $q, $a );
				// Arabic twin stays at the same position as its English question.
				$ar[] = array( isset( $row[2] ) ? sanitize_text_field( $row[2] ) : '', isset( $row[3] ) ? sanitize_textarea_field( $row[3] ) : '' );
			}
		}
		set_theme_mod( 'bn_faqs', $faqs );
		set_theme_mod( 'bn_faqs_ar', $ar );
	} else {
		$in = isset( $_POST['bn'] ) && is_array( $_POST['bn'] ) ? wp_unslash( $_POST['bn'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( $schema[ $tab ][2] as $fields ) {
			foreach ( $fields as $key => $f ) {
				if ( ! array_key_exists( $key, $in ) ) {
					continue;
				}
				$v = $in[ $key ];
				switch ( $f[0] ) {
					case 'textarea':
						$v = sanitize_textarea_field( $v );
						break;
					case 'url':
					case 'image':
						$v = esc_url_raw( $v );
						break;
					case 'email':
						$v = sanitize_email( $v );
						break;
					case 'number':
					case 'hour':
						$v = (string) max( 0, min( 'hour' === $f[0] ? 24 : 60, (int) $v ) );
						break;
					case 'toggle':
						$v = '1' === (string) $v ? '1' : '0';
						break;
					case 'choice':
						$v = array_key_exists( (string) $v, $f[3] ) ? (string) $v : (string) key( $f[3] );
						break;
					case 'money':
						$v = (string) max( 0, round( (float) $v, 2 ) );
						break;
					case 'areafees':
						$fees = array();
						foreach ( brightnest_cities() as $city ) {
							$fees[ $city ] = is_array( $v ) && isset( $v[ $city ] ) ? max( 0, round( (float) $v[ $city ], 2 ) ) : 0;
						}
						$v = $fees;
						break;
					default:
						$v = sanitize_text_field( $v );
				}
				if ( 'whatsapp' === $key ) {
					$v = preg_replace( '/\D/', '', $v );
				}
				set_theme_mod( 'bn_' . $key, $v );
				if ( brightnest_settings_has_ar( $key, $f[0] ) && array_key_exists( $key . '_ar', $in ) ) {
					set_theme_mod( 'bn_' . $key . '_ar', 'textarea' === $f[0] ? sanitize_textarea_field( $in[ $key . '_ar' ] ) : sanitize_text_field( $in[ $key . '_ar' ] ) );
				}
			}
		}
		// Keep the time window sensible.
		if ( (int) brightnest_opt( 'slot_end' ) <= (int) brightnest_opt( 'slot_start' ) ) {
			set_theme_mod( 'bn_slot_end', (string) min( 24, (int) brightnest_opt( 'slot_start' ) + 1 ) );
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=brightnest-settings&tab=' . $tab . '&saved=1' ) );
	exit;
}
add_action( 'admin_post_bn_save_settings', 'brightnest_save_settings' );

/**
 * Payment methods offered at checkout: value => [ title, description ].
 */
function brightnest_payment_methods() {
	// Bookings are paid online only, through Ziina (cash and card on arrival were removed).
	return array(
		BRIGHTNEST_ONLINE_METHOD => array( brightnest_opt( 'pay_online_label' ), brightnest_opt( 'pay_online_text' ) ),
	);
}
