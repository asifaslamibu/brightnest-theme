<?php
/**
 * Default page. About Us and legal pages get their own layouts (inc/page-layouts.php).
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bn_kind = brightnest_page_kind();
	?>
	<?php $bn_hero_img = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'full' ) : ''; // The page's featured image fills the hero. ?>
	<section class="bn-page-hero<?php echo $bn_hero_img ? ' bn-page-hero--img' : ''; ?><?php echo 'legal' === $bn_kind ? ' bn-page-hero--legal' : ''; ?>"<?php echo $bn_hero_img ? ' style="--bn-hero-img:url(' . esc_url( $bn_hero_img ) . ')"' : ''; ?>>
		<div class="bn-container">
			<?php brightnest_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="bn-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php elseif ( 'legal' === $bn_kind ) : ?>
				<p class="bn-lead"><?php esc_html_e( 'Please read this page carefully. It explains how we work with you when you use our website and services.', 'brightnest' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php if ( 'about' === $bn_kind ) : ?>
		<?php get_template_part( 'template-parts/page-about' ); ?>
	<?php elseif ( 'legal' === $bn_kind ) : ?>
		<?php get_template_part( 'template-parts/page-legal' ); ?>
	<?php else : ?>
		<section class="bn-section">
			<div class="bn-container bn-narrow">
				<div class="bn-prose"><?php the_content(); ?></div>
				<?php wp_link_pages(); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
