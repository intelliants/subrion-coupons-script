<h4>{lang key='sales_statistics'}</h4>

{if $codes}
    <form method="get">
        <div class="input-group">
            <input type="text" name="code" class="form-control">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-primary">Search</button>
            </span>
        </div>
    </form>
    <table class="table m-t">
        <tbody>
        {foreach $codes as $codeEntry}
            <tr>
                <td>
                    <p>{lang key='simple_coupon'} <strong>{$codeEntry.code}</strong></p>
                    <p>{$codeEntry.owner|escape}</p>
                    <p><small>{lang key='transaction'} #{$codeEntry.reference_id}</small></p>
                </td>
                <td>{$codeEntry.date_paid|date_format}</td>
                <td>
                    <select class="form-control js-code-status" data-id="{$codeEntry.id}">
                        {foreach $codeStatuses as $status}
                            <option value="{$status}"{if $codeEntry.status == $status} selected{/if}>{lang key=$status}</option>
                        {/foreach}
                    </select>
                </td>
                <td>{$codeEntry.amount_formatted}</td>
            </tr>
        {/foreach}
        <tr>
            <td colspan="4" class="text-right"><strong>{lang key='total'}: {$total}</td>
        </tr>
        </tbody>
    </table>
{else}
    <div class="alert alert-info">{lang key='no_codes_bought'}</div>
{/if}