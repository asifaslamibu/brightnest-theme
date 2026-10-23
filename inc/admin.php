<?php
/**
 * Admin experience: BrightNest dashboard, simpler service editor, list columns,
 * duplicate action, booking status shortcuts.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Editor & assets
 * ---------------------------------------------------------------------- */

/**
 * Services use the classic editor so the details box sits right under the description.
 */
function brightnest_service_classic_editor( $use, $post_type ) {
	return 'bn_service' === $post_type ? false : $use;
}
add_filter( 'use_block_editor_for_post_type', 'brightnest_service_classic_editor', 10, 2 );

/**
 * Screens that get the BrightNest admin styles.
 */
function brightnest_is_bn_screen() {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return false;
	}
	return in_array( $screen->post_type, array( 'bn_service', 'bn_request', 'bn_review' ), true )
		|| 'toplevel_page_brightnest' === $screen->id
		|| 'dashboard' === $screen->id;
}

function brightnest_admin_assets() {
	if ( ! brightnest_is_bn_screen() ) {
		return;
	}
	wp_enqueue_style( 'brightnest-admin', BRIGHTNEST_URI . '/assets/admin/admin.css', array(), BRIGHTNEST_VERSION );
	$screen = get_current_screen();
	if ( 'bn_service_cat' === $screen->taxonomy && in_array( $screen->base, array( 'edit-tags', 'term' ), true ) ) {
		wp_enqueue_media(); // Category icon image.
		wp_enqueue_script( 'brightnest-settings', BRIGHTNEST_URI . '/assets/admin/settings.js', array(), BRIGHTNEST_VERSION, true );
	}
	if ( 'bn_service' === $screen->post_type && 'post' === $screen->base ) {
		wp_enqueue_script( 'brightnest-settings', BRIGHTNEST_URI . '/assets/admin/settings.js', array(), BRIGHTNEST_VERSION, true );
		wp_enqueue_media(); // Option photos.
		wp_enqueue_script( 'brightnest-admin', BRIGHTNEST_URI . '/assets/admin/admin.js', array(), BRIGHTNEST_VERSION, true );
		wp_localize_script(
			'brightnest-admin',
			'BN_ADMIN',
			array(
				'currency' => brightnest_opt( 'currency' ),
				'i18n'     => array(
					'tab'         => __( 'Tab name', 'brightnest' ),
					'tabPh'       => __( 'e.g. Bestsellers, Manicure, Apartment', 'brightnest' ),
					'name'        => __( 'Option name', 'brightnest' ),
					'desc'        => __( 'Short description', 'brightnest' ),
					'price'       => __( 'Price', 'brightnest' ),
					'old'         => __( 'Was', 'brightnest' ),
					'addOption'   => __( 'Add option', 'brightnest' ),
					'removeTab'   => __( 'Remove tab', 'brightnest' ),
					'remove'      => __( 'Remove', 'brightnest' ),
					'up'          => __( 'Move up', 'brightnest' ),
					'down'        => __( 'Move down', 'brightnest' ),
					'confirmTab'  => __( 'Remove this tab and all its options?', 'brightnest' ),
					'empty'       => __( 'No options yet. Click "Add tab" to start, or leave empty to sell the service at its starting price.', 'brightnest' ),
					'item'        => __( 'e.g. Sofa cleaning materials included', 'brightnest' ),
					'newTab'      => __( 'Packages', 'brightnest' ),
					'hourlyEx'    => __( 'Example: 3 hours × 2 professionals = %s (+ %s with materials)', 'brightnest' ),
					'options'     => __( 'options', 'brightnest' ),
					'photo'       => __( 'Photo (shown in the checkout)', 'brightnest' ),
					'choosePhoto' => __( 'Choose option photo', 'brightnest' ),
					'details'     => __( 'Popup details (shown when a customer clicks the option)', 'brightnest' ),
					'about'       => __( 'About this option (popup text)', 'brightnest' ),
					'nameAr'      => __( 'Arabic name (العربية)', 'brightnest' ),
					'descAr'      => __( 'Arabic description', 'brightnest' ),
					'aboutAr'     => __( 'Arabic popup text', 'brightnest' ),
					'hlAr'        => __( 'Arabic highlights (same format)', 'brightnest' ),
					'tabImg'      => __( 'Tab picture (checkout strip)', 'brightnest' ),
					'tabAr'       => __( 'Arabic tab name', 'brightnest' ),
					'highlights'  => __( 'Highlights, one per line. Optional icon first: shield | 60-Day Warranty (icons: shield, clock, star, check-circle, users, home, calendar, tag, ac, cleaning, sofa, water, pest, spa)', 'brightnest' ),
				),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'brightnest_admin_assets' );

/**
 * Friendlier title placeholder.
 */
function brightnest_service_title_placeholder( $text, $post ) {
	return 'bn_service' === $post->post_type ? __( 'Service name, e.g. Sofa Cleaning', 'brightnest' ) : $text;
}
add_filter( 'enter_title_here', 'brightnest_service_title_placeholder', 10, 2 );

/**
 * Service screen order: title → short description → Service Details → full description.
 * The two boxes are moved into a custom "bn_top" context printed right after the title.
 */
function brightnest_service_top_boxes() {
	remove_meta_box( 'postexcerpt', 'bn_service', 'normal' );
	remove_meta_box( 'bn_service_details', 'bn_service', 'normal' );
	remove_meta_box( 'pageparentdiv', 'bn_service', 'side' ); // Display order lives in the Basics tab.
	add_meta_box(
		'postexcerpt',
		__( 'Short description — shown on cards and at the top of the service page', 'brightnest' ),
		'post_excerpt_meta_box',
		'bn_service',
		'bn_top',
		'high'
	);
	add_meta_box( 'bn_service_details', __( 'Service Details', 'brightnest' ), 'brightnest_service_meta_box', 'bn_service', 'bn_top', 'high' );
}
add_action( 'add_meta_boxes_bn_service', 'brightnest_service_top_boxes', 20 );

function brightnest_service_print_top_boxes( $post ) {
	if ( 'bn_service' !== $post->post_type ) {
		return;
	}
	echo '<div class="bn-top-boxes">';
	do_meta_boxes( get_current_screen(), 'bn_top', $post );
	echo '</div><h2 class="bn-editor-label">' . esc_html__( 'Full description (service page content)', 'brightnest' ) . '</h2>';
}
add_action( 'edit_form_after_title', 'brightnest_service_print_top_boxes' );

/* -------------------------------------------------------------------------
 * Menu: BrightNest dashboard + new-bookings bubble
 * ---------------------------------------------------------------------- */

function brightnest_new_requests_count() {
	$q = new WP_Query(
		array(
			'post_type'      => 'bn_request',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'OR',
				array( 'key' => '_bn_status', 'value' => 'new' ),
				array( 'key' => '_bn_status', 'compare' => 'NOT EXISTS' ),
			),
		)
	);
	return (int) $q->found_posts;
}

/**
 * One "BrightNest" menu; Bookings, Services, Categories, Reviews and Settings live under it.
 * The post types register with show_in_menu => 'brightnest' (see post-types.php).
 */
function brightnest_admin_menu() {
	$count  = brightnest_new_requests_count();
	$bubble = $count ? ' <span class="awaiting-mod"><span class="pending-count">' . absint( $count ) . '</span></span>' : '';

	add_menu_page( __( 'BrightNest', 'brightnest' ), __( 'BrightNest', 'brightnest' ) . $bubble, 'edit_posts', 'brightnest', 'brightnest_dashboard_page', 'dashicons-admin-home', 3 );
	add_submenu_page( 'brightnest', __( 'Dashboard', 'brightnest' ), __( 'Dashboard', 'brightnest' ), 'edit_posts', 'brightnest', 'brightnest_dashboard_page' );
	add_submenu_page( 'brightnest', __( 'Add Service', 'brightnest' ), __( 'Add Service', 'brightnest' ), 'edit_posts', 'post-new.php?post_type=bn_service' );
	add_submenu_page( 'brightnest', __( 'Service Categories', 'brightnest' ), __( 'Categories', 'brightnest' ), 'manage_categories', 'edit-tags.php?taxonomy=bn_service_cat&post_type=bn_service' );
	add_submenu_page( 'brightnest', __( 'Cities & Areas', 'brightnest' ), __( 'Cities & Areas', 'brightnest' ), 'manage_categories', 'edit-tags.php?taxonomy=bn_area&post_type=bn_service' );
}
add_action( 'admin_menu', 'brightnest_admin_menu', 9 );

/**
 * Order the submenu and label the post-type entries.
 */
function brightnest_admin_menu_order() {
	global $submenu;
	if ( empty( $submenu['brightnest'] ) ) {
		return;
	}
	$count  = brightnest_new_requests_count();
	$bubble = $count ? ' <span class="awaiting-mod"><span class="pending-count">' . absint( $count ) . '</span></span>' : '';
	$labels = array(
		'brightnest'                                              => array( 0, __( 'Dashboard', 'brightnest' ) ),
		'edit.php?post_type=bn_request'                           => array( 1, __( 'Bookings', 'brightnest' ) . $bubble ),
		'edit.php?post_type=bn_service'                           => array( 2, __( 'Services', 'brightnest' ) ),
		'post-new.php?post_type=bn_service'                       => array( 3, __( 'Add Service', 'brightnest' ) ),
		'edit-tags.php?taxonomy=bn_service_cat&post_type=bn_service' => array( 4, __( 'Categories', 'brightnest' ) ),
		'edit-tags.php?taxonomy=bn_area&post_type=bn_service'     => array( 4.5, __( 'Cities & Areas', 'brightnest' ) ),
		'brightnest-settings'                                     => array( 6, __( 'Settings', 'brightnest' ) ),
	);
	$items = $submenu['brightnest'];
	foreach ( $items as $i => $item ) {
		if ( isset( $labels[ $item[2] ] ) ) {
			$items[ $i ][0] = $labels[ $item[2] ][1];
		}
	}
	usort(
		$items,
		function ( $a, $b ) use ( $labels ) {
			$pa = isset( $labels[ $a[2] ] ) ? $labels[ $a[2] ][0] : 50;
			$pb = isset( $labels[ $b[2] ] ) ? $labels[ $b[2] ][0] : 50;
			return $pa <=> $pb; // Positions can be decimals (4.5 = right after Categories).
		}
	);
	$submenu['brightnest'] = $items; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
}
add_action( 'admin_menu', 'brightnest_admin_menu_order', 999 );

/**
 * Keep the BrightNest menu open and the right item highlighted on related screens.
 */
function brightnest_admin_parent_file( $parent ) {
	$screen = get_current_screen();
	if ( $screen && ( in_array( $screen->post_type, array( 'bn_service', 'bn_request', 'bn_review' ), true ) || 'bn_service_cat' === $screen->taxonomy ) ) {
		return 'brightnest';
	}
	return $parent;
}
add_filter( 'parent_file', 'brightnest_admin_parent_file' );

function brightnest_admin_submenu_file( $file ) {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return $file;
	}
	if ( 'bn_service_cat' === $screen->taxonomy ) {
		return 'edit-tags.php?taxonomy=bn_service_cat&post_type=bn_service';
	}
	if ( 'bn_service' === $screen->post_type && 'add' === $screen->action ) {
		return 'post-new.php?post_type=bn_service';
	}
	if ( in_array( $screen->post_type, array( 'bn_service', 'bn_request', 'bn_review' ), true ) ) {
		return 'edit.php?post_type=' . $screen->post_type;
	}
	return $file;
}
add_filter( 'submenu_file', 'brightnest_admin_submenu_file' );

/**
 * Booking data for the dashboard (last 60 days), in one pass.
 */
function brightnest_dashboard_data() {
	$ids = get_posts(
		array(
			'post_type'      => 'bn_request',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'date_query'     => array( array( 'after' => '60 days ago' ) ),
		)
	);

	$now      = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
	$today    = strtotime( wp_date( 'Y-m-d', $now ) . ' 00:00:00' );
	$days     = array();
	for ( $i = 13; $i >= 0; $i-- ) {
		$days[ wp_date( 'Y-m-d', $today - $i * DAY_IN_SECONDS ) ] = 0;
	}
	$data = array(
		'days'      => $days,
		'this_week' => 0,
		'last_week' => 0,
		'month_val' => 0,
		'status'    => array_fill_keys( array_keys( brightnest_request_statuses() ), 0 ),
		'services'  => array(),
	);
	$month = wp_date( 'Y-m', $now );

	foreach ( $ids as $id ) {
		$ts     = get_post_time( 'U', false, $id );
		$day    = wp_date( 'Y-m-d', $ts );
		$age    = ( $today + DAY_IN_SECONDS - $ts ) / DAY_IN_SECONDS;
		$status = get_post_meta( $id, '_bn_status', true ) ?: 'new';
		$type   = get_post_meta( $id, '_bn_type', true );

		if ( isset( $data['days'][ $day ] ) ) {
			$data['days'][ $day ]++;
		}
		if ( $age <= 7 ) {
			$data['this_week']++;
		} elseif ( $age <= 14 ) {
			$data['last_week']++;
		}
		if ( isset( $data['status'][ $status ] ) ) {
			$data['status'][ $status ]++;
		}
		if ( 'cancelled' !== $status && wp_date( 'Y-m', $ts ) === $month ) {
			$data['month_val'] += (float) get_post_meta( $id, '_bn_total', true );
		}
		if ( 'contact' !== $type ) {
			$svc = get_post_meta( $id, '_bn_service', true );
			if ( $svc ) {
				$data['services'][ $svc ] = ( isset( $data['services'][ $svc ] ) ? $data['services'][ $svc ] : 0 ) + 1;
			}
		}
	}
	arsort( $data['services'] );
	$data['services'] = array_slice( $data['services'], 0, 5, true );
	return $data;
}

/**
 * Setup checklist items: [done, label, link].
 */
function brightnest_setup_checklist() {
	$defaults = brightnest_defaults();
	$with_img = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_thumbnail_id' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	$cust     = admin_url( 'admin.php?page=brightnest-settings' );
	$name     = get_bloginfo( 'name' );
	return array(
		array( brightnest_opt( 'phone' ) !== $defaults['phone'], __( 'Add your real phone number', 'brightnest' ), $cust ),
		array( brightnest_opt( 'whatsapp' ) !== $defaults['whatsapp'], __( 'Add your WhatsApp number', 'brightnest' ), $cust ),
		array(
			function_exists( 'brightnest_ziina_gateway' ) && brightnest_ziina_gateway() && 'yes' !== brightnest_ziina_gateway()->get_option( 'is_test' ),
			__( 'Switch Ziina payments to live mode', 'brightnest' ),
			admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ziina' ),
		),
		array( $name && $name !== strtolower( $name ), __( 'Set a proper site title (e.g. "BrightNest Clean")', 'brightnest' ), admin_url( 'options-general.php' ) ),
		array( has_custom_logo(), __( 'Upload your logo', 'brightnest' ), admin_url( 'customize.php?autofocus[section]=title_tagline' ) ),
		array( ! empty( $with_img ), __( 'Add photos to your services', 'brightnest' ), admin_url( 'edit.php?post_type=bn_service' ) ),
		array( '' !== brightnest_greviews_embed(), __( 'Connect your Google reviews', 'brightnest' ), admin_url( 'admin.php?page=brightnest-settings&tab=homepage' ) ),
	);
}

/**
 * BrightNest dashboard page.
 */
function brightnest_dashboard_page() {
	$d        = brightnest_dashboard_data();
	$cur      = brightnest_opt( 'currency' );
	$new      = brightnest_new_requests_count();
	$services = wp_count_posts( 'bn_service' );
	$recent   = get_posts( array( 'post_type' => 'bn_request', 'posts_per_page' => 6 ) );
	$req_url  = admin_url( 'edit.php?post_type=bn_request' );
	$user     = wp_get_current_user();
	$hour     = (int) wp_date( 'G' );
	$greeting = $hour < 12 ? __( 'Good morning', 'brightnest' ) : ( $hour < 17 ? __( 'Good afternoon', 'brightnest' ) : __( 'Good evening', 'brightnest' ) );
	$delta    = $d['this_week'] - $d['last_week'];
	$max_day  = max( 1, max( $d['days'] ) );
	$total_st = array_sum( $d['status'] );
	$checks   = brightnest_setup_checklist();
	$done     = count( array_filter( wp_list_pluck( $checks, 0 ) ) );
	$st_label = brightnest_request_statuses();
	$st_icon  = array( 'new' => 'bell', 'confirmed' => 'yes-alt', 'completed' => 'flag', 'cancelled' => 'dismiss' );
	?>
	<div class="wrap bn-dash">
		<h1 class="screen-reader-text"><?php esc_html_e( 'BrightNest dashboard', 'brightnest' ); ?></h1>

		<header class="bn-dash-hero">
			<div>
				<img class="bn-dash-logo" src="<?php echo esc_url( BRIGHTNEST_URI . '/assets/img/logo-light.png' ); ?>" width="720" height="200" alt="">
				<p class="bn-dash-date"><?php echo esc_html( wp_date( 'l, j F Y' ) ); ?></p>
				<h2><?php echo esc_html( $greeting . ', ' . ( $user->display_name ? $user->display_name : $user->user_login ) ); ?> 👋</h2>
				<p>
					<?php
					if ( $new ) {
						/* translators: %d: number of new bookings */
						echo esc_html( sprintf( _n( 'You have %d new booking waiting for confirmation.', 'You have %d new bookings waiting for confirmation.', $new, 'brightnest' ), $new ) );
					} else {
						esc_html_e( 'All bookings are handled. Nice work!', 'brightnest' );
					}
					?>
				</p>
			</div>
			<div class="bn-dash-hero-actions">
				<a class="button bn-btn-yellow" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=bn_service' ) ); ?>"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add service', 'brightnest' ); ?></a>
				<a class="button bn-btn-glass" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-external"></span> <?php esc_html_e( 'View website', 'brightnest' ); ?></a>
			</div>
		</header>

		<section class="bn-kpis" aria-label="<?php esc_attr_e( 'Key numbers', 'brightnest' ); ?>">
			<a class="bn-kpi <?php echo $new ? 'is-alert' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'bn_status', 'new', $req_url ) ); ?>">
				<span class="bn-kpi-icon"><span class="dashicons dashicons-bell"></span></span>
				<span class="bn-kpi-label"><?php esc_html_e( 'New bookings', 'brightnest' ); ?></span>
				<strong><?php echo esc_html( number_format_i18n( $new ) ); ?></strong>
				<span class="bn-kpi-foot"><?php echo $new ? esc_html__( 'Need confirmation →', 'brightnest' ) : esc_html__( 'Nothing waiting', 'brightnest' ); ?></span>
			</a>
			<a class="bn-kpi" href="<?php echo esc_url( $req_url ); ?>">
				<span class="bn-kpi-icon"><span class="dashicons dashicons-calendar-alt"></span></span>
				<span class="bn-kpi-label"><?php esc_html_e( 'Bookings · last 7 days', 'brightnest' ); ?></span>
				<strong><?php echo esc_html( number_format_i18n( $d['this_week'] ) ); ?></strong>
				<span class="bn-kpi-foot <?php echo $delta > 0 ? 'is-up' : ( $delta < 0 ? 'is-down' : '' ); ?>">
					<?php
					/* translators: %s: signed difference */
					echo esc_html( sprintf( __( '%s vs previous 7 days', 'brightnest' ), ( $delta > 0 ? '▲ +' : ( $delta < 0 ? '▼ ' : '' ) ) . $delta ) );
					?>
				</span>
			</a>
			<div class="bn-kpi">
				<span class="bn-kpi-icon"><span class="dashicons dashicons-money-alt"></span></span>
				<span class="bn-kpi-label"><?php esc_html_e( 'Booked value · this month', 'brightnest' ); ?></span>
				<strong><?php echo esc_html( $cur . ' ' . number_format_i18n( $d['month_val'] ) ); ?></strong>
				<span class="bn-kpi-foot"><?php esc_html_e( 'Excludes cancelled bookings', 'brightnest' ); ?></span>
			</div>
			<a class="bn-kpi" href="<?php echo esc_url( admin_url( 'edit.php?post_type=bn_service' ) ); ?>">
				<span class="bn-kpi-icon"><span class="dashicons dashicons-admin-tools"></span></span>
				<span class="bn-kpi-label"><?php esc_html_e( 'Live services', 'brightnest' ); ?></span>
				<strong><?php echo esc_html( number_format_i18n( $services->publish ) ); ?></strong>
				<span class="bn-kpi-foot"><?php echo esc_html( sprintf( /* translators: %d drafts */ __( '%d drafts', 'brightnest' ), (int) $services->draft ) ); ?></span>
			</a>
		</section>

		<div class="bn-dash-grid">
			<?php
			$jobs = get_posts(
				array(
					'post_type'      => 'bn_request',
					'posts_per_page' => 8,
					'meta_key'       => '_bn_date', // phpcs:ignore WordPress.DB.SlowDBQuery
					'orderby'        => array( 'meta_value' => 'ASC', 'date' => 'ASC' ),
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array( 'key' => '_bn_date', 'value' => wp_date( 'Y-m-d' ), 'compare' => '>=' ),
						array(
							'relation' => 'OR',
							array( 'key' => '_bn_status', 'compare' => 'NOT EXISTS' ),
							array( 'key' => '_bn_status', 'value' => array( 'cancelled', 'completed' ), 'compare' => 'NOT IN' ),
						),
					),
				)
			);
			$today_iso = wp_date( 'Y-m-d' );
			$tmrw_iso  = wp_date( 'Y-m-d', time() + DAY_IN_SECONDS );
			?>
			<section class="bn-panel bn-panel-full">
				<div class="bn-panel-head">
					<h2><?php esc_html_e( 'Upcoming jobs', 'brightnest' ); ?></h2>
					<span class="bn-muted"><?php esc_html_e( 'By service date', 'brightnest' ); ?></span>
				</div>
				<?php if ( $jobs ) : ?>
					<ul class="bn-bookings bn-jobs">
						<?php foreach ( $jobs as $r ) : ?>
							<?php
							$jd    = get_post_meta( $r->ID, '_bn_date', true );
							$day   = $jd === $today_iso ? __( 'Today', 'brightnest' ) : ( $jd === $tmrw_iso ? __( 'Tomorrow', 'brightnest' ) : wp_date( 'D j M', strtotime( $jd ) ) );
							$phone = preg_replace( '/\D/', '', (string) get_post_meta( $r->ID, '_bn_phone', true ) );
							?>
							<li>
								<span class="bn-job-day <?php echo $jd === $today_iso ? 'is-today' : ''; ?>"><strong><?php echo esc_html( $day ); ?></strong><small><?php echo esc_html( get_post_meta( $r->ID, '_bn_time', true ) ); ?></small></span>
								<span class="bn-booking-main">
									<a href="<?php echo esc_url( get_edit_post_link( $r->ID ) ); ?>"><strong><?php echo esc_html( get_post_meta( $r->ID, '_bn_name', true ) ?: $r->post_title ); ?></strong></a>
									<small><?php echo esc_html( get_post_meta( $r->ID, '_bn_service', true ) ); ?> · <?php echo esc_html( get_post_meta( $r->ID, '_bn_address', true ) ?: get_post_meta( $r->ID, '_bn_area', true ) ); ?></small>
								</span>
								<span class="bn-job-pay"><?php echo function_exists( 'brightnest_payment_badge' ) ? brightnest_payment_badge( $r->ID ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
								<span class="bn-booking-actions">
									<?php if ( $phone ) : ?>
										<a class="button button-small" href="<?php echo esc_url( 'tel:+' . $phone ); ?>" title="<?php esc_attr_e( 'Call', 'brightnest' ); ?>"><span class="dashicons dashicons-phone"></span></a>
										<a class="button button-small" href="<?php echo esc_url( 'https://wa.me/' . $phone ); ?>" target="_blank" rel="noopener" title="WhatsApp"><span class="dashicons dashicons-whatsapp"></span></a>
									<?php endif; ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="bn-empty"><?php esc_html_e( 'No upcoming jobs. New bookings will show here by their service date.', 'brightnest' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="bn-panel bn-panel-chart">
				<div class="bn-panel-head">
					<h2><?php esc_html_e( 'Bookings — last 14 days', 'brightnest' ); ?></h2>
					<span class="bn-muted"><?php echo esc_html( sprintf( /* translators: %d total */ __( '%d total', 'brightnest' ), array_sum( $d['days'] ) ) ); ?></span>
				</div>
				<div class="bn-chart" role="img" aria-label="<?php esc_attr_e( 'Bar chart of bookings per day for the last 14 days', 'brightnest' ); ?>">
					<div class="bn-chart-grid" aria-hidden="true"><span><?php echo esc_html( $max_day ); ?></span><span><?php echo esc_html( $max_day > 1 ? round( $max_day / 2 ) : '' ); ?></span><span>0</span></div>
					<div class="bn-chart-bars">
						<?php foreach ( $d['days'] as $day => $n ) : ?>
							<?php $ts = strtotime( $day ); ?>
							<div class="bn-bar-col" tabindex="0">
								<span class="bn-bar <?php echo $n ? '' : 'is-zero'; ?>" style="height: <?php echo esc_attr( $n ? max( 4, round( $n / $max_day * 100 ) ) : 0 ); ?>%"></span>
								<span class="bn-bar-tip"><?php echo esc_html( wp_date( 'D j M', $ts ) ); ?> · <strong><?php echo esc_html( $n ); ?></strong></span>
								<span class="bn-bar-label"><?php echo esc_html( wp_date( 'j', $ts ) ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<details class="bn-chart-table">
					<summary><?php esc_html_e( 'Show as table', 'brightnest' ); ?></summary>
					<table class="bn-table"><thead><tr><th><?php esc_html_e( 'Day', 'brightnest' ); ?></th><th><?php esc_html_e( 'Bookings', 'brightnest' ); ?></th></tr></thead><tbody>
						<?php foreach ( $d['days'] as $day => $n ) : ?><tr><td><?php echo esc_html( wp_date( 'D j M', strtotime( $day ) ) ); ?></td><td><?php echo esc_html( $n ); ?></td></tr><?php endforeach; ?>
					</tbody></table>
				</details>
			</section>

			<section class="bn-panel">
				<div class="bn-panel-head"><h2><?php esc_html_e( 'Booking status', 'brightnest' ); ?></h2><span class="bn-muted"><?php esc_html_e( 'Last 60 days', 'brightnest' ); ?></span></div>
				<?php if ( $total_st ) : ?>
					<div class="bn-stack" aria-hidden="true">
						<?php foreach ( $d['status'] as $k => $n ) : ?>
							<?php if ( $n ) : ?><span class="bn-stack-<?php echo esc_attr( $k ); ?>" style="flex: <?php echo esc_attr( $n ); ?>"></span><?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<ul class="bn-status-list">
					<?php foreach ( $d['status'] as $k => $n ) : ?>
						<li>
							<a href="<?php echo esc_url( add_query_arg( 'bn_status', $k, $req_url ) ); ?>">
								<span class="bn-dot bn-dot-<?php echo esc_attr( $k ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $st_icon[ $k ] ); ?>"></span></span>
								<span class="bn-status-name"><?php echo esc_html( $st_label[ $k ] ); ?></span>
								<strong><?php echo esc_html( $n ); ?></strong>
								<span class="bn-muted"><?php echo esc_html( $total_st ? round( $n / $total_st * 100 ) . '%' : '—' ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="bn-panel bn-panel-wide">
				<div class="bn-panel-head">
					<h2><?php esc_html_e( 'Latest bookings', 'brightnest' ); ?></h2>
					<a href="<?php echo esc_url( $req_url ); ?>"><?php esc_html_e( 'View all', 'brightnest' ); ?> →</a>
				</div>
				<?php if ( $recent ) : ?>
					<ul class="bn-bookings">
						<?php foreach ( $recent as $r ) : ?>
							<?php
							$status = get_post_meta( $r->ID, '_bn_status', true ) ?: 'new';
							$name   = get_post_meta( $r->ID, '_bn_name', true ) ?: $r->post_title;
							$total  = get_post_meta( $r->ID, '_bn_total', true );
							$when   = trim( get_post_meta( $r->ID, '_bn_date', true ) . ' · ' . get_post_meta( $r->ID, '_bn_time', true ), ' ·' );
							$svc    = get_post_meta( $r->ID, '_bn_service', true ) ?: __( 'Enquiry', 'brightnest' );
							$phone  = preg_replace( '/\D/', '', (string) get_post_meta( $r->ID, '_bn_phone', true ) );
							?>
							<li>
								<span class="bn-avatar"><?php echo esc_html( strtoupper( mb_substr( $name, 0, 1 ) ) ); ?></span>
								<span class="bn-booking-main">
									<a href="<?php echo esc_url( get_edit_post_link( $r->ID ) ); ?>"><strong><?php echo esc_html( $name ); ?></strong></a>
									<small><?php echo esc_html( $svc ); ?><?php echo $when ? ' · ' . esc_html( $when ) : ''; ?></small>
								</span>
								<span class="bn-booking-total"><?php echo '' !== $total ? esc_html( $cur . ' ' . number_format_i18n( (float) $total ) ) : ''; ?></span>
								<span class="bn-status bn-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $st_label[ $status ] ?? ucfirst( $status ) ); ?></span>
								<span class="bn-booking-actions">
									<?php if ( 'new' === $status ) : ?>
										<a class="button button-small button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bn_set_status&post=' . $r->ID . '&status=confirmed' ), 'bn_set_status_' . $r->ID ) ); ?>"><?php esc_html_e( 'Confirm', 'brightnest' ); ?></a>
									<?php endif; ?>
									<?php if ( $phone ) : ?>
										<a class="button button-small" href="<?php echo esc_url( 'https://wa.me/' . $phone ); ?>" target="_blank" rel="noopener" title="WhatsApp"><span class="dashicons dashicons-whatsapp"></span></a>
									<?php endif; ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="bn-empty"><?php esc_html_e( 'No bookings yet. They will appear here as soon as customers book.', 'brightnest' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="bn-panel">
				<div class="bn-panel-head"><h2><?php esc_html_e( 'Top services', 'brightnest' ); ?></h2><span class="bn-muted"><?php esc_html_e( 'By bookings, 60 days', 'brightnest' ); ?></span></div>
				<?php if ( $d['services'] ) : ?>
					<?php $top_max = max( $d['services'] ); ?>
					<ol class="bn-top">
						<?php foreach ( $d['services'] as $svc => $n ) : ?>
							<li>
								<span class="bn-top-name"><?php echo esc_html( $svc ); ?></span>
								<span class="bn-top-bar"><span style="width: <?php echo esc_attr( round( $n / $top_max * 100 ) ); ?>%"></span></span>
								<strong><?php echo esc_html( $n ); ?></strong>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php else : ?>
					<p class="bn-empty"><?php esc_html_e( 'Appears after your first bookings.', 'brightnest' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="bn-panel">
				<div class="bn-panel-head"><h2><?php esc_html_e( 'Website setup', 'brightnest' ); ?></h2><span class="bn-muted"><?php echo esc_html( $done . ' / ' . count( $checks ) ); ?></span></div>
				<div class="bn-progress" aria-hidden="true"><span style="width: <?php echo esc_attr( round( $done / max( 1, count( $checks ) ) * 100 ) ); ?>%"></span></div>
				<ul class="bn-checklist">
					<?php foreach ( $checks as $c ) : ?>
						<li class="<?php echo $c[0] ? 'is-done' : ''; ?>">
							<span class="dashicons dashicons-<?php echo $c[0] ? 'yes-alt' : 'marker'; ?>"></span>
							<?php if ( $c[0] ) : ?>
								<span><?php echo esc_html( $c[1] ); ?></span>
							<?php else : ?>
								<a href="<?php echo esc_url( $c[2] ); ?>"><?php echo esc_html( $c[1] ); ?></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="bn-panel bn-panel-wide">
				<div class="bn-panel-head"><h2><?php esc_html_e( 'Shortcuts', 'brightnest' ); ?></h2></div>
				<div class="bn-shortcuts">
					<a href="<?php echo esc_url( $req_url ); ?>"><span class="dashicons dashicons-calendar-alt"></span><?php esc_html_e( 'Bookings', 'brightnest' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=bn_service' ) ); ?>"><span class="dashicons dashicons-list-view"></span><?php esc_html_e( 'Services', 'brightnest' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=bn_service_cat&post_type=bn_service' ) ); ?>"><span class="dashicons dashicons-category"></span><?php esc_html_e( 'Categories', 'brightnest' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=brightnest-settings&tab=homepage' ) ); ?>"><span class="dashicons dashicons-star-filled"></span><?php esc_html_e( 'Google reviews', 'brightnest' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=brightnest-settings&tab=booking' ) ); ?>"><span class="dashicons dashicons-tickets-alt"></span><?php esc_html_e( 'Vouchers', 'brightnest' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=brightnest-settings' ) ); ?>"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e( 'Settings', 'brightnest' ); ?></a>
				</div>
			</section>
		</div>
	</div>
	<?php
}

/**
 * Small widget on the main WordPress dashboard.
 */
function brightnest_dashboard_widget() {
	wp_add_dashboard_widget(
		'brightnest_bookings',
		__( 'BrightNest — bookings', 'brightnest' ),
		function () {
			$new = brightnest_new_requests_count();
			echo '<p class="bn-widget-big"><strong>' . esc_html( number_format_i18n( $new ) ) . '</strong> ' . esc_html__( 'new bookings waiting for confirmation', 'brightnest' ) . '</p>';
			echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=brightnest' ) ) . '">' . esc_html__( 'Open BrightNest dashboard', 'brightnest' ) . '</a> ';
			echo '<a class="button" href="' . esc_url( admin_url( 'post-new.php?post_type=bn_service' ) ) . '">' . esc_html__( 'Add service', 'brightnest' ) . '</a></p>';
		}
	);
}
add_action( 'wp_dashboard_setup', 'brightnest_dashboard_widget' );

/* -------------------------------------------------------------------------
 * Services list
 * ---------------------------------------------------------------------- */

function brightnest_service_columns( $cols ) {
	$new = array(
		'cb'         => $cols['cb'],
		'bn_icon'    => '',
		'title'      => __( 'Service', 'brightnest' ),
		'taxonomy-bn_service_cat' => __( 'Category', 'brightnest' ),
		'bn_price'   => __( 'From', 'brightnest' ),
		'bn_booking' => __( 'Checkout', 'brightnest' ),
		'bn_popular' => __( 'Popular', 'brightnest' ),
		'bn_active'  => __( 'On / Off', 'brightnest' ),
	);
	return $new;
}
add_filter( 'manage_bn_service_posts_columns', 'brightnest_service_columns' );

function brightnest_service_column_content( $col, $post_id ) {
	switch ( $col ) {
		case 'bn_icon':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 40, 40 ), array( 'class' => 'bn-col-thumb' ) );
			} else {
				echo '<span class="bn-col-icon">' . brightnest_icon( brightnest_service_icon( $post_id ), 22 ) . '</span>'; // phpcs:ignore
			}
			break;
		case 'bn_price':
			$p = brightnest_service_from( $post_id )[0];
			echo '' !== $p ? esc_html( brightnest_opt( 'currency' ) . ' ' . number_format_i18n( (float) $p ) ) : '—';
			break;
		case 'bn_booking':
			if ( brightnest_hourly_config( $post_id ) ) {
				echo '<span class="bn-pill">' . esc_html__( 'Hourly', 'brightnest' ) . '</span>';
			} else {
				$groups = brightnest_service_options( $post_id );
				$items  = count( brightnest_service_items( $post_id ) );
				$raw    = trim( (string) get_post_meta( $post_id, '_bn_options', true ) );
				echo $raw
					? '<span class="bn-pill">' . esc_html( sprintf( /* translators: 1: options, 2: tabs */ __( '%1$d options · %2$d tabs', 'brightnest' ), $items, count( $groups ) ) ) . '</span>'
					: '<span class="bn-pill bn-pill-muted">' . esc_html__( 'Single price', 'brightnest' ) . '</span>';
			}
			echo ' <a href="' . esc_url( brightnest_checkout_url( $post_id ) ) . '" target="_blank" rel="noopener" title="' . esc_attr__( 'Open checkout', 'brightnest' ) . '"><span class="dashicons dashicons-external"></span></a>';
			break;
		case 'bn_popular':
			$on  = '1' === get_post_meta( $post_id, '_bn_featured', true );
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=bn_toggle_popular&post=' . $post_id ), 'bn_toggle_popular_' . $post_id );
			printf(
				'<a class="bn-star %1$s" href="%2$s" title="%3$s"><span class="dashicons dashicons-star-%4$s"></span></a>',
				$on ? 'is-on' : '',
				esc_url( $url ),
				esc_attr__( 'Toggle popular (homepage top services)', 'brightnest' ),
				$on ? 'filled' : 'empty'
			);
			break;
		case 'bn_active':
			$on  = brightnest_service_active( $post_id );
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=bn_toggle_active&post=' . $post_id ), 'bn_toggle_active_' . $post_id );
			printf(
				'<a class="bn-onoff %1$s" href="%2$s" title="%3$s"><span class="bn-onoff-ui"></span>%4$s</a>',
				$on ? 'is-on' : '',
				esc_url( $url ),
				esc_attr__( 'Click to switch on/off', 'brightnest' ),
				$on ? esc_html__( 'On', 'brightnest' ) : esc_html__( 'Off', 'brightnest' )
			);
			break;
	}
}

