<?php 
mb_internal_encoding("UTF-8"); 
$FullPath = __DIR__;
$aDir = array_filter(explode('/',$FullPath));
list($TemplDir) = array_slice($aDir,-1,1);
$ModFullPath = str_replace('add/search/'.$TemplDir,'',$FullPath);
define('CM_PROLOG_INCLUDED',true);
require($ModFullPath.'config.php');
if(!defined('FURL_SEARCH')){define('FURL_SEARCH','search');}
if($SearchPosition==''){$SearchPosition='Left';}
if(isset($Search_Def_Lang) AND strlen($Search_Def_Lang)==2){define('SELECT_DEF_LANG',$Search_Def_Lang);}else{define('SELECT_DEF_LANG','en');}

if(!function_exists('Ln_x')){
	function Ln_x($Key){
		$aLn=Array();
		$L = $_SESSION['LANG_x']; if($L==''){$L=SELECT_DEF_LANG;}
		$LnFile = __DIR__.'/../lang/'.$L.'.php';
		if(file_exists($LnFile)){
			include($LnFile);
		}else{
			include(__DIR__.'/../lang/en.php');
		}
		if(array_key_exists($Key,$aLn)){$Key=$aLn[$Key];}
		echo $Key;
	}
} 
?>
<link rel="stylesheet" href="/<?=CM_DIR?>/templates/default/artnum_search/style.css" type="text/css">
<link rel="stylesheet" href="/<?=CM_DIR?>/add/search/default/styles.css" type="text/css">

<div class="CmSearchWrap CmSearchPosition<?=$SearchPosition?>">
	<input type="text" id="ArtSearch VinNumValue" value="" maxlength="40" class="CmSearchAddField c_BorderFoc" placeholder="<?Ln_x('Article')?>..">
	<div class="CmSearchLoading"><div class="CmLoadSBl"></div><div class="CmLoadSBl"></div><div class="CmLoadSBl"></div><div class="CmLoadSBl"></div></div>
	<div class="CmSearchGo">
		<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M23.111 20.058l-4.977-4.977c.965-1.52 1.523-3.322 1.523-5.251 0-5.42-4.409-9.83-9.829-9.83-5.42 0-9.828 4.41-9.828 9.83s4.408 9.83 9.829 9.83c1.834 0 3.552-.505 5.022-1.383l5.021 5.021c2.144 2.141 5.384-1.096 3.239-3.24zm-20.064-10.228c0-3.739 3.043-6.782 6.782-6.782s6.782 3.042 6.782 6.782-3.043 6.782-6.782 6.782-6.782-3.043-6.782-6.782zm2.01-1.764c1.984-4.599 8.664-4.066 9.922.749-2.534-2.974-6.993-3.294-9.922-.749z"/></svg>
	</div>
	<div class="CmSearchClear"><div id="CmSearchResult" class="c_Border CmSearchRes<?=$SearchPosition?>"></div></div>
</div>
<script type="text/javascript">

</script>
<script type="text/javascript">
$(document).ready(function($){
	function CmSearch(){
		var ArtNum = $('#ArtSearch').val();
		if(ArtNum!=''){
			$("#CmSearchResult").hide();
			ArtNum = ArtNum.replace(/[^a-zа-яА-ЯA-Z0-9. -]+/g, '');
			if(ArtNum.length>2){ //alert(ArtNum);
				$('.CmSearchGo').removeClass('CmColorFi'); //Hide
				$('.CmSearchLoading').fadeIn(100);
				$('#ArtSearch').prop("disabled",true);
				$.ajax({url:'<?=FURL_x?>/<?=FURL_SEARCH?>/'+ArtNum+'/', type:'POST', dataType:'html', data:{CarModAjax:'Y', ShortResult:'Y', HideStat:'Y', WithRedirects:'Y', ArtSearch:ArtNum}})
					.done(function(Result){
						var aResult = Result.split('REDIRECT:');
						if(aResult.length>1){
							window.location = aResult[1];
						}else{
							$("#CmSearchResult").show().html(Result);
							$('#ArtSearch').prop("disabled",false);
							$('.CmSearchLoading').fadeOut(100);
						}
					});
				//location = '<?=FURL_x?>/<?=FURL_SEARCH?>/'+ArtNum+'/';
			}else{$('#ArtSearch').focus();}
		}else{$('#ArtSearch').focus();}
	}
	$("body").on("keyup","#ArtSearch", function(e){
		if(e.which == 13){
			CmSearch(); return false;
		}else{
			var ArtNum = $('#ArtSearch').val();
			ArtNum = ArtNum.replace(/[^a-zа-яА-ЯA-Z0-9 \/.-]+/g, '');
			$('#ArtSearch').val(ArtNum);
			//alert(ArtNum);
			if(ArtNum!=''){
				if(ArtNum.length>2){
					$('.CmSearchGo').addClass('CmColorFi'); //Show
				}else{
					$('.CmSearchGo').removeClass('CmColorFi'); //Hide
				}
			}else{
				$('.CmSearchGo').removeClass('CmColorFi'); //Hide
			}
		}
	});
	$("body").on("click",".CmSearchGo", function(e){
		CmSearch(); return false;
	});
});
$(document).click(function(event) { //Close on click Out Side
    if(!$(event.target).closest('#CmSearchResult').length) {
        if($('#CmSearchResult').is(":visible")) {
            $('#CmSearchResult').hide();
        }
    }        
});

