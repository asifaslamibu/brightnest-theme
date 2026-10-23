/* Cleaning guide page (page-templates/guide.php). */
/* Article layout ([data-art], the cleaning guide): each H3 with its text becomes a card, "1. …" headings get a number badge,
   "Step 1: …" lines become step cards. The text itself stays as written in the editor. */
(function () {
	var art = document.querySelector('[data-art]');
	if (!art) { return; }
	var kids = Array.prototype.slice.call(art.children);
	var card = null;
	var steps = null;
	kids.forEach(function (el) {
		var tag = el.tagName;
		var strong = tag === 'P' && el.children.length === 1 && el.firstElementChild.tagName === 'STRONG' && el.textContent.trim() === el.firstElementChild.textContent.trim() ? el.textContent.trim() : '';
		var step = strong.match(/^Step\s*(\d+)\s*:\s*(.+)$/i);
		if (tag === 'H2') { card = null; steps = null; return; }
		if (step) {
			card = null;
			if (!steps) { steps = document.createElement('ol'); steps.className = 'bn-art-steps'; art.insertBefore(steps, el); }
			var li = document.createElement('li');
			li.innerHTML = '<span class="bn-art-n">' + step[1] + '</span><div><h4></h4></div>';
			li.querySelector('h4').textContent = step[2];
			steps.appendChild(li);
			el.remove();
			card = li.querySelector('div');
			return;
		}
		if (tag === 'H3') {
			steps = null;
			card = document.createElement('div');
			card.className = 'bn-art-card';
			art.insertBefore(card, el);
			var m = el.textContent.trim().match(/^(\d+)\.\s*(.+)$/);
			if (m) { el.textContent = m[2]; el.insertAdjacentHTML('afterbegin', '<span class="bn-art-n">' + m[1] + '</span>'); card.classList.add('is-num'); }
			card.appendChild(el);
			return;
		}
		if (card) { card.appendChild(el); }
	});
	// Cards that follow each other sit side by side in a grid.
	Array.prototype.slice.call(art.querySelectorAll(':scope > .bn-art-card')).forEach(function (c) {
		var prev = c.previousElementSibling;
		if (prev && prev.classList.contains('bn-art-grid')) { prev.appendChild(c); return; }
		var g = document.createElement('div');
		g.className = 'bn-art-grid';
		art.insertBefore(g, c);
		g.appendChild(c);
	});
	art.querySelectorAll('.bn-art-card').forEach(function (c) { if (c.querySelector('ul, ol')) { c.classList.add('is-wide'); } }); // Lists get the full row.
	art.querySelectorAll('.bn-art-grid').forEach(function (g) {
		if (g.children.length === 1) { g.classList.add('is-one'); }
		// Two per row: a card left alone in its row (before a wide one, or the last) takes the row too.
		var open = null;
		Array.prototype.forEach.call(g.children, function (c) {
			if (c.classList.contains('is-wide')) { if (open) { open.classList.add('is-wide'); } open = null; return; }
			open = open ? null : c;
		});
		if (open) { open.classList.add('is-wide'); }
	});
})();

/* "On this page": mark the section being read; reading bar. */
(function () {
	var links = Array.prototype.slice.call(document.querySelectorAll('[data-toc-list] a'));
	var heads = links.map(function (a) { return document.getElementById(a.getAttribute('href').slice(1)); });
	var list = document.querySelector('[data-toc-list]');
	var toc = document.querySelector('[data-toc]');
	if (toc && window.matchMedia('(max-width: 1024px)').matches) { toc.open = false; }
	var bar = document.querySelector('[data-read-progress]');
	var art = document.querySelector('.bn-guide');
	function tick() {
		var cur = 0;
		heads.forEach(function (h, i) { if (h && h.getBoundingClientRect().top < 160) { cur = i; } });
		links.forEach(function (a, i) { a.classList.toggle('is-current', i === cur); });
		if (list && links[cur] && window.matchMedia('(min-width: 1025px)').matches) {
			var top = links[cur].offsetTop - list.offsetTop;
			if (top < list.scrollTop || top > list.scrollTop + list.clientHeight - 30) { list.scrollTop = top - 40; }
		}
		if (list) { list.classList.toggle('is-end', list.scrollTop + list.clientHeight >= list.scrollHeight - 4); }
		if (bar && art) {
			var r = art.getBoundingClientRect();
			bar.style.transform = 'scaleX(' + Math.min(1, Math.max(0, -r.top / Math.max(1, r.height - window.innerHeight))) + ')';
		}
	}
	if (list) {
		list.addEventListener('scroll', tick, { passive: true });
		list.addEventListener('click', function (e) { if (e.target.closest('a') && toc && window.matchMedia('(max-width: 1024px)').matches) { toc.open = false; } });
	}
	window.addEventListener('scroll', tick, { passive: true });
	window.addEventListener('resize', tick);
	tick();
})();
