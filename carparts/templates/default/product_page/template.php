<?VerifyAccess_x('ProductPage.templ');
//$aRes - Controller Result array with all data
//$aSets - Page Settings array (defined at admin side: settings)
//TEMPLATE_PAGE_DIR - php Constant that contain current page template folder path
//
//RATING STARS FUNCTION
function StarRatingFunc($rating,$maxRating = 5) {
    $fullStar = '<svg viewBox="0 0 24 24" width="22" fill-rule="evenodd" clip-rule="evenodd"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z"/></svg>';
    $halfStar = '<svg viewBox="0 0 24 24" width="22" fill-rule="evenodd" clip-rule="evenodd"><path d="M12 5.173l2.335 4.817 5.305.732-3.861 3.71.942 5.27-4.721-2.524v-12.005zm0-4.586l-3.668 7.568-8.332 1.151 6.064 5.828-1.48 8.279 7.416-3.967 7.416 3.966-1.48-8.279 6.064-5.827-8.332-1.15-3.668-7.569z"/></svg>';
    $emptyStar = '<svg viewBox="0 0 24 24" width="22" fill-rule="evenodd" clip-rule="evenodd"><path d="M12 5.173l2.335 4.817 5.305.732-3.861 3.71.942 5.27-4.721-2.524-4.721 2.525.942-5.27-3.861-3.71 5.305-.733 2.335-4.817zm0-4.586l-3.668 7.568-8.332 1.151 6.064 5.828-1.48 8.279 7.416-3.967 7.416 3.966-1.48-8.279 6.064-5.827-8.332-1.15-3.668-7.569z"/></svg>';
    $rating = $rating / 999 * 5.001;

    $fullStarCount = (int)$rating;
    $halfStarCount = round($rating)-$fullStarCount;
    $emptyStarCount = $maxRating-$fullStarCount-$halfStarCount;

    $html = str_repeat($fullStar,$fullStarCount);
    $html .= str_repeat($halfStar,$halfStarCount);
    $html .= str_repeat($emptyStar,$emptyStarCount);
    $html = '<div class="cmRatingSt">'.$html.'</div>';
    return $html;
}
//--- For display OE numbers (line 306)
$aNumsBlock = [$aRes['OE'], $aRes['ANALOGS'], $aRes['TRADE_NUMBERS'], $aRes['OLD_NUMBERS'], $aRes['NEW_NUMBERS'], $aRes['EANS']];
foreach ($aNumsBlock as $key => $value) {
    if($value==''){
        continue;
    }
    $aNumRes[$key] = $value;
}
//---

//echo $aRes['Rating'];
if($aRes['OE'] || $aRes['ANALOGS'] || $aRes['TRADE_NUMBERS'] || $aRes['OLD_NUMBERS'] || $aRes['NEW_NUMBERS'] || $aRes['EANS']){
	$AnalogsAvail = true;
}
if($aRes['VEHICLES']){
	$VehiclesAvail = true;
}
if(!$aRes['PRICES'] && $aRes['HAVE_CROSSES'] && !$aRes['OE'] && !$aRes['IMAGES'] && !$aRes['VEHICLES']){
    $HideProps = true;
}
//CHANGE ARRAY PRICE FOR PRICE BLOCK
$aProd = $aRes;
if(!$aRes['PRODUCTS'] && $aRes['PRICES']){
    foreach ($aRes['PRICES'] as $k => $aPr) {
        $aProd['PRICES'][$k] = $aPr;
    }
}

//// Image ratio
// function calculateAspectRatioFit($srcWidth, $srcHeight, $maxWidth, $maxHeight) {
//     $ratioSize = [$maxWidth / $srcWidth, $maxHeight / $srcHeight];
//     $ratioSize = min($ratioSize[0], $ratioSize[1]);
//     $widthHeight['width'] = $srcWidth*$ratioSize;
//     $widthHeight['height'] .= $srcHeight*$ratioSize;
//     return $widthHeight;
// }
//AddCart URL for prices.php block
$ProductURL = GetProductLink_x($aRes);

//Template Block setting
$BLOCK = $aSets['TEMPLATE_BLOCK'];
//include('blocks/'.$BLOCK.'.php');

if($aRes['Color']){$BrandStyleColor = 'style="color:#'.$aRes['Color'].'"';}

