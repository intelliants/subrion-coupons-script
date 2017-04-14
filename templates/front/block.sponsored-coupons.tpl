{if !empty($coupon_blocks.sponsored)}
    {foreach $coupon_blocks.sponsored as $coupon}
        <div class="media ia-item ia-item-bordered-bottom">
            <div class="media-body">
                <h3 class="media-heading">
                    {ia_url type='link' item='coupons' data=$coupon text=$coupon.title} <small>{lang key='from'} <a href="{$smarty.const.IA_URL}shop/{$coupon.shop_alias}.html">{$coupon.shop_title|escape}</a></small>
                </h3>
                {if $coupon.shop_image}
                    <a href="{ia_image file=$coupon.shop_image url=true type='large'}" class="ia-item-thumbnail" rel="ia_lightbox">
                        {ia_image file=$coupon.shop_image title=$coupon.shop_title type='thumbnail' class='media-object'}
                    </a>
                {else}
                    <a href="{$smarty.const.IA_URL}shop/{$coupon.shop_alias}.html" class="ia-item-thumbnail">
                        <img src="//api.webthumbnail.org?width=150&height=150&format=png&screen=1024&url={$coupon.shop_website}" alt="Generated by WebThumbnail.org" class="media-object">
                    </a>
                {/if}

                {if $coupon.expire_date != 0}
                    <div class="coupon-expire text-danger">
                        <span class="fa fa-clock-o"></span>
                        <span class="js-countdown" data-countdown="{$coupon.expire_date}" title="{lang key='coupon_expire'}"></span>
                    </div>
                {/if}

                <div class="ia-item-body">{$coupon.short_description|strip_tags|truncate:150:'...'}</div>
                <div class="coupon-tags">
                    <i class="icon-tags"></i>
                    {if $coupon.tags}
                        {lang key='tags'}: {$coupon.tags|replace:',':', '}
                    {else}
                        {lang key='no_tags'}
                    {/if}
                </div>
            </div>

            <div class="ia-item-panel" style="border-top: 1px solid #eee;">
                {if $coupon.member_id}
                    <div class="coupon-user pull-left">
                        <i class="icon-user"></i> <a href="{$smarty.const.IA_URL}member/{$coupon.account_username}.html">{$coupon.account}</a>
                    </div>
                {/if}

                {if $coupon.category_parent_id > 0 && !empty($category) && $category.parent_id > 1}
                    <div class="coupon-category pull-left">
                        <i class="icon-folder-close"></i> <a href="{$category.link}">{$coupon.category_title|escape}</a>
                    </div>
                {/if}

                {if $member && $member.id != $coupon.member_id}
                    <div class="pull-left">
                        {printFavorites item=$coupon itemtype='coupons'}
                    </div>
                {/if}

                <div class="coupon-stats pull-left">{$coupon.views_num} {lang key='views_since'} {$coupon.date_added|date_format:$core.config.date_format}</div>
            </div>
        </div>

        {ia_add_js}
$(function()
{
    var clip_{$coupon.id} = new ZeroClipboard($('.clip_{$coupon.id}'),
    {
        moviePath: '{$smarty.const.IA_CLEAR_URL}js/utils/zeroclipboard/ZeroClipboard.swf',
        hoverClass: 'hover',
        activeClass: 'approval'
    });

    clip_{$coupon.id}.on('complete', function(client, args)
    {
        var affiliateLink = $(this).data('affiliate-link');
        var couponLink    = $(this).data('coupon-link');

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
    {/foreach}
{/if}