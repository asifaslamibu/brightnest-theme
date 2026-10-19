<?php
/**
 * Customizer settings (Appearance > Customize > BrightNest Settings).
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for all theme options.
 *
 * @return array
 */
function brightnest_defaults() {
	return array(
		'hero_title'      => 'Cleaning & repairs, | done right.',
		'hero_subtitle'   => 'Book trusted cleaning, maintenance, moving and 25+ home services in a few clicks. Vetted professionals, upfront prices.',
		'hero_image'      => '',
		'currency'        => 'AED',
		'cities'          => "Dubai\nAbu Dhabi\nSharjah",
		'phone'           => '+971 50 000 0000',
		'whatsapp'        => '971500000000',
		'email'           => 'hello@brightnestclean.com',
		'address'         => 'Dubai, United Arab Emirates',
		'hours'           => 'Mon - Sun, 8:00 AM - 10:00 PM',
		'stat_1_num'      => '10,000+',
		'stat_1_label'    => 'Happy customers',
		'stat_2_num'      => '4.8/5',
		'stat_2_label'    => 'Average rating',
		'stat_3_num'      => '25+',
		'stat_3_label'    => 'Home services',
		'stat_4_num'      => '7 days',
		'stat_4_label'    => 'Live customer support',
		'promo_title'     => 'Get 20% off your first booking',
		'promo_text'      => 'Book any service online today and save on your first visit. No hidden charges.',
		'promo_code'      => 'WELCOME20',
		'facebook'        => '',
		'instagram'       => '',
		'twitter'         => '',
		'linkedin'        => '',
		'footer_about'    => 'BrightNest Clean connects you with vetted, background-checked home service professionals. Quality guaranteed on every booking.',
		'booking_email'   => '',
		'remind_on'       => '1',
		'remind_hours'    => '1',
		'remind_subject'  => 'Your {service} booking is almost done',
		'remind_body'     => "Hi {name},\n\nYou were one step away from booking {service}. We saved your details, so finishing takes less than a minute:\n\n{link}\n\nQuestions? Call or WhatsApp us on {phone}.\n\n{site}",
		'phone2'          => '',
		'youtube'         => '',
		'tiktok'          => '',
		'achievements'    => "8k|Services provided
12k|Orders served
300+|Team members
7k|5-star reviews",
		'vouchers'        => "WELCOME20:20%\nWB50:50",
		'map_lat'         => '25.0804',
		'map_lng'         => '55.1366',
		'google_maps_key' => '',

		// Homepage (BrightNest → Settings → Homepage). "|" marks the orange accent.
		'hero_badge'         => 'Trusted by 3,600+ customers',
		'hero_img_1'         => '',
		'hero_img_2'         => '',
		'hero_img_3'         => '',
		'search_placeholder' => 'What do you need?',
		'show_marquee'       => '1',
		'show_services'      => '1',
		'show_why'           => '1',
		'show_numbers'       => '1',
		'show_process'       => '1',
		'show_reviews'       => '1',
		'show_cta'           => '1',
		'services_eyebrow'   => 'Our services',
		'services_title'     => 'Everything your home needs, |in one place',
		'why_eyebrow'        => 'Why Bright Nest',
		'why_title'          => 'Your trusted partner in |home & office| cleanliness',
		'why_text'           => 'From daily home upkeep to deep cleaning, and hourly maids to full property maintenance — we do it all with care and professionalism across the UAE.',
		'why_image'          => '',
		'why_badge_title'    => '100% satisfaction',
		'why_badge_text'     => 'guaranteed on every job',
		'why_points'         => "Flexible scheduling|We work around your timing – daily, weekly, or monthly.\nProfessional staff|Trained, verified and uniformed cleaning professionals.\nCompetitive pricing|Transparent rates with no hidden charges.\nSafe & eco-friendly|Products and practices that are safe for kids and pets.",
		'numbers_title'      => 'Premium cleaning & maintenance. |100% spotless results.',
		'process_eyebrow'    => 'How it works',
		'process_title'      => 'Booked in |three easy steps',
		'process_steps'      => "Find your service|Search or browse our services and see prices upfront.\nChoose a time|Pick a day and an hourly slot that suits you.\nRelax, we arrive|We confirm and send our team to your door.",
		'reviews_eyebrow'    => 'Testimonials',
		'reviews_title'      => 'Kind words from |our customers',
		'cta_title'          => "Let's make your home |shine",
		'cta_text'           => 'Leave your number and we will call you back with the right service and price. Free site inspection & quote.',

		// Booking (BrightNest → Settings → Booking).
		'slot_start'      => '8',
		'slot_end'        => '20',
		'lead_hours'      => '2',
		'days_ahead'      => '14',
		'pay_online'      => '1',
		'pay_online_label' => 'Pay online now',
		'pay_online_text' => 'Card, Apple Pay or Google Pay — secured by Ziina',
		'booking_note'    => 'Secure payment by Ziina. We will confirm the booking with you by phone or WhatsApp.',

		// Homepage sections (BrightNest → Settings → Homepage).
		'loc_question'  => 'Where would you like to receive your service?',
		'slot_length'       => '60',
		'service_fee'       => '0',
		'service_fee_label' => 'Service Fee',
		'area_fees'         => array(),
		'area_fee_label'    => 'Area charge',
		'min_amount'        => '2',
		'checkout_why'      => "Our own trained team, not random freelancers\nThe price you see is the price you pay\nMissed a spot? We come back for free\nReal people on phone and WhatsApp",
		'show_banner'   => '1',
		'show_promise'  => '1',
		'show_app'      => '1',
		'show_areas'    => '1',
		'banner_title'  => 'Leave your to-do list to us!',
		'banner_text'   => 'Pick a service, choose a time and pay online. Our team takes it from there.',
		'banner_points' => "Pay online with card, Apple Pay or Google Pay\nSame-day slots when available\nFree re-clean if something is missed",
		'top_title'     => 'Check out some of our top home services:',
		'love_title'    => 'Why people in Dubai book |Bright Nest',
		'love_sub'      => 'Four things our customers mention the most.',
		'love_points'   => "Rated after every visit|Each job gets a customer rating. Cleaners who slip don't stay on the team.
Same-day slots|Book in the morning and we can often be there the same afternoon.
Clear prices|You see the full price before you pay. Nothing extra at the door.
One team for the whole home|Cleaning, AC, pest control, repairs and more in one booking.",
		'promise_title' => 'Our promise',
		'promise_text'  => 'We send the same standard every time: a trained Bright Nest cleaner, on time, with the right equipment. Tell us how you like things done and we write it down for the next visit.',
		'promise_close' => 'Something not right? Tell us within 24 hours and we come back for free.',
		'app_title'     => 'Rather chat on WhatsApp?',
		'app_text'      => 'Send us a message to book, move a visit or ask for a quote. A real person from our team will reply during working hours.',
		'country'       => 'United Arab Emirates',
		'country_code'  => 'AE',
	);
}

