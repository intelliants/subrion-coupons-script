<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
	{preventCsrf}

	{capture name='title' append='field_after'}
		<div class="row" id="field-title-alias"{if iaCore::ACTION_EDIT != $pageAction && empty($smarty.post.save)} style="display: none;"{/if}>
			<label class="col col-lg-2 control-label" for="input-alias">{lang key='title_alias'}</label>

			<div class="col col-lg-4">
				<input type="text" name="title_alias" id="input-alias" value="{if isset($item.title_alias)}{$item.title_alias|escape:'html'}{/if}">
				<p class="help-block text-break-word">{lang key='page_url_will_be'}: <span class="text-danger" id="title_url">{$smarty.const.IA_URL}</span></p>
			</div>
		</div>
	{/capture}

	{capture name='coupons' append='fieldset_before'}
		<div class="row">
			<label class="col col-lg-2 control-label">
				{lang key='coupon_category'} <span class="required">*</span><br>
				<a href="#" class="categories-toggle" id="js-tree-toggler">{lang key='open_close'}</a>
			</label>
			<div class="col col-lg-4">
				<input type="text" id="js-category-label" value="{if $category}{$category.title|escape:'html'}{else}{lang key='field_category_id_tooltip'}{/if}" disabled>
				<div id="js-tree" class="tree categories-tree" {if $item.category_id != -1}style="display:none"{/if}></div>
				<input type="hidden" name="category_id" id="input-category" value="{$item.category_id}">
				{ia_add_js}
$(function()
{
	new IntelliTree(
	{
		url: intelli.config.admin_url + '/coupons/categories/tree.json',
		onchange: intelli.fillUrlBox,
		nodeOpened: [{$category.parents}],
		nodeSelected: {$item.category_id}
	});
});
				{/ia_add_js}
				{ia_add_media files='tree'}
			</div>
		</div>

		<div class="row">
			<label class="col col-lg-2 control-label" for="input-shop">{lang key='shop'} <span class="required">*</span></label>

			<div class="col col-lg-4">
				<input type="text" name="shop" id="input-shop" value="{$item.shop|escape:'html'}" autocomplete="off">
			</div>
		</div>
	{/capture}

	{ia_hooker name='smartyAdminSubmitItemBeforeFields'}

	{include file='field-type-content-fieldset.tpl' isSystem=true}
</form>
{ia_add_media files='tagsinput, js:_IA_URL_packages/coupons/js/admin/coupons'}