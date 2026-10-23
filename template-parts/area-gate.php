<?php
/**
 * First visit: "Where would you like to receive your service?" pop-up with the area picker.
 * Opens on every page until the visitor picks an area (saved in localStorage "bn_location").
 * It can be closed (✕) to look around first: then it stays away for this browser session, and the
 * area can be picked later in the header or at the checkout. Picking opens the services page on the
 * homepage (assets/js/hub.js) and keeps the visitor on the same page everywhere else.
 * The checkout has no picker of its own, so there it opens whenever no area is saved yet.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bn-gate" data-area-gate<?php echo function_exists( 'brightnest_is_checkout' ) && brightnest_is_checkout() ? ' data-gate-must' : ''; ?> hidden role="dialog" aria-modal="true" aria-labelledby="bn-gate-title">
	<div class="bn-gate-box">
		<button type="button" class="bn-gate-close" data-gate-close aria-label="<?php esc_attr_e( 'Close', 'brightnest' ); ?>"><?php echo brightnest_icon( 'close', 20 ); // phpcs:ignore ?></button>
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
/* Area gate: open when no area is saved (and it was not closed in this session); close once one is picked. */
(function () {
	var gate = document.querySelector('[data-area-gate]');
	if (!gate) { return; }
	var saved = null;
	var skipped = false;
	try { saved = JSON.parse(localStorage.getItem('bn_location') || 'null'); } catch (e) { /* ignore */ }
	try { skipped = sessionStorage.getItem('bn_gate_closed') === '1'; } catch (e) { /* ignore */ }
	// The checkout has no area picker of its own: without an area the pop-up opens there even if it was closed before.
	if ((saved && saved.area) || (skipped && !gate.hasAttribute('data-gate-must'))) { return; }
	var close = function () {
		gate.hidden = true;
		document.body.classList.remove('bn-modal-open');
	};
	gate.hidden = false;
	document.body.classList.add('bn-modal-open');
	var input = gate.querySelector('[data-dd-search]');
	if (input && window.matchMedia('(min-width: 768px)').matches) { setTimeout(function () { input.focus(); }, 60); }
	gate.querySelector('[data-gate-close]').addEventListener('click', function () {
		try { sessionStorage.setItem('bn_gate_closed', '1'); } catch (e) { /* ignore */ }
		close();
	});
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !gate.hidden) { gate.querySelector('[data-gate-close]').click(); } });
	document.addEventListener('bn:location', close);
})();
</script>
