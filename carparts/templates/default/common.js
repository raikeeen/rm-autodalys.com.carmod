function calculateAspectRatioFit(srcWidth, srcHeight, maxWidth, maxHeight) {
    var ratio = [maxWidth / srcWidth, maxHeight / srcHeight];
    ratio = Math.min(ratio[0], ratio[1]);
    var width = srcWidth*ratio;
    var height = srcHeight*ratio;
    $(".CmSchPicture").css({width: width, height: height});
}

var scrollFix = function(e){
    if(e.keyCode == 38 || e.keyCode == 40 || e.type == 'mousewheel'){
        return false;
    }
    $(this).scrollTop(position);

};
jQuery(document).ready(function( $ ) {


     //Product Prices block (Webservices AJAX updated)
    var ProdListBlocks;
    var WsPpNum;
    function WebServiceListBlocks(){
        ProdListBlocks = [];
        WsPpNum=0;
        $('body .rightBlock, body .CmListPrTab_c').each(function(){
            if(typeof $(this).data('artnum')!== 'undefined' && typeof $(this).data('brand')!== 'undefined'){
                if($(this).data('artnum')!='' && $(this).data('brand')!=''){
                    ProdListBlocks.push(this);
                }
            }
        });
        return ProdListBlocks;
    }
    WebServiceListBlocks();

    if(ProdListBlocks.length>0){
        WsNextProdPrices();
    }
    
    function WsNextProdPrices(){
        var ePrlb = ProdListBlocks[WsPpNum];
        if(ePrlb){
            $(ePrlb).find('.CmWsLoadBar').show();
            var Dir = $(ePrlb).data('dir');
            var ArtNum = $(ePrlb).data('artnum');
            var Brand = $(ePrlb).data('brand');
            // console.log(Dir+'/ '+ArtNum+'/ '+Brand);
//            var pData = 'CarModAjaxProductPrices=Y&SearchWS=Y&ArtNum='+ArtNum+'&Brand='+Brand+'&Sets=List';
//            ReqFetch('/'+Dir+'/', pData)
//                .then(result => {
//                    $(ePrlb).html(result);
//                    WsPpNum++;
//                    WsNextProdPrices();
//                });
            $.ajax({url:'/'+Dir+'/', type:'POST', dataType:'html', data:{CarModAjaxProductPrices:'Y', SearchWS:'Y', ArtNum:ArtNum, Brand:Brand, Sets:'List'}})
               .done(function(Result){
                    //Check for WS Errors for admin
                    var aResult = Result.split('|CmWsErrors|');
                    if(aResult.length>1){
                        $('.fxCont').html(aResult[1]).css('text-align','left');
                        $('.fxOverlay').css('display', 'flex');
                    }else{
                        //Update Prices block
//                        console.log(Result);
                        $(ePrlb).html(Result);
                        WsPpNum++;
                        WsNextProdPrices(); //Next search (if block exists)
                    }
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $(ePrlb).html(jqXHR.responseText+' ['+textStatus+'] '+errorThrown);
                    WsPpNum++;
                    WsNextProdPrices();
                });
        }
    }

    
        // FILTERS
    $("#CmAjaxBox").on("click",'.CmFilterCheck', function () {
        $(this).find('.check_b').toggleClass('check_back');
        LoadingToggle('CmContent', $('#CmAjaxBox').offset().top-20);
        var oData = {};
        oData['CarModAjax']='Y';
        var prid = $(this).data('prid');
        var crcod = $(this).data('crcod');
        var bcode = $(this).data('bcode');
        if(prid){
            oData['ByProductID']=prid;
        }else if(crcod){
            oData['ByCriteriaCode']=crcod;
        }else if(bcode){
            oData['ByBrandCode']=bcode;
        }
        $.post(window.location.href, oData, function(Result){
            $("#CmAjaxBox").html(Result);
            if($(window).width() <= 992) {
                $('.left_fil').css('right', '315px');
            }
            LoadingToggle();
            WebServiceListBlocks();
            WsNextProdPrices();
        });
    });


    //OE, Analog switch
    $('#CmAjaxBox').on('click', '.CmTumButn', function(e){
        e.preventDefault();
        $('.CmTumButn').each(function(){
             $(this).removeClass('CmTumPushed');
        });
        $(this).addClass('CmTumPushed');
        var titPosHrf = $(this).attr('href');
        LoadingToggle('CmContent', $('#CmAjaxBox').offset().top-20);
        $.ajax({url:titPosHrf, type:'POST', dataType:'html', data:{CarModAjax:'Y'}})
            .done(function(Result){
                $("#CmAjaxBox").html(Result);
                if($(window).width() <= 992) {
                    $('.left_fil').css('right', '315px');
                }
                LoadingToggle();
                WebServiceListBlocks();
                WsNextProdPrices();
            });
    });

    // Select Products SubSection
    $("#CmAjaxBox").on("click",".PickSection", function(e){
        LoadingToggle('CmContent',1);
        window.history.pushState('object or string', 'Title', $(this).attr('href'));
        e.preventDefault();
        var Code = $(this).data('code');
        var pickHeight = $(this).height();
        $(this).parents('.cm_FsBlock').find('.FilterSection').css('border-top-width') == pickHeight / 2;
        $(this).parents('.cm_FsBlock').find('.FilterSection').css('border-bottom-width') == pickHeight / 2;
        $.post(window.location.href, {CarModAjax:'Y', PickSection:Code}, function(Result){
            $("#CmAjaxBox").html(Result);
            if($(window).width() <= 992) {
                $('.left_fil').css('right', '315px');
            }
            LoadingToggle('CmContent',1);
            WebServiceListBlocks();
            WsNextProdPrices();
        });
    });
    $("#CmAjaxBox").on("click",".PickSection span", function(e){
        e.stopPropagation();
            e.preventDefault();
        $(this).toggleClass("DownActive");
        $(this).parent().parent().next().slideToggle(400);
    });

    // SORT BY PRODUCT_LIST
    $("#CmAjaxBox ").on("click", ".sort_bl", function(e){
        e.stopPropagation();
        $('.hide_bl').toggleClass('hiBlbor');
        $('.show_bl').toggleClass('shBlbor');
        $('.hide_bl').toggle();
    });
    $("#CmAjaxBox ").on("click", ".sort_list", function(e){
        e.stopPropagation();
        $('.hide_bl').toggleClass('hiBlbor');
        $('.show_bl').toggleClass('shBlbor');
        $('.hide_bl').hide();
        LoadingToggle('CmContent', $('#CmAjaxBox').offset().top-20);
        $.post(window.location.href, {CarModAjax:'Y', SortBy:$(this).data('sort') }, function(Result){
            $("#CmAjaxBox").html(Result);
            LoadingToggle('CmContent');
            WebServiceListBlocks();
            WsNextProdPrices();
        });
    });
    $("#CmAjaxBox").on("mouseleave", ".hide_bl", function (){
        $(this).hide();
    });
    jQuery('.CmSortBlockClose').on('click',function (e){
        $(".hide_bl").hide();
    });


    // VIEW SWITCH PRODUCT_LIST
    $("#CmAjaxBox").on("click",".cm_viewAct", function(e){
        var uri = $(this).data('urix');
        LoadingToggle('CmContent', $('#CmAjaxBox').offset().top-20);
        var view = $(this).data('view');
        if(view && view!=''){
            $.post(uri, {CarModAjax:'Y', ActivateTab:view}, function(Result){
                $("#CmAjaxBox").html(Result);
                LoadingToggle();
                WebServiceListBlocks();
                WsNextProdPrices();
            });
        }
    });

    // SELECT SETUP SIDE
    // front, rear
    $('#CmAjaxBox').on('click', '.CmSelectCarSide', function(e){
        e.preventDefault();
        $('.CmFrRr').each(function(){
            $(this).find('.CmCarSide').css('fill','#909090');
            $(this).find('.CmCarSideTxt').css('color','#909090');
            $(this).removeClass('CmSelSideTogg');
            $(this).addClass('CmSelectCarSide');
        });
        $(this).find('.CmCarSide').css('fill','#f93a3a');
        $(this).find('.CmCarSideTxt').css('color','#f93a3a');
        $(this).removeClass('CmSelectCarSide');
        $(this).addClass('CmSelSideTogg');
        var titPosHrf = $(this).attr('href');
        LoadingToggle('CmContent', $('#CmAjaxBox').offset().top-20);
        $.ajax({url:titPosHrf, type:'POST', dataType:'html', data:{CarModAjax:'Y'}})
            .done(function(Result){
                $("#CmAjaxBox").html(Result);
                if($(window).width() <= 992) {
                    $('.left_fil').css('right', '315px');
                }
                LoadingToggle();
                WebServiceListBlocks();
                WsNextProdPrices();
            });
    });
    //left, right
    $('#CmAjaxBox').on('click', '.CmSelectBVSide', function(e){
        e.preventDefault();
        $('.CmLfRt').each(function(){
            $(this).find('.CmBackView').css('fill','#909090');
            $(this).find('.CmBVTxt').css('color','#909090');
            $(this).removeClass('CmSelBVTogg');
            $(this).addClass('CmSelectBVSide');
        });
        $(this).find('.CmBackView').css('fill','#f93a3a');
        $(this).find('.CmBVTxt').css('color','#f93a3a');
        $(this).removeClass('CmSelectBVSide');
        $(this).addClass('CmSelBVTogg');
        var titPosHrf = $(this).attr('href');
        LoadingToggle('CmContent', $('#CmAjaxBox').offset().top-20);
        $.ajax({url:titPosHrf, type:'POST', dataType:'html', data:{CarModAjax:'Y'}})
            .done(function(Result){
                $("#CmAjaxBox").html(Result);
                LoadingToggle();
                WebServiceListBlocks();
                WsNextProdPrices();
            });
    });


    /* Admin Tips for Publick side */
    $("#CmContent").on("mouseover", ".CmTitShow", function(){
        var title = $(this).attr('title');
        if(title){
            $(this).data('tipText', title).removeAttr('title');
            $('<p class="CmTipBox"></p>').html(title).appendTo('body').show(); //alert('+'+title);
        }else{return false;}
    });
    $("#CmContent").on("mouseleave", ".CmTitShow", function(){
        $(this).attr('title', $(this).data('tipText'));
        $('.CmTipBox').remove();
    });
    $("#CmContent").on("mousemove", ".CmTitShow", function(e){
        var mousex = e.pageX + 16; //Get X coordinates
        var mousey = e.pageY + 7; //Get Y coordinates
        $('.CmTipBox').css({ top:mousey, left:mousex });
    });


    /* ====== SCHEMES ==================================================== */
    /* show more toggle */
    $("#CmContent").on("click", ".CmSchemaShowAll", function(e){
       $(".CmSchemaBox").css('max-height','100%');
       $(this).hide();
    });
    $("body").on("click", '.CmSchema', function (e){
        var SchPicID = $(this).data('picid'),
        Lng = $(this).data('lng');
        $(this).find('.CmSchLoadWrap').show().css('display','flex');
        $.ajax({url:window.location.href, type:'POST', dataType:'html', data:{SchPicID:SchPicID, Lng:Lng}})
        .done(function(Result){
            $('.fxOverlay').css('display', 'flex');
            $('.CmLoadWrap').css('display', 'flex');
            $('.fxCont').height(window.innerHeight-100);
            $('.fxCont').width(1180);
            $('.fxCont').html('<div class="fxClose"></div>'+Result);
            $('.CmLoadWrap').css('display', 'flex');
            var maxWidth = $('.CmSchemeBlockWrap').width(),
            maxHeight = $('.CmSchemeBlockWrap').height(),
            srcHeight = $('.CmSchemeGridWrap').data('height'),
            srcWidth = $('.CmSchemeGridWrap').data('width');
            calculateAspectRatioFit(srcWidth, srcHeight, maxWidth, maxHeight);
            $('.CmLoadWrap').hide();
            $('.CmSchLoadWrap').hide();
        });
    });
     //MORE PRICES
    $("#CmAjaxBox, .blockProdPrice").on('click', '.CmMorePrices', function(){
        $(this).find('.morePricestab').slideDown(200);
    });

    //CLOSE MORE PRICE
    $('#CmAjaxBox, .blockProdPrice').on('click','.CmMorePriceBlClose', function(e){
        e.stopPropagation();
        $('.morePricestab').slideUp(200);
        $('.CmMoreHidePr').show();
    });
    $(document).mousedown(function (e){ // событие клика по веб-документу
        var div = $(".morePricestab"); // тут указываем ID элемента
        if (!div.is(e.target) && div.has(e.target).length === 0) {
            $('.morePricestab').slideUp(200);
        }
    });

    //SHOW MORE PRICE IN HIDE BLOCK
    $("#CmAjaxBox, .blockProdPrice").on('click', '.CmMoreHidePr', function(){
        $('.CmWrapBlMorePrice ').removeClass('CmWrapBlHeight');
        $(this).hide();
    });

   // PRICE QUANTITY
    $("#CmAjaxBox, .blockProdPrice").on("click", ".cm_countButM", function () {
        min_quant = $(this).siblings('.cm_countRes').data('minimalqnt');
        var input = $(this).parent().find('.cm_countRes');
        if(min_quant){
            var count= parseInt(input.val()) - min_quant;
            count = count <= 0 ? min_quant : count;
        }else{
            var count= parseInt(input.val()) - 1;
        }
        count = count <= 0 ? 1 : count;
        input.val(count);
        input.change();
        return false;
    });
    $("#CmAjaxBox, .blockProdPrice").on("click", ".cm_countButP", function () {
        var min_quant = $(this).siblings('.cm_countRes').data('minimalqnt');
        var maxaval = $(this).parent().find('.cm_countRes').data('maxaval');
        var input = $(this).parent().find('.cm_countRes');
        if(min_quant){
            var count= parseInt(input.val()) + min_quant;
        }else{
            var count= parseInt(input.val()) + 1;
        }
        count = count > maxaval ? maxaval : count;
        input.val(count);
        input.change();
        return false;
    });
    $("#CmAjaxBox, .blockProdPrice").on("keyup", '.cm_countRes', function () {
        checkSymb(this);
    });
    function checkSymb(input){
        var value = input.value;
        var maxav = $(input).data('maxaval');
        var rep = /[-\.;":'a-zA-Zа-яА-Я]/;
        if (rep.test(value)){
            value = value.replace(rep, '');
            input.value = value;
        }
        if(value>maxav){
            value = value.replace(value, maxav);
            input.value = value;
        }
        if(value==0){
            value = 1;
            input.value = value;
        }
        return value;
    }

});



// Modal window close
    var doc = $(document);
    $(document).mousedown(function (e){ // событие клика по веб-документу
        var div = $(".fxCont"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
        && div.has(e.target).length === 0) { // и не по его дочерним элементам
            $('.fxOverlay').hide(); // скрываем его
            $('.fxCont').html('').css({width:'unset', height:'unset'});
            doc.unbind('scroll keydown mousewheel', scrollFix);
        }
    });
    $('body').on('click', '.fxClose', function(){
        $('.fxOverlay').hide();
        $('.fxCont').html('');//.css({width:'unset', height:'unset'});
        doc.unbind('scroll keydown mousewheel', scrollFix);
    });

// LOADING overlay
function LoadingToggle(IdContentBox, ScrollPx){
    IdContentBox=IdContentBox||'CmContent';
    ScrollPx=ScrollPx||0;
    if($("#Loading").css('display') !== 'none'){
        $("#Loading").hide();
    }else{
        var Cont = $("#"+IdContentBox);
        var CTop = Cont.position().top;
        if(ScrollPx==-1){ScrollPx=CTop;}
        var CLeft = Cont.position().left; //alert('Top:'+CTop+'; Left:'+CLeft); // .offset()
        if(ScrollPx>0){
            $('html, body').animate({ scrollTop:ScrollPx }, 500);
        }
        var CWidth = Cont.outerWidth();
        if(!$.isNumeric(CWidth)){CWidth = Cont.width() + (parseInt(Cont.css('padding-left')) + parseInt(Cont.css('padding-right')) );}
        var CHeight = Cont.outerHeight();
        if(!$.isNumeric(CHeight)){CHeight = Cont.height() + (parseInt(Cont.css('padding-top')) + parseInt(Cont.css('padding-bottom')) );}
        if(IdContentBox!='CmContent'){ //Для элементов внитри контента CarMod
            var PadT = parseInt($("#CmContent").css('padding-top'));
            var PadR = parseInt($("#CmContent").css('padding-right'));
            var PadB = parseInt($("#CmContent").css('padding-bottom'));
            var PadL = parseInt( $("#CmContent").css('padding-left'));
            $("#Loading").css({top:CTop-PadT, left:CLeft-PadL});
            $("#Loading").width(CWidth+PadL+PadR).height(CHeight+PadT+PadB).show();
        }else{ // Для #CmContent

            $("#Loading").width(CWidth).height(CHeight).show();
        }
    }
}

// SHOW & HIDE MORE NOT HIDE PRICE
$('body').on('click', '.CmShowMorePrice', function(){
    $(this).prev('.CmTablePriceWrap').find('.CmTablePriceValueRow_2').show();
    var hide = $(this).data('hide');
    $(this).html(hide).addClass('CmHideMorePrice').removeClass('CmShowMorePrice');
});
$('body').on('click', '.CmHideMorePrice', function(){
    $(this).prev('.CmTablePriceWrap').find('.CmTablePriceValueRow_2').hide();
    var show = $(this).data('show');
    $(this).html(show).addClass('CmShowMorePrice').removeClass('CmHideMorePrice');

});


// SHOW MORE OE NUMBERS
 $('body').on('click', '.CmShowHidOeNum', function(){
    $('.CmHiddenOeNum').each(function(){
        $(this).hide().parents('.CmOeNumsTd').find('.CmHideOeNum').hide();
        $(this).parents('.CmOeNumsTd').find('.CmShowHidOeNum').show();
    });
    $(this).hide();
    $(this).siblings('.CmOeNumWrap').find('.CmHiddenOeNum').show();
    $(this).siblings('.CmHideOeNum').show().css('align-self','flex-end');
    if(($(this).parent().siblings('.CmOeBrName').data('check')=='Y' && $(this).siblings('.CmOeNumWrap').find('.CmHiddenOeNum').length>2) || $(this).siblings('.CmOeNumWrap').find('.CmHiddenOeNum').length>6){
        $('.CmOeBlockInside').removeClass('CmOeNumHeightToHide');
        $('.CmHideOe').show();
        $('.CmShowOe').hide();
    }
});
$('body').on('click', '.CmHideOeNum', function(){
    $(this).siblings('.CmOeNumWrap').find('.CmHiddenOeNum').hide();
    $(this).hide();
    $(this).siblings('.CmShowHidOeNum').show();
});

//REDIRECT FROM PRODUCT_LIST
$('body').on('click','.CmLookAnalogHook',function(){
    $('.tabOeNum').addClass('activeSecTab CmColorBr CmColorBg');
    $('.tabPartUse').removeClass('activeSecTab CmColorBr CmColorBg');
    $('.centBlockInfo').addClass('CmAddClassFlex');
    $('.cmSuitBlock').hide();
    $('.tabOeNum').find('.cmSvgImg').css('fill','#ffffff');
    $('.tabPartUse').find('.cmSvgImg').css('fill','#808080');
});

// TABS ON PRODUCT PAGE
$(document).ready(function( $ ) {
    $('.activeSecTab').find('.cmSvgImg').css('fill','#ffffff');
    $('body').on('click','.tabSelBut',function(){
        $('.tabSelBut').removeClass('activeSecTab c_boxShad CmColorBr CmColorBg');
        $('.tabSelBut').find('.cmSvgImg').css('fill','#808080');
        $(this).addClass('activeSecTab CmColorBr CmColorBg');
        $(this).find('.cmSvgImg').css('fill','#ffffff');
        if($(this).data('change')==='OeNum'){
           $('.centBlockInfo').css({height: 'auto', opacity: 1});
//            $('.centBlockInfo').addClass('CmAddClassFlex');
            $('.cmSuitBlock').css({height: 0, opacity: 0});
//            $('.cmSuitBlock').fadeOut(500);
        }
        if($(this).data('change')==='Suite'){
            $('.centBlockInfo').css({height: 0, opacity: 0});
            $('.cmSuitBlock').css({height: 'auto', opacity: 1});
//            $('.cmSuitBlock').fadeIn(500);
        }
        if($(this).data('change')==='ProdInfo'){
            $('.centBlockInfo').css({height: 0, opacity: 0});
            $('.cmSuitBlock').css({height: 0, opacity: 0});
//            $('.cmSuitBlock').fadeOut(500);
        }
    });


    //APPLIED TO MODEL
    //More brand models
    $('body').on('click', '.CmBrandNameBl', function(){
        $('.CmSelectModelTxt').show();
        var bn = $(this).data('brandname');
        $('.CmBrandNameBl').each(function(){
            $(this).removeClass('CmColorBr CmColorBgL CmBordForAct CmColorOu');
        });
        $(this).addClass('CmColorBr CmColorBgL CmBordForAct CmColorOu');
        $('.CmModelList').hide();
        $('.CmTypesList').hide();
        $('.CmModelList').each(function(){
            var mc = $(this).data('modname');
            if(bn == mc){
                $(this).show();
            }
        });
        $('.CmSelectModTitl').show();
    });
    $('.CmBrandNameBl:first-child').click();

    //More model modify
    $('body').on('click','.CmModelList',function(){
        $('.CmSelectModelTxt').hide();
        $('.CmModifListOverf').css({justifyContent:'flex-start'});
        var modCode = $(this).data('modcode');
        var modDir = $(this).data('moduledir');
        var purl = $(this).data('pageurl');
        $('.CmModelList').each(function(){
            $(this).removeClass('CmColorBgL');
        });
        $(this).addClass('CmColorBgL');
        $('.CmTypesList, .CmSelectModTitl').hide();
        $('.CmModifListBlock').append('<div class="CmSmLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div>');
        $.ajax({url:purl, type:'POST', dataType:'html', data:{GetVhApp:modCode}})
            .done(function(Res){
            var aJRes = JSON.parse(Res);
            $.each(aJRes, function(key, val) {
                $('.CmModifListBlock').append('<div class="CmTypesList CmColorBgLh">'+val+'</div>').css('width', '100%');
            });
            $('.CmSmLoading').css('display','none');
        });
    });


    //MORE ANALOGS
    $('body').on('click','.CmShowA',function(){
       $('.CmAnalogBlockInside').removeClass('CmBlockHeightToHIde');
       $('.CmHideA').show();
       $('.CmHideTextBlock').hide();
       $(this).hide();
    });
    $('body').on('click','.CmHideA',function(){
        $('.CmAnalogBlockInside').addClass('CmBlockHeightToHIde');
        $('.CmShowA, .CmHideTextBlock').show();
        $(this).hide();

    });

    //MORE VEHICLES
    $('body').on('click','.CmShowV',function(){
       $('.CmVehicBlockWrap').removeClass('CmVehicleHeightBl');
       $('.CmHideV').show();
       $('.CmHideTextVehicBlock').hide();
       $(this).hide();
    });
    $('body').on('click','.CmHideV',function(){
        $('.CmVehicBlockWrap').addClass('CmVehicleHeightBl');
        $('.CmShowV, .CmHideTextVehicBlock').show();
        $(this).hide();
        $(window).scrollTop(300);
    });

    //MORE OE NUMBERS
    $('body').on('click','.CmShowOe',function(){
       $('.CmOeBlockInside').removeClass('CmOeNumHeightToHide');
       $('.CmHideOe').show();
       $(this).hide();
    });
    $('body').on('click','.CmHideOe',function(){
        $('.CmOeBlockInside').addClass('CmOeNumHeightToHide');
        $('.CmShowOe, .CmShowHidOeNum').show();
        $('.CmHiddenOeNum, .CmHideOeNum').hide();
        $(this).hide();
        $(window).scrollTop(500);
    });
    
    //Hover on price block
    $('body').on('mouseenter', '.CmPriceProd', function(){
        var hintTxtA = $(this).data('txta');
        var hintTxtD = $(this).data('txtd');
        var hintBlockA = '<div class="CmShowHintBl CmAvalHintBl">'+hintTxtA+'</div>';
        var hintBlockD = '<div class="CmShowHintBl CmDelivHintBl">'+hintTxtD+'</div>';
        $(this).find('.avalTd').append(hintBlockA);
        $(this).find('.delivTd').append(hintBlockD);
        setTimeout(() => 
        $('.CmShowHintBl').addClass('CmShowHintPopup'),
        $('.CmAvalImgTextPage').css('border-radius', '0px 0px 0px 3px'),
        $('.delivTd ').css('border-radius', '0px 0px 3px 0px'));
    });
    $('body').on('mouseleave', '.CmPriceProd', function(){
        setTimeout(() => $(this).find('.CmShowHintBl').removeClass('CmShowHintPopup'));
        setTimeout(() => $(this).find('.CmShowHintBl').remove(), 200);
        $('.CmAvalImgTextPage').css('border-radius', '3px 0px 0px 3px');
        $('.delivTd ').css('border-radius', '0px 3px 3px 0px');
    });
    
});

// Fetch request
async function ReqFetch(url, data) {
    try{
        const response = await fetch(url, {
            method: 'POST',
            body: data,
            headers: new Headers({
                'Content-Type': 'application/x-www-form-urlencoded'
            })
        });
        const resData = await response.text();
        return resData;
    } catch(error){
        console.log(error);
    }
    // return fetch(url, {
        // method: 'POST',
        // body: data,
        // headers: new Headers({
        //     'Content-Type': 'application/x-www-form-urlencoded'
        // })
    // })
    // .then(response => response.text()) // возвращаем промис
}

//ASK PRICE AND MAIL ORDER POPUP BLOCK
function ToCartMailOrder(elem, addFolder, e){
    var Brand = $(elem).data('brand'),
    Article = $(elem).data('artnum'),
    ModuleDir = $(elem).data('moduledir'),
    DataLang = $(elem).data('lang'),
    Link = $(elem).data('link');
    pData = 'Brand='+Brand+'&Article='+Article+'&Lang='+DataLang+'&ModDir='+ModuleDir+'&Link='+Link;
    e.preventDefault();
    $('.fxOverlay').css('display', 'flex');
    $('.fxCont').html('<div id="tempSaver"></div><div class="CmSchLoadWrap" style="display:flex; margin:auto;"><div class="CmSchLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>');

    ReqFetch('/'+ModuleDir+'/add/'+addFolder+'/controller.php', pData)
        .then(result => $('.fxCont').html('<div class="fxClose"></div>'+result));

    // DON'T DELETE

    // $.post('/'+ModuleDir+'/add/'+addFolder+'/controller.php', {Brand:Brand, Article:Article, Lang:DataLang, ModDir:ModuleDir, Link:Link}, function(Result){
    //     $('.fxCont').find('#tempSaver').html(Result);
    //     setTimeout(() => {
    //         $('.fxCont').html('<div class="fxClose"></div>'+Result);
    //     }, 300);
    //     $('.fxCont').find('#tempSaver').html('');
    // });
}
$(document).ready(function($) {
    $("#CmAjaxBox, .CmPriceProd, .blockProdPrice").on("click", '.ListAskPrice', function (e){
        var elem = $(this);
        ToCartMailOrder(elem, 'askprice', e);
    });
    $("#CmAjaxBox, .CmPriceProd").on("click", '.CmMailOrder', function (e){
        var elem = $(this);
        ToCartMailOrder(elem, 'mail_order', e);
    });
});
