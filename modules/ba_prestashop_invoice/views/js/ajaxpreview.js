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
	jQuery('#desc-supp-configuration-preview,#preview_supinvoice').click(function(){
		ajaxPreviewSupplier();
	});
	jQuery('#desc-configuration-preview').click(function(){
		ajaxPreviewInvoice();
	});
	jQuery('#preview_invoice').click(function(){
		ajaxPreviewInvoice();
	});
	jQuery('#desc-delivery-configuration-preview').click(function(){
		ajaxPreviewDelivery();
	});
	jQuery('#preview_delivery').click(function(){
		ajaxPreviewDelivery();
	});
	jQuery('#preview_credit').click(function(){
		ajaxPreviewCredit();
	});
	
	jQuery(document).on('click','.bapreview_close',function(){
		$(".header_style").remove();
		$(".content_style").remove();
	});
	jQuery(document).on('click','.header_style',function(){
		$(".header_style").remove();
		$(".content_style").remove();
	});
	jQuery(document).on('click','.bapreview_a',function(){
		$(".header_style").remove();
		$(".content_style").remove();
	});
	
    var myVar;
    myVar = setInterval(function () {
        var width = $(window).width();
        var width_content_style = $(".content_style").outerWidth();
		// if (width_content_style%10 != 0) {
			width_content_style = Math.round(width_content_style / 10 + 1) * 10;
		// }
        var left = (width - width_content_style) / 2;
        $(".content_style").css("left", left + "px");
        var height = $(window).height();
        var height_content_style = $(".content_style").outerHeight();
        var top = (height - height_content_style) / 2;
        $(".content_style").css("top", top + "px");
    }, 100);
});
function ajaxPreviewSupplier() {
	var header_invoice_template = tinymce.get($('.baheader_invoice_template').attr('id')).getContent();
	jQuery('.baheader_invoice_template.rte').html(header_invoice_template);
	
	var invoice_template = tinymce.get($('.bainvoice_template').attr('id')).getContent();
	jQuery('.bainvoice_template.rte').html(invoice_template);
	
	var footer_invoice_template = tinymce.get($('.bafooter_invoice_template').attr('id')).getContent();
	jQuery('.bafooter_invoice_template.rte').html(footer_invoice_template);
	var templateData = jQuery("#ba_form_template").serialize();
	jQuery.ajax({
		url		: baseUrl+'index.php?controller=ajaxpreviewsupplier&fc=module&module=ba_prestashop_invoice&batoken='+batoken,
		data	: templateData,
		type	: 'POST',
		beforeSend: function() {
			$( "body" ).append("<div class='header_style_load'></div>");
			$( "body" ).append(popupLoading());
			$(".content_style").delay(100).fadeIn(500);
		},
		success: function(result){
			// console.log(baseUrl+"ba_invoice_preview.pdf");
			if (result == '') {
				$(".header_style_load").remove();
				$(".content_style").remove();
				$( "body" ).append("<div class='header_style'></div>");
				$( "body" ).append(popupPreview('ba_supplier_preview.pdf'));
				$(".content_style").delay(100).fadeIn(500);
			}
			// window.open(baseUrl+"ba_invoice_preview.pdf");
		}
	});
}
function ajaxPreviewInvoice() {
	var header_invoice_template = tinymce.get('header_invoice_template').getContent();
	jQuery('#header_invoice_template').html(header_invoice_template);
	
	var invoice_template = tinymce.get('invoice_template').getContent();
	jQuery('#invoice_template').html(invoice_template);
	
	var footer_invoice_template = tinymce.get('footer_invoice_template').getContent();
	jQuery('#footer_invoice_template').html(footer_invoice_template);
	var templateData = jQuery("#form_template").serialize();
	// console.log(id);
	jQuery.ajax({
		url		: baseUrl+'index.php?controller=ajaxpreview&fc=module&module=ba_prestashop_invoice&batoken='+batoken,
		data	: templateData,
		type	: 'POST',
		beforeSend: function() {
			$( "body" ).append("<div class='header_style_load'></div>");
			$( "body" ).append(popupLoading());
			$(".content_style").delay(100).fadeIn(500);
		},
		success: function(result){
			// console.log(baseUrl+"ba_invoice_preview.pdf");
			if (result == '') {
				$(".header_style_load").remove();
				$(".content_style").remove();
				$( "body" ).append("<div class='header_style'></div>");
				$( "body" ).append(popupPreview('ba_invoice_preview.pdf'));
				$(".content_style").delay(100).fadeIn(500);
			}
			// window.open(baseUrl+"ba_invoice_preview.pdf");
		}
	});
}

