<div id="makecommerce_label">
    <div>
        <div class="panel">
            <div class="panel-heading"><img width="16" height="16" src="{$base_url|escape:'htmlall':'UTF-8'}modules/makecommerce/logo.png" alt="" /> {l s='Shipment actions' mod='makecommerce'}</div>
            <form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}">
                <button type="submit" class="btn btn-default" name="submitMKLabel">
                    <i class="icon-print"></i>
                    {l s='Print parcel Label' mod='makecommerce'}
                </button>
                {if empty($shipment_id)}
                    <button type="submit" class="btn btn-default" name="submitMKRegister">
                        <i class="icon-arrow-up"></i>
                        {l s='Register order' mod='makecommerce'}
                    </button>
                {/if}
            </form>
        </div>
    </div>
</div>
{if isset($label_url) && $label_url}
    <script>
        window.open('{$label_url}', '_blank');
    </script>
{/if}