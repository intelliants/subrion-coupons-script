<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
	{preventCsrf}

	{capture name='title' append='field_after'}
		<div class="row" id="title_alias_fieldzone"{if iaCore::ACTION_EDIT != $pageAction && empty($smarty.post.save)} style="display: none;"{/if}>
			<label class="col col-lg-2 control-label" for="input-alias">{lang key='title_alias'}</label>

			<div class="col col-lg-4">
				<input type="text" name="title_alias" id="input-alias" value="{if isset($item.title_alias)}{$item.title_alias|escape:'html'}{/if}">
				<p class="help-block text-break-word">{lang key='page_url_will_be'}: <span class="text-danger" id="title_url">{$smarty.const.IA_URL}</span></p>
			</div>
		</div>
	{/capture}

	{capture name='coupons' append='fieldset_before'}
		{include 'tree.tpl' url="{$smarty.const.IA_ADMIN_URL}coupons/categories/tree.json?noroot"}

		<div class="row">
			<label class="col col-lg-2 control-label" for="input-shop">{lang key='shop'} <span class="required">*</span></label>

			<div class="col col-lg-4">
				<input type="text" name="shop" id="input-shop" value="{$shopName|escape:'html'}" autocomplete="off">
			</div>
		</div>
	{/capture}

	{ia_hooker name='smartyAdminSubmitItemBeforeFields'}

	{include 'field-type-content-fieldset.tpl' isSystem=true}
</form>
{ia_add_media files='tagsinput, js:_IA_URL_modules/coupons/js/admin/coupons'}