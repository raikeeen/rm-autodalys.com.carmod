<?VerifyAccess_x('PrestaShop');
define('_PS_MODE_DEV_', true);
define("ENABLE_DEBUG_OUTPUT", true);

require($_SERVER["DOCUMENT_ROOT"].'/config/config.inc.php');
if(!$context){$context = Context::getContext();}
// Get language from URL & save for client
if(isset($_GET['ulng']) AND strlen($_GET['ulng'])==2){
	$aPsLangs = Language::getLanguages(true);
	foreach($aPsLangs as $aPsLang){
		if($aPsLang['iso_code']==$_GET['ulng']){
			$context->language = new Language($aPsLang['id_lang']);
		}
	}
}

//$sql = 'SELECT * FROM `ps_1product` WHERE `id_product` = 10 ';
//$arRes = Db::getInstance()->executeS($sql);
//echo '<br><pre>';print_r($arRes);echo '</pre>';


//Groups:
//$arPsUGroups = $context->customer->getGroups(); //array of linked groups
$PsDefGroup = $context->customer->id_default_group;
if($PsDefGroup){
	foreach($CPMod->aUserGroups as $GpID=>$aGp){
		if($aGp['CMS_UID']==$PsDefGroup AND $_SESSION['CM_USER_GROUP']!=$GpID){
			$_SESSION['CM_USER_GROUP'] = $GpID; 
			Redirect_x();
		}
	}
}


//Lang (Switched in /carparts/config.php)
$PS_LANG = (string)$context->language->iso_code;
if($PS_LANG AND LANG_x!=$PS_LANG AND in_array($PS_LANG,$CPMod->arLangs)){
	$_SESSION['LANG_x']=$PS_LANG;
	Redirect_x($_SERVER['REQUEST_URI']);
}

//Currency 
$PsCurrID = $context->cookie->__get('id_currency'); //echo $PsCurrID;
$aPsCurrCodes = Array(1=>'EUR',2=>'USD',3=>'AED');
if(isset($aPsCurrCodes[$PsCurrID])){$PsCurr = $aPsCurrCodes[$PsCurrID];}
if(isset($PsCurr) AND $PsCurr AND CURR_x!=$PsCurr){
	$_SESSION['CURR_x']=$PsCurr;
	Redirect_x($_SERVER['REQUEST_URI']);
}

/* if(!IsADMIN_x){
    $arPGID = $CPMod->arPriceGID;
    global $USER;
    $arGroups = array((int)Group::getCurrent()->id);
    $isAuthorisedGroup = false;
		//var_dump($_SESSION);var_dump($arPGID);var_dump($arGroups );die();
    foreach($arPGID as $TDM_GID=>$CMS_GID){
        if(in_array($CMS_GID,$arGroups)){
            $isAuthorisedGroup = true;
            if($_SESSION['CM_USER_GROUP']!=$TDM_GID){
                $_SESSION['CM_USER_GROUP']=$TDM_GID;
				//var_dump($_SESSION);var_dump($arPGID);var_dump($arGroups );die();
                Redirect_x($_SERVER['REQUEST_URI']);
            }
            break;
        }

    }
    if (!$isAuthorisedGroup) {
        unset($_SESSION['CM_USER_GROUP']);
    }
} */

//Clear Presta URLs
$_REQUEST=Array(); 
$_GET=Array();

$controller = new FrontController();
$controller->init();


