<?php
/**
 * Single service page.
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bn_id       = get_the_ID();
	$bn_terms    = get_the_terms( $bn_id, 'bn_service_cat' );
	$bn_cat      = ( $bn_terms && ! is_wp_error( $bn_terms ) ) ? $bn_terms[0] : null;
	$bn_inc_ar   = brightnest_show_ar() ? brightnest_ar_meta( $bn_id, 'includes' ) : null; // Arabic list when filled in.
	$bn_includes = array_filter( array_map( 'trim', explode( "\n", (string) ( null !== $bn_inc_ar ? $bn_inc_ar : get_post_meta( $bn_id, '_bn_includes', true ) ) ) ) );
	$bn_duration = get_post_meta( $bn_id, '_bn_duration', true );
	$bn_price    = get_post_meta( $bn_id, '_bn_price', true );
	$bn_unit     = get_post_meta( $bn_id, '_bn_price_unit', true );
	$bn_rating   = get_post_meta( $bn_id, '_bn_rating', true );
	$bn_reviews  = get_post_meta( $bn_id, '_bn_review_count', true );
	$bn_cur      = brightnest_opt( 'currency' );
	$bn_hourly   = brightnest_hourly_config( $bn_id );
	$bn_groups   = $bn_hourly ? array() : brightnest_service_options( $bn_id );
	$bn_has_opts = ! $bn_hourly && '' !== trim( (string) get_post_meta( $bn_id, '_bn_options', true ) );
	$bn_count    = $bn_has_opts ? count( brightnest_service_items( $bn_id ) ) : 0;
	$bn_book     = brightnest_checkout_url( $bn_id );
	$bn_title    = get_the_title();
	$bn_wa       = brightnest_whatsapp_url( sprintf( /* translators: %s service */ __( 'Hi, I would like to book %s.', 'brightnest' ), wp_strip_all_tags( html_entity_decode( $bn_title ) ) ) );
	$bn_money    = function ( $n ) use ( $bn_cur ) {
		return $bn_cur . ' ' . number_format_i18n( $n, floor( $n ) == $n ? 0 : 2 );
	};
	?>

	<section class="bn-sv-hero">
		<div class="bn-container bn-sv-hero-grid">
			<div class="bn-sv-hero-copy">
				<?php brightnest_breadcrumbs(); ?>
				<?php if ( $bn_cat ) : ?>
					<a class="bn-sv-chip" href="<?php echo esc_url( get_term_link( $bn_cat ) ); ?>"><?php echo brightnest_service_icon_html( $bn_id, 16 ); // phpcs:ignore ?> <?php echo esc_html( $bn_cat->name ); ?></a>
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="bn-sv-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<ul class="bn-sv-facts">
					<?php if ( '' !== $bn_price ) : ?>
						<li><small><?php esc_html_e( 'Starting from', 'brightnest' ); ?></small><strong><?php echo esc_html( $bn_money( (float) $bn_price ) ); ?></strong><?php if ( $bn_unit ) : ?><em><?php echo esc_html( $bn_unit ); ?></em><?php endif; ?></li>
					<?php endif; ?>
					<?php if ( $bn_duration ) : ?>
						<li><small><?php esc_html_e( 'Duration', 'brightnest' ); ?></small><strong><?php echo esc_html( $bn_duration ); ?></strong></li>
					<?php endif; ?>
					<?php if ( $bn_count ) : ?>
						<li><small><?php esc_html_e( 'Choose from', 'brightnest' ); ?></small><strong><?php echo esc_html( sprintf( /* translators: %d options */ _n( '%d option', '%d options', $bn_count, 'brightnest' ), $bn_count ) ); ?></strong></li>
					<?php elseif ( $bn_hourly ) : ?>
						<li><small><?php esc_html_e( 'Book by', 'brightnest' ); ?></small><strong><?php esc_html_e( 'the hour', 'brightnest' ); ?></strong></li>
					<?php endif; ?>
					<?php if ( '' !== $bn_rating ) : ?>
						<li><small><?php esc_html_e( 'Rating', 'brightnest' ); ?></small><strong>★ <?php echo esc_html( number_format_i18n( (float) $bn_rating, 1 ) ); ?></strong><?php if ( $bn_reviews ) : ?><em>(<?php echo esc_html( number_format_i18n( (int) $bn_reviews ) ); ?>)</em><?php endif; ?></li>
					<?php endif; ?>
				</ul>
				<div class="bn-sv-actions">
					<a class="bn-btn bn-btn-primary bn-btn-lg" href="<?php echo esc_url( $bn_book ); ?>"><?php esc_html_e( 'Book now', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></a>
					<?php if ( $bn_wa ) : ?>
						<a class="bn-btn bn-btn-outline-light bn-btn-lg" href="<?php echo esc_url( $bn_wa ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Ask on WhatsApp', 'brightnest' ); ?></a>
					<?php endif; ?>
				</div>
				<ul class="bn-sv-trust">
					<li><?php echo brightnest_icon( 'shield', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Vetted professionals', 'brightnest' ); ?></li>
					<li><?php echo brightnest_icon( 'tag', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Secure online payment', 'brightnest' ); ?></li>
					<li><?php echo brightnest_icon( 'check-circle', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Satisfaction guarantee', 'brightnest' ); ?></li>
				</ul>
			</div>
			<div class="bn-sv-hero-art">
				<div class="bn-sv-frame">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'brightnest-hero', array( 'alt' => esc_attr( $bn_title ) ) ); ?>
					<?php else : ?>
						<span class="bn-sv-frame-icon"><?php echo brightnest_service_icon_html( $bn_id, 110 ); // phpcs:ignore ?></span>
					<?php endif; ?>
				</div>
				<?php if ( '' !== $bn_price ) : ?>
					<div class="bn-sv-pricetag"><small><?php esc_html_e( 'From', 'brightnest' ); ?></small><strong><?php echo esc_html( $bn_money( (float) $bn_price ) ); ?></strong></div>
				<?php endif; ?>
				<span class="bn-spark bn-sv-spark-1"></span><span class="bn-spark bn-sv-spark-2"></span>
			</div>
		</div>
	</section>

	<nav class="bn-sv-subnav" aria-label="<?php esc_attr_e( 'On this page', 'brightnest' ); ?>" data-subnav>
		<div class="bn-container">
			<a href="#overview" class="is-active"><?php esc_html_e( 'Overview', 'brightnest' ); ?></a>
			<?php if ( $bn_includes ) : ?><a href="#included"><?php esc_html_e( "What's included", 'brightnest' ); ?></a><?php endif; ?>
			<?php if ( $bn_hourly || $bn_has_opts ) : ?><a href="#pricing"><?php esc_html_e( 'Prices', 'brightnest' ); ?></a><?php endif; ?>
			<a href="#how"><?php esc_html_e( 'How it works', 'brightnest' ); ?></a>
			<a href="#faq"><?php esc_html_e( 'FAQ', 'brightnest' ); ?></a>
			<a class="bn-sv-subnav-cta" href="<?php echo esc_url( $bn_book ); ?>"><?php esc_html_e( 'Book now', 'brightnest' ); ?></a>
		</div>
	</nav>

	<div class="bn-container bn-sv-layout">
		<div class="bn-sv-main">

			<section class="bn-sv-sec" id="overview">
				<span class="bn-eyebrow"><?php esc_html_e( 'Overview', 'brightnest' ); ?></span>
				<h2 class="bn-sv-h2"><?php echo esc_html( sprintf( /* translators: %s service */ __( 'About our %s service', 'brightnest' ), $bn_title ) ); ?></h2>
				<div class="bn-prose"><?php the_content(); ?></div>
				<div class="bn-sv-highlights">
					<div><?php echo brightnest_icon( 'users', 22 ); // phpcs:ignore ?><strong><?php esc_html_e( 'Trained team', 'brightnest' ); ?></strong><span><?php esc_html_e( 'Verified & uniformed', 'brightnest' ); ?></span></div>
					<div><?php echo brightnest_icon( 'clock', 22 ); // phpcs:ignore ?><strong><?php esc_html_e( 'On time', 'brightnest' ); ?></strong><span><?php esc_html_e( 'Hourly slots, 7 days', 'brightnest' ); ?></span></div>
					<div><?php echo brightnest_icon( 'deep-clean', 22 ); // phpcs:ignore ?><strong><?php esc_html_e( 'Pro equipment', 'brightnest' ); ?></strong><span><?php esc_html_e( 'Safe, eco-friendly products', 'brightnest' ); ?></span></div>
				</div>
			</section>

			<?php if ( $bn_includes ) : ?>
				<section class="bn-sv-sec" id="included">
					<span class="bn-eyebrow"><?php esc_html_e( "What's included", 'brightnest' ); ?></span>
					<h2 class="bn-sv-h2"><?php esc_html_e( 'Everything that comes with your booking', 'brightnest' ); ?></h2>
					<ul class="bn-sv-incl">
						<?php foreach ( $bn_includes as $bn_n => $bn_item ) : ?>
							<li><span><?php echo brightnest_icon( 'check', 16 ); // phpcs:ignore ?></span><?php echo esc_html( $bn_item ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( $bn_hourly ) : ?>
				<section class="bn-sv-sec" id="pricing">
					<span class="bn-eyebrow"><?php esc_html_e( 'Prices', 'brightnest' ); ?></span>
					<h2 class="bn-sv-h2"><?php esc_html_e( 'Simple hourly pricing', 'brightnest' ); ?></h2>
					<div class="bn-sv-rate">
						<div class="bn-sv-rate-main"><small><?php esc_html_e( 'Per professional, per hour', 'brightnest' ); ?></small><strong><?php echo esc_html( $bn_money( $bn_hourly['rate'] ) ); ?></strong>
							<?php if ( $bn_hourly['materials'] > 0 ) : ?><span><?php echo esc_html( sprintf( /* translators: %s amount */ __( '+ %s / hour with cleaning materials', 'brightnest' ), $bn_money( $bn_hourly['materials'] ) ) ); ?></span><?php endif; ?>
						</div>
						<table class="bn-sv-table">
							<thead><tr><th><?php esc_html_e( 'Duration', 'brightnest' ); ?></th><th><?php esc_html_e( '1 professional', 'brightnest' ); ?></th><th><?php esc_html_e( '2 professionals', 'brightnest' ); ?></th></tr></thead>
							<tbody>
								<?php foreach ( array( 2, 3, 4, 5 ) as $bn_h ) : ?>
									<tr><td><?php echo esc_html( sprintf( /* translators: %d hours */ __( '%d hours', 'brightnest' ), $bn_h ) ); ?></td><td><?php echo esc_html( $bn_money( $bn_hourly['rate'] * $bn_h ) ); ?></td><td><?php echo esc_html( $bn_money( $bn_hourly['rate'] * $bn_h * 2 ) ); ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</section>
			<?php elseif ( $bn_has_opts ) : ?>
				<section class="bn-sv-sec" id="pricing">
					<span class="bn-eyebrow"><?php esc_html_e( 'Prices', 'brightnest' ); ?></span>
					<h2 class="bn-sv-h2"><?php esc_html_e( 'Packages & prices', 'brightnest' ); ?></h2>
					<?php if ( count( $bn_groups ) > 1 ) : ?>
						<div class="bn-sv-tabs" role="tablist" data-price-tabs>
							<?php foreach ( $bn_groups as $bn_gi => $bn_g ) : ?>
								<button type="button" role="tab" class="<?php echo 0 === $bn_gi ? 'is-active' : ''; ?>" aria-selected="<?php echo 0 === $bn_gi ? 'true' : 'false'; ?>" data-tab="<?php echo esc_attr( $bn_gi ); ?>"><?php echo esc_html( $bn_g['name'] ); ?> <em><?php echo esc_html( count( $bn_g['items'] ) ); ?></em></button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php foreach ( $bn_groups as $bn_gi => $bn_g ) : ?>
						<ul class="bn-sv-pkgs" data-panel="<?php echo esc_attr( $bn_gi ); ?>" <?php echo 0 === $bn_gi ? '' : 'hidden'; ?>>
							<?php foreach ( $bn_g['items'] as $bn_it ) : ?>
								<li>
									<div class="bn-sv-pkg-info">
										<strong><?php echo esc_html( $bn_it['name'] ); ?></strong>
										<?php if ( $bn_it['desc'] ) : ?><span><?php echo esc_html( $bn_it['desc'] ); ?></span><?php endif; ?>
									</div>
									<div class="bn-sv-pkg-price">
										<?php if ( $bn_it['old'] ) : ?><del><?php echo esc_html( $bn_money( $bn_it['old'] ) ); ?></del><?php endif; ?>
										<strong><?php echo esc_html( $bn_money( $bn_it['price'] ) ); ?></strong>
									</div>
									<a class="bn-btn bn-btn-navy bn-btn-sm" href="<?php echo esc_url( add_query_arg( 'add', $bn_it['id'], $bn_book ) ); ?>"><?php esc_html_e( 'Book', 'brightnest' ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>

			<section class="bn-sv-sec" id="how">
				<span class="bn-eyebrow"><?php esc_html_e( 'How it works', 'brightnest' ); ?></span>
				<h2 class="bn-sv-h2"><?php esc_html_e( 'Book in three easy steps', 'brightnest' ); ?></h2>
				<ol class="bn-sv-steps">
					<li><em>1</em><div><strong><?php esc_html_e( 'Choose your service', 'brightnest' ); ?></strong><span><?php esc_html_e( 'Pick a package or the hours you need. You see the price before you book.', 'brightnest' ); ?></span></div></li>
					<li><em>2</em><div><strong><?php esc_html_e( 'Pick a date & time', 'brightnest' ); ?></strong><span><?php esc_html_e( 'Hourly slots from 8 AM, including same-day when available.', 'brightnest' ); ?></span></div></li>
					<li><em>3</em><div><strong><?php esc_html_e( 'We take care of it', 'brightnest' ); ?></strong><span><?php esc_html_e( 'Our team arrives on time and gets the job done.', 'brightnest' ); ?></span></div></li>
				</ol>
			</section>

			<section class="bn-sv-sec" id="faq">
				<span class="bn-eyebrow"><?php esc_html_e( 'FAQ', 'brightnest' ); ?></span>
				<h2 class="bn-sv-h2"><?php esc_html_e( 'Questions customers ask', 'brightnest' ); ?></h2>
				<?php get_template_part( 'template-parts/faq' ); ?>
			</section>
		</div>

		<aside class="bn-sv-side">
			<div class="bn-sv-book">
				<div class="bn-sv-book-head">
					<small><?php esc_html_e( 'Starting from', 'brightnest' ); ?></small>
					<strong><?php echo '' !== $bn_price ? esc_html( $bn_money( (float) $bn_price ) ) : esc_html__( 'Get a quote', 'brightnest' ); ?></strong>
					<?php if ( $bn_unit ) : ?><span><?php echo esc_html( $bn_unit ); ?></span><?php endif; ?>
				</div>
				<ul class="bn-sv-book-list">
					<li><?php echo brightnest_icon( 'check', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Pay securely by card, Apple Pay or Google Pay', 'brightnest' ); ?></li>
					<li><?php echo brightnest_icon( 'check', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Free rescheduling up to 12h before', 'brightnest' ); ?></li>
					<li><?php echo brightnest_icon( 'check', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Re-do guarantee within 24h', 'brightnest' ); ?></li>
				</ul>
				<a class="bn-btn bn-btn-primary bn-btn-block bn-btn-lg" href="<?php echo esc_url( $bn_book ); ?>"><?php esc_html_e( 'Book now', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></a>
				<?php if ( $bn_wa ) : ?>
					<a class="bn-btn bn-btn-wa bn-btn-block" href="<?php echo esc_url( $bn_wa ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Book on WhatsApp', 'brightnest' ); ?></a>
				<?php endif; ?>
				<?php if ( brightnest_opt( 'phone' ) ) : ?>
					<a class="bn-sv-call" href="<?php echo esc_url( brightnest_tel_url() ); ?>"><?php echo brightnest_icon( 'phone', 16 ); // phpcs:ignore ?> <?php echo esc_html( sprintf( /* translators: %s phone */ __( 'Or call %s', 'brightnest' ), brightnest_opt( 'phone' ) ) ); ?></a>
				<?php endif; ?>
			</div>
			<div class="bn-sv-guarantee">
				<?php echo brightnest_icon( 'shield', 26 ); // phpcs:ignore ?>
				<div><strong><?php esc_html_e( 'Bright Nest guarantee', 'brightnest' ); ?></strong><span><?php esc_html_e( 'Not happy? We come back and fix it for free.', 'brightnest' ); ?></span></div>
			</div>
		</aside>
	</div>

	<?php get_template_part( 'template-parts/reviews' ); ?>

	<?php
	$bn_related = $bn_cat ? get_posts(
		array(
			'post_type'      => 'bn_service',
			'posts_per_page' => 4,
			'fields'         => 'ids',
			'post__not_in'   => array( $bn_id ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
			'tax_query'      => array( array( 'taxonomy' => 'bn_service_cat', 'terms' => $bn_cat->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	) : array();
	if ( count( $bn_related ) < 4 ) {
		$bn_related = array_merge(
			$bn_related,
			get_posts(
				array(
					'post_type'      => 'bn_service',
					'posts_per_page' => 4 - count( $bn_related ),
					'fields'         => 'ids',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'post__not_in'   => array_merge( array( $bn_id ), $bn_related ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
				)
			)
		);
	}
	if ( $bn_related ) :
		?>
		<section class="bn-section bn-sv-related">
			<div class="bn-container">
				<div class="bn-svcs-head">
					<div>
						<span class="bn-eyebrow"><?php esc_html_e( 'You may also need', 'brightnest' ); ?></span>
						<h2 class="bn-h2"><?php esc_html_e( 'Related services', 'brightnest' ); ?></h2>
					</div>
					<a class="bn-btn bn-btn-navy" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'All services', 'brightnest' ); ?></a>
				</div>
				<div class="bn-sc-grid">
					<?php foreach ( $bn_related as $bn_rid ) { get_template_part( 'template-parts/service-card-photo', null, array( 'id' => $bn_rid ) ); } ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="bn-sv-cta">
		<div class="bn-container">
			<div class="bn-sv-cta-inner">
				<div>
					<h2><?php echo esc_html( sprintf( /* translators: %s service */ __( 'Ready to book %s?', 'brightnest' ), $bn_title ) ); ?></h2>
					<p><?php esc_html_e( 'Pick a day and time. It takes less than a minute.', 'brightnest' ); ?></p>
				</div>
				<a class="bn-btn bn-btn-light bn-btn-lg" href="<?php echo esc_url( $bn_book ); ?>"><?php esc_html_e( 'Book now', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></a>
			</div>
		</div>
	</section>

	<div class="bn-sv-mbar">
		<div><small><?php esc_html_e( 'From', 'brightnest' ); ?></small><strong><?php echo '' !== $bn_price ? esc_html( $bn_money( (float) $bn_price ) ) : ''; ?></strong></div>
		<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( $bn_book ); ?>"><?php esc_html_e( 'Book now', 'brightnest' ); ?></a>
	</div>

	<?php
	// Service schema for SEO.
	$bn_schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'name'        => wp_strip_all_tags( html_entity_decode( $bn_title ) ),
		'description' => wp_strip_all_tags( get_the_excerpt() ),
		'url'         => get_permalink(),
		'provider'    => array( '@type' => 'LocalBusiness', 'name' => get_bloginfo( 'name' ), 'telephone' => brightnest_opt( 'phone' ), 'address' => brightnest_opt( 'address' ) ),
		'areaServed'  => brightnest_cities(),
	);
	if ( '' !== $bn_price ) {
		$bn_schema['offers'] = array( '@type' => 'Offer', 'price' => $bn_price, 'priceCurrency' => $bn_cur );
	}
	if ( has_post_thumbnail() ) {
		$bn_schema['image'] = get_the_post_thumbnail_url( $bn_id, 'brightnest-hero' );
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $bn_schema ) . '</script>';

endwhile;

get_footer();
