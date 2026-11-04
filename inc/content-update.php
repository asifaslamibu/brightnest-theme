<?php
/**
 * One-time content updates, each run once on the first admin page load after the theme is updated:
 * - Bookable services with their own text get a service page (same address, text, photo, FAQ,
 *   Google title, Arabic) that books them; the service itself then only opens the checkout.
 * - About Us: text from brightnestclean.ae (English and Arabic), a short line under the title,
 *   and a hero photo; Contact: a hero photo (photos only when the page has none).
 * - The FAQ page is unpublished (the menu and footer no longer link to it).
 * - Em and en dashes are taken out of the site's text: posts, pages, services, service pages,
 *   their options and Arabic text, and the BrightNest settings.
 * Orders, bookings, requests and users are not touched.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * A dash in text as a person would write it: a colon after a label, a comma elsewhere,
 * "to" in ranges ("Mon to Fri", "8:00 AM to 5:00 PM"), a hyphen between years, "|" in titles.
 */
function brightnest_undash( $s, $title = false, $ar = false ) {
	if ( ! is_string( $s ) || ( false === strpos( $s, '—' ) && false === strpos( $s, '–' ) && false === strpos( $s, '&#821' ) && false === strpos( $s, 'dash;' ) ) ) {
		return $s;
	}
	$d = '(?:—|–|&mdash;|&ndash;|&#8212;|&#8211;)';
	$s = preg_replace( '/((?:19|20)\d\d)\s*' . $d . '\s*((?:19|20)\d\d)/u', '$1-$2', $s );
	$s = preg_replace( '/(\d)\s+' . $d . '\s+(\d)/u', '$1 to $2', $s );
	$s = preg_replace( '/(\d)' . $d . '(\d)/u', '$1-$2', $s );
	$s = preg_replace( '/(\d\s?(?:AM|PM|am|pm))\s*' . $d . '\s*(\d)/u', '$1 to $2', $s );
	$s = preg_replace( '/(\w)(?:–|&ndash;|&#8211;)(\w)/u', $ar ? '$1 إلى $2' : '$1 to $2', $s );
	if ( $ar ) {
		$s = preg_replace( '/\s*(?:–|&ndash;|&#8211;)\s*(?=\d)/u', ' إلى ', $s );
		return preg_replace( '/\s*' . $d . '\s*/u', '، ', $s );
	}
	if ( $title ) {
		return preg_replace( '/\s*' . $d . '\s*/u', ' | ', $s );
	}
	$s = preg_replace( '/(<\/(?:strong|b)>)\s*' . $d . '\s*/u', '$1: ', $s );
	$s = preg_replace( '/(:<\/(?:strong|b)>)\s*' . $d . '\s*/u', '$1 ', $s );
	$s = preg_replace( '/(<li>[^<.,:]{2,50}?)\s+' . $d . '\s+/u', '$1: ', $s );
	$s = preg_replace( '/(^|\n)([^<\n.,:|]{2,50}?)\s+' . $d . '\s+/u', '$1$2: ', $s );
	$s = preg_replace( '/\s*' . $d . '\s*(?=[)"”<]|$)/u', '', $s );
	$s = preg_replace( '/\s*' . $d . '\s*/u', ', ', $s );
	return str_replace( array( ',,', ', ,', ',.', ', .', ',:', ', ?', ',?' ), array( ',', ',', '.', '.', ':', '?', '?' ), $s );
}

/** Each "|"-separated part on its own, so option lines and their "name|desc" keys stay matched. */
function brightnest_undash_parts( $s, $ar = false ) {
	if ( ! is_string( $s ) ) {
		return $s;
	}
	return preg_replace_callback(
		'/[^|\n]+/u',
		function ( $m ) use ( $ar ) {
			preg_match( '/^(\s*)(.*?)(\s*)$/us', $m[0], $p );
			return $p[1] . brightnest_undash( $p[2], false, $ar ) . $p[3];
		},
		$s
	);
}

