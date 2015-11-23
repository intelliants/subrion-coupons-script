{if $letters}
	{include file='ia-alpha-sorting.tpl' letters=$letters url="{$core.packages.coupons.url}shops/"}
{/if}

{if !empty($shops)}
	{$letter=''}
	<div class="shops-list">
		{foreach $shops as $listing}
			{if $letter|upper != $listing.title[0]|upper}
				{if '' != $letter}</ul>{/if}
				<h2>{$listing.title[0]|upper}</h2>
				<hr>
			{/if}
			{include 'extra:coupons/list-shops'}
			{assign var=letter value=$listing.title[0]}
		{/foreach}
	</div>
{else}
	<div class="alert alert-info">{lang key='no_shops'}</div>
{/if}