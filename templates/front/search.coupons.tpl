{if isset($listings) && $listings}
	<div class="ia-items ia-items--cards">
		{foreach $listings as $listing}
			{include 'extra:coupons/list-coupons'}
		{/foreach}
	</div>
{else}
	<div class="alert alert-info">
		{lang key='no_coupons'}
	</div>
{/if}