<?php
/**
 * GTranslate (plugin): English and Arabic only, switched in the header ("English" / "العربية").
 *
 * While the plugin is active it does the Arabic: the theme's own /ar/ pages are off (old /ar/ links
 * redirect to the English page), the header shows the GTranslate switch, and an Arabic page turns
 * right-to-left with the Arabic typeface (assets/css/rtl.css). Deactivate the plugin and the
 * theme's /ar/ version (inc/i18n.php) is back as it was.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

function brightnest_gt_on() {
	return class_exists( 'GTranslate' );
}

/**
 * Once: GTranslate shows English and Arabic only, with the language names in their own language.
 */
function brightnest_gt_settings() {
	if ( ! brightnest_gt_on() || get_option( 'bn_gt_setup_v1' ) ) {
		return;
	}
	$d = get_option( 'GTranslate' );
	if ( ! is_array( $d ) ) {
		return; // The plugin has not saved its settings yet; try again on the next admin page.
	}
	$d['default_language']           = 'en';
	$d['incl_langs']                 = array( 'en', 'ar' );
	$d['fincl_langs']                = array( 'en', 'ar' );
	$d['native_language_names']      = 1;
	$d['floating_language_selector'] = 'no'; // The switch is in the header.
	update_option( 'GTranslate', $d );
	update_option( 'bn_gt_setup_v1', 1 );
}
add_action( 'admin_init', 'brightnest_gt_settings' );

/**
 * Old /ar/ addresses (the theme's own Arabic pages) go to the English page; GTranslate translates it.
 */
function brightnest_gt_ar_redirect() {
	if ( empty( $GLOBALS['bn_ar_redirect'] ) ) {
		return;
	}
	$scheme = is_ssl() ? 'https://' : 'http://';
	$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : wp_parse_url( home_url(), PHP_URL_HOST );
	wp_safe_redirect( $scheme . $host . $GLOBALS['bn_ar_redirect'], 301 );
	exit;
}
add_action( 'template_redirect', 'brightnest_gt_ar_redirect', 0 );

/**
 * The header switch: two GTranslate links; the one for the language on screen is hidden.
 */
function brightnest_gt_switch() {
	echo '<span class="bn-hlang bn-gt-switch notranslate">';
	echo do_shortcode( '[gt-link lang="en" label="English" widget_look="lang_names"]' );
	echo do_shortcode( '[gt-link lang="ar" label="العربية" widget_look="lang_names"]' );
	echo '</span>';
}

/**
 * Arabic on screen (Google marks the page "translated-rtl"): turn the page right-to-left.
 */
function brightnest_gt_rtl_script() {
	if ( ! brightnest_gt_on() || is_admin() ) {
		return;
	}
	?>
	<script>
	(function () {
		var h = document.documentElement;
		function apply() {
			var ar = h.classList.contains('translated-rtl');
			if ((h.getAttribute('dir') === 'rtl') === ar) { return; }
			h.setAttribute('dir', ar ? 'rtl' : 'ltr');
			h.setAttribute('lang', ar ? 'ar' : 'en');
		}
		new MutationObserver(apply).observe(h, { attributes: true, attributeFilter: ['class'] });
		apply();
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'brightnest_gt_rtl_script', 1 );
