<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
    {preventCsrf}
    <input type="hidden" id="input-id" value="{$id}">

    {if $website_field_exists}
        {capture name='website' append='field_after'}
    {else}
        {capture name='title' append='field_after'}
    {/if}
        <div class="row" id="title_alias_fieldzone"{if iaCore::ACTION_EDIT != $pageAction && empty($smarty.post.save)} style="display: none;"{/if}>
            <label class="col col-lg-2 control-label" for="field_title_alias">{lang key='title_alias'}</label>

            <div class="col col-lg-4">
                <input type="text" name="title_alias" id="field_title_alias" value="{if isset($item.title_alias)}{$item.title_alias}{/if}">
                <p class="help-block text-break-word">{lang key='page_url_will_be'}: <span class="text-danger" id="title_url">{$smarty.const.IA_URL}</span></p>
            </div>
        </div>
    {/capture}

    {include 'field-type-content-fieldset.tpl' isSystem=true}
</form>
{ia_hooker name='smartyAdminSubmitItemBeforeFooter'}
{ia_add_media files='js:_IA_URL_modules/coupons/js/admin/shops'}