function brightnest_undash_deep( $v, $ar = false ) {
	if ( is_array( $v ) ) {
		$out = array();
		foreach ( $v as $k => $x ) {
			$key         = is_string( $k ) ? brightnest_undash_parts( $k ) : $k;
			$out[ $key ] = brightnest_undash_deep( $x, $ar || ( is_string( $k ) && '_ar' === substr( $k, -3 ) ) );
		}
		return $out;
	}
	return brightnest_undash_parts( $v, $ar );
}

/** Gutenberg blocks from a short list: h2, p, ul, q (quote with a name). */
function brightnest_content_blocks( $parts ) {
	$out = '';
	foreach ( $parts as $p ) {
		list( $t, $v ) = $p;
		if ( 'h2' === $t ) {
			$out .= '<!-- wp:heading --><h2 class="wp-block-heading">' . $v . '</h2><!-- /wp:heading -->';
		} elseif ( 'p' === $t ) {
			$out .= '<!-- wp:paragraph --><p>' . $v . '</p><!-- /wp:paragraph -->';
		} elseif ( 'ul' === $t ) {
			$out .= '<!-- wp:list --><ul class="wp-block-list"><li>' . implode( '</li><li>', $v ) . '</li></ul><!-- /wp:list -->';
		} elseif ( 'q' === $t ) {
			$out .= '<!-- wp:quote --><blockquote class="wp-block-quote"><p>' . $v[0] . '</p><cite>' . $v[1] . '</cite></blockquote><!-- /wp:quote -->';
		}
	}
	return $out;
}

/** The theme photo as the page's featured image, unless the page already has one. */
function brightnest_content_page_photo( $page_id, $file, $alt ) {
	if ( has_post_thumbnail( $page_id ) || ! file_exists( BRIGHTNEST_DIR . '/assets/img/pages/' . $file ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$tmp = wp_tempnam( $file );
	copy( BRIGHTNEST_DIR . '/assets/img/pages/' . $file, $tmp );
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), $page_id, $alt );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	set_post_thumbnail( $page_id, $id );
}

