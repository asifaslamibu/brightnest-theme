<?php
/**
 * Blog post card (blog page, categories, related posts).
 * Args: id (post ID, default the current post), big (true for the featured first post).
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

$bn_pc_id  = isset( $args['id'] ) ? (int) $args['id'] : get_the_ID();
$bn_pc_big = ! empty( $args['big'] );
$bn_pc_cat = get_the_category( $bn_pc_id );
?>
<article class="bn-pc<?php echo $bn_pc_big ? ' bn-pc-big' : ''; ?>">
	<a class="bn-pc-img" href="<?php echo esc_url( get_permalink( $bn_pc_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail( $bn_pc_id ) ) : ?>
			<?php echo get_the_post_thumbnail( $bn_pc_id, $bn_pc_big ? 'large' : 'medium_large', array( 'loading' => $bn_pc_big ? 'eager' : 'lazy' ) ); ?>
		<?php else : ?>
			<span class="bn-pc-noimg"><?php echo brightnest_icon( 'deep-clean', 40 ); // phpcs:ignore ?></span>
		<?php endif; ?>
	</a>
	<div class="bn-pc-body">
		<?php if ( $bn_pc_cat ) : ?>
			<a class="bn-pc-cat" href="<?php echo esc_url( get_category_link( $bn_pc_cat[0] ) ); ?>"><?php echo esc_html( $bn_pc_cat[0]->name ); ?></a>
		<?php endif; ?>
		<h3 class="bn-pc-title"><a href="<?php echo esc_url( get_permalink( $bn_pc_id ) ); ?>"><?php echo esc_html( get_the_title( $bn_pc_id ) ); ?></a></h3>
		<p class="bn-pc-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $bn_pc_id ), $bn_pc_big ? 34 : 20 ) ); ?></p>
		<p class="bn-pc-meta"><?php echo esc_html( get_the_date( '', $bn_pc_id ) ); ?> · <?php echo esc_html( brightnest_reading_time( $bn_pc_id ) ); ?></p>
	</div>
</article>
