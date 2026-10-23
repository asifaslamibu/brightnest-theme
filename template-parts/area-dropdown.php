<?php
/**
 * City/area picker (first-visit pop-up, header, homepage hero, checkout).
 * Search box, city tabs, zone chips, "Popular areas" quick picks, then the areas by zone.
 * Cities, zones and areas come from BrightNest → Cities & Areas (brightnest_area_tree()); only cities
 * where at least one service is offered are listed.
 * The choice is saved in the browser (localStorage "bn_location": { label, street, area }) and a
 * "bn:location" event is fired, so the homepage opens the services page and the checkout picks the city and area.
 *
 * Args: placeholder (string), class (extra CSS class), source (sent with the "bn:location" event, e.g. "gate").
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

$bn_dd_args  = isset( $args ) && is_array( $args ) ? $args : array();
$bn_dd_ph    = ! empty( $bn_dd_args['placeholder'] ) ? $bn_dd_args['placeholder'] : __( 'Select your city / area…', 'brightnest' );
$bn_dd_class = ! empty( $bn_dd_args['class'] ) ? ' ' . $bn_dd_args['class'] : '';
$bn_dd_src   = ! empty( $bn_dd_args['source'] ) ? $bn_dd_args['source'] : '';

// Cities where at least one service is switched on.
$bn_dd_services = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$bn_dd_tree     = array();
foreach ( brightnest_area_tree() as $bn_dd_city => $bn_dd_c ) {
	foreach ( $bn_dd_services as $bn_dd_id ) {
		$bn_dd_ar = brightnest_service_areas( $bn_dd_id );
		if ( ! empty( $bn_dd_ar[ $bn_dd_city ]['on'] ) ) {
			$bn_dd_tree[ $bn_dd_city ] = $bn_dd_c;
			break;
		}
	}
}
$bn_dd_wa = function_exists( 'brightnest_whatsapp_url' ) ? brightnest_whatsapp_url( __( 'Hi, do you serve my area?', 'brightnest' ) ) : '';
?>
<div class="bn-jx-dd<?php echo esc_attr( $bn_dd_class ); ?>" data-area-dd<?php echo $bn_dd_src ? ' data-source="' . esc_attr( $bn_dd_src ) . '"' : ''; ?>>
	<button type="button" class="bn-jx-dd-btn" data-dd-toggle aria-haspopup="listbox" aria-expanded="false">
		<?php echo brightnest_icon( 'pin', 20 ); // phpcs:ignore ?>
		<span data-dd-label data-empty="<?php echo esc_attr( $bn_dd_ph ); ?>"><?php echo esc_html( $bn_dd_ph ); ?></span>
		<svg class="bn-jx-dd-chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
	</button>
	<div class="bn-jx-dd-panel" data-dd-panel hidden>
		<label class="bn-jx-dd-search">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
			<input type="search" data-dd-search placeholder="<?php esc_attr_e( 'Search area, e.g. Marina, JVC, Mirdif…', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Search area', 'brightnest' ); ?>" autocomplete="off">
		</label>

		<?php if ( count( $bn_dd_tree ) > 1 ) : ?>
			<div class="bn-jx-dd-cities" role="tablist" aria-label="<?php esc_attr_e( 'City', 'brightnest' ); ?>">
				<?php foreach ( $bn_dd_tree as $bn_dd_city => $bn_dd_c ) : ?>
					<button type="button" role="tab" data-dd-city-tab="<?php echo esc_attr( $bn_dd_city ); ?>"><?php echo esc_html( $bn_dd_c['label'] ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $bn_dd_tree as $bn_dd_city => $bn_dd_c ) : ?>
			<?php if ( count( $bn_dd_c['zones'] ) > 1 ) : ?>
				<div class="bn-jx-dd-zones" data-dd-zones="<?php echo esc_attr( $bn_dd_city ); ?>" aria-label="<?php esc_attr_e( 'Zone', 'brightnest' ); ?>">
					<button type="button" class="is-active" data-dd-zone-chip=""><?php esc_html_e( 'All areas', 'brightnest' ); ?></button>
					<?php foreach ( $bn_dd_c['zones'] as $bn_dd_zone => $bn_dd_z ) : ?>
						<button type="button" data-dd-zone-chip="<?php echo esc_attr( $bn_dd_zone ); ?>"><?php echo esc_html( $bn_dd_z['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

		<div class="bn-jx-dd-list" role="listbox">
			<?php foreach ( $bn_dd_tree as $bn_dd_city => $bn_dd_c ) : ?>
				<?php
				$bn_dd_pop = array();
				foreach ( $bn_dd_c['zones'] as $bn_dd_z ) {
					foreach ( $bn_dd_z['areas'] as $bn_dd_a ) {
						if ( $bn_dd_a['popular'] ) {
							$bn_dd_pop[] = $bn_dd_a;
						}
					}
				}
				?>
				<?php if ( $bn_dd_pop ) : ?>
					<div class="bn-jx-dd-popular" data-dd-popular="<?php echo esc_attr( $bn_dd_city ); ?>">
						<p class="bn-jx-dd-city"><?php esc_html_e( 'Popular areas', 'brightnest' ); ?></p>
						<div class="bn-jx-dd-chips">
							<?php foreach ( $bn_dd_pop as $bn_dd_a ) : ?>
								<button type="button" data-dd-opt data-city="<?php echo esc_attr( $bn_dd_city ); ?>" data-name="<?php echo esc_attr( $bn_dd_a['name'] ); ?>" data-label="<?php echo esc_attr( $bn_dd_a['label'] ); ?>" data-city-label="<?php echo esc_attr( $bn_dd_c['label'] ); ?>"><?php echo esc_html( $bn_dd_a['label'] ); ?></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
				<?php foreach ( $bn_dd_c['zones'] as $bn_dd_zone => $bn_dd_z ) : ?>
					<div class="bn-jx-dd-group" data-dd-group data-city="<?php echo esc_attr( $bn_dd_city ); ?>" data-zone="<?php echo esc_attr( $bn_dd_zone ); ?>">
						<p class="bn-jx-dd-city"><?php echo esc_html( $bn_dd_z['label'] ); ?></p>
						<ul>
							<?php foreach ( $bn_dd_z['areas'] as $bn_dd_a ) : ?>
								<li><button type="button" role="option" data-dd-opt data-city="<?php echo esc_attr( $bn_dd_city ); ?>" data-name="<?php echo esc_attr( $bn_dd_a['name'] ); ?>" data-label="<?php echo esc_attr( $bn_dd_a['label'] ); ?>" data-city-label="<?php echo esc_attr( $bn_dd_c['label'] ); ?>" data-search="<?php echo esc_attr( strtolower( $bn_dd_a['name'] . ' ' . $bn_dd_a['label'] . ' ' . $bn_dd_city . ' ' . $bn_dd_c['label'] ) ); ?>">
									<?php echo brightnest_icon( 'pin', 16 ); // phpcs:ignore ?>
									<span class="bn-jx-dd-name" data-dd-name><?php echo esc_html( $bn_dd_a['label'] ); ?></span>
								</button></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
			<div class="bn-jx-dd-empty" data-dd-empty hidden>
				<p><?php esc_html_e( 'No area found.', 'brightnest' ); ?></p>
				<?php if ( $bn_dd_wa ) : ?>
					<a href="<?php echo esc_url( $bn_dd_wa ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Ask us on WhatsApp if we serve your area', 'brightnest' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<script>
/* Area picker: search, city tab, zone chip, popular pick; remember the choice and tell the page. */
(function () {
	var dd = document.currentScript.previousElementSibling;
	if (!dd || !dd.hasAttribute('data-area-dd')) { return; }
	var $$ = function (sel) { return Array.prototype.slice.call(dd.querySelectorAll(sel)); };
	var toggle = dd.querySelector('[data-dd-toggle]');
	var label = dd.querySelector('[data-dd-label]');
	var panel = dd.querySelector('[data-dd-panel]');
	var input = dd.querySelector('[data-dd-search]');
	var empty = dd.querySelector('[data-dd-empty]');
	var list = dd.querySelector('.bn-jx-dd-list');
	var tabs = $$('[data-dd-city-tab]');
	var groups = $$('[data-dd-group]');
	var rows = $$('[data-dd-group] [data-dd-opt]');
	var opts = $$('[data-dd-opt]');
	var read = function () { try { return JSON.parse(localStorage.getItem('bn_location') || 'null'); } catch (e) { return null; } };
	var firstCity = (groups[0] && groups[0].getAttribute('data-city')) || '';
	var state = { city: firstCity, zone: '' };
	var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };

	function mark() {
		var saved = read();
		var set = !!(saved && saved.label);
		opts.forEach(function (o) {
			o.classList.toggle('is-active', !!(saved && saved.area === o.getAttribute('data-city') && (saved.street || saved.area) === o.getAttribute('data-name')));
		});
		toggle.classList.toggle('is-set', set);
		label.textContent = set ? saved.label : label.getAttribute('data-empty');
		if (saved && saved.area && groups.some(function (g) { return g.getAttribute('data-city') === saved.area; })) { state.city = saved.area; }
	}
	function shown() { return rows.filter(function (o) { return !o.parentNode.hidden && !o.closest('[data-dd-group]').hidden; }); }
	function render() {
		var q = input.value.trim().toLowerCase();
		var searching = q.length > 0;
		dd.classList.toggle('is-searching', searching);
		// City tabs and zone chips are for browsing; a search looks in every city.
		tabs.forEach(function (t) { var on = t.getAttribute('data-dd-city-tab') === state.city; t.classList.toggle('is-active', on); t.setAttribute('aria-selected', on ? 'true' : 'false'); });
		$$('[data-dd-zones]').forEach(function (z) {
			z.hidden = searching || z.getAttribute('data-dd-zones') !== state.city;
			Array.prototype.forEach.call(z.querySelectorAll('[data-dd-zone-chip]'), function (c) { c.classList.toggle('is-active', c.getAttribute('data-dd-zone-chip') === state.zone); });
		});
		$$('[data-dd-popular]').forEach(function (p) { p.hidden = searching || !!state.zone || p.getAttribute('data-dd-popular') !== state.city; });
		groups.forEach(function (g) {
			var inScope = searching || (g.getAttribute('data-city') === state.city && (!state.zone || g.getAttribute('data-zone') === state.zone));
			var n = 0;
			Array.prototype.forEach.call(g.querySelectorAll('[data-dd-opt]'), function (o) {
				var hit = inScope && (!searching || o.getAttribute('data-search').indexOf(q) !== -1);
				o.parentNode.hidden = !hit;
				// Show the matched letters in bold.
				var nm = o.querySelector('[data-dd-name]');
				var txt = o.getAttribute('data-label');
				var at = searching ? txt.toLowerCase().indexOf(q) : -1;
				nm.innerHTML = at === -1 ? esc(txt) : esc(txt.slice(0, at)) + '<b>' + esc(txt.slice(at, at + q.length)) + '</b>' + esc(txt.slice(at + q.length));
				if (hit) { n++; }
			});
			g.hidden = !n;
		});
		empty.hidden = shown().length > 0;
	}
	function setOpen(on) {
		panel.hidden = !on;
		dd.classList.toggle('is-open', on);
		toggle.setAttribute('aria-expanded', on ? 'true' : 'false');
		if (on) {
			input.value = '';
			mark();
			render();
			list.scrollTop = 0;
			if (window.matchMedia('(min-width: 768px)').matches) { setTimeout(function () { input.focus(); }, 30); }
		}
	}
	function pick(o) {
		var city = o.getAttribute('data-city');
		var name = o.getAttribute('data-name');
		var shownName = o.getAttribute('data-label') || name;
		var shownCity = o.getAttribute('data-city-label') || city;
		var comma = document.documentElement.dir === 'rtl' ? '، ' : ', ';
		var loc = { label: name === city ? shownCity : shownName + comma + shownCity, street: name === city ? '' : name, area: city };
		try { localStorage.setItem('bn_location', JSON.stringify(loc)); } catch (e) { /* private mode */ }
		mark();
		setOpen(false);
		document.dispatchEvent(new CustomEvent('bn:location', { detail: { label: loc.label, street: loc.street, area: loc.area, source: dd.getAttribute('data-source') || '' } }));
	}

	toggle.addEventListener('click', function (e) { e.stopPropagation(); setOpen(panel.hidden); });
	input.addEventListener('input', function () { render(); list.scrollTop = 0; });
	tabs.forEach(function (t) { t.addEventListener('click', function () { state.city = t.getAttribute('data-dd-city-tab'); state.zone = ''; render(); list.scrollTop = 0; }); });
	$$('[data-dd-zone-chip]').forEach(function (c) { c.addEventListener('click', function () { state.zone = c.getAttribute('data-dd-zone-chip'); render(); list.scrollTop = 0; }); });
	input.addEventListener('keydown', function (e) {
		var vis = shown();
		if (e.key === 'ArrowDown' && vis.length) { e.preventDefault(); vis[0].focus(); }
		if (e.key === 'Enter' && vis.length) { e.preventDefault(); pick(vis[0]); }
	});
	opts.forEach(function (o) {
		o.addEventListener('click', function () { pick(o); });
		o.addEventListener('keydown', function (e) {
			var vis = shown();
			var i = vis.indexOf(o);
			if (e.key === 'ArrowDown') { e.preventDefault(); if (vis[i + 1]) { vis[i + 1].focus(); } }
			if (e.key === 'ArrowUp') { e.preventDefault(); (vis[i - 1] || input).focus(); }
		});
	});
	document.addEventListener('click', function (e) { if (!panel.hidden && !dd.contains(e.target) && !dd.closest('[data-area-gate]')) { setOpen(false); } });
	dd.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden && !dd.closest('[data-area-gate]')) { setOpen(false); toggle.focus(); } });
	// Another picker on the page (e.g. the header) chose an area: show it here too.
	document.addEventListener('bn:location', mark);
	mark();
	render();
})();
</script>
