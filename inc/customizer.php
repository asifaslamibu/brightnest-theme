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
		'hero_title'      => "Sit back, we'll take care of your home",
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
		'vouchers'        => "WELCOME20:20%\nWB50:50",
		'map_lat'         => '25.0804',
		'map_lng'         => '55.1366',
	);
}

/**
 * Get a theme option.
 *
 * @param string $key Option key.
 * @return string
 */
function brightnest_opt( $key ) {
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
		'hero_title'    => array( 'bn_hero', __( 'Headline', 'brightnest' ), 'text' ),
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
