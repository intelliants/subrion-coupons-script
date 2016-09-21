$(function () {
	var modal = $('#report-coupon-modal');
	$('#js-cmd-report-coupon').on('click', function (e) {
		e.preventDefault();
		couponId = $(this).data('id');
		modal.modal();
	});
	$('#report-coupon-form').on('submit', function (e) {
		e.preventDefault();

		var comment = $('#report-coupon-comment');
		var commentText = comment.val();

		$.post(intelli.config.packages.coupons.url + 'coupon/read.json', {
			action: 'report', id: couponId, comments: commentText
		}, function () {
			comment.val('');
			modal.modal('hide');
			intelli.notifFloatBox({msg: _t('you_sent_report'), type: 'success', autohide: true});
		});
	});

	var modalStatistics = $('#statistic-coupon-modal');
	$('#js-cmd-statistics-coupon').on('click', function (e) {
		e.preventDefault();
		couponId = $(this).data('id');
		modalStatistics.modal();
	});

});