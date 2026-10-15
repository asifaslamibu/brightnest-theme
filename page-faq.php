<?php
/**
 * FAQ page (slug: faq).
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="bn-page-hero">
		<div class="bn-container">
			<?php brightnest_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>
			<p class="bn-lead"><?php esc_html_e( 'Everything you need to know about booking with us.', 'brightnest' ); ?></p>
		</div>
	</section>
	<section class="bn-section">
		<div class="bn-container bn-narrow">
			<?php if ( get_the_content() ) : ?>
				<div class="bn-prose"><?php the_content(); ?></div>
			<?php endif; ?>
			<?php get_template_part( 'template-parts/faq' ); ?>
		</div>
	</section>
	<?php
endwhile;

get_template_part( 'template-parts/cta' );

get_footer();
