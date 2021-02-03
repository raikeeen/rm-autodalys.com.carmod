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
if(extension_loaded('soap')){
	$Soap = @new SoapClient("https://www.lakinet.ee/WS/SOAP/SoapEndpoint.asmx?WSDL");
	try{
		$oCRes = $Soap->GetProductInStock(Array("UserGuid"=>"82f75375-e412-4ffe-b1a1-bb6a7697f2b1", "ProductCode"=>$AKey));
	}catch(Exception $e){
		echo $e->getMessage(); 
	}
	//echo '<pre>'; print_r($oCRes); echo '</pre>';
	
	if(count($oCRes->GetProductInStockResult->Data)>0){
		foreach($oCRes->GetProductInStockResult->Data as $o){
			$aPrice = Array(
				'ArtNum' => $o->Code,
				'Brand' => $o->Supplier,
				'Name' => Array('et'=>$obRes->Name),
				'Price' => $o->RetailPriceExclVat,
				'Available' => intval($o->InStockQty),
				//'Stock' => 'LAK',
				'Delivery' => '1',
				//'Currency' => 'RUB',
				'Options' => Array()
			);
			$aPrices[] = $aPrice;
		}
	}
}else{echo 'PHP extension "soap" is not loaded';}
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