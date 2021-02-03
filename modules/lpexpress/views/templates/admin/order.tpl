<div class="panel">
    <div class="panel-heading">
        <i class="icon-truck"></i>
        {l s='LPExpress' mod='lpexpress'}
    </div>
    <div class="well">
        <form action="{$link->getAdminLink('AdminOrders')}&id_order={$lp_order->id_order}&vieworder=1" method="POST">
            <div class="form-horizontal">
                <div class="form-group">
                    <label for="lp_packets" class="control-label col-lg-3 required">{l s='Packets' mod='lpexpress'}:</label>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <input id="lp_packets" name="lp_packets" class="form-control" type="text" value="{if isset($smarty.post.lp_packets)}{$smarty.post.lp_packets}{else}{$lp_order->packets}{/if}"{if $lp_order->isConfirmed()} disabled{/if}>
                            <span class="input-group-addon">
                                {l s='pcs' mod='lpexpress'}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group" {if $LP_ORDER_HOME_TYPE == 'CH'}data-reference-carrier="{$carrier_post->id_reference}"{else}data-reference-ignore-carrier="{$carrier_terminal->id_reference}"{/if}>
                    <label for="lp_weight" class="control-label col-lg-3 required">{l s='Weight' mod='lpexpress'}:</label>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <input id="lp_weight" name="lp_weight" class="form-control" type="text" value="{if isset($smarty.post.lp_weight)}{$smarty.post.lp_weight}{else}{$lp_order->weight}{/if}"{if $lp_order->isConfirmed()} disabled{/if}>
                            <span class="input-group-addon">
                                {l s='kg' mod='lpexpress'}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="lp_carrier" class="control-label col-lg-3 required">{l s='Carriers' mod='lpexpress'}:</label>
                    <div class="col-lg-9">
                        <select id="lp_carrier" name="lp_carrier"{if $lp_order->isConfirmed()} disabled{/if}>
                            <option value="{$carrier_post->id_reference}"{if (isset($smarty.post.lp_carrier) && $smarty.post.lp_carrier == $carrier_post->id_reference) || (!isset($smarty.post.lp_carrier) && $carrier_post->id_reference == $order_carrier->id_reference)} selected{/if}>{$carrier_post->name}</option>
                            <option value="{$carrier_terminal->id_reference}"{if (isset($smarty.post.lp_carrier) && $smarty.post.lp_carrier == $carrier_terminal->id_reference) || (!isset($smarty.post.lp_carrier) && $carrier_terminal->id_reference == $order_carrier->id_reference)} selected{/if}>{$carrier_terminal->name}</option>
                            <option value="{$carrier_home->id_reference}"{if (isset($smarty.post.lp_carrier) && $smarty.post.lp_carrier == $carrier_home->id_reference) || (!isset($smarty.post.lp_carrier) && $carrier_home->id_reference == $order_carrier->id_reference)} selected{/if}>{$carrier_home->name}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-reference-carrier="{$carrier_terminal->id_reference}">
                    <label for="lp_terminal" class="control-label col-lg-3 required">{l s='Terminals' mod='lpexpress'}:</label>
                    <div class="col-lg-9">
                        <select id="lp_terminal" name="lp_terminal"{if $lp_order->isConfirmed()} disabled{/if}>
                            <option>{l s='Select terminal' mod='lpexpress'}</option>
                            {foreach $terminal_list as $city => $terminals_by_city}
                                <optgroup label="{$city}">
                                    {foreach $terminals_by_city as $terminal}
                                        <option value="{$terminal['id_lpexpress_terminal']}"{if (isset($smarty.post.lp_terminal) && $smarty.post.lp_terminal == $terminal['id_lpexpress_terminal']) || (!isset($smarty.post.lp_terminal) && $lp_order->id_lpexpress_terminal == $terminal['id_lpexpress_terminal'])} selected{/if}>{$terminal['name']} {$terminal['address']}, {$terminal['city']}</option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="form-group" {if $LP_ORDER_HOME_TYPE == 'CH'}data-reference-ignore-carrier="{$carrier_post->id_reference}"{else}data-reference-carrier="{$carrier_terminal->id_reference}"{/if}>
                    <input type="hidden" id="lp_terminal_box_size" value="{if isset($smarty.post.lp_box_size)}{$smarty.post.lp_box_size}{else}{$lp_order->id_lpexpress_box}{/if}">
                    <label for="lp_box_size" class="control-label col-lg-3 required">{l s='Box size' mod='lpexpress'}:</label>
                    <div class="col-lg-9">
                        <select id="lp_box_size" name="lp_box_size"{if $lp_order->isConfirmed()} disabled{/if}>
                            <option>{l s='Select box size' mod='lpexpress'}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-reference-carrier="{$carrier_post->id_reference}">
                    <label for="lp_post_address" class="control-label col-lg-3 required">{l s='Post address' mod='lpexpress'}:</label>
                    <div class="col-lg-9">
                        <div>
                            <input id="lp_post_address" class="form-control" type="text" value="{$post_address}" disabled>
                        </div>
                    </div>
                </div>


                <div class="form-group" data-reference-ignore-carrier="{$carrier_post->id_reference}">
                    <label class="control-label col-lg-3">{l s='Cash on delivery' mod='lpexpress'}:</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="lp_cod" id="cod_visibility_on" value="1"{if (isset($smarty.post.lp_cod) && $smarty.post.lp_cod == 1) || (!isset($smarty.post.lp_cod) && $lp_order->cod == 1)} checked{/if}{if $lp_order->isConfirmed()} disabled{/if}>
                            <label for="cod_visibility_on">{l s='Yes' mod='lpexpress'}</label>

                            <input type="radio" name="lp_cod" id="cod_visibility_off" value="0"{if (isset($smarty.post.lp_cod) && $smarty.post.lp_cod == 0) || (!isset($smarty.post.lp_cod) && $lp_order->cod == 0)} checked{/if}{if $lp_order->isConfirmed()} disabled{/if}>
                            <label for="cod_visibility_off">{l s='No' mod='lpexpress'}</label>

                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>

                <div class="form-group" id="lp_cod_amount"{if (isset($smarty.post.lp_cod) && $smarty.post.lp_cod == 0) || (!isset($smarty.post.lp_cod) && $lp_order->cod == 0)} style="display: none;"{/if}>
                    <label for="lp_cod_amount" class="control-label col-lg-3 required">{l s='Cash on delivery amount' mod='lpexpress'}:</label>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <input id="lp_cod_amount" name="lp_cod_amount" class="form-control" type="text" value="{if isset($smarty.post.lp_cod_amount)}{$smarty.post.lp_cod_amount}{else}{$lp_order->cod_amount}{/if}"{if $lp_order->isConfirmed()} disabled{/if}>
                            <span class="input-group-addon">
                                {l s='€' mod='lpexpress'}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="lp_comment" class="control-label col-lg-3 required">{l s='Comment' mod='lpexpress'}:</label>
                    <div class="col-lg-9">
                        <textarea id="lp_comment" name="lp_comment" class="textarea-autosize" style="overflow: hidden; word-wrap: break-word; resize: none; height: 48px;"{if $lp_order->isConfirmed()} disabled{/if}>{if isset($smarty.post.lp_comment)}{$smarty.post.lp_comment}{else}{$lp_order->comment}{/if}</textarea>
                    </div>
                </div>

                {if $lp_order->isConfirmed()}
                    {if $lp_order->isManifestCreated()}
                        <a target="_blank" href="{$link->getAdminLink('AdminOrders')}&id_order={$lp_order->id_order}&vieworder=1&getLPManifest=1" class="btn btn-primary pull-right">
                            {l s='Get manifest' mod='lpexpress'}
                        </a>
                    {else}
                        <button type="submit" class="btn btn-primary pull-right" name="cancelLPOrder">
                            {l s='Cancel' mod='lpexpress'}
                        </button>
                    {/if}
                    <a target="_blank" href="{$link->getAdminLink('AdminOrders')}&id_order={$lp_order->id_order}&vieworder=1&getLPLabel=1" class="btn btn-primary pull-right" style="margin-right: 10px">
                        {l s='Get label' mod='lpexpress'}
                    </a>
                {else}
                    <button type="submit" class="btn btn-primary pull-right" name="saveLPOrder">
                        {l s='Save' mod='lpexpress'}
                    </button>

                    <button type="submit" class="btn btn-primary pull-right" name="generateLPOrder" style="margin-right: 10px">
                        {l s='Generate' mod='lpexpress'}
                    </button>
                {/if}

                <div class="clearfix"></div>
            </div>
        </form>
    </div>
</div>

<script>
    var terminals = {$terminals};
    var all_boxes = {$boxes};
    var LP_ORDER_HOME_TYPE = "{$LP_ORDER_HOME_TYPE}";
    var carrier_post_reference = "{$carrier_post->id_reference}";
    var carrier_terminal_reference = "{$carrier_terminal->id_reference}";
    var carrier_home_reference = "{$carrier_home->id_reference}";
</script>
