<?VerifyAccess_x('ProductListList.templ');?>
<?//print_r($_REQUEST);?>
<?
//RATING STARS FUNCTION
function renderStarRating($rating,$maxRating=5,$col) {
    $fullStar = '<svg viewBox="0 0 24 24" width="22" style="fill:#'.$col.'"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z"/></svg>';
    $halfStar = '<svg viewBox="0 0 24 24" width="22" style="fill:#'.$col.'"><path d="M12 5.173l2.335 4.817 5.305.732-3.861 3.71.942 5.27-4.721-2.524v-12.005zm0-4.586l-3.668 7.568-8.332 1.151 6.064 5.828-1.48 8.279 7.416-3.967 7.416 3.966-1.48-8.279 6.064-5.827-8.332-1.15-3.668-7.569z"/></svg>';
    $emptyStar = '<svg viewBox="0 0 24 24" width="22" style="fill:#'.$col.'"><path d="M12 5.173l2.335 4.817 5.305.732-3.861 3.71.942 5.27-4.721-2.524-4.721 2.525.942-5.27-3.861-3.71 5.305-.733 2.335-4.817zm0-4.586l-3.668 7.568-8.332 1.151 6.064 5.828-1.48 8.279 7.416-3.967 7.416 3.966-1.48-8.279 6.064-5.827-8.332-1.15-3.668-7.569z"/></svg>';
    $rating = $rating / 999 * 5.001;
    // $rating = $rating <= $maxRating?$rating:$maxRating;

    $fullStarCount = (int)$rating;
    $halfStarCount = round($rating)-$fullStarCount;
    $emptyStarCount = $maxRating-$fullStarCount-$halfStarCount;

    $html = str_repeat($fullStar,$fullStarCount);
    $html .= str_repeat($halfStar,$halfStarCount);
    $html .= str_repeat($emptyStar,$emptyStarCount);
    $html = '<div class="cmRatingSt_l">'.$html.'</div>';
    return $html;
}
// END RATING STARS FUNCTION

