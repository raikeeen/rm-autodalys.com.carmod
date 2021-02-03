<?php
/*
$MSelect_Position -> Main block position for CSS: Left / Right
LangMS_x() -> Function to show phrases at selected language. Phrases from /carparts/add/mselect/langs/..
$aYearGroups -> Array of Years
$aManufGroups -> Array of Manufacturers by Groups: Pass, Comm, Moto
$aFavManuf -> Array of Favorite Manufacturers
$IsDisab.. -> String variables: contains CSS class "CmMSelectDisabled" or empty
$MfGroupsCnt -> Int variable: manufacturer groups count (Pass, Comm, Moto)
$ModelsMesg -> String variable, phrase: "No models of this year"
ModelsAjaxStart() -> Function to cut top & bottom of AJAX response
$aModels -> Array of Models
FURL_x - Constant: real CarMod "Friendly URL" link, like "/en/carparts/" (from CarMod /config.php script)
MsLang - Constant: currently selected Language code (en,ru,..)

*/
?>
<div id="CmMSelectAdd">
    <div class="CmMSelectTable">
        <div class="CmMSelectTableSeTD">
            <div class="CmMselBoxWrap">
                <div class="CmMSelectBox">
                    <div class="CmMSelectBut"><span id="CmMS_Year" data-value="<?=$sYear?>" class="CmColorTxi"><?=$vYear?></span> &#9660;</div>
                    <div class="CmYearSelWrap CmMSelectDown CmMSelectDown<?=$MSelect_Position?>">
                        <?foreach($aYearGroups as $aYears){?>
                            <div class="CmYearGroupInner CmColorBr">
                                <?//if($FsMsCut){?><!-- <div class="CmMSelectYearCut CmColorBr"></div> --><?//}$FsMsCut=true;
                                foreach($aYears as $Year){?>
                                    <div class="CmColorBgh CmMS_Option CmMSelectYear" data-type="Year" data-option="<?=$Year?>"><?=$Year?></div>
                                <?}?>
                            </div>
                        <?}?>
                    </div>
                </div>
                <div class="CmMSelectBox">
                    <div class="CmMSelectBut <?=$IsDisabManuf?>" id="CmMS_BoxManuf"><span id="CmMS_Manuf" data-value="<?=$sManuf?>"><?=$vManuf?></span> &#9660;</div>
                    <div class="CmMSelectDown CmMSelectDown<?=$MSelect_Position?> CmMSelectDownManuf" id="CmMS_DdManuf">
                        <?if($MfGroupsCnt>1){?>
                            <div class="CmColorBr CmMSelectGroups">
                                <?$GrActive = 'CmMSelectGrActive CmColorTxi';
                                foreach($aManufGroups as $Group=>$aManufs){?>
                                    <div class="CmMSelectGrTab <?=$GrActive?>" data-group="<?=$Group?>"><?=LangMS_x('Group_'.$Group);?></div><?
                                    $GrActive = '';
                                }?>
                            </div><div style="clear:both;"></div><?
                        }
                        foreach($aManufGroups as $Group=>$aManufs){
                            ?><div id="Cm<?=$Group?>Tab" class="CmMSelectMfTab" <?=$IsDisplayTab?> >
                                <div class="CmManufInner">
                                    <?foreach($aManufs as $Code=>$Name){?>
                                        <div class="CmColorBgh CmMS_Option CmMSelectManuf <?if(in_array($Name,$aFavManuf)){?>CmColorBgL<?}?>" data-type="Manuf" data-option="<?=$Code?>"><?=$Name?></div><?
                                    }?>
                                </div>
                            </div>
                            <?
                            if($MfGroupsCnt>1){$IsDisplayTab='style="display:none;"';}
                        }?>
                    </div>
                </div>
            </div>
            <div class="CmMselBoxWrap">
                <div class="CmMSelectBox">
                    <div class="CmMSelectBut <?=$IsDisabModel?>" id="CmMS_BoxModel"><span id="CmMS_Model" data-value="<?=$sModel?>"><?=$vModel?></span> &#9660;</div>
                    <div class="CmModelSelWrap CmMSelectDown CmMSelectDown<?=$MSelect_Position?>" id="CmMS_DdModel">
                        <?
                        ModelsAjaxStart();
                        echo $ModelsMesg;
                        if(count($aModels)>0){?>
                            <div class="CmMSelectInner">
                                <?foreach($aModels as $ModID=>$ModName){
                                    ?><div class="CmColorBgh CmMS_Option CmMSelectList" data-type="Model" data-option="<?=$ModID?>"><?=$ModName?></div><?
                                }?>
                            </div>
                        <?}
                        ModelsAjaxEnd();
                        ?>
                    </div>
                </div>
                <div class="CmMSelectBox">
                    <div class="CmMSelectBut <?=$IsDisabType?>" id="CmMS_BoxType"><span id="CmMS_Type" data-value="<?=$sType?>"><?=$vType?></span> &#9660;</div>
                    <div class="CmEngineSelWrap CmMSelectDown CmMSelectDown<?=$MSelect_Position?>" id="CmMS_DdType">
                        <?
                        TypesAjaxStart();
                        echo $TypesMesg;
                        if(count($aTypes)>0){//print_r($aTypes);?>
                            <div class="CmMSelectInner">
                                <?foreach($aTypes as $TypID=>$TypName){
                                    ?><div class="CmColorBgh CmMS_Option CmMSelectList" data-type="Type" data-option="<?=$TypID?>"><?=$TypName?></div><?
                                }?>
                            </div>
                        <?}
                        TypesAjaxEnd();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="CmMSelectVehicle CmColorTxi" <?if($vPicture){?>style="background-image:url(<?=$vPicture?>)"<?}?>>
            <?if(!$vPicture){?>
                <?=LangMS_x('Select_vehicle')?>
            <?}else{
                if($DisplayReset){?>
                    <svg class="CmResetSettings CmColorFi CmTitShow" title="<?=LangMS_x('Reset_model')?>" data-urix="<?=PROTOCOL_DOMAIN_x?><?=$_SERVER['REQUEST_URI']?>" viewBox="0 0 475.2 475.2" width="16" height="16"><path d="M405.6,69.6C360.7,24.7,301.1,0,237.6,0s-123.1,24.7-168,69.6S0,174.1,0,237.6s24.7,123.1,69.6,168s104.5,69.6,168,69.6    s123.1-24.7,168-69.6s69.6-104.5,69.6-168S450.5,114.5,405.6,69.6z M386.5,386.5c-39.8,39.8-92.7,61.7-148.9,61.7    s-109.1-21.9-148.9-61.7c-82.1-82.1-82.1-215.7,0-297.8C128.5,48.9,181.4,27,237.6,27s109.1,21.9,148.9,61.7    C468.6,170.8,468.6,304.4,386.5,386.5z"/><path d="M342.3,132.9c-5.3-5.3-13.8-5.3-19.1,0l-85.6,85.6L152,132.9c-5.3-5.3-13.8-5.3-19.1,0c-5.3,5.3-5.3,13.8,0,19.1    l85.6,85.6l-85.6,85.6c-5.3,5.3-5.3,13.8,0,19.1c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4l85.6-85.6l85.6,85.6c2.6,2.6,6.1,4,9.5,4    c3.5,0,6.9-1.3,9.5-4c5.3-5.3,5.3-13.8,0-19.1l-85.4-85.6l85.6-85.6C347.6,146.7,347.6,138.2,342.3,132.9z"/></svg>
                <?}
            }?>
        </div>
	</div>
