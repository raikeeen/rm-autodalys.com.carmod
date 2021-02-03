<?php
/*
$Selector_Position -> Main block position for CSS: Left / Right
SeLng_x() -> Function to show phrases at selected language. Phrases from /carparts/add/mselect/langs/..
$aManufGroups -> Array of Manufacturers by Groups: Pass, Comm, Moto
$aFavManuf -> Array of Favorite Manufacturers
$IsDisab.. -> String variables: contains CSS class "CmMSelectDisabled" or empty
$MfGroupsCnt -> Int variable: manufacturer groups count (Pass, Comm, Moto)
$ModelsMesg -> String variable, phrase: "No models of this year"
SeModelsAjaxStart() -> Function to cut top & bottom of AJAX response
$aModels -> Array of Models
FURL_x - Constant: real CarMod "Friendly URL" link, like "/en/carparts/" (from CarMod /config.php script)
MsLang - Constant: currently selected Language code (en,ru,..)
*/
?>
<div class="CmSelModelImg">
    <div class="CmMobGarlock">
        <div class="CmSelMyGarOpen CmColorBgh  CmColorBr CmColorBgL" data-vpid="<?=$vTypID?>" style="<?if(($_COOKIE['CarModGarage']&&preg_match_all('/'.$vTypID.'/', $_COOKIE['CarModGarage']))||($_COOKIE['CarModGarage']&&!$vPicture)){?>display:flex;<?}?>">
            <span class="CmMyGarage"><?=SeLng_x('My_Garage')?></span>
            <svg class="CmGarageFull" viewBox="0 0 252.094 252.094">
                <path d="M196.979,146.785c-1.091,0-2.214,0.157-3.338,0.467l-4.228,1.165l-6.229-15.173c-3.492-8.506-13.814-15.426-23.01-15.426
                    H91.808c-9.195,0-19.518,6.921-23.009,15.427l-6.218,15.145l-4.127-1.137c-1.124-0.31-2.247-0.467-3.338-0.467
                    c-5.485,0-9.467,3.935-9.467,9.356c0,5.352,3.906,9.858,9.2,11.211c-2.903,8.017-5.159,20.034-5.159,27.929v32.287
                    c0,6.893,5.607,12.5,12.5,12.5h4.583c6.893,0,12.5-5.607,12.5-12.5v-6.04h93.435v6.04c0,6.893,5.607,12.5,12.5,12.5h4.585
                    c6.893,0,12.5-5.607,12.5-12.5v-32.287c0-7.887-2.252-19.888-5.15-27.905c5.346-1.32,9.303-5.85,9.303-11.235
                    C206.445,150.72,202.464,146.785,196.979,146.785z M70.352,159.384l10.161-24.754c2.089-5.088,8.298-9.251,13.798-9.251h63.363
                    c5.5,0,11.709,4.163,13.798,9.251l10.161,24.754c2.089,5.088-0.702,9.251-6.202,9.251H76.554
                    C71.054,168.635,68.263,164.472,70.352,159.384z M97.292,199.635c0,2.75-2.25,5-5,5H71.554c-2.75,0-5-2.25-5-5v-8.271
                    c0-2.75,2.25-5,5-5h20.738c2.75,0,5,2.25,5,5V199.635z M185.203,199.635c0,2.75-2.25,5-5,5h-20.736c-2.75,0-5-2.25-5-5v-8.271
                    c0-2.75,2.25-5,5-5h20.736c2.75,0,5,2.25,5,5V199.635z"/>
                <path d="M246.545,71.538L131.625,4.175c-1.525-0.894-3.506-1.386-5.578-1.386c-2.072,0-4.053,0.492-5.578,1.386L5.549,71.538
                    C2.386,73.392,0,77.556,0,81.223v160.582c0,4.135,3.364,7.5,7.5,7.5h12.912c4.136,0,7.5-3.365,7.5-7.5V105.917
                    c0-1.378,1.121-2.5,2.5-2.5h191.268c1.379,0,2.5,1.122,2.5,2.5v135.888c0,4.135,3.364,7.5,7.5,7.5h12.913
                    c4.136,0,7.5-3.365,7.5-7.5V81.223C252.094,77.556,249.708,73.392,246.545,71.538z"/>
            </svg>
        </div>
        <div class="CmSelAddGarBlock" data-vpid="<?=$vTypID?>" style="<?if(!preg_match_all('/'.$vTypID.'/', $_COOKIE['CarModGarage'])&&$vPicture){?>display:flex;<?}?>">
            <span class="CmSelAddGarage CmColorTx"><?=SeLng_x('Add_Garage')?></span>
            <div class="CmColorBg CmColorBr CmSelGarOpen CmSelGarSvgBlock">
                <svg class="CmGarageSvg" viewBox="0 0 435.905 435.905" <?if(!$_COOKIE['CarModGarage']){?>style="display:block;"<?}?>>
                    <path d="M430.902,120.789l-208.1-115.6c-3-1.7-6.7-1.7-9.7,0l-208,115.6c-3.2,1.8-5.1,5.1-5.1,8.7v292.7c-0.1,5.4,4.2,9.8,9.6,9.8
                        c0.1,0,0.2,0,0.2,0h416.3c5.4,0.1,9.8-4.2,9.8-9.6c0-0.1,0-0.2,0-0.2v-292.6C436.002,125.889,434.102,122.589,430.902,120.789z
                        M358.002,412.089h-280v-41h280V412.089z M358.002,351.089h-280v-38h280V351.089z M358.002,293.089h-280v-38h280V293.089z
                        M358.002,235.089h-280v-38h280V235.089z M416.002,412.089h-38v-224.7c0-5.5-4.1-10.3-9.7-10.3h-300.6c-5.5,0-9.7,4.8-9.7,10.3
                        v224.7h-38v-276.7l198-110.1l198,110.1V412.089z"/>
                </svg>
                <svg class="CmSelGarageFull CmColorFih" viewBox="0 0 252.094 252.094" <?if($_COOKIE['CarModGarage']){?>style="display:block;"<?}?>>
                    <path d="M196.979,146.785c-1.091,0-2.214,0.157-3.338,0.467l-4.228,1.165l-6.229-15.173c-3.492-8.506-13.814-15.426-23.01-15.426
                        H91.808c-9.195,0-19.518,6.921-23.009,15.427l-6.218,15.145l-4.127-1.137c-1.124-0.31-2.247-0.467-3.338-0.467
                        c-5.485,0-9.467,3.935-9.467,9.356c0,5.352,3.906,9.858,9.2,11.211c-2.903,8.017-5.159,20.034-5.159,27.929v32.287
                        c0,6.893,5.607,12.5,12.5,12.5h4.583c6.893,0,12.5-5.607,12.5-12.5v-6.04h93.435v6.04c0,6.893,5.607,12.5,12.5,12.5h4.585
                        c6.893,0,12.5-5.607,12.5-12.5v-32.287c0-7.887-2.252-19.888-5.15-27.905c5.346-1.32,9.303-5.85,9.303-11.235
                        C206.445,150.72,202.464,146.785,196.979,146.785z M70.352,159.384l10.161-24.754c2.089-5.088,8.298-9.251,13.798-9.251h63.363
                        c5.5,0,11.709,4.163,13.798,9.251l10.161,24.754c2.089,5.088-0.702,9.251-6.202,9.251H76.554
                        C71.054,168.635,68.263,164.472,70.352,159.384z M97.292,199.635c0,2.75-2.25,5-5,5H71.554c-2.75,0-5-2.25-5-5v-8.271
                        c0-2.75,2.25-5,5-5h20.738c2.75,0,5,2.25,5,5V199.635z M185.203,199.635c0,2.75-2.25,5-5,5h-20.736c-2.75,0-5-2.25-5-5v-8.271
                        c0-2.75,2.25-5,5-5h20.736c2.75,0,5,2.25,5,5V199.635z"/>
                    <path d="M246.545,71.538L131.625,4.175c-1.525-0.894-3.506-1.386-5.578-1.386c-2.072,0-4.053,0.492-5.578,1.386L5.549,71.538
                        C2.386,73.392,0,77.556,0,81.223v160.582c0,4.135,3.364,7.5,7.5,7.5h12.912c4.136,0,7.5-3.365,7.5-7.5V105.917
                        c0-1.378,1.121-2.5,2.5-2.5h191.268c1.379,0,2.5,1.122,2.5,2.5v135.888c0,4.135,3.364,7.5,7.5,7.5h12.913
                        c4.136,0,7.5-3.365,7.5-7.5V81.223C252.094,77.556,249.708,73.392,246.545,71.538z"/>
                </svg>
            </div>
        </div>
        <div class="CmGarDropDown CmColorBr">
            <div class="CmSelGarTitle CmColorBg">
                <span class="CmSelTitleTxt"><?=SeLng_x('My_Garage')?></span>
                <svg class="CmSelCloseGar" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm6 16.538l-4.592-4.548 4.546-4.587-1.416-1.403-4.545 4.589-4.588-4.543-1.405 1.405 4.593 4.552-4.547 4.592 1.405 1.405 4.555-4.596 4.591 4.55 1.403-1.416z"/></svg>
            </div>
            <!-- <div class="CmSelFavorVehicle">
                        <div class="CmSelFavVehTitle CmColorTx CmColorBgL"><?=SeLng_x('Favorite')?></div>
                        <div class="CmSelFavItemWrap CmColorBr"></div>
                         <span class="CmSelFavBut CmTitShow" title="<?=SeLng_x('Add_to_favorite')?>" data-vpid="'+vIdMod+'"><svg class="CmSelFavSvg CmColorFih"><use xlink:href="#vaf_icon"></use></svg></span>
                </div> -->
            <div class="CmModInGarItem">
                <div class="CmModGarageWrap">
                    <?if(!$_COOKIE['CarModGarage']){?>
                        <div class='CmSelGarEmpMess CmColorTx'><svg class='CmGarEmptySvg' viewBox='0 0 435.905 435.905'><path d='M430.902,120.789l-208.1-115.6c-3-1.7-6.7-1.7-9.7,0l-208,115.6c-3.2,1.8-5.1,5.1-5.1,8.7v292.7c-0.1,5.4,4.2,9.8,9.6,9.8c0.1,0,0.2,0,0.2,0h416.3c5.4,0.1,9.8-4.2,9.8-9.6c0-0.1,0-0.2,0-0.2v-292.6C436.002,125.889,434.102,122.589,430.902,120.789zM358.002,412.089h-280v-41h280V412.089z M358.002,351.089h-280v-38h280V351.089z M358.002,293.089h-280v-38h280V293.089zM358.002,235.089h-280v-38h280V235.089z M416.002,412.089h-38v-224.7c0-5.5-4.1-10.3-9.7-10.3h-300.6c-5.5,0-9.7,4.8-9.7,10.3v224.7h-38v-276.7l198-110.1l198,110.1V412.089z'/></svg><span class='CmSelEmptyTxt'><?=SeLng_x('Empty')?></span></div>
                    <?}?>
                </div>
            </div>
            <svg class="CmSelCloseGar" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm6 16.538l-4.592-4.548 4.546-4.587-1.416-1.403-4.545 4.589-4.588-4.543-1.405 1.405 4.593 4.552-4.547 4.592 1.405 1.405 4.555-4.596 4.591 4.55 1.403-1.416z"/></svg>
        </div>
    </div>
    <?if($vPicture){?>
        <a href="<?=$SelectedLink?>">
            <div class="CmMSelectVehicle nav-buttons" style="<?if($vPicture){?>background-image:url(<?=$vPicture?>);<?}else{?>background-position:0px -19px;<?}?>">
            </div>
        </a>
    <?}else{?>
        <div class="CmMSelectVehicle nav-buttons" style="<?if($vPicture){?>background-image:url(<?=$vPicture?>);<?}else{?>background-position:0px -19px;<?}?>">
        </div>
    <?}?>
    <?if($vPicture){
        if($DisplayReset){?>
            <svg class="CmResetSettings CmColorFih CmTitShow" title="<?=SeLng_x('Reset_model')?>" data-urix="<?=PROTOCOL_DOMAIN_x?><?=$_SERVER['REQUEST_URI']?>" viewBox="0 0 475.2 475.2" width="16" height="16"><path d="M405.6,69.6C360.7,24.7,301.1,0,237.6,0s-123.1,24.7-168,69.6S0,174.1,0,237.6s24.7,123.1,69.6,168s104.5,69.6,168,69.6    s123.1-24.7,168-69.6s69.6-104.5,69.6-168S450.5,114.5,405.6,69.6z M386.5,386.5c-39.8,39.8-92.7,61.7-148.9,61.7    s-109.1-21.9-148.9-61.7c-82.1-82.1-82.1-215.7,0-297.8C128.5,48.9,181.4,27,237.6,27s109.1,21.9,148.9,61.7    C468.6,170.8,468.6,304.4,386.5,386.5z"/><path d="M342.3,132.9c-5.3-5.3-13.8-5.3-19.1,0l-85.6,85.6L152,132.9c-5.3-5.3-13.8-5.3-19.1,0c-5.3,5.3-5.3,13.8,0,19.1    l85.6,85.6l-85.6,85.6c-5.3,5.3-5.3,13.8,0,19.1c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4l85.6-85.6l85.6,85.6c2.6,2.6,6.1,4,9.5,4    c3.5,0,6.9-1.3,9.5-4c5.3-5.3,5.3-13.8,0-19.1l-85.4-85.6l85.6-85.6C347.6,146.7,347.6,138.2,342.3,132.9z"/></svg>
        <?}
    }?>
