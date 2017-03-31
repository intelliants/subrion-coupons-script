{if !empty($coupon_blocks.top_categories)}
    <div class="list-group">
        {foreach $coupon_blocks.top_categories as $coupon_category}
            <a class="list-group-item{if isset($current_category) && $current_category.id == $coupon_category.id} active{/if}" href="{ia_url type='url' item='ccats' data=$coupon_category}">
                <span class="badge">{$coupon_category.num_all_coupons}</span>
                {$coupon_category.title}
            </a>
        {/foreach}
    </div>
{/if}