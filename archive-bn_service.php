<?php
/**
 * Services archive & service category pages.
 *
 * @package BrightNest
 */

get_header();

$bn_current = is_tax( 'bn_service_cat' ) ? get_queried_object() : null;
$bn_cats    = brightnest_categories();
?>

<section class="bn-page-hero">
	<div class="bn-container">
		<?php brightnest_breadcrumbs(); ?>
		<h1><?php echo $bn_current ? esc_html( $bn_current->name ) : esc_html__( 'All home services', 'brightnest' ); ?></h1>
		<p class="bn-lead">
			<?php
			if ( $bn_current && $bn_current->description ) {
				echo esc_html( $bn_current->description );
			} else {
				esc_html_e( 'Browse our services, check starting prices and book a vetted professional in minutes.', 'brightnest' );
			}
			?>
		</p>
	</div>
</section>

<section class="bn-section bn-section-tight">
	<div class="bn-container">
		<nav class="bn-filter" aria-label="<?php esc_attr_e( 'Service categories', 'brightnest' ); ?>">
			<a class="bn-chip <?php echo $bn_current ? '' : 'is-active'; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'All', 'brightnest' ); ?></a>
			<?php foreach ( $bn_cats as $bn_term ) : ?>
				<a class="bn-chip <?php echo ( $bn_current && $bn_current->term_id === $bn_term->term_id ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $bn_term ) ); ?>"><?php echo esc_html( $bn_term->name ); ?></a>
			<?php endforeach; ?>
		</nav>

		<?php if ( have_posts() ) : ?>
			<div class="bn-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					brightnest_service_card();
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'prev_text' => brightnest_icon( 'arrow-left', 16 ),
					'next_text' => brightnest_icon( 'arrow-right', 16 ),
				)
			);
			?>
		<?php else : ?>
			<p class="bn-empty"><?php esc_html_e( 'No services found in this category yet.', 'brightnest' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/cta' ); ?>

<?php
get_footer();
