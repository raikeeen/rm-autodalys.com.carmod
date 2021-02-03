<?VerifyAccess_x('includes');  ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/<?=CM_DIR?>/templates/<?=TEMPLATE_x?>/common.css?v=<?=CM_VERSION?>" type="text/css">
<link rel="stylesheet" href="/<?=CM_DIR?>/templates/<?=TEMPLATE_x?>/custom.css" type="text/css">
<script src="/<?=CM_DIR?>/media/js/common.js?v=<?=CM_VERSION?>"></script>
<link rel="stylesheet" href="/<?=CM_DIR?>/media/js/jquery-ui.css">
<script src="/<?=CM_DIR?>/media/js/jquery-ui.js"></script>
<script src="/<?=CM_DIR?>/media/js/jquery.ui.touch-punch.min.js"></script>
<script src="/<?=CM_DIR?>/templates/<?=TEMPLATE_x?>/common.js?v=<?=CM_VERSION?>"></script>
<script src="/<?=CM_DIR?>/media/snd/ions.min.js"></script>
<script src="/<?=CM_DIR?>/add/mail_order/templates/default/script.js"></script>
<script src="/<?=CM_DIR?>/add/askprice/templates/default/script.js"></script>
<script src="/<?=CM_DIR?>/templates/<?=TEMPLATE_x?>/custom.js"></script>
<script>
// Pop-up window for Ajax inside
function PopupForAjax(){
    $('.fxCont').html('<div class="CmLoading" style="background-image:url(/<?=CM_DIR?>/templates/<?=TEMPLATE_x?>/_images/loading.gif)"></div>');
    $('.fxOverlay').show();
}

