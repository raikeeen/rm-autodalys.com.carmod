<input type="hidden" id="venipak_carrier_carrier_id" value="{$venipak_carrier_id}" />
<input type="hidden" id="venipak_carrier_address_id" value="{$id_address_delivery}" />

<div id="venipak-additional-info">
    <div class="venipak-order-comments">

        {if Configuration::get('VENIPAK_SHOW_DELIVERY_TYPES') == '1'}
            <div class="field-row">
                <label>{l s='Delivery time:' mod='venipakcarrier'}</label>
                <select name="venipak_delivery_type" id="venipak_delivery_type" class="venipak_select2 input-medium form-control" style="width:100%">
                    {foreach from=$availableDeliveryTypes item=type}
                        <option value="{$type}">{$allDeliveryTypes.$type}</option>
                    {/foreach}
                </select>
            </div>
        {/if}

        {if Configuration::get('VENIPAK_SHOW_COMMENT_DOOR_CODE') == '1'}
            <div class="field-row">
                <label>{l s='Door code:' mod='venipakcarrier'}</label>
                <input type="text" name="venipak_door_code" id="venipak_door_code" class="form-control" value="" />
            </div>
        {/if}

        {if Configuration::get('VENIPAK_SHOW_COMMENT_OFFICE_NO') == '1'}
            <div class="field-row">
                <label>{l s='Office number:' mod='venipakcarrier'}</label>
                <input type="text" name="venipak_office_no" id="venipak_office_no" class="form-control" value="" />
            </div>
        {/if}

        {if Configuration::get('VENIPAK_SHOW_COMMENT_WAREHOUSE_NO') == '1'}
            <div class="field-row">
                <label>{l s='Warehouse number:' mod='venipakcarrier'}</label>
                <input type="text" name="venipak_warehouse_no" id="venipak_warehouse_no" class="form-control" value="" />
            </div>
        {/if}

        {if Configuration::get('VENIPAK_SHOW_COMMENT_CALL') == '1'}
            <div class="field-row" ><label>{l s='Call before delivery:' mod='venipakcarrier'}</label>
                <select name="venipak_comment_call" id="venipak_comment_call" class="form-control select2 col-12">
                    <option value="1">{l s='Yes' mod='venipakcarrier'}</option>
                    <option value="0">{l s='No' mod='venipakcarrier'}</option>
                </select>
            </div>
        {/if}

    </div>
</div>
