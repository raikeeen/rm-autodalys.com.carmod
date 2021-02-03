jQuery(document).ready(function ($) {

	//Выпадающий список подразделов
	jQuery('.showAllSect').on('click', function () {
        $(this).hide();
        var showLNext = jQuery(this).attr("showLNext");
        var widthBoxSect = jQuery('.boxSect_x').width();
        showLNext.toString();
        jQuery('.CmListNSectBl').css({ width: '100%' });
        jQuery(this).closest('.boxSect_x').css({ boxShadow: '0px 0px 10px 1px #d0d0d0' });

        jQuery('#' + showLNext).slideDown(400);
        jQuery(this).parent().parent().parent().parent('.boxSect_x').mouseleave(function () {
            jQuery(this).find('.CmListNSectBl').slideUp(400);
            jQuery(this).closest('.boxSect_x').css({ boxShadow: 'none' });
            $(this).find('.showAllSect').delay(500).fadeIn(200);
        });
	});

	//Прятать подразделы
	jQuery('.hideAllSect').on('click', function () {
		jQuery(this).parent().slideUp(400);
		jQuery(this).closest('.boxSect_x').css({ boxShadow: 'none' });
	});

	//Выпадающий список разделов
	jQuery('.butAllSec').on('click', function () {
		jQuery('.boxSect_x').show();
		jQuery('.butAllSec').remove();
	});

    //MORE SPECS POPUP
    jQuery('.more_specs').on('click', function () {
        jQuery('.CmOverlaySpecs').show().css({display:'flex', justifyContent:'center'});
        $('.CmSpecs').show();
        var cmspec = $('.CmSpecsBl').detach();
        cmspec.appendTo('.CmContSpecs');
        $('.CmContSpecs').append('<div class="CmCloseButSpecs"></div>');
    });

    //CLOSE MORE SPECS POPUP
    jQuery('#CmContent').on('click', ".CmCloseButSpecs", function (e) {
        e.stopPropagation();
        $(".CmOverlaySpecs").hide();
        return false;
    });
    jQuery(document).mousedown(function (e){
        if($(".CmModalSpecs").has(e.target).length === 0){
            $(".CmOverlaySpecs").hide();
        }
    });

    //OPEN EACh SPECS IN POPUP
    jQuery('#CmContent').on('click', '.sp_name', function () {
        jQuery('.spec_name').each(function () {
            jQuery(this).find('.sp_name').removeClass('CmColorBg SpNameCol');
            jQuery(this).find('span').removeClass('CmColorBgh CmColorBg SpNameSpanCol');
        });
        $('.CmSubSpec').hide();
        jQuery(this).parents('.spec_name').find('.CmSubSpec').show();
        jQuery(this).addClass('CmColorBg SpNameCol');
        jQuery(this).find('span').addClass('SpNameSpanCol');
    });

    //IMAGE IN SPECS POPUP
    jQuery('#CmContent').on('click', '.cm_Spimg', function (e){
        e.stopPropagation();
        $('.CmOverlayImg').show().css({display:'flex', justifyContent:'center'});
        var img = $(this).data('img');
        $('.CmContImg').html('<div class="CmCloseButImg"></div><img src="'+img+'"/>');
    });

    //CLOSE IMAGE IN SPECS POPUP
    jQuery('#CmContent').on('click', ".CmCloseButImg", function (e) {
        e.stopPropagation();
        $(".CmOverlayImg").hide();
        return false;
    });
    jQuery(document).mousedown(function (e){
        e.stopPropagation();
        if($(".CmModalImg").has(e.target).length === 0){
            $(".CmOverlayImg").hide();
        }
    });

    // Another modification
    $("#CmContent").on("click", ".an_mod", function (e) {
        e.preventDefault();
        $('.fxOverlay').css('display', 'flex');
        $('.fxCont').html('<div class="CmSchLoadWrap" style="display:block; top:0; left:0;"><div class="CmSchLoading" style="width:65px; height:50px;"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>');
        $.post($(this).data('href'), { CarModAjax: 'Y' }, function (data) {
            jQuery('.fxCont').html('<div class="fxClose"></div>'+data);
            $('.fxCont').css('width', '1080px');
        });
    });

    var SpecHeight = $('.hid_text').data('height');
    if(SpecHeight!='0'){
        $('.hid_text').show();
        $('.CmSpecsBlockWrap').css({height:SpecHeight+'px'});
    }

	// Фильтр по разделам
    $('.CmInputSect').keyup(function(){
        jQuery('.boxSect_x').mouseleave(function(e){
            e.preventDefault();
        });
        $('.CmListSectBl').find('li').addClass('f_list');
        $('.boxSect_x').find('.nameSect_x').addClass('f_title');
        $('.CmListNSectBl').find('li').addClass('f_Hlist');
        if($('.CmInputSect').val().length == 0){
            $('.CmClearButt').hide();
       }
        if($('.CmInputSect').val().length > 0){
             $('.CmClearButt').show();
        }
        if($('.CmInputSect').val().length >= 3){
            var val_inp = $('.CmInputSect').val();
            var sw_x = 0;
			var regexTitle = new RegExp(val_inp,'i');
            var regSubTitle = new RegExp('\\s' + val_inp,'i');
			$('.f_title').each(function(){
                var f_title = $(this).text();
                if (regexTitle.test(f_title)) {
                    sw_x = 1;
                }
            });
            $('.f_list').each(function(){
                var f_list = $(this).text();
                if (regexTitle.test(f_list)) {
                    sw_x = 1;
                }
            });
            $('.f_Hlist').each(function(){
                var f_Hlist = $(this).text();
                if (regexTitle.test(f_Hlist)) {
                    sw_x = 1;
                }
            });
			if(sw_x == 1){
                $('.f_title').each(function(){
                    var val_title = $(this).text();
                    if (regexTitle.test(val_title)) {
                        $(this).show();
                    }else{
                        $(this).hide().removeClass('f_title');
                    }
                });
                $('.f_list').each(function(){
                    var val_list = $(this).text();
                    if (regSubTitle.test(val_list)) {
                        $(this).show();
                        $(this).parents().siblings('.nameSect_x').show();
                    }else{
                        $(this).hide().removeClass('f_list');
                    }
                });
                $('.f_Hlist').each(function(){
                    var val_Hlist = $(this).text();
                    if (regSubTitle.test(val_Hlist)) {
                        $(this).show();
                        $(this).parents().siblings().find('.nameSect_x').show();
                        $('.hideAllSect').text('');
                    }else{
                        $(this).hide().removeClass('f_Hlist');
                    }
                });
                $('.boxSect_x').each(function(){
                    var titl = $(this).find('.f_title').length;
                    var listN = $(this).find('.f_list').length;
                    var hideL = $(this).find('.f_Hlist').length;
                    if(titl > 0 || listN > 0 || hideL > 0){
                        $(this).show();
                    }else{
                        $(this).hide().removeClass('boxSel_x');
                    }
                    if(titl == 0 && listN == 0 && hideL > 0){
                        $(this).find('.CmListNSectBl').show().addClass('f_sec_block');
                        $(this).find('.showAllSect').hide();
                    }
                    if(hideL == 0){
                        $(this).find('.CmListNSectBl').hide().removeClass('f_sec_block');
                        $(this).find('.showAllSect').hide();
                    }
                    if(titl == 1 && (listN == 0 || hideL == 0)){
                        $(this).find('.sh_list').show();
                        $(this).find('.hi_list').show();
                        $(this).find('.CmListNSectBl').hide().removeClass('f_sec_block');
                        $(this).find('.showAllSect').show();
                    }
                });
            }
        }else{
			$('.f_title, .f_list, .f_Hlist, .boxSect_x, .showAllSect').show();
			$('.CmListSectBl').find('li').addClass('f_list');
			$('.boxSect_x').find('.nameSect_x').addClass('f_title');
			$('.CmListNSectBl').find('li').addClass('f_Hlist');
			$('.CmListNSectBl').hide().removeClass('f_sec_block');
        }
    });
    $('body').on('click', '.CmClearButt', function(){
        $(this).hide();
        $('.CmInputSect').val('');
		$('.CmListSectBl').find('li').addClass('f_list');
		$('.boxSect_x').find('.nameSect_x').addClass('f_title');
		$('.CmListNSectBl').find('li').addClass('f_Hlist');
		$('.f_title, .f_list, .f_Hlist, .boxSect_x, .showAllSect').show();
		$('.CmListNSectBl').hide().removeClass('f_sec_block');
    });
});
