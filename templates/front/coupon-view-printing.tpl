<div class="couponItem">
    <div class="meta">
        <p>
            <span><span class="fa fa-calendar"></span> {$item.date_added|date_format:$core.config.date_format}</span>
            {if $item.expire_date != 0}
                <span class="text-danger"><span class="fa fa-clock-o"></span> {lang key='coupon_expire'} {$item.expire_date|date_format:$core.config.date_format}</span>
            {/if}
        </p>
        <p><span class="text-success"><span class="fa fa-eye"></span> {$item.views_num} {lang key='views_since'} {$item.date_added|date_format:$core.config.date_format}</span></p>
    </div>

    {if 'simple' == $item.type && $item.code}
        <div class="code clearfix">
            <div class="coupon-code">
                {$item.code}
                <div class="code-copy clip_{$item.id}" data-clipboard-text="{$item.code}" title="{lang key='coupon_copy_to_clipboard'}" data-affiliate-link="{if $item.affiliate_link && 'http://' != $item.affiliate_link}{$item.affiliate_link}{elseif $item.shop_affiliate_link && 'http://' != $item.shop_affiliate_link}{$item.shop_affiliate_link}{/if}"></div>
            </div>
        </div>
    {else}
        {if !empty($item.gallery)}
            {ia_add_media files='js: jquery/plugins/fotorama/fotorama, css: _IA_URL_js/jquery/plugins/fotorama/fotorama'}

            <div class="couponItem__gallery">
                <div class="fotorama" 
                     data-nav="thumbs"
                     data-width="100%"
                     data-ratio="16/9"
                     data-allowfullscreen="true">
                    {foreach $item.gallery as $entry}
                        <a class="couponItem__gallery__item" href="{ia_image file=$entry url=true type='large'}">{ia_image file=$entry type='large'}</a>
                    {/foreach}
                </div>
            </div>
        {elseif $item.image}
            <div class="text-center">
                {ia_image file=$item.image class='img-responsive' title=$item.title}
            </div>
        {elseif $item.shop_image}
            <a href="{ia_image file=$item.shop_image url=true type='large'}" rel="ia_lightbox">
                {ia_image file=$item.shop_image type='thumbnail' title=$item.shop_title class='img-responsive'}
            </a>
        {/if}
    {/if}

    {if 'deal' == $item.type}
        {if $item.item_price && '0.00' != $item.item_price}
            <hr>
            <div class="coupon-price">
                <div class="row">
                    <div class="col-sm-6">
                        {if $item.item_discount}
                            <span class="label label-disabled">{$core.config.coupon_item_price_currency}{$item.item_price}</span>
                            <span class="label label-success">{$core.config.coupon_item_price_currency}{$item.discounted_price}</span>
                            <span class="label-saving">{lang key='you_save'} {$core.config.coupon_item_price_currency}{$item.discount_saving}</span>
                        {else}
                            <span class="label label-warning">{$core.config.coupon_item_price_currency}{$item.item_price}</span>
                        {/if}
                    </div>
                    <div class="col-sm-6">
                        {if isset($item.buy_code_link)}
                            <a class="btn btn-info" href="{$item.buy_code_link}" rel="nofollow">{lang key='buy'}</a>
                        {/if}
                    </div>
                </div>
            </div>
            <hr>
        {else}
            <hr>
            <div class="coupon-price">
                <div class="row">
                    <div class="col-sm-6">
                        <span class="label label-success">{$core.config.coupon_item_price_currency}{$item.cost}</span>
                        {if $item.item_discount}
                            <span class="label-saving">
                                {lang key='you_save'} 
                                {if 'percent' == $item.item_discount_type}
                                    {$item.item_discount}%
                                {else}
                                    {$core.config.coupon_item_price_currency}{$item.item_discount|string_format:"%.2f"}
                                {/if}
                            </span>
                        {/if}
                    </div>
                    <div class="col-sm-6">
                        {if isset($item.buy_code_link)}
                            <a class="btn btn-info btn-block m-t-0" href="{$item.buy_code_link}" rel="nofollow">{lang key='buy'}</a>
                        {/if}
                    </div>
                </div>
            </div>
            <hr>
        {/if}
    {/if}

    <p class="text-box lead text-info">
        {$item.short_description|strip_tags}
    </p>

    {if !empty($item.description)}
        <div class="text-box">{$item.description}</div>
    {/if}

    <div class="clearfix">
        <div class="coupon-rate text-center pull-left">
            <a href="#" class="thumbs-up" data-id="{$item.id}" data-trigger="up"><i class="fa fa-thumbs-up"></i></a>
            <span class="rate good" id="thumb_result_{$item.id}">{$item.thumbs_num|default:0}</span>
            <a href="#" class="thumbs-down"  data-id="{$item.id}" data-trigger="down"><i class="fa fa-thumbs-down"></i></a>
        </div>

        {if $item.affiliate_link && 'http://' != $item.affiliate_link}
            <a href="{$item.affiliate_link}" class="btn btn-warning pull-right" target="_blank">{lang key='more_info'}</a>
        {elseif $item.shop_affiliate_link && 'http://' != $item.shop_affiliate_link}
            <a href="{$item.shop_affiliate_link}" class="btn btn-warning pull-right" target="_blank">{lang key='more_info'}</a>
        {/if}
    </div>

    {if $item.tags}
        <p class="text-fade-50">
            <span class="fa fa-tags"></span> {lang key='tags'}:
            {if $item.tags}
                {foreach explode(',', $item.tags) as $tag}
                    <a href="{$tag}" class="tag">{$tag}</a>{if !$tag@last}, {/if}
                {/foreach}
            {else}
                {lang key='no_tags'}
            {/if}
        </p>
    {/if}

    <div class="meta clearfix">
        <span><span class="fa fa-shopping-cart"></span> {ia_url type='link' item='shops' data=$shop text=$shop.title}</span>
        {if $item.category_id}
            <span><span class="fa fa-folder-o"></span> {ia_url type='link' item='ccats' data=$coupon_category text=$coupon_category.title rel='tag'}</span>
        {/if}
        {if $item.member_id}
            <span><span class="fa fa-user"></span> <a href="{$smarty.const.IA_URL}member/{$item.account_username}.html">{$item.account}</a></span>
        {/if}
    </div>
</div>

{ia_hooker name='smartyViewCouponBeforeFooter'}
{ia_add_media files='js:_IA_URL_modules/coupons/js/front/view'}