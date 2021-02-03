
<div class="panel clearfix">
    <div class="col-lg-6 col-md-6 col-xs-12">

        <div class="panel-heading">
            <i class="icon-tags"></i>
            {l s='Venipak' mod='venipakcarrier'}
        </div>

        {if $venipakBlockAllowed}
            <div class="venipak_order_config">
                <form action="{$venimoduleurl}" method="post" id="venipakOrderSubmitForm">

                    <div class="row">
                        <div class="col-md-12 col-xs-12">

                            <div class="row">
                                <div class="col-md-6 col-xs-12">
                                    <div class="field-row">
                                        <span>{l s="Packets (count)" mod='venipakcarrier'}: </span>
                                        <span>
                        <input type="text" name="packs" value="{if isset($venipakOrderInfo.packs)} {$venipakOrderInfo.packs}{else}1{/if}" {if $venipakalldisabled==1}disabled="disabled"{/if} />
                    </span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <div class="field-row">
                                        <span>{l s="Weight (Kg)" mod='venipakcarrier'}: </span>
                                        <span>
                        <input type="text" name="weight" value="{if isset($venipakOrderInfo.weight)}{$venipakOrderInfo.weight}{else}{$total_weight}{/if}" {if $venipakalldisabled==1}disabled="disabled"{/if} />
                    </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-xs-12">
                                    <div class="field-row row">
                                        <div class="col-sm-6">
                                            <span>{l s="C.O.D." mod='venipakcarrier'}: </span>
                                            <span>
                    <select name="is_cod" disabled="disabled">
                        <option value="0">{l s='No' mod='venipakcarrier'}</option>
                        <option value="1" {if $is_cod} selected="selected" {/if}>{l s='Yes' mod='venipakcarrier'}</option>
                    </select>
                </span>
                                        </div>
                                        <div class="col-sm-6">{l s="C.O.D. amount" mod='venipakcarrier'}: <input type="text" name="cod_amount" value="{if $isPickup}0{else}{$total_paid_tax_incl}{/if}" disabled="disabled" /></div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <div class="field-row">
                                        <span>{l s="Pickup address" mod='venipakcarrier'}: </span>
                                        <span>
                <select name="warehouse_id" {if $venipakalldisabled==1}disabled="disabled"{/if}>
                    {foreach from=$venipak_warehouses item=warehouse}
                        <option value="{$warehouse.id}" {if isset($venipakOrderInfo.warehouse_id) && $venipakOrderInfo.warehouse_id==$warehouse.id} selected="selected" {/if}>{$warehouse.address_title}</option>
                    {/foreach}
                </select>
            </span>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6 col-xs-12">
                                    <div class="field-row">{l s="Delivery option" mod='venipakcarrier'}:
                                        <select name="delivery_time" {if $venipakalldisabled==1}disabled="disabled"{/if}>
                                            {foreach from=$delivery_times item=deliveryName key=deliveryt}
                                                <option value="{$deliveryt}" {if (isset($venipakOrderInfo.delivery_time) && $venipakOrderInfo.delivery_time==$deliveryt) || (isset($selectedComments.delivery_type) && $selectedComments.delivery_type==$deliveryt)} selected="selected" {/if}>{$deliveryName}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    {if $VENIPAK_SHOW_RETURN_DOCS == 1}
                                        <div class="field-row">{l s="Return documents" mod='venipakcarrier'}:
                                            <select name="return_docs" {if $venipakalldisabled==1}disabled="disabled"{/if}>
                                                <option value="0">{l s='No' mod='venipakcarrier'}</option>
                                                <option value="1" {if isset($venipakOrderInfo.return_docs) && $venipakOrderInfo.return_docs==1} selected="selected" {/if}>{l s='Yes' mod='venipakcarrier'}</option>
                                            </select>
                                        </div>
                                    {/if}
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    {if $VENIPAK_SHOW_CHECK_ID == 1}
                                        <div class="field-row">{l s='Check recipient ID' mod='venipakcarrier'}:
                                            <select name="check_docs" {if $venipakalldisabled==1}disabled="disabled"{/if}>
                                                <option value="0">{l s='No' mod='venipakcarrier'}</option>
                                                <option value="1" {if isset($venipakOrderInfo.check_docs) && $venipakOrderInfo.check_docs==1} selected="selected" {/if}>{l s='Yes' mod='venipakcarrier'}</option>
                                            </select>
                                        </div>
                                    {/if}
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <div class="field-row" {if $VENIPAK_SHOW_PICKUP != 1}style="display: none;"{/if}>{l s="Sender" mod='venipakcarrier'}:
                                        <select name="show_sender" {if $venipakalldisabled==1}disabled="disabled"{/if}>
                                            {foreach from=$venipak_senders item=sender}
                                                <option value="{$sender.id}" {if isset($venipakOrderInfo.show_sender) && $venipakOrderInfo.show_sender==$sender.id} selected="selected" {/if}>{$sender.address_title}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <div class="field-row">{l s="Package preparation time" mod='venipakcarrier'}:
                                        <select name="manifest_date" {if $venipakalldisabled==1}disabled="disabled"{/if}>
                                            {foreach from=$venipak_manifest_dates item=mandate}
                                                <option value="{$mandate}" {if isset($venipakOrderInfo.manifest_date) && $venipakOrderInfo.manifest_date==$mandate} selected="selected" {/if}>{$mandate}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {if $isPickup == false}

                                {if Configuration::get('VENIPAK_SHOW_COMMENT_DOOR_CODE') == '1' || Configuration::get('VENIPAK_SHOW_COMMENT_OFFICE_NO') == '1' || Configuration::get('VENIPAK_SHOW_COMMENT_WAREHOUSE_NO') == '1' || Configuration::get('VENIPAK_SHOW_COMMENT_CALL') == '1'}
                                    <hr />
                                {/if}

                                <div class="row">
                                    <div class="col-md-6 col-xs-12">
                                        {if Configuration::get('VENIPAK_SHOW_COMMENT_DOOR_CODE') == '1'}
                                            <div class="field-row">
                                                <span>{l s="Comment - door code" mod='venipakcarrier'}: </span><span>
                                    <input type="text" name="comment_door_code" value="{if isset($selectedComments.comment_door_code)}{$selectedComments.comment_door_code}{/if}"  {if $venipakalldisabled==1}disabled="disabled"{/if}/>
                                </span>
                                            </div>
                                        {/if}
                                    </div>
                                    <div class="col-md-6 col-xs-12">
                                        {if Configuration::get('VENIPAK_SHOW_COMMENT_OFFICE_NO') == '1'}
                                            <div class="field-row">
                                                <span>{l s="Comment - office no." mod='venipakcarrier'}: </span><span>
                                    <input type="text" name="comment_office_no" value="{if isset($selectedComments.comment_office_no)}{$selectedComments.comment_office_no}{/if}"  {if $venipakalldisabled==1}disabled="disabled"{/if}/>
                                </span>
                                            </div>
                                        {/if}
                                    </div>
                                    <div class="col-md-6 col-xs-12">
                                        {if Configuration::get('VENIPAK_SHOW_COMMENT_WAREHOUSE_NO') == '1'}
                                            <div class="field-row">
                                                <span>{l s="Comment - warehouse number" mod='venipakcarrier'}: </span><span>
                                    <input type="text" name="comment_warehous_no" value="{if isset($selectedComments.comment_warehous_no)}{$selectedComments.comment_warehous_no}{/if}"  {if $venipakalldisabled==1}disabled="disabled"{/if}/>
                                </span>
                                            </div>
                                        {/if}
                                    </div>
                                    <div class="col-md-6 col-xs-12">
                                        {if Configuration::get('VENIPAK_SHOW_COMMENT_CALL') == '1'}
                                            <div class="field-row">
                                                <span>{l s="Comment - call before delivery" mod='venipakcarrier'}: </span><span>
                                    <select name="comment_call" {if $venipakalldisabled==1}disabled="disabled"{/if}>
                                        <option value="0" {if isset($selectedComments.comment_call) && $selectedComments.comment_call==0} selected="selected" {/if}>{l s="No"}</option>
                                        <option value="1" {if isset($selectedComments.comment_call) && $selectedComments.comment_call==1} selected="selected" {/if}>{l s="Yes"}</option>
                                    </select>
                                </span>
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            {else}
                                <hr />

                                <div class="col-md-6 col-xs-12">
                                    <div class="field-row">
                                        <span>{l s="Selected pickup point" mod='venipakcarrier'}: </span><span>
                                <select id="id_pickup_point" name="id_pickup_point" {if $venipakalldisabled==1}disabled="disabled"{/if}>
                                <option value="0">{l s='Select pickup location' mod='venipakcarrier'}</option>
                                    {foreach from=$pickup_points item=country}
                                        {if count($pickup_points) > 1}
                                            <optgroup label="{$country.name}">
                                        {/if}
                                        {foreach from=$country.city item=city}
                                            <optgroup label="{$city.name}">
                                            {foreach from=$city.point item=point}
                                                <option value="{$point.id}" {if isset($selectedComments.id_pickup_point) && $selectedComments.id_pickup_point == $point.id} selected="selected"{/if}>{$point.name}</option>
                                            {/foreach}
                                        </optgroup>
                                        {/foreach}
                                        {if count($pickup_points) > 1}
                                            </optgroup>
                                        {/if}
                                    {/foreach}
                            </select>
                            </span>
                                    </div>
                                </div>

                            {/if}

                        </div>
                    </div>

                    <div class="venipak-padding">
                        <div class="venipakAction">
                            <button type="button" name="venipak_save" id="venipakOrderSubmitBtn" class="btn btn-primary" {if $venipakalldisabled == true}disabled="disabled"{/if}>{l s="1. Save" mod='venipakcarrier'}</button>
                        </div>
                    </div>
                </form>

                <div class="venipak-padding">
                    <div class="venipakAction">
                        <form method="POST" action="{$veniprintlabelsurl}" id="venipakOrderPrintLabelsForm" target="_blank">
                            <input type="hidden" name="order_ids[]" value="{$order_id}" />
                            <button type="submit" name="venipak_printlabel" id="venipakOrderPrintLabels" {if $venipakalldisabled == false}disabled="disabled"{/if} class="btn btn-success">{l s="2. Print labels" mod='venipakcarrier'}</button>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 responseVenipak" style="margin-top: 15px;"></div>
                </div>
            </div>
        {else}
            <div class="alert alert-danger">{$errorMessage} <a href="{$moduleUrl}">{l s='Click here' mod='venipakcarrier'}</a> {l s='to change settings.' mod='venipakcarrier'}</div>
        {/if}
    </div>
