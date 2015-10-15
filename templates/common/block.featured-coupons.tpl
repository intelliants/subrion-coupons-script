{if !empty($coupon_blocks.featured)}
	{foreach $coupon_blocks.featured as $coupon}
		{include file="extra:coupons/coupon-list-{$coupon.coupon_type}"}
	{/foreach}
{/if}