/* BrightNest settings: media picker, FAQ list, list editor ("rows"), homepage section order.
   Also used by the service editor FAQ tab. */
(function () {
	'use strict';

	/* Media frame for one photo: calls done(url). */
	function pickPhoto(done) {
		if (!window.wp || !wp.media) { return; }
		var frame = wp.media({ title: 'Choose a photo', button: { text: 'Use this photo' }, library: { type: 'image' }, multiple: false });
		frame.on('select', function () {
			var a = frame.state().get('selection').first().toJSON();
			done(a.sizes && a.sizes.large ? a.sizes.large.url : a.url);
		});
		frame.open();
	}

	/* List editor: rows of columns, written back as "a|b|c" lines into the hidden box the setting saves. */
	document.querySelectorAll('[data-rows]').forEach(function (box) {
		var cols = JSON.parse(box.getAttribute('data-cols'));
		var txt = JSON.parse(box.getAttribute('data-txt'));
		var value = box.querySelector('[data-rows-value]');
		var list = box.querySelector('[data-rows-list]');
		var clean = function (v) { return String(v || '').replace(/[|\r\n]+/g, ' ').trim(); };
		function sync() {
			var lines = [];
			list.querySelectorAll(':scope > li').forEach(function (li, i) {
				li.querySelector('.bn-row-n').textContent = i + 1;
				var cells = cols.map(function (c) { return clean(li.querySelector('[data-col="' + c.key + '"]').value); });
				while (cells.length && cells[cells.length - 1] === '') { cells.pop(); }
				if (cells.length) { lines.push(cells.join('|')); }
			});
			value.value = lines.join('\n');
		}
		function thumb(el, url) { el.innerHTML = url ? '<img src="' + url.replace(/"/g, '&quot;') + '" alt="">' : '<span>' + txt.none + '</span>'; }
		function addRow(cells, focus) {
			var li = document.createElement('li');
			li.className = 'bn-row';
			var head = '<div class="bn-row-head"><span class="bn-row-n"></span>' +
				'<button type="button" class="button-link" data-row-up title="' + txt.up + '"><span class="dashicons dashicons-arrow-up-alt2"></span></button>' +
				'<button type="button" class="button-link" data-row-down title="' + txt.down + '"><span class="dashicons dashicons-arrow-down-alt2"></span></button>' +
				'<button type="button" class="button-link bn-opt-del" data-row-del title="' + txt.del + '"><span class="dashicons dashicons-trash"></span></button></div>';
			var body = '<div class="bn-row-cols bn-row-cols-' + cols.length + '">';
			cols.forEach(function (c) {
				body += '<label class="bn-row-col bn-row-' + c.type + '"><span>' + c.label + '</span>';
				if (c.type === 'textarea') { body += '<textarea rows="3" data-col="' + c.key + '"></textarea>'; }
				else if (c.type === 'image') { body += '<input type="hidden" data-col="' + c.key + '"><span class="bn-row-thumb" data-thumb></span><span class="bn-row-imgbtns"><button type="button" class="button" data-row-pick>' + txt.pick + '</button> <button type="button" class="button-link bn-opt-del" data-row-clear>' + txt.clear + '</button></span>'; }
				else { body += '<input type="text" data-col="' + c.key + '"' + (c.type === 'url' ? ' placeholder="/services/…/ or https://…" dir="ltr"' : '') + '>'; }
				body += '</label>';
			});
			li.innerHTML = head + body + '</div>';
			cols.forEach(function (c, i) {
				var el = li.querySelector('[data-col="' + c.key + '"]');
				el.value = cells[i] || '';
				if (c.type === 'image') { thumb(li.querySelector('[data-thumb]'), el.value); }
			});
			list.appendChild(li);
			if (focus) { var f = li.querySelector('input:not([type=hidden]), textarea'); if (f) { f.focus(); } }
		}
		value.value.split(/\r?\n/).forEach(function (line) {
			if (line.trim() === '') { return; }
			var parts = line.split('|');
			// The last column keeps any extra "|" (e.g. a single-column list).
			if (parts.length > cols.length) { parts = parts.slice(0, cols.length - 1).concat([parts.slice(cols.length - 1).join('|')]); }
			addRow(parts.map(function (p) { return p.trim(); }));
		});
		sync();
		box.querySelector('[data-rows-add]').addEventListener('click', function () { addRow([], true); sync(); });
		list.addEventListener('input', sync);
		list.addEventListener('click', function (e) {
			var li = e.target.closest('li');
			if (!li) { return; }
			if (e.target.closest('[data-row-del]')) { li.remove(); }
			else if (e.target.closest('[data-row-up]') && li.previousElementSibling) { list.insertBefore(li, li.previousElementSibling); }
			else if (e.target.closest('[data-row-down]') && li.nextElementSibling) { list.insertBefore(li.nextElementSibling, li); }
			else if (e.target.closest('[data-row-pick]')) {
				pickPhoto(function (url) { li.querySelector('[data-thumb]').previousElementSibling.value = url; thumb(li.querySelector('[data-thumb]'), url); sync(); });
				return;
			} else if (e.target.closest('[data-row-clear]')) {
				li.querySelector('[data-thumb]').previousElementSibling.value = ''; thumb(li.querySelector('[data-thumb]'), '');
			} else { return; }
			sync();
		});
	});

	/* Homepage sections: drag or arrows to reorder; the switch dims a section that is off. */
	document.querySelectorAll('[data-secs]').forEach(function (list) {
		var dragged = null;
		list.addEventListener('click', function (e) {
			var li = e.target.closest('li');
			if (!li) { return; }
			if (e.target.closest('[data-sec-up]') && li.previousElementSibling) { list.insertBefore(li, li.previousElementSibling); }
			if (e.target.closest('[data-sec-down]') && li.nextElementSibling) { list.insertBefore(li.nextElementSibling, li); }
		});
		list.addEventListener('change', function (e) {
			if (e.target.matches('[data-sec-on]')) { e.target.closest('li').classList.toggle('is-off', !e.target.checked); }
		});
		list.addEventListener('dragstart', function (e) { dragged = e.target.closest('li'); if (dragged) { dragged.classList.add('is-drag'); e.dataTransfer.effectAllowed = 'move'; } });
		list.addEventListener('dragend', function () { if (dragged) { dragged.classList.remove('is-drag'); } dragged = null; });
		list.addEventListener('dragover', function (e) {
			if (!dragged) { return; }
			e.preventDefault();
			var over = e.target.closest('li');
			if (!over || over === dragged) { return; }
			var r = over.getBoundingClientRect();
			list.insertBefore(dragged, e.clientY > r.top + r.height / 2 ? over.nextElementSibling : over);
		});
	});

	/* "Open all" / "Close all" on the Homepage tab. */
	document.querySelectorAll('[data-fold-all]').forEach(function (b) {
		b.addEventListener('click', function () {
			var open = b.getAttribute('data-fold-all') === '1';
			document.querySelectorAll('.bn-set-fold').forEach(function (d) { d.open = open; });
		});
	});

	/* Image fields */
	document.querySelectorAll('[data-image]').forEach(function (box) {
		var input = box.querySelector('[data-url]');
		var thumb = box.querySelector('[data-thumb]');
		var frame;
		box.querySelector('[data-pick]').addEventListener('click', function () {
			if (!window.wp || !wp.media) { return; }
			if (!frame) {
				frame = wp.media({ title: 'Choose a photo', button: { text: 'Use this photo' }, library: { type: 'image' }, multiple: false });
				frame.on('select', function () {
					var a = frame.state().get('selection').first().toJSON();
					var url = (a.sizes && a.sizes.large ? a.sizes.large.url : a.url);
					input.value = url;
					thumb.innerHTML = '<img src="' + url + '" alt="">';
				});
			}
			frame.open();
		});
		box.querySelector('[data-clear]').addEventListener('click', function () {
			input.value = '';
			thumb.textContent = thumb.getAttribute('data-empty') || 'Default photo';
		});
	});

	/* FAQ lists */
	document.querySelectorAll('[data-faq-add]').forEach(function (btn) {
		var list = btn.parentNode.querySelector('[data-faq-list]');
		var name = btn.getAttribute('data-name');
		var renumber = function () {
			list.querySelectorAll('li').forEach(function (li, i) {
				li.querySelector('input').name = name + '[' + i + '][0]';
				li.querySelector('textarea').name = name + '[' + i + '][1]';
			});
		};
		btn.addEventListener('click', function () {
			var li = document.createElement('li');
			li.innerHTML = '<input placeholder="Question"><textarea rows="2" placeholder="Answer"></textarea>' +
				'<button type="button" class="button-link bn-opt-del" data-faq-del><span class="dashicons dashicons-trash"></span></button>';
			list.appendChild(li);
			renumber();
			li.querySelector('input').focus();
		});
		list.addEventListener('click', function (e) {
			var del = e.target.closest('[data-faq-del]');
			if (del) { del.closest('li').remove(); renumber(); }
		});
	});
})();
