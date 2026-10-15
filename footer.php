<?php
/**
 * Footer.
 *
 * @package BrightNest
 */

$bn_cats = array_slice( brightnest_categories(), 0, 8 );
$bn_page = function ( $slug, $fallback ) {
	$p = get_page_by_path( $slug );
	return $p ? '<a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a>' : '<span>' . esc_html( $fallback ) . '</span>';
};
?>
</main>

<footer class="bn-footer">
	<div class="bn-container">
		<div class="bn-footer-grid">
			<div class="bn-footer-brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="bn-logo-text bn-logo-light">
					<span class="bn-logo-mark"><?php echo brightnest_icon( 'home', 20 ); // phpcs:ignore ?></span>
					<span><?php bloginfo( 'name' ); ?></span>
				</a>
				<p><?php echo esc_html( brightnest_opt( 'footer_about' ) ); ?></p>
				<?php $bn_social = brightnest_social_links(); ?>
				<?php if ( $bn_social ) : ?>
					<div class="bn-social"><?php echo $bn_social; // phpcs:ignore ?></div>
				<?php endif; ?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Services', 'brightnest' ); ?></h4>
				<ul>
					<?php foreach ( $bn_cats as $bn_term ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $bn_term ) ); ?>"><?php echo esc_html( $bn_term->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div>
				<h4><?php esc_html_e( 'Company', 'brightnest' ); ?></h4>
				<ul>
					<li><?php echo $bn_page( 'about-us', 'About us' ); // phpcs:ignore ?></li>
					<li><?php echo $bn_page( 'faq', 'FAQs' ); // phpcs:ignore ?></li>
					<li><?php echo $bn_page( 'contact', 'Contact us' ); // phpcs:ignore ?></li>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'All services', 'brightnest' ); ?></a></li>
					<?php if ( get_option( 'page_for_posts' ) ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Blog', 'brightnest' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>

			<div>
				<h4><?php esc_html_e( 'Get in touch', 'brightnest' ); ?></h4>
				<ul class="bn-footer-contact">
					<?php if ( brightnest_opt( 'phone' ) ) : ?>
						<li><?php echo brightnest_icon( 'phone', 16 ); // phpcs:ignore ?><a href="<?php echo esc_url( brightnest_tel_url() ); ?>"><?php echo esc_html( brightnest_opt( 'phone' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( brightnest_opt( 'email' ) ) : ?>
						<li><?php echo brightnest_icon( 'mail', 16 ); // phpcs:ignore ?><a href="mailto:<?php echo esc_attr( brightnest_opt( 'email' ) ); ?>"><?php echo esc_html( brightnest_opt( 'email' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( brightnest_opt( 'address' ) ) : ?>
						<li><?php echo brightnest_icon( 'pin', 16 ); // phpcs:ignore ?><span><?php echo esc_html( brightnest_opt( 'address' ) ); ?></span></li>
					<?php endif; ?>
					<li><?php echo brightnest_icon( 'clock', 16 ); // phpcs:ignore ?><span><?php echo esc_html( brightnest_opt( 'hours' ) ); ?></span></li>
				</ul>
			</div>
		</div>

		<div class="bn-footer-bottom">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'brightnest' ); ?></p>
			<p class="bn-footer-legal">
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
