<form action="{$smarty.const.IA_SELF}" method="post" enctype="multipart/form-data" class="ia-form" id="coupon_form">

	{include file='plans.tpl' item=$item}

	{capture name='title' append='field_before'}
		<div class="control-group">
			<label class="control-label" for="category_id">{lang key='coupon_category'}: <span class="required">*</span></label>
			<div class="controls">
				<select name="category_id" id="category_id">
					<option>{lang key='_select_'}</option>
					{foreach $coupon_categories as $category}
						<option value="{$category.id}"{if isset($item.category_id) && $category.id == $item.category_id} selected="selected"{/if}>{section name='leveld' loop=$category.level - 1}--{/section}{$category.title}</option>
					{/foreach}
				</select>
			</div>
		</div>
	{/capture}

	{capture append='tabs_after' name='common'}
		<div class="fieldset-wrapper">
			<div class="fieldset">
				<h3 class="title">{lang key='shop_information'}</h3>
				<div class="content">
					<div class="control-group">
						<label class="control-label" for="shop">{lang key='shop'}: <span class="required">*</span></label>
						<div class="controls">
							<input type="text" name="shop" id="shop" value="{if isset($item.shop_title)}{$item.shop_title}{elseif isset($smarty.post.shop)}{$smarty.post.shop}{/if}" size="50" autocomplete="off">
						</div>
					</div>

					<div class="control-group" id="shop_website">
						<label class="control-label" for="website">{lang key='shop_website'}:</label>
						<div class="controls">
							<input type="text" name="website" id="website" value="{if isset($item.shop_website)}{$item.shop_website}{elseif isset($smarty.post.website)}{$smarty.post.website}{/if}" size="50" placeholder="http://"{if 'edit' == $pageAction} disabled="disabled"{/if}>
						</div>
					</div>
				</div>
			</div>
		</div>

		{ia_hooker name='smartyListingSubmitBeforeFooter'}

		{include file='captcha.tpl'}

		<div class="actions">
			<input type="submit" name="data-coupon" class="btn btn-primary" value="{if iaCore::ACTION_ADD == $pageAction}{lang key='add_coupon'}{else}{lang key='save'}{/if}">
		</div>
	{/capture}

	{include file='item-view-tabs.tpl'}
</form>

{ia_add_media files='tagsinput,datepicker,js:jquery/plugins/jquery.textcounter,js:_IA_URL_packages/coupons/js/front/manage'}