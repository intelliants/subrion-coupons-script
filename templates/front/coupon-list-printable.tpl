<div class="ia-item ia-item--border {if $listing.featured} ia-item---featured{/if}{if $listing.sponsored} ia-item--sponsored{/if} has-panel" data-affiliate-link="{if !empty($listing.affiliate_link) && 'http://' != $listing.affiliate_link}{$listing.affiliate_link}{elseif $listing.shop_affiliate_link && 'http://' != $listing.shop_affiliate_link}{$listing.shop_affiliate_link|escape}{/if}" id="coupon-list-{$listing.id}">
    <div class="ia-item__image">
        {if $listing.image}
            <a href="{ia_image file=$listing.image url=true type='large'}" rel="ia_lightbox">
                {ia_image file=$listing.image type='thumbnail' title=$listing.title class='img-responsive'}
            </a>
        {elseif $listing.shop_image}
            <a href="{ia_image file=$listing.shop_image url=true type='large'}" rel="ia_lightbox">
                {ia_image file=$listing.shop_image type='thumbnail' title=$listing.shop_title class='img-responsive'}
            </a>
        {else}
            <a href="{ia_url type='url' item='shops' data=$listing}" class="ia-item-thumbnail">
                <img src="//api.webthumbnail.org?width=150&height=150&format=png&screen=1024&url={$listing.shop_website}" alt="Generated by WebThumbnail.org" class="img-responsive">
            </a>
        {/if}

        <div class="coupon-rate text-center">
            <a href="#" class="thumbs-up" data-id="{$listing.id}" data-trigger="up"><i class="fa fa-thumbs-up"></i></a>
            <span class="rate good" id="thumb_result_{$listing.id}">{$listing.thumbs_num|default:0}</span>
            <a href="#" class="thumbs-down" data-id="{$listing.id}" data-trigger="down"><i class="fa fa-thumbs-down"></i></a>
        </div>
    </div>

    <div class="ia-item__labels">
        {if $listing.sponsored}<span class="label label-warning" title="{lang key='sponsored'}"><span class="fa fa-star"></span> {lang key='sponsored'}</span>{/if}
        {if $listing.featured}<span class="label label-info" title="{lang key='featured'}"><span class="fa fa-star-o"></span> {lang key='featured'}</span>{/if}
    </div>

    <div class="ia-item__content">
        <div class="ia-item__actions">
            {printFavorites item=$listing itemtype='coupons' guests=true}
            {accountActions item=$listing itemtype='coupons'}
        </div>

        <div class="ia-item__title">
            {ia_url type='link' item='coupons' data=$listing text=$listing.title} <small>{lang key='from'} {ia_url type='link' item='shop' data=$listing text=$listing.shop_title}</small>
        </div>

        {if $listing.expire_date != 0}
            <div class="coupon-expire text-danger">
                <span class="fa fa-clock-o"></span>
                <span class="js-countdown" data-countdown="{$listing.expire_date}" title="{lang key='coupon_expire'}"></span>
            </div>
        {/if}

        <p>{$listing.short_description|strip_tags|truncate:150:'...'}</p>

        <p class="coupon-tags text-fade-50">
            <span class="fa fa-tags"></span>
            {if $listing.tags}
                {lang key='tags'}: {$listing.tags|replace:',':', '}
            {else}
                {lang key='no_tags'}
            {/if}
        </p>
    </div>

    <div class="ia-item__panel">
        {if $listing.member_id}
            <span class="ia-item__panel__item pull-left">
                <span class="fa fa-user"></span> <a href="{$smarty.const.IA_URL}member/{$listing.account_username}.html">{$listing.account}</a>
            </span>
        {/if}

        {if $listing.category_parent_id > 0 && !empty($category) && $category.parent_id > 1}
            <span class="ia-item__panel__item pull-left">
                <span class="fa fa-folder-o"></span> <a href="{$category.link}">{$listing.category_title|escape}</a>
            </span>
        {/if}

        <span class="ia-item__panel__item pull-right">
            {$listing.views_num} {lang key='views_since'} {$listing.date_added|date_format}
        </span>
    </div>
</div>

{ia_add_js}
$(function () {
    $('#coupon-list-{$listing.id} h4.media-heading a:first').on('click', function(e)
    {
        e.preventDefault();

        var affiliateLink = $('#coupon-list-{$listing.id}').data('affiliate-link');
        var couponLink    = '{ia_url type='url' item='coupons' data=$listing}';

        if ('undefined' != typeof affiliateLink && '' != affiliateLink)
        {
            window.location.href = couponLink;
            window.open(affiliateLink, '_blank');
        }
        else
        {
            window.location.href = couponLink;
        }
    });
});
{/ia_add_js}