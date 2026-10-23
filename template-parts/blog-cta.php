<?php
/**
 * Booking band at the end of the blog pages and articles.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="bn-blog-cta">
	<div class="bn-container">
		<div class="bn-blog-cta-card">
			<div>
				<h2><?php esc_html_e( 'Need a hand at home?', 'brightnest' ); ?></h2>
				<p><?php esc_html_e( 'Book a vetted Bright Nest professional in a minute. Clear prices, secure online payment.', 'brightnest' ); ?></p>
			</div>
			<div class="bn-blog-cta-actions">
				<a class="bn-btn bn-btn-primary bn-btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
				<?php if ( brightnest_whatsapp_url() ) : ?>
					<a class="bn-btn bn-btn-wa bn-btn-lg" href="<?php echo esc_url( brightnest_whatsapp_url( __( 'Hi, I would like to book a service.', 'brightnest' ) ) ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> WhatsApp</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
