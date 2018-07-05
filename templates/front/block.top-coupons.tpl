{if !empty($coupon_blocks.top)}
    {foreach $coupon_blocks.top as $listing}
        {include 'module:coupons/list-coupons.tpl'}
    {/foreach}
{/if}