{if !empty($listings)}
    <div class="ia-items ia-items--cards">
        {foreach $listings as $listing}
            {include 'module:coupons/list-shops.tpl'}
        {/foreach}
    </div>
{else}
    <div class="alert alert-info">{lang key='no_shops'}</div>
{/if}