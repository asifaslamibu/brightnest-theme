<?php
/**
 * Horizontal service carousel.
 * Args: title, subtitle, link, ids.
 *
 * @package BrightNest
 */

if ( empty( $args['ids'] ) ) {
	return;
}
?>
<div class="bn-carousel" data-carousel>
	<div class="bn-carousel-head">
		<div>
			<h3 class="bn-carousel-title"><?php echo esc_html( $args['title'] ); ?></h3>
			<?php if ( ! empty( $args['subtitle'] ) ) : ?>
				<p class="bn-muted"><?php echo esc_html( $args['subtitle'] ); ?></p>
			<?php endif; ?>
		</div>
		<div class="bn-carousel-nav">
			<?php if ( ! empty( $args['link'] ) ) : ?>
				<a class="bn-link" href="<?php echo esc_url( $args['link'] ); ?>"><?php esc_html_e( 'View all', 'brightnest' ); ?></a>
			<?php endif; ?>
			<button type="button" class="bn-arrow" data-prev aria-label="<?php esc_attr_e( 'Previous', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-left', 18 ); // phpcs:ignore ?></button>
			<button type="button" class="bn-arrow" data-next aria-label="<?php esc_attr_e( 'Next', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></button>
		</div>
	</div>
	<div class="bn-carousel-track" data-track>
		<?php foreach ( $args['ids'] as $bn_sid ) : ?>
			<div class="bn-carousel-item"><?php brightnest_service_card( $bn_sid ); ?></div>
		<?php endforeach; ?>
	</div>
</div>
