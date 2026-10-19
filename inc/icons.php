<?php
/**
 * Inline SVG icon set (stroke icons, 24x24).
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return SVG markup for an icon.
 *
 * @param string $name  Icon key.
 * @param int    $size  Pixel size.
 * @return string
 */
function brightnest_icon( $name, $size = 24 ) {
	$paths = array(
		'cleaning'    => '<path d="M3 21h18"/><path d="M6 21v-4a6 6 0 0 1 12 0v4"/><path d="M12 11V3"/><path d="M9 3h6"/><path d="M5 8l1.5 1.5M19 8l-1.5 1.5"/>',
		'deep-clean'  => '<path d="M12 3l1.9 4.6L18.5 9l-4.6 1.9L12 15.5l-1.9-4.6L5.5 9l4.6-1.4z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9z"/><path d="M5 15l.6 1.4L7 17l-1.4.6L5 19l-.6-1.4L3 17l1.4-.6z"/>',
		'moving'      => '<path d="M1 7h13v10H1z"/><path d="M14 10h4l3 3v4h-7z"/><circle cx="5.5" cy="18" r="2"/><circle cx="17.5" cy="18" r="2"/>',
		'maintenance' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.2L3 17.8V21h3.2l6.3-6.3a4 4 0 0 0 5.2-5.4l-2.6 2.6-2.4-.6-.6-2.4z"/>',
		'ac'          => '<rect x="2" y="4" width="20" height="9" rx="2"/><path d="M6 9h12"/><path d="M7 17c0 1.5-1 2-1 3M12 17c0 1.5-1 2-1 3M17 17c0 1.5-1 2-1 3"/>',
		'pest'        => '<ellipse cx="12" cy="14" rx="4.5" ry="6"/><path d="M12 8V5M9.5 5.5 8 3M14.5 5.5 16 3M7.5 12H3M7.5 16 4 18M16.5 12H21M16.5 16 20 18M12 9v11"/>',
		'salon'       => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4 8.1 15.9M14.5 14.5 20 20M8.1 8.1 12 12"/>',
		'painting'    => '<rect x="3" y="3" width="15" height="6" rx="1.5"/><path d="M18 6h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-8v3"/><rect x="10" y="14" width="4" height="7" rx="1"/>',
		'gardening'   => '<path d="M12 21v-9"/><path d="M12 12c0-4 3-7 8-7 0 5-3 8-8 7z"/><path d="M12 14c0-3-2.5-5.5-7-5.5 0 4 2.5 6 7 5.5z"/><path d="M7 21h10"/>',
		'pet'         => '<circle cx="5" cy="10" r="2"/><circle cx="9" cy="5.5" r="2"/><circle cx="15" cy="5.5" r="2"/><circle cx="19" cy="10" r="2"/><path d="M12 12c-3 0-6 4-6 6.5 0 1.5 1.2 2.5 2.7 2.5 1.2 0 2-.6 3.3-.6s2.1.6 3.3.6c1.5 0 2.7-1 2.7-2.5C18 16 15 12 12 12z"/>',
		'car'         => '<path d="M5 17H3v-5l2-5h14l2 5v5h-2"/><path d="M3 12h18"/><circle cx="7.5" cy="17" r="2"/><circle cx="16.5" cy="17" r="2"/>',
		'health'      => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21.2l8.8-8.8a5.5 5.5 0 0 0 0-7.8z"/><path d="M12 9v6M9 12h6"/>',
		'maid'        => '<circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0"/>',
		'sofa'        => '<path d="M4 11V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3"/><path d="M2 13a2 2 0 0 1 4 0v2h12v-2a2 2 0 0 1 4 0v5H2z"/><path d="M5 18v2M19 18v2"/>',
		'water'       => '<path d="M12 2.7S5.5 10 5.5 14.5a6.5 6.5 0 0 0 13 0C18.5 10 12 2.7 12 2.7z"/>',
		'spa'         => '<path d="M12 21c-4.5 0-8-2.5-9-6 3 0 6 1 9 4 3-3 6-4 9-4-1 3.5-4.5 6-9 6z"/><path d="M12 19c-2-2-3-5-3-8 0-2.5 1.2-5 3-7 1.8 2 3 4.5 3 7 0 3-1 6-3 8z"/>',
		'barber'      => '<circle cx="12" cy="7" r="4"/><path d="M8 7c0-2.5 1.8-4 4-4s4 1.5 4 4"/><path d="M9.5 10.5c.7.7 1.6 1 2.5 1s1.8-.3 2.5-1"/><path d="M5 21v-1a7 7 0 0 1 14 0v1"/><path d="M12 13v4"/>',
		'flask'       => '<path d="M9 3h6M10 3v6L4.5 18.5A1.7 1.7 0 0 0 6 21h12a1.7 1.7 0 0 0 1.5-2.5L14 9V3"/><path d="M7 15h10"/><circle cx="10" cy="18" r=".6" fill="currentColor"/><circle cx="13.5" cy="17.5" r=".6" fill="currentColor"/>',
		'search'      =>'<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'star'        => '<path d="m12 2.5 2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.1 1.2-6.5L2.5 9.4l6.6-.9z" fill="currentColor" stroke="none"/>',
		'check'       => '<path d="M20 6 9 17l-5-5"/>',
		'check-circle'=> '<circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/>',
		'shield'      => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
		'headset'     => '<path d="M3 14v-2a9 9 0 0 1 18 0v2"/><path d="M21 15a2 2 0 0 1-2 2h-1v-6h1a2 2 0 0 1 2 2zM3 15a2 2 0 0 0 2 2h1v-6H5a2 2 0 0 0-2 2z"/><path d="M18 17v1a3 3 0 0 1-3 3h-3"/>',
		'users'       => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M16 4a4 4 0 0 1 0 8M22 21a7 7 0 0 0-4-6.3"/>',
		'home'        => '<path d="M3 11 12 3l9 8"/><path d="M5 10v11h14V10"/><path d="M10 21v-6h4v6"/>',
		'clipboard'   => '<rect x="5" y="4" width="14" height="18" rx="2"/><path d="M9 2h6v4H9z"/><path d="M9 12h6M9 16h4"/>',
		'calendar'    => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
		'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'sofa-relax'  => '<path d="M8 14s1.5 2 4 2 4-2 4-2"/><circle cx="12" cy="12" r="10"/><path d="M9 9h.01M15 9h.01"/>',
		'phone'       => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
		'mail'        => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
		'pin'         => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
		'whatsapp'    => '<path d="M3 21l1.6-4.7A8.5 8.5 0 1 1 7.8 19.6z"/><path d="M9 8.5c0 3.5 3 6.5 6.5 6.5l1-1.6-2-1-1 .8c-1-.4-2-1.4-2.4-2.4l.8-1-1-2z"/>',
		'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-left'  => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
		'chevron-down'=> '<path d="m6 9 6 6 6-6"/>',
		'menu'        => '<path d="M3 6h18M3 12h18M3 18h18"/>',
		'close'       => '<path d="M18 6 6 18M6 6l12 12"/>',
		'tag'         => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
		'facebook'    => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
		'instagram'   => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
		'twitter'     => '<path d="M4 4l16 16M20 4 4 20"/>',
		'youtube'     => '<path d="M22 8.2a3 3 0 0 0-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.9.5A3 3 0 0 0 2 8.2 31 31 0 0 0 1.6 12 31 31 0 0 0 2 15.8a3 3 0 0 0 2.1 2.1c1.9.5 7.9.5 7.9.5s6 0 7.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .4-3.8 31 31 0 0 0-.4-3.8z"/><path d="m10 15 5-3-5-3z" fill="currentColor"/>',
		'tiktok'      => '<path d="M16 3c.4 2.4 1.9 4 4.5 4.3v3.2c-1.7 0-3.2-.5-4.5-1.4V15a6 6 0 1 1-6-6v3.3a2.8 2.8 0 1 0 2.8 2.7V3z"/>',
		'linkedin'    => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><path d="M2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		$name = 'home';
	}

	return sprintf(
		'<svg class="bn-icon bn-icon-%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		absint( $size ),
		$paths[ $name ]
	);
}

