<?VerifyAccess_x('ArtSearch.templ');
AjaxCut_x(); //Top of AJAX requested content

if($aRes['PRODUCTS']){
	?><table class="CmSearchTab"><?
	foreach($aRes['PRODUCTS'] as $aP){
		?><tr><td>
			<div class="CmSearchImage" style="background-image:url(<?=$aP['Image']?>)"></div>
			</td>
			<td><a href="<?=$aP['Link']?>" class="CmSearchBox CmSearchActive CmColorBgh CmColorTxh">
				<nobr><span class="CmSearchBrand"><?=$aP['Brand']?></span> <span class="CmSearchArtNum"><?=$aP['ArtNum']?></span> 
				<?if($aP['Type']!='ART'){?><sup><?=$aP['Type']?></sup><?}?></nobr>
				<div class="CmSearchPrice"><?if($aP['PRICES_COUNT']>1){echo Lng_x('from',0).' ';}?><?=$aP['MIN_PRICE']?></div>
				<br>
				<span class="CmSearchName CmColorTx"><?=$aP['Name']?></span>
			</a>
		</td></tr><?
	}
	?></table><?
}else{
	?><div class="CmSearchBox CmSearchNotFound"><?=Lng_x('No_search_result',1)?></div><?
}

AjaxCut_x(); //Bottom of AJAX requested content

//aprint_x($aRes);
?>