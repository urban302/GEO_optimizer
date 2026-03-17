/* global jQuery, wp */
(function ($) {
	'use strict';

	$(document).on('click', '.geo-media-upload', function (e) {
		e.preventDefault();

		var $button   = $(this);
		var targetId  = $button.data('target');
		var $input    = $('#' + targetId);

		var frame = wp.media({
			title: 'Selecteer afbeelding',
			button: { text: 'Gebruik deze afbeelding' },
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$input.val(attachment.url);

			// Update or create preview.
			var $preview = $button.closest('td').find('.geo-media-preview');
			if ($preview.length) {
				$preview.find('img').attr('src', attachment.url);
			} else {
				$button.after(
					'<div class="geo-media-preview" style="margin-top:8px">' +
					'<img src="' + attachment.url + '" alt="" style="max-width:200px;height:auto" />' +
					'</div>'
				);
			}
		});

		frame.open();
	});
})(jQuery);
