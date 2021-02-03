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
<div {if $checkver17 == true}style="display: none;"{/if} class="panel">
	<div class="panel" style="float:left;width:100%;padding:0;">
		<div class="row"  id="sample_product_template" style="display:none;">
			<div class='product_template col-sm-12'>
				<div class="product_template_head"><i class="icon-th"></i> <span class="header_text"></span></div>
				<div class='form-group col-sm-6'>
					<label class='col-sm-3 control-label' style='text-align:right;'>Name column</label>
					<div class='col-sm-7'>
						{foreach from=$tabsuppall item=vtabsuppall}
							<input {if $vtabsuppall['id_lang'] != $lang_default}style="display:none;"{/if} class="bachecktni bachecktnis_{$vtabsuppall.id_lang|escape:'htmlall':'UTF-8'}" type='text' name="colums_title[{$vtabsuppall.id_lang|escape:'htmlall':'UTF-8'}][]" value=""/>
						{/foreach}
					</div>
					<div class="col-sm-2">
						<div style="position: relative;float: left;">
							<button type="button" class="btn btn-default balangsupp">
								<span class="ba-sp-isocode">{$iso_lang_default|escape:'htmlall':'UTF-8'}</span>
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu baitemlangsupp">
								{foreach from=$languages_select item=language}
									<li><a onclick="bagetisocode('{$language.iso_code|escape:'htmlall':'UTF-8'}',{$language.id_lang|escape:'htmlall':'UTF-8'})">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
								{/foreach}
							</ul>
						</div>
					</div>
				</div>
				<div class='form-group col-sm-6'>
					<label class='col-sm-3 control-label' style='text-align:right;'>Content column</label>
					<div class='col-sm-9'>
						<select name="colums_content[]">
							<option value="1">{l s='Product name' mod='ba_prestashop_invoice'}</option>
							<option value="2">{l s='Supplier reference' mod='ba_prestashop_invoice'}</option>
							<option value="3">{l s='Qty' mod='ba_prestashop_invoice'}</option>
							<option value="4">{l s='Unit price(Tax Excl) not Discount' mod='ba_prestashop_invoice'}</option>
							<option value="5">{l s='Total(Tax Excl) not Discount' mod='ba_prestashop_invoice'}</option>
							<option value="6">{l s='Discounted' mod='ba_prestashop_invoice'}</option>
							<option value="7">{l s='Tax rate' mod='ba_prestashop_invoice'}</option>
							<option value="8">{l s='Total(Tax Excl)' mod='ba_prestashop_invoice'}</option>
							<option value="9">{l s='Total(Tax Incl)' mod='ba_prestashop_invoice'}</option>
						</select>
					</div>
				</div>
				<div class='form-group col-sm-6'>
					<label class='col-sm-3 control-label' style='text-align:right;'>Color title column</label>
					<div class='col-sm-2'>
						<input type='text' class='color' name="colums_color[]"/>
					</div>
				</div>
				<div class='form-group col-sm-6'>
					<label class='col-sm-3 control-label' style='text-align:right;'>Color background title column</label>
					<div class='col-sm-2'>
						<input type='text' class='color' name="colums_bgcolor[]"/>
					</div>
				</div>
			</div>
		</div>
		<form id="ba_form_template" class="form-horizontal"  method="POST">
			<input type="hidden" class="bareviewlang" name="bareviewlang" value="{$lang_default|escape:'htmlall':'UTF-8'}">
			<div class="" style="padding:0 10px;width:100%;float:left;">
				<h3 class="col-sm-12" style="margin:0 !important;">
					<i class="icon-wrench"></i> {l s='General' mod='ba_prestashop_invoice'}
					<span class="panel-heading-action">
						{foreach from=$toolBarBtn item=btn key=k}
							<a id="desc-supp-{$table|escape:'htmlall':'UTF-8'}-{if isset($btn.imgclass)}{$btn.imgclass|escape:'htmlall':'UTF-8'}{else}{$k|escape:'htmlall':'UTF-8'}{/if}" class="list-toolbar-btn" {if isset($btn.href)}href="{$btn.href|escape:'htmlall':'UTF-8'}"{/if} {if isset($btn.target) && $btn.target}target="_blank"{/if}{if isset($btn.js) && $btn.js}onclick="{$btn.js|escape:'htmlall':'UTF-8'}"{/if}>
								<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s=$btn.desc mod='ba_prestashop_invoice'}" data-html="true">
									<i class="process-icon-{if isset($btn.imgclass)}{$btn.imgclass|escape:'htmlall':'UTF-8'}{else}{$k|escape:'htmlall':'UTF-8'}{/if} {if isset($btn.class)}{$btn.class|escape:'htmlall':'UTF-8'}{/if}" ></i>
								</span>
							</a>
						{/foreach}
					</span>
				</h3>
			</div>
			<div class="col-sm-12" style="margin-top:7px;">
				<div class="col-sm-12" style="margin-bottom: 10px;">
					<div class="col-sm-4 control-label" style="padding:0;font-size: 13px;">{l s='Invoice Description' mod='ba_prestashop_invoice'}: </div>
					<div class="col-sm-3"><textarea name="descriptionInvoice">{$tabsupp['description']|escape:'htmlall':'UTF-8'}</textarea></div>
				</div>
			</div>
			<div class="col-sm-12" style="margin-bottom: 10px;">
				<div class="col-sm-4 control-label" style="padding:0;font-size: 13px;">{l s='Enable Landscape PDF' mod='ba_prestashop_invoice'}: </div>
				<div class="col-sm-5"><input type="checkbox" {if $tabsupp['baInvoiceEnableLandscape'] == 1}checked{/if} name="basuppEnableLandscape" value="1"></div>
			</div>
			<div class="col-sm-12" style="margin-bottom: 10px;">
				<div class="col-sm-4 control-label" style="padding:0;font-size: 13px;">{l s='Show Pagination' mod='ba_prestashop_invoice'}: </div>
				<div class="col-sm-5"><input type="checkbox" {if $tabsupp['showPagination'] == 1}checked{/if} name="showsuppPagination" value="1"></div>
			</div>
			{* <div class="col-sm-12" style="margin-bottom: 10px;">
				<div class="col-sm-4 control-label" style="padding:0;font-size: 13px;">{l s='Language' mod='ba_prestashop_invoice'}: </div>
				<div class="col-sm-2">
					<select name="sel_language" id="sel_language">
						{foreach from=$languages_select item=language}
							<option {if $tabsupp['id_lang'] == $language.id_lang}selected{/if} value="{$language.id_lang|escape:'htmlall':'UTF-8'}">{$language.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
			</div> *}
			<div class="col-sm-12" style="margin-bottom: 10px;">
				<div class="col-sm-4 control-label" style="padding:0;font-size: 13px;">{l s='Default' mod='ba_prestashop_invoice'}: </div>
				<div class="col-sm-5">
					<span class="switch prestashop-switch fixed-width-lg">
						<input {if $tabsupp['status'] == 1}checked{/if}  type="radio" name="status_supp" id="active_on" value="1" checked="">
						<label for="active_on" class="radioCheck">
							{l s='Yes' mod='ba_prestashop_invoice'}
						</label>
						<input {if $tabsupp['status'] == 0}checked{/if} type="radio" name="status_supp" id="active_off" value="0">
						<label for="active_off" class="radioCheck">
							{l s='No' mod='ba_prestashop_invoice'}
						</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
			</div>
			<div style="padding: 0px 10px;">
				<h3 class="col-sm-12" style="margin:0;"><i class="icon-code"></i>{l s='Tokens' mod='ba_prestashop_invoice'}</h3>
				<div class="list_token col-sm-12" rev="close" style="height:100px;overflow:hidden;margin-bottom: 20px;">
					<div class="ba_invoice_token_wrapper">
						<label>[currency_prefix]: </label>
						<label class="control-label">{l s='Prefix currency' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[currency_suffix]: </label>
						<label class="control-label">{l s='Suffix currency' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[shopname]: </label>
						<label class="control-label">{l s='Name shop' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[lastname_warehouse]: </label>
						<label class="control-label">{l s='Last name warehouse' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[firstname_warehouse]: </label>
						<label class="control-label">{l s='First name warehouse' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[phone_warehouse]: </label>
						<label class="control-label">{l s='Phone warehouse' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[phone_mobile_warehouse]: </label>
						<label class="control-label">{l s='Phone mobile warehouse' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[vat_number_warehouse]: </label>
						<label class="control-label">{l s='Vat number warehouse' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[address_warehouse_address1]: </label>
						<label class="control-label">{l s='Address1 warehouse' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[address_warehouse_address2]: </label>
						<label class="control-label">{l s='Address2 warehouse' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[warehouse_postcode]: </label>
						<label class="control-label">{l s='Postcode warehouse' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[supplier_name]: </label>
						<label class="control-label">{l s='Name supplier' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[supplier_address1]: </label>
						<label class="control-label">{l s='Address1 supplier' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[supplier_address2]: </label>
						<label class="control-label">{l s='Address2 supplier' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[supplier_country]: </label>
						<label class="control-label">{l s='Country supplier' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[supplier_postcode]: </label>
						<label class="control-label">{l s='Postcode supplier' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[supplier_city]: </label>
						<label class="control-label">{l s='City supplier' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[reference]: </label>
						<label class="control-label">{l s='Reference order' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[total_te]: </label>
						<label class="control-label">{l s='Total terminal' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[discount_value_te]: </label>
						<label class="control-label">{l s='Discount value terminal' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[total_with_discount_te]: </label>
						<label class="control-label">{l s='Total with discount terminal' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[total_tax]: </label>
						<label class="control-label">{l s='Total tax' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[total_ti]: </label>
						<label class="control-label">{l s='Total order' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[date]: </label>
						<label class="control-label">{l s='Date order' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[date_delivery_expected]: </label>
						<label class="control-label">{l s='Date delivery expected order' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[product_list]: </label>
						<label class="control-label">{l s='Table list product' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[tax_list]: </label>
						<label class="control-label">{l s='Table list tax' mod='ba_prestashop_invoice'}</label>
					</div>
					<div class="ba_invoice_token_wrapper">
						<label>[title]: </label>
						<label class="control-label">{l s='Title order' mod='ba_prestashop_invoice'}</label>
					</div>
				</div>
			</div>
			<div style="padding: 0px 10px;">
				<div style="" id="show_list_token">
					{l s='More' mod='ba_prestashop_invoice'}<br>
					<i class="icon-double-angle-down"></i>
				</div>
			</div>
			<h3 class="col-sm-12" style="margin:0 0 20px;">{l s='Template' mod='ba_prestashop_invoice'}</h3>
			<div class="row" style="margin-bottom:10px">
				<div>
					<label class="col-sm-1 control-label" style="padding:0;">{l s='Header Template' mod='ba_prestashop_invoice'}</label>
					<div class="col-sm-9">
						{foreach from=$tabsuppall item=vtabsuppall}
							<div {if $vtabsuppall['id_lang'] != $lang_default}style="display:none;"{/if} class="bachecktni bachecktnis_{$vtabsuppall['id_lang']|escape:'htmlall':'UTF-8'}">
								<textarea class="baheader_invoice_template rte" id="header_invoice_template_{$vtabsuppall['id_lang']|escape:'htmlall':'UTF-8'}" name="header_invoice_template[{$vtabsuppall['id_lang']|escape:'htmlall':'UTF-8'}]">{Tools::htmlentitiesDecodeUTF8($vtabsuppall['header_invoice_template']|escape:'htmlall':'UTF-8')} 
								</textarea>
							</div>
						{/foreach}
					</div>
				</div>
				<div style="position: relative;float: left;">
					<button type="button" class="btn btn-default balangsupp">
						<span class="ba-sp-isocode">{$iso_lang_default|escape:'htmlall':'UTF-8'}</span>
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu baitemlangsupp">
						{foreach from=$languages_select item=language}
							<li><a onclick="bagetisocode('{$language.iso_code|escape:'htmlall':'UTF-8'}',{$language.id_lang|escape:'htmlall':'UTF-8'})">{$language.name}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
			<div class="row" style="margin-bottom:10px">
				<div>
					<label class="col-sm-1 control-label">{l s='Invoice Content' mod='ba_prestashop_invoice'}</label>
					<div class="col-sm-9">
						{foreach from=$tabsuppall item=vtabsuppall}
							<div {if $vtabsuppall['id_lang'] != $lang_default}style="display:none;"{/if} class="bachecktni bachecktnis_{$vtabsuppall['id_lang']|escape:'htmlall':'UTF-8'}">
								<textarea class="bainvoice_template rte" id="invoice_template_{$vtabsuppall['id_lang']|escape:'htmlall':'UTF-8'}" name="invoice_template[{$vtabsuppall['id_lang']|escape:'htmlall':'UTF-8'}]">{Tools::htmlentitiesDecodeUTF8($vtabsuppall['invoice_template']|escape:'htmlall':'UTF-8')} 
								</textarea>
							</div>
						{/foreach}
					</div>
				</div>
				<div style="position: relative;float: left;">
					<button type="button" class="btn btn-default balangsupp">
						<span class="ba-sp-isocode">{$iso_lang_default|escape:'htmlall':'UTF-8'}</span>
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu baitemlangsupp">
						{foreach from=$languages_select item=language}
							<li><a onclick="bagetisocode('{$language.iso_code|escape:'htmlall':'UTF-8'}',{$language.id_lang|escape:'htmlall':'UTF-8'})">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
			<div class="row">
				<div>
					<label class="col-sm-1 control-label">{l s='Footer Template' mod='ba_prestashop_invoice'}</label>
					<div class="col-sm-9">
						{foreach from=$tabsuppall item=vtabsuppall}
							<div {if $vtabsuppall['id_lang'] != $lang_default}style="display:none;"{/if} class="bachecktni bachecktnis_{$vtabsuppall['id_lang']|escape:'htmlall':'UTF-8'}">
								<textarea id="footer_invoice_templates_{$vtabsuppall['id_lang']|escape:'htmlall':'UTF-8'}" class="rte bafooter_invoice_template" name="footer_invoice_template[{$vtabsuppall['id_lang']|escape:'htmlall':'UTF-8'}]">{Tools::htmlentitiesDecodeUTF8($vtabsuppall['footer_invoice_template']|escape:'htmlall':'UTF-8')} 
								</textarea>
							</div>
						{/foreach}
					</div>
				</div>
				<div style="position: relative;float: left;">
					<button type="button" class="btn btn-default balangsupp">
						<span class="ba-sp-isocode">{$iso_lang_default|escape:'htmlall':'UTF-8'}</span>
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu baitemlangsupp">
						{foreach from=$languages_select item=language}
							<li><a onclick="bagetisocode('{$language.iso_code|escape:'htmlall':'UTF-8'}',{$language.id_lang|escape:'htmlall':'UTF-8'})">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
			<div class="row" style="margin-top:10px;">
				<div>
					<label class="col-sm-1 control-label">{l s='Customize CSS' mod='ba_prestashop_invoice'}</label>
					<div class="col-sm-9">
						<textarea style="height:155px;" name="customize_css">{$tabsupp['customize_css']|escape:'htmlall':'UTF-8'}</textarea>
					</div>
				</div>
			</div>
			<div style="padding: 0px 15px;" class="product_template">
				<h3 style="float:left;width:100%;margin:0 0 10px;margin: 10px 0 15px;"><i class="icon-table"></i> Products list template</h3>
				<div class="number_column">
					<div class="form-group">
						<label class="col-sm-1 control-label">{l s='Number of table' mod='ba_prestashop_invoice'}</label>
						<div class="col-sm-10">
							<input type="text" id="numberColumnOfTableTemplaterPro" value="{$tabsupp.numberColumnOfTableTemplaterPro|escape:'htmlall':'UTF-8'}" name="numberColumnOfTableTemplaterPro" onchange="addColumn()">
						</div>
					</div>
				</div>
				
				<div id="product_list_columns">
					{for $i=0 to {$tabsupp.numberColumnOfTableTemplaterPro}-1}
					<div class='product_template product_template_{$i+1|escape:'htmlall':'UTF-8'} col-sm-12'>
						<div class="product_template_head"><i class="icon-th"></i> <span class="header_text">Column {$i+1|escape:'htmlall':'UTF-8'}</span></div>
						<div class='form-group col-sm-6'>
							<label class='col-sm-3 control-label' style='text-align:right;'>{l s='Name column' mod='ba_prestashop_invoice'}</label>
							<div class='col-sm-7'>
								{foreach from=$tabsuppall item=vtabsuppall}
									{assign var="bacolumsTitleJson" value=$vtabsuppall.columsTitleJson|json_decode}
									<input {if $vtabsuppall['id_lang'] != $lang_default}style="display:none;"{/if} class="bachecktni bachecktnis_{$vtabsuppall.id_lang|escape:'htmlall':'UTF-8'}" type='text' name="colums_title[{$vtabsuppall.id_lang|escape:'htmlall':'UTF-8'}][{$i|escape:'htmlall':'UTF-8'}]" value="{$bacolumsTitleJson[$i]|escape:'htmlall':'UTF-8'}"/>
								{/foreach}
							</div>
							<div class="col-sm-2">
								<div style="position: relative;float: left;">
									<button type="button" class="btn btn-default balangsupp">
										<span class="ba-sp-isocode">{$iso_lang_default|escape:'htmlall':'UTF-8'}</span>
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu baitemlangsupp">
										{foreach from=$languages_select item=language}
											<li><a onclick="bagetisocode('{$language.iso_code|escape:'htmlall':'UTF-8'}',{$language.id_lang|escape:'htmlall':'UTF-8'})">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						</div>
						<div class='form-group col-sm-6'>
							<label class='col-sm-3 control-label' style='text-align:right;'>{l s='Content column' mod='ba_prestashop_invoice'}</label>
							<div class='col-sm-9'>
								<select name="colums_content[]">
									{assign var="bacolumsContentJson" value=$tabsupp.columsContentJson|json_decode}
									<option {if $bacolumsContentJson[$i] == 1} selected {/if} value="1">{l s='Product name' mod='ba_prestashop_invoice'}</option>
									<option {if $bacolumsContentJson[$i] == 2} selected {/if} value="2">{l s='Supplier reference' mod='ba_prestashop_invoice'}</option>
									<option {if $bacolumsContentJson[$i] == 3} selected {/if} value="3">{l s='Qty' mod='ba_prestashop_invoice'}</option>
									<option {if $bacolumsContentJson[$i] == 4} selected {/if} value="4">{l s='Unit price(Tax Excl) not Discount' mod='ba_prestashop_invoice'}</option>
									<option {if $bacolumsContentJson[$i] == 5} selected {/if} value="5">{l s='Total(Tax Excl) not Discount' mod='ba_prestashop_invoice'}</option>
									<option {if $bacolumsContentJson[$i] == 6} selected {/if} value="6">{l s='Discounted' mod='ba_prestashop_invoice'}</option>
									<option {if $bacolumsContentJson[$i] == 7} selected {/if} value="7">{l s='Tax rate' mod='ba_prestashop_invoice'}</option>
									<option {if $bacolumsContentJson[$i] == 8} selected {/if} value="8">{l s='Total(Tax Excl)' mod='ba_prestashop_invoice'}</option>
									<option {if $bacolumsContentJson[$i] == 9} selected {/if} value="9">{l s='Total(Tax Incl)' mod='ba_prestashop_invoice'}</option>
								</select>
							</div>
						</div>
						<div class='form-group col-sm-6'>
							<label class='col-sm-3 control-label' style='text-align:right;'>{l s='Color title column' mod='ba_prestashop_invoice'}</label>
							<div class='col-sm-2'>
								{assign var="bacolumsColorJson" value=$tabsupp.columsColorJson|json_decode}
								<input type='text' class='color' name="colums_color[]" value="{$bacolumsColorJson[$i]|escape:'htmlall':'UTF-8'}"/>
							</div>
						</div>
						<div class='form-group col-sm-6'>
							<label class='col-sm-3 control-label' style='text-align:right;'>{l s='Color background title column' mod='ba_prestashop_invoice'}</label>
							<div class='col-sm-2'>
								{assign var="bacolumsColorBgJson" value=$tabsupp.columsColorBgJson|json_decode}
								<input type='text' class='color' name="colums_bgcolor[]" value="{$bacolumsColorBgJson[$i]|escape:'htmlall':'UTF-8'}"/>
							</div>
						</div>
					</div>
					{/for}
				</div>
				<script>
					var sample_product;
					var init_table_colums={$tabsupp.numberColumnOfTableTemplaterPro|escape:'htmlall':'UTF-8'};
					function addColumn(){
						var numberColumn = parseInt(jQuery("#numberColumnOfTableTemplaterPro").val());	
						if(numberColumn>init_table_colums){
							//console.log(numberColumn);
							for(var i=init_table_colums+1; i<=numberColumn; i++){
								sample_product=$("#sample_product_template").clone();
								sample_product.find(".product_template").addClass("product_template_"+i);
								sample_product.find(".header_text").text("Columns "+i);
								$("#product_list_columns").append(sample_product.html());	
							}
							jscolor.bind();
						}else{
							for(var i=numberColumn+1; i<=init_table_colums; i++){
								$(".product_template_"+i).remove();
							}
						}
						////////////
						init_table_colums=numberColumn;
					}
					
					//Open and close token
					
					
				</script>
			</div>
			<div class="panel-footer">
				<button type="submit" value="1" name="submitBaSupCancel" class="btn btn-default pull-left">
					<i class="process-icon-cancel"></i> {l s='Cancel' mod='ba_prestashop_invoice'}
				</button>
				<button type="submit" value="1" name="submitBaSupSave" class="btn btn-default pull-right">
					<i class="process-icon-save"></i> {l s='Save' mod='ba_prestashop_invoice'}
				</button>
				<a id="preview_supinvoice" class="btn btn-default pull-right">
					<i class="process-icon-preview"></i> {l s='Preview' mod='ba_prestashop_invoice'}
				</a>
			</div>
		</form>
	</div>
</div>