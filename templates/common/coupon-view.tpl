<div class="couponItem">
	<div class="meta">
		<p>
			<span><span class="fa fa-calendar"></span> {$item.date_added|date_format:$core.config.date_format}</span>
			{if $item.expire_date != 0}
			<span class="text-danger"><span class="fa fa-clock-o"></span> {lang key='coupon_expire'} {$item.expire_date|date_format:$core.config.date_format}</span>
			{/if}
		</p>
		<p><span class="text-success"><span class="fa fa-eye"></span> {$item.views_num} {lang key='views_since'} {$item.date_added|date_format:$core.config.date_format}</span></p>
	</div>

	{if 'simple' == $item.coupon_type && $item.coupon_code}
		<div class="code clearfix">
			{if $core.config.purchase_coupon_codes}
				{coupon_code coupon=$item}
			{elseif $core.config.hide_coupon_code}
				<div id="coupon-view-{$item.id}" class="coupon-code coupon-code--hidden">
					<a href="{if $item.affiliate_link && 'http://' != $item.affiliate_link}{$item.affiliate_link}{elseif $item.shop_affiliate_link && 'http://' != $item.shop_affiliate_link}{$item.shop_affiliate_link}{else}#{/if}" target="_blank">{lang key='show_coupon_code'}</a>
					<span>{$item.coupon_code}</span>
				</div>
			{else}
				<div class="coupon-code">
					{$item.coupon_code}
					<div class="code-copy clip_{$item.id}" data-clipboard-text="{$item.coupon_code}" title="{lang key='coupon_copy_to_clipboard'}" data-affiliate-link="{if $item.affiliate_link && 'http://' != $item.affiliate_link}{$item.affiliate_link}{elseif $item.shop_affiliate_link && 'http://' != $item.shop_affiliate_link}{$item.shop_affiliate_link}{/if}"></div>
				</div>
			{/if}
		</div>
	{else}
		<div class="text-center">
			<a href="{printImage imgfile=$item.coupon_image url=true fullimage=true}" rel="ia_lightbox[{$item.title}]">
				{printImage imgfile=$item.coupon_image fullimage="true" class='img-responsive' title=$item.title|escape:'html'}
			</a>
		</div>
	{/if}

	{if $item.item_price && '0.00' != $item.item_price}
		<hr>
		<div class="coupon-price">
			{if $item.item_discount}
				{if 'fixed' == $item.item_discount_type}
					{assign var=discount_total value=($item.item_price - $item.item_discount)}
					{assign discount $item.item_discount}
				{else}
					{assign var=discount_total value=($item.item_price - $item.item_price * $item.item_discount / 100)}
					{assign var=discount value=($item.item_price * $item.item_discount / 100)}
				{/if}

				<span class="label label-disabled">{$core.config.coupon_item_price_currency}{$item.item_price}</span>
				<span class="label label-success">{$core.config.coupon_item_price_currency}{$discount_total|string_format:"%.2f"}</span>
				<span class="label-saving">{lang key='you_save'} {$core.config.coupon_item_price_currency}{$discount|string_format:"%.2f"}</span>
			{else}
				<span class="label label-warning">{$core.config.coupon_item_price_currency}{$item.item_price}</span>
			{/if}
		</div>
		<hr>
	{/if}

	<div class="code-share">
		<!-- AddThis Button BEGIN -->
		<div class="addthis_toolbox addthis_default_style ">
			<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
			<a class="addthis_button_tweet"></a>
			<a class="addthis_counter addthis_pill_style"></a>
		</div>
		<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#username=xa-4c6e050a3d706b83"></script>
		<!-- AddThis Button END -->
	</div>

	<p class="text-box lead text-info">
		{$item.short_description|strip_tags}
	</p>

	{if $item.description}
		<div class="text-box">{$item.description}</div>
	{/if}

	<div class="clearfix">
		<div class="coupon-rate text-center pull-left">
			<a href="#" class="thumbs-up" data-id="{$item.id}" data-trigger="up"><i class="fa fa-thumbs-up"></i></a>
			<span class="rate good" id="thumb_result_{$item.id}">{$item.thumbs_num|default:0}</span>
			<a href="#" class="thumbs-down"  data-id="{$item.id}" data-trigger="down"><i class="fa fa-thumbs-down"></i></a>
		</div>

		{if $item.affiliate_link && 'http://' != $item.affiliate_link}
			<a href="{$item.affiliate_link}" class="btn btn-warning pull-right" target="_blank">{lang key='more_info'}</a>
		{elseif $item.shop_affiliate_link && 'http://' != $item.shop_affiliate_link}
			<a href="{$item.shop_affiliate_link}" class="btn btn-warning pull-right" target="_blank">{lang key='more_info'}</a>
		{/if}
	</div>

	{if $item.coupon_tags}
		<p class="text-fade-50">
			<span class="fa fa-tags"></span> {lang key='tags'}:
			{if $item.coupon_tags}
				{foreach explode(',', $item.coupon_tags) as $tag}
					<a href="{$tag}" class="tag">{$tag}</a>{if !$tag@last}, {/if}
				{/foreach}
			{else}
				{lang key='no_coupon_tags'}
			{/if}
		</p>
	{/if}

	<div class="meta clearfix">
		<span><span class="fa fa-shopping-cart"></span> {ia_url type='link' item='shops' data=$shop text=$shop.title}</span>
		{if $item.category_id}
			<span><span class="fa fa-folder-o"></span> {ia_url type='link' item='ccats' data=$coupon_category text=$coupon_category.title rel='tag'}</span>
		{/if}
		{if $item.member_id}
			<span><span class="fa fa-user"></span> <a href="{$smarty.const.IA_URL}member/{$item.account_username}.html">{$item.account}</a></span>
		{/if}
	</div>
</div>

{ia_hooker name='smartyViewCouponBeforeFooter'}
{ia_add_media files='js:_IA_URL_packages/coupons/js/front/view'}

{ia_add_js}
$(function()
{
	// Copy code
	var clip_{$item.id} = new ZeroClipboard($('.clip_{$item.id}'),
	{
		moviePath: '{$smarty.const.IA_CLEAR_URL}js/utils/zeroclipboard/ZeroClipboard.swf',
		hoverClass: 'hover',
		activeClass: 'active'
	});

	clip_{$item.id}.on('complete', function(client, args)
	{
		var affiliateLink = $(this).data('affiliate-link');

		if('undefined' != typeof affiliateLink && '' != affiliateLink) {
			window.open(affiliateLink);
		}
	});

	// Picking tags
	$('.couponItem .tag').on('click', function(e)
	{
		e.preventDefault();

		var tag = $.trim($(this).attr('href'));
		$('input[name="q"]').val(tag).closest('form').submit();
	});
});

$(function() {
	$('.js-delete-coupon').on('click', function(e) {
		e.preventDefault();

		intelli.confirm(_t('delete_coupon_confirmation'), { url: $(this).attr('href') });
	});
});
{/ia_add_js}

{if $core.config.hide_coupon_code}
	{ia_add_js}
$(function()
{
	$('#coupon-view-{$item.id} > a').on('click', function(e)
	{
		if ('#' === $(this).attr('href'))
		{
			e.preventDefault();
		}
		$(this).hide().next().show();
	});
});
	{/ia_add_js}
{/if}