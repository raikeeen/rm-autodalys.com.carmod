<?VerifyAccess_x('ProductListMain.templ');?><?
//$aRes - Controller Result array with all data
//$aSets - Page Settings array (defined at admin side: settings)
//TEMPLATE_PAGE_DIR - php Constant that contain current page template folder path

// Image ratio
// function calcAspectRatioProdList($srcWidth, $srcHeight, $maxWidth, $maxHeight) {
//     $ratioSize = [$maxWidth / $srcWidth, $maxHeight / $srcHeight];
//     $ratioSize = min($ratioSize[0], $ratioSize[1]);
//     $widthHeight['width'] = $srcWidth*$ratioSize;
//     $widthHeight['height'] .= $srcHeight*$ratioSize;
//     return $widthHeight;
// }


if(!isset($aRes['HIDE_HEADBOX'])){?>
	<div class="CmTopBox">
        <div class="CmHeadTitleWrapBlock">
            <div class="CmTitleBox">
                <div class="CmHeadSecPicture" style="background-image:url(<?=TEMPLDIR_x?>_images/sections/<?=$aRes['SECTION_PARENT_NOD']?>.png)"></div>
                <div class="CmHeadTextLim CmColorTxi"><?=$aRes['SECTION_PARENT_NAME']?><br><span><?=$aRes['SECTION_NAME']?></span></div>
                <div class="CmClrb"></div>
            </div>
            <?$MSelect_Position='Right'; //Left?>
            <div class="CmMSelectBlock CmMSelectPosition<?=$MSelect_Position?>">
                <?$Selector_Template = 'default';
                include_once(PATH_x.'/add/selector/controller.php');?>
            </div>
        </div>
    </div>
<?}?>
<?php AjaxCut_x();
if(!isset($aRes['HIDE_HEADBOX'])){?> <!--Makes: <div id="CmAjaxBox">  -->
    <div class="CmBrTitleSearchWrap CmColorBgL CmColorBr">
        <div id="CmTitlH1Page"><h1 class="CmColorTx"><?=H1_x?></h1></div>
    </div>
<?}?>
<?
if($aRes['CONTROLLER']){
    BreadCrumbs_x(); // Edit in: ../templates/default/includes.php
}

//Include SVG
include('svg.php');
?>
<script>
    //PRICE RANGE
    PriceRange();
