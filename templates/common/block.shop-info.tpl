{if isset($shop)}
	<div class="ia-item-author">
		<div class="ia-item-author__image">
			{if $shop.shop_image}
				<a href="{printImage imgfile=$shop.shop_image.path url=true type='full'}" rel="ia_lightbox">
					{printImage imgfile=$shop.shop_image.path title=$shop.title|escape:'html' class='img-responsive center-block'}
				</a>
			{else}
				<a href="{$smarty.const.IA_URL}shop/{$shop.title_alias}.html">
					<img src="http://api.webthumbnail.org?width=150&height=150&format=png&screen=1024&url={$shop.website}" alt="Generated by WebThumbnail.org" class="img-responsive center-block">
				</a>
			{/if}
		</div>

		<div class="ia-item-author__content">
			<h4 class="ia-item__title"><a href="{$smarty.const.IA_URL}shop/{$shop.title_alias}.html">{$shop.title}</a></h4>

			<div class="ia-item__additional">
				<p>{lang key='coupons'}: {$shop.num_coupons|string_format:'%d'}</p>
				<p><span class="fa fa-link"></span> 
				{if $shop.affiliate_link && 'http://' != $shop.affiliate_link}
					<a href="{$shop.affiliate_link}">{lang key='website'}</a>
				{else}
					<a href="{$shop.website}">{lang key='website'}</a>
				{/if}</p>
			</div>
		</div>

		{ia_hooker name='smartyViewListingAuthorBlock'}
	</div>
{/if}