{extends file='page.tpl'}
{block name="page_content"}
    <p class="alert alert-{$msg_class}">{$banklink_msg}</p>
    {if isset($order_reference) && $order_reference}
        <a class="btn btn-default button button-medium exclusive" href="{$order_page_link}">
            <span>{l s='Try paying again' mod='makecommerce'}</span>
        </a>
    {/if}
{/block}