<?VerifyAccess_x('NoCMS');

//Own CMS sets
if(!IsADMIN_x){	 if($LNG!=LANG_x){$_REQUEST['l']=$LNG;} }


//Add to cart
if(defined('CPM_ADD_TO_CART') AND CPM_ADD_TO_CART){
	global $arCartPrice;
	//echo '<pre>'; print_r($arCartPrice); echo '</pre>';
}
//Lang
global $CPMod;
/* if($_REQUEST['l']!='' AND LANG_x!=$_REQUEST['l'] AND in_array($_REQUEST['l'],$CPMod->arLangs)){ 
	$_SESSION['LANG_x']=$_REQUEST['l'];
	header('Location: '.PROTOCOL."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	die();
} */
$actButMenu='Catalog';

if(!defined('CM_INDEX_INCLUDED')){
	define("SHOP_PAGE",true);
	AxajAddCartDOM(); //Show only Cart div if AddCart action was run
	?>
	<html>
    <head>
        <title><?=TITLE_x?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
		<script src="/<?=CM_DIR?>/media/js/jquery.js"></script>
	</head>
	<body>
		<div style="width:1100px; margin:0px auto;">
			<div id="mini_cart" style="border:1px solid #000000; padding:15px; clear:both;">Demo cart</div>
			<?=$CarMod_Content;?>
		</div>
	</body>
	</html>
	<?
	AxajAddCartDOM();
}
?>