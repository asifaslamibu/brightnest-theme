/* BrightNest checkout: 4 steps (service details → date & time → address → payment). */
(function () {
	'use strict';

	var cfg = window.BN_CHECKOUT;
	var root = document.querySelector('[data-checkout]');
	if (!cfg || !root) { return; }

	var $ = function (sel, ctx) { return (ctx || root).querySelector(sel); };
	var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || root).querySelectorAll(sel)); };
	var esc = function (s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
	};

	var LAST = 4;
	var STEP_TITLES = { 1: 'Service Details', 2: 'Date & Time', 3: 'Address', 4: 'Payment' };
	var STORE_KEY = 'bn_co_' + cfg.service.id;
	var CUSTOMER_KEY = 'bn_customer';
	var hourly = cfg.service.hourly;

	var items = {};
	cfg.service.groups.forEach(function (g) { g.items.forEach(function (it) { items[it.id] = it; }); });

	/* State -------------------------------------------------------------- */
	var state = {
		cart: {}, hourly: { hours: 0, pros: 0, materials: null }, frequency: (cfg.service.freqs[0] || [''])[0], date: '', time: '', message: '',
		lat: null, lng: null, address: '', area: cfg.cities[0] || '', street: '', building: '', apartment: '',
		name: '', phone: '', email: '', payment: (cfg.payments && cfg.payments[0]) || 'Cash after service', voucher: cfg.voucher || '', step: 1
	};
	var readJSON = function (store, key) { try { return JSON.parse(store.getItem(key) || 'null'); } catch (e) { return null; } };
	var saved = readJSON(sessionStorage, STORE_KEY);
	if (saved) { Object.keys(state).forEach(function (k) { if (saved[k] !== undefined) { state[k] = saved[k]; } }); }
	if (cfg.voucher) { state.voucher = cfg.voucher; }
	Object.keys(state.cart).forEach(function (id) { if (!items[id]) { delete state.cart[id]; } });
	// "Book" on a package in the service page price list (?add=item-id) pre-selects it.
	var addId = new URL(window.location.href).searchParams.get('add');
	if (addId && items[addId] && !state.cart[addId]) { state.cart[addId] = 1; }

	// Returning customer: reuse the details from their last booking.
	var customer = readJSON(localStorage, CUSTOMER_KEY);
	if (customer) {
		['name', 'phone', 'email', 'street', 'building', 'apartment', 'area', 'lat', 'lng', 'address'].forEach(function (k) {
			if (!state[k] && customer[k]) { state[k] = customer[k]; }
		});
	}
	// Location picked in the homepage hero.
	var picked = readJSON(localStorage, 'bn_location');
	if (!state.lat && picked && picked.lat) {
		state.lat = picked.lat; state.lng = picked.lng; state.address = picked.label || '';
		if (!state.street && picked.street) { state.street = picked.street; }
		if (picked.area && cfg.cities.indexOf(picked.area) !== -1) { state.area = picked.area; }
	}
	if (cfg.cities.indexOf(state.area) === -1) { state.area = cfg.cities[0] || ''; }
	if (!cfg.service.freqs.some(function (f) { return f[0] === state.frequency; })) { state.frequency = (cfg.service.freqs[0] || [''])[0]; }
	if (cfg.payments && cfg.payments.indexOf(state.payment) === -1) { state.payment = cfg.payments[0]; }

	function save() { try { sessionStorage.setItem(STORE_KEY, JSON.stringify(state)); } catch (e) { /* ignore */ } }

	/* Money & lines ------------------------------------------------------ */
	function money(n) {
		return cfg.currency + ' ' + Number(n).toLocaleString('en-US', { minimumFractionDigits: n % 1 ? 2 : 0, maximumFractionDigits: 2 });
	}
	/* Area pricing: each service can cost more/less per city, or not be offered there. */
	function areaInfo() { return cfg.service.areas[state.area] || { on: false, adj: 0, prices: {} }; }
	function unit(price) { return Math.round(price * (1 + areaInfo().adj / 100) * 100) / 100; }
	/* Same rules as the server: an area's own price wins, otherwise base ± area %. */
	function ownPrice(key) { var p = areaInfo().prices; return p && p[key] ? +p[key] : 0; }
	function itemPrice(id) { return ownPrice(items[id].key) || unit(items[id].price); }
	function hourRate() { var a = areaInfo(); return a.rate > 0 ? +a.rate : unit(hourly.rate); }
	function matRate() { var a = areaInfo(); return a.materials !== null && a.materials !== undefined ? +a.materials : unit(hourly.materials); }
	function renderPrices() {
		$$('[data-money]').forEach(function (el) {
			var key = el.getAttribute('data-key');
			var own = key ? ownPrice(key) : 0;
			if (el.hasAttribute('data-hourly-mat')) { el.textContent = money(matRate()); return; }
			el.textContent = money(own || unit(+el.getAttribute('data-money')));
		});
		// A crossed-out "was" price only makes sense when it is still higher than today's price.
		$$('[data-old-of]').forEach(function (el) {
			var own = ownPrice(el.getAttribute('data-old-of'));
			el.hidden = !!own && own >= unit(+el.getAttribute('data-money'));
		});
	}
	function freqPct() {
		var f = cfg.service.freqs.filter(function (x) { return x[0] === state.frequency; })[0];
		return f ? +f[1] : 0;
	}
	function hourlyReady() { var h = state.hourly; return !!(h.hours && h.pros && (!hourly.mat_on || h.materials !== null)); }
	function lineItems() {
		if (hourly) {
			var h = state.hourly;
			if (!h.hours || !h.pros) { return []; }
			var out = [[h.hours + (h.hours > 1 ? ' hours' : ' hour') + ' × ' + h.pros + (h.pros > 1 ? ' professionals' : ' professional'), hourRate() * h.hours * h.pros]];
			if (hourly.mat_on && h.materials && matRate() > 0) { out.push([hourly.mat_name, matRate() * h.hours * h.pros]); }
			return out;
		}
		return Object.keys(state.cart).map(function (id) { return [state.cart[id] + ' × ' + items[id].name, itemPrice(id) * state.cart[id]]; });
	}
	function subtotal() { return lineItems().reduce(function (s, l) { return s + l[1]; }, 0); }
	/* Same order as the server: frequency discount → voucher → service fee. */
	function totals() {
		var sub = subtotal();
		var fdisc = Math.round(sub * freqPct()) / 100;
		var disc = discount(sub - fdisc);
		var afee = +(cfg.areaFees && cfg.areaFees[state.area]) || 0;
		return { sub: sub, fdisc: fdisc, disc: disc, afee: afee, total: Math.round((Math.max(0, sub - fdisc - disc) + (+cfg.fee || 0) + afee) * 100) / 100 };
	}
	function cartCount() {
		if (hourly) { return hourlyReady() ? 1 : 0; }
		return Object.keys(state.cart).reduce(function (s, id) { return s + state.cart[id]; }, 0);
	}
	function discount(sub) {
		var v = cfg.vouchers[(state.voucher || '').toUpperCase()];
		if (!v || sub <= 0) { return 0; }
		var d = v.type === 'percent' ? sub * v.value / 100 : v.value;
		return Math.round(Math.min(d, sub) * 100) / 100;
	}

	/* Step 1: hourly pills ----------------------------------------------- */
	function renderHourly() {
		$$('[data-hourly]').forEach(function (g) {
			var key = g.getAttribute('data-hourly');
			$$('.bn-co-pill', g).forEach(function (b) {
				var v = key === 'materials' ? (state.hourly.materials === null ? null : (state.hourly.materials ? '1' : '0')) : String(state.hourly[key]);
				b.classList.toggle('is-active', b.getAttribute('data-value') === v);
				b.setAttribute('aria-pressed', b.classList.contains('is-active') ? 'true' : 'false');
			});
		});
	}
	$$('[data-hourly]').forEach(function (g) {
		var key = g.getAttribute('data-hourly');
		g.addEventListener('click', function (e) {
			var b = e.target.closest('.bn-co-pill');
			if (!b) { return; }
			var v = +b.getAttribute('data-value');
			state.hourly[key] = key === 'materials' ? v === 1 : v;
			renderHourly();
			update();
		});
	});

	/* Step 1: option list ------------------------------------------------ */
	function renderItems() {
		$$('[data-item]').forEach(function (el) {
			var q = state.cart[el.getAttribute('data-item')] || 0;
			el.classList.toggle('is-selected', q > 0);
			$('[data-qty]', el).textContent = q;
			$('[data-qty-x]', el).textContent = q ? q + 'x ' : '';
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

	var tabs = $$('[data-tab]');
	tabs.forEach(function (t) {
		t.addEventListener('click', function (e) {
			e.preventDefault();
			var g = $('[data-group="' + t.getAttribute('data-tab') + '"]');
			if (g) { window.scrollTo({ top: g.getBoundingClientRect().top + window.scrollY - 150, behavior: 'smooth' }); }
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

	var search = $('[data-opt-search]');
	// The search box opens from the round button next to the title (Justlife style).
	var searchOpen = $('[data-search-open]');
	if (search && searchOpen) {
		searchOpen.addEventListener('click', function () {
			var box = $('[data-search-box]');
			box.hidden = !box.hidden;
			searchOpen.classList.toggle('is-active', !box.hidden);
			if (!box.hidden) { search.focus(); return; }
			search.value = '';
			search.dispatchEvent(new Event('input'));
		});
	}
	if (search) {
		search.addEventListener('input', function () {
			var q = search.value.trim().toLowerCase();
			var any = false;
			$$('[data-group]').forEach(function (g) {
				var shown = 0;
				$$('[data-item]', g).forEach(function (el) {
					var hit = !q || el.textContent.toLowerCase().indexOf(q) !== -1;
					el.hidden = !hit;
					if (hit) { shown++; }
				});
				g.hidden = !shown;
				if (shown) { any = true; }
			});
			$('[data-opt-none]').hidden = any;
			var tabsNav = $('[data-tabs]');
			if (tabsNav) { tabsNav.hidden = !!q; }
		});
	}

	/* Step 2: frequency, days, times ------------------------------------- */
	var DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
	var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	var SLOTS = cfg.slots || { start: 8, end: 20, lead: 2, days: 14 };
	var LEAD_MIN = SLOTS.lead * 60; // Same-day notice from Settings → Booking.
	function iso(d) { return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2); }
	function pretty(isoDate) {
		if (!isoDate) { return ''; }
		var p = isoDate.split('-');
		var d = new Date(+p[0], +p[1] - 1, +p[2]);
		var today = iso(new Date());
		var tmr = new Date(); tmr.setDate(tmr.getDate() + 1);
		if (isoDate === today) { return 'Today, ' + d.getDate() + ' ' + MONTHS[d.getMonth()]; }
		if (isoDate === iso(tmr)) { return 'Tomorrow, ' + d.getDate() + ' ' + MONTHS[d.getMonth()]; }
		return DAYS[d.getDay()] + ', ' + d.getDate() + ' ' + MONTHS[d.getMonth()];
	}
	// Arrival windows of Settings → Booking → "Length of each time slot" minutes.
	function hhmm(m) { return ('0' + Math.floor(m / 60)).slice(-2) + ':' + ('0' + (m % 60)).slice(-2); }
	function clock(m) {
		var h = Math.floor(m / 60), mm = m % 60;
		return (h % 12 || 12) + (mm ? ':' + ('0' + mm).slice(-2) : '') + ((h >= 12 && h < 24) ? 'PM' : 'AM');
	}
	function slots() {
		var out = [];
		var step = SLOTS.step || 60;
		for (var m = SLOTS.start * 60; m + step <= SLOTS.end * 60; m += step) {
			out.push({
				start: m,
				value: hhmm(m) + '-' + hhmm(m + step),
				label: clock(m) + ' – ' + clock(m + step),
				part: m < 720 ? 'Morning' : (m < 960 ? 'Afternoon' : 'Evening')
			});
		}
		return out;
	}
	function slotOpen(date, s) {
		var now = new Date();
		return date !== iso(now) || s.start >= now.getHours() * 60 + now.getMinutes() + LEAD_MIN;
	}
	function dayHasSlots(date) { return slots().some(function (s) { return slotOpen(date, s); }); }
	function timeLabel(value) {
		var s = slots().filter(function (x) { return x.value === value; })[0];
		return s ? s.label : value;
	}

	(function buildDays() {
		var wrap = $('[data-days]');
		var today = new Date();
		for (var i = 0; i < SLOTS.days; i++) {
			var d = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i);
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'bn-co-day';
			b.setAttribute('data-value', iso(d));
			b.innerHTML = '<small>' + (i === 0 ? 'Today' : (i === 1 ? 'Tmrw' : DAYS[d.getDay()])) + '</small><strong>' + d.getDate() + '</strong><small>' + MONTHS[d.getMonth()] + '</small>';
			if (!dayHasSlots(iso(d))) { b.disabled = true; b.title = 'Fully booked'; }
			wrap.appendChild(b);
		}
		wrap.addEventListener('click', function (e) {
			var b = e.target.closest('.bn-co-day');
			if (!b || b.disabled) { return; }
			state.date = b.getAttribute('data-value');
			if (state.time && !slots().some(function (s) { return s.value === state.time && slotOpen(state.date, s); })) { state.time = ''; }
			renderStep2();
			update();
		});
	})();

	function renderTimes() {
		var wrap = $('[data-times]');
		var parts = {};
		slots().forEach(function (s) { (parts[s.part] = parts[s.part] || []).push(s); });
		wrap.innerHTML = Object.keys(parts).map(function (p) {
			var open = parts[p].filter(function (s) { return slotOpen(state.date, s); });
			if (!open.length) { return ''; }
			return '<div class="bn-co-part"><h3>' + p + '</h3><div class="bn-co-times">' + parts[p].map(function (s) {
				var ok = slotOpen(state.date, s);
				return '<button type="button" class="bn-co-time' + (state.time === s.value ? ' is-active' : '') + '" data-value="' + s.value + '"' + (ok ? '' : ' disabled') + '>' + s.label + '</button>';
			}).join('') + '</div></div>';
		}).join('');
	}
	$('[data-times]').addEventListener('click', function (e) {
		var b = e.target.closest('.bn-co-time');
		if (!b || b.disabled) { return; }
		state.time = b.getAttribute('data-value');
		clearError('time');
		renderStep2();
		update();
	});
	$$('[data-freq] .bn-co-choice').forEach(function (b) {
		b.addEventListener('click', function () { state.frequency = b.getAttribute('data-value'); renderStep2(); update(); });
	});
	function renderStep2() {
		// Pick the first day with free slots so customers only need to choose a time.
		if (!state.date || !dayHasSlots(state.date) || state.date < iso(new Date())) {
			var first = $$('.bn-co-day').filter(function (b) { return !b.disabled; })[0];
			state.date = first ? first.getAttribute('data-value') : '';
		}
		// Drop a saved slot that no longer exists or has passed.
		if (state.time && !slots().some(function (s) { return s.value === state.time && slotOpen(state.date, s); })) { state.time = ''; }
		$$('[data-freq] .bn-co-choice').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-value') === state.frequency); });
		$$('.bn-co-day').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-value') === state.date); });
		renderTimes();
	}

	/* Step 3: fields ----------------------------------------------------- */
	$$('[data-field]').forEach(function (el) {
		var k = el.getAttribute('data-field');
		if (state[k]) { el.value = state[k]; }
		var sync = function () { state[k] = el.value; clearError(k); update(); };
		el.addEventListener('input', sync);
		el.addEventListener('change', sync);
	});
	if (state.voucher) { var vb = $('[data-voucher-box]'); if (vb) { vb.open = true; } }

	function renderCities() {
		$$('[data-cities] .bn-co-choice').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-value') === state.area); });
	}
	var areaSelect = $('[data-area]');
	if (areaSelect) {
		areaSelect.addEventListener('change', function () { state.area = areaSelect.value; renderCities(); update(); });
	}
	$$('[data-cities] .bn-co-choice').forEach(function (b) {
		b.addEventListener('click', function () { state.area = b.getAttribute('data-value'); renderCities(); update(); });
	});
	$$('[name="bn_pay"]').forEach(function (r) {
		r.checked = r.value === state.payment;
		r.addEventListener('change', function () { if (r.checked) { state.payment = r.value; update(); } });
	});

	/* Voucher */
	var vInput = $('[data-voucher-input]');
	var vMsg = $('[data-voucher-msg]');
	vInput.value = state.voucher;
	function applyVoucher() {
		var code = vInput.value.trim().toUpperCase();
		if (!code) { state.voucher = ''; vMsg.textContent = ''; update(); return; }
		var done = function (ok) {
			state.voucher = ok ? code : '';
			vMsg.className = 'bn-co-voucher-msg ' + (ok ? 'is-ok' : 'is-bad');
			vMsg.textContent = ok ? 'Voucher ' + code + ' applied.' : 'This voucher code is not valid.';
			update();
		};
		if (cfg.vouchers[code]) { done(true); return; }
		// Codes are checked on the server so the full list never reaches the browser.
		var body = new FormData();
		body.append('action', 'bn_voucher');
		body.append('nonce', cfg.nonce);
		body.append('code', code);
		vMsg.className = 'bn-co-voucher-msg';
		vMsg.textContent = 'Checking…';
		fetch(cfg.ajax, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res && res.success) { cfg.vouchers[res.data.code] = res.data.voucher; }
				done(!!(res && res.success));
			})
			.catch(function () { done(false); });
	}
	$('[data-voucher-apply]').addEventListener('click', applyVoucher);
	vInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); applyVoucher(); } });

	/* Errors ------------------------------------------------------------- */
	function showError(key, msg) {
		var el = $('[data-err="' + key + '"]');
		if (el) { el.textContent = msg; }
		var wrap = $('[data-wrap="' + key + '"]');
		if (wrap) { wrap.classList.add('has-error'); }
	}
	function clearError(key) {
		var el = $('[data-err="' + key + '"]');
		if (el) { el.textContent = ''; }
		var wrap = $('[data-wrap="' + key + '"]');
		if (wrap) { wrap.classList.remove('has-error'); }
	}
	/* Returns a list of {key, msg} problems for a step. */
	function problems(n) {
		var out = [];
		if (!cfg.service.active || !areaInfo().on) {
			out.push({ key: 'bar', msg: $('[data-area-off]').textContent || 'This service is not available in the selected area.' });
			return out;
		}
		if (n === LAST && totals().total < cfg.min) {
			out.push({ key: 'bar', msg: 'The minimum booking amount is ' + money(cfg.min) + '.' });
		}
		if (n === 1) {
			if (hourly) {
				if (!state.hourly.hours) { out.push({ key: 'bar', msg: 'Choose how many hours you need.' }); }
				else if (!state.hourly.pros) { out.push({ key: 'bar', msg: 'Choose how many professionals you need.' }); }
				else if (state.hourly.materials === null) { out.push({ key: 'bar', msg: 'Tell us if you need cleaning materials.' }); }
			} else if (!cartCount()) {
				out.push({ key: 'bar', msg: 'Tap "Add" on at least one option.' });
			}
		}
		if (n === 2 && (!state.date || !state.time)) { out.push({ key: 'time', msg: 'Pick a time slot that suits you.' }); }
		if (n === 3) {
			if (state.street.trim().length < 3) { out.push({ key: 'street', msg: 'Please enter your area or street.' }); }
			if (!state.building.trim()) { out.push({ key: 'building', msg: 'Please enter the building or villa name.' }); }
			if (!state.apartment.trim()) { out.push({ key: 'apartment', msg: 'Please enter the apartment or villa number.' }); }
		}
		if (n === 4) {
			if (!state.name.trim()) { out.push({ key: 'name', msg: 'Please enter your name.' }); }
			if (state.phone.replace(/\D/g, '').length < 7) { out.push({ key: 'phone', msg: 'Please enter a valid mobile number.' }); }
			if (state.email && !/^\S+@\S+\.\S+$/.test(state.email)) { out.push({ key: 'email', msg: 'This email does not look right.' }); }
		}
		return out;
	}
	var barError = $('[data-error]');

	/* Steps -------------------------------------------------------------- */
	var nextBtn = $('[data-next]');

	function goTo(n, push) {
		for (var i = 1; i < n; i++) { if (problems(i).length) { n = i; break; } }
		state.step = n;
		root.classList.toggle('is-last-step', n === LAST);
		$$('[data-step]').forEach(function (s) { s.hidden = s.getAttribute('data-step') !== String(n); });
		$('[data-step-num]').textContent = n;
		$('[data-step-title]').textContent = STEP_TITLES[n] || '';
		$('[data-progress]').style.width = (n / LAST * 100) + '%';
		barError.textContent = '';
		if (n === 2) { renderStep2(); }
		if (n === 3) { renderCities(); }
		if (n === 4) { renderRecap(); }
		var url = new URL(window.location.href);
		url.searchParams.set('step', n);
		if (push) { history.pushState({ step: n }, '', url); } else { history.replaceState({ step: n }, '', url); }
		update();
		window.scrollTo({ top: 0, behavior: 'smooth' });
	}
	window.addEventListener('popstate', function (e) {
		goTo((e.state && e.state.step) || +new URL(window.location.href).searchParams.get('step') || 1, false);
	});
	$('[data-back]').addEventListener('click', function () {
		if (state.step > 1) { goTo(state.step - 1, true); } else { window.location.href = cfg.service.url; }
	});

	nextBtn.addEventListener('click', function () {
		var list = problems(state.step);
		if (list.length) {
			barError.textContent = '';
			list.forEach(function (p) { if (p.key === 'bar') { barError.textContent = p.msg; } else { showError(p.key, p.msg); } });
			var first = list[0].key;
			var target = first === 'bar' ? null : ($('[data-field="' + first + '"]') || $('[data-times-block]'));
			if (target) {
				target.scrollIntoView({ behavior: 'smooth', block: 'center' });
				if (target.focus) { setTimeout(function () { target.focus({ preventScroll: true }); }, 300); }
			}
			nextBtn.classList.remove('bn-shake'); void nextBtn.offsetWidth; nextBtn.classList.add('bn-shake');
			return;
		}
		if (state.step < LAST) { goTo(state.step + 1, true); return; }
		submit();
	});

	/* Recap at the top of step 4 ---------------------------------------- */
	function renderRecap() {
		var lines = lineItems().map(function (l) { return esc(l[0]); }).join(', ');
		var when = pretty(state.date) + ' · ' + timeLabel(state.time) + (cfg.service.freqs.length > 1 && state.frequency !== cfg.service.freqs[0][0] ? ' · ' + state.frequency : '');
		$('[data-recap]').innerHTML =
			'<div><span class="bn-co-recap-k">' + esc(cfg.service.title) + '</span><span class="bn-co-recap-v">' + lines + '</span><button type="button" class="bn-link" data-edit="1">Change</button></div>' +
			'<div><span class="bn-co-recap-k">When</span><span class="bn-co-recap-v">' + esc(when) + '</span><button type="button" class="bn-link" data-edit="2">Change</button></div>' +
			'<div><span class="bn-co-recap-k">Address</span><span class="bn-co-recap-v">' + esc(addrText()) + '</span><button type="button" class="bn-link" data-edit="3">Change</button></div>';
		$$('[data-edit]').forEach(function (b) { b.addEventListener('click', function () { goTo(+b.getAttribute('data-edit'), true); }); });
	}

	/* Summary + bar ------------------------------------------------------ */
	function addrText() { return [state.apartment, state.building, state.street, state.area].filter(function (x) { return x && x.trim(); }).join(', '); }
	function update() {
		var t = totals();
		var sub = t.sub;
		var disc = t.disc;
		var total = t.total;
		var lines = lineItems();
		var count = cartCount();

		var freqEl = $('[data-sum="freq"]');
		if (freqEl) { freqEl.textContent = state.frequency; }
		$('[data-sum-row="fdisc"]').hidden = $('[data-sum="fdisc"]').hidden = !t.fdisc;
		$('[data-sum="fdisc"]').textContent = '- ' + money(t.fdisc) + ' (' + freqPct() + '%)';
		$('[data-sum-row="afee"]').hidden = $('[data-sum="afee"]').hidden = !t.afee;
		$('[data-sum="afee"]').textContent = money(t.afee);
		$('[data-sum="aname"]').textContent = t.afee ? '(' + state.area + ')' : '';
		var areaSel = $('[data-area]');
		if (areaSel) { areaSel.value = state.area; }
		var areaName = $('[data-co-area-name]');
		if (areaName) { areaName.textContent = state.area; }
		var off = $('[data-area-off]');
		off.hidden = cfg.service.active && areaInfo().on;
		off.textContent = !cfg.service.active ? 'Sorry, this service is not available right now.' : 'Sorry, ' + cfg.service.title + ' is not available in ' + state.area + ' yet. Please choose another area.';
		renderPrices();
		$$('[data-lines]').forEach(function (el) { el.hidden = !lines.length; });
		// Like Justlife: "1x Haircut & Massage", the money is in the payment summary.
		$('[data-lines-list]').innerHTML = lines.map(function (l) { return '<li>' + esc(String(l[0]).replace(/^(\d+) × /, '$1x ')) + '</li>'; }).join('');

		var hasDate = !!(state.date && state.time);
		$('[data-sum-row="date"]').hidden = $('[data-sum="date"]').hidden = !hasDate;
		$('[data-sum="date"]').textContent = hasDate ? pretty(state.date) + ', ' + timeLabel(state.time) : '';
		var addr = addrText();
		// The chosen map location shows from step 1; the typed address replaces it on step 3.
		var addrShow = state.street ? addr : state.address;
		$('[data-sum-row="addr"]').hidden = $('[data-sum="addr"]').hidden = !addrShow;
		$('[data-sum="addr"]').textContent = addrShow;
		$('[data-addr-label]').textContent = state.lat ? (state.address || 'Location pinned') + ' (change)' : 'Pin your location on the map';
		var pin = $('[data-open-map]');
		if (pin) { pin.classList.toggle('is-set', !!state.lat); }

		$('[data-sum="sub"]').textContent = money(sub);
		$('[data-sum-row="disc"]').hidden = $('[data-sum="disc"]').hidden = !disc;
		$('[data-sum="disc"]').textContent = '- ' + money(disc);
		$('[data-sum="code"]').textContent = disc ? '(' + state.voucher.toUpperCase() + ')' : '';
		$('[data-sum="total"]').textContent = money(total);
		$('[data-total-mini]').textContent = money(total);
		$('[data-count-mini]').textContent = hourly ? 'Total' : (count ? count + (count > 1 ? ' items' : ' item') + ' · Total' : 'Total');

		var hint = $('[data-voucher-hint]');
		var hasVoucher = !!cfg.vouchers[(state.voucher || '').toUpperCase()];
		hint.hidden = !(hasVoucher && !sub && state.step === 1);
		$('[data-voucher-code]').textContent = (state.voucher || '').toUpperCase();

		var ready = !problems(state.step).length;
		nextBtn.classList.toggle('is-waiting', !ready);
		nextBtn.textContent = state.step === LAST ? (state.payment === cfg.online ? 'Pay securely' : 'Confirm booking') + (total ? ' · ' + money(total) : '') : 'Continue';
		if (ready) { barError.textContent = ''; }
		save();
	}
	/* Location pill (Justlife style): choosing a location sets the area, address and prices. */
	document.addEventListener('bn:location', function (e) {
		var loc = e.detail || {};
		if (loc.lat) { state.lat = loc.lat; state.lng = loc.lng; state.address = loc.label || state.address; }
		if (loc.street && !String(state.street || '').trim()) {
			state.street = loc.street;
			var st = $('[data-field="street"]');
			if (st) { st.value = loc.street; }
		}
		if (loc.area && cfg.cities.indexOf(loc.area) !== -1) { state.area = loc.area; }
		if (typeof renderCities === 'function') { renderCities(); }
		if (loc.label) { document.querySelectorAll('[data-loc-label]').forEach(function (el) { el.textContent = loc.label; }); }
		update();
	});

	$('[data-summary-toggle]').addEventListener('click', function () { $('[data-side]').scrollIntoView({ behavior: 'smooth', block: 'start' }); });

	/* Map (optional) ----------------------------------------------------- */
	var modal = $('[data-map-modal]');
	var map = null;
	function openMap() {
		modal.hidden = false;
		document.body.classList.add('bn-modal-open');
		if (!window.L) { return; }
		if (!map) {
			map = L.map($('[data-map]'), { zoomControl: false }).setView(state.lat ? [state.lat, state.lng] : [cfg.map.lat, cfg.map.lng], 15);
			L.control.zoom({ position: 'bottomright' }).addTo(map);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
			map.on('movestart', function () { modal.classList.add('is-moving'); });
			map.on('moveend', function () { modal.classList.remove('is-moving'); });
		}
		setTimeout(function () { map.invalidateSize(); }, 50);
	}
	function closeMap() { modal.hidden = true; document.body.classList.remove('bn-modal-open'); }
	$$('[data-open-map]').forEach(function (b) { b.addEventListener('click', openMap); });
	$('[data-map-close]').addEventListener('click', closeMap);
	modal.addEventListener('click', function (e) { if (e.target === modal) { closeMap(); } });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) { closeMap(); } });
	$('[data-map-locate]').addEventListener('click', function () {
		if (!navigator.geolocation || !map) { return; }
		navigator.geolocation.getCurrentPosition(function (p) { map.setView([p.coords.latitude, p.coords.longitude], 17); });
	});
	var results = $('[data-map-results]');
	var searchTimer;
	function geosearch(q) {
		var c = map ? map.getCenter() : { lat: cfg.map.lat, lng: cfg.map.lng };
		fetch('https://nominatim.openstreetmap.org/search?format=json&limit=5&addressdetails=1' + (cfg.country ? '&countrycodes=' + cfg.country : '') + '&q=' + encodeURIComponent(q) + '&viewbox=' + (c.lng - 1) + ',' + (c.lat + 1) + ',' + (c.lng + 1) + ',' + (c.lat - 1), { headers: { 'Accept-Language': 'en' } })
			.then(function (r) { return r.json(); })
			.then(function (list) {
				results.innerHTML = '';
				list.forEach(function (it) {
					var li = document.createElement('li');
					var b = document.createElement('button');
					b.type = 'button';
					b.textContent = it.display_name;
					b.addEventListener('click', function () { map.setView([+it.lat, +it.lon], 17); results.hidden = true; });
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
	$('[data-map-search]').addEventListener('submit', function (e) { e.preventDefault(); var q = $('[data-map-query]').value.trim(); if (q.length >= 3) { geosearch(q); } });
	$('[data-map-confirm]').addEventListener('click', function () {
		var btn = this;
		var label = btn.textContent;
		var c = map ? map.getCenter() : { lat: cfg.map.lat, lng: cfg.map.lng };
		var lat = Math.round(c.lat * 1e6) / 1e6;
		var lng = Math.round(c.lng * 1e6) / 1e6;
		var pin = function (address) {
			state.lat = lat;
			state.lng = lng;
			state.address = address || 'Location pinned';
			closeMap();
			renderCities();
			update();
		};
		btn.disabled = true;
		btn.textContent = 'Checking…';
		fetch('https://nominatim.openstreetmap.org/reverse?format=json&zoom=18&lat=' + lat + '&lon=' + lng, { headers: { 'Accept-Language': 'en' } })
			.then(function (r) { return r.json(); })
			.then(function (d) {
				var a = d && d.address;
				var area = window.bnMatchCity ? window.bnMatchCity(a, cfg.cities, cfg.country) : '';
				if (!area) {
					// Outside our cities (or another country): keep the map open.
					if (window.bnAlert) { window.bnAlert('Error', window.BN_AREA_ERROR); }
					return;
				}
				state.area = area;
				if (!state.street.trim()) {
					state.street = [a.road, a.neighbourhood || a.suburb].filter(Boolean).join(', ');
					$('[data-field="street"]').value = state.street;
					clearError('street');
				}
				if (!state.building.trim() && (a.building || a.house_name || a.amenity)) {
					state.building = a.building || a.house_name || a.amenity;
					$('[data-field="building"]').value = state.building;
					clearError('building');
				}
				pin(d.display_name.split(',').slice(0, 2).join(','));
			})
			.catch(function () { if (window.bnAlert) { window.bnAlert('Error', window.BN_AREA_CHECK_ERROR); } })
			.then(function () { btn.disabled = false; btn.textContent = label; });
	});

	/* Submit ------------------------------------------------------------- */
	function submit() {
		nextBtn.disabled = true;
		nextBtn.textContent = 'Booking…';
		var body = new FormData();
		body.append('action', 'bn_checkout');
		body.append('nonce', cfg.nonce);
		body.append('service', cfg.service.id);
		body.append('cart', JSON.stringify(hourly ? state.hourly : state.cart));
		['frequency', 'date', 'time', 'message', 'area', 'street', 'building', 'apartment', 'name', 'phone', 'email', 'payment', 'voucher'].forEach(function (k) { body.append(k, state[k] || ''); });
		body.append('address', state.lat ? state.address : '');
		body.append('lat', state.lat || '');
		body.append('lng', state.lng || '');

		fetch(cfg.ajax, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (!res || !res.success) { throw new Error((res && res.data && res.data.message) || 'Something went wrong. Please try again.'); }
				rememberCustomer();
				if (res.data.redirect) {
					// Online payment: continue on the secure Ziina payment page.
					nextBtn.textContent = 'Redirecting to secure payment…';
					window.location.href = res.data.redirect;
					return;
				}
				var total = money(res.data.total);
				$('[data-ref]').textContent = '#' + res.data.ref;
				$('[data-done-sum]').innerHTML = [
					['Service', cfg.service.title],
					['When', pretty(state.date) + ' · ' + timeLabel(state.time)],
					['Address', addrText()],
					['Total', total + ' (' + state.payment.toLowerCase() + ')']
				].map(function (r) { return '<div><dt>' + r[0] + '</dt><dd>' + esc(r[1]) + '</dd></div>'; }).join('');
				if (cfg.whatsapp) {
					var wa = $('[data-done-wa]');
					var msg = 'Hi, I just booked ' + cfg.service.title + ' (#' + res.data.ref + ') for ' + pretty(state.date) + ', ' + timeLabel(state.time) + '.';
					wa.href = cfg.whatsapp + (cfg.whatsapp.indexOf('?') === -1 ? '?' : '&') + 'text=' + encodeURIComponent(msg);
					wa.hidden = false;
				}
				$$('[data-step]').forEach(function (s) { s.hidden = s.getAttribute('data-step') !== 'done'; });
				$('[data-bar]').hidden = true;
				$('.bn-co-top', document).hidden = true;
				root.classList.add('is-done');
				try { sessionStorage.removeItem(STORE_KEY); } catch (e) { /* ignore */ }
				window.scrollTo({ top: 0, behavior: 'smooth' });
			})
			.catch(function (err) {
				barError.textContent = err.message;
				nextBtn.disabled = false;
				update();
			});
	}

	function rememberCustomer() {
		try {
			localStorage.setItem(CUSTOMER_KEY, JSON.stringify({ name: state.name, phone: state.phone, email: state.email, street: state.street, building: state.building, apartment: state.apartment, area: state.area, lat: state.lat, lng: state.lng, address: state.address }));
		} catch (e) { /* ignore */ }
	}

	/* Back from the Ziina payment page ----------------------------------- */
	function showPaid(p) {
		$('[data-ref]').textContent = '#' + p.ref;
		$('[data-done-sum]').innerHTML = '<div><dt>Service</dt><dd>' + esc(cfg.service.title) + '</dd></div>' +
			'<div><dt>Total</dt><dd>' + esc(money(p.total)) + ' (paid online)</dd></div>';
		$$('[data-step]').forEach(function (s) { s.hidden = s.getAttribute('data-step') !== 'done'; });
		$('[data-bar]').hidden = true;
		$('.bn-co-top', document).hidden = true;
		root.classList.add('is-done');
		try { sessionStorage.removeItem(STORE_KEY); } catch (e) { /* ignore */ }
	}

	/* Init --------------------------------------------------------------- */
	renderItems();
	renderHourly();
	renderCities();
	renderStep2();
	if (cfg.paid && cfg.paid.paid) {
		showPaid(cfg.paid);
	} else {
		goTo(Math.min(LAST, Math.max(1, +new URL(window.location.href).searchParams.get('step') || 1)), false);
		if (cfg.paid) {
			barError.textContent = 'Payment for booking #' + cfg.paid.ref + ' was not completed. Please try again or choose another payment method.';
		}
	}
	// Like Justlife: no saved location yet → ask for it first (prices depend on the area).
	if (!readJSON(localStorage, 'bn_location') && state.step === 1) {
		setTimeout(function () {
			var pill = document.querySelector('[data-loc-open]');
			if (pill && document.querySelector('[data-loc-modal]')) { pill.click(); }
		}, 700);
	}
})();
