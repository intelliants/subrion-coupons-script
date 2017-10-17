<div class="ia-item ia-item--border m-b">
    <div class="ia-item__content">
        <div class="ia-item__actions">
            {printFavorites item=$item itemtype='coupons' guests=true}
            {accountActions item=$item itemtype='coupons'}
        </div>

        <div class="ia-item__title">
            {ia_url type='link' item='coupons' data=$item text=$item.title} <small>{lang key='from'} <a href="{$smarty.const.IA_URL}shop/{$item.shop_alias}.html">{$item.shop_title}</a></small>
        </div>

        {if $item.item_price && $item.item_price > 0}
            <div class="coupon-price clearfix">
                {if $item.activations_left < 10}
                    <p>Hurry up. Only {$item.activations_left} activations left. {$item.activations_sold} sold.</p>
                {/if}

                {if $item.item_discount}
                    <span class="label label-disabled">{$core.config.coupon_item_price_currency}{$item.item_price}</span>
                    <span class="label label-success">{$core.config.coupon_item_price_currency}{$item.discounted_price|string_format:"%.2f"}</span>
                    <span class="label-saving">{lang key='you_save'} {$core.config.coupon_item_price_currency}{$item.discount_saving|string_format:"%.2f"}</span>
                {else}
                    <span class="label label-warning">{$core.config.coupon_item_price_currency}{$item.item_price}</span>
                {/if}

                {if $core.config.purchase_codes}
                    {code coupon=$item}
                {/if}
            </div>
        {/if}

        {if $item.expire_date != 0}
            <div class="coupon-expire text-danger">
                <span class="fa fa-clock-o"></span>
                <span class="js-countdown" data-countdown="{$item.expire_date}" title="{lang key='coupon_expire'}"></span>
            </div>
        {/if}

        <p>{$item.short_description|strip_tags|truncate:150:'...'}</p>

        <p class="coupon-tags text-fade-50">
            <span class="fa fa-tags"></span>
            {if $item.tags}
                {lang key='tags'}: {$item.tags|replace:',':', '}
            {else}
                {lang key='no_tags'}
            {/if}
        </p>
    </div>

    <div class="ia-item__panel">
        {if $item.member_id}
            <span class="ia-item__panel__item pull-left">
                <span class="fa fa-user"></span> <a href="{$smarty.const.IA_URL}member/{$item.account_username}.html">{$item.account}</a>
            </span>
        {/if}

        {if $item.category_parent_id > 0 && !empty($category) && $category.parent_id > 1}
            <span class="ia-item__panel__item pull-left">
                <span class="fa fa-folder-o"></span> <a href="{$category.link}">{$item.category_title|escape}</a>
            </span>
        {/if}

        <span class="ia-item__panel__item pull-right">
            {$item.views_num} {lang key='views_since'} {$item.date_added|date_format}
        </span>
    </div>
</div>

{if $item.codes}
    <table class="table">
        <thead>
            <tr>
                <th>{lang key='code'}</th>
                <th>{lang key='status'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $item.codes as $code}
                <tr>
                    <td>{$code.code}</td>
                    <td><span class="label label-{if 'active' == $code.status}info{elseif 'inactive' == $code.status}warning{elseif 'used'== $code.status}success{/if}">{lang key={$code.status}}</span></td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{/if}