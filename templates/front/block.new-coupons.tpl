{if !empty($coupon_blocks.new)}
    {foreach $coupon_blocks.new as $listing}
        {include 'module:coupons/list-coupons.tpl'}
    {/foreach}
{/if}