?>
<div class="cm_listView">
    <div class="main_bl">
        <? foreach($aRes['PRODUCTS'] as $PKEY=>$aProd) {?>
            <div class="row_bl CmAdmButsProduct" <?=isAdmButs_Product_x($aProd)?> >
                <div class="CmInnerBlockList">
                    <a href="<?=$aProd['Link']?>" class="CmMobViewBrandNameBlock">
                        <div class="CmProdLinkNameMob" style="color:#<?if($aProd['BColor']){echo $aProd['BColor'];}?> !important;" data-rat="<?=$aProd['Rating']?>">
                            <div class="CmListName" style="color:#<?if($aProd['BColor']){echo $aProd['BColor'].'!important';}else{?>3E3E3E<?}?>;"><?=$aProd['Name']?></div>
                        </div>
                        <div class="CmBrandArtwrap">
                            <div class="CmBranArtCoun" <?if(IsADMIN_x){?>title="ArtID:<?=$aProd['ArtID']?>"<?}?> style="border:1px solid #<?if($aProd['BColor']){echo $aProd['BColor'];}else{echo '808080';}?>;">
                                <div class="brand_link" style="background-color:#<?if($aProd['BColor']){echo $aProd['BColor'];}else{echo '3e3e3e';}?>;"><?=$aProd['Brand']?></div>
                                <span class="CmArtNumText" style="color:#<?if($aProd['BColor']){echo $aProd['BColor'];}else{echo '3e3e3e';}?>;"><?=$aProd['ArtNum']?></span>
                            </div>
                        </div>
                    </a>
                    <div class="CmleftBlWrap" style="border:1px solid #<?if($aProd['BColor'] && $aProd['BColor'] != "FFFFFF"){echo $aProd['BColor'];}else{echo '3e3e3e';}?>; <?if($aSets['NOT_HIDE_PRICES']==1 && $aProd['PRICES']){?>width:190px; min-width:unset;<?}?>">
                        <div class="tit_art" style="background-color:#<?if($aProd['BColor'] && $aProd['BColor'] != "FFFFFF"){echo $aProd['BColor'].'24';}else{echo '3e3e3e';}?>;">
                            <?if($aProd['COUNTRY']){?>
                                <div class="CmCountryFlag">
                                    <div>
                                        <div class="CmCountry_l CmCountryList" title="<?=$aProd['COUNTRY_NAME']?>" style="background-image:url(/<?=CM_DIR?>/media/country/<?=$aProd['COUNTRY']?>.png)"></div>
                                    </div>
                                    <span class="<?if(mb_strlen($aProd['COUNTRY_NAME']) > 18){?>CmTitShow<?}?>" style="color:#<?if($aProd['BColor']){echo $aProd['BColor'];}else{echo 'ffffff';}?>;" <?if(mb_strlen($aProd['COUNTRY_NAME']) > 18){?>title="<?=$aProd['COUNTRY_NAME']?>"<?}?>><?=$aProd['COUNTRY_NAME']?></span>
                                </div>
                            <?}?>
                            <div class="CmStarRatingInfo">
                                <?//=$aProd['Rating']?>
                                <?=renderStarRating($aProd['Rating'], 5, $aProd['BColor']?$aProd['BColor']:'fa6a00')?>
                            </div>
                        </div>
                        <div class="CmImgLogoWrapBlock">
                            <div class="CmEditImgCross">
                                <? EditImgBut_x($aProd['Pom']); //Admin Edit Images button ?>
    							<? EditLinkBut_x($aProd['Pom']); //Admin Edit analogs group button ?>
								<? EditProdBut_x($aProd['Pom']); //Admin Edit Product data button ?>
                            </div>
                            <div class="CmImgLogoProd" <?if(($aSets['NOT_HIDE_PRICES']==1 && $aProd['PRICES'])||(!$aProd['Schema_src'] && !$aProd['Image'])){?>style="grid-template-columns:1fr;"<?}?>>
                                <div class="CmBrandLogoLeftBl" style="background-image:url(<?=$aProd['Logo']?>)"></div>
                                <?if($aProd['Schema_src'] || $aProd['Image']){?>
	                                <div class="img_bl img_blHov cm_curMove ProductImg" style="<?if($aSets['NOT_HIDE_PRICES']==1 && $aProd['PRICES']){?>width:auto; min-width:unset;<?}?>">
	                                    <?if($aProd['Schema_src']&&$aProd['Schema_src']!=''){?>
	                                        <div class="CmSchemaCoordsWrap">
	                                            <img class="CmProdIm" src="<?=$aProd['Schema_src']?>" alt="<?=$aProd['Brand'].' '.$aProd['ArtNum'].' - '.$aProd['Name'].' '.$_SERVER['SERVER_NAME']?>" data-pictype="schema">
	                                            <?=$aProd['Schema_html']?>
	                                        </div>
	                                    <?}else if($aProd['Image']&&$aProd['Image']!=''){?>
	                                        <img class="CmProdIm" src="<?=$aProd['Image']?>" alt="<?=$aProd['Brand'].' '.$aProd['ArtNum'].' - '.$aProd['Name'].' '.$_SERVER['SERVER_NAME']?>">
	                                    <?}?>
	                                </div>
                                <?}?>
                            </div>
                        </div>
                    </div>
                    <div class="CmDescInfoPriceBlock" <?//if($aSets['NOT_HIDE_PRICES']==1 && count($aProd['PRICES'])>1){?><?//}?>>
                        <div class="desc_bl">
                            <div class="CmNameInfoPropsWrapBl">
                                <a href="<?=$aProd['Link']?>" class="CmNameInfoWrapBl">
                                    <div class="CmBrandArtwrap">
                                        <div class="CmBranArtCoun" <?if(IsADMIN_x){?>title="ArtID:<?=$aProd['ArtID']?>"<?}?>>
                                            <div class="brand_link" style="background-color:#<?if($aProd['BColor'] && $aProd['BColor'] != "FFFFFF"){echo $aProd['BColor'];}else{echo '3e3e3e';}?>;"><?=$aProd['Brand']?></div>
                                            <span class="CmArtNumText" style="color:#<?if($aProd['BColor'] && $aProd['BColor'] != "FFFFFF"){echo $aProd['BColor'];}else{echo '3e3e3e';}?>;border:1px solid #<?if($aProd['BColor'] && $aProd['BColor'] != "FFFFFF"){echo $aProd['BColor'];}else{echo '808080';}?>;"><?=$aProd['ArtNum']?></span>
                                        </div>
                                    </div>
                                    <div class="CmRateNameBlock">
                                        <div class="CmProductLinkArtName" style="color:#<?if($aProd['BColor']){echo $aProd['BColor'];}?> !important;">
                                            <span class="CmListName"><?=$aProd['Name']?></span>
                                            <?if($aProd['Names']){?>
                                                <span class="CmAdditName" style="color:#<?if($aProd['BColor']){echo $aProd['BColor'].'!important';}else{?>3E3E3E<?}?>;"><?=current($aProd['Names'])?></span>
                                            <?}?>
                                        </div>
                                    </div>
                                </a>
                                <div class="CmFitInfoBlWrap">
                                    <?if($aProd['FitPos']){?>
                                        <div class="CmWrapSideSet">
                                            <div class="CmInnerSideBl">
                                                <div class="CmFrontSideBl">
                                                    <div class="CmFrontLeft CmVehicWheel CmFitPos<?=$aProd['FitPos']['FL']?>"></div>
                                                    <div class="CmMiddleBl"></div>
                                                    <div class="CmMiddleCenterF"></div>
                                                    <div class="CmMiddleBl"></div>
                                                    <div class="CmFrontRight CmVehicWheel CmFitPos<?=$aProd['FitPos']['FR']?>"></div>
                                                </div>
                                                <div class="CmShaftCent"></div>
                                                <div class="CmRearSideBl">
                                                    <div class="CmRearLeft CmVehicWheel CmFitPos<?=$aProd['FitPos']['RL']?>"></div>
                                                    <div class="CmMiddleBl"></div>
                                                    <div class="CmMiddleCenterR"></div>
                                                    <div class="CmMiddleBl"></div>
                                                    <div class="CmRearRight CmVehicWheel CmFitPos<?=$aProd['FitPos']['RR']?>"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?}?>
        							<?//if($aProd['FitPos']){?>
        								<!-- <div class="CmFitPosBox">
        									<div class="CmFitPosMap">
        										<div class="CmFitPosPoint CmFitPosFL CmFitPos<?=$aProd['FitPos']['FL']?>"></div>
        										<div class="CmFitPosPoint CmFitPosFR CmFitPos<?=$aProd['FitPos']['FR']?>"></div>
        										<div class="CmFitPosPoint CmFitPosMid CmFitPos"></div>
        										<div class="CmFitPosPoint CmFitPosRL CmFitPos<?=$aProd['FitPos']['RL']?>"></div>
        										<div class="CmFitPosPoint CmFitPosRR CmFitPos<?=$aProd['FitPos']['RR']?>"></div>
        									</div>
        									<div class="CmFitPosText">
        										<?=Lng_x('Installation side',1)?>:<br>
        										<span><?=$aProd['FitPos']['Text']?></span>
        									</div>
        								</div> -->
        							<?//}?>
                                    <div class="info_bl" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>" data-link="<?=$aProd['Link']?>">
                                        <div class="ProductInfoOe infoIcon CmTitShow" data-furl="<?=PROTOCOL_DOMAIN_x?><?=$aProd['Link']?>" data-moduledir="<?=CM_DIR?>" data-tab="Articles" title="<?=Lng_x('OE_Numbers');?>">
                                            <svg class="cm_svgInfo CmColorFih" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-.001 5.75c.69 0 1.251.56 1.251 1.25s-.561 1.25-1.251 1.25-1.249-.56-1.249-1.25.559-1.25 1.249-1.25zm2.001 12.25h-4v-1c.484-.179 1-.201 1-.735v-4.467c0-.534-.516-.618-1-.797v-1h3v6.265c0 .535.517.558 1 .735v.999z"/></svg>
                                        </div>
                                        <div class="ProductInfoSuit carIcon CmTitShow" data-furl="<?=PROTOCOL_DOMAIN_x?><?=$aProd['Link']?>" data-moduledir="<?=CM_DIR?>" data-tab="Vehicles" title="<?=Lng_x('Suitable_vehicles');?>">
                                            <svg class="material-icon car_x CmColorFih" viewBox="0 0 24 24">
                                                <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                                            </svg>
                                        </div>
                                        <div class="analogButt cm_evenButt CmTitShow" title="<?=Lng_x('Lookup_analogues');?>">
                                            <a class="CmLookAnalogHook" href="<?=$aProd['Link']?>#ProductsList">
                                                <svg class="material-icon analog_x CmColorFih" viewBox="0 -2 24 24">
                                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="CmNamePropsBlock">
                                    <?if($aProd['TrdNums']){?>
                                        <div class="CmListTrdNums"><b><?=Lng_x('Trade_numbers',1)?></b>: <?=$aProd['TrdNums']?></div>
                                    <?}?>
                                    <?if($aProd['PartsIncluded']){?>
                                        <div class="CmListTrdNums"><b><?=Lng_x('Parts_included',1)?></b>: <?=$aProd['PartsIncluded']?></div>
                                    <?}?>
                                    <?if($aProd['CRITERIAS']){
                                      $countCr = count($aRes['CRITERIAS']);
                                      $resCr = count($aRes['CRITERIAS']) - 4;?>
                                        <div class="desc_3">
                                            <ul class="props_l c_Tx">
                                            <?foreach($aProd['CRITERIAS'] as $CriID=>$aCri){?>
                                                <li><?=$aCri['Name']?>: <?=$aCri['Value']?> <?=$aCri['Unit']?></li>
                                            <?}?>
                                            </ul>
                                        </div>
                                    <?}?>
                                    <?if($aProd['PROPERTIES']){
                                        $i = 0;
                                        foreach($aProd['PROPERTIES'] as $propN => $propV) {$i++;
                                            if($i<=2){?>
                                                <div class="CmListProps">
                                                    <span><?=$propN?></span> <span><?=$propV?></span>
                                                </div>
                                            <?continue;}?>
                                            <div class="CmListProps_2">
                                                <span><?=$propN?></span> <span><?=$propV?></span>
                                            </div>
                                        <?}
                                    }?>
                                </div>
                                <?if(count($aProd['PROPERTIES'])>2){?>
                                    <div class="CmShowPrBl" style="color:#<?if($aProd['BColor']){echo $aProd['BColor'];}else{?>3E3E3E<?}?>;" data-hide="<?=Lng_x('Hide')?>&nbsp;&#9650;" data-show="<?=Lng_x('Show_more')?>(<?=count($aProd['PROPERTIES'])-2?>)&nbsp;&#9660;"><?=Lng_x('Show_more')?>&nbsp;(<?=count($aProd['PROPERTIES'])-2?>)&#9660;</div>
                                <?}?>
                            </div>
                        </div>
                        <!-- PRICE BLOCK -->
                        <div class="rightBlock" <?if($aProd['WsAjax']){?>data-dir="<?=CM_DIR?>" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>"<?}?>>
                            <?$PodPriceNum++;
                            $ProductURL = GetProductLink_x($aProd); //AddCart URL
                            include(dirname(__DIR__).'/product_page/blocks/prices.php');?>
                            <?AdmWsRequests_x($aProd); //WebServices messages for Admin?>
                            <div class="info_bl_MobView" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>" data-link="<?=$aProd['Link']?>">
                                <div class="ProductInfoOe infoBlockElem infoIconMob CmTitShow" data-furl="<?=PROTOCOL_DOMAIN_x?><?=$aProd['Link']?>" data-moduledir="<?=CM_DIR?>" data-tab="Articles" title="<?=Lng_x('OE_Numbers');?>">
                                    <svg class="cm_svgInfoMob CmColorFih" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-.001 5.75c.69 0 1.251.56 1.251 1.25s-.561 1.25-1.251 1.25-1.249-.56-1.249-1.25.559-1.25 1.249-1.25zm2.001 12.25h-4v-1c.484-.179 1-.201 1-.735v-4.467c0-.534-.516-.618-1-.797v-1h3v6.265c0 .535.517.558 1 .735v.999z"/></svg>
                                </div>
                                <div class="ProductInfoSuit infoBlockElem carIconMob CmTitShow" data-furl="<?=PROTOCOL_DOMAIN_x?><?=$aProd['Link']?>" data-moduledir="<?=CM_DIR?>" data-tab="Vehicles" title="<?=Lng_x('Suitable_vehicles');?>">
                                    <svg class="material-icon car_xMob CmColorFih" viewBox="0 0 24 24">
                                        <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                                    </svg>
                                </div>
                                <div class="infoBlockElem analogButtMob cm_evenButtMob">
                                  <a class="CmLookAnalogHook" href="<?=$aProd['Link']?>#ProductsList">
                                    <svg class="material-icon analog_xMob" viewBox="0 -2 24 24">
                                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
                                    </svg>
                                  </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		<?//aprint_x($aProd, '$aProd');
		}?>
	</div>
</div>
