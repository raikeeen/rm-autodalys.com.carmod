<?if(!isset($aPrices)){die('Restricted: WS');} //Direct include protection

$useOnlineStocks=1;  //Флаг "использовать online-склады". Может принимать значения 0 или 1 (не использовать и использовать соответственно; по умолчанию - 0). Если выключено, то в выдачу не будут попадать детали с online-складов, что позволит увеличить скорость ответа.

if(extension_loaded('curl')){
	$Passw = md5($aWs['PASSW']);
	
	$URL = "http://voshod-avto.ru.public.api.abcp.ru/search/articles/?userlogin=".$aWs['LOGIN']."&userpsw=".$Passw."&number=".urlencode($ArtNum)."&brand=".urlencode($Brand)."&useOnlineStocks=".$useOnlineStocks;
	
	$ch = curl_init($URL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$json = curl_exec($ch); curl_close($ch);
	$aRes = json_decode($json,true);
	
	echo '<br><pre>'; print_r($aRes); echo '</pre>'; die();
	
	if(is_array($jsonRes)){
		foreach($jsonRes as $obRes){
			//Make valid Price array
			$arPrice = TDMPriceArray($arPart); 
			//Webservice data
			$arPrice["ARTICLE"] = (string)$obRes->number;
			$arPrice["ALT_NAME"] = (string)$obRes->description;
			$arPrice["BRAND"] = (string)$obRes->brand;
			$arPrice["PRICE"] = (string)$obRes->price;
			$arPrice["CURRENCY"] = $aWs['CURRENCY'];
			$arPrice["DAY"] = (string)$obRes->deliveryPeriod; //Срок поставки (в часах).
			$arPrice["DAY"] = intval($arPrice["DAY"]/24);
			$arPrice["AVAILABLE"] = (string)$obRes->availability;
			if($arPrice["AVAILABLE"]=='-1'){$arPrice["AVAILABLE"]='1+';}
			if($arPrice["AVAILABLE"]=='-2'){$arPrice["AVAILABLE"]='10+';}
			if($arPrice["AVAILABLE"]=='-3'){$arPrice["AVAILABLE"]='99+';}
			if($arPrice["AVAILABLE"]=='-10'){$arPrice["AVAILABLE"]=0;} //"под заказ"
			$arPrice["STOCK"] = (string)$obRes->supplierCode;
			$arPrice["OPTIONS"] = '';
			//Price options
			$arOps = Array();
			$MINIMUM = (string)$obRes->packing;
			if($MINIMUM>1){$arOps['MINIMUM']=$MINIMUM;}
			$WEIGHT = (string)$obRes->weight; //Вес одной единицы товара в килограммах
			if($WEIGHT>0){$arOps['WEIGHT']=$WEIGHT;}
			$LITERS = (string)$obRes->volume; //Объем одной единицы товара
			if($LITERS>0){$arOps['LITERS']=$LITERS;}
			$PERCENTGIVE = (string)$obRes->deliveryProbability; //Вероятность поставки товара поставщика
			if($PERCENTGIVE>0){$arOps['PERCENTGIVE']=$PERCENTGIVE;}
			$NORETURN = (string)$obRes->noReturn; //Флаг "Без возврата"
			if($NORETURN>0){$arOps['NORETURN']=$NORETURN;}
			$arPrice["OPTIONS"] = TDMOptionsImplode($arOps,$arPrice);
			//Add new record
			$arPrices[] = $arPrice;
		}
	}elseif(is_object($jsonRes)){
		if($jsonRes->errorCode>0){
			if($jsonRes->errorCode==301){
				//No results
			}else{
				echo $jsonRes->errorMessage.' ['.$jsonRes->errorCode.']';
			}
		}
	}
		
	
}else{echo 'PHP extension "curl" is not loaded';}

/*
Documentation:
http://docs.abcp.ru/wiki/API:Docs 

Result array sample: 
Array
(
    [0] => stdClass Object
        (
            [distributorId] => 307154
            [grp] => 
            [code] => 
            [nonliquid] => 
            [brand] => Febi
            [number] => 02374
            [numberFix] => 02374
            [description] => Антифриз желтый-зелёный G11 1,5л FEBI концентрат упак12шт.
            [availability] => 50
            [packing] => 1
            [deliveryPeriod] => 24
            [deliveryPeriodMax] => 
            [distributorCode] => 
            [supplierCode] => 29835
            [supplierColor] => E0FDFF
            [supplierDescription] => 
            [itemKey] => qvajQs2Z/tP+TKYf6hA4lOVSwWrEIBD9lTDnJGg0u6nHQlv21EOvhWCiWwZsIkYLS+m/N2og6f5CL8+ZN2+cccZvWIK1BrXrx1lpEFMwpgSll9Gh9ThPIOA9EE5JRKYi8ibZXbJ5QrIz7Fyk45SwTTjsaTzZ7KHK2ntRSw91NmnxQmlBy01VPD89XnIJmVAfMo5FD71u/ZHMpGTOEnXdA0zSLM4Pa2pYB7FYh6O+xDE07anudu7A2BhPzmq/Br87b9LoTHRd3dISglXS697jZ7qAUF5RUjW8oEQwLvgZ/ky/V/oqg/H/ewvyS6KRAxr0NxAtKQFVr3DxDofgZweCkTNteQljcE5P4613ccq4roXek3Ncx8p+OJu/+88vEL/dtQ==
            [price] => 288.51
            [weight] => 1.780
            [volume] => 
            [groupId] => 0
            [deliveryProbability] => 0
            [lastUpdateTime] => 2014-10-24 10:34:47
            [additionalPrice] => 0
            [noReturn] => 0
            [isSetInOnlineWh] => 1
            [isSetInNonOnlineWh] => 
            [fromPublicApi] => 1
        )

Result Error sample:
stdClass Object
(
    [errorCode] => 102
    [errorMessage] => Неправильное имя или пароль!
)
*/

//echo '<pre>'; print_r($arWsParts); echo '</pre>'; 
//echo '<pre>'; print_r($aWs); echo '</pre>';

?>