</div>

{literal}
    <style type="text/css">
        .venipak-padding {
            padding-top:10px;
            border-top: 1px solid #ececec;
            padding-right: 15px;
            float: left;
        }

        .venipakAction {
            display:inline-block;
        }

        .venipak_order_config .field-row {
            margin-bottom:10px;
        }

        .venipak_order_config .field-row input, .venipak_order_config .field-row select {
            margin-top:5px;
        }
    </style>
{/literal}


<script type="text/javascript">
$(document).ready(function(){
    $("#venipakOrderPrintLabelsForm").submit(function(e){
        $("#venipakOrderSubmitForm").find('.response').html('');
        if($("#venipakOrderSubmitBtn").is(':disabled')){
            return true;
        }else{
            var saveResult = saveVenipakOrder();
            if(saveResult){
                return true;
            }else{
                return false;
            }
        }
    });

    function saveVenipakOrder(){
        $("#venipakOrderSubmitBtn").attr('disabled','disabled');
        var formData = $("#venipakOrderSubmitForm").serialize()+'&'+$.param({
					ajax: "1",
                    token: "{getAdminToken tab='AdminOrders'}",
					order_id: "{$order_id}",

					});
        $.ajax({
				type:"POST",
                url: "{$venimoduleurl}",
				async: false,
				dataType: "json",
				data : formData,
				success : function(res)
				{
					//disable the inputs
                    if(typeof res.error !== "undefined"){
                        $('.responseVenipak').html('<div class="alert alert-danger">'+res.error+'</div>');
                        $("#venipakOrderSubmitBtn").removeAttr('disabled');
                    }else{
                        $('.responseVenipak').html('<div class="alert alert-success">{l s="Successfully saved. Now you can print label." mod='venipakcarrier'}</div>');
                        $("#venipakOrderSubmitForm").find('input, select, button').attr('disabled','disabled');
                        $("#venipakOrderPrintLabels").removeAttr('disabled');
                    }
				},
                error: function(res){
                }
			});
            return $("#venipakOrderSubmitBtn").is(":disabled");
    }
    $("#venipakOrderSubmitBtn").unbind('click').bind('click',function(e){
        $(this).attr('disabled','disabled');
        $('.responseVenipak').html('');
        e.preventDefault();
        e.stopPropagation();
        saveVenipakOrder();

        return false;
    });
});
</script>
