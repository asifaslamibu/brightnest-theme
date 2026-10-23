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

<?php
// Example in the search box: the first popular service, like Justlife's "Home Cleaning".
$bn_example = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => 1, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC' ) );
$bn_example = $bn_example ? wp_strip_all_tags( html_entity_decode( get_the_title( $bn_example[0] ) ) ) : __( 'Home Cleaning', 'brightnest' );
?>
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

		<?php
		// Inner pages: the city/area dropdown sits in the header (it sets the prices on checkout).
		// Not on the homepage (the hero has it) or the checkout (it has its own above the options).
		$bn_hdr_dd = ! is_front_page() && ! ( function_exists( 'brightnest_is_checkout' ) && brightnest_is_checkout() );
		if ( $bn_hdr_dd ) {
			get_template_part( 'template-parts/area-dropdown', null, array( 'class' => 'bn-hdr-dd', 'placeholder' => __( 'Select your area', 'brightnest' ) ) );
		}
		?>

		<form class="bn-hsearch" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo brightnest_icon( 'search', 18 ); // phpcs:ignore ?>
			<input type="search" name="s" value="<?php echo esc_attr( is_search() ? get_search_query() : '' ); ?>" data-hub-search placeholder="<?php echo esc_attr( sprintf( /* translators: %s service name */ __( 'Search for "%s"', 'brightnest' ), $bn_example ) ); ?>" aria-label="<?php esc_attr_e( 'Search services', 'brightnest' ); ?>">
			<input type="hidden" name="post_type" value="bn_service">
		</form>

		<div class="bn-header-actions">
			<?php // Language switch like Justlife: "العربية" on the English site, "English" on the Arabic one. ?>
			<?php if ( brightnest_gt_on() ) : ?>
				<?php brightnest_gt_switch(); // GTranslate: English / العربية. ?>
			<?php else : ?>
				<a class="bn-hlang" href="<?php echo esc_url( brightnest_switch_url() ); ?>" hreflang="<?php echo brightnest_is_ar() ? 'en' : 'ar'; ?>" lang="<?php echo brightnest_is_ar() ? 'en' : 'ar'; ?>"><?php echo brightnest_is_ar() ? 'English' : 'العربية'; ?></a>
			<?php endif; ?>
			<?php if ( brightnest_opt( 'phone' ) ) : ?>
				<a class="bn-hround" href="<?php echo esc_url( brightnest_tel_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s phone */ __( 'Call %s', 'brightnest' ), brightnest_opt( 'phone' ) ) ); ?>"><?php echo brightnest_icon( 'phone', 18 ); // phpcs:ignore ?></a>
			<?php endif; ?>
			<button class="bn-nav-toggle" type="button" aria-controls="bn-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'brightnest' ); ?>">
				<span class="bn-nav-toggle-user"><?php echo brightnest_icon( 'users', 20 ); // phpcs:ignore ?></span>
				<span class="bn-open"><?php echo brightnest_icon( 'menu', 22 ); // phpcs:ignore ?></span>
				<span class="bn-close"><?php echo brightnest_icon( 'close', 22 ); // phpcs:ignore ?></span>
			</button>
			<?php // Menu: a small dropdown under the menu button (like Justlife), on every screen size. ?>
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
				<a class="bn-btn bn-btn-primary bn-nav-cta" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
				<div class="bn-nav-contact">
					<?php foreach ( array_filter( array( brightnest_opt( 'phone' ), brightnest_opt( 'phone2' ) ) ) as $bn_ph ) : ?>
						<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $bn_ph ) ); ?>"><?php echo brightnest_icon( 'phone', 16 ); // phpcs:ignore ?> <?php echo esc_html( $bn_ph ); ?></a>
					<?php endforeach; ?>
					<?php if ( brightnest_whatsapp_url() ) : ?>
						<a href="<?php echo esc_url( brightnest_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 16 ); // phpcs:ignore ?> WhatsApp</a>
					<?php endif; ?>
					<?php if ( brightnest_opt( 'email' ) ) : ?>
						<a href="mailto:<?php echo esc_attr( brightnest_opt( 'email' ) ); ?>"><?php echo brightnest_icon( 'mail', 16 ); // phpcs:ignore ?> <?php echo esc_html( brightnest_opt( 'email' ) ); ?></a>
					<?php endif; ?>
				</div>
				<?php $bn_social = brightnest_social_links(); ?>
				<?php if ( $bn_social ) : ?><div class="bn-social bn-nav-social"><?php echo $bn_social; // phpcs:ignore ?></div><?php endif; ?>
			</nav>
		</div>
	</div>

</header>

<main id="bn-main" class="bn-main">
