<? 
$arUrlRewrite = array(
	//Search page
	array(
		"CONDITION"	=>	"#^".FURL_x."/".FURL_SEARCH."/([^?]+?)/#",
		"RULE"	=>	"con=artnum_search&ArtSearch=$1&",
	),
/* 	array(
		"CONDITION"	=>	"#^".FURL_x."/".FURL_SEARCH."/([^?]+?)#",
		"RULE"	=>	"con=artnum_search&ArtSearch=$1",
	), */
	//Product page
	array(
		"CONDITION"	=>	"#^".FURL_x."/".FURL_PRODUCT."/(.+)/([^?]+?)/#",
		"RULE"	=>	"con=product_page&Brand=$1&ArtNum=$2&",
	),
	array(
		"CONDITION"	=>	"#^".FURL_x."/".FURL_PRODUCT."/(.+)/([^?]+?)#",
		"RULE"	=>	"con=product_page&Brand=$1&ArtNum=$2",
	),
	//Car Section Products
	array(
		"CONDITION"	=>	"#^".FURL_x."/(.+?)/(.+?)/(.+?)/([^?]+?)/#",
		"RULE"	=>	"con=car_products&brand=$1&mod_furl=$2&type_furl=$3&section_furl=$4&last=$5&",
	),
	array(
		"CONDITION"	=>	"#^".FURL_x."/(.+?)/(.+?)/(.+?)/([^?]+?)#",
		"RULE"	=>	"con=car_products&brand=$1&mod_furl=$2&type_furl=$3&section_furl=$4",
	),
	//Car Info
	array(
		"CONDITION"	=>	"#^".FURL_x."/(.+?)/(.+?)/(.+?)/#",
		"RULE"	=>	"con=car_info&brand=$1&mod_furl=$2&type_furl=$3&last=$4",
	),
	array(
		"CONDITION"	=>	"#^".FURL_x."/(.+?)/(.+?)/([^?]+?)#",
		"RULE"	=>	"con=car_info&brand=$1&mod_furl=$2&type_furl=$3",
	),
	//Car Types
	array(
		"CONDITION"	=>	"#^".FURL_x."/(.+?)/(.+?)/#",
		"RULE"	=>	"con=car_types&brand=$1&mod_furl=$2&last=$3",
	),
	array(
		"CONDITION"	=>	"#^".FURL_x."/(.+?)/([^?]+?)#",
		"RULE"	=>	"con=car_types&brand=$1&mod_furl=$2",
	),
	//Car Models - or - Root section
	array(
		"CONDITION"	=>	"#^".FURL_x."/([^?]+?)/#",
		"RULE"	=>	"con=car_models&FURL1=$1&last=$2&",
	),
	array(
		"CONDITION"	=>	"#^".FURL_x."/([^?]+?)#",
		"RULE"	=>	"con=car_models&FURL1=$1",
	),
	//Main page
	array(
		"CONDITION"	=>	"#^".FURL_x."/([^?]+?)#",
		"RULE"	=>	"con=main_page&last=$1",
	),
	array(
		"CONDITION"	=>	"#^".FURL_x."#",
		"RULE"	=>	"con=main_page&last=$1",
	),
);
?>