function brightnest_content_about( $about ) {
	$contact = brightnest_core_page( 'contact' );
	$link    = $contact ? esc_url( get_permalink( $contact ) ) : esc_url( home_url( '/contact/' ) );
	$en      = brightnest_content_blocks(
		array(
			array( 'h2', 'Your Trusted Partner in Home and Office Cleanliness' ),
			array( 'p', 'Bright Nest Cleaning Co. is committed to delivering high-quality cleaning and maintenance solutions across the UAE.' ),
			array( 'p', 'From daily home upkeep to deep cleaning, and hourly maids to full property maintenance, we do it all with care and professionalism.' ),
			array( 'ul', array( '100% Satisfaction Guaranteed', 'Safe &amp; Eco-Friendly Practices', '24/7 Customer Support', 'Free Site Inspection &amp; Quote' ) ),
			array( 'h2', '25+ Years of Experience' ),
			array( 'p', 'Our team has more than 25 years of experience in cleaning and property maintenance. Every job is handled by trained staff who know how to look after homes, villas and offices in the UAE.' ),
			array( 'h2', 'Premium Cleaning &amp; Maintenance Solutions, 100% Spotless Results' ),
			array( 'p', 'We offer home and office cleaning, deep cleaning, move-in and move-out cleaning, pest control, AC maintenance, electrical and plumbing repairs, painting and handyman services. You can book most services online in under a minute.' ),
			array( 'h2', 'Some Feedback From Our Customers' ),
			array( 'q', array( 'I\'ve been using Bright Nest for maid services for 6 months now. The staff is polite, punctual, and my home has never felt this fresh. Highly recommended!', 'Ayesha Khan, Downtown Dubai' ) ),
			array( 'q', array( 'Our office cleaning has become completely stress-free. Their team is consistent, professional, and always leaves everything spotless.', 'Mohammed R., Business Bay' ) ),
			array( 'q', array( 'I booked a move-out cleaning and was amazed at the results. The place looked brand new! Definitely calling them again.', 'Sarah Malik, Jumeirah Lake Towers' ) ),
			array( 'q', array( 'They fixed our AC, cleaned the ducts, and even helped with plumbing. Truly a one-stop solution for maintenance and cleaning.', 'Ali Zafar, Al Barsha' ) ),
			array( 'p', 'Have a question or need a quote? <a href="' . $link . '">Contact us</a> and our team will get back to you shortly.' ),
		)
	);
	$ar      = brightnest_content_blocks(
		array(
			array( 'h2', 'شريكك الموثوق لنظافة المنزل والمكتب' ),
			array( 'p', 'تلتزم برايت نست للتنظيف بتقديم حلول تنظيف وصيانة عالية الجودة في جميع أنحاء الإمارات.' ),
			array( 'p', 'من العناية اليومية بالمنزل إلى التنظيف العميق، ومن العاملات بالساعة إلى الصيانة الشاملة للعقارات، نقوم بكل ذلك بعناية واحترافية.' ),
			array( 'ul', array( 'رضا مضمون 100%', 'ممارسات آمنة وصديقة للبيئة', 'دعم العملاء على مدار الساعة طوال أيام الأسبوع', 'معاينة الموقع وعرض السعر مجانًا' ) ),
			array( 'h2', 'أكثر من 25 عامًا من الخبرة' ),
			array( 'p', 'يمتلك فريقنا أكثر من 25 عامًا من الخبرة في التنظيف وصيانة العقارات. ينفذ كل عمل موظفون مدرَّبون يعرفون كيف يعتنون بالمنازل والفلل والمكاتب في الإمارات.' ),
			array( 'h2', 'حلول تنظيف وصيانة متميزة، ونتائج نظيفة 100%' ),
			array( 'p', 'نقدم تنظيف المنازل والمكاتب، والتنظيف العميق، وتنظيف الانتقال من وإلى المسكن، ومكافحة الحشرات، وصيانة المكيفات، وإصلاحات الكهرباء والسباكة، والدهان وخدمات الصيانة العامة. يمكنك حجز معظم الخدمات عبر الإنترنت في أقل من دقيقة.' ),
			array( 'h2', 'آراء بعض عملائنا' ),
			array( 'q', array( 'أستخدم برايت نست لخدمات العاملات منذ 6 أشهر. الموظفات مهذبات وملتزمات بالمواعيد، ولم يكن منزلي بهذه النضارة من قبل. أنصح بهم بشدة!', 'عائشة خان، داون تاون دبي' ) ),
			array( 'q', array( 'أصبح تنظيف مكتبنا خاليًا تمامًا من الهموم. فريقهم ثابت ومحترف ويترك كل شيء نظيفًا دائمًا.', 'محمد ر.، الخليج التجاري' ) ),
			array( 'q', array( 'حجزت تنظيف إخلاء المسكن وأذهلتني النتيجة. بدا المكان جديدًا تمامًا! سأتصل بهم مرة أخرى بالتأكيد.', 'سارة مالك، أبراج بحيرات جميرا' ) ),
			array( 'q', array( 'أصلحوا المكيف ونظفوا مجاري الهواء وساعدونا حتى في السباكة. حل متكامل للصيانة والتنظيف.', 'علي ظفر، البرشاء' ) ),
			array( 'p', 'لديك سؤال أو تحتاج إلى عرض سعر؟ <a href="' . $link . '">تواصل معنا</a> وسيرد عليك فريقنا قريبًا.' ),
		)
	);
	update_post_meta( $about->ID, '_bn_old_about', wp_slash( $about->post_content ) ); // The previous text, in case it is wanted back.
	wp_update_post(
		wp_slash(
			array(
				'ID'           => $about->ID,
				'post_content' => $en,
				'post_excerpt' => 'Trusted cleaning and maintenance for homes and offices across the UAE.',
			)
		)
	);
	update_post_meta( $about->ID, '_bn_ar_content', wp_slash( $ar ) );
	update_post_meta( $about->ID, '_bn_ar_excerpt', 'تنظيف وصيانة موثوقة للمنازل والمكاتب في جميع أنحاء الإمارات.' );
}

