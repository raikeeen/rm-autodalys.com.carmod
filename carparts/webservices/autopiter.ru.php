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
if(extension_loaded('soap')){
	$arPart['ARTICLE'] = $ArtNum;
	$arPart['BRAND'] = $Brand;
	$arPart['BKEY'] = SingleKey_x($Brand);
    if($aWs['CURRENCY']!='EUR' AND $aWs['CURRENCY']!='USD'){$aWs['CURRENCY']='РУБ';} //("РУБ", "EUR","USD") only
    $client = NULL; 
    global $ERROR;
    function APConnect($aWs){
        global $client; global $ERROR;
        $client = new SoapClient('http://service.autopiter.ru/price.asmx?WSDL',array('soap_version'=>SOAP_1_2,'encoding'=>'UTF-8')); 
        $rsIsAuth = $client->IsAuthorization(); 
        if(!$rsIsAuth->IsAuthorizationResult){
            $rsAuth = $client->Authorization(array('UserID'=>$aWs['LOGIN'],'Password' =>$aWs['PASSW'],'Save'=>true));
            if(!$rsAuth->AuthorizationResult){$ERROR = 'Error. "AuthorizationResult" fail for user ID <b>'.$aWs['LOGIN'].'</b>';}
        }
    }
    function APgetPriceByNum ($arPart,$aWs){
        global $client;  global $ERROR;
        //echo '$catalogObj = $client->FindCatalog(array("ShortNumberDetail"=>"'.$arPart['ARTICLE'].'","Name"=>"'.$arPart['BRAND'].'")); <br>';  
        try{
            $catalogObj = $client->FindCatalog(array('ShortNumberDetail'=>$arPart['ARTICLE'],'Name'=>$arPart['BRAND']));
        }catch(Exception $e){
            $ERROR = $e->getMessage();
        }
        if(!$catalogObj->FindCatalogResult) {return false;}
        $ItemCat = $catalogObj->FindCatalogResult->SearchedTheCatalog; 
        $arCatID=Array();
		if(is_array($ItemCat)){
            foreach($ItemCat as $obCatItem){
                $CatName = SingleKey_x($obCatItem->Name,true);
				if($arPart['BKEY']=='' OR $CatName==$arPart['BKEY']){ //Only searched BRAND
                    $arCatID[] = $obCatItem->id;
                }
            }
        }else{
			$CatName = SingleKey_x($ItemCat->Name,true);
			if($arPart['BKEY']=='' OR $CatName==$arPart['BKEY']){
				$arCatID[] = $ItemCat->id;
			}
        }
		$arRes=Array();
        if(count($arCatID)>0){
            foreach($arCatID as $CatID){
				try{$details = $client->GetPriceId(array ('ID'=>$CatID,'IdArticleDetail'=>-1,'FormatCurrency'=>$aWs['CURRENCY'],'SearchCross'=>$aWs['GET_CROSSES']));}catch(Exception $e){
					echo 'exception'; var_dump($e);
					return $arRes;
				}
				if(!$details->GetPriceIdResult) {continue;}
				$arRes[] = $details->GetPriceIdResult->BasePriceForClient;
			}
        }
		return $arRes; 
    }
    APConnect($aWs);
    if($ERROR==''){
		$arResPack = APgetPriceByNum($arPart,$aWs);
		//echo '<pre>'; print_r($arResPack); echo '</pre>'; die();
		$limit_price_counter = 0;
		foreach($arResPack as $arRes){
			if(is_array($arRes) AND count($arRes)>0){
				foreach($arRes as $obRes){
					if($limit_price_counter >= 3){
						//break;
					}
					if((string)$obRes->IsSale!=''){$Damaged=1;}else{$Damaged=0;}
					$aPrices[] = Array(
						'ArtNum' => (string)$obRes->Number,
						'Brand' => (string)$obRes->NameOfCatalog, //for Search by Article only
						'Name' => Array('ru'=>$obRes->NameRus),
						'Price' => floatval($obRes->SalePrice),
						'Available' => (string)$obRes->NumberOfAvailable,
						'Stock' => trim((string)$obRes->CitySupply),
						'Delivery' => (string)$obRes->NumberOfDaysSupply,
						'Currency' => $aWs['CURRENCY'],
						'Options' => Array(
							'Damaged' => $Damaged
						)
					);
				}
			}
		}
		$limit_price_counter += 1;
    }else{echo $ERROR;}
    //die();
}else{$ERROR = 'Warning! PHP extension SOAP is not loaded';}
/* 
Documentation:
http://service.autopiter.ru/price.asmx?op=GetPriceId


Result array sample: 
Array
(
    [0] => stdClass Object
        (
            [Express] =>                            //Доставка товара завтра (! Есть определённые условия доставки - заказ до определённого времени. В зависимости от постащика разное время.)
            [RealTimeInProc] => 28                  //Выдано поставщиком, %
            [ID] => 12047590                        //ID каталога
            [IdDetail] => 417927271                 //ID детали (требуется: для корзины, при заказе, при уточнении цены на данную позицию)
            [IsSale] =>                             //Товар с дефектом
            [IsStore] =>                            //Товар на нашем складе
            [Number] => 22300-P2Y-005               //Полный номер детали
            [ShotNumber] => 22300p2y005             //Сокращенный номер детали
            [NameRus] => Корзина сцепления          //Рус. наименование
            [NameEng] => HONDA CIVIC V (...         //Анг. наименование
            [MinNumberOfSales] => 1                 //Минимальное кол-во(может быть 0 или null, тогда мин. кол-во 1)
            [NumberOfAvailable] => 4                //Доступное кол-во (если 0 или null - есть в наличии, кол-во неизвестно)
            [NumberOfDaysSupply] => 4               //Дней доставки
            [DeliveryDate] => 2014-09-30T00:00:00   //Дата доставки детали до города клиента(см. на портале)
            [NameOfCatalog] => Honda                //Название каталога
            [CitySupply] => Москва                  //Город поставщика                                                                                      
            [SalePrice] => 4656.57                  //Цена продажи с учетом доставки до города клиента и вашего коэффициента наценки (см. на портале)
            [CountrySupply] => Russia               //Страна поставщика                                                                                     
            [NumberChange] => 22300P02010           //Номер замены (если не пустой, то заказ по данной позиции не возможен, необходимо получить прайс-лист по NumberChange)
            [IsDimension] =>                        //По крупногабаритным деталям этого каталога будет отказ
            [TypeRefusal] => 4                      //Тип возврата (Значения 3 и 4 - возврат невозможен, иначе возврат возможен)
            [SearchNum] =>                          //Оригинальный номер или нет
            [RowPrice] => 3
            [RowDay] => 1
            [Weight] => 
            [PriceReturnOf] => 
            [PurchasePrice] => 
            [IDLogCenter] => 
            [MultPrice] => 
        )

    [1] => stdClass Object
        ( ...
        
        
*/
?>
