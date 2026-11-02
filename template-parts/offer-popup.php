<?php
/**
 * Offer pop-up (BrightNest → Settings → Offer pop-up): when it opens (after the area is chosen, on page
 * load, after scrolling, or when leaving), on which pages, on phones or not, where (middle or corner),
 * its colours, texts and voucher code.
 * "After the area is chosen" on the homepage waits for the booking page that opens next
 * (sessionStorage "bn_offer_due"). Closed or claimed: it stays away for "Show again after (days)"
 * (localStorage "bn_offer_seen"), or for this visit when that is 0. "Claim" keeps the voucher code for
 * the checkout ("bn_offer_code"). Never shown on the checkout itself.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

if ( '1' !== (string) brightnest_opt( 'offer_on' ) || brightnest_is_checkout() ) {
	return;
}
// On which pages.
$bn_of_pages = (string) brightnest_opt( 'offer_pages' );
$bn_of_book  = is_post_type_archive( 'bn_service' ) || is_tax( 'bn_service_cat' ) || is_singular( 'bn_service' );
if ( ( 'home' === $bn_of_pages && ! is_front_page() ) || ( 'booking' === $bn_of_pages && ! $bn_of_book ) || ( 'content' === $bn_of_pages && $bn_of_book ) ) {
	return;
}
$bn_of_img    = brightnest_opt( 'offer_image' );
$bn_of_image  = 'image' === brightnest_opt( 'offer_style' ) && $bn_of_img;
$bn_of_link   = brightnest_opt( 'offer_link' ) ? brightnest_opt( 'offer_link' ) : get_post_type_archive_link( 'bn_service' );
$bn_of_code   = strtoupper( trim( (string) brightnest_opt( 'offer_code' ) ) );
$bn_of_code   = $bn_of_code && isset( brightnest_vouchers()[ $bn_of_code ] ) ? $bn_of_code : ''; // Only a code that works.
$bn_of_title  = brightnest_opt( 'offer_title' );
$bn_of_later  = brightnest_label( 'offer_later', __( 'No thanks', 'brightnest' ) );
$bn_of_line   = (string) brightnest_label( 'offer_code_text', __( 'Use code {code} — we fill it in at the checkout.', 'brightnest' ) );
$bn_of_corner = 'corner' === brightnest_opt( 'offer_position' );
$bn_of_color  = function ( $key ) {
	$c = sanitize_hex_color( (string) brightnest_opt( $key ) );
	return $c ? $c : '';
};
$bn_of_style  = array_filter(
	array(
		'--bn-of-bg'     => $bn_of_color( 'offer_bg' ),
		'--bn-of-ink'    => $bn_of_color( 'offer_color' ),
		'--bn-of-accent' => $bn_of_color( 'offer_accent' ),
		'--bn-of-btn'    => $bn_of_color( 'offer_btn_text_color' ),
		'--bn-of-radius' => '' !== (string) brightnest_opt( 'offer_radius' ) ? (int) brightnest_opt( 'offer_radius' ) . 'px' : '',
	)
);
$bn_of_css    = '';
foreach ( $bn_of_style as $bn_k => $bn_v ) {
	$bn_of_css .= $bn_k . ':' . $bn_v . ';';
}
?>
<div class="bn-offer<?php echo $bn_of_corner ? ' is-corner' : ''; ?>" data-offer hidden role="dialog" aria-modal="<?php echo $bn_of_corner ? 'false' : 'true'; ?>" aria-label="<?php echo esc_attr( $bn_of_title ? $bn_of_title : __( 'Special offer', 'brightnest' ) ); ?>"
	style="<?php echo esc_attr( $bn_of_css ); ?>"
	data-days="<?php echo esc_attr( (int) brightnest_opt( 'offer_days' ) ); ?>" data-code="<?php echo esc_attr( $bn_of_code ); ?>"
	data-trigger="<?php echo esc_attr( brightnest_opt( 'offer_trigger' ) ? brightnest_opt( 'offer_trigger' ) : 'area' ); ?>"
	data-delay="<?php echo esc_attr( max( 0, (int) brightnest_opt( 'offer_delay' ) ) ); ?>"
	data-scroll="<?php echo esc_attr( max( 1, (int) brightnest_opt( 'offer_scroll' ) ) ); ?>"
	data-mobile="<?php echo '0' === (string) brightnest_opt( 'offer_mobile' ) ? '0' : '1'; ?>">
	<div class="bn-offer-box<?php echo $bn_of_image ? ' is-image' : ''; ?>">
		<button type="button" class="bn-offer-close" data-offer-close aria-label="<?php esc_attr_e( 'Close', 'brightnest' ); ?>"><?php echo brightnest_icon( 'close', 18 ); // phpcs:ignore ?></button>
		<?php if ( $bn_of_image ) : ?>
			<a class="bn-offer-pic" href="<?php echo esc_url( $bn_of_link ); ?>" data-offer-claim><img src="<?php echo esc_url( $bn_of_img ); ?>" alt="<?php echo esc_attr( $bn_of_title ); ?>"></a>
		<?php else : ?>
			<?php if ( $bn_of_img ) : ?>
				<span class="bn-offer-top"><img src="<?php echo esc_url( $bn_of_img ); ?>" alt=""></span>
			<?php endif; ?>
			<div class="bn-offer-body">
				<?php
				$bn_of_badge = trim( (string) brightnest_opt( 'offer_badge' ) );
				// The pop-up's own discount is set: the badge follows it unless a different badge was typed.
				if ( (float) brightnest_opt( 'offer_disc_value' ) > 0 && '20%' === $bn_of_badge ) {
					$bn_of_badge = '';
				}
				if ( '' === $bn_of_badge && $bn_of_code ) {
					// Made from the code's discount: "20%" or "AED 50".
					$bn_of_v     = brightnest_vouchers()[ $bn_of_code ];
					$bn_of_badge = 'percent' === $bn_of_v['type'] ? (float) $bn_of_v['value'] . '%' : brightnest_opt( 'currency' ) . ' ' . (float) $bn_of_v['value'];
				}
				?>
				<?php if ( '' !== $bn_of_badge ) : ?>
					<span class="bn-offer-badge<?php echo strlen( $bn_of_badge ) > 5 ? ' is-long' : ''; ?>"><strong><?php echo esc_html( $bn_of_badge ); ?></strong><?php if ( brightnest_opt( 'offer_badge_sub' ) ) : ?><small><?php echo esc_html( brightnest_opt( 'offer_badge_sub' ) ); ?></small><?php endif; ?></span>
				<?php endif; ?>
				<?php if ( $bn_of_title ) : ?><h2><?php echo esc_html( $bn_of_title ); ?></h2><?php endif; ?>
				<?php if ( brightnest_opt( 'offer_text' ) ) : ?><p><?php echo esc_html( brightnest_opt( 'offer_text' ) ); ?></p><?php endif; ?>
				<?php if ( $bn_of_code ) : ?>
					<?php $bn_of_parts = explode( '{code}', $bn_of_line, 2 ); ?>
					<p class="bn-offer-code">
						<span><?php echo esc_html( $bn_of_parts[0] ); ?><strong><?php echo esc_html( $bn_of_code ); ?></strong><?php echo isset( $bn_of_parts[1] ) ? esc_html( $bn_of_parts[1] ) : ''; ?></span>
						<?php if ( '0' !== (string) brightnest_opt( 'offer_copy' ) ) : ?>
							<button type="button" class="bn-offer-copy" data-offer-copy data-done="<?php esc_attr_e( 'Copied', 'brightnest' ); ?>"><?php esc_html_e( 'Copy', 'brightnest' ); ?></button>
						<?php endif; ?>
					</p>
				<?php endif; ?>
				<a class="bn-btn bn-btn-primary bn-btn-lg bn-btn-block bn-offer-btn" href="<?php echo esc_url( $bn_of_link ); ?>" data-offer-claim><?php echo esc_html( brightnest_label( 'offer_btn', __( 'Claim your discount', 'brightnest' ) ) ); ?></a>
				<?php if ( '' !== trim( (string) $bn_of_later ) ) : ?>
					<button type="button" class="bn-offer-later" data-offer-close><?php echo esc_html( $bn_of_later ); ?></button>
				<?php endif; ?>
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
	var trigger = box.getAttribute('data-trigger');
	var delay = (+box.getAttribute('data-delay') || 0) * 1000;
	var corner = box.classList.contains('is-corner');
	var phone = window.matchMedia('(max-width: 760px)').matches;
	if (phone && box.getAttribute('data-mobile') === '0') { return; }
	var get = function (s, k) { try { return s.getItem(k); } catch (e) { return null; } };
	var put = function (s, k, v) { try { s.setItem(k, v); } catch (e) { /* private mode */ } };
	var del = function (s, k) { try { s.removeItem(k); } catch (e) { /* ignore */ } };
	var opened = false;
	function seenLately() {
		if (!days) { return get(sessionStorage, 'bn_offer_seen') === '1'; }
		var at = +get(localStorage, 'bn_offer_seen') || 0;
		return Date.now() - at < days * 86400000;
	}
	function done() {
		if (days) { put(localStorage, 'bn_offer_seen', String(Date.now())); } else { put(sessionStorage, 'bn_offer_seen', '1'); }
		del(sessionStorage, 'bn_offer_due');
	}
	function close() { box.hidden = true; if (!corner) { document.body.classList.remove('bn-modal-open'); } done(); }
	function open() {
		if (opened) { return; }
		if (seenLately()) { del(sessionStorage, 'bn_offer_due'); return; }
		var gate = document.querySelector('[data-area-gate]');
		if (gate && !gate.hidden) { setTimeout(open, 1500); return; } // Never on top of the area pop-up: try again later.
		opened = true;
		box.hidden = false;
		if (!corner) { document.body.classList.add('bn-modal-open'); }
		del(sessionStorage, 'bn_offer_due');
	}
	Array.prototype.forEach.call(box.querySelectorAll('[data-offer-close]'), function (b) { b.addEventListener('click', close); });
	box.addEventListener('click', function (e) { if (e.target === box) { close(); } });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !box.hidden) { close(); } });
	Array.prototype.forEach.call(box.querySelectorAll('[data-offer-claim]'), function (a) {
		a.addEventListener('click', function () { if (code) { put(localStorage, 'bn_offer_code', code); } done(); });
	});
	var copy = box.querySelector('[data-offer-copy]');
	if (copy) {
		copy.addEventListener('click', function () {
			var label = copy.textContent;
			var ok = function () { copy.textContent = copy.getAttribute('data-done'); setTimeout(function () { copy.textContent = label; }, 1600); };
			if (code) { put(localStorage, 'bn_offer_code', code); }
			if (navigator.clipboard) { navigator.clipboard.writeText(code).then(ok, ok); } else { ok(); }
		});
	}
	if (seenLately()) { return; }
	if (trigger === 'load') {
		setTimeout(open, delay);
	} else if (trigger === 'scroll') {
		var need = +box.getAttribute('data-scroll') / 100;
		var onScroll = function () {
			var max = document.documentElement.scrollHeight - window.innerHeight;
			if (max > 0 && window.scrollY / max >= need) { window.removeEventListener('scroll', onScroll); setTimeout(open, delay); }
		};
		window.addEventListener('scroll', onScroll, { passive: true });
	} else if (trigger === 'exit') {
		// Desktop: the mouse leaves through the top of the window. Phones have no mouse: after the delay.
		if (phone) { setTimeout(open, Math.max(delay, 8000)); } else {
			document.addEventListener('mouseout', function (e) { if (!e.relatedTarget && e.clientY <= 0) { open(); } });
		}
	} else {
		// After the area is chosen: the homepage moves on to the booking page, which opens the pop-up; other pages open it here.
		document.addEventListener('bn:location', function () {
			put(sessionStorage, 'bn_offer_due', '1');
			if (!document.body.classList.contains('home')) { setTimeout(open, Math.max(500, delay)); }
		});
		if (get(sessionStorage, 'bn_offer_due') === '1') { setTimeout(open, Math.max(700, delay)); }
	}
})();
</script>
