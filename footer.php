<?php
/**
 * Footer.
 *
 * @package BrightNest
 */

$bn_all  = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC' ) );
$bn_page = function ( $slug, $fallback ) {
	$p = get_page_by_path( $slug );
	return $p ? '<a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a>' : '';
};
?>
</main>

<?php // Justlife-style footer: service chips, area chips, then social icons and links. ?>
<footer class="bn-footer bn-jlf">
	<div class="bn-container">
		<?php if ( $bn_all ) : ?>
			<h2 class="bn-jlf-h"><?php esc_html_e( 'Service Areas', 'brightnest' ); ?></h2>
			<ul class="bn-jlf-chips">
				<?php foreach ( $bn_all as $bn_sid ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $bn_sid ) ); ?>"><?php echo esc_html( get_the_title( $bn_sid ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="bn-jlf-cities">
			<h2 class="bn-jlf-h"><?php echo esc_html( brightnest_opt( 'country' ) ? brightnest_opt( 'country' ) : __( 'United Arab Emirates', 'brightnest' ) ); ?></h2>
			<ul class="bn-jlf-chips">
				<?php foreach ( brightnest_cities() as $bn_city ) : ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php echo esc_html( brightnest_city_label( $bn_city ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="bn-jlf-bottom">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="bn-jlf-logo">
				<img src="<?php echo esc_url( BRIGHTNEST_URI . '/assets/img/logo-light.png' ); ?>" width="720" height="200" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy">
			</a>
			<?php $bn_social = brightnest_social_links(); ?>
			<div class="bn-jlf-social">
				<?php if ( brightnest_whatsapp_url() ) : ?>
					<a href="<?php echo esc_url( brightnest_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?></a>
				<?php endif; ?>
				<?php echo $bn_social; // phpcs:ignore ?>
			</div>
			<nav class="bn-jlf-links" aria-label="<?php esc_attr_e( 'Footer', 'brightnest' ); ?>">
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1, 'items_wrap' => '%3$s', 'fallback_cb' => false ) ); ?>
				<?php else : ?>
					<?php echo $bn_page( 'about-us', 'About Us' ); // phpcs:ignore ?>
					<?php echo $bn_page( 'contact', 'Contact Us' ); // phpcs:ignore ?>
					<?php echo $bn_page( 'faq', 'F.A.Q' ); // phpcs:ignore ?>
					<?php echo $bn_page( 'terms', 'Terms' ); // phpcs:ignore ?>
					<?php echo $bn_page( 'privacy-policy-bn', 'Privacy' ); // phpcs:ignore ?>
					<?php if ( get_option( 'page_for_posts' ) ) : ?>
						<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Blog', 'brightnest' ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</nav>
		</div>
		<p class="bn-jlf-copy">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'brightnest' ); ?></p>
	</div>
</footer>

<?php if ( brightnest_whatsapp_url() ) : ?>
	<a class="bn-wa-float" href="<?php echo esc_url( brightnest_whatsapp_url( __( 'Hi, I would like to book a service.', 'brightnest' ) ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'brightnest' ); ?>">
		<?php echo brightnest_icon( 'whatsapp', 28 ); // phpcs:ignore ?>
	</a>
<?php endif; ?>

<?php // First visit: pick an area before anything else. ?>
<?php get_template_part( 'template-parts/area-gate' ); ?>
<?php get_template_part( 'template-parts/offer-popup' ); ?>

<?php wp_footer(); ?>
</body>
</html>
