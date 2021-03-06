Ext.onReady(function () {
    if (Ext.get('js-grid-placeholder')) {
        var grid = new IntelliGrid(
            {
                columns: [
                    'selection',
                    {name: 'coupon', title: _t('coupon'), width: 1},
                    {name: 'member', title: _t('member'), width: 160},
                    {name: 'code', title: _t('code'), width: 120},
                    {name: 'reference_id', title: _t('transaction'), width: 150},
                    {name: 'date_paid', title: _t('field_transaction_date_paid'), width: 170, editor: 'date'},
                    'status',
                    'delete'
                ],
                //expanderTemplate: '<pre style="font-size: 0.9em">{short_description}</pre>',
                sorters: [{property: 'date_paid', direction: 'DESC'}],
                statuses: ['active', 'inactive', 'used']
            }, false);

        grid.toolbar = Ext.create('Ext.Toolbar', {
            items: [
                {
                    emptyText: _t('code'),
                    xtype: 'textfield',
                    id: 'fltCode',
                    name: 'code',
                    listeners: intelli.gridHelper.listener.specialKey
                }, new Ext.form.ComboBox({
                    displayField: 'title',
                    emptyText: _t('member'),
                    name: 'member',
                    store: intelli.gridHelper.store.ajax(intelli.config.admin_url + '/transactions/members.json'),
                    listeners: intelli.gridHelper.listener.specialKey,
                    valueField: 'value'
                }), {
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

$(function () {
    if ($('#js-grid-placeholder').length) {
        return;
    }
});