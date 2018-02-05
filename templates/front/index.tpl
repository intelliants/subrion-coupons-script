{if isset($category.title)}
    <div class="slogan">{$category.description}</div>
{/if}

{if !empty($categories)}
    <div class="ia-categories">
        {include 'ia-categories.tpl' categories=$categories num_columns=3 show_amount=true item='ccats' package='coupons'}
    </div>
{/if}

{if !empty($coupons)}
    {if isset($sorting)}
        <div class="ia-sorting">
            {lang key='sort_by'}:

            <div class="btn-group">
                <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
                    {if 'date_added' == $sorting[0]}
                        {lang key='date'}
                    {elseif 'thumbs_num' == $sorting[0]}
                        {lang key='likes'}
                    {elseif 'views_num' == $sorting[0]}
                        {lang key='popularity'}
                    {/if}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{$smarty.const.IA_SELF}?sort=date" rel="nofollow"><span class="fa fa-clock-o"></span> {lang key='date'}</a></li>
                    <li><a href="{$smarty.const.IA_SELF}?sort=likes" rel="nofollow"><span class="fa fa-dollar"></span> {lang key='likes'}</a></li>
                    <li><a href="{$smarty.const.IA_SELF}?sort=popularity" rel="nofollow"><span class="fa fa-eye"></span> {lang key='popularity'}</a></li>
                </ul>
            </div>

            <div class="btn-group">
                <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
                    {if 'ASC' == $sorting[1]}
                        {lang key='asc'}
                    {else}
                        {lang key='desc'}
                    {/if}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{$smarty.const.IA_SELF}?order=up" rel="nofollow"><span class="fa fa-long-arrow-down"></span> {lang key='asc'}</a></li>
                    <li><a href="{$smarty.const.IA_SELF}?order=down" rel="nofollow"><span class="fa fa-long-arrow-up"></span> {lang key='desc'}</a></li>
                </ul>
            </div>
        </div>
    {/if}
    <div class="ia-items">
        {foreach $coupons as $listing}
            {include 'module:coupons/list-coupons.tpl'}
        {/foreach}

        {navigation aTotal=$pagination.total aTemplate=$pagination.url aItemsPerPage=$pagination.limit aNumPageItems=5 aTruncateParam=1}
    </div>
{elseif isset($category) && $category.parent_id != 0}
    <div class="alert alert-info">{lang key='no_coupons_for_category'}</div>
{elseif !isset($category)}
    <div class="alert alert-info">{lang key='no_my_coupons'}</div>
{/if}