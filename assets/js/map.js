/* BrightNest map adapter.
 * Google Maps when a key is set in BrightNest → Settings → Business, otherwise the free
 * OpenStreetMap (Leaflet + Nominatim). location.js and checkout.js only talk to window.bnMap:
 *   bnMap.ready(cb)                                  run cb once the map library has loaded
 *   bnMap.create(el, lat, lng, zoom, onStart, onEnd) → { setView(lat, lng, zoom), center(), resize() }
 *   bnMap.search(query, center, cb)                  cb([{ label, locate(done(lat, lng)) }])
 *   bnMap.reverse(lat, lng, cb)                      cb(error, { address, label }), address uses OSM field names
 */
(function () {
	'use strict';

	var cfg = window.BN_MAP || {};
	var useGoogle = !!cfg.key;
	var loaded = !useGoogle;
	var waiting = [];
	var maps = [];
	var LEAFLET = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min';

	function flush() {
		loaded = true;
		waiting.splice(0).forEach(function (f) { f(); });
	}
	function ready(cb) { if (loaded) { cb(); } else { waiting.push(cb); } }

	function loadLeaflet(cb) {
		if (window.L) { cb(); return; }
		var css = document.createElement('link');
		css.rel = 'stylesheet';
		css.href = LEAFLET + '.css';
		document.head.appendChild(css);
		var js = document.createElement('script');
		js.src = LEAFLET + '.js';
		js.onload = cb;
		js.onerror = cb;
		document.head.appendChild(js);
	}

	// Google refused the key (wrong, expired, no billing) or never loaded: carry on with
	// OpenStreetMap so customers can still pick a location. Open maps are rebuilt in place.
	function fallBack(why) {
		if (!useGoogle) { return; }
		useGoogle = false;
		api.google = false;
		if (window.console) { console.warn('BrightNest: Google Maps unavailable (' + why + '), using OpenStreetMap. Check the key in BrightNest → Settings → Business.'); }
		loadLeaflet(function () {
			maps.forEach(function (w) { w.toOsm(); });
			flush();
		});
	}

	// Called by the Google Maps script (callback=bnGmapsReady).
	window.bnGmapsReady = flush;
	window.gm_authFailure = function () { fallBack('key refused'); };
	if (useGoogle) {
		setTimeout(function () { if (!loaded) { fallBack('not loaded'); } }, 10000);
	}

	var OSM = 'https://nominatim.openstreetmap.org/';

	/* Map ------------------------------------------------------------------ */
	function create(el, lat, lng, zoom, onStart, onEnd) {
		var impl = null;

		function googleMap() {
			var g = new google.maps.Map(el, {
				center: { lat: lat, lng: lng }, zoom: zoom, disableDefaultUI: true, zoomControl: true,
				gestureHandling: 'greedy', clickableIcons: false
			});
			g.addListener('dragstart', onStart);
			g.addListener('idle', onEnd);
			impl = {
				setView: function (a, b, z) { g.setCenter({ lat: a, lng: b }); if (z) { g.setZoom(z); } },
				center: function () { var c = g.getCenter(); return c ? { lat: c.lat(), lng: c.lng() } : { lat: lat, lng: lng }; },
				resize: function () {}
			};
		}
		function osmMap() {
			var m = L.map(el, { zoomControl: false }).setView([lat, lng], zoom);
			L.control.zoom({ position: 'bottomright' }).addTo(m);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(m);
			m.on('movestart', onStart);
			m.on('moveend', onEnd);
			impl = {
				setView: function (a, b, z) { m.setView([a, b], z || m.getZoom()); },
				center: function () { var c = m.getCenter(); return { lat: c.lat, lng: c.lng }; },
				resize: function () { m.invalidateSize(); }
			};
		}

		if (useGoogle && window.google && google.maps) { googleMap(); } else if (window.L) { osmMap(); } else { return null; }
		maps.push({
			toOsm: function () {
				if (!window.L) { return; }
				var c = impl.center();
				lat = c.lat;
				lng = c.lng;
				el.innerHTML = '';
				osmMap();
			}
		});
		return {
			setView: function (a, b, z) { impl.setView(a, b, z); },
			center: function () { return impl.center(); },
			resize: function () { impl.resize(); }
		};
	}

	/* Search ---------------------------------------------------------------- */
	var token = null;

	// Google answers within 5 s or the free map answers instead (a refused key can stay silent).
	function guard(work, backup, cb) {
		var done = false;
		var finish = function () { if (!done) { done = true; cb.apply(null, arguments); } };
		var timer = setTimeout(function () { if (!done) { fallBack('no answer'); backup(finish); } }, 5000);
		work(function () { clearTimeout(timer); finish.apply(null, arguments); });
	}

	function googleGeocodeSearch(q, c, cb) {
		var req = { address: q };
		if (cfg.country) { req.componentRestrictions = { country: cfg.country }; }
		new google.maps.Geocoder().geocode(req).then(function (r) {
			if (!r.results.length) { osmSearch(q, c, cb); return; } // Nothing on Google: try the free map too.
			cb(r.results.slice(0, 6).map(function (x) {
				return { label: x.formatted_address, locate: function (done) { done(x.geometry.location.lat(), x.geometry.location.lng()); } };
			}));
		}).catch(function () {
			// Google refused (key, billing, quota): answer this search with OpenStreetMap.
			fallBack('search failed');
			osmSearch(q, c, cb);
		});
	}

	function googleSearch(q, c, cb) {
		var P = google.maps.places;
		// Places API (New) gives building and shop names; the Geocoder is the fallback.
		if (!P || !P.AutocompleteSuggestion) { googleGeocodeSearch(q, c, cb); return; }
		token = token || new P.AutocompleteSessionToken();
		var req = { input: q, sessionToken: token, locationBias: { center: c, radius: 50000 }, language: 'en' };
		if (cfg.country) { req.includedRegionCodes = [cfg.country]; }
		P.AutocompleteSuggestion.fetchAutocompleteSuggestions(req).then(function (r) {
			if (!(r.suggestions || []).length) { googleGeocodeSearch(q, c, cb); return; }
			cb((r.suggestions || []).filter(function (s) { return s.placePrediction; }).slice(0, 6).map(function (s) {
				var p = s.placePrediction;
				return {
					label: p.text.toString(),
					locate: function (done) {
						var place = p.toPlace();
						place.fetchFields({ fields: ['location'] }).then(function () {
							token = null; // A pick ends the billing session.
							done(place.location.lat(), place.location.lng());
						}).catch(function () {});
					}
				};
			}));
		}).catch(function () { googleGeocodeSearch(q, c, cb); });
	}

	function osmSearch(q, c, cb) {
		fetch(OSM + 'search?format=json&limit=6&addressdetails=1' + (cfg.country ? '&countrycodes=' + cfg.country : '') + '&q=' + encodeURIComponent(q) + '&viewbox=' + (c.lng - 1.5) + ',' + (c.lat + 1.5) + ',' + (c.lng + 1.5) + ',' + (c.lat - 1.5), { headers: { 'Accept-Language': 'en' } })
			.then(function (r) { return r.json(); })
			.then(function (list) {
				cb(list.map(function (it) {
					return { label: it.display_name, locate: function (done) { done(+it.lat, +it.lon); } };
				}));
			})
			.catch(function () { cb([]); });
	}

	function search(q, c, cb) {
		ready(function () {
			if (!useGoogle) { osmSearch(q, c, cb); return; }
			guard(function (done) { googleSearch(q, c, done); }, function (done) { osmSearch(q, c, done); }, cb);
		});
	}

	/* Point → address -------------------------------------------------------- */
	// Google address parts renamed to the OpenStreetMap names the rest of the theme reads.
	function googleAddress(results) {
		var a = {};
		var pick = function (type, key, short) {
			results.some(function (res) {
				return res.address_components.some(function (comp) {
					if (comp.types.indexOf(type) === -1) { return false; }
					if (!a[key]) { a[key] = short ? comp.short_name : comp.long_name; }
					return true;
				});
			});
		};
		pick('country', 'country_code', true);
		pick('locality', 'city');
		pick('administrative_area_level_2', 'county');
		pick('administrative_area_level_1', 'state');
		pick('sublocality_level_1', 'suburb');
		pick('sublocality', 'suburb');
		pick('neighborhood', 'neighbourhood');
		pick('route', 'road');
		pick('premise', 'building');
		if (a.country_code) { a.country_code = a.country_code.toLowerCase(); }
		return a;
	}

	function googleReverse(lat, lng, cb) {
		new google.maps.Geocoder().geocode({ location: { lat: lat, lng: lng } }).then(function (r) {
			var list = (r.results || []).filter(function (x) { return x.types.indexOf('plus_code') === -1; });
			if (!list.length) { cb(new Error('no result')); return; }
			cb(null, { address: googleAddress(list), label: list[0].formatted_address.split(',').slice(0, 2).join(',') });
		}).catch(function () {
			// Google refused: look the point up on OpenStreetMap instead.
			fallBack('geocode failed');
			osmReverse(lat, lng, cb);
		});
	}

	function osmReverse(lat, lng, cb) {
		fetch(OSM + 'reverse?format=json&zoom=18&lat=' + lat + '&lon=' + lng, { headers: { 'Accept-Language': 'en' } })
			.then(function (r) { return r.json(); })
			.then(function (d) {
				if (!d || !d.address) { cb(new Error('no result')); return; }
				cb(null, { address: d.address, label: d.display_name.split(',').slice(0, 2).join(',') });
			})
			.catch(function (e) { cb(e); });
	}

	function reverse(lat, lng, cb) {
		ready(function () {
			if (!useGoogle) { osmReverse(lat, lng, cb); return; }
			guard(function (done) { googleReverse(lat, lng, done); }, function (done) { osmReverse(lat, lng, done); }, cb);
		});
	}

	var api = window.bnMap = { google: useGoogle, ready: ready, create: create, search: search, reverse: reverse };
})();
