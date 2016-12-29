Ext.onReady(function()
{
	if (Ext.get('js-grid-placeholder'))
	{
		var grid = new IntelliGrid(
		{
			columns: [
				'selection',
				{name: 'title', title: _t('title'), width: 2, editor: 'text'},
				{name: 'title_alias', title: _t('title_alias'), width: 220},
				{name: 'coupons_num', title: _t('coupons'), width: 65},
				{name: 'date_added', title: _t('date_added'), width: 170, editor: 'date'},
				'status',
				'update',
				'delete'
			],
			sorters: [{property: 'date_added', direction: 'DESC'}],
			statuses: ['active','inactive','suspended'],
			texts: {
				delete_single: _t('are_you_sure_to_delete_selected_shop'),
				delete_multiple: _t('are_you_sure_to_delete_selected_shops')
			}
		}, false);

		grid.toolbar = Ext.create('Ext.Toolbar', {items:[
		{
			emptyText: _t('title'),
			name: 'title',
			xtype: 'textfield',
			id: 'fltTitle',
			listeners: intelli.gridHelper.listener.specialKey
		},new Ext.form.ComboBox({
			displayField: 'title',
			emptyText: _t('member'),
			name: 'member',
			store: intelli.gridHelper.store.ajax(intelli.config.admin_url + '/transactions/members.json'),
			listeners: intelli.gridHelper.listener.specialKey,
			valueField: 'value'
		}),{
			emptyText: _t('status'),
			xtype: 'combo',
			typeAhead: true,
			editable: false,
			id: 'fltStatus',
			name: 'status',
			store: grid.stores.statuses,
			displayField: 'title',
			valueField: 'value'
		},{
			text: '<i class="i-search"></i> ' + _t('search'),
			id: 'fltBtn',
			handler: function(){intelli.gridHelper.search(grid);}
		},{
			text: '<i class="i-close"></i> ' + _t('reset'),
			handler: function(){intelli.gridHelper.search(grid, true);}
		}]});

		grid.init();

		var searchStatus = intelli.urlVal('status');
		if (searchStatus)
		{
			Ext.getCmp('fltStatus').setValue(searchStatus);
			intelli.gridHelper.search(grid);
		}
	}
});

intelli.titleCache = '';
intelli.fillUrlBox = function()
{
	var alias = $("input[name='title_alias']").val(),
		title = ('' == alias ? $("input[name='website']").val() : alias),
		aliasType = 'website';

	if ($("input[name='website']").length == 0 || '' == $("input[name='website']").val() || 'http://' == $("input[name='website']").val())
	{
		title = ('' == alias ? $("input[name='title']").val() : alias);
		aliasType = 'title';
	}

	var cache = title;

	if ('' != title && intelli.titleCache != cache)
	{
		var params = {title: title, type: aliasType, id: $('#input-id').val()};
		if ('' != alias)
		{
			params.alias = 1;
		}
		$.get(intelli.config.admin_url + '/coupons/shops/alias.json', params, function(response)
		{
			if ('' != response.data)
			{
				$('#title_url').html(response.data + (response.exists ? ' <b style="color: #F00;">' + response.exists + '</b>' : ''));
				$('#title_box').fadeIn();
			}
		});
	}
	intelli.titleCache = cache;
};

$(function()
{
	$('input[name="title"], input[name="website"]').keyup(function()
	{
		$('#field-title-alias').show();
	});

	$('input[name="website"], input[name="title"], input[name="title_alias"]').blur(intelli.fillUrlBox).blur();
});