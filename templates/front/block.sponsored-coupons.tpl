{if !empty($coupon_blocks.sponsored)}
    {foreach $coupon_blocks.sponsored as $listing}
        {include 'module:coupons/list-coupons.tpl'}
    {/foreach}
{/if}