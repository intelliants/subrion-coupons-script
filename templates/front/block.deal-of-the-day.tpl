{var_dump($coupon_blocks.oftheday.link)}
{if $coupon_blocks.oftheday}
    <div class="ia-item">
        <div class="m-b">
            {if $coupon_blocks.oftheday.gallery}
                <a href="{$coupon_blocks.oftheday.link}" class="center-block">
                    {ia_image file=$coupon_blocks.oftheday.gallery[0] type='thumbnail' title=$coupon_blocks.oftheday.title class='img-responsive'}
                </a>
            {elseif $coupon_blocks.oftheday.shop_image}
                <a href="{ia_url type='url' item='shops' data=$coupon_blocks.oftheday}" class="center-block">
                    {ia_image file=$coupon_blocks.oftheday.shop_image title=$coupon_blocks.oftheday.shop_title class='img-responsive'}
                </a>
            {else}
                <a href="{ia_url type='url' item='shops' data=$coupon_blocks.oftheday}" class="center-block">
                    <img src="//api.webthumbnail.org?width=150&height=150&format=png&screen=1024&url={$coupon_blocks.oftheday.shop_website}" alt="Generated by WebThumbnail.org" class="img-responsive">
                </a>
            {/if}
        </div>

        <div class="ia-item__content">
            <div class="ia-item__title">
                {ia_url type='link' item='coupons' data=$coupon_blocks.oftheday text=$coupon_blocks.oftheday.title} <small>{lang key='from'} <a href="{$smarty.const.IA_URL}shop/{$coupon_blocks.oftheday.shop_alias}.html">{$coupon_blocks.oftheday.shop_title}</a></small>
            </div>

            <p>{$coupon_blocks.oftheday.short_description|strip_tags|truncate:150:'...'}</p>
        </div>
    </div>
{/if}