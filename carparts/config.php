<?if(!defined("CM_PROLOG_INCLUDED") || CM_PROLOG_INCLUDED!==true)die('Restricted:config'); //Configuration file

//CarMod LOCAL MySQL Base (utf8) connection settings:
$arMyCon = Array(
	"HOST" => "localhost",	
	"LOGIN" => "car-mod",
	"PASSW" => "Nl1jln6pRtFD8H#iB$",
	"DB_NAME" => "car-modTest",
);

//Administrator Password
$ADMIN_PASSW = "Zebra2222";

//CarMod License KEY
$CarModKey = "11728a711b832d2cccb4c281b102b555";

//Defines
$arCPDefines = Array(
	'CM_DIR' => 'carparts', //Module REAL root folder
	'CM_ADM' => 'admin15', //Admin REAL folder
	'PROTOCOL' => 'https', // http / https
	'FURL_MOD' 	=> 'carparts', 	//CPMod root FURL section
	'FURL_COMM' => '-com', 	//Commercial Vehicle Producers FURL appendix
	'FURL_MOTO' => '-mc', 	//Motorcycles Producers FURL appendix
	'FURL_TYPE' => 'n', 	//Same vehicle Type name - separating FURL symbol
	'FURL_PRODUCT' => 'product', //Product Frendly URL
	'FURL_SEARCH' => 'search', //Article search FURL
	'CM_FILE_CHMOD' => "", // Files PERMISSIONS used by module Updates system when creating it
	'CM_DF_RIGHTS' => "0755", // Folders PERMISSIONS used by module Updates system when creating it
);
foreach($arCPDefines as $k=>$v){if($k!=''){define($k,$v);}}

//Default path is your Domain. But you can redefine to car-mod.com
//define('MEDIA_LINK', PROTOCOL.'://car-mod.com/img');
define('MEDIA_LINK', PROTOCOL.'://'.$_SERVER['SERVER_NAME'].'/'.CM_DIR.'/img');
define('IMG_PRODUCT', PROTOCOL.'://'.$_SERVER['SERVER_NAME'].'/'.CM_DIR.'/img/product');

//$_SERVER['DOCUMENT_ROOT'] .= '/shop';
define('CM_DEV_MODE',false);

//Is Cart have encoding Utf8 BUG for Ajax?
//define('CART_UTF8DEC',true);

//Session
if(!defined('CM_NO_SESSION')){
	//@session_set_cookie_params(3600*24*3,"/");
	//session_name('frontend'); //Magento
	@session_start();
}

//PHP setup
ini_set("display_errors", 1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
//date_default_timezone_set('Europe/Kiev');

//Defines
if($_GET['ulng']!='' AND strlen($_GET['ulng'])==2){
	define('URL_LANG',$_GET['ulng']);
	$ULNG = '/'.$_GET['ulng'];
}else{$ULNG='';}
if(FURL_MOD==''){define('FURL_x',$ULNG);}else{define('FURL_x',$ULNG.'/'.FURL_MOD);}
define('PATH_x',$_SERVER['DOCUMENT_ROOT'].'/'.CM_DIR);
define('aPATH_x',$_SERVER['DOCUMENT_ROOT'].'/'.CM_DIR.'/'.CM_ADM.'/');

// My WebSite PROTOCOL & DOMAIN
if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443){$cP='https';}else{$cP='http';}
define('PROTOCOL_DOMAIN_x', $cP.'://'.$_SERVER['HTTP_HOST']); //support www.
?>