</div>
<div id="CmModSelector" class="CmMSelectTable" data-vehicle="<?if($vPicture){?>Y<?}?>">
            <div class="row">
                <div class="col-12 col-sm-12 col-sp-12 col-md-12 col-lg-12 col-xl-12 row-selector-drop">
            <div class="CmMSelectBox">
                <div class="CmMSelectBut <?=$IsDisabManuf?>" id="CmMS_BoxManuf"><div class="CmSelManufNameBl"><span class="CmColorTx" id="CmMS_Manuf" data-value="<?=$sManuf?>"><?=$vManuf?></span></div> &#9660;</div>
                <div class="CmMSelectDown CmMSelectDown<?=$Selector_Position?> CmMSelectDownManuf" id="CmMS_DdManuf">
                        <?if($MfGroupsCnt>1){?>
                            <div class="CmColorBr CmMSelectGroups">
                                <?$GrActive = 'CmMSelectGrActive CmColorTxi';
                                foreach($aManufGroups as $Group=>$aManufs){?>
                                    <div class="CmMSelectGrTab <?=$GrActive?>" data-group="<?=$Group?>"><?=SeLng_x('Group_'.$Group);?></div><?
                                    $GrActive = '';
                                }?>
                            </div><?
                        }
                        foreach($aManufGroups as $Group=>$aManufs){
                            ?><div id="Cm<?=$Group?>Tab" class="CmMSelectMfTab" <?=$IsDisplayTab?> >
                                <div class="CmManufInner">
                                    <?foreach($aManufs as $Code=>$Name){?>
                                        <div class="CmMS_Option CmMSelectManuf <?if(in_array($Name,$aFavManuf)){?>CmColorBgL<?}?>" data-type="Manuf" data-option="<?=$Code?>"><?=$Name?></div><?
                                    }?>
                                </div>
                            </div>
                            <?
                            if($MfGroupsCnt>1){$IsDisplayTab='style="display:none;"';}
                        }?>
                </div>
            </div>
                </div>

                <div class="col-12 col-sm-12 col-sp-12 col-md-12 col-lg-12 col-xl-12 row-selector-drop">
            <div class="CmMSelectBox" style="z-index: 775">
                <div class="CmMSelectBut <?=$IsDisabModel?>" id="CmMS_BoxModel"><div class="CmSelModNameBl"><span class="<?if($IsDisabModel){?>CmColorTxiA<?}else{?>CmColorTx<?}?>" id="CmMS_Model" data-value="<?=$sModel?>"><?=$vModel?></span></div> &#9660;</div>
                <div class="CmModelSelWrap CmMSelectDown CmMSelectDown<?=$Selector_Position?>" id="CmMS_DdModel">
                    <?SeModelsAjaxStart();
                    echo $ModelsMesg;
                    if(count($aModels)>0){?>
                        <div class="CmMSelectInner">
                            <?foreach($aModels as $ModID=>$ModName){
                                ?><div class="CmColorBgh CmMS_Option CmMSelectList" data-type="Model" data-option="<?=$ModID?>"><?=$ModName?></div><?
                            }?>
                        </div>
                    <?}SeModelsAjaxEnd();?>
                </div>
            </div>
                </div>

                <div class="col-12 col-sm-12 col-sp-12 col-md-12 col-lg-12 col-xl-12 row-selector-drop">
            <div class="CmMSelectBox" style="z-index: 774">
                <div class="CmMSelectBut <?=$IsDisabType?>" id="CmMS_BoxType"><div class="CmSelTypeNameBl"><span class="<?if($IsDisabModel){?>CmColorTxiA<?}else{?>CmColorTx<?}?>" id="CmMS_Type" data-value="<?=$sType?>"><?=$vType?></span></div> &#9660;</div>
                <div class="CmEngineSelWrap CmMSelectDown CmMSelectDown<?=$Selector_Position?>" id="CmMS_DdType">
                    <?SeTypesAjaxStart();
                    echo $TypesMesg;
                    if(count($aTypes)>0){//print_r($aTypes);?>
                        <div class="CmMSelectInner">
                            <?foreach($aTypes as $TypID=>$TypName){?>
                                <div class="CmColorBgh CmMS_Option CmMSelectList" data-type="Type" data-option="<?=$TypID?>"><?=$TypName?></div>
                            <?}?>
                        </div>
                    <?}SeTypesAjaxEnd();?>
                </div>
            </div>
                </div>

                <div class="col-12 col-sm-12 col-sp-12 col-md-12 col-lg-12 col-xl-12 row-selector-drop">
        <div class="CmSelModNameGarButWrap">
            <?if(!$vPicture){?>
                <div class="CmSelModelTxt CmColorTxi">
                    <div class="CmSelModPic">
                        <svg class="CmSelModSvg CmColorFi" viewBox="0 0 24 24"><path d="M24 22h-24l12-20z"/></svg>
                    </div>
                    <span><?=SeLng_x('Select_vehicle')?></span>
               </div>
            <?}?>
            <div class="CmGarageBlock">
                <div class="CmSelMyGarOpen CmColorBr CmColorBgL" data-vpid="<?=$vTypID?>" style="<?if(($_COOKIE['CarModGarage']&&preg_match_all('/'.$vTypID.'/', $_COOKIE['CarModGarage']))||($_COOKIE['CarModGarage']&&!$vPicture)){?>display:flex;<?}?>">
                    <span class="CmMyGarage CmColorTx"><?=SeLng_x('My_Garage')?></span>
                    <svg class="CmGarageFull CmColorFi" viewBox="0 0 252.094 252.094">
                        <path d="M196.979,146.785c-1.091,0-2.214,0.157-3.338,0.467l-4.228,1.165l-6.229-15.173c-3.492-8.506-13.814-15.426-23.01-15.426
                        H91.808c-9.195,0-19.518,6.921-23.009,15.427l-6.218,15.145l-4.127-1.137c-1.124-0.31-2.247-0.467-3.338-0.467
                        c-5.485,0-9.467,3.935-9.467,9.356c0,5.352,3.906,9.858,9.2,11.211c-2.903,8.017-5.159,20.034-5.159,27.929v32.287
                        c0,6.893,5.607,12.5,12.5,12.5h4.583c6.893,0,12.5-5.607,12.5-12.5v-6.04h93.435v6.04c0,6.893,5.607,12.5,12.5,12.5h4.585
                        c6.893,0,12.5-5.607,12.5-12.5v-32.287c0-7.887-2.252-19.888-5.15-27.905c5.346-1.32,9.303-5.85,9.303-11.235
                        C206.445,150.72,202.464,146.785,196.979,146.785z M70.352,159.384l10.161-24.754c2.089-5.088,8.298-9.251,13.798-9.251h63.363
                        c5.5,0,11.709,4.163,13.798,9.251l10.161,24.754c2.089,5.088-0.702,9.251-6.202,9.251H76.554
                        C71.054,168.635,68.263,164.472,70.352,159.384z M97.292,199.635c0,2.75-2.25,5-5,5H71.554c-2.75,0-5-2.25-5-5v-8.271
                        c0-2.75,2.25-5,5-5h20.738c2.75,0,5,2.25,5,5V199.635z M185.203,199.635c0,2.75-2.25,5-5,5h-20.736c-2.75,0-5-2.25-5-5v-8.271
                        c0-2.75,2.25-5,5-5h20.736c2.75,0,5,2.25,5,5V199.635z"/>
                        <path d="M246.545,71.538L131.625,4.175c-1.525-0.894-3.506-1.386-5.578-1.386c-2.072,0-4.053,0.492-5.578,1.386L5.549,71.538
                        C2.386,73.392,0,77.556,0,81.223v160.582c0,4.135,3.364,7.5,7.5,7.5h12.912c4.136,0,7.5-3.365,7.5-7.5V105.917
                        c0-1.378,1.121-2.5,2.5-2.5h191.268c1.379,0,2.5,1.122,2.5,2.5v135.888c0,4.135,3.364,7.5,7.5,7.5h12.913
                        c4.136,0,7.5-3.365,7.5-7.5V81.223C252.094,77.556,249.708,73.392,246.545,71.538z"/>
                    </svg>
                </div>
                <div class="CmSelAddGarBlock" data-vpid="<?=$vTypID?>" style="<?if(!preg_match_all('/'.$vTypID.'/', $_COOKIE['CarModGarage'])&&$vPicture){?>display:flex;<?}?>">
                    <span class="CmSelAddGarage CmColorTx"><?=SeLng_x('Add_Garage')?></span>
                    <div class="CmColorBr CmSelGarOpen CmSelGarSvgBlock">
                        <svg class="CmGarageSvg" viewBox="0 0 435.905 435.905" <?if(!$_COOKIE['CarModGarage']){?>style="display:block;"<?}?>>
                            <path d="M430.902,120.789l-208.1-115.6c-3-1.7-6.7-1.7-9.7,0l-208,115.6c-3.2,1.8-5.1,5.1-5.1,8.7v292.7c-0.1,5.4,4.2,9.8,9.6,9.8
                            c0.1,0,0.2,0,0.2,0h416.3c5.4,0.1,9.8-4.2,9.8-9.6c0-0.1,0-0.2,0-0.2v-292.6C436.002,125.889,434.102,122.589,430.902,120.789z
                            M358.002,412.089h-280v-41h280V412.089z M358.002,351.089h-280v-38h280V351.089z M358.002,293.089h-280v-38h280V293.089z
                            M358.002,235.089h-280v-38h280V235.089z M416.002,412.089h-38v-224.7c0-5.5-4.1-10.3-9.7-10.3h-300.6c-5.5,0-9.7,4.8-9.7,10.3
                            v224.7h-38v-276.7l198-110.1l198,110.1V412.089z"/>
                        </svg>
                        <svg class="CmSelGarageFull CmColorFih" viewBox="0 0 252.094 252.094" <?if($_COOKIE['CarModGarage']){?>style="display:block;"<?}?>>
                            <path d="M196.979,146.785c-1.091,0-2.214,0.157-3.338,0.467l-4.228,1.165l-6.229-15.173c-3.492-8.506-13.814-15.426-23.01-15.426
                            H91.808c-9.195,0-19.518,6.921-23.009,15.427l-6.218,15.145l-4.127-1.137c-1.124-0.31-2.247-0.467-3.338-0.467
                            c-5.485,0-9.467,3.935-9.467,9.356c0,5.352,3.906,9.858,9.2,11.211c-2.903,8.017-5.159,20.034-5.159,27.929v32.287
                            c0,6.893,5.607,12.5,12.5,12.5h4.583c6.893,0,12.5-5.607,12.5-12.5v-6.04h93.435v6.04c0,6.893,5.607,12.5,12.5,12.5h4.585
                            c6.893,0,12.5-5.607,12.5-12.5v-32.287c0-7.887-2.252-19.888-5.15-27.905c5.346-1.32,9.303-5.85,9.303-11.235
                            C206.445,150.72,202.464,146.785,196.979,146.785z M70.352,159.384l10.161-24.754c2.089-5.088,8.298-9.251,13.798-9.251h63.363
                            c5.5,0,11.709,4.163,13.798,9.251l10.161,24.754c2.089,5.088-0.702,9.251-6.202,9.251H76.554
                            C71.054,168.635,68.263,164.472,70.352,159.384z M97.292,199.635c0,2.75-2.25,5-5,5H71.554c-2.75,0-5-2.25-5-5v-8.271
                            c0-2.75,2.25-5,5-5h20.738c2.75,0,5,2.25,5,5V199.635z M185.203,199.635c0,2.75-2.25,5-5,5h-20.736c-2.75,0-5-2.25-5-5v-8.271
                            c0-2.75,2.25-5,5-5h20.736c2.75,0,5,2.25,5,5V199.635z"/>
                            <path d="M246.545,71.538L131.625,4.175c-1.525-0.894-3.506-1.386-5.578-1.386c-2.072,0-4.053,0.492-5.578,1.386L5.549,71.538
                            C2.386,73.392,0,77.556,0,81.223v160.582c0,4.135,3.364,7.5,7.5,7.5h12.912c4.136,0,7.5-3.365,7.5-7.5V105.917
                            c0-1.378,1.121-2.5,2.5-2.5h191.268c1.379,0,2.5,1.122,2.5,2.5v135.888c0,4.135,3.364,7.5,7.5,7.5h12.913
                            c4.136,0,7.5-3.365,7.5-7.5V81.223C252.094,77.556,249.708,73.392,246.545,71.538z"/>
                        </svg>
                    </div>
                </div>
                <div class="CmGarDropDown CmColorBr">
                    <div class="CmSelGarTitle">
                        <span class="CmSelTitleTxt"><?=SeLng_x('My_Garage')?></span>
                        <svg class="CmSelCloseGar" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm6 16.538l-4.592-4.548 4.546-4.587-1.416-1.403-4.545 4.589-4.588-4.543-1.405 1.405 4.593 4.552-4.547 4.592 1.405 1.405 4.555-4.596 4.591 4.55 1.403-1.416z"/></svg>
                    </div>
                    <!-- <div class="CmSelFavorVehicle">
                            <div class="CmSelFavVehTitle CmColorTx CmColorBgL"><?=SeLng_x('Favorite')?></div>
                            <div class="CmSelFavItemWrap CmColorBr"></div>
                    </div> -->
                    <div class="CmModInGarItem">
                        <div class="CmModGarageWrap">
                                <?if(!$_COOKIE['CarModGarage']){?>
                                    <div class='CmSelGarEmpMess CmColorTx'><svg class='CmGarEmptySvg' viewBox='0 0 435.905 435.905'><path d='M430.902,120.789l-208.1-115.6c-3-1.7-6.7-1.7-9.7,0l-208,115.6c-3.2,1.8-5.1,5.1-5.1,8.7v292.7c-0.1,5.4,4.2,9.8,9.6,9.8c0.1,0,0.2,0,0.2,0h416.3c5.4,0.1,9.8-4.2,9.8-9.6c0-0.1,0-0.2,0-0.2v-292.6C436.002,125.889,434.102,122.589,430.902,120.789zM358.002,412.089h-280v-41h280V412.089z M358.002,351.089h-280v-38h280V351.089z M358.002,293.089h-280v-38h280V293.089zM358.002,235.089h-280v-38h280V235.089z M416.002,412.089h-38v-224.7c0-5.5-4.1-10.3-9.7-10.3h-300.6c-5.5,0-9.7,4.8-9.7,10.3v224.7h-38v-276.7l198-110.1l198,110.1V412.089z'/></svg><span class='CmSelEmptyTxt'><?=SeLng_x('Empty')?></span></div>
                                <?}?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
            </div>