function brightnest_content_undash_all() {
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT ID, post_title, post_content, post_excerpt FROM $wpdb->posts WHERE post_type NOT IN ('revision','nav_menu_item','shop_order','shop_order_refund','bn_request') AND post_status IN ('publish','draft','private','future','pending','inherit') AND (post_title LIKE '%–%' OR post_title LIKE '%—%' OR post_content LIKE '%–%' OR post_content LIKE '%—%' OR post_excerpt LIKE '%–%' OR post_excerpt LIKE '%—%')" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( $rows as $r ) {
		$u = array();
		foreach ( array( 'post_title' => true, 'post_content' => false, 'post_excerpt' => false ) as $f => $is_title ) {
			$n = brightnest_undash( $r->$f, $is_title );
			if ( $n !== $r->$f ) {
				$u[ $f ] = $n;
			}
		}
		if ( $u ) {
			$wpdb->update( $wpdb->posts, $u, array( 'ID' => $r->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( $r->ID );
		}
	}
	// Text kept beside posts (service options, FAQs, Arabic text, headings); not file names or links.
	$meta = $wpdb->get_results( "SELECT m.meta_id, m.meta_key, m.meta_value FROM $wpdb->postmeta m JOIN $wpdb->posts p ON p.ID = m.post_id WHERE p.post_type NOT IN ('shop_order','shop_order_refund','bn_request') AND m.meta_key NOT IN ('_bn_old_content','_bn_old_about','_wp_attachment_metadata','_wp_attached_file','_source_url','_wp_old_slug') AND m.meta_key NOT LIKE '%url%' AND m.meta_key NOT LIKE '%file%' AND (m.meta_value LIKE '%–%' OR m.meta_value LIKE '%—%' OR m.meta_value LIKE '%u2013%' OR m.meta_value LIKE '%u2014%')" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( $meta as $r ) {
		$ar   = false !== strpos( $r->meta_key, '_ar_' ) || '_ar' === substr( $r->meta_key, -3 );
		$val  = maybe_unserialize( $r->meta_value );
		$json = is_string( $val ) && in_array( substr( $val, 0, 1 ), array( '{', '[' ), true ) ? json_decode( $val, true ) : null;
		if ( is_array( $json ) ) {
			$new     = wp_json_encode( brightnest_undash_deep( $json, $ar ), JSON_UNESCAPED_UNICODE );
			$changed = wp_json_encode( $json, JSON_UNESCAPED_UNICODE ) !== $new;
		} else {
			$new     = brightnest_undash_deep( $val, $ar );
			$changed = $new !== $val;
		}
		if ( $changed ) {
			update_metadata_by_mid( 'post', $r->meta_id, $new );
		}
	}
	foreach ( get_theme_mods() as $k => $v ) {
		$n = brightnest_undash_deep( $v, is_string( $k ) && '_ar' === substr( $k, -3 ) );
		if ( $n !== $v ) {
			set_theme_mod( $k, $n );
		}
	}
	foreach ( $wpdb->get_results( "SELECT term_taxonomy_id, description FROM $wpdb->term_taxonomy WHERE description LIKE '%–%' OR description LIKE '%—%'" ) as $r ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->update( $wpdb->term_taxonomy, array( 'description' => brightnest_undash( $r->description ) ), array( 'term_taxonomy_id' => $r->term_taxonomy_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
}

/**
 * Bookable services that have their own text: that text moves to a service page with the same
 * address (the service is renamed "<name>-booking"), and the page books the service.
 * If a service page with the same name already exists, it only gets the booking link (if it has none).
 */
function brightnest_content_service_pages() {
	global $wpdb;
	$keys = array( '_bn_icon', '_bn_faqs', '_bn_seo_title', '_bn_seo_desc', '_bn_h1', '_bn_source_url', '_bn_ar_title', '_bn_ar_excerpt', '_bn_ar_content', '_bn_ar_faqs', '_bn_home_group', '_thumbnail_id' );
	foreach ( get_posts( array( 'post_type' => 'bn_service', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $sv ) {
		if ( strlen( trim( wp_strip_all_tags( $sv->post_content ) ) ) < 400 ) {
			continue; // No text of its own.
		}
		$same = get_posts( array( 'post_type' => 'bn_info', 'post_status' => array( 'publish', 'draft', 'private' ), 'title' => $sv->post_title, 'posts_per_page' => 1 ) );
		if ( $same ) {
			if ( ! get_post_meta( $same[0]->ID, '_bn_book_service', true ) ) {
				update_post_meta( $same[0]->ID, '_bn_book_service', $sv->ID );
			}
			continue;
		}
		$slug = $sv->post_name;
		$wpdb->update( $wpdb->posts, array( 'post_name' => $slug . '-booking' ), array( 'ID' => $sv->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		clean_post_cache( $sv->ID );
		$page = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'bn_info',
					'post_status'  => 'publish',
					'post_title'   => $sv->post_title,
					'post_name'    => $slug,
					'post_content' => $sv->post_content,
					'post_excerpt' => $sv->post_excerpt,
					'menu_order'   => $sv->menu_order,
					'post_author'  => $sv->post_author,
				)
			),
			true
		);
		if ( is_wp_error( $page ) ) {
			continue;
		}
		foreach ( $keys as $k ) {
			$v = get_post_meta( $sv->ID, $k, true );
			if ( '' !== $v && array() !== $v ) {
				update_post_meta( $page, $k, wp_slash( $v ) );
			}
		}
		update_post_meta( $page, '_bn_book_service', $sv->ID );
		update_post_meta( $page, '_bn_from_service', $sv->ID );
	}
}

/**
 * Runs the updates not done yet (each once), on an admin page load by an administrator.
 */
function brightnest_content_update() {
	$steps = array(
		'about'         => 'brightnest_content_step_about',
		'photos'        => 'brightnest_content_step_photos',
		'faq'           => 'brightnest_content_step_faq',
		'undash'        => 'brightnest_content_undash_all',
		'service-pages' => 'brightnest_content_service_pages',
		'home-hero'     => 'brightnest_content_step_home_hero',
	);
	$done = (array) get_option( 'bn_content_steps', array() );
	if ( '2026-11-a' === get_option( 'bn_content_version' ) ) {
		$done = array_unique( array_merge( $done, array( 'about', 'photos', 'faq', 'undash' ) ) ); // Done by the first version.
	}
	$todo = array_diff( array_keys( $steps ), $done );
	if ( ! $todo || ! current_user_can( 'manage_options' ) || get_transient( 'bn_content_lock' ) ) {
		return;
	}
	set_transient( 'bn_content_lock', 1, 5 * MINUTE_IN_SECONDS );
	foreach ( $todo as $step ) {
		$done[] = $step;
		update_option( 'bn_content_steps', array_values( array_unique( $done ) ) ); // Saved first: a failure halfway never repeats a step.
		call_user_func( $steps[ $step ] );
	}
	delete_transient( 'bn_content_lock' );
	wp_cache_flush();
	do_action( 'litespeed_purge_all' );
}
add_action( 'admin_init', 'brightnest_content_update', 20 );

function brightnest_content_step_about() {
	$about = brightnest_core_page( 'about-us' );
	if ( $about ) {
		brightnest_content_about( $about );
	}
}

function brightnest_content_step_photos() {
	$about = brightnest_core_page( 'about-us' );
	if ( $about ) {
		brightnest_content_page_photo( $about->ID, 'about-hero-cleaner.jpg', 'Bright Nest cleaner mopping a living room floor' );
	}
	$contact = brightnest_core_page( 'contact' );
	if ( $contact ) {
		brightnest_content_page_photo( $contact->ID, 'contact-hero-team.jpg', 'Bright Nest cleaning team at work in a home' );
	}
}

function brightnest_content_step_faq() {
	$faq = get_page_by_path( 'faq' );
	if ( $faq && 'publish' === $faq->post_status ) {
		wp_update_post( array( 'ID' => $faq->ID, 'post_status' => 'draft' ) );
	}
}

/* Homepage hero: the theme's new photo (assets/img/site/home-hero.jpg) instead of one picked earlier. */
function brightnest_content_step_home_hero() {
	set_theme_mod( 'bn_hero_img_1', '' );
}
