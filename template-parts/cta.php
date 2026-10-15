<?php
/**
 * Promo / call-to-action banner.
 *
 * @package BrightNest
 */

?>
<section class="bn-section">
	<div class="bn-container">
		<div class="bn-cta">
			<div class="bn-cta-text">
				<span class="bn-eyebrow bn-eyebrow-light"><?php echo brightnest_icon( 'tag', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Limited offer', 'brightnest' ); ?></span>
				<h2><?php echo esc_html( brightnest_opt( 'promo_title' ) ); ?></h2>
				<p><?php echo esc_html( brightnest_opt( 'promo_text' ) ); ?></p>
				<?php if ( brightnest_opt( 'promo_code' ) ) : ?>
					<p class="bn-code"><?php esc_html_e( 'Use code', 'brightnest' ); ?> <strong><?php echo esc_html( brightnest_opt( 'promo_code' ) ); ?></strong></p>
				<?php endif; ?>
			</div>
			<div class="bn-cta-actions">
				<a class="bn-btn bn-btn-light" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book now', 'brightnest' ); ?></a>
				<?php if ( brightnest_whatsapp_url() ) : ?>
					<a class="bn-btn bn-btn-outline-light" href="<?php echo esc_url( brightnest_whatsapp_url( __( 'Hi, I would like to book a service.', 'brightnest' ) ) ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Book on WhatsApp', 'brightnest' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
