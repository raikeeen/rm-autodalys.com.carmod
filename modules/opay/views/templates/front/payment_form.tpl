<form action="{$action}" id="opay-payment-form" onSubmit="{$on_submit}">
    {if $payment_list == 1}
    <ul style="font-size: 0">
    {/if}
    {foreach from=$channels item=group key=key}
        {if $payment_list == 2}
        <div class="opay-payment-group opay-payment-group-{$key|escape:'html'}" style="margin-bottom:10px;">
            <span class="h4 opay-payment-group-title opay-payment-group-title-{$key|escape:'html'}" style="display:block;">{$group.group_title}</span>
            <ul style="font-size: 0">
        {/if}
        {foreach from=$group.channels item=item}
            <li class="payment_module opay-payment-item opay-payment-item-{$item.channel_name|escape:'html'} opay-payment-item-{$logo_size}" style="border:1px solid #d4d4d4; margin:2px; display:inline-block; width:{if $logo_size == 33}110px{elseif $logo_size == 49}180px{/if}; ">
                <label style="padding:{if $logo_size == 33}10px{elseif $logo_size == 49}17px{/if} 0; cursor:pointer; display:block; margin: 0; text-align:center;" for="opay_{$item.channel_name}">
                    <div class="opay-payment-item-icon" style="margin: 4px 0; background-image:url('{if $logo_size == 33}{$item.logo_urls.color_33px|escape:'html'}'); height: 33px{elseif $logo_size == 49}{$item.logo_urls.color_49px|escape:'html'}'); height: 49px{/if}; background-position:center center; background-repeat:no-repeat;"></div>
                    <input type="radio" name="opay_channel" style="margin-top: 8px" id="opay_{$item.channel_name}" value="{$item.channel_name}"/>
                </label>
            </li>
        {/foreach}
        {if $payment_list == 2}
            </ul>
        </div>
        {/if}
    {/foreach}
    {if $payment_list == 1}
    </ul>
    {/if}
</form>
{literal}
<script>
var opayCheckoutData = {
    "pleaseSelectChannelMsg": "{/literal}{l s='Please select payment method.' mod='opay'}{literal}"
};
</script>
{/literal}
