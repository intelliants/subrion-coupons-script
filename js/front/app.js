$(function () {
    var initCountDown = function () {
        $('.js-countdown').each(function () {
            var $this = $(this),
                finalDate = $this.data('countdown');

            $this.countdown(finalDate)
                .on('update.countdown', function (event) {
                    var format = '%H:%M:%S';

                    if (event.offset.totalDays > 0) {
                        format = '%-d day%!d ' + format;
                    }
                    if (event.offset.weeks > 0) {
                        format = '%-w week%!w ' + format;
                    }
                    $this.html(event.strftime(format));
                })
                .on('finish.countdown', function (e) {
                    $this.html(_t('expired_offer'));
                });
        });
    };

    initCountDown();

    $(document).on('intelli.search.finished', function () {
        initCountDown();
    });

    $('#action-delete').on('click', function (e) {
        e.preventDefault();

        intelli.confirm(_t('delete_coupon_confirmation'), {url: $(this).attr('href')});
    });

    var $body = $('body');

    // show coupon code on click
    $body.on('click', '.js-btn-coupon .btn-coupon__cover', function (e) {
        var $this = $(this),
            $parent = $this.parent(),
            affiliateLink = $parent.data('affiliate-link').trim();

        if (undefined !== affiliateLink && '' !== affiliateLink) {
            window.open(affiliateLink, '_blank');
        }

        $this.hide();
    });

    // Copy codes
    var clipboard = new Clipboard('.js-copy');

    clipboard.on('success', function(e) {
        intelli.notifFloatBox({
            msg: _t('code_was_copied'),
            type: 'success',
            autohide: true
        });
    });

    // thumbs actions
    $body.on('click', 'a[class^="thumbs-"]', function (e) {
        e.preventDefault();

        var params = $(this).data();

        $.ajax({
            url: intelli.config.packages.coupons.url + 'coupons/rate.json',
            type: 'get',
            dataType: 'json',
            data: params,
            success: function (response) {
                intelli.notifFloatBox({
                    msg: response.message,
                    type: response.error ? 'error' : 'success',
                    autohide: true
                });
                response.error || $('#thumb_result_' + params.id).text(response.rating);
            }
        });
    });
});
