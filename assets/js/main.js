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
	var targets = document.querySelectorAll('.bn-section-head, .bn-cat, .bn-feature, .bn-step, .bn-carousel, .bn-cta, .bn-location, .bn-grid > .bn-card');
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

	/* Scroll to form notice after submit */
	if (window.location.search.indexOf('bn_status=') !== -1) {
		var form = document.getElementById('bn-form');
		if (form) {
			var details = form.closest('details');
			if (details) { details.open = true; }
			form.scrollIntoView({ block: 'center' });
		}
	}
})();