function brightnest_toggle_active() {
	$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	check_admin_referer( 'bn_toggle_active_' . $id );
	if ( ! $id || 'bn_service' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_die( esc_html__( 'Not allowed.', 'brightnest' ) );
	}
	update_post_meta( $id, '_bn_active', brightnest_service_active( $id ) ? '0' : '1' );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=bn_service' ) );
	exit;
}
add_action( 'admin_post_bn_toggle_active', 'brightnest_toggle_active' );
add_action( 'manage_bn_service_posts_custom_column', 'brightnest_service_column_content', 10, 2 );

/**
 * Sort services by their display order in the admin list too.
 */
function brightnest_admin_service_order( $query ) {
	if ( is_admin() && $query->is_main_query() && 'bn_service' === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
}
add_action( 'pre_get_posts', 'brightnest_admin_service_order' );

function brightnest_toggle_popular() {
	$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	check_admin_referer( 'bn_toggle_popular_' . $id );
	if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
		wp_die( esc_html__( 'Not allowed.', 'brightnest' ) );
	}
	update_post_meta( $id, '_bn_featured', '1' === get_post_meta( $id, '_bn_featured', true ) ? '' : '1' );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=bn_service' ) );
	exit;
}
add_action( 'admin_post_bn_toggle_popular', 'brightnest_toggle_popular' );

