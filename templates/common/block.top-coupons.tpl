{if !empty($coupon_blocks.top)}
	{foreach $coupon_blocks.top as $coupon}
		{include file="extra:coupons/coupon-list-{$coupon.coupon_type}"}
	{/foreach}
{/if}