function ajaxPreviewDelivery() {
	var header_invoice_template = tinymce.get('header_invoice_template').getContent();
	jQuery('#header_invoice_template').html(header_invoice_template);
	
	var invoice_template = tinymce.get('invoice_template').getContent();
	jQuery('#invoice_template').html(invoice_template);
	
	var footer_invoice_template = tinymce.get('footer_invoice_template').getContent();
	jQuery('#footer_invoice_template').html(footer_invoice_template);
	var templateData = jQuery("#form_template").serialize();
	// console.log(id);
	jQuery.ajax({
		url		: baseUrl+'index.php?controller=ajaxpreviewdelivery&fc=module&module=ba_prestashop_invoice&batoken='+batoken,
		data	: templateData,
		type	: 'POST',
		beforeSend: function() {
			$( "body" ).append("<div class='header_style_load'></div>");
			$( "body" ).append(popupLoading());
			$(".content_style").delay(100).fadeIn(500);
		},
		success: function(result){
			// console.log(result);
			if (result == '') {
				$(".header_style_load").remove();
				$(".content_style").remove();
				$( "body" ).append("<div class='header_style'></div>");
				$( "body" ).append(popupPreview('ba_delivery_preview.pdf'));
				$(".content_style").delay(100).fadeIn(500);
			}
			// window.open(baseUrl+"ba_delivery_preview.pdf");
		}
	});
}

function ajaxPreviewCredit() {
	var header_invoice_template = tinymce.get('header_invoice_template').getContent();
	jQuery('#header_invoice_template').html(header_invoice_template);
	
	var invoice_template = tinymce.get('invoice_template').getContent();
	jQuery('#invoice_template').html(invoice_template);
	
	var footer_invoice_template = tinymce.get('footer_invoice_template').getContent();
	jQuery('#footer_invoice_template').html(footer_invoice_template);
	var templateData = jQuery("#form_template").serialize();
	// console.log(id);
	jQuery.ajax({
		url		: baseUrl+'index.php?controller=ajaxpreviewcredit&fc=module&module=ba_prestashop_invoice&batoken='+batoken,
		data	: templateData,
		type	: 'POST',
		beforeSend: function() {
			$( "body" ).append("<div class='header_style_load'></div>");
			$( "body" ).append(popupLoading());
			$(".content_style").delay(100).fadeIn(500);
		},
		success: function(result){
			// console.log(result);
			if (result == '') {
				$(".header_style_load").remove();
				$(".content_style").remove();
				$( "body" ).append("<div class='header_style'></div>");
				$( "body" ).append(popupPreview('ba_credit_preview.pdf'));
				$(".content_style").delay(100).fadeIn(500);
				// window.open(baseUrl+"ba_credit_preview.pdf");
			}
		}
	});
}

function popupPreview(namePDF) {
	var html = '';
	var d = new Date();
	var n = d.getTime();
	html += "<div class='content_style' style='display: none'>";
		html += "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>";
		html += "<span class='bapreview_close'><i class='fa fa-close'></i></span>";
		html += "<div>";
			html += "<span class='bapreview_generated'>"+mess_success+"</span>";
		html += "</div>";
		html += "<div class='bapreview_wrap_button'>";
			html += "<a href='"+baseUrl+namePDF+"?time="+n+"' target='_blank' class='bapreview_button'>"+mess_clickpreview+"</a>";
		html += "</div>";
		html += "<div class='bapreview_wrap_a'>";
			html += "<a class='bapreview_a'>"+mess_nothanks+"</a>";
		html += "</div>";
	html += "</div>";
	return html;
}

function popupLoading() {
	var html = '';
	html += "<div class='content_style' style='display: none; background-color: #fff0;'>";
		html += "<img src='"+baseUrl+"modules/ba_prestashop_invoice/views/img/load.gif' alt='load' width='100px' height='100px'>";
	html += "</div>";
	return html;
}