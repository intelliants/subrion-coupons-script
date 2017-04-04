$(function () {
    $('#couponType').on('change', function () {
        var couponType = $(this).val();

        $('.coupon-types .control-group').hide();

        if (couponType) {
            $('.coupon-type-' + couponType).show();
        }
    });

    // tags implementation
    $('#field_coupons_tags').tagsInput({width: '100%', height: 'auto'});

    // validate shop
    $('#shop')
        .blur(function () {
            var params = {action: 'validate', shop: $(this).val()};
            $.get(intelli.config.packages.coupons.url + 'coupons/add.json', params, function (data) {
                var element = $('#website');

                if (false == data.data) {
                    element.prop('disabled', false);
                }
                else {
                    element.prop('disabled', true);
                    element.val(data.data);
                }

            });
        })
        .typeahead({
            source: function (query, process) {
                return $.ajax(
                    {
                        url: intelli.config.packages.coupons.url + 'coupons/add.json',
                        type: 'get',
                        dataType: 'json',
                        data: {q: query},
                        success: function (data) {
                            return 'undefined' === typeof data.options ? false : process(data.options);
                        }
                    });
            }
        });

    $('#field_coupons_type').on('change', function () {
        var $o = $('#fieldgroup_coupons_pricing');
        'deal' == $(this).val() ? $o.show() : $o.hide();
    });
});