//Include SVG
include('svg.php');
?>
<?if(IsADMIN_x){?>
    <div id="BoxCross_x" class="fxOverlay_adm"><div class="fxModal_adm"><div class="fxCont_adm">
        <input type="hidden" id="BrBrand" name="BrBrand" value="<?=$aRes['Brand']?>"/>
        <table class="SetsTab">
            <tr><td><?=Tip_x('Add_cross_link_to')?>:</td><td><b style="color:#636363;"><?=$aRes['Brand']?></b> <b style="color:#fa6a00;"><?=$aRes['ArtNum']?></b></td></tr>
            <tr><td></td><td><br/></td></tr>
            <tr><td nowrap><?=Tip_x('Column_Brand')?>:</td>
                <td><input id="CBrand_x" name="CBrand_x" maxlength="12" style="width:250px;" class="TextA" type="text" pathx="<?=CM_DIR?>/<?=CM_ADM?>" value=""/>
                <div id="lBV_x" class="boxAList"></div></td>
            </tr>
            <tr>
                <td nowrap><?=Tip_x('Article_Number')?>:</td>
                <td>
                    <div class="InpRes"></div><input id="CArticle" type="text" pathx="<?=CM_DIR?>/<?=CM_ADM?>" class="TextA InpCatID" disabled style="width:250px; text-align:center;" maxlength="24"/>
                </td>
            </tr>
            <tr>
                <td nowrap><?=Tip_x('Direction')?>:</td>
                <td>
                    <div id="DLeft" class="direction_x direcActive_x"><svg height="30" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M700.29141 790.509155c31.136138 18.067494 56.673767 3.396353 56.750515-32.601512l1.100054-518.214539c0.077771-35.994796-25.39846-50.776454-56.610322-32.843012L252.232698 464.987267c-31.208792 17.930371-31.271214 47.379077-0.140193 65.445548l448.199928 260.07634L700.29141 790.509155 700.29141 790.509155z" /></svg></div>
                    <div id="DRight" class="direction_x direcActive_x"><svg height="30" style="transform:rotate(180deg);" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M700.29141 790.509155c31.136138 18.067494 56.673767 3.396353 56.750515-32.601512l1.100054-518.214539c0.077771-35.994796-25.39846-50.776454-56.610322-32.843012L252.232698 464.987267c-31.208792 17.930371-31.271214 47.379077-0.140193 65.445548l448.199928 260.07634L700.29141 790.509155 700.29141 790.509155z" /></svg></div>
                </td>
            </tr>
        </table>
    </div></div>
    </div>
<?}?>
<div class="CmTopBox">
    <div class="CmHeadTitleWrapBlock">
        <div class="CmTitleBox">
            <div class="cmInnerBl">
                <div class="cmWrapLogoBl">
                    <div class="blockLogo" style="background-image:url(<?=$aRes['Logo']?>);"></div>
                </div>
                <div class="CmVertLineBlock" style="border-color:#<?if($aRes['BRAND']['Color']){echo $aRes['BRAND']['Color'];}else{echo 'cdcdcd';}?>"></div>
                <div class="ratLinkCuntr">
                    <div class="CmRatStarsFlagBl">
                        <?=StarRatingFunc($aRes['BRAND']['Rating'])?>
                        <style>
                            .cmRatingSt svg{fill:#<?if($aRes['BRAND']['Color']){echo $aRes['BRAND']['Color'];}else{echo '808080';}?>}
                        </style>
                        <div class="CmCountry_ad" title="" style="background-image:url(/<?=CM_DIR?>/media/country/<?=$aRes['BRAND']['CCode']?>.png);"></div>
                    </div>
                    <div class="CmHorisLineBlock" style="border-color:#<?if($aRes['BRAND']['Color']){echo $aRes['BRAND']['Color'];}else{echo 'cdcdcd';}?>"></div>
                    <div class="CmRatingCNameBrCWSite">
                        <div class="CmFlagBrName">
                            <div class="CmBrNamebl CmColorTx <?if($aRes['BRAND']['Corp']){?>CmTitShow<?}?>" <?if($aRes['BRAND']['Corp']){?>title="<?=$aRes['BRAND']['Corp']?>"<?}?>><b <?=$BrandStyleColor?>  style="color:#<?=$aRes['BRAND']['Color']?>"><?=$aRes['BRAND']['Name']?></b></div>
                        </div>
                        <div class="cmCountNameFlagWrap" data-color="<?=$aRes['BRAND']['Color']?>">
                            <div class="CmCoutryName" style="color:#<?if($aRes['BRAND']['Color']){echo $aRes['BRAND']['Color'];}else{echo 'cdcdcd';}?>"><?=$aRes['BRAND']['CName']?></div>
                        </div>
                        <?if($aRes['BRAND']['Corp']){?>
                            <div class="CmBrandCorp"><?=$aRes['BRAND']['Corp']?></div>
                        <?}?>
                        <?//if($aRes['BRAND']['Website']){?>
<!--                            <div class="cmWebsiteTd">--><?//=$aRes['BRAND']['Website']?><!--</div>-->
                        <?//}?>
                    </div>
                </div>
            </div>
        </div>
        <?$MSelect_Position='Right'; //Left?>
        <div class="CmMSelectBlock CmMSelectPosition<?=$MSelect_Position?>">
            <?$Selector_Template = 'default';
            include_once(PATH_x.'/add/selector/controller.php');?>
        </div>
    </div>
</div>
<div class="CmBrTitleSearchWrap CmColorBgL CmColorBr">
    <div id="CmTitlH1Page"><h1 class="CmColorTx"><?=H1_x?></h1></div>
</div>
<?BreadCrumbs_x(); // Edit in: ../templates/default/includes.php ?>
<!--MAIN PART-->
<div class="blockMainPart">
	<div class="CmMargTopMinus"><?
	EditImgBut_x($aRes['Pom']); //Admin Edit Images button
	EditLinkBut_x($aRes['Pom']); //Admin Edit analogs group button
	EditProdBut_x($aRes['Pom']); //Admin Edit Product data button
	?></div>
	<div class="CmClrb"></div>
	<div class="blockProdCard" style="<?if($HideProps){?>margin:10px 0px 0px 0px<?}?>">
        <div class="blockProdFoto" style="<?if($HideProps){?>flex-basis:unset;<?}?>"><?
            if($aRes['IMAGES']){
                $aImg = reset($aRes['IMAGES']);
            }
            ?><div class="innBlockFoto">
                <?// Images
                if($aRes['IMAGES']){
                    if($aImg['Class']&&$aImg['Class']!=''){?>
                        <div class="CmImageToPopup" data-imgtype="scheme" style="<?if($HideProps){?>width:150px; height:150px;<?}?>">
                            <div class="CmSchemBlockWrap" style="<?if($aImg['Width']>$aImg['Height']){?>height:unset;<?}?>">
                                <img class="<?=$aImg['Class']?> CmProdImgBl" src="<?=$aImg['Src']?>" alt="Scheme" data-width="<?=$aImg['Width']?>" data-Height="<?=$aImg['Height']?>" style="width:auto;">
                                <?=$aImg['Html']?>
                            </div>
                        </div>
                    <?}else{?>
                        <div class="CmImageToPopup" style="<?if($HideProps){?>width:150px; height:150px;<?}?>">
                            <img class="CmProdImgBl" src="<?=$aImg['Src']?>" alt="<?=$aRes['Brand'].' '.$aRes['ArtNum'].' - '.$aRes['Name'].' '.$_SERVER['SERVER_NAME']?>">
                        </div>
                    <?}
                // Logo
                }else{?>
                    <div class="imgNoFoto" data-imgsrc="<?=$firstImg?>" style="background-image:url(<?=TEMPLDIR_x?>_images/no-image.jpg); cursor:default; <?if($HideProps){?>width:100px; height:100px;<?}?>">
                        <img class="CmLogoNoFoto" src="<?=$aRes['Logo']?>">
                    </div>
                <?}
                // More pictures
                if(count($aRes['IMAGES']) > 1){?>
                    <div class="blSmallFoto"><?
                        foreach($aRes['IMAGES'] as $aSmImg){
                            if($aSmImg['Class'] AND $aImg['Class']!=''){
                                ?><div class="cmChangeImg CmColorBrh">
                                    <div class="CmSchemBlockWrap" style="<?if($aSmImg['Width']>$aSmImg['Height']){?>height:unset;<?}?>">
                                        <img class="<?=$aImg['Class']?> CmProdSmallImgBl" src="<?=$aSmImg['Src']?>" data-width="<?=$aSmImg['Width']?>" data-Height="<?=$aSmImg['Height']?>" alt="Scheme" style="width:auto;">
                                        <?=$aSmImg['Html']?>
                                    </div>
                                </div><?
                            }else{
                                ?><div class="cmChangeImg CmColorBrh">
                                    <img class="CmProdSmallImgBl" src="<?=$aSmImg['Src']?>" alt="<?=$aRes['Brand'].' '.$aRes['ArtNum'].' - '.$aRes['Name'].' '.$_SERVER['SERVER_NAME']?>">
                                </div><?
                            }
                        }
                        if($aRes['EXTERNAL_URL']){?>
                            <div class="CmExtUrlImgWrap">
                                <div class="CmExtUrlImgBlock CmColorBrh">
                                    <?if($aRes['EXTERNAL_URL']['You']){
                                        ?><a class="CmExtUrlLink" href="<?=$aRes['EXTERNAL_URL']['You']?>" target="_blank"><?=$aPageSVG['ExLinkYou']?></a><?
                                    }elseif($aRes['EXTERNAL_URL']['Pdf']){
                                        ?><a class="CmExtUrlLink" href="<?=$aRes['EXTERNAL_URL']['Pdf']?>" target="_blank"><?=$aPageSVG['ExLinkPdf']?></a><?
                                    }elseif($aRes['EXTERNAL_URL']['Emea']){?>
                                        <div class="CmIfrBut" data-eu="<?=$aRes['EXTERNAL_URL']['Emea']?>"><?=$aPageSVG['ExLinkImea']?></div>
                                    <?}elseif($aRes['EXTERNAL_URL']['Vzaa']){?>
                                        <div class="CmIfrBut" data-eu="<?=$aRes['EXTERNAL_URL']['Vzaa']?>"><?=$aPageSVG['ExLinkVzaa']?></div>
                                    <?}else{
                                        foreach($aRes['EXTERNAL_URL'] as $eKey => $eVal){?>
                                            <a class="CmExtUrlLink" href="<?=$aRes['EXTERNAL_URL'][$eKey]?>" target="_blank"><?=$aPageSVG['ExLinks']?></a>
                                        <?}
                                    }?>
                                </div>
                            </div>
                        <?}?>
                    </div><?
                }
                $ifr1 = 1;
                if(IsADMIN_x && $ifr1 == 1){
                    if($aRes['EXTERNAL_URL'] && (!$aRes['IMAGES'] || count($aRes['IMAGES']) == 1)){
                        ?><div class="CmExtUrlWrap">
                            <div class="CmExtUrlBlock CmColorBgLh CmColorBr">
                                <?if($aRes['EXTERNAL_URL']['You']){
                                    ?><a class="CmExtUrlLink" href="<?=$aRes['EXTERNAL_URL']['You']?>" target="_blank"><?=$aPageSVG['ExLinkYouImg']?></a><?
                                }elseif($aRes['EXTERNAL_URL']['Pdf']){
                                    ?><a class="CmExtUrlLink" href="<?=$aRes['EXTERNAL_URL']['Pdf']?>" target="_blank"><?=$aPageSVG['ExLinkPdfImg']?></a><?
                                }elseif($aRes['EXTERNAL_URL']['Spin']){?>
                                    <div class="CmIfrBut Cm360But" data-eu="<?=$aRes['EXTERNAL_URL']['Spin']?>"><?=$aPageSVG['ExLinkSpinImg']?></div>
                                <?}elseif($aRes['EXTERNAL_URL']['Emea']){?>
                                    <div class="CmIfrBut Cm360But" data-eu="<?=$aRes['EXTERNAL_URL']['Emea']?>"><?=$aPageSVG['ExLinkImeaImg']?></div>
                                <?}elseif($aRes['EXTERNAL_URL']['Vzaa']){?>
                                    <div class="CmIfrBut Cm360But" data-eu="<?=$aRes['EXTERNAL_URL']['Vzaa']?>">
                                        <svg class="Cm360Svg CmColorFi" viewBox="0 0 499.999 499.999"><?=$aPageSVG['ExLinkVzaaImg']?></div>
                                <?}else{
                                    foreach($aRes['EXTERNAL_URL'] as $eKey => $eVal){?>
                                        <a class="CmExtUrlLink" href="<?=$aRes['EXTERNAL_URL'][$eKey]?>" target="_blank"><?=$aPageSVG['ExLinksImg']?></a>
                                    <?}
                                }?>
                            </div>
                        </div>
                    <?}
                }?>
            </div>
        </div>

        <!--NAME PROPERTIES BLOCK-->
        <div class="CmBlockPropsPriceWrap">
            <?//if($HideProps){?>
                <!--<div class="CmOpenPropsBlock CmColorBr CmColorTx"><span><?=Lng_x('Description_and_specs')?></span><div class="CmArrDownOpen CmColorFi"><?=$aPageSVG['CmArrowDown']?></div></div>-->
            <?//}?>
                <div class="blockProdProps">
                    <div class="CmPropsInnerBlock" style="<?if($HideProps){?>height:85px; overflow:hidden;<?}?>">
                        <div class="innBlockProps">
                             <div class="MetaTitle CmColorBr CmColorTx"><?=Lng_x('Product_description', 1)?>:</div>
                             <ul class="ulMetaName">
                                 <li class="cmProdName"><?=$aRes['Name']?></li>
                                 <?if($aRes['META']){?>
                                     <?foreach($aRes['META'] as $aMeta){
                                         foreach($aMeta as $val){?>
                                             <li class="cmMetaName"><?=$val?></li>
                                         <?}
                                     }?>
                                 <?}?>
                                 <?if(ShowSEOText_x("TOP",true)){?><li class="cmMetaName"><?=ShowSEOText_x("TOP")?></li><?}?>
                                 <?if(SEOTEXT_x!=''){?><li class="cmMetaName"><?=SEOTEXT_x?></li><?}?>
                             </ul>
                             <?if($aRes['PROPERTIES']){?>
                                 <div class="propTitle CmColorBr CmColorTx"><?=Lng_x('Characteristics', 1)?>:</div>
                                 <div class="cmWrapPropTab">
                                     <div class="CmPropWrap <?if(count($aRes['PROPERTIES'])>14){?>CmPropTabHeight<?}?>">
                                         <table class="cmProperTab">
                                             <?foreach($aRes['PROPERTIES'] as $Name=>$Value){$i++;
                                                if($i % 2 != 0){
                                                    $bColor = 'background-color:#ececec';
                                                }else{
                                                    $bColor = '';
                                                }?>
                                                 <tr class="cmFullProp" style="<?=$bColor?>">
                                                     <td class="propTdName"><?=$Name?>&nbsp;</td>
                                                     <td class="propTdVal"><?=$Value?></td>
                                                 </tr>
                                             <?}?>
                                         </table>
                                     </div>
                                 </div>
                             <?}?>
                         </div>
                         <?if($aRes['ShortNumbers']){?>
                             <div class="CmShortNumWrap">
                                 <div class="CmShortNumTit CmColorBr CmColorTx"><?=Lng_x('Short_codes_Internal_numbers')?></div>
                                 <div class="CmShortNumTab">
                                     <?foreach($aRes['ShortNumbers'] as $Name=>$Value){?>
                                         <div class="CmShortNumb">
                                             <div class="CmShortName"><?=$Name?>:</div>
                                             <div class="CmShortValTd">
                                                 <?foreach($Value as $Val){?>
                                                     <div class="CmShortValTxt"><?=str_replace(' ', '', $Val)?></div>
                                                 <?}?>
                                             </div>
                                         </div>
                                     <?}?>
                                 </div>
                             </div>
                         <?}?>
                    </div>
                    <?if($HideProps){?>
                        <div class="CmShowHiddSpecs CmColorTx"><?=Lng_x('Show_more')?><div class="CmArrDownOpen CmColorFi"><?=$aPageSVG['CmArrowDown']?></div></div>
                    <?}?>
                </div>
                
            <!--PRICE BLOCK-->
            <div class="CmPriceEditPrButWrap">
                <!--If button "Find out price" is activated-->
                <!-- <div class="AvalAsk">
                    <a href="<?=$aProd['FindPriceLink']?>" class="CmPriceAskBut CmColorBg CmColorBr" <?=$aRes['FindPrice_isBlank']?> >
                        <svg class="CmAskImg CmColorFi" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm1.25 17c0 .69-.559 1.25-1.25 1.25-.689 0-1.25-.56-1.25-1.25s.561-1.25 1.25-1.25c.691 0 1.25.56 1.25 1.25zm1.393-9.998c-.608-.616-1.515-.955-2.551-.955-2.18 0-3.59 1.55-3.59 3.95h2.011c0-1.486.829-2.013 1.538-2.013.634 0 1.307.421 1.364 1.226.062.847-.39 1.277-.962 1.821-1.412 1.343-1.438 1.993-1.432 3.468h2.005c-.013-.664.03-1.203.935-2.178.677-.73 1.519-1.638 1.536-3.022.011-.924-.284-1.719-.854-2.297z"/></svg>
                        <span class="CmColorTx"><?=Lng_x('Get_a_price',0)?></span>
                    </a>
                </div> -->

                <div class="blockProdPrice" data-wsact="<?if($aRes['WsAjax']){echo '1';}?>" >
                    <?include('blocks/prices.php')?>
                </div>
                <?AdmWsRequests_x($aRes); //WebServices messages for Admin?>
            </div>
        </div>
    </div>
	<?//aprint_x($aRes,'$aRes');?>
    <?AjaxCut_x('ProdPrice'); // Makes: <div id="CmAjaxBox"> ?>
    <!--NUMBER ANALOGS BLOCK-->
    <?if($AnalogsAvail || $VehiclesAvail){?>
    <!-- STYLES in common.css;-->
        <div class="wrapBlTabsMenu" style="<?if($_REQUEST['CarModAjaxProdPrice']=='Y'){echo 'width:100%; margin:0px auto';}?>" data-request="<?=$_REQUEST['CarModAjaxProdPrice']?>">
            <div class="cmBlockTabs" style="<?if($_REQUEST['CarModAjaxProdPrice']=='Y'){?>display:none;<?}?>">
                <?//if(IsADMIN_x){?>
                 <!-- <div class="CmTabPartSpecs tabSelBut activeSecTab CmColorBr CmColorBg" data-change="ProdInfo">
                    <svg class="cmSvgCar cmSvgImg" viewBox="0 0 22 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>
                    <span class="cmTabText"><?=Lng_x('Characteristics', 1)?></span>
                </div> -->
                <?//}?>
                 <?if($VehiclesAvail){?>
                    <div class="tabPartUse tabSelBut activeSecTab CmColorBr CmColorBg" data-change="Suite">
                        <svg class="cmSvgCar cmSvgImg" viewBox="0 0 22 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>
                        <?if(!$_REQUEST['CarModAjaxProdPrice']=='Y'){?>
                            <span class="cmTabText"><?=Lng_x('Suitable_vehicles')?></span>
                        <?}?>
                    </div>
                <?}?>
                <?if($AnalogsAvail){?>
                    <div class="tabOeNum tabSelBut <?if(!$VehiclesAvail){?>activeSecTab CmColorBr CmColorBg<?}?>" data-change="OeNum">
                        <svg class="cmSvgInfo cmSvgImg" viewBox="-1 -1 27 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-.001 5.75c.69 0 1.251.56 1.251 1.25s-.561 1.25-1.251 1.25-1.249-.56-1.249-1.25.559-1.25 1.249-1.25zm2.001 12.25h-4v-1c.484-.179 1-.201 1-.735v-4.467c0-.534-.516-.618-1-.797v-1h3v6.265c0 .535.517.558 1 .735v.999z"/></svg>
                        <?if(!$_REQUEST['CarModAjaxProdPrice']=='Y'){?>
                            <span class="cmTabText"><?=Lng_x('OE_Numbers')?></span>
                        <?}?>
                    </div>
                <?}?>
            </div>
            <div class="cmBlockInfo bordTop2px CmColorBr">
                <?//if(IsADMIN_x){?>
                <!-- <div class="CmProdSpecsBlock">
                    <div class="CmMobileProdSpecsBlock">
                        <div class="innBlockProps">
                            <div class="MetaTitle CmColorBr CmColorTx"><?=Lng_x('Product_description', 1)?>:</div>
                            <ul class="ulMetaName">
                                <li class="cmProdName"><?=$aRes['Name']?></li>
                                <?if($aRes['META']){?>
                                    <?foreach($aRes['META'] as $aMeta){
                                        foreach($aMeta as $val){?>
                                            <li class="cmMetaName"><?=$val?></li>
                                        <?}
                                    }?>
                                <?}?>
                                <?if(ShowSEOText_x("TOP",true)){?><li class="cmMetaName"><?=ShowSEOText_x("TOP")?></li><?}?>
                            </ul>
                            <?if($aRes['PROPERTIES']){?>
                                <div class="propTitle CmColorBr CmColorTx"><?=Lng_x('Characteristics', 1)?>:</div>
                                <div class="cmWrapPropTab">
                                    <div class="CmPropWrap <?if(count($aRes['PROPERTIES'])>14){?>CmPropTabHeight<?}?>">
                                        <table class="cmProperTab">
                                            <?foreach($aRes['PROPERTIES'] as $Name=>$Value){?>
                                                <tr class="cmFullProp">
                                                    <td class="propTdName"><?=$Name?></td>
                                                    <td class="propTdVal"><?=$Value?></td>
                                                </tr>
                                            <?}?>
                                        </table>
                                    </div>
                                    <?if(count($aRes['PROPERTIES'])>14){?>
                                        <div class="CmMorePropBut CmColorTx"><?=Lng_x('Show_more',1)?>&nbsp;&#9660;</div>
                                    <?}?>
                                </div>
                            <?}?>
                        </div>
                    </div>
                </div> -->
                <?//}?>
                <?//AjaxCut_x('OENumbers'); // Makes: <div id="CmAjaxBox"> ?>
                <?if(($AnalogsAvail && !$_REQUEST['CarModAjaxProdPrice']) || ($AnalogsAvail && $_REQUEST['OeNumers'] == 'Y')){?>
                    <div class="centBlockInfo" style="<?if(!$VehiclesAvail || $_REQUEST['CarModAjaxProdPrice']){?>height:auto; opacity:1;<? }?> grid-template-columns:repeat(<?=count($aNumRes)?>, 1fr); display:grid;">
                        <?if($aRes['ANALOGS']){?>
                            <div class="CmAnalogBlockWrap">
                                <div class="anNumTitleBl">
                                    <div class="cmAnalogTitle CmColorTx CmColorBr"><?=Lng_x('Analogs')?>:</div>
                                </div>
                                <div class="CmAnalogBlockInside <?if(count($aRes['ANALOGS'])>12){?>CmBlockHeightToHIde<?}?>">
                                    <?if(count($aRes['ANALOGS'])>12){?>
                                        <div class="CmHideTextBlock"></div>
                                    <?}?>
                                    <table class="CmAnalogsTable">
                                        <?foreach($aRes['ANALOGS'] as $Brand=>$aAnalogNum){?>
                                            <tr class="cmAnalogBlocks  CmColorBgLh">
                                                <td class="cmAnBrandName"><?=$Brand?></td>
                                                <td class="cmAnArtNum">
                                                    <div class="CmWrapBlockArtNum">
                                                        <?foreach($aAnalogNum as $aNum){?>
                                                            <a class="CmColorBgh" href="<?=$aNum['Link']?>"><?=$aNum['ArtNum']?><span>,</span></a>
                                                        <?}?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?}?>
                                    </table>
                                    <?if(count($aRes['ANALOGS'])>12){?>
                                        <div class="CmMoreAnalogsNum CmColorTx">
                                            <span class="CmShowA"><?=Lng_x('Show_more')?>&nbsp;&#9660;</span>
                                            <span class="CmHideA"><?=Lng_x('Hide')?>&nbsp;&#9650;</span>
                                        </div>
                                    <?}?>
                                </div>
                            </div>
                        <?}?>
                        <?if($aRes['OE']){?>
                            <div class="CmOeNumBlockWrap">
                                <div class="CmOeBlockInside">
                                    <div class="modelNumTable ">
                                        <div class="CmColorTx cmOETitle"><?=Lng_x('Original_numbers')?>:</div>
                                        <?$s=0;?>
                                        <table class="CmOeNumTable">
                                            <?foreach($aRes['OE'] as $Brand=>$aOENums){$Cm='';$s++?>
                                                <tr class="CmColorBgLh">
                                                    <td class="CmOeNameTd" <?if($s>6){?>data-check="Y"<?}?> style="<?if(count($aNumRes) == 1){?>width:10%;<?}?>"><span><?=$Brand?></span></td>
                                                    <td class="CmNumbTd">
                                                        <div class="CmOeNumsTd" style="<?if($_REQUEST['CarModAjaxProdPrice'] == 'Y'){?>grid-template-columns:repeat(2, auto);<?}?>">
                                                            <?foreach($aOENums as $aOENum){?>
                                                                <a href="<?=$aOENum['Link']?>">
                                                                    <span class="CmOeNumberLink CmColorBgh"><?=$aOENum['ArtNum']?></span>
                                                                </a>
                                                            <?}?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?}?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?}?>
                            <!--div class="CmWrapInfoBlock <?if(!$aRes['OE']&&!($aRes['OLD_NUMBERS'] || $aRes['NEW_NUMBERS'])&&!$aRes['EANS']){?>CmGridTempCol<?}?>">-->
                        <?if($aRes['TRADE_NUMBERS']){?>
                            <table class="tradNumBlock">
                                <tr class="cmTradTitle">
                                    <td class="CmColorTx CmColorBr" colspan="2"><?=Lng_x('Trade_numbers')?>:</td>
                                </tr>
                                <?foreach($aRes['TRADE_NUMBERS'] as $aJNumbers){?>
                                    <tr class="tradeNumTr">
                                        <td><?=$aJNumbers['Name']?></td>
                                        <td><?foreach($aJNumbers['JNumbers'] as $JNumber=>$JLink){?><a class="CmTradeNumLink CmColorTxh" href="<?=$JLink?>"><?=$JNumber?></a>, <?}?></td>
                                    </tr>
                                <?}?>
                            </table>
                        <?}?>
                        <?if($aRes['OLD_NUMBERS'] || $aRes['NEW_NUMBERS']){?>
                            <div class="blOldNewNum">
                                <div class="cmNewOldTitleTd cmNewOldTitle CmColorTx CmColorBr" colspan="2"><?if($aRes['OLD_NUMBERS']){echo Lng_x('Replaced_Numbers');}else if($aRes['NEW_NUMBERS']){echo Lng_x('New_Number');}?>:</div>
                                <?if($aRes['OLD_NUMBERS']){
                                    foreach($aRes['OLD_NUMBERS'] as $Num => $Link){?>
                                        <div class="OldNewNumtd OldNewlinkNum"><a class="CmColorTxh" href="<?=$Link?>" target="blank"><?=$Num?></a></div>
                                    <?}
                                }else if($aRes['NEW_NUMBERS']){
                                    foreach($aRes['NEW_NUMBERS'] as $Num => $Link){?>
                                        <div class="OldNewNumtd OldNewlinkNum"><a class="CmColorTxh" href="<?=$Link?>" target="blank"><?=$Num?></a></div>
                                    <?}
                                }?>
                            </div>
                        <?}?>
                        <?if($aRes['EANS']){?>
                            <table class="cmBarcodeBlock">
                                <tr class="cmEanTitle">
                                    <td class="CmColorTx CmColorBr" colspan="2"><?=Lng_x('Barcode')?>:</td>
                                </tr>
                                <?foreach($aRes['EANS'] as $Ean){ $fEAN=$Ean;?>
                                    <tr class="EanBarTr CmColorBgLh">
                                        <td class="barcodText"><?=$Ean?></td>
                                    </tr>
                                <?}?>
                                <tr>
                                    <td class="EanBarcodeImg">
                                        <div class="ImgBarEan">
                                            <?require(PATH_x.'/media/barcode.php');
                                            ShowBarCode_x($fEAN);
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        <?}?>
                    </div>
                <?}elseif(!$AnalogsAvail && $_REQUEST['OeNumers'] == 'Y'){?>
                    <div class="CmNotFoundInfo">
                        <?=$aListSVG['CmNotFound']?>
                        <span class="CmNoInfoTxt"><?=Lng_x('No_info',1)?></span>
                    </div>
                <?}?>
                <?//AjaxCut_x('OENumbers'); // Makes: </div>?>
                <?//AjaxCut_x('SuitVehicle'); // Makes: <div id="CmAjaxBox"> ?>
                <?if($VehiclesAvail){?>
                    <div class="CmModelSuitBlock cmSuitBlock">
                        <!-- <span class="cmHspanVeh" data-firstkey="<?=key($aRes['VEHICLES'])?>"></span> -->
                        <div class="CmBrandBlockWrap">
                            <div class="CmBrandListBlWrap">
                                <?foreach($aRes['VEHICLES'] as $Key => $aBrName){
                                    $aVehMod[$Key] = $aBrName;?>
                                    <div class="CmBrandNameBl cmBranNameHov <?if(!isset($aBrName['MODELS']) && $aBrName['TypsCount'] > 0){echo 'CmAjaxBrand';}?> CmTitShow" title="<?=Lng_x($Key)?>" data-brandname="<?=$Key?>">
                                        <div class="CmLogoBrandImg" style="background-image:url(/<?=CM_DIR?>/media/brands/<?=$aBrName['CODE']?>.png)"></div>
                                    </div>
                                <?}?>
                            </div>
                        </div>
                        <div class="CmModelModifWrap">
                            <div class="CmModelListBlWrap">
                            	<div class="CmTitleNameTx CmColorTx CmColorBr"><?=Lng_x('Model')?>,&nbsp;<?=Lng_x('Year')?></div>
                            	<div class="CmModelListOverf" id="idscroll">
                                    <div class="CmModelListBlock">
    	                                <?if(isset($aVehMod) AND count($aVehMod)>0){
    	                                    foreach($aVehMod as $K=>$Model){
    	                                    	foreach ($Model['MODELS'] as $key => $v) {?>
    	                                        <div class="CmModelList CmVehicStyle cmVehicModHov CmColorBgLh" data-pageurl="<?=$aRes['DETAIL_PAGE_URL']?>" data-modcode="<?=$key?>" data-modname="<?=$K?>" data-moduledir="<?=CM_DIR?>">
    	                                            <div class="СmProdModNameTxt CmModelNameClass"><?=$v?></div>
    	                                            <div class="CmModelModif"></div>
    	                                        </div>
    	                                    <?}}
    	                                }?>
    	                            </div>
                                </div>
                                <style type="text/css" media="screen">
                                    <?if(count($v)<10){?>height:auto;<?}?>
                                </style>
                            </div>
                            <div class="CmModifBlWrap">
                                <div class="CmTitleNameTx CmColorTx CmColorBr"><?=Lng_x('Engine')?></div>
                                <div class="CmModifListOverf">
                                    <div class="CmSelectModelTxt">
                                        <svg class="CmLArrowImg CmColorFi" viewBox="0 0 24 24"><path d="M3 12l18-12v24z"/></svg>
                                        <div class="CmSelectModTitl"><?=Lng_x('select_model')?></div>
                                    </div>
                                    <div class="CmModifListBlock"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?}elseif(!$VehiclesAvail && $_REQUEST['ProdVehicle'] == 'Y'){?>
                    <div class="CmNotFoundInfo">
                        <svg class="CmNoInfoPic CmColorFi" viewBox="0 0 512.00037 512">
                            <?=$aPageSVG['CmNotFound']?>
                        </svg>
                        <span class="CmNoInfoTxt"><?=Lng_x('No_info',1)?></span>
                    </div>
                <?}
                //AjaxCut_x('SuitVehicle'); // Makes: </div>?>
            </div>
             <a name="ProductsList"></a>
        </div>
    <?}elseif((!$AnalogsAvail || !$VehiclesAvail) && $_REQUEST['CarModAjaxProdPrice'] == 'Y'){?>
        <div class="CmNotFoundInfo">
            <svg class="CmNoInfoPic CmColorFi" viewBox="0 0 512.00037 512">
                <?=$aPageSVG['CmNotFound']?>
            </svg>
            <span class="CmNoInfoTxt"><?=Lng_x('No_info',1)?></span>
        </div>
    <?}?>
    <?AjaxCut_x('ProdPrice'); // Makes: <div id="CmAjaxBox"> ?>
    <?aprint_x($aRes,'$aRes');
    //aprint_x($aSets,'$aSets');?>

    <?=ShowSEOText_x("BOT")?>
    <?if($aRes['RELATED_PRODUCTS']){?>
        <div class="CmRelProdWrap">
            <div class="CmRelProdTitWr">
                <div class="CmRelProdTitBl CmColorBgL CmColorBr">
                    <span class="CmRelProdTit CmColorTx"><?=Lng_x('Related_products')?></span>
                </div>
            </div>
            <div class="CmRelProdWrapper">
                <?foreach($aRes['RELATED_PRODUCTS'] as $key => $rProd){?>
                    <div class="CmRelProdBlock CmColorBr">
                        <div class="CmRelProdItem">
                            <a href="<?=$rProd['Link']?>">
                                <div class="CmRelProdTitTxt CmColorBg">
                                    <div class="CmRelProdBrandN"><?=$rProd['Brand']?></div>
                                    <div class="CmRelProdArtNum"><?=$rProd['ArtNum']?></div>
                                </div>
                                <div class="CmRelProdContentItemWr">
                                    <div class="CmRelProdItemImg">
                                        <img src="<?=$rProd['Img']?>"/>
                                    </div>
                                    <div class="CmRelProdNamePriceWr">
                                        <div class="CmRelProdItemName" style="<?if(!$rProd['Price']){?>order:2;<?}?>"><?=$rProd['Name']?></div>
                                        <div class="CmRelProdItemPrice">
                                            <?if($rProd['Price']){?>
                                                <span><?=Lng_x('from')?>&nbsp;</span><?=$rProd['Price']?><span class="CmRprodCurr"><?=$rProd['Currency']?></span>
                                            <?}?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?}?>
            </div>
        </div>
    <?}?>
</div>
<?if($aRes['HAVE_CROSSES']){?>
    <div class="CmCrossTitleBl" data-page="<?=$_GET['page']?>">
        <div class="CmTitleCrossText CmColorBgL CmColorBr">
            <span class="CmTextCr CmColorTx"><?=Lng_x('Parts_analog')?>:&nbsp;</span>
            <span class="CmBrArt CmColorTx"><?=$aRes['Brand']?>&nbsp;<?=$aRes['AKey']?></span>
        </div>
    </div>
<?}
if($aSets['NARROW_DESIGN']){?>
	<link rel="stylesheet" type="text/css" href="<?=TEMPLATE_PAGE_DIR?>blocks/mini.css" />
<?}?>
