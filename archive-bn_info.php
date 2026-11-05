<?php
/**
 * Service pages hub (/services/): the content pages (BrightNest → Service Pages) in sections by
 * category — Cleaning, Maintenance, then any others — each a grid of cards with photo, short text and
 * a link to the page; chips at the top jump to a section; then the booking band.
 * A page's category is its "Homepage slider" choice in its editor (by default from its name).
 *
 * @package BrightNest
 */

get_header();

$bn_pages = get_posts( array( 'post_type' => 'bn_info', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
$bn_secs  = array(
	'clean' => array( __( 'Cleaning Services', 'brightnest' ), __( 'Professional cleaning for homes, offices and businesses.', 'brightnest' ), 'cleaning' ),
	'maint' => array( __( 'Maintenance Services', 'brightnest' ), __( 'Repairs and upkeep to keep your property in top condition.', 'brightnest' ), 'maintenance' ),
	'none'  => array( __( 'More Services', 'brightnest' ), '', 'home' ),
);
$bn_by    = array();
foreach ( $bn_pages as $bn_id ) {
	$bn_by[ brightnest_home_group( $bn_id ) ][] = $bn_id;
}
$bn_secs = array_intersect_key( $bn_secs, $bn_by );
?>

<section class="bn-blog-hero bn-ours-hero">
	<div class="bn-container">
		<?php brightnest_breadcrumbs(); ?>
		<span class="bn-blog-kicker"><?php esc_html_e( 'Bright Nest Cleaning Co.', 'brightnest' ); ?></span>
		<h1><?php esc_html_e( 'Our Services', 'brightnest' ); ?></h1>
		<p class="bn-blog-sub"><?php esc_html_e( 'Cleaning, maintenance and property care across Dubai and the UAE. Pick a service to learn more, or book online in a minute.', 'brightnest' ); ?></p>
		<p class="bn-ours-btns"><a class="bn-btn bn-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a></p>
	</div>
</section>

<section class="bn-blog-list">
	<div class="bn-container">
		<?php if ( $bn_pages ) : ?>
			<?php if ( count( $bn_secs ) > 1 ) : ?>
				<nav class="bn-ours-jump" aria-label="<?php esc_attr_e( 'Categories', 'brightnest' ); ?>">
					<?php foreach ( $bn_secs as $bn_k => $bn_sec ) : ?>
						<a href="#<?php echo esc_attr( $bn_sec[2] ); ?>"><?php echo brightnest_icon( 'maintenance' === $bn_sec[2] ? 'maintenance' : ( 'cleaning' === $bn_sec[2] ? 'cleaning' : 'home' ), 16 ); // phpcs:ignore ?> <?php echo esc_html( $bn_sec[0] ); ?> <span><?php echo (int) count( $bn_by[ $bn_k ] ); ?></span></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
			<?php foreach ( $bn_secs as $bn_k => $bn_sec ) : ?>
				<section class="bn-ours-sec" id="<?php echo esc_attr( $bn_sec[2] ); ?>">
					<div class="bn-ours-sec-head">
						<span class="bn-ours-sec-ic"><?php echo brightnest_icon( 'maintenance' === $bn_sec[2] ? 'maintenance' : ( 'cleaning' === $bn_sec[2] ? 'cleaning' : 'home' ), 22 ); // phpcs:ignore ?></span>
						<div>
							<h2><?php echo esc_html( $bn_sec[0] ); ?> <small><?php echo esc_html( sprintf( /* translators: %d number of services */ _n( '%d service', '%d services', count( $bn_by[ $bn_k ] ), 'brightnest' ), count( $bn_by[ $bn_k ] ) ) ); ?></small></h2>
							<?php if ( $bn_sec[1] ) : ?><p><?php echo esc_html( $bn_sec[1] ); ?></p><?php endif; ?>
						</div>
					</div>
					<div class="bn-pc-grid">
						<?php foreach ( $bn_by[ $bn_k ] as $bn_id ) : ?>
							<?php $bn_txt = has_excerpt( $bn_id ) ? get_the_excerpt( $bn_id ) : wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $bn_id ) ), 26, '…' ); ?>
							<article class="bn-pc">
								<a class="bn-pc-img" href="<?php echo esc_url( get_permalink( $bn_id ) ); ?>" tabindex="-1" aria-hidden="true">
									<?php if ( has_post_thumbnail( $bn_id ) ) : ?>
										<?php echo get_the_post_thumbnail( $bn_id, 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
									<?php else : ?>
										<span class="bn-pc-noimg"><?php echo brightnest_icon( 'maintenance', 40 ); // phpcs:ignore ?></span>
									<?php endif; ?>
								</a>
								<div class="bn-pc-body">
									<h3 class="bn-pc-title"><a href="<?php echo esc_url( get_permalink( $bn_id ) ); ?>"><?php echo esc_html( get_the_title( $bn_id ) ); ?></a></h3>
									<p class="bn-pc-excerpt"><?php echo esc_html( $bn_txt ); ?></p>
									<a class="bn-ours-more" href="<?php echo esc_url( get_permalink( $bn_id ) ); ?>"><?php esc_html_e( 'Learn more', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></a>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="bn-empty"><p><?php esc_html_e( 'No services here yet.', 'brightnest' ); ?></p></div>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/blog-cta' ); ?>

<?php
get_footer();
