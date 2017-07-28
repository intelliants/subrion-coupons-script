{if !empty($coupon_blocks.sponsored)}
    {foreach $coupon_blocks.featured as $listing}
        {include 'extra:coupons/list-coupons'}
    {/foreach}
{/if}