/**
 * Text with "|" separators: every second part is wrapped in <em> (orange accent).
 * "Booked in |three easy steps" → Booked in <em>three easy steps</em>.
 *
 * @param string $text Raw setting.
 * @return string Escaped HTML.
 */
function brightnest_accent( $text ) {
	$out = '';
	foreach ( explode( '|', (string) $text ) as $i => $part ) {
		$out .= $i % 2 ? '<em>' . esc_html( $part ) . '</em>' : esc_html( $part );
	}
	return $out;
}

/**
 * "Title|Text" lines → [ [title, text], … ].
 */
function brightnest_lines( $key ) {
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) brightnest_opt( $key ) ) as $line ) {
		$p = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' !== $p[0] ) {
			$rows[] = array( $p[0], isset( $p[1] ) ? $p[1] : '' );
		}
	}
	return $rows;
}

/**
 * Image setting with a theme fallback.
 */
function brightnest_img_opt( $key, $fallback ) {
	$url = brightnest_opt( $key );
	return $url ? $url : BRIGHTNEST_URI . '/assets/img/site/' . $fallback;
}

/**
 * FAQs: global list from Settings → FAQ, plus the service's own questions first.
 *
 * @param int $service_id Optional service ID.
 * @return array[] [ [question, answer], … ]
 */
