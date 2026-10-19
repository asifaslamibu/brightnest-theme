<?php
/**
 * Header.
 *
 * @package BrightNest
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="bn-skip" href="#bn-main"><?php esc_html_e( 'Skip to content', 'brightnest' ); ?></a>

<div class="bn-topbar">
	<div class="bn-container bn-topbar-inner">
		<div class="bn-topbar-left">
			<?php foreach ( array_filter( array( brightnest_opt( 'phone' ), brightnest_opt( 'phone2' ) ) ) as $bn_ph ) : ?>
				<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $bn_ph ) ); ?>"><?php echo brightnest_icon( 'phone', 14 ); // phpcs:ignore ?> <?php echo esc_html( $bn_ph ); ?></a>
			<?php endforeach; ?>
			<?php if ( brightnest_opt( 'email' ) ) : ?>
				<a class="bn-hide-sm" href="mailto:<?php echo esc_attr( brightnest_opt( 'email' ) ); ?>"><?php echo brightnest_icon( 'mail', 14 ); // phpcs:ignore ?> <?php echo esc_html( brightnest_opt( 'email' ) ); ?></a>
			<?php endif; ?>
		</div>
		<div class="bn-topbar-social bn-hide-sm">
			<?php if ( brightnest_whatsapp_url() ) : ?>
				<a href="<?php echo esc_url( brightnest_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?php echo brightnest_icon( 'whatsapp', 15 ); // phpcs:ignore ?></a>
			<?php endif; ?>
			<?php echo brightnest_social_links(); // phpcs:ignore ?>
		</div>
	</div>
</div>

<header class="bn-header" id="bn-header">
	<div class="bn-container bn-header-inner">
		<div class="bn-logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="bn-logo-img">
					<img src="<?php echo esc_url( BRIGHTNEST_URI . '/assets/img/logo.png' ); ?>" width="720" height="200" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>
			<?php endif; ?>
		</div>

		<nav class="bn-nav" id="bn-nav" aria-label="<?php esc_attr_e( 'Primary', 'brightnest' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'bn-menu',
					'depth'          => 2,
					'fallback_cb'    => 'brightnest_menu_fallback',
				)
			);
			?>
			<a class="bn-btn bn-btn-primary bn-nav-cta-mobile" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
		</nav>

		<div class="bn-header-actions">
			<?php if ( brightnest_opt( 'phone' ) ) : ?>
				<a class="bn-header-phone bn-hide-sm" href="<?php echo esc_url( brightnest_tel_url() ); ?>"><?php echo brightnest_icon( 'phone', 18 ); // phpcs:ignore ?> <span><?php echo esc_html( brightnest_opt( 'phone' ) ); ?></span></a>
			<?php endif; ?>
			<a class="bn-btn bn-btn-primary bn-hide-sm" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
			<button class="bn-nav-toggle" type="button" aria-controls="bn-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'brightnest' ); ?>">
				<span class="bn-open"><?php echo brightnest_icon( 'menu', 24 ); // phpcs:ignore ?></span>
				<span class="bn-close"><?php echo brightnest_icon( 'close', 24 ); // phpcs:ignore ?></span>
			</button>
		</div>
	</div>
</header>

<main id="bn-main" class="bn-main">
