{if !empty($coupon_blocks.new)}
	{foreach $coupon_blocks.new as $coupon}
		{include file="extra:coupons/coupon-list-{$coupon.coupon_type}"}
	{/foreach}
{/if}