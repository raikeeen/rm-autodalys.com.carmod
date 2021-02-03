<?if(!isset($aPrices)){die('Restricted: WS');} //Direct include protection
/* 
-> INCOMING variables:
$aWs - incoming Webservice Settings array
$ArtNum - incoming Article for search
$Brand - incoming Brand for search (will not be determined if the search by Article, because on that step Brand is not selected)
$LangCode - Website selected Language code ("en","ru","ro"..) 

<- OUTGOING, required:
$aPrices - Prices array with fields: Name, Price, Currency, Available, Delivery, Stock, Options array()
(only Price field is required)
*/
//echo "<pre>"; print_r($aWs); echo "</pre>"; die();
if(extension_loaded('curl')){
	$AKey = SingleKey_x($ArtNum);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSLVERSION,3);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_URL, "https://wes.autonet.ro/ArticleOffer/GetArticleOffers");
	curl_setopt($ch, CURLOPT_POSTFIELDS,"[{'PartNo': '".$AKey."'}]");
	//curl_setopt($ch, CURLOPT_POSTFIELDS,"[{'TDBrandId': '$BRANDID'}]");
	//curl_setopt($ch, CURLOPT_POSTFIELDS,"[{'TDArticleNo': '$ARTICLETEC'}]");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json; charset=utf-8",
		"TAX-CODE: ".$aWs['LOGIN'],
		"SECURITY-TOKEN: ".$aWs['PASSW']
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$jRes = curl_exec($ch); curl_close($ch);
	$aWsRes = json_decode($jRes,true);
	//echo $ArtNum."<pre>"; print_r($aWsRes); echo "</pre>";//die();

	if(isset($aWsRes['ArticleOffers']) AND is_array($aWsRes['ArticleOffers']) ){
		foreach($aWsRes['ArticleOffers'] as $a){
			$aPrices[] = Array(
				'ArtNum' => $a['PartNo'],
				'Brand' => BrandAlias($a['BrandName'],true), //for Search by Article only
				'Name' => Array('ro'=>$a['ArticleName']),
				'Price' => $a['PriceWoVat'],
				'Available' => $a['StockQuantity'],
			);
		}
	}
	if(isset($aWsRes['Error']) AND $aWsRes['Error']['HasError']){
		echo $aWsRes['Error']['Message'];
	}
}else{echo 'PHP extension "curl" is not loaded';}

/*

Array(
	[ArticleOffers] => Array(
		[0] => Array(
			[PartNo] => HU6004X
			[ArticleName] => Filtru ulei
			[BrandName] => MANN-HUMME
			[TDBrandId] => 4
			[TDArticleNo] => HU 6004 x
			[PriceWoVat] => 35.55
			[StockQuantity] => 30
		)
	)
    [Error] => Array(
		[HasError] => 
		[Type] => -1
		[Message] => NO ERROR.
	)
)
*/

function BrandAlias($Brand,$Back=false){
	$aWsB = Array(
		"MANN-FILTER" => "MANN-HUMME",
	);
	foreach($aWsB as $From=>$To){
		if($Back){
			if($Brand==$To){$Brand = $From; break;}
		}else{
			if($Brand==$From){$Brand = $To; break;}
		}
	}
	return $Brand;
}

?>