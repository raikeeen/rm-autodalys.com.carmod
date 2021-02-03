var sendForm = 1;//Проверка формы (swich)
function OverlayToggle(){ /* Admin side only */
	if($("#Overlay").css('display') !== 'none'){
		$("#Overlay").fadeOut(200);
	}else{
		$("#Overlay").width($("#AdmContent").width()+40).height($("#AdmContent").height()+60).fadeIn(200);
	}
}

function SimplePost(Name,Value){
	jQuery("<form action='' id='SimplePost' method='post'><input type='hidden' name='"+Name+"' value='"+Value+"'/></form>").appendTo('body');
	jQuery("#SimplePost").submit();
}

function IMAddRngDisc(from,to){
	jQuery("<span>"+from+"</span> <input class='TextA rngFROM' type='text' name='RNG_FROM[]' value='' maxlength='9' style='min-width:80px; width:80px;' /> "+to+
	" <input class='TextA rngTO' type='text' name='RNG_TO[]' value='' maxlength='9' style='min-width:80px; width:80px;' /> &#9658; "+
	"<input class='TextA rngVAL' type='text' name='RNG_VALUE[]' value='' maxlength='6' style='min-width:60px; width:60px;' />% <div class='tclear'></div>").appendTo('#rngs');
	return false;
}
function AddTips(track){
	jQuery(function() {  jQuery( document ).tooltip({track:true, content:function(){return jQuery(this).prop('title');}});   });
}

//Попап вывод результата AJAX (ResType = 0 - ошибка, 1 - успех)
function ShowResult(ResText, ResType=0){
	if(ResType>0){
		$('#ResMess').css({"color": "#001b00", "border": "3px solid #43a743"});
	}else{
		$('#ResMess').css({"color": "#190000", "border": "3px solid #d62626"});
	}
	$('#ResMess').html(ResText);
	$('#BoxResMess').show();
}

//Функция подсветки незаполненных полей
function lightEmpty(id){
	$(id).css({'border':'1px solid #d8512d','background':'#fff1f1'});
	setTimeout(function(){
		$(id).css({'border':'','background':'none'});
	},1000);
}

function checkInput(inputC){
	var inpC = $('#'+inputC).val();
	var result = true;
	if(inpC.length<3 || inpC==''){
		lightEmpty('#'+inputC);
		sendForm = 0;
		result = false;
	}
	return result;
}

function chInEmpty(inputC){
	var inpC = $('#'+inputC).val();
	var result = true;
	if(inpC==''){
		lightEmpty('#'+inputC);
		sendForm = 0;
		result = false;
	}
	return result;
}

function checkIP(inpIP){
	var inpE = $('#'+inpIP).val();
	var cIP = /[^0-9\.]/g;
	var result = true;
	if(!cIP.test(inpE)){
		//$('.er_msg').html('E-Mail - неверный формат');
		lightEmpty('#'+inpIP);
		sendForm = 0;
		result = false;
	}
	return result;
}

