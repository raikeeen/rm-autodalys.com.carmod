<?
$aCurList = Array("USD","EUR","RUB","BYR","BYN","UAH","AMD","GBP","LTL","MDL","ANG","PLN","RSD","KZT","BGN","CZK","DKK","GEL","HRK","HUF","COM","CHF","ILS","RON","TRY","CNY","BAM","JPY","BGN","SEK");

function SimpleXmlUrl_x($Url){
	if(extension_loaded('libxml')){
		if(function_exists('simplexml_load_file')){
			try{
				return @simplexml_load_file($Url);
			}catch(Exception $e){ 
				ErAdd_x('Exception: '.$e); 
				return false;
			}
		}else{
			ErAdd_x('Function <b>simplexml_load_file()</b> not loaded in your PHP');
			return false;
		}
	}else{
		ErAdd_x('Extension <b>libxml</b> not loaded in your PHP'); 
		return false;
	}
}

//RUB
class ExchangeRatesRUB{
	var $rates;
	var $Conn="N";
	var $Name = 'ЦБРФ';
	var $Site = 'cbr.ru';
	var $Nominal = 1;
	var $Round = 5;
	function __construct($date = null){
		if(extension_loaded('soap')){
			try{
				ini_set("default_socket_timeout", 3);
				$client = new SoapClient("http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL");
				if (!isset($date)) $date = date("Y-m-d"); 
				$curs = $client->GetCursOnDate(array("On_date" => $date));
				$this->rates = new SimpleXMLElement($curs->GetCursOnDateResult->any);
				$this->Conn="Y";
			}catch(Exception $e){  
				$this->Conn="N"; //echo $e->getMessage();
			}
		}
	}
	function GetRate($code){
		if($this->Conn=="Y"){
			$code1 = (int)$code;
			if ($code1!=0){
				$result = $this->rates->xpath('ValuteData/ValuteCursOnDate/Vcode[.='.$code.']/parent::*');
			}else{
				$result = $this->rates->xpath('ValuteData/ValuteCursOnDate/VchCode[.="'.$code.'"]/parent::*');
			}
			if (!$result){return false; 
			}else {
				$vc = (float)$result[0]->Vcurs;
				$vn = (int)$result[0]->Vnom;
				return ($vc/$vn);
			}
		}
	}
}
//UAH
class ExchangeRatesUAH{
	public $exchange_url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';
	public $xml;
	var $Name = 'НБУ';
	var $Site = 'bank.gov.ua';
	var $Nominal = 1;
	var $Round = 5;
	function __construct(){
		if($this->xml = SimpleXmlUrl_x($this->exchange_url)){
			return $this->xml;
		}else{
			ErAdd_x('/core/exrates.php => '.get_class($this).'<br>');
			return false;
		}
	}
	function GetRate($NeedCode){
		if($this->xml!==FALSE){
			foreach($this->xml->children() as $obItem){
				$CurCode = (string)$obItem->cc;
				if($CurCode==$NeedCode){
					$CurRate = (float)$obItem->rate;
					$result = $CurRate;
				}
			}
		}
		return $result;
	}
	
	/* public $aCurRes;
	var $Name = 'Минфин';
	var $Site = 'minfin.com.ua';
	var $Nominal = 1;
	var $Round = 5;
	function __construct(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://api.minfin.com.ua/mb/749edd231438bca930dd8ffb1d6a3b20c64d9678/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$jRes = curl_exec($ch); curl_close($ch);
		$this->aCurRes = json_decode($jRes,true);
	}
	function GetRate($NeedCode){
		if(is_array($this->aCurRes) AND count($this->aCurRes)>0){
			foreach($this->aCurRes as $aItem){
				$CurCode = strtoupper($aItem['currency']);
				if($CurCode==$NeedCode){
					$result = (float)$aItem['ask'];
				}
			}
		}
		return $result;
	} */
	
}
//EUR
class ExchangeRatesEUR{
    public $exchange_url = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    public $xml;
	var $Name = 'ECB';
	var $Site = 'ecb.europa.eu';
	var $Nominal = 1;
	var $Round = 5;
    function __construct(){ 
		if($this->xml = SimpleXmlUrl_x($this->exchange_url)){
			return $this->xml;
		}else{
			ErAdd_x('/core/exrates.php => '.get_class($this).'<br>');
			return false;
		}
	}
    function GetRate($NeedCode){
		if($this->xml!==FALSE){
			foreach($this->xml->Cube->Cube->Cube as $obItem){ //echo '<pre>';print_r($obItem);echo '</pre>';
				$CurCode = (string)$obItem->attributes()['currency'];
				if($CurCode==$NeedCode){
					$result = 1/(float)$obItem->attributes()['rate'];
				}
			}
		}
		return $result;
	}
}
//BYN
class ExchangeRatesBYN{
    public $exchange_url = 'http://www.nbrb.by/Services/XmlExRates.aspx';
    public $xml;
	var $Name = 'НБРБ';
	var $Site = 'nbrb.by';
	var $Nominal = 1;
	var $Round = 3;
    function __construct(){ 
		if($this->xml = SimpleXmlUrl_x($this->exchange_url)){
			return $this->xml;
		}else{
			ErAdd_x('/core/exrates.php => '.get_class($this).'<br>');
			return false;
		}
	}
    function GetRate($NeedCode){
		if($this->xml!==FALSE){
			foreach($this->xml->children() as $obItem){
				//echo '<pre>';print_r($obItem);echo '</pre>';
				$CurCode = (string)$obItem->CharCode;
				if($CurCode==$NeedCode){
					$CurRate = (float)$obItem->Rate;
					$CurSize = (float)$obItem->Scale;
					$CurRate = $CurRate/$CurSize;
					$result = $CurRate;
				}
			}
		}
		return $result;
	}
}

//AMD
class ExchangeRatesAMD{
	var $url = "http://api.cba.am/exchangerates.asmx?op=ExchangeRatesByDate";
	var $rates;
	var $Name = 'CBA';
	var $Site = 'cba.am';
	var $Nominal = 1;
	var $Round = 4;
	function __construct(){
		$date = Date("Y-m-d");
		$xml = 
		'<?xml version="1.0" encoding="utf-8"?>'.
		'<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'.
		'<soap:Body>'.
		'<ExchangeRatesByDate xmlns="http://www.cba.am/">'.
		'<date>'.$date.'</date>'.
		'</ExchangeRatesByDate>'.
		'</soap:Body>'.
		'</soap:Envelope>';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$headers = array();
		array_push($headers, "Host: api.cba.am");
		array_push($headers, "Content-Type: text/xml; charset=utf-8");
		array_push($headers, "Cache-Control: no-cache");
		array_push($headers, "Pragma: no-cache");
		array_push($headers, "SOAPAction: http://www.cba.am/ExchangeRatesByDate");
		if($xml != null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			array_push($headers, "Content-Length: " . strlen($xml));
		}
		//curl_setopt($ch, CURLOPT_USERPWD, "user_name:password"); /* If required */
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		curl_close($ch);
		$this->rates=$response;
	}
	function getRate($iso,$node='Rate'){ // допустимые параметры  для $node: Rate для курса, Difference для разницы
		$doc = new DOMDocument();
		$doc->loadXML($this->rates);
		$l = $doc->getElementsByTagName('ExchangeRate')->length;
		for($i=0;$i<$l;$i++){
			if($doc->getElementsByTagName('ISO')->item($i)->nodeValue==$iso){
				return $doc->getElementsByTagName($node)->item($i)->nodeValue;
			}
		}
		//echo htmlspecialchars($response);
	}
}
?>