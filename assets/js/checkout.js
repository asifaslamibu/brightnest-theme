/* BrightNest multi-step checkout. */
(function () {
	'use strict';

	var cfg = window.BN_CHECKOUT;
	var root = document.querySelector('[data-checkout]');
	if (!cfg || !root) { return; }

	var $ = function (sel, ctx) { return (ctx || root).querySelector(sel); };
	var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || root).querySelectorAll(sel)); };

	var STEP_TITLES = { 1: 'Select options', 2: 'Date & time', 3: 'Address & contact', 4: 'Review & pay' };
	var STORE_KEY = 'bn_co_' + cfg.service.id;

	/* Items lookup ------------------------------------------------------- */
	var items = {};
	cfg.service.groups.forEach(function (g) {
		g.items.forEach(function (it) { items[it.id] = it; });
	});

	/* State -------------------------------------------------------------- */
	var state = {
		cart: {}, frequency: 'One Time', date: '', time: '', message: '',
		lat: null, lng: null, address: '', area: cfg.cities[0] || '', street: '', building: '', apartment: '',
		name: '', phone: '', email: '', payment: 'Cash after service', voucher: cfg.voucher || '', step: 1
	};
	try {
		var saved = JSON.parse(sessionStorage.getItem(STORE_KEY) || 'null');
		if (saved) { Object.keys(state).forEach(function (k) { if (saved[k] !== undefined) { state[k] = saved[k]; } }); }
	} catch (e) { /* storage unavailable */ }
	if (cfg.voucher) { state.voucher = cfg.voucher; }
	// Drop cart ids that no longer exist.
	Object.keys(state.cart).forEach(function (id) { if (!items[id]) { delete state.cart[id]; } });

	function save() {
		try { sessionStorage.setItem(STORE_KEY, JSON.stringify(state)); } catch (e) { /* ignore */ }
	}

	/* Money -------------------------------------------------------------- */
	function money(n) {
		return cfg.currency + ' ' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}
	function subtotal() {
		return Object.keys(state.cart).reduce(function (s, id) { return s + items[id].price * state.cart[id]; }, 0);
	}
	function cartCount() {
		return Object.keys(state.cart).reduce(function (s, id) { return s + state.cart[id]; }, 0);
	}
	function discount(sub) {
		var v = cfg.vouchers[(state.voucher || '').toUpperCase()];
		if (!v || sub <= 0) { return 0; }
		var d = v.type === 'percent' ? sub * v.value / 100 : v.value;
		return Math.round(Math.min(d, sub) * 100) / 100;
	}

	/* Step 1: cart ------------------------------------------------------- */
	function renderItems() {
		$$('[data-item]').forEach(function (el) {
			var q = state.cart[el.getAttribute('data-item')] || 0;
			el.classList.toggle('is-selected', q > 0);
			$('[data-qty]', el).textContent = q;
		});
	}
	function setQty(id, q) {
		q = Math.max(0, Math.min(10, q));
		if (q) { state.cart[id] = q; } else { delete state.cart[id]; }
		renderItems();
		update();
	}
	$$('[data-item]').forEach(function (el) {
		var id = el.getAttribute('data-item');
		$('[data-add]', el).addEventListener('click', function () { setQty(id, 1); });
		$('[data-inc]', el).addEventListener('click', function () { setQty(id, (state.cart[id] || 0) + 1); });
		$('[data-dec]', el).addEventListener('click', function () { setQty(id, (state.cart[id] || 0) - 1); });
	});

	/* Category tabs: scroll-spy */
	var tabs = $$('[data-tab]');
	tabs.forEach(function (t) {
		t.addEventListener('click', function (e) {
			e.preventDefault();
			var g = $('[data-group="' + t.getAttribute('data-tab') + '"]');
			if (g) {
				var y = g.getBoundingClientRect().top + window.scrollY - 150;
				window.scrollTo({ top: y, behavior: 'smooth' });
			}
		});
	});
	if (tabs.length && 'IntersectionObserver' in window) {
		var spy = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (!en.isIntersecting) { return; }
				var idx = en.target.getAttribute('data-group');
				tabs.forEach(function (t) {
					var on = t.getAttribute('data-tab') === idx;
					t.classList.toggle('is-active', on);
					if (on) { t.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' }); }
				});
			});
		}, { rootMargin: '-160px 0px -60% 0px' });
		$$('[data-group]').forEach(function (g) { spy.observe(g); });
	}

	/* Step 2: frequency, days, times ------------------------------------ */
	$$('[data-freq] .bn-co-choice').forEach(function (b) {
		b.addEventListener('click', function () { state.frequency = b.getAttribute('data-value'); renderStep2(); update(); });
	});

	var DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
	var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	function iso(d) {
		return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
	}
	function pretty(isoDate) {
		if (!isoDate) { return ''; }
		var p = isoDate.split('-');
		var d = new Date(+p[0], +p[1] - 1, +p[2]);
		return DAYS[d.getDay()] + ', ' + d.getDate() + ' ' + MONTHS[d.getMonth()];
	}
	(function buildDays() {
		var wrap = $('[data-days]');
		var today = new Date();
		for (var i = 0; i < 14; i++) {
			var d = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i);
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'bn-co-day';
			b.setAttribute('data-value', iso(d));
			b.innerHTML = '<small>' + (i === 0 ? 'Today' : DAYS[d.getDay()]) + '</small><strong>' + d.getDate() + '</strong><small>' + MONTHS[d.getMonth()] + '</small>';
			wrap.appendChild(b);
		}
		wrap.addEventListener('click', function (e) {
			var b = e.target.closest('.bn-co-day');
			if (!b) { return; }
			state.date = b.getAttribute('data-value');
			state.time = '';
			renderStep2();
			update();
		});
	})();
	function slots() {
		var out = [];
		for (var h = 8; h < 20; h++) {
			[0, 30].forEach(function (m) {
				var s = ('0' + h).slice(-2) + ':' + (m ? '30' : '00');
				var eh = m ? h + 1 : h, em = m ? '00' : '30';
				out.push({ start: h * 60 + m, label: s + '-' + ('0' + eh).slice(-2) + ':' + em });
			});
		}
		return out;
	}
	function renderTimes() {
		var wrap = $('[data-times]');
		wrap.innerHTML = '';
		if (!state.date) {
			wrap.innerHTML = '<p class="bn-muted">Please select a date first.</p>';
			return;
		}
		var now = new Date();
		var isToday = state.date === iso(now);
		var minStart = now.getHours() * 60 + now.getMinutes() + 120; // 2h lead time for same-day.
		var any = false;
		slots().forEach(function (s) {
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'bn-co-time' + (state.time === s.label ? ' is-active' : '');
			b.textContent = s.label;
			b.setAttribute('data-value', s.label);
			if (isToday && s.start < minStart) { b.disabled = true; } else { any = true; }
			wrap.appendChild(b);
		});
		if (!any) { wrap.innerHTML = '<p class="bn-muted">No slots left today, please choose another day.</p>'; }
	}
	$('[data-times]').addEventListener('click', function (e) {
		var b = e.target.closest('.bn-co-time');
		if (!b || b.disabled) { return; }
		state.time = b.getAttribute('data-value');
		renderStep2();
		update();
	});
	function renderStep2() {
		$$('[data-freq] .bn-co-choice').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-value') === state.frequency); });
		$$('.bn-co-day').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-value') === state.date); });
		renderTimes();
	}

	/* Simple fields ------------------------------------------------------ */
	$$('[data-field]').forEach(function (el) {
		var k = el.getAttribute('data-field');
		if (state[k]) { el.value = state[k]; }
		el.addEventListener('input', function () { state[k] = el.value; update(); });
		el.addEventListener('change', function () { state[k] = el.value; update(); });
	});
	$$('[name="bn_pay"]').forEach(function (r) {
		r.checked = r.value === state.payment;
		r.addEventListener('change', function () { if (r.checked) { state.payment = r.value; update(); } });
	});
	var terms = $('[data-terms]');
	terms.addEventListener('change', update);

	/* Voucher ------------------------------------------------------------ */
	var vInput = $('[data-voucher-input]');
	var vMsg = $('[data-voucher-msg]');
	vInput.value = state.voucher;
	$('[data-voucher-apply]').addEventListener('click', function () {
		var code = vInput.value.trim().toUpperCase();
		if (!code) { state.voucher = ''; vMsg.textContent = ''; update(); return; }
		if (cfg.vouchers[code]) {
			state.voucher = code;
			vMsg.className = 'bn-co-voucher-msg is-ok';
			vMsg.textContent = 'Voucher ' + code + ' applied.';
		} else {
			state.voucher = '';
			vMsg.className = 'bn-co-voucher-msg is-bad';
			vMsg.textContent = 'This voucher code is not valid.';
		}
		update();
	});

	/* Map / address modal ------------------------------------------------ */
	var modal = $('[data-map-modal]');
	var map = null;
	function openMap() {
		modal.hidden = false;
		document.body.classList.add('bn-modal-open');
		if (!window.L) { return; }
		if (!map) {
			var start = state.lat ? [state.lat, state.lng] : [cfg.map.lat, cfg.map.lng];
			map = L.map($('[data-map]'), { zoomControl: true, attributionControl: true }).setView(start, 15);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 19,
				attribution: '&copy; OpenStreetMap'
			}).addTo(map);
			map.on('movestart', function () { modal.classList.add('is-moving'); });
			map.on('moveend', function () { modal.classList.remove('is-moving'); });
		}
		setTimeout(function () { map.invalidateSize(); }, 50);
	}
	function closeMap() {
		modal.hidden = true;
		document.body.classList.remove('bn-modal-open');
	}
	$$('[data-open-map]').forEach(function (b) { b.addEventListener('click', openMap); });
	$('[data-map-close]').addEventListener('click', closeMap);
	modal.addEventListener('click', function (e) { if (e.target === modal) { closeMap(); } });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) { closeMap(); } });

	$('[data-map-locate]').addEventListener('click', function () {
		if (!navigator.geolocation || !map) { return; }
		navigator.geolocation.getCurrentPosition(function (p) {
			map.setView([p.coords.latitude, p.coords.longitude], 17);
		});
	});

	var results = $('[data-map-results]');
	var searchTimer;
	function geosearch(q) {
		var c = map ? map.getCenter() : { lat: cfg.map.lat, lng: cfg.map.lng };
		var url = 'https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent(q) +
			'&viewbox=' + (c.lng - 1) + ',' + (c.lat + 1) + ',' + (c.lng + 1) + ',' + (c.lat - 1);
		fetch(url, { headers: { 'Accept-Language': 'en' } }).then(function (r) { return r.json(); }).then(function (list) {
			results.innerHTML = '';
			list.forEach(function (it) {
				var li = document.createElement('li');
				var b = document.createElement('button');
				b.type = 'button';
				b.textContent = it.display_name;
				b.addEventListener('click', function () {
					map.setView([+it.lat, +it.lon], 17);
					results.hidden = true;
				});
				li.appendChild(b);
				results.appendChild(li);
			});
			results.hidden = !list.length;
		}).catch(function () { results.hidden = true; });
	}
	$('[data-map-query]').addEventListener('input', function (e) {
		clearTimeout(searchTimer);
		var q = e.target.value.trim();
		if (q.length < 3) { results.hidden = true; return; }
		searchTimer = setTimeout(function () { geosearch(q); }, 450);
	});
	$('[data-map-search]').addEventListener('submit', function (e) {
		e.preventDefault();
		var q = $('[data-map-query]').value.trim();
		if (q.length >= 3) { geosearch(q); }
	});

	$('[data-map-confirm]').addEventListener('click', function () {
		var c = map ? map.getCenter() : { lat: cfg.map.lat, lng: cfg.map.lng };
		state.lat = Math.round(c.lat * 1e6) / 1e6;
		state.lng = Math.round(c.lng * 1e6) / 1e6;
		state.address = state.lat + ', ' + state.lng;
		closeMap();
		update();
		fetch('https://nominatim.openstreetmap.org/reverse?format=json&zoom=18&lat=' + state.lat + '&lon=' + state.lng, { headers: { 'Accept-Language': 'en' } })
			.then(function (r) { return r.json(); })
			.then(function (d) {
				if (!d || !d.address) { return; }
				var a = d.address;
				state.address = d.display_name.split(',').slice(0, 3).join(',');
				var street = [a.road, a.neighbourhood || a.suburb].filter(Boolean).join(', ');
				if (street && !state.street) { state.street = street; $('[data-field="street"]').value = street; }
				if ((a.building || a.house_name || a.amenity) && !state.building) {
					state.building = a.building || a.house_name || a.amenity;
					$('[data-field="building"]').value = state.building;
				}
				var city = a.city || a.town || a.state || '';
				var sel = $('[data-field="area"]');
				Array.prototype.forEach.call(sel.options, function (o) {
					if (city && city.toLowerCase().indexOf(o.value.toLowerCase()) !== -1) { sel.value = o.value; state.area = o.value; }
				});
				update();
			}).catch(function () { /* keep coordinates */ });
	});

	/* Steps -------------------------------------------------------------- */
	var nextBtn = $('[data-next]');
	var errorEl = $('[data-error]');

	function stepValid(n) {
		if (n === 1) { return cartCount() > 0 ? '' : 'Please select at least one option.'; }
		if (n === 2) { return state.date && state.time ? '' : 'Please choose a date and time slot.'; }
		if (n === 3) {
			if (!state.street.trim() || !state.building.trim()) { return 'Please enter your area/street and building.'; }
			if (!state.name.trim()) { return 'Please enter your name.'; }
			if (state.phone.replace(/\D/g, '').length < 7) { return 'Please enter a valid mobile number.'; }
			if (state.email && !/^\S+@\S+\.\S+$/.test(state.email)) { return 'Please enter a valid email.'; }
			return '';
		}
		if (n === 4) { return terms.checked ? '' : 'Please accept the terms to continue.'; }
		return '';
	}

	function goTo(n, push) {
		// Never skip ahead past an incomplete step.
		for (var i = 1; i < n; i++) { if (stepValid(i)) { n = i; break; } }
		state.step = n;
		$$('[data-step]').forEach(function (s) { s.hidden = s.getAttribute('data-step') !== String(n); });
		$('[data-step-num]').textContent = n;
		$('[data-step-title]').textContent = STEP_TITLES[n] || '';
		$('[data-progress]').style.width = (n / 4 * 100) + '%';
		$('[data-back]').style.visibility = 'visible';
		errorEl.textContent = '';
		if (n === 2) { renderStep2(); }
		if (n === 4) { renderReview(); }
		var url = new URL(window.location.href);
		url.searchParams.set('step', n);
		if (push) { history.pushState({ step: n }, '', url); } else { history.replaceState({ step: n }, '', url); }
		update();
		window.scrollTo({ top: 0, behavior: 'smooth' });
	}

	window.addEventListener('popstate', function (e) {
		var n = (e.state && e.state.step) || +new URL(window.location.href).searchParams.get('step') || 1;
		goTo(n, false);
	});

	$('[data-back]').addEventListener('click', function () {
		if (state.step > 1) { goTo(state.step - 1, true); } else { window.location.href = cfg.service.url; }
	});

	nextBtn.addEventListener('click', function () {
		var err = stepValid(state.step);
		if (err) { errorEl.textContent = err; return; }
		if (state.step < 4) { goTo(state.step + 1, true); return; }
		submit();
	});

	/* Review ------------------------------------------------------------- */
	function esc(s) {
		return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
	}
	function addrText() {
		return [state.apartment, state.building, state.street, state.area].filter(function (x) { return x && x.trim(); }).join(', ');
	}
	function renderReview() {
		var rows = [
			['Service', cfg.service.title, 1],
			['Options', Object.keys(state.cart).map(function (id) { return state.cart[id] + ' × ' + items[id].name; }).join('<br>'), 1],
			['Frequency', state.frequency, 2],
			['Date & time', pretty(state.date) + ' · ' + state.time, 2],
			['Address', addrText(), 3],
			['Contact', [state.name, state.phone, state.email].filter(Boolean).join(' · '), 3]
		];
		if (state.message) { rows.splice(4, 0, ['Instructions', state.message, 2]); }
		$('[data-review]').innerHTML = rows.map(function (r) {
			var val = r[0] === 'Options' ? r[1] : esc(r[1]);
			return '<div><dt>' + r[0] + '</dt><dd>' + val + '</dd><button type="button" class="bn-link" data-edit="' + r[2] + '">Edit</button></div>';
		}).join('');
		$$('[data-edit]').forEach(function (b) {
			b.addEventListener('click', function () { goTo(+b.getAttribute('data-edit'), true); });
		});
	}

	/* Summary + bar ------------------------------------------------------ */
	function update() {
		var sub = subtotal();
		var disc = discount(sub);
		var total = Math.max(0, sub - disc);

		$('[data-sum="freq"]').textContent = state.frequency;
		var lines = Object.keys(state.cart);
		$('[data-lines]').hidden = !lines.length;
		$('[data-lines-list]').innerHTML = lines.map(function (id) {
			return '<li><span>' + state.cart[id] + ' × ' + esc(items[id].name) + '</span><strong>' + money(items[id].price * state.cart[id]) + '</strong></li>';
		}).join('');

		var hasDate = !!(state.date && state.time);
		$('[data-sum-row="date"]').hidden = $('[data-sum="date"]').hidden = !hasDate;
		$('[data-sum="date"]').textContent = hasDate ? pretty(state.date) + ', ' + state.time : '';

		var addr = addrText() || state.address;
		$('[data-sum-row="addr"]').hidden = $('[data-sum="addr"]').hidden = !addr;
		$('[data-sum="addr"]').textContent = addr;
		$('[data-addr-label]').textContent = state.address || 'Pin your location on the map';

		$('[data-sum="sub"]').textContent = money(sub);
		$('[data-sum-row="disc"]').hidden = $('[data-sum="disc"]').hidden = !disc;
		$('[data-sum="disc"]').textContent = '- ' + money(disc);
		$('[data-sum="code"]').textContent = disc ? '(' + state.voucher.toUpperCase() + ')' : '';
		$('[data-sum="total"]').textContent = money(total);
		$('[data-total-mini]').textContent = money(total);

		var hint = $('[data-voucher-hint]');
		var hasVoucher = !!cfg.vouchers[(state.voucher || '').toUpperCase()];
		hint.hidden = !(hasVoucher && !sub && state.step === 1);
		$('[data-voucher-code]').textContent = (state.voucher || '').toUpperCase();

		nextBtn.disabled = !!stepValid(state.step);
		nextBtn.textContent = state.step === 4 ? 'Complete booking' : 'Next';
		if (!stepValid(state.step)) { errorEl.textContent = ''; }
		save();
	}

	/* Mobile summary toggle */
	$('[data-summary-toggle]').addEventListener('click', function () {
		var side = $('[data-side]');
		side.scrollIntoView({ behavior: 'smooth', block: 'start' });
	});

	/* Submit ------------------------------------------------------------- */
	function submit() {
		nextBtn.disabled = true;
		nextBtn.textContent = 'Booking…';
		var body = new FormData();
		body.append('action', 'bn_checkout');
		body.append('nonce', cfg.nonce);
		body.append('service', cfg.service.id);
		body.append('cart', JSON.stringify(state.cart));
		['frequency', 'date', 'time', 'message', 'area', 'street', 'building', 'apartment', 'name', 'phone', 'email', 'payment', 'voucher'].forEach(function (k) {
			body.append(k, state[k] || '');
		});
		body.append('address', state.address || '');
		body.append('lat', state.lat || '');
		body.append('lng', state.lng || '');

		fetch(cfg.ajax, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (!res || !res.success) { throw new Error((res && res.data && res.data.message) || 'Something went wrong.'); }
				$('[data-ref]').textContent = '#' + res.data.ref;
				$$('[data-step]').forEach(function (s) { s.hidden = s.getAttribute('data-step') !== 'done'; });
				$('[data-bar]').hidden = true;
				$('.bn-co-head').hidden = true;
				root.classList.add('is-done');
				try { sessionStorage.removeItem(STORE_KEY); } catch (e) { /* ignore */ }
				window.scrollTo({ top: 0, behavior: 'smooth' });
			})
			.catch(function (err) {
				errorEl.textContent = err.message;
				nextBtn.disabled = false;
				nextBtn.textContent = 'Complete booking';
			});
	}

	/* Init --------------------------------------------------------------- */
	renderItems();
	var startStep = +new URL(window.location.href).searchParams.get('step') || 1;
	goTo(Math.min(4, Math.max(1, startStep)), false);
	if (!state.lat && state.step === 1) { openMap(); }
})();
