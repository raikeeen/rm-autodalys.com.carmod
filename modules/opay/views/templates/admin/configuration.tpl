{if $version <= 14}
<br/>
<img height="33" style="float:left; margin-right:15px;" src="https://widgets.opay.lt/img/internal_opay_color_0x33.png"><br/>
<b>{l s='OPAY payment gateway' mod='opay'}</b>
<br/>
<br/>
<br/>
<br/>
<form method="post" action="{$requestUrl|escape:'quotes'}">
    {if $messages ne ''}
    <div class="bootstrap">
        {foreach from=$messages item=message}
        <div class="{if $message.class=='conf confirm'}conf confirm{else}alert error{/if}">
            {$message.msg|escape:'html'}
        </div>
        {/foreach}
    </div>
    {/if}
    <fieldset>
        <legend><img src="../img/admin/contact.gif">{l s='Payment module details' mod='opay'}</legend>
        <table cellspacing="0" cellpadding="0" border="0" width="100%" id="form">
            <tbody>
                <tr>
                    <td width="130" style="height: 35px;">*{l s='OPAY website ID (website_id):' mod='opay'}</td>
                    <td><input style="width: 300px;" type="text" value="{$website_id|escape}" name="website_id"></td>
                </tr>
                <tr>
                    <td width="130" style="height: 35px;">{l s='Test mode:' mod='opay'}</td>
                    <td>
                        <label for="testmode_on" style="width: auto;"><input type="radio" value="1" id="testmode_on" name="testmode" onclick="{literal}if($(this).is(':selected')){$('.testmode').hide();}else{$('.testmode').show();}{/literal}" {if $testmode}checked="checked"{/if} >
                            {l s='Yes' mod='opay'}</label>
                        <label for="testmode_off" style="width: auto;"><input type="radio" value="0" id="testmode_off" name="testmode" onclick="{literal}if($(this).is(':selected')){$('.testmode').show();}else{$('.testmode').hide();}{/literal}" {if !$testmode}checked="checked"{/if} >
                            {l s='No' mod='opay'}</label>
                    </td>
                </tr>
                <tr class="testmode" style="{if !$testmode}display: none;{/if}">
                    <td width="130" style="height: 35px;">*{l s='OPAY user ID (user_id):' mod='opay'}</td>
                    <td><input style="width: 300px;" type="text" value="{$opay_user_id|escape}" name="opay_user_id"></td>
                </tr>
                <tr>
                    <td width="130" style="height: 35px;">{l s='Signature type:' mod='opay'}</td>
                    <td>
                        <label for="typersa" style="width: auto;"><input id="typersa" onchange="{literal}if($(this).is(':selected')){$('.rsa').hide();$('.password').show();}else{$('.rsa').show();$('.password').hide();}{/literal}" type="radio" name="signature_type" value="rsa" {if $signature_type == 'rsa'}checked="checked"{/if} />
                            {l s='RSA privačiu raktu' mod='opay'}</label>
                        <label for="typepass" style="width: auto;"><input id="typepass" onchange="{literal}if($(this).is(':selected')){$('.rsa').show();$('.password').hide();}else{$('.rsa').hide();$('.password').show();}{/literal}"  type="radio" name="signature_type" value="password" {if $signature_type == 'password'}checked="checked"{/if} />
                            {l s='Pasirašymo slaptažodžiu' mod='opay'}</label>
                    </td>
                </tr>
                <tr class="rsa" style="{if $signature_type == 'password'}display: none;{/if}">
                    <td width="130" style="height: 35px;vertical-align: top;">*{l s='Private key:' mod='opay'}</td>
                    <td><textarea class=" textarea-autosize" name="private_key" style="width: 600px;height: 361px;">{$private_key|escape}</textarea><br/><br/></td>
                </tr>
                <tr class="rsa" style="{if $signature_type == 'password'}display: none;{/if}">
                    <td width="130" style="height: 35px;vertical-align: top;">*{l s='OPAY sertificate:' mod='opay'}</td>
                    <td><textarea class=" textarea-autosize" name="certificate" style="width: 600px;height: 361px;">{$certificate|escape}</textarea></td>
                </tr>
                <tr class="password" style="{if $signature_type == 'rsa'} display: none;{/if}">
                    <td width="130" style="height: 35px;">*{l s='Signature password:' mod='opay'}</td>
                    <td><input style="width: 300px;" type="text" name="signature_password" value="{$signature_password|escape}"></td>
                </tr>
                <tr>
                    <td width="170" style="height: 35px;">{l s='Display payments list:' mod='opay'}</td>
                    <td>
                        <label for="payment1" style="width: auto;"><input id="payment1" type="radio" name="payment_list" value="1" {if $payment_list == 1}checked="checked"{/if} />
                            {l s='In checkout page' mod='opay'}</label>
                        <label for="payment2" style="width: auto;"><input id="payment2" type="radio" name="payment_list" value="2" {if $payment_list == 2}checked="checked"{/if} />
                            {l s='In checkout page grouped' mod='opay'}</label>
                        <label for="payment0" style="width: auto;"><input id="payment0" type="radio" name="payment_list" value="0" {if $payment_list == 0}checked="checked"{/if} />
                            {l s='In external OPAY page' mod='opay'}</label>
                    </td>
                </tr>
                <tr>
                    <td width="130" style="height: 35px;">{l s='Logo size:' mod='opay'}</td>
                    <td>
                        <select name="logo_size">
                            <option value="33"{if $logo_size=='33'} selected="selected"{/if}>33px</option>
                            <option value="49"{if $logo_size=='49'} selected="selected"{/if}>49px</option>
                        </select>
                    </td>
                </tr>
                <tr><td align="center" colspan="2"><input type="submit" value="Update settings" name="btnSubmit" class="button"></td></tr>
            </tbody></table>
    </fieldset>
