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

//Options
$searchType = '0'; //Настройка поиска, с аналогами или без. 0 или 1. По умолчанию 0 - поиск без аналогов.
$siteName = 'ml-auto.by'; //Белорусским клиентам ml-auto.by, для России ml-auto.ru
$timeout = 5; //Максимальное время ожидания ответа от сервера, по каждому запросу, в секундах.

$login = $aWs['LOGIN'];
$password = $aWs['PASSW'];
$article = $ArtNum;
$brand = $Brand;

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://www.'.$siteName.'/webservice/Search/');
curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, "LOGIN=$login&PASSWORD=$password&ARTICLE=$article&BRAND=$brand&SEARCH_TYPE=$searchType");
$result = curl_exec($curl);
curl_close($curl);

//Remove BOM
$result = substr($result, 3);
$data = json_decode($result);
//echo '<pre>'; print_r($data); echo '</pre><br><br>';
if(count($data->RESPONSE)>0){
	foreach ($data->RESPONSE as $dd){
		$dateY = substr($dd->DATE,0,4);
		$dateM = substr($dd->DATE,4,2);
		$dateD = substr($dd->DATE,6,2);
		$date1 = "$dateY-$dateM-$dateD";
		$deliv=ceil((strtotime($date1)-time())/86400);
		if(intval($dd->CHANCE)>0){$percent=intval($dd->CHANCE);}
		if(intval($dd->MIN)>1){$min=intval($dd->MIN);}
		
		$aPrices[] = Array(
			'ArtNum' => strtoupper($dd->PIN),
			'Brand' => strtoupper($dd->BRAND), //for Search by Article only
			'Name' => Array('ru'=>$dd->NAME),
			'Price' => $dd->PRICE,
			'Available' => $dd->QUANTITY,
			'Stock' => $dd->STORAGE_CODE,
			'Delivery' => $deliv,
			'Currency' => $aWs['CURRENCY'],
			'Options' => Array(
				'Minimal_qnt' => $min,
				'Chance_delivery' => $percent,
			)
		);
	}
}

/*
Documentation:
http://ml-auto.by/webservice/
http://ml-auto.ru/webservice/

Result array sample:
Array
(
[0] => stdClass Object
(
[PIN] => 06688
[BRAND] => FEBI BILSTEIN
[NAME] => РОЛИК AUDI_VW 1.6_1.8 (ГРМ)
[QUANTITY] => 10
[PRICE] => 21.54
[DATE] => 20170225101222
[STORAGE_CODE] => 207
[CHANCE] => 78.36
[MIN] => 1
[ANALOG] => 1
)
*/
?>