<?php
/**
 * Google reviews band (homepage): business card with the stars and "Write a review" | review cards
 * from Google in a slider (with a Places API key) — then the Google map of the business.
 * Args: eyebrow, title. Data: inc/google-reviews.php.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

$bn_gr       = brightnest_greviews_data();
$bn_gr_embed = brightnest_greviews_embed();
$bn_gr_all   = brightnest_greviews_maps_url();
$bn_gr_write = brightnest_greviews_write_url();
if ( ! $bn_gr && ! $bn_gr_embed && ! $bn_gr_write ) {
	return;
}
$bn_gr_logo = get_theme_mod( 'custom_logo' ) ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'medium' ) : BRIGHTNEST_URI . '/assets/img/logo.png';
$bn_gr_name = brightnest_opt( 'greviews_name' ) ? brightnest_opt( 'greviews_name' ) : get_bloginfo( 'name' );
$bn_gr_rate = ! empty( $bn_gr['rating'] ) ? (float) $bn_gr['rating'] : 0;
$bn_gr_list = ! empty( $bn_gr['reviews'] ) ? $bn_gr['reviews'] : array();

// Five stars, filled up to the rating (e.g. 4.8 → the last one 80 %).
$bn_stars = function ( $rating, $size ) {
	$out = '<span class="bn-grev-stars" style="--bn-stars:' . esc_attr( max( 0, min( 5, $rating ) ) / 5 * 100 ) . '%;--bn-star:' . (int) $size . 'px" role="img" aria-label="' . esc_attr( sprintf( /* translators: %s rating */ __( '%s out of 5 stars', 'brightnest' ), number_format_i18n( $rating, 1 ) ) ) . '">';
	$out .= str_repeat( '<svg viewBox="0 0 24 24" width="' . (int) $size . '" height="' . (int) $size . '" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6L2.5 9.4l6.6-.8z"/></svg>', 5 );
	return $out . '</span>';
};
$bn_g = '<svg class="bn-grev-g" viewBox="0 0 48 48" width="22" height="22" aria-label="Google" role="img"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/></svg>';
?>
<section class="bn-grev" id="reviews">
	<div class="bn-container">
		<div class="bn-jx-head">
			<?php if ( ! empty( $args['eyebrow'] ) ) : ?><span class="bn-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></span><?php endif; ?>
			<h2><?php echo brightnest_accent( ! empty( $args['title'] ) ? $args['title'] : __( 'What our customers say', 'brightnest' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		</div>

		<div class="bn-grev-grid<?php echo $bn_gr_list ? '' : ' is-map'; ?>">
			<div class="bn-grev-sum">
				<span class="bn-grev-logo"><img src="<?php echo esc_url( $bn_gr_logo ); ?>" alt="" width="72" height="72" loading="lazy"></span>
				<div>
					<strong class="bn-grev-name"><?php echo esc_html( $bn_gr_name ); ?></strong>
					<?php if ( $bn_gr_rate ) : ?>
						<p class="bn-grev-score"><b><?php echo esc_html( number_format_i18n( $bn_gr_rate, 1 ) ); ?></b> <?php echo $bn_stars( $bn_gr_rate, 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php endif; ?>
					<p class="bn-grev-count"><?php echo $bn_g; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG ?>
						<?php
						echo esc_html(
							! empty( $bn_gr['count'] )
								/* translators: %s number of reviews */
								? sprintf( _n( '%s Google review', '%s Google reviews', (int) $bn_gr['count'], 'brightnest' ), number_format_i18n( (int) $bn_gr['count'] ) )
								: __( 'Reviews on Google', 'brightnest' )
						);
						?>
					</p>
					<div class="bn-grev-btns">
						<?php if ( $bn_gr_write ) : ?>
							<a class="bn-btn bn-btn-ghost" href="<?php echo esc_url( $bn_gr_write ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Write a review', 'brightnest' ); ?></a>
						<?php endif; ?>
						<?php if ( $bn_gr_all ) : ?>
							<a class="bn-grev-all" href="<?php echo esc_url( $bn_gr_all ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'See all reviews on Google', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 14 ); // phpcs:ignore ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if ( $bn_gr_list ) : ?>
				<div class="bn-grev-slider" data-carousel data-autoplay="5000">
					<ul class="bn-grev-track bn-carousel-track" data-track>
						<?php foreach ( $bn_gr_list as $bn_r ) : ?>
							<li class="bn-grev-card bn-carousel-item">
								<div class="bn-grev-who">
									<?php if ( $bn_r['photo'] ) : ?>
										<img class="bn-grev-ava" src="<?php echo esc_url( $bn_r['photo'] ); ?>" alt="" width="44" height="44" loading="lazy" referrerpolicy="no-referrer">
									<?php else : ?>
										<span class="bn-grev-ava"><?php echo esc_html( mb_strtoupper( mb_substr( $bn_r['name'], 0, 1 ) ) ); ?></span>
									<?php endif; ?>
									<div class="bn-grev-meta">
										<?php if ( $bn_r['link'] ) : ?>
											<a class="bn-grev-author" href="<?php echo esc_url( $bn_r['link'] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $bn_r['name'] ); ?></a>
										<?php else : ?>
											<span class="bn-grev-author"><?php echo esc_html( $bn_r['name'] ); ?></span>
										<?php endif; ?>
										<small><?php echo esc_html( $bn_r['when'] ); ?></small>
									</div>
									<?php echo $bn_g; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG ?>
								</div>
								<p class="bn-grev-rate">
									<?php echo $bn_stars( $bn_r['rating'], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<svg class="bn-grev-tick" viewBox="0 0 24 24" width="18" height="18" aria-label="<?php esc_attr_e( 'Verified on Google', 'brightnest' ); ?>" role="img"><path fill="#1a73e8" d="M12 1l2.6 2.2 3.4-.4.9 3.3 3 1.7-1.3 3.2 1.3 3.2-3 1.7-.9 3.3-3.4-.4L12 23l-2.6-2.2-3.4.4-.9-3.3-3-1.7L3.4 13 2.1 9.8l3-1.7.9-3.3 3.4.4z"/><path d="M7.5 12.3l3 3 6-6.2" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
								</p>
								<div class="bn-more bn-more-sm" data-more>
									<div class="bn-more-body"><p><?php echo esc_html( $bn_r['text'] ); ?></p></div>
									<button type="button" class="bn-more-btn" data-more-btn hidden><?php esc_html_e( 'Read more', 'brightnest' ); ?></button>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
					<div class="bn-h2-slider-nav">
						<button type="button" class="bn-arrow" data-prev aria-label="<?php esc_attr_e( 'Previous', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-left', 18 ); // phpcs:ignore ?></button>
						<span class="bn-h2-dots" data-dots></span>
						<button type="button" class="bn-arrow" data-next aria-label="<?php esc_attr_e( 'Next', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></button>
					</div>
				</div>
			<?php elseif ( $bn_gr_embed ) : ?>
				<div class="bn-grev-map"><iframe src="<?php echo esc_url( $bn_gr_embed ); ?>" title="<?php echo esc_attr( $bn_gr_name ); ?>" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
			<?php endif; ?>
		</div>

		<?php if ( $bn_gr_list && $bn_gr_embed ) : ?>
			<div class="bn-grev-map bn-grev-map-wide"><iframe src="<?php echo esc_url( $bn_gr_embed ); ?>" title="<?php echo esc_attr( $bn_gr_name ); ?>" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
		<?php endif; ?>
	</div>
</section>
