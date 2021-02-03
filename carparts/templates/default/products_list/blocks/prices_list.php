<?VerifyAccess_x('ProductList_prices.templ');?>
<?if($aProd['PricesCount']){?>
    <?$aFstPrice = array_shift($aProd['PRICES']);?>
        <?if($aSets['NOT_HIDE_PRICES']==0 || count($aProd['PRICES'])==0){?>
            <div class="cm_DelAvalStock">
                <?if(!HIDE_PRODUCTS_COUNT){?>
                    <div class="cm_Delivtd CmTitShow" title="<?=Lng_x('Dtime_delivery');?>" data-suplstock="<?=$aFstPrice['SUPPLIER_STOCK']?>">
                        <?if($aFstPrice['DELIVERY_NUM']==0){?>
                            <div class="CmInStockText CmColorFi">
                                <svg width="16" height="16" viewBox="0 0 44 44" enable-background="new 0 0 44 44">
                                    <path d="m22,0c-12.2,0-22,9.8-22,22s9.8,22 22,22 22-9.8 22-22-9.8-22-22-22zm12.7,15.1l0,0-16,16.6c-0.2,0.2-0.4,0.3-0.7,0.3-0.3,0-0.6-0.1-0.7-0.3l-7.8-8.4-.2-.2c-0.2-0.2-0.3-0.5-0.3-0.7s0.1-0.5 0.3-0.7l1.4-1.4c0.4-0.4 1-0.4 1.4,0l.1,.1 5.5,5.9c0.2,0.2 0.5,0.2 0.7,0l13.4-13.9h0.1c0.4-0.4 1-0.4 1.4,0l1.4,1.4c0.4,0.3 0.4,0.9 0,1.3z"/>
                                </svg>
                            </div>
                        <?}else{?>
                            <div class="cm_svgDeliv">
                                <svg class="cm_deliv" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M2 11.741c-1.221-1.009-2-2.535-2-4.241 0-3.036 2.464-5.5 5.5-5.5 1.706 0 3.232.779 4.241 2h4.259c.552 0 1 .448 1 1v2h4.667c1.117 0 1.6.576 1.936 1.107.594.94 1.536 2.432 2.109 3.378.188.312.288.67.288 1.035v4.48c0 1.156-.616 2-2 2h-1c0 1.656-1.344 3-3 3s-3-1.344-3-3h-4c0 1.656-1.344 3-3 3s-3-1.344-3-3h-2c-.552 0-1-.448-1-1v-6.259zm6 6.059c.662 0 1.2.538 1.2 1.2 0 .662-.538 1.2-1.2 1.2-.662 0-1.2-.538-1.2-1.2 0-.662.538-1.2 1.2-1.2zm10 0c.662 0 1.2.538 1.2 1.2 0 .662-.538 1.2-1.2 1.2-.662 0-1.2-.538-1.2-1.2 0-.662.538-1.2 1.2-1.2zm-7.207-11.8c.135.477.207.98.207 1.5 0 3.036-2.464 5.5-5.5 5.5-.52 0-1.023-.072-1.5-.207v4.207h1.765c.549-.614 1.347-1 2.235-1 .888 0 1.686.386 2.235 1h5.53c.549-.614 1.347-1 2.235-1 .888 0 1.686.386 2.235 1h1.765v-4.575l-1.711-2.929c-.179-.307-.508-.496-.863-.496h-4.426v6h-2v-9h-2.207zm5.207 4v3h5l-1.427-2.496c-.178-.312-.509-.504-.868-.504h-2.705zm-10.5-6c1.932 0 3.5 1.568 3.5 3.5s-1.568 3.5-3.5 3.5-3.5-1.568-3.5-3.5 1.568-3.5 3.5-3.5zm.5 3h2v1h-3v-3h1v2z"/></svg>
                            </div>
                            <div class="CmListPrDelivery <?if($aPrice['DELIVERY_NUM']==0){echo 'CmInStockDelivery';}else{echo 'CmTimeDelivery';}?>" title="<?=Lng_x('Delivery_time',0)?>">
                                <?=$aFstPrice['DELIVERY_VIEW']?>
                            </div>
                        <?}?>
                    </div>
                <?}?>
                <div class="CmSupplNameStockWrap <?if($aFstPrice['SUPPLIER_NAME']!='' && $aFstPrice['SUPPLIER_STOCK']!=''){?>CmFlexBoxDirCol<?}?>">
                    <?if($aRes['SHOW_SUPPLIER']&&$aFstPrice['SUPPLIER_NAME']!=''){?>
                        <div class="CmStockTdList CmTitShow" title="<?=Lng_x('Supplier');?>">
                            <div class="svgStock">
                                <svg class="stockImg" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M5 23h-2v-10l8.991-8.005c1.124.998 2.25 1.997 3.378 2.996l2.255 1.997c1.127.999 2.252 2.013 3.376 3.012v10h-2v-9.118l-7.009-6.215-6.991 6.22v9.113zm2-2h10v2h-10v-2zm0-3h10v2h-10v-2zm10-3v2h-10v-2h10zm-5-14l12 10.632-1.328 1.493-10.672-9.481-10.672 9.481-1.328-1.493 12-10.632z"/></svg>
                            </div>
                            <div class="CmListPrStock"><?=$aFstPrice['SUPPLIER_NAME']?></div>
                            <div class="clear"></div>
                        </div>
                    <?}?>
                    <?if($aRes['SHOW_STOCK'] && $aFstPrice['SUPPLIER_STOCK']!=''){?>
                        <div class="CmStockTdList CmTitShow <?if($aFstPrice['SUPPLIER_NAME']!='' && $aFstPrice['SUPPLIER_STOCK']!=''){?>CmBoxMargTop<?}?>" title="<?=Lng_x('Stock');?>">
                            <div class="cm_svgStock">
                                <svg class="cm_stock" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M5 23h-2v-10l8.991-8.005c1.124.998 2.25 1.997 3.378 2.996l2.255 1.997c1.127.999 2.252 2.013 3.376 3.012v10h-2v-9.118l-7.009-6.215-6.991 6.22v9.113zm2-2h10v2h-10v-2zm0-3h10v2h-10v-2zm10-3v2h-10v-2h10zm-5-14l12 10.632-1.328 1.493-10.672-9.481-10.672 9.481-1.328-1.493 12-10.632z"/></svg>
                            </div>
                            <div class="CmListPrStock"><?=$aFstPrice['SUPPLIER_STOCK']?></div>
                        </div>
                    <?}?>
                </div>
            </div>
            <?if($aFstPrice['OLD_PRICE']){?>
                <div class="CmDiscountPriceList">
                    <div class="CmOldPriceList"><i><?=$aFstPrice['OLD_PRICE']?></i>&ensp;</div>
                    <div class="CmPercentDiscList"><?=$aFstPrice['DISCOUNT_VIEW']?></div>
                </div>
            <?}?>
            <div class="CmPriceTable"  cellpadding="10">
                <div class="CmAvalPriceFlexBl">
                    <div class="cm_Avaltd CmTitShow <?if(!HIDE_PRODUCTS_COUNT){?>CmAvaltdMargin<?}?>" title="<?=Lng_x('Availability');?>">
                        <div class="CmAvalImgTextList">
                            <?if(!HIDE_PRODUCTS_COUNT){?>
                                <div class="cm_svgAval"><svg class="cm_aval fillBg" viewBox="0 0 24 24"><path d="M16.677 17.868l-.343.195v-1.717l.343-.195v1.717zm2.823-3.325l-.342.195v1.717l.342-.195v-1.717zm3.5-7.602v11.507l-9.75 5.552-12.25-6.978v-11.507l9.767-5.515 12.233 6.941zm-13.846-3.733l9.022 5.178 1.7-.917-9.113-5.17-1.609.909zm2.846 9.68l-9-5.218v8.19l9 5.126v-8.098zm3.021-2.809l-8.819-5.217-2.044 1.167 8.86 5.138 2.003-1.088zm5.979-.943l-2 1.078v2.786l-3 1.688v-2.856l-2 1.078v8.362l7-3.985v-8.151zm-4.907 7.348l-.349.199v1.713l.349-.195v-1.717zm1.405-.8l-.344.196v1.717l.344-.196v-1.717zm.574-.327l-.343.195v1.717l.343-.195v-1.717zm.584-.333l-.35.199v1.717l.35-.199v-1.717z"/></svg></div>
                            <?}?>	
                            <?PrintProductAvailable_x($aFstPrice)?>
                        </div>
                        <div class="CmOptTable">
                            <?if($aFstPrice['OPTIONS_VIEW']){
                                foreach($aFstPrice['OPTIONS_VIEW'] as $aOpt){?>
                                    <div class="CmOptTdList"><?=$aOpt['Value']?></div>
                                <?}    
                            }?>
                        </div>
                    </div>
                    <div class="CmPriceText"><?=$aFstPrice['PRICE_FORMATED'];?></div>
                </div>
                <div class="CmListPrToCart">
                    <?if($aFstPrice['AVAILABLE_NUM']>0 || $aRes['ALLOW_NOTAVAIL']){
                        if($aFstPrice['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']){
                            $aFstPrice['AVAILABLE_NUM'] = 99;
                        }?>    
                    <?// "ADD TO CART" Class is: CmAddToCart / CmAddToCartQty (../includes.php)?>
                        <div class="CmQuantPrice">
                            <div class="blockQty">
                                <div class="quantMinus CmColorTxh cm_countButM">-</div>
                                <input name="re_count" type="text" class="CmAddToCartQty quantProd cm_countRes" value="1" data-maxaval="<?=$aFstPrice['AVAILABLE_NUM']?>">
                                <div class="quantPlus CmColorTxh cm_countButP">+</div>
                            </div>
                        </div>
                        <div class="CmAddCartBut">
                            <div class="CmAddToCart cm_ToCart CmColorBg" data-furl="<?=$aProd['Link']?>" data-priceid="<?=$aFstPrice['PriceID']?>">
                                <span><svg class="cm_cartImg" viewBox="0 2 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
                                    <span class="cm_cartText"><?=Lng_x('To cart')?></span>
                                </span>
                            </div>
                        </div>
                        <?}else{?>
                            <div>
                                <div class="cm_NotAvailable_l">
                                    <svg class="cm_NotAvImg" viewBox="0 -2 24 24"><path d="M13.5 18c-.828 0-1.5.672-1.5 1.5 0 .829.672 1.5 1.5 1.5s1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-3.5 1.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm13.257-14.5h-1.929l-3.473 12h-13.239l-4.616-11h2.169l3.776 9h10.428l3.432-12h4.195l-.743 2zm-12.257 1.475l2.475-2.475 1.414 1.414-2.475 2.475 2.475 2.475-1.414 1.414-2.475-2.475-2.475 2.475-1.414-1.414 2.475-2.475-2.475-2.475 1.414-1.414 2.475 2.475z"/></svg>
                                    <span><?=Lng_x('Not_available')?></span>
                                </div>
                            </div>
                        <?}?>
                </div> 
            </div> 
        <?}?>
    <?}else{?>
        <div class="cm_AvalAsk" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>">
            <?if($aRes['ASK_PRICE']){?>
                <div class="ListAskPrice_l CmAskPrice" data-tab="AskPrice" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>" data-moduledir="<?=CM_DIR?>" data-lang="<?=LANG_x?>"  data-link="<?=$aProd['Link']?>">
                    <svg class="cm_askImg" viewBox="0 -5 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm1.25 17c0 .69-.559 1.25-1.25 1.25-.689 0-1.25-.56-1.25-1.25s.561-1.25 1.25-1.25c.691 0 1.25.56 1.25 1.25zm1.393-9.998c-.608-.616-1.515-.955-2.551-.955-2.18 0-3.59 1.55-3.59 3.95h2.011c0-1.486.829-2.013 1.538-2.013.634 0 1.307.421 1.364 1.226.062.847-.39 1.277-.962 1.821-1.412 1.343-1.438 1.993-1.432 3.468h2.005c-.013-.664.03-1.203.935-2.178.677-.73 1.519-1.638 1.536-3.022.011-.924-.284-1.719-.854-2.297z"/></svg>
                    <span><?=Lng_x('Ask_price')?></span>
                </div>
            <?}
            if($aRes['ALLOW_ORDER']){?>
                <div class="CmAddToCart ListNotAvailable_l" data-furl="<?=$aProd['Link']?>" data-priceid="order" style="<?if($aRes['ASK_PRICE']==false){echo 'margin-top:35px;';}?>">
                    <svg class="cm_cartImg" viewBox="0 3 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
                    <span><?=Lng_x('Order')?></span>
                </div>
            <?}?>
            <div class="clear"></div>
        </div>
        <?if(!$aRes['ASK_PRICE'] && !$aRes['ALLOW_ORDER']){?>
            <div class="cmNoInStock">
                <svg class="cm_NotInStockImg" viewBox="0 0 32 32">
                    <path d="M23 1l-7 6 9 6 7-6z"></path>
                    <path d="M16 7l-7-6-9 6 7 6z"></path>
                    <path d="M25 13l7 6-9 5-7-6z"></path>
                    <path d="M16 18l-9-5-7 6 9 5z"></path>
                    <path d="M22.755 26.424l-6.755-5.79-6.755 5.79-4.245-2.358v2.934l11 5 11-5v-2.934z"></path>
                </svg>                            
                <span><?=Lng_x('No_in_stock',1)?></span>
            </div>
        <?}?>
    <?}?>    
    <!--MORE PRICE IF NOT HIDE-->
    <?if($aSets['NOT_HIDE_PRICES']==1){
    if(count($aProd['PRICES']) > 0){?>
        <div class="CmTablePriceWrapList">
            <table class="CmTablePrices">
                <tr class="CmTitleImg">
                    <?if(!HIDE_PRODUCTS_COUNT){?>
                        <td>
                            <div class="cm_svgDeliv">
                                <svg class="cm_deliv" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M2 11.741c-1.221-1.009-2-2.535-2-4.241 0-3.036 2.464-5.5 5.5-5.5 1.706 0 3.232.779 4.241 2h4.259c.552 0 1 .448 1 1v2h4.667c1.117 0 1.6.576 1.936 1.107.594.94 1.536 2.432 2.109 3.378.188.312.288.67.288 1.035v4.48c0 1.156-.616 2-2 2h-1c0 1.656-1.344 3-3 3s-3-1.344-3-3h-4c0 1.656-1.344 3-3 3s-3-1.344-3-3h-2c-.552 0-1-.448-1-1v-6.259zm6 6.059c.662 0 1.2.538 1.2 1.2 0 .662-.538 1.2-1.2 1.2-.662 0-1.2-.538-1.2-1.2 0-.662.538-1.2 1.2-1.2zm10 0c.662 0 1.2.538 1.2 1.2 0 .662-.538 1.2-1.2 1.2-.662 0-1.2-.538-1.2-1.2 0-.662.538-1.2 1.2-1.2zm-7.207-11.8c.135.477.207.98.207 1.5 0 3.036-2.464 5.5-5.5 5.5-.52 0-1.023-.072-1.5-.207v4.207h1.765c.549-.614 1.347-1 2.235-1 .888 0 1.686.386 2.235 1h5.53c.549-.614 1.347-1 2.235-1 .888 0 1.686.386 2.235 1h1.765v-4.575l-1.711-2.929c-.179-.307-.508-.496-.863-.496h-4.426v6h-2v-9h-2.207zm5.207 4v3h5l-1.427-2.496c-.178-.312-.509-.504-.868-.504h-2.705zm-10.5-6c1.932 0 3.5 1.568 3.5 3.5s-1.568 3.5-3.5 3.5-3.5-1.568-3.5-3.5 1.568-3.5 3.5-3.5zm.5 3h2v1h-3v-3h1v2z"/></svg>
                            </div>
                        </td>
                    <?}?>
                    <td class="CmSupplStockList">
                        <div class="cm_svgStock">
                            <svg class="cm_stock" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M5 23h-2v-10l8.991-8.005c1.124.998 2.25 1.997 3.378 2.996l2.255 1.997c1.127.999 2.252 2.013 3.376 3.012v10h-2v-9.118l-7.009-6.215-6.991 6.22v9.113zm2-2h10v2h-10v-2zm0-3h10v2h-10v-2zm10-3v2h-10v-2h10zm-5-14l12 10.632-1.328 1.493-10.672-9.481-10.672 9.481-1.328-1.493 12-10.632z"/></svg>
                        </div>
                    </td>
                    <td class="CmSupplNameList">
                        <div class="svgStock">
                            <svg class="stockImg" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M5 23h-2v-10l8.991-8.005c1.124.998 2.25 1.997 3.378 2.996l2.255 1.997c1.127.999 2.252 2.013 3.376 3.012v10h-2v-9.118l-7.009-6.215-6.991 6.22v9.113zm2-2h10v2h-10v-2zm0-3h10v2h-10v-2zm10-3v2h-10v-2h10zm-5-14l12 10.632-1.328 1.493-10.672-9.481-10.672 9.481-1.328-1.493 12-10.632z"/></svg>
                        </div>
                    </td>
                    <td>
                        <div class="cm_svgAval" style="<?if($aRes['ACTIVE_TAB']=='TABLE'){echo 'display:none';}?>">
                            <svg class="cm_aval fillBg" viewBox="0 0 24 24"><path d="M16.677 17.868l-.343.195v-1.717l.343-.195v1.717zm2.823-3.325l-.342.195v1.717l.342-.195v-1.717zm3.5-7.602v11.507l-9.75 5.552-12.25-6.978v-11.507l9.767-5.515 12.233 6.941zm-13.846-3.733l9.022 5.178 1.7-.917-9.113-5.17-1.609.909zm2.846 9.68l-9-5.218v8.19l9 5.126v-8.098zm3.021-2.809l-8.819-5.217-2.044 1.167 8.86 5.138 2.003-1.088zm5.979-.943l-2 1.078v2.786l-3 1.688v-2.856l-2 1.078v8.362l7-3.985v-8.151zm-4.907 7.348l-.349.199v1.713l.349-.195v-1.717zm1.405-.8l-.344.196v1.717l.344-.196v-1.717zm.574-.327l-.343.195v1.717l.343-.195v-1.717zm.584-.333l-.35.199v1.717l.35-.199v-1.717z"/></svg>
                        </div>
                    </td>
                    <td colspan="2">
                        <div class="CmPriceTextTitle"><?=Lng_x('Price')?></div>
                    </td>
                </tr>
                <!--FIRST PRICE IF NOT HIDE-->
                <?if($aSets['NOT_HIDE_PRICES']==1){?>
                    <tr>
                        <?if(!HIDE_PRODUCTS_COUNT){?>
                            <td>
                                <?if($aFstPrice['DELIVERY_NUM']==0){?>
                                    <div class="CmInStockText CmColorFi CmTitShow" title="<?=Lng_x('In_stock')?>">
                                        <svg width="16" height="16" viewBox="0 0 44 44" enable-background="new 0 0 44 44">
                                          <path d="m22,0c-12.2,0-22,9.8-22,22s9.8,22 22,22 22-9.8 22-22-9.8-22-22-22zm12.7,15.1l0,0-16,16.6c-0.2,0.2-0.4,0.3-0.7,0.3-0.3,0-0.6-0.1-0.7-0.3l-7.8-8.4-.2-.2c-0.2-0.2-0.3-0.5-0.3-0.7s0.1-0.5 0.3-0.7l1.4-1.4c0.4-0.4 1-0.4 1.4,0l.1,.1 5.5,5.9c0.2,0.2 0.5,0.2 0.7,0l13.4-13.9h0.1c0.4-0.4 1-0.4 1.4,0l1.4,1.4c0.4,0.3 0.4,0.9 0,1.3z"/>
                                        </svg>
                                    </div>
                                <?}else{?>
                                    <div class="CmDeliveryNum CmTitShow" title="<?=Lng_x('Dtime_delivery');?>" data-suplstock="<?=$aFstPrice['SUPPLIER_STOCK']?>">
                                        <div class="CmListPrDelivery <?if($aFstPrice['DELIVERY_NUM']==0){echo 'CmInStockDelivery';}else{echo 'CmTimeDelivery';}?>" title="<?=Lng_x('Delivery_time',0)?>">
                                            <?=$aFstPrice['DELIVERY_VIEW']?>
                                        </div>
                                    </div>
                                <?}?>
                            </td>
                        <?}?>
                        <?if($aRes['SHOW_STOCK']&&$aFstPrice['SUPPLIER_STOCK']!=''){$suppl['stock']='stock';?>
                            <td>
                                <div class="CmStockNum CmTitShow" title="<?=Lng_x('Stock');?>">
                                    <div class="CmListPrStock"><?=$aFstPrice['SUPPLIER_STOCK']?></div>
                                </div>
                            </td>
                        <?}?>
                        <?if($aRes['SHOW_SUPPLIER']&&$aFstPrice['SUPPLIER_NAME']!=''){$suppl['name']='name';?>
                            <td>
                                <div class="CmStockName CmTitShow" title="<?=Lng_x('Supplier');?>">
                                    <div class="CmListPrStock CmColorTxh"><?=$aFstPrice['SUPPLIER_NAME']?></div>
                                </div>
                            </td>
                        <?}?>
                        <td>
                            <div class="cm_AvalNotHide CmTitShow" title="<?=Lng_x('Availability');?>">
                                <?PrintProductAvailable_x($aFstPrice)?>
                            </div>
                        </td>
                        <td>
                            <div class="CmWrapPriceDiscount">
                                <?if($aFstPrice['OLD_PRICE']){?>
                                    <div class="CmDiscPriceHide">
                                        <div class="CmOldPriceHide"><i><?=$aFstPrice['OLD_PRICE']?></i>&ensp;</div>
                                        <div class="CmPercentDiscHide"><?=$aFstPrice['DISCOUNT_VIEW']?></div>
                                    </div>
                                <?}?>
                                <div class="CmPriceNum" style="color:#<?if($aFstPrice['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']==false){echo '808080';}?>;">
                                    <span><nobr><?=$aFstPrice['PRICE_FORMATED'];?></nobr></span>
                                </div>
                            </div>
                        </td>
                        <td class="CmButAddToCart">
                        <?if($aFstPrice['AVAILABLE_NUM']>0 || $aRes['ALLOW_NOTAVAIL']){
                            if($aFstPrice['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']){
                                $aFstPrice['AVAILABLE_NUM'] = 99;
                            }?> 
                            <?// "ADD TO CART" Class is: CmAddToCart / CmAddToCartQty (../includes.php)?>
                            <div class="CmPriceQuanBlockWrap">
                                <div class="CmQuantPriceBlock">
                                    <div class="CmQuantMinusButList CmColorTxh cm_countButM">-</div>
                                    <input name="re_count" type="text" class="CmAddToCartQty CmQuantInputProd cm_countRes" value="1" data-maxaval="<?=$aPrice['AVAILABLE_NUM']?>">
                                    <div class="CmQuantPlusButList CmColorTxh cm_countButP">+</div>
                                </div>
                                <div class="CmAddToCart CmButtonToCartList CmColorBg" data-furl="<?=$aProd['Link']?>" data-priceid="<?=$aFstPrice['PriceID']?>">
                                    <svg class="cm_HideCartImg" viewBox="0 0 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
                                </div>
                            </div>    
                        <?}?>
                        </td>
                    </tr>
                <?}?>
                <?$suppl=[];?>
                <?foreach($aProd['PRICES'] as $aPrice){?>    
                    <tr class="CmTablePriceValueRow CmColorBgLh">
                        <?if(!HIDE_PRODUCTS_COUNT){?>
                            <td>
                                <?if($aPrice['DELIVERY_NUM']==0){?>
                                    <div class="CmInStockText CmColorFi CmTitShow" title="<?=Lng_x('In_stock')?>">
                                        <svg width="16" height="16" viewBox="0 0 44 44" enable-background="new 0 0 44 44">
                                          <path d="m22,0c-12.2,0-22,9.8-22,22s9.8,22 22,22 22-9.8 22-22-9.8-22-22-22zm12.7,15.1l0,0-16,16.6c-0.2,0.2-0.4,0.3-0.7,0.3-0.3,0-0.6-0.1-0.7-0.3l-7.8-8.4-.2-.2c-0.2-0.2-0.3-0.5-0.3-0.7s0.1-0.5 0.3-0.7l1.4-1.4c0.4-0.4 1-0.4 1.4,0l.1,.1 5.5,5.9c0.2,0.2 0.5,0.2 0.7,0l13.4-13.9h0.1c0.4-0.4 1-0.4 1.4,0l1.4,1.4c0.4,0.3 0.4,0.9 0,1.3z"/>
                                        </svg>
                                    </div>
                                <?}else{?>
                                    <div class="CmDeliveryNum CmTitShow" title="<?=Lng_x('Dtime_delivery');?>" data-suplstock="<?=$aPrice['SUPPLIER_STOCK']?>">
                                        <div class="CmListPrDelivery <?if($aPrice['DELIVERY_NUM']==0){echo 'CmInStockDelivery';}else{echo 'CmTimeDelivery';}?>" title="<?=Lng_x('Delivery_time',0)?>">
                                            <?=$aPrice['DELIVERY_VIEW']?>
                                        </div>
                                    </div>
                                <?}?>
                            </td>
                        <?}?>
                        <?if($aRes['SHOW_STOCK']&&$aPrice['SUPPLIER_STOCK']!=''){$suppl['stock']='stock';?>
                            <td>
                                <div class="CmStockNum CmTitShow" title="<?=Lng_x('Stock');?>">
                                    <div class="CmListPrStock"><?=$aPrice['SUPPLIER_STOCK']?></div>
                                </div>
                            </td>
                        <?}?>
                        <?if($aRes['SHOW_SUPPLIER']&&$aPrice['SUPPLIER_NAME']!=''){$suppl['name']='name';?>
                            <td>
                                <div class="CmStockName CmTitShow" title="<?=Lng_x('Supplier');?>">
                                    <div class="CmListPrStock CmColorTxh"><?=$aPrice['SUPPLIER_NAME']?></div>
                                </div>
                            </td>
                        <?}?>
                        <td>
                            <div class="cm_AvalNotHide CmTitShow" title="<?=Lng_x('Availability');?>">
                                <?PrintProductAvailable_x($aPrice)?>
                            </div>
                        </td>
                        <td>
                            <div class="CmWrapPriceDiscount">
                                <?if($aPrice['OLD_PRICE']){?>
                                    <div class="CmDiscPriceHide">
                                        <div class="CmOldPriceHide"><i><?=$aPrice['OLD_PRICE']?></i>&ensp;</div>
                                        <div class="CmPercentDiscHide"><?=$aPrice['DISCOUNT_VIEW']?></div>
                                    </div>
                                <?}?>
                                <div class="CmPriceNum" style="color:#<?if($aPrice['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']==false){echo '808080';}?>;">
                                    <span><nobr><?=$aPrice['PRICE_FORMATED'];?></nobr></span>
                                </div>
                            </div>
                        </td>
                        <td class="CmButAddToCart">
                            <?if($aPrice['AVAILABLE_NUM']>0 || $aRes['ALLOW_NOTAVAIL']){
                                if($aPrice['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']){
                                    $aPrice['AVAILABLE_NUM'] = 99;
                                }?> 
                                <?// "ADD TO CART" Class is: CmAddToCart / CmAddToCartQty (../includes.php)?>
                                <div class="CmPriceQuanBlockWrap">
                                    <div class="CmQuantPriceBlock">
                                        <div class="CmQuantMinusButList CmColorTxh cm_countButM">-</div>
                                        <input name="re_count" type="text" class="CmAddToCartQty CmQuantInputProd cm_countRes" value="1" data-maxaval="<?=$aPrice['AVAILABLE_NUM']?>">
                                        <div class="CmQuantPlusButList CmColorTxh cm_countButP">+</div>
                                    </div>
                                    <div class="CmAddToCart CmButtonToCartList CmColorBg" data-furl="<?=$aProd['Link']?>" data-priceid="<?=$aPrice['PriceID']?>">
                                        <svg class="cm_HideCartImg" viewBox="0 0 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
                                    </div>
                                </div>    
                            <?}?>
                        </td>
                    </tr>
                <?}?>    
            </table>
        </div>
    <?}
    }?>	
    <?if(count($aProd['PRICES']) > 0 && $aSets['NOT_HIDE_PRICES']==0){?>
        <div class="cm_ShowMorePr CmColorTx">&#9660; <?=Lng_x('Show_more_prices')?>
            (<?=count($aProd['PRICES'])?>)
            
            <!--MORE PRICE IF HIDE-->
            <?if(count($aProd['PRICES']) > 0){?>
                <div class="cm_HidePricetb CmColorBr">
                    <div class="cm_svgRow">
                        <div class="artic"><span  style="color:#<?if($aProd['BColor']){echo $aProd['BColor'];}?>"><?=$aProd['Brand']?>:</span> <span class="CmArtNumText"><?=$aProd['ArtNum']?></span></div>
                        <div class="CmCloseTable">
                            <svg class="CmCloseImg CmColorFi" viewBox="0 0 24 24"><path d="M23.954 21.03l-9.184-9.095 9.092-9.174-2.832-2.807-9.09 9.179-9.176-9.088-2.81 2.81 9.186 9.105-9.095 9.184 2.81 2.81 9.112-9.192 9.18 9.1z"/></svg>
                        </div>
                    </div>
                    <?foreach($aProd['PRICES'] as $aPrice){?>    
                        <div class="cm_valRow CmColorBgLh">
                            <div class="CmHidePriceBlFlexEl cm_AvaltdHide CmTitShow" title="<?=Lng_x('Availability');?>">
                                <div class="CmAvalImgTextList">
                                    <?if(!HIDE_PRODUCTS_COUNT){?>
                                        <div class="cm_svgAval"><svg class="cm_aval fillBg" viewBox="0 0 24 24"><path d="M16.677 17.868l-.343.195v-1.717l.343-.195v1.717zm2.823-3.325l-.342.195v1.717l.342-.195v-1.717zm3.5-7.602v11.507l-9.75 5.552-12.25-6.978v-11.507l9.767-5.515 12.233 6.941zm-13.846-3.733l9.022 5.178 1.7-.917-9.113-5.17-1.609.909zm2.846 9.68l-9-5.218v8.19l9 5.126v-8.098zm3.021-2.809l-8.819-5.217-2.044 1.167 8.86 5.138 2.003-1.088zm5.979-.943l-2 1.078v2.786l-3 1.688v-2.856l-2 1.078v8.362l7-3.985v-8.151zm-4.907 7.348l-.349.199v1.713l.349-.195v-1.717zm1.405-.8l-.344.196v1.717l.344-.196v-1.717zm.574-.327l-.343.195v1.717l.343-.195v-1.717zm.584-.333l-.35.199v1.717l.35-.199v-1.717z"/></svg></div>
                                    <?}?>	
                                    <?PrintProductAvailable_x($aPrice)?>
                                </div>	
                            </div>
                            <?if(!HIDE_PRODUCTS_COUNT){?>
                                <div class="CmHidePriceBlFlexEl">
                                    <?if($aPrice['DELIVERY_NUM']==0){?>
                                        <div class="CmInStockText CmColorFi CmTitShow" title="<?=Lng_x('In_stock')?>">
                                            <svg width="16" height="16" viewBox="0 0 44 44" enable-background="new 0 0 44 44">
                                              <path d="m22,0c-12.2,0-22,9.8-22,22s9.8,22 22,22 22-9.8 22-22-9.8-22-22-22zm12.7,15.1l0,0-16,16.6c-0.2,0.2-0.4,0.3-0.7,0.3-0.3,0-0.6-0.1-0.7-0.3l-7.8-8.4-.2-.2c-0.2-0.2-0.3-0.5-0.3-0.7s0.1-0.5 0.3-0.7l1.4-1.4c0.4-0.4 1-0.4 1.4,0l.1,.1 5.5,5.9c0.2,0.2 0.5,0.2 0.7,0l13.4-13.9h0.1c0.4-0.4 1-0.4 1.4,0l1.4,1.4c0.4,0.3 0.4,0.9 0,1.3z"/>
                                            </svg>
                                        </div>
                                    <?}else{?>
                                        <div class="cm_DelivtdHide CmTitShow" title="<?=Lng_x('Dtime_delivery');?>" data-suplstock="<?=$aPrice['SUPPLIER_STOCK']?>">
                                            <div class="cm_svgDeliv">
                                                <svg class="cm_deliv" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M2 11.741c-1.221-1.009-2-2.535-2-4.241 0-3.036 2.464-5.5 5.5-5.5 1.706 0 3.232.779 4.241 2h4.259c.552 0 1 .448 1 1v2h4.667c1.117 0 1.6.576 1.936 1.107.594.94 1.536 2.432 2.109 3.378.188.312.288.67.288 1.035v4.48c0 1.156-.616 2-2 2h-1c0 1.656-1.344 3-3 3s-3-1.344-3-3h-4c0 1.656-1.344 3-3 3s-3-1.344-3-3h-2c-.552 0-1-.448-1-1v-6.259zm6 6.059c.662 0 1.2.538 1.2 1.2 0 .662-.538 1.2-1.2 1.2-.662 0-1.2-.538-1.2-1.2 0-.662.538-1.2 1.2-1.2zm10 0c.662 0 1.2.538 1.2 1.2 0 .662-.538 1.2-1.2 1.2-.662 0-1.2-.538-1.2-1.2 0-.662.538-1.2 1.2-1.2zm-7.207-11.8c.135.477.207.98.207 1.5 0 3.036-2.464 5.5-5.5 5.5-.52 0-1.023-.072-1.5-.207v4.207h1.765c.549-.614 1.347-1 2.235-1 .888 0 1.686.386 2.235 1h5.53c.549-.614 1.347-1 2.235-1 .888 0 1.686.386 2.235 1h1.765v-4.575l-1.711-2.929c-.179-.307-.508-.496-.863-.496h-4.426v6h-2v-9h-2.207zm5.207 4v3h5l-1.427-2.496c-.178-.312-.509-.504-.868-.504h-2.705zm-10.5-6c1.932 0 3.5 1.568 3.5 3.5s-1.568 3.5-3.5 3.5-3.5-1.568-3.5-3.5 1.568-3.5 3.5-3.5zm.5 3h2v1h-3v-3h1v2z"/></svg>
                                            </div>
                                            <div class="CmListPrDelivery <?if($aPrice['DELIVERY_NUM']==0){echo 'CmInStockDelivery';}else{echo 'CmTimeDelivery';}?>" title="<?=Lng_x('Delivery_time',0)?>">
                                                <?=$aPrice['DELIVERY_VIEW']?>
                                            </div>
                                        </div>
                                    <?}?>
                                </div>
                            <?}?>
                            <div class="CmHidePriceBlFlexEl CmNameStockBlockFlex">   
                                <?if($aRes['SHOW_STOCK']&&$aPrice['SUPPLIER_STOCK']!=''){?>
                                    <div class="cm_StocktdHide CmTitShow" title="<?=Lng_x('Stock');?>">
                                        <div class="cm_svgStock">
                                            <svg class="cm_stock" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M5 23h-2v-10l8.991-8.005c1.124.998 2.25 1.997 3.378 2.996l2.255 1.997c1.127.999 2.252 2.013 3.376 3.012v10h-2v-9.118l-7.009-6.215-6.991 6.22v9.113zm2-2h10v2h-10v-2zm0-3h10v2h-10v-2zm10-3v2h-10v-2h10zm-5-14l12 10.632-1.328 1.493-10.672-9.481-10.672 9.481-1.328-1.493 12-10.632z"/></svg>
                                        </div>
                                        <div class="CmListPrStock"><?=$aPrice['SUPPLIER_STOCK']?></div>
                                    </div>
                                <?}?>
                                <?if($aRes['SHOW_SUPPLIER']&&$aPrice['SUPPLIER_NAME']!=''){?>
                                    <div class="CmStockTdList CmTitShow" title="<?=Lng_x('Supplier');?>">
                                        <div class="svgStock">
                                            <svg class="stockImg" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M5 23h-2v-10l8.991-8.005c1.124.998 2.25 1.997 3.378 2.996l2.255 1.997c1.127.999 2.252 2.013 3.376 3.012v10h-2v-9.118l-7.009-6.215-6.991 6.22v9.113zm2-2h10v2h-10v-2zm0-3h10v2h-10v-2zm10-3v2h-10v-2h10zm-5-14l12 10.632-1.328 1.493-10.672-9.481-10.672 9.481-1.328-1.493 12-10.632z"/></svg>
                                        </div>
                                        <div class="CmListPrStock"><?=$aPrice['SUPPLIER_NAME']?></div>
                                    </div>
                                <?}?>
                            </div>     
                            <div class="CmHidePriceBlFlexEl CmWrapPriceDiscount">
                                <?if($aPrice['OLD_PRICE']){?>
                                    <div class="CmDiscPriceHide">
                                        <div class="CmOldPriceHide"><i><?=$aPrice['OLD_PRICE']?></i>&ensp;</div>
                                        <div class="CmPercentDiscHide"><?=$aPrice['DISCOUNT_VIEW']?></div>
                                    </div>
                                <?}?>
                                <div class="HideCmListPrCost" style="color:#<?if($aPrice['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']==false){echo '808080';}?>;">
                                    <span><?=$aPrice['PRICE_FORMATED'];?></span>
                                </div>
                            </div>
                            <div class="CmHidePriceBlFlexEl CmToCartBut">
                            <?if($aPrice['AVAILABLE_NUM']>0 || $aRes['ALLOW_NOTAVAIL']){
                                if($aPrice['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']){
                                    $aPrice['AVAILABLE_NUM'] = 99;
                                }?> 
                                <?// "ADD TO CART" Class is: CmAddToCart / CmAddToCartQty (../includes.php)?>
                                <div class="CmPriceQuanBlockWrap">
                                    <div class="cm_Hideqty_t">
                                        <div class="HidequantMinus_t CmColorTxh cm_countButM">-</div>
                                        <input name="re_count" type="text" class="CmAddToCartQty HidequantProd_t cm_countRes" value="1" data-maxaval="<?=$aPrice['AVAILABLE_NUM']?>">
                                        <div class="HidequantPlus_t CmColorTxh cm_countButP">+</div>
                                    </div>
                                    <div class="CmAddToCart HideCmTablePrToCart" data-furl="<?=$aProd['Link']?>" data-priceid="<?=$aPrice['PriceID']?>">
                                        <svg class="cm_HideCartImg" viewBox="0 0 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
                                    </div>
                                </div>
                            <?}?>
                            </div>
                        </div>
                    <?}?>    
                </div>
            <?}?>
        </div>
    <?}?>
