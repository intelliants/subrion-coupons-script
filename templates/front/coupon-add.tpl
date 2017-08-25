<form method="post" enctype="multipart/form-data" class="ia-form" id="coupon_form">
    {preventCsrf}

    {include 'plans.tpl' item=$item}

    {capture name='title' append='field_before'}
        <div class="form-group">
            <label for="category_id">{lang key='coupon_category'}: <span class="required">*</span></label>
            <select class="form-control" name="category_id" id="category_id">
                <option>{lang key='_select_'}</option>
                {foreach $coupon_categories as $cid => $category}
                    <option value="{$cid}"{if isset($item.category_id) && $cid == $item.category_id} selected{/if}>{section name='leveld' loop=$category.level - 1}--{/section}{$category.title|escape}</option>
                {/foreach}
            </select>
        </div>
    {/capture}

    {capture append='tabs_after' name='common'}
        <div class="fieldset">
            <div class="fieldset__header">{lang key='shop_information'}</div>
            <div class="fieldset__content">
                <div class="form-group">
                    <label for="shop">{lang key='shop'}: <span class="required">*</span></label>
                    <input class="form-control" type="text" name="shop" id="shop" value="{if isset($item.shop_title)}{$item.shop_title|escape}{elseif isset($smarty.post.shop)}{$smarty.post.shop|escape}{/if}" size="50" autocomplete="off">
                </div>

                <div class="form-group" id="shop_website">
                    <label for="website">{lang key='shop_website'}:</label>
                    <input class="form-control" type="text" name="website" id="website" value="{if isset($item.shop_website)}{$item.shop_website|escape}{elseif isset($smarty.post.website)}{$smarty.post.website|escape}{/if}" size="50" placeholder="http://"{if 'edit' == $pageAction} disabled="disabled"{/if}>
                </div>
            </div>
        </div>

        {ia_hooker name='smartyListingSubmitBeforeFooter'}

        {include 'captcha.tpl'}

        <div class="fieldset__actions">
            <input type="submit" name="data-coupon" class="btn btn-primary" value="{if iaCore::ACTION_ADD == $pageAction}{lang key='add_coupon'}{else}{lang key='save'}{/if}">
        </div>
    {/capture}

    {include 'item-view-tabs.tpl'}
</form>

{ia_add_media files='tagsinput,datepicker,js:jquery/plugins/jquery.textcounter,js:_IA_URL_modules/coupons/js/front/manage'}