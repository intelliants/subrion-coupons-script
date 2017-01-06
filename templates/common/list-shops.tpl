<div class="media ia-item ia-item-bordered shops-list-item">
	{if $listing.shop_image}
		<a href="{printImage imgfile=$listing.shop_image.path url=true type='full'}" class="pull-left ia-item-thumbnail" rel="ia_lightbox">
			{printImage imgfile=$listing.shop_image.path title=$listing.title|escape:'html' class='media-object'}
		</a>
	{else}
		<a href="{$smarty.const.IA_URL}shop/{$listing.title_alias}.html" class="pull-left ia-item-thumbnail">
			<img src="http://api.webthumbnail.org?width=150&height=150&format=png&screen=1024&url={$listing.website}" alt="Generated by WebThumbnail.org" class="media-object">
		</a>
	{/if}

	<div class="media-body">
		<h4 class="media-heading">{ia_url type='link' item='shops' data=$listing text=$listing.title}</h4>
		<div class="ia-item-body">{$listing.description|strip_tags|truncate:200:'...'}</div>
	</div>
</div>