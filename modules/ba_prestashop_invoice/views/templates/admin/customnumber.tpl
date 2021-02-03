{*
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2020 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<style type="text/css" media="screen">
	.bahides{
		cursor: pointer;
	}
</style>
{if isset($smarty.post.saveCustomNumber)}
<div class="module_confirmation conf confirm alert alert-success">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{l s='Update successful' mod='ba_prestashop_invoice'}
</div>
{/if}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<form action="" method="POST" id="custom-number-form" class="form-horizontal">

<div class="panel">
	<div class="panel-heading bahides"><i class="icon-th"></i> <span class="header_text">
	{l s='General' mod='ba_prestashop_invoice'}
	</span>
	<i style="float: right;margin-top: 8px;" class="icon_hide fa fa-minus-circle"></i></div>
	<div class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Enable Debug' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-9'>
			<div class="form-element">
				<input type="checkbox" name="invoice_debug" class="field-short" value="1" {if $setting.invoice_debug==1}checked{/if}/>
				<p class="description">{l s='Output as HTML to the browser instead of downloading a PDF.' mod='ba_prestashop_invoice'}</p>
			</div>
		</div>
	</div>
	<div class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Start to use Custom Number Feature from' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-2'>
			<div class="form-element">
				<input type="text" name="start_numbering" class="field-short datetimepicker" value="{$setting.invoice_start_numbering|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>
	</div>
	<div class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Paper Invoice' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-2'>
			<div class="form-element">
				<select name="bapaperinvoice">
					{for $forpaper = 1 to 10}
						<option {if $setting.bapaperinvoice == "A$forpaper"}selected{/if} value="A{$forpaper|escape:'htmlall':'UTF-8'}">A{$forpaper|escape:'htmlall':'UTF-8'}</option>
					{/for}
				</select>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<button type="submit" value="1" name="submitBaCancel" class="btn btn-default pull-left">
			<i class="process-icon-cancel"></i> {l s='Cancel' mod='ba_prestashop_invoice'}
		</button>
		<button type="submit" name="saveCustomNumber" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ba_prestashop_invoice'}</button>
	</div>
</div>
<!--  Invoice templates for different customer -->
<div class="panel">
	<div class="panel-heading bahides baselected"><i class="icon-th"></i> 
		<span class="header_text">
		{l s='Invoice templates for different customer' mod='ba_prestashop_invoice'}
		</span>
		<i style="float: right;margin-top: 8px;" class="icon_hide fa fa-plus-circle"></i>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Enable' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="custemp" id="invoice_custemp_status_active_on" value="1" {if $setting.invoice_custemp_status == 1}checked{/if}/>
				<label for="invoice_custemp_status_active_on" class="radioCheck">
					{l s='Yes' mod='ba_prestashop_invoice'}
				</label>
				<input type="radio" name="custemp" id="invoice_custemp_status_active_off" value="0" {if $setting.invoice_custemp_status == 0}checked{/if}/>
				<label for="invoice_custemp_status_active_off" class="radioCheck">
					{l s='No' mod='ba_prestashop_invoice'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Group access' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			{foreach from=$groupcus item=igroupcus}
				<div class="row" style="margin-top: 10px;">
					<div class="col-sm-2">
						<div class="checkbox">
							<b>{$igroupcus['name']|escape:'htmlall':'UTF-8'}</b>
						</div>
					</div>
					<div class="col-sm-5">
						<select name="baidtempinvoice[{$igroupcus['id_group']|escape:'htmlall':'UTF-8'}]" class="form-control">
							{foreach from=$bagetinvoice item=isqlgetinvoice}
								<option {if $isqlgetinvoice['id'] == $tabinvoicecus1[$igroupcus['id_group']]['id_template_invoice']}selected{/if} value="{$isqlgetinvoice['id']|escape:'htmlall':'UTF-8'}">{$isqlgetinvoice['name']|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
	<div style="display: none;" class="panel-footer">
		<button type="submit" value="1" name="submitBaCancel" class="btn btn-default pull-left">
			<i class="process-icon-cancel"></i> {l s='Cancel' mod='ba_prestashop_invoice'}
		</button>
		<button type="submit" name="saveCustomNumber" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ba_prestashop_invoice'}</button>
	</div>
</div>
<!--  delivery templates for different customer -->
<div class="panel">
	<div class="panel-heading bahides baselected"><i class="icon-th"></i> 
		<span class="header_text">
		{l s='delivery templates for different customer' mod='ba_prestashop_invoice'}
		</span>
		<i style="float: right;margin-top: 8px;" class="icon_hide fa fa-plus-circle"></i>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Enable' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="custempde" id="deli_custemp_status_active_on" value="1" {if $setting.deli_custemp_status == 1}checked{/if}/>
				<label for="deli_custemp_status_active_on" class="radioCheck">
					{l s='Yes' mod='ba_prestashop_invoice'}
				</label>
				<input type="radio" name="custempde" id="deli_custemp_status_active_off" value="0" {if $setting.deli_custemp_status == 0}checked{/if}/>
				<label for="deli_custemp_status_active_off" class="radioCheck">
					{l s='No' mod='ba_prestashop_invoice'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Group access' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			{foreach from=$groupcus item=igroupcus}
				<div class="row" style="margin-top: 10px;">
					<div class="col-sm-2">
						<div class="checkbox">
							<b>{$igroupcus['name']|escape:'htmlall':'UTF-8'}</b>
						</div>
					</div>
					<div class="col-sm-5">
						<select name="baidtempdeli[{$igroupcus['id_group']|escape:'htmlall':'UTF-8'}]" class="form-control">
							{foreach from=$bagetdeli item=ibagetdeli}
								<option {if $ibagetdeli['id'] == $tabdelicus1[$igroupcus['id_group']]['id_template_delivery']}selected{/if} value="{$ibagetdeli['id']|escape:'htmlall':'UTF-8'}">{$ibagetdeli['name']|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
	<div style="display: none;" class="panel-footer">
		<button type="submit" value="1" name="submitBaCancel" class="btn btn-default pull-left">
			<i class="process-icon-cancel"></i> {l s='Cancel' mod='ba_prestashop_invoice'}
		</button>
		<button type="submit" name="saveCustomNumber" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ba_prestashop_invoice'}</button>
	</div>
</div>
<!--  credit templates for different customer -->
<div class="panel">
	<div class="panel-heading bahides baselected"><i class="icon-th"></i> 
		<span class="header_text">
		{l s='Credit templates for different customer' mod='ba_prestashop_invoice'}
		</span>
		<i style="float: right;margin-top: 8px;" class="icon_hide fa fa-plus-circle"></i>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Enable' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="custempcre" id="cre_custemp_status_active_on" value="1" {if $setting.cre_custemp_status == 1}checked{/if}/>
				<label for="cre_custemp_status_active_on" class="radioCheck">
					{l s='Yes' mod='ba_prestashop_invoice'}
				</label>
				<input type="radio" name="custempcre" id="cre_custemp_status_active_off" value="0" {if $setting.cre_custemp_status == 0}checked{/if}/>
				<label for="cre_custemp_status_active_off" class="radioCheck">
					{l s='No' mod='ba_prestashop_invoice'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Group access' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			{foreach from=$groupcus item=igroupcus}
				<div class="row" style="margin-top: 10px;">
					<div class="col-sm-2">
						<div class="checkbox">
							<b>{$igroupcus['name']|escape:'htmlall':'UTF-8'}</b>
						</div>
					</div>
					<div class="col-sm-5">
						<select name="baidtempcre[{$igroupcus['id_group']|escape:'htmlall':'UTF-8'}]" class="form-control">
							{foreach from=$bagetcre item=ibagetcre}
								<option {if $ibagetcre['id'] == $tabcrecus1[$igroupcus['id_group']]['id_template_credit']}selected{/if} value="{$ibagetcre['id']|escape:'htmlall':'UTF-8'}">{$ibagetcre['name']|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
	<div style="display: none;" class="panel-footer">
		<button type="submit" value="1" name="submitBaCancel" class="btn btn-default pull-left">
			<i class="process-icon-cancel"></i> {l s='Cancel' mod='ba_prestashop_invoice'}
		</button>
		<button type="submit" name="saveCustomNumber" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ba_prestashop_invoice'}</button>
	</div>
</div>
<!--  setting for Invoice number -->
<div class="panel">
	<div class="panel-heading bahides baselected"><i class="icon-th"></i> 
		<span class="header_text">
		{l s='Custom Invoice Number Setting' mod='ba_prestashop_invoice'}
		</span>
		<i style="float: right;margin-top: 8px;" class="icon_hide fa fa-plus-circle"></i>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Enable' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			<span class="switch prestashop-switch fixed-width-lg">
				<input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" type="radio" name="invoice_number_status" id="invoice_number_status_active_on" value="1" {if $setting.invoice_number_status == 1}checked{/if}/>
				<label for="invoice_number_status_active_on" class="radioCheck">
					{l s='Yes' mod='ba_prestashop_invoice'}
				</label>
				<input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" type="radio" name="invoice_number_status" id="invoice_number_status_active_off" value="0" {if $setting.invoice_number_status == 0}checked{/if}/>
				<label for="invoice_number_status_active_off" class="radioCheck">
					{l s='No' mod='ba_prestashop_invoice'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Start Number' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="invoice_start" class="field-short" value="{$setting.invoice_start|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Step' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="invoice_step" class="field-short" value="{$setting.invoice_step|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Length' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="invoice_length" class="field-short" value="{$setting.invoice_length|escape:'htmlall':'UTF-8'}" />
				<p class="description">{l s='The length of [counter]' mod='ba_prestashop_invoice'}</p>
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Invoice number format' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="invoice_format" class="field-short" value="{$setting.invoice_format|escape:'htmlall':'UTF-8'}" />
				<p class="description">{l s='Shortcode: [counter] [m] [Y] [d] - month, year, day from date of invoice' mod='ba_prestashop_invoice'}</p>
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Reset?' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-9'>
			<div class="form-group col-sm-12">
				<div class='col-sm-2'>
					<input type="radio" name="invoice_reset" class="field-short invoice_reset" value="0" {if $setting.invoice_reset ==0}checked{/if}/>
					{l s='None' mod='ba_prestashop_invoice'}
				</div>
			</div>
			<div class="form-group col-sm-12">
				<div class='col-sm-2'>
					<input type="radio" name="invoice_reset" class="field-short invoice_reset" value="1" {if $setting.invoice_reset ==1}checked{/if}/>
					{l s='When number =' mod='ba_prestashop_invoice'}
				</div>
				<div class="col-sm-3">
					<input type="text" name="invoice_reset_value" class="field-short" value="{$setting.invoice_reset_value|escape:'htmlall':'UTF-8'}" />
				</div>
			</div>
			<div class="form-group col-sm-12 form-element">
				<div class='col-sm-2'>
					<input type="radio" name="invoice_reset" class="field-short invoice_reset" value="2" {if $setting.invoice_reset ==2}checked{/if}/>
					{l s='When date is' mod='ba_prestashop_invoice'}
				</div>
				<div class="col-sm-3">
					<input type="text" name="invoice_reset_date" class="field-short datetimepicker" value="{$setting.invoice_reset_date|escape:'htmlall':'UTF-8'}"/>
				</div>
			</div>
		</div>
	</div>
	<div style="display: none;" class="panel-footer">
		<button type="submit" value="1" name="submitBaCancel" class="btn btn-default pull-left">
			<i class="process-icon-cancel"></i> {l s='Cancel' mod='ba_prestashop_invoice'}
		</button>
		<button type="submit" name="saveCustomNumber" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ba_prestashop_invoice'}</button>
	</div>
</div>
<!--  setting for Delivery number -->
<div class="panel">
	<div class="panel-heading bahides baselected"><i class="icon-th"></i> 
		<span class="header_text">
		{l s='Custom Delivery Number Setting' mod='ba_prestashop_invoice'}
		</span>
		<i style="float: right;margin-top: 8px;" class="icon_hide fa fa-plus-circle"></i>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Enable' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			<span class="switch prestashop-switch fixed-width-lg">
				<input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" type="radio" name="delivery_number_status" id="delivery_number_status_active_on" value="1" {if $setting.delivery_number_status==1}checked{/if} />
				<label for="delivery_number_status_active_on" class="radioCheck">
					{l s='Yes' mod='ba_prestashop_invoice'}
				</label>
				<input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" type="radio" name="delivery_number_status" id="delivery_number_status_active_off" value="0" {if $setting.delivery_number_status==0}checked{/if} />
				<label for="delivery_number_status_active_off" class="radioCheck">
					{l s='No' mod='ba_prestashop_invoice'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Start Number' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="delivery_start" class="field-short" value="{$setting.delivery_start|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Step' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="delivery_step" class="field-short" value="{$setting.delivery_step|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Length' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="delivery_length" class="field-short" value="{$setting.delivery_length|escape:'htmlall':'UTF-8'}" />
				<p class="description">{l s='The length of [counter]' mod='ba_prestashop_invoice'}</p>
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Invoice number format' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="delivery_format" class="field-short" value="{$setting.delivery_format|escape:'htmlall':'UTF-8'}" />
				<p class="description">{l s='Shortcode: [counter] [m] [Y] [d] - month, year, day from date of delivery' mod='ba_prestashop_invoice'}</p>
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Reset?' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-9'>
			<div class="form-group col-sm-12">
				<div class='col-sm-2'>
					<input type="radio" name="delivery_reset" class="field-short invoice_reset" value="0" {if $setting.delivery_reset==0}checked{/if} />
					{l s='None' mod='ba_prestashop_invoice'}
				</div>
			</div>
			<div class="form-group col-sm-12">
				<div class='col-sm-2'>
					<input type="radio" name="delivery_reset" class="field-short invoice_reset" value="1" {if $setting.delivery_reset==1}checked{/if}/>
					{l s='When number =' mod='ba_prestashop_invoice'}
				</div>
				<div class="col-sm-3">
					<input type="text" name="delivery_reset_value" class="field-short" value="{$setting.delivery_reset_value|escape:'htmlall':'UTF-8'}" />
				</div>
			</div>
			<div class="form-group col-sm-12 form-element">
				<div class='col-sm-2'>
					<input type="radio" name="delivery_reset" class="field-short invoice_reset" value="2" {if $setting.delivery_reset==2}checked{/if}/>
					{l s='When date is' mod='ba_prestashop_invoice'}
				</div>
				<div class="col-sm-3">
					<input type="text" name="delivery_reset_date" class="field-short datetimepicker" value="{$setting.delivery_reset_date|escape:'htmlall':'UTF-8'}"/>
				</div>
			</div>
		</div>
	</div>
	<div style="display: none;" class="panel-footer">
		<button type="submit" value="1" name="submitBaCancel" class="btn btn-default pull-left">
			<i class="process-icon-cancel"></i> {l s='Cancel' mod='ba_prestashop_invoice'}
		</button>
		<button type="submit" name="saveCustomNumber" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ba_prestashop_invoice'}</button>
	</div>
</div>
<!--  setting for Credit Slips number -->
<div class="panel">
	<div class="panel-heading bahides baselected"><i class="icon-th"></i> 
		<span class="header_text">
		{l s='Custom Credit Slips Number Setting' mod='ba_prestashop_invoice'}
		</span>
		<i style="float: right;margin-top: 8px;" class="icon_hide fa fa-plus-circle"></i>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Enable' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			<span class="switch prestashop-switch fixed-width-lg">
				<input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" type="radio" name="credit_number_status" id="credit_number_status_active_on" value="1" {if $setting.credit_number_status==1}checked{/if}/>
				<label for="credit_number_status_active_on" class="radioCheck">
					{l s='Yes' mod='ba_prestashop_invoice'}
				</label>
				<input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" type="radio" name="credit_number_status" id="credit_number_status_active_off" value="0" {if $setting.credit_number_status==0}checked{/if} />
				<label for="credit_number_status_active_off" class="radioCheck">
					{l s='No' mod='ba_prestashop_invoice'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Start Number' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="credit_start" class="field-short" value="{$setting.credit_start|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Step' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="credit_step" class="field-short" value="{$setting.credit_step|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Length' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="credit_length" class="field-short" value="{$setting.credit_length|escape:'htmlall':'UTF-8'}" />
				<p class="description">{l s='The length of [counter]' mod='ba_prestashop_invoice'}</p>
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Credit number format' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="credit_format" class="field-short" value="{$setting.credit_format|escape:'htmlall':'UTF-8'}" />
				<p class="description">{l s='Shortcode: [counter] [m] [Y] [d] - month, year, day from date of Credit' mod='ba_prestashop_invoice'}</p>
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Reset?' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-9'>
			<div class="form-group col-sm-12">
				<div class='col-sm-2'>
					<input type="radio" name="credit_reset" class="field-short invoice_reset" value="0" {if $setting.credit_reset==0}checked{/if} />
					{l s='None' mod='ba_prestashop_invoice'}
				</div>
			</div>
			<div class="form-group col-sm-12">
				<div class='col-sm-2'>
					<input type="radio" name="credit_reset" class="field-short invoice_reset" value="1" {if $setting.credit_reset==1}checked{/if}/>
					{l s='When number =' mod='ba_prestashop_invoice'}
				</div>
				<div class="col-sm-3">
					<input type="text" name="credit_reset_value" class="field-short" value="{$setting.credit_reset_value|escape:'htmlall':'UTF-8'}"/>
				</div>
			</div>
			<div class="form-group col-sm-12 form-element">
				<div class='col-sm-2'>
					<input type="radio" name="credit_reset" class="field-short invoice_reset" value="2" {if $setting.credit_reset==2}checked{/if} />
					{l s='When date is' mod='ba_prestashop_invoice'}
				</div>
				<div class="col-sm-3">
					<input type="text" name="credit_reset_date" class="field-short datetimepicker" value="{$setting.credit_reset_date|escape:'htmlall':'UTF-8'}"/>
				</div>
			</div>
		</div>
	</div>
	<div style="display: none;" class="panel-footer">
		<button type="submit" value="1" name="submitBaCancel" class="btn btn-default pull-left">
			<i class="process-icon-cancel"></i> {l s='Cancel' mod='ba_prestashop_invoice'}
		</button>
		<button type="submit" name="saveCustomNumber" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ba_prestashop_invoice'}</button>
	</div>
</div>
<!--  setting for Order Reference -->
<div class="panel">
	<div class="panel-heading bahides baselected"><i class="icon-th"></i> 
		<span class="header_text">
		{l s='Custom Order Reference Setting' mod='ba_prestashop_invoice'}
		</span>
		<i style="float: right;margin-top: 8px;" class="icon_hide fa fa-plus-circle"></i>
	</div>
	<div class="col-sm-12 form-group" style="margin-bottom: 10px;display: none;">
		<label class="col-sm-3 control-label">{l s='Enable' mod='ba_prestashop_invoice'}: </label>
		<div class="col-sm-5">
			<span class="switch prestashop-switch fixed-width-lg">
				<input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" type="radio" name="order_number_status" id="order_number_status_active_on" value="1" {if $setting.order_number_status == 1}checked{/if}/>
				<label for="order_number_status_active_on" class="radioCheck">
					{l s='Yes' mod='ba_prestashop_invoice'}
				</label>
				<input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" type="radio" name="order_number_status" id="order_number_status_active_off" value="0" {if $setting.order_number_status == 0}checked{/if} />
				<label for="order_number_status_active_off" class="radioCheck">
					{l s='No' mod='ba_prestashop_invoice'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Start Number' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="order_start" class="field-short" value="{$setting.order_start|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Step' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="order_step" class="field-short" value="{$setting.order_step|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Length' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="order_length" class="field-short" value="{$setting.order_length|escape:'htmlall':'UTF-8'}" />
				<p class="description">{l s='The length of [counter]' mod='ba_prestashop_invoice'}</p>
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Order Reference format' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-3'>
			<div class="form-element">
				<input type="text" name="order_format" class="field-short" value="{$setting.order_format|escape:'htmlall':'UTF-8'}" />
				<p class="description">{l s='Shortcode: [counter] [m] [Y] [d] - month, year, day from date of order' mod='ba_prestashop_invoice'}</p>
			</div>
		</div>
	</div>
	<div style="display: none;" class='form-group col-sm-12'>
		<label class='col-sm-3 control-label' style='text-align:right;'>
			{l s='Reset?' mod='ba_prestashop_invoice'}
		</label>
		<div class='col-sm-9'>
			<div class="form-group col-sm-12">
				<div class='col-sm-2'>
					<input type="radio" name="order_reset" class="field-short invoice_reset" value="0" {if $setting.order_reset==0}checked{/if} />
					{l s='None' mod='ba_prestashop_invoice'}
				</div>
			</div>
			<div class="form-group col-sm-12">
				<div class='col-sm-2'>
					<input type="radio" name="order_reset" class="field-short invoice_reset" value="1" {if $setting.order_reset==1}checked{/if} />
					{l s='When number =' mod='ba_prestashop_invoice'}
				</div>
				<div class="col-sm-3">
					<input type="text" name="order_reset_value" class="field-short" value="{$setting.order_reset_value|escape:'htmlall':'UTF-8'}"/>
				</div>
			</div>
			<div class="form-group col-sm-12 form-element">
				<div class='col-sm-2'>
					<input type="radio" name="order_reset" class="field-short invoice_reset" value="2" {if $setting.order_reset==2}checked{/if} />
					{l s='When date is' mod='ba_prestashop_invoice'}
				</div>
				<div class="col-sm-3">
					<input type="text" name="order_reset_date" class="field-short datetimepicker" value="{$setting.order_reset_date|escape:'htmlall':'UTF-8'}"/>
				</div>
			</div>
		</div>
	</div>
	<div style="display: none;" class="panel-footer">
		<button type="submit" value="1" name="submitBaCancel" class="btn btn-default pull-left">
			<i class="process-icon-cancel"></i> {l s='Cancel' mod='ba_prestashop_invoice'}
		</button>
		<button type="submit" name="saveCustomNumber" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ba_prestashop_invoice'}</button>
	</div>
</div>
<input type="hidden" name="task" class="field-short" value="customnumber" />
</form>
<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery(".datetimepicker").datepicker({
			dateFormat: dateFormat1
		});
		//console.log(dateFormat1);
	});
</script>