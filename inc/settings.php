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
					'cities'   => array( 'note', __( 'Cities & areas you serve', 'brightnest' ), __( 'Managed in BrightNest → Cities & Areas (cities, zones, areas, popular areas, Arabic names).', 'brightnest' ) ),
					'country_code' => array( 'text', __( 'Country code (2 letters)', 'brightnest' ), __( 'e.g. AE for UAE. Map search and pinned locations are limited to this country.', 'brightnest' ) ),
					'currency' => array( 'text', __( 'Currency', 'brightnest' ), __( 'e.g. AED', 'brightnest' ) ),
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
					'area_fees'       => array( 'areafees', __( 'Extra charge per area (added to every booking)', 'brightnest' ), __( 'E.g. a transport charge for areas far from your base. 0 = no charge. Set one charge per city, then open "Charges per area" to give single areas (Dubai Marina, JVC…) their own charge; an empty area uses its city charge. Cities come from Business → Cities / areas you serve. Per-service price changes are in each service → Areas & prices.', 'brightnest' ) ),
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
				__( 'Google (SEO)', 'brightnest' ) => array(
					'home_seo_title' => array( 'text', __( 'Page title', 'brightnest' ), __( 'The title shown in Google and on the browser tab. Leave empty to use the site name.', 'brightnest' ) ),
					'home_seo_desc'  => array( 'textarea', __( 'Meta description', 'brightnest' ), __( 'About 150 characters, shown under the title in Google.', 'brightnest' ) ),
				),
				__( 'Hero', 'brightnest' ) => array(
					'hero_title'    => array( 'text', __( 'Headline', 'brightnest' ), __( 'Put | before and after the words to highlight in orange, e.g. "Cleaning in |Dubai|".', 'brightnest' ) ),
					'hero_subtitle' => array( 'textarea', __( 'Text under the headline', 'brightnest' ), '' ),
					'hero_more'     => array( 'textarea', __( 'More text (behind "Read more")', 'brightnest' ), __( 'Optional. A blank line starts a new paragraph.', 'brightnest' ) ),
					'loc_question'  => array( 'text', __( 'Location question', 'brightnest' ), __( 'Shown above the "Set my location" field.', 'brightnest' ) ),
					'hero_img_1'    => array( 'image', __( 'Hero photo', 'brightnest' ), '' ),
				),
				__( 'Sections on/off', 'brightnest' ) => array(
					'show_services'  => array( 'toggle', __( 'Top home services', 'brightnest' ), '' ),
					'show_intro'     => array( 'toggle', __( 'Trusted partner (intro)', 'brightnest' ), '' ),
					'show_clean'     => array( 'toggle', __( 'Cleaning services list', 'brightnest' ), '' ),
					'show_maint'     => array( 'toggle', __( 'Maintenance services list', 'brightnest' ), '' ),
					'show_process'   => array( 'toggle', __( 'Easy steps', 'brightnest' ), '' ),
					'show_numbers'   => array( 'toggle', __( 'Numbers band', 'brightnest' ), '' ),
					'show_why'       => array( 'toggle', __( 'Reasons to love us', 'brightnest' ), '' ),
					'show_reviews'   => array( 'toggle', __( 'Reviews', 'brightnest' ), '' ),
					'show_blog'      => array( 'toggle', __( 'Latest blog posts', 'brightnest' ), '' ),
					'show_article'   => array( 'toggle', __( 'Cleaning guide (short intro + button)', 'brightnest' ), '' ),
					'show_home_faq'  => array( 'toggle', __( 'Homepage FAQ', 'brightnest' ), '' ),
					'show_promise'   => array( 'toggle', __( 'Closing card', 'brightnest' ), '' ),
					'show_app'       => array( 'toggle', __( 'Book on WhatsApp', 'brightnest' ), '' ),
				),
				__( 'Top home services', 'brightnest' ) => array(
					'banner_title' => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'top_title'    => array( 'text', __( 'Sub-heading', 'brightnest' ), __( 'Popular services (star in the Services list) are shown first.', 'brightnest' ) ),
				),
				__( 'Trusted partner (intro)', 'brightnest' ) => array(
					'intro_title'  => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'intro_text'   => array( 'textarea', __( 'Text', 'brightnest' ), __( 'A blank line starts a new paragraph. Long text gets a "Read more" button.', 'brightnest' ) ),
					'intro_points' => array( 'textarea', __( 'Ticks', 'brightnest' ), __( 'One per line.', 'brightnest' ) ),
					'why_image'    => array( 'image', __( 'Photo', 'brightnest' ), '' ),
				),
				__( 'Cleaning services list', 'brightnest' ) => array(
					'clean_title' => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'clean_sub'   => array( 'textarea', __( 'Text under the heading', 'brightnest' ), '' ),
					'clean_list'  => array( 'textarea', __( 'Services', 'brightnest' ), __( 'One per line: Title|Description|link (link optional, e.g. /service/deep-cleaning/). The first 4 show, the rest behind "Load more".', 'brightnest' ) ),
				),
				__( 'Maintenance services list', 'brightnest' ) => array(
					'maint_title' => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'maint_sub'   => array( 'textarea', __( 'Text under the heading', 'brightnest' ), '' ),
					'maint_list'  => array( 'textarea', __( 'Services', 'brightnest' ), __( 'One per line: Title|Description|link (link optional).', 'brightnest' ) ),
				),
				__( 'Easy steps', 'brightnest' ) => array(
					'process_title' => array( 'text', __( 'Heading', 'brightnest' ), __( 'Put | around the words to highlight in orange.', 'brightnest' ) ),
					'process_steps' => array( 'textarea', __( 'Steps', 'brightnest' ), __( 'Three lines: Title|Description', 'brightnest' ) ),
				),
				__( 'Numbers band', 'brightnest' ) => array(
					'numbers_title' => array( 'text', __( 'Heading', 'brightnest' ), __( 'The numbers are under Social & numbers → Numbers band.', 'brightnest' ) ),
					'numbers_sub'   => array( 'textarea', __( 'Text', 'brightnest' ), '' ),
				),
				__( 'Reasons to love us', 'brightnest' ) => array(
					'love_title'  => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'love_sub'    => array( 'text', __( 'Sub-heading', 'brightnest' ), '' ),
					'love_points' => array( 'textarea', __( 'Reasons', 'brightnest' ), __( 'Four lines: Title|Description', 'brightnest' ) ),
				),
				__( 'Reviews and blog', 'brightnest' ) => array(
					'reviews_eyebrow' => array( 'text', __( 'Reviews — small label', 'brightnest' ), '' ),
					'reviews_title'   => array( 'text', __( 'Reviews — heading', 'brightnest' ), '' ),
					'blog_eyebrow'    => array( 'text', __( 'Blog — small label', 'brightnest' ), '' ),
					'blog_title'      => array( 'text', __( 'Blog — heading', 'brightnest' ), __( 'Shows the 3 newest posts.', 'brightnest' ) ),
				),
				__( 'Google reviews', 'brightnest' ) => array(
					'greviews_name'      => array( 'text', __( 'Business name on Google', 'brightnest' ), __( 'Shown next to the stars, e.g. "Bright Nest Clean Dubai".', 'brightnest' ) ),
					'greviews_embed'     => array( 'textarea', __( 'Google Maps embed', 'brightnest' ), __( 'Google Maps → your business → Share → Embed a map → copy the HTML and paste it here.', 'brightnest' ) ),
					'greviews_write_url' => array( 'url', __( '"Write a review" link', 'brightnest' ), __( 'Optional. Google Business Profile → "Ask for reviews" → copy the link. Empty: made from the map.', 'brightnest' ) ),
					'greviews_key'       => array( 'text', __( 'Google Places API key (for the review cards)', 'brightnest' ), __( 'Optional. With a key, the rating and the latest reviews come from Google (updated every 12 hours). Google Cloud → enable "Places API (New)" → create an API key, restricted to that API. Without a key the map and the buttons show.', 'brightnest' ) ),
					'greviews_place_id'  => array( 'text', __( 'Google Place ID', 'brightnest' ), __( 'Optional. Found by itself from the business name when a key is set.', 'brightnest' ) ),
				),
				__( 'Long text and FAQ', 'brightnest' ) => array(
					'article_note'  => array( 'pagenote', __( 'Cleaning guide', 'brightnest' ), __( 'The full text is its own page (Pages → Professional Cleaning Services in Dubai). The homepage shows its first paragraphs (or its excerpt), its headings as links and a "Read the full guide" button.', 'brightnest' ), 'bn_guide_page' ),
					'home_faq_title' => array( 'text', __( 'FAQ heading', 'brightnest' ), '' ),
					'home_faqs'      => array( 'textarea', __( 'Questions', 'brightnest' ), __( 'One per line: Question|Answer. The first 5 show, the rest behind "Load more".', 'brightnest' ) ),
				),
				__( 'Closing card', 'brightnest' ) => array(
					'promise_title' => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'promise_text'  => array( 'textarea', __( 'Text', 'brightnest' ), __( 'A blank line starts a new paragraph.', 'brightnest' ) ),
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
		'service'  => array(
			__( 'Service page', 'brightnest' ),
			'media-document',
			array(
				__( 'Sections (same on every service page)', 'brightnest' ) => array(
					'show_sv_included' => array( 'toggle', __( "What's included", 'brightnest' ), '' ),
					'show_sv_prices'   => array( 'toggle', __( 'Prices', 'brightnest' ), '' ),
					'show_sv_faq'      => array( 'toggle', __( 'FAQ', 'brightnest' ), '' ),
					'show_sv_related'  => array( 'toggle', __( 'Related services', 'brightnest' ), '' ),
					'show_sv_whatsapp' => array( 'toggle', __( 'WhatsApp buttons', 'brightnest' ), '' ),
				),
				__( 'Headings and button texts', 'brightnest' ) => array(
					'sv_about_title'    => array( 'text', __( 'About section heading', 'brightnest' ), __( '{service} is replaced by the service name.', 'brightnest' ) ),
					'sv_included_title' => array( 'text', __( '"What\'s included" heading', 'brightnest' ), '' ),
					'sv_prices_title'   => array( 'text', __( 'Prices heading', 'brightnest' ), '' ),
					'sv_faq_title'      => array( 'text', __( 'FAQ heading', 'brightnest' ), '' ),
					'sv_related_title'  => array( 'text', __( 'Related services heading', 'brightnest' ), '' ),
					'sv_from_label'     => array( 'text', __( 'Price label', 'brightnest' ), __( 'Shown before the price, e.g. "Starting from".', 'brightnest' ) ),
					'sv_book_label'     => array( 'text', __( 'Book button', 'brightnest' ), '' ),
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
				__( 'Offer pop-up (after the area is chosen)', 'brightnest' ) => array(
					'offer_on'    => array( 'toggle', __( 'Show the offer pop-up', 'brightnest' ), __( 'Opens once the visitor has chosen their area (on the homepage: on the services page that opens next). Never on the checkout.', 'brightnest' ) ),
					'offer_style' => array( 'choice', __( 'Pop-up style', 'brightnest' ), __( 'Your own picture: upload a finished design (e.g. 800 × 800); the whole picture is the button.', 'brightnest' ), array( 'design' => __( 'Text, badge and button', 'brightnest' ), 'image' => __( 'Your own picture', 'brightnest' ) ) ),
					'offer_image' => array( 'image', __( 'Picture', 'brightnest' ), __( 'Used for "Your own picture". With "Text, badge and button" it shows on top of the text (optional).', 'brightnest' ) ),
					'offer_badge' => array( 'text', __( 'Badge — big text', 'brightnest' ), __( 'e.g. 20%. Leave empty to hide the badge.', 'brightnest' ) ),
					'offer_badge_sub' => array( 'text', __( 'Badge — small text', 'brightnest' ), __( 'e.g. OFF', 'brightnest' ) ),
					'offer_title' => array( 'text', __( 'Heading', 'brightnest' ), '' ),
					'offer_text'  => array( 'textarea', __( 'Text', 'brightnest' ), '' ),
					'offer_btn'   => array( 'text', __( 'Button text', 'brightnest' ), '' ),
					'offer_link'  => array( 'url', __( 'Button link', 'brightnest' ), __( 'Leave empty for the services page.', 'brightnest' ) ),
					'offer_code'  => array( 'text', __( 'Voucher code', 'brightnest' ), __( 'Optional. Shown in the pop-up and filled in at the checkout. Must also exist in Booking → Voucher codes.', 'brightnest' ) ),
					'offer_days'  => array( 'number', __( 'Show again after (days)', 'brightnest' ), __( '0 = once per visit. E.g. 7 = a visitor who closed it sees it again after a week.', 'brightnest' ) ),
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
	if ( 'pagenote' === $type ) {
		// Information only, with a link to edit the page set as the homepage.
		$front = (int) get_option( isset( $f[3] ) ? $f[3] : 'page_on_front' );
		echo '<label>' . esc_html( $label ) . '</label>';
		if ( $front ) {
			echo '<p><a class="button" href="' . esc_url( get_edit_post_link( $front ) ) . '">' . esc_html__( 'Edit the page text', 'brightnest' ) . '</a> <a class="button-link" href="' . esc_url( get_permalink( $front ) ) . '" target="_blank">' . esc_html__( 'View', 'brightnest' ) . '</a></p>';
		}
		echo '<small>' . esc_html( $help ) . '</small></div>';
		return;
	}
	if ( 'note' === $type ) {
		// Information only, with a link (e.g. cities now live in their own screen).
		echo '<label>' . esc_html( $label ) . '</label><p><a class="button" href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=bn_area&post_type=bn_service' ) ) . '">' . esc_html__( 'Open Cities & Areas', 'brightnest' ) . '</a></p>';
		echo '<small>' . esc_html( $help ) . '</small></div>';
		return;
	}
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
				// One charge per city, and optionally one per area of that city ("city|area"; empty = the city's charge).
				$fees = is_array( $val ) ? $val : array();
				$cur  = brightnest_opt( 'currency' );
				echo '<div class="bn-set-areafees" id="' . esc_attr( $id ) . '">';
				foreach ( brightnest_area_zones() as $city => $zones ) {
					$city_fee = isset( $fees[ $city ] ) ? $fees[ $city ] : 0;
					$own      = 0;
					foreach ( $zones as $places ) {
						foreach ( $places as $place ) {
							$own += isset( $fees[ $city . '|' . $place ] ) ? 1 : 0;
						}
					}
					echo '<div class="bn-set-afcity">';
					printf(
						'<div class="bn-set-afrow"><strong>%1$s</strong><label><span>%2$s</span> <input type="number" min="0" step="0.01" name="%3$s" value="%4$s" aria-label="%5$s"></label></div>',
						esc_html( $city ),
						esc_html( $cur ),
						esc_attr( $name . '[' . $city . ']' ),
						esc_attr( $city_fee ),
						/* translators: %s: city */
						esc_attr( sprintf( __( 'Charge for all of %s', 'brightnest' ), $city ) )
					);
					$has_list = ! ( 1 === count( $zones ) && array( $city ) === reset( $zones ) );
					if ( $has_list ) {
						echo '<details class="bn-set-afmore"' . ( $own ? ' open' : '' ) . '><summary>';
						/* translators: 1: city, 2: number of areas with their own charge */
						echo esc_html( sprintf( __( 'Charges per area in %1$s (%2$d with their own charge)', 'brightnest' ), $city, $own ) );
						echo '</summary><p class="description">' . esc_html__( 'Leave an area empty to use the city charge above.', 'brightnest' ) . '</p>';
						foreach ( $zones as $zone => $places ) {
							if ( count( $zones ) > 1 ) {
								echo '<h4 class="bn-set-afzone">' . esc_html( $zone ) . '</h4>';
							}
							echo '<div class="bn-set-afgrid">';
							foreach ( $places as $place ) {
								$key = $city . '|' . $place;
								printf(
									'<label><span>%1$s</span><input type="number" min="0" step="0.01" name="%2$s" value="%3$s" placeholder="%4$s"></label>',
									esc_html( $place ),
									esc_attr( $name . '[' . $key . ']' ),
									esc_attr( isset( $fees[ $key ] ) ? $fees[ $key ] : '' ),
									esc_attr( $city_fee )
								);
							}
							echo '</div>';
						}
						echo '</details>';
					}
					echo '</div>';
				}
				echo '</div>';
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
						foreach ( brightnest_city_areas() as $city => $places ) {
							$fees[ $city ] = is_array( $v ) && isset( $v[ $city ] ) ? max( 0, round( (float) $v[ $city ], 2 ) ) : 0;
							// An area keeps its own charge only when one is typed in.
							foreach ( $places as $place ) {
								$pkey = $city . '|' . $place;
								if ( is_array( $v ) && isset( $v[ $pkey ] ) && '' !== trim( (string) $v[ $pkey ] ) ) {
									$fees[ $pkey ] = max( 0, round( (float) $v[ $pkey ], 2 ) );
								}
							}
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
