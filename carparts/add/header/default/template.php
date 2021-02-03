<link rel='stylesheet' href='/<?=CM_DIR?>/add/header/default/header.css' type='text/css' media='all' />
<div class="CmHeader">
	<?
	if($HeadSearch){
		$SearchPosition = 'Main';
		include_once(PATH_x.'/add/search/default/template.php');
	}
	
	if($HeadVIN){
		//$VinNum_Def_Lang = 'en'; //Will be session selected if in CarMod
		//$VinNum_Template = 'default';
		include_once(PATH_x.'/add/vinnum/controller.php');
	}
	
	if($HeadRegNum){
		if(LANG_x=='fr'){$aRegNum_Countries = Array('F');}
		if(LANG_x=='da'){$aRegNum_Countries = Array('DK');}
		if(LANG_x=='en'){$aRegNum_Countries = Array('GB');}
		if(LANG_x=='es'){$aRegNum_Countries = Array('E');}
		if(LANG_x=='it'){$aRegNum_Countries = Array('I');}
		if(LANG_x=='de'){$aRegNum_Countries = Array('A');}
		
		//$RegNum_Def_Lang = 'en'; //Will be session selected if in CarMod
		//$RegNum_Template = 'default';
		include_once(PATH_x.'/add/regnum/controller.php');
	}
	?>
</div>