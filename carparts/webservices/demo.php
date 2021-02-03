<?if(!isset($aPrices)){die('Restricted: WS');} //Direct include protection
/* 
-> INCOMING variables:
$aWs - incoming Webservice Settings array
$ArtNum - incoming Article for search
$Brand - incoming Brand for search (will not be determined if the search by Article, because on that step Brand is not selected)
$LangCode - Website selected Language code ("en","ru","ro"..) 

<- OUTGOING, required:
$aPrices - Prices array with fields: Name, Price, Currency, Available, Delivery, Stock
(only Price field is required)
*/
//echo "<pre>"; print_r($aWs); echo "</pre>"; die();


//echo "<pre>"; print_r($aPrices); echo "</pre>"; die();

/*
Documentation:
https://tehnomir.com.ua/ws/


?>