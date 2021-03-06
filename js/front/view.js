$(function () {
    var $modal = $('#report-coupon-modal');
    var couponId;

    $('#js-cmd-report-coupon').on('click', function (e) {
        e.preventDefault();
        couponId = $(this).data('id');
        $modal.modal();
    });

    $('#report-coupon-form').on('submit', function (e) {
        e.preventDefault();

        var $comment = $('#report-coupon-comment');
        var url = intelli.config.packages.coupons.url + 'coupon/read.json';

        intelli.post(url, {action: 'report', id: couponId, comments: $comment.val()}, function () {
            $comment.val('');
            $modal.modal('hide');
            intelli.notifFloatBox({msg: _t('you_sent_report'), type: 'success', autohide: true});
        });
    });

    var modalStatistics = $('#statistic-coupon-modal');
    $('#js-cmd-statistics-coupon').on('click', function (e) {
        e.preventDefault();
        couponId = $(this).data('id');
        modalStatistics.modal();
    });

    // Picking tags
    $('.couponItem .tag').on('click', function (e) {
        e.preventDefault();

        var tag = $.trim($(this).attr('href'));
        $('input[name="q"]').val(tag).closest('form').submit();
    });

    $('.js-delete-coupon').on('click', function (e) {
        e.preventDefault();
        intelli.confirm(_t('delete_coupon_confirmation'), {url: $(this).attr('href')});
    });

    $('.js-code-status').on('change', function (e) {
        var $this = $(this).prop('disabled', true);
        intelli.post(intelli.config.packages.coupons.url + 'coupon/read.json', {
            action: 'status',
            id: $this.data('id'),
            status: $this.val()
        }, function (response) {
            $this.prop('disabled', false);
            intelli.notifFloatBox({msg: response.message, type: response.result ? 'success' : 'error', autohide: true});
        });
    });

    $('.js-cmd-print-coupon').on('click', function () {
        var win = window.open(window.location.href + '?print', '_blank');
        win.focus();
        // window.location.href = window.location.href + '?print';
    })
});