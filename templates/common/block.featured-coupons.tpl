{if !empty($coupon_blocks.featured)}
	{foreach $coupon_blocks.featured as $listing}
		{include 'extra:coupons/list-coupons'}
	{/foreach}
{/if}