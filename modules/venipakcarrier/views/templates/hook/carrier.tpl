<input type="hidden" id="venipak_carrier_carrier_id" value="{$id_address_delivery}" />
<input type="hidden" id="venipak_carrier_address_id" value="{$id_address_delivery}" />

<div id="venipak-additional-info">
    <div class="venipak-carrier-order-comments" style="margin-bottom: 20px;">
        {if Configuration::get('VENIPAK_SHOW_DELIVERY_TYPES') == '1'}
            <div class="field-row">
                <label>{l s='Delivery time:' mod='venipakcarrier'}</label>
                <select name="venipak_delivery_type" id="venipak_delivery_type" class="form-control">
                    {foreach from=$availableDeliveryTypes item=type}
                        <option value="{$type}">{$allDeliveryTypes.$type}</option>
                    {/foreach}
                </select>
            </div>
        {/if}
        {if Configuration::get('VENIPAK_SHOW_COMMENT_DOOR_CODE') == '1'}
            <div class="field-row"> <label>{l s='Door code:' mod='venipakcarrier'}</label> <input type="text" name="venipak_door_code" id="venipak_door_code" class="form-control" value="" /></div>
        {/if}
        {if Configuration::get('VENIPAK_SHOW_COMMENT_OFFICE_NO') == '1'}
            <div class="field-row"><label>{l s='Office number:' mod='venipakcarrier'}</label> <input type="text" name="venipak_office_no" id="venipak_office_no" class="form-control" value="" /></div>
        {/if}
        {if Configuration::get('VENIPAK_SHOW_COMMENT_WAREHOUSE_NO') == '1'}
            <div class="field-row"><label>{l s='Warehouse number:' mod='venipakcarrier'}</label> <input type="text" name="venipak_warehouse_no" id="venipak_warehouse_no" class="form-control" value="" /></div>
        {/if}
        {if Configuration::get('VENIPAK_SHOW_COMMENT_CALL') == '1'}
            <div class="field-row"><label>{l s='Call before delivery:' mod='venipakcarrier'}</label>
                <select name="venipak_comment_call" id="venipak_comment_call" class="form-control col-12">
                    <option value="1">{l s='Yes' mod='venipakcarrier'}</option>
                    <option value="0">{l s='No' mod='venipakcarrier'}</option>
                </select>
            </div>
        {/if}
    </div>
</div>
