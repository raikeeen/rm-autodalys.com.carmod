<?/*<script src="/<?=$ModDir?>/media/js/jquery.js"></script>*/?>
<?/*<div class="c_BrTop3px CmRegNumAdd CmRegNumPosition<?=$RegNumPosition?>">*/?>
<div class="CmRegNumWrap">
	<div class="CmRegNumCous"><span id="RegNumCountry"><?=$DEF_COUNTRY?></span></div>
	<input type="text" id="RegNumValue" value="" maxlength="10" class="CmRegNumField c_BorderFoc" placeholder="<?=LangRN_x('Plate_Number')?>"> 
	<div class="CmRegnumLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div>
	<div class="CmRegNumGo">
		<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M23.111 20.058l-4.977-4.977c.965-1.52 1.523-3.322 1.523-5.251 0-5.42-4.409-9.83-9.829-9.83-5.42 0-9.828 4.41-9.828 9.83s4.408 9.83 9.829 9.83c1.834 0 3.552-.505 5.022-1.383l5.021 5.021c2.144 2.141 5.384-1.096 3.239-3.24zm-20.064-10.228c0-3.739 3.043-6.782 6.782-6.782s6.782 3.042 6.782 6.782-3.043 6.782-6.782 6.782-6.782-3.043-6.782-6.782zm2.01-1.764c1.984-4.599 8.664-4.066 9.922.749-2.534-2.974-6.993-3.294-9.922-.749z"/></svg>
	</div>
	<div class="CmRegNumClear">
		<div id="CmRegNumFail" class="CmRegNumRes"></div>
		<div id="CmRegNumTypes" class="CmRegNumRes"></div>
	</div>
</div>
<?/*</div>*/?>

<script type="text/javascript">
$(document).ready(function($){
	var RegNumCookie = RegNumReadCookie('RegNum');
	if(RegNumCookie!==null && RegNumCookie!=''){$('#RegNumValue').val(RegNumCookie);}
	
	function CmRegNum(){
		var RegNum = $('#RegNumValue').val();
		if(RegNum!=''){
			$("#CmRegNumFail").hide();
			RegNum = RegNum.replace(/[^a-zA-Z0-9ÄäÖöÅå]+/g, '');
			if(RegNum.length>5 && RegNum.length<11){ //alert(RegNum);
				$('.CmRegNumGo').removeClass('c_fillBg'); //Hide
				$('.CmRegnumLoading').fadeIn(100);
				$('#RegNumValue').prop("disabled",true);
				$.ajax({
					url:'<?=REGNUM_PROCESSOR?>', type:'post', dataType:'html',
					data:'RegNumCountry='+$("#RegNumCountry").text()+'&RegNumValue='+RegNum,
					statusCode:{
						202: function(Res){ //Admin result
							$('#CmRegNumFail').html(Res).show();
							RegNumWriteCookie('RegNum',RegNum,999);
						},
						204: function(){ //User result
							$('#CmRegNumFail').html('<?=LangRN_x('No_result')?>').show().delay(2000).fadeOut("slow"); 
							$('#RegNumValue').prop("disabled",false);
							$('.CmRegnumLoading').fadeOut(100);
						},
						200: function(Res){ //Redirect
							RegNumWriteCookie('RegNum',RegNum,999); //alert(Res);
							$('.CmRegNumAnim').fadeIn(100);
							$(location).attr('href',Res);
						},
						201: function(Res){ //Select model
							RegNumWriteCookie('RegNum',RegNum,999);
							$('#CmRegNumTypes').html(Res).show();
							$('#RegNumValue').prop("disabled",false);
							$('.CmRegnumLoading').fadeOut(100);
						},
					},
					success: function(Res){
						
					},
				});
			}else{$('#RegNumValue').focus();}
		}else{$('#RegNumValue').focus();}
	}
	
	$("body").on("keyup","#RegNumValue", function(e){
		if(e.which == 13){
			CmRegNum(); return false;
		}else{
			var RegNum = $('#RegNumValue').val();
			RegNum = RegNum.replace(/[^a-zA-Z0-9ÄäÖöÅå]+/g, '');
			$('#RegNumValue').val(RegNum);
			//alert(RegNum);
			if(RegNum!=''){
				if(RegNum.length>5){
					$('.CmRegNumGo').addClass('c_fillBg'); //Show
				}else{
					$('.CmRegNumGo').removeClass('c_fillBg'); //Hide
				}
			}else{
				$('.CmRegNumGo').removeClass('c_fillBg'); //Hide
			}
		}
	});
	$("body").on("click",".CmRegNumGo", function(e){
		CmRegNum(); return false;
	});
	$("body").on("click","#RegNumClose", function(e){
		$('#CmRegNumTypes').html('').hide();
	});
});
$(document).click(function(event) { //Close on click Out Side
    if(!$(event.target).closest('#CmRegNumFail').length) {
        if($('#CmRegNumFail').is(":visible")) {
            $('#CmRegNumFail').hide();
        }
    }     
});


function RegNumWriteCookie(name, value, days){
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

function RegNumReadCookie(name){
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