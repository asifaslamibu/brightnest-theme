<?php
/**
 * Footer: a booking band, then columns (brand and social, service pages in two columns, book online
 * with company under it, contact), the cities served, and the copyright line.
 *
 * @package BrightNest
 */

// Service pages: the four main ones (house cleaning, deep cleaning, maid, AC), then "View all services".
$bn_info  = get_posts( array( 'post_type' => 'bn_info', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'title', 'order' => 'ASC' ) );
$bn_rank  = function ( $id ) {
	foreach ( array( '/\b(house|home) clean/i', '/\bdeep clean/i', '/\bmaid/i', '/\bac\b|air ?con/i' ) as $i => $re ) {
		if ( preg_match( $re, get_the_title( $id ) ) ) {
			return $i;
		}
	}
	return 9;
};
usort( $bn_info, function ( $a, $b ) use ( $bn_rank ) { return $bn_rank( $a ) - $bn_rank( $b ); } ); // Stable: A to Z within a rank.
$bn_more  = count( $bn_info ) > 4;
$bn_info  = array_slice( $bn_info, 0, 4 );
$bn_book  = array_slice( array_values( array_filter( get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => 12, 'fields' => 'ids', 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) ), function ( $id ) { return ! brightnest_info_only( $id ); } ) ), 0, 8 );
$bn_page  = function ( $slug ) {
	$p = brightnest_core_page( $slug );
	return $p ? '<li><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></li>' : '';
};
$bn_wa    = brightnest_whatsapp_url( __( 'Hi, I would like to book a service.', 'brightnest' ) );
$bn_email = brightnest_opt( 'email' );
$bn_addr  = brightnest_opt( 'address' );
?>
</main>

