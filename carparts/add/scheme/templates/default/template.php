<?
$WidthPercent = 20; //%
$HeightRatio = round(($Height*$WidthPercent)/$Width);
?>
<div class="CmSchemeGridWrap" data-width="<?=$Width?>" data-height="<?=$Height?>">
    <div id="CmZoomImg" class="CmSchemeBlockWrap"> <!--style="width:<?=$WidthPercent?>%;"-->
        <div class="CmSchPicture" id="CmPicBlock">
            <div class="CmLoadWrap"><div class="CmSchLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>
            <img class="schemImage" src="<?=$PicSrc?>" alt="Scheme">
            <?foreach($aCoords as $Num=>$aCo){
                ?><div class="CmSchCoord CmColorBgA" style="left:<?=$aCo['X']?>%; width:<?if(strlen($Num)>4){echo $aCo['W']+2.5;}else{echo $aCo['W'];}?>%; top:<?=$aCo['Y']?>%; height:<?if(strlen($Num)>4){echo $aCo['H']+2.5;}else{echo $aCo['H'];}?>%;" data-num="<?=$Num?>"></div><?
            }?>
        </div>
    </div>
    <div class="CmSchOEMs">
        <div class="CmSchPicName CmColorTxi"><?=$PicName?></div>
        <div class="CmSchTab">
            <div class="CmSchTabHead"><div class="CmSchEmpBlock"></div><div class="CmSchNameOeNum">OE / Name</div></div>
            <?foreach($aArtOEs as $Num=>$a){
                ?><a class="CmSchLink CmColorBgLh" href="<?=$a['Link']?>" data-num1="<?=$Num?>">
                    <div class="CmSchNum"><?=$Num?>.</div>
                    <?if($a['Link']){?>
                        <div class="CmSchLinkBl CmColorBgLh" href="<?=$a['Link']?>" data-num1="<?=$Num?>">
                            <span class="CmSchOeName">
                                <b class="CmOeArtNum"><?=$a['OE']?></b>
                                <span class="CmColorTx CmOeNameTxt"><?=$a['Name']?></span>
                            </span>
                            <?if($a['Qnt']!=''){?><div class="CmSchQnt"><?=$a['Qnt']?> qnt.</div><?}?>
                        </div>
                    <?}else{?>
                        <div class="CmSchLinkBl" data-num1="<?=$Num?>">
                            <span class="CmColorTx"><?=$a['Name']?></span>
                        </div>
                    <?}?>
                </a><?
            }?>
        </div>
    </div>
</div>
<div class="CmClrb"></div>
<?/* if(IsADMIN_x){?>PicID: <?=$PicID?><br><?} */?>
<script>
    var windWidth = $(window).width();
    var container = $('.CmSchTab');
    $('body').on('click','.CmSchCoord',function(){
        var num = $(this).data('num');
        $(this).removeClass('CmColorBgA');
        $(this).addClass('CmColorBg CmSchCoordFontS').html(num);
        $('.CmSchLink').each(function(){
            if($(this).data('num1')===num){
                $(this).removeClass("CmColorBg").delay(50).queue(function(){
                    $(this).addClass("CmColorBg").dequeue();}).delay(50).queue(function(){
                    $(this).removeClass("CmColorBg").dequeue();}).delay(50).queue(function(){
                    $(this).addClass("CmColorBg").dequeue();
                });
                var scrollTo = $(this);
                container.animate({
                    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
                },500);
            // return false;
            }else{
                $(this).removeClass('CmColorBg CmSchLinkWhite');
                $(this).find('span').removeClass('CmSchLinkWhite')
            }
        });
       $('body').unbind('mouseleave');
    });

    $('body').on('mouseenter','.CmSchLink',function(){
        var num = $(this).data('num1');
        $('.CmSchCoord').each(function(){
            if($(this).data('num')===num){
                $(this).addClass('CmSchCoordFontS CmSchOutline');
            }else{
                $(this).removeClass('CmSchCoordFontS CmSchOutline');
            }
        });
    });
    $('body').on('mouseleave','.CmSchLink',function(){
        $('.CmSchCoord').removeClass('CmSchOutline').addClass('CmColorBgA').html('');
    });

    $('body').on('mouseleave','.CmSchCoord',function(){
        $(this).addClass('CmColorBgA');
        $(this).removeClass('CmColorBg').html('');
        $('.CmSchLink').removeClass('CmColorBg CmSchLinkWhite').find('span').removeClass('CmSchLinkWhite');
    });
    $('body').on('mouseenter','.CmSchCoord',function(){
        $('body').bind('mouseleave');
        var num = $(this).data('num');
        $('.CmSchCoord').each(function(){
            $(this).removeClass('CmColorBg CmSchCoordFontS').addClass('CmColorBgA').html('');
        });
        $(this).removeClass('CmColorBgA');
        $(this).addClass('CmColorBg CmSchCoordFontS').html('<span>'+num+'</span>');
        $('.CmSchLink').each(function(){
            if($(this).data('num1')===num){
                $(this).addClass('CmColorBg CmSchLinkWhite');
                $(this).find('.CmOeNameTxt, .CmOeArtNum').addClass('CmSchLinkWhite');
            }else{
                $(this).removeClass('CmColorBg CmSchLinkWhite');
                $(this).find('.CmOeNameTxt, .CmOeArtNum').removeClass('CmSchLinkWhite');
            }
        });
    });

