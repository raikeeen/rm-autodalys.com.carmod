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
<page size="A4">
<div style="clear:both;background: #fff;width: 764px;">{$header}{* HTML, cannot escape*}</div>
	{$content}{* HTML, cannot escape*}
<div style="clear:both;background: #fff;width: 764px;">{$footer}{* HTML, cannot escape*}</div>
</page>
<style type="text/css">
page {
  background: white;
  display: block;
  margin: 0 auto;
  margin-bottom: 0.5cm;
  box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
}
{if $landscape == 'Y'}
	page[size="A4"] {  
	  height: 21cm;
	  width: 29.7cm; 
	  padding:16px 15px;
	}
{else}
	page[size="A4"] {  
	  width: 21cm;
	  height: 29.7cm; 
	  padding:16px 15px;
	}
{/if}
</style>