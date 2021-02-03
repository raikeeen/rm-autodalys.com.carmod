<?if (!defined("CM_PROLOG_INCLUDED") || CM_PROLOG_INCLUDED !== true) {die('Restricted:car_info.templ');}
// $aRes - is incoming data array from controller

$SchemeSVG = '<svg x="0px" y="0px" width="14px" height="14px" viewBox="0 0 488.3 488.3">
<path d="M314.25,85.4h-227c-21.3,0-38.6,17.3-38.6,38.6v325.7c0,21.3,17.3,38.6,38.6,38.6h227c21.3,0,38.6-17.3,38.6-38.6V124
			C352.75,102.7,335.45,85.4,314.25,85.4z M325.75,449.6c0,6.4-5.2,11.6-11.6,11.6h-227c-6.4,0-11.6-5.2-11.6-11.6V124
			c0-6.4,5.2-11.6,11.6-11.6h227c6.4,0,11.6,5.2,11.6,11.6V449.6z"/>
		<path d="M401.05,0h-227c-21.3,0-38.6,17.3-38.6,38.6c0,7.5,6,13.5,13.5,13.5s13.5-6,13.5-13.5c0-6.4,5.2-11.6,11.6-11.6h227
			c6.4,0,11.6,5.2,11.6,11.6v325.7c0,6.4-5.2,11.6-11.6,11.6c-7.5,0-13.5,6-13.5,13.5s6,13.5,13.5,13.5c21.3,0,38.6-17.3,38.6-38.6
			V38.6C439.65,17.3,422.35,0,401.05,0z"/>
</svg>';
?>
<div class="CmTopBox">
    <div class="CmHeadTitleWrapBlock">
        <div class="CmTitleBradWrap">
            <div class="CmTitleBox">
                <div class="CmHeadSecPicture" style="background-image:url(/<?=CM_DIR?>/media/brands/90/<?=$aRes['BRAND_CODE']?>.png)"></div>
                <div class="CmHeadTextLim CmColorTxi"><?=$aRes['BRAND_NAME']?> <?=$aRes['MODEL_NAME']?><br><span><?=$aRes['TYPE_NAME']?>, <?=$aRes['TYPE_YEARS']?></span></div>
                <div class="CmClrb"></div>
            </div>
        </div>
        <div class="CmMSelectBlock  CmMSelectPositionRight"><?
            $Selector_Template = 'default';
            include_once(PATH_x.'/add/selector/controller.php');?>
        </div>
    </div>

</div>
<div class="CmBrTitleSearchWrap CmColorBgL CmColorBr">
    <div id="CmTitlH1Page"><h1 class="CmColorTx"><?=H1_x?></h1></div>
</div>
<div class="CmBreadCrSearch">
    <?BreadCrumbs_x(); // Edit in: ../templates/default/includes.php ?>
    <div class="CmBrSearchWrap">
	    <div class="CmTitleSearchBlock">
	        <?if(count($aRes['SECTIONS'])>6){?>
	            <div class="CmSearcSectInput">
	                <div><input class="CmInputSect CmColorBr CmColorTx" data-lng="<?=$aLngCode?>" type="text" placeholder="<?=Lng_x('Find product section',0)?>.."></div>
	                <div class="CmClearButt CmColorBgh">
	                    <svg class="material-icon" viewBox="0 1.5 24 24">
	                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
	                    </svg>
	                </div>
	            </div>
	        <?}?>
	    </div>
	    <?if($aRes['SCHEMES_COUNT']){?>
	        <div class="CmSchAllCount"><?=$SchemeSVG?> <?=Lng_x('OE_Schemes',1)?>: <?=$aRes['SCHEMES_COUNT']?></div>
	    <?}?>
	</div>
