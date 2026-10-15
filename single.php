<?php
/**
 * Single blog post.
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="bn-page-hero">
		<div class="bn-container bn-narrow">
			<?php brightnest_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>
			<p class="bn-muted"><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></p>
		</div>
	</section>
	<section class="bn-section">
		<div class="bn-container bn-narrow">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="bn-featured-img"><?php the_post_thumbnail( 'brightnest-hero' ); ?></div>
			<?php endif; ?>
			<div class="bn-prose"><?php the_content(); ?></div>
			<?php wp_link_pages(); ?>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</section>
	<?php
endwhile;

get_template_part( 'template-parts/cta' );

get_footer();
