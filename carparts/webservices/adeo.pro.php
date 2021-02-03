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

$xml='<?xml version="1.0" encoding="UTF-8" ?>
	<message>
		<param>
			<action>price</action>
			<login>'.$aWs['LOGIN'].'</login>
			<password>'.$aWs['PASSW'].'</password>
			<code>'.$ArtNum.'</code>
			<brand>'.$Brand.'</brand>
			<crosses>disallow</crosses>
		</param>
	</message>';
$data = array('xml' => $xml);
$address="https://xml.adeo.pro/pricedetals2.php";
$ch = curl_init($address);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$xmlRes=curl_exec($ch);
$aRes = new SimpleXMLElement($xmlRes);
//echo '<pre>'; print_r($aRes->detail); echo '</pre><br><br>'; die();
if(isset($aRes->detail)){
	if(is_array($aRes->detail)){
		foreach($aRes->detail as $o){
			if(isset($o->PercentRefuse)){$Chance_delivery = 100 - intval($o->PercentRefuse);}else{$Chance_delivery = 100;}
			$aPrices[] = Array(
				'ArtNum' => $o->code,
				'Brand' => $o->producer, //for Search by Article only
				'Name' => Array('ru'=>$o->caption),
				'Price' => $o->price,
				'Available' => $o->rest,
				'Stock' => $o->stock,
				'Delivery' => $o->deliveryDisplay,
				//'Currency' => $a['Currency'],
				'Options' => Array(
					'Chance_delivery' => $Chance_delivery
				)
			);
		}
	}else{
		//echo '<pre>'; print_r($aRes->detail); echo '</pre><br><br>'; die();
		$aPrices[] = Array(
			'ArtNum' => $aRes->detail->code,
			'Brand' => $aRes->detail->producer, //for Search by Article only
			'Name' => Array('ru'=>$aRes->detail->caption),
			'Price' => $aRes->detail->price,
			'Available' => $aRes->detail->rest,
			'Stock' => $aRes->detail->stock,
			'Delivery' => $aRes->detail->deliveryDisplay,
			//'Currency' => $a['Currency'],
			'Options' => Array(
				'Chance_delivery' => $Chance_delivery
			)
		);
	}
}else{echo 'Result Empty';}

/*
Documentation:
https://wiki.adeo.pro/index.php/XML_%D1%81%D0%B5%D1%80%D0%B2%D0%B8%D1%81

SimpleXMLElement Object
(
    [detail] => Array
        (
            [0] => SimpleXMLElement Object
                (
                    [analog] => Прайс
                    [caption] => 1974G Амортизатор газ. перед Fiat Ducato (250) 18Q 06->
                    [code] => 351974070000
                    [currency] => руб
                    [dataprice] => 2019-10-26 11:30:06
                    [delivery] => 3
                    [deliverydays] => 1
                    [deliveryDisplay] => 0-1
                    [id] => 0_0
                    [price] => 4704.72
                    [userformat_price] => 4705
                    [producer] => MAGNETI MARELLI
                    [rest] => 20
                    [minOrderCount] => 1
                    [stock] => Cella1183/1
                    [RegionName] => Минск
                    [bra_id] => 95
                    [cella_id] => 48384
                    [PercentRefuse] => 2
                    [fast_cella] => 87
                    [n_file] => 1
                    [supplier_comment] => Возврат невозможен

                    [arrival_date] => 2019-10-29 19:00:00
                    [b_id] => C4DDBF35BA2437D9C79ABBBD07366072FF5BF1080E3F32D63BD1429142C5AAFD4670B4F420E86D8571AB1EF6528DFDE5A443454AA675A3EA56D41FFD3D4F0B30FC113693529BE8515641FB95DA4BA9CCF257F4097B0B1F946852CF5397345433BE604D69CA77E680E96649F479E3EA4DDC39E0558835D30EA9EA2BF26D3A0754DA4D2B342D0485536BA46C495E1867BEC62CE4909CDA5DCADEC4F15808A977E4595BB6977090BAA9E765053CCF38744D
                )
*/
?>