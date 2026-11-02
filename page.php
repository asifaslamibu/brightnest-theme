<?php
/**
 * Default page.
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<?php $bn_hero_img = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'full' ) : ''; // The page's featured image fills the hero. ?>
	<section class="bn-page-hero<?php echo $bn_hero_img ? ' bn-page-hero--img' : ''; ?>"<?php echo $bn_hero_img ? ' style="--bn-hero-img:url(' . esc_url( $bn_hero_img ) . ')"' : ''; ?>>
		<div class="bn-container">
			<?php brightnest_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="bn-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<section class="bn-section">
		<div class="bn-container bn-narrow">
			<div class="bn-prose"><?php the_content(); ?></div>
			<?php wp_link_pages(); ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