//Add to cart
if(defined('CM_ADD_TO_CART')){
	global $aCmAddCart;
	global $aCmCartErrors;
	$DefaultCategory = intval($CPMod->arSettings["CMS_DEFCATID"]);
	$Price = $aCmAddCart['Price'];
	//$Price = round($Price-(($Price/100)*16.66659),2); //-20%
	$DefaultTaxGroup = intval($CPMod->arSettings["CMS_TAXID"]);
	$Reference = $aCmAddCart['Brand'].' '.$aCmAddCart['ArtNum'];

	//Presta init
	$logged = $context->cookie->__get('logged');
	$id_cart = $context->cookie->__get('id_cart');
	$id_lang = $context->cookie->__get('id_lang');
	$id_guest = $context->cookie->__get('id_guest');
	$id_currency = $context->cookie->__get('id_currency');

	// Add cart if no cart found
	if (!$id_cart){
		$context->cart = new Cart();
		$context->cart->id_customer = $context->customer->id;
		$context->cart->id_currency = $id_currency;
		$context->cart->add();
		if($context->cart->id){
			$context->cookie->id_cart = (int)$context->cart->id;
		}
		$id_cart = (int)$context->cart->id;
	}

	$doAdd=true; $SkipNewProduct=false; $AttID=false;
	if(!$id_cart>0){$doAdd=false; $aCmCartErrors = 'Your cookie <b>id_cart</b> is wrong!'; }
	if(!$id_lang>0){$doAdd=false; $aCmCartErrors = 'Your cookie <b>id_lang</b> is wrong!'; }

	if($doAdd){
		
		// Weight (Product & Attribute)
		$Weight=0;
		if(isset($aCmAddCart['Options']['Weight_kg'])){
			$Weight = floatval($aCmAddCart['Options']['Weight_kg']['Text']);
		}
		if(isset($aCmAddCart['Options']['Weight_gr'])){
			$Weight = round($aCmAddCart['Options']['Weight_gr']['Text']/1000,2);
		}
		// EAN13 (Product & Attribute)
		$ean13='';
		if(isset($aCmAddCart['EANS'])){
			$aEANs = explode(', ',$aCmAddCart['EANS']); //First one
			$ean13 = $aEANs[0];
		}
		// Minimal Quantity (Product & Attribute)
		$minimal_quantity=1;
		if(isset($aCmAddCart['Options']['Minimal_qnt']) AND $aCmAddCart['Options']['Minimal_qnt']>0){
			$minimal_quantity = $aCmAddCart['Options']['Minimal_qnt']; 
			if($aCmAddCart['Quantity']<$aCmAddCart['Options']['Minimal_qnt']){$aCmAddCart['Quantity']=$aCmAddCart['Options']['Minimal_qnt'];}
		}
		
		//Check avail. CarMod Product in Presta
		$sql = 'SELECT p.`id_product`, p.`price`, pl.`name` FROM `'._DB_PREFIX_.'product` p
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
				WHERE pl.`id_lang` = '.$id_lang.' AND '.
				//p.`price` = '.$Price.' AND
				'p.`reference` = "'.$Reference.'" ';
		$aPsRes = Db::getInstance()->executeS($sql);
		if(count($aPsRes)>0){
			$SkipNewProduct=true; //echo '<br><pre>';print_r($aPsRes);echo '</pre>';
			$aProduct = $aPsRes[0];
			$ProdID = $aProduct['id_product'];
			if($aProduct['price']!=$Price){
				
				$AttPriceEx = $Price - $aProduct['price']; //Difference +/-
				
				// Attributes search
				$aAtts = Product::getProductAttributesIds($ProdID); //$obProduct = new Product($ProdID,false,$id_lang);
				if($aAtts AND is_array($aAtts) AND count($aAtts)>0){
					foreach($aAtts as $aAtt){
						$PrAtID = $aAtt['id_product_attribute'];
						$AttPrice = Combination::getPrice($PrAtID);
						if(round($AttPrice,4) == round($AttPriceEx,4)){ $AttID=$PrAtID; break; } //Attribute founded
					}
				}
				//Add Attribute
				if(!$AttID){
					$obProduct = new Product($ProdID,false);//,$id_lang
					$AttID = $obProduct->addCombinationEntity(
						$Price, //wholesale_price
						round($AttPriceEx,2), //price 
						0, //Weight
						0, //unit_impact
						false, //ecotax
						0, //quantity DEPRECATED
						"", //id_images
						$aCmAddCart['Brand'].' '.$aCmAddCart['ArtNum'].' '.$aCmAddCart['PriceNum'], //reference
						null, //id_supplier
						$ean13,
						false, //default
						$aCmAddCart['Supplier_stock'], //location
						$aCmAddCart['PriceNum'], //upc
						$minimal_quantity,
						array(), //id_shop_list array
						null //$available_date
					);
				}
			}
		}
			
		if(!$SkipNewProduct){
			$obProduct = new Product(false,false);//,$id_lang
			$obProduct->id_category_default = $DefaultCategory;
			$obProduct->id_category = $DefaultCategory;
			$obProduct->redirect_type = '404';
			$obProduct->unity = $aCmAddCart['URL'];
			
			// Supplier of PrestaShop (1C integration)
			/*
			$SupName = $aCmAddCart['Supplier_stock']; //or $aCmAddCart['Supplier'] (without "Stock")
			$SupID = SupplierCore::getIdByName($SupName);
			if(!$SupID){
				$obSupp = new SupplierCore(null,$id_lang);
				$obSupp->name = $SupName;
				$obSupp->active = 1;
				$obSupp->add();
				$SupID = $obSupp->id;
				//echo '<br><pre>';print_r($obSupp);echo '</pre>';die();
			}
			$obProduct->supplier_name = $SupName;
			$obProduct->id_supplier = $SupID;
			*/
			
			// Multilangual
			$NAME = substr(trim($aCmAddCart['Name']),0,128);
			$NAME = str_replace(Array('/','=',';'),',',$NAME);
			if($NAME==''){$NAME = $aCmAddCart['Brand'].' '.$aCmAddCart['ArtNum'];}
			$arPsLangs = Language::getLanguages(true);
			foreach($arPsLangs as $aL){
				$aNAME[$aL['id_lang']] = $NAME;
				$aDESC[$aL['id_lang']] = 'This product is generated by CarMod at /'.CM_DIR.'/tocms/PrestaShop.1.7.x.php';
				$aSHOR[$aL['id_lang']] = 'Supplier:'.$aCmAddCart['Supplier_stock'].'; Delivery:'.$aCmAddCart['Delivery_view'].'';
				$aLINK[$aL['id_lang']] = $aCmAddCart['PriceNum'].'_'.$aL['iso_code'];
			}
			$obProduct->name = $aNAME;
			$obProduct->description = $aDESC;
			$obProduct->description_short = $aSHOR;
			$obProduct->link_rewrite = $aLINK;
			// Price
			$obProduct->price = $Price; //Что бы кидать в корзину товар без учета VAT - создайте правило найенки с %VAT и опцией "Применить и для корзины" = OFF
			$obProduct->wholesale_price = $aCmAddCart['Price_source']; //Cost price: Purchase price for the product. Don't include tax. It must be below the retail price: the difference between the two will be your profit.
			
			$obProduct->show_price = 1;
			$obProduct->out_of_stock = 1;
			// Data
			$obProduct->reference = $Reference;
			$obProduct->available_for_order = 1; //true
			$obProduct->visibility = 'none'; //both
			$obProduct->is_virtual = 0;
			$obProduct->id_tax_rules_group = $DefaultTaxGroup;
			// 
			$obProduct->meta_title = $aCmAddCart['Brand'].' - '.$aCmAddCart['ArtNum'];
			$obProduct->meta_description = $aCmAddCart['Brand'].' - '.$aCmAddCart['ArtNum'].' '.$NAME; //$aCmAddCart['URL'];
			// Options
			$obProduct->weight = $Weight;
			$obProduct->ean13 = $ean13;
			$obProduct->minimal_quantity = $minimal_quantity;
			$obProduct->delivery_in_stock = $aCmAddCart['Delivery_view'];
			$obProduct->delivery_out_stock = $aCmAddCart['Delivery_view'];
			if(isset($aCmAddCart['Options']['Used'])){$obProduct->condition = 'used';}
			if(isset($aCmAddCart['Options']['Restored'])){$obProduct->condition = 'refurbished';}
			
			//echo '<br><pre>';print_r($obProduct);echo '</pre>';
			$obProduct->add();
			if($obProduct->id>0){
				$ProdID = $obProduct->id;
				$obProduct->addToCategories(array($DefaultCategory));
				//$obProduct->setWsCategories(Array("id"=>$DefaultCategory));
				//Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'category_product` (`id_category`, `id_product`, `position`) VALUES ('.$DefaultCategory.','.$ProdID.',0)');
				
				//Add image
				if($aCmAddCart['Image']!='' AND $ProdID>0){
					$shops = Shop::getShops(true, null, true);
					$image = new Image();
					$image->id_product = $ProdID;
					$image->position = Image::getHighestPosition($ProdID)+1;
					$image->cover = true; // or false;
					if(($image->validateFields(false, true)) === true && ($image->validateFieldsLang(false, true)) === true && $image->add()){
						$image->associateTo($shops);
						$tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
						if(Tools::copy($aCmAddCart['Image'], $tmpfile)){
							$path = $image->getPathForCreation();
							ImageManager::resize($tmpfile, $path.'.jpg');
						}
						unlink($tmpfile);
					}
				}
				
			}else{
				$aCmCartErrors = 'Prestashop new ProductID error'; 
			}
			unset($obProduct);
		}
		
		if($ProdID>0){
			//Update Qnt Product [Attribute]
			StockAvailable::setQuantity($ProdID, $AttID, 99); //intval($aCmAddCart['Available_num'])
			
			//Cart
			$obCart = new Cart($id_cart);
			$obCart->id_lang = $id_lang;
			$obCart->id_currency = $id_currency;
			if($obCart->updateQty($aCmAddCart['Quantity'], $ProdID, $AttID)){
				NtAdd_x('Added to cart');
				//Header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); die();
			}else{
				$aCmCartErrors = 'AddCart Error: Qnt:'.$aCmAddCart['Quantity'].', ProdID:'.$ProdID.',  AttID:'.$AttID.', CartID:'.$id_cart.', LangID:'.$id_lang.', CurID:'.$id_currency.' '; 
			}
		}else{
			$aCmCartErrors = 'AddCart Error: ProdID:'.$ProdID.' '; 
		}
	}
	
	
}

