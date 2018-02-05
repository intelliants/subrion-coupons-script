{if !empty($coupon_blocks.featured)}
    {foreach $coupon_blocks.featured as $listing}
        {include 'module:coupons/list-coupons.tpl'}
    {/foreach}
{/if}