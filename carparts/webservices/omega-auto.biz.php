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
//echo "<pre>"; print_r($aWs); echo "</pre>";
if(extension_loaded('curl')){
	$AKey = SingleKey_x($ArtNum);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://public.omega-auto.biz/public/api/v1.0/product/search");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$Data = '{"SearchPhrase":"'.$AKey.'","Rest":0,"From":0,"Count":20,"Key":"ewawb5X2tHcmiMSGPCc6VIAaTiya9feg"}'; //RQzSCdY99JgV0kMtSYx0KTcE9bdWmhai
	curl_setopt($ch, CURLOPT_POSTFIELDS, $Data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: '.strlen($Data),
		'Accept: application/json')
	);
	$jRes = curl_exec($ch); curl_close($ch);
	$aWsRes = json_decode($jRes,true);
	//echo $ArtNum."<pre>"; print_r($aWsRes); echo "</pre>";// die();
	
	if(count($aWsRes['Result'])>0){
		$CurStmp=time();
		foreach($aWsRes['Result'] as $a){
			if(isset($a['Price'])){
				foreach($a['Rests'] as $rest){
					if(!in_array($rest['Key'], Array('Киев', 'Харьков', 'Днепр'))){continue;}
					$DeliveryDay = 0;
					if($rest['Key']=='Киев'){
						$rest['Key']='OMKV';
						$DeliveryDay = 1;
					}
					if($rest['Key']=='Днепр'){
						$rest['Key']='DROM';
						$DeliveryDay = 1;
					}
					if($rest['Key']=='Харьков'){$rest['Key']='KHOM';}
					
					//Options
					$aOptions = Array();
					
					$aPrices[] = Array(
						'ArtNum' => $a['Number'],
						'Brand' => $a['BrandDescription'],
						'Name' => Array('ru'=>$a['Description']),
						'Price' => $a['CustomerPrice'],
						'Available' => $rest['Value'],
						'Stock' => $rest['Key'],
						'Delivery' => $DeliveryDay,
						'Options' => $aOptions
					);
				}
			}
		}
	}
	
}else{echo 'PHP extension "curl" is not loaded';}

/*

[Result] => Array
	(
		[0] => Array
			(
				[ProductId] => -84669
				[Card] => 4610495     
				[Number] => OC90                
				[BrandDescription] => KNECHT
				[Description] => Фильтр масляный двигателя LANOS, AVEO, LACETTI, NUBIRA, NEXIA (пр-во KNECHT-MAHLE)                  
				[DescriptionUkr] => Фільтр масляний LANOS, AVEO, LACETTI, NUBIRA, NEXIA (вир-во KNECHT-MAHLE)                           
				[Weight] => 0
				[Price] => 156.12
				[CustomerPrice] => 87.48
				[Rests] => Array
					(
						[0] => Array
							(
								[Key] => Киев
								[Value] => >5
							)
*/
?>