<?VerifyAccess_x('Models.templ');
//Filter - Years groups
if(count($aRes['YEARS_FULL'])>0){
	foreach($aRes['YEARS_FULL'] as $Year){
		$gY = substr($Year,0,3);
		$aY[$gY.'0'][] = $Year;
	}
}
//Template Block setting
$BLOCK = $aSets['TEMPLATE_BLOCK'];
?>

<?php AjaxCut_x(); //Makes: <div id="CmAjaxBox"> ?>

<div class="CmHeadBox">
	<?/*
	<div class="boxInOver" id="sectionBox" style="display: none;">
		<div class="bxIOPosit" style="max-width:500px;">
			<div class="CmTitleBox c_BrTop3px">
				<a href="<?=FURL_x?>"><div class="cmProdLogo" title="<?=$aRes['BRAND_CODE']?>" style="background:url(/<?=CM_DIR?>/media/brands/90/<?=$aRes['BRAND_CODE']?>.png)"></div></a>
				<a href="javascript:void(0)" onclick="jQuery('#sectionBox').fadeIn(400);"><div class="cmH1"><h1 class="c_H1b"><?=H1_x?></h1></div></a>
			</div>
		</div>
	</div>*/?>
    <div class="CmFilterSwitchWrap">
        <div class="CmTitleBox CmColorBr">
            <div class="cmProdLogo" title="<?=$aRes['BRAND_CODE']?>" style="background:url(/<?=CM_DIR?>/media/brands/90/<?=$aRes['BRAND_CODE']?>.png)"></div>
            <?if($aRes['MODELS_COUNT']>12){?>
                <script>ShowFilter=1;</script>
                <div class="fByName">
                    <?/*
                    <div class="fByYear">
                        <div class="fByYearTitle"><?=Lng_x('Year',1)?>:</div>
                        <div id="fByYearSel" class="fByYearSelected c_Tx noselect_x"> - <?=Lng_x('Select')?> &#9660;</div>
                    </div>
                    */?>
                    <div class="fByNameTitle"><?=Lng_x('Filter_by_name',1)?>:</div><div class="cmCB"></div>
                    <div class="fByNameButs">
                        <div class="CmActFB CmColorBgh CmColorBg" href="javascript:void(0)"><?=Lng_x('All')?></div>
                    </div>
                     <?=ShowSEOText_x("TOP")?>
                    <script>var AllLng = '<?=Lng_x('All')?>';</script>
                    <?if($aRes['TOTAL_COUNT']>$aRes['ACTUAL_COUNT']){?>
                        <div class="boxTAuto">
                            <div class="slLeft sliderTg <?if(!isset($_POST['All']) OR (isset($_POST['All']) AND $_POST['All']!='Y')){echo 'activeTg';}?>" selactual="<?if(isset($_POST['All']) AND $_POST['All']=='Y'){?>N<?}?>">
                                <?=Lng_x('Actual');?> <sup><?=$aRes['ACTUAL_COUNT']?></sup>
                            </div>
                            <div class="slRight CmColorTxh sliderTg <?if(isset($_POST['All']) AND $_POST['All']=='Y'){echo 'activeTg';}?>" selactual="<?if(!isset($_POST['All']) OR (isset($_POST['All']) AND $_POST['All']!='Y')){?>Y<?}?>">
                                <?=Lng_x('All');?> <sup><?=$aRes['TOTAL_COUNT']?></sup>
                            </div>
                        </div>
                    <?}?>
                </div>
                <?/*
                <div id="yearBox" class="yearBox mouseMiss" style="display:none;">
                    <table><tbody>
                    <?foreach($aY as $YGroupe=>$aYears){$int++;
                        ?><tr <?$int2=$int/2; if(is_int($int2)){echo 'style="background:#dadada;"';}?>><td class="fYGroupe"><?=$YGroupe?>-x:</td><td><?
                        foreach($aYears as $Year){
                            ?><div class="fYear c_BgHov"><?=$Year?></div><?
                        }
                        ?></td></tr><?
                    }?>
                    </tbody></table>
                </div>
                */?>
            <?}?>
        </div>
        <?//TDMShowBreadCumbs()?>
        
    </div>
</div>
<div class="CmBrTitleSearchWrap CmColorBgL CmColorBr">
    <div id="CmTitlH1Page"><h1 class="CmColorTx"><?=H1_x?></h1></div>
</div>
<?BreadCrumbs_x(); // Edit in: ../templates/default/includes.php ?>
<div class="CmTitleModelBlock">
	<div class="boxMod" data-typespopup="<?=$aSets['TYPES_POPUP']?>">
		<?if($aRes['MODELS_COUNT']>0){
			foreach($aRes['MODELS'] as $aModel){
				//echo '<pre>'; print_r($aModel); echo '</pre><br><br>';
				?><a href="<?=$aModel['FURL']?>" class="ModBox CmColorBrh CmColorTx" data-mname="<?=$aModel['MOD_TITLE']?>" data-yfrom="<?=$aModel['YEAR_START']?>" data-yto="<?=$aModel['YEAR_END']?>" title="<?=$aModel['ID']?>" style="background-image:url(<?=$aModel['IMAGE_PATH']?>);">
					<div class="ModName"><?=$aModel['MOD_TITLE']?><br><?if($aModel['BODY']!=''){?><i><?=$aModel['BODY']?></i><?}?></div>
					<div class="ModYear"><?=$aModel['YEAR_FROM']?>-<?=$aModel['YEAR_TO']?></div>
					<div class="ModVDS"><?=$aModel['VDS']?></div>

					<?/* if(IsADMIN_x){?>
					<div id="Disp<?=$aModel['ID']?>" class="ModDisp <?if($aModel['sDISP']){echo 'ActualMod';}else{echo 'HiddenMod';}?>" data-modid="<?=$aModel['ID']?>"></div>
					<?}?>
					*/?>
				</a>
			<?}
		}else{
			echo Lng_x('No_models',1).'...';
		}?>
	</div>
</div>
<?php AjaxCut_x(); //Makes: </div> ?>
<?aprint_x($aRes, '$aRes');?>
<div class="tclear"></div>
<link rel="stylesheet" type="text/css" href="<?=TEMPLATE_PAGE_DIR?>blocks/<?=$BLOCK?>.css" />

<style>
	.ModDisp{position:absolute; right:10px; bottom:30px; padding:15px; color:#fff;}
	.ModDisp:hover{border:2px solid #ff0000;}
	.ActualMod{background:#8cd686;}
	.HiddenMod{background:#9e9e9e;}
</style>
<script>
var LastMod = '';
jQuery(document).ready(function (){
	$(".ModDisp").on("click","", function(e){
		e.preventDefault(); e.stopPropagation();
		if($(this).hasClass('ActualMod')){var SetTo=0;}else{var SetTo=1;}
		var LastMod = $(this).data('modid');
		var obPostCH = {};
		obPostCH['HeadOff']='Y';
		obPostCH['ModId']=LastMod;
		obPostCH['SetTo']=SetTo;
		LoadingToggle();
		$.post("<?=$_SERVER['REQUEST_URI']?>", obPostCH, function(ResCH){
			if(ResCH=='CHANGED'){
				$("#Disp"+LastMod).toggleClass('ActualMod').toggleClass('HiddenMod');
			}else{
				alert(ResCH);
			}
			LoadingToggle();
		});
	});
});
</script>
<?=ShowSEOText_x("BOT")?>
