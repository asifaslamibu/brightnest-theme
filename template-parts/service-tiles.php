<?php
/**
 * Grid of icon tiles linking to services (homepage "top services", "other services").
 * Args: ids (service IDs).
 *
 * @package BrightNest
 */

if ( empty( $args['ids'] ) ) {
	return;
}
?>
<ul class="bn-tiles">
	<?php foreach ( $args['ids'] as $bn_tid ) : ?>
		<li>
			<a class="bn-tile" href="<?php echo esc_url( get_permalink( $bn_tid ) ); ?>">
				<span class="bn-tile-box"><?php echo brightnest_icon( brightnest_service_icon( $bn_tid ), 46 ); // phpcs:ignore ?></span>
				<span class="bn-tile-name"><?php echo esc_html( get_the_title( $bn_tid ) ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
