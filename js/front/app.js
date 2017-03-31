ZeroClipboard.config({
    swfPath: intelli.config.ia_url + "js/utils/zeroclipboard/ZeroClipboard.swf",
    hoverClass: 'hover',
    activeClass: 'active'
});

$(function () {
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
    })

    $('#action-delete').on('click', function (e) {
        e.preventDefault

        intelli.confirm(_t('delete_coupon_confirmation'), {url: $(this).attr('href')});
    });

    // show coupon code on click
    $('.js-btn-coupon .btn-coupon__cover').on('click', function (e) {
        var $this = $(this)
        $parent = $this.parent();

        var affiliateLink = $parent.data('affiliate-link');

        if ('undefined' != typeof affiliateLink && '' != affiliateLink) {
            window.open(affiliateLink, '_blank');
        }

        $this.hide();
    });

    // Copy codes
    var client = new ZeroClipboard($(".js-copy"));

    // thumbs actions
    $('a[class^="thumbs-"]').on('click', function (e) {
        e.preventDefault();

        var params = $(this).data();

        $.ajax(
            {
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
