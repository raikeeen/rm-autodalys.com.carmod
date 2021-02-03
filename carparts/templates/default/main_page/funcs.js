//---//SHOW ELEMENTS FUNCTION
function showElements (elements) {
 elements = elements.length ? elements : [elements];
  for (var index = 0; index < elements.length; index++) {
    elements[index].style.transition = '0.35s ease-in-out';
    elements[index].style.display = 'flex';
    elements[index].style.opacity = '1';
  }
}
//---//

document.addEventListener("DOMContentLoaded",function(){

	// SELECT MANUFACTURER TABS
	var tabCars = document.getElementsByClassName('CmTabSelManuf');
	for(i=0; i<tabCars.length; i++){
		if(tabCars[i].classList.contains('CmTabCars')){
			tabCars[i].classList.add('CmActiveTabManuf');
		}
		tabCars[i].addEventListener('click', function(e){
			Array.from(tabCars).forEach(tab=>{
				tab.classList.remove('CmActiveTabManuf');
			});
			this.classList.add('CmActiveTabManuf');
			let elemId = this.dataset.name,
			manBlock = document.getElementsByClassName('CmManufContBlock');
			for(i=0; i<manBlock.length; i++){
				if(manBlock[i].id != elemId){
                    manBlock[i].style.display = 'none';
				}else{
                    manBlock[i].style.display = 'grid';
				}
			}
		});
	}
	// $(".ltabs").lightTabs();

	/* 	var _contentRows = $('.carsbuts');
      _count = 0;
      var symbol = [];
      _contentRows.each(function (i) {
	var _cellText = $(this).children('.tdmbut-text').eq(0).text().substr(0,1);
	if($.inArray(_cellText,symbol)==-1) {
	  symbol.push(_cellText);
	}
	_count += 1;
      });
      var arr = symbol.sort();
      for(var k=0;k<arr.length;k++) {
	$('.carsfilter').append('<a href="javascript:void(0)">'+arr[k]+'</a>');
      }
    var _alphabets = $('.carsfilter > a');

    _alphabets.click(
		function () {
			var _letter = $(this), _text = $(this).text(), _count = 0;

			_alphabets.removeClass("active");
			_letter.addClass("active");

			_contentRows.hide();
			_contentRows.each(function (i) {
				if(_text==AllLng) {
					_count += 1;
					$(this).show();
				}else {
					var _cellText = $(this).children('.tdmbut-text').eq(0).text();
					if (RegExp('^' + _text).test(_cellText)) {
						_count += 1;
						$(this).show();
					}
				}
			});
    }); */

    //Выпадающий список подразделов
    jQuery('.showAllSect').on('click', function(){
        $(this).hide();
        var showLNext = jQuery(this).attr("showLNext");
        var widthBoxSect = jQuery('.boxSect_x').width();
        // alert(widthBoxSect);
        showLNext.toString();
        jQuery('.CmListNSectBl').css('width', '100%');
        jQuery(this).closest('.boxSect_x').css({boxShadow: '0px 0px 10px 1px #d0d0d0'});

        jQuery('#'+showLNext).slideDown(400);
        jQuery(this).parent().parent().parent().parent('.boxSect_x').mouseleave(function(){
            jQuery(this).find('.CmListNSectBl').slideUp(400);
            $(this).find('.showAllSect').delay(500).fadeIn(200);
            jQuery(this).closest('.boxSect_x').css({boxShadow: 'none'});
        });
    });

    //Прятать подразделы
    jQuery('.hideAllSect').on('click', function(){
        jQuery(this).parent().slideUp(400);
        jQuery(this).closest('.boxSect_x').css({boxShadow: 'none'});
    });

    //Выпадающий список разделов
    var allSect = document.getElementsByClassName('butAllSec');
    for(i=0; i<allSect.length; i++){
        allSect[i].addEventListener('click', function(){
            showElements(document.querySelectorAll('.boxSect_x'));
            this.style.display='none';
        });
    }
//     jQuery('.butAllSec').on('click', function(){
//         jQuery('.boxSect_x').fadeIn(300);
//         jQuery('.butAllSec').remove();
//     });

	// Фильтр по разделам
    $('.CmInputSect').keyup(function(){
        var c = $(this).data('lng');
        jQuery('.boxSect_x').mouseleave(function(e){
            e.preventDefault();
        });
        $('.CmListSectBl').find('a').addClass('f_list');
        $('.boxSect_x').find('.nameSect_x').addClass('f_title');
        $('.CmListNSectBl').find('a').addClass('f_Hlist');
        if($('.CmInputSect').val().length == 0){
            $('.clearButt').hide();
        }
        if($('.CmInputSect').val().length > 0){
            $('.clearButt').show();
        }
        if($('.CmInputSect').val().length >= 3){
            $('.butAllSec').hide();
            var val_inp = $(this).val();
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
                if (regSubTitle.test(f_list)) {
                    sw_x = 1;
                }
            });
            $('.f_Hlist').each(function(){
                var f_Hlist = $(this).text();
                if (regSubTitle.test(f_Hlist)) {
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
            $('.CmListSectBl').find('a').addClass('f_list');
            $('.boxSect_x').find('.nameSect_x').addClass('f_title');
            $('.CmListNSectBl').find('a').addClass('f_Hlist');
            $('.CmListNSectBl').hide().removeClass('f_sec_block');
        }
    });
    $('.clearButt').click(function(){
        $(this).hide();
        $('.CmInputSect').val('');
        $('.CmListSectBl').find('a').addClass('f_list');
        $('.boxSect_x').find('.nameSect_x').addClass('f_title');
        $('.CmListNSectBl').find('a').addClass('f_Hlist');
        $('.f_title, .f_list, .f_Hlist, .boxSect_x, .showAllSect').show();
        $('.CmListNSectBl').hide().removeClass('f_sec_block');
    });
});


// (function($){
// 	jQuery.fn.lightTabs = function(options){
// 		var createTabs = function(){
// 			tabs = this;
// 			i = 0;
// 			showPage = function(i){
// 				$(tabs).children("div").children("div").hide();
// 				$(tabs).children("div").children("div").eq(i).show();
// 				$(tabs).children("ul").children("li").removeClass("active");
// 				$(tabs).children("ul").children("li").eq(i).addClass("active");
// 			}
// 			showPage(0);
// 			$(tabs).children("ul").children("li").each(function(index, element){
// 				$(element).attr("data-page", i);
// 				i++;
// 			});
// 			$(tabs).children("ul").children("li").click(function(){
// 				if($(this).attr("id")!='tboc'){
// 					showPage(parseInt($(this).attr("data-page")));
// 				}else{
// 					window.location = "/"+tmd_root_dir+"/original-catalog";
// 				}
// 			});
// 		};
// 		return this.each(createTabs);
// 	};
// })(jQuery);
