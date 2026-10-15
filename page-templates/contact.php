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
			<p class="bn-lead"><?php esc_html_e( "We're here 7 days a week. Reach out any way you like.", 'brightnest' ); ?></p>
		</div>
	</section>
	<section class="bn-section">
		<div class="bn-container bn-contact-grid">
			<div>
				<div class="bn-prose"><?php the_content(); ?></div>
				<ul class="bn-contact-cards">
					<?php if ( brightnest_opt( 'phone' ) ) : ?>
						<li><span class="bn-feature-icon"><?php echo brightnest_icon( 'phone', 22 ); // phpcs:ignore ?></span><span><small><?php esc_html_e( 'Call us', 'brightnest' ); ?></small><a href="<?php echo esc_url( brightnest_tel_url() ); ?>"><?php echo esc_html( brightnest_opt( 'phone' ) ); ?></a></span></li>
					<?php endif; ?>
					<?php if ( brightnest_whatsapp_url() ) : ?>
						<li><span class="bn-feature-icon"><?php echo brightnest_icon( 'whatsapp', 22 ); // phpcs:ignore ?></span><span><small>WhatsApp</small><a href="<?php echo esc_url( brightnest_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat with us', 'brightnest' ); ?></a></span></li>
					<?php endif; ?>
					<?php if ( brightnest_opt( 'email' ) ) : ?>
						<li><span class="bn-feature-icon"><?php echo brightnest_icon( 'mail', 22 ); // phpcs:ignore ?></span><span><small><?php esc_html_e( 'Email', 'brightnest' ); ?></small><a href="mailto:<?php echo esc_attr( brightnest_opt( 'email' ) ); ?>"><?php echo esc_html( brightnest_opt( 'email' ) ); ?></a></span></li>
					<?php endif; ?>
					<?php if ( brightnest_opt( 'address' ) ) : ?>
						<li><span class="bn-feature-icon"><?php echo brightnest_icon( 'pin', 22 ); // phpcs:ignore ?></span><span><small><?php esc_html_e( 'Address', 'brightnest' ); ?></small><strong><?php echo esc_html( brightnest_opt( 'address' ) ); ?></strong></span></li>
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
