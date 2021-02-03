<?if(!isset($aWs)){die('Restricted: WS');} //Direct include protection
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
class ooApiClass{
	var $url = 'http://www.originaalosad.ee/api/';
	var $company_id = '';
	var $password = '';
	function getProducts($productCodes){
		return json_decode(trim(file_get_contents($this->url.'/?company_id='.$this->company_id.'&password='.$this->password.'&getProducts='.$productCodes.'&format=json'), "\xEF\xBB\xBF"), true);
	}
}	
$ooApi = new ooApiClass();
$ooApi -> company_id = $aWs['LOGIN'];
$ooApi -> password = $aWs['PASSW'];
$aRes = $ooApi->getProducts($ArtNum);

if(count($aRes['products'])>0){
	foreach($aRes['products'] as $a){
		if(substr($a['brand'],-2) == 'EU'){
			$a['brand'] = trim(substr($a['brand'], 0, -2));
		}
		$aPrices[] = Array(
			'ArtNum' => $a['code'],
			'Brand' => $a['brand'], //for Search by Article only
			'Name' => Array('et'=>$a['name']),
			'Price' => $a['price'],
			//'Available' => '15',
			'Stock' => 'JOB',
			'Delivery' => $a['delivery_time'],
			'Options' => Array(
				'Weight_kg' => round($a['weight'],2),
			)
		);
	}
}

//echo '<pre>'; print_r($aPrices); echo '</pre><br><br>';

/*
Documentation:
https://lakinet.ee/WS/JSON/?m=GetMethods&UserGuid=82f75375-e412-4ffe-b1a1-bb6a7697f2b1&AspxAutoDetectCookieSupport=1
https://www.lakinet.ee/WS/SOAP/

Result array sample: 
stdClass Object
(
    [GetProductInStockResult] => stdClass Object
        (
            [Data] => stdClass Object
                (
                    [ProductInformation] => stdClass Object
                        (
                            [Code] => EFF172
                            [Name] => Kütusefilter
                            [Supplier] => Comline
                            [SupplierId] => 200000047
                            [SupplierTecDocId] => 421
                            [Ean] => 5055181548907
                            [PriceExclVat] => 14.61250
                            [RetailPriceExclVat] => 19.4833
                            [InStockQty] => 6.0000
                            [IsEndSale] => 
                            [EndSaleQty] => 
                        )

                )
*/
?>