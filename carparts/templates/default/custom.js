// This JS script is NOT updatable by CarMod auto-updates system. 
// You can make any modifications of it


// Called after "Ajax Add Cart" event - in ../includes.php 
function CmAfterCartAjax(ResultHTML, CmsCartID, ClickedButton, CartErrors, IsCmAdmin){
	if(CartErrors!='' && IsCmAdmin=='Y'){
		$('.fxCont').html(CartErrors).css('text-align','left');
		$('.fxOverlay').show();
		//alert( CmCartErrors );
	}else{
		ion.sound.play("add",{loop:false,volume:0.6});
		if($("#"+CmsCartID).length){
			$("#"+CmsCartID).html(ResultHTML); //Refresh mini-cart html content
//			$("#"+CmsCartID).bounce({
//				interval:100,
//				distance:5,
//				times:4
//			});
			ClickedButton.removeClass('c_BgGradient c_Br1pxDark c_BgGradHov');
			//if($(document).width() < 770){LoadingToggle(); location.reload();}
			/* /wp-content/plugins/woocommerce/assets/js/frontend/cart-fragments.js   line:104 */
			// $( document.body ).trigger( 'wc_fragment_refresh' );
		}else{
			alert('CmsCartID element ID not exists: '+CmsCartID+' ');
		}
	}
	return true;
}

//Called if NOT Ajax used - but POST/reload method
function CmAddCartPost(URL, PriceID, Quantity){


	LoadingToggle('CmContent',1);
	$.ajax({url:URL, type:'POST', dataType:'html', data:{'AddToCart':PriceID,'Qty':Quantity} })
	.done(function(Res){
		ion.sound.play("add",{loop:false,volume:0.6});
		location.reload();
	});

	/* URL = window.location.href;
	LoadingToggle('CmContent',1);
	ion.sound.play("add",{loop:false,volume:0.6});
	$("<form action='"+URL+"' id='CmAddCartForm' method='post'>"+
		"<input type='hidden' name='AddToCart' value='"+PriceID+"'/>"+
		"<input type='hidden' name='Qty' value='"+Quantity+"'/>"+
		"</form>").appendTo('body'); $("#CmAddCartForm").submit();
	 */
	 return true;
}