<?if(!isset($aWs)){die('Restricted: WS');} //Direct include protection
/* 
-> INCOMING variables:
$aWs - incoming Webservice Settings array
$ArtNum - incoming Article for search
$AKey - incoming Article (short view) for search
$Brand - incoming Brand for search (will not be determined if the search by Article, because on that step Brand is not selected)
$LangCode - Website selected Language code ("en","ru","ro"..) 

<- OUTGOING, required:
$aPrices - Prices array with fields: ArtNum, Brand, Name, Price, Currency, Available, Delivery, Stock, Options array()
Required Fields: ArtNum, Brand, Price


mikado-parts.ru:
- запрос по Артикулу -> Да
- запрос по Артикул + Бренд -> Нет
- запрос группа артиклей -> Нет
- запрос группа артиклей + Бренд -> Нет
*/

if(extension_loaded('soap')){
	$Soap = @new SoapClient("http://www.mikado-parts.ru/ws/service.asmx?WSDL");
	try{
		$obCRes = $Soap->Code_Search(Array("Search_Code"=>$AKey, "ClientID"=>$aWs['LOGIN'], "Password"=>$aWs['PASSW']));
	}catch(Exception $e){
		echo $e->getMessage(); 
	}
	//echo '<pre>'; print_r($obCRes); echo '</pre>';die();
	
	if(count($obCRes->Code_SearchResult->List->Code_List_Row)>0){
		foreach($obCRes->Code_SearchResult->List->Code_List_Row as $obRes){
			$MikBrand = (string)$obRes->ProducerBrand;
			if(SkipBrand($MikBrand)){continue;}
			$Stock = (string)$obRes->Supplier;
			$Stock = substr($Stock,0,strpos($Stock,' ['));
			$Stock = trim(str_replace('Склад','',$Stock));
			$aPrice = Array(
				'ArtNum' => (string)$obRes->ProducerCode,
				'Brand' => (string)$obRes->ProducerBrand,
				'Name' => Array('ru'=>(string)$obRes->Name),
				'Price' => (string)$obRes->PriceRUR,
				'Available' => (string)$obRes->OnStock,
				'Stock' => $Stock,
				'Delivery' => (string)$obRes->Srock,
				'Currency' => 'RUB',
				'Options' => Array()
			);
			//Income Crosses
			if(isset($obRes->Source) AND is_object($obRes->Source)){
				$aPrice['CrossArtNum'] = (string)$obRes->Source->SourceCode;
				$aPrice['CrossBrand'] = (string)$obRes->Source->SourceProducer;
			}
			$aPrices[] = $aPrice;
		}
		
	}else{
		
	}
	
}else{echo 'PHP extension "soap" is not loaded';}

function SkipBrand($b){
	$a = Array('NO NAME','ПРОЧИЕ');
	if(in_array($b,$a)){return true;}else{return false;}
}
/*
Documentation:
http://www.mikado-parts.ru/ws/service.asmx?op=Code_Search
http://www.mikado-parts.ru/office/HelpWS.asp

Для получения разрешения на доступ сервису обращайтесь с запросом к администратору gmv@mikado-parts.ru. 
В запросе обязательно укажите ваш клиентский номер, для чего будет использоваться WebService, IP адрес, 
с которого будет осуществляться доступ, адрес страницы WEB-сайта, на которой будут использоваться функции сервиса (если возможно).


Result array sample: 
[4] => stdClass Object(
	[ZakazCode] => v264-12345
	[Supplier] => Склад №264 [FEBI BILSTEIN]
	[ProducerBrand] => FEBI BILSTEIN
	[ProducerCode] => 12345
	[Brand] => FEBI BILSTEIN
	[Country] => Евросоюз
	[Name] => Опора двигателя
	[Price] => 17.15
	[PriceRUR] => 878
	[Srock] => 6 дн.
	[CodeType] => Aftermarket
	[Source] => stdClass Object(
			[SourceProducer] => FEBI BILSTEIN
			[SourceCode] => 12345
		)
	[PrefixLength] => 5
)

*/
?>