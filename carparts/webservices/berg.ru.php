<?if(!isset($aPrices)){die('Restricted: WS');} //Direct include protection
/* 
-> INCOMING variables:
$aWs - incoming Webservice Settings array
$ArtNum - incoming Article for search
$AKey - incoming Article (short view) for search
$Brand - incoming Brand for search (will not be determined if the search by Article, because on that step Brand is not selected)
$LangCode - Website selected Language code ("en","ru","ro"..) 
$GetCross - 1/0 

<- OUTGOING, required:
$aPrices - Prices array with fields: ArtNum, Brand, Name, Price, Currency, Available, Delivery, Stock, Options array()
(Required fields: Price, ArtNum, Brand)
*/
if(extension_loaded('curl')){
	$arQuery[]='items[0][resource_article]='.$AKey.'&items[0][brand_name]='.$Brand;
	$API_Key = '405a94404720f6be43c0a13a7c9b5c5ed6b9d8f91ef044b70aa5cdf77bb06472';
	
	//echo '<pre>'; print_r($arQuery); echo '</pre>';
	$ch = curl_init('https://api.berg.ru/ordering/get_stock.json?'.implode('&',$arQuery).'&key='.$API_Key.'&analogs='.intval($aWs['GET_CROSSES']));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
	$json = curl_exec($ch); curl_close($ch); 
	$jsonRes = json_decode($json,true);
	//echo '<br>Response:<pre>'; print_r($jsonRes); echo '</pre><br><br>'; 
		
	if(is_array($jsonRes['resources'])){
		foreach($jsonRes['resources'] as $arRes){
			//Offers
			if(is_array($arRes['offers'])){
				foreach($arRes['offers'] as $arOffer){
					$quantity = intval($arOffer['quantity']);
					if(intval($arOffer['available_more'])){$quantity.='+';}
					$day = intval($arOffer['average_period']);
					if(intval($arOffer['assured_period'])>$arPrice["DAY"]){$day.='-'.intval($arOffer['assured_period']);}
					if(intval($arOffer['reliability'])>0){$percent=intval($arOffer['reliability']);}else{$percent='';}
					if(intval($arOffer['multiplication_factor'])>1){$min=intval($arOffer['multiplication_factor']);}else{$min='';}

					$aPrices[] = Array(
						'ArtNum' => $arRes['article'],
						'Brand' => $arRes['brand']['name'], //for Search by Article only
						'Name' => Array('ru'=>$arRes['name']),
						'Price' => floatval($arOffer['price']),
						'Available' => $quantity,
						'Stock' => $arOffer['warehouse']['type'].'-'.$arOffer['warehouse']['name'],
						'Delivery' => $day,
						'Currency' => $aWs['CURRENCY'],
						'Options' => Array(
							'Chance_delivery' => $percent,
							'Minimal_qnt' => $min
						)
					);
				}
			}
		}
	}
}else{echo 'PHP extension "curl" is not loaded';}


/*
Documentation:
https://api.berg.ru/

Array
(
    [resources] => Array
        (
            [0] => Array
                (
                    [id] => 117508140
                    [article] => 10-03-314
                    [brand] => Array
                        (
                            [id] => 332
                            [name] => ASHIKA
                        )

                    [name] => Масляный фильтр 10-03-314
                    [offers] => Array
                        (
                            [0] => Array
                                (
                                    [warehouse] => Array
                                        (
                                            [id] => 15949385
                                            [name] => BAW
                                            [type] => 3
                                        )

                                    [price] => 298.43
                                    [average_period] => 3
                                    [assured_period] => 3
                                    [reliability] => 93
                                    [is_transit] => 
                                    [quantity] => 1
                                    [available_more] => 
                                    [multiplication_factor] => 1
                                    [delivery_type] => 1
                                )

*/

?>