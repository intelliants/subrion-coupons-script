{if $codes}

    <ul class="nav nav-pills nav-justified m-b-20">
        <li role="presentation" data-toggle="tab" class="active"><a href="#active">Active</a></li>
        <li role="presentation" data-toggle="tab"><a href="#used">Used</a></li>
        <li role="presentation" data-toggle="tab"><a href="#inactive">Inactive</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="active">
            {foreach $codes as $code}
                <div class="ia-item ia-item--border">
                    <div class="row">
                        <div class="col-sm-2">
                            {if $code.gallery}
                                {ia_image file=$code.gallery.0 class='img-responsive' title=$code.title type='thumbnail'}
                            {elseif $code.shop_image}
                                <a href="{ia_image file=$listing.shop_image url=true type='large'}" rel="ia_lightbox">
                                    {ia_image file=$listing.shop_image type='thumbnail' title=$listing.shop_title class='img-responsive'}
                                </a>
                            {else}
                                <a href="{ia_url type='url' item='shops' data=$listing}" class="ia-item-thumbnail">
                                    <img src="//api.webthumbnail.org?width=150&height=150&format=png&screen=1024&url={$listing.shop_website}" alt="Generated by WebThumbnail.org" class="img-responsive">
                                </a>
                            {/if}
                        </div>
                        <div class="col-sm-6">
                            <a href="{$code.coupon_url}">{$code.title}</a>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid consequuntur excepturi obcaecati perspiciatis possimus quas quo rem veniam. A asperiores autem commodi consequatur earum ex laboriosam nihil non sint sit!
                            </p>
                        </div>
                        <div class="col-sm-2">
                            <p class="m-t-20 m-b-0"><b>{lang key='coupon_expire'}:</b></p>
                            {$code.expire_date|date_format}
                        </div>
                        <div class="col-sm-2">
                            <p class="m-t-20 m-b-0"><b>{lang key='code'}:</b></p>
                            {$code.code}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="tab-pane" id="used">
            {foreach $codes as $code}
                <div class="ia-item ia-item--border">
                    <div class="row">
                        <div class="col-sm-2">
                            {if $code.gallery}
                                {ia_image file=$code.gallery.0 class='img-responsive' title=$code.title type='thumbnail'}
                            {elseif $code.shop_image}
                                <a href="{ia_image file=$listing.shop_image url=true type='large'}" rel="ia_lightbox">
                                    {ia_image file=$listing.shop_image type='thumbnail' title=$listing.shop_title class='img-responsive'}
                                </a>
                            {else}
                                <a href="{ia_url type='url' item='shops' data=$listing}" class="ia-item-thumbnail">
                                    <img src="//api.webthumbnail.org?width=150&height=150&format=png&screen=1024&url={$listing.shop_website}" alt="Generated by WebThumbnail.org" class="img-responsive">
                                </a>
                            {/if}
                        </div>
                        <div class="col-sm-6">
                            <a href="{$code.coupon_url}">{$code.title}</a>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid consequuntur excepturi obcaecati perspiciatis possimus quas quo rem veniam. A asperiores autem commodi consequatur earum ex laboriosam nihil non sint sit!
                            </p>
                        </div>
                        <div class="col-sm-2">
                            <p class="m-t-20 m-b-0"><b>{lang key='coupon_expire'}:</b></p>
                            {$code.expire_date|date_format}
                        </div>
                        <div class="col-sm-2">
                            <p class="m-t-20 m-b-0"><b>{lang key='code'}:</b></p>
                            {$code.code}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="tab-pane" id="inactive">
            {foreach $codes as $code}
                <div class="ia-item ia-item--border">
                    <div class="row">
                        <div class="col-sm-2">
                            {if $code.gallery}
                                {ia_image file=$code.gallery.0 class='img-responsive' title=$code.title type='thumbnail'}
                            {elseif $code.shop_image}
                                <a href="{ia_image file=$listing.shop_image url=true type='large'}" rel="ia_lightbox">
                                    {ia_image file=$listing.shop_image type='thumbnail' title=$listing.shop_title class='img-responsive'}
                                </a>
                            {else}
                                <a href="{ia_url type='url' item='shops' data=$listing}" class="ia-item-thumbnail">
                                    <img src="//api.webthumbnail.org?width=150&height=150&format=png&screen=1024&url={$listing.shop_website}" alt="Generated by WebThumbnail.org" class="img-responsive">
                                </a>
                            {/if}
                        </div>
                        <div class="col-sm-6">
                            <a href="{$code.coupon_url}">{$code.title}</a>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid consequuntur excepturi obcaecati perspiciatis possimus quas quo rem veniam. A asperiores autem commodi consequatur earum ex laboriosam nihil non sint sit!
                            </p>
                        </div>
                        <div class="col-sm-2">
                            <p class="m-t-20 m-b-0"><b>{lang key='coupon_expire'}:</b></p>
                            {$code.expire_date|date_format}
                        </div>
                        <div class="col-sm-2">
                            <p class="m-t-20 m-b-0"><b>{lang key='code'}:</b></p>
                            {$code.code}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{else}
    <div class="alert alert-info">{lang key='no_my_coupons'}</div>
{/if}
