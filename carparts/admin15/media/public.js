$(document).ready(function() {
	
	//Admin edit Image button
	$('body').on("click",".CmEditProductImg", function(e){
		$('#AjaxPopupCont_x').html('');
		$('#AjaxPopup_x').fadeIn();
		var Pom = $(this).data('pom');
		var aPath = $(this).data('apath');
		$('#AjaxPopupLoad_x').show();
		$.ajax({url:aPath+'EditImage.php', type:'POST', dataType:'html', data:{Pom:Pom}})
            .done(function(Result){
			   $('#AjaxPopupCont_x').html(Result);
			   $('#AjaxPopupLoad_x').hide();
            });
	});
	
	//Admin Edit analogs group button
	$('body').on("click",".CmEditProductLink", function(e){
		$('#AjaxPopupCont_x').html('');
		$('#AjaxPopup_x').fadeIn();
		var Pom = $(this).data('pom');
		var aPath = $(this).data('apath');
		$('#AjaxPopupLoad_x').show();
		$.ajax({url:aPath+'EditLink.php', type:'POST', dataType:'html', data:{Pom:Pom}})
            .done(function(Result){
			   $('#AjaxPopupCont_x').html(Result);
			   $('#AjaxPopupLoad_x').hide();
            });
	});
	
	//Admin Edit product Data button
	$('body').on("click",".CmEditProductData", function(e){
		$('#AjaxPopupCont_x').html('');
		$('#AjaxPopup_x').fadeIn();
		var Pom = $(this).data('pom');
		var aPath = $(this).data('apath');
		$('#AjaxPopupLoad_x').show();
		$.ajax({url:aPath+'EditProduct.php', type:'POST', dataType:'html', data:{Pom:Pom}})
            .done(function(Result){
			   $('#AjaxPopupCont_x').html(Result);
			   $('#AjaxPopupLoad_x').hide();
            });
	});
	
	
});

jQuery(document).mousedown(function (e){
	$("#AjaxPopup_x").on("click","", function(e){
		if($(".fxModal_adm").has(e.target).length === 0){
			$(this).hide();
			$('#AjaxPopupCont_x').html('');
		}
	});
});