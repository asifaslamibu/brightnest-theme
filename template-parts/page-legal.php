<?php
/**
 * Legal page layout (Terms, Privacy…; inc/page-layouts.php): a contents list built from the page's
 * headings beside the text, numbered sections, and a help box with the contact details from Settings.
 *
 * @package BrightNest
 */

$bn_html = apply_filters( 'the_content', get_the_content() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
list( $bn_html, $bn_toc ) = brightnest_legal_toc( $bn_html );
$bn_contact               = brightnest_core_page( 'contact' );
?>
<section class="bn-legal">
	<div class="bn-container bn-legal-grid<?php echo $bn_toc ? '' : ' no-toc'; ?>">
		<?php if ( $bn_toc ) : ?>
			<aside class="bn-legal-side">
				<details class="bn-legal-toc" open>
					<summary><?php esc_html_e( 'On this page', 'brightnest' ); ?></summary>
					<ol>
						<?php foreach ( $bn_toc as $bn_t ) : ?>
							<li><a href="#<?php echo esc_attr( $bn_t[0] ); ?>"><?php echo esc_html( $bn_t[1] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</details>
			</aside>
		<?php endif; ?>
		<div class="bn-legal-main">
			<article class="bn-legal-doc bn-prose">
				<?php echo $bn_html; // phpcs:ignore WordPress.Security.EscapeOutput -- the page's own content. ?>
			</article>
			<div class="bn-legal-help">
				<span class="bn-legal-help-icon"><?php echo brightnest_icon( 'headset', 24 ); // phpcs:ignore ?></span>
				<div>
					<strong><?php esc_html_e( 'Questions about this page?', 'brightnest' ); ?></strong>
					<p><?php esc_html_e( 'Our team is happy to help. Get in touch any way you like.', 'brightnest' ); ?></p>
					<div class="bn-legal-help-links">
						<?php if ( brightnest_opt( 'phone' ) ) : ?>
							<a href="<?php echo esc_url( brightnest_tel_url() ); ?>" dir="ltr"><?php echo brightnest_icon( 'phone', 15 ); // phpcs:ignore ?> <?php echo esc_html( brightnest_opt( 'phone' ) ); ?></a>
						<?php endif; ?>
						<?php if ( brightnest_whatsapp_url() ) : ?>
							<a href="<?php echo esc_url( brightnest_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 15 ); // phpcs:ignore ?> WhatsApp</a>
						<?php endif; ?>
						<?php if ( brightnest_opt( 'email' ) ) : ?>
							<a href="mailto:<?php echo esc_attr( brightnest_opt( 'email' ) ); ?>"><?php echo brightnest_icon( 'mail', 15 ); // phpcs:ignore ?> <?php echo esc_html( brightnest_opt( 'email' ) ); ?></a>
						<?php endif; ?>
						<?php if ( $bn_contact ) : ?>
							<a href="<?php echo esc_url( get_permalink( $bn_contact ) ); ?>"><?php esc_html_e( 'Contact page', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 14 ); // phpcs:ignore ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php if ( $bn_toc ) : ?>
<script>
/* Contents list: the section on screen is highlighted. */
(function () {
	var toc = document.querySelector('.bn-legal-toc');
	if (toc && window.innerWidth < 960) { toc.open = false; } // Phones: closed until tapped.
	var links = document.querySelectorAll('.bn-legal-toc a');
	if (!('IntersectionObserver' in window) || !links.length) { return; }
	var byId = {};
	links.forEach(function (a) { byId[a.getAttribute('href').slice(1)] = a; });
	var io = new IntersectionObserver(function (entries) {
		entries.forEach(function (e) {
			if (!e.isIntersecting) { return; }
			links.forEach(function (a) { a.classList.remove('is-active'); });
			if (byId[e.target.id]) { byId[e.target.id].classList.add('is-active'); }
		});
	}, { rootMargin: '-100px 0px -70% 0px' });
	document.querySelectorAll('.bn-legal-doc h2[id]').forEach(function (h) { io.observe(h); });
})();
</script>
<?php endif; ?>
