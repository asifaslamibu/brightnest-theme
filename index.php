<?php
/**
 * Fallback template: blog index, archives, search.
 *
 * @package BrightNest
 */

get_header();

$bn_service_search = is_search() && 'bn_service' === get_query_var( 'post_type' );
?>

<section class="bn-page-hero">
	<div class="bn-container">
		<?php brightnest_breadcrumbs(); ?>
		<h1>
			<?php
			if ( is_search() ) {
				/* translators: %s search term */
				printf( esc_html__( 'Results for "%s"', 'brightnest' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
			} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
				echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) );
			} else {
				esc_html_e( 'Blog', 'brightnest' );
			}
			?>
		</h1>
	</div>
</section>

<section class="bn-section">
	<div class="bn-container">
		<?php if ( have_posts() ) : ?>
			<div class="bn-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( 'bn_service' === get_post_type() ) {
						brightnest_service_card();
						continue;
					}
					?>
					<article <?php post_class( 'bn-card bn-post-card' ); ?>>
						<a class="bn-card-media bn-tone-<?php echo esc_attr( brightnest_tone( get_the_ID() ) ); ?>" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'brightnest-card', array( 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<span class="bn-card-icon"><?php echo brightnest_icon( 'deep-clean', 44 ); // phpcs:ignore ?></span>
							<?php endif; ?>
						</a>
						<div class="bn-card-body">
							<span class="bn-card-cat"><?php echo esc_html( get_the_date() ); ?></span>
							<h2 class="bn-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="bn-muted"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<a class="bn-card-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></a>
						</div>
					</article>
				<?php endwhile; ?>
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
			<div class="bn-empty">
				<p><?php echo $bn_service_search ? esc_html__( "We couldn't find that service. Try another keyword or browse all services.", 'brightnest' ) : esc_html__( 'Nothing found.', 'brightnest' ); ?></p>
				<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Browse all services', 'brightnest' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
