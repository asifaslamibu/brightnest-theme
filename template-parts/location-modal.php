<?php
/**
 * "Where would you like to receive your service?" map picker (homepage hero).
 * The chosen spot is saved in the browser (localStorage "bn_location") and the checkout
 * pre-fills the address from it.
 *
 * @package BrightNest
 */

?>
<div class="bn-modal" data-loc-modal hidden role="dialog" aria-modal="true" aria-labelledby="bn-loc-title">
	<div class="bn-modal-box">
		<div class="bn-modal-head">
			<h2 id="bn-loc-title"><?php esc_html_e( 'Where would you like to receive your service?', 'brightnest' ); ?></h2>
			<button type="button" class="bn-modal-close" data-loc-close aria-label="<?php esc_attr_e( 'Close', 'brightnest' ); ?>"><?php echo brightnest_icon( 'close', 22 ); // phpcs:ignore ?></button>
		</div>
		<div class="bn-map-wrap">
			<form class="bn-map-search" data-loc-search>
				<?php echo brightnest_icon( 'search', 18 ); // phpcs:ignore ?>
				<input type="search" data-loc-query placeholder="<?php esc_attr_e( 'Search area, street or building', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Search location', 'brightnest' ); ?>">
				<button type="button" class="bn-map-locate" data-loc-gps aria-label="<?php esc_attr_e( 'Use my current location', 'brightnest' ); ?>"><?php echo brightnest_icon( 'pin', 16 ); // phpcs:ignore ?> <span class="bn-map-locate-text"><?php esc_html_e( 'Locate me', 'brightnest' ); ?></span></button>
			</form>
			<ul class="bn-map-results" data-loc-results hidden></ul>
			<div class="bn-map" data-loc-map></div>
			<div class="bn-map-pin" aria-hidden="true">
				<span class="bn-map-tip"><?php esc_html_e( 'Move the map to set the exact position', 'brightnest' ); ?></span>
				<svg width="40" height="48" viewBox="0 0 24 30"><path d="M12 0C5.4 0 0 5.3 0 11.9 0 20.8 12 30 12 30s12-9.2 12-18.1C24 5.3 18.6 0 12 0z" fill="#e5383b"/><circle cx="12" cy="11.5" r="4.5" fill="#fff"/></svg>
			</div>
			<button type="button" class="bn-btn bn-btn-primary bn-map-confirm" data-loc-confirm><?php esc_html_e( 'Confirm location', 'brightnest' ); ?></button>
		</div>
	</div>
</div>
