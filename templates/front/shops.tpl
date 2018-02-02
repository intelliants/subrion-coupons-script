{if $shops}
    <div class="shops-list">
        {foreach $shops as $listing}
            {include 'module:coupons/list-shops.tpl'}
        {/foreach}
    </div>

    {navigation aTotal=$pagination.total aTemplate=$pagination.url aItemsPerPage=$pagination.limit aNumPageItems=5 aTruncateParam=1}
{else}
    <div class="alert alert-info">{lang key='no_shops'}</div>
{/if}