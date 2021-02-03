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

if(extension_loaded('curl')){
	//��������� ������ - ������ ���� ����� �����! �����������, ��. ������������ http://ws.armtek.by/?page=service&alias=search#methsearch_post
	if($GetCross AND $Brand!=''){$QTp='2';}else{$QTp='1';}
	
	//VKORG = 2000 - ��������, 4000 - ������
	$URL = "VKORG=4000&QUERY_TYPE=".$QTp."&KUNNR_RG=".$aWs['CLIENT_ID']."&PIN=".$ArtNum; //echo $URL.'<br>';
	if($Brand!=''){$URL .='&BRAND='.$Brand; $WithCrosses=true; }
	$ch = curl_init('http://ws.armtek.ru/api/ws_search/search?format=json');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $URL);
	curl_setopt($ch, CURLOPT_USERPWD, $aWs['LOGIN'].':'.$aWs['PASSW'] );
	$jRes = curl_exec($ch); curl_close($ch);
	$aWsRes = json_decode($jRes,true);
	//echo '<pre>'; print_r($aWsRes); echo '</pre>'; die();
	
	//������
	if(isset($aWsRes['MESSAGES'])){
		foreach($aWsRes['MESSAGES'] as $aMsg){
			if($aMsg['TYPE']=='E'){echo $aMsg['TEXT'].'<br>';}
		}
	}
	if(isset($aWsRes['RESP'])){
		$CurStmp=time();
		foreach($aWsRes['RESP'] as $a){
			if(isset($a['PRICE'])){
				//Date
				if($a['DLVDT']!=''){
					$Y=substr($a['DLVDT'],0,4);
					$M=substr($a['DLVDT'],4,2);
					$D=substr($a['DLVDT'],6,2);
					$Stmp = strtotime($D.'-'.$M.'-'.$Y);
					$Delivery = round(($Stmp-$CurStmp)/86400);
				}
				//Options
				$aOptions = Array();
				if(intval($a['MINBM'])>1){$aOptions['Minimal_qnt'] = $a['MINBM'];}
				if(isset($a['VENSL'])){$aOptions['Chance_delivery'] = intval($a['VENSL']);}
				if(!isset($a['RETDAYS']) OR $a['RETDAYS']==0){$aOptions['No_return'] = 1;}
				
				$aPrices[] = Array(
					'ArtNum' => $a['PIN'],
					'Brand' => $a['BRAND'],
					'Name' => Array('ru'=>$a['NAME']),
					'Price' => $a['PRICE'],
					'Available' => $a['RVALUE'],
					'Stock' => $a['KEYZAK'], //$a['PARNR'].'/'.
					'Delivery' => $Delivery,
					'Currency' => $a['WAERS'],
					'Options' => $aOptions
				);
			}
		}
	}
	
}else{echo 'PHP extension "curl" is not loaded';}


/*
Documentation:
http://ws.armtek.by/?page=service&alias=search#methsearch_post

�������� ��� �������:
https://etp.armtek.by/content/index/rest-services/rest-connection

������ �����, ������ � �������� ����������� (�������� - 2000, ������ - 4000)
http://ws.armtek.by/test/test/generate/Ws_user/getUserInfo_post
���������� KUNNR ���������� �������� ������� ������� ���������� (��� ������� ��� ���� KUNNR_RG)
��� � �������� �������
https://etp.armtek.by/profile/rg
���������� ����� � ������� "����������" ����� ���������� [4000XXXX] � � ��� ������� "������":
������ �� WEB-service - ��
��������� �������� � ����� (MAX) - 5000
� ������� ���� ����� �������� � ��������� ������


Array(
    [STATUS] => 200
    [MESSAGES] => Array()
    [RESP] => Array(
		[0] => Array (
			[PIN] => C8301
			[BRAND] => LYNXAUTO
			[NAME] => ����������� �������� / ����. ������ / �����. ����. HONDA Civic 1.4-1.6 95-01 / C
			[ARTID] => 9241141
			[PARNR] => 115840 - ��� ������ ��������
			[KEYZAK] => 0000008403 - ��� ������
			[RVALUE] => 5 - ��������� ����������
			[RETDAYS] => 2 - ���������� ���� �� �������
			[RDPRF] => 1 - ���������
			[MINBM] => 1 - ����������� ����������
			[VENSL] => 95.00 - ����������� ��������
			[PRICE] => 270.50 - ����
			[WAERS] => RUB - ������
			[DLVDT] => 20161011140000 - ���� ��������, ������ ���� YYYYMMDDHHIISS
			[WRNTDT] => 20181005120000 - ���� ��������������� ��������, ������ ���� YYYYMMDDHHIISS
			[ANALOG] => ������� �������
		)
		[1] => Array
*/

?>