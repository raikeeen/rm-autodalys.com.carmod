{if isset($error) && !empty($error)}
    <div class="lp_carrier error col-xs-12" data-carrier-id="{$id_carrier}">
        {$error}
    </div>
{elseif isset($terminals) && !empty($terminals)}
    <div class="lp_carrier col-xs-12" data-carrier-id="{$id_carrier}">
        <select id="lp_express_terminal">
            <option>{l s='Select terminal' mod='lpexpress'}</option>

            {foreach $terminals as $city => $terminals_by_city}
                <optgroup label="{$city}">
                    {foreach $terminals_by_city as $terminal}
                        <option value="{$terminal['id_lpexpress_terminal']}"{if isset($selected_terminal) && $selected_terminal == $terminal['id_lpexpress_terminal']} selected{/if}>{$terminal['name']} {$terminal['address']}, {$terminal['city']}</option>
                    {/foreach}
                </optgroup>
            {/foreach}
        </select>
    </div>
{/if}