<?php $bn_cta = empty( $GLOBALS['bn_cta_shown'] ) && ! ( function_exists( 'brightnest_is_checkout' ) && brightnest_is_checkout() ); // One booking band per page. ?>
<footer class="bn-footer bn-ft<?php echo $bn_cta ? '' : ' no-cta'; ?>">
	<div class="bn-container">
		<?php if ( $bn_cta ) : ?>
		<div class="bn-ft-cta">
			<div>
				<strong><?php esc_html_e( 'Ready for a spotless home?', 'brightnest' ); ?></strong>
				<span><?php esc_html_e( 'Book a vetted professional in a minute. Clear prices, secure online payment.', 'brightnest' ); ?></span>
			</div>
			<div class="bn-ft-cta-btns">
				<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
				<?php if ( $bn_wa ) : ?>
					<a class="bn-btn bn-ft-wa" href="<?php echo esc_url( $bn_wa ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> WhatsApp</a>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="bn-ft-grid">
			<div class="bn-ft-brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="bn-ft-logo">
					<img src="<?php echo esc_url( BRIGHTNEST_URI . '/assets/img/logo-light.png' ); ?>" width="720" height="200" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy">
				</a>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="bn-ft-logo bn-ft-logo-ar notranslate">
					<img src="<?php echo esc_url( BRIGHTNEST_URI . '/assets/img/logo-ar-light.png' ); ?>" width="720" height="280" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy">
				</a>
				<?php if ( brightnest_opt( 'footer_about' ) ) : ?><p><?php echo esc_html( brightnest_opt( 'footer_about' ) ); ?></p><?php endif; ?>
				<div class="bn-ft-social">
					<?php if ( brightnest_whatsapp_url() ) : ?>
						<a href="<?php echo esc_url( brightnest_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?></a>
					<?php endif; ?>
					<?php echo brightnest_social_links(); // phpcs:ignore ?>
				</div>
			</div>

			<?php if ( $bn_info ) : ?>
				<nav class="bn-ft-col bn-ft-services" aria-label="<?php esc_attr_e( 'Our Services', 'brightnest' ); ?>">
					<h2><a href="<?php echo esc_url( get_post_type_archive_link( 'bn_info' ) ); ?>"><?php esc_html_e( 'Our Services', 'brightnest' ); ?></a></h2>
					<ul>
						<?php foreach ( $bn_info as $bn_id ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $bn_id ) ); ?>"><?php echo esc_html( get_the_title( $bn_id ) ); ?></a></li>
						<?php endforeach; ?>
						<?php if ( $bn_more ) : ?>
							<li><a class="bn-ft-more" href="<?php echo esc_url( get_post_type_archive_link( 'bn_info' ) ); ?>"><?php esc_html_e( 'View all services', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 14 ); // phpcs:ignore ?></a></li>
						<?php endif; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<div class="bn-ft-stack">
			<?php if ( $bn_book ) : ?>
				<nav class="bn-ft-col" aria-label="<?php esc_attr_e( 'Book online', 'brightnest' ); ?>">
					<h2><a href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book online', 'brightnest' ); ?></a></h2>
					<ul>
						<?php foreach ( $bn_book as $bn_id ) : ?>
							<li><a href="<?php echo esc_url( brightnest_checkout_url( $bn_id ) ); ?>"><?php echo esc_html( get_the_title( $bn_id ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<nav class="bn-ft-col" aria-label="<?php esc_attr_e( 'Company', 'brightnest' ); ?>">
				<h2><?php esc_html_e( 'Company', 'brightnest' ); ?></h2>
				<ul>
					<?php if ( has_nav_menu( 'footer' ) ) : ?>
						<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1, 'items_wrap' => '%3$s', 'fallback_cb' => false ) ); ?>
					<?php else : ?>
						<?php echo $bn_page( 'about-us' ); // phpcs:ignore ?>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'bn_info' ) ); ?>"><?php esc_html_e( 'Services', 'brightnest' ); ?></a></li>
						<?php echo $bn_page( 'contact' ); // phpcs:ignore ?>
						<?php if ( get_option( 'page_for_posts' ) ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Blog', 'brightnest' ); ?></a></li>
						<?php endif; ?>
						<?php echo $bn_page( 'terms' ) . $bn_page( 'privacy-policy-bn' ); // phpcs:ignore ?>
					<?php endif; ?>
				</ul>
			</nav>

			</div>

			<div class="bn-ft-col bn-ft-contact">
				<h2><?php esc_html_e( 'Get in touch', 'brightnest' ); ?></h2>
				<ul>
					<?php foreach ( array_filter( array( brightnest_opt( 'phone' ), brightnest_opt( 'phone2' ) ) ) as $bn_ph ) : ?>
						<li><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $bn_ph ) ); ?>" dir="ltr"><?php echo brightnest_icon( 'phone', 16 ); // phpcs:ignore ?> <?php echo esc_html( $bn_ph ); ?></a></li>
					<?php endforeach; ?>
					<?php if ( $bn_wa ) : ?>
						<li><a href="<?php echo esc_url( $bn_wa ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Chat on WhatsApp', 'brightnest' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $bn_email ) : ?>
						<li><a href="<?php echo esc_url( 'mailto:' . $bn_email ); ?>"><?php echo brightnest_icon( 'mail', 16 ); // phpcs:ignore ?> <?php echo esc_html( $bn_email ); ?></a></li>
					<?php endif; ?>
					<?php if ( $bn_addr ) : ?>
						<li><span><?php echo brightnest_icon( 'pin', 16 ); // phpcs:ignore ?> <?php echo esc_html( $bn_addr ); ?></span></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

		<div class="bn-ft-cities">
			<span><?php echo brightnest_icon( 'pin', 14 ); // phpcs:ignore ?> <?php echo esc_html( sprintf( /* translators: %s country */ __( 'Serving %s', 'brightnest' ), brightnest_opt( 'country' ) ? brightnest_opt( 'country' ) : __( 'United Arab Emirates', 'brightnest' ) ) ); ?>:</span>
			<?php foreach ( brightnest_cities() as $bn_city ) : ?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php echo esc_html( brightnest_city_label( $bn_city ) ); ?></a>
			<?php endforeach; ?>
		</div>

		<div class="bn-ft-bottom">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php esc_html_e( 'Bright Nest Cleaning Co.', 'brightnest' ); ?> <?php esc_html_e( 'All rights reserved.', 'brightnest' ); ?></p>
			<p class="bn-ft-credit"><?php esc_html_e( 'Designed & developed by', 'brightnest' ); ?> <a href="https://360digitalism.com/" target="_blank" rel="noopener" dir="ltr">360 Digitalism</a></p>
			<p class="bn-ft-pay"><?php echo brightnest_icon( 'shield', 15 ); // phpcs:ignore ?> <?php esc_html_e( 'Secure online payment', 'brightnest' ); ?></p>
		</div>
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