</div>
<?//=$VehicleURL?>
<?//=$SelectedLink?>
<div class="CmMSelectLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div>
<svg class="CmSelSvgImBlock">
    <symbol viewBox="0 0 16 16" id="vaf_icon">
        <path d="m12.853 16.0905c-.0688477 0-.138184-.014183-.203125-.0430384l-4.61816-2.05606-4.61816 2.05606c-.170898.0758066-.370117.0493956-.515625-.0670033-.145996-.116888-.214355-.30567-.177734-.489072l1.01855-5.10249-3.56055-3.5668c-.130371-.130582-.178223-.322299-.125-.499343.0532227-.176555.199707-.309583.380371-.345774l5.10352-1.02265 2.0376-4.59239c.161133-.361913.75293-.361913.914063 0l2.0376 4.59239 5.10352 1.02265c.180664.0361915.327148.169219.380371.345774.0532227.177044.0053711.368761-.125.499343l-3.56055 3.5668 1.01855 5.10249c.0366211.183402-.0317383.372184-.177734.489072-.0903321.072383-.201172.110042-.3125.110042zm-4.82129-3.14816c.0693359 0 .138672.014183.203125.0430384l3.93848 1.75381-.881836-4.41681c-.0327148-.164328.0185547-.334036.136719-.452392l3.08936-3.09436-4.44092-.890111c-.15918-.0317899-.292969-.138897-.358887-.287575l-1.68604-3.7996-1.68604 3.7996c-.065918.148678-.199707.255785-.358887.287575l-4.44092.890111 3.08936 3.09436c.118164.118356.169434.288064.136719.452392l-.881836 4.41681 3.93848-1.75381c.064453-.0288553.133789-.0430384.203125-.0430384z" transform="translate(-.032 -.09)"/>
    </symbol>