jQuery(document).ready(function($){
    var CmsCartID = '<?if(defined('CmsCartID')){echo CmsCartID;}?>';
    var IsCmAdmin = '<?if(IsADMIN_x){echo 'Y';}?>';

    // Add to Cart
    $("body").on("click",".CmAddToCart", function(e){
        e.stopPropagation();
        var ClickedButton = $(this);
        var URL = '<?=PROTOCOL_DOMAIN_x?>'+$(this).data('furl');
        var PriceID = $(this).data('priceid');
        if((PriceID.length==32 || PriceID=='order') && URL.length>6){
            Qty = $(this).parent(".CmQuantBlToCartBl").find(".CmAddToCartQty").val();
            Qty = parseInt(Qty);
            <?if(isset($_REQUEST['CartDebug']) OR $_REQUEST['last']=='?CartDebug'){?>
                alert(URL+'\r\n'+PriceID+'\r\n'+Qty);
                $("<form action='"+URL+"' id='TestCart"+PriceID+"' method='post' target='_blank'>"+
                "<input type='hidden' name='AddToCart' value='"+PriceID+"'/>"+
                "<input type='hidden' name='Qty' value='"+Qty+"'/>"+
                "</form >").appendTo('body'); $("#TestCart"+PriceID+"").submit();
            <?}else{?>
                if(CmsCartID==''){
                    CmAddCartPost(URL, PriceID, Qty); //custom.js
                }else{
                    LoadingToggle('CmContent',1); // Scroll top of body (2-scroll to content box: CmContent)
                    // console.log(URL+' - '+PriceID+' - '+Qty);
//                    var pData = 'AddToCart='+PriceID+'&Qty='+Qty;
//                    ReqFetch(URL, pData)
//                        .then(result => {
//                            CmAfterCartAjax(result, CmsCartID);
//                            LoadingToggle();
//                        });
                    $.ajax({url:URL, type:'POST', dataType:'html', data:{AddToCart:PriceID, Qty:Qty},
                        success: function(Result){
                            var CmCartErrors = '';
                            var aResult = Result.split('|CmCartErrors|');
                            if(aResult.length>1){
                                CmCartErrors = aResult[0];
                                Result = aResult[1];
                            }
                            CmAfterCartAjax(Result, CmsCartID, ClickedButton, CmCartErrors, IsCmAdmin); //custom.js
                            LoadingToggle(); //Not Scroll
                        },
                        error: function(jqXHR, error, errorThrown){
                            if(jqXHR.status&&jqXHR.status==400){
                                alert(jqXHR.responseText);
                            }else{
                                alert('Hmm.. there is problems with "Ajax Add Cart" \r\n'+URL);
                            }
                            LoadingToggle(); //Not Scroll
                        },
                        timeout:30000
                    });
                }
            <?}?>
        }else{
            alert('Wrong PriceID ['+PriceID+'] or URL ['+URL+']');
        }
    });
    $.fn.bounce = function(settings){
        var CssPosition = $(this).css('position');
        if(CssPosition!='absolute'){ $(this).css('position','relative'); }
        if(typeof settings.interval == 'undefined'){settings.interval = 100;}
        if(typeof settings.distance == 'undefined'){settings.distance = 10;}
        if(typeof settings.times == 'undefined'){settings.times = 4;}
        if(typeof settings.complete == 'undefined'){settings.complete = function(){};}
        var StartTop = parseInt($(this).css('top')) || 0;
        var Down = StartTop + settings.distance;
        var Up = StartTop + (settings.distance*-1);
        for(var iter=0; iter<(settings.times+1); iter++){
            $(this).animate({ top:((iter%2 == 0 ? Down : Up )) }, settings.interval);
        }
        $(this).animate({ top:StartTop }, settings.interval, settings.complete );
    };
    ion.sound({
        sounds: [{name:"add"}],
        path:"/<?=CM_DIR?>/media/snd/",
        preload:true,
        volume:1.0,
        multiplay:true,
    });

});
</script>
<style>
/* Text */
	.CmColorTx{color:#<?=COLOR_MAIN?>!important;}
	.CmColorTxi{color:#<?=COLOR_MAIN?>!important;}
	.CmColorTxiA{color:#<?=COLOR_MAIN?>9f!important;}
	.CmColorTxh:hover{color:#<?=COLOR_MAIN?>!important;}
	.CmColorTxD{text-decoration-color:#<?=COLOR_MAIN?>!important;}
/* Background */
	.CmColorBg{background-color:#<?=COLOR_MAIN?>!important;}
	.CmColorBgh:hover{background-color:#<?=COLOR_MAIN?>!important;}
	.CmColorBgA{background-color:#<?=COLOR_MAIN?>1f!important;}
	.CmColorBgAct:active{background-color:#<?=COLOR_MAIN?>1f!important;}
	.CmColorBgL{background-color:#<?=COLOR_LIGHT?>!important;}
  .CmColorBgLh:hover{background-color:#<?=COLOR_LIGHT?>!important;}
	.CmColorFi{fill:#<?=COLOR_MAIN?>!important;}
	/*.CmColorFi svg{fill:#<?=COLOR_MAIN?>!important;}*/
	.CmColorFih:hover{fill:#<?=COLOR_MAIN?>!important;}
/* Border */
	.CmColorBr{border-color:#<?=COLOR_MAIN?>!important;}
	.CmColorBrh:hover{border-color:#<?=COLOR_MAIN?>!important;}
	.CmColorOu{outline-color:#<?=COLOR_MAIN?>!important;}
	.CmColorOuh:hover{outline-color:#<?=COLOR_MAIN?>!important;}
/* Shadow */
	.CmColorSh{--box-shadow-color:#<?=COLOR_MAIN?>!important;}
</style>
<?
function BreadCrumbs_x(){
    global $aCmBCrumbs; //Filled by CarMod controllers
    if(IsADMIN_x){//aprint_x($aCmBCrumbs);
    }
    ?><ul class="CmBreadCrumbs" typeof="BreadcrumbList"><?
    if(is_array($aCmBCrumbs) AND count($aCmBCrumbs)>1){
        $lastKey = key(array_slice($aCmBCrumbs, -1, 1, true));
        array_pop($aCmBCrumbs);
        $pos = 0;
        foreach($aCmBCrumbs as $Crumb=>$Link){ $pos++;
//            if($Crumb){$arrow='<div class="CmCrumbArr" style="background-image:url('.TEMPLDIR_x.'_images/arrow.png)"></div>';}
            if($Link!='' OR $Crumb=='Home'){
                if($Link=='#'){$Href=$Link;}else{$Chain .= $Link.'/'; $Href=$Chain;}
                if($Crumb=='Home'){
                    $Crumb = '<svg class="CmBcHomeIcon CmColorFih" viewBox="0 0 24 24"><path d="M 12 2.0996094 L 1 12 L 4 12 L 4 21 L 10 21 L 10 14 L 14 14 L 14 21 L 20 21 L 20 12 L 23 12 L 12 2.0996094 z"/></svg>';
                    //$Crumb = Lng_x('Catalog',0);
                }
                ?><li class="CmBrCrItem" property="itemListElement" typeof="ListItem">
                    <a property="item" typeof="WebPage" href="<?=$Href?>">
                        <span class="CmColorTxh" property="name"><?=$Crumb?></span>
                    </a>
                    <meta property="position" content="<?=$pos?>">
                </li>
                <svg class="CmBrCrArrow" viewBox="0 0 24 24"><path d="M8.122 24l-4.122-4 8-8-8-8 4.122-4 11.878 12z"/></svg><?
            }else{
                ?><div class="CmCrumbLast"><?=$Crumb?></div><?
            }
        }?>
        <li class="CmLastBcItem" property="itemListElement" typeof="ListItem">
            <span property="item" typeof="WebPage">
                <span class="CmLastItemTxt" property="name"><?=$lastKey?></span>
            </span>
            <meta property="position" content="<?=$pos?>">
        </li>
    <?}
    ?></ul><?
}

function PrintProductAvailable_x($aPrice, $Res=Array()){
    if(HIDE_PRODUCTS_COUNT){
        if($aPrice['AVAILABLE_NUM']>0){
            $col='Y';?><div class="cm_InStock <?if($Res['ACTIVE_TAB']=='TABLE'){echo 'StockTableStyle';}?> CmMargZ">
                <svg class="InStockImg" width="18" height="18" viewBox="0 0 24 24" style="<?if($Res['ACTIVE_TAB']=='TABLE'){echo 'margin:0px;';}?>">
                    <path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z" fill="#FFFFFF"/>
                </svg>
                <span class="InstockTxt <?if($Res['ACTIVE_TAB']=='TABLE'){echo 'StockTableTxt';}?>"><?=Lng_x('Available',1)?></span>
            </div><?
            }else{
                ?><div class="cm_OutOfStock <?if($Res['ACTIVE_TAB']=='TABLE'){echo 'StockTableStyle';}?>">
                    <svg class="OutStockImg" width="14" height="16" viewBox="0 0 24 24" style="<?if($Res['ACTIVE_TAB']=='TABLE'){echo 'margin:0px;';}?>">
                            <path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z" fill="#FFFFFF"/>
                    </svg>
                    <span class="OutstockTxt <?if($Res['ACTIVE_TAB']=='TABLE'){echo 'StockTableTxt';}?>"><?=Lng_x('Not_available',1)?></span>
                </div><?
            }
    }else{?>
        <div class="CmListPrAvail"><?=$aPrice['AVAILABLE_VIEW']?></div>
    <?}
}
?>
