{if $shops}
    <div class="shops-list">
        {foreach $shops as $listing}
            {include 'extra:coupons/list-shops'}
        {/foreach}
    </div>
{else}
    <div class="alert alert-info">{lang key='no_shops'}</div>
{/if}