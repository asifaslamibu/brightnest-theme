<?php
/**
 * Starter content created once when the theme is activated.
 * Everything here can be edited or deleted from wp-admin.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

function brightnest_starter_catalog() {
	return array(
		array(
			'name'     => 'Cleaning Services',
			'slug'     => 'cleaning',
			'icon'     => 'cleaning',
			'desc'     => 'Regular home cleaning, deep cleaning, move-in/move-out and more by trained, vetted cleaners.',
			'services' => array(
				array( 'Home Cleaning', 35, 'per hour', '2 - 6 hours', 'cleaning', true, 'Regular cleaning for apartments and villas by experienced, background-checked cleaners.', "Dusting & wiping all surfaces\nVacuuming & mopping floors\nKitchen counters & sink\nBathroom cleaning\nMaking beds & tidying up\nCleaning materials on request" ),
				array( 'Deep Cleaning', 399, 'per apartment', '4 - 8 hours', 'deep-clean', true, 'Top-to-bottom intensive cleaning that reaches every corner — ideal before guests or seasonally.', "Inside cabinets & appliances\nDescaling bathrooms & tiles\nWindow & glass cleaning\nBalcony & terrace cleaning\nProfessional equipment & chemicals" ),
				array( 'Move In / Move Out Cleaning', 499, 'per apartment', '5 - 9 hours', 'deep-clean', false, 'Get your deposit back or start fresh in your new home with a complete empty-property clean.', "Full kitchen degreasing\nWardrobes inside & out\nWalls spot cleaning\nBathrooms descaling\nFloors scrubbed" ),
				array( 'Sofa & Carpet Cleaning', 149, 'per seat', '1 - 3 hours', 'sofa', true, 'Shampooing and steam extraction for sofas, carpets, rugs and mattresses.', "Stain treatment\nSteam / shampoo extraction\nDeodorising\nQuick-dry process" ),
				array( 'Office Cleaning', 45, 'per hour', 'Flexible', 'cleaning', false, 'Daily, weekly or one-off cleaning for offices, clinics and retail spaces.', "Workstations & meeting rooms\nPantry & washrooms\nFloor care\nWaste removal" ),
			),
		),
		array(
			'name'     => 'Maintenance & Handyman',
			'slug'     => 'maintenance',
			'icon'     => 'maintenance',
			'desc'     => 'Plumbing, electrical, carpentry and general repairs by certified technicians.',
			'services' => array(
				array( 'Handyman Services', 99, 'per visit', '1 - 3 hours', 'maintenance', true, 'Furniture assembly, TV mounting, curtain installation, shelves and small repairs.', "Furniture assembly\nTV & shelf mounting\nCurtains & blinds\nMinor repairs" ),
				array( 'Plumbing', 129, 'per visit', '1 - 2 hours', 'water', false, 'Leaks, blockages, water heaters, taps and bathroom fittings fixed fast.', "Leak detection & repair\nDrain unblocking\nWater heater service\nTap & mixer replacement" ),
				array( 'Electrical Services', 129, 'per visit', '1 - 2 hours', 'maintenance', false, 'Certified electricians for lights, sockets, DB boards and fault finding.', "Light & fan installation\nSocket & switch repair\nFault diagnosis\nSafety check" ),
			),
		),
		array(
			'name'     => 'AC Services',
			'slug'     => 'ac-services',
			'icon'     => 'ac',
			'desc'     => 'AC cleaning, servicing, gas top-up and duct cleaning to keep your home cool and healthy.',
			'services' => array(
				array( 'AC Cleaning & Servicing', 149, 'per unit', '1 - 2 hours', 'ac', true, 'Full AC service including filter, coil and drain cleaning for better cooling and air quality.', "Filter & coil cleaning\nDrain line flushing\nGas pressure check\nPerformance test" ),
				array( 'AC Duct Cleaning', 699, 'per apartment', '3 - 5 hours', 'ac', false, 'Remove dust, mould and allergens from your AC ducts with professional equipment.', "Duct inspection\nRotary brush cleaning\nSanitisation\nBefore/after photos" ),
			),
		),
		array(
			'name'     => 'Moving & Storage',
			'slug'     => 'moving',
			'icon'     => 'moving',
			'desc'     => 'Local and international movers, packing and secure storage.',
			'services' => array(
				array( 'Home Movers', 999, 'per move', 'Same day', 'moving', true, 'Stress-free local moving with packing, dismantling, transport and reassembly.', "Packing materials\nFurniture dismantling & assembly\nTransport & insurance\nTrained moving crew" ),
				array( 'Storage', 299, 'per month', 'Monthly', 'moving', false, 'Clean, secure, climate-controlled storage for furniture and boxes.', "Pick-up from your home\nClimate-controlled units\n24/7 security\nFlexible plans" ),
			),
		),
		array(
			'name'     => 'Pest Control',
			'slug'     => 'pest-control',
			'icon'     => 'pest',
			'desc'     => 'Safe, approved treatments for cockroaches, bed bugs, ants, termites and more.',
			'services' => array(
				array( 'General Pest Control', 199, 'per apartment', '1 - 2 hours', 'pest', true, 'Government-approved, family-safe treatment for cockroaches, ants and common pests.', "Inspection\nGel & spray treatment\nFollow-up visit\nService warranty" ),
				array( 'Bed Bug Treatment', 349, 'per apartment', '2 - 3 hours', 'pest', false, 'Targeted multi-stage bed bug treatment with follow-up.', "Mattress & furniture treatment\nTwo-stage process\nPrep checklist\nWarranty" ),
			),
		),
		array(
			'name'     => 'Painting',
			'slug'     => 'painting',
			'icon'     => 'painting',
			'desc'     => 'Interior and exterior painting with premium paints and tidy crews.',
			'services' => array(
				array( 'Apartment Painting', 899, 'per apartment', '1 - 2 days', 'painting', false, 'Fresh, flawless walls with furniture protection and full clean-up.', "Wall preparation\nPremium paint\nFurniture covering\nPost-job cleanup" ),
			),
		),
		array(
			'name'     => 'Gardening',
			'slug'     => 'gardening',
			'icon'     => 'gardening',
			'desc'     => 'Lawn care, landscaping, irrigation and plant maintenance.',
			'services' => array(
				array( 'Garden Maintenance', 199, 'per visit', '2 - 4 hours', 'gardening', false, 'Mowing, trimming, weeding and plant care to keep your garden beautiful.', "Lawn mowing\nHedge trimming\nWeeding\nGreen waste removal" ),
			),
		),
		array(
			'name'     => 'Maids & Nannies',
			'slug'     => 'maids',
			'icon'     => 'maid',
			'desc'     => 'Part-time and full-time maids and babysitters you can trust.',
			'services' => array(
				array( 'Part-Time Maid', 30, 'per hour', 'Flexible', 'maid', false, 'Reliable part-time maids for cleaning, ironing and household help.', "Cleaning & tidying\nIroning & laundry\nDishwashing\nSame maid on request" ),
				array( 'Babysitting', 45, 'per hour', 'Flexible', 'maid', false, 'Experienced, caring babysitters for evenings, weekends or regular care.', "Vetted sitters\nFirst-aid aware\nPlay & bedtime routines" ),
			),
		),
		array(
			'name'     => 'Salon at Home',
			'slug'     => 'salon-at-home',
			'icon'     => 'salon',
			'desc'     => 'Beauty and grooming services in the comfort of your home.',
			'services' => array(
				array( 'Women\'s Salon at Home', 99, 'per session', '1 - 3 hours', 'salon', false, 'Manicure, pedicure, waxing, facials and hair styling by certified beauticians.', "Hygienic disposable kits\nCertified beauticians\nPremium products" ),
			),
		),
		array(
			'name'     => 'Car Services',
			'slug'     => 'car-services',
			'icon'     => 'car',
			'desc'     => 'Car wash, detailing and servicing at your doorstep.',
			'services' => array(
				array( 'Car Wash at Home', 49, 'per car', '45 minutes', 'car', false, 'Waterless or foam car wash at your parking spot.', "Exterior wash\nInterior vacuum\nDashboard wipe\nTyre shine" ),
			),
		),
	);
}

function brightnest_create_starter_content() {
	if ( get_option( 'brightnest_starter_done' ) ) {
		return;
	}

	brightnest_register_post_types();

	$cat_order = 0;
	$svc_order = 0;
	foreach ( brightnest_starter_catalog() as $cat ) {
		$term = term_exists( $cat['slug'], 'bn_service_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $cat['name'], 'bn_service_cat', array( 'slug' => $cat['slug'], 'description' => $cat['desc'] ) );
		}
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$term_id = (int) $term['term_id'];
		update_term_meta( $term_id, 'bn_icon', $cat['icon'] );
		update_term_meta( $term_id, 'bn_order', ++$cat_order );

		foreach ( $cat['services'] as $s ) {
			list( $title, $price, $unit, $duration, $icon, $featured, $excerpt, $includes ) = $s;
			if ( get_page_by_path( sanitize_title( $title ), OBJECT, 'bn_service' ) ) {
				continue;
			}
			$content  = '<!-- wp:heading --><h2>Why book ' . esc_html( $title ) . ' with us?</h2><!-- /wp:heading -->';
			$content .= '<!-- wp:paragraph --><p>Every professional on our team is vetted, trained and insured. You get upfront pricing, flexible time slots and a satisfaction guarantee — if something isn\'t right, we\'ll come back and fix it.</p><!-- /wp:paragraph -->';
			$content .= '<!-- wp:heading --><h2>How it works</h2><!-- /wp:heading -->';
			$content .= '<!-- wp:list --><ul><li>Choose your date and time and submit the booking form.</li><li>Our team calls to confirm the details and final price.</li><li>A trained professional arrives on time and gets the job done.</li></ul><!-- /wp:list -->';

			$id = wp_insert_post(
				array(
					'post_type'    => 'bn_service',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_excerpt' => $excerpt,
					'post_content' => $content,
					'menu_order'   => ++$svc_order,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				wp_set_object_terms( $id, $term_id, 'bn_service_cat' );
				update_post_meta( $id, '_bn_price', $price );
				update_post_meta( $id, '_bn_price_unit', $unit );
				update_post_meta( $id, '_bn_duration', $duration );
				update_post_meta( $id, '_bn_icon', $icon );
				update_post_meta( $id, '_bn_featured', $featured ? '1' : '' );
				update_post_meta( $id, '_bn_includes', $includes );
			}
		}
	}

	// Sample reviews — clearly marked so they get replaced with real ones.
	if ( ! get_posts( array( 'post_type' => 'bn_review', 'posts_per_page' => 1, 'post_status' => 'any' ) ) ) {
		$samples = array(
			array( 'Sample Customer 1', 'Home Cleaning', 'Sample review — replace with a real customer review in Reviews > All Reviews. Example: the cleaner arrived on time and the apartment was spotless.' ),
			array( 'Sample Customer 2', 'AC Cleaning & Servicing', 'Sample review — replace with a real customer review. Example: quick booking, professional technician, AC is cooling much better now.' ),
			array( 'Sample Customer 3', 'Deep Cleaning', 'Sample review — replace with a real customer review. Example: great attention to detail, kitchen and bathrooms look brand new.' ),
			array( 'Sample Customer 4', 'Home Movers', 'Sample review — replace with a real customer review. Example: the moving team was careful with our furniture and finished early.' ),
		);
		foreach ( $samples as $r ) {
			$id = wp_insert_post( array( 'post_type' => 'bn_review', 'post_status' => 'publish', 'post_title' => $r[0], 'post_content' => $r[2] ) );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_bn_rating', 5 );
				update_post_meta( $id, '_bn_service_name', $r[1] );
			}
		}
	}

	// Core pages.
	$pages = array(
		'about-us' => array( 'About Us', '', '<!-- wp:paragraph --><p>BrightNest Clean makes it easy to book trusted home services. We carefully vet every professional, keep prices transparent and stand behind every job with our service guarantee.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Our promise</h2><!-- /wp:heading --><!-- wp:list --><ul><li>Background-checked, trained professionals</li><li>Upfront pricing with no hidden charges</li><li>On-time arrival and friendly support 7 days a week</li><li>Re-service guarantee if you are not happy</li></ul><!-- /wp:list -->' ),
		'contact'  => array( 'Contact', 'page-templates/contact.php', '<!-- wp:paragraph --><p>Have a question or need a custom quote? Send us a message and our team will get back to you within a few hours.</p><!-- /wp:paragraph -->' ),
		'faq'      => array( 'FAQs', '', '' ),
		'privacy-policy-bn' => array( 'Privacy Policy', '', '<!-- wp:paragraph --><p>Add your privacy policy here.</p><!-- /wp:paragraph -->' ),
		'terms'    => array( 'Terms & Conditions', '', '<!-- wp:paragraph --><p>Add your terms and conditions here.</p><!-- /wp:paragraph -->' ),
	);
	foreach ( $pages as $slug => $p ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $p[0], 'post_content' => $p[2] ) );
		if ( $id && $p[1] ) {
			update_post_meta( $id, '_wp_page_template', $p[1] );
		}
	}

	update_option( 'brightnest_starter_done', 1 );
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'brightnest_create_starter_content' );

function brightnest_flush_on_switch() {
	flush_rewrite_rules();
}
add_action( 'switch_theme', 'brightnest_flush_on_switch' );
