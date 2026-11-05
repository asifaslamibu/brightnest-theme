<?php
/**
 * "Where would you like to receive your service?" pop-up with the area picker.
 * Opens on every page until the visitor picks an area (saved in localStorage "bn_location"); it has
 * no close button, so an area is always chosen before prices and bookings. Picking opens the
 * services page on the homepage (assets/js/hub.js) and keeps the visitor on the same page elsewhere.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bn-gate" data-area-gate hidden role="dialog" aria-modal="true" aria-labelledby="bn-gate-title">
	<div class="bn-gate-box">
		<div class="bn-gate-head">
			<span class="bn-gate-icon" aria-hidden="true"><?php echo brightnest_icon( 'pin', 22 ); // phpcs:ignore ?></span>
			<div>
				<h2 id="bn-gate-title"><?php echo esc_html( brightnest_opt( 'loc_question' ) ? brightnest_opt( 'loc_question' ) : __( 'Where would you like to receive your service?', 'brightnest' ) ); ?></h2>
				<p><?php esc_html_e( 'Select your area to see the services and prices there.', 'brightnest' ); ?></p>
			</div>
		</div>
		<?php get_template_part( 'template-parts/area-dropdown', null, array( 'class' => 'bn-gate-dd', 'source' => 'gate' ) ); ?>
	</div>
</div>
<script>
/* Area gate: open when no area is saved; it closes only once one is picked. */
(function () {
	var gate = document.querySelector('[data-area-gate]');
	if (!gate) { return; }
	var saved = null;
	try { saved = JSON.parse(localStorage.getItem('bn_location') || 'null'); } catch (e) { /* ignore */ }
	if (saved && saved.area) { return; }
	gate.hidden = false;
	document.body.classList.add('bn-modal-open');
	var input = gate.querySelector('[data-dd-search]');
	if (input && window.matchMedia('(min-width: 768px)').matches) { setTimeout(function () { input.focus(); }, 60); }
	document.addEventListener('bn:location', function () {
		gate.hidden = true;
		document.body.classList.remove('bn-modal-open');
	});
})();
</script>
