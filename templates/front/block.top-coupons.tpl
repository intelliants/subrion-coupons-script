{if !empty($coupon_blocks.top)}
    {foreach $coupon_blocks.top as $listing}
        {include 'extra:coupons/list-coupons'}
    {/foreach}
{/if}