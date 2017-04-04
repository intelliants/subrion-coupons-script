Ext.onReady(function () {
    if (Ext.get('js-grid-placeholder')) {
        var grid = new IntelliGrid(
            {
                columns: [
                    'selection',
                    'expander',
                    {name: 'title', title: _t('title'), width: 1, editor: 'text'},
                    {name: 'title_alias', title: _t('title_alias'), width: 200},
                    {name: 'category', title: _t('category'), width: 120},
                    {
                        name: 'coupon_type', title: _t('coupon_type'), width: 120, renderer: function (value) {
                        return _t('field_coupons_coupon_type+' + value, value);
                    }
                    },
                    {name: 'member', title: _t('member'), width: 120},
                    {name: 'expire_date', title: _t('coupon_expire'), width: 120, hidden: true},
                    {name: 'date_added', title: _t('date_added'), width: 120, editor: 'date'},
                    'status',
                    {
                        name: 'reported_as_problem', title: _t('problem'), icon: 'info', click: function (node) {
                        Ext.MessageBox.alert(
                            _t('reported_as_problem_comments'),
                            node.data.reported_as_problem_comments.replace(/(?:\r\n|\r|\n)/g, '<br />')
                        )
                    }
                    },
                    'update',
                    'delete'
                ],
                expanderTemplate: '<pre style="font-size: 0.9em">{short_description}</pre>',
                fields: ['short_description', 'reported_as_problem_comments'],
                sorters: [{property: 'date_added', direction: 'DESC'}],
                statuses: ['active', 'approval', 'suspended', 'expired'],
                texts: {
                    delete_single: _t('are_you_sure_to_delete_selected_coupon'),
                    delete_multiple: _t('are_you_sure_to_delete_selected_coupons')
                }
            }, false);

        grid.toolbar = Ext.create('Ext.Toolbar', {
            items: [
                {
                    emptyText: _t('title'),
                    xtype: 'textfield',
                    id: 'fltTitle',
                    name: 'title',
                    listeners: intelli.gridHelper.listener.specialKey
                }, new Ext.form.ComboBox({
                    displayField: 'title',
                    emptyText: _t('member'),
                    name: 'member',
                    store: intelli.gridHelper.store.ajax(intelli.config.admin_url + '/transactions/members.json'),
                    listeners: intelli.gridHelper.listener.specialKey,
                    valueField: 'value'
                }), {
                    emptyText: _t('coupon_type'),
                    xtype: 'combo',
                    typeAhead: true,
                    editable: false,
                    id: 'fltType',
                    name: 'coupon_type',
                    store: new Ext.data.SimpleStore(
                        {
                            fields: ['value', 'title'],
                            data: [['simple', _t('field_coupons_coupon_type+simple')], ['printable', _t('field_coupons_coupon_type+printable')], ['deal', _t('field_coupons_coupon_type+deal')]]
                        }),
                    displayField: 'title',
                    valueField: 'value'
                }, {
                    emptyText: _t('status'),
                    xtype: 'combo',
                    typeAhead: true,
                    editable: false,
                    id: 'fltStatus',
                    name: 'status',
                    store: grid.stores.statuses,
                    displayField: 'title',
                    valueField: 'value'
                }, {
                    boxLabel: _t('reported_as_problem'),
                    name: 'reported_as_problem',
                    xtype: 'checkbox'
                }, {
                    text: '<i class="i-search"></i> ' + _t('search'),
                    id: 'fltBtn',
                    handler: function () {
                        intelli.gridHelper.search(grid);
                    }
                }, {
                    text: '<i class="i-close"></i> ' + _t('reset'),
                    handler: function () {
                        intelli.gridHelper.search(grid, true);
                    }
                }]
        });

        grid.init();

        var searchStatus = intelli.urlVal('status');
        if (searchStatus) {
            Ext.getCmp('fltStatus').setValue(searchStatus);
            intelli.gridHelper.search(grid);
        }
    }
});

intelli.titleCache = '';
intelli.fillUrlBox = function () {
    var alias = $('#input-alias').val();
    var title = ('' == alias ? $('#field_coupons_title').val() : alias);
    var shop = $('#input-shop').val();
    var category = $('#input-tree').val();
    var cache = category + '%%' + shop + '%%' + title;

    if ('' != title && '' != shop && intelli.titleCache != cache) {
        var params = {title: title, shop: shop, category: category};
        if ('' != alias) params.alias = 1;
        $.get(intelli.config.admin_url + '/coupons/coupons/alias.json', params, function (response) {
            if ('' != response.data) {
                $('#title_url').text(response.data);
                $('#title_box').fadeIn();
            }
        });
    }
    intelli.titleCache = cache;
};

$(function () {
    if ($('#js-grid-placeholder').length) {
        return;
    }

    $('#field_coupons_title').keyup(function () {
        $('#title_alias_fieldzone').show();
    });
    $('#field_coupons_title, #input-alias').blur(intelli.fillUrlBox).blur();

    // get shops autocomplete
    $('#input-shop').typeahead(
        {
            source: function (query, process) {
                return $.ajax(
                    {
                        url: intelli.config.admin_url + '/coupons/coupons/shops.json',
                        dataType: 'json',
                        data: {q: query},
                        success: function (data) {
                            return 'undefined' === typeof data.options ? false : process(data.options);
                        }
                    });
            }
        });

    $('#field_coupons_coupon_tags').tagsInput({width: '100%', height: 'auto'});

    // hide pricing options for non deals
    $('#field_coupons_coupon_type').on('change', function () {
        var $o = $('#coupons_pricing');
        'deal' == $(this).val() ? $o.show() : $o.hide();
    });
});