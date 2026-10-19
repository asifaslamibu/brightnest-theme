/* Services hub behaviour.
 * Homepage: once a location is chosen (now or on an earlier visit) the services hub opens.
 * Hub: works with or without a location; the search box filters the cards as you type. */
(function () {
	'use strict';

	var cfg = window.BN_HUB;
	if (!cfg || !cfg.hub) { return; }

	var hasLocation = function () {
		try {
			var loc = JSON.parse(localStorage.getItem('bn_location') || 'null');
			return !!(loc && loc.lat);
		} catch (e) { return false; }
	};

	if (cfg.front) {
		// ?home in the URL keeps the landing page (e.g. to change the location from the homepage).
		var stay = /[?&]home\b/.test(window.location.search);
		if (!stay && hasLocation()) {
			window.location.replace(cfg.hub);
			return;
		}
		document.addEventListener('bn:location', function () { window.location.href = cfg.hub; });
		return;
	}

	/* Live filter on the hub */
	var input = document.querySelector('[data-hub-search]');
	var rows = document.querySelectorAll('[data-hub-row]');
	var empty = document.querySelector('[data-hub-empty]');
	if (!input || !rows.length) { return; }

	input.addEventListener('input', function () {
		var q = input.value.trim().toLowerCase();
		var any = false;
		rows.forEach(function (row) {
			var shown = 0;
			row.querySelectorAll('[data-hub-card]').forEach(function (card) {
				var hit = !q || card.getAttribute('data-name').indexOf(q) !== -1;
				card.hidden = !hit;
				if (hit) { shown++; }
			});
			row.hidden = !shown;
			if (shown) { any = true; }
		});
		if (empty) { empty.hidden = any; }
	});
})();
