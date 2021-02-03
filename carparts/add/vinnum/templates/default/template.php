<?/*<script src="/<?=$ModDir?>/media/js/jquery.js"></script> */?>
<div class="CmVinNumWrap">
	<input type="text" id="VinNumValue" value="" maxlength="17" class="CmVinNumField c_BorderFoc" placeholder="<?=LangVN_x('VIN_Number')?>">
	<div class="CmVinnumLoading"><div class="CmLoadVinBl"></div><div class="CmLoadVinBl"></div><div class="CmLoadVinBl"></div><div class="CmLoadVinBl"></div></div>
	<div class="CmVinNumGo">
		<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M23.111 20.058l-4.977-4.977c.965-1.52 1.523-3.322 1.523-5.251 0-5.42-4.409-9.83-9.829-9.83-5.42 0-9.828 4.41-9.828 9.83s4.408 9.83 9.829 9.83c1.834 0 3.552-.505 5.022-1.383l5.021 5.021c2.144 2.141 5.384-1.096 3.239-3.24zm-20.064-10.228c0-3.739 3.043-6.782 6.782-6.782s6.782 3.042 6.782 6.782-3.043 6.782-6.782 6.782-6.782-3.043-6.782-6.782zm2.01-1.764c1.984-4.599 8.664-4.066 9.922.749-2.534-2.974-6.993-3.294-9.922-.749z"/></svg>
	</div>
	<div class="CmVinNumClear">
		<div id="CmVinNumFail" class="CmVinNumRes"></div>
		<div id="CmVinNumTypes" class="CmVinNumRes"></div>
	</div>
</div>
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
					url:'<?=VINNUM_PROCESSOR?>', type:'post', dataType:'html',
					data:'VinNumValue='+VinNum,
					statusCode:{
						202: function(Res){ //Admin result
							$('#CmVinNumTypes').html('').hide();
							$('#CmVinNumFail').html(Res).show();
							VinNumWriteCookie('VinNum',VinNum,999);
						},
						204: function(){ //User result
							$('#CmVinNumTypes').html('').hide();
							$('#CmVinNumFail').html('<?=LangVN_x('No_result')?>').show().delay(2000).fadeOut("slow");
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
			url:'<?=VINNUM_PROCESSOR?>', type:'post', dataType:'html',data:'VinNumValue='+VinNum+'&Selected='+$(this).data('typid'),
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