</script>
<style>
.fxCont{background:#ffffff; overflow:hidden;}
.CmSchemeGridWrap{display:flex; justify-content:space-between; align-items:flex-start; width:100%; height:100%;}
.CmSchPicture{display:flex; justify-content:center; position:relative;}
.CmSchPicture img{align-self:flex-start; background-color:#ffffff; width:100%;}
.CmSchCoord{position:absolute; cursor:pointer; z-index:999;}
.CmSchCoordFontS{font-size:100%; color:#ffffff; display:flex; padding:1px 3px;}
.CmSchCoordFontS span{margin:auto;}
.CmSchOutline{outline:2px solid #ff0000;}
.CmSchemeBlockWrap{width:100%; height:100%; display:flex; justify-content:center; align-items:center; align-self: flex-start; overflow:auto;}
.CmSchOEMs{display:flex; flex-direction:column; align-items:stretch; flex-basis:30%;}
.CmSchTab{max-height:550px; overflow-y:auto; overflow-x:hidden; display:flex; flex-direction:column; align-items:flex-start; height:auto;}
.CmSchLink{font-size:11px; display:flex; justify-content:flex-start; align-items:center; padding:3px 6px 3px 6px; margin:0px; color:#afafaf; border-bottom:1px solid #c1c1c1; width:100%;}
.CmSchLink b{font-size:11px; font-family:Calibri; color:#808080;}
.CmSchLinkWhite{color:#ffffff !important;}
.CmSchLinkBl{display:flex; justify-content:space-between; align-items:center; width:100%; padding:0px 5px;}
.CmSchQnt{float:right; font-size:11px;}
.CmSchNum{font-size:12px; text-align:left;}
.CmSchPicName{font-weight:bold; text-align:left;}
.CmOeNameTxt{text-align:left; white-space:nowrap;}
.CmSchTabHead{ display:flex; justify-content:flex-start; align-items:center;}
.CmSchOeName{display:flex; flex-direction:column; align-items:flex-start;}
.CmSchEmpBlock{width:70px;}
.CmSchNameOeNum{color:#afafaf;}
.schemImage{z-index:999;}

/*LARGE SCHEMAS LOADING*/
.CmLoadWrap{position:absolute; left:0; top:0; width:100%; height:100%; background-color:#ffffff; z-index:990; display: none; justify-content:center; align-items:center;}
.CmSchLoading {display:inline-block; position:relative; width:64px; height:64px;
}
.CmSchLoading div { position: absolute; width: 5px; height: 5px; border-radius: 50%; animation: lds-default 1.2s linear infinite;
}
.CmSchLoading div:nth-child(1) { animation-delay: 0s; top: 29px; left: 53px;
}
.CmSchLoading div:nth-child(2) { animation-delay: -0.1s; top: 18px; left: 50px;
}
.CmSchLoading div:nth-child(3) { animation-delay: -0.2s; top: 9px; left: 41px;
}
.CmSchLoading div:nth-child(4) { animation-delay: -0.3s; top: 6px; left: 29px;
}
.CmSchLoading div:nth-child(5) { animation-delay: -0.4s; top: 9px; left: 18px;
}
.CmSchLoading div:nth-child(6) { animation-delay: -0.5s; top: 18px; left: 9px;
}
.CmSchLoading div:nth-child(7) { animation-delay: -0.6s; top: 29px; left: 6px;
}
.CmSchLoading div:nth-child(8) { animation-delay: -0.7s; top: 41px; left: 9px;
}
.CmSchLoading div:nth-child(9) { animation-delay: -0.8s; top: 50px; left: 18px;
}
.CmSchLoading div:nth-child(10) { animation-delay: -0.9s; top: 53px; left: 29px;
}
.CmSchLoading div:nth-child(11) { animation-delay: -1s; top: 50px; left: 41px;
}
.CmSchLoading div:nth-child(12) { animation-delay: -1.1s; top: 41px; left: 50px;
}
@keyframes lds-default {
  0%, 20%, 80%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.5);
  }
}
@media screen and (min-width:1367px) {
    .CmSchemeBlockWrap{align-self:center;}
}
@media screen and (max-width:900px) {
    .CmSchemeGridWrap{flex-direction:column;}
    .CmSchTab{height:250px;}
}
@media screen and (max-width:960px) {
    .CmSchCoordFontS{font-size:70%;}
    .CmSchemeBlockWrap{align-self:center;}
}
@media screen and (max-width:768px){
    .fxCont{flex-direction:column;}
    .CmSchPicture{align-self:flex-start;}
    .schemImage{width:auto; height:auto;}
    .CmSchemeBlockWrap{margin-bottom:20px;}
    .fxCont{padding:40px 10px 10px 10px !important;}
}
@media screen and (min-width:320px) and (max-width:480px){
    .CmSchCoordFontS{font-size:50%;}
    .CmSchTab{max-height:350px;}
    .CmSchemeGridWrap{flex-direction:column; align-items:center;}
}
</style>
