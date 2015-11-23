{if !empty($coupon_blocks.new)}
	{foreach $coupon_blocks.new as $listing}
		{include 'extra:coupons/list-coupons'}
	{/foreach}
{/if}