<?php
/**
 * Customer reviews slider.
 *
 * @package BrightNest
 */

$bn_reviews = get_posts( array( 'post_type' => 'bn_review', 'posts_per_page' => 12 ) );
if ( ! $bn_reviews ) {
	return;
}
?>
<section class="bn-section bn-section-soft" id="reviews">
	<div class="bn-container">
		<div class="bn-carousel" data-carousel>
			<div class="bn-carousel-head">
				<div>
					<span class="bn-eyebrow"><?php echo esc_html( ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : __( 'Reviews', 'brightnest' ) ); ?></span>
					<h2 class="bn-h2"><?php echo ! empty( $args['title'] ) ? brightnest_accent( $args['title'] ) : esc_html__( 'What our customers say', 'brightnest' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				</div>
				<div class="bn-carousel-nav">
					<button type="button" class="bn-arrow" data-prev aria-label="<?php esc_attr_e( 'Previous', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-left', 18 ); // phpcs:ignore ?></button>
					<button type="button" class="bn-arrow" data-next aria-label="<?php esc_attr_e( 'Next', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></button>
				</div>
			</div>
			<div class="bn-carousel-track" data-track>
				<?php foreach ( $bn_reviews as $bn_r ) : ?>
					<?php $bn_rating = (int) get_post_meta( $bn_r->ID, '_bn_rating', true ) ?: 5; ?>
					<figure class="bn-carousel-item bn-review">
						<div class="bn-review-stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d stars */ __( '%d out of 5 stars', 'brightnest' ), $bn_rating ) ); ?>">
							<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
								<span class="<?php echo $i <= $bn_rating ? 'on' : 'off'; ?>"><?php echo brightnest_icon( 'star', 16 ); // phpcs:ignore ?></span>
							<?php endfor; ?>
						</div>
						<blockquote><?php echo esc_html( wp_strip_all_tags( $bn_r->post_content ) ); ?></blockquote>
						<figcaption>
							<span class="bn-avatar"><?php echo esc_html( mb_substr( $bn_r->post_title, 0, 1 ) ); ?></span>
							<span>
								<strong><?php echo esc_html( $bn_r->post_title ); ?></strong>
								<small class="bn-muted"><?php echo esc_html( get_post_meta( $bn_r->ID, '_bn_service_name', true ) ); ?> · <?php echo esc_html( get_the_date( '', $bn_r ) ); ?></small>
							</span>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
