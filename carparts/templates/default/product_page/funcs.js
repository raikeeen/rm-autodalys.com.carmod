jQuery(document).ready(function ($) {
    
    // Scroll to element if go to pages
    if($('div').is('.CmCrossTitleBl')){
        var pageNum = $('.CmCrossTitleBl').data('page');
        if(pageNum != ''){
            $('html, body').animate({
                scrollTop: $(".CmCrossTitleBl").offset().top
            }, 500);
        }
    }
    
    
    $('.CmIfrBut').click(function(){
        let eu = $(this).data('eu');
        let rep = /http:/;
        let newUrl = eu.replace(rep, 'https:');
        $('.fxOverlay').css('display','flex');
        $('.fxCont').css({width:'100%', height:'90%'}).append('<div class="fxClose"></div><iframe class="CmIframe" src="'+newUrl+'" style="width:100%; height:100%" frameborder="0" seamless></iframe>');
    });

    //Product Prices block (Webservices AJAX updated)
	var WsAct = parseInt($('.blockProdPrice').data('wsact'));
	if(WsAct){
       $('.blockProdPrice').find('.CmWsLoadBar').show();
        $.ajax({url:window.location.href, type:'POST', dataType:'html', data:{CarModAjaxProductPrices:'Y', SearchWS:'Y'}})
            .done(function(Result){
                //Check for WS Errors for admin
                var aResult = Result.split('|CmWsErrors|');
                if(aResult.length>1){
                    $('.fxCont').html(aResult[0]).css('text-align','left');
                    $('.fxOverlay').css('display','flex');
                    $('.blockProdPrice').html(aResult[1]);
                }else{
                    $('.blockProdPrice').html(Result);//Update Prices block
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                    $('.blockProdPrice').html(jqXHR.responseText+' ['+textStatus+'] '+errorThrown);
                });
    }

    //ASK PRICE POPUP BLOCK
    // $("#CmAjaxBox, .blockProdPrice").on("click", '.ListAskPrice', function (e){
    //     var Brand = $(this).data('brand');
    //     var Article = $(this).data('artnum');
    //     var ModuleDir = $(this).data('moduledir');
    //     var DataLang = $(this).data('lang');
    //     var Link = $(this).data('link');
    //     e.preventDefault();
    //     $('.fxOverlay').css('display','flex');
    //     $('.fxCont').html('<div class="CmLoading" style="background-image:url(/carparts/templates/default/_images/loading.gif)"></div>');
    //     $.post('/'+ModuleDir+'/add/askprice/controller.php', {Brand:Brand, Article:Article, Lang:DataLang, ModDir:ModuleDir, Link:Link}, function(Result){
    //         $('.fxCont').html('<div class="fxClose"></div>'+Result);
    //         $('.CmLoading').css('display','none');
    //     });
    // });
    //

    ////////IMAGE BLOCK
    // CHANGE SMALL IMAGE TO BIG
    $('body').on("click", ".cmChangeImg", function(){
        var smallfoto = $(this).html();
        $('.CmImageToPopup').html(smallfoto);

    });
    //IMAGE TO POPUP\
    $('body').on("click", ".CmImageToPopup", function(){
        let imType = $(this).data('imgtype');
        let prodIm = $(this).html();
        let imgWid = $(this).find('img').data('width');
        let imgHei = $(this).find('img').data('height');
        let windHeight = document.body.clientHeight;
        $('.fxOverlay').css('display','flex');
        $('.fxCont').html('<div class="fxClose"></div>'+prodIm);
        if(imType == 'scheme' && imgHei > windHeight){
            $('.fxCont').height(windHeight-80);
        }
    });
    ///////////

    // Open props block if OE without price
    $('body').on('click', '.CmShowHiddSpecs', function(){
        $('.CmPropsInnerBlock').css({height: 'auto'});
        $(this).hide();
    });

    // AGENCY BLOCKS
    $('.cmAgencyText').click(function (){
        $('.fxOverlay').css('display','flex');
        $('.fxCont').html(function(){
            var AgenTab = $('.hideBlockAdr').html();
            return '<div class="cmBlockAddr">'+AgenTab+'</div>';
        });
    });

    //MORE PROPERTIES
    $(".CmMorePropBut").click(function(){
        $('.CmPropWrap').removeClass('CmPropTabHeight');
        $(this).hide();
    });

    // Admin Tips for Publick side
    $(".cmTitShow").hover(function(){
        var title = $(this).attr('title');
        if(title){
            $(this).data('tipText', title).removeAttr('title');
            $('<p class="Cm_TitBox"></p>').html(title).appendTo('body').show(); //alert('+'+title);
        }else{return false;}
    },function(){
         $(this).attr('title', $(this).data('tipText'));
         $('.Cm_TitBox').remove();
    }).mousemove(function(e){
        var mousex = e.pageX + 16; //Get X coordinates
        var mousey = e.pageY + 7; //Get Y coordinates
        $('.Cm_TitBox').css({ top:mousey, left:mousex });
    });
        if($('.cm_Delivtd').data('suplstock')===''){
           $('.CmListPrDelivery').css('borderRight','unset');
        }

    // Prod info tab active on mobile
    // if($(window).width()<=960){
    //     $('.tabPartUse').removeClass('activeSecTab CmColorBr CmColorBg');
    //     $('.tabPartUse').find('.cmSvgImg').css('fill','#808080');
    // }

});
