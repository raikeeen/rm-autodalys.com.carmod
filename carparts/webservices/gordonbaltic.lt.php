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
$aParameters=Array(
  'CanRequestByBrand'=>true, //This script can use request by Article+Brand filter - important for farther CarMod "Joins Logic"
);
$BKey = SingleKey_x($Brand);

if(extension_loaded('curl')){
	/* $chLog = curl_init('http://catalogue.gordon-orders.lt/WebApi/');
	$jData = '{
	  "Authentication": {
		"UserId": "'.$aWs['LOGIN'].'",
		"Password": "'.$aWs['PASSW'].'"
	  },
	  "ArticleIds": {
		"'.$ArtNum.'"
	  }
	}';
	curl_setopt($chLog, CURLOPT_POST, 1);
	curl_setopt($chLog, CURLOPT_POSTFIELDS, $jData);
	curl_setopt($chLog, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($chLog, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'charset=utf-8'));
	$jR = curl_exec($chLog);
	if(curl_exec($chLog) === false){echo '(DoLog) Curl Error: '.curl_error($chLog);}
	curl_close($chLog);
	$aWRes = json_decode($jR,TRUE);


	echo '<pre>'; print_r($jR); echo '</pre><br><br>'; */



$chLog = curl_init('http://catalogue.gordon-orders.lt/WebApi/articles');

 

$jData = '{
 "Authentication": {
    "UserId": "0006032",
    "Password": "kavateka"
 },
 "ArticleIds": [
    "71-31691-00 REI"
  ]
}';
//curl_setopt($chLog, CURLOPT_VERBOSE, true);

curl_setopt($chLog, CURLOPT_POST, 1);
curl_setopt($chLog, CURLOPT_POSTFIELDS, $jData);
curl_setopt($chLog, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chLog, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'charset=utf-8'));
$jR = curl_exec($chLog);
if(curl_exec($chLog) === false){
    echo '(DoLog) Curl Error: '.curl_error($chLog);
}
curl_close($chLog);
$aWRes = json_decode($jR,TRUE);

echo "resultat";
echo '<pre>'; print_r($aWRes); echo '</pre><br><br>';






}else{echo 'PHP extension "curl" is not loaded';}

/*
Documentation:
https://gordonbaltic.lt/api/

Result:

*/
?>