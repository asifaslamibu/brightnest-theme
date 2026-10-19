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

<footer class="bn-footer">
	<div class="bn-container">
		<div class="bn-footer-strip">
			<?php if ( brightnest_opt( 'address' ) ) : ?>
				<div><span class="bn-fs-ic"><?php echo brightnest_icon( 'pin', 22 ); // phpcs:ignore ?></span><span><strong><?php esc_html_e( 'Address', 'brightnest' ); ?></strong><?php echo esc_html( brightnest_opt( 'address' ) ); ?></span></div>
			<?php endif; ?>
			<div><span class="bn-fs-ic"><?php echo brightnest_icon( 'phone', 22 ); // phpcs:ignore ?></span><span><strong><?php esc_html_e( 'Contact', 'brightnest' ); ?></strong>
				<?php if ( brightnest_opt( 'email' ) ) : ?><a href="mailto:<?php echo esc_attr( brightnest_opt( 'email' ) ); ?>"><?php echo esc_html( brightnest_opt( 'email' ) ); ?></a><br><?php endif; ?>
				<a href="<?php echo esc_url( brightnest_tel_url() ); ?>"><?php echo esc_html( brightnest_opt( 'phone' ) ); ?></a></span></div>
			<div><span class="bn-fs-ic"><?php echo brightnest_icon( 'clock', 22 ); // phpcs:ignore ?></span><span><strong><?php esc_html_e( 'Working hours', 'brightnest' ); ?></strong><?php echo esc_html( brightnest_opt( 'hours' ) ); ?></span></div>
		</div>

		<div class="bn-footer-grid">
			<div class="bn-footer-brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="bn-logo-img bn-logo-footer">
					<img src="<?php echo esc_url( BRIGHTNEST_URI . '/assets/img/logo-light.png' ); ?>" width="720" height="200" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy">
				</a>
				<p><?php echo esc_html( brightnest_opt( 'footer_about' ) ); ?></p>
				<?php $bn_social = brightnest_social_links(); ?>
				<?php if ( $bn_social ) : ?>
					<div class="bn-social"><?php echo $bn_social; // phpcs:ignore ?></div>
				<?php endif; ?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Our services', 'brightnest' ); ?></h4>
				<ul>
					<?php foreach ( array_slice( $bn_all, 0, 7 ) as $bn_sid ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $bn_sid ) ); ?>"><?php echo esc_html( get_the_title( $bn_sid ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div>
				<h4><?php esc_html_e( 'Company', 'brightnest' ); ?></h4>
				<ul>
					<?php foreach ( array( 'about-us' => 'About us', 'contact' => 'Contact us', 'faq' => 'FAQs' ) as $bn_slug => $bn_label ) : ?>
						<?php $bn_link = $bn_page( $bn_slug, $bn_label ); ?>
						<?php if ( $bn_link ) : ?><li><?php echo $bn_link; // phpcs:ignore ?></li><?php endif; ?>
					<?php endforeach; ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'All services', 'brightnest' ); ?></a></li>
					<?php if ( get_option( 'page_for_posts' ) ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Blog', 'brightnest' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>

		</div>

		<div class="bn-footer-bottom">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'brightnest' ); ?></p>
			<p class="bn-footer-legal">
				<?php echo $bn_page( 'contact', 'Contact Us' ); // phpcs:ignore ?>
				<?php echo $bn_page( 'faq', 'F.A.Q' ); // phpcs:ignore ?>
				<?php echo $bn_page( 'terms', 'Terms' ); // phpcs:ignore ?>
				<?php echo $bn_page( 'privacy-policy-bn', 'Privacy' ); // phpcs:ignore ?>
			</p>
		</div>
	</div>
</footer>

<?php if ( brightnest_whatsapp_url() ) : ?>
	<a class="bn-wa-float" href="<?php echo esc_url( brightnest_whatsapp_url( __( 'Hi, I would like to book a service.', 'brightnest' ) ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'brightnest' ); ?>">
		<?php echo brightnest_icon( 'whatsapp', 28 ); // phpcs:ignore ?>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
