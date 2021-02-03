<?VerifyAccess_x('ProductListGrid.templ'); ?>
<div class="cm_gridView">
    <div class="wrapCard">
    <?foreach($aRes['PRODUCTS'] as $PKEY=>$aProd){?>
        <div class="partCard CmColorBrh CmAdmButsProduct" <?=isAdmButs_Product_x($aProd)?> >
            <?if($aProd['Brand']){?>
                <div class="CmTitArt_c" style="background-color:#<?if($aProd['BColor']){echo $aProd['BColor'];}else{echo '3e3e3e';}?>;">
                    <div class="CmBranArtCoun_c">
                        <a href="<?=$aProd['Link']?>" <?if(IsADMIN_x){?>title="ArtID:<?=$aProd['ArtID']?>"<?}?> >
                            <div class="CmBrandLink_c" style="background-color:#<?if($aProd['BColor']){echo $aProd['BColor'];}else{echo '3e3e3e';}?>; border:1px solid #<?if($aProd['BColor']){echo $aProd['BColor'];}else{echo '3e3e3e';}?>;"><?=$aProd['Brand']?></div>
                        </a>
                    </div>
                    <?if($aProd['COUNTRY']){?>
                        <div class="CmCountry_c CmCountryList" title="<?=$aProd['COUNTRY_NAME']?>" style="background-image:url(/<?=CM_DIR?>/media/country/<?=$aProd['COUNTRY']?>.png)"></div>
                    <?}?>
                </div>
            <?}?>
            <div class="CmImgLogoProd_c">
                <div class="CmBrandLogoLeftBl" style="background-image:url(<?=$aProd['Logo']?>)"></div>
                <?if($aProd['Schema_src'] || $aProd['Image']){?>
                    <div class="img_bl img_blHov cm_curMove ProductImg">
                        <?if($aProd['Schema_src']&&$aProd['Schema_src']!=''){?>
                            <div class="CmSchemaCoordsWrap">
                                <img class="CmProdIm" src="<?=$aProd['Schema_src']?>" alt="<?=$aProd['Brand'].' '.$aProd['ArtNum'].' - '.$aProd['Name'].' '.$_SERVER['SERVER_NAME']?>" data-pictype="schema" style="width:auto;">
                                <div class="CmCoordBlock"><?=$aProd['Schema_html']?></div>
                            </div>
                        <?}else{?>
                            <img class="CmProdIm" src="<?=$aProd['Image']?>" alt="<?=$aProd['Brand'].' '.$aProd['ArtNum'].' - '.$aProd['Name'].' '.$_SERVER['SERVER_NAME']?>">
                        <?}?>
                    </div>
                <?}?>
			</div>
            <div class="CmInfoBlockGrid" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>" data-link="<?=$aProd['Link']?>">
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
            <?if($aProd['Brand']||$aProd['ArtNum']||$aProd['Name']){ ?>
                <a href="<?=$aProd['Link']?>" class="linkBrandArt"  style="color:<?if($aProd['BColor']){echo $aProd['BColor'];}else{echo '3e3e3e';}?>;">
                    <div class="artBrandName">
                        <div class="titArticle">
                            <div class="brand_c"><?=$aProd['Brand']?></div>
                            <div class="artic_c"><?=$aProd['ArtNum']?></div>
                        </div>
                        <div class="partLink">
                            <?=$aProd['Name']?>&#9658;
                        </div>
                    </div>
                </a>
            <?}else{?><div class="CmEmptyGridBlock"></div><?}?>
            <div class="CmListPrTab_c" <?if($aProd['WsAjax']){?>data-dir="<?=CM_DIR?>" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>"<?}?> data-act="<?=$aRes['ACTIVE_TAB']?>">
                <?$PodPriceNum++;
				$ProductURL = GetProductLink_x($aProd); //AddCart URL
                include(dirname(__DIR__).'/product_page/blocks/prices.php');?>
            </div>
        </div>
    <?}?>
    </div>
</div>
<?//aprint_x($aRes,'ares');?>
