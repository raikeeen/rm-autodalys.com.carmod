<div class="hidden" id="makecommerce_carrier_{$carrier.id}" data-id_carrier={$carrier.id} data-id_address={$id_address}>
    <input type="hidden" name="identification" value="{$carrier.name}" class="makecommerce-carrier" />
    <input type="hidden" name="{$carrier.name}_{$id_address}_ajax" value="{$ajax_url}" class="{$carrier.name}_{$id_address}_ajax"/>
    <select name='terminals' class='makecommerce_carrier' id='{$carrier.name}_{$id_address}' onchange="updateCarrierTerminal({$id_address}, '{$carrier.name}', '{$carrier.name}');">
        <option value='0' {if $carrier.terminal_id == 0}selected="selected"{/if}>{l s='Please select terminal ...' mod='makecommerce'}</option>
        {foreach key=city from=$terminals item=group}
            <optgroup label="{$city}">
                {foreach from=$group item=terminal}
                    <option value='{$terminal.id}'
                            {if $terminal.id == $carrier.terminal_id}selected="selected"{/if}
                            data-address="{$terminal.name}">
                        {$terminal.name}
                    </option>
                {/foreach}
            </optgroup>
        {/foreach}
    </select>
</div>
{literal}
<script>
    chooseTerminal = {/literal}"{l s='Please choose parcel terminal' mod='makecommerce'}";{literal}
</script>
{/literal}