</div>
<div class="CmSearchSectInf">
	<link rel="stylesheet" href="<?=TEMPLDIR_x?>main_page/style.css">
	<div class="CmOverlaySpecs"><div class="CmModalSpecs"><div class="CmContSpecs"></div></div></div>
	<div class="CmSpecsBl">
		<div class="CmSpecs">
			<div class="CmInfoH1Block">
				<h1 class="c_H1b"><?=H1_x?></h1>
				<?if($aRes['MODEL_IMAGE']!=''){?>
					<div class="cm_ModImg" style="background-image:url(<?=$aRes['MODEL_IMAGE']?>)"></div>
				<?}?>
			</div>
			<?foreach($aRes['SPEC'] as $aSpecs){?>
				<div class="spec_name">
					<div class="sp_name CmColorBgh"><span>&#9660;</span><?=$aSpecs['NAME']?></div>
                    <table class="tab_spec CmSubSpec">
	                    <?foreach($aSpecs['SUB'] as $aSpItem){?>
	                        <tr style="background:#dadada;"><td></td><td class="sub_name" colspan="2"><?=$aSpItem['NAME']?>:</td></tr>
	                        <?foreach($aSpItem['ITEMS'] as $aSpName){?>
	                            <tr class="sub_tr">
	                            <td class="cm_Spval CmColorTx"><?=$aSpName['VALUE']?></td>
	                            <td class="cm_Spnam">
	                                <?=$aSpName['NAME']?>
	                                <?if($aSpName['ADDITIONAL_TEXT']){?><i>(<?=$aSpName['ADDITIONAL_TEXT']?>)</i><?}?>
	                                <?if($aSpName['QCOL_TXT']){?><i>[<?=$aSpName['QCOL_TXT']?>]</i><?}?>
	                            </td>
	                            <?if($aSpName['IMG']!=''){?>
	                                <td class="cm_Spimg" data-img="<?=$aSpName['IMG']?>"></td>
	                            <?}?>
	                            </tr>
	                        <?}?>
	                    <?}?>
                    </table>
				</div>
			<?}?>
		</div>
		<div class="CmOverlayImg"><div class="CmModalImg"><div class="CmContImg"></div></div></div>
	</div>
	<div class="boxSections_x">
	    <div class="CmSpecsBlockWrap CmColorBgL <?if(IsFullArray_x($aRes['SPEC'])){?>CmSpecsFullInfo<?}?> <?if($aRes['FAST_SPEC_COUNT']<=8&&IsFullArray_x($aRes['SPEC'])){?>gridRow1_3<?}elseif($aRes['FAST_SPEC_COUNT']>8&&IsFullArray_x($aRes['SPEC'])){?>gridRow1_4<?}?>">
			<div class="CmSpecsListBl">
				<div class="CmSpecRowBlock CmColorBgL">
	                <div class="CmModName"><?=Lng_x('Type',1)?>:</div>
	                <div class="CmModValue"><b class="CmColorTx an_mod" data-href="<?=$aRes['TYPES_FURL']?>" title="<?=Lng_x('Model_modifications')?>"><?=Lng_x($aRes['TYPE_BODY'])?> &#9658;</b> <?=$aRes['TYPE_YEARS_SHORT']?> <?if($aRes['TYPE_VDS']!=''){?>(<?=$aRes['TYPE_VDS']?>)<?}?><?if($aRes['TYPE_KBA']!=''){?><br>KBA: <?=$aRes['TYPE_KBA']?><?}?></div>
	            </div>
				<?if($aRes['TYPE_LITRE']!='0' OR $aRes['TYPE_CUBTEC']>0 OR $aRes['TYPE_ENGINES']){?>
					<div class="CmSpecRowBlock CmColorBgL">
						<div class="CmModName"><?=Lng_x('Engine',1)?>:</div>
						<div class="CmModValue">
							<?if($aRes['VEHICLE_TYPE']=='COM'){?><b><?=$aRes['TYPE_CUBTEC']?></b> <?=Lng_x('sm',1)?><?}else{?><b class="CmColorTx"><?=$aRes['TYPE_LITRE']?></b><?}?>
							<?if($aRes['TYPE_ENGINES']){?>(<?=$aRes['TYPE_ENGINES']?>)<?}?>
						</div>
					</div>
				<?}?>
				<div class="CmSpecRowBlock CmColorBgL">
	                <div class="CmModName"><?=Lng_x('Power',1)?>:</div>
	                <div class="CmModValue"><b><?=$aRes['TYPE_KW']?></b> <?=Lng_x('Kv',1)?> / <b><?=$aRes['TYPE_HP']?></b> <?=Lng_x('Hp',1)?></div>
	            </div>
				<?// PASSANGER / MOTORBIKE
				if($aRes['VEHICLE_TYPE']=='PAS' OR $aRes['VEHICLE_TYPE']=='MOT'){?>
					<?if($aRes['TYPE_CUBTEC'] OR $aRes['TYPE_CUBTAX']){?>
						<div class="CmSpecRowBlock CmColorBgL">
	                        <div class="CmModName"><?=Lng_x('Capacity',1)?>:</div>
	                        <div class="CmModValue">
							<?if($aRes['TYPE_CUBTEC']>0){?><b title="Technical"><?=$aRes['TYPE_CUBTEC']?></b> <?=Lng_x('sm',1)?></b><?}?>
							<?if($aRes['TYPE_CUBTAX']>0){?><i title="Tax">(<?=$aRes['TYPE_CUBTAX']?> <?=Lng_x('sm',1)?>)</i><?}?>
							<?if($aRes['TYPE_ENGINE_TYPE']){?>
								<b title="<?=$aRes['TYPE_MIXTURE']?>"><?=$aRes['TYPE_ENGINE_TYPE']?></b>
							<?}?></div>
	                    </div>
					<?}?>
					<div class="CmSpecRowBlock CmColorBgL">
						<div class="CmModName"><?=Lng_x('Drive',1)?>:</div>
						<div class="CmModValue"><b class="CmColorTx an_mod" data-href="<?=$aRes['TYPES_FURL']?>" title="<?=Lng_x('Model_modifications')?>"><?=Lng_x($aRes['TYPE_DRIVE'])?> &#9658;</b></div>
					</div>
					<?if($aRes['TYPE_GEAR']!=''){?>
						<div class="CmSpecRowBlock CmColorBgL">
	                        <div class="CmModName"><?=Lng_x('Transmission')?>:</div>
	                        <div class="CmModValue"><?=Lng_x($aRes['TYPE_GEAR'])?></div>
	                    </div>
					<?}?>
					<div class="CmSpecRowBlock CmColorBgL">
	                    <div class="CmModName"><?=Lng_x('Fuel',1)?>:</div>
	                    <div class="CmColorTx CmModValue"><?=Lng_x($aRes['TYPE_FUEL'],1)?></div>
	                </div>
				<?// COMMERCIAL
				}else{
					if($aRes['TYPE_AXLE']){?>
						<div class="CmSpecRowBlock CmColorBgL">
	                        <div class="CmModName"><?=Lng_x('Axle',1)?>:</div>
							<div class="CmModValue"><b class="CmColorTx an_mod" data-href="<?=$aRes['TYPES_FURL']?>" title="<?=Lng_x('Model_modifications')?>"><?=$aRes['TYPE_AXLE']?> &#9658;</b></div>
						</div>
					<?}?>
					<div class="CmSpecRowBlock CmColorBgL">
	                    <div class="CmModName"><?=Lng_x('Chassis',1)?>:</div>
						<div class="CmModValue">
							<?=$aRes['TYPE_WHREELBASE']?>
							<?if($aRes['TYPE_TONNAGE']){?>(<?=Lng_x('Tonnage',1)?>: <?=$aRes['TYPE_TONNAGE']?>)<?}?>
						</div>
					</div>
				<?}?>
	<!--			<tr class="CmColorBgL"><td></td><td></td></tr>-->

				<?if(IsFullArray_x($aRes['SPEC'])){?>
					<div class="CmSpecRowBlock CmHeadTitleSpec">
	<!--                    <div></div>-->
	                    <div class="specs_head"><?=Lng_x('Specifications',1)?>:</div>
	                </div>
	<!--				<tr><td></td><td></td></tr>-->
					<?foreach($aRes['FAST_SPEC'] as $aFSpec){?>
						<div class="CmSpecRowBlock specs_tr">
	                        <div class="val CmColorTx"><?=$aFSpec['Value']?></div>
	                        <div class="CmSpecNameVal"><?=$aFSpec['Name']?></div>
	                    </div>
					<?}?>
				<?}?>
			</div>
			<?if($aRes['FAST_SPEC_COUNT']<=8){$SpHeight=356;}else{$SpHeight=533;}
			if($aRes['SPEC_COUNT']<=0){$SpHeight=0;}
			?>
			<div class="hid_text" data-height="<?=$SpHeight?>"><div class="more_specs CmColorTx"><?=Lng_x('More_information',1)?> (<?=$aRes['SPEC_COUNT']?>)&#8194;&#9658;</div></div>
		</div>
		<?if(count($aRes['SECTIONS'])<=0){?>
			<div class="CmMesNoProd">
	            <div class="CmNoProdWarn CmColorTx">!</div>
	            <div class="CmNoProdText">
	                <?=Lng_x('No_products_for',1)?>:<br>
	                <span class="CmBrandModName"><?=UWord_x($aRes['BRAND_CODE'])?>  <?=$aRes['MODEL_NAME']?> <i><?=$aRes['TYPE_NAME']?><i>, <?=$aRes['TYPE_YEARS']?></span><br>
	                <span class="an_mod" data-href="<?=$aRes['TYPES_FURL']?>"><?=Lng_x('Try_choose_another_modification')?> ►</span>
	            </div>
			</div>
		<?}else{
			foreach($aRes['SECTIONS'] as $aRoot){$ctnSect++;?>
				<div class="boxSect_x CmSectionBox CmColorBrh">
					<div class="boxOverLSect">
						<div class="CmListSectBl">
							<div class="nameSect_x CmColorTx f_title" ><?=$aRoot['NAME']?></div>
							<?$ctnChild=0;
							if(isset($aRoot['CHILDS'])){?>
								<ul class="CmSubcatList">
									<?foreach($aRoot['CHILDS'] as $a){ $ctnChild++;?>
										<li class="sh_list f_list" title="<?=$a['NAME']?>">
											<a class="sCmSubLink CmColorTx" href="<?=$a['LINK']?>/">
												<?if($aRoot['SchemeCount']){?>
													<div class="CmSchemePick CmColorFi CmTitShow" title="<?=Lng_x('OE_Schemes',0)?>: <?=$a['SchemeCount']?>"><?if($a['SchemeCount']){?><?=$SchemeSVG?><?}?></div>
												<?}?>
												<?=$a['NAME']?>
											</a>
										</li>
										<?if($ctnChild>5 AND count($aRoot['CHILDS'])>7){break;}?>
									<?}?>
									<?if(count($aRoot['CHILDS'])>6 AND count($aRoot['CHILDS'])!=7){?>
										<li class="showAllSect CmShowMore" showLNext="sectL<?=$ctnSect;?>"><?=Lng_x('All_sections')?> <span>&#9660;</span></li>
									<?}?>
								</ul>
							<?}?>
						</div>
						<div id="sectL<?=$ctnSect?>" class="CmListNSectBl CmColorBr" style="display:none;">
							<ul>
								<?$ctnC=0;
								foreach($aRoot['CHILDS'] as $a){ $ctnC++;?>
									<?if($ctnC >6){?>
										<li class="hi_list f_Hlist" title="<?=$a['NAME']?>">
											<a class="CmSubLink CmColorTx" href="<?=$a['LINK']?>/">
												<?if($aRoot['SchemeCount']){?><div class="CmSchemePick CmColorFi CmTitShow" title="<?=Lng_x('OE_Schemes',0)?>: <?=$a['SchemeCount']?>"><?if($a['SchemeCount']){?><?=$SchemeSVG?><?}?></div><?}?>
												<?=$a['NAME']?>
											</a>
										</li>
									<?}?>
								<?}?>
							</ul>
							<div class="hideAllSect CmColorTxhi">&#9650;</div>
						</div>
					</div>
					<div class="CmSectImgBL CmSec_<?=$aRoot['NOD']?>"></div>
				</div>
			<?}
		}?>
		<div class="tclear"></div>
	</div>
</div>
<?if($aSets['NARROW_DESIGN']){?>
	<link rel="stylesheet" type="text/css" href="<?=TEMPLATE_PAGE_DIR?>blocks/mini.css" />
<?}?>
<br/>
<?aprint_x($aRes, 'aRes')?>
<?=ShowSEOText_x("BOT")?>
