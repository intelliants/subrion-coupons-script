{if isset($coupon_blocks.search)} 
	<form action="{$smarty.const.IA_URL}search/coupons/" class="ia-form q-search">
		<div class="row">
			<div class="col-md-5">
				<select class="form-control" name="category">
					<option value="">{lang key='select_category'}</option>
					{foreach $coupon_blocks.search.categories as $cat}
						<option value="{$cat.id}">{$cat.title}</option>
					{/foreach}}
				</select>
			</div>
			<div class="col-md-5">
				<input class="form-control" type="text" name="keywords" placeholder="{lang key='search_keywords'}">
			</div>
			<div class="col-md-2">
				<button class="btn btn-primary btn-block" type="submit">{lang key='search'}</button>
			</div>
		</div>
	</form>
{/if}