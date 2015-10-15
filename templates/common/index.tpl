{if isset($category.title)}
	<div class="slogan">{$category.description}</div>
{/if}

{if isset($categories) && $categories}
	<div class="ia-categories">
		{include file='ia-categories.tpl' categories=$categories num_columns=3 show_amount=true item='ccats' package='coupons'}
	</div>
{/if}

{if !empty($coupons)}
	{if isset($sorting)}
		<div class="btn-toolbar items-sorting c">
			<p class="btn-group">
				<span class="btn btn-small disabled">{lang key='sort_by'}:</span>
				{if $sorting[0] == 'date_added'}<span class="btn btn-small disabled">{lang key='date'}</span>{else}<a class="btn btn-small" href="{$smarty.const.IA_SELF}?sort=date" rel="nofollow">{lang key='date'}</a>{/if}
				{if $sorting[0] == 'thumbs_num'}<span class="btn btn-small disabled">{lang key='likes'}</span>{else}<a class="btn btn-small" href="{$smarty.const.IA_SELF}?sort=likes" rel="nofollow">{lang key='likes'}</a>{/if}
				{if $sorting[0] == 'views_num'}<span class="btn btn-small disabled">{lang key='popularity'}</span>{else}<a class="btn btn-small" href="{$smarty.const.IA_SELF}?sort=popularity" rel="nofollow">{lang key='popularity'}</a>{/if}
			</p>
			<p class="btn-group">
				{if $sorting[1] == 'ASC'}<span class="btn btn-small disabled">up</span>{else}<a class="btn btn-small" href="{$smarty.const.IA_SELF}?order=up" rel="nofollow">up</a>{/if}
				{if $sorting[1] == 'DESC'}<span class="btn btn-small disabled">down</span>{else}<a class="btn btn-small" href="{$smarty.const.IA_SELF}?order=down" rel="nofollow">down</a>{/if}
			</p>
		</div>
	{/if}
	<div class="ia-items">
		{foreach $coupons as $coupon}
			{include file='extra:coupons/list-coupons'}
		{/foreach}

		{navigation aTotal=$pagination.total aTemplate=$pagination.url aItemsPerPage=$pagination.limit aNumPageItems=5 aTruncateParam=1}
	</div>
{elseif isset($category) && $category.parent_id != -1}
	<div class="alert alert-info">{lang key='no_coupons_for_category'}</div>
{elseif !isset($category)}
	<div class="alert alert-info">{lang key='no_my_coupons'}</div>
{/if}