</form>
{elseif $version == 15}
<br/>
<img height="33" style="float:left; margin-right:15px;" src="https://widgets.opay.lt/img/internal_opay_color_0x33.png"><br/>
<b>{l s='OPAY payment gateway' mod='opay'}</b>
<br/>
<br/>
<br/>
<br/>
<form method="post" action="{$requestUrl|escape:'quotes'}">
    {if $messages ne ''}
    <div class="bootstrap">
        {foreach from=$messages item=message}
        <div class="{if $message.class=='conf confirm'}conf confirm{else}alert error{/if}">
            {$message.msg|escape:'html'}
        </div>
        {/foreach}
    </div>
    {/if}
    <fieldset>
        <legend><img src="../img/admin/contact.gif">{l s='Payment module details' mod='opay'}</legend>
        <table cellspacing="0" cellpadding="0" border="0" width="100%" id="form">
            <tbody>
                <tr>
                    <td style="width:130px; height: 35px;">*{l s='OPAY website ID (website_id):' mod='opay'}</td>
                    <td><input style="width: 300px;" type="text" value="{$website_id|escape}" name="website_id"></td>
                </tr>
                <tr>
                    <td style="width:130px; height: 35px;">{l s='Test mode:' mod='opay'}</td>
                    <td style="text-align:left;">
                        <label class="radioCheck" for="testmode_on" style="width:auto; margin-right:10px;">
                            <input type="radio" value="1" id="testmode_on" name="testmode" onclick="{literal}if($(this).is(':selected')){$('.testmode').hide();}else{$('.testmode').show();}{/literal}" {if $testmode}checked="checked"{/if} >
                            {l s='Yes' mod='opay'}
                        </label>
                        <label class="radioCheck" for="testmode_off" style="width:auto; margin-right:10px;">
                            <input type="radio" value="0" id="testmode_off" name="testmode" onclick="{literal}if($(this).is(':selected')){$('.testmode').show();}else{$('.testmode').hide();}{/literal}" {if !$testmode}checked="checked"{/if} >
                            {l s='No' mod='opay'}
                        </label>
                    </td>
                </tr>
                <tr class="testmode" style="{if !$testmode}display: none;{/if}">
                    <td style="width:130px; height: 35px;">*{l s='OPAY user ID (user_id):' mod='opay'}</td>
                    <td><input style="width: 300px;" type="text" value="{$opay_user_id|escape}" name="opay_user_id"></td>
                </tr>
                <tr>
                    <td style="width:130px; height: 35px;">{l s='Signature type:' mod='opay'}</td>
                    <td>
                        <label class="radioCheck" for="signature_type_rsa" style="width:auto; margin-right:10px;">
                            <input id="signature_type_rsa" onchange="{literal}if($(this).is(':selected')){$('.rsa').hide();$('.password').show();}else{$('.rsa').show();$('.password').hide();}{/literal}" type="radio" name="signature_type" value="rsa" {if $signature_type == 'rsa'}checked="checked"{/if} />
                            {l s='RSA privačiu raktu' mod='opay'}
                        </label>
                        <label class="radioCheck" for="signature_type_pss" style="width:auto; margin-right:10px;">
                            <input id="signature_type_pss" onchange="{literal}if($(this).is(':selected')){$('.rsa').show();$('.password').hide();}else{$('.rsa').hide();$('.password').show();}{/literal}"  type="radio" name="signature_type" value="password" {if $signature_type == 'password'}checked="checked"{/if}  />
                            {l s='Pasirašymo slaptažodžiu' mod='opay'}
                        </label>
                    </td>
                </tr>
                <tr class="rsa" style="{if $signature_type == 'password'}display: none;{/if}">
                    <td style="width:130px; height: 35px; vertical-align: top;">*{l s='Private key:' mod='opay'}</td>
                    <td><textarea class=" textarea-autosize" name="private_key" style="width: 600px;height: 361px;">{$private_key|escape}</textarea><br/><br/></td>
                </tr>
                <tr class="rsa" style="{if $signature_type == 'password'}display: none;{/if}">
                    <td width="130" style="height: 35px;vertical-align: top;">*{l s='OPAY sertificate:' mod='opay'}</td>
                    <td><textarea class=" textarea-autosize" name="certificate" style="width: 600px;height: 361px;">{$certificate|escape}</textarea></td>
                </tr>
                <tr class="password" style="{if $signature_type == 'rsa'}display: none;{/if}">
                    <td width="130" style="height: 35px;">*{l s='Signature password:' mod='opay'}</td>
                    <td><input style="width: 300px;" type="text" name="signature_password" value="{$signature_password|escape}"></td>
                </tr>
                <tr>
                    <td style="width:130px; height: 35px; padding-right:10px;">{l s='Display payments list:' mod='opay'}</td>
                    <td>

                        <label class="radioCheck" for="payment_list1" style="width:auto; margin-right:10px;">
                            <input id="payment_list1" type="radio" name="payment_list" value="1" {if $payment_list == 1}checked="checked"{/if} />
                            {l s='In checkout page' mod='opay'}
                        </label>

                        <label class="radioCheck" for="payment_list2" style="width:auto; margin-right:10px;">
                            <input id="payment_list2" type="radio" name="payment_list" value="2" {if $payment_list == 2}checked="checked"{/if} />
                            {l s='In checkout page grouped' mod='opay'}
                        </label>

                        <label class="radioCheck" for="payment_list0" style="width:auto; margin-right:10px;">
                            <input id="payment_list0" type="radio" name="payment_list" value="0" {if $payment_list == 0}checked="checked"{/if} />
                            {l s='In external OPAY page' mod='opay'}
                        </label>
                    </td>
                </tr>
                <tr>
                    <td style="width:130px; height: 35px;">{l s='Logo size:' mod='opay'}</td>
                    <td>
                        <select name="logo_size">
                            <option value="33"{if $logo_size=='33'} selected="selected"{/if}>33px</option>
                            <option value="49"{if $logo_size=='49'} selected="selected"{/if}>49px</option>
                        </select>
                    </td>
                </tr>
                <tr><td style="text-align:center;" colspan="2"><input type="submit" value="Update settings" name="btnSubmit" class="button"></td></tr>
            </tbody></table>
    </fieldset>
