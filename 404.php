<?php
/**
 * 404.
 *
 * @package BrightNest
 */

get_header();
?>
<section class="bn-section bn-404">
	<div class="bn-container bn-narrow">
		<span class="bn-404-code">404</span>
		<h1><?php esc_html_e( 'Page not found', 'brightnest' ); ?></h1>
		<p class="bn-lead"><?php esc_html_e( "The page you're looking for doesn't exist or has moved.", 'brightnest' ); ?></p>
		<div class="bn-404-actions">
			<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go home', 'brightnest' ); ?></a>
			<a class="bn-btn bn-btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Browse services', 'brightnest' ); ?></a>
		</div>
	</div>
</section>
<?php
get_footer();
