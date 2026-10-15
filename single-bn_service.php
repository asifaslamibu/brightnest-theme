<?php
/**
 * Single service with booking form.
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bn_id       = get_the_ID();
	$bn_icon     = brightnest_service_icon( $bn_id );
	$bn_terms    = get_the_terms( $bn_id, 'bn_service_cat' );
	$bn_cat      = ( $bn_terms && ! is_wp_error( $bn_terms ) ) ? $bn_terms[0] : null;
	$bn_includes = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $bn_id, '_bn_includes', true ) ) ) );
	$bn_duration = get_post_meta( $bn_id, '_bn_duration', true );
	?>

	<section class="bn-page-hero bn-service-hero">
		<div class="bn-container bn-service-hero-grid">
			<div>
				<?php brightnest_breadcrumbs(); ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="bn-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<div class="bn-service-facts">
					<?php echo brightnest_price( $bn_id ); // phpcs:ignore ?>
					<?php echo brightnest_stars( get_post_meta( $bn_id, '_bn_rating', true ), get_post_meta( $bn_id, '_bn_review_count', true ) ); // phpcs:ignore ?>
					<?php if ( $bn_duration ) : ?>
						<span class="bn-fact"><?php echo brightnest_icon( 'clock', 16 ); // phpcs:ignore ?> <?php echo esc_html( $bn_duration ); ?></span>
					<?php endif; ?>
				</div>
				<ul class="bn-trust-list">
					<li><?php echo brightnest_icon( 'check-circle', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Vetted professionals', 'brightnest' ); ?></li>
					<li><?php echo brightnest_icon( 'check-circle', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Pay after service', 'brightnest' ); ?></li>
					<li><?php echo brightnest_icon( 'check-circle', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Satisfaction guarantee', 'brightnest' ); ?></li>
				</ul>
			</div>
			<div class="bn-service-media bn-tone-<?php echo esc_attr( brightnest_tone( $bn_cat ? $bn_cat->slug : $bn_id ) ); ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'brightnest-hero' ); ?>
				<?php else : ?>
					<?php echo brightnest_icon( $bn_icon, 96 ); // phpcs:ignore ?>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="bn-section">
		<div class="bn-container bn-service-layout">
			<div class="bn-service-content">
				<?php if ( $bn_includes ) : ?>
					<div class="bn-box">
						<h2 class="bn-h3"><?php esc_html_e( "What's included", 'brightnest' ); ?></h2>
						<ul class="bn-includes">
							<?php foreach ( $bn_includes as $bn_item ) : ?>
								<li><?php echo brightnest_icon( 'check', 18 ); // phpcs:ignore ?> <?php echo esc_html( $bn_item ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="bn-prose">
					<?php the_content(); ?>
				</div>

				<div class="bn-box">
					<h2 class="bn-h3"><?php esc_html_e( 'Frequently asked questions', 'brightnest' ); ?></h2>
					<?php get_template_part( 'template-parts/faq' ); ?>
				</div>
			</div>

			<aside class="bn-booking" aria-labelledby="bn-booking-title">
				<div class="bn-booking-card">
					<h2 id="bn-booking-title" class="bn-h3"><?php esc_html_e( 'Book this service', 'brightnest' ); ?></h2>
					<div class="bn-book-price"><?php echo brightnest_price( $bn_id ); // phpcs:ignore ?></div>
					<ul class="bn-book-steps">
						<li><span>1</span> <?php esc_html_e( 'Choose options', 'brightnest' ); ?></li>
						<li><span>2</span> <?php esc_html_e( 'Pick date & time', 'brightnest' ); ?></li>
						<li><span>3</span> <?php esc_html_e( 'Add address', 'brightnest' ); ?></li>
						<li><span>4</span> <?php esc_html_e( 'Confirm booking', 'brightnest' ); ?></li>
					</ul>
					<a class="bn-btn bn-btn-primary bn-btn-block bn-btn-lg" href="<?php echo esc_url( brightnest_checkout_url( $bn_id ) ); ?>"><?php esc_html_e( 'Book now', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></a>
					<p class="bn-form-note"><?php echo brightnest_icon( 'shield', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'No payment now. Pay after the service.', 'brightnest' ); ?></p>
					<details class="bn-callback">
						<summary><?php esc_html_e( 'Prefer a call back? Send a quick request', 'brightnest' ); ?></summary>
						<?php brightnest_booking_form( get_the_title() ); ?>
					</details>
					<?php if ( brightnest_whatsapp_url() ) : ?>
						<a class="bn-btn bn-btn-wa bn-btn-block" href="<?php echo esc_url( brightnest_whatsapp_url( sprintf( /* translators: %s service */ __( 'Hi, I would like to book %s.', 'brightnest' ), get_the_title() ) ) ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Book on WhatsApp', 'brightnest' ); ?></a>
					<?php endif; ?>
				</div>
			</aside>
		</div>
	</section>

	<?php
	if ( $bn_cat ) {
		$bn_related = get_posts(
			array(
				'post_type'      => 'bn_service',
				'posts_per_page' => 8,
				'fields'         => 'ids',
				'post__not_in'   => array( $bn_id ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
				'tax_query'      => array( array( 'taxonomy' => 'bn_service_cat', 'terms' => $bn_cat->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		if ( count( $bn_related ) < 3 ) {
			$bn_related = array_merge(
				$bn_related,
				get_posts(
					array(
						'post_type'      => 'bn_service',
						'posts_per_page' => 6,
						'fields'         => 'ids',
						'post__not_in'   => array_merge( array( $bn_id ), $bn_related ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
						'meta_key'       => '_bn_featured', // phpcs:ignore WordPress.DB.SlowDBQuery
						'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
					)
				)
			);
		}
		if ( $bn_related ) {
			echo '<section class="bn-section bn-section-soft"><div class="bn-container">';
			get_template_part(
				'template-parts/carousel',
				null,
				array(
					'title' => __( 'You may also need', 'brightnest' ),
					'link'  => get_term_link( $bn_cat ),
					'ids'   => $bn_related,
				)
			);
			echo '</div></section>';
		}
	}

	// Service schema for SEO.
	$bn_schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'name'        => get_the_title(),
		'description' => wp_strip_all_tags( get_the_excerpt() ),
		'url'         => get_permalink(),
		'provider'    => array( '@type' => 'LocalBusiness', 'name' => get_bloginfo( 'name' ), 'telephone' => brightnest_opt( 'phone' ), 'address' => brightnest_opt( 'address' ) ),
		'areaServed'  => brightnest_cities(),
	);
	$bn_price = get_post_meta( $bn_id, '_bn_price', true );
	if ( '' !== $bn_price ) {
		$bn_schema['offers'] = array( '@type' => 'Offer', 'price' => $bn_price, 'priceCurrency' => brightnest_opt( 'currency' ) );
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $bn_schema ) . '</script>';

endwhile;

get_footer();
