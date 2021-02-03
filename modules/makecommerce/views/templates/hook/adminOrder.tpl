<div id="makecommerce_refund">
    <div>
        <div class="panel">
            <div class="panel-heading"><img width="16" height="16" src="{$base_url|escape:'htmlall':'UTF-8'}modules/makecommerce/logo.png" alt="" /> {l s='MakeCommerce Refund' mod='makecommerce'}</div>
			{if isset($error) && $error}
                <div class="alert alert-warning">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <ul class="list-unstyled">
                        <li>{$error}</li>
                    </ul>
                </div>
            {/if}	
            {if !isset($refunded)}
                <h3>{l s='Total refund' mod='makecommerce'}</h3>
                <form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" class="well">
                    <input type="hidden" name="id_order" value="{$id_order}" />
                    <p class="center">
                        <button type="submit" class="btn btn-default" name="submitMKRefund" onclick="if (!confirm('{l s='Are you sure to refund total amount?' mod='makecommerce'}'))return false;">
                            <i class="icon-undo"></i>
                            {l s='Refund total ' mod='makecommerce'}{convertPrice price=$total_amount}
                        </button>
                    </p>
                </form>
			{/if}
            <h3>{l s='Partial refund' mod='makecommerce'}</h3>
            <form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" class="well">
				<input type="hidden" name="id_order" value="{$id_order}" />
                <p class="center">
					<input type="text" name="mk_partial_refund" id="mk_partial_refund" class="form-control fixed-width-sm pull-left" placeholder="{l s='Amount' mod='makecommerce'}">
                    <button type="submit" class="btn btn-default" name="submitMKRefundPartial" onclick="if (!confirm('{l s='Are you sure you want to do refund?' mod='makecommerce'}'))return false;">
						<i class="icon-undo"></i>
                        {l s='Partial refund' mod='makecommerce'}
                    </button>
                </p>
            </form>
			{if isset($refund_details) AND $refund_details}
                <h3>{l s='Refund history' mod='makecommerce'} ({l s='Total refunded:' mod='makecommerce'} {convertPrice price=$refunded})</h3>
                <div class="table-responsive">
                    <table class="table" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <th>{l s='Refund amount' mod='makecommerce'}</th>
                            <th>{l s='Refund date' mod='makecommerce'}</th>
                        </tr>
                        {foreach from=$refund_details item=refund}
                            <tr>
                                <td>{convertPrice price=$refund.refund_amount}</td>
                                <td>{Tools::displayDate($refund.refund_date, $smarty.const.null,true)|escape:'htmlall':'UTF-8'}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {/if}
        </div>
    </div>
</div>

