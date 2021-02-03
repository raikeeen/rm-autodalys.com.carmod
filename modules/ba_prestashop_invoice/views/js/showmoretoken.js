/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@buy-addons.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    Buy-addons    <contact@buy-addons.com>
* @copyright 2007-2020 Buy-addons
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
jQuery(document).ready(function(){
	jQuery('#show_list_token').click(function(){
		var checkListToken = jQuery('.list_token').attr('rev');
		if(checkListToken=="close"){
			jQuery('.list_token').css('height','auto');
			jQuery('.list_token').attr('rev','open');
		}else if(checkListToken=="open"){
			jQuery('.list_token').css('height','100px');
			jQuery('.list_token').attr('rev','close');
		}
	});
	jQuery('table.ba_prestashop_invoice_invoice tbody tr').tooltip({
		content:function(){
			return $(this).find(".riverroad").html();
		}, 
		show: null,
		items: "tr",
	});
	jQuery('table.ba_prestashop_invoice_deliveryslip tbody tr').tooltip({
		content:function(){
			return $(this).find(".riverroad").html();
		}, 
		show: null,
		items: "tr",
	});
	jQuery('.balangsupp').on('click',function(){
		if ($(this).hasClass('selected')) {
			jQuery('.baitemlangsupp').css('display','none');
			jQuery(this).removeClass('selected');
		} else {
			jQuery('.baitemlangsupp').css('display','none');
			jQuery('.balangsupp').removeClass('selected');
			$(this).next('.baitemlangsupp').css('display','block');
			jQuery(this).addClass('selected');
		}
	});
	jQuery('.panel-heading.bahides').on('click',function(){
		if (!jQuery(this).hasClass('baselected')) {
			jQuery(this).addClass('baselected');
			jQuery(this).nextAll().css('display','none');
			jQuery(this).find('.icon_hide').removeClass('fa-minus-circle').addClass('fa-plus-circle');
		} else {
			jQuery(this).removeClass('baselected');
			jQuery(this).nextAll().css('display','block');
			jQuery(this).find('.icon_hide').removeClass('fa-plus-circle').addClass('fa-minus-circle');
		}
	});
});

function bagetisocode(isocode,id_lang) {
	jQuery('.bachecktni').css('display','none');
	jQuery('.bachecktnis_'+id_lang).css('display','block');
	jQuery('.balangsupp>.ba-sp-isocode').text(isocode);
	jQuery('.bareviewlang').val(id_lang);
	jQuery('.baitemlangsupp').css('display','none');
	jQuery('.balangsupp').removeClass('selected');
}