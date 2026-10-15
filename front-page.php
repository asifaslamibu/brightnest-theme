<?php
/**
 * Homepage.
 *
 * @package BrightNest
 */

get_header();

$bn_cats     = brightnest_categories();
$bn_all_ids  = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC' ) );
$bn_popular  = get_posts(
	array(
		'post_type'      => 'bn_service',
		'posts_per_page' => 12,
		'fields'         => 'ids',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'meta_key'       => '_bn_featured', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
	)
);
$bn_hero_img = brightnest_opt( 'hero_image' );
?>

<section class="bn-hero">
	<div class="bn-container bn-hero-grid">
		<div class="bn-hero-text">
			<span class="bn-pill"><?php echo brightnest_icon( 'shield', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Vetted & insured professionals', 'brightnest' ); ?></span>
			<h1><?php echo esc_html( brightnest_opt( 'hero_title' ) ); ?></h1>
			<p class="bn-lead"><?php echo esc_html( brightnest_opt( 'hero_subtitle' ) ); ?></p>

			<form class="bn-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="bn-search-input"><?php esc_html_e( 'What service do you need?', 'brightnest' ); ?></label>
				<?php echo brightnest_icon( 'search', 20 ); // phpcs:ignore ?>
				<input id="bn-search-input" type="search" name="s" list="bn-service-list" placeholder="<?php esc_attr_e( 'What service do you need? e.g. Deep cleaning', 'brightnest' ); ?>" autocomplete="off">
				<input type="hidden" name="post_type" value="bn_service">
				<datalist id="bn-service-list">
					<?php foreach ( $bn_all_ids as $bn_sid ) : ?>
						<option value="<?php echo esc_attr( get_the_title( $bn_sid ) ); ?>"></option>
					<?php endforeach; ?>
				</datalist>
				<button type="submit" class="bn-btn bn-btn-primary"><?php esc_html_e( 'Search', 'brightnest' ); ?></button>
			</form>

			<?php if ( $bn_popular ) : ?>
				<div class="bn-chips">
					<span class="bn-muted"><?php esc_html_e( 'Popular:', 'brightnest' ); ?></span>
					<?php foreach ( array_slice( $bn_popular, 0, 4 ) as $bn_sid ) : ?>
						<a class="bn-chip" href="<?php echo esc_url( get_permalink( $bn_sid ) ); ?>"><?php echo esc_html( get_the_title( $bn_sid ) ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="bn-hero-visual" aria-hidden="true">
			<?php if ( $bn_hero_img ) : ?>
				<img class="bn-hero-img" src="<?php echo esc_url( $bn_hero_img ); ?>" alt="">
			<?php else : ?>
				<div class="bn-hero-tiles">
					<?php foreach ( array_slice( $bn_cats, 0, 6 ) as $bn_term ) : ?>
						<?php $bn_icon = get_term_meta( $bn_term->term_id, 'bn_icon', true ) ?: 'home'; ?>
						<div class="bn-hero-tile bn-tone-<?php echo esc_attr( brightnest_tone( $bn_term->slug ) ); ?>">
							<?php echo brightnest_icon( $bn_icon, 36 ); // phpcs:ignore ?>
							<span><?php echo esc_html( $bn_term->name ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="bn-float-card bn-float-1">
				<span class="bn-float-icon"><?php echo brightnest_icon( 'check', 18 ); // phpcs:ignore ?></span>
				<span><strong><?php esc_html_e( 'Booking confirmed', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Pro arriving at 10:00 AM', 'brightnest' ); ?></small></span>
			</div>
			<div class="bn-float-card bn-float-2">
				<span class="bn-stars-inline"><?php echo str_repeat( brightnest_icon( 'star', 14 ), 5 ); // phpcs:ignore ?></span>
				<span><strong><?php echo esc_html( brightnest_opt( 'stat_2_num' ) ); ?></strong><small><?php echo esc_html( brightnest_opt( 'stat_2_label' ) ); ?></small></span>
			</div>
		</div>
	</div>

	<div class="bn-container">
		<ul class="bn-stats">
			<?php
			$bn_stat_icons = array( 1 => 'users', 2 => 'star', 3 => 'clipboard', 4 => 'headset' );
			for ( $i = 1; $i <= 4; $i++ ) :
				if ( ! brightnest_opt( "stat_{$i}_num" ) ) {
					continue;
				}
				?>
				<li>
					<span class="bn-stat-icon"><?php echo brightnest_icon( $bn_stat_icons[ $i ], 22 ); // phpcs:ignore ?></span>
					<span><strong><?php echo esc_html( brightnest_opt( "stat_{$i}_num" ) ); ?></strong><small><?php echo esc_html( brightnest_opt( "stat_{$i}_label" ) ); ?></small></span>
				</li>
			<?php endfor; ?>
		</ul>
	</div>
</section>


<section>

<?php if ( $bn_cats ) : ?>
<section class="bn-section" id="categories">
	<div class="bn-container">
		<div class="bn-section-head">
			<span class="bn-eyebrow"><?php esc_html_e( 'Categories', 'brightnest' ); ?></span>
			<h2 class="bn-h2"><?php esc_html_e( 'What can we help you with?', 'brightnest' ); ?></h2>
		</div>
		<div class="bn-cat-grid">
			<?php foreach ( $bn_cats as $bn_term ) : ?>
				<?php $bn_icon = get_term_meta( $bn_term->term_id, 'bn_icon', true ) ?: 'home'; ?>
				<a class="bn-cat" href="<?php echo esc_url( get_term_link( $bn_term ) ); ?>">
					<span class="bn-cat-icon bn-tone-<?php echo esc_attr( brightnest_tone( $bn_term->slug ) ); ?>"><?php echo brightnest_icon( $bn_icon, 28 ); // phpcs:ignore ?></span>
					<span class="bn-cat-name"><?php echo esc_html( $bn_term->name ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="bn-section bn-section-tight">
	<div class="bn-container bn-carousels">
		<?php
		get_template_part(
			'template-parts/carousel',
			null,
			array(
				'title'    => __( 'Most popular services', 'brightnest' ),
				'subtitle' => __( 'Booked again and again by our customers', 'brightnest' ),
				'link'     => get_post_type_archive_link( 'bn_service' ),
				'ids'      => $bn_popular ? $bn_popular : array_slice( $bn_all_ids, 0, 10 ),
			)
		);

		$bn_shown = 0;
		foreach ( $bn_cats as $bn_term ) {
			if ( $bn_shown >= 3 ) {
				break;
			}
			$bn_ids = get_posts(
				array(
					'post_type'      => 'bn_service',
					'posts_per_page' => 10,
					'fields'         => 'ids',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'tax_query'      => array( array( 'taxonomy' => 'bn_service_cat', 'terms' => $bn_term->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
				)
			);
			if ( count( $bn_ids ) < 2 ) {
				continue;
			}
			++$bn_shown;
			get_template_part(
				'template-parts/carousel',
				null,
				array(
					'title'    => $bn_term->name,
					'subtitle' => $bn_term->description,
					'link'     => get_term_link( $bn_term ),
					'ids'      => $bn_ids,
				)
			);
		}
		?>
	</div>
</section>

<section class="bn-section bn-section-soft" id="why-us">
	<div class="bn-container">
		<div class="bn-section-head">
			<span class="bn-eyebrow"><?php esc_html_e( 'Why choose us', 'brightnest' ); ?></span>
			<h2 class="bn-h2"><?php echo esc_html( sprintf( /* translators: %s site name */ __( 'Why customers love %s', 'brightnest' ), get_bloginfo( 'name' ) ) ); ?></h2>
		</div>
		<div class="bn-features">
			<?php
			$bn_features = array(
				array( 'home', __( 'Everything your home needs', 'brightnest' ), __( 'From cleaning to AC, moving to pest control — 25+ services, one trusted place to book them all.', 'brightnest' ) ),
				array( 'shield', __( 'The best professionals', 'brightnest' ), __( 'Background-checked, trained and rated by real customers after every job.', 'brightnest' ) ),
				array( 'tag', __( 'Upfront, fair pricing', 'brightnest' ), __( 'See starting prices before you book. No hidden charges, no surprises.', 'brightnest' ) ),
				array( 'headset', __( 'Great customer support', 'brightnest' ), __( 'Our friendly team is available 7 days a week by phone and WhatsApp.', 'brightnest' ) ),
			);
			foreach ( $bn_features as $bn_f ) :
				?>
				<div class="bn-feature">
					<span class="bn-feature-icon"><?php echo brightnest_icon( $bn_f[0], 26 ); // phpcs:ignore ?></span>
					<h3><?php echo esc_html( $bn_f[1] ); ?></h3>
					<p><?php echo esc_html( $bn_f[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="bn-section" id="how-it-works">
	<div class="bn-container">
		<div class="bn-section-head">
			<span class="bn-eyebrow"><?php esc_html_e( 'How it works', 'brightnest' ); ?></span>
			<h2 class="bn-h2"><?php esc_html_e( 'Book in 3 simple steps', 'brightnest' ); ?></h2>
		</div>
		<ol class="bn-steps">
			<?php
			$bn_steps = array(
				array( 'clipboard', __( 'Tell us what you need', 'brightnest' ), __( 'Choose a service, date and time that suits you. It takes less than a minute.', 'brightnest' ) ),
				array( 'users', __( 'We match the right pro', 'brightnest' ), __( 'We confirm your booking and assign a vetted professional for the job.', 'brightnest' ) ),
				array( 'sofa-relax', __( 'Sit back and relax', 'brightnest' ), __( 'Your pro arrives on time and gets it done. Pay after the service.', 'brightnest' ) ),
			);
			foreach ( $bn_steps as $bn_n => $bn_s ) :
				?>
				<li class="bn-step">
					<span class="bn-step-num"><?php echo esc_html( $bn_n + 1 ); ?></span>
					<span class="bn-step-icon"><?php echo brightnest_icon( $bn_s[0], 30 ); // phpcs:ignore ?></span>
					<h3><?php echo esc_html( $bn_s[1] ); ?></h3>
					<p><?php echo esc_html( $bn_s[2] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<?php get_template_part( 'template-parts/reviews' ); ?>

<?php get_template_part( 'template-parts/cta' ); ?>

<section class="bn-section bn-section-tight" id="locations">
	<div class="bn-container">
		<div class="bn-section-head">
			<span class="bn-eyebrow"><?php esc_html_e( 'Locations', 'brightnest' ); ?></span>
			<h2 class="bn-h2"><?php esc_html_e( 'Areas we serve', 'brightnest' ); ?></h2>
		</div>
		<div class="bn-locations">
			<?php foreach ( brightnest_cities() as $bn_city ) : ?>
				<div class="bn-location bn-tone-<?php echo esc_attr( brightnest_tone( $bn_city ) ); ?>">
					<?php echo brightnest_icon( 'pin', 22 ); // phpcs:ignore ?>
					<span><strong><?php echo esc_html( $bn_city ); ?></strong><small><?php esc_html_e( 'All services available', 'brightnest' ); ?></small></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="bn-section bn-section-soft" id="faq">
	<div class="bn-container bn-faq-wrap">
		<div class="bn-section-head bn-left">
			<span class="bn-eyebrow"><?php esc_html_e( 'FAQs', 'brightnest' ); ?></span>
			<h2 class="bn-h2"><?php esc_html_e( 'Frequently asked questions', 'brightnest' ); ?></h2>
			<p class="bn-muted"><?php esc_html_e( "Can't find your answer? Our team is happy to help.", 'brightnest' ); ?></p>
			<?php $bn_contact = get_page_by_path( 'contact' ); ?>
			<?php if ( $bn_contact ) : ?>
				<a class="bn-btn bn-btn-ghost" href="<?php echo esc_url( get_permalink( $bn_contact ) ); ?>"><?php esc_html_e( 'Contact us', 'brightnest' ); ?></a>
			<?php endif; ?>
		</div>
		<?php get_template_part( 'template-parts/faq' ); ?>
	</div>
</section>

<?php
get_footer();
