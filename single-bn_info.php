<?php
/**
 * Service page (bn_info, inc/service-pages.php), Justlife style: one narrow column — breadcrumbs, a photo
 * card with the title, a short line and the Book button on it, the page text, FAQ, then other services
 * in a slider and the booking band.
 * Book now opens the checkout of the page's "Booking service"; without one, "Get a quote" opens WhatsApp.
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bn_id    = get_the_ID();
	$bn_title = wp_strip_all_tags( html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ) );
	$bn_sid   = brightnest_info_service( $bn_id );
	$bn_book  = $bn_sid ? brightnest_checkout_url( $bn_sid ) : brightnest_quote_url( $bn_id );
	$bn_label = brightnest_sv_text( $bn_sid ? 'sv_book_label' : 'sv_quote_label' );
	$bn_out   = $bn_sid ? '' : ' target="_blank" rel="noopener"'; // The quote opens WhatsApp.
	$bn_img   = has_post_thumbnail() ? get_the_post_thumbnail_url( $bn_id, 'large' ) : BRIGHTNEST_URI . '/assets/img/site/about-1.jpg';
	$bn_faqs  = brightnest_faqs( $bn_id );
	?>
	<article class="bn-info">
		<div class="bn-container bn-info-col">
			<?php brightnest_breadcrumbs(); ?>
			<header class="bn-info-hero" style="background-image:url('<?php echo esc_url( $bn_img ); ?>')">
				<div class="bn-info-hero-copy">
					<h1><?php echo esc_html( brightnest_page_h1() ); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( $bn_book ); ?>"<?php echo $bn_out; // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $bn_label ); ?></a>
				</div>
			</header>

			<?php if ( '' !== trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
				<div class="bn-prose bn-info-body"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( $bn_faqs ) : ?>
				<section class="bn-info-faq" id="faq">
					<h2><?php echo esc_html( brightnest_sv_text( 'sv_faq_title' ) ); ?></h2>
					<?php get_template_part( 'template-parts/faq' ); ?>
				</section>
			<?php endif; ?>
		</div>
	</article>

	<?php
	// Other services: the bookable ones, in the Services list order.
	$bn_other = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => 12, 'fields' => 'ids', 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
	$bn_other = array_values( array_filter( $bn_other, function ( $id ) { return ! brightnest_info_only( $id ); } ) );
	?>
	<?php if ( $bn_other ) : ?>
		<section class="bn-info-more" data-carousel>
			<div class="bn-container bn-info-col">
				<div class="bn-info-more-head">
					<div>
						<span class="bn-info-kicker"><?php esc_html_e( 'Everything you need', 'brightnest' ); ?></span>
						<h2><?php esc_html_e( 'What other services can we help you with?', 'brightnest' ); ?></h2>
					</div>
					<div class="bn-info-more-nav">
						<button type="button" class="bn-arrow" data-prev aria-label="<?php esc_attr_e( 'Previous', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-left', 16 ); // phpcs:ignore ?></button>
						<button type="button" class="bn-arrow" data-next aria-label="<?php esc_attr_e( 'Next', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></button>
					</div>
				</div>
				<ul class="bn-info-more-track bn-carousel-track" data-track>
					<?php foreach ( $bn_other as $bn_oid ) : ?>
						<li class="bn-carousel-item">
							<a href="<?php echo esc_url( brightnest_checkout_url( $bn_oid ) ); ?>">
								<span class="bn-info-more-img">
									<?php if ( has_post_thumbnail( $bn_oid ) ) : ?>
										<?php echo get_the_post_thumbnail( $bn_oid, 'brightnest-card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
									<?php else : ?>
										<span class="bn-info-more-icon"><?php echo brightnest_service_icon_html( $bn_oid, 40 ); // phpcs:ignore ?></span>
									<?php endif; ?>
								</span>
								<span class="bn-info-more-name"><?php echo esc_html( get_the_title( $bn_oid ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<div class="bn-sv-mbar">
		<div><small><?php echo esc_html( $bn_title ); ?></small></div>
		<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( $bn_book ); ?>"<?php echo $bn_out; // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $bn_label ); ?></a>
	</div>
	<?php
endwhile;

get_template_part( 'template-parts/blog-cta' );

get_footer();
