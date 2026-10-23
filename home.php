<?php
/**
 * Blog: the posts page (Settings → Reading), categories and tags (archive.php loads this too).
 * Hero with search → category chips → featured latest post (first page) → post grid → pagination → booking band.
 *
 * @package BrightNest
 */

get_header();

$bn_blog_id = (int) get_option( 'page_for_posts' );
$bn_blog    = $bn_blog_id ? get_permalink( $bn_blog_id ) : home_url( '/' );
$bn_term    = is_category() || is_tag() ? get_queried_object() : null;
$bn_paged   = max( 1, (int) get_query_var( 'paged' ) );
$bn_first   = is_home() && 1 === $bn_paged; // The newest post gets the big card on the first page.

if ( $bn_term ) {
	$bn_title = $bn_term->name;
	$bn_sub   = $bn_term->description ? $bn_term->description : sprintf( /* translators: %s category */ __( 'Tips and guides about %s from the Bright Nest team.', 'brightnest' ), $bn_term->name );
} elseif ( is_date() || is_author() ) {
	$bn_title = brightnest_blog_archive_title();
	$bn_sub   = __( 'Cleaning tips, home care guides and news from the Bright Nest team in Dubai.', 'brightnest' );
} else {
	$bn_title = $bn_blog_id ? get_the_title( $bn_blog_id ) : __( 'Blog', 'brightnest' );
	$bn_sub   = $bn_blog_id && has_excerpt( $bn_blog_id ) ? get_the_excerpt( $bn_blog_id ) : __( 'Cleaning tips, home care guides and news from the Bright Nest team in Dubai.', 'brightnest' );
}
$bn_cats = get_categories( array( 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
?>

<section class="bn-blog-hero">
	<div class="bn-container">
		<?php brightnest_breadcrumbs(); ?>
		<span class="bn-blog-kicker"><?php esc_html_e( 'Bright Nest Blog', 'brightnest' ); ?></span>
		<h1><?php echo esc_html( $bn_title ); ?></h1>
		<p class="bn-blog-sub"><?php echo esc_html( wp_strip_all_tags( $bn_sub ) ); ?></p>
		<form class="bn-blog-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo brightnest_icon( 'search', 18 ); // phpcs:ignore ?>
			<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search articles…', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Search articles', 'brightnest' ); ?>">
			<input type="hidden" name="post_type" value="post">
			<button type="submit" class="bn-btn bn-btn-primary"><?php esc_html_e( 'Search', 'brightnest' ); ?></button>
		</form>
		<?php if ( brightnest_blog_archive_url() ) : ?>
			<p class="bn-blog-archlink"><a href="<?php echo esc_url( brightnest_blog_archive_url() ); ?>"><?php echo brightnest_icon( 'calendar', 15 ); // phpcs:ignore ?> <?php esc_html_e( 'Browse all articles by month', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 14 ); // phpcs:ignore ?></a></p>
		<?php endif; ?>
	</div>
</section>

<?php if ( $bn_cats ) : ?>
	<nav class="bn-blog-cats" aria-label="<?php esc_attr_e( 'Blog categories', 'brightnest' ); ?>">
		<div class="bn-container">
			<a class="<?php echo $bn_term ? '' : 'is-active'; ?>" href="<?php echo esc_url( $bn_blog ); ?>"><?php esc_html_e( 'All', 'brightnest' ); ?></a>
			<?php foreach ( $bn_cats as $bn_c ) : ?>
				<a class="<?php echo $bn_term && $bn_term->term_id === $bn_c->term_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $bn_c ) ); ?>"><?php echo esc_html( $bn_c->name ); ?> <span><?php echo (int) $bn_c->count; ?></span></a>
			<?php endforeach; ?>
			<?php if ( brightnest_blog_archive_url() ) : ?>
				<a class="bn-blog-cats-arch" href="<?php echo esc_url( brightnest_blog_archive_url() ); ?>"><?php echo brightnest_icon( 'calendar', 15 ); // phpcs:ignore ?> <?php esc_html_e( 'Archive', 'brightnest' ); ?></a>
			<?php endif; ?>
		</div>
	</nav>
<?php endif; ?>

<section class="bn-blog-list">
	<div class="bn-container">
		<?php if ( have_posts() ) : ?>
			<?php
			if ( $bn_first ) {
				the_post();
				get_template_part( 'template-parts/post-card', null, array( 'big' => true ) );
			}
			?>
			<div class="bn-pc-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</div>
			<div class="bn-blog-pages">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => brightnest_icon( 'arrow-left', 16 ) . '<span class="screen-reader-text">' . esc_html__( 'Previous', 'brightnest' ) . '</span>',
						'next_text' => '<span class="screen-reader-text">' . esc_html__( 'Next', 'brightnest' ) . '</span>' . brightnest_icon( 'arrow-right', 16 ),
					)
				);
				?>
			</div>
		<?php else : ?>
			<div class="bn-empty"><p><?php esc_html_e( 'No articles here yet.', 'brightnest' ); ?></p></div>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/blog-cta' ); ?>

<?php
get_footer();
