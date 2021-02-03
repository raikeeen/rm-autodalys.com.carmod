{extends file='page.tpl'}
{block name="page_content"}
    <div id="veebipoed-overlay" class="ajax_loader"></div>
    <div class="box">
        <h3 class="page-subheading"><img class="logo_size_m" src="{$logo}"> </h3>
        <p style="margin-top:20px;">
            {l s='The total amount of your order is' mod='makecommerce'}
            <span id="amount" class="price">{$total}</span>
            {if $priceDisplay == 0}
                {l s='(tax incl.)' mod='makecommerce'}
            {/if}
        </p>
        <p>{l s='The payment is being processed through ' mod='makecommerce'}<a href="https://maksekeskus.ee/ostjale/" target="_blank">Maksekeskus AS</a></p>
        <p>{l s='If you want to proceed, please click "Confirm", otherwise click "Back"' mod='makecommerce'}</p>
    </div>
    <p class="cart_navigation clearfix" id="cart_navigation">
        <a class="button-exclusive btn btn-default" href="{$back_href}">
            <i class="icon-chevron-left"></i>{l s='Back' mod='makecommerce'}
        </a>
        <a class="button btn btn-default button-medium" href="{$href}">
            <span>{l s='Confirm' mod='makecommerce'}<i class="icon-chevron-right right"></i></span>
        </a>
    </p>
{/block}
