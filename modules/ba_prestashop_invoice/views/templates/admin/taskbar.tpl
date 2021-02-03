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
{if $demoMode=="1"}
<div class="bootstrap ba_error">
	<div class="module_error alert alert-danger">
		{l s='You are use ' mod='ba_prestashop_invoice'}
		<strong>{l s='Demo Mode ' mod='ba_prestashop_invoice'}</strong>
		{l s=', so some buttons, functions will be disabled because of security. ' mod='ba_prestashop_invoice'}
		{l s='You can use them in Live mode after you puchase our module. ' mod='ba_prestashop_invoice'}
		{l s='Thanks !' mod='ba_prestashop_invoice'}
	</div>
</div>
{/if}
<ul class="nav nav-tabs">
    <li class="{if $taskbar=="orderinvoice"}active{/if}">
		<a href="{$bamodule|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&ba_lang={$ba_lang|escape:'htmlall':'UTF-8'}&task=orderinvoice">{l s='Invoice' mod='ba_prestashop_invoice'}</a>
	</li>
    <li class="{if $taskbar=="deliveryslip"}active{/if}">
		<a href="{$bamodule|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&ba_lang={$ba_lang|escape:'htmlall':'UTF-8'}&task=deliveryslip">{l s='Delivery Slips' mod='ba_prestashop_invoice'}</a>
	</li>
    <li class="{if $taskbar=="creditslip"}active{/if}">
		<a href="{$bamodule|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&ba_lang={$ba_lang|escape:'htmlall':'UTF-8'}&task=creditslip">{l s='Credit Slips' mod='ba_prestashop_invoice'}</a>
	</li>
	<li {if $checkver17 == true}style="display: none;"{/if} class="{if $taskbar=="supplier"}active{/if}">
		<a href="{$bamodule|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&ba_lang={$ba_lang|escape:'htmlall':'UTF-8'}&task=supplier">{l s='Supply Orders' mod='ba_prestashop_invoice'}</a>
	</li>
	<li class="{if $taskbar=="customnumber"}active{/if}">
		<a href="{$bamodule|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&ba_lang={$ba_lang|escape:'htmlall':'UTF-8'}&task=customnumber">{l s='Settings' mod='ba_prestashop_invoice'}</a>
	</li>
</ul>