</form>
{else} {* other versions *}
{if $messages ne ''}
<div class="bootstrap">
    {foreach from=$messages item=message}
    <div class="{if $message.class=='conf confirm'}module_confirmation alert alert-success{else}module_error alert alert-danger{/if}">
        <button data-dismiss="alert" class="close" type="button">×</button>
        {$message.msg|escape:'html'}
    </div>
    {/foreach}
</div>
{/if}
<div class="alert">
    <img src="https://widgets.opay.lt/img/internal_opay_color_0x33.png" style="float:left; margin:-8px 15px 0 0;">
    <p><strong>{l s='OPAY payment gateway' mod='opay'}</strong></p>
    <p>
    </p>
</div>
<form novalidate="" enctype="multipart/form-data" method="post" action="{$requestUrl|escape:'quotes'}" class="defaultForm form-horizontal" id="module_form">
    <input type="hidden" value="1" name="btnSubmit">

    <div id="fieldset_0" class="panel">

        <div class="panel-heading">
            <i class="icon-dollar"></i> {l s='Payment module details' mod='opay'}
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3 required">{l s='OPAY website ID (website_id):' mod='opay'}</label>
                <div class="col-lg-9 ">
                    <input type="text" value="{$website_id|escape}" name="website_id">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Test mode:' mod='opay'}</label>
                <div class="col-lg-9 ">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" value="1" id="testmode_on" name="testmode" onclick="{literal}if($(this).is(':selected')){$('.testmode').hide();}else{$('.testmode').show();}{/literal}toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" {if $testmode}checked="checked"{/if} >
                        <label class="radioCheck" for="testmode_on">
                            {l s='Yes' mod='opay'}
                        </label>
                        <input type="radio" value="0" id="testmode_off" name="testmode" onclick="{literal}if($(this).is(':selected')){$('.testmode').show();}else{$('.testmode').hide();}{/literal}toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" {if !$testmode}checked="checked"{/if} >
                        <label class="radioCheck" for="testmode_off">
                            {l s='No' mod='opay'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="form-group testmode" style="{if !$testmode}display: none;{/if}">
                <label class="control-label col-lg-3 required">{l s='OPAY user ID (user_id):' mod='opay'}</label>
                <div class="col-lg-9 ">
                    <input type="text" value="{$opay_user_id|escape}" name="opay_user_id">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Signature type:' mod='opay'}</label>
                <div class="col-lg-9 ">
                    <div class="radio">
                        <label for="simple_product_rsa"><input id="simple_product_rsa" onchange="{literal}if($(this).is(':selected')){$('.rsa').hide();$('.password').show();}else{$('.rsa').show();$('.password').hide();}{/literal}" type="radio" name="signature_type" value="rsa" {if $signature_type == 'rsa'}checked="checked"{/if} />{l s='RSA privačiu raktu' mod='opay'}</label>
                    </div>
                    <div class="radio">
                        <label for="simple_product_pass"><input id="simple_product_pass" onchange="{literal}if($(this).is(':selected')){$('.rsa').show();$('.password').hide();}else{$('.rsa').hide();$('.password').show();}{/literal}"  type="radio" name="signature_type" value="password" {if $signature_type == 'password'}checked="checked"{/if} />{l s='Pasirašymo slaptažodžiu' mod='opay'}</label>
                    </div>
                </div>
            </div>
            <div class="form-group rsa" style="{if $signature_type == 'password'}display: none;{/if}">
                <label class="control-label col-lg-3 required">{l s='Private key:' mod='opay'}</label>
                <div class="col-lg-9 ">
                    <textarea class=" textarea-autosize" name="private_key" style="height: 361px;">{$private_key|escape}</textarea>
                </div>
            </div>
            <div class="form-group rsa" style="{if $signature_type == 'password'}display: none;{/if}">
                <label class="control-label col-lg-3 required">{l s='OPAY sertificate:' mod='opay'}</label>
                <div class="col-lg-9 ">
                    <textarea class=" textarea-autosize" name="certificate" style="height: 361px;">{$certificate|escape}</textarea>
                </div>
            </div>
            <div class="form-group password" style="{if $signature_type == 'rsa'}display: none;{/if}">
                <label class="control-label col-lg-3 required">{l s='Signature password:' mod='opay'}</label>
                <div class="col-lg-9 ">
                    <input type="text" name="signature_password" value="{$signature_password|escape}">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Display payments list:' mod='opay'}</label>
                <div class="col-lg-9 ">
                    <div class="radio">
                        <label for="payment_list1"><input id="payment_list1" type="radio" name="payment_list" value="1" {if $payment_list == 1}checked="checked"{/if} />{l s='In checkout page' mod='opay'}</label>
                    </div>
                    <div class="radio">
                        <label for="payment_list2"><input id="payment_list2" type="radio" name="payment_list" value="2" {if $payment_list == 2}checked="checked"{/if} />{l s='In checkout page grouped' mod='opay'}</label>
                    </div>
                    {if $version >= 17}
                    <div class="radio">
                        <label for="payment_list3"><input id="payment_list3" type="radio" name="payment_list" value="3" {if $payment_list == 3}checked="checked"{/if} />{l s='In checkout page as seperate options (text only)' mod='opay'}</label>
                    </div>
                    <div class="radio">
                        <label for="payment_list4"><input id="payment_list4" type="radio" name="payment_list" value="4" {if $payment_list == 4}checked="checked"{/if} />{l s='In checkout page as seperate options (logo only)' mod='opay'}</label>
                    </div>
                    <div class="radio">
                        <label for="payment_list5"><input id="payment_list5" type="radio" name="payment_list" value="5" {if $payment_list == 5}checked="checked"{/if} />{l s='In checkout page as seperate options (text and logo)' mod='opay'}</label>
                    </div>
                    {/if}
                    <div class="radio">
                        <label for="payment_list0"><input id="payment_list0" type="radio" name="payment_list" value="0" {if $payment_list == 0}checked="checked"{/if} />{l s='In external OPAY page' mod='opay'}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Logo size:' mod='opay'}</label>
                <div class="col-lg-9 ">
                    <select name="logo_size">
                        <option value="33"{if $logo_size=='33'} selected="selected"{/if}>33px</option>
                        <option value="49"{if $logo_size=='49'} selected="selected"{/if}>49px</option>
                    </select>
                </div>
            </div>
        </div><!-- /.form-wrapper -->



        <div class="panel-footer">
            <button class="btn btn-default pull-right" name="btnSubmit" id="module_form_submit_btn" value="1" type="submit">
                <i class="process-icon-save"></i> {l s='Update settings' mod='opay'}
            </button>
        </div>

    </div>


</form>
{/if}
