<?VerifyAccess_x('WordPress');

//Lock WP chache folder
/* if(IsADMIN_x AND $_GET['LockWpCache']){
	//$_GET['LockWpCache'] = /page_enhanced/www.hibdeta.lt/carparts/
	$CacheFolder = $_GET['LockWpCache'];
	$CacheRoot = '/wp-content/cache'.$CacheFolder;
	$CachePath = $_SERVER['DOCUMENT_ROOT'].$CacheRoot;
	if(file_exists($CachePath)){
		if(CmDelTree($CachePath)){
			if(mkdir($CachePath, octdec("0500"), true)){
				echo 'Done!';
			}else{echo 'Error create folder [0500]: '.$CacheRoot;}
		}else{echo 'Error deleting: '.$CacheRoot;}
	}else{echo 'No folder: '.$CacheRoot;}
	die();
}
function CmDelTree($dir){
	$files = array_diff(scandir($dir), array('.','..')); 
	foreach ($files as $file){(is_dir("$dir/$file")) ? CmDelTree("$dir/$file") : unlink("$dir/$file");}
	return rmdir($dir); 
} */

//Header
require($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

// https://codex.wordpress.org/Roles_and_Capabilities
$WPUser = wp_get_current_user(); //echo '<br><pre>';print_r($WPUser);echo '</pre>';
if($WPUser->exists() AND is_array($CPMod->aUserGroups)){
	$ID = $WPUser->ID;
	$aCaps = $WPUser->caps;
	$aRoles = $WPUser->roles;
	foreach($CPMod->aUserGroups as $GpID=>$aGp){
		//by WP user ID
		if($aGp['CMS_UID']=='UserID_'.$ID AND $_SESSION['CM_USER_GROUP']!=$GpID){
			$_SESSION['CM_USER_GROUP'] = $GpID; 
			Redirect_x();
		}
		//by WP user Capabilities
		if(is_array($aCaps)){
			foreach($aCaps as $CapName=>$CapID){
				if($aGp['CMS_UID']=='CapsName_'.$CapName AND $_SESSION['CM_USER_GROUP']!=$GpID){
					$_SESSION['CM_USER_GROUP'] = $GpID; 
					Redirect_x();
				}
			}
		}
		//by WP user Roles
		if(is_array($aRoles)){
			foreach($aRoles as $RoleID=>$RoleName){
				if($aGp['CMS_UID']=='RoleName_'.$RoleName AND $_SESSION['CM_USER_GROUP']!=$GpID){
					$_SESSION['CM_USER_GROUP'] = $GpID; 
					Redirect_x();
				}
			}
		}
	}
}else{
	//Header('Location: /index.php/my-account/'); die();
}


global $woocommerce;

//Add to cart
if(defined('CM_ADD_TO_CART')){
	global $aCmCartErrors;
	$CartCategory = intval($CPMod->arSettings["CMS_DEFCATID"]);
	global $aCmAddCart;
	global $wpdb;
	$PostName = 'id_'.$aCmAddCart['PriceNum']; // название не должно быть числом
	//Find
	//$obWPPost = get_page_by_path($aCmAddCart['PriceNum'],'OBJECT','product');
	$obWPPost = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = 'product' AND post_status = 'publish' AND post_name = %s", $PostName) );
	if($obWPPost){
		$post_id = (int)$obWPPost->ID;
	}else{
		//Create POST
		$post_id = wp_insert_post(array(
			'post_author' => 1,
			'post_content' => $aCmAddCart['Name'],
			'post_status' => "publish",
			'post_title' => $aCmAddCart['Brand'].' '.$aCmAddCart['ArtNum'].'<br>'.$aCmAddCart['Name'],
			'post_name' => $PostName,
			'post_parent' => 0,
			//'post_status' => 'private',
			'comment_status' => 'open',
			'ping_status' => 'closed',
			'post_excerpt' => $aCmAddCart['Name'],
			//'guid' => 'http://'.$_SERVER['SERVER_NAME'].$aCmAddCart['URL'],
			'post_type' => "product",
		),$wp_error = true);
		if($post_id && ! is_wp_error($post_id)){
			$obWPPost = get_post($post_id);
			//Image
			if($aCmAddCart['Image']!=''){
				require_once(ABSPATH . 'wp-admin/includes/media.php');
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				require_once(ABSPATH . 'wp-admin/includes/image.php');
				//Fix SSL check
				function DisableSSL($args){return false;} 
				add_filter('https_ssl_verify', 'DisableSSL', 10, 1);
				add_filter('https_local_ssl_verify', 'DisableSSL', 11, 1);
				$RemoteImageSrc = str_replace(PROTOCOL_DOMAIN_x.'/carparts','http://car-mod.com',$aCmAddCart['Image']);
				$filename = media_sideload_image($RemoteImageSrc, $post_id, $aCmAddCart['Name'].' ('.$aCmAddCart['Brand'].' - '.$aCmAddCart['ArtNum'].')', 'src');
				$filename = str_replace(PROTOCOL_DOMAIN_x,$_SERVER['DOCUMENT_ROOT'],$filename);
				if(is_wp_error($filename)){
					$aCmCartErrors[] = 'Warning: cart product image failed to loading from:<br><a href="'.$aCmAddCart['Image'].'" target="_blank">'.$aCmAddCart['Image'].'</a><br>media_sideload_image() at /'.CM_DIR.'/WordPress.WC.php<br><b>'.$filename->get_error_message().'</b><br>/wp-admin/includes/media.php';
				}else{
					$wp_filetype = wp_check_filetype(basename($filename), null );
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
						'post_content' => '',
						'post_status' => 'inherit'
					);
					$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id,  $attach_data );
					add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
				}
			}
			//Category
			if(intval($CartCategory)>0){
				wp_set_object_terms( $post_id, $CartCategory, 'product_cat' );
			}
			wp_set_object_terms($post_id, 'simple', 'product_type');
			//Meta data
			update_post_meta( $post_id, '_visibility', 'visible' ); //hidden
			update_post_meta( $post_id, 'total_sales', '0');
			update_post_meta( $post_id, '_downloadable', 'no');
			update_post_meta( $post_id, '_virtual', 'no');
			update_post_meta( $post_id, '_sale_price', "" );
			update_post_meta( $post_id, '_purchase_note', "" );
			update_post_meta( $post_id, '_featured', "no" );
			if( $aCmAddCart['Options']['Weight_kg'] ){
				$wp_weight = ($aCmAddCart['Options']['Weight_kg']['Text']/1000);
				update_post_meta( $post_id, '_weight', $wp_weight );
			}
			if( $aCmAddCart['Options']['Weight_gr'] ){
				$wp_weight = ($aCmAddCart['Options']['Weight_gr']['Text']);
				update_post_meta( $post_id, '_weight', $wp_weight );
			}
			update_post_meta( $post_id, '_length', "" );
			update_post_meta( $post_id, '_width', "" );
			update_post_meta( $post_id, '_height', "" );
			update_post_meta( $post_id, '_sku', $aCmAddCart['URL'] ); //$aCmAddCart['Brand'].' '.$aCmAddCart['ArtNum']
			//Attributes
			update_post_meta( $post_id, '_product_attributes', array(
				0 => array ('name'=>'Supplier', 'value'=>$aCmAddCart['Supplier_stock'], 'position'=>1, 'is_visible'=>0, 'is_variation'=>1, 'is_taxonomy'=>0),
				1 => array ('name'=>'Delivery', 'value'=>$aCmAddCart['Delivery_view'], 'position'=>2, 'is_visible'=>1, 'is_variation'=>1, 'is_taxonomy'=>0),
				2 => array ('name'=>'Availability', 'value'=>$aCmAddCart['Available_view'], 'position'=>3, 'is_visible'=>1, 'is_variation'=>1, 'is_taxonomy'=>0),
				4 => array ('name'=>'SrcPrice', 'value'=>$aCmAddCart['Source'], 'position'=>4, 'is_visible'=>0, 'is_variation'=>1, 'is_taxonomy'=>0),
			));
			update_post_meta( $post_id, '_sale_price_dates_from', "" );
			update_post_meta( $post_id, '_sale_price_dates_to', "" );
			
			update_post_meta( $post_id, '_sold_individually', "" );
			update_post_meta( $post_id, '_backorders', "no" );
			if($aCmAddCart['Available_num']>0){$WP_manage_stock = 'yes';}else{$WP_manage_stock = 'no';}
			update_post_meta( $post_id, '_manage_stock', $WP_manage_stock );
			update_post_meta( $post_id, '_stock_status', 'instock');
			update_post_meta( $post_id, '_stock', $aCmAddCart['Available_num'] );
		}
	}
	
	//Add to cart
	if($post_id>0){
		update_post_meta( $post_id, '_price', $aCmAddCart['Price']);
		update_post_meta( $post_id, '_regular_price', $aCmAddCart['Price']);
		
		/*if(!is_user_logged_in()){ // куки дл€ неавторизованных 		woocommerce_cart_hash 		woocommerce_items_in_cart 		wp_woocommerce_session
			// Hook after add to cart
			add_action( 'woocommerce_add_to_cart' , function(){
				if( WC()->cart->is_empty() ) return;
				wc_setcookie( 'woocommerce_items_in_cart', 1 );
				wc_setcookie( 'woocommerce_cart_hash', md5( json_encode( WC()->cart->get_cart_for_session() ) ) ); //wc_setcookie( 'woocommerce_cart_hash', md5( json_encode( WC()->cart->get_cart() ) ) );
				do_action( 'woocommerce_set_cart_cookies', true );
			});
		}*/
		//if(!is_user_logged_in()) WC()->cart->set_cart_cookies( true );
		//WC()->cart->maybe_set_cart_cookies();
		
		//if($_COOKIE['woocommerce_items_in_cart']==''){setcookie("woocommerce_items_in_cart",'1',time()+60*60*24*30,'/');}
		//if($_COOKIE['woocommerce_cart_hash']==''){setcookie("woocommerce_cart_hash", md5(json_encode(WC()->cart->get_cart_for_session())), time()+60*60*24*30, '/');}
		
		do_action( 'woocommerce_set_cart_cookies', TRUE );
		//WC()->cart->set_cart_cookies(true);
		$WooCID = WC()->cart->add_to_cart( $post_id, $aCmAddCart['Quantity']);
		
		
		$GLOBALS['tdm_add_to_cart__notices'] = '';
		if( $notices = wc_get_notices() ){
			foreach( $notices as $note ){
				$GLOBALS['tdm_add_to_cart__notices'] .= '<div style="margin:1em 0; padding:1em; background:#eee;">«аметка по товару: <a href="'. esc_url($obWPPost->guid) .'">'. esc_html( $obWPPost->post_type ) .'</a>: '. $note[0] .'</div>';
			}
		}
	}
	
}

if(!defined('CM_INDEX_INCLUDED')){
	AxajAddCartDOM(); //Show only Cart div if AddCart action was run
	get_header();
}

if(!defined('CM_ADD_TO_CART')){
	
	?><div style="width:100%; grid-area:breadcrumbs;"><?
	echo $CarMod_Content;
	?></div><?
}

if(!defined('CM_INDEX_INCLUDED')){
	//Footer
	get_footer();
	AxajAddCartDOM();
}
?>