/* global jQuery, geoOptimizerScore */
(function ($) {
	'use strict';

	$('#geo-score-refresh').on('click', function () {
		var $btn = $(this);
		$btn.prop('disabled', true).text('…');

		$.post(geoOptimizerScore.ajaxUrl, {
			action: 'geo_optimizer_score',
			nonce: geoOptimizerScore.nonce,
			post_id: geoOptimizerScore.postId
		}, function (response) {
			if (!response.success) {
				$btn.prop('disabled', false).text('Recalculate');
				return;
			}

			var data = response.data;
			var $wrap = $('#geo-score-wrap');

			// Update total score.
			$wrap.find('.geo-score-number')
				.text(data.score)
				.attr('class', 'geo-score-number ' + data.colorClass);

			// Update factors.
			var $factors = $wrap.find('.geo-factor');
			$.each(data.factors, function (i, factor) {
				var $li = $factors.eq(i);
				$li.attr('class', 'geo-factor geo-factor--' + factor.status);
				$li.find('.geo-factor-label').text(factor.label);
				$li.find('.geo-factor-points').text(factor.points + '/' + factor.max);
			});

			$btn.prop('disabled', false).text('Recalculate');
		});
	});
})(jQuery);
