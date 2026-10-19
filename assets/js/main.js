/* BrightNest front-end interactions. */
(function () {
	'use strict';

	var doc = document.documentElement;
	doc.classList.add('bn-js');

	/* Sticky header shadow */
	var header = document.getElementById('bn-header');
	function onScroll() {
		if (header) {
			header.classList.toggle('is-scrolled', window.scrollY > 8);
		}
	}
	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	/* Mobile navigation */
	var toggle = document.querySelector('.bn-nav-toggle');
	var nav = document.getElementById('bn-nav');
	function setNav(open) {
		document.body.classList.toggle('bn-nav-open', open);
		if (toggle) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
	}
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			setNav(!document.body.classList.contains('bn-nav-open'));
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { setNav(false); }
		});
		document.addEventListener('click', function (e) {
			if (document.body.classList.contains('bn-nav-open') && !nav.contains(e.target) && !toggle.contains(e.target)) {
				setNav(false);
			}
		});
		nav.addEventListener('click', function (e) {
			if (e.target.closest('a')) { setNav(false); }
		});
	}

	/* Carousels */
	document.querySelectorAll('[data-carousel]').forEach(function (carousel) {
		var track = carousel.querySelector('[data-track]');
		var prev = carousel.querySelector('[data-prev]');
		var next = carousel.querySelector('[data-next]');
		if (!track) { return; }

		function step() {
			var item = track.querySelector('.bn-carousel-item');
			if (!item) { return track.clientWidth; }
			var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
			var perView = Math.max(1, Math.floor((track.clientWidth + gap) / (item.offsetWidth + gap)));
			return perView * (item.offsetWidth + gap);
		}
		function update() {
			var max = track.scrollWidth - track.clientWidth - 2;
			if (prev) { prev.disabled = track.scrollLeft <= 2; }
			if (next) { next.disabled = track.scrollLeft >= max; }
		}
		if (prev) { prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); }); }
		if (next) { next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); }); }
		track.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update);
		update();
	});

	/* Reveal on scroll */
	var targets = document.querySelectorAll('.bn-pm-head, .bn-pm-feature, .bn-pm-cards .bn-sc, .bn-pm-why-art, .bn-pm-points li, .bn-pm-numbers li, .bn-pm-steps li, .bn-pm-voice-stage, .bn-pm-cta-inner, .bn-sv-sec, .bn-ar-grid .bn-sc, .bn-sv-related .bn-sc, .bn-carousel, .bn-grid > .bn-card');
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					var el = entry.target;
					el.classList.add('is-visible');
					io.unobserve(el);
					// Drop reveal styles afterwards so hover transitions work normally.
					setTimeout(function () {
						el.classList.remove('bn-reveal', 'is-visible');
						el.style.transitionDelay = '';
					}, 1000);
				}
			});
		}, { rootMargin: '0px 0px -40px 0px' });
		targets.forEach(function (el, i) {
			el.classList.add('bn-reveal');
			el.style.transitionDelay = (i % 5) * 60 + 'ms';
			io.observe(el);
		});
	}

	/* Homepage service filter pills */
	var filter = document.querySelector('[data-svc-filter]');
	var grid = document.querySelector('[data-svc-grid]');
	if (filter && grid) {
		filter.addEventListener('click', function (e) {
			var b = e.target.closest('button');
			if (!b) { return; }
			var cat = b.getAttribute('data-cat');
			filter.querySelectorAll('button').forEach(function (x) {
				var on = x === b;
				x.classList.toggle('is-active', on);
				x.setAttribute('aria-selected', on ? 'true' : 'false');
			});
			grid.querySelectorAll('.bn-svc-card').forEach(function (card) {
				card.hidden = !!cat && card.getAttribute('data-cat') !== cat;
			});
		});
	}

	/* Hero: rotate the highlighted city name */
	var city = document.querySelector('[data-rotate]');
	if (city && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		var names = [];
		try { names = JSON.parse(city.getAttribute('data-rotate')) || []; } catch (e) { /* ignore */ }
		var n = 0;
		if (names.length > 1) {
			setInterval(function () {
				city.classList.add('is-swap');
				setTimeout(function () {
					n = (n + 1) % names.length;
					city.textContent = names[n];
					city.classList.remove('is-swap');
				}, 300);
			}, 2800);
		}
	}

	/* Service page: price tabs */
	var ptabs = document.querySelector('[data-price-tabs]');
	if (ptabs) {
		ptabs.addEventListener('click', function (e) {
			var b = e.target.closest('button');
			if (!b) { return; }
			var key = b.getAttribute('data-tab');
			ptabs.querySelectorAll('button').forEach(function (x) {
				var on = x === b;
				x.classList.toggle('is-active', on);
				x.setAttribute('aria-selected', on ? 'true' : 'false');
			});
			document.querySelectorAll('.bn-sv-pkgs[data-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== key; });
		});
	}

	/* Service page: highlight the sub-nav link of the section in view */
	var subnav = document.querySelector('[data-subnav]');
	if (subnav && 'IntersectionObserver' in window) {
		var links = subnav.querySelectorAll('a[href^="#"]');
		var spy = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (!en.isIntersecting) { return; }
				links.forEach(function (l) { l.classList.toggle('is-active', l.getAttribute('href') === '#' + en.target.id); });
			});
		}, { rootMargin: '-160px 0px -60% 0px' });
		links.forEach(function (l) {
			var sec = document.querySelector(l.getAttribute('href'));
			if (sec) { spy.observe(sec); }
		});
	}

	/* Homepage: service tabs */
	var pmTabs = document.querySelector('[data-pm-tabs]');
	if (pmTabs) {
		pmTabs.addEventListener('click', function (e) {
			var b = e.target.closest('button');
			if (!b) { return; }
			var key = b.getAttribute('data-tab');
			pmTabs.querySelectorAll('button').forEach(function (x) {
				var on = x === b;
				x.classList.toggle('is-active', on);
				x.setAttribute('aria-selected', on ? 'true' : 'false');
			});
			document.querySelectorAll('.bn-pm-panel').forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== key; });
		});
	}

	/* Homepage: testimonial spotlight */
	var stage = document.querySelector('[data-voices]');
	if (stage) {
		var voices = stage.querySelectorAll('.bn-pm-voice');
		var counter = document.querySelector('[data-voice-count]');
		var cur = 0, timer;
		var show = function (i) {
			cur = (i + voices.length) % voices.length;
			voices.forEach(function (v, k) { v.hidden = k !== cur; });
			if (counter) { counter.textContent = (cur + 1) + ' / ' + voices.length; }
		};
		var auto = function () {
			clearInterval(timer);
			if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) { timer = setInterval(function () { show(cur + 1); }, 7000); }
		};
		var prev = document.querySelector('[data-voice-prev]');
		var next = document.querySelector('[data-voice-next]');
		if (prev) { prev.addEventListener('click', function () { show(cur - 1); auto(); }); }
		if (next) { next.addEventListener('click', function () { show(cur + 1); auto(); }); }
		if (voices.length > 1) { auto(); }
	}

	/* Scroll to form notice after submit */
	if (window.location.search.indexOf('bn_status=') !== -1) {
		var form = document.getElementById('bn-form');
		if (form) {
			var details = form.closest('details');
			if (details) { details.open = true; }
			form.scrollIntoView({ block: 'center' });
		}
	}

	/* Small alert dialog (e.g. "We do not currently operate in this area.") */
	window.bnAlert = function (title, message) {
		var old = document.querySelector('.bn-alert');
		if (old) { old.remove(); }
		var wrap = document.createElement('div');
		wrap.className = 'bn-alert';
		wrap.setAttribute('role', 'alertdialog');
		wrap.setAttribute('aria-modal', 'true');
		var box = document.createElement('div');
		box.className = 'bn-alert-box';
		var h = document.createElement('h2');
		h.textContent = title;
		var p = document.createElement('p');
		p.textContent = message;
		var ok = document.createElement('button');
		ok.type = 'button';
		ok.className = 'bn-btn bn-btn-primary';
		ok.textContent = 'OK';
		box.appendChild(h);
		box.appendChild(p);
		box.appendChild(ok);
		wrap.appendChild(box);
		document.body.appendChild(wrap);
		var close = function () { wrap.remove(); document.removeEventListener('keydown', onKey); };
		var onKey = function (e) { if (e.key === 'Escape' || e.key === 'Enter') { close(); } };
		ok.addEventListener('click', close);
		wrap.addEventListener('click', function (e) { if (e.target === wrap) { close(); } });
		document.addEventListener('keydown', onKey);
		ok.focus();
	};

	/* Which of our service cities a reverse-geocoded OpenStreetMap address is in ('' = none). */
	window.bnMatchCity = function (address, cities, country) {
		if (!address) { return ''; }
		// Another country is never accepted, even if a city name happens to match.
		if (country && String(address.country_code || '').toLowerCase() !== country) { return ''; }
		var parts = ['city', 'town', 'village', 'municipality', 'county', 'state_district', 'state', 'region'].map(function (k) { return String(address[k] || '').toLowerCase(); }).filter(Boolean);
		var hit = '';
		(cities || []).forEach(function (c) {
			var name = String(c).toLowerCase().trim();
			if (!hit && name && parts.some(function (p) { return p.indexOf(name) !== -1; })) { hit = c; }
		});
		return hit;
	};
	window.BN_AREA_ERROR = 'We do not currently operate in this area.';
	window.BN_AREA_CHECK_ERROR = 'We could not check this location. Please check your connection and try again.';

	/* Services category bar: keep the active category in view on phones */
	var catBar = document.querySelector('.bn-ar-cats');
	var catActive = catBar && catBar.querySelector('.is-active');
	if (catActive && catBar.scrollWidth > catBar.clientWidth) {
		catBar.scrollLeft = catActive.offsetLeft - (catBar.clientWidth - catActive.offsetWidth) / 2;
	}
})();