</script>
<script type="text/javascript">
    $(document).ready(function($){
        var VinNumCookie = VinNumReadCookie('VinNum');
        //if(VinNumCookie!==null && VinNumCookie!=''){$('#VinNumValue').val(VinNumCookie);}

        function CmVinNum(){
            var VinNum = $('#VinNumValue').val();
            if(VinNum!=''){
                $("#CmVinNumFail").hide();
                VinNum = VinNum.replace(/[^a-z. _)(A-Z0-9ÄäÖöÅå-]+/g, '');
                if(VinNum.length>2 && VinNum.length<18){ //alert(VinNum);
                    $('.CmVinNumGo').removeClass('c_fillBg'); //Hide
                    $('.CmVinnumLoading').fadeIn(100);
                    $('#VinNumValue').prop("disabled",true);
                    $.ajax({
                        url:'/carparts/add/vinnum/controller.php', type:'post', dataType:'html',
                        data:'VinNumValue='+VinNum,
                        statusCode:{
                            202: function(Res){ //Admin result
                                $('#CmVinNumTypes').html('').hide();
                                $('#CmVinNumFail').html(Res).show();
                                VinNumWriteCookie('VinNum',VinNum,999);
                            },
                            204: function(){ //User result
                                $('#CmVinNumTypes').html('').hide();
                                $('#CmVinNumFail').html('Nerasta').show().delay(2000).fadeOut("slow");
                            },
                            200: function(Res){ //Redirect
                                VinNumWriteCookie('VinNum',VinNum,999); //alert(Res);
                                $('.CmVinnumLoading').fadeIn(100);
                                $(location).attr('href',Res);
                            },
                            201: function(Res){ //Select model
                                VinNumWriteCookie('VinNum',VinNum,999);
                                $('#CmVinNumTypes').html(Res).show();
                                $('.VinNumLit:first-child').click();
                            },
                        },
                        success: function(){
                            $('#VinNumValue').prop("disabled",false);
                            $('.CmVinnumLoading').fadeOut(100);
                        },
                    });
                }else{$('#VinNumValue').focus(); }
            }else{$('#VinNumValue').focus();}
        }

        $("body").on("keyup","#VinNumValue", function(e){
            if(e.which == 13){
                CmVinNum(); return false;
            }else{
                var VinNum = $('#VinNumValue').val();
                VinNum = VinNum.replace(/[^a-z. _)(A-Z0-9ÄäÖöÅå-]+/g, '');
                $('#VinNumValue').val(VinNum);
                //alert(VinNum);
                if(VinNum!=''){
                    if(VinNum.length>2 && VinNum.length<18){
                        $('.CmVinNumGo').addClass('c_fillBg'); //Show
                    }else{
                        $('.CmVinNumGo').removeClass('c_fillBg'); //Hide
                    }
                }else{
                    $('.CmVinNumGo').removeClass('c_fillBg'); //Hide
                }
            }
        });
        $("body").on("click",".CmVinNumGo", function(e){
            CmVinNum(); return false;
        });
        $("body").on("click","#VinNumClose", function(e){
            $('#CmVinNumTypes').html('').hide();
        });
        $("body").on("click",".VinNumLit", function(e){
            var TabLit = $(this).html();
            $(this).parent().find('td').each(function(){
                $(this).removeClass('VinNumLitActive');
            });
            $(this).addClass('VinNumLitActive');


            $('.VinNumTab').find('.VinNumModel').each(function(){
                $(this).hide();
            });

            $(this).parent().parent().find('tr').each(function(){
                var Lit = $(this).data('lit');
                var ModId = $(this).data('modid');
                if(Lit!=null && Lit!=''){
                    if(Lit==TabLit){
                        $(this).show();
                        $('.ModId'+ModId).show();
                    }else{
                        $(this).hide();
                    }
                }
            });
        });

        $("body").on("click",".VinNumSelector", function(e){
            var VinNum = $('#VinNumValue').val();
            var href = $(this).attr('href');
            e.preventDefault();
            $('.CmVinnumLoading').fadeIn(100);
            $('#CmVinNumTypes').hide();
            $.ajax({
                url:'/carparts/add/vinnum/controller.php', type:'post', dataType:'html',data:'VinNumValue='+VinNum+'&Selected='+$(this).data('typid'),
                success: function(){
                    window.location = href;
                },
            });
        });

    });
    $(document).click(function(event) { //Close on click Out Side
        if(!$(event.target).closest('#CmVinNumFail').length) {
            if($('#CmVinNumFail').is(":visible")) {
                $('#CmVinNumFail').hide();
            }
        }
    });


    function VinNumWriteCookie(name, value, days){
        var expires;
        if(days){
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
        }else{
            expires = "";
        }
        document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
    }

    function VinNumReadCookie(name){
        var nameEQ = encodeURIComponent(name) + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ')
                c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0)
                return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    }
</script>