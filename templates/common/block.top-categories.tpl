{if !empty($coupon_blocks.top_categories)}
	<ul class="ia-list-items">
		{foreach $coupon_blocks.top_categories as $coupon_category}
			<li class="cat-item{if isset($current_category) && $current_category.id == $coupon_category.id} active{/if}">{ia_url type='link' item='ccats' data=$coupon_category text="{$coupon_category.title}"} &mdash; {$coupon_category.num_all_coupons}</li>
		{/foreach}
	</ul>
{/if}