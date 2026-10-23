/* Services hub behaviour.
 * Homepage: picking an area (pop-up, hero or header) opens the services hub. The homepage itself
 * always stays reachable (a saved area does not redirect it).
 * Hub: works with or without a location; the search box filters the cards as you type. */
(function () {
	'use strict';

	var cfg = window.BN_HUB;
	if (!cfg || !cfg.hub) { return; }

	if (cfg.front) {
		// Any area pick on the homepage (first-visit pop-up, hero or header) opens the services hub.
		document.addEventListener('bn:location', function () {
			window.location.href = cfg.hub;
		});
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
