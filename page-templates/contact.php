<?php
/**
 * Template Name: Contact
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="bn-page-hero">
		<div class="bn-container">
			<?php brightnest_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>
			<p class="bn-lead"><?php esc_html_e( "Call, WhatsApp, email or visit our office. We reply quickly.", 'brightnest' ); ?></p>
		</div>
	</section>
	<section class="bn-section">
		<div class="bn-container bn-contact-grid">
			<div>
				<div class="bn-prose"><?php the_content(); ?></div>
				<ul class="bn-contact-cards">
					<?php foreach ( array_filter( array( brightnest_opt( 'phone' ), brightnest_opt( 'phone2' ) ) ) as $bn_ph ) : ?>
						<li><span class="bn-feature-icon"><?php echo brightnest_icon( 'phone', 22 ); // phpcs:ignore ?></span><span><small><?php esc_html_e( 'Call us', 'brightnest' ); ?></small><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $bn_ph ) ); ?>" dir="ltr"><?php echo esc_html( $bn_ph ); ?></a></span></li>
					<?php endforeach; ?>
					<?php if ( brightnest_whatsapp_url() ) : ?>
						<li><span class="bn-feature-icon"><?php echo brightnest_icon( 'whatsapp', 22 ); // phpcs:ignore ?></span><span><small>WhatsApp</small><a href="<?php echo esc_url( brightnest_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat with us', 'brightnest' ); ?></a></span></li>
					<?php endif; ?>
					<?php if ( brightnest_opt( 'email' ) ) : ?>
						<li><span class="bn-feature-icon"><?php echo brightnest_icon( 'mail', 22 ); // phpcs:ignore ?></span><span><small><?php esc_html_e( 'Email', 'brightnest' ); ?></small><a href="mailto:<?php echo esc_attr( brightnest_opt( 'email' ) ); ?>"><?php echo esc_html( brightnest_opt( 'email' ) ); ?></a></span></li>
					<?php endif; ?>
					<?php if ( brightnest_opt( 'address' ) ) : ?>
						<li><span class="bn-feature-icon"><?php echo brightnest_icon( 'pin', 22 ); // phpcs:ignore ?></span><span><small><?php esc_html_e( 'Address', 'brightnest' ); ?></small><strong><?php echo esc_html( brightnest_opt( 'address' ) ); ?></strong></span></li>
					<?php endif; ?>
					<?php if ( brightnest_opt( 'hours' ) ) : ?>
						<li><span class="bn-feature-icon"><?php echo brightnest_icon( 'clock', 22 ); // phpcs:ignore ?></span><span><small><?php esc_html_e( 'Working hours', 'brightnest' ); ?></small><strong><?php echo esc_html( brightnest_opt( 'hours' ) ); ?></strong></span></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="bn-booking-card">
				<h2 class="bn-h3"><?php esc_html_e( 'Send us a message', 'brightnest' ); ?></h2>
				<?php brightnest_contact_form(); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
