<?php
/**
 * Offer pop-up (BrightNest → Settings → Social & numbers → Offer pop-up).
 * Opens once the visitor has chosen their area. On the homepage the services page opens right
 * after the pick, so the pop-up waits for that page (sessionStorage "bn_offer_due").
 * Closed or claimed: it stays away for "Show again after (days)" (localStorage "bn_offer_seen"),
 * or for this visit when that is 0. "Claim" keeps the voucher code for the checkout ("bn_offer_code").
 * Never shown on the checkout itself.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

if ( '1' !== (string) brightnest_opt( 'offer_on' ) || brightnest_is_checkout() ) {
	return;
}
$bn_of_img   = brightnest_opt( 'offer_image' );
$bn_of_image = 'image' === brightnest_opt( 'offer_style' ) && $bn_of_img;
$bn_of_link  = brightnest_opt( 'offer_link' ) ? brightnest_opt( 'offer_link' ) : get_post_type_archive_link( 'bn_service' );
$bn_of_code  = strtoupper( trim( (string) brightnest_opt( 'offer_code' ) ) );
$bn_of_code  = $bn_of_code && isset( brightnest_vouchers()[ $bn_of_code ] ) ? $bn_of_code : ''; // Only a code that works.
$bn_of_title = brightnest_opt( 'offer_title' );
?>
<div class="bn-offer" data-offer hidden role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $bn_of_title ? $bn_of_title : __( 'Special offer', 'brightnest' ) ); ?>"
	data-days="<?php echo esc_attr( (int) brightnest_opt( 'offer_days' ) ); ?>" data-code="<?php echo esc_attr( $bn_of_code ); ?>">
	<div class="bn-offer-box<?php echo $bn_of_image ? ' is-image' : ''; ?>">
		<button type="button" class="bn-offer-close" data-offer-close aria-label="<?php esc_attr_e( 'Close', 'brightnest' ); ?>"><?php echo brightnest_icon( 'close', 18 ); // phpcs:ignore ?></button>
		<?php if ( $bn_of_image ) : ?>
			<a class="bn-offer-pic" href="<?php echo esc_url( $bn_of_link ); ?>" data-offer-claim><img src="<?php echo esc_url( $bn_of_img ); ?>" alt="<?php echo esc_attr( $bn_of_title ); ?>"></a>
		<?php else : ?>
			<?php if ( $bn_of_img ) : ?>
				<span class="bn-offer-top"><img src="<?php echo esc_url( $bn_of_img ); ?>" alt=""></span>
			<?php endif; ?>
			<div class="bn-offer-body">
				<?php if ( brightnest_opt( 'offer_badge' ) ) : ?>
					<span class="bn-offer-badge"><strong><?php echo esc_html( brightnest_opt( 'offer_badge' ) ); ?></strong><?php if ( brightnest_opt( 'offer_badge_sub' ) ) : ?><small><?php echo esc_html( brightnest_opt( 'offer_badge_sub' ) ); ?></small><?php endif; ?></span>
				<?php endif; ?>
				<?php if ( $bn_of_title ) : ?><h2><?php echo esc_html( $bn_of_title ); ?></h2><?php endif; ?>
				<?php if ( brightnest_opt( 'offer_text' ) ) : ?><p><?php echo esc_html( brightnest_opt( 'offer_text' ) ); ?></p><?php endif; ?>
				<?php if ( $bn_of_code ) : ?>
					<p class="bn-offer-code"><?php esc_html_e( 'Use code', 'brightnest' ); ?> <strong><?php echo esc_html( $bn_of_code ); ?></strong> <span><?php esc_html_e( '— we fill it in at the checkout.', 'brightnest' ); ?></span></p>
				<?php endif; ?>
				<a class="bn-btn bn-btn-primary bn-btn-lg bn-btn-block" href="<?php echo esc_url( $bn_of_link ); ?>" data-offer-claim><?php echo esc_html( brightnest_opt( 'offer_btn' ) ? brightnest_opt( 'offer_btn' ) : __( 'Claim your discount', 'brightnest' ) ); ?></a>
				<button type="button" class="bn-offer-later" data-offer-close><?php esc_html_e( 'No thanks', 'brightnest' ); ?></button>
			</div>
		<?php endif; ?>
	</div>
</div>
<script>
(function () {
	var box = document.querySelector('[data-offer]');
	if (!box) { return; }
	var days = +box.getAttribute('data-days') || 0;
	var code = box.getAttribute('data-code');
	var get = function (s, k) { try { return s.getItem(k); } catch (e) { return null; } };
	var put = function (s, k, v) { try { s.setItem(k, v); } catch (e) { /* private mode */ } };
	var del = function (s, k) { try { s.removeItem(k); } catch (e) { /* ignore */ } };
	function seenLately() {
		if (!days) { return get(sessionStorage, 'bn_offer_seen') === '1'; }
		var at = +get(localStorage, 'bn_offer_seen') || 0;
		return Date.now() - at < days * 86400000;
	}
	function done() {
		if (days) { put(localStorage, 'bn_offer_seen', String(Date.now())); } else { put(sessionStorage, 'bn_offer_seen', '1'); }
		del(sessionStorage, 'bn_offer_due');
	}
	function close() { box.hidden = true; document.body.classList.remove('bn-modal-open'); done(); }
	function open() {
		if (seenLately()) { del(sessionStorage, 'bn_offer_due'); return; }
		var gate = document.querySelector('[data-area-gate]');
		if (gate && !gate.hidden) { return; } // Never on top of the area pop-up.
		box.hidden = false;
		document.body.classList.add('bn-modal-open');
		del(sessionStorage, 'bn_offer_due');
	}
	Array.prototype.forEach.call(box.querySelectorAll('[data-offer-close]'), function (b) { b.addEventListener('click', close); });
	box.addEventListener('click', function (e) { if (e.target === box) { close(); } });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !box.hidden) { close(); } });
	Array.prototype.forEach.call(box.querySelectorAll('[data-offer-claim]'), function (a) {
		a.addEventListener('click', function () { if (code) { put(localStorage, 'bn_offer_code', code); } done(); });
	});
	// Area chosen: the homepage moves on to the services page, which opens the pop-up; other pages open it here.
	document.addEventListener('bn:location', function () {
		put(sessionStorage, 'bn_offer_due', '1');
		if (!document.body.classList.contains('home')) { setTimeout(open, 500); }
	});
	if (get(sessionStorage, 'bn_offer_due') === '1') { setTimeout(open, 700); }
})();
</script>