</div>
<div class="CmMSelectLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div>

<script type="text/javascript">
    function setCookie(key, value, expireDays, expireHours, expireMinutes, expireSeconds) {
        var expireDate = new Date();
        if (expireDays) {
            expireDate.setDate(expireDate.getDate() + expireDays);
        }
        if (expireHours) {
            expireDate.setHours(expireDate.getHours() + expireHours);
        }
        if (expireMinutes) {
            expireDate.setMinutes(expireDate.getMinutes() + expireMinutes);
        }
        if (expireSeconds) {
            expireDate.setSeconds(expireDate.getSeconds() + expireSeconds);
        }
        document.cookie = key +"="+ escape(value) +
            ";domain="+ window.location.hostname +
            ";path=/"+
            ";expires="+expireDate.toUTCString();
    }

    function deleteCookie(name) {
        setCookie(name, "", null , null , null, 1);
    }
    // function delete_cookie( cookie_name ){
    //     alert(document.cookie);
    //     var cookie_date = new Date ( );  // Текущая дата и время
    //     cookie_date.setTime(cookie_date.getTime()-360);
    //     document.cookie = cookie_name + "=; expires=" + cookie_date.toUTCString();
    // }
	$(document).ready(function(){
        console.log(document.cookie);
		/* Open Box */
		$("body").on("click", ".CmMSelectBut", function(){
			if(!$(this).hasClass('CmMSelectDisabled')){
				$('.CmMSelectDown').each(function(i,obj){ $(this).hide(); });
				$(this).next().show();
				$('.CmMSelectBox').each(function(i,obj){ $(this).removeClass('CmMSelectActive'); });
				$(this).addClass('CmMSelectActive');
			}
		});
		/* Option selected */
		$("body").on("click", ".CmMS_Option", function(){
			var Option = $(this).data('option').toString();
			var Type = $(this).data('type');
			var View = $(this).html();
			var View = View.split(' (')[0];
			$('#CmMS_'+Type).data('value',Option).html(View);

			var sYear = $('#CmMS_Year').data('value');
			var sManuf = $('#CmMS_Manuf').data('value');
			var sModel = $('#CmMS_Model').data('value');
			var sType = $('#CmMS_Type').data('value');

			$('.CmMSelectBut').each(function(i,obj){ $(this).removeClass('CmMSelectActive'); });
			$('.CmMSelectDown').each(function(i,obj){ $(this).hide(); });

			if(Type=='Year' && sManuf==''){
				CmMS_ButDd("Manuf");
			}else{
				if(Type=='Type'){
					$('.CmMSelectLoading').appendTo($(".CmMSelectVehicle")).show();
					$('.CmMSelectBut').each(function(i,obj){ $(this).addClass('CmMSelectDisabled'); });
				}else{
					var LoadingAt='';
					if(Type=='Model'){LoadingAt='Type';}else{LoadingAt='Model';}
					$('.CmMSelectLoading').appendTo($("#CmMS_Box"+LoadingAt)).show();
					$('#CmMS_'+LoadingAt).html('');
				}
				var PostData = 'FURL_x=<?=FURL_x?>&MsLang=<?=MsLang?>&sYear='+sYear+'&sManuf='+sManuf+'&sModel='+sModel+'&sType='+sType+'&sSection=<?=$_GET['section_furl']?>';
				
				$.ajax({
					url:'<?=MSELECT_PROCESSOR?>', type:'POST', dataType:'html', data:PostData,
					statusCode:{
						200: function(Res){
							$('.CmMSelectLoading').show(); //alert(Res);
							$(location).attr('href',Res); //Redirect
						},
						201: function(Res){ //Next select Model
							$('#CmMS_DdModel').html(Res);
							$('#CmMS_Model').html('<?=LangMS_x('Model')?>');
							CmMS_ButDd("Model");
						},
						202: function(Res){ //Next select Type
							$('#CmMS_DdType').html(Res);
							$('#CmMS_Type').html('<?=LangMS_x('Engine')?>');
							CmMS_ButDd("Type");
						},
					},
					success: function(Res){
						$('.CmMSelectLoading').hide();
						// alert('Debug: '+Res);
                       console.log(PostData);
					},
				});
			}
		});

		/* Manufacturers Group select */
		$("body").on("click", ".CmMSelectGrTab", function(){
			$('.CmMSelectGrTab').each(function(i,obj){ $(this).removeClass('CmMSelectGrActive CmColorTxi'); });
			$(this).addClass('CmMSelectGrActive CmColorTxi');
			$('.CmMSelectMfTab').each(function(i,obj){ $(this).hide(); });
			var Group = $(this).data('group');
			$('#Cm'+Group+'Tab').show();
		});

		/* Close Box */
		$(document).mousedown(function (e){
			var Event = e;
			$('.CmMSelectBox').each(function(i,obj){
				if($(this).has(Event.target).length === 0){
					$(this).find(".CmMSelectBut").removeClass('CmMSelectActive');
					$(this).find(".CmMSelectDown").hide();
				}
			});
		});

		function CmMS_ButDd(BoxIdName){
			$("#CmMS_Box"+BoxIdName).removeClass('CmMSelectDisabled').addClass('CmMSelectActive');
			$("#CmMS_Dd"+BoxIdName).show();
		}

        //Reset model selector
        var el = document.getElementsByClassName('CmResetSettings');
        for (i=0; i<el.length; i++){
            el[i].onclick = function(){
                deleteCookie('CarModVehicle');
                location.reload(true);
            }
        }
        // $('.CmResetSettings').on('click', function(){
        //     $('#CmSelFormHid').submit();
        // });
	});

</script>
