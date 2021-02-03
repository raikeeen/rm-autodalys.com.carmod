{if $version <= 15}
    {if $payment_list==1}
        {foreach from=$channels item=group key=key}
            {foreach from=$group.channels item=item}
            <p class="payment_module opay-payment-item opay-payment-group-{$key|escape:'html'} opay-payment-item-{$item.channel_name|escape:'html'}">
                <a href="{$item.link|escape:'html'}" title="{$item.title|escape:'html'}">
                    <img src="{$item.logo_urls|escape:'html'}" alt="{$item.title|escape:'html'}" />
                    {$item.title|escape:'html'}
                </a>
            </p>
            {/foreach}
        {/foreach}
    {elseif $payment_list == 2}
        {if $version == 15}
            {foreach from=$channels item=group key=key}
            <div class="opay-payment-group opay-payment-group-{$key|escape:'html'}" style="margin-bottom:10px;">
                <span class="opay-payment-group-title opay-payment-group-title-{$key|escape:'html'}" style="font-weight: bold;display: block;padding: 1% 0 0 1%;">{$group.group_title}</span>
                {foreach from=$group.channels item=item}
                    <p style="padding-left:0; width: 170px;float: left; border: 1px dotted #ccc; margin: 5px 3px 0 2px;" class="payment_module opay-payment-item opay-payment-item-{$item.channel_name|escape:'html'}">
                        <a style="text-align:center;" href="{$item.link|escape:'html'}" title="{$item.title|escape:'html'}">
                            <img style="margin: 0;float:none;" src="{$item.logo_urls|escape:'html'}" alt="{$item.title|escape:'html'}" />
                        </a>
                    </p>
                {/foreach}
                <div style="clear: both;"></div>
            </div>
            {/foreach}
        {else}
            {foreach from=$channels item=group key=key}
                <div class="opay-payment-group opay-payment-group-{$key|escape:'html'}" style="margin-bottom:10px;">
                <span class="opay-payment-group-title opay-payment-group-title-{$key|escape:'html'}" style="font-weight: bold;display: block;padding: 1% 0 0 1%;">{$group.group_title}</span>
                {foreach from=$group.channels item=item}
                    <p style="width: 177px;float: left;" class="payment_module opay-payment-item opay-payment-item-{$item.channel_name|escape:'html'}">
                        <a style="text-align:center;" href="{$item.link|escape:'html'}" title="{$item.title|escape:'html'}">
                            <img style="margin: 0;" src="{$item.logo_urls|escape:'html'}" alt="{$item.title|escape:'html'}" />
                        </a>
                    </p>
                {/foreach}
                <div style="clear: both;"></div>
                </div>
            {/foreach}
        {/if}
    {else}
    <p class="payment_module">
        {if $payment_gateway_name}
            <a href="{$mlink|escape:'html'}" title="{l s='Pay by OPAY' mod='opay'}">
                <img src="{$logo_opay|escape:'html'}" alt="{l s='Pay by OPAY' mod='opay'}" />
                {l s='Pay by OPAY' mod='opay'}
            </a>
        {else}
            <a href="{$mlink|escape:'html'}" title="{$payment_gateway_name|escape:'html'}">
                <img src="{$logo_opay|escape:'html'}" alt="{$payment_gateway_name|escape:'html'}" />
                {$payment_gateway_name|escape:'html'}
            </a>
        {/if}
    </p>
    {/if}
{else}
    {if $payment_list == 1}
        {foreach from=$channels item=group key=key}
            {foreach from=$group.channels item=item}
            <div class="row opay-payment-item opay-payment-group-{$key|escape:'html'} opay-payment-item-{$item.channel_name|escape:'html'}">
                <div class="col-xs-12">
                    <p class="payment_module">
                        <a class="bankwire" style="background-image: url('{$item.logo_urls|escape:'html'}'); padding-left:200px; background-position: 20px; background-repeat: no-repeat;" href="{$item.link|escape:'html'}">
                            {$item.title|escape:'html'}
                        </a>
                    </p>
                </div>
            </div>
            {/foreach}
        {/foreach}
    {elseif $payment_list == 2}
        {foreach from=$channels item=group key=key}
        <div class="row opay-payment-group opay-payment-group-{$key|escape:'html'}" style="margin-bottom:10px;">
            <div class="col-xs-12">
                <div style="font-size: 17px;font-weight: bold;color:#333;">
                    <span class="opay-payment-group-title opay-payment-group-title-{$key|escape:'html'}" style="font-weight: bold;display: block; margin-bottom:5px;">{$group.group_title}</span>
                    {foreach from=$group.channels item=item}
                        <p class="payment_module opay-payment-item opay-payment-item-{$item.channel_name|escape:'html'}" style="display: block; width: 23%; min-width:150px; float: left; position:relative;">
                            <a class="" style="display: block; content: ''; background-image: url('{$item.logo_urls|escape:'html'}'); background-position: center center; background-repeat: no-repeat;" href="{$item.link|escape:'html'}">
                            </a>
                        </p>
                        <p class="payment_module opay-payment-item opay-payment-item-{$item.channel_name|escape:'html'}" style="display: block; width: 1%; min-width:3px; float: left; position:relative;">
                        </p>
                    {/foreach}
                    <div style="clear: both;"></div>
                </div>
            </div>
        </div>
        {/foreach}
    {else}
    <div class="row opay-payment-item">
        <div class="col-xs-12">
            <p class="payment_module">
                {if $payment_gateway_name}
                    <a class="bankwire" style="background-image: url('{$logo_opay|escape:'html'}'); padding-left:150px; background-position: 20px; background-repeat: no-repeat;" href="{$link->getModuleLink('opay', 'payment')|escape:'html'}" title="{$payment_gateway_name|escape:'html'}">
                        {$payment_gateway_name|escape:'html'}
                    </a>
                {else}
                    <a class="bankwire" style="background-image: url('{$logo_opay|escape:'html'}'); padding-left:150px; background-position: 20px; background-repeat: no-repeat;" href="{$link->getModuleLink('opay', 'payment')|escape:'html'}" title="{l s='Pay by OPAY' mod='opay'}">
                        {l s='Pay by OPAY' mod='opay'}
                    </a>
                {/if}
            </p>
        </div>
    </div>
    {/if}
{/if}
{literal}
<script type="application/javascript">
    
    var opay_payment_methods_clicked = false;
    
    $( document ).ready(function() {
        // eliminating double payment submit problem
        $('.payment_module a').click(function(){
            if (!opay_payment_methods_clicked) {
                opay_payment_methods_clicked = true;
                return true;
            } else {
                return false;
            }
        });
    });

</script>
{/literal}