/**
 * "Duplicate" row action for services.
 */
function brightnest_service_row_actions( $actions, $post ) {
	if ( 'bn_service' === $post->post_type && current_user_can( 'edit_posts' ) ) {
		$url                    = wp_nonce_url( admin_url( 'admin-post.php?action=bn_duplicate_service&post=' . $post->ID ), 'bn_duplicate_' . $post->ID );
		$actions['bn_duplicate'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Duplicate', 'brightnest' ) . '</a>';
		$actions['bn_checkout']  = '<a href="' . esc_url( brightnest_checkout_url( $post->ID ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Test checkout', 'brightnest' ) . '</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'brightnest_service_row_actions', 10, 2 );

function brightnest_duplicate_service() {
	$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	check_admin_referer( 'bn_duplicate_' . $id );
	$src = get_post( $id );
	if ( ! $src || 'bn_service' !== $src->post_type || ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'brightnest' ) );
	}
	$new_id = wp_insert_post(
		array(
			'post_type'    => 'bn_service',
			'post_status'  => 'draft',
			/* translators: %s: service title */
			'post_title'   => sprintf( __( '%s (copy)', 'brightnest' ), $src->post_title ),
			'post_content' => $src->post_content,
			'post_excerpt' => $src->post_excerpt,
			'menu_order'   => $src->menu_order + 1,
		)
	);
	if ( ! $new_id || is_wp_error( $new_id ) ) {
		wp_die( esc_html__( 'Could not duplicate the service.', 'brightnest' ) );
	}
	foreach ( array( 'price', 'price_unit', 'duration', 'icon', 'includes', 'options', 'mode', 'hourly_rate', 'materials_rate', 'materials_on', 'materials_name', 'hours_min', 'hours_max', 'pros_max', 'q_hours', 'q_pros', 'q_materials', 'guide', 'freqs', 'areas', 'faqs', 'active' ) as $k ) {
		update_post_meta( $new_id, '_bn_' . $k, get_post_meta( $id, '_bn_' . $k, true ) );
	}
	$thumb = get_post_thumbnail_id( $id );
	if ( $thumb ) {
		set_post_thumbnail( $new_id, $thumb );
	}
	wp_set_object_terms( $new_id, wp_get_object_terms( $id, 'bn_service_cat', array( 'fields' => 'ids' ) ), 'bn_service_cat' );
	wp_safe_redirect( admin_url( 'post.php?post=' . $new_id . '&action=edit&bn_duplicated=1' ) );
	exit;
}
add_action( 'admin_post_bn_duplicate_service', 'brightnest_duplicate_service' );

function brightnest_admin_notices() {
	if ( isset( $_GET['bn_duplicated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Service copied as a draft. Change the name and details, then click Publish.', 'brightnest' ) . '</p></div>';
	}
	if ( isset( $_GET['bn_status_set'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Booking status updated.', 'brightnest' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'brightnest_admin_notices' );

/* -------------------------------------------------------------------------
 * Bookings list: status filter + one-click status
 * ---------------------------------------------------------------------- */

function brightnest_request_statuses() {
	return array(
		'new'       => __( 'New', 'brightnest' ),
		'confirmed' => __( 'Confirmed', 'brightnest' ),
		'completed' => __( 'Completed', 'brightnest' ),
		'cancelled' => __( 'Cancelled', 'brightnest' ),
	);
}

function brightnest_request_status_filter( $post_type ) {
	if ( 'bn_request' !== $post_type ) {
		return;
	}
	$current = isset( $_GET['bn_status'] ) ? sanitize_key( $_GET['bn_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	echo '<select name="bn_status"><option value="">' . esc_html__( 'All statuses', 'brightnest' ) . '</option>';
	foreach ( brightnest_request_statuses() as $k => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $k ), selected( $current, $k, false ), esc_html( $label ) );
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'brightnest_request_status_filter' );

function brightnest_request_status_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'bn_request' !== $query->get( 'post_type' ) || empty( $_GET['bn_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$status = sanitize_key( $_GET['bn_status'] ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'new' === $status ) {
		$query->set(
			'meta_query',
			array(
				'relation' => 'OR',
				array( 'key' => '_bn_status', 'value' => 'new' ),
				array( 'key' => '_bn_status', 'compare' => 'NOT EXISTS' ),
			)
		);
	} elseif ( isset( brightnest_request_statuses()[ $status ] ) ) {
		$query->set( 'meta_query', array( array( 'key' => '_bn_status', 'value' => $status ) ) );
	}
}
add_action( 'pre_get_posts', 'brightnest_request_status_query' );

function brightnest_request_row_actions( $actions, $post ) {
	if ( 'bn_request' !== $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}
	unset( $actions['inline hide-if-no-js'] );
	$current = get_post_meta( $post->ID, '_bn_status', true ) ?: 'new';
	foreach ( array( 'confirmed' => __( 'Confirm', 'brightnest' ), 'completed' => __( 'Complete', 'brightnest' ), 'cancelled' => __( 'Cancel', 'brightnest' ) ) as $status => $label ) {
		if ( $status === $current ) {
			continue;
		}
		$url                        = wp_nonce_url( admin_url( 'admin-post.php?action=bn_set_status&post=' . $post->ID . '&status=' . $status ), 'bn_set_status_' . $post->ID );
		$actions[ 'bn_' . $status ] = '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}
	$phone = preg_replace( '/\D/', '', (string) get_post_meta( $post->ID, '_bn_phone', true ) );
	if ( $phone ) {
		$actions['bn_wa'] = '<a href="' . esc_url( 'https://wa.me/' . $phone ) . '" target="_blank" rel="noopener">WhatsApp</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'brightnest_request_row_actions', 10, 2 );

function brightnest_set_request_status() {
	$id     = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	$status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
	check_admin_referer( 'bn_set_status_' . $id );
	if ( ! $id || 'bn_request' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) || ! isset( brightnest_request_statuses()[ $status ] ) ) {
		wp_die( esc_html__( 'Not allowed.', 'brightnest' ) );
	}
	update_post_meta( $id, '_bn_status', $status );
	$back = wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=bn_request' );
	wp_safe_redirect( add_query_arg( 'bn_status_set', 1, $back ) );
	exit;
}
add_action( 'admin_post_bn_set_status', 'brightnest_set_request_status' );

/* -------------------------------------------------------------------------
 * Bookings: CSV export
 * ---------------------------------------------------------------------- */

function brightnest_export_button( $which ) {
	$screen = get_current_screen();
	if ( 'top' !== $which || ! $screen || 'bn_request' !== $screen->post_type ) {
		return;
	}
	$args = array( 'action' => 'bn_export_bookings' );
	if ( ! empty( $_GET['bn_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$args['bn_status'] = sanitize_key( $_GET['bn_status'] ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	$url = wp_nonce_url( add_query_arg( $args, admin_url( 'admin-post.php' ) ), 'bn_export_bookings' );
	echo '<div class="alignleft actions"><a class="button" href="' . esc_url( $url ) . '"><span class="dashicons dashicons-download" style="vertical-align:text-top"></span> ' . esc_html__( 'Export CSV', 'brightnest' ) . '</a></div>';
}
add_action( 'manage_posts_extra_tablenav', 'brightnest_export_button' );

function brightnest_export_bookings() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'brightnest' ) );
	}
	check_admin_referer( 'bn_export_bookings' );
	$q = array( 'post_type' => 'bn_request', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' );
	$status = isset( $_GET['bn_status'] ) ? sanitize_key( $_GET['bn_status'] ) : '';
	if ( 'new' === $status ) {
		$q['meta_query'] = array( 'relation' => 'OR', array( 'key' => '_bn_status', 'value' => 'new' ), array( 'key' => '_bn_status', 'compare' => 'NOT EXISTS' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	} elseif ( $status && isset( brightnest_request_statuses()[ $status ] ) ) {
		$q['meta_query'] = array( array( 'key' => '_bn_status', 'value' => $status ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	$cols = array(
		'ref' => 'Ref', 'type' => 'Type', 'status' => 'Status', 'service' => 'Service', 'items' => 'Items', 'date' => 'Date', 'time' => 'Time',
		'name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'area' => 'City', 'place' => 'Area', 'address' => 'Address', 'directions' => 'Directions',
		'subtotal' => 'Subtotal', 'discount' => 'Discount', 'voucher' => 'Voucher', 'total' => 'Total', 'payment' => 'Payment',
		'frequency' => 'Frequency', 'message' => 'Customer notes', 'admin_notes' => 'Admin notes',
	);
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=bookings-' . wp_date( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM so Excel shows Arabic/Urdu names correctly.
	fputcsv( $out, array_merge( array( 'Received' ), array_values( $cols ) ) );
	foreach ( get_posts( $q ) as $p ) {
		$row = array( get_the_date( 'Y-m-d H:i', $p ) );
		foreach ( array_keys( $cols ) as $k ) {
			$v = (string) get_post_meta( $p->ID, '_bn_' . $k, true );
			if ( 'status' === $k && '' === $v ) {
				$v = 'new';
			}
			// Neutralise spreadsheet formulas (=, +, -, @) in customer input.
			if ( '' !== $v && in_array( $v[0], array( '=', '+', '-', '@' ), true ) && 'phone' !== $k ) {
				$v = "'" . $v;
			}
			$row[] = str_replace( "
", ' / ', $v );
		}
		fputcsv( $out, $row );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}
add_action( 'admin_post_bn_export_bookings', 'brightnest_export_bookings' );