function brightnest_faqs( $service_id = 0 ) {
	$global = get_theme_mod( 'bn_faqs', null );
	if ( ! is_array( $global ) ) {
		$global = array(
			array( 'How do I book a service?', 'Pick a service, choose your preferred date and time and confirm the booking. Our team will call or WhatsApp you to confirm the details.' ),
			array( 'Are your professionals vetted?', 'Yes. Every professional is background-checked, trained and reviewed by customers after each job.' ),
			array( 'Do I need to pay online?', 'Yes. You pay securely online when you book — by card, Apple Pay or Google Pay through Ziina.' ),
			array( 'Do cleaners bring their own materials?', 'Yes, cleaning materials and equipment can be included — just choose it when you book or mention it in the notes.' ),
			array( 'Can I reschedule or cancel?', 'Of course. Contact us at least 12 hours before your booking to reschedule or cancel free of charge.' ),
			array( 'What if I am not happy with the service?', 'Let us know within 24 hours and we will send a team back to fix it at no extra cost.' ),
		);
	}
	$own = $service_id ? get_post_meta( $service_id, '_bn_faqs', true ) : array();
	$own = is_array( $own ) ? $own : array();
	// Arabic site: each question's Arabic twin (same position), when filled in.
	if ( function_exists( 'brightnest_show_ar' ) && brightnest_show_ar() ) {
		$swap = function ( $rows, $ar ) {
			foreach ( $rows as $i => $row ) {
				if ( isset( $ar[ $i ][0], $ar[ $i ][1] ) && '' !== trim( $ar[ $i ][0] ) && '' !== trim( $ar[ $i ][1] ) ) {
					$rows[ $i ] = array( $ar[ $i ][0], $ar[ $i ][1] );
				}
			}
			return $rows;
		};
		$global = $swap( $global, (array) get_theme_mod( 'bn_faqs_ar', array() ) );
		$own    = $service_id ? $swap( $own, (array) get_post_meta( $service_id, '_bn_ar_faqs', true ) ) : $own;
	}
	return array_merge( $own, $global );
}

/**
 * Get a theme option.
 *
 * @param string $key Option key.
 * @return string
 */
