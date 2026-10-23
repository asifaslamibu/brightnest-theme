<?php
/**
 * Template Name: Blog archive
 *
 * Every published article, newest first, grouped by year and month: hero with a live filter →
 * year chips → the list (with categories and a booking card beside it) → booking band.
 * The page is made once at /blogs/archive/ (see brightnest_blog_archive_seed()).
 *
 * @package BrightNest
 */

get_header();

$bn_ids  = get_posts( array( 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
$bn_tree = array(); // year => month number => [ post IDs ].
foreach ( $bn_ids as $bn_id ) {
	$bn_tree[ get_the_date( 'Y', $bn_id ) ][ (int) get_the_date( 'n', $bn_id ) ][] = $bn_id;
}
$bn_cats  = get_categories( array( 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
$bn_blog  = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
$bn_intro = has_excerpt() ? get_the_excerpt() : sprintf(
	/* translators: %d number of articles */
	_n( 'All %d article from the Bright Nest team, newest first.', 'All %d articles from the Bright Nest team, newest first.', count( $bn_ids ), 'brightnest' ),
	count( $bn_ids )
);
?>

<section class="bn-blog-hero">
	<div class="bn-container">
		<?php brightnest_breadcrumbs(); ?>
		<span class="bn-blog-kicker"><?php esc_html_e( 'Bright Nest Blog', 'brightnest' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="bn-blog-sub"><?php echo esc_html( wp_strip_all_tags( $bn_intro ) ); ?></p>
		<label class="bn-blog-search">
			<?php echo brightnest_icon( 'search', 18 ); // phpcs:ignore ?>
			<input type="search" data-arch-filter placeholder="<?php esc_attr_e( 'Filter articles by title…', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Filter articles by title', 'brightnest' ); ?>">
		</label>
	</div>
</section>

<?php if ( count( $bn_tree ) > 0 ) : ?>
	<nav class="bn-blog-cats" aria-label="<?php esc_attr_e( 'Years', 'brightnest' ); ?>">
		<div class="bn-container">
			<a href="<?php echo esc_url( $bn_blog ); ?>"><?php esc_html_e( 'Latest articles', 'brightnest' ); ?></a>
			<?php foreach ( $bn_tree as $bn_y => $bn_months ) : ?>
				<a href="#y<?php echo esc_attr( $bn_y ); ?>" data-arch-year="<?php echo esc_attr( $bn_y ); ?>"><?php echo esc_html( $bn_y ); ?> <span><?php echo (int) array_sum( array_map( 'count', $bn_months ) ); ?></span></a>
			<?php endforeach; ?>
		</div>
	</nav>
<?php endif; ?>

<section class="bn-blog-list">
	<div class="bn-container bn-arch-layout">
		<div class="bn-arch-main">
			<?php if ( ! $bn_tree ) : ?>
				<div class="bn-empty"><p><?php esc_html_e( 'No articles here yet.', 'brightnest' ); ?></p></div>
			<?php endif; ?>
			<?php foreach ( $bn_tree as $bn_y => $bn_months ) : ?>
				<section class="bn-arch-year" id="y<?php echo esc_attr( $bn_y ); ?>" data-arch-group>
					<h2><?php echo esc_html( $bn_y ); ?> <small><?php echo esc_html( sprintf( /* translators: %d number of articles */ _n( '%d article', '%d articles', array_sum( array_map( 'count', $bn_months ) ), 'brightnest' ), array_sum( array_map( 'count', $bn_months ) ) ) ); ?></small></h2>
					<?php foreach ( $bn_months as $bn_m => $bn_posts ) : ?>
						<div class="bn-arch-month" data-arch-group>
							<h3>
								<a href="<?php echo esc_url( get_month_link( (int) $bn_y, $bn_m ) ); ?>"><?php echo esc_html( date_i18n( 'F', mktime( 0, 0, 0, $bn_m, 1 ) ) ); ?></a>
								<span><?php echo (int) count( $bn_posts ); ?></span>
							</h3>
							<ul class="bn-arch-list">
								<?php foreach ( $bn_posts as $bn_pid ) : ?>
									<?php $bn_pc = get_the_category( $bn_pid ); ?>
									<li data-arch-row data-title="<?php echo esc_attr( strtolower( wp_strip_all_tags( get_the_title( $bn_pid ) ) ) ); ?>">
										<a class="bn-arch-thumb" href="<?php echo esc_url( get_permalink( $bn_pid ) ); ?>" tabindex="-1" aria-hidden="true">
											<?php if ( has_post_thumbnail( $bn_pid ) ) : ?>
												<?php echo get_the_post_thumbnail( $bn_pid, 'medium', array( 'loading' => 'lazy' ) ); ?>
											<?php else : ?>
												<?php echo brightnest_icon( 'deep-clean', 22 ); // phpcs:ignore ?>
											<?php endif; ?>
										</a>
										<div class="bn-arch-text">
											<a class="bn-arch-title" href="<?php echo esc_url( get_permalink( $bn_pid ) ); ?>"><?php echo esc_html( get_the_title( $bn_pid ) ); ?></a>
											<p class="bn-arch-meta">
												<span><?php echo esc_html( get_the_date( 'j M Y', $bn_pid ) ); ?></span>
												<span><?php echo esc_html( brightnest_reading_time( $bn_pid ) ); ?></span>
												<?php if ( $bn_pc ) : ?>
													<a href="<?php echo esc_url( get_category_link( $bn_pc[0] ) ); ?>"><?php echo esc_html( $bn_pc[0]->name ); ?></a>
												<?php endif; ?>
											</p>
										</div>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</section>
			<?php endforeach; ?>
			<div class="bn-empty" data-arch-none hidden><p><?php esc_html_e( 'No article matches that title.', 'brightnest' ); ?></p></div>
		</div>

		<aside class="bn-arch-side">
			<?php if ( $bn_cats ) : ?>
				<div class="bn-arch-card">
					<strong><?php esc_html_e( 'Categories', 'brightnest' ); ?></strong>
					<ul class="bn-arch-cats">
						<?php foreach ( $bn_cats as $bn_c ) : ?>
							<li><a href="<?php echo esc_url( get_category_link( $bn_c ) ); ?>"><?php echo esc_html( $bn_c->name ); ?> <span><?php echo (int) $bn_c->count; ?></span></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<div class="bn-post-book">
				<strong><?php esc_html_e( 'Need help at home?', 'brightnest' ); ?></strong>
				<p><?php esc_html_e( 'Vetted professionals, clear prices and secure online payment.', 'brightnest' ); ?></p>
				<a class="bn-btn bn-btn-primary bn-btn-block" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
			</div>
		</aside>
	</div>
</section>

<script>
/* Archive: filter the list by title as you type (empty months and years fold away). */
(function () {
	var input = document.querySelector('[data-arch-filter]');
	if (!input) { return; }
	var rows = document.querySelectorAll('[data-arch-row]');
	var groups = Array.prototype.slice.call(document.querySelectorAll('[data-arch-group]')).reverse(); // Months before their year.
	var none = document.querySelector('[data-arch-none]');
	input.addEventListener('input', function () {
		var q = input.value.trim().toLowerCase();
		var any = false;
		rows.forEach(function (r) { var hit = !q || r.getAttribute('data-title').indexOf(q) !== -1; r.hidden = !hit; if (hit) { any = true; } });
		groups.forEach(function (g) { g.hidden = !g.querySelector('[data-arch-row]:not([hidden])'); });
		none.hidden = any;
	});
})();
</script>

<?php get_template_part( 'template-parts/blog-cta' ); ?>

<?php
get_footer();
