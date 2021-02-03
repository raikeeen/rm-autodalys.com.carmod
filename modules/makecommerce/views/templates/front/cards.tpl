{extends file='page.tpl'}
{block name="page_content"}
    <p class="cart_navigation clearfix" id="cart_navigation">
        {if isset($opc) && $opc}
            <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order-opc', true, NULL, "submitReorder&id_order={$order_reference}")|escape:'htmlall':'UTF-8'}">
                <i class="icon-chevron-left"></i>{l s='Back' mod='makecommerce'}
            </a>
        {else}
            <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "submitReorder&id_order={$order_reference}")|escape:'htmlall':'UTF-8'}">
                <i class="icon-chevron-left"></i>{l s='Back' mod='makecommerce'}
            </a>
        {/if}
    </p>
    {if $quick_mode}<form method="POST" class="cart_navigation clearfix" id="makecommerce-form">{else}<div id="makecommerce-form">{/if}
            <button class="button button-medium btn btn-default{if !$quick_mode} hidden{/if}" id="payButton">
                <span>{l s='Pay now' mod='makecommerce'}<i class="icon-chevron-right right"></i></span>
            </button>
            <script onload="$('#payButton').click();"
                    src="{$js_src}"
                    data-key="{$publishable_key}"
                    data-amount="{$amount}"
                    data-currency="{$currency}"
                    data-email="{if $prefill}{$customer_email}{/if}"
                    data-client-name="{if $prefill}{$customer_name}{/if}"
                    data-name="{$shop_name}"
                    data-transaction="{$transaction_id}"
                    data-selector="#payButton"
                    data-description="{$description}"
                    data-locale="{$locale}"
                    Checkout>
            </script>
    {if $quick_mode}</form>{else}</div>{/if}
{/block}
