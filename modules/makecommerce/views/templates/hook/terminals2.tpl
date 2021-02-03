<div class="hidden" id="makecommerce_carrier_{$carrier.id}" data-id_carrier={$carrier.id} data-id_address={$id_address}>
    <input type="hidden" name="identification" value="{$carrier.name}" class="makecommerce-carrier" />
    <input type="hidden" name="{$carrier.name}_{$id_address}_ajax" value="{$ajax_url}" class="{$carrier.name}_{$id_address}_ajax"/>
    {assign var="citytext" value=""}
    <script>
        var {$carrier.name}_terminals = [];
        {foreach key=city from=$terminals item=group}{foreach from=$group item=terminal}{$carrier.name}_terminals.push( { 'city':'{$terminal.city}','id':'{$terminal.id}','name':'{$terminal.name}' } );
        {/foreach}{/foreach}
    </script>
    {foreach key=city from=$terminals item=group}{foreach from=$group item=terminal}{if $carrier.terminal_id > 0}{if $terminal.id == $carrier.terminal_id}{assign var="citytext" value="{$terminal.city}"}{/if}{/if}{/foreach}{/foreach}
    {if $citytext == ""}{assign var="citytext" value={$customer.addresses[{$id_address}].city}}{/if}
    <input type="hidden" name="{$carrier.name}_city" value="{$citytext}">
    <select name='terminalCities' class='makecommerce_carrier' id='{$carrier.name}_location' onchange="updateCarrierCity({$id_address}, '{$carrier.name}', '{$carrier.terminal_id}','{l s='Please select terminal ...' mod='makecommerce'}');">
        <option value='0' {if $carrier.terminal_id == 0 && $carrier.group_id == 0}selected="selected"{/if}>{l s='Please select city ...' mod='makecommerce'}</option>
        {foreach key=city from=$terminals item=group}
                <option value='{$city}'{if $city == $citytext} selected="selected" {/if}>{$city}</option>
        {/foreach}
    </select>
    <!--  -->
    <select name='terminals' class='makecommerce_carrier' id='{$carrier.name}_{$id_address}' onchange="updateCarrierTerminal({$id_address}, '{$carrier.name}', '{$carrier.name}');"{if $group ==""} disabled"{/if}>
        <option value='0' {if $carrier.terminal_id == 0 and $.city_id ==""}selected="selected"{/if}>{l s='Please select terminal ...' mod='makecommerce'}</option>
        {foreach key=city from=$terminals item=group}
            <!-- <optgroup label="{$city}"> -->
                {if $city == $citytext}
                {foreach from=$group item=terminal}
                    <option value='{$terminal.id}'
                            {if $terminal.id == $carrier.terminal_id}selected="selected"{/if}
                            data-address="{$terminal.name}">
                        {$terminal.name}
                    </option>
                {/foreach}
                {/if}
            <!-- </optgroup> -->
        {/foreach}
    </select>
</div>
{literal}
<script>
    chooseTerminal = {/literal}"{l s='Please choose parcel terminal' mod='makecommerce'}";{literal}
</script>
{/literal}
