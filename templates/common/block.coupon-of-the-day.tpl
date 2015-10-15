{if !empty($coupon_blocks.oftheday)}
	{foreach $coupon_blocks.oftheday as $coupon}
		{include file="extra:coupons/coupon-list-{$coupon.coupon_type}"}
	{/foreach}
{/if}