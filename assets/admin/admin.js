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

	// Popup details per option, keyed by "name|desc" (same key the checkout reads).
	var detailsEl = $('[data-opt-details]');
	var details = {};
	try { details = JSON.parse((detailsEl && detailsEl.value) || '{}') || {}; } catch (e) { details = {}; }
	var tabImgsEl = $('[data-tab-imgs]');
	var tabImgs = {};
	try { tabImgs = JSON.parse((tabImgsEl && tabImgsEl.value) || '{}') || {}; } catch (e) { tabImgs = {}; }
	var arTabsEl = $('[data-ar-tabs]');
	var arTabs = {};
	try { arTabs = JSON.parse((arTabsEl && arTabsEl.value) || '{}') || {}; } catch (e) { arTabs = {}; }

	function itemRow(it) {
		var det = details[it.name + '|' + (it.desc || '')] || {};
		return '<div class="bn-opt-row' + (det.about || det.hl ? ' has-detail' : '') + '">' +
			'<div class="bn-opt-move"><button type="button" class="button-link" data-up title="' + esc(t.up) + '"><span class="dashicons dashicons-arrow-up-alt2"></span></button>' +
			'<button type="button" class="button-link" data-down title="' + esc(t.down) + '"><span class="dashicons dashicons-arrow-down-alt2"></span></button></div>' +
			'<button type="button" class="bn-opt-img" data-pick-img title="' + esc(t.photo) + '"' + (it.img ? ' style="background-image:url(\'' + esc(it.img) + '\')"' : '') + '>' + (it.img ? '' : '<span class="dashicons dashicons-format-image"></span>') + '</button>' +
			'<input type="hidden" data-k="img" value="' + esc(it.img || '') + '">' +
			'<input class="bn-opt-name" data-k="name" placeholder="' + esc(t.name) + '" value="' + esc(it.name) + '">' +
			'<input class="bn-opt-desc" data-k="desc" placeholder="' + esc(t.desc) + '" value="' + esc(it.desc) + '">' +
			'<label class="bn-opt-money"><span>' + esc(cfg.currency) + '</span><input type="number" min="0" step="0.01" data-k="price" placeholder="' + esc(t.price) + '" value="' + esc(it.price) + '"></label>' +
			'<label class="bn-opt-money bn-opt-old"><span>' + esc(t.old) + '</span><input type="number" min="0" step="0.01" data-k="old" value="' + esc(it.old) + '"></label>' +
			'<button type="button" class="button-link bn-opt-more" data-more title="' + esc(t.details) + '" aria-expanded="false"><span class="dashicons dashicons-info-outline"></span></button>' +
			'<button type="button" class="button-link bn-opt-del" data-del title="' + esc(t.remove) + '"><span class="dashicons dashicons-trash"></span></button>' +
			// Popup details (shown when a customer clicks the option at the checkout).
			'<div class="bn-opt-detail" hidden>' +
			'<label><span>' + esc(t.about) + '</span><textarea rows="3" data-d="about">' + esc(det.about || '') + '</textarea></label>' +
			'<label><span>' + esc(t.highlights) + '</span><textarea rows="3" data-d="hl" placeholder="shield | 60-Day Service Warranty&#10;Ideal for split AC units">' + esc(det.hl || '') + '</textarea></label>' +
			// Arabic site texts for this option.
			'<label><span>' + esc(t.nameAr) + '</span><input dir="rtl" data-d="name_ar" value="' + esc(det.name_ar || '') + '"></label>' +
			'<label><span>' + esc(t.descAr) + '</span><input dir="rtl" data-d="desc_ar" value="' + esc(det.desc_ar || '') + '"></label>' +
			'<label><span>' + esc(t.aboutAr) + '</span><textarea dir="rtl" rows="3" data-d="about_ar">' + esc(det.about_ar || '') + '</textarea></label>' +
			'<label><span>' + esc(t.hlAr) + '</span><textarea dir="rtl" rows="3" data-d="hl_ar">' + esc(det.hl_ar || '') + '</textarea></label>' +
			'</div>' +
			'</div>';
	}

	function groupBlock(g) {
		var div = document.createElement('div');
		div.className = 'bn-opt-group';
		div.innerHTML =
			'<div class="bn-opt-group-head">' +
			'<span class="dashicons dashicons-category"></span>' +
			'<input class="bn-opt-tab" data-tab-name placeholder="' + esc(t.tabPh) + '" value="' + esc(g.name) + '" aria-label="' + esc(t.tab) + '">' +
			'<button type="button" class="bn-opt-img" data-pick-tab-img title="' + esc(t.tabImg) + '"' + (tabImgs[g.name] ? ' style="background-image:url(\'' + esc(tabImgs[g.name]) + '\')"' : '') + '>' + (tabImgs[g.name] ? '' : '<span class="dashicons dashicons-format-image"></span>') + '</button>' +
			'<input type="hidden" data-tab-img value="' + esc(tabImgs[g.name] || '') + '">' +
			'<input class="bn-opt-tab bn-opt-tab-ar" dir="rtl" data-tab-ar placeholder="' + esc(t.tabAr) + '" value="' + esc(arTabs[g.name] || '') + '" aria-label="' + esc(t.tabAr) + '">' +
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
		var det = {};
		$$('.bn-opt-group', groupsEl).forEach(function (g) {
			var tab = $('[data-tab-name]', g).value.trim().replace(/\|/g, '/') || (t.newTab || 'Packages');
			$$('.bn-opt-row', g).forEach(function (r) {
				var v = function (k) { return $('[data-k="' + k + '"]', r).value.trim().replace(/\|/g, '/'); };
				if (!v('name')) { return; }
				var line = [tab, v('name'), v('desc'), v('price') || '0'];
				if (v('old') || v('img')) { line.push(v('old')); }
				if (v('img')) { line.push(v('img')); }
				lines.push(line.join(' | '));
				var row = {};
				['about', 'hl', 'name_ar', 'desc_ar', 'about_ar', 'hl_ar'].forEach(function (f) {
					var val = $('[data-d="' + f + '"]', r).value.trim();
					if (val) { row[f] = val; }
				});
				if (Object.keys(row).length) { det[v('name') + '|' + v('desc')] = row; }
				r.classList.toggle('has-detail', !!(row.about || row.hl));
			});
		});
		raw.value = lines.join('\n');
		if (detailsEl) { detailsEl.value = JSON.stringify(det); details = det; }
		// Arabic tab names, keyed by the English tab name.
		var tabs = {};
		var imgs = {};
		$$('.bn-opt-group', groupsEl).forEach(function (g) {
			var en = $('[data-tab-name]', g).value.trim().replace(/\|/g, '/') || (t.newTab || 'Packages');
			var ar = $('[data-tab-ar]', g).value.trim();
			var img = $('[data-tab-img]', g).value.trim();
			if (ar) { tabs[en] = ar; }
			if (img) { imgs[en] = img; }
		});
		if (arTabsEl) { arTabsEl.value = JSON.stringify(tabs); arTabs = tabs; }
		if (tabImgsEl) { tabImgsEl.value = JSON.stringify(imgs); tabImgs = imgs; }
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
		if (b.hasAttribute('data-more')) {
			var panel = $('.bn-opt-detail', row);
			panel.hidden = !panel.hidden;
			b.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
			if (!panel.hidden) { $('textarea', panel).focus(); }
			return;
		}
		if (b.hasAttribute('data-pick-tab-img')) {
			pickImg(b, $('[data-tab-img]', group));
			return;
		}
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
