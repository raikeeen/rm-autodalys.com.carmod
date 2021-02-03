$(document).ready(function () {
    // block of additional info
    /* Admin Tips for Publick side */
    $("body").on("click", ".CmInfoSvgIm", function(e){
        e.preventDefault();
        var blockW = $(this).parent().find('.CmDopInfBlWrap').width();
        var heightTable = $('.CmTypesWrap').innerHeight();
        var dopBlock = $(this).parent().find('.CmDopInfBlWrap').clone();
        if($(window).width() >= 860) {
            dopBlock.appendTo('.CmTypesWrap').css({top: e.clientY - 20, left: e.clientX - blockW, display: 'block'});
        }else {
            dopBlock.appendTo('.CmTypesWrap').css({top: e.clientY - 20, right: '0px', display: 'block'});
        }
        var outHeight = dopBlock.outerHeight();
        var innHeight = dopBlock.innerHeight();
        var pHeight = window.innerHeight;
        var pos = $('.CmTypesWrap').offset();
		var elem_top = pos.top.toFixed(0);
		var y = e.pageY - elem_top;
        var diff = heightTable - y;
        var numLeft = pHeight - e.clientY;
        if (diff < outHeight || numLeft < blockW) {
            dopBlock.css({top: e.clientY - innHeight + 20, left: e.clientX - blockW});
        }
    });
    $("body").on("mouseleave", ".CmDopInfBlWrap", function(){
        $(this).fadeOut(200);
        setTimeout(() => $(this).remove(), 400);
    });
    $("body").on("click", ".CmCloseBlock", function(e){
        e.stopPropagation();
        $(this).parents('.CmDopInfBlWrap').fadeOut(200);
        setTimeout(() => $(this).parents('.CmDopInfBlWrap').remove(), 400);
    });

    //  filter by liter
    var _content_Rows = $(".CmTypeListWrap");
    var _litbut = $(".lit_but");
    _litbut.click(function () {
        var _letter = jQuery(this);
        var _text = jQuery(this).text();
        _litbut.removeClass("CmColorBg col_fff").addClass("CmColorTx");
        _letter.addClass("CmColorBg col_fff").removeClass("CmColorTx");
        _content_Rows.hide();
        _content_Rows.each(function (i) {
            if(_text === All_Lng) {
                jQuery(this).show();
            }else{
               var _cellText = $(this).data('liter');
               if (_text == _cellText) {
                jQuery(this).show();
               }
            }
        });
    });

    // PRODUCT LINK
    $('.CmTypesRow').click(function(){
        var link = $(this).data('furl');
        location.href = link;
    });
});
