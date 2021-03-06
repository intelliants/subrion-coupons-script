<div class="couponItem">
    <div class="meta">
        <p>
            <span><span class="fa fa-calendar"></span> {$item.date_added|date_format}</span>
            {if $item.expire_date != 0}
                <span class="text-danger"><span class="fa fa-clock-o"></span> {lang key='coupon_expire'} {$item.expire_date|date_format}</span>
            {/if}
        </p>
        <p><span class="text-success"><span class="fa fa-eye"></span> {$item.views_num} {lang key='views_since'} {$item.date_added|date_format}</span></p>
    </div>

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
        <button class="btn btn-default js-cmd-print-coupon m-t"><span class="fa fa-print"></span> {lang key='print_coupon'}</button>
    {elseif $item.shop_image}
        <a href="{ia_image file=$item.shop_image url=true type='large'}" rel="ia_lightbox">
            {ia_image file=$item.shop_image type='thumbnail' title=$item.shop_title class='img-responsive'}
        </a>
    {/if}

    {if 'deal' == $item.type}
        {if floatval($item.item_price)}
            <hr>
            <div class="coupon-price">
                <div class="row">
                    <div class="col-sm-6">
                        {if floatval($item.item_discount)}
                            <span class="label label-disabled">{$item.item_price_formatted}</span>
                            <span class="label label-success">{$item.discounted_price_formatted}</span>
                            <span class="label-saving">{lang key='you_save'} {$item.discount_saving_formatted}</span>
                        {else}
                            <span class="label label-warning">{$item.item_price_formatted}</span>
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
                        <span class="label label-success">{$item.cost_formatted}</span>
                        {if $item.item_discount}
                            <span class="label-saving">
                                {lang key='you_save'} {$item.item_discount_formatted}
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

    <div class="code-share">
        <!-- AddThis Button BEGIN -->
        <div class="addthis_toolbox addthis_default_style ">
            <a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
            <a class="addthis_button_tweet"></a>
            <a class="addthis_counter addthis_pill_style"></a>
        </div>
        <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#username=xa-4c6e050a3d706b83"></script>
        <!-- AddThis Button END -->
    </div>

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

{if isset($codes)}
    {include 'module:coupons/block.codes.tpl'}
{/if}