<?if(!isset($aPrices)){die('Restricted: WS');} //Direct include protection


if(extension_loaded('curl')){
	global $URL; 
	$URL = "https://api.shate-m.com";
	global $ch; 
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $URL."/login");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, $aWs['LOGIN'].":".$aWs['PASSW']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "ApiKey=".$aWs['CLIENT_ID']."");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$Res = curl_exec($ch);
	$HeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$Headers = substr($Res, 0, $HeaderSize);
	$ResBody = substr($Res, $HeaderSize);
	$aHeaders = explode("\r\n", $Headers);
	global $Token; 
	foreach($aHeaders as $Header){
		if(strpos($Header,'token: ')!==false OR strpos($Header,'Token: ')!==false){
			$Token=$Header;
		}
	}
	
	if($Token){
		//$_SESSION['ShateM-Token'] = $Token;
		curl_setopt($ch, CURLOPT_HTTPHEADER,array($Token));
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, false);
		$aRes=Array();
		
		//Поиск по Артикулу
		if($Brand==''){
			curl_setopt($ch, CURLOPT_URL, $URL."/api/search/GetTradeMarksByArticleCode/".$AKey);
			$jRes = curl_exec($ch); 
			$aMrk = json_decode($jRes,TRUE);
			//echo '<pre>'; print_r($aMrk); echo '</pre>'; 
			if($aMrk['TradeMarkByArticleCodeModels'] AND count($aMrk['TradeMarkByArticleCodeModels'])>0){
				foreach($aMrk['TradeMarkByArticleCodeModels'] as $a){
					$aRes[] = RequestShateM_BraArt($AKey,$a['TradeMarkName'],$a['TradeMarkId'],0);
				}
			}
		//Бренд и Артикул
		}else{
			$Brand = BrandAlias_ShateM($Brand); //Конвертировать Бренд в представление Шате-М
			$aRes[] = RequestShateM_BraArt($AKey,$Brand,'',$GetCross);
		}
		//Prices
		if(count($aRes)>0){
			foreach($aRes as $aRow){
				if(count($aRow['PriceModels'])>0){
					foreach($aRow['PriceModels'] as $aArt){
						if(count($aArt['ArticlePriceInfo'])>0){
							foreach($aArt['ArticlePriceInfo'] as $a){
								
								$aPrices[] = Array(
									'ArtNum' => $aArt['ArticleCode'],
									'Brand' => BrandAlias_ShateM($aArt['TradeMarkName'],true), //for Search by Article only
									'Name' => Array('ru'=>ucfirst(strtolower($aArt['Description']))),
									'Price' => $a['Price'],
									'Available' => $a['Qty'],
									'Stock' => $a['City'],
									'Delivery' => $a['DeliveryTerm'],
									'Options' => Array(
										'Multiplicity' => intval($a['Multiplicity']),
									)
								);
							}
						}
					
					}
				}
			}
		}
		//echo '<pre>'; print_r($aRes); echo '</pre>'; //die();
		//echo '<pre>'; print_r($aPrices); echo '</pre>'; //die();
		
	}else{
		echo 'No Token returned<br>'.$ResBody.'<br>';
		echo '<pre>'; print_r($aHeaders); echo '</pre>';
	}
	curl_close($ch);
}else{echo 'PHP extension "curl" is not loaded';}



function RequestShateM_BraArt($AKey,$Brand='',$BrandID='',$GetCross){
	global $Token; global $ch; global $URL;
	$U = $URL."/api/search/GetPricesByArticle?ArticleCode=".$AKey."&TradeMarkName=".$Brand."&TradeMarkId=".$BrandID."&IncludeAnalogs=".$GetCross;
	$U = str_replace(' ','%20',$U); //echo $U;
	curl_setopt($ch, CURLOPT_URL, $U);
	$jRes = curl_exec($ch); //echo '<pre>'; print_r($jRes); echo '</pre>';
	return json_decode($jRes,TRUE);
}

function BrandAlias_ShateM($Brand,$Back=false){
	$aWsB = Array(
		"Mario Ghibaudi" => "GHIBAUDI",
	);
	foreach($aWsB as $From=>$To){
		if($Back){if($Brand==$To){$Brand = $From; break;}}else{if($Brand==$From){$Brand = $To; break;}}
	}
	return $Brand;
}
/*
Documentation: https://api.shate-m.com/Help

https://api.shate-m.com" (для РБ) или "https://api.shate-m.ru" (для РФ) или "https://api.shate-m.kz:4443" (для РК)

Array(
    [PriceModels] => Array(
		[0] => Array(
			[ArticleId] => 244348274
			[ArticleCode] => 2323
			[TradeMarkId] => 10002
			[TradeMarkName] => LIQUI MOLY
			[Description] => М/М СИНТ. TOP TEC 4300 5W-30 1Л
			[ArticlePriceInfo] => Array(
				[0] => Array(
					[Price] => 23.46
					[Qty] => 8
					[DeliveryTerm] => 7
					[Multiplicity] => 1
					[City] => 
					[OfferKey] => qVO4NbRldxZ+f3l7cOedqlMASABBAFQARQAtAFMAMAAxAA==
				)
*/


?>