function brightnest_opt( $key ) {
	// Arabic site: the Arabic twin of a text setting, when one is filled in.
	$ar = function_exists( 'brightnest_opt_ar' ) ? brightnest_opt_ar( $key ) : null;
	if ( null !== $ar ) {
		return $ar;
	}
	$defaults = brightnest_defaults();
	return get_theme_mod( 'bn_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

function brightnest_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'brightnest', array( 'title' => __( 'BrightNest Settings', 'brightnest' ), 'priority' => 20 ) );

	$sections = array(
		'bn_hero'    => __( 'Homepage Hero', 'brightnest' ),
		'bn_stats'   => __( 'Trust Stats', 'brightnest' ),
		'bn_promo'   => __( 'Promo Banner', 'brightnest' ),
		'bn_contact' => __( 'Contact & Business', 'brightnest' ),
		'bn_social'  => __( 'Social & Footer', 'brightnest' ),
	);
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( $id, array( 'title' => $title, 'panel' => 'brightnest' ) );
	}

	$fields = array(
		'hero_title'    => array( 'bn_hero', __( 'Headline (text after | is shown in orange)', 'brightnest' ), 'text' ),
		'hero_subtitle' => array( 'bn_hero', __( 'Sub-headline', 'brightnest' ), 'textarea' ),
		'stat_1_num'    => array( 'bn_stats', __( 'Stat 1 number', 'brightnest' ), 'text' ),
		'stat_1_label'  => array( 'bn_stats', __( 'Stat 1 label', 'brightnest' ), 'text' ),
		'stat_2_num'    => array( 'bn_stats', __( 'Stat 2 number', 'brightnest' ), 'text' ),
		'stat_2_label'  => array( 'bn_stats', __( 'Stat 2 label', 'brightnest' ), 'text' ),
		'stat_3_num'    => array( 'bn_stats', __( 'Stat 3 number', 'brightnest' ), 'text' ),
		'stat_3_label'  => array( 'bn_stats', __( 'Stat 3 label', 'brightnest' ), 'text' ),
		'stat_4_num'    => array( 'bn_stats', __( 'Stat 4 number', 'brightnest' ), 'text' ),
		'stat_4_label'  => array( 'bn_stats', __( 'Stat 4 label', 'brightnest' ), 'text' ),
		'promo_title'   => array( 'bn_promo', __( 'Promo title', 'brightnest' ), 'text' ),
		'promo_text'    => array( 'bn_promo', __( 'Promo text', 'brightnest' ), 'textarea' ),
		'promo_code'    => array( 'bn_promo', __( 'Promo code (leave empty to hide)', 'brightnest' ), 'text' ),
		'currency'      => array( 'bn_contact', __( 'Currency label', 'brightnest' ), 'text' ),
		'cities'        => array( 'bn_contact', __( 'Cities / areas served (one per line)', 'brightnest' ), 'textarea' ),
		'phone'         => array( 'bn_contact', __( 'Phone', 'brightnest' ), 'text' ),
		'phone2'        => array( 'bn_contact', __( 'Second phone (optional)', 'brightnest' ), 'text' ),
		'whatsapp'      => array( 'bn_contact', __( 'WhatsApp number (digits only, with country code)', 'brightnest' ), 'text' ),
		'email'         => array( 'bn_contact', __( 'Public email', 'brightnest' ), 'text' ),
		'booking_email' => array( 'bn_contact', __( 'Send booking notifications to (defaults to admin email)', 'brightnest' ), 'text' ),
		'address'       => array( 'bn_contact', __( 'Address', 'brightnest' ), 'text' ),
		'vouchers'      => array( 'bn_promo', __( 'Voucher codes, one per line: CODE:20% (percent) or CODE:50 (fixed amount)', 'brightnest' ), 'textarea' ),
		'map_lat'       => array( 'bn_contact', __( 'Checkout map default latitude', 'brightnest' ), 'text' ),
		'map_lng'       => array( 'bn_contact', __( 'Checkout map default longitude', 'brightnest' ), 'text' ),
		'hours'         => array( 'bn_contact', __( 'Working hours', 'brightnest' ), 'text' ),
		'facebook'      => array( 'bn_social', __( 'Facebook URL', 'brightnest' ), 'url' ),
		'instagram'     => array( 'bn_social', __( 'Instagram URL', 'brightnest' ), 'url' ),
		'twitter'       => array( 'bn_social', __( 'X / Twitter URL', 'brightnest' ), 'url' ),
		'linkedin'      => array( 'bn_social', __( 'LinkedIn URL', 'brightnest' ), 'url' ),
		'youtube'       => array( 'bn_social', __( 'YouTube URL', 'brightnest' ), 'url' ),
		'tiktok'        => array( 'bn_social', __( 'TikTok URL', 'brightnest' ), 'url' ),
		'achievements'  => array( 'bn_stats', __( 'Achievements band, one per line: number|label', 'brightnest' ), 'textarea' ),
		'footer_about'  => array( 'bn_social', __( 'Footer about text', 'brightnest' ), 'textarea' ),
	);

	$defaults = brightnest_defaults();
	foreach ( $fields as $key => $f ) {
		$sanitize = 'url' === $f[2] ? 'esc_url_raw' : ( 'textarea' === $f[2] ? 'sanitize_textarea_field' : 'sanitize_text_field' );
		$wp_customize->add_setting( 'bn_' . $key, array( 'default' => $defaults[ $key ], 'sanitize_callback' => $sanitize ) );
		$wp_customize->add_control( 'bn_' . $key, array( 'label' => $f[1], 'section' => $f[0], 'type' => $f[2] ) );
	}

	$wp_customize->add_setting( 'bn_hero_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'bn_hero_image',
			array(
				'label'   => __( 'Hero image (optional)', 'brightnest' ),
				'section' => 'bn_hero',
			)
		)
	);
}
add_action( 'customize_register', 'brightnest_customize_register' );
