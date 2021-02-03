jQuery(document).ready(function ($) {

        // MOBILE VIEW SHOW & HIDE FILTER
    $("#CmAjaxBox").on('click','.CmFilterShowButton',function(){
        var bodyHeig = $('html').height();
        $('html').append('<div class="CmBlockOverPage"></div>');
        $('.CmBlockOverPage').show().height(bodyHeig);
        $('.left_fil').css({right: '315px', transition:"right 0.3s"});
    });
    $(document).mousedown(function(e) {
        e.stopPropagation();
        if($(".left_fil").has(e.target).length===0||$(".CmHideFiltersBlock").has(e.target).length>0){
            $('.left_fil').css({right: '0px', transition:"right 0.3s"});
            $('.CmBlockOverPage').hide();
            // $('body').css({overflow:'auto', marginRight:'0px'});
            $('.CmBlockOverPage').remove();
        }
    });

    // More Block
    $("#CmAjaxBox").on("mouseenter",'.cm_moreImg', function(){
        $('.cm_moreText').addClass('c_Tx');
    });
    $("#CmAjaxBox").on("mouseleave",'.cm_moreImg', function(){
        $('.cm_moreText').removeClass('c_Tx');
    });
    $("#CmAjaxBox").on("mouseenter",'.cm_moreImg', function(){
        $('.cm_imgBlock').addClass('cm_RedoRotate');
    });
    $("#CmAjaxBox").on("mouseleave",'.cm_moreImg', function(){
        $('.cm_imgBlock').removeClass('cm_RedoRotate');
    });

    //More Prices
    $("#CmAjaxBox").on("click",'.cm_ShowMorePr', function(){
        $(this).find('.cm_HidePricetb').css('display','flex');
    });
    $("#CmAjaxBox").on("click",'.cm_ShowMorePr_c', function(){
        $(this).siblings('.cm_HidePrice_c').show();
        $('.cm_HidePrice_c').css('margin', '-25px -2px 0px 0px');
    });
    $("#CmAjaxBox").on("mouseleave",'.cm_HidePricetb, .cm_HidePrice_c', function(){
     $('.cm_HidePricetb, .cm_HidePrice_c').css('display','none');
    });
    $('.CmCloseTable').click(function(e){
        e.stopPropagation();
        $('.cm_HidePricetb, .cm_HidePrice_c').css('display','none');
    });

    // SHOW & HIDE MORE PRODUCT INFO
    $('body').on('click', '.CmShowPrBl', function(){
        $(this).prev('.CmNamePropsBlock').find('.CmListProps_2').slideToggle(300);
        var hide = $(this).data('hide');
        $(this).html(hide).addClass('CmHidePrBl').removeClass('CmShowPrBl');
    });
    $('body').on('click', '.CmHidePrBl', function(){
        $(this).prev('.CmNamePropsBlock').find('.CmListProps_2').slideToggle(300);
        var show = $(this).data('show');
        $(this).html(show).addClass('CmShowPrBl').removeClass('CmHidePrBl');

    });


//    SHOW HIDE PRICES TABLE VIEW
    $("#CmAjaxBox").on("click",'.cm_ShowMorePr_t', function(){
        var prcount = $(this).data('countpr');
        var pkey = $(this).data('pkey');
        var hid = $(this).data('hide');
        $(this).parent('.CmMorePriceTr').siblings('.'+pkey).show(300);
        $(this).parent('.CmMorePriceTr').siblings('.CmAdmButsProduct, .CmProdTabRow').find('td.'+pkey).attr('rowspan', prcount+1);
        $(this).find('.cm_mP').text(hid);
        $(this).addClass('CmHidePrTr');
    });
    $("#CmAjaxBox").on("click",'.CmHidePrTr', function(){
        var pkey = $(this).data('pkey');
        var hid = $(this).data('show');
        $(this).parent('.CmMorePriceTr').siblings('.'+pkey).hide();
        $(this).parent('.CmMorePriceTr').siblings('.CmAdmButsProduct, .CmProdTabRow').find('td.'+pkey).attr('rowspan', '');
        $(this).find('.cm_mP').text(hid);
        $(this).removeClass('CmHidePrTr');
    });

    $("#CmAjaxBox").on("click",'.CmShowMorePrBut', function(){
        var prcount = $(this).data('countpr');
        var pkey = $(this).data('pkey');
        var hid = $(this).data('hide');
        $(this).siblings('.'+pkey).show().css('display','grid');
        $(this).find('.cm_mP').text(hid);
        $(this).addClass('CmHidePrTr');
    });
    $("#CmAjaxBox").on("click",'.CmHidePrTr', function(){
        var pkey = $(this).data('pkey');
        var hid = $(this).data('show');
        $(this).siblings('.'+pkey).hide().css('display','none');
        $(this).find('.cm_mP').text(hid);
        $(this).removeClass('CmHidePrTr');
    });

    // More brands
    $("#CmAjaxBox").on("click",'.CMShowMoreBr', function(){
        $('.l_filterBr').removeClass('CmLeftFilHeight');
        $(this).hide();
    });

    // Remove color border right
    if($('.cm_Delivtd').data('suplstock')==''){
        $('.CmListPrDelivery').css('borderRight','unset');
    }

    // Image to Popup window
    $("#CmAjaxBox").on("click", '.ProductImg', function (e){
        e.preventDefault();
        var ChemaCoords = $(this).html();
        var picType = $(this).find('.CmProdIm').data('pictype');
        $('.fxOverlay').css('display', 'flex');
        jQuery('.fxCont').html('<div class="fxClose"></div>'+ChemaCoords);
        // let windWidth = $('#CmContent').width();
        let windHeight = document.body.clientHeight;
//        console.log(picType+'/'+windHeight);
        if(picType == 'schema'){
            $('.fxCont').height(windHeight-80).css('width', 'auto');
        }
//        let img = $('.fxCont').find('.CmProdIm');
//        var width = '';
//        var height = '';
//        img.load(function(){
//            // удаляем атрибуты width и height
//            $(this).removeAttr("width")
//                   .removeAttr("height")
//                   .css({ width: "", height: "" });
//
//            // получаем заветные цифры
//            width  = $(this).width();
//            height = $(this).height();
//            // console.log(width+'/'+height);
//        });

    });

    $("#CmAjaxBox").on("click", '.CmPopUpImg', function (e){
        $('.fxOverlay').css('display', 'flex');
        jQuery('.fxCont').html('<div class="fxClose"></div><img class="cmImgTablOv" src="'+$(this).data('imgsrc')+'">');
        var img = $('.fxCont').find('.cmImgTablOv');
        var windWidth = $(window).width();
        if(img.height() > 800 && windWidth < 1600){
            $('.fxCont').find('.cmImgTablOv').css('height','580px');
        }
         if(img.height() > 800 && windWidth >= 1600){
            $('.fxCont').find('.cmImgTablOv').css('height','900px');
        }
    });

    $("#CmAjaxBox").on("click", '.ProductInfoOe', function (e){
        e.preventDefault();
        var thisEl = $(this);
        var furl = $(this).data('furl');
        $('.fxOverlay').css('display', 'flex');
        $('.fxCont').html('<div class="CmSchLoadWrap" style="display:flex; top:0; left:0;"><div class="CmSchLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>');
        $.ajax({url:furl, type:'POST', dataType:'html', data:{CarModAjaxProdPrice:'Y', Numbers:'Y', IncludeFuncs:'Yes', ArtNum:$(this).parent().data('artnum'), Brand:$(this).parent().data('brand'), Tab:$(this).data('tab'), HideStat:'Y', OeNumers:'Y'}})
            .done(function(Result){
                jQuery('.fxCont').html('<div class="fxClose"></div>'+Result);
                $('.fxCont .CmNotFoundInfo').css('display', 'flex');
                $('.fxCont .centBlockInfo').css({height: 'auto', opacity: 1});
                $('.fxCont .cmSuitBlock').css({height: 0, opacity: 0});
            });
        });
    $("#CmAjaxBox").on("click", '.ProductInfoSuit', function (e){
        e.preventDefault();
        var thisEl = $(this);
        var furl = $(this).data('furl');
        $('.fxOverlay').css('display', 'flex');
        $('.fxCont').html('<div class="CmSchLoadWrap" style="display:flex; top:0; left:0;"><div class="CmSchLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>');
        $.ajax({url:furl, type:'POST', dataType:'html', data:{CarModAjaxProdPrice:'Y', Vehicle:'Y', IncludeFuncs:'Yes', ArtNum:$(this).parent().data('artnum'), Brand:$(this).parent().data('brand'), Tab:$(this).data('tab'), HideStat:'Y', ProdVehicle:'Y'}})
            .done(function(Result){
                jQuery('.fxCont').html('<div class="fxClose"></div>'+Result);
                $('.fxCont .CmBrandNameBl:first-child').click();
                $('.fxCont .CmNotFoundInfo').css('display', 'flex');
                //tabsEvent(thisEl);
            });
        });

    $("#CmAjaxBox").on("click",'.more_pr', function(){
        $(this).parents('.desc_bl').find('.CmListProps_2').slideDown(200);
        $(this).hide();
    });

    // SECTION NAME
    $("#CmAjaxBox").on('click', '.cm_sectName', function (){
        $('.cm_Namebl').show();
    });
    $("#CmAjaxBox").on('mouseleave', '.cm_Namebl', function () {
        $('.cm_Namebl').hide();
    });

    //LIST VIEW IMG
    if(screen.width <= 1024){
        $('.img_bl').removeClass('img_blHov');
    }

    //FILTER BY BRANDS
    function ChangeTxtCol(){
        $('.CmSearchNoResTxt').hide();
        $('.filt_sect').addClass('CmColorBr CmColorTx').removeClass('CmBorderRed');
    }
    $('#CmAjaxBox').on('keyup', '.filt_sect', function(){
        var bf = 0;
        if($('.filt_sect').val().length > 0){
            $('.clearButt').show();
        }else if($('.filt_sect').val().length == 0){
            $('.clearButt').hide();
            $('.CmBrandFilter').show();
             ChangeTxtCol()
        }
        var val_inp = $('.filt_sect').val();
        $('.CmBrandFilter').each(function(){
            var CmBranName = $(this).find('.CmBranName').text();
            if(RegExp('\^'+val_inp,'i').test(CmBranName)) {
                bf = 1;
            }
        });
        if(bf === 1){
            $('.CmBrandFilter').each(function(){
                var CmBranName = $(this).find('.CmBranName').text();
                if (RegExp('\^'+val_inp,'i').test(CmBranName)) {
                    $(this).show();
                }else{
                    $(this).hide();
                }
            });
             ChangeTxtCol()
        }else{
            $('.CmSearchNoResTxt').show();
            $('.filt_sect').removeClass('CmColorBr CmColorTx').addClass('CmBorderRed');
        }
    });
    $("#CmAjaxBox").on('click', '.clearButt', function(){
        $('.filt_sect').val('');
        $(this).hide();
        $('.CmBrandFilter').show();
         ChangeTxtCol()
    });
    //END FILTER BY BRANDS



    $('body').on('click','.CmBrandHovBut',function(){
        $('.CmBrandHovBut').each(function(){
           $(this).removeClass('CmBrandActBut').find('span').removeClass('CmColorTx');
        });
        $(this).addClass('CmBrandActBut').find('span').addClass('CmColorTx');
    });

    if(window.screen.width <= 480){
        $('.CmBrArtTdCol').attr('colspan', 1);
    }

});
    //PRICE RANGE FUNCTION
    var priceFrom;
    var priceTo;
    function PriceRange(prFr,prTo){
        if(prFr&&prTo){
            var prF = prFr;
            var prT = prTo;
        }else{
            var prF = $('#amount').data('pricefrom');
            var prT = $('#amount').data('priceto');
        }
        // $( "#slider-range" ).slider({
        //     range: true,
        //     min: $('#amount').data('pricefrom'),
        //     max: $('#amount').data('priceto'),
        //     values: [prF, prT],
        //     slide: function( event, ui ) {
        //         priceFrom = ui.values[0];
        //         priceTo = ui.values[1];
        //         $("#amount").val(ui.values[0] + " - " + ui.values[1]);
        //         $('.CmApplyBut, .CmResetBut').css('display','block').animate({
        //             opacity: 1
        //         }, 300);
        //     }
        // });
        // $( "#amount" ).val($( "#slider-range" ).slider( "values", 0 ) +
        //     " - "+$( "#slider-range" ).slider( "values", 1 ) );

    }
    // apply price range
    function getQueryVariable(queryString) { //string to associative array
        var vars = queryString.split("&");
        var arr = new Object();
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            arr[pair[0]] = pair[1];
        }
        return arr;
    }
    function eachArr(arr){ //associative array to array then to string
        var blkstr = [];
        var str;
        $.each(arr,function(idx,val) {
            str = idx + "=" + val;
            blkstr.push(str);
        });
        return blkstr.join('&');
    }

    var locPath;
    $('body').on('click','.CmApplyBut',function(){
        locPath = document.location.search;
        var locHref = document.location.href;
        if(locPath!='') {
            var sp = getQueryVariable(locPath);
            sp['PriceFrom'] = priceFrom;
            sp['PriceTo'] = priceTo;
            document.location.search = eachArr(sp);
        }else {
            locPath = ['?page=1','PriceFrom='+priceFrom, 'PriceTo='+priceTo];
            document.location.search = locPath.join('&');
        }
    });
    //reset filter button
    $('body').on('click', '.CmResetBut', function(){
        locPath = document.location.search;
        var aSearch = getQueryVariable(locPath);
        delete aSearch.PriceFrom;
        delete aSearch.PriceTo;
        document.location.search = eachArr(aSearch);
    });
    $(document).ready(function() {
        //PRICE RANGE
        locPath = document.location.search;
        var arrSear = getQueryVariable(locPath);
        var saveFr = Number(arrSear['PriceFrom']);
        var saveTo = Number(arrSear['PriceTo']);
        if(arrSear['PriceFrom']||arrSear['PriceTo']){
            $('.CmApplyBut, .CmResetBut').css({display:'block', opacity:1});
        }
        PriceRange(saveFr, saveTo);
    });
