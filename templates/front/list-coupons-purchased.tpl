{if $codes}
    <table class="table table-bordered">
        {foreach $codes as $code}
            <tr>
                <td>
                    {if $code.gallery}
                        <div class="text-center">
                            {ia_image file=$code.gallery.0 class='img-responsive' title=$code.title type='thumbnail'}
                        </div>
                    {/if}
                </td>
                <td>{$code.title}</td>
                <td>{lang key='coupon_expire'} {$code.expire_date|date_format}</td>
                <td>{lang key='code'}: {$code.code}</td>
            </tr>
        {/foreach}
    </table>
{/if}