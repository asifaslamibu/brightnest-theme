<?php
/**
 * Premium service card with photo (or icon) — homepage, archives, related services.
 * Args: id.
 *
 * @package BrightNest
 */

$bn_id    = isset( $args['id'] ) ? (int) $args['id'] : get_the_ID();
$bn_price = get_post_meta( $bn_id, '_bn_price', true );
$bn_terms = get_the_terms( $bn_id, 'bn_service_cat' );
$bn_cat   = ( $bn_terms && ! is_wp_error( $bn_terms ) ) ? $bn_terms[0] : null;
?>
<article class="bn-sc" data-cat="<?php echo esc_attr( $bn_cat ? $bn_cat->slug : '' ); ?>">
	<a class="bn-sc-media" href="<?php echo esc_url( get_permalink( $bn_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail( $bn_id ) ) : ?>
			<?php echo get_the_post_thumbnail( $bn_id, 'brightnest-card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
		<?php else : ?>
			<span class="bn-sc-icon"><?php echo brightnest_icon( brightnest_service_icon( $bn_id ), 48 ); // phpcs:ignore ?></span>
		<?php endif; ?>
		<?php if ( $bn_cat ) : ?><span class="bn-sc-cat"><?php echo esc_html( $bn_cat->name ); ?></span><?php endif; ?>
		<span class="bn-sc-go"><?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></span>
	</a>
	<div class="bn-sc-body">
		<h3><a href="<?php echo esc_url( get_permalink( $bn_id ) ); ?>"><?php echo esc_html( get_the_title( $bn_id ) ); ?></a></h3>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $bn_id ), 18 ) ); ?></p>
		<div class="bn-sc-foot">
			<span><?php if ( '' !== $bn_price ) : ?><small><?php esc_html_e( 'From', 'brightnest' ); ?></small> <?php echo esc_html( brightnest_opt( 'currency' ) . ' ' . number_format_i18n( (float) $bn_price ) ); ?><?php endif; ?></span>
			<a class="bn-btn bn-btn-primary bn-btn-sm" href="<?php echo esc_url( brightnest_checkout_url( $bn_id ) ); ?>"><?php esc_html_e( 'Book now', 'brightnest' ); ?></a>
		</div>
	</div>
</article>