function checkEmail(inpEmail){
	var inpE = $('#'+inpEmail).val();
	var checkE = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
	if(!checkE.test(inpE)){
		//$('.er_msg').html('E-Mail - неверный формат');
		lightEmpty('#'+inpEmail);
		sendForm = 0;
	}
}
$(document).ready(function(){
	// PHP:: aprint_x()
	$(".PreTitle").on("click","", function(e){
		var PreID = $(this).data('preid');
		$("#Pre"+PreID).slideToggle();
	});

	// WebServices Reload by PKey
	$("body").on("click",".WsReload", function(e){
		var PKey = $(this).data('pkey');
		if(PKey!='' && PKey!='undefined'){
			$("<form action='' id='FoWsReload' method='post'><input type='hidden' name='WsReload' value='"+PKey+"'/></form>").appendTo('body');
			$("#FoWsReload").submit();
		}
	});
	
	$(".fxOverlay").on("mousedown","", function(e){
		if($(".fxCont").has(e.target).length === 0){
 			$(this).hide();
 		}
 	});
	
	$(".fxOverlay_adm").on("mousedown","", function(e){
		if($(".fxModal_adm").has(e.target).length === 0){
 			$(this).hide();
 		}
 	});

    /* if($(".fxModal").has(e.target).length === 0){
        $(".fxOverlay").hide();
    } */


	//check fields when adding currency
	$("#addCurr").on("click","", function(e){
		sendForm = 1;
		chInEmpty('RATADD');
		chInEmpty('TEMPLADD');
		if(sendForm==1){
			var CURADD = $('#CURADD').val();
			var RATADD = $('#RATADD').val();
			var TEMPLADD = $('#TEMPLADD').val();
			var TRUNCADD = $('#TRUNCADD').val();
			//alert(TRUNCADD);
			var cursb = $('#addCurr').attr('cursb');
			//alert(attrCur);
			if (cursb !== undefined && cursb !== false && cursb !== ''){
				OverlayToggle();
				jQuery("<form action='' id='fAddCur' method='post'><input type='hidden' name='CURA' value='"+cursb+"'/><input type='hidden' name='RATA' value='"+RATADD+"'/><input type='hidden' name='TEMA' value='"+TEMPLADD+"'/><input type='hidden' name='TRUA' value='"+TRUNCADD+"'/><input type='hidden' name='curEdit' value='Y'/></form>").appendTo('body');
				jQuery("#fAddCur").submit();
			}else{
				OverlayToggle();
				jQuery("<form action='' id='fAddCur' method='post'><input type='hidden' name='CURA' value='"+CURADD+"'/><input type='hidden' name='RATA' value='"+RATADD+"'/><input type='hidden' name='TEMA' value='"+TEMPLADD+"'/><input type='hidden' name='TRUA' value='"+TRUNCADD+"'/><input type='hidden' name='curAddNew' value='Y'/></form>").appendTo('body');
				jQuery("#fAddCur").submit();
			}
		}
	});

	/* Admin side Tips */
	$(".CloseTips").on("click","", function(e){
		var tipsid = $(this).attr('tipsid');
		var parentCls = $(this).parent();
		var objPostRL = {};
		objPostRL['HeadOff']='Y';
		objPostRL['HideTopTip']=tipsid;
		$.post("", objPostRL, function(resClsTip){
			if(resClsTip=='TIP_HIDED'){
				parentCls.hide().end().remove();
			}
		});
	});
	$(".TipMark").on("mouseover","", function(e){
		var tipid = $(this).data('tipid');
		$("#TipBox"+tipid).show();
	});
	$(".TipMark").on("mouseleave","", function(e){
        var tipid = $(this).data('tipid');
		$("#TipBox"+tipid).hide();
    });

    /* Admin Tips for Publick side */
    $("#CmContent, #AdmContent").on("mouseover",".CmATip", function(e){
        var title = $(this).attr('title');
        if(title){
            $(this).data('tipText', title).removeAttr('title');
            $('<p class="CmATipBox"></p>').html(title).appendTo('body').show(); //alert('+'+title);
        }else{return false;}
    });
    $("#CmContent, #AdmContent").on("mouseleave",".CmATip", function(e){
        $(this).attr('title', $(this).data('tipText'));
        $('.CmATipBox').remove();
    });
    $("#CmContent, #AdmContent").on("mousemove",".CmATip", function(e){
		var wBody = $('body').width();
		var mousey = e.pageY + 7; //Get Y coordinates
		var mousex = e.pageX; //Get X coordinates
		if(wBody - mousex < 300){
			var wTipBox = $('.CmATipBox').width();
			mousex = e.pageX - wTipBox - 25; //Get X coordinates
			$('.CmATipBox').css({ maxWidth:700 })
		}
        $('.CmATipBox').css({ top:mousey, left:mousex })
    });

	//Edit Price
	$("#EditPrice").click(function (e){
		//PopupForAjax();
		var aPath = $(this).data('path');
		var objEP = {};
		objEP['HeadOff']='Y';
		objEP['AdminAjax']='Y';
		objEP['sea']='add';
		objEP['BRAND_VIEW']=$(this).data('brand');
		objEP['PRID']=$(this).data('type');
		objEP['ARTICLE_VIEW']=$(this).data('art');
		$.post('/'+aPath+'/Prices.php', objEP, function(ResEP){
			$('#AjaxPopupCont_x').html(ResEP);
			$('#AjaxPopup_x').css('display', 'block');
			$('input').styler();
		});
	});
	$("body").on("click", "#sendAEPrice", function(e){
		$('input').trigger('refresh');
		//PopupForAjax();
		var sea = $(this).attr('sea');
		var aPath = $(this).attr('path');
		var objEP = {};
		if(sea == 'edit'){
			objEP['epk']=$('#epk').val();
			objEP['ept']=$('#ept').val();
			objEP['ecr']=$('#ecr').val();
			objEP['edn']=$('#edn').val();
			objEP['esn']=$('#esn').val();
			objEP['ess']=$('#ess').val();
		}
		objEP['HeadOff']='Y';
		objEP['AdminAjax']='Y';
		objEP['switchP']=sea;
		objEP['ARTICLE_VIEW']=$('#ARTICLE_VIEW').val();
		objEP['BRAND_VIEW']=$('#BRAND_VIEW').val();
		objEP['PRID']=$('#PRID').val();
		objEP['PRICE_LOADED']=$('#PRICE_LOADED').val();
		objEP['CURRENCY']=$('#CURRENCY').val();
		objEP['PRICE_TYPE']=$('#PRICE_TYPE').val();
		objEP['SUPPLIER_NAME']=$('#SUPPLIER_NAME').val();
		objEP['SUPPLIER_STOCK']=$('#SUPPLIER_STOCK').val();
		objEP['AVAILABLE_VIEW']=$('#AVAILABLE_VIEW').val();
		objEP['AVAILABLE_NUM']=$('#AVAILABLE_NUM').val();
		objEP['DELIVERY_VIEW']=$('#DELIVERY_VIEW').val();
		objEP['DELIVERY_NUM']=$('#DELIVERY_NUM').val();
		objEP['CODE']=$('#CODE').val();
		$(".cOpt_I").each(function(){
			var idInput = $(this).attr('id');
			objEP[idInput]=$(this).val();
		});
		$(".cOpt_C").each(function(){
			var idInput = $(this).attr('id');
			if($('#'+idInput).prop('checked')){
				//alert(idInput);
				objEP[idInput]=$(this).val();
			}
		});
		$.post('/'+aPath+'/Prices.php', objEP, function(ResEA){
			if(ResEA == 'ADD_PRICE' || ResEA == 'EDIT_PRICE'){
				location.reload(true);
			}else{alert(ResEA);}
		});
	});

	$("body").on("click", ".EditPrice_x", function(e){
		//PopupForAjax();
		var aPath = $(this).data('path');
		var objEP = {};
		objEP['HeadOff']='Y';
		objEP['AdminAjax']='Y';
		objEP['sea']='edit';
		objEP['eav']=$(this).data('eav');
		objEP['epk']=$(this).data('epk');
		objEP['ept']=$(this).data('ept');
		objEP['ecr']=$(this).data('ecr');
		objEP['edn']=$(this).data('edn');
		objEP['esn']=$(this).data('esn');
		objEP['ess']=$(this).data('ess');
		$.post('/'+aPath+'/Prices.php', objEP, function(ResEP){
			$('#AjaxPopupCont_x').html(ResEP);
			$('#AjaxPopup_x').css('display', 'block');
			$('input').styler();
		});
	});

	//Delete price
	$("body").on("click", ".DPrice_x", function(e){
		PopupForAjax();
		var aPath = $(this).data('path');
		var objDel = {};
		objDel['HeadOff']='Y';
		objDel['AdminAjax']='Y';
		objDel['eav'] = $(this).data('dav');
		objDel['dpk'] = $(this).data('dpk');
		objDel['dpt'] = $(this).data('dpt');
		objDel['dcr'] = $(this).data('dcr');
		objDel['ddn'] = $(this).data('ddn');
		objDel['dsn'] = $(this).data('dsn');
		objDel['dss'] = $(this).data('dss');

		$.post('/'+aPath+'/Prices.php', objDel, function(ResDel){
			if(ResDel == 'DELETE_PRICE'){
				location.reload(true);
			}else{alert(ResDel);}
		});
	});


	//apanel
	$('#CBrand_x').on('input',function(e){
		$("#CArticle").prop("disabled", true);
		var aPath = $(this).attr('pathx');
		//alert('red');
		$("#lBV_x").show();
		$("#CBrand_x").css({'color': 'red', 'border': '1px solid red'});
		var objPN = {};
		objPN['HeadOff']='Y';
		objPN['BV']=$("#CBrand_x").val();
		$.post("/"+aPath+"/Crosses.php", objPN, function(ResPN){
			$("#lBV_x").html(ResPN);
		});
	});

	$("body").on("click", ".optBV", function(e){
		$("#CArticle").prop("disabled", false);
		$('#BrBrand').val($(this).text());
		$('#CBrand_x').val($(this).text());
		$("#CBrand_x").css({'color': 'black', 'border': '1px solid #bebebe'});
		$("#lBV_x").hide();
	});

	$('#DLeft').on('click',function(e){
		if($(this).hasClass("direcActive_x")){
			if($('#DRight').hasClass("direcActive_x")){
				$(this).removeClass("direcActive_x");
			}
		}else{
			$(this).addClass("direcActive_x");
		}
	});

	$('#DRight').on('click',function(e){
		if($(this).hasClass("direcActive_x")){
			if($('#DLeft').hasClass("direcActive_x")){
				$(this).removeClass("direcActive_x");
			}
		}else{
			$(this).addClass("direcActive_x");
		}
	});

	var isUpdCatID = false;
	$("#CArticle").on("input","", function(e){
		var ResEl = $(this).prev();
		ResEl.show();
		var CurEVal = $(this).val();

		isUpdCatID = true;
		ResEl.addClass("InpResX"); ResEl.html('OK');
	});
	$("#CArticle").on("focusout","", function(e){
		var aPath = $(this).attr('pathx');
		if(isUpdCatID){
			OverlayToggle();
			var objPostFV = {};
			objPostFV['HeadOff']='Y';
			objPostFV['ArtCheck']=$(this).val();
			objPostFV['BraCheck']=$('#CBrand_x').val();
			$.post("/"+aPath+"/Crosses.php", objPostFV, function(Res){
				OverlayToggle();
				if(Res=='SUCCESS_ART'){

				}else if(Res=='NO_ART'){

				}else{
					alert(Res);
				}
			});
			$(this).prev().hide();
			isUpdCatID = false;
		}
	});
	
	//Закрыть попап окно при выборе раздела/бренда
	$(document).click(function(e){
		var elem = $(".boxAList");
		if(e.target!=elem[0]&&!elem.has(e.target).length){
			elem.fadeOut(100);
		}
	})


	// Font-size admin-panel main page
	// const admSect = document.getElementsByClassName('AdBtName');
	// for(i=0; i<admSect.length; i++){
	// 	const txt = $(admSect[i]).text();
	// 	const edTxt = txt.replace(/\s/g, '');
	// 	const txtVal = edTxt.length;
	// 	if(txtVal > 19){
	// 		$(admSect[i]).css('font-size','11px');
	// 	}else{
	// 		$(admSect[i]).css('font-size','12px');
	// 	}
	// }
});
