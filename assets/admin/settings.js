/* BrightNest settings: media picker + FAQ list. Also used by the service editor FAQ tab. */
(function () {
	'use strict';

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
			thumb.textContent = 'Default photo';
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
