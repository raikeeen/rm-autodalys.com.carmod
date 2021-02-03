var ShowFilter=0;

function FiltByLett(){
    if(ShowFilter==1){
        var _contentRows = jQuery('.ModBox');
        _count = 0;
        var symbol = [];
        _contentRows.each(function (i) {
            var mname = $(this).data('mname').toString();
            var _cellText = mname.substr(0,1);
            if($.inArray(_cellText,symbol)==-1) {
                symbol.push(_cellText);
            }
            _count += 1;
        });
        var arr = symbol.sort();
        for(var k=0;k<arr.length;k++) {
            jQuery('.fByNameButs').append('<div class="CmColorBgh" href="javascript:void(0)">'+arr[k]+'</div>');
        }
        var _alphabets = jQuery('.fByNameButs > div');
        _alphabets.click(function (){
            var _letter = jQuery(this), _text = jQuery(this).text(), _count = 0;
            var yearF = 0;
            var yearT = 0;
            var arrYear = [];
            var nowDate = new Date();
            _alphabets.removeClass("CmActFB CmColorBg");
            _letter.addClass("CmActFB CmColorBg");
            _contentRows.addClass('hideLitter');
            _contentRows.each(function (i){
                if(_text==AllLng) {
                 _count += 1;
                 jQuery(this).removeClass('hideLitter');
                }else{
                    //var _cellText = jQuery(this).children('.ModelName').eq(0).text();
                    var _cellText = $(this).data('mname');
                    if (RegExp('^' + _text).test(_cellText)) {
                        _count += 1;
                        jQuery(this).removeClass('hideLitter');
                        jQuery(this).addClass('yearShow');

                        if($(this).data('yto')==0){
                            yearT = nowDate.getFullYear();
                        }else{yearT = jQuery(this).data('yto');}
                        yearF = jQuery(this).data('yfrom');
                        var yearStart = yearF;
                        while(yearStart <= yearT){
                           arrYear.push(yearStart++);
                        }
                    }
                }
           });
           var i = arrYear.length, resY = [];
           arrYear.sort(function(a,b) {
                return b-a;
           });
           var tr, ctYear = [];
           while(i--){
                if(resY.join().search(arrYear[i]+'\\b') == '-1') {
                    resY.push(arrYear[i]);
                    tr = arrYear[i].toString();
                    if(jQuery.inArray(tr.substr(0, 3)+'0', ctYear) !== -1){continue;}
                    ctYear.push(tr.substr(0, 3)+'0');
                }
           }
           var iCtY = ctYear.length;
           jQuery('#yearBox table tbody').empty();
           for(var kaw=0;kaw<iCtY;kaw++){
                jQuery('#yearBox table tbody').append('</td><tr><td class="fYGroupe">'+ctYear[kaw]+'-x:</td><td class="fYG'+ctYear[kaw]+'">');
                var ctnS = 0;
                for(var cty=resY[0];cty<=resY[resY.length-1];cty++){
                    if(ctYear[kaw].substr(0, 3) == cty.toString().substr(0, 3)){
                        jQuery('.fYG'+ctYear[kaw]).append('<div class="fYear c_BgHov">'+cty+'</div>');
                    }
                }
           }
        });
    }
}

jQuery(document).ready(FiltByLett);

jQuery(document).ready(function( $ ) {

    $('.ModBox').mouseover(function(){
        $(this).find('.ModName').addClass('CmColorBg');
    });
    $('.ModBox').mouseleave(function(){
        $(this).find('.ModName').removeClass('CmColorBg');
    });

    //Filter Years
    if(ShowFilter==1){
        jQuery('#yearBox table tbody tr td .fYear').on('click', function(){
            jQuery('#yearBox').hide();
            var curYear = jQuery(this).html();
            var _contentRows2 = jQuery('.ModBox');
            jQuery('.fByYearSelected').html(' - '+curYear+' &#9660;');
            _contentRows.each(function (i) {
                var yfrom = jQuery(this).data('yfrom').toString();
                var yto = jQuery(this).data('yto').toString();
                if(curYear < yfrom  || (curYear > yto && yto != 0)){
                    jQuery(this).addClass("hideYear").removeClass("litShow");
                }else{jQuery(this).removeClass("hideYear").addClass("litShow");}
            });
            jQuery('.fByNameButs').empty();
            jQuery('.fByNameButs').append('<a href="javascript:void(0)">All</a>');
            var litShow = jQuery('.litShow');
            _count = 0;
            var symbol = [];
            litShow.each(function (i) {
                //var _cellText = jQuery(this).children('.ModelName').eq(0).text().substr(0,1);
                var mname = $(this).data('mname').toString();
                var _cellText = mname.substr(0,1);
                if($.inArray(_cellText,symbol)==-1) {
                    symbol.push(_cellText);
                }
                _count += 1;
            });
            var arr = symbol.sort();
            for(var k=0;k<arr.length;k++) {
                jQuery('.fByNameButs').append('<a href="javascript:void(0)">'+arr[k]+'</a>');
            }
            var _alphabets = jQuery('.fByNameButs > a');
            _alphabets.click(function (){
                var _letter = jQuery(this), _text = jQuery(this).text(), _count = 0;
                _alphabets.removeClass("active");
                _letter.addClass("active");
                _contentRows.addClass('hideLitter');
                _contentRows.each(function (i){
                 if(_text==AllLng) {
                    // _count += 2;
                    jQuery(this).removeClass('hideLitter');
                 }else{
                    //var _cellText = jQuery(this).children('.ModelName').eq(0).text();
                    var _cellText = $(this).data('mname');
                    if (RegExp('^' + _text).test(_cellText)) {
                        _count += 1;
                        jQuery(this).removeClass('hideLitter');
                    }
                 }
                });
            });
        });
    }

	/* Actual Selection AJAX */
    jQuery('#CmContent').on('click', '.sliderTg', function(){
        var selactual = jQuery(this).attr("selactual").toString();
        if(selactual!=''){
        LoadingToggle();
            $.post(window.location.href, {CarModAjax:'Y', All:selactual}, function(Result){
                jQuery('#CmAjaxBox').html(Result);
                LoadingToggle();
                FiltByLett();
            });
        }
    });

    jQuery('#fByYearSel').on('click', function(){
        jQuery('#yearBox').toggle(0);
    });

    /* Type Selection PopUp */
    var typPopup = $('.boxMod').data('typespopup');
    if(typPopup==1){
        $("#CmContent").on("click",".ModBox", function(e){
            e.preventDefault();
            $('.fxOverlay').css('display', 'flex');
            $('.fxCont').html('<div class="CmSchLoadWrap" style="display:block; top:0; left:0;"><div class="CmSchLoading" style="width:65px; height:50px;"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>');
            $.post($(this).attr('href'), {CarModAjax:'Y',HideStat:'Y'}, function(data){
                jQuery('.fxCont').html('<div class="fxClose"></div>'+data);
                $('.fxCont').css({width:'1080px'});
            });
        });
    }
});
