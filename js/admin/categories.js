Ext.onReady(function () {
    if (Ext.get('js-grid-placeholder')) {
        var grid = new IntelliGrid(
            {
                columns: [
                    'selection',
                    {name: 'title', title: _t('title'), width: 2, editor: 'text'},
                    {name: 'title_alias', title: _t('title_alias'), width: 220},
                    {name: 'num_coupons', title: _t('listings'), width: 50},
                    {name: 'num_all_coupons', title: _t('total'), width: 50},
                    {
                        name: 'order',
                        title: _t('order'),
                        width: 60,
                        editor: 'number',
                        align: intelli.gridHelper.constants.ALIGN_CENTER
                    },
                    {name: 'locked', title: _t('locked'), width: 65, renderer: intelli.gridHelper.renderer.check},
                    {name: 'level', title: _t('level'), width: 50, hidden: true},
                    'status',
                    'update',
                    'delete'
                ],
                sorters: [{property: 'level', direction: 'ASC'}, {property: 'title', direction: 'ASC'}],
                texts: {
                    delete_single: _t('are_you_sure_to_delete_selected_coupon_category'),
                    delete_multiple: _t('are_you_sure_to_delete_selected_coupon_categories')
                }
            }, false);

        grid.toolbar = Ext.create('Ext.Toolbar', {
            items: [
                {
                    emptyText: _t('title'),
                    name: 'title',
                    xtype: 'textfield',
                    listeners: intelli.gridHelper.listener.specialKey
                }, {
                    emptyText: _t('status'),
                    xtype: 'combo',
                    typeAhead: true,
                    editable: false,
                    name: 'status',
                    store: grid.stores.statuses,
                    displayField: 'title',
                    valueField: 'value'
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
    }
});

intelli.titleCache = '';
intelli.fillUrlBox = function () {
    var slug = $('#field_title_alias').val();
    var title = ('' == slug ? $('#field_ccats_title').val() : slug);
    var category = $('#input-tree').val();
    var cache = title + '%%' + category;

    if ('' != title && intelli.titleCache != cache) {
        var params = {title: title, category: category};
        if ('' != slug) {
            params.alias = 1;
        }

        $.getJSON(intelli.config.admin_url + '/coupons/categories/slug.json', params, function (response) {
            if ('' !== response.data) {
                $('#title_url').html(response.data + (response.exists ? ' <b style="color:red">' + response.exists + '</b>' : ''));
                $('#title_box').fadeIn();
            }
        });
    }

    intelli.titleCache = cache;
};

$(function () {
    $('#field_ccats_title').keyup(function () {
        $('#title_alias_fieldzone').show();
    });
    $('#field_ccats_title, #field_title_alias').blur(intelli.fillUrlBox).blur();
});