</script>
<div class="CmWrapFlexBlock">
	<?=ShowSEOText_x("TOP")?>
    <div class="CmFiltersMainP">
        <?if($aRes['HAVE_FILTERS']){?>
            <div class="left_fil <?if($aRes['ACTIVE_TAB']=='LIST'){echo 'CmColorBr CmLeftFilBor';}?>">
				<div class="CmTitleCloseWrap">
					<span class="CmTitleMobFilt CmColorTx"><?=Lng_x('Filters')?></span>
	                <div class="CmHideFiltersBlock CmColorFi">
	                    <svg viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"/></svg>
	                </div>
				</div>
                <?if(!$aRes['FILTER_SECTIONS']){
                    if(isset($aRes['NB_SECTIONS']) AND count($aRes['NB_SECTIONS'])>0){?>
                        <div class="CmLv2Sections">
                            <?foreach($aRes['NB_SECTIONS'] as $aNbS){?>
                                <a href="<?=$aNbS['FURL']?>" class="CmLv2Link CmColorBgh CmColorTx <?if($aNbS['ACTIVE']){?>CmColorBg CmLinkWhCol<?}?>"><?=$aNbS['NAME']?></a>
                            <?}?>
                        </div><?
                    }
                }else{?>
                    <div class="cm_hidNamebl CmColorBgL CmColorBr">
                        <span class="cm_sectName"><span class="cm_sectArrow"></span><span class="cm_sectNametext CmColorTx"><?=$aRes['SECTION_NAME']?></span></span>
						<?if(isset($aRes['NB_SECTIONS']) && count($aRes['NB_SECTIONS'])>0){?>
	                        <div class="cm_Namebl">
	                            <?foreach($aRes['NB_SECTIONS'] as $aVal){?>
	                                <a style="color:#ffffff;" href="<?=$aVal['FURL']?>">
	                                    <div class="hidName CmColorBgh">
	                                        <?=$aVal['NAME']?>
	                                    </div>
	                                </a>
	                            <?}?>
	                        </div>
						<?}?>
                    </div>
                <?}?>

                <?if($aRes['FILTER_SECTIONS']){?>
                    <div class="cm_FsBlock">
                        <?foreach($aRes['FILTER_SECTIONS'] as $Code=>$a){?>
                            <a href="<?=$a['Link']?>" class="S3<?=$a['isActive']?> <?if(!isset($a['isActive'])){?>FilterSection<?}?> PickSection S3<?=$a['isHaveProducts']?> <?if($a['isActive']){echo 'CmColorBg CmBackgrNone';}?>" data-code="<?=$Code?>" >
								<div class="CmDowmNameBl">
	                                <?if($a['SubSections']){?>
	                                    <span class="Down<?=$a['isActive']?>">&#9660;</span> <?
	                                }else{?>
										<div class="CmEmpbl"></div>
									<?}?>
	                                <div class="CmSectNameTitl"><?=$a['Name']?></div>
								</div>
                                <?if(!$a['isActive']){?>
                                    <sup class="cmTitShow" title="<?=Lng_x('Products_count',0)?>"><?=$a['ProductsCount']?></sup>
                                <?}
                                if($a['SubSections']){?>
                            </a>
                                <div class="FilterSubSections SupParent<?=$a['isActive']?>">
                                    <?foreach($a['SubSections'] as $SubCode=>$aSubSection){
                                        if($aSubSection['isActive']=='Active'){$SubClass='c_Txi';}else{$SubClass='';}?>
                                        <div class="FilterSubSection CmColorBgLh PickSection S4<?=$aSubSection['isActive']?> S4<?=$aSubSection['isHaveProducts']?> <?=$SubClass?>" data-code="<?=$SubCode?>" >
											<div class="CmSubSectName"><?=$aSubSection['Name']?></div>
											<sup class="cmTitShow" title="<?=Lng_x('Products_count',0)?>"><?=$aSubSection['ProductsCount']?></sup>
										</div><?
                                    }?>
                                </div>
                            <?}else{echo '</a>';}
                        }?>
                    </div>
                <?}?>
                <div class="CmWrapLpartBlock">
					<?if($aRes['FILTER_OEM_ANALOG']){?>
						<div class="l_part">
							<div class="l_title CmColorTx"><?=Lng_x('Show_only',1)?></div>
							<hr class="titleSet CmColorBr">
							<div class="CmTumblers">
								<?if($aRes['Articles_OEM_Count']){?>
									<a <?=$aRes['Articles_OEM_Href']?> class="CmColorTxh CmTumButn CmTum<?=$aRes['Articles_OEM_Pushed']?>" >
										<span>OEM</span>
										<span class="CmItemCount"><?=$aRes['Articles_OEM_Count']?></span>
									</a>
								<?}?>
								<?if($aRes['Articles_ANALOG_Count']){?>
									<a <?=$aRes['Articles_ANALOG_Href']?> class="CmColorTxh CmTumButn CmTum<?=$aRes['Articles_ANALOG_Pushed']?>" >
										<span><?=Lng_x('Analogs')?></span>
										<span class="CmItemCount"><?=$aRes['Articles_ANALOG_Count']?></span>
									</a>
								<?}?>
								<?if($aRes['Articles_ALL_Count']){?>
									<a <?=$aRes['Articles_ALL_Href']?> class="CmColorTxh CmTumButn CmTum<?=$aRes['Articles_ALL_Pushed']?>">
										<span><?=Lng_x('All')?></span>
										<span class="CmItemCount"><?=$aRes['Articles_ALL_Count']?></span>
									</a>
								<?}?>
							</div>
						</div>
					<?}?>
					<?if($aRes['FILTER_FITPOSITION']){?>
						<div class="CmSelCarSideWrap">
							<div class="CmSideTitle">
								<div class="l_title CmColorTx"><?=Lng_x('Installation side',1)?></div>
								<hr class="titleSet CmColorBr">
							</div>
							<div class="CmInstSideBlock">
								<div class="CmCarFrRR">
									<?if($aRes['FILTER_FitPos_FrontRear']){?>
										<a class="CmSelectCarSide CmFrontBl CmSelSide<?=$aRes['FILTER_FitPos_Front_Pushed']?> CmFrRr" href="<?=$aRes['FILTER_FitPos_Link_Front']?>" >
											<span class="CmCarSideTxt CmSelTxt<?=$aRes['FILTER_FitPos_Front_Pushed']?>"><?=Lng_x('Front')?></span>
											<svg class="CmCarSide CmSelSvg<?=$aRes['FILTER_FitPos_Front_Pushed']?>" viewBox="-3 32 40.967 36.800"><?=$aListSVG['CarFront']?></svg>
										</a>
									<?}?>
									<?if($aRes['FILTER_FitPos_FrontRear']){?>
                                        <a class="CmSelectCarSide CmRearBl CmSelSide<?=$aRes['FILTER_FitPos_Rear_Pushed']?> CmFrRr" href="<?=$aRes['FILTER_FitPos_Link_Rear']?>">
                                            <svg class="CmCarSide CmSelSvg<?=$aRes['FILTER_FitPos_Rear_Pushed']?>" viewBox="58 30 46.967 37.967"><?=$aListSVG['CarRear']?></svg>
                                            <span class="CmCarSideTxt CmSelTxt<?=$aRes['FILTER_FitPos_Rear_Pushed']?>"><?=Lng_x('Rear')?></span>
                                        </a>
                                    <?}?>
								</div>
								<div class="CmBackViewCar" style="<?if(!$aRes['FILTER_FitPos_FrontRear']){?>border-top:1px solid #969597<?}?>">
                                    <?if($aRes['FILTER_FitPos_LeftRight']){?>
                                        <a class="CmSelectBVSide CmBVLeft CmSelSide<?=$aRes['FILTER_FitPos_Left_Pushed']?> CmLfRt" href="<?=$aRes['FILTER_FitPos_Link_Left']?>" >
                                            <span class="CmBVTxt CmSelTxt<?=$aRes['FILTER_FitPos_Left_Pushed']?>"><?=Lng_x('SLeft')?></span>
                                            <svg class="CmBackView CmBVL CmSelSvg<?=$aRes['FILTER_FitPos_Left_Pushed']?>" viewBox="-641 0 1280.000000 825.000000"><?=$aListSVG['CarBackLeft']?></svg>
                                        </a>
                                    <?}?>
									<?if($aRes['FILTER_FitPos_LeftRight']){?>
										<a class="CmSelectBVSide CmBVRight CmSelSide<?=$aRes['FILTER_FitPos_Right_Pushed']?> CmLfRt" href="<?=$aRes['FILTER_FitPos_Link_Right']?>">
											<svg class="CmBackView CmBVR CmSelSvg<?=$aRes['FILTER_FitPos_Right_Pushed']?>" viewBox="653 0 1280.000000 825.000000"><?=$aListSVG['CarBackRight']?></svg>
											<span class="CmBVTxt CmSelTxt<?=$aRes['FILTER_FitPos_Right_Pushed']?>"><?=Lng_x('SRight')?></span>
										</a>
									<?}?>
								</div>
							</div>
						</div>
					<?}?>
					<?if($aRes['FILTER_PRODUCTS']){?>
                        <div class="l_part">
                            <div class="l_title CmColorTx"><?=Lng_x('Product_type',1)?></div>
                            <hr class="titleSet CmColorBr">
                            <?foreach($aRes['FILTER_PRODUCTS'] as $ProdID=>$aProd){?>
                                <div class="CmFilterCheck <?if($aProd['Checked']){?>CmColorTxi TxBold<?}?>" data-prid="<?=$ProdID?>">
                                    <div class="check_b">
                                        <?if($aProd['Checked']){?>
                                            <svg class="material-icon CmColorFi" viewBox="2 2 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                        <?}?>
                                    </div>
                                    <span class="CmFilCheckTxt" title="<?=$aProd['Info']?>"><?=$aProd['Name']?><sup>&nbsp;<?=$aRes['PRODUCTS_TYPES'][$ProdID]?></sup><br><span><?=$aProd['Info']?></span></span>
                                </div>
                            <?}?>
                        </div>
                    <?}?>
                    <?if($aRes['FILTER_CRITERIA']){?>
                        <div class="l_part"><?
                            foreach($aRes['FILTER_CRITERIA'] as $aCri){?>
                              <div class="l_title  CmColorTx"><?=$aCri['Name']?>:</div>
                              <hr class="titleSet CmColorBr">
                                <div class="l_filter"><?
                                    foreach($aCri['Values'] as $CrCode=>$CrName){
                                    if($aCri['Checked'] AND in_array($CrCode,$aCri['Checked'])){$Checked=true;}else{$Checked=false;}?>
                                      <div class="CmFilterCheck <?if($Checked){?>c_Txi TxBold<?}?>" data-crcod="<?=$CrCode?>" >
                                            <div class="check_b">
                                                <?if($Checked){?><svg class="material-icon fillBg" viewBox="2 2 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg><?}?>
                                            </div>
                                            <span>
                                                <?=$CrName?>
                                                <sup><?=$aCri['ProductsCnt'][$CrCode]?></sup>
                                            </span>
                                      </div>
                                    <?}?>
                                </div>
                            <?}?>
                        </div>
                    <?}?>
                    <?if(IsADMIN_x AND $UsePriceFilter){
                    $rangeStep = ($aRes['MAX_PRICE']-$aRes['MIN_PRICE'])/4;
                    $rangeFl = $aRes['MIN_PRICE']+$rangeStep;
                    $rangeInt = intval($aRes['MIN_PRICE']+$rangeStep);?>
                        <div class="l_title CmColorTx"><b><?=Lng_x('Price')?>&nbsp;<?=$aRes['CURRENCY_TEMPLATE']?></b></div>
                        <hr class="titleSet CmColorBr">
                        <div class="CmPriceRangeSl">
                            <div class="CmTitlePriceBl">
                                <p>
                                    <input class="CmPriceNum" type="text" id="amount" data-priceFrom="<?=$aRes['MIN_PRICE']?>" data-priceTo="<?=$aRes['MAX_PRICE']?>" readonly>
                                </p>
                                <div id="slider-range"></div>
                            </div>
                            <div class="rangePriceBlock">
                                <div class="bigRangeTextBl" style="left:0%">
                                    <span class="bigRange"></span>
                                    <span class="bigRangeText"><?=$aRes['MIN_PRICE']?></span>
                                </div>
                                    <span class="smallRange" style="left:5%"></span>
                                    <span class="smallRange" style="left:10%"></span>
                                    <span class="smallRange" style="left:15%"></span>
                                    <span class="smallRange" style="left:20%"></span>
                                <div class="bigRangeTextBl" style="left:25%">
                                    <span class="bigRange"></span>
                                    <span class="bigRangeText"><?if($aRes['MIN_PRICE']>0 && $aRes['MAX_PRICE']<=10){echo $rangeFl;}else{echo $rangeInt;}?></span>
                                </div>
                                    <span class="smallRange" style="left:30%"></span>
                                    <span class="smallRange" style="left:35%"></span>
                                    <span class="smallRange" style="left:40%"></span>
                                    <span class="smallRange" style="left:45%"></span>
                                <div class="bigRangeTextBl" style="left:50%">
                                    <span class="bigRange"></span>
                                    <span class="bigRangeText"><?if($aRes['MIN_PRICE']<10 && $aRes['MAX_PRICE']==10){echo $rangeFl*2;}else{echo $rangeInt*2;}?></span>
                                </div>
                                    <span class="smallRange" style="left:55%"></span>
                                    <span class="smallRange" style="left:60%"></span>
                                    <span class="smallRange" style="left:65%"></span>
                                    <span class="smallRange" style="left:70%"></span>
                                <div class="bigRangeTextBl" style="left:75.4%">
                                    <span class="bigRange"></span>
                                    <span class="bigRangeText"><?if($aRes['MIN_PRICE']<10 && $aRes['MAX_PRICE']==10){echo $rangeFl*3;}else{echo $rangeInt*3;}?></span>
                                </div>
                                    <span class="smallRange" style="left:80%"></span>
                                    <span class="smallRange" style="left:85%"></span>
                                    <span class="smallRange" style="left:90%"></span>
                                    <span class="smallRange" style="left:95%"></span>
                                <div class="bigRangeTextBl" style="left:100%">
                                    <span class="bigRange"></span>
                                    <span class="bigRangeText"><?=$aRes['MAX_PRICE']?></span>
                                </div>
                            </div>
                            <div class="CmRangePrSubmBut">
                                <?//if($aRes['PRICE_UPTO']>0){?>
                                    <span class="CmResetBut CmColorBgh"><?=Lng_x('Reset',1)?></span>
                                <?//}?>
                                <span class="CmApplyBut CmColorBg"><?=Lng_x('Apply',1)?></span>
                            </div>
                        </div>
                    <?}?>
                    <div class="l_title CmColorTx"><b><?=Lng_x('Manufacturer',1)?></b></div>
                    <hr class="titleSet CmColorBr">
                    <?if($aRes['FILTER_BRANDS']){?>
                        <div class="l_part">
                            <!--<div class="CmBrandOeAnalogWrap">
                                <div class="CmAllBrandsBut CmBrandHovBut CmBrandActBut CmColorBgL"><span class="CmColorTx"><?=Lng_x('All')?></span></div>
                                <div class="CmAnalogBrandBut CmBrandHovBut"><span><?=Lng_x('Analogs')?></span></div>
                                <div class="CmOeBrandBut CmBrandHovBut"><span>OEM</span></div>
                            </div>-->
                            <div class="searc_inp">
                                <input class="filt_sect CmColorBr CmColorTx" type="text" placeholder="<?=Lng_x('Filter_by_brands',0)?>..">
                                <div class="clearButt CmColorFih">
                                    <svg class="material-icon" viewBox="0 -1 24 24">
                                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                    </svg>
                                </div>
								<span class="CmSearchNoResTxt"><?=Lng_x('No_search_result');?></span>
							</div>
                            <?$countBrand=count($aRes['FILTER_BRANDS']);?>
							<div class="CmBrandInnerBl <?if($countBrand>15){?>CmLeftFilHeight<?}?>">
	                            <div class="l_filterBr">
	                                <?foreach($aRes['FILTER_BRANDS'] as $BCode=>$aBrand){?>
	                                    <div class="CmBrandFilter CmFilterCheck <?if($aBrand['Checked']){?>CmColorTxi TxBold<?}?>" data-bcode="<?=$BCode?>">
	                                        <div class="check_b">
	                                            <?if($aBrand['Checked']){?>
	                                                <svg class="material-icon CmColorFi" viewBox="2 3 24 24">
	                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
	                                                </svg>
	                                            <?}?>
	                                        </div>
	                                        <span class="CmBranName"><?=$aBrand['Name']?>
	                                            <sup><?=$aBrand['Count']?></sup>
	                                        </span>
	                                    </div>
	                                <?}
	                                //if(count($aRes['FILTER_BRANDS'])>15){?>
	                                    <!-- <div class="CMShowMoreBr CmColorTx"><?=Lng_x('Show_more',1)?>&nbsp;&#9660;</div> -->
	                                <?//}?>
	                            </div>
                            </div>
                        </div>
                    <?}?>
                </div>
            </div>
        <?}?>

        <div class="main_part" style="<?if(!$aRes['HAVE_FILTERS']){echo 'width:100%';} ?>">

            <?// SCHEMES
            if($aRes['SCHEMES_COUNT']){?>
                <div class="CmSchemaTitle CmColorTxi"><?=Lng_x('Select_OEM_schema',1);?>:</div>
                <div class="CmSchemaBox"><?
                    foreach($aRes['SCHEMES'] as $aSchema){
                        $MinSize = 100; //px?>
                        <div class="CmSchema CmColorBr" style="background-image:url(<?=$aSchema['PicSrc']?>); height:<?=$MinSize*$aSchema['RatioH']?>px;" data-picid="<?=$aSchema['PicID']?>" data-lng="<?=LANG_x?>">
                            <div class="CmSchemName CmColorBgA">
                                <div class="CmSchNameTxt CmColorTxi"><?=$aSchema['Name']?></div>
                            </div>
                            <div class="CmSchLoadWrap"><div class="CmSchLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>
                        </div>
                    <?}?>
                    <div class="CmSchemaShowAll CmColorBg">&#9660; <?=Lng_x('Show_all_schemes',1);?></div>
                </div><?
            }
            ?>
			<div class="CmSortFilterBlock">
	            <div class="sort_but">
	                <div class="butt_sel" data-loadview="<?=$aRes['ACTIVE_TAB']?>">
	                    <div class="viewlist_cm cm_viewAct <?if($aRes['ACTIVE_TAB']=='LIST'){echo 'CmColorTxi cm_viewShad CmColorFi';}?>" data-view="LIST" data-urix="<?=PROTOCOL_DOMAIN_x?><?=$_SERVER['REQUEST_URI']?>">
	                        <svg class="material-icon icon_im" viewBox="0 -4 24 24" position="50">
	                            <path d="M4 14h4v-4H4v4zm0 5h4v-4H4v4zM4 9h4V5H4v4zm5 5h12v-4H9v4zm0 5h12v-4H9v4zM9 5v4h12V5H9z"/>
	                        </svg>
	                        <div class="but_text"></div>
	                    </div>
	                    <div class="viewgrid_cm cm_viewAct <?if($aRes['ACTIVE_TAB']=='GRID'){echo 'CmColorTxi cm_viewShad CmColorFi';}?>" data-view="GRID" data-urix="<?=PROTOCOL_DOMAIN_x?><?=$_SERVER['REQUEST_URI']?>">
	                        <svg class="material-icon icon_im" viewBox="0 -4 24 24">
	                            <path d="M4 11h5V5H4v6zm0 7h5v-6H4v6zm6 0h5v-6h-5v6zm6 0h5v-6h-5v6zm-6-7h5V5h-5v6zm6-6v6h5V5h-5z"/>
	                        </svg>
	                        <div class="but_text"></div>
	                    </div>
	                    <div class="viewtable_cm cm_viewAct <?if($aRes['ACTIVE_TAB']=='TABLE'){echo 'CmColorTxi cm_viewShad CmColorFi';}?>" data-view="TABLE" data-urix="<?=PROTOCOL_DOMAIN_x?><?=$_SERVER['REQUEST_URI']?>">
	                        <svg class="material-icon icon_im" viewBox="0 -4 24 24" position="50">
	                            <path d="M4 15h16v-2H4v2zm0 4h16v-2H4v2zm0-8h16V9H4v2zm0-6v2h16V5H4z"/>
	                        </svg>
	                        <div class="but_text"></div>
	                    </div>
	                </div>
                    <?if(count($aRes['PRODUCTS']) >= 3){?>
                        <div class="sort_sel">
                            <span class="sort_tit"><?=Lng_x('Sort_by')?>: </span>
                            <div class="sort_bl">
                                <div class="show_bl CmColorTx">
                                    <?$aSort = Array('Rating'=>Lng_x('Sort_rating'), 'LowPrice'=>Lng_x('Sort_lowest_price'), 'Delivery'=>Lng_x('Sort_delivery_time'), 'Available'=>Lng_x('Sort_available'));?>
                                    <span class="sort_text"><?=Lng_x($aSort[$aRes['SORT']])?></span>
                                    <div class="open_bl">&#9660;</div>
                                </div>
                                <div class="hide_bl">
                                    <?foreach($aSort as $SCode=>$Name){
                                        if($SCode==$aRes['SORT']){$Class='CmSortActive';}else{$Class='';}?>
                                        <div class="sort_list CmColorBgh <?=$Class?>" data-sort="<?=$SCode?>"><?=$Name?></div>
                                    <?}?>
                                    <div class="CmSortBlockClose">
                                        <svg class="CmCloseSort" viewBox="0 0 24 24"><path d="M23.954 21.03l-9.184-9.095 9.092-9.174-2.832-2.807-9.09 9.179-9.176-9.088-2.81 2.81 9.186 9.105-9.095 9.184 2.81 2.81 9.112-9.192 9.18 9.1z"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?}?>
	                <div class="cm_countProdBl">
	                    <span><?=Lng_x('Products_count',0)?>: </span><span class="cm_cPr"><?=$aRes['TOTAL_PRODUCTS']?></span>
	                </div>
	                <div class="clear"></div>
	            </div>
	            <div class="CmFilterShowButton CmColorBg">
	                <svg class="CmSvgFilterImg" viewBox="0 0 24 24"><path d="M1 0h22l-9 15.094v8.906l-4-3v-5.906z"/></svg>
	                <span class="CmFilterButtonText"><?=Lng_x('Filters')?></span>
	            </div>
			</div>
            <?if($aRes['TOTAL_PRODUCTS']){

                // Include VIEW template
                include('view_'.mb_strtolower($aRes['ACTIVE_TAB']).'.php');
                $aRes['MORE_PRODUCTS_COUNT']=false;
                if($aRes['MORE_PRODUCTS_COUNT']){?>
                    <a href="?page=2#ProductsList" class="cm_moreProd">
                        <div class="cm_moreImg">
                            <div class="cm_imgbl">
                                <div class="cm_imgBlock">
                                    <svg class="cm_imgRedo CmColorFi" viewBox="0 0 24 24">
                                        <path d="M12 0c3.31 0 6.291 1.353 8.459 3.522l2.48-2.48 1.061 7.341-7.437-.966 2.489-2.489c-1.808-1.807-4.299-2.928-7.052-2.928-5.514 0-10 4.486-10 10s4.486 10 10 10c3.872 0 7.229-2.216 8.89-5.443l1.717 1.046c-2.012 3.803-6.005 6.397-10.607 6.397-6.627 0-12-5.373-12-12s5.373-12 12-12z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="cm_moreText">
                                <span><?=Lng_x('Show_more_products',0)?> (<?=$aRes['MORE_PRODUCTS_COUNT']?>)</span>
                                <span><?=Lng_x('or_use_product_filter',0)?></span>
                            </div>
                        </div>
                    </a>
                <?}elseif($aRes['PAGE_TOTAL']>1){?>
                    <div class="CmPagination">
						<div class="CmPagTextBlock"><?=Lng_x('Page')?>:</div>
						<?$adjacentCount = 4;
					    $firstPage = 1;
					    $lastPage  = $aRes['PAGE_TOTAL'];
					    if ($lastPage == 1) {
					        return;
					    }
					    if ($aRes['PAGE_CURRENT'] <= $adjacentCount + $adjacentCount) {
					        $firstAdjacentPage = $firstPage;
					        $lastAdjacentPage  = min($firstPage + $adjacentCount + $adjacentCount, $lastPage);
					    } elseif ($aRes['PAGE_CURRENT'] > $lastPage - $adjacentCount - $adjacentCount) {
					        $lastAdjacentPage  = $lastPage;
					        $firstAdjacentPage = $lastPage - $adjacentCount - $adjacentCount;
					    } else {
					        $firstAdjacentPage = $aRes['PAGE_CURRENT'] - $adjacentCount;
					        $lastAdjacentPage  = $aRes['PAGE_CURRENT'] + $adjacentCount;
					    }?>
					    <div class="CmPageLinkBlock">
							<?if(substr(URL_x, -1, 1) != '/'){
								$LinkPag = URL_x.'/';
							}else{$LinkPag = URL_x;}?>
						    <?if ($firstAdjacentPage > $firstPage) {?>
						        <a class="CmPagLink <?if($firstAdjacentPage==$aRes['PAGE_CURRENT']){?>CmPageAct<?}?>" href="<?=$LinkPag.QueryString_x('page', $firstPage)?>"><?=$firstPage?></a>
						        <?if ($firstAdjacentPage > $firstPage + 1) {?>
						            <span>...</span>
						        <?}
						    }
						    for ($i = $firstAdjacentPage; $i <= $lastAdjacentPage; $i++) {?>
					            <a class="CmPagLink <?if($i==$aRes['PAGE_CURRENT']){?>CmPageAct<?}?>" href="<?=$LinkPag.QueryString_x('page', $i)?>"><?=$i?></a>
						    <?}
						    if ($lastAdjacentPage < $lastPage) {
						        if ($lastAdjacentPage < $lastPage - 1) {?>
						            <span>...</span>
						        <?}?>
						        <a class="CmPagLink <?if($lastAdjacentPage==$aRes['PAGE_CURRENT']){?>CmPageAct<?}?>" href="<?=$LinkPag.QueryString_x('page', $lastPage)?>"><?=$lastPage?></a>
						    <?}?>
					    </div>
                    </div>

                <?}
            }else{?>
                <div class="cm_NoProduct"><span><?=Lng_x('No products')?></span></div>
            <?}?>
        </div>
    </div>
	<?=ShowSEOText_x("BOT")?>

</div>
<?if($aSets['NARROW_DESIGN']){?>
	<link rel="stylesheet" type="text/css" href="<?=TEMPLATE_PAGE_DIR?>blocks/mini.css" />
<?}?>
<?php AjaxCut_x(); //Makes: </div> ?>
<?//aprint_x($aSets,'$aSets');?>
<?aprint_x($aRes,'$aRes');
?>