$controller->initContent();
$controller->setMedia();

if(defined('TITLE_x')){
	$context->smarty->tpl_vars['page']->value['title'] = TITLE_x;
	$context->smarty->tpl_vars['page']->value['meta'] = Array(
		'title' => TITLE_x,
		'description' => DESCRIPTION_x,
		'keywords' => KEYWORDS_x,
		'robots' => ''
	);
}

AxajAddCartDOM(); //Show only Cart div if AddCart action was run

$controller->setTemplate('car-mod.com.header.tpl'); 
$controller->display();


/*
?>
	<table><tr><td>
	<?require_once($_SERVER["DOCUMENT_ROOT"].'/autoparts/addons/regnum/component.php');?>
	</td><td>
	<?require_once($_SERVER["DOCUMENT_ROOT"].'/autoparts/addons/mpopup/component.php');?>
	</td><td>
	<?$arKTParams=Array("MODULE_ROOT_DIR"=>"autoparts");
	require_once($_SERVER["DOCUMENT_ROOT"].'/autoparts/addons/vin/component.php');?>
	</table>
<?
*/

/* ?><div style="max-width:1100px; margin:0px auto;"><? */
echo $CarMod_Content;
/* ?></div><? */

$controller->setTemplate('car-mod.com.footer.tpl'); 
$controller->display();

AxajAddCartDOM();

//Stop Presta from website root /index.php
if(defined('CM_INDEX_INCLUDED')){die();}
?>