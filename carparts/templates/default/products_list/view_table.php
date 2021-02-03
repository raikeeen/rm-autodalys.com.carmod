<?VerifyAccess_x('ProductListTable.templ'); ?>
<?$aPrC=array(); // For show or hide title svg?>

<div class="CmPartTableView">
	<div class="CmTitleRowBl">
		<div class="CmBranArtWrap">
			<div class="CmImageIconTitlBl">
				<svg class="CmImageIconTitl" viewBox="0 0 24 24" data-imgsrc="<?=$aProd['Image']?>">
					<circle cx="12" cy="12" r="3.2"/><path d="M9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/>
				</svg>
			</div>
			<div class="CmBrArtTitl">
				<span><?=Lng_x('Brand')?>/<?=Lng_x('Article')?></span>
			</div>
			<div class="CmNameTitl">
				<span><?=Lng_x('Name')?></span>
			</div>
		</div>
		<?//if($aProd['PricesCount']){?>
			<div class="CmAvalDelStockWrap <?if(HIDE_PRODUCTS_COUNT){?>CmGrid1Fr<?}?>">
				<div class="CmAvalTitl <?if(HIDE_PRODUCTS_COUNT){?>CmBordRightN<?}?>">
					<span><?=Lng_x('Availability',0)?></span>
				</div>
				<?if(!HIDE_PRODUCTS_COUNT){?>
					<div class="CmDelivTitl">
						<span><?=Lng_x('Dtime_delivery',0)?></span>
					</div>
				<?}?>
				<div class="CmStockTitl">
					<span><?=Lng_x('Stock');?></span>
				</div>
			</div>
		<?//}?>
		<div class="CmPriceTitl"><?=Lng_x('Price')?></div>
	</div>
	<?foreach($aRes['PRODUCTS'] as $PKEY=>$aProd){?>
		<div class="CmProdTabRow CmAdmButsProduct <?if(!$aProd['PricesCount']){?>CmProdRowChange<?}?>" <?=isAdmButs_Product_x($aProd)?>>
			<div class="CmImBranArtWrap">
				<div class="CmProdImgBl <?if($aProd['Image']){?>CmPopUpImg<?}?> <?=$PKEY?>" data-imgsrc="<?=$aProd['Image']?>">
					<?if($aProd['Image']){?>
						<img class="CmPrIm" src="<?=$aProd['Image']?>" alt="<?=$aProd['Brand'].' '.$aProd['ArtNum'].' - '.$aProd['Name'].' '.$_SERVER['SERVER_NAME']?>">
					<?}?>
				</div>
				<div class="CmBrandArtBlock <?=$PKEY?>" rowspan="">
					<a class="cmBrandArt" href="<?=$aProd['Link']?>">
						<span class="brand_c" >
							<?=$aProd['Brand']?>
						</span>
						<span class="artic_c">

							<?=$aProd['ArtNum']?>
						</span>
					</a>
				</div>
				<div class="CmNameProdBlock <?=$PKEY?>" rowspan="">
					<a class="CmColorTxh" href="<?=$aProd['Link']?>"><?=$aProd['Name']?></a>
				</div>
			</div>
			<?if($aProd['PricesCount']){$aPrC[]='Y';?>
				<?$aProd['FirstPrice'] = array_shift($aProd['PRICES']);?>
				<div class="CmAvDelStWrap <?if(HIDE_PRODUCTS_COUNT){?>CmGrid1Fr<?}?>">
					<div class="CmAvailNumBlock CmColorTx CmTitShow <?if(HIDE_PRODUCTS_COUNT){?>CmBordRightN<?}?>" title="<?=Lng_x('Availability');?>">
						<?PrintProductAvailable_x($aProd['FirstPrice'], $aRes)?>
					</div>
					<?if(!HIDE_PRODUCTS_COUNT){?>
						<div class="CmDeliveryBlock <?if($aProd['FirstPrice']['DELIVERY_NUM']==0){echo 'CmInStockDelivery';}else{echo 'CmTimeDelivery';}?> CmTitShow" title="<?=Lng_x('Dtime_delivery',0)?>">
							<?if($aProd['FirstPrice']['DELIVERY_NUM']==0){?>
								<div class="CmInStockText">
									<?=Lng_x('In_stock')?>
								</div>
							<?}else{?>
								<span class="CmTextStock">
									<?=$aProd['FirstPrice']['DELIVERY_VIEW'];?>
								</span>
							<?}?>
						</div>
					<?}?>
					<div class="CmStockNameBl">
						<?if($aRes['SHOW_STOCK']&&$aProd['FirstPrice']['SUPPLIER_STOCK']!=''){?>
							<div class="CmTablePrStock CmTitShow" title="<?=Lng_x('Stock');?>">
								<?=$aProd['FirstPrice']['SUPPLIER_STOCK']?>
							</div>
						<?}?>
						<?if($aRes['SHOW_SUPPLIER']&&$aProd['FirstPrice']['SUPPLIER_NAME']!=''){?>
							<div class="CmTablePrName CmTitShow" title="<?=Lng_x('Stock');?>">
								<span>/</span>
								<span class="CmSupplNameText"><?=$aProd['FirstPrice']['SUPPLIER_NAME']?></span>
							</div>
						<?}?>
					</div>
				</div>
				<div class="CmPriceQuantBlWrap">
					<div class="CmDiscVatBlock">
						<?if($aProd['FirstPrice']['OLD_PRICE']){?>
							<div class="CmTableDiscPrice">
								<?if($aProd['FirstPrice']['PRICE_INCLUDE']){?>
									<div class="CmOldPrice"><i><span class="CmColorTx"><?=Lng_x('Including',1)?></span>&nbsp;<span class="CmVatTxt CmColorTx"><?=Lng_x($aProd['FirstPrice']['PRICE_RULE'],1)?></span></i>:&nbsp;</div>
									<div class="CmPercentDisc CmVatIncl"><?=$aProd['FirstPrice']['PRICE_INCLUDE']?></div>
								<?}else{?>
									<div class="CmOldPrice"><i><span class="CmOldPriceTable CmOldPr CmColorTx"><?=$aProd['FirstPrice']['OLD_PRICE']?></span></i>&ensp;</div>
									<div class="CmPercentDisc CmMinusPerc"><?=$aProd['FirstPrice']['DISCOUNT_VIEW']?></div>
								<?}?>
							</div>
						<?}?>
						<div class="CmTablePrCost" style="color:#<?if($aProd['FirstPrice']['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']==false){echo '808080';}?>;">
							<?=$aProd['FirstPrice']['PRICE_FORMATED']?>
						</div>
					</div>
					<div class="cm_qtyCart">
						<?if($aProd['FirstPrice']['AVAILABLE_NUM']>0 || $aRes['ALLOW_NOTAVAIL']){
						if($aProd['FirstPrice']['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']){
							$aProd['FirstPrice']['AVAILABLE_NUM'] = 99;
						}?>
							<?// "ADD TO CART" Class is: CmAddToCart / CmAddToCartQty (../includes.php)?>
							<?if($aSets['ONECLICK_EMAIL_ORDER']){?>
								<div class="CmMailOrder toCartButt CmColorBg" data-tab="AskPrice" data-tab="AskPrice" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>" data-moduledir="<?=CM_DIR?>" data-lang="<?=LANG_x?>"  data-link="<?=$ProductURL?>">
									<span>
										<svg class="CmCartImgPp" viewBox="0 2 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
										<span class="cartText"><?=Lng_x('Order')?></span>
									</span>
								</div>
							<?}else{?>
								<div class="CmQuantBlToCartBl">
									<div class="cm_qty_t">
										<div class="quantMinus_t c_TxHov cm_countButM">-</div>
										<input name="re_count" type="text" class="CmAddToCartQty quantProd_t cm_countRes" value="1" data-maxaval="<?=$aProd['FirstPrice']['AVAILABLE_NUM']?>">
										<div class="quantPlus_t c_TxHov cm_countButP">+</div>
									</div>
									<div class="CmAddToCart CmTablePrToCart CmColorBg" data-furl="<?=$aProd['Link']?>" data-priceid="<?=$aProd['FirstPrice']['PriceID']?>">
										<svg class="cm_HideCartImg" viewBox="-1 -3 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
									</div>
								</div>
							<?}?>
						<?}else{?>
							<div class="cm_NotAvailable_t">
								<svg class="cm_NotAvImg" viewBox="0 -2 24 24"><path d="M13.5 18c-.828 0-1.5.672-1.5 1.5 0 .829.672 1.5 1.5 1.5s1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-3.5 1.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm13.257-14.5h-1.929l-3.473 12h-13.239l-4.616-11h2.169l3.776 9h10.428l3.432-12h4.195l-.743 2zm-12.257 1.475l2.475-2.475 1.414 1.414-2.475 2.475 2.475 2.475-1.414 1.414-2.475-2.475-2.475 2.475-1.414-1.414 2.475-2.475-2.475-2.475 1.414-1.414 2.475 2.475z"/></svg>
								<span><?=Lng_x('Not_available')?></span>
							</div>
						<?}?>
					</div>
				</div>
			<?}else{?>
				<div class="CmAvalAskPrBlock">
					<?if($aRes['FINDPRICE_BUTTON']){?>
						<a href="<?=$aProd['FindPriceLink']?>" class="CmPriceAskBut CmColorBg CmColorBr" <?=$aRes['FindPrice_isBlank']?> >
							<svg class="CmAskImg CmColorFi" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm1.25 17c0 .69-.559 1.25-1.25 1.25-.689 0-1.25-.56-1.25-1.25s.561-1.25 1.25-1.25c.691 0 1.25.56 1.25 1.25zm1.393-9.998c-.608-.616-1.515-.955-2.551-.955-2.18 0-3.59 1.55-3.59 3.95h2.011c0-1.486.829-2.013 1.538-2.013.634 0 1.307.421 1.364 1.226.062.847-.39 1.277-.962 1.821-1.412 1.343-1.438 1.993-1.432 3.468h2.005c-.013-.664.03-1.203.935-2.178.677-.73 1.519-1.638 1.536-3.022.011-.924-.284-1.719-.854-2.297z"/></svg>
							<span class="CmColorTx"><?=Lng_x('Get_a_price',0)?></span>
						</a>
					<?}else{?>
						<?if($aRes['ASK_PRICE']){?>
							<div class="ListAskPrice_t CmAskPrice" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>" data-moduledir="<?=CM_DIR?>" data-lang="<?=LANG_x?>">
								<svg class="cm_askImg_t" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm1.25 17c0 .69-.559 1.25-1.25 1.25-.689 0-1.25-.56-1.25-1.25s.561-1.25 1.25-1.25c.691 0 1.25.56 1.25 1.25zm1.393-9.998c-.608-.616-1.515-.955-2.551-.955-2.18 0-3.59 1.55-3.59 3.95h2.011c0-1.486.829-2.013 1.538-2.013.634 0 1.307.421 1.364 1.226.062.847-.39 1.277-.962 1.821-1.412 1.343-1.438 1.993-1.432 3.468h2.005c-.013-.664.03-1.203.935-2.178.677-.73 1.519-1.638 1.536-3.022.011-.924-.284-1.719-.854-2.297z"/></svg>
								<span><?=Lng_x('Ask_price')?></span>
							</div>
						<?}
						if($aRes['ALLOW_ORDER']){?>
							<div class="CmAddToCart ListNotAvailable_t" data-furl="<?=$aProd['Link']?>" data-priceid="order">
								<svg class="cm_cartImg_t" viewBox="0 0 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
								<span><?=Lng_x('Order')?></span>
							</div>
						<?}?>
						<?if(!$aRes['ASK_PRICE'] && !$aRes['ALLOW_ORDER']){?>
							<div class="CmNoInStock_t">
								<svg class="CmNotInStockImg_t" viewBox="0 0 32 32">
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
				</div>
			<?}?>
		</div>
		<?if(count($aProd['PRICES']) > 1 && $aSets['NOT_HIDE_PRICES']==0){
			foreach ($aProd['PRICES'] as $aPrice){?>
				<div class="CmHidePriceRows <?=$PKEY?>">
					<div></div>
					<div class="CmHideAvDelStWrap">
						<div class="CmAvailNumHideBl <?if(HIDE_PRODUCTS_COUNT){?>CmBordRightN<?}?> CmColorTx">
							<span><?PrintProductAvailable_x($aPrice, $aRes)?></span>
						</div>
						<?if(!HIDE_PRODUCTS_COUNT){?>
							<div>
								<?if($aPrice['DELIVERY_NUM']===0){?>
									<div class="CmInStockText">
										<?=Lng_x('In_stock')?>
									</div>
								<?}else{?>
									<span class="CmTablePrDelivery_H"><?=$aPrice['DELIVERY_VIEW']?></span>
								<?}?>
							</div>
						<?}?>
						<div class="CmHideStockNameBl">
							<?if($aRes['SHOW_STOCK']&&$aProd['FirstPrice']['SUPPLIER_STOCK']!=''){?>
								<span><?=$aPrice['SUPPLIER_STOCK']?></span>
								<span class="CmSlash">/</span>
							<?}?>
							<?if($aRes['SHOW_SUPPLIER']&&$aProd['FirstPrice']['SUPPLIER_NAME']!=''){?>
								<span><?=$aPrice['SUPPLIER_NAME']?></span>
							<?}?>
						</div>
					</div>
					<div class="CmHidePriceQuantBlWrap">
						<div class="CmDiscVatBlock">
							<?if($aPrice['OLD_PRICE']){?>
								<div class="CmTableDiscPrice">
									<?if($aPrice['PRICE_INCLUDE']){?>
										<div class="CmOldPrice"><i><span class="CmColorTx"><?=Lng_x('Including',1)?></span>&nbsp;<span class="CmVatTxt CmColorTx"><?=Lng_x($aPrice['PRICE_RULE'],1)?></span></i>:&nbsp;</div>
										<div class="CmPercentDisc CmVatIncl"><?=$aPrice['PRICE_INCLUDE']?></div>
									<?}else{?>
										<div class="CmOldPrice"><i><span class="CmOldPr CmColorTx"><?=$aProd['FirstPrice']['OLD_PRICE']?></span></i>&ensp;</div>
										<div class="CmPercentDisc CmMinusPerc"><?=$aPrice['DISCOUNT_VIEW']?></div>
									<?}?>
								</div>
							<?}?>
							<div class="CmTablePrCost_H" style="color:#<?if($aPrice['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']==false){echo '808080';}?>"><?=$aPrice['PRICE_FORMATED']?></div>
						</div>
						<!--"ADD TO CART" Class is: CmAddToCart / CmAddToCartQty (../includes.php)-->
						<?if($aPrice['AVAILABLE_NUM']>0 || $aRes['ALLOW_NOTAVAIL']){
							if($aPrice['AVAILABLE_NUM']==0 && $aRes['ALLOW_NOTAVAIL']){
								$aPrice['AVAILABLE_NUM'] = 99;
							}?>
							<?if($aSets['ONECLICK_EMAIL_ORDER']){?>
								<div class="CmMailOrder toCartButt CmColorBg" data-tab="AskPrice" data-tab="AskPrice" data-artnum="<?=$aProd['ArtNum']?>" data-brand="<?=$aProd['Brand']?>" data-moduledir="<?=CM_DIR?>" data-lang="<?=LANG_x?>"  data-link="<?=$ProductURL?>">
									<span>
										<svg class="CmCartImgPp" viewBox="0 2 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
										<span class="cartText"><?=Lng_x('Order')?></span>
									</span>
								</div>
							<?}else{?>
								<div class="CmQuantBlockTable">
									<div class="cm_qty_tH" style="<?if($aSets['NOT_HIDE_PRICES']==1){?>/*display:flex;*/<?}?>">
										<div class="quantMinus_t CmColorTxh cm_countButM">-</div>
										<input name="re_count" type="text" class="CmAddToCartQty quantProd_t cm_countRes" value="1" data-maxaval="<?=$aPrice['AVAILABLE_NUM']?>">
										<div class="quantPlus_t CmColorTxh cm_countButP">+</div>
									</div>
									<div class="CmAddToCart CmTablePrToCart_H CmColorBg" data-furl="<?=$aProd['Link']?>" data-priceid="<?=$aPrice['PriceID']?>">
										<svg class="cm_HideCartImg" viewBox="-1 -3 24 24"><path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-10.563-5l-2.937-7h16.812l-1.977 7h-11.898zm11.233-5h-11.162l1.259 3h9.056l.847-3zm5.635-5l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/></svg>
									</div>
								</div>
							<?}?>
						<?}else{?>
							<div class="cm_NotAvailable_tH">
								<svg class="cm_NotAvImg" viewBox="0 -2 24 24"><path d="M13.5 18c-.828 0-1.5.672-1.5 1.5 0 .829.672 1.5 1.5 1.5s1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm-3.5 1.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm13.257-14.5h-1.929l-3.473 12h-13.239l-4.616-11h2.169l3.776 9h10.428l3.432-12h4.195l-.743 2zm-12.257 1.475l2.475-2.475 1.414 1.414-2.475 2.475 2.475 2.475-1.414 1.414-2.475-2.475-2.475 2.475-1.414-1.414 2.475-2.475-2.475-2.475 1.414-1.414 2.475 2.475z"/></svg>
								<span><?=Lng_x('Not_available')?></span>
							</div>
						<?}?>
					</div>
				</div>
			<?}
		}
		if(count($aProd['PRICES']) > 1 && $aSets['NOT_HIDE_PRICES']==0){?>
			<div class="CmShowMorePrBut cm_ShowMorePr_t CmColorTx" data-countpr="<?=count($aProd['PRICES'])?>" data-pkey="<?=$PKEY?>" data-hide="<?=Lng_x('Hide')?>" data-show="<?=Lng_x('More')?> (<?=count($aProd['PRICES'])?>)">
				<div></div>
				<div></div>
				<span class="cm_mP"><?=Lng_x('More')?> (<?=count($aProd['PRICES'])?>)</span>
			</div>
		<?}?>
	<?}?>
</div>
<style>
    /*.CmThSvg{<?if(count($aPrC)==0){?>display:none;<?}?>}*/
</style>
<?//aprint_x($aRes, 'aRes');