</svg>

<? //aprint_x($aRes, '$aRes');?>

<script type="text/javascript">

    //SET COOKIE FUNCTION
    function setCookie(key, value, expireDays, expireHours, expireMinutes, expireSeconds){
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
        var cleaned_host;
        if(location.host.indexOf('www.') === 0){
            cleaned_host = location.host.replace('www.','');
        }else{
            cleaned_host = window.location.hostname;
        }
        document.cookie = key +"="+ escape(value) +
            ";domain="+ cleaned_host +
            ";path=/"+
            ";expires="+expireDate.toUTCString();
    }
    // DELETE COOKIE
    function deleteCookie(name){
        setCookie(name, "", null , null , null, -1);
    }
    //GET COOKIE FUNC
    function get_cookie ( cookie_name ){
        var results = document.cookie.match ( '(^|;) ?' + cookie_name + '=([^;]*)(;|$)' );
        if ( results )
            return ( unescape ( results[2] ) );
        else
            return null;
    }
    $(document).ready(function(){
        /*GARAGE*/
        var modelItems = get_cookie('CarModGarage');
        if(modelItems != null){
            var resPars = JSON.parse(modelItems);
            var arMod = new Array();
            if(resPars != null){
                arMod = resPars;
            }else{
                arMod.push(resPars);
            }
            $('.CmModGarageWrap').html('');
            $.each(arMod,function(key,data) {
                let picMod_2 = data['pic'];
                let dataMod_2 = data['data'];
                let linkMod_2 = data['link'];
                let vIdMod_2 = data['id'];
                let manufModel_2 = data['manuf'];
                let garModel_2 = '<div class="CmSelGarItemWrap" data-vpid="'+vIdMod_2+'"><div class="CmSeModImg"><img src="'+picMod_2+'"></div><div class="CmSelImgNameBl CmColorBgLh"><a href="'+linkMod_2+'" class="CmSelModelLink CmColorTxh"><div class="CmSelManufTxt"><b>'+manufModel_2+'</b></div><div class="CmColorTxh">'+dataMod_2+'</div></a><svg id="CmDelSelMod" class="CmColorFih" data-vpid="'+vIdMod_2+'" viewBox="0 0 128 128"><path d="m109.5 16.917h-23.85v-10.417a1.75 1.75 0 0 0 -1.75-1.75h-39.8a1.751 1.751 0 0 0 -1.75 1.75v10.417h-23.85a1.75 1.75 0 0 0 -1.75 1.75v12.641a1.751 1.751 0 0 0 1.75 1.75h4.222l4.78 88.536a1.751 1.751 0 0 0 1.748 1.656h69.5a1.749 1.749 0 0 0 1.747-1.656l4.781-88.536h4.222a1.75 1.75 0 0 0 1.75-1.75v-12.641a1.749 1.749 0 0 0 -1.75-1.75zm-63.65-8.667h36.3v8.667h-36.3zm51.242 111.5h-66.184l-4.681-86.692h75.547zm10.658-90.192h-87.5v-9.141h23.816.034s.022 0 .034 0h39.733.033s.023 0 .034 0h23.816z"/><path d="m46.667 96.559a1.751 1.751 0 0 0 1.75-1.75v-42.142a1.75 1.75 0 0 0 -3.5 0v42.142a1.75 1.75 0 0 0 1.75 1.75z"/><path d="m64 96.559a1.751 1.751 0 0 0 1.75-1.75v-42.142a1.75 1.75 0 0 0 -3.5 0v42.142a1.751 1.751 0 0 0 1.75 1.75z"/><path d="m81.333 96.559a1.75 1.75 0 0 0 1.75-1.75v-42.142a1.75 1.75 0 0 0 -3.5 0v42.142a1.75 1.75 0 0 0 1.75 1.75z"/></svg></div></div>';
                $(garModel_2).appendTo('.CmModGarageWrap');
            });
        }
        // ADD MODEL TO GARAGE
        $('.CmSelAddGarage').on('click', function(){
            let itemsCookie = get_cookie('CarModGarage');
            let resP = JSON.parse(itemsCookie);
            let arModels = new Array();
            if(resP != null){
                arModels = resP;
            }
            arModels.push({manuf:'<?=$vManuf?>', link:'<?=$VehicleURL?>', pic:'<?=$vPicture?>', data:'<?=$vTypData?>', id:'<?=$vTypID?>', sort:10});
            surrModel = JSON.stringify(arModels);
            setCookie('CarModGarage', surrModel, 9999999, null, null, null);
            let par = JSON.parse(surrModel);
            let qwe = par.sort();
            console.table(qwe);
            $('.CmModGarageWrap').html('');
            $.each(qwe,function(key, data){
                let picMod = data['pic'];
                let dataMod = data['data'];
                let linkMod = data['link'];
                let vIdMod = data['id'];
                let manufModel = data['manuf'];
                let garModel = '<div class="CmSelGarItemWrap data-vpid="'+vIdMod+'"><div class="CmSeModImg"><img src="'+picMod+'"></div><div class="CmSelImgNameBl CmColorBgLh"><a href="'+linkMod+'" class="CmSelModelLink CmColorTxh"><div class="CmSelManufTxt"><b>'+manufModel+'</b></div><div class="CmColorTxh">'+dataMod+'</div></a><svg id="CmDelSelMod" class="CmColorFih" data-vpid="'+vIdMod+'" viewBox="0 0 128 128"><path d="m109.5 16.917h-23.85v-10.417a1.75 1.75 0 0 0 -1.75-1.75h-39.8a1.751 1.751 0 0 0 -1.75 1.75v10.417h-23.85a1.75 1.75 0 0 0 -1.75 1.75v12.641a1.751 1.751 0 0 0 1.75 1.75h4.222l4.78 88.536a1.751 1.751 0 0 0 1.748 1.656h69.5a1.749 1.749 0 0 0 1.747-1.656l4.781-88.536h4.222a1.75 1.75 0 0 0 1.75-1.75v-12.641a1.749 1.749 0 0 0 -1.75-1.75zm-63.65-8.667h36.3v8.667h-36.3zm51.242 111.5h-66.184l-4.681-86.692h75.547zm10.658-90.192h-87.5v-9.141h23.816.034s.022 0 .034 0h39.733.033s.023 0 .034 0h23.816z"/><path d="m46.667 96.559a1.751 1.751 0 0 0 1.75-1.75v-42.142a1.75 1.75 0 0 0 -3.5 0v42.142a1.75 1.75 0 0 0 1.75 1.75z"/><path d="m64 96.559a1.751 1.751 0 0 0 1.75-1.75v-42.142a1.75 1.75 0 0 0 -3.5 0v42.142a1.751 1.751 0 0 0 1.75 1.75z"/><path d="m81.333 96.559a1.75 1.75 0 0 0 1.75-1.75v-42.142a1.75 1.75 0 0 0 -3.5 0v42.142a1.75 1.75 0 0 0 1.75 1.75z"/></svg></div></div>';
                $(garModel).appendTo('.CmModGarageWrap');
            });
            $('.CmSelMyGarOpen').css('display','flex');
            $('.CmSelAddGarBlock').hide();
        });

        $('.CmSelMyGarOpen, .CmSelGarOpen').on('click', function(){
          $('.CmGarDropDown').fadeIn(300);
        });

        //DELETE GARAGE MODEL ITEM
        $("body").on('click','#CmDelSelMod', function(){
            let vId = $(this).data('vpid'),
            vehic = $('.CmMSelectTable').data('vehicle');
            vpId = $(this).parents('.CmGarageBlock').find('.CmSelAddGarBlock').data('vpid'),
            delItem = get_cookie('CarModGarage'),
            resIt = JSON.parse(delItem);
            const idToRemove = vId;
            const filteredMod = resIt.filter((item) => item.id != idToRemove);
            if(filteredMod.length == 0 && vehic == 'Y'){
                deleteCookie('CarModGarage');
                $(this).parents('.CmSelGarItemWrap').html("<div class='CmSelGarEmpMess CmColorTx'><svg class='CmGarEmptySvg' viewBox='0 0 435.905 435.905'><path d='M430.902,120.789l-208.1-115.6c-3-1.7-6.7-1.7-9.7,0l-208,115.6c-3.2,1.8-5.1,5.1-5.1,8.7v292.7c-0.1,5.4,4.2,9.8,9.6,9.8c0.1,0,0.2,0,0.2,0h416.3c5.4,0.1,9.8-4.2,9.8-9.6c0-0.1,0-0.2,0-0.2v-292.6C436.002,125.889,434.102,122.589,430.902,120.789zM358.002,412.089h-280v-41h280V412.089z M358.002,351.089h-280v-38h280V351.089z M358.002,293.089h-280v-38h280V293.089zM358.002,235.089h-280v-38h280V235.089z M416.002,412.089h-38v-224.7c0-5.5-4.1-10.3-9.7-10.3h-300.6c-5.5,0-9.7,4.8-9.7,10.3v224.7h-38v-276.7l198-110.1l198,110.1V412.089z'/></svg><span class='CmSelEmptyTxt'><?=SeLng_x('Empty')?></span></div>").removeClass('CmSelGarItemWrap');
                $('.CmSelMyGarOpen').hide();
                $('.CmSelAddGarBlock').show().css('display','flex');
                $('.CmSelGarageFull').hide();
                $('.CmGarageSvg').show();
            }else if(filteredMod.length == 0 && vehic == ''){
                deleteCookie('CarModGarage');
                $(this).parents('.CmSelGarItemWrap').html("<div class='CmSelGarEmpMess CmColorTx'><svg class='CmGarEmptySvg' viewBox='0 0 435.905 435.905'><path d='M430.902,120.789l-208.1-115.6c-3-1.7-6.7-1.7-9.7,0l-208,115.6c-3.2,1.8-5.1,5.1-5.1,8.7v292.7c-0.1,5.4,4.2,9.8,9.6,9.8c0.1,0,0.2,0,0.2,0h416.3c5.4,0.1,9.8-4.2,9.8-9.6c0-0.1,0-0.2,0-0.2v-292.6C436.002,125.889,434.102,122.589,430.902,120.789zM358.002,412.089h-280v-41h280V412.089z M358.002,351.089h-280v-38h280V351.089z M358.002,293.089h-280v-38h280V293.089zM358.002,235.089h-280v-38h280V235.089z M416.002,412.089h-38v-224.7c0-5.5-4.1-10.3-9.7-10.3h-300.6c-5.5,0-9.7,4.8-9.7,10.3v224.7h-38v-276.7l198-110.1l198,110.1V412.089z'/></svg><span class='CmSelEmptyTxt'><?=SeLng_x('Empty')?></span></div>").removeClass('CmSelGarItemWrap');
            }else{
                leftModel = JSON.stringify(filteredMod);
                setCookie('CarModGarage', leftModel, 9999999, null, null, null);
                $(this).parents('.CmSelGarItemWrap').fadeOut(200);
                if(vId == vpId){
                    $('.CmSelMyGarOpen').hide();
                    $('.CmSelAddGarBlock').show().css('display','flex');
                    if(filteredMod.length==0){
                        $('.CmSelGarageFull').hide();
                        $('.CmGarageSvg').show();
                    }
                }
            }
        });

        //CLOSE GARAGE
        $(document).mousedown(function (e){ // событие клика по веб-документу
            var div = $(".CmGarDropDown"); // тут указываем ID элемента
            if (!div.is(e.target) && div.has(e.target).length === 0) {
                $('.CmGarDropDown').fadeOut();
            }
        });
        $('.CmSelCloseGar').on('click', function(e){
            e.stopPropagation();
            $('.CmGarDropDown').fadeOut();
        });
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
                    if(Type=='Model'){
                        LoadingAt='Type';
                    }else{
                        LoadingAt='Model';
                        $('#CmMS_BoxType').removeClass('CmMSelectActive').addClass('CmMSelectDisabled');
                    }
                    $('.CmMSelectLoading').appendTo($("#CmMS_Box"+LoadingAt)).show();
                    // $('#CmMS_'+LoadingAt).html('');
                }
                var PostData = 'FURL_x=<?=FURL_x?>&MsLang=<?=MsLang?>&sYear='+sYear+'&sManuf='+sManuf+'&sModel='+sModel+'&sType='+sType+'&sSection=<?=$_GET['section_furl']?>';
                $.ajax({
                    url:'<?=SELECTOR_PROCESSOR?>', type:'POST', dataType:'html', data:PostData,
                    statusCode:{
                        200: function(Res){//alert(Res);
                            $(location).attr('href',Res); //Redirect
                        },
                        201: function(Res){ //Next select Model
                            $('#CmMS_DdModel').html('<div class="CmFilterWrapBl"><input class="CmSelModSearch CmColorBr" type="text" placeholder="<?=SeLng_x('Find',0)?>..."><svg class="CmSelClearBut material-icon CmColorBgh" viewBox="0 1.5 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></div><div class="CmSelModBlock">'+Res+'</div>');
                            $('#CmMS_Model').html('<?=SeLng_x('Model')?>');
                            CmMS_ButDd("Model");
                            $('.CmSelModSearch').focus();
                            ModelFilterByLet();
                        },
                        202: function(Res){ //Next select Type
                            $('#CmMS_DdType').html(Res);
                            $('#CmMS_Type').html('<?=SeLng_x('Engine')?>');
                            CmMS_ButDd("Type");
                        },
                    },
                    success: function(Res){
                        $('.CmMSelectLoading').hide();
                        // console.log('Debug: '+Res);
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
        $('body').on('click', '.CmMSelectBox', function(){
            $('.CmSelModSearch').focus();
        });
        function CmMS_ButDd(BoxIdName){
            $("#CmMS_Box"+BoxIdName).removeClass('CmMSelectDisabled').addClass('CmMSelectActive');
            $("#CmMS_Dd"+BoxIdName).show();
            if(BoxIdName=='Model'){
                $("#CmMS_Dd"+BoxIdName).css('display', 'flex');
            }
        }

        //Reset model selector
        var el = document.getElementsByClassName('CmResetSettings');
        for (i=0; i<el.length; i++){
            el[i].onclick = function(){
                deleteCookie('CarModVehicle');
            location.reload(true);
            }
        }
    });

function ModelFilterByLet(){
    $('.CmSelModSearch').keyup(function(){
        if($('.CmSelModSearch').val().length == 0){
            $('.CmSelClearBut').hide();
        }
        if($('.CmSelModSearch').val().length > 0){
                $('.CmSelClearBut').show();
            var val_inp = $('.CmSelModSearch').val();
            var sw_x = 0;
            $('.CmSelTypeNAme').each(function(){
                var val_title = $(this).text();
                if (RegExp('\^'+val_inp,'i').test(val_title)||RegExp('\\s\\'+val_inp,'i').test(val_title)) {
                    $(this).parents('.CmMS_Option').show();
                }else{
                    $(this).parents('.CmMS_Option').hide();
                }
            });
        }else{
            $('.CmMS_Option').show();
        }
    });
    $('.CmSelClearBut').click(function(){
        $(this).hide();
        $('.CmSelModSearch').val('');
        $('.CmMS_Option').show();
    });
}

</script>
