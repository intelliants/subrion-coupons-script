{if !empty($coupon_blocks.featured_shops)}
    {foreach $coupon_blocks.featured_shops as $featured_shop}
    <div class="featured-shop-item">
            <a href="{$featured_shop.link}" class="shop-thumbnail">
                {if !empty($featured_shop.shop_image)}
                    {ia_image file=$featured_shop.shop_image type='thumbnail' title=$featured_shop.title width=125 height=125}
                {else}
                    <img src="//api.webthumbnail.org?width=125&height=125&format=png&screen=1024&url={$featured_shop.website}" alt="Generated by WebThumbnail.org">
                {/if}
            </a>
            <h5><a href="{$featured_shop.link}">{$featured_shop.title|escape}</a></h5>
            <p>{$featured_shop.description|strip_tags|truncate:150:'...'}</p>
        </div>
    {/foreach}
{/if}