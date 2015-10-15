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
				{name: 'category', title: _t('category'), width: 120},
				{name: 'member', title: _t('member'), width: 120},
				{name: 'date_added', title: _t('date_added'), width: 120, editor: 'date'},
				'status',
				'update',
				'delete'
			],
			statuses: ['active','approval','suspended','expired'],
			texts: {
				delete_single: _t('are_you_sure_to_delete_selected_coupon'),
				delete_multiple: _t('are_you_sure_to_delete_selected_coupons')
			}
		}, false);

		grid.toolbar = Ext.create('Ext.Toolbar', {items:[
		{
			emptyText: _t('title'),
			xtype: 'textfield',
			id: 'fltTitle',
			name: 'title',
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

		var searchTitle = intelli.urlVal('q');
		if (searchTitle)
		{
			Ext.getCmp('fltTitle').setValue(searchTitle);
		}
		var searchStatus = intelli.urlVal('status');
		if (searchStatus)
		{
			Ext.getCmp('fltStatus').setValue(searchStatus);
		}

		if (searchStatus || searchTitle)
		{
			intelli.gridHelper.search(grid);
		}
	}
});

intelli.titleCache = '';
intelli.fillUrlBox = function()
{
	var alias = $("input[name='title_alias']").val();
	var title = ('' == alias ? $("input[name='title']").val() : alias);
	var shop = $('#input-shop').val();
	var category = $('#input-category').val();
	var cache = shop + '%%';

	if ('' != title && '' != shop && intelli.titleCache != cache)
	{
		var params = {title: title, shop: shop, category: category};
		if ('' != alias) params.alias = 1;
		$.get(intelli.config.admin_url + '/coupons/coupons/alias.json', params, function(response)
		{
			if ('' != response.data)
			{
				$('#title_url').text(response.data);
				$('#title_box').fadeIn();
			}
		});
	}
	intelli.titleCache = cache;
};

$(function()
{
	if ($('#js-grid-placeholder').length)
	{
		return;
	}

	$('#field_title').keyup(function(){ $('#field-title-alias').show(); });
	$("input[name='title'], input[name='alias']").blur(intelli.fillUrlBox).blur();

	// get shops autocomplete
	$('#input-shop').typeahead(
	{
		source: function (query, process)
		{
			return $.ajax(
			{
				url: intelli.config.admin_url + '/coupons/coupons/shops.json',
				dataType: 'json',
				data: {q: query},
				success: function (data)
				{
					return typeof data.options == 'undefined' ? false : process(data.options);
				}
			});
		}
	});

	$('#field_coupon_tags').tagsInput({width: '100%', height: 'auto'});
});