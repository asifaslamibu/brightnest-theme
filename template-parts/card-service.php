<?php
/**
 * Service card.
 *
 * @package BrightNest
 */

$bn_id    = isset( $args['id'] ) ? (int) $args['id'] : get_the_ID();
$bn_icon  = brightnest_service_icon( $bn_id );
$bn_terms = get_the_terms( $bn_id, 'bn_service_cat' );
$bn_cat   = ( $bn_terms && ! is_wp_error( $bn_terms ) ) ? $bn_terms[0] : null;
?>
<article class="bn-card">
	<a class="bn-card-media bn-tone-<?php echo esc_attr( brightnest_tone( $bn_cat ? $bn_cat->slug : $bn_id ) ); ?>" href="<?php echo esc_url( get_permalink( $bn_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail( $bn_id ) ) : ?>
			<?php echo get_the_post_thumbnail( $bn_id, 'brightnest-card', array( 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<span class="bn-card-icon"><?php echo brightnest_icon( $bn_icon, 44 ); // phpcs:ignore ?></span>
		<?php endif; ?>
		<?php if ( get_post_meta( $bn_id, '_bn_featured', true ) ) : ?>
			<span class="bn-badge"><?php esc_html_e( 'Popular', 'brightnest' ); ?></span>
		<?php endif; ?>
	</a>
	<div class="bn-card-body">
		<?php if ( $bn_cat ) : ?>
			<span class="bn-card-cat"><?php echo esc_html( $bn_cat->name ); ?></span>
		<?php endif; ?>
		<h3 class="bn-card-title"><a href="<?php echo esc_url( get_permalink( $bn_id ) ); ?>"><?php echo esc_html( get_the_title( $bn_id ) ); ?></a></h3>
		<div class="bn-card-meta">
			<?php echo brightnest_stars( get_post_meta( $bn_id, '_bn_rating', true ), get_post_meta( $bn_id, '_bn_review_count', true ) ); // phpcs:ignore ?>
			<?php $bn_dur = get_post_meta( $bn_id, '_bn_duration', true ); ?>
			<?php if ( $bn_dur ) : ?>
				<span class="bn-muted"><?php echo brightnest_icon( 'clock', 14 ); // phpcs:ignore ?> <?php echo esc_html( $bn_dur ); ?></span>
			<?php endif; ?>
		</div>
		<div class="bn-card-foot">
			<?php echo brightnest_price( $bn_id ); // phpcs:ignore ?>
			<a class="bn-card-link" href="<?php echo esc_url( brightnest_checkout_url( $bn_id ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s service */ __( 'Book %s', 'brightnest' ), get_the_title( $bn_id ) ) ); ?>"><?php esc_html_e( 'Book', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></a>
		</div>
	</div>
</article>