/**
 * List of icon keys usable for services/categories (for admin dropdowns).
 *
 * @return array
 */
function brightnest_service_icon_choices() {
	return array(
		'cleaning'    => __( 'Cleaning', 'brightnest' ),
		'deep-clean'  => __( 'Deep clean / Sparkle', 'brightnest' ),
		'sofa'        => __( 'Sofa / Upholstery', 'brightnest' ),
		'moving'      => __( 'Moving truck', 'brightnest' ),
		'maintenance' => __( 'Maintenance / Wrench', 'brightnest' ),
		'ac'          => __( 'AC', 'brightnest' ),
		'pest'        => __( 'Pest control', 'brightnest' ),
		'salon'       => __( 'Salon', 'brightnest' ),
		'painting'    => __( 'Painting', 'brightnest' ),
		'gardening'   => __( 'Gardening', 'brightnest' ),
		'pet'         => __( 'Pets', 'brightnest' ),
		'car'         => __( 'Car', 'brightnest' ),
		'health'      => __( 'Health', 'brightnest' ),
		'maid'        => __( 'Maid / Nanny', 'brightnest' ),
		'water'       => __( 'Water / Plumbing', 'brightnest' ),
		'spa'         => __( 'Spa / Massage', 'brightnest' ),
		'barber'      => __( "Men's grooming", 'brightnest' ),
		'flask'       => __( 'Lab test', 'brightnest' ),
		'home'        => __( 'Home', 'brightnest' ),
	);
}
