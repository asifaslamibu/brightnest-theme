/* BrightNest location picker: "Set my location" (homepage, services hub, checkout). Map via map.js.
 * Saves { lat, lng, label, street, area } to localStorage "bn_location"; the checkout reads it. */
(function () {
	'use strict';

	var cfg = window.BN_LOC;
	var modal = document.querySelector('[data-loc-modal]');
	if (!cfg || !modal) { return; }

	var KEY = 'bn_location';
	var I18N = window.BN_I18N || {};
	var t = function (s, a) { return (I18N[s] || s).replace('%s', a === undefined ? '%s' : a); };
	// Shown names of our cities (they stay English inside, for prices).
	var cityLabel = function (c) { return (cfg.cityLabels && cfg.cityLabels[c]) || c; };
	var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
	var $$ = function (sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); };
	var read = function () { try { return JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) { return null; } };
	var map = null;
	var results = $('[data-loc-results]', modal);
	var timer;

	function render() {
		var loc = read();
		$$('[data-loc-label]').forEach(function (el) {
			el.textContent = loc && loc.label ? loc.label : el.getAttribute('data-empty');
		});
		$$('[data-loc-open]').forEach(function (el) { el.classList.toggle('is-set', !!(loc && loc.lat)); });
		// Services not offered in the chosen area are greyed out.
		var area = loc && loc.area;
		$$('[data-off-areas]').forEach(function (li) {
			var off = [];
			try { off = JSON.parse(li.getAttribute('data-off-areas') || '[]'); } catch (e) { /* ignore */ }
			var na = !!area && off.indexOf(area) !== -1;
			li.classList.toggle('is-unavailable', na);
			var note = li.querySelector('.bn-jl-tile-off');
			if (note) { note.hidden = !na; note.textContent = na ? t('Not available in %s', cityLabel(area)) : ''; }
		});
	}

	function save(lat, lng, label, street, area) {
		var loc = { lat: Math.round(lat * 1e6) / 1e6, lng: Math.round(lng * 1e6) / 1e6, label: label || t('Location pinned'), street: street || '', area: area || '' };
		try { localStorage.setItem(KEY, JSON.stringify(loc)); } catch (e) { /* private mode */ }
		render();
		// Let other scripts react (the homepage then opens the services page).
		document.dispatchEvent(new CustomEvent('bn:location', { detail: loc }));
	}

	/* Reverse geocode a point (Google or OpenStreetMap, see map.js), then save it. */
	// Only places inside one of our cities are saved; anywhere else shows
	// "We do not currently operate in this area." done(true|false).
	function resolve(lat, lng, done) {
		var finish = function (ok) { if (done) { done(ok); } };
		window.bnMap.reverse(lat, lng, function (err, r) {
			if (err) {
				// The place could not be checked, so it is not accepted.
				if (window.bnAlert) { window.bnAlert(t('Error'), window.BN_AREA_CHECK_ERROR); }
				finish(false);
				return;
			}
			var a = r.address;
			var area = window.bnMatchCity ? window.bnMatchCity(a, cfg.cities, cfg.country) : '';
			if (!area) {
				if (window.bnAlert) { window.bnAlert(t('Error'), window.BN_AREA_ERROR); }
				finish(false);
				return;
			}
			var street = [a.building || a.house_name || a.amenity, a.road, a.neighbourhood || a.suburb].filter(Boolean).join(', ');
			save(lat, lng, r.label, street, area);
			finish(true);
		});
	}

	function open() {
		modal.hidden = false;
		document.body.classList.add('bn-modal-open');
		window.bnMap.ready(function () {
			if (!map) {
				var loc = read();
				var start = loc && loc.lat ? loc : cfg;
				map = window.bnMap.create($('[data-loc-map]', modal), +start.lat, +start.lng, 14,
					function () { modal.classList.add('is-moving'); },
					function () { modal.classList.remove('is-moving'); });
			}
			setTimeout(function () { if (map) { map.resize(); } $('[data-loc-query]', modal).focus(); }, 60);
		});
	}
	function close() { modal.hidden = true; document.body.classList.remove('bn-modal-open'); }

	function locate(onPoint) {
		if (!navigator.geolocation) { open(); return; }
		navigator.geolocation.getCurrentPosition(function (p) { onPoint(p.coords.latitude, p.coords.longitude); }, function () { open(); }, { enableHighAccuracy: true, timeout: 8000 });
	}

	function search(q) {
		var c = map ? map.center() : { lat: cfg.lat, lng: cfg.lng };
		window.bnMap.search(q, c, function (list) {
			results.innerHTML = '';
			list.forEach(function (it) {
				var li = document.createElement('li');
				var b = document.createElement('button');
				b.type = 'button';
				b.textContent = it.label;
				b.addEventListener('click', function () {
					results.hidden = true;
					it.locate(function (lat, lng) { if (map) { map.setView(lat, lng, 17); } });
				});
				li.appendChild(b);
				results.appendChild(li);
			});
			results.hidden = !list.length;
		});
	}

	$$('[data-loc-open]').forEach(function (b) { b.addEventListener('click', open); });
	// Hero "locate me" button: use GPS straight away, no map needed.
	$$('[data-loc-here]').forEach(function (b) {
		b.addEventListener('click', function (e) {
			e.stopPropagation();
			b.classList.add('is-busy');
			locate(function (lat, lng) { resolve(lat, lng, function () { b.classList.remove('is-busy'); }); });
		});
	});
	$('[data-loc-close]', modal).addEventListener('click', close);
	modal.addEventListener('click', function (e) { if (e.target === modal) { close(); } });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) { close(); } });
	$('[data-loc-gps]', modal).addEventListener('click', function () { locate(function (lat, lng) { if (map) { map.setView(lat, lng, 17); } }); });
	$('[data-loc-query]', modal).addEventListener('input', function (e) {
		clearTimeout(timer);
		var q = e.target.value.trim();
		if (q.length < 3) { results.hidden = true; return; }
		timer = setTimeout(function () { search(q); }, 450);
	});
	$('[data-loc-search]', modal).addEventListener('submit', function (e) {
		e.preventDefault();
		var q = $('[data-loc-query]', modal).value.trim();
		if (q.length >= 3) { search(q); }
	});
	$('[data-loc-confirm]', modal).addEventListener('click', function () {
		var btn = this;
		var label = btn.textContent;
		var c = map ? map.center() : { lat: cfg.lat, lng: cfg.lng };
		btn.disabled = true;
		btn.textContent = t('Checking…');
		resolve(c.lat, c.lng, function (ok) {
			btn.disabled = false;
			btn.textContent = label;
			if (!ok) { return; } // Outside our areas: the map stays open to pick another spot.
			close();
			var next = document.getElementById('services');
			if (next) { next.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
		});
	});

	render();
})();
