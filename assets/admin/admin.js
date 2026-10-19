/* BrightNest service editor: tabs, booking mode, option & "included" repeaters. */
(function () {
	'use strict';

	var box = document.querySelector('[data-bn-service]');
	if (!box) { return; }
	var cfg = window.BN_ADMIN || { currency: 'AED', i18n: {} };
	var t = cfg.i18n;
	var $ = function (sel, ctx) { return (ctx || box).querySelector(sel); };
	var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || box).querySelectorAll(sel)); };
	var esc = function (s) {
		return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
	};

	/* Tabs ------------------------------------------------------------- */
	$$('.bn-adm-tabs button').forEach(function (b) {
		b.addEventListener('click', function () {
			var name = b.getAttribute('data-tab');
			$$('.bn-adm-tabs button').forEach(function (x) { x.classList.toggle('is-active', x === b); });
			$$('.bn-adm-panel').forEach(function (p) {
				var on = p.getAttribute('data-panel') === name;
				p.hidden = !on;
				p.classList.toggle('is-active', on);
			});
			try { sessionStorage.setItem('bn_adm_tab', name); } catch (e) { /* ignore */ }
		});
	});
	try {
		var last = sessionStorage.getItem('bn_adm_tab');
		var btn = last && $('.bn-adm-tabs [data-tab="' + last + '"]');
		if (btn) { btn.click(); }
	} catch (e) { /* ignore */ }

	/* Booking mode ----------------------------------------------------- */
	function applyMode() {
		var checked = $('[name="bn_mode"]:checked');
		var mode = checked ? checked.value : '';
		$$('[data-show-mode]').forEach(function (el) { el.hidden = el.getAttribute('data-show-mode') !== mode; });
		$$('.bn-adm-mode').forEach(function (l) { l.classList.toggle('is-active', l.querySelector('input').checked); });
	}
	$$('[name="bn_mode"]').forEach(function (r) { r.addEventListener('change', applyMode); });
	applyMode();

	var rate = $('[name="bn_hourly_rate"]');
	var mat = $('[name="bn_materials_rate"]');
	function hourlyExample() {
		var el = $('[data-hourly-example]');
		var r = parseFloat(rate.value) || 0;
		var m = parseFloat(mat.value) || 0;
		el.textContent = r ? (t.hourlyEx || '%s (+ %s)').replace('%s', cfg.currency + ' ' + (r * 6)).replace('%s', cfg.currency + ' ' + (m * 6)) : '';
	}
	[rate, mat].forEach(function (i) { i.addEventListener('input', hourlyExample); });
	hourlyExample();

	/* Options repeater ------------------------------------------------- */
	var raw = $('[data-opt-raw]');
	var groupsEl = $('[data-opt-groups]');
	// True when the text box was edited by hand after the editor last wrote to it.
	var rawDirty = false;
	raw.addEventListener('input', function () { rawDirty = true; });

	function parseRaw(text) {
		var groups = [];
		var byName = {};
		text.split(/\r?\n/).forEach(function (line) {
			var p = line.split('|').map(function (x) { return x.trim(); });
			if (p.length < 4 || !p[1]) { return; }
			var g = p[0] || t.newTab || 'Packages';
			if (!byName[g]) { byName[g] = { name: g, items: [] }; groups.push(byName[g]); }
			byName[g].items.push({ name: p[1], desc: p[2] || '', price: p[3] || '', old: p[4] || '', img: p[5] || '' });
		});
		return groups;
	}

	function itemRow(it) {
		return '<div class="bn-opt-row">' +
			'<div class="bn-opt-move"><button type="button" class="button-link" data-up title="' + esc(t.up) + '"><span class="dashicons dashicons-arrow-up-alt2"></span></button>' +
			'<button type="button" class="button-link" data-down title="' + esc(t.down) + '"><span class="dashicons dashicons-arrow-down-alt2"></span></button></div>' +
			'<button type="button" class="bn-opt-img" data-pick-img title="' + esc(t.photo) + '"' + (it.img ? ' style="background-image:url(\'' + esc(it.img) + '\')"' : '') + '>' + (it.img ? '' : '<span class="dashicons dashicons-format-image"></span>') + '</button>' +
			'<input type="hidden" data-k="img" value="' + esc(it.img || '') + '">' +
			'<input class="bn-opt-name" data-k="name" placeholder="' + esc(t.name) + '" value="' + esc(it.name) + '">' +
			'<input class="bn-opt-desc" data-k="desc" placeholder="' + esc(t.desc) + '" value="' + esc(it.desc) + '">' +
			'<label class="bn-opt-money"><span>' + esc(cfg.currency) + '</span><input type="number" min="0" step="0.01" data-k="price" placeholder="' + esc(t.price) + '" value="' + esc(it.price) + '"></label>' +
			'<label class="bn-opt-money bn-opt-old"><span>' + esc(t.old) + '</span><input type="number" min="0" step="0.01" data-k="old" value="' + esc(it.old) + '"></label>' +
			'<button type="button" class="button-link bn-opt-del" data-del title="' + esc(t.remove) + '"><span class="dashicons dashicons-trash"></span></button>' +
			'</div>';
	}

	function groupBlock(g) {
		var div = document.createElement('div');
		div.className = 'bn-opt-group';
		div.innerHTML =
			'<div class="bn-opt-group-head">' +
			'<span class="dashicons dashicons-category"></span>' +
			'<input class="bn-opt-tab" data-tab-name placeholder="' + esc(t.tabPh) + '" value="' + esc(g.name) + '" aria-label="' + esc(t.tab) + '">' +
			'<span class="bn-opt-count"></span>' +
			'<button type="button" class="button-link" data-gup title="' + esc(t.up) + '"><span class="dashicons dashicons-arrow-up-alt2"></span></button>' +
			'<button type="button" class="button-link" data-gdown title="' + esc(t.down) + '"><span class="dashicons dashicons-arrow-down-alt2"></span></button>' +
			'<button type="button" class="button-link bn-opt-del" data-gdel title="' + esc(t.removeTab) + '"><span class="dashicons dashicons-trash"></span></button>' +
			'</div>' +
			'<div class="bn-opt-rows">' + g.items.map(itemRow).join('') + '</div>' +
			'<button type="button" class="button bn-opt-add" data-add-item><span class="dashicons dashicons-plus"></span> ' + esc(t.addOption) + '</button>';
		return div;
	}

	// Option photo from the media library; picking again replaces it, cancel keeps it.
	function pickImg(btn, input) {
		if (!window.wp || !wp.media) { return; }
		var frame = wp.media({ title: t.choosePhoto, multiple: false, library: { type: 'image' } });
		frame.on('select', function () {
			var a = frame.state().get('selection').first().toJSON();
			var url = a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url;
			input.value = url;
			btn.style.backgroundImage = 'url(\'' + url + '\')';
			btn.innerHTML = '';
			serialize();
		});
		frame.open();
	}

	function render(groups) {
		groupsEl.innerHTML = '';
		groups.forEach(function (g) { groupsEl.appendChild(groupBlock(g)); });
		refresh();
	}

	function serialize() {
		var lines = [];
		$$('.bn-opt-group', groupsEl).forEach(function (g) {
			var tab = $('[data-tab-name]', g).value.trim().replace(/\|/g, '/') || (t.newTab || 'Packages');
			$$('.bn-opt-row', g).forEach(function (r) {
				var v = function (k) { return $('[data-k="' + k + '"]', r).value.trim().replace(/\|/g, '/'); };
				if (!v('name')) { return; }
				var line = [tab, v('name'), v('desc'), v('price') || '0'];
				if (v('old') || v('img')) { line.push(v('old')); }
				if (v('img')) { line.push(v('img')); }
				lines.push(line.join(' | '));
			});
		});
		raw.value = lines.join('\n');
		rawDirty = false;
	}

	function refresh() {
		var groups = $$('.bn-opt-group', groupsEl);
		groups.forEach(function (g) {
			var n = $$('.bn-opt-row', g).length;
			$('.bn-opt-count', g).textContent = n + ' ' + (t.options || 'options');
		});
		var empty = $('.bn-opt-empty', groupsEl.parentNode);
		if (!groups.length) {
			if (!empty) {
				empty = document.createElement('p');
				empty.className = 'bn-opt-empty';
				empty.textContent = t.empty;
				groupsEl.parentNode.insertBefore(empty, groupsEl);
			}
		} else if (empty) {
			empty.remove();
		}
		serialize();
	}

	groupsEl.addEventListener('input', serialize);
	groupsEl.addEventListener('click', function (e) {
		var b = e.target.closest('button');
		if (!b) { return; }
		var row = b.closest('.bn-opt-row');
		var group = b.closest('.bn-opt-group');
		if (b.hasAttribute('data-pick-img')) {
			pickImg(b, $('[data-k="img"]', row));
			return;
		}
		if (b.hasAttribute('data-add-item')) {
			var rows = $('.bn-opt-rows', group);
			rows.insertAdjacentHTML('beforeend', itemRow({ name: '', desc: '', price: '', old: '' }));
			rows.lastElementChild.querySelector('.bn-opt-name').focus();
		} else if (b.hasAttribute('data-del')) {
			row.remove();
		} else if (b.hasAttribute('data-up') && row.previousElementSibling) {
			row.parentNode.insertBefore(row, row.previousElementSibling);
		} else if (b.hasAttribute('data-down') && row.nextElementSibling) {
			row.parentNode.insertBefore(row.nextElementSibling, row);
		} else if (b.hasAttribute('data-gdel')) {
			if ($$('.bn-opt-row', group).length && !window.confirm(t.confirmTab)) { return; }
			group.remove();
		} else if (b.hasAttribute('data-gup') && group.previousElementSibling) {
			groupsEl.insertBefore(group, group.previousElementSibling);
		} else if (b.hasAttribute('data-gdown') && group.nextElementSibling) {
			groupsEl.insertBefore(group.nextElementSibling, group);
		}
		refresh();
	});

	$('[data-add-group]').addEventListener('click', function () {
		var g = groupBlock({ name: '', items: [{ name: '', desc: '', price: '', old: '' }] });
		groupsEl.appendChild(g);
		$('[data-tab-name]', g).focus();
		refresh();
	});
	$('[data-opt-reload]').addEventListener('click', function () { render(parseRaw(raw.value)); });

	render(parseRaw(raw.value));

	/* "What's included" repeater -------------------------------------- */
	var incRaw = $('[data-inc-raw]');
	var incList = $('[data-inc-list]');
	function incRow(text) {
		return '<li><span class="dashicons dashicons-yes"></span><input value="' + esc(text) + '" placeholder="' + esc(t.item) + '">' +
			'<button type="button" class="button-link bn-opt-del" title="' + esc(t.remove) + '"><span class="dashicons dashicons-trash"></span></button></li>';
	}
	function incSave() {
		incRaw.value = $$('input', incList).map(function (i) { return i.value.trim(); }).filter(Boolean).join('\n');
	}
	incList.innerHTML = incRaw.value.split(/\r?\n/).filter(function (x) { return x.trim(); }).map(incRow).join('');
	incList.addEventListener('input', incSave);
	incList.addEventListener('click', function (e) {
		var b = e.target.closest('button');
		if (b) { b.closest('li').remove(); incSave(); }
	});
	incList.addEventListener('keydown', function (e) {
		if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
			e.preventDefault();
			$('[data-add-inc]').click();
		}
	});
	$('[data-add-inc]').addEventListener('click', function () {
		incList.insertAdjacentHTML('beforeend', incRow(''));
		incList.lastElementChild.querySelector('input').focus();
	});

	// Make sure the hidden fields are current when the post is saved.
	var form = document.getElementById('post');
	// Hand-edited text that was not applied is saved as typed.
	if (form) { form.addEventListener('submit', function () { if (!rawDirty) { serialize(); } incSave(); }); }
	// Enter inside option inputs must not submit the whole post form.
	groupsEl.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); } });

	/* Switches that show/hide a box (e.g. materials) ------------------- */
	$$('[data-toggle-target]').forEach(function (cb) {
		var target = $('[data-toggle-box="' + cb.getAttribute('data-toggle-target') + '"]');
		var sync = function () { if (target) { target.hidden = !cb.checked; } };
		cb.addEventListener('change', sync);
		sync();
	});

	/* "How often?" rows ------------------------------------------------- */
	var freqList = $('[data-freq-list]');
	var freqIdx = 1000;
	$('[data-freq-add]').addEventListener('click', function () {
		var i = freqIdx++;
		var li = document.createElement('li');
		li.innerHTML = '<input name="bn_freqs[' + i + '][0]" placeholder="e.g. Weekly">' +
			'<label><input type="number" min="0" max="90" step="0.5" name="bn_freqs[' + i + '][1]" value="0"> % off</label>' +
			'<button type="button" class="button-link bn-opt-del" data-freq-del><span class="dashicons dashicons-trash"></span></button>';
		freqList.appendChild(li);
		li.querySelector('input').focus();
	});
	freqList.addEventListener('click', function (e) {
		var del = e.target.closest('[data-freq-del]');
		if (del) { del.closest('li').remove(); }
	});
	freqList.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); } });
})();

/* Areas & prices: show the % box or the own-price table for the pricing chosen per area. */
(function () {
	'use strict';
	document.querySelectorAll('.bn-adm-areas tbody tr').forEach(function (row) {
		var sel = row.querySelector('[data-area-mode]');
		if (!sel) { return; }
		var apply = function () {
			row.querySelectorAll('[data-show-for]').forEach(function (el) { el.hidden = el.getAttribute('data-show-for') !== sel.value; });
		};
		sel.addEventListener('change', apply);
		apply();
	});
})();
