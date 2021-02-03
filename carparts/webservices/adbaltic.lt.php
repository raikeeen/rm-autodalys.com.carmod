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

$AKey = SingleKey_x($ArtNum);

$braURL = str_replace(' ','',$Brand);

$URL = 'http://api.adbaltic.lt/api/JsonOrders/Items?itemIds='.$braURL.'-'.$AKey;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$URL);
curl_setopt($ch, CURLOPT_USERPWD, $aWs['LOGIN'].':'.$aWs['PASSW']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$aHeaders = curl_getinfo($ch);
$json = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
$jsonRes = json_decode($json,true);
//echo $err."<pre>"; print_r($json); echo "</pre>";

if(count($jsonRes)>0){
	foreach($jsonRes as $aPrice){
		foreach($aPrice['inventory'] as $aStock){
			if($aStock['locationCode'] == '1CORCW01' OR $aStock['locationCode'] == '1LTKRA01'){
				$stmp = strtotime($aStock['shipmentDate']);
				$Day = round(($stmp-time())/60/60/24,PHP_ROUND_HALF_UP);
				if($Day<1){$Day=1;}
				$aPrices[] = Array(
					'ArtNum' => $ArtNum,
					'Brand' => $Brand, //for Search by Article only
					'Price' => $aPrice['price'],
					'Available' => $aStock['quantity'],
					//'Stock' => $aStock['locationCode'],
					'Delivery' => $Day,
					'Options' => Array()
				);
			}
		}
	}
	//$WithCrosses=true;
}else{
	echo '<a href="'.$URL.'" target="_blank">Test link</a><br>';
	echo 'Empty response';
}

/*

Array
(
    [0] => Array
        (
            [id] => CONTITECH-CT1138
            [price] => 14.99
            [depositPrice] => 0
            [inventory] => Array
                (
                    [0] => Array
                        (
                            [locationCode] => 1CORCW01
                            [quantity] => 8
                            [shipmentDate] => 2019-04-21
                        )

                    [1] => Array
                        (
                            [locationCode] => 1LTKEU01
                            [quantity] => 0
                            [shipmentDate] => 2019-04-21
                        )

                    [2] => Array
                        (
                            [locationCode] => 1LTKMR01
                            [quantity] => 0
                            [shipmentDate] => 2019-04-21
                        )

                    [3] => Array
                        (
                            [locationCode] => 1LTKTA01
                            [quantity] => 0
                            [shipmentDate] => 2019-04-21
                        )

                )

        )

)
*/


?>