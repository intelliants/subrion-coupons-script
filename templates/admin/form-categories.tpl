<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
	{preventCsrf}
	{capture name='categories' append='fieldset_before'}
		<div class="row">
			<label class="col col-lg-2 control-label">
				{lang key='coupon_category'}<br>
				<a href="#" class="categories-toggle" id="js-tree-toggler">{lang key='open_close'}</a>
			</label>
			<div class="col col-lg-4">
				<input type="text" id="js-category-label" value="{$parent.title|escape:'html'}" disabled>
				<div id="js-tree" class="tree categories-tree" {if iaCore::ACTION_EDIT == $pageAction}style="display:none"{/if}></div>
				<input type="hidden" name="parent_id" id="input-category" value="{$item.parent_id}">
				{ia_add_js}
$(function()
{
	new IntelliTree(
	{
		url: intelli.config.admin_url + '/coupons/categories/tree.json',
		onchange: intelli.fillUrlBox,
		nodeOpened: [{$parent.parents}],
		nodeSelected: {$item.parent_id}
	});
});
				{/ia_add_js}
				{ia_add_media files='tree'}
			</div>
		</div>
	{/capture}

	{capture name='title' append='field_after'}
		<div class="row" id="field-title-alias"{if iaCore::ACTION_EDIT != $pageAction && empty($smarty.post.save)} style="display: none;"{/if}>
			<label class="col col-lg-2 control-label" for="field_title_alias">{lang key='title_alias'}</label>

			<div class="col col-lg-4">
				<input type="text" name="title_alias" id="field_title_alias" value="{if isset($item.title_alias)}{$item.title_alias}{/if}">
				<p class="help-block text-break-word">{lang key='page_url_will_be'}: <span class="text-danger" id="title_url">{$smarty.const.IA_URL}</span></p>
			</div>
		</div>
	{/capture}

	{include file='field-type-content-fieldset.tpl' isSystem=true}
</form>
{ia_hooker name='smartyAdminSubmitListingBeforeFooter'}
{ia_add_media files='js:_IA_URL_packages/coupons/js/admin/categories'}