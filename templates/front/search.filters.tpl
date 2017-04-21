{if isset($filters.item) && 'shops' == $filters.item}
    <div class="form-group">
        <label>{lang key='keywords'}</label>
        <input type="text" name="keywords" placeholder="{lang key='keywords'}" class="form-control"{if isset($filters.params.keywords)} value="{$filters.params.keywords|escape}"{/if}>
    </div>
{/if}