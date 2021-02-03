<?php
/**
 * 2007-2019 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2019 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
	exit;
include_once(_PS_MODULE_DIR_.'ets_superspeed/classes/cache.php');
include_once(_PS_MODULE_DIR_.'ets_superspeed/classes/http_build_url.php');
include_once(_PS_MODULE_DIR_.'ets_superspeed/ets_superspeed_defines.php');
include_once(_PS_MODULE_DIR_.'ets_superspeed/classes/ets_superspeed_cache_page.php');
include_once(_PS_MODULE_DIR_.'ets_superspeed/classes/ets_superspeed_paggination_class.php');
if(!function_exists('ets_execute_php'))
    include_once(_PS_MODULE_DIR_.'ets_superspeed/classes/ext/temp');
if (!defined('_ETS_SPEED_CACHE_DIR_')) 
    define('_ETS_SPEED_CACHE_DIR_',_PS_CACHE_DIR_.'ss_pagecache/');
if(!defined('_ETS_SPEED_CACHE_DIR_IMAGES'))
     define('_ETS_SPEED_CACHE_DIR_IMAGES',_PS_IMG_DIR_.'ss_imagesoptimize/');
class Ets_superspeed extends Module
{
    public $_class_ss_cache;
    public $_resmush=0;
    public $_google =0;
    public $_tynypng = 0; 
    public $is17 = false;
    public $is16 = false;
    public $isblog = false;
    public $isSlide = false;
    public $isBanner = false;
    public $_errors = array();
    public $number_optimize=1;
    public function __construct()
    {
        $this->name = 'ets_superspeed';
    	$this->tab = 'front_office_features';
    	$this->version = '1.0.9';
    	$this->author = 'ETS-Soft';
        $this->module_key = 'e1e4b552d9ac605082095fcb451f5bac';
    	$this->need_instance = 0;
    	$this->secure_key = Tools::encrypt($this->name);
    	$this->bootstrap = true;
        if(version_compare(_PS_VERSION_, '1.7', '>='))
            $this->is17 = true; 
        if(version_compare(_PS_VERSION_, '1.7', '<'))
            $this->is16 = true;
        if(Module::isInstalled('ybc_blog') && Module::isEnabled('ybc_blog'))
            $this->isblog = true;
        if((Module::isInstalled('ps_imageslider') && Module::isEnabled('ps_imageslider')) ||  (Module::isInstalled('homeslider') && Module::isEnabled('homeslider')))
            $this->isSlide = true;
        if((Module::isInstalled('blockbanner') && Module::isEnabled('blockbanner')) ||  (Module::isInstalled('ps_banner') && Module::isEnabled('ps_banner')))
            $this->isBanner = true;
    	parent::__construct();
        $this->ps_versions_compliancy = array('min' => '1.6.0.0', 'max' => _PS_VERSION_);
    	$this->displayName = $this->l('Super Speed');
        $this->description = $this->l('All-in-one speed optimization tool for Prestashop. Everything you need to maximize your website\'s speed, minimize page loading time, utilize server resource and save bandwidth');
        $this->shortlink = 'https://mf.short-link.org/';
        if(Tools::getValue('configure')==$this->name && Tools::isSubmit('othermodules'))
        {
            $this->displayRecommendedModules();
        }
        if(!$this->active)
        {
            $this->context->smarty->assign(
                array(
                    'ets_superspeed_disabled' => 1,
                )
            );
        }
        if(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT')=='google' || Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT')=='php')
            $this->number_optimize=5;
    }
    public function getImageTypes($type='',$string=false)
    {
        $sql = 'SELECT name as value,name as label FROM '._DB_PREFIX_.'image_type '.($type ? ' WHERE '.pSQL($type).'=1' :'' );
        $image_types = Db::getInstance()->executeS($sql);
        if($string)
        {
            $images='';
            foreach($image_types as $image_type)
            {
                $images .=','.$image_type['value'];
            }
            return trim($images,',');
        }
        else
            return $image_types;
    }
    public function install()
    {
        if(Module::isInstalled('ets_pagecache') || Module::isInstalled('ets_imagecompressor'))
            return false;
        return  parent::install() && $this->_installDb() && $this->_installTab() && $this->_registerHook() && $this->_installDbDefault();
    }
    public function _installDb()
    {
        $rs=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_cache_page` (
          `id_cache_page` int(11) NOT NULL AUTO_INCREMENT,
          `page` varchar(222) NOT NULL,
          `id_object` int(11) NOT NULL,
          `id_product_attribute` INT(11) NOT NULL,
          `ip` varchar(222) NOT NULL,
          `file_cache` text NOT NULL,
          `request_uri` text NOT NULL,
          `id_shop` int(11) NOT NULL,
          `has_customer` int(1) NOT NULL,
          `has_cart` int(1) NOT NULL,
          `click` int(11) NOT NULL,
          `file_size` FLOAT(10,2),
          `user_agent` text,
          `date_add` datetime NOT NULL,
          `date_upd` datetime NOT NULL,
          PRIMARY KEY (`id_cache_page`)) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_cache_page_hook` (
              `id_cache_page` int(11) NOT NULL,
              `hook_name` varchar(64) NOT NULL,
              PRIMARY KEY( `id_cache_page`, `hook_name`)
            ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_category_image` (
          `id_category` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_category`, `type_image`)
          ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_dynamic` (
              `id_module` int(11) NOT NULL,
              `hook_name` varchar(64) NOT NULL,
              `empty_content` int(1) DEFAULT NULL,
              PRIMARY KEY( `id_module`, `hook_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_manufacturer_image` (
          `id_manufacturer` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_manufacturer`, `type_image`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_product_image` (
          `id_image` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_image`, `type_image`)
        ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_time` (
          `id_shop` int(11) NOT NULL,
          `date` datetime NOT NULL,
          `time` float(10,2) NOT NULL
        ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_supplier_image` (
          `id_supplier` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_supplier`, `type_image`)
        ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_blog_post_image` (
          `id_post` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_post`, `type_image`)
        ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_blog_category_image` (
          `id_category` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_category`, `type_image`)
        ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_blog_gallery_image` (
          `id_gallery` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_gallery`, `type_image`)
        ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_blog_slide_image` (
          `id_slide` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_slide`, `type_image`)
        ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_home_slide_image` (
          `id_homeslider_slides` int(11) NOT NULL,
          `image` varchar(165) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_homeslider_slides`, `type_image`,`image`)
        ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_others_image`(
          `image` varchar(165) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY(`type_image`,`image`)
        ) ENGINE=InnoDB');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS  `'._DB_PREFIX_.'ets_superspeed_hook_time` ( 
        `id_module` INT(11) NOT NULL , 
        `hook_name` VARCHAR(111) NOT NULL , 
        `page` VARCHAR(1000) NOT NULL , 
        `id_shop` INT(11) NOT NULL,
        `date_add` datetime NOT NULL , 
        `time` float(10,4) NOT NULL , 
        PRIMARY KEY (`id_module`, `hook_name`,`id_shop`)) ENGINE = InnoDB'); 
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_hook_module` ( 
        `id_module` INT(11) NOT NULL , 
        `id_shop` INT(11) NOT NULL , 
        `id_hook` INT(11) NOT NULL , 
        `position` INT(2) NOT NULL , 
        PRIMARY KEY (`id_module`, `id_shop`, `id_hook`)) ENGINE = InnoDB');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_upload_image` (
          `id_ets_superspeed_upload_image` int(11) NOT NULL AUTO_INCREMENT,
          `image_name` varchar(222) NOT NULL,
          `old_size` float(10,2) NOT NULL,
          `new_size` float(10,2) NOT NULL,
          `image_name_new` varchar(222) NOT NULL,
          `date_add` datetime NOT NULL,
           PRIMARY KEY (`id_ets_superspeed_upload_image`)) ENGINE=InnoDB');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_browse_image` (
          `id_ets_superspeed_browse_image` int(11) NOT NULL AUTO_INCREMENT,
          `image_name` varchar(222) NOT NULL,
          `image_dir` text,
          `image_id` text,
          `old_size` float(10,2) NOT NULL,
          `new_size` float(10,2) NOT NULL,
          `date_add` datetime NOT NULL,
           PRIMARY KEY (`id_ets_superspeed_browse_image`)) ENGINE=InnoDB');
        return $rs;
    } 
    public function _registerHook()
    {
        $defines = new Ets_superspeed_defines();
        foreach($defines->_hooks as $hook)
            $this->registerHook($hook);
        return true;
    }
    public function _installTab()
    {
        $languages = Language::getLanguages(false);
        $tab = new Tab();
        $tab->class_name = 'AdminSuperSpeed';
        $tab->module = $this->name;
        $tab->id_parent = 0;            
        foreach($languages as $lang){
            $tab->name[$lang['id_lang']] = $this->l('Speed Optimization');
        }
        $tab->save();
        $tabId = Tab::getIdFromClassName('AdminSuperSpeed');
        $defines = new Ets_superspeed_defines();
        if($tabId)
        {
            foreach($defines->_admin_tabs as $tabArg)
            {
                if($tabArg['class_name']!='othermodules')
                {
                    $tab = new Tab();
                    $tab->class_name = $tabArg['class_name'];
                    $tab->module = $this->name;
                    $tab->id_parent = $tabId;
                    $tab->icon=$tabArg['icon'];
                    foreach($languages as $lang){
                            $tab->name[$lang['id_lang']] = $tabArg['tab_name'];
                    }
                    $tab->save();
                }
            }                
        }            
        return true;
    }
    public function _installDbDefault()
    {
        if(!is_dir(_ETS_SPEED_CACHE_DIR_))
            @mkdir(_ETS_SPEED_CACHE_DIR_,0777,true);            
        if(file_exists(dirname(__FILE__).'/views/js/script_custom.js'))
            @unlink(dirname(__FILE__).'/views/js/script_custom.js');
        Configuration::updateValue('PS_TOKEN_ENABLE',0);
        $hookHeaderId = Hook::getIdByName('Header');
        $this->updatePosition($hookHeaderId, 0, 1);
        $defines= new Ets_superspeed_defines();
        if($defines->_config_gzip)
        {
            foreach($defines->_config_gzip as $config_zip)
            {
                if(isset($config_zip['default']))
                    Configuration::updateValue($config_zip['name'],$config_zip['default']);
            }
            foreach($defines->_config_images as $config_image)
            {
                if(isset($config_image['default']))
                    Configuration::updateValue($config_image['name'],$config_image['default']);
            }
        }
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_INDEX',5);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_CATEGORY',5);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_PRODUCT',15);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_CMS',15);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_NEWPRODUCTS',7);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_BESTSALES',7);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_SUPPLIER',7);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_MANUFACTURER',7);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_CONTACT',30);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_PRICESDROP',7);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_SITEMAP',7);
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_BLOG',7);
        Configuration::updateValue('ETS_SPEED_USE_DEFAULT_CACHE',1);
        Configuration::updateValue('ETS_SPEED_PAGES_EXCEPTION',"refs=\naffp=");
        Configuration::updateGlobalValue('ETS_SPEED_SUPER_TOCKEN',$this->genSecure(6));
        Configuration::updateValue('ETS_RECORD_PAGE_CLICK',1);
        Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD',50);
        Configuration::updateValue('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD','php');
        Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE',50);
        Configuration::updateValue('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE','php');
        Configuration::updateValue('ETS_SPEED_ENABLE_LAYZY_LOAD',0);
        Configuration::updateValue('ETS_SPEED_LAZY_FOR','product_list,home_slide,home_banner,home_themeconfig');
        //$this->replaceTemplateProductDefault();
        return true;
    }  
    public function uninstall()
    {
        return parent::uninstall() && $this->_uninstallTab()  && $this->_uninstallHook() && $this->_uninstallDb();
    }
    public function _uninstallDb()
    {
        if(!$this->_class_ss_cache)
            $this->getClassCahe();
        if(file_exists(dirname(__FILE__).'/views/js/script_custom.js'))
            @unlink(dirname(__FILE__).'/views/js/script_custom.js');
        $this->_class_ss_cache->deleteCache();
        $this->clearLogInstall();
        $res = Db::getInstance()->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'ets_superspeed_cache_page');
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_cache_page_hook");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_dynamic");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_category_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_manufacturer_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_product_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_supplier_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_time");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_hook_module");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_hook_time");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_blog_post_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_blog_category_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_blog_gallery_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_blog_slide_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_home_slide_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_others_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_browse_image");
        $res &=Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."ets_superspeed_upload_image");
        $defines = new Ets_superspeed_defines();
        foreach($defines->_config_gzip as $gzip)
        {
            Configuration::deleteByName($gzip['name']);
        }
        foreach($defines->_config_images as $image)
        {
            Configuration::deleteByName($image['name']);
        }
        Configuration::deleteByName('ETS_SPEED_ENABLE_PAGE_CACHE');
        Configuration::deleteByName('ETS_SPEED_COMPRESS_CACHE_FIIE');
        Configuration::deleteByName('ETS_SPEED_AUTO_CACHE');
        Configuration::deleteByName('ETS_SPEED_CACHE_TIME');
        Configuration::deleteByName('ETS_SPEED_PAGES_TO_CACHE');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_INDEX');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_CATEGORY');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_PRODUCT');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_CMS');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_NEWPRODUCTS');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_BESTSALES');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_SUPPLIER');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_MANUFACTURER');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_CONTACT');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_PRICESDROP');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_SITEMAP');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_BLOG');
        Configuration::updateValue('ETS_SPEED_ENABLE_LAYZY_LOAD',0);
        $this->replaceTemplateProductDefault(false);
        return true;
    }
    public function getClassCahe()
    {
        $this->_class_ss_cache= new Ets_ss_class_cache();
    }
    public function _uninstallTab()
    {
        $defines = new Ets_superspeed_defines();
        foreach($defines->_admin_tabs as $tab)
        {
            if(($tabId = Tab::getIdFromClassName($tab['class_name'])) && $tabId!='othermodules')
            {
                $tab = new Tab($tabId);
                if($tab)
                    $tab->delete();
            }                
        }
        if($tabId = Tab::getIdFromClassName('AdminSuperSpeed'))
        {
            $tab = new Tab($tabId);
            if($tab)
                $tab->delete();
        }
        return true;
    }
    public function _uninstallHook()
    {
        $defines = new Ets_superspeed_defines();
        foreach($defines->_hooks as $hook)
        {
            $this->unRegisterHook($hook);
        }
        return true;
    }
    
    public function hookHeader()
    { 
        if(Configuration::get('ETS_SPEED_ENABLE_LAYZY_LOAD'))
        {
            $this->context->smarty->assign(
                array(
                    'ETS_SPEED_ENABLE_LAYZY_LOAD' => true,
                    'ets_link_base' => $this->getBaseLink(),
                    'ETS_SPEED_LOADING_IMAGE_TYPE' => Configuration::get('ETS_SPEED_LOADING_IMAGE_TYPE'),
                )
            );
            $this->context->controller->addJS(($this->_path).'/views/js/ets_lazysizes.js');
        }
        $this->context->controller->addJS(($this->_path).'/views/js/ets_superspeed.js');
        $this->context->controller->addCSS(($this->_path).'/views/css/ets_superspeed.css');
        $this->context->smarty->assign(
            array(
                'sp_link_base' => $this->getBaseLink(),
                'sp_custom_js' => file_exists(dirname(__FILE__).'/views/js/script_custom.js') ? 1 :0
            )
        );
        return $this->display(__FILE__,'javascript.tpl');
    }
    public function displayRecommendedModules()
    {
        $cacheDir = dirname(__file__) . '/../../cache/'.$this->name.'/';
        $cacheFile = $cacheDir.'module-list.xml';
        $cacheLifeTime = 24;
        $cacheTime = (int)Configuration::getGlobalValue('ETS_MOD_CACHE_'.$this->name);
        $profileLinks = array(
            'en' => 'https://addons.prestashop.com/en/207_ets-soft',
            'fr' => 'https://addons.prestashop.com/fr/207_ets-soft',
            'it' => 'https://addons.prestashop.com/it/207_ets-soft',
            'es' => 'https://addons.prestashop.com/es/207_ets-soft',
        );
        if(!is_dir($cacheDir))
        {
            @mkdir($cacheDir, 0755,true);
            if ( @file_exists(dirname(__file__).'/index.php')){
                @copy(dirname(__file__).'/index.php', $cacheDir.'index.php');
            }
        }
        if(!file_exists($cacheFile) || !$cacheTime || time()-$cacheTime > $cacheLifeTime * 60 * 60)
        {
            if(file_exists($cacheFile))
                @unlink($cacheFile);
            if($xml = self::file_get_contents($this->shortlink.'ml.xml'))
            {
                $xmlData = @simplexml_load_string($xml);
                if($xmlData && (!isset($xmlData->enable_cache) || (int)$xmlData->enable_cache))
                {
                    @file_put_contents($cacheFile,$xml);
                    Configuration::updateGlobalValue('ETS_MOD_CACHE_'.$this->name,time());
                }
            }
        }
        else
            $xml = Tools::file_get_contents($cacheFile);
        $modules = array();
        $categories = array();
        $categories[] = array('id'=>0,'title' => $this->l('All categories'));
        $enabled = true;
        $iso = Tools::strtolower($this->context->language->iso_code);
        $moduleName = $this->displayName;
        $contactUrl = '';
        if($xml && ($xmlData = @simplexml_load_string($xml)))
        {
            if(isset($xmlData->modules->item) && $xmlData->modules->item)
            {
                foreach($xmlData->modules->item as $arg)
                {
                    if($arg)
                    {
                        if(isset($arg->module_id) && (string)$arg->module_id==$this->name && isset($arg->{'title'.($iso=='en' ? '' : '_'.$iso)}) && (string)$arg->{'title'.($iso=='en' ? '' : '_'.$iso)})
                            $moduleName = (string)$arg->{'title'.($iso=='en' ? '' : '_'.$iso)};
                        if(isset($arg->module_id) && (string)$arg->module_id==$this->name && isset($arg->contact_url) && (string)$arg->contact_url)
                            $contactUrl = $iso!='en' ? str_replace('/en/','/'.$iso.'/',(string)$arg->contact_url) : (string)$arg->contact_url;
                        $temp = array();
                        foreach($arg as $key=>$val)
                        {
                            if($key=='price' || $key=='download')
                                $temp[$key] = (int)$val;
                            elseif($key=='rating')
                            {
                                $rating = (float)$val;
                                if($rating > 0)
                                {
                                    $ratingInt = (int)$rating;
                                    $ratingDec = $rating-$ratingInt;
                                    $startClass = $ratingDec >= 0.5 ? ceil($rating) : ($ratingDec > 0 ? $ratingInt.'5' : $ratingInt);
                                    $temp['ratingClass'] = 'mod-start-'.$startClass;
                                }
                                else
                                    $temp['ratingClass'] = '';
                            }
                            elseif($key=='rating_count')
                                $temp[$key] = (int)$val;
                            else
                                $temp[$key] = (string)strip_tags($val);
                        }
                        if($iso)
                        {
                            if(isset($temp['link_'.$iso]) && isset($temp['link_'.$iso]))
                                $temp['link'] = $temp['link_'.$iso];
                            if(isset($temp['title_'.$iso]) && isset($temp['title_'.$iso]))
                                $temp['title'] = $temp['title_'.$iso];
                            if(isset($temp['desc_'.$iso]) && isset($temp['desc_'.$iso]))
                                $temp['desc'] = $temp['desc_'.$iso];
                        }
                        $modules[] = $temp;
                    }
                }
            }
            if(isset($xmlData->categories->item) && $xmlData->categories->item)
            {
                foreach($xmlData->categories->item as $arg)
                {
                    if($arg)
                    {
                        $temp = array();
                        foreach($arg as $key=>$val)
                        {
                            $temp[$key] = (string)strip_tags($val);
                        }
                        if(isset($temp['title_'.$iso]) && $temp['title_'.$iso])
                                $temp['title'] = $temp['title_'.$iso];
                        $categories[] = $temp;
                    }
                }
            }
        }
        if(isset($xmlData->{'intro_'.$iso}))
            $intro = $xmlData->{'intro_'.$iso};
        else
            $intro = isset($xmlData->intro_en) ? $xmlData->intro_en : false;
        $this->smarty->assign(array(
            'modules' => $modules,
            'enabled' => $enabled,
            'module_name' => $moduleName,
            'categories' => $categories,
            'img_dir' => $this->_path . 'views/img/',
            'intro' => $intro,
            'shortlink' => $this->shortlink,
            'ets_profile_url' => isset($profileLinks[$iso]) ? $profileLinks[$iso] : $profileLinks['en'],
            'trans' => array(
                'txt_must_have' => $this->l('Must-Have'),
                'txt_downloads' => $this->l('Downloads!'),
                'txt_view_all' => $this->l('View all our modules'),
                'txt_fav' => $this->l('Prestashop\'s favourite'),
                'txt_elected' => $this->l('Elected by merchants'),
                'txt_superhero' => $this->l('Superhero Seller'),
                'txt_partner' => $this->l('Module Partner Creator'),
                'txt_contact' => $this->l('Contact us'),
                'txt_close' => $this->l('Close'),
            ),
            'contactUrl' => $contactUrl,
         ));
         echo $this->display(__FILE__, 'module-list.tpl');
         die;
    }
    public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 60)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = stream_context_create(array(
                "http" => array(
                    "timeout" => $curl_timeout,
                    "max_redirects" => 101,
                    "header" => 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
                ),
                "ssl"=>array(
                    "allow_self_signed"=>true,
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            ));
        }
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => html_entity_decode($url),
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => $curl_timeout,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ));
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        } elseif (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
            return Tools::file_get_contents($url, $use_include_path, $stream_context);
        } else {
            return false;
        }
    }
    public function hookActionAdminPerformanceControllerSaveAfter()
    {
        if(!$this->_class_ss_cache)
            $this->getClassCahe();
        $this->_class_ss_cache->deleteCache();
    }
    public function hookActionHtaccessCreate()
    {
        Configuration::updateValue('ETS_SPEED_USE_DEFAULT_CACHE',1);
    }
    public function hookActionWatermark($params)
    {
        $id_image=$params['id_image'];
        if($id_image && Configuration::get('ETS_SPEED_OPTIMIZE_NEW_IMAGE') && $type_product=Configuration::get('ETS_SPEED_OPTIMIZE_NEW_IMAGE_PRODUCT_TYPE'))
        {
            $quality=(int)Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_NEW') > 0 ? (int)Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_NEW') : 90;
            $new_image= new Image($id_image);
            $path = $new_image->getPathForCreation();
            $types= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'image_type WHERE products=1 AND  name IN ("'.implode('","',array_map('pSQL',explode(',',$type_product))).'")');
            if($types)
            {
                foreach($types as $type)
                {
                    if(Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')))
                        $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE id_image = '.(int)$id_image.' AND type_image="'.pSQL($type['name']).'" AND quality!=100';
                    else
                        $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE id_image ="'.(int)$id_image.'" AND type_image ="'.pSQL($type['name']).'"'.(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') !='tynypng' || $quality==100 || !Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) ? ' AND quality="'.(int)$quality.'"':' AND quality!=100').' AND optimize_type = "'.pSQL(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT')).'"';
                    if(!Db::getInstance()->getRow($sql))
                    {
                        $optimizied= (int)Db::getInstance()->getValue('SELECT id_image FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE id_image ="'.(int)$id_image.'" AND type_image ="'.pSQL($type['name']).'"');
                        if($size_old= $this->createImage($path,$type,$optimizied))
                        {
                            if($this->checkOptimizeImageResmush())
                            {
                                $product_class = new Product($new_image->id_product,false, $this->context->language->id);
                                $url_image= $this->context->link->getImageLink($product_class->link_rewrite,$new_image->id,$type['name']);
                            }
                            else
                                $url_image=null;
                            $quality_old = Db::getInstance()->getValue('SELECT quality FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE id_image ="'.(int)$id_image.'" AND type_image ="'.pSQL($type['name']).'"');
                            $compress = $this->compress($path,$type,$quality,$url_image,$quality_old);
                            while($compress===false)
                            {
                                $compress = $this->compress($path,$type,$quality,$url_image,$quality_old);
                            }
                            if(!$optimizied)
                                Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_product_image (id_image,type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$id_image.'","'.pSQL($type['name']).'","'.(int)$quality.'","'.(float)$size_old.'","'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'","'.pSQL($compress['optimize_type']).'")');
                            else
                                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_product_image SET quality ="'.(int)$quality.'",size_new="'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'",size_old="'.(float)$size_old.'",optimize_type="'.pSQL($compress['optimize_type']).'" WHERE id_image ="'.(int)$id_image.'" AND type_image ="'.pSQL($type['name']).'"');
                        }
                    }
                }
            }
        }
    }
    public function hookDisplayBackOfficeHeader()
    {   
        if(Tools::isSubmit('check_mod_deflate'))
        {
            die(
                Tools::jsonEncode(
                    array(
                        'mod_deflate' => $this->check_mod_cache(false),
                    )
                )
            );
        }
        if(Tools::isSubmit('check_mod_expires'))
        {
            die(
                Tools::jsonEncode(
                    array(
                        'mod_expires' => $this->check_mod_cache(true)
                    )
                )
            );
        }
        $controller= Tools::getValue('controller');
        $controllers=array('AdminSuperSpeedStatistics','AdminSuperSpeed','AdminSuperSpeedDatabase','AdminSuperSpeedDiagnostics','AdminSuperSpeedGeneral','AdminSuperSpeedGzip','AdminSuperSpeedImage','AdminSuperSpeedMinization','AdminSuperSpeedPageCaches','AdminSuperSpeedStatistics','AdminSuperSpeedHelps','AdminSuperSpeedSystemAnalytics');
        $this->context->controller->addCSS($this->_path.'views/css/all_admin.css');
        $this->context->controller->addCSS($this->_path.'views/css/font-awesome.css');
        if(in_array($controller,$controllers))
        {
            $this->context->controller->addJquery();
            $this->context->controller->addCSS($this->_path.'views/css/admin.css');
            if(version_compare(_PS_VERSION_, '1.7', '<'))
                $this->context->controller->addCSS($this->_path.'views/css/admin16.css');
            $this->context->controller->addCSS($this->_path.'views/css/other.css');
            $this->context->controller->addJS($this->_path.'views/js/other.js');
        }
        if($controller=='AdminSuperSpeedStatistics')
        {
            $this->context->controller->addJqueryPlugin('flot');
            $this->context->controller->addJS($this->_path.'views/js/gauge.js');
            $this->context->controller->addJS($this->_path.'views/js/speed_meter.js');
            $this->context->controller->addJS($this->_path.'views/js/statistics.js');
            $this->context->controller->addJS($this->_path.'views/js/chart.min.js');
            $this->context->controller->addJS($this->_path.'views/js/chart.image.js');
            $this->context->controller->addJS($this->_path.'views/js/chart.image.js');
        }
        if($controller=='AdminSuperSpeedPageCaches')
        {
            $this->context->controller->addJS($this->_path.'views/js/codemirror.js');
            $this->context->controller->addCSS($this->_path.'views/css/codemirror.css');
        }
        if($controller=='AdminSuperSpeedImage')
        {
            $this->context->controller->addJS($this->_path.'views/js/upload.js');
        }
    }
    public function hookActionObjectAddAfter($params)
    {
        return $this->hookActionObjectUpdateAfter($params);
    }
    public function hookActionObjectDeleteAfter($params)
    {
        return $this->hookActionObjectUpdateAfter($params);
    }
    public function hookActionObjectUpdateAfter($params)
    {
        $object = $params['object'];
        if(self::isInstalled($this->name) && Db::getInstance()->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'ets_superspeed_cache_page"'))
        {
            if(!$this->_class_ss_cache)
                $this->getClassCahe();
            $this->_class_ss_cache->deleteCache(Tools::strtolower(get_class($object)),$object->id);
        }
        return true;
    }
    public function hookActionProductUpdate($params)
    {
        if(isset($params['product']))
        {
            $params['object'] = $params['product'];
            $this->hookActionObjectProductUpdateAfter($params);
        }
        
    }
    public function hookActionCategoryUpdate($params)
    {
        if(isset($params['category']))
        {
            $params['object'] = $params['category'];
            $this->hookActionObjectCategoryUpdateAfter($params);
        }
    }
    public function hookActionValidateOrder($params)
    {
        if(!$this->_class_ss_cache)
            $this->getClassCahe();
        if(isset($params['orderStatus']))
        {
            $orderStatus = $params['orderStatus'];
            if($orderStatus->logable)
                $this->_class_ss_cache->deleteCache('bestsales');
        }
        if(isset($params['cart']))
        {
            $cart= $params['cart'];
            foreach ($cart->getProducts() as $product) 
            {
                $this->_class_ss_cache->deleteCache('product',$product['id_product']);
            }
        }
    }
    public function hookActionObjectProductUpdateAfter($params)
    {
        $product= $params['object'];
        if(self::isInstalled($this->name))
        {
            if(!$this->_class_ss_cache)
                $this->getClassCahe();
            $this->_class_ss_cache->deleteCache('product',$product->id);
            $this->_class_ss_cache->deleteCache('pricesdrop');
            if($product->id_manufacturer)
                $this->_class_ss_cache->deleteCache('manufacturer',$product->id_manufacturer);
            $suppliers= Db::getInstance()->executeS('SELECT id_supplier FROM '._DB_PREFIX_.'product_supplier where id_product='.(int)$product->id);
            if($suppliers)
            {
                foreach($suppliers as $supplier)
                    $this->_class_ss_cache->deleteCache('supplier',$supplier['id_supplier']);
            }
            $categories = Db::getInstance()->executeS('SELECT id_category FROM '._DB_PREFIX_.'category_product WHERE id_product='.(int)$product->id);
            if($categories)
            {
                foreach($categories as $category)
                {
                    $this->clearCacheCategory($category['id_category']);
                }
            }
        }
        
    }
    public function hookActionOrderStatusPostUpdate()
	{
        if(!$this->_class_ss_cache)
		  $this->getClassCahe();
        $this->_class_ss_cache->deleteCache('bestsales');
	}
    public function hookActionObjectProductAddAfter($params)
    {
        $this->_class_ss_cache->deleteCache('newproducts');
        if(self::isInstalled($this->name))
        {
            $this->hookActionObjectProductUpdateAfter($params);
        }
    }
    public function hookActionObjectProductDeleteAfter($params)
    {
        if(!$this->_class_ss_cache)
		  $this->getClassCahe();
        $this->_class_ss_cache->deleteCache('bestsales');
        $this->_class_ss_cache->deleteCache('newproducts');
        if(self::isInstalled($this->name))
        {
            $this->hookActionObjectProductUpdateAfter($params);
        }
    }
    public function hookActionObjectCategoryUpdateAfter($params)
    {
        $category = $params['object'];
        if(!$this->_class_ss_cache)
            $this->getClassCahe();
        if(self::isInstalled($this->name))
        {
            if($category->id_parent)
                $this->clearCacheCategory($category->id_parent);
            $this->clearCacheCategory($category->id);
            $products= Db::getInstance()->executeS('SELECT id_product FROM '._DB_PREFIX_.'category_product WHERE id_category='.(int)$category->id);
            if($products)
            {
                foreach($products as $product)
                {
                    $this->_class_ss_cache->deleteCache('product',$product['id_product']);
                }
            }
        }
        
    }
    public function hookActionObjectCategoryDeleteAfter($params)
    {
        if(self::isInstalled($this->name))
            $this->hookActionObjectCategoryUpdateAfter($params);
    }
    public function hookActionObjectCategoryAddAfter($params)
    {
        if(self::isInstalled($this->name))
            $this->hookActionObjectCategoryUpdateAfter($params);
    }
    
    public function hookActionObjectCMSCategoryUpdateAfter($params)
    {
        if(!$this->_class_ss_cache)
            $this->getClassCahe();
        if(self::isInstalled($this->name))
        {
            $cmss= Db::getInstance()->executeS('SELECT id_cms FROM '._DB_PREFIX_.'cms WHERE id_cms_category='.(int)$params['object']);
            if($cmss)
            {
                foreach($cmss as $cms)
                {
                    $this->_class_ss_cache->deleteCache('cms',$cms['id_cms']);
                }
            }
        }
    }
    public function hookActionObjectCMSCategoryDeleteAfter($params)
    {
        if(self::isInstalled($this->name))
        {
            $this->hookActionObjectCMSCategoryUpdateAfter($params);
        }
    }
    public function hookDisplayAdminLeft()
    {
        $defines = new Ets_superspeed_defines();
        if(Tools::getValue('controller')=='AdminSuperSpeedImage')
        {
            $images= $this->getImageOptimize(true);
        }
        else
        {
            $total_image_product= $this->getTotalImage('product',true,false,false,true);
            $total_image_category = $this->getTotalImage('category',true,false,false,true);
            $total_image_manufacturer = $this->getTotalImage('manufacturer',true,false,false,true);
            $total_image_supplier = $this->getTotalImage('supplier',true,false,false,true);
            $total_images = $total_image_product + $total_image_category + $total_image_manufacturer + $total_image_supplier;
        }
        $this->context->smarty->assign(
            array(
                'left_tabs' => $defines->_admin_tabs,
                'control' => Tools::getValue('controller'),
                'ets_sp_module_dir'=> $this->_path,
                'total_images'=> isset($images) ? $images['total_images'] : $total_images,
                'url_speedcache_ajax' => $this->getBaseLink().'/modules/'.$this->name.'/ajax_ets_superspeed.php?token='.md5($this->id),
                'link_ajax_submit' => $this->getBaseLink().'/modules/'.$this->name.'/ajax.php?token='.md5($this->id),
            )
        );
        return $this->display(__FILE__,'admin_left.tpl');
    }
    public function hookActionModuleUnRegisterHookAfter($params)
    {
        $hook_name=$params['hook_name'];
        if(!$this->_class_ss_cache)
            $this->getClassCahe();
        $this->_class_ss_cache->deleteCache('',0,$hook_name);
    }
    public function  hookActionModuleRegisterHookAfter($params)
    {
        $hook_name=$params['hook_name'];
        if(!$this->_class_ss_cache)
            $this->getClassCahe();
        $this->_class_ss_cache->deleteCache('',0,$hook_name);
    }
    public function hookActionOutputHTMLBefore($params)
    {
        if($this->is17 && Configuration::get('PS_HTML_THEME_COMPRESSION'))
            $params['html'] = self::minifyHTML($params['html']);
        Ets_superspeed::createCache($params['html']);
    }
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedStatistics'));
    }
    public function clearCacheCategory($id_category)
    {
        if(!$this->_class_ss_cache)
            $this->getClassCahe();
        $this->_class_ss_cache->deleteCache('category',$id_category);
        $products= Db::getInstance()->executeS('SELECT id_product FROM '._DB_PREFIX_.'category_product WHERE id_category='.(int)$id_category);
        if($products)
        {
            foreach($products as $product)
            {
                $this->_class_ss_cache->deleteCache('product',$product['id_product']);
            }
        }
    }
    public function _postMinization()
    {
        if(Tools::isSubmit('btnSubmitMinization'))
        {
            if(Tools::getValue('ETS_SPEED_SMARTY_CACHE'))
            {
                if(Configuration::get('PS_SMARTY_FORCE_COMPILE')==2)
                    Configuration::updateValue('PS_SMARTY_FORCE_COMPILE',1);
            }
            else
            {
                Configuration::updateValue('PS_SMARTY_FORCE_COMPILE',2);
            }
            if(Tools::getValue('PS_SMARTY_CACHE'))
            {
                if(!Configuration::get('PS_SMARTY_CACHE'))
                {
                    Configuration::updateValue('PS_SMARTY_CACHE',1);
                    Configuration::updateValue('PS_SMARTY_CACHING_TYPE','filesystem');
                    Configuration::updateValue('PS_SMARTY_CLEAR_CACHE','everytime');
                }
            }
            else
                Configuration::updateValue('PS_SMARTY_CACHE',0);
            Configuration::updateValue('PS_HTML_THEME_COMPRESSION',Tools::getValue('PS_HTML_THEME_COMPRESSION'));
            Configuration::updateValue('PS_JS_THEME_CACHE',Tools::getValue('PS_JS_THEME_CACHE'));
            Configuration::updateValue('PS_CSS_THEME_CACHE',Tools::getValue('PS_CSS_THEME_CACHE'));
            Tools::clearSmartyCache();
            Tools::clearXMLCache();
            Media::clearCache();
            Tools::generateIndex();
            if(!$this->_class_ss_cache)
                $this->getClassCahe();
            $this->_class_ss_cache->deleteCache();
            if(Tools::isSubmit('ajax'))
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'success' => $this->displaySuccessMessage($this->l('Updated successfully'))
                        )
                    )
                );
            }
            else
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedMinization',true).'&conf=4');
        }
    }
    public function rederFormMinization()
    {
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Server cache and minification'),
					'icon' => 'icon-envelope'
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Smarty Cache'),
						'name' => 'ETS_SPEED_SMARTY_CACHE',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
                        'desc' => $this->l('Reduce template rendering time'),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Server Cache'),
						'name' => 'PS_SMARTY_CACHE',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
                        'desc' => $this->l('Reduce database access time'),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Minify HTML'),
						'name' => 'PS_HTML_THEME_COMPRESSION',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
                        'desc' => $this->l('Compress HTML code by removing repeated line breaks, white spaces, tabs and other unnecessary characters in the HTML code'),
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Minify Javascript'),
						'name' => 'PS_JS_THEME_CACHE',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
                        'desc' => $this->l('Compress Javascript code by removing repeated line breaks, white spaces, tabs and other unnecessary characters'),
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Minify CSS'),
						'name' => 'PS_CSS_THEME_CACHE',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
                        'desc' => $this->l('Compress CSS code by removing repeated line breaks, white spaces, tabs and other unnecessary characters'),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmitMinization';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminSuperSpeedMinization',false);
		$helper->token = Tools::getAdminTokenLite('AdminSuperSpeedMinization');
        $helper->module = $this;
		$helper->tpl_vars = array(
			'fields_value' => $this->getMinizationFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
		return $helper->generateForm(array($fields_form));
    }
    public function getMinizationFieldsValues()
    {
        return array(
            'ETS_SPEED_SMARTY_CACHE' => Configuration::get('PS_SMARTY_FORCE_COMPILE') == 2 ? false : true,
            'PS_SMARTY_CACHE' => Tools::getValue('PS_SMARTY_CACHE',Configuration::get('PS_SMARTY_CACHE')),
            'PS_HTML_THEME_COMPRESSION' => Tools::getValue('PS_HTML_THEME_COMPRESSION',Configuration::get('PS_HTML_THEME_COMPRESSION')),
            'PS_JS_THEME_CACHE' => Tools::getValue('PS_JS_THEME_CACHE',Configuration::get('PS_JS_THEME_CACHE')),
            'PS_CSS_THEME_CACHE' => Tools::getValue('PS_CSS_THEME_CACHE',Configuration::get('PS_CSS_THEME_CACHE')),
        );
    }
    public function _postDatabase()
    {
        $defines = new Ets_superspeed_defines();
        if(Tools::isSubmit('downloadDb') && $table=Tools::getValue('table'))
        {
            if(isset($defines->_datas_dynamic[$table]) && $data=$defines->_datas_dynamic[$table])
            {
               $total =(int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.pSQL($table).$data['where']);
               if(isset($data['table2']))
                    $total2 = (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.pSQL($data['table2']).$data['where2']);
               else 
                    $total2=0;
               if($total || $total2)
               {
                    if($total)
                    {
                        $csv= $this->getCSVData($data['table'],$data['where']);
                    }
                    if($total2)
                        $csv2=$this->getCSVData($data['table2'],$data['where2']);
                    if(isset($csv2) && isset($csv))
                    {
                        $zip = new ZipArchive();
                        $moduleDir = dirname(__FILE__).'/';
                        $zip_file_name =  date("Y-m-d").'_'.$data['table'].'_'.$data['table2'].'.zip';
                        if ($zip->open($moduleDir.$zip_file_name, ZipArchive::OVERWRITE | ZipArchive::CREATE) === true ) {
                            $zip->addFromString($data['table'].'.xls', $csv);
                            $zip->addFromString($data['table2'].'.xls', $csv2);
                            if (ob_get_length() > 0) {
                                ob_end_clean();
                            }
                            ob_start();
                            header('Pragma: public');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Cache-Control: public');
                            header('Content-Description: File Transfer');
                            header('Content-type: application/octet-stream');
                            header('Content-Disposition: attachment; filename="'.$zip_file_name.'"');
                            header('Content-Transfer-Encoding: binary');
                            ob_end_flush();
                            readfile($moduleDir.$zip_file_name);
                            @unlink($moduleDir.$zip_file_name);
                            exit;
                            
                        }
                    }
                    elseif(isset($csv2))
                    {
                        header("Content-type: application/x-msdownload");
                        header("Content-disposition: csv; filename=" . date("Y-m-d") .$data['table2'].".csv; size=".Tools::strlen($csv2));
                        echo $csv2;
                        exit();
                    }
                    else
                    {
                        header("Content-type: application/x-msdownload");
                        header("Content-disposition: csv; filename=" . date("Y-m-d") .$data['table'].".csv; size=".Tools::strlen($csv));
                        echo $csv;
                        exit();
                    }
               }     
               
            }
        }
        if(Tools::isSubmit('deleteDb') && $table=Tools::getValue('table'))
        {
            if(isset($defines->_datas_dynamic[$table]) && $data=$defines->_datas_dynamic[$table])
            {
                if(isset($data['table2']))
                    Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.pSQL($data['table2']).$data['where2']);
                Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.pSQL($data['table']).$data['where']);
                if(Tools::isSubmit('ajax'))
                {
                    die(
                        Tools::jsonEncode(
                            array(
                                'success' => $this->displaySuccessMessage($this->l('Deleted data successfully')),
                            )
                        )
                    );
                }
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedDatabase',true).'&conf=2');
            }
        }
        if(Tools::isSubmit('deleteallDb'))
        {
            foreach($defines->_datas_dynamic as $data)
            {
                if(isset($data['table2']))
                    Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.pSQL($data['table2']).$data['where2']);
                Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.pSQL($data['table']).$data['where']);
            }
            if(Tools::isSubmit('ajax'))
            {
                die(
                    Tools::jsonEncode(
                        array(
                             'success' => $this->displaySuccessMessage($this->l('Deleted all data successfully')),
                        )
                    )
                );
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedDatabase',true).'&conf=2');
        }
    }
    public function renderFormDataBase()
    {
        $datas=array();
        $defines = new Ets_superspeed_defines();
        foreach($defines->_datas_dynamic as $key=> $data)
        {
            $total =(int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.pSQL($key).$data['where']);
            if(isset($data['table2']))
                $total += (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.pSQL($data['table2']).$data['where2']);
            $data=array(
                'total' => $total,
                'name'=>$data['name'],
                'desc' =>$data['desc'],
                'link_download' => $this->context->link->getAdminLink('AdminSuperSpeedDatabase').'&downloadDb=1&table='.$key,
                'link_delete'=> $this->context->link->getAdminLink('AdminSuperSpeedDatabase').'&deleteDb=1&table='.$key,
            );
            $datas[]=$data;
        }
        $this->context->smarty->assign(
            array(
                'datas'=>$datas,
                'link_delete_all'=>$this->context->link->getAdminLink('AdminSuperSpeedDatabase').'&deleteallDb=1',
            )
        );
        return $this->display(__FILE__,'form_data.tpl');
    }
    public function getCSVData($table,$where)
    {
        $sql ='SELECT * FROM '._DB_PREFIX_.pSQL($table).$where;
        $results= Db::getInstance()->executeS($sql);
        $tam='';
        $csv='';
        if($results)
        {
            foreach($results as $key=>$result)
            {
                $message=$result;
                if($key==0)
                {
                    $i=1;
                    foreach($result as $key1=>$value1)
                    {
                        if($i!=count($result))
                            $csv .=$key1."\t";
                        else
                            $csv .=$key1."\r\n";
                        $i++;
                        unset($value1);
                    }
                } 
                $csv .= join("\t", $message)."\r\n";             
            }
        }
        unset($tam);
        $csv = chr(255).chr(254).mb_convert_encoding($csv, "UTF-16LE", "UTF-8");
        return $csv;
    }
    public function _postGzip()
    {
        $defines = new Ets_superspeed_defines();
        if(Tools::isSubmit('btnSubmitGzip'))
        {
            foreach($defines->_config_gzip as $config)
            {
                Configuration::updateValue($config['name'],Tools::getValue($config['name']));
            }
            if(version_compare(_PS_VERSION_, '1.7', '>='))
            {
                call_user_func('Ets_generateHtaccess17');
            }
            else
                call_user_func('Ets_generateHtaccess16');
            if(Tools::isSubmit('ajax'))
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'success' => $this->displaySuccessMessage($this->l('Updated successfully')),
                            'mod_deflate' => $this->check_mod_cache(false),
                            'mod_expires' => $this->check_mod_cache(true)
                        )
                    )
                );
            }
        }
        return true;
    }
    public function renderFormGzip()
    {
        $defines = new Ets_superspeed_defines();
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('GZIP & browser cache'),
					'icon' => 'icon-envelope'
				),
				'input' => $defines->_config_gzip,
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmitGzip';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminSuperSpeedGzip',false);
		$helper->token = Tools::getAdminTokenLite('AdminSuperSpeedGzip');
        $helper->module = $this;
		$helper->tpl_vars = array(
			'fields_value' => $this->getFieldsValues($defines->_config_gzip),
            'no_mod_deflate' => Tools::strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'deflate')===false ? true :false,
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
		return $helper->generateForm(array($fields_form));
    } 
    public function _saveTotalImageOpimized($image)
    {
        $total_image_optimized = (int)Configuration::get('ETS_SP_TOTAL_IMAGE_OPTIMIZED')+1;
        Configuration::updateValue('ETS_SP_TOTAL_IMAGE_OPTIMIZED',$total_image_optimized);
        if($images = Configuration::get('ETS_SP_LIST_IMAGE_OPTIMIZED'))
        {
            $images = explode(',',$images);
            if(count($images)<5)
                $images[] = $image;
            else
                $images[4] = $image;
            Configuration::updateValue('ETS_SP_LIST_IMAGE_OPTIMIZED',implode(',',$images));
        }
        else
            Configuration::updateValue('ETS_SP_LIST_IMAGE_OPTIMIZED',$image);
        if($total_image_optimized%$this->number_optimize==0)
            die('x');
        
    }
    public function _postImage(){
        $defines = new Ets_superspeed_defines();
        if(Tools::isSubmit('btnSubmitLazyLoadImage'))
        {
            $errors = array();
            if(!$errors)
            {
                Configuration::updateValue('ETS_SPEED_ENABLE_LAYZY_LOAD',Tools::getValue('ETS_SPEED_ENABLE_LAYZY_LOAD'));
                Configuration::updateValue('ETS_SPEED_LOADING_IMAGE_TYPE',Tools::getValue('ETS_SPEED_LOADING_IMAGE_TYPE'));
                Configuration::updateValue('ETS_SPEED_LAZY_FOR',implode(',',Tools::getValue('ETS_SPEED_LAZY_FOR')));
                $this->replaceTemplateProductDefault();
                die(
                    Tools::jsonEncode(
                        array(
                            'success' => $this->displaySuccessMessage($this->l('Updated successfully')),
                        )
                    )
                );
            }
            else
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'errors' => $this->displayError($errors),
                        )
                    )
                );
            }
        }
        if(Tools::isSubmit('btnSubmitCleaneImageUnUsed'))
        {
            if(Tools::getValue('unused_category_images'))
                $this->getImagesUnUsed('c','category','id_category','categories',true);
            if(Tools::getValue('unused_supplier_images'))
                $this->getImagesUnUsed('su','supplier','id_supplier','suppliers',true);
            if(Tools::getValue('unused_manufacturer_images'))
                $this->getImagesUnUsed('m','manufacturer','id_manufacturer','manufacturers',true);
            if(Tools::getValue('unused_product_images'))
                $this->getImagesProductUnUsed(true);
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->l('Clear unused images successfully'),
                    )
                )
            );
        }
        if(Tools::isSubmit('btnSubmitGlobImagesToFolder') && ($folder=Tools::getValue('folder')) && is_dir($folder))
        {
            die(
                Tools::jsonEncode(
                    array(
                        'list_files' => $this->globImagesToFolder($folder), 
                    )
                )
            );
        }
        if(Tools::isSubmit('restore_image_browse') && Tools::getValue('restore_image_browse'))
        {
            $image = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_browse_image WHERE image_id="'.pSQL(Tools::getValue('restore_image_browse')).'"');
            if($image['image_dir'] && file_exists($image['image_dir']))
            {
                $path = str_replace($image['image_name'],'',$image['image_dir']);
                $this->createBlogImage($path,$image['image_name'],true);
            }
            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ets_superspeed_browse_image WHERE image_id="'.pSQL(Tools::getValue('restore_image_browse')).'"');
            die(
                Tools::jsonEncode(
                    array(
                        'success'=> $this->l('Restored successfully'),
                        'image_id' => isset($image['image_dir']) && $image['image_dir'] ? MD5(str_replace('\\','/',$image['image_dir'])) :''
                    )
                )
            );
        }
        
        if(Tools::isSubmit('delete_image_upload') && Tools::getValue('delete_image_upload'))
        {
            $image_name = Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ets_superspeed_upload_image WHERE image_name_new="'.pSQL(Tools::getValue('delete_image_upload')).'"');
            if(@file_exists(_ETS_SPEED_CACHE_DIR_IMAGES.Tools::getValue('delete_image_upload')))
            {
                @unlink(_ETS_SPEED_CACHE_DIR_IMAGES.Tools::getValue('delete_image_upload'));
            }
            if(Tools::getValue('ajax'))
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'success' => $this->l('Deleted successfully'),
                        )
                    )
                );
            }
        }
        if(Tools::isSubmit('download_image_upload') && Tools::getValue('download_image_upload') && file_exists(_ETS_SPEED_CACHE_DIR_IMAGES.Tools::getValue('download_image_upload')))
        {
            $image_name = Db::getInstance()->getValue('SELECT image_name FROM '._DB_PREFIX_.'ets_superspeed_upload_image WHERE image_name_new="'.pSQL(Tools::getValue('download_image_upload')).'"');
            if($image_name && file_exists(_ETS_SPEED_CACHE_DIR_IMAGES.Tools::getValue('download_image_upload')))
            {
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary"); 
                header("Content-disposition: attachment; filename=\"".$image_name."\""); 
                readfile(_ETS_SPEED_CACHE_DIR_IMAGES.Tools::getValue('download_image_upload'));
                exit;
            }
            else
                die($this->l('Image does not exist'));
            
        }
        if(Tools::isSubmit('download_image_browse') && Tools::getValue('download_image_browse'))
        {
            $image = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_browse_image WHERE image_id="'.pSQL(Tools::getValue('download_image_browse')).'"');
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-disposition: attachment; filename=\"".$image['image_name']."\""); 
            readfile($image['image_dir']);
            exit;
        }
        if(Tools::isSubmit('submitBrowseImageOptimize') && $image=Tools::getValue('image'))
        {
            Configuration::updateValue('ETS_SP_ERRORS_TINYPNG','');
            $file_size= Tools::ps_round(@filesize($image)/1024,2);
            $image_id = MD5(str_replace('\\','/',$image));
            $image= explode('/',$image);
            $imageName = $image[count($image)-1];
            $path = str_replace($imageName,'',Tools::getValue('image'));
            $quality = Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE');
            if($this->createBlogImage($path,$imageName,false))
            {
                if($this->checkOptimizeImageResmush())
                    $url_image= $this->getBaseLink().str_replace(str_replace('\\','/',_PS_ROOT_DIR_),'',Tools::getValue('image'));
                else
                    $url_image=null;
                $compress= $this->compress($path,$imageName,$quality,$url_image,false);
                while($compress===false)
                    $compress= $this->compress($path,$imageName,$quality,$url_image,false);
                Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_browse_image(image_name,image_dir,image_id,old_size,new_size,date_add) VALUES("'.pSQL($imageName).'","'.pSQL(Tools::getValue('image')).'","'.pSQL($image_id).'","'.(float)$file_size.'","'.(float)$compress['file_size'].'","'.pSQL(date('Y-m-d H:i:s')).'")');
                die(
                    Tools::jsonEncode(array(
                        'success' => $this->l('Compress image successfully'),
                        'file_size' => $compress['file_size'] < 1024 ? $compress['file_size'].'KB' : Tools::ps_round($compress['file_size']/1024,2).'MB',
                        'saved' => Tools::ps_round(($file_size-$compress['file_size'])*100/$file_size,2).'%',
                        'image_dir' => str_replace(str_replace('\\','/',_PS_ROOT_DIR_),'',Tools::getValue('image')),
                        'link_download' =>'index.php?controller=AdminSuperSpeedImage&token='.Tools::getAdminTokenLite('AdminSuperSpeedImage').'&download_image_browse='.$image_id,
                        'link_restore' =>'index.php?controller=AdminSuperSpeedImage&token='.Tools::getAdminTokenLite('AdminSuperSpeedImage').'&restore_image_browse='.$image_id,
                    ))
                );
            }
            else
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'error' => $this->l('Create image failed'),
                        )
                    )
                );
            }
        }
        if(Tools::isSubmit('submitUploadImageCompress') && $image= Tools::getValue('image'))
        {
            Configuration::updateValue('ETS_SP_ERRORS_TINYPNG','');
            $file_size = Tools::getValue('file_size');
            $imageName = Tools::getValue('image_name');
            $compress = $this->compress(_ETS_SPEED_CACHE_DIR_IMAGES,$image,Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD'),$this->getBaseLink().'/ss_imagesoptimize/'.$image,0);
            while($compress===false)
            {
                $compress = $this->compress(_ETS_SPEED_CACHE_DIR_IMAGES,$image,Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD'),$this->getBaseLink().'/ss_imagesoptimize/'.$image,0);
            }
            Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_upload_image(image_name,old_size,new_size,image_name_new,date_add) VALUES("'.pSQL($imageName).'","'.(float)$file_size.'","'.(float)$compress['file_size'].'","'.pSQL($image).'","'.pSQL(date('Y-m-d H:i:s')).'")');
            die(
                Tools::jsonEncode(array(
                    'success' => $this->l('Compress image successfully'),
                    'file_size' => $compress['file_size'] < 1024 ? $compress['file_size'].'KB' : Tools::ps_round($compress['file_size']/1024,2).'MB',
                    'saved' => Tools::ps_round(($file_size-$compress['file_size'])*100/$file_size,2).'%',
                    'link_download' => 'index.php?controller=AdminSuperSpeedImage&token='.Tools::getAdminTokenLite('AdminSuperSpeedImage').'&download_image_upload='.$image,
                    'link_delete' => 'index.php?controller=AdminSuperSpeedImage&token='.Tools::getAdminTokenLite('AdminSuperSpeedImage').'&delete_image_upload='.$image,
                ))
            );
        }
        if(Tools::isSubmit('submitUploadImageSave'))
        {
            $errors = array();
            if(isset($_FILES['upload_image']['tmp_name']) &&  $_FILES['upload_image']['name'])
            {
                if(!is_dir(_ETS_SPEED_CACHE_DIR_IMAGES))
                    @mkdir(_ETS_SPEED_CACHE_DIR_IMAGES,0777,true);
                $imageName = $_FILES['upload_image']['name'];
                if(file_exists(_ETS_SPEED_CACHE_DIR_IMAGES.$_FILES['upload_image']['name']))
                {
                    $_FILES['upload_image']['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['upload_image']['name'];
                }
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['upload_image']['name'], '.'), 1));
                $file_size= Tools::ps_round(@filesize($_FILES['upload_image']['tmp_name'])/1024,2);
    			if (isset($_FILES['upload_image']) &&				
    				!empty($_FILES['upload_image']['tmp_name']) &&
    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
    			)
    			{ 				
    				if ($error = ImageManager::validateUpload($_FILES['upload_image']))
    					$errors[] = $error;
    				elseif (!move_uploaded_file($_FILES['upload_image']['tmp_name'], _ETS_SPEED_CACHE_DIR_IMAGES.$_FILES['upload_image']['name']))
    					$errors[] = $this->l('Can not upload the file');
    			}
                else
                    $errors[] = $this->l('File is not valid');
                if(!$errors)
                {
                    die(
                        Tools::jsonEncode(array(
                            'success' => $this->l('Uploaded successfully'),
                            'image' => $_FILES['upload_image']['name'],
                            'file_size' => $file_size,
                            'image_name'=>$imageName,
                        ))
                    );
                }
                else
                {
                    die(
                        Tools::jsonEncode(
                            array(
                                'errors' => $errors[0],
                            )
                        )
                    );
                }
            }
        }
        if(Tools::isSubmit('changeSubmitImageOptimize'))
        {
            die(
                Tools::jsonEncode(
                    $this->getImageOptimize(true)
                )
            );
        }
        if(Tools::isSubmit('btnSubmitImageAllOptimize'))
        {
            if(Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE')==100)
                Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE',50);
            $this->ajaxSubmitOptimizeImage(true);
            if(Tools::isSubmit('ajax'))
            {
                $array2= $this->getImageOptimize(true);
                $array1 =array(
                    'success' => $this->displaySuccessMessage($this->l('Optimized images successful')),
        			'id_language' => $this->context->language->id,
                    'configTabs' => $defines->_cache_image_tabs,
                );
                die(
                    Tools::jsonEncode(
                        array_merge($array1,$array2)
                    )
                );
            }       
        }
        if(Tools::isSubmit('btnSaveOptimizeImageUpload'))
        {
            if(Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD')=='tynypng')
            {
                $this->checkKeyTinyPNG();
            }
            Configuration::updateValue('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD',Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD'));
            Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD',Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD'));
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Saved successfully')),
                    )
                )
            );
        }
        if(Tools::isSubmit('btnSaveOptimizeImageBrowse'))
        {
            if(Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE')=='tynypng')
            {
                $this->checkKeyTinyPNG();
            }
            Configuration::updateValue('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE',Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE'));
            Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE',Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE'));
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Saved successfully')),
                    )
                )
            );
        }
        if(Tools::isSubmit('btnSubmitNewImageOptimize'))
        {
            if(Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT_NEW')=='tynypng')
            {
                $this->checkKeyTinyPNG();
            }           
            foreach($defines->_config_images as $config)
            {
                if(Tools::strpos($config['name'],'_NEW')!==false)
                {
                    if($config['type']=='checkbox')
                    {
                        if(Tools::getValue($config['name']))
                            Configuration::updateValue($config['name'],implode(',',Tools::getValue($config['name'])));
                        else
                            Configuration::updateValue($config['name'],'');
                    }
                    else
                        Configuration::updateValue($config['name'],Tools::getValue($config['name']));
                }
                
            }
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Saved successfully')),
                    )
                )
            );
        }
        if(Tools::isSubmit('btnSubmitOldImageOptimize'))
        {
            if(Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT')=='tynypng')
            {
                $this->checkKeyTinyPNG();
            }           
            foreach($defines->_config_images as $config)
            {
                if(Tools::strpos($config['name'],'_NEW')===false)
                {
                    if($config['type']=='checkbox')
                    {
                        if(Tools::getValue($config['name']))
                            Configuration::updateValue($config['name'],implode(',',Tools::getValue($config['name'])));
                        else
                            Configuration::updateValue($config['name'],'');
                    }
                    else
                        Configuration::updateValue($config['name'],Tools::getValue($config['name']));
                }
                
            }
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Saved Successfully')),
                    )
                )
            );
        }
        if(Tools::isSubmit('btnSubmitImageOptimize'))
        {
            if(Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT')=='tynypng')
            {
                $this->checkKeyTinyPNG();
            }
            foreach($defines->_config_images as $config)
            {
                if(Tools::strpos($config['name'],'_NEW')===false)
                {
                    if($config['type']=='checkbox')
                    {
                        if(Tools::getValue($config['name']))
                            Configuration::updateValue($config['name'],implode(',',Tools::getValue($config['name'])));
                        else
                            Configuration::updateValue($config['name'],'');
                    }
                    else
                        Configuration::updateValue($config['name'],Tools::getValue($config['name']));
                }
            }
            $quality=Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') ? Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') :90;
            $this->ajaxSubmitOptimizeImage(false);
            $array2= $this->getImageOptimize(false);
            $array1 =array(
                'success' => $this->displaySuccessMessage($quality == 100 ? $this->l('Restored images successfully') : $this->l('Optimized images successfully')),
    			'id_language' => $this->context->language->id,
                'configTabs' => $defines->_cache_image_tabs,
            );
            die(
                Tools::jsonEncode(
                    array_merge($array1,$array2)
                )
            );
        }
        return true;
    }
    public function renderFromImageCache()
    {
        $defines = new Ets_superspeed_defines();
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Image optimization'),
					'icon' => 'icon-envelope'
				),
				'input' => $defines->_config_images,
				'submit' => array(
					'title' =>Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE')==100 ? $this->l('Restore original images'): $this->l('Optimize existing images'),
                    'icon'=>'process-icon-cogs',
				),
                'buttons'=>array(
                    array(
                        'name'=> 'btnSubmitLazyLoadImage',
                        'icon'=>'process-icon-save',
                        'title'=> $this->l('Save'),
                        'class'=>'pull-right',
                    ),
                    array(
                        'name'=> 'btnSubmitNewImageOptimize',
                        'icon'=>'process-icon-save',
                        'title'=> $this->l('Save'),
                        'class'=>'pull-right',
                    ),
                    array(
                        'name'=> 'btnSubmitOldImageOptimize',
                        'icon'=>'process-icon-save',
                        'title'=> $this->l('Save'),
                        'class'=>'pull-left',
                    )
                ),
			),
		);
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmitImageOptimize';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminSuperSpeedImage',false);
		$helper->token = Tools::getAdminTokenLite('AdminSuperSpeedImage');
        $helper->module = $this;
        $install_logs= file_exists(dirname(__FILE__).'/cache/install.log') ? array_keys(Tools::jsonDecode(Tools::file_get_contents(dirname(__FILE__).'/cache/install.log'),true)):false;
		if($install_logs)
        {
            foreach($install_logs as $key=>$log)
                if(!in_array($log,array('AdminCategoriesController','AdminManufacturersController','AdminSuppliersController')))
                    unset($install_logs[$key]);
                else
                    $install_logs[$key] .='.php';
        }
        $tpl_vars=array(
            'fields_value' => array_merge($this->getFieldsValues($defines->_config_images),array('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD'=> Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD'),'ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD'=> Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD'),'ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE'=>Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE'),'ETS_SPEED_QUALITY_OPTIMIZE_BROWSE'=>Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE'))),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
            'dir_override' => _PS_OVERRIDE_DIR_,
            'sp_dir_override' => dirname(__FILE__).'/override/',
            'configTabs' => $defines->_cache_image_tabs,
            'ETS_SPEED_API_TYNY_KEY' => explode(';',Configuration::get('ETS_SPEED_API_TYNY_KEY')),
            'install_logs' => $install_logs ? implode(', ',$install_logs) : false,
        );
        $images= $this->getImageOptimize(true);
		$helper->tpl_vars = array_merge($tpl_vars,$images);
		return $helper->generateForm(array($fields_form));
    }
    public function getTotalImage($type='product',$all_type=false,$optimizaed=false,$check_quality=false,$noconfig=false,$type_image='')
    {
        $total=0;
        $count_type=1;
        $quality = (int)Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE',Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE'));
        $optimize_script = Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT',Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT'));
        $update_quantity = Tools::isSubmit('ETS_SPEED_QUALITY_OPTIMIZE') ? Tools::getValue('ETS_SPEED_UPDATE_QUALITY') : Configuration::get('ETS_SPEED_UPDATE_QUALITY');
        if($update_quantity && $quality!=100)
        {
            $check_quality = false;
            $check_optimize_script = false;
        }
        else
        {
            $check_quality = true;
            if($quality==100)
                $check_optimize_script=false;
            else
                $check_optimize_script = true;
        }
        switch($type){
            case 'category':
                if(Tools::getValue('changeSubmitImageOptimize'))
                    $category_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE'):array();
                else
                    $category_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE', Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE')):array());
                if($all_type)
                {
                    if(( $category_type && in_array('0',$category_type))|| $noconfig)
                        $count_type = count($this->getImageTypes('categories'));
                    else
                        $count_type = $category_type ? count($category_type):0;
                }
                $categoies= Db::getInstance()->executeS('SELECT id_category FROM '._DB_PREFIX_.'category');
                if($categoies)
                {
                    foreach($categoies as $category)
                    {
                        if(file_exists(_PS_CAT_IMG_DIR_.$category['id_category'].'.jpg'))
                            $total++;
                    }
                }
                $total = $total*$count_type;
                if($optimizaed)
                {
                    $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sci.id_category) FROM '._DB_PREFIX_.'ets_superspeed_category_image sci 
                    INNER JOIN '._DB_PREFIX_.'category c ON (c.id_category = sci.id_category)
                    WHERE sci.size_old!=0 '.($all_type && $category_type && !$noconfig ? ' AND  type_image IN ("'.implode('","', array_map('pSQL',$category_type)).'")':'').($type_image ? ' AND type_image="'.pSQL($type_image).'"':'').($check_quality ? ' AND quality = "'.(int)$quality.'"':' AND quality!=100').($check_optimize_script ? ' AND optimize_type="'.pSQL($optimize_script).'"':''));
                    return $count_type ? ($total_optimized > $total ? $total : $total_optimized) :0;
                }
                return $total;
            case 'manufacturer':
                if(Tools::getValue('changeSubmitImageOptimize'))
                    $manufacturer_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE'):array();
                else
                    $manufacturer_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE')):array());
                if($all_type)
                {
                    if(($manufacturer_type && in_array('0',$manufacturer_type))||$noconfig)
                        $count_type= count($this->getImageTypes('manufacturers'));
                    else
                        $count_type = $manufacturer_type ? count($manufacturer_type):0;
                }
                $manufacturers = Db::getInstance()->executeS('SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer');
                if($manufacturers)
                {
                    foreach($manufacturers as $manufacturer)
                    {
                        if(file_exists(_PS_MANU_IMG_DIR_.$manufacturer['id_manufacturer'].'.jpg'))
                            $total++;
                    }  
                        
                }
                $total = $count_type*$total;
                if($optimizaed)
                {
                    $total_optimized = Db::getInstance()->getValue('SELECT COUNT(smi.id_manufacturer) FROM '._DB_PREFIX_.'ets_superspeed_manufacturer_image smi
                    INNER JOIN '._DB_PREFIX_.'manufacturer m ON (smi.id_manufacturer = m.id_manufacturer)
                    WHERE smi.size_old!=0 '.($all_type && $manufacturer_type && !$noconfig ? ' AND  type_image IN ("'.implode('","', array_map('pSQL',$manufacturer_type)).'")':'').($type_image ? ' AND type_image="'.pSQL($type_image).'"':'').($check_quality ? ' AND quality = "'.(int)$quality.'"':' AND quality!=100').($check_optimize_script ? ' AND optimize_type="'.pSQL($optimize_script).'"':''));
                    return $count_type ? ($total_optimized >$total ? $total : $total_optimized) :0;
                }
                return $total;
            case 'supplier':
                if(Tools::getValue('changeSubmitImageOptimize'))
                    $supplier_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE'):array();
                else
                    $supplier_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE')):array());
                if($all_type)
                {
                    if(($supplier_type && in_array('0',$supplier_type))||$noconfig)
                        $count_type = count($this->getImageTypes('suppliers'));
                    else
                        $count_type = $supplier_type ? count($supplier_type):0;
                }
                $suppliers = Db::getInstance()->executeS('SELECT id_supplier FROM '._DB_PREFIX_.'supplier');
                if($suppliers)
                {
                    foreach($suppliers as $supplier)
                    {
                        if(file_exists(_PS_SUPP_IMG_DIR_.$supplier['id_supplier'].'.jpg'))
                            $total++;
                    }
                }
                $total = $total*$count_type;
                if($optimizaed)
                {
                    $total_optimized = Db::getInstance()->getValue('SELECT COUNT(ssi.id_supplier) FROM '._DB_PREFIX_.'ets_superspeed_supplier_image ssi
                    INNER JOIN '._DB_PREFIX_.'supplier s ON (ssi.id_supplier = s.id_supplier)
                    WHERE ssi.size_old!=0'.($all_type && $supplier_type && !$noconfig ? ' AND  type_image IN ("'.implode('","', array_map('pSQL',$supplier_type)).'")':'').($type_image ? ' AND type_image="'.pSQL($type_image).'"':'').($check_quality ? ' AND quality = "'.(int)$quality.'"':' AND quality!=100').($check_optimize_script ? ' AND optimize_type="'.pSQL($optimize_script).'"':''));
                    return $count_type ? ($total_optimized > $total ? $total: $total_optimized): 0;
                }
                return $total;
            case 'product':
                if(Tools::getValue('changeSubmitImageOptimize'))
                    $product_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE'):array();
                else
                    $product_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE', Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE')) : array());
                if($all_type)
                {
                    if(($product_type && in_array('0',$product_type))||$noconfig)
                        $count_type = count($this->getImageTypes('products'));
                    else
                        $count_type = $product_type ? count($product_type):0;
                    
                }
                if($optimizaed)
                {
                    $total = Db::getInstance()->getValue('
                        SELECT COUNT(pm.id_image) FROM '._DB_PREFIX_.'ets_superspeed_product_image pm
                        INNER JOIN '._DB_PREFIX_.'image m ON (pm.id_image= m.id_image)
                        WHERE 1'.($all_type && $product_type && !$noconfig ? ' AND pm.type_image IN ("'.implode('","', array_map('pSQL',$product_type)).'")':'').($type_image ? ' AND pm.type_image="'.pSQL($type_image).'"':'').( $check_quality ? ' AND pm.quality = "'.(int)$quality.'"':' AND pm.quality!=100').($check_optimize_script ? 'AND optimize_type="'.pSQL($optimize_script).'"':''));
                    return $count_type ? $total:0;
                }
                $total = Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'image');
                return $total*$count_type;
            case 'blog_post':
                if($this->isblog)
                {
                    if(Tools::getValue('changeSubmitImageOptimize'))
                        $blog_post_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE'):array();
                    else
                        $blog_post_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE')):array());
                    $total = 0;
                    if($all_type)
                    {
                        if(in_array('image',$blog_post_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_post) FROM '._DB_PREFIX_.'ybc_blog_post WHERE image!=""');
                        if(in_array('thumb',$blog_post_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_post) FROM '._DB_PREFIX_.'ybc_blog_post WHERE thumb!=""');
                    }
                    elseif($type_image && in_array($type_image,array('image','thumb')))
                        $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_post) FROM '._DB_PREFIX_.'ybc_blog_post WHERE '.pSQL($type_image).'!=""');
                    if($optimizaed)
                    {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbpi.id_post) FROM '._DB_PREFIX_.'ets_superspeed_blog_post_image sbpi
                        INNER JOIN '._DB_PREFIX_.'ybc_blog_post bp ON (sbpi.id_post = bp.id_post)
                        WHERE sbpi.size_old!=0'.($all_type && $blog_post_type && !$noconfig ? ' AND  type_image IN ("'.implode('","', array_map('pSQL',$blog_post_type)).'")':'').($type_image ? ' AND type_image="'.pSQL($type_image).'"':'').($check_quality ? ' AND quality = "'.(int)$quality.'"':' AND quality!=100').($check_optimize_script ? ' AND optimize_type="'.pSQL($optimize_script).'"':''));
                        return $total_optimized > $total ? $total: $total_optimized;
                    }
                    return $total;
                }
                else
                    return 0;
            case 'blog_category':
                if($this->isblog)
                {
                    if(Tools::getValue('changeSubmitImageOptimize'))
                        $blog_category_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE'):array();
                    else
                        $blog_category_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE')):array());
                    $total = 0;
                    if($all_type)
                    {
                        if(in_array('image',$blog_category_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_category) FROM '._DB_PREFIX_.'ybc_blog_category WHERE image!=""');
                        if(in_array('thumb',$blog_category_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_category) FROM '._DB_PREFIX_.'ybc_blog_category WHERE thumb!=""');
                    }
                    elseif($type_image && in_array($type_image,array('image','thumb')))
                        $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_category) FROM '._DB_PREFIX_.'ybc_blog_category WHERE '.pSQL($type_image).'!=""');
                    if($optimizaed)
                    {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbci.id_category) FROM '._DB_PREFIX_.'ets_superspeed_blog_category_image sbci
                        INNER JOIN '._DB_PREFIX_.'ybc_blog_category bc ON (bc.id_category = sbci.id_category)
                        WHERE sbci.size_old!=0 '.($all_type && $blog_category_type && !$noconfig ? ' AND  type_image IN ("'.implode('","', array_map('pSQL',$blog_category_type)).'")':'').($type_image ? ' AND type_image="'.pSQL($type_image).'"':'').($check_quality ? ' AND quality = "'.(int)$quality.'"':' AND quality!=100').($check_optimize_script ? ' AND optimize_type="'.pSQL($optimize_script).'"':''));
                        return $total_optimized > $total ? $total: $total_optimized;
                    }
                    return $total;
                }
                else
                    return 0;
            case 'blog_gallery':
                if($this->isblog)
                {
                    if(Tools::getValue('changeSubmitImageOptimize'))
                        $blog_gallery_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE'):array();
                    else
                        $blog_gallery_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE')):array());
                    $total = 0;
                    if($all_type)
                    {
                        if(in_array('image',$blog_gallery_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_gallery) FROM '._DB_PREFIX_.'ybc_blog_gallery WHERE image!=""');
                        if(in_array('thumb',$blog_gallery_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_gallery) FROM '._DB_PREFIX_.'ybc_blog_gallery WHERE thumb!=""');
                    }
                    elseif($type_image && in_array($type_image,array('image','thumb')))
                        $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_gallery) FROM '._DB_PREFIX_.'ybc_blog_gallery WHERE '.pSQL($type_image).'!=""');
                    if($optimizaed)
                    {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbgi.id_gallery) FROM '._DB_PREFIX_.'ets_superspeed_blog_gallery_image sbgi
                        INNER JOIN '._DB_PREFIX_.'ybc_blog_gallery bg ON (bg.id_gallery = sbgi.id_gallery)
                        WHERE sbgi.size_old!=0'.($all_type && $blog_gallery_type && !$noconfig ? ' AND  type_image IN ("'.implode('","', array_map('pSQL',$blog_gallery_type)).'")':'').($type_image ? ' AND type_image="'.pSQL($type_image).'"':'').($check_quality ? ' AND quality = "'.(int)$quality.'"':' AND quality!=100').($check_optimize_script ? ' AND optimize_type="'.pSQL($optimize_script).'"':''));
                        return $total_optimized > $total ? $total: $total_optimized;
                    }
                    return $total;
                }
                else
                    return 0;
            case 'blog_slide':
                if($this->isblog)
                {
                    if(Tools::getValue('changeSubmitImageOptimize'))
                        $blog_slide_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE'):array();
                    else
                        $blog_slide_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE')):array());
                    $total = 0;
                    if($all_type)
                    {
                        if(in_array('image',$blog_slide_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_slide) FROM '._DB_PREFIX_.'ybc_blog_slide WHERE image!=""');
                    }
                    elseif($type_image && in_array($type_image,array('image')))
                        $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_slide) FROM '._DB_PREFIX_.'ybc_blog_slide WHERE '.pSQL($type_image).'!=""');
                    if($optimizaed)
                    {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbsi.id_slide) FROM '._DB_PREFIX_.'ets_superspeed_blog_slide_image sbsi
                        INNER JOIN '._DB_PREFIX_.'ybc_blog_slide bs ON (bs.id_slide = sbsi.id_slide)
                        WHERE sbsi.size_old!=0'.($all_type && $blog_slide_type && !$noconfig ? ' AND  type_image IN ("'.implode('","', array_map('pSQL',$blog_slide_type)).'")':'').($type_image ? ' AND type_image="'.pSQL($type_image).'"':'').($check_quality ? ' AND quality = "'.(int)$quality.'"':' AND quality!=100').($check_optimize_script ? ' AND optimize_type="'.pSQL($optimize_script).'"':''));
                        return $total_optimized > $total ? $total: $total_optimized;
                    }
                    return $total;
                }
                else
                    return 0;
            case 'home_slide':
                if($this->isSlide)
                {
                    if(Tools::getValue('changeSubmitImageOptimize'))
                        $home_slide_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE'):array();
                    else
                        $home_slide_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE')):array());
                    $total = 0;
                    if($home_slide_type || ($all_type && $noconfig) || $type_image)
                    {
                        $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT image) FROM '._DB_PREFIX_.'homeslider_slides_lang WHERE image!=""');
                    }
                    if($optimizaed)
                    {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(shsi.image) FROM '._DB_PREFIX_.'ets_superspeed_home_slide_image shsi
                        INNER JOIN '._DB_PREFIX_.'homeslider_slides hs ON (hs.id_homeslider_slides=shsi.id_homeslider_slides)
                        WHERE shsi.size_old!=0'.($check_quality ? ' AND quality = "'.(int)$quality.'"':' AND quality!=100').($check_optimize_script ? ' AND optimize_type="'.pSQL($optimize_script).'"':''));
                        return $total_optimized > $total ? $total: $total_optimized;
                    }
                    return $total;
                }
                else
                    return 0;
            case 'others' :
            {
                if(Tools::getValue('changeSubmitImageOptimize'))
                    $orther_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE'):array();
                else
                    $orther_type= Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE') ? explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE')):array());
                $total = 0;
                if($all_type)
                {
                    if(in_array('logo',$orther_type) || $noconfig)
                        $total += (Configuration::get('PS_LOGO') ? 1 :0);
                    if(in_array('banner',$orther_type) || $noconfig)
                    {
                        if($this->is17)
                        {
                            if(Module::isInstalled('ps_banner') && Module::isEnabled('ps_banner'))
                            {
                                $languages = Language::getLanguages(false);
                                $banners = array();
                                foreach($languages as $language)
                                {
                                    if(($image = Configuration::get('BANNER_IMG',$language['id_lang'])) && !in_array($image,$banners))
                                    {
                                        $banners[] = $image;
                                        $total ++;
                                    }
                                }
                            }
                        }
                        else
                        {
                            if(Module::isInstalled('blockbanner') && Module::isEnabled('blockbanner'))
                            {
                                $languages = Language::getLanguages(false);
                                $banners = array();
                                foreach($languages as $language)
                                {
                                    if(($image = Configuration::get('BLOCKBANNER_IMG',$language['id_lang'])) && !in_array($image,$banners))
                                    {
                                        $banners[] = $image;
                                        $total ++;
                                    }
                                }
                            }
                        }
                    } 
                    if(in_array('themeconfig',$orther_type) || $noconfig)
                    {
                      
                        if(Module::isInstalled('themeconfigurator') && Module::isEnabled('themeconfigurator'))
                        {
                            $themeconfigurators = Db::getInstance()->executeS('SELECT image FROM '._DB_PREFIX_.'themeconfigurator WHERE image!="" GROUP BY image');
                            $themes = array();
                            if($themeconfigurators)
                            {
                                foreach($themeconfigurators as $themeconfigurator)
                                {
                                    $themes[]= $themeconfigurator['image'];
                                    $total ++;
                                }
                            }
                        }
                    }    
                }
                elseif($type_image && in_array($type_image,array('logo','banner','themeconfig')))
                {
                    if($type_image=='logo' && Configuration::get('PS_LOGO'))
                        $total ++;
                    elseif($type_image=='banner')
                    {
                        if($this->is17)
                        {
                            if(Module::isInstalled('ps_banner') && Module::isEnabled('ps_banner'))
                            {
                                $languages = Language::getLanguages(false);
                                $banners = array();
                                foreach($languages as $language)
                                {
                                    if(($image = Configuration::get('BANNER_IMG',$language['id_lang'])) && !in_array($image,$banners))
                                    {
                                        $banners[] = $image;
                                        $total ++;
                                    }
                                }
                            }
                        }
                        else
                        {
                            if(Module::isInstalled('blockbanner') && Module::isEnabled('blockbanner'))
                            {
                                $languages = Language::getLanguages(false);
                                $banners = array();
                                foreach($languages as $language)
                                {
                                    if(($image = Configuration::get('BLOCKBANNER_IMG',$language['id_lang'])) && !in_array($image,$banners))
                                    {
                                        $banners[] = $image;
                                        $total ++;
                                    }
                                }
                            }
                        }
                    }
                    elseif($type_image=='themeconfig')
                    {
                        if(Module::isInstalled('themeconfigurator') && Module::isEnabled('themeconfigurator'))
                        {
                            $themeconfigurators = Db::getInstance()->executeS('SELECT image FROM '._DB_PREFIX_.'themeconfigurator WHERE image!="" GROUP BY image');
                            $themes = array();
                            if($themeconfigurators)
                            {
                                foreach($themeconfigurators as $themeconfigurator)
                                {
                                    $themes[]= $themeconfigurator['image'];
                                    $total ++;
                                }
                            }
                        }
                    }
                }
                if($optimizaed)
                {
                    if(isset($banners))
                        $images = $banners;
                    else    
                        $images = array();
                    if(Configuration::get('PS_LOGO'))
                        $images[] = Configuration::get('PS_LOGO');
                    if(isset($themes))
                        $images = array_merge($images,$themes);
                    $total_optimized = Db::getInstance()->getValue('SELECT COUNT(image) FROM '._DB_PREFIX_.'ets_superspeed_others_image
                    WHERE 1'.($all_type && $orther_type && !$noconfig ? ' AND  type_image IN ("'.implode('","', array_map('pSQL',$orther_type)).'")':'').($type_image ? ' AND type_image="'.pSQL($type_image).'"':'').($images ? ' AND image IN ("'.implode('","',array_map('pSQL',$images)).'")':'').($check_quality ? ' AND quality = "'.(int)$quality.'"':' AND quality!=100').($check_optimize_script ? ' AND optimize_type="'.pSQL($optimize_script).'"':''));
                    return $total_optimized > $total ? $total: $total_optimized;
                }
                return $total;
            }
        }
        return $total;
    }
    public function getFieldsValues($formFields){
        $values=array();
        foreach($formFields as $field)
        {
            if($field['type']=='checkbox')
            {
                $values[$field['name']] = Tools::getValue($field['name'], explode(',',Configuration::get($field['name'])));
            }
                
            else
                $values[$field['name']] = Tools::getValue($field['name'],Configuration::get($field['name']));
        }
        return $values;
    }
    public function createBlogImage($path,$name,$restore = true)
    {
        $type_image = Tools::strtolower(Tools::substr(strrchr($name, '.'), 1));
        $name_bk = str_replace('.'.$type_image,'',$name).'_bk.'.$type_image;
        if(file_exists($path.$name_bk) && $restore)
        {
            if(file_exists($path.$name))
                unlink($path.$name);
            Tools::copy($path.$name_bk,$path.$name);
            return Tools::ps_round(filesize($path.$name_bk)/1024,2);            
        }
        elseif(file_exists($path.$name))
        {
            if(!file_exists($path.$name_bk))
                Tools::copy($path.$name,$path.$name_bk);
            return Tools::ps_round(filesize($path.$name)/1024,2);
        }
        return 0;
        
    }
    public function createImage($path,$type,$optimizied=false)
    {
        $tgt_width = $tgt_height = 0;
        $src_width = $src_height = 0;
        $error = 0;
        if(file_exists($path.'.jpg'))
        {
            if(@file_exists($path.'-'.Tools::stripslashes($type['name']).'.jpg') && $optimizied)
            {
                @unlink($path.'-'.Tools::stripslashes($type['name']).'.jpg');
            }
            if(!@file_exists($path.'-'.Tools::stripslashes($type['name']).'.jpg'))
            {
                ImageManager::resize(
                    $path.'.jpg',
                    $path.'-'.Tools::stripslashes($type['name']).'.jpg',
                    $type['width'],
                    $type['height'],
                    'jpg',
                    false,
                    $error,
                    $tgt_width,
                    $tgt_height,
                    5,
                    $src_width,
                    $src_height
                );          
            }  
        }
        if(file_exists($path.'-'.Tools::stripslashes($type['name']).'.jpg'))
            return Tools::ps_round(filesize($path.'-'.Tools::stripslashes($type['name']).'.jpg')/1024,2);
        else
            return false;
    }
    public function compressByReSmush($url_image,$quality,$temp,$destination,$file_size_old)
    {
        $optimized_jpg_arr = Tools::jsonDecode(Tools::file_get_contents('http://api.resmush.it/ws.php?img='. $url_image.($quality <80 ? '&qlty='.(int)$quality: '')),true);
    	if(isset($optimized_jpg_arr['dest']))
    	{
    	   $optimized_jpg_url= $optimized_jpg_arr['dest'];
            if(Configuration::get('ETS_SPEEP_RESUMSH')!=1)
                Configuration::updateValue('ETS_SPEEP_RESUMSH',1);
            file_put_contents($temp,Tools::file_get_contents($optimized_jpg_url));
            $file_size= Tools::ps_round(@filesize($temp)/1024,2);
            if($file_size>0)
            {
                Tools::copy($temp,$destination);
                @unlink($temp);
                if($file_size < $file_size_old)
                    return array(
                        'file_size' => $file_size,
                        'optimize_type' => 'resmush',
                    );
                else
                    return array(
                        'file_size' => $file_size_old,
                        'optimize_type' => 'resmush',
                    );
            }
            else
            {
                @unlink($temp);
                $this->_errors[] = $this->l('Resmush failed to create image');
                return false;
            }
    	}
        $this->_errors[] = $this->l('Resmush failed to create image');
        return false;
    }
    public function compressByPhp($path,$name,$source,$destination,$temp,$quality,$type,$file_size_old,$quality_old)
    {
        if($this->png_has_transparency($source))
            return array(
                'file_size' => $file_size_old,
                'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
            );
		
        ini_set('gd.jpeg_ignore_warning', 1);
        $temp2 = $path.'temp2-'.$name;
        Tools::copy($source,$temp2);
		$image= @getimagesize($source);
        $default = false;
        if($quality >=100 || ($quality<=80 && is_array($type) && isset($type['width']) && $type['width']<=260) || ($name==Configuration::get('PS_LOGO') && $quality<=80))
        {
            if($quality_old <= 80)
                return array(
                    'file_size' => $file_size_old,
                    'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
                );
            $default = true;
        }
        if($image)
        {
            ini_set('gd.jpeg_ignore_warning', 1);
            $widthImage = $image[0];
            $heightImage = $image[1];
			
            $imageCanves = imagecreatetruecolor($widthImage, $heightImage);
            switch(Tools::strtolower($image['mime']))
            {
                case 'image/jpeg':
                    $NewImage = imagecreatefromjpeg($source);
                    break;
                case 'image/JPEG':
                    $NewImage = imagecreatefromjpeg($source);
                    break;
                case 'image/png':
                    $NewImage = imagecreatefrompng($source);
                    break;
                case 'image/PNG':
                    $NewImage = imagecreatefrompng($source);
                    break;
                case 'image/gif':
                    $NewImage = imagecreatefromgif($source);
                    break;
                default:
                    return array(
						'file_size' => $file_size_old,
						'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
					);
            }
			
            $white = imagecolorallocate($imageCanves, 255, 255, 255); 
            imagefill($imageCanves,0,0,$white); 
            // Resize Image
            if(imagecopyresampled($imageCanves, $NewImage,0, 0, 0, 0, $widthImage, $heightImage, $widthImage, $heightImage))
            {
                // copy file

                if(imagejpeg($imageCanves,$destination,$default ? 80 : $quality))
                {
                    imagedestroy($imageCanves);   
                    if(Tools::copy($destination,$temp))
                    {
                        $file_size= Tools::ps_round(@filesize($temp)/1024,2);
                        if($file_size > $file_size_old)
                        {
                            Tools::copy($temp2,$destination);
                            $file_size = $file_size_old;
                        }
                        @unlink($temp);
                        @unlink($temp2);
                        return array(
                            'file_size' => $file_size,
                            'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
                        );
                    }       
                }
            } 
        }
        @unlink($temp2);
        return array(
            'file_size' => $file_size_old,
            'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
        );
    }
    public function compressByTyNyPNG($source,$api_key)
    {
        $curl = curl_init();
        $curlOptions = array(
            CURLOPT_BINARYTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.tinypng.com/shrink',
            CURLOPT_USERAGENT => 'TinyPNG PHP v1',
            CURLOPT_USERPWD => 'api:'.$api_key,
        );
        curl_setopt_array($curl, $curlOptions);
        curl_setopt($curl, CURLOPT_POSTFIELDS, Tools::file_get_contents($source));
        $response = curl_exec($curl);
        $content = Tools::jsonDecode(Tools::substr($response, curl_getinfo($curl, CURLINFO_HEADER_SIZE)),true);
        if(isset($content['output']['url']) && $content['output']['url'])
        {
            if($content['output']['size']>0)
            {
                if(file_put_contents($source, Tools::file_get_contents($content['output']['url'])) !== false)
                {
                    return array(
                        'file_size' => Tools::ps_round($content['output']['size']/1024,2),
                        'optimize_type' => 'tynypng',
                    );           
                }
            }
        }
        $this->_errors[] = $this->l('TinyPNG is not working. Your API key(s) is invalid or you may have reached API limit.');
        return false;
    }
    public function compress($path, $type, $quality,$url_image=null,$quality_old=0) {
        if(Tools::isSubmit('btnSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageAllOptimize')|| Tools::isSubmit('btnSubmitPageCacheDashboard'))
        {
            $script_optimize = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT');
            $image = 'old';
        }
        elseif(Tools::isSubmit('submitUploadImageCompress'))
        {
            $script_optimize = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD');
            $image='upload';
        }
        elseif(Tools::isSubmit('submitBrowseImageOptimize'))
        {
            $script_optimize = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE');
            $image='browse';
        }
        else
        {
            $script_optimize = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT_NEW');
            $image ='new';
        }
        if(!is_array($type))
        {
            $name = $type;
            $source = $path.$name;
            $destination = $path.$name;
            $temp = $path.'temp-'.$name;
        }
        else
        {
            $name = Tools::stripslashes($type['name']);
            $source = $path.'-'.$name.'.jpg';
            $destination = $path.'-'.$name.'.jpg';
            $temp = $path.'-'.$name.'-temp.jpg';
        }
        $file_size_old= Tools::ps_round(@filesize($source)/1024,2);
        if($quality >=100)
        {
            if(Configuration::get('ETS_SPEEP_RESUMSH')!=2)
                    Configuration::updateValue('ETS_SPEEP_RESUMSH',2);
            return array(
                'file_size' => $file_size_old,
                'optimize_type' => $script_optimize ? $script_optimize : 'google',
            );
        }
        if($this->checkOptimizeImageResmush() && $url_image && $quality<100 && $this->_resmush < 10 && !Tools::getValue('continue'))
        {
            $this->_errors=array();
        	if($file_size = $this->compressByReSmush($url_image,$quality,$temp,$destination,$file_size_old))
                return $file_size;
            else
            {
                $this->_resmush++;
                return false;
            }
        }
        if($script_optimize=='tynypng' && !Tools::getValue('continue'))
        { 
            $tynypng_api_keys = explode(';',Configuration::get('ETS_SPEED_API_TYNY_KEY'));
            if(Configuration::get('ETS_SP_ERRORS_TINYPNG'))
                $errors_api =  Tools::jsonDecode(Configuration::get('ETS_SP_ERRORS_TINYPNG'),true);
            else
                $errors_api = array();
            if($tynypng_api_keys)
            {
                foreach($tynypng_api_keys as $api_key)
                {
                    if(!isset($errors_api[$api_key]) || (isset($errors_api[$api_key]) && $errors_api[$api_key]<=5 ))
                    {
                        $this->_errors = array();
                        if($file_size = $this->compressByTyNyPNG($source,$api_key))
                        {
                            if(isset($errors_api[$api_key]) && $errors_api[$api_key]!=1)
                            {
                                $errors_api[$api_key]=1;
                                Configuration::updateValue('ETS_SP_ERRORS_TINYPNG', Tools::jsonEncode($errors_api));
                            }
                            return $file_size;
                        }
                        else
                        {
                            if(isset($errors_api[$api_key]))
                                $errors_api[$api_key]++;
                            else
                                $errors_api[$api_key]=1;
                            Configuration::updateValue('ETS_SP_ERRORS_TINYPNG', Tools::jsonEncode($errors_api));
                            return false;
                        }
                            
                    }
                }
            }           
        }
        if($script_optimize!='php' && (!Tools::getValue('continue') || Tools::getValue('continue_webp')) && ($image=='old' || $image=='upload' || $image=='browse'))
        {
            $mime_type= Tools::strtolower(mime_content_type($source));
            if($image=='upload')
                @unlink($source);
            if(($script_optimize=='google' || Tools::getValue('continue_webp')) && ($mime_type=='image/jpeg' || $mime_type=='image/png'))
            {
               die(
                    Tools::jsonEncode(
                        array(
                            'error' => $this->displayGoogleError($this->_errors,true),
                            'script_continue'=> 'php',
                        )
                    )
                ); 
            }

            die(
                Tools::jsonEncode(
                    array(
                        'error' => $this->_errors ? $this->displayError($this->_errors,true):$this->displayError($this->l('errors'),true),
                        'script_continue' => 'php', //$script_optimize=='google' || Tools::getValue('continue_webp') ? 'php' :'webp',
                    )
                )
            );
        }
        return $this->compressByPhp($path,$name,$source,$destination,$temp,$quality,$type,$file_size_old,$quality_old);
	}
    public function optimizeProductImage($all_type=false)
    {
        $quality=Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') ? Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') :50;
        $optmize_script= Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT');
        if($all_type)
            $types= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'image_type WHERE products=1');
        else
            $types= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'image_type WHERE products=1 AND  name IN ("'.implode('","',array_map('pSQL',explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE')))).'")');
        $ok=false;
        if($types)
        {
            foreach($types as $type)
            {
                if(Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) && $quality!=100)
                    $and_quality = ' AND pi.quality!=100';
                else
                    $and_quality = ($optmize_script !='tynypng'  || $quality==100 || !Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) ? ' AND pi.quality="'.(int)$quality.'"':' AND pi.quality!=100').($quality!=100 ? ' AND pi.optimize_type = "'.pSQL($optmize_script).'"':'');
                $images = Db::getInstance()->executeS('
                SELECT i.id_image FROM '._DB_PREFIX_.'image i
                LEFT JOIN '._DB_PREFIX_.'ets_superspeed_product_image pi ON i.id_image = pi.id_image AND pi.type_image="'.pSQL($type['name']).'"'.$and_quality.'
                WHERE pi.id_image is NULL LIMIT 0 ,'.(int)$this->number_optimize);
                if($images)
                {
                    
                    $ok=true;
                    foreach($images as $image)
                    {
                        $image_obj = new Image($image['id_image']);
                        $path = $image_obj->getPathForCreation();
                        foreach($types as $type)
                        {
                            if(Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) && $quality!=100)
                                $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE id_image = '.(int)$image['id_image'].' AND type_image="'.pSQL($type['name']).'" AND quality!=100';
                            else
                                $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE id_image = '.(int)$image['id_image'].' AND type_image="'.pSQL($type['name']).'"'.($optmize_script !='tynypng' || $quality==100 ? ' AND quality="'.(int)$quality.'"':' AND quality!=100').($quality!=100 ? ' AND optimize_type = "'.pSQL($optmize_script).'"':'');
                            if(!Db::getInstance()->getRow($sql))
                            {
                                $optimizied = (int)Db::getInstance()->getRow('SELECT id_image FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE id_image = '.(int)$image['id_image'].' AND type_image="'.pSQL($type['name']).'"');
								if($size_old = $this->createImage($path,$type,$optimizied))
                                {
                                    if($this->checkOptimizeImageResmush())
                                    {
                                        $product_class= new Product($image_obj->id_product,$this->context->language->id);
                                        $url_image= $this->context->link->getImageLink($product_class->link_rewrite,$image_obj->id,$type['name']);
                                    }
                                    else
                                        $url_image=null;
                                    $quality_old = Db::getInstance()->getValue('SELECT quality FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE id_image = '.(int)$image['id_image'].' AND type_image="'.pSQL($type['name']).'"');
                                    $compress = $this->compress($path,$type,$quality,$url_image,$quality_old);
                                    while($compress===false)
                                    {
										$compress = $this->compress($path,$type,$quality,$url_image,$quality_old);
									}
                                    if(!$optimizied)
                                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_product_image (id_image,type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$image['id_image'].'","'.pSQL($type['name']).'","'.(int)$quality.'","'.(float)$size_old.'","'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'","'.pSQL($compress['optimize_type']).'")');
                                    else
                                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_product_image SET quality ="'.(int)$quality.'",size_old="'.(float)$size_old.'",size_new ="'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'",optimize_type="'.pSQL($compress['optimize_type']).'" WHERE id_image ="'.(int)$image['id_image'].'" AND type_image ="'.pSQL($type['name']).'"');
                                }
                                else
                                {
                                    if(!$optimizied)
                                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_product_image (id_image,type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$image['id_image'].'","'.pSQL($type['name']).'","'.(int)$quality.'","0","0","'.($optmize_script ? pSQL($optmize_script):'php').'")');
                                    else
                                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_product_image SET quality ="'.(int)$quality.'",size_old="0",size_new ="0",optimize_type="'.($optmize_script ? pSQL($optmize_script):'php').'" WHERE id_image ="'.(int)$image['id_image'].'" AND type_image ="'.pSQL($type['name']).'"');
                                }
                                $this->_saveTotalImageOpimized($path.'-'.Tools::stripslashes($type['name']).'.jpg');
                            }
                            elseif(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') =='tynypng' && !Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE quality="'.(int)$quality.'" AND id_image ="'.(int)$image['id_image'].'" AND type_image ="'.pSQL($type['name']).'"' ))
                            {
                                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_product_image SET quality ="'.(int)$quality.'" WHERE id_image ="'.(int)$image['id_image'].'" AND type_image ="'.pSQL($type['name']).'"');
                                    $this->_saveTotalImageOpimized($path.'-'.Tools::stripslashes($type['name']).'.jpg');
                            }
                                
                        }
                    }
                }
                
            }
            
        }
        if($ok)
        {
           die(
                Tools::jsonEncode(
                    array(
                        'resume' => true,
                        'optimize_type' => 'products',
                        'limit_optimized' => 0,
                    )
                )  
            );
        }
        else
        {
            return true;
        }
    }
    public function optimiziObjImage($table,$type_obj,$path,$all_type=false,$next='')
    {
        $optmize_script= Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT');
        $quality=Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') ? Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') :90;
        if($all_type)
            $types = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'image_type WHERE '.pSQL($type_obj).'=1');
        else
            $types= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'image_type WHERE '.pSQL($type_obj).'=1 AND  name IN ("'.implode('","',array_map('pSQL',explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_'.Tools::strtoupper($table).'_TYPE')))).'")');
        $ok=false;
        if($types)
        {
            if($types)
            {
                foreach($types as $type)
                {
                    if(Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) && $quality!=100)
                        $and_quality = ' AND pi.quality!=100';
                    else
                        $and_quality = ($optmize_script !='tynypng'  || $quality==100 || !Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) ? ' AND pi.quality="'.(int)$quality.'"':' AND pi.quality!=100').($quality!=100 ? ' AND pi.optimize_type = "'.pSQL($optmize_script).'"':'');
                    $objects = Db::getInstance()->executeS('
                    SELECT o.id_'.pSQL($table).' FROM '._DB_PREFIX_.pSQL($table).' o
                    LEFT JOIN '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image pi ON o.id_'.pSQL($table).' = pi.id_'.pSQL($table).' AND pi.type_image="'.pSQL($type['name']).'"'.$and_quality.'
                    WHERE pi.id_'.pSQL($table).' is NULL LIMIT 0 ,'.(int)$this->number_optimize);
                    if($objects)
                    {
                        $ok=true;
                        foreach($objects as $object)
                        {
                            $path_image =$path.$object['id_'.$table];
                            if(Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) && $quality!=100)
                                $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type['name']).'" AND quality!=100';
                            else
                                $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type['name']).'"'.($optmize_script !='tynypng' || $quality==100 ? ' AND quality="'.(int)$quality.'"':' AND quality!=100').($quality!=100  ? ' AND optimize_type = "'.pSQL($optmize_script).'"':'');
                            if(!Db::getInstance()->getRow($sql))
                            {
                                $optimizied = Db::getInstance()->getValue('SELECT id_'.pSQL($table).' FROM '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type['name']).'"');
                                if($size_old = $this->createImage($path_image,$type,$optimizied))
                                {
                                    if($this->checkOptimizeImageResmush())
                                        $url_image= $this->getLinkTable($table).$object['id_'.$table].'-'.$type['name'].'.jpg';
                                    else
                                        $url_image=null;
                                    $quality_old = Db::getInstance()->getValue('SELECT quality FROM '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type['name']).'" AND optimize_type = "'.pSQL(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT')).'"');
                                    $compress= $this->compress($path_image,$type,$quality,$url_image,$quality_old);
                                    while($compress===false)
                                        $compress= $this->compress($path_image,$type,$quality,$url_image,$quality_old);
                                    if(!$optimizied)
                                    {
                                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image (id_'.$table.',type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$object['id_'.$table].'","'.pSQL($type['name']).'","'.(int)$quality.'","'.(float)$size_old.'","'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'","'.pSQl($compress['optimize_type']).'")');
                                    } 
                                    else
                                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image SET quality="'.(int)$quality.'",size_old="'.(float)$size_old.'",size_new="'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'",optimize_type="'.pSQL($compress['optimize_type']).'" WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type['name']).'"');
                                    $this->_saveTotalImageOpimized($path.'-'.Tools::stripslashes($type['name']).'.jpg');
                                }
                                else
                                {
                                    if(!$optimizied)
                                    {
                                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image (id_'.$table.',type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$object['id_'.$table].'","'.pSQL($type['name']).'","'.(int)$quality.'","0","0","'.pSQl($optmize_script).'")');
                                    } 
                                    else
                                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image SET quality="'.(int)$quality.'",size_old="0",size_new="0",optimize_type="'.pSQL($optmize_script).'" WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type['name']).'"');
                                }
                            }elseif(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') =='tynypng' && !Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image WHERE quality="'.(int)$quality.'" AND id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type['name']).'"'))
                            {
                                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_'.pSQL($table).'_image SET quality="'.(int)$quality.'" WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type['name']).'"');
                                $this->_saveTotalImageOpimized($path.'-'.Tools::stripslashes($type['name']).'.jpg');
                            }
                        }
                    }
                }
            }
        }
        unset($next);
        if($ok)
            die(
                Tools::jsonEncode(
                    array(
                        'resume' => true,
                        'optimize_type' => $type_obj,
                        'limit_optimized' => 0,
                    )
                )  
            );
        else
        {
            return true;
        }
    }
    public function optimiziBlogImage($table,$path,$all_type=false,$next='')
    {
        $quality=Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') ? Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') :90;
        $optmize_script = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT');
        if($all_type)
            if($table=='slide')
                $types=array('image');
            else                
                $types = array('image','thumb');
        else
            $types = explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_'.Tools::strtoupper($table).'_TYPE'));
        $ok= false;
        if($types)
        {
            foreach($types as $type)
            {
                if($type)
                {
                    if($type=='thumb')
                        $path .='thumb/';
                    if(Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY'))&& $quality!=100)
                        $end_quality = ' AND quality!=100';
                    else
                        $end_quality = ($optmize_script !='tynypng' || $quality==100 || !Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) ? ' AND quality="'.(int)$quality.'"':' AND quality!=100').($quality!=100 ? ' AND optimize_type = "'.pSQL($optmize_script).'"':'');
                    $objects = Db::getInstance()->executeS('SELECT bl.* FROM '._DB_PREFIX_.'ybc_blog_'.pSQL($table).' bl
                    LEFT JOIN '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image bli ON type_image="'.pSQL($type).'" AND bli.id_'.pSQL($table).'=bl.id_'.pSQL($table).$end_quality.'
                    WHERE bli.id_'.pSQL($table).' is NULL LIMIT 0,'.(int)$this->number_optimize);
                    if($objects)
                    {
                        $ok=true;
                        foreach($objects as $object)
                        {
                            if(Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) && $quality!=100)
                                $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type).'" AND quality!=100';
                            else
                                $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type).'"'.($optmize_script !='tynypng' || $quality==100 ? ' AND quality="'.(int)$quality.'"':' AND quality!=100').($quality!=100 ? ' AND optimize_type = "'.pSQL($optmize_script).'"':'');
                            if(!Db::getInstance()->getRow($sql))
                            {
                                
                                $optimizied = Db::getInstance()->getValue('SELECT id_'.pSQL($table).' FROM '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type).'"');
                                if($size_old= $this->createBlogImage($path,$object[$type]))
                                {
                                    if($this->checkOptimizeImageResmush())
                                        $url_image= $this->getLinkTable('blog_'.$table,$type).$object[$type];
                                    else
                                        $url_image=null;
                                    $compress= $this->compress($path,$object[$type],$quality,$url_image,false);
                                    while($compress===false)
                                        $compress= $this->compress($path,$object[$type],$quality,$url_image,false);
                                    if(!$optimizied)
                                    {
                                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image (id_'.pSQL($table).',type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$object['id_'.$table].'","'.pSQL($type).'","'.(int)$quality.'","'.(float)$size_old.'","'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'","'.pSQl($compress['optimize_type']).'")');
                                    } 
                                    else
                                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image SET quality="'.(int)$quality.'",size_old="'.(float)$size_old.'",size_new="'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'",optimize_type="'.pSQL($compress['optimize_type']).'" WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type).'"');
                                    $this->_saveTotalImageOpimized($path.$object[$type]);
                                }
                                else
                                {
                                    if(!$optimizied)
                                    {
                                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image (id_'.pSQL($table).',type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$object['id_'.$table].'","'.pSQL($type).'","'.(int)$quality.'","0","0","'.pSQl($optmize_script).'")');
                                    } 
                                    else
                                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image SET quality="'.(int)$quality.'",size_old="0",size_new="0",optimize_type="'.pSQL($optmize_script).'" WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type).'"');
                                }
                            }elseif(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') =='tynypng' && !Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image WHERE quality="'.(int)$quality.'" AND id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type).'"')) 
                            {
                                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image SET quality="'.(int)$quality.'" WHERE id_'.pSQL($table).' = '.(int)$object['id_'.$table].' AND type_image="'.pSQL($type).'"');
                                $this->_saveTotalImageOpimized($path.$object[$type]);
                            }
                        }
                    }
                }
            }
        }
        unset($next);
        if($ok)
            die(
                Tools::jsonEncode(
                    array(
                        'resume' => true,
                        'optimize_type' => $table,
                        'limit_optimized' => 0,
                    )
                )  
            );
        else
            return true;
    }
    public function optimiziSlideImage($all_type=false)
    {
        if($all_type || Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE'))
        {
            $limit = Tools::getValue('limit_optimized',0);
            $homeSlides = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'homeslider_slides_lang LIMIT '.(int)$limit.','.(int)$this->number_optimize);
            $quality=Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') ? Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') :90;
            $total_images = $this->getTotalImage('home_slide',true,false,false,$all_type) - $this->getTotalImage('home_slide',true,true,false,$all_type);
            if($homeSlides && $total_images >0 )
            {
                $path = _PS_MODULE_DIR_.($this->is17 ? 'ps_imageslider':'homeslider').'/images/';
                foreach($homeSlides as $homeSlide)
                {
                    if(Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) && $quality!=100)
                        $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_home_slide_image WHERE id_homeslider_slides = "'.(int)$homeSlide['id_homeslider_slides'].'" AND image="'.pSQL($homeSlide['image']).'" AND quality!=100';
                    else
                        $sql ='SELECT * FROM '._DB_PREFIX_.'ets_superspeed_home_slide_image WHERE id_homeslider_slides ="'.(int)$homeSlide['id_homeslider_slides'].'" AND image = "'.pSQL($homeSlide['image']).'"'.(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') !='tynypng' || $quality==100 ? ' AND quality="'.(int)$quality.'"':' AND quality!=100').($quality!=100 ? ' AND optimize_type = "'.pSQL(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT')).'"':'');
                    if(!Db::getInstance()->getRow($sql))
                    {
                        if($size_old= $this->createBlogImage($path,$homeSlide['image']))
                        {
                            if($this->checkOptimizeImageResmush())
                                $url_image= $this->getBaseLink().'/modules/'.($this->is17 ? 'ps_imageslider':'homeslider').'/images/'.$homeSlide['image'];
                            else
                                $url_image=null;
                            $compress= $this->compress($path,$homeSlide['image'],$quality,$url_image,false);
                            while($compress===false)
                                $compress= $this->compress($path,$homeSlide['image'],$quality,$url_image,false);
                            if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_home_slide_image WHERE id_homeslider_slides="'.(int)$homeSlide['id_homeslider_slides'].'" AND image="'.pSQL($homeSlide['image']).'"'))
                            {
                                Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_home_slide_image (id_homeslider_slides,image,type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$homeSlide['id_homeslider_slides'].'","'.pSQL($homeSlide['image']).'", "image","'.(int)$quality.'","'.(float)$size_old.'","'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'","'.pSQl($compress['optimize_type']).'")');
                            } 
                            else
                                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_home_slide_image SET quality="'.(int)$quality.'",size_old="'.(float)$size_old.'",size_new="'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'",optimize_type="'.pSQL($compress['optimize_type']).'" WHERE id_homeslider_slides="'.(int)$homeSlide['id_homeslider_slides'].'" AND image="'.pSQL($homeSlide['image']).'"');
                            $this->_saveTotalImageOpimized($path.$homeSlide['image']);
                        }
                    }elseif(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') =='tynypng' && !Db::getInstance()->getRow('SELECT *FROM '._DB_PREFIX_.'ets_superspeed_home_slide_image WHERE quality="'.(int)$quality.'" AND id_homeslider_slides="'.(int)$homeSlide['id_homeslider_slides'].'" AND image="'.pSQL($homeSlide['image']).'"'))
                    {
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_home_slide_image SET quality="'.(int)$quality.'" WHERE id_homeslider_slides="'.(int)$homeSlide['id_homeslider_slides'].'"  AND image="'.pSQL($homeSlide['image']).'"');
                        $this->_saveTotalImageOpimized($path.$homeSlide['image']);
                    }  
                }
                die(
                    Tools::jsonEncode(
                        array(
                            'resume' => true,
                            'optimize_type' => 'home_slide',
                            'limit_optimized' => $limit+$this->number_optimize,
                        )
                    )  
                );
            }
            if($total_images>0)
            {
                Tools::jsonEncode(
                    array(
                        'resume' => true,
                        'optimize_type' => 'other_image',
                        'limit_optimized' => 0,
                    )
                );
            }
            else
            {
                $_POST['limit_optimized'] =0;
                return true;
            }
        }
        else
        Tools::jsonEncode(
            array(
                'resume' => true,
                'optimize_type' => 'other_image',
                'limit_optimized' => 0,
            )
        );
    }
    public function optimiziOthersImage($all_type)
    {
        $quality=Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') ? Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE') :90;
        if($all_type)
            $types = array('logo','banner');
        else
            $types = explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE'));
        if($types && $this->getTotalImage('others',true,false,false,$all_type) - $this->getTotalImage('others',true,true,false,$all_type)>0)
        {
            foreach($types as $type)
            {
                $images = array();
                if($type=='logo')
                {
                    if(Configuration::get('PS_LOGO'))
                        $images[] = Configuration::get('PS_LOGO');
                    $path = _PS_IMG_DIR_;
                }elseif($type=='banner')
                {
                    $languages = Language::getLanguages(false);
                    if($this->is17)
                    {
                        $path = _PS_MODULE_DIR_.'ps_banner/img/';
                        if(module::isInstalled('ps_banner') && Module::isEnabled('ps_banner'))
                        {
                            foreach($languages as $language)
                            {
                                if(($image = Configuration::get('BANNER_IMG',$language['id_lang'])) && !in_array($image,$images))
                                    $images[] = $image;
                            }
                        }
                    }
                    else
                    {
                        $path = _PS_MODULE_DIR_.'blockbanner/img/';
                        if(module::isInstalled('blockbanner') && Module::isEnabled('blockbanner'))
                        {
                            foreach($languages as $language)
                            {
                                if(($image = Configuration::get('BLOCKBANNER_IMG',$language['id_lang'])) && !in_array($image,$images))
                                    $images[] = $image;
                            }
                        }
                    }
                }
                elseif($type=='themeconfig')
                {
                    $path = _PS_MODULE_DIR_.'themeconfigurator/img/';
                    if(Module::isInstalled('themeconfigurator') && Module::isEnabled('themeconfigurator'))
                    {
                        $themeconfigurators = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'themeconfigurator WHERE image!="" GROUP BY image');
                        if($themeconfigurators)
                        {
                            foreach($themeconfigurators as $themeconfigurator)
                                $images[]= $themeconfigurator['image'];
                        }
                    }
                }
                if($images)
                {
                    foreach($images as $image)
                    {
                        if(Tools::getValue('ETS_SPEED_UPDATE_QUALITY',Configuration::get('ETS_SPEED_UPDATE_QUALITY')) && $quality!=100)
                            $sql = 'SELECT * FROM '._DB_PREFIX_.'ets_superspeed_others_image WHERE image = "'.pSQL($image).'" AND type_image="'.pSQL($type).'" AND quality!=100';
                        else
                            $sql ='SELECT * FROM '._DB_PREFIX_.'ets_superspeed_others_image WHERE image="'.pSQL($image).'" AND type_image="'.pSQL($type).'"'.(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') !='tynypng' || $quality==100  ? ' AND quality="'.(int)$quality.'"':' AND quality!=100').($quality!=100 ? ' AND optimize_type = "'.pSQL(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT')).'"':'');    
                        if(!Db::getInstance()->getRow($sql))
                        {
                            if($size_old= $this->createBlogImage($path,$image))
                            {
                                if($this->checkOptimizeImageResmush())
                                    if($type=='logo')
                                        $url_image = $this->getBaseLink().'/'.$image;
                                    
                                    elseif($type=='banner')
                                    {
                                        $url_image = $this->getBaseLink().'/modules/'.($this->is17 ? 'ps_banner' : 'blockbanner').'/img/'.$image;
                                    }
                                    elseif($type=='themeconfig')
                                    {
                                        $url_image = $this->getBaseLink().'/modules/themeconfigurator/img/'.$image;
                                    }
                                else
                                    $url_image=null;
                                $optimizied = Db::getInstance()->getValue('SELECT image FROM '._DB_PREFIX_.'ets_superspeed_others_image WHERE image="'.pSQL($image).'" AND type_image="'.pSQL($type).'"');
                                $compress= $this->compress($path,$image,$quality,$url_image,false);
                                while($compress===false)
                                    $compress= $this->compress($path,$image,$quality,$url_image,false);
                                if(!$optimizied)
                                {
                                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_others_image (image,type_image,quality,size_old,size_new,optimize_type) VALUES("'.pSQL($image).'","'.pSQL($type).'","'.(int)$quality.'","'.(float)$size_old.'","'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'","'.pSQl($compress['optimize_type']).'")');
                                } 
                                else
                                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_others_image SET quality="'.(int)$quality.'",size_old="'.(float)$size_old.'",size_new="'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'",optimize_type="'.pSQL($compress['optimize_type']).'" WHERE image="'.pSQL($image).'" AND type_image="'.pSQL($type).'"');
                                $this->_saveTotalImageOpimized($path.$image);
                            }
                        }elseif(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') =='tynypng' && !Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_others_image WHERE quality="'.(int)$quality.'" AND image="'.pSQL($image).'" AND type_image="'.pSQL($type).'"'))
                        {
                            Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_others_image SET quality="'.(int)$quality.'" WHERE image="'.pSQL($image).'" AND type_image="'.pSQL($type).'"');
                             $this->_saveTotalImageOpimized($path.$image);
                        }
                            
                    }   
                }
            }
        }
    }
    public function displaySuccessMessage($msg, $title = false, $link = false)
    {
         $this->smarty->assign(array(
            'msg' => $msg,
            'title' => $title,
            'link' => $link
         ));
         if($msg)
            return $this->display(__FILE__, 'success_message.tpl');
    }
    public function _postPageCache()
    {
        if(Tools::isSubmit('btnSubmitSuperSpeedException'))
        {
            if(Validate::isCleanHtml(Tools::getValue('ETS_SPEED_PAGES_EXCEPTION')))
            {
                Configuration::updateValue('ETS_SPEED_PAGES_EXCEPTION',Tools::getValue('ETS_SPEED_PAGES_EXCEPTION'));
                if($pages_exception = Configuration::get('ETS_SPEED_PAGES_EXCEPTION'))
                {
                    $pages_exception = explode("\n",$pages_exception);
                    foreach($pages_exception as $page_exception)
                    {
                        if($page_exception)
                            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE request_uri like "%'.pSQL($page_exception).'%"');
                    }
                }
                die(
                    Tools::jsonEncode(
                        array(
                            'success' => $this->l('Updated successfully')
                        )
                    )
                );
            }
            else
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'error' => $this->l('exception is not valid'),
                        )
                    )
                );
            }
        }
        if(Tools::isSubmit('btnSubmitPageCache'))
        {
            if(Tools::getValue('live_script') && (Tools::strpos(Tools::getValue('live_script'),'<script')!==false || Tools::strpos(Tools::getValue('live_script'),'script>')!==false))
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'errors' => $this->displayError($this->l('Please enter JavaScript code without "script" tag. The tag will be automatically embedded to your code.')),
                        )
                    )
                );
            }
            Configuration::updateValue('ETS_SPEED_ENABLE_PAGE_CACHE',Tools::getValue('ETS_SPEED_ENABLE_PAGE_CACHE'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_INDEX',Tools::getValue('ETS_SPEED_TIME_CACHE_INDEX'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_CATEGORY',Tools::getValue('ETS_SPEED_TIME_CACHE_CATEGORY'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_CMS',Tools::getValue('ETS_SPEED_TIME_CACHE_CMS'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_PRODUCT',Tools::getValue('ETS_SPEED_TIME_CACHE_PRODUCT'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_NEWPRODUCTS',Tools::getValue('ETS_SPEED_TIME_CACHE_NEWPRODUCTS'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_BESTSALES',Tools::getValue('ETS_SPEED_TIME_CACHE_BESTSALES'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_SUPPLIER',Tools::getValue('ETS_SPEED_TIME_CACHE_SUPPLIER'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_MANUFACTURER',Tools::getValue('ETS_SPEED_TIME_CACHE_MANUFACTURER'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_CONTACT',Tools::getValue('ETS_SPEED_TIME_CACHE_CONTACT'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_PRICESDROP',Tools::getValue('ETS_SPEED_TIME_CACHE_PRICESDROP'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_SITEMAP',Tools::getValue('ETS_SPEED_TIME_CACHE_SITEMAP'));
            Configuration::updateValue('ETS_SPEED_TIME_CACHE_BLOG',Tools::getValue('ETS_SPEED_TIME_CACHE_BLOG'));
            Configuration::updateValue('ETS_SPEED_COMPRESS_CACHE_FIIE',Tools::getValue('ETS_SPEED_COMPRESS_CACHE_FIIE'));
            Configuration::updateValue('ETS_RECORD_PAGE_CLICK',Tools::getValue('ETS_RECORD_PAGE_CLICK'));
            Configuration::updateValue('ETS_SPEED_CHECK_USER_AGENT',Tools::getValue('ETS_SPEED_CHECK_USER_AGENT'));
            $page_cache_old = Configuration::get('ETS_SPEED_PAGES_TO_CACHE');
            if(Tools::getValue('ETS_SPEED_PAGES_TO_CACHE'))
                Configuration::updateValue('ETS_SPEED_PAGES_TO_CACHE',implode(',',Tools::getValue('ETS_SPEED_PAGES_TO_CACHE')));
            else
                Configuration::updateValue('ETS_SPEED_PAGES_TO_CACHE','');
            if(Tools::getValue('ETS_SPEED_AUTO_CACHE') && !Configuration::get('ETS_SPEED_AUTO_CACHE'))
                Configuration::updateValue('ETS_SPEED_CACHE_TIME',strtotime("now"));
            Configuration::updateValue('ETS_SPEED_AUTO_CACHE',Tools::getValue('ETS_SPEED_AUTO_CACHE'));
            if($page_cache_old!= Configuration::get('ETS_SPEED_PAGES_TO_CACHE'))
            {
                if(!$this->_class_ss_cache)
                    $this->getClassCahe();
                $this->_class_ss_cache->deleteCache();
            }
            if(Tools::getValue('live_script'))
            {
                file_put_contents(dirname(__FILE__).'/views/js/script_custom.js',Tools::getValue('live_script'));
            }
            elseif(file_exists(dirname(__FILE__).'/views/js/script_custom.js'))
                @unlink(dirname(__FILE__).'/views/js/script_custom.js');
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Successfully saved')),
                    )
                )
            );
        }
        if($id_cache_page= (int)Tools::getValue('downloadcache'))
        {
            $file_cache= Db::getInstance()->getValue('SELECT file_cache FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE id_shop="'.(int)$this->context->shop->id.'" AND id_cache_page='.(int)$id_cache_page);
            if($file_cache && file_exists($file_cache))
            {
                $ext = Tools::strtolower(Tools::substr(strrchr($file_cache, '.'), 1));
                switch ($ext) {
        			case "html": $ctype="application/html"; break;
        			case "zip": $ctype="application/zip"; break;
        			default: $ctype="application/force-download";
        		}
                header("Pragma: public"); // required
        		header("Expires: 0");
        		header("X-Robots-Tag: noindex, nofollow", true);
        		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        		header("Cache-Control: private",false); // required for certain browsers
        		header("Content-Type: $ctype");
        		header("Content-Disposition: attachment; filename=\"".basename($file_cache)."\";" );
        		header("Content-Transfer-Encoding: Binary");
        		if ($fsize = @filesize($file_cache)) {
        			header( "Content-Length: ".$fsize);
        		}
        		ob_clean();
        		flush();
        		readfile($file_cache);
                exit();
            }
        }
        if(Tools::isSubmit('clear_all_page_caches'))
        {
            if(!$this->_class_ss_cache)
                $this->getClassCahe();
            $this->_class_ss_cache->deleteCache();
            Tools::clearSmartyCache();
            Tools::clearXMLCache();
            Media::clearCache();
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Cache cleared successfully')),
                    )
                )
            );
        }
        if(Tools::isSubmit('btnSubmitPageCacheDashboard'))
        {
            if(!Tools::isSubmit('resume'))
            {
                Configuration::updateValue('ETS_SP_TOTAL_IMAGE_OPTIMIZED',0);
                if(Tools::getValue('smarty_cache'))
                {
                    if(Configuration::get('PS_SMARTY_FORCE_COMPILE')==2)
                        Configuration::updateValue('PS_SMARTY_FORCE_COMPILE',0);
                }
                else
                    Configuration::updateValue('PS_SMARTY_FORCE_COMPILE',2);
                if(Tools::getValue('server_cache'))
                {
                    if(!Configuration::get('PS_SMARTY_CACHE'))
                    {
                        Configuration::updateValue('PS_SMARTY_CACHE',1);
                        Configuration::updateValue('PS_SMARTY_CACHING_TYPE','filesystem');
                        Configuration::updateValue('PS_SMARTY_CLEAR_CACHE','everytime');
                    }
                }
                else
                    Configuration::updateValue('PS_SMARTY_CACHE',0);
                if(Tools::getValue('minify_html'))
                    Configuration::updateValue('PS_HTML_THEME_COMPRESSION',1);
                else
                    Configuration::updateValue('PS_HTML_THEME_COMPRESSION',0);
                if(Tools::getValue('minify_javascript'))
                    Configuration::updateValue('PS_JS_THEME_CACHE',1);
                else
                    Configuration::updateValue('PS_JS_THEME_CACHE',0);
                if(Tools::getValue('minify_css'))
                    Configuration::updateValue('PS_CSS_THEME_CACHE',1);
                else
                    Configuration::updateValue('PS_CSS_THEME_CACHE',0);
                if(Tools::getValue('page_cache'))
                {
                     Configuration::updateValue('ETS_SPEED_ENABLE_PAGE_CACHE',1);
                     if(!Configuration::get('ETS_SPEED_PAGES_TO_CACHE'))
                     {
                        $page_caches= 'index,category,product,cms,newproducts,bestsales,supplier,manufacturer,contact,pricesdrop,sitemap,blog';
                        Configuration::updateValue('ETS_SPEED_PAGES_TO_CACHE',$page_caches);
                     }
                }
                else
                {
                    Configuration::updateValue('ETS_SPEED_ENABLE_PAGE_CACHE',0);
                    Configuration::updateValue('ETS_SPEED_PAGES_TO_CACHE','');
                }    
                if(Tools::getValue('browser_cache'))
                {
                    Configuration::updateValue('PS_HTACCESS_CACHE_CONTROL',1);
                    if(version_compare(_PS_VERSION_, '1.7', '>='))
                    {
                        call_user_func('Ets_generateHtaccess17');
                    }
                    else
                        call_user_func('Ets_generateHtaccess16');
                }
                else
                {
                    Configuration::updateValue('PS_HTACCESS_CACHE_CONTROL',0);
                    if(version_compare(_PS_VERSION_, '1.7', '>='))
                    {
                        call_user_func('Ets_generateHtaccess17');
                    }
                    else
                        call_user_func('Ets_generateHtaccess16');
                }
                $this->updateDebugModeValueInCustomFile(Tools::getValue('production_mode') ? 'false':'true');
                if(Tools::getValue('optimize_newly_images'))
                    Configuration::updateValue('ETS_SPEED_OPTIMIZE_NEW_IMAGE',1);
                else
                    Configuration::updateValue('ETS_SPEED_OPTIMIZE_NEW_IMAGE',0);
                if(Tools::getValue('lazy_load'))
                {
                    Configuration::updateValue('ETS_SPEED_ENABLE_LAYZY_LOAD',1);
                    if(!Configuration::get('ETS_SPEED_LAZY_FOR'))
                    {
                        Configuration::updateValue('ETS_SPEED_LAZY_FOR','product_list,home_slide,home_banner');
                    }
                }
                else
                {
                    Configuration::updateValue('ETS_SPEED_ENABLE_LAYZY_LOAD',0);
                }
                $this->replaceTemplateProductDefault(true);
            }
            if(Tools::getValue('percent_unoptimized_images') && Tools::getValue('optimize_existing_images'))
            {
                if(Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE')==100)
                    Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE',50);
                $this->ajaxSubmitOptimizeImage(true);
            }
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Configured successfully')),
                        'total_image_optimized_size' => $this->getTotalSizeSave(),
                    )
                )
            );
        }
        if(Tools::isSubmit('btnSubmitDisabledPageCacheDashboard'))
        {
            Configuration::updateValue('PS_SMARTY_FORCE_COMPILE',2);
            Configuration::updateValue('PS_SMARTY_CACHE',0);
            Configuration::updateValue('PS_HTML_THEME_COMPRESSION',0);
            Configuration::updateValue('PS_JS_THEME_CACHE',0);
            Configuration::updateValue('PS_CSS_THEME_CACHE',0);
            Configuration::updateValue('ETS_SPEED_ENABLE_PAGE_CACHE',0);
            Configuration::updateValue('PS_HTACCESS_CACHE_CONTROL',0);
            Configuration::updateValue('ETS_SPEED_PAGES_TO_CACHE','');
            if(version_compare(_PS_VERSION_, '1.7', '>='))
            {
                call_user_func('Ets_generateHtaccess17');
            }
            else
                call_user_func('Ets_generateHtaccess16');
            
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Successfully disable cache')),
                    )
                )
            );
        }
        if((Tools::getValue('action')=='add_dynamic_modules' || Tools::getValue('action')=='update_dynamic_modules') && Tools::getValue('hook_name'))
        {
            if(!$this->_class_ss_cache)
                $this->getClassCahe();
            $this->_class_ss_cache->deleteCache('',0,Tools::getValue('hook_name'));
            if(Tools::getValue('add') || Tools::getValue('action')=='update_dynamic_modules')
            {
                if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_dynamic WHERE id_module="'.(int)Tools::getValue('id_module').'" AND hook_name="'.pSQL(Tools::getValue('hook_name')).'"'))
                {
                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_dynamic (id_module,hook_name,empty_content) VALUES("'.(int)Tools::getValue('id_module').'","'.pSQL(Tools::getValue('hook_name')).'","'.(int)Tools::getValue('empty_content').'")');
                }
                else
                {
                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_dynamic SET empty_content="'.(int)Tools::getValue('empty_content').'" WHERE id_module="'.(int)Tools::getValue('id_module').'" AND hook_name="'.pSQL(Tools::getValue('hook_name')).'"');
                }
                if(Tools::getValue('action')=='add_dynamic_modules')
                {
                    die(
                        Tools::jsonEncode(
                            array(
                                'success' => $this->displaySuccessMessage($this->l('Successfully saved')),
                            )
                        )
                    );
                }
                elseif(Tools::getValue('action')=='update_dynamic_modules')
                {
                    die(
                        Tools::jsonEncode(
                            array(
                                'success' => $this->displaySuccessMessage($this->l('Updated successfully')),
                            )
                        )
                    );
                }
            }
            else
            {
                Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ets_superspeed_dynamic WHERE id_module="'.(int)Tools::getValue('id_module').'" AND hook_name="'.pSQL(Tools::getValue('hook_name')).'"');
                die(
                    Tools::jsonEncode(
                        array(
                            'success' => $this->displaySuccessMessage($this->l('Updated successfully')),
                        )
                    )
                );
            }
        }
        if(Tools::isSubmit('btnRefreshCachePageNew'))
        {
            $file_caches= Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'ets_superspeed_cache_page` WHERE id_shop="'.(int)$this->context->shop->id.'" AND  request_uri not like "%check_speed=1%" ORDER BY date_upd desc LIMIT 0,10');
            if($file_caches)
            {
                foreach($file_caches as &$file_cache)
                {
                    $file_cache['basename'] = basename($file_cache['file_cache']);
                    if($file_cache['file_size']==0)
                    {
                        $file_cache['file_size'] = Tools::ps_round(@filesize($file_cache['file_cache'])/1024,2);
                    }
                    if(Tools::strlen($file_cache['request_uri'])>26)
                        $file_cache['name_display'] = Tools::substr($file_cache['request_uri'],0,13).' . . . '.Tools::substr($file_cache['request_uri'],Tools::strlen($file_cache['request_uri'])-13);
                }
            }
            $total_cache = Db::getInstance()->getValue('SELECT SUM(file_size) FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE id_shop='.(int)$this->context->shop->id);
            if($total_cache <1024)
                $total_text ='KB';
            else
            {
                $total_cache = $total_cache/1024;
                if($total_cache<1024)
                    $total_text='Mb';
                else
                {
                    $total_cache= $total_cache/1024;
                    $total_text='Gb';
                }
            }
            $this->context->smarty->assign(
                array(
                    'file_caches'=> $file_caches,
                    'cache_url_ajax' => $this->context->link->getAdminLink('AdminSuperSpeedPageCaches'),
                    'total_cache' => $total_cache ? $total_cache.$total_text :'',
                )
            );
            die(
                Tools::jsonEncode(
                    array(
                        'file_caches' => $this->display(__FILE__,'file_caches.tpl'),
                        'total_cache' => $total_cache ? $total_cache.$total_text :'',
                    )
                )
            );
        }
        if(Tools::isSubmit('btnRefreshSystemAnalyticsNew'))
        {
            $check_points = array();
            $total_point = (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ets_superspeed_hook_time pht
            INNER JOIN '._DB_PREFIX_.'hook h ON (pht.hook_name = h.name)
            INNER JOIN '._DB_PREFIX_.'hook_module hm ON (hm.id_hook=h.id_hook AND hm.id_module=pht.id_module)
            WHERE hm.id_shop="'.(int)$this->context->shop->id.'" AND pht.time >1');
            $check_points[] = array(
                'check_point' => $this->l('Number of module hooks have execution time greater than 1000 ms'),
                'number_data' => $total_point ,
                'status' =>$total_point ? $this->l('Bad') : $this->l('Good'),
                'class_status' => $total_point ? 'status-bab' :'status-good',
            );
            $this->context->smarty->assign(
                array(
                    'check_points' => array_merge($check_points,$this->getCheckPoints(false))
                )
            );
            die(
                Tools::jsonEncode(
                    array(
                        'check_points' => $this->display(__FILE__,'check_points.tpl'),
                    )
                )
            );
        }
        return true;
    }
    public function renderFormPageCache()
    {
        $defines = new Ets_superspeed_defines();
        $pages = array(
        array(
            'id'=>'index',
            'label'=>$this->l('Home page'),
            'value'=>'index',
            'extra'=>'ETS_SPEED_TIME_CACHE_INDEX'
        ),
        array(
            'id'=>'category',
            'label'=>$this->l('Category page'),
            'value'=>'category',
            'extra'=>'ETS_SPEED_TIME_CACHE_CATEGORY'
        ),
        array(
            'id'=>'product',
            'label'=>$this->l('Product page'),
            'value'=>'product',
            'extra'=>'ETS_SPEED_TIME_CACHE_PRODUCT'
        ),
        array(
            'id'=>'cms',
            'label'=>$this->l('CMS page'),
            'value'=>'cms',
            'extra'=>'ETS_SPEED_TIME_CACHE_CMS',
        ),
        array(
            'id'=>'newproducts',
            'label'=>$this->l('New product page'),
            'value'=>'newproducts',
            'extra'=>'ETS_SPEED_TIME_CACHE_NEWPRODUCTS',
        ),
        array(
            'id'=>'bestsales',
            'label'=>$this->l('Best sales page'),
            'value'=>'bestsales',
            'extra'=>'ETS_SPEED_TIME_CACHE_BESTSALES',
        ),
        array(
            'id'=>'supplier',
            'label'=>$this->l('Supplier page'),
            'value'=>'supplier',
            'extra'=>'ETS_SPEED_TIME_CACHE_SUPPLIER',
        ),
        array(
            'id'=>'manufacturer',
            'label'=>$this->l('Manufacturer page'),
            'value'=>'manufacturer',
            'extra'=>'ETS_SPEED_TIME_CACHE_MANUFACTURER',
        ),
        array(
            'id'=>'contact',
            'label'=>$this->l('Contact page'),
            'value'=>'contact',
            'extra'=>'ETS_SPEED_TIME_CACHE_CONTACT',
        ),
        array(
            'id'=>'pricesdrop',
            'label'=>$this->l('Prices drop page'),
            'value'=>'pricesdrop',
            'extra'=>'ETS_SPEED_TIME_CACHE_PRICESDROP',
        ),
        array(
            'id'=>'sitemap',
            'label'=>$this->l('Sitemap page'),
            'value'=>'sitemap',
            'extra'=>'ETS_SPEED_TIME_CACHE_SITEMAP',
        ));
        if($this->isblog)
        {
            $pages[] = array(
                'id' =>'blog',
                'label' => $this->l('Blog pages'),
                'value' => 'blog',
                'extra'=>'ETS_SPEED_TIME_CACHE_BLOG',
            );
        }
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Page cache'),
					'icon' => 'icon-envelope'
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Page cache'),
						'name' => 'ETS_SPEED_ENABLE_PAGE_CACHE',
                        'form_group_class'=>'form_cache_page page_setting',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Compress cache file'),
						'name' => 'ETS_SPEED_COMPRESS_CACHE_FIIE',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
                        'desc'=> $this->l('Compress HTML cache files into .zip files, this helps save your disk space but page loading time will be a bit longer (because server needs to unzip compressed files before displaying them to website visitors)'),
                        'form_group_class'=>'form_cache_page page_setting',
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Auto refresh cache'),
						'name' => 'ETS_SPEED_AUTO_CACHE',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
                        'desc'=>$this->l('Cache files can be automatically regenerated via cron job for smoother user experience on the front office. See how to setup cronjob on "Help" tab.'),
                        'form_group_class'=>'form_cache_page page_setting',
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Generate particular page cache for each user-agent'),
						'name' => 'ETS_SPEED_CHECK_USER_AGENT',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
                        'desc' => $this->l('Enable this if your website has particular views for desktop and mobile'),
                        'form_group_class'=>'form_cache_page page_setting',
					),
                    array(
                        'type'=>'checkbox',
                        'label' => $this->l('Pages to cache'),
                        'name'=>'ETS_SPEED_PAGES_TO_CACHE',
                        'form_group_class'=>'form_cache_page page_setting',
                        'values' => array(
                             'query' => $pages, 
                             'id' => 'value',
                             'name' => 'label' ,
                                                                                           
                        ),
                    ),
                    array(
						'type' => 'switch',
						'label' => $this->l('Record page clicks'),
						'name' => 'ETS_RECORD_PAGE_CLICK',
						'values' => array(
        					array(
        						'id' => 'active_on',
        						'value' => 1,
        						'label' => $this->l('On')
        					),
        					array(
        						'id' => 'active_off',
        						'value' => 0,
        						'label' => $this->l('Off')
        					)
        				),
                        'desc'=>$this->l('Enable this option to see how many times a page cache is used'),
                        'form_group_class'=>'form_cache_page page_setting',
					),
                    array(
                        'type'=>'buttons',
                        'buttons'=>array(
                            array(
                                'type'=>'button',
                                'name'=>'btnSubmitPageCache',
                                'title' =>$this->l('Save'),
                                'icon'=>'process-icon-save',
                                'class'=>'pull-right',
                            ),
                            array(
                                'type'=>'button',
                                'name'=>'clear_all_page_caches',
                                'title' =>$this->l('Clear all page caches'),
                                'icon'=>'icon-trash',
                                'class'=>'pull-left',
                            ),
                        ),
                        'name'=>'',  
                        'form_group_class'=>'form_cache_page page_setting group-button',
                    ),
                    array(
                        'type'=>'textarea',
                        'name'=>'ETS_SPEED_PAGES_EXCEPTION',
                        'label'=> $this->l('URL exception(s)'),
                        'row'=>'4',
                        'desc' => $this->l('Any URL containing at least 1 string entered above will not be cached. Please enter each string on 1 line.'),
                        'form_group_class'=>'form_cache_page dynamic_contents url_exceptions',
                    ),
                    array(
                        'type'=>'buttons',
                        'buttons'=>array(
                            array(
                                'type'=>'button',
                                'name'=>'btnSubmitSuperSpeedException',
                                'title' =>$this->l('Save'),
                                'icon'=>'icon-save',
                                'class'=>'pull-left',
                            ),
                        ),
                        'name'=>'',  
                        'form_group_class'=>'form_cache_page dynamic_contents group-button button_border_bottom',
                    ),
                    array(
                        'type'=>'list_module',
                        'name'=>'dynamic_modules',
                        'modules'=> $this->getModulesDynamic(),
                        'form_group_class'=>'form_cache_page dynamic_contents',
                    ),
                    array(
                        'label'=>$this->l('Live JavaScript'),
                        'type'=>'textarea',
                        'name'=>'live_script',
                        'rows'=>32,
                        'form_group_class'=>'form_cache_page livescript',
                        'desc'=> $this->l('Enter here custom JavaScript code that you need to execute after non-cached content are fully loaded. Be careful with your code, invalid JavaScript code may result in global JavaScript errors on the front office.'),
                    ),
                    array(
                        'type'=>'buttons',
                        'buttons'=>array(
                            array(
                                'type'=>'button',
                                'name'=>'btnSubmitPageCache',
                                'title' =>$this->l('Save'),
                                'icon'=>'process-icon-save',
                                'class'=>'pull-right',
                            ),
                        ),
                        'name'=>'',  
                        'form_group_class'=>'form_cache_page livescript group-button',
                    ),
                    
				),
			),
		);
        if(!is_dir(_ETS_SPEED_CACHE_DIR_))
            mkdir(_ETS_SPEED_CACHE_DIR_,0777,true);
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmitPageCache';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminSuperSpeedPageCaches',false);
		$helper->token = Tools::getAdminTokenLite('AdminSuperSpeedPageCaches');
        $helper->module = $this;
        $install_logs= file_exists(dirname(__FILE__).'/cache/install.log') ? array_keys(Tools::jsonDecode(Tools::file_get_contents(dirname(__FILE__).'/cache/install.log'),true)):false;
		if($install_logs)
        {
            foreach($install_logs as $key=>$log)
                if(in_array($log,array('AdminCategoriesController','AdminManufacturersController','AdminSuppliersController')))
                    unset($install_logs[$key]);
                else
                    $install_logs[$key] .='.php';
        }
        $helper->tpl_vars = array(
            'file_caches' => $this->displayPageCaches(),
			'fields_value' => $this->getCachePageFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
            'configTabs' => $defines->_cache_page_tabs,
            'current_tab'=> Tools::getValue('current_tab','page_setting'),
            'is_dir_cache' => is_dir(_ETS_SPEED_CACHE_DIR_),
            'dir_cache' => _PS_CACHE_DIR_,
            'sp_dir_cache' => _ETS_SPEED_CACHE_DIR_,
            'dir_override' => _PS_OVERRIDE_DIR_,
            'sp_dir_override' => dirname(__FILE__).'/override/',
            'is_blog_installed' => $this->isblog,
            'install_log_file_url' => dirname(__FILE__).'/cache/install.log',
            'install_logs' => $install_logs ? implode(', ',$install_logs) : false,
            'xcronjob_auto_no_run' => Configuration::get('ETS_SPEED_AUTO_CACHE') && Configuration::get('ETS_SPEED_CACHE_TIME') && Configuration::get('ETS_SPEED_CACHE_TIME') < strtotime("-1 day"),
		);
		return $helper->generateForm(array($fields_form));
    }
    public function getCachePageFieldsValues()
    {
        return array(
            'ETS_SPEED_ENABLE_PAGE_CACHE' => Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE'),
            'ETS_SPEED_COMPRESS_CACHE_FIIE' => Configuration::get('ETS_SPEED_COMPRESS_CACHE_FIIE'),
            'ETS_SPEED_AUTO_CACHE' => Configuration::get('ETS_SPEED_AUTO_CACHE'),
            'ETS_SPEED_TIME_CACHE_INDEX' => Configuration::get('ETS_SPEED_TIME_CACHE_INDEX'),
            'ETS_SPEED_TIME_CACHE_CATEGORY' => Configuration::get('ETS_SPEED_TIME_CACHE_CATEGORY'),
            'ETS_SPEED_TIME_CACHE_PRODUCT' => Configuration::get('ETS_SPEED_TIME_CACHE_PRODUCT'),
            'ETS_SPEED_TIME_CACHE_CMS' => Configuration::get('ETS_SPEED_TIME_CACHE_CMS'),
            'ETS_SPEED_PAGES_EXCEPTION' => Configuration::get('ETS_SPEED_PAGES_EXCEPTION'),
            'ETS_SPEED_TIME_CACHE_NEWPRODUCTS' => Configuration::get('ETS_SPEED_TIME_CACHE_NEWPRODUCTS'),
            'ETS_SPEED_TIME_CACHE_BESTSALES' => Configuration::get('ETS_SPEED_TIME_CACHE_BESTSALES'),
            'ETS_SPEED_TIME_CACHE_SUPPLIER' => Configuration::get('ETS_SPEED_TIME_CACHE_SUPPLIER'),
            'ETS_SPEED_TIME_CACHE_MANUFACTURER' => Configuration::get('ETS_SPEED_TIME_CACHE_MANUFACTURER'),
            'ETS_SPEED_TIME_CACHE_CONTACT' => Configuration::get('ETS_SPEED_TIME_CACHE_CONTACT'),
            'ETS_SPEED_TIME_CACHE_PRICESDROP' => Configuration::get('ETS_SPEED_TIME_CACHE_PRICESDROP'),
            'ETS_SPEED_TIME_CACHE_SITEMAP' => Configuration::get('ETS_SPEED_TIME_CACHE_SITEMAP'),
            'ETS_SPEED_TIME_CACHE_BLOG' => Configuration::get('ETS_SPEED_TIME_CACHE_BLOG'),
            'ETS_RECORD_PAGE_CLICK' => Configuration::get('ETS_RECORD_PAGE_CLICK'),
            'ETS_SPEED_CHECK_USER_AGENT' => Configuration::get('ETS_SPEED_CHECK_USER_AGENT'),
            'ETS_SPEED_PAGES_TO_CACHE' => Configuration::get('ETS_SPEED_PAGES_TO_CACHE') ? explode(',',Configuration::get('ETS_SPEED_PAGES_TO_CACHE')):array(),
            'live_script' => file_exists(dirname(__FILE__).'/views/js/script_custom.js') ? Tools::file_get_contents(dirname(__FILE__).'/views/js/script_custom.js'):'',
        );
    }
    public function getModulesDynamic()
    {
        $defines = new Ets_superspeed_defines();
        $customerSignin ='ps_customersignin';
        $shoppingcart ='ps_shoppingcart';
        $sql = 'SELECT m.id_module,m.name,m.version FROM '._DB_PREFIX_.'module m
        INNER JOIN '._DB_PREFIX_.'hook_module mh ON (mh.id_module=m.id_module)
        INNER JOIN '._DB_PREFIX_.'hook h ON (h.id_hook=mh.id_hook)
        WHERE m.name!="'.pSQL($customerSignin).'" AND m.name!="'.pSQL($shoppingcart).'" AND m.name!="blockcart" AND m.name!="blockuserinfo" AND h.name IN ("'.implode('","',array_map('pSQL',$defines->_dynamic_hooks)).'") GROUP BY m.name';
        $modules= Db::getInstance()->executeS($sql);
        if($modules)
        {
            foreach($modules as &$module)
            {
                $sql = 'SELECT h.id_hook,h.name FROM '._DB_PREFIX_.'hook h
                INNER JOIN '._DB_PREFIX_.'hook_module mh ON (mh.id_hook=h.id_hook)
                INNER JOIN '._DB_PREFIX_.'module m ON (m.id_module=mh.id_module)
                WHERE h.name IN ("'.implode('","',array_map('pSQL',$defines->_dynamic_hooks)).'") AND m.id_module="'.(int)$module['id_module'].'" GROUP BY h.name';
                $module['hooks'] = Db::getInstance()->executeS($sql);
                if($module['hooks'])
                {
                    foreach($module['hooks'] as &$hook)
                    {
                        $hook['dynamic'] = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_dynamic WHERE id_module="'.(int)$module['id_module'].'" AND hook_name="'.pSQL($hook['name']).'"');
                    }
                }
                $module['logo'] = $this->getBaseLink().'/modules/'.$module['name'].'/logo.png';
            }
        }
        return $modules;
    }
    public static function getModuleAuthor($module)
    {
        $iso = Tools::substr(Context::getContext()->language->iso_code, 0, 2);

        // Config file
        $config_file = _PS_MODULE_DIR_ . $module . '/config_' . $iso . '.xml';
        // For "en" iso code, we keep the default config.xml name
        if ($iso == 'en' || !file_exists($config_file)) {
            $config_file = _PS_MODULE_DIR_ . $module . '/config.xml';
            if (!file_exists($config_file)) {
                return 'Module ' . Tools::ucfirst($module);
            }
        }
        // Load config.xml
        libxml_use_internal_errors(true);
        $xml_module = @simplexml_load_file($config_file);
        if (!$xml_module) {
            return 'Module ' . Tools::ucfirst($module);
        }
        foreach (libxml_get_errors() as $error) {
            libxml_clear_errors();
            unset($error);
            return 'Module ' . Tools::ucfirst($module);
        }
        libxml_clear_errors();
        // Return Author
        return $xml_module->author;
    }
    public static function displayContentCache($check_connect= false)
    {
        if(Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE') && (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD']!='POST'))
        {
            $ets_ss_class_cache= new Ets_ss_class_cache();
            $cache= $ets_ss_class_cache->getCache($check_connect);
            if($cache)
            {
                return $cache;
            }
        }
        return false;
    }
    public static function createCache($html)
    {
        $controller = Tools::getValue('controller');
        if(Module::isInstalled('ybc_blog') && Module::isEnabled('ybc_blog') && Tools::getValue('fc')=='moudle' && Tools::getValue('module')=='ybc_blog' && in_array($controller,array('blog','category','gallery','author')) && !Tools::isSubmit('edit_comment'))
        {
            $controller = 'blog';
        }
        $pages_cache= Configuration::get('ETS_SPEED_PAGES_TO_CACHE')? explode(',',Configuration::get('ETS_SPEED_PAGES_TO_CACHE')):array();
        if($pages_cache && in_array($controller,$pages_cache) && Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE'))
        {
            $ets_ss_class_cache= new Ets_ss_class_cache();
            return $ets_ss_class_cache->setCache($html);
        }
    }
    public static function getDynamicHookModule($id_module,$hook_name)
    {
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_dynamic` (
          `id_module` int(11) NOT NULL,
          `hook_name` varchar(222) NOT NULL,
          `empty_content` int(1) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1');
        $context = Context::getContext();
        if($id_module && ($id_module==Module::getModuleIdByName('block_cart') || $id_module== Module::getModuleIdByName('ps_shoppingcart'))&& $hook_name!='header' && $hook_name!='displayHeader' && isset($context->cookie->id_cart) && $context->cookie->id_cart)
        {
            if($hook_name=='top' || $hook_name=='displayTop')
               return array(
                    'empty_content' =>0,
                );
            else
                return array(
                    'empty_content' =>1,
                );
        }
        if($id_module && ($id_module==Module::getModuleIdByName('ps_customersignin') || $id_module==Module::getModuleIdByName('blockuserinfo')) && $hook_name!='header' && $hook_name!='displayHeader' && isset($context->customer->id) && $context->customer->id)
        {
            return array(
                'empty_content'=>1,
            );
        }
        $sql ='SELECT d.* FROM '._DB_PREFIX_.'ets_superspeed_dynamic d
        INNER JOIN '._DB_PREFIX_.'hook h ON (h.name=d.hook_name)
        LEFT JOIN '._DB_PREFIX_.'hook_alias ha ON (h.name=ha.name)
        WHERE (h.name="'.pSQL($hook_name).'" OR ha.alias ="'.pSQL($hook_name).'") AND d.id_module="'.(int)$id_module.'"';
        return Db::getInstance()->getRow($sql);
    }
    public function renderSpeedStatistics()
    {
        $fileCaches= Db::getInstance()->executeS('SELECT page,SUM(file_size) as total_size FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE id_shop='.(int)$this->context->shop->id.' GROUP BY page');
        if($fileCaches)
        {
            foreach($fileCaches as &$cache)
            {
                if($cache['total_size'] < 1024)
                    $cache['total_size'] .='KB';
                elseif($cache['total_size'] >=1024 && $cache['total_size'] <1024*1024)
                     $cache['total_size'] .='Mb';
                if($cache['total_size'] >= 1024*1024)
                    $cache['total_size'] .='Gb';
            }
        }
        $firstime = $this->getTimeSpeed(true);
        $this->context->smarty->assign(
            array(
                'times' => $this->getTimeSpeed(),
                'fileCaches' => $fileCaches,
                'start_time' => $firstime['value'],
                'time_zone' =>date('Z')/3600,
                'url_home' => $this->context->link->getPageLink('index',null,null,array('check_speed'=>1)),
            )  
        );
        return $this->display(__FILE__,'statistics.tpl');
    }
    public function renderSpeedHelps()
    {
        $cronjob_last= '';
        if(file_exists(dirname(__FILE__).'/cronjob_log.txt') && $cronjob_time = Tools::file_get_contents(dirname(__FILE__).'/cronjob_log.txt'))
        {
            $last_time = strtotime($cronjob_time);
            $time = strtotime(date('Y-m-d H:i:s'))-$last_time;
            if($time > 86400)
                $cronjob_last = $cronjob_time;
            elseif($time)
            {
                if($hours =floor($time/3600))
                {
                    $cronjob_last .= $hours.' '.$this->l('hours').' ';
                    $time = $time%3600;
                }
                if($minutes = floor($time/60))
                {
                    $cronjob_last .= $minutes.' '.$this->l('minutes').' ';
                    $time = $time%60;
                }
                if($time)
                    $cronjob_last .= $time.' '.$this->l('seconds').' ';
                $cronjob_last .= $this->l('ago');
            }    
        }
        $this->context->smarty->assign(
            array(
                'link_cronjob'=> $this->getBaseLink().'/modules/'.$this->name.'/cronjob.php?token='.Configuration::getGlobalValue('ETS_SPEED_SUPER_TOCKEN'),
                'dir_cronjob' => dirname(__FILE__).'/cronjob.php',
                'ETS_SPEED_SUPER_TOCKEN'=> Configuration::getGlobalValue('ETS_SPEED_SUPER_TOCKEN'),
                'link_base' => $this->getBaseLink(),
                'cronjob_last' => trim($cronjob_last,', '),
            )
        );
        return $this->display(__FILE__,'helps.tpl');
    }
    public function getTimeSpeed($first=false)
    {
        $times= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_time WHERE id_shop="'.(int)$this->context->shop->id.'" AND `date`<="'.pSQL(date('Y-m-d H:i:s')).'" ORDER BY `date` DESC LIMIT 0,150');
        if($first)
        {
            if($times)
            {
                return array(
                    'time'=> date('Y-m-d H:i:s'),
                    'value'=>$times[0]['time'],
                );
            }
            else
                return array(
                    'time'=> date('Y-m-d H:i:s'),
                    'value'=>0,
                );
            
        }
        
        $second=0;
        $time_datas= array();
        if($times)
        {
            //$times = array_reverse($times);
            foreach($times as $time)
            {
                $time_datas[] = array(
                    'time'=>date("Y-m-d H:i:s", strtotime("-$second seconds")),
                    'value'=>$time['time'],
                );
                $second +=2;
            }
        }
        if(Count($time_datas)<150)
        {
            $n = count($time_datas);
            for($i=$n;$i < 150;$i++)
            {
                $time_datas[] = array(
                    'time'=>date("Y-m-d H:i:s", strtotime("-$second seconds")),
                    'value'=>0,
                );
                $second +=2;
            }
        }
        return array_reverse($time_datas);
    }
    public function renderSpeedDiagnostics()
    {
        return $this->display(__FILE__,'diagnostics.tpl');
    }
    public static function isInstalled($module_name)
    {
        $result = (int) Db::getInstance()->getValue('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module` WHERE `name` = "' . pSQL($module_name) . '"');
        return $result;
    }
    public static function isEnabled($module_name)
    {
        $active = false;
        $id_module = (int) Db::getInstance()->getValue('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module` WHERE `name` = "' . pSQL($module_name) . '"');
        if (Db::getInstance()->getValue('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module_shop` WHERE `id_module` = ' . (int) $id_module . ' AND `id_shop` = ' . (int) Context::getContext()->shop->id)) {
            $active = true;
        }
        return (bool) $active;
    }
    public function getBaseLink()
    {
        $link = (Configuration::get('PS_SSL_ENABLED_EVERYWHERE')?'https://':'http://').$this->context->shop->domain.$this->context->shop->getBaseURI();
        return trim($link,'/');
    }
    public function getLinkTable($table,$type='')
    {
        if($table=='category')
            return $this->getBaseLink().'/img/c/';
        elseif($table=='manufacturer')
            return $this->getBaseLink().'/img/m/';
        elseif($table=='blog_post')
            return $this->getBaseLink().'/modules/ybc_blog/views/img/post/'.($type=='thumb' ? 'thumb/':'');
        elseif($table=='blog_category')
            return $this->getBaseLink().'/modules/ybc_blog/views/img/category/'.($type=='thumb' ? 'thumb/':'');
        elseif($table=='blog_gallery')
            return $this->getBaseLink().'/modules/ybc_blog/views/img/gallery/'.($type=='thumb' ? 'thumb/':'');
        else    
            return $this->getBaseLink().'/img/su/'; 
    }
    public static function minifyHTML($html_content)
    {
        if (Tools::strlen($html_content) > 0) {
            include_once(_PS_MODULE_DIR_.'ets_superspeed/classes/ext/minify_html');
            $html_content = str_replace(chr(194).chr(160), '&nbsp;', $html_content);
            if (trim($minified_content = Minify_HTML::minify($html_content, array('cssMinifier', 'jsMinifier'))) != '') {
                $html_content = $minified_content;
            }

            return $html_content;
        }
        return false;
    }
    public function autoRefreshCache()
    {
        $created= false;
        Configuration::updateValue('ETS_SPEED_CACHE_TIME',strtotime("now"));
        file_put_contents(dirname(__FILE__).'/cronjob_log.txt',date('Y-m-d H:i:s'));
        if(Configuration::get('ETS_SPEED_AUTO_CACHE') && Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE') && Configuration::get('ETS_SPEED_PAGES_TO_CACHE'))
        {
            $pages= explode(',',Configuration::get('ETS_SPEED_PAGES_TO_CACHE'));
            if(in_array('index',$pages))
            {
                $page ='index';
                if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE page="'.pSQL($page).'" AND id_shop="'.(int)$this->context->shop->id.'"'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) !=31 ? ' AND date_upd > "'.date('Y-m-d', strtotime('-'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) ? (int)Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) : 1).' DAY')).'"':'')))
                {
                    Tools::file_get_contents($this->getBaseLink().'/');
                }
                
            }
            if(in_array('pricesdrop',$pages))
            {
                $page ='pricesdrop';
                if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE page="'.pSQL($page).'" AND id_shop="'.(int)$this->context->shop->id.'"'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) !=31 ? ' AND date_upd > "'.date('Y-m-d', strtotime('-'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) ? (int)Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) : 1).' DAY')).'"':'')))
                {
                    Tools::file_get_contents($this->context->link->getPageLink('prices-drop'));
                }
            }
            if(in_array('bestsales',$pages))
            {
                $page ='bestsales';
                if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE page="'.pSQL($page).'" AND id_shop="'.(int)$this->context->shop->id.'"'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) !=31 ? ' AND date_upd > "'.date('Y-m-d', strtotime('-'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) ? (int)Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) : 1).' DAY')).'"':'')))
                {
                    Tools::file_get_contents($this->context->link->getPageLink('best-sales'));
                }
            }
            if(in_array('newproducts',$pages))
            {
                $page ='newproducts';
                if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE page="'.pSQL($page).'" AND id_shop="'.(int)$this->context->shop->id.'"'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) !=31 ? ' AND date_upd > "'.date('Y-m-d', strtotime('-'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) ? (int)Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) : 1).' DAY')).'"':'')))
                {
                    Tools::file_get_contents($this->context->link->getPageLink('new-products'));
                }

            }
            if(in_array('sitemap',$pages))
            {
                $page ='sitemap';
                if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE page="'.pSQL($page).'" AND id_shop="'.(int)$this->context->shop->id.'"'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) !=31 ? ' AND date_upd > "'.date('Y-m-d', strtotime('-'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) ? (int)Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) : 1).' DAY')).'"':'')))
                {
                    Tools::file_get_contents($this->context->link->getPageLink('sitemap'));
                }
            }
            if(in_array('contact',$pages))
            {
                $page ='contact';
                if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE page="'.pSQL($page).'" AND id_shop="'.(int)$this->context->shop->id.'"'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) !=31 ? ' AND date_upd > "'.date('Y-m-d', strtotime('-'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) ? (int)Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page)) : 1).' DAY')).'"':'')))
                {
                     Tools::file_get_contents($this->context->link->getPageLink('contact'));
                }
            }
            $created=true;
        }
        $pages_cache= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_cache_page');
        if($pages_cache)
        {
            foreach($pages_cache as $page_cache)
            {
                if(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page_cache['page']))!=31 && !Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE id_shop="'.(int)$this->context->shop->id.'" AND  id_cache_page="'.(int)$page_cache['id_cache_page'].'" AND date_upd > "'.date('Y-m-d', strtotime('-'.(Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page_cache['page'])) ? (int)Configuration::get('ETS_SPEED_TIME_CACHE_'.Tools::strtoupper($page_cache['page'])) : 1).' DAY')).'"'))
                {
                  Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE id_cache_page ='.(int)$page_cache['id_cache_page']);
                  Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ets_superspeed_cache_page_hook WHERE id_cache_page="'.(int)$page_cache['id_cache_page'].'"');
                  @unlink($page_cache['file_cache']);  
                }
            }
        }
        if($created)
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Cronjob done. All expired caches was cleared. New caches was successfully created.')),
                    )
                )
            );
        else
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Cronjob done. All expired caches was cleared.')),
                    )
                )
            );
    }
    public function getImageOptimize($check_quality=false,$total_all_type=true)
    {
        $array =array();
        $image_types= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'image_type');
        if($image_types)
        {
            foreach($image_types as $image_type)
            {
                if($image_type['products'])
                {
                    $array['product_'.$image_type['name'].'_optimized'] = $this->getTotalImage('product',false,true,$check_quality,false,$image_type['name']);
                    $array['product_'.$image_type['name']] = $this->getTotalImage('product',false,false,$check_quality,false,$image_type['name']) - $array['product_'.$image_type['name'].'_optimized'];
                }
                if($image_type['suppliers'])
                {
                    $array['supplier_'.$image_type['name'].'_optimized'] = $this->getTotalImage('supplier',false,true,$check_quality,false,$image_type['name']);
                    $array['supplier_'.$image_type['name']] = $this->getTotalImage('supplier',false,false,$check_quality,false,$image_type['name']) - $array['supplier_'.$image_type['name'].'_optimized'];
                }
                if($image_type['manufacturers'])
                {
                    $array['manufacturer_'.$image_type['name'].'_optimized'] = $this->getTotalImage('manufacturer',false,true,$check_quality,false,$image_type['name']);
                    $array['manufacturer_'.$image_type['name']] = $this->getTotalImage('manufacturer',false,false,$check_quality,false,$image_type['name'])- $array['manufacturer_'.$image_type['name'].'_optimized'];
                }
                if($image_type['categories'])
                {
                    $array['category_'.$image_type['name'].'_optimized'] = $this->getTotalImage('category',false,true,$check_quality,false,$image_type['name']);
                    $array['category_'.$image_type['name']] = $this->getTotalImage('category',false,false,$check_quality,false,$image_type['name'])- $array['category_'.$image_type['name'].'_optimized'];
                }    
            }
        }
        if($this->isblog)
        {
            $array['blog_post_image_optimized'] = $this->getTotalImage('blog_post',false,true,$check_quality,false,'image');
            $array['blog_post_thumb_optimized'] = $this->getTotalImage('blog_post',false,true,$check_quality,false,'thumb');
            $array['blog_post_image'] = $this->getTotalImage('blog_post',false,false,$check_quality,false,'image') - $array['blog_post_image_optimized'];
            $array['blog_post_thumb'] = $this->getTotalImage('blog_post',false,false,$check_quality,false,'thumb') - $array['blog_post_thumb_optimized'];
            $array['blog_category_image_optimized'] = $this->getTotalImage('blog_category',false,true,$check_quality,false,'image');
            $array['blog_category_thumb_optimized'] = $this->getTotalImage('blog_category',false,true,$check_quality,false,'thumb');
            $array['blog_category_image'] = $this->getTotalImage('blog_category',false,false,$check_quality,false,'image') - $array['blog_category_image_optimized'];
            $array['blog_category_thumb'] = $this->getTotalImage('blog_category',false,false,$check_quality,false,'thumb') - $array['blog_category_thumb_optimized'];
            $array['blog_gallery_image_optimized'] = $this->getTotalImage('blog_gallery',false,true,$check_quality,false,'image');
            $array['blog_gallery_thumb_optimized'] = $this->getTotalImage('blog_gallery',false,true,$check_quality,false,'thumb');
            $array['blog_gallery_image'] = $this->getTotalImage('blog_gallery',false,false,$check_quality,false,'image') - $array['blog_gallery_image_optimized'];
            $array['blog_gallery_thumb'] = $this->getTotalImage('blog_gallery',false,false,$check_quality,false,'thumb') - $array['blog_gallery_thumb_optimized'];
            $array['blog_slide_image_optimized'] = $this->getTotalImage('blog_slide',false,true,$check_quality,false,'image');
            $array['blog_slide_image'] = $this->getTotalImage('blog_slide',false,false,$check_quality,false,'image') - $array['blog_slide_image_optimized'];
            
        }
        if($this->isSlide)
        {
            $array['home_slide_image_optimized'] = $this->getTotalImage('home_slide',false,true,$check_quality,false,'image');
            $array['home_slide_image'] = $this->getTotalImage('home_slide',false,false,$check_quality,false,'image') - $array['home_slide_image_optimized'];
        }
        $array['others_logo_optimized'] = $this->getTotalImage('others',false,true,$check_quality,false,'logo');
        $array['others_logo'] = $this->getTotalImage('others',false,false,$check_quality,false,'logo') - $array['others_logo_optimized'];
        $array['others_banner_optimized'] = $this->getTotalImage('others',false,true,$check_quality,false,'banner');
        $array['others_banner'] = $this->getTotalImage('others',false,false,$check_quality,false,'banner') - $array['others_banner_optimized'];
        $array['others_themeconfig_optimized'] = $this->getTotalImage('others',false,true,$check_quality,false,'themeconfig');
        $array['others_themeconfig'] = $this->getTotalImage('others',false,false,$check_quality,false,'themeconfig') - $array['others_themeconfig_optimized'];
        if(Tools::isSubmit('btnSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageAllOptimize') || Tools::isSubmit('submitUploadImageSave')||Tools::isSubmit('submitUploadImageCompress') || Tools::isSubmit('submitBrowseImageOptimize') || Tools::isSubmit('btnSubmitCleaneImageUnUsed') || Tools::getValue('controller')=='AdminSuperSpeedImage' || Tools::isSubmit('getPercentageImageOptimize'))
            $noconfig=false;
        else
            $noconfig=true;
        $total_image_product= $this->getTotalImage('product',$total_all_type,false,$check_quality,$noconfig);
        $total_image_category = $this->getTotalImage('category',$total_all_type,false,$check_quality,$noconfig);
        $total_image_manufacturer = $this->getTotalImage('manufacturer',$total_all_type,false,$check_quality,$noconfig);
        $total_image_supplier = $this->getTotalImage('supplier',$total_all_type,false,$check_quality,$noconfig);
        if($this->isblog)
        {
            $total_image_blog_post = $this->getTotalImage('blog_post',$total_all_type,false,$check_quality,$noconfig);
            $total_image_blog_category = $this->getTotalImage('blog_category',$total_all_type,false,$check_quality,$noconfig);
            $total_image_blog_gallery = $this->getTotalImage('blog_gallery',$total_all_type,false,$check_quality,$noconfig);
            $total_image_blog_slide = $this->getTotalImage('blog_slide',$total_all_type,false,$check_quality,$noconfig);
        }
        if($this->isSlide)
            $total_image_home_slide = $this->getTotalImage('home_slide',$total_all_type,false,$check_quality,$noconfig);
        $total_image_product_optimizaed = $this->getTotalImage('product',$total_all_type,true,$check_quality,$noconfig);
        $total_image_category_optimizaed = $this->getTotalImage('category',$total_all_type,true,$check_quality,$noconfig);
        $total_image_manufacturer_optimizaed = $this->getTotalImage('manufacturer',$total_all_type,true,$check_quality,$noconfig);
        $total_image_supplier_optimizaed = $this->getTotalImage('supplier',$total_all_type,true,$check_quality,$noconfig);
        $total = ($total_image_product-$total_image_product_optimizaed)+($total_image_category-$total_image_category_optimizaed)+($total_image_manufacturer-$total_image_manufacturer_optimizaed)+($total_image_supplier-$total_image_supplier_optimizaed);  
        if($this->isblog)
        {
            $total_image_blog_post_optimizaed = $this->getTotalImage('blog_post',$total_all_type,true,$check_quality,$noconfig);
            $total_image_blog_category_optimizaed = $this->getTotalImage('blog_category',$total_all_type,true,$check_quality,$noconfig);
            $total_image_blog_gallery_optimizaed = $this->getTotalImage('blog_gallery',$total_all_type,true,$check_quality,$noconfig);
            $total_image_blog_slide_optimizaed = $this->getTotalImage('blog_slide',$total_all_type,true,$check_quality,$noconfig);
            $total += ($total_image_blog_slide - $total_image_blog_slide_optimizaed)+($total_image_blog_post-$total_image_blog_post_optimizaed) + ($total_image_blog_category-$total_image_blog_category_optimizaed) + ($total_image_blog_gallery-$total_image_blog_gallery_optimizaed);
        }
        if($this->isSlide)
        {
            $total_image_home_slide_optimizaed = $this->getTotalImage('home_slide',$total_all_type,true,$check_quality,$noconfig);
            $total += ($total_image_home_slide-$total_image_home_slide_optimizaed);
        }
        $total_image_others = $this->getTotalImage('others',$total_all_type,false,$check_quality,$noconfig);
        $total_image_others_optimizaed = $this->getTotalImage('others',$total_all_type,true,$check_quality,$noconfig);
        $total += ($total_image_others-$total_image_others_optimizaed);
        $array['total_images'] = $total >0 ? $total :0;
        $array['quality_optimize'] = Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE',Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE'));
        $array['total_images_optimized']= $total_image_product_optimizaed+$total_image_category_optimizaed+$total_image_manufacturer_optimizaed+$total_image_supplier_optimizaed + ($this->isblog ? $total_image_blog_post_optimizaed+$total_image_blog_category_optimizaed+$total_image_blog_gallery_optimizaed+$total_image_blog_slide_optimizaed :0) +($this->isSlide ? $total_image_home_slide_optimizaed:0);
        $array['total_size_save'] = $this->getTotalSizeSave();
        $array['check_optimize'] = $this->checkOptimizeAllImage(true);
        return $array;
    }
        /**
     * @return array|null
     */
    public function getOverrides()
    {
        if (!$this->is17)
        {
            if (!is_dir($this->getLocalPath().'override')) {
                return null;
            }
            $result = array();
            foreach (Tools::scandir($this->getLocalPath().'override', 'php', '', true) as $file) {
                $class = basename($file, '.php');
                if (PrestaShopAutoload::getInstance()->getClassPath($class.'Core') || Module::getModuleIdByName($class)) {
                    $result[] = $class;
                }
            }
            return $result;
        }
        else
            return parent::getOverrides();
    }

    /**
     * @param string $classname
     * @return bool
     * @throws ReflectionException
     */
    public function addOverride($classname)
    {
        $_errors = array();
        $orig_path = $path = PrestaShopAutoload::getInstance()->getClassPath($classname.'Core');
        if (!$path) {
            $path = 'modules'.DIRECTORY_SEPARATOR.$classname.DIRECTORY_SEPARATOR.$classname.'.php';
        }
        $path_override = $this->getLocalPath().'override'.DIRECTORY_SEPARATOR.$path;
        if (!@file_exists($path_override)) {
            return true;
        } else {
            @file_put_contents($path_override, preg_replace('#(\r\n|\r)#ism', "\n", Tools::file_get_contents($path_override)));
        }
        $pattern_escape_com = '#(^\s*?\/\/.*?\n|\/\*(?!\n\s+\* module:.*?\* date:.*?\* version:.*?\*\/).*?\*\/)#ism';
        if ($file = PrestaShopAutoload::getInstance()->getClassPath($classname))
        {
            $override_path = _PS_ROOT_DIR_.'/'.$file;

            if ((!@file_exists($override_path) && !is_writable(dirname($override_path))) || (@file_exists($override_path) && !is_writable($override_path))) {
                $_errors[] = sprintf($this->l('file (%s) not writable'), $override_path);
            }
            do {
                $uniq = uniqid();
            } while (@class_exists($classname.'OverrideOriginal_remove', false));

            $override_file = file($override_path);
            $override_file = array_diff($override_file, array("\n"));
            $this->execCode(preg_replace(array('#^\s*<\?(?:php)?#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array(' ', 'class '.$classname.'OverrideOriginal'.$uniq), implode('', $override_file)));
            $override_class = new ReflectionClass($classname.'OverrideOriginal'.$uniq);

            $module_file = file($path_override);
            $module_file = array_diff($module_file, array("\n"));
            $this->execCode(preg_replace(array('#^\s*<\?(?:php)?#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array(' ', 'class '.$classname.'Override'.$uniq), implode('', $module_file)));
            $module_class = new ReflectionClass($classname.'Override'.$uniq);

            foreach ($module_class->getMethods() as $method) {
                if ($override_class->hasMethod($method->getName())) {
                    $method_override = $override_class->getMethod($method->getName());
                    if (preg_match('/module: (.*)/ism', $override_file[$method_override->getStartLine() - 5], $name) && preg_match('/date: (.*)/ism', $override_file[$method_override->getStartLine() - 4], $date) && preg_match('/version: ([0-9.]+)/ism', $override_file[$method_override->getStartLine() - 3], $version)) {
                        $_errors[] = sprintf($this->l('The method %1$s in the class %2$s is already overridden by the module %3$s version %4$s at %5$s.'), $method->getName(), $classname, $name[1], $version[1], $date[1]);
                    } else {
                        $_errors[] = sprintf($this->l('The method %1$s in the class %2$s is already overridden.'), $method->getName(), $classname);
                    }
                }
                $module_file = preg_replace('/((:?public|private|protected)\s+(static\s+)?function\s+(?:\b'.$method->getName().'\b))/ism', "/*\n    * module: ".$this->name."\n    * date: ".date('Y-m-d H:i:s')."\n    * version: ".$this->version."\n    */\n    $1", $module_file);
                if ($module_file === null) {
                    $_errors[] = sprintf($this->l('Failed to override method %1$s in class %2$s.'), $method->getName(), $classname);
                }
            }
            if (!$_errors)
            {
                $copy_from = array_slice($module_file, $module_class->getStartLine() + 1, $module_class->getEndLine() - $module_class->getStartLine() - 2);
                array_splice($override_file, $override_class->getEndLine() - 1, 0, $copy_from);
                $code = implode('', $override_file);

                @file_put_contents($override_path, preg_replace($pattern_escape_com, '', $code));
            }
        }
        else
        {
            $override_src = $path_override;
            $override_dest = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.$path;
            $dir_name = dirname($override_dest);
            if (!$orig_path && !is_dir($dir_name)) {
                $oldumask = umask(0000);
                @mkdir($dir_name, 0777);
                umask($oldumask);
            }
            if (!is_writable($dir_name)) {
                $_errors[] = sprintf($this->l('directory (%s) not writable'), $dir_name);
            }
            $module_file = file($override_src);
            $module_file = array_diff($module_file, array("\n"));
            if ($orig_path) {
                do {
                    $uniq = uniqid();
                } while (@class_exists($classname.'OverrideOriginal_remove', false));
                $this->execCode(preg_replace(array('#^\s*<\?(?:php)?#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array(' ', 'class '.$classname.'Override'.$uniq), implode('', $module_file)));
                $module_class = new ReflectionClass($classname.'Override'.$uniq);

                foreach ($module_class->getMethods() as $method) {
                    $module_file = preg_replace('/((:?public|private|protected)\s+(static\s+)?function\s+(?:\b'.$method->getName().'\b))/ism', "/*\n    * module: ".$this->name."\n    * date: ".date('Y-m-d H:i:s')."\n    * version: ".$this->version."\n    */\n    $1", $module_file);
                    if ($module_file === null) {
                        $_errors[] = sprintf($this->l('Failed to override method %1$s in class %2$s.'), $method->getName(), $classname);
                    }
                }
            }
            if (!$_errors)
            {
                @file_put_contents($override_dest, preg_replace($pattern_escape_com, '', $module_file));
                Tools::generateIndex();
            }
        }
        if ($_errors)
            $this->logInstall($classname, $_errors);
        return true;
    }

    /**
     * @param $php_code
     */
    public function execCode($php_code)
    {
        if(function_exists('ets_execute_php'))
            call_user_func('ets_execute_php',$php_code);
        else
        {
            $temp = @tempnam($this->getLocalPath().'cache', 'execCode');
            $handle = fopen($temp, "w+");
            fwrite($handle, "<?php\n" . $php_code);
            fclose($handle);
            include $temp;
            @unlink($temp);
        }
    }

    /**
     * @param string $classname
     * @return bool
     */
    public function removeOverride($classname)
    {
        if ($this->isLogInstall($classname))
            return true;
        $orig_path = $path = PrestaShopAutoload::getInstance()->getClassPath($classname.'Core');
        if ($orig_path && !$file = PrestaShopAutoload::getInstance()->getClassPath($classname))
            return true;
        elseif (!$orig_path && Module::getModuleIdByName($classname))
            $path = 'modules'.DIRECTORY_SEPARATOR.$classname.DIRECTORY_SEPARATOR.$classname.'.php';
        $override_path = $orig_path? _PS_ROOT_DIR_.'/'.$file : _PS_OVERRIDE_DIR_.$path;
        if (!@is_file($override_path) || !is_writable($override_path))
            return true;
        return parent::removeOverride($classname);
    }

    public $log_file = 'cache/install.log';

    /**
     * @param $classname
     * @param $_errors
     */
    public function logInstall($classname, $_errors)
    {
        $log_file = $this->getLocalPath().$this->log_file;
        $data = array();
        if (@file_exists($log_file))
            $data = (array)Tools::jsonDecode(Tools::file_get_contents($log_file));
        $data[$classname] = $_errors;
        @file_put_contents($log_file, Tools::jsonEncode($data));
    }

    /**
     * @param $classname
     * @return bool
     */
    public function isLogInstall($classname)
    {
        $log_file = $this->getLocalPath().$this->log_file;
        if (!@file_exists($log_file))
            return false;
        $cached = (array)Tools::jsonDecode(Tools::file_get_contents($log_file));
        if ($cached && !empty($cached[$classname]))
            return true;
        return false;
    }

    /**
     * @return bool
     */
    public function clearLogInstall()
    {
        $log_file = $this->getLocalPath().$this->log_file;
        if (@file_exists($log_file))
            @unlink($log_file);
        return true;
    }
    public function genSecure($size)
    {
            $chars = md5(time()); 
            $code = '';
            for ($i = 1; $i <= $size; ++$i)
            {
                $char= Tools::substr($chars, rand(0, Tools::strlen($chars) - 1), 1);
                if($char=='e')
                    $char='a';
                $code .= $char;
            }
            return $code;
    }
    public function getTotalSizeSave($check_quality=false)
    {
        $quality = (int)Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE',Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE'));
        if((Tools::getValue('controller')!='AdminSuperSpeedImage' || Tools::getValue('ajax')) && !Tools::isSubmit('getPercentageAllImageOptimize') && $quality==100 )
            $check_quality = false;
        else
            $check_quality = true;
        $sql = 'SELECT sum(total_old) as old,sum(total_new) as new
            FROM (SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_product_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100').
            ' UNION ALL
            SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_category_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100').
            ' UNION ALL
            SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_supplier_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100').
            ' UNION ALL
            SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_manufacturer_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100').
            ($this->isblog ? ' UNION ALL
            SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_blog_post_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100').
            ' UNION ALL
            SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_blog_category_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100').
            ' UNION ALL
            SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_blog_slide_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100').
            ' UNION ALL
            SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_home_slide_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100').
            ' UNION ALL
            SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_others_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100').
            ' UNION ALL
            SELECT sum(size_old) as total_old,sum(size_new) as total_new FROM '._DB_PREFIX_.'ets_superspeed_blog_gallery_image WHERE size_new < size_old'.($check_quality ? ' AND quality="'.(int)$quality.'"' :' AND quality!=100') :'').') t';
        $total = Db::getInstance()->getRow($sql);
        $total_save= $total['old'] - $total['new'];
        $total_old = $total['old'];
        if($total_save)
            $percent_save = ($total_save/$total_old) * 100;
        if($total_save <1024)
            $total_text ='KB';
        else
        {
            $total_save = $total_save/1024;
            if($total_save<1024)
                $total_text='Mb';
            else
            {
                $total_save= $total_save/1024;
                $total_text='Gb';
            }
        }
        return $total_save >0 ? $this->l('save').' '.Tools::ps_round($total_save,2).$total_text.' ('.Tools::ps_round($percent_save,2).'%)' :'';
    }
    public function checkOptimizeAllImage($check_quality=false)
    {
        $total_image_product= $this->getTotalImage('product',true,false,$check_quality,true);
        $total_image_category = $this->getTotalImage('category',true,false,$check_quality,true);
        $total_image_manufacturer = $this->getTotalImage('manufacturer',true,false,$check_quality,true);
        $total_image_supplier = $this->getTotalImage('supplier',true,false,$check_quality,true);
        $total_image_product_optimizaed = $this->getTotalImage('product',true,true,$check_quality,true);
        $total_image_category_optimizaed = $this->getTotalImage('category',true,true,$check_quality,true);
        $total_image_manufacturer_optimizaed = $this->getTotalImage('manufacturer',true,true,$check_quality,true);
        $total_image_supplier_optimizaed = $this->getTotalImage('supplier',true,true,$check_quality,true);
        $total_images = $total_image_product + $total_image_category + $total_image_manufacturer + $total_image_supplier;
        $total_optimized_images = $total_image_category_optimizaed + $total_image_product_optimizaed + $total_image_supplier_optimizaed + $total_image_manufacturer_optimizaed;
        if($this->isblog)
        {
            $total_image_blog_post= $this->getTotalImage('blog_post',true,false,$check_quality,true);
            $total_image_blog_category = $this->getTotalImage('blog_category',true,false,$check_quality,true);
            $total_image_blog_gallery = $this->getTotalImage('blog_gallery',true,false,$check_quality,true);
            $total_image_blog_slide = $this->getTotalImage('blog_slide',true,false,$check_quality,true);
            $total_image_blog_post_optimizaed = $this->getTotalImage('blog_post',true,true,$check_quality,true);
            $total_image_blog_category_optimizaed = $this->getTotalImage('blog_category',true,true,$check_quality,true);
            $total_image_blog_gallery_optimizaed = $this->getTotalImage('blog_gallery',true,true,$check_quality,true);
            $total_image_blog_slide_optimizaed = $this->getTotalImage('blog_slide',true,true,$check_quality,true);
            $total_images += $total_image_blog_post + $total_image_blog_category + $total_image_blog_gallery + $total_image_blog_slide;
            $total_optimized_images += $total_image_blog_post_optimizaed + $total_image_blog_category_optimizaed + $total_image_blog_gallery_optimizaed + $total_image_blog_slide_optimizaed;
        }
        if($this->isSlide)
        {
            $total_image_home_slide= $this->getTotalImage('home_slide',true,false,$check_quality,true);
            $total_image_home_slide_optimizaed = $this->getTotalImage('home_slide',true,true,$check_quality,true);
            $total_images += $total_image_home_slide;
            $total_optimized_images += $total_image_home_slide_optimizaed; 
        }
        $total_image_others = $this->getTotalImage('others',true,false,$check_quality,true);
        $total_image_others_optimizaed = $this->getTotalImage('others',true,true,$check_quality,true);
        $total_images += $total_image_others;
        $total_optimized_images += $total_image_others_optimizaed; 
        $total_unoptimized_images = $total_images - $total_optimized_images;
        if($total_unoptimized_images==0)
            return $total_images;
        else
            return false;
    }
    public function checkOptimizeImageResmush()
    {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );
        if(!in_array(Tools::getRemoteAddr(), $whitelist) && Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT')=='resmush'){
            return true;
        }
        return false;
    }
    public function checkCreatedColumn($table,$column)
    {
        $fieldsCustomers = Db::getInstance()->ExecuteS('DESCRIBE '._DB_PREFIX_.pSQL($table));
        $check_add=false;
        foreach($fieldsCustomers as $field)
        {
            if($field['Field']==$column)
            {
                $check_add=true;
                break;
            }    
        }
        return $check_add;
    }
    public function check_mod_cache($expires=true)
    {
        if(!Configuration::get('PS_HTACCESS_CACHE_CONTROL'))
            return true;
        $curl = curl_init();
        $curlOptions = array(
            CURLOPT_BINARYTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $expires ? $this->getBaseLink().'/modules/ets_superspeed/logo.png' : $this->context->link->getPageLink('index',null,null,array('check_speed'=>time())),
            CURLOPT_HTTPHEADER => array('Accept-Encoding: gzip, deflate, br')
        );
        curl_setopt_array($curl, $curlOptions);
        $response = curl_exec($curl);
        $headers = Tools::substr($response, 0, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
        $headers = explode("\n",$headers);
        if($headers)
        {
            foreach($headers as $header)
            {
                if($expires && Tools::strpos(Tools::strtolower($header),'expires')!==false)
                {
                    $array = explode(':',$header);
                    $year = date('Y')-1;
                    if(isset($array[1]) && strtotime($array[1].':00:00') > strtotime($year))
                        return true;
                    else
                        return false;
                    
                }
                elseif(Tools::strpos(Tools::strtolower($header),'content-encoding')!==false)
                {
                    $array = explode(':',$header);
                    if(isset($array[1]) && in_array(trim(Tools::strtolower($array[1])),array('gzip', 'deflate', 'br')))
                        return true;
                    else
                        return false;
                }     
            }
            return false;
        }
        return true;
    }
    public function hookActionUpdateBlog()
    {
        if(!$this->_class_ss_cache)
            $this->getClassCahe();
        $this->_class_ss_cache->deleteCache('blog');
    }
    public function hookActionUpdateBlogImage($params)
    {
        
        if(isset($params['id_post']))
        {
            $table = 'blog';
            $path = _PS_MODULE_DIR_.'ybc_blog/views/img/post/';
            $id_obj = $params['id_post'];
        }
        if(isset($params['id_category']))
        {
            $table = 'category';
            $path = _PS_MODULE_DIR_.'ybc_blog/views/img/category/';
            $id_obj = $params['id_category'];
        }
        if(isset($params['id_gallery']))
        {
            $table = 'gallery';
            $path = _PS_MODULE_DIR_.'ybc_blog/views/img/gallery/';
            $id_obj = $params['id_gallery'];
        }
        if(isset($params['id_slide']))
        {
            $table = 'slide';
            $path = _PS_MODULE_DIR_.'ybc_blog/views/img/slide/';
            $id_obj = $params['id_slide'];
        }
        $quality=(int)Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_NEW')>0 ? (int)Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_NEW') : 90;
        if(isset($params['image']) && $params['image'])
        {
            $type = 'image';
            if($size_old= $this->createBlogImage($path,$params['image']))
            {
                if($this->checkOptimizeImageResmush())
                    $url_image= $this->getLinkTable('blog_'.$table,'image').$params['image'];
                else
                    $url_image=null;
                $quality_old = Db::getInstance()->getValue('SELECT quality FROM '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = '.(int)$id_obj.' AND type_image="'.pSQL($type).'" AND optimize_type = "'.pSQL(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT')).'"');
                $compress = $this->compress($path,$params['image'],$quality,$url_image,$quality_old);
                while($compress===false)
                {
                    $compress = $this->compress($path,$params['image'],$quality,$url_image,$quality_old);
                }
                if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = "'.(int)$id_obj.'" AND type_image="'.pSQL($type).'"'))
                {
                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image (id_'.pSQL($table).',type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$id_obj.'","'.pSQL($type).'","'.(int)$quality.'","'.(float)$size_old.'","'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'","'.pSQl($compress['optimize_type']).'")');
                } 
                else
                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image SET quality="'.(int)$quality.'",size_old="'.(float)$size_old.'",size_new="'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'",optimize_type="'.pSQL($compress['optimize_type']).'" WHERE id_'.pSQL($table).' = '.(int)$id_obj.' AND type_image="'.pSQL($type).'"');
            }
        }
        if(isset($params['thumb']) && $params['thumb'])
        {
            $type = 'thumb';
            $path .= 'thumb/';
            if($size_old= $this->createBlogImage($path,$params['thumb']))
            {
                if($this->checkOptimizeImageResmush())
                    $url_image= $this->getLinkTable('blog_'.$table,'thumb').$params['thumb'];
                else
                    $url_image=null;
                $quality_old = Db::getInstance()->getValue('SELECT quality FROM '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = '.(int)$id_obj.' AND type_image="'.pSQL($type).'" AND optimize_type = "'.pSQL(Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT')).'"');
                $compress= $this->compress($path,$params['thumb'],$quality,$url_image,$quality_old);
                while($compress===false)
                {
                    $compress = $this->compress($path,$params['thumb'],$quality,$url_image,$quality_old);
                }
                if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image WHERE id_'.pSQL($table).' = "'.(int)$id_obj.'" AND type_image="'.pSQL($type).'"'))
                {
                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image (id_'.pSQL($table).',type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$id_obj.'","'.pSQL($type).'","'.(int)$quality.'","'.(float)$size_old.'","'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'","'.pSQl($compress['optimize_type']).'")');
                } 
                else
                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ets_superspeed_blog_'.pSQL($table).'_image SET quality="'.(int)$quality.'",size_old="'.(float)$size_old.'",size_new="'.($compress['file_size'] < $size_old ? (float)$compress['file_size'] : (float)$size_old).'",optimize_type="'.pSQL($compress['optimize_type']).'" WHERE id_'.pSQL($table).' = '.(int)$id_obj.' AND type_image="'.pSQL($type).'"');
            }
        }
    }
    public function png_has_transparency( $filename ) {
        if ( Tools::strlen( $filename ) == 0 || !file_exists( $filename ) )
            return false;
        if ( ord( call_user_func('file_get_contents',$filename, false, null, 25, 1)) & 4 )
        {   
             return true;
        }   
        $contents = Tools::file_get_contents( $filename );
        if (stripos( $contents, 'PLTE' ) !== false && stripos( $contents, 'tRNS' ) !== false )
            return true;
        
        return false;
    }
    public function ajaxSubmitOptimizeImage($all_type)
    {
        if(!Tools::isSubmit('resume'))
        {
            Configuration::updateValue('ETS_SPEEP_RESUMSH',2);
            Configuration::updateValue('ETS_SP_ERRORS_TINYPNG','');
            Configuration::updateValue('ETS_SP_TOTAL_IMAGE_OPTIMIZED',0);
            Configuration::updateValue('ETS_SP_LIST_IMAGE_OPTIMIZED','');
        }
        $optimize_type  = Tools::getValue('optimize_type','products');
        switch ($optimize_type) {
            case 'products':
                $this->optimizeProductImage($all_type);
            case 'categories':
                $this->optimiziObjImage('category','categories',_PS_CAT_IMG_DIR_,$all_type,'manufacturers');
            case 'manufacturers':
                $this->optimiziObjImage('manufacturer','manufacturers',_PS_MANU_IMG_DIR_,$all_type,'suppliers');
            case 'suppliers':
                $next= $this->isblog ? 'post' : ($this->isSlide ? 'home_slide' :'other_image');
                $this->optimiziObjImage('supplier','suppliers',_PS_SUPP_IMG_DIR_,$all_type,$next);
            case 'post':
                if($this->isblog)
                    $this->optimiziBlogImage('post',_PS_MODULE_DIR_.'ybc_blog/views/img/post/',$all_type,'category');
            case 'category':
                if($this->isblog)
                    $this->optimiziBlogImage('category',_PS_MODULE_DIR_.'ybc_blog/views/img/category/',$all_type,'gallery');
            case 'gallery':
                if($this->isblog)
                    $this->optimiziBlogImage('gallery',_PS_MODULE_DIR_.'ybc_blog/views/img/gallery/',$all_type,'slide');
            case 'slide':
                if($this->isblog)
                {
                    $next = $this->isSlide ? 'home_slide' :'other_image';
                    $this->optimiziBlogImage('slide',_PS_MODULE_DIR_.'ybc_blog/views/img/slide/',$all_type,$next); 
                }
            case 'home_slide':
                if($this->isSlide)
                    $this->optimiziSlideImage($all_type);
            case 'other_image':
                $this->optimiziOthersImage($all_type);
        }
    }
    public function displayError($errors,$popup=false)
    {
        $this->context->smarty->assign(
            array(
                'errors'=>$errors,
                'popup' => $popup
            )
        );
        return $this->display(__FILE__,'error.tpl');
    }
    public function displayGoogleError()
    {
        return $this->display(__FILE__,'google.tpl');
    }
    public function displayPageCaches()
    {
        $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
        $totalRecords = (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ets_superspeed_cache_page WHERE id_shop='.(int)$this->context->shop->id.' AND request_uri not like "%check_speed=%"');
        $paggination = new Ets_superspeed_paggination_class();            
        $paggination->total = $totalRecords;
        $paggination->url = $this->context->link->getAdminLink('AdminSuperSpeedPageCaches', true).'&current_tab=page-list-caches&page=_page_';
        $paggination->limit =  10;
        $totalPages = ceil($totalRecords / $paggination->limit);
        if($page > $totalPages)
            $page = $totalPages;
        $paggination->page = $page;
        $start = $paggination->limit * ($page - 1);
        if($start < 0)
            $start = 0;
        $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
        $paggination->style_links = $this->l('links');
        $paggination->style_results = $this->l('results');
        $file_caches= Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'ets_superspeed_cache_page` WHERE id_shop="'.(int)$this->context->shop->id.'" AND request_uri not like "%check_speed=%" ORDER BY date_upd desc LIMIT '.(int)$start.','.(int)$paggination->limit);   
        if($file_caches)
        {
            foreach($file_caches as &$file_cache)
            {
                $file_cache['basename'] = basename($file_cache['file_cache']);
                if($file_cache['file_size']==0)
                {
                    $file_cache['file_size'] = Tools::ps_round(@filesize($file_cache['file_cache'])/1024,2);
                }
            }
        }
        $this->context->smarty->assign(
            array(
                'file_caches' => $file_caches,
                'page_caches' => true,
                'paggination' => $paggination->render(),
            )
        );  
        return $this->display(__FILE__,'file_caches.tpl');
    }
    public function renderSpeedSystemAnalytics()
    {
        $sql_filter = '';
        $orderby = Tools::getValue('Orderby','pht.time');
        $orderway = Tools::getValue('Orderway','desc');
        if(Tools::isSubmit('submitFilterModule'))
        {
            $filter= array();
            if(trim(Tools::getValue('module_name'))!='')
            {
                $filter['module_name'] = Tools::getValue('module_name');
                $sql_filter .=' AND m.name like "%'.pSQL($filter['module_name']).'%"';
                
            }
            if(trim(Tools::getValue('hook_name'))!='')
            {
                $filter['hook_name']= Tools::getValue('hook_name');
                $sql_filter .=' AND pht.hook_name like "%'.pSQL($filter['hook_name']).'%"';
            }
            if(trim(Tools::isSubmit('disabled')))
            {
                $filter['disabled'] = Tools::getValue('disabled');
                if($filter['disabled']!='')
                {
                    if($filter['disabled']==1)
                    {
                         $sql_filter .=' AND phm.id_module is not null'; 
                    }
                    else
                    {
                        unset($filter['disabled']);
                        $sql_filter .=' AND phm.id_module is null';
                    }  
                }
            }
            if(Tools::getValue('date_add_from') && Validate::isDate(Tools::getValue('date_add_from')))
            {
                $filter['date_add_from'] = Tools::getValue('date_add_from');
                $sql_filter .=' AND pht.date_add >= "'.pSQL(Tools::getValue('date_add_from')).'"';
            }
            if(Tools::getValue('date_add_to') && Validate::isDate(Tools::getValue('date_add_to')))
            {
                $filter['date_add_to'] = Tools::getValue('date_add_to');
                $sql_filter .=' AND pht.date_add <= "'.pSQL(Tools::getValue('date_add_to')).'"';
            }
            if(Tools::getValue('module_page'))
            {
                $filter['module_page'] = Tools::getValue('module_page');
                $sql_filter .= 'AND pht.page LIKE "%'.pSQL(Tools::getValue('module_page')).'%"';
            }
            if(Tools::getValue('module_time_min'))
            {
                $filter['module_time_min'] = Tools::getValue('module_time_min');
                $sql_filter .= ' AND pht.time >="'.((float)$filter['module_time_min']/1000).'"';
            }
            if(Tools::getValue('module_time_max'))
            {
                $filter['module_time_max'] = Tools::getValue('module_time_max');
                $sql_filter .= ' AND pht.time <= "'.((float)$filter['module_time_max']/1000).'"';
            }
            if($filter)
            {
                $filter['submitFilterModule']=1;
                $this->context->smarty->assign(
                    array(
                        'filter'=>$filter,
                    )
                );
            }
        }
        else
            $sql_filter = 'AND phm.id_module is null';
        $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
        $totalRecords =count(Db::getInstance()->executeS('SELECT DISTINCT pht.*, phm.id_module as disabled FROM '._DB_PREFIX_.'ets_superspeed_hook_time pht
        INNER JOIN '._DB_PREFIX_.'module_shop ms ON (pht.id_module = ms.id_module)
        LEFT JOIN '._DB_PREFIX_.'ets_superspeed_hook_module phm ON phm.id_module= pht.id_module
        LEFT JOIN '._DB_PREFIX_.'hook h ON h.id_hook= phm.id_hook
        WHERE ms.id_shop="'.(int)$this->context->shop->id.'" AND pht.id_module!="'.(int)$this->id.'" AND pht.id_shop="'.(int)$this->context->shop->id.'"'.$sql_filter));
        $paggination = new Ets_superspeed_paggination_class();            
        $paggination->total = $totalRecords;
        $paggination->url = $this->context->link->getAdminLink('AdminSuperSpeedSystemAnalytics', true).'&page=_page_'.(isset($filter) ? $this->getFilterValues($filter):'').'&Orderby='.$orderby.'&OrderWay='.$orderway;
        $paggination->limit =  20; 
        $totalPages = ceil($totalRecords / $paggination->limit);
        if($page > $totalPages)
            $page = $totalPages;
        $paggination->page = $page;
        $start = $paggination->limit * ($page - 1);
        if($start < 0)
            $start = 0;
        $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
        $paggination->style_links = $this->l('links');
        $paggination->style_results = $this->l('results');
        $module_hooks = Db::getInstance()->executeS('SELECT DISTINCT pht.*, phm.id_module as disabled FROM '._DB_PREFIX_.'ets_superspeed_hook_time pht
        INNER JOIN '._DB_PREFIX_.'module_shop ms ON (pht.id_module = ms.id_module)
        LEFT JOIN '._DB_PREFIX_.'ets_superspeed_hook_module phm ON phm.id_module= pht.id_module
        LEFT JOIN '._DB_PREFIX_.'hook h ON h.id_hook= phm.id_hook
        WHERE ms.id_shop="'.(int)$this->context->shop->id.'" AND pht.id_module!="'.(int)$this->id.'" AND pht.id_shop="'.(int)$this->context->shop->id.'"'.$sql_filter.' ORDER BY '.pSQl($orderby).' '.pSQL($orderway).' LIMIT '.(int)$start.','.(int)$paggination->limit);
        if($module_hooks)
        {
            foreach($module_hooks as &$module_hook)
            {
                $module = Module::getInstanceById($module_hook['id_module']);
                $module_hook['display_name'] = $module->displayName;
                $module_hook['logo'] = $this->getBaseLink().'/modules/'.$module->name.'/logo.png';
                $module_hook['page'] = str_replace(array('&check_speed=1','?check_speed=1'),'',$module_hook['page']);
            }
        }
        $this->context->smarty->assign(
            array(
                'module_hooks' => $module_hooks,
                'extra_hooks' => $this-> getCheckPoints(),
                'orderby' => $orderby,
                'orderway' => $orderway,
                'tab_current' => Tools::getValue('tab_current','module_performance'),
                'PS_RECORD_MODULE_PERFORMANCE' => Configuration::get('PS_RECORD_MODULE_PERFORMANCE'),
                'url_base' => $this->context->link->getAdminLink('AdminSuperSpeedSystemAnalytics', true).'&page='.(int)Tools::getValue('page',1).(isset($filter) ? $this->getFilterValues($filter):''),
                'paggination' => $paggination->render(),
            )
        );
        if(Tools::isSubmit('paggination_ajax'))
        {
            $this->context->smarty->assign(
                array(
                    'ajax' => 1,
                )
            );
        }
        return $this->display(__FILE__,'system_analytics.tpl');
    }
    public function getFilterValues($filter)
    {
        $text = '';
        if($filter)
        {
            foreach($filter as $key=>$value)
            {
                $text .= '&'.$key.'='.$value;
            }
        }
        return $text;
    }
    public function getImageInSite()
    {
        $url = $this->context->link->getPageLink('index',null,null,array('check_speed'=>1));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $htmlString = curl_exec($curl);
        curl_close($curl);
        return array(
            'count_img' => substr_count($htmlString,'<img'),
            'count_css' => substr_count($htmlString,'.css'),
            'count_js' => ($totaljs = substr_count($htmlString,'.js')) && $totaljs > 1 ? $totaljs -1 : $totaljs,
        );
    }
    private function updateDebugModeValueInCustomFile($value)
    {
        $customFileName = _PS_ROOT_DIR_ . '/config/defines.inc.php';
        $cleanedFileContent = php_strip_whitespace($customFileName);
        $fileContent = Tools::file_get_contents($customFileName);
        if (!empty($cleanedFileContent) && preg_match('/define\(\'_PS_MODE_DEV_\', ([a-zA-Z]+)\);/Ui', $cleanedFileContent)) {
            $fileContent = preg_replace('/define\(\'_PS_MODE_DEV_\', ([a-zA-Z]+)\);/Ui', 'define(\'_PS_MODE_DEV_\', ' . $value . ');', $fileContent);
            if (!@file_put_contents($customFileName, $fileContent)) {
                return false;
            }
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($customFileName);
            }
            return true;
        }
    }
    public function getCheckPoints($after_ajax=true)
    {
        $extra_hooks = array();
        if(!$after_ajax)
            $totals = $this->getImageInSite();
        if(($this->is17 && Module::isEnabled('ps_imageslider')) || (!$this->is17 && Module::isEnabled('homeslider')))
            $extra_hooks[]=array(
                'name' => 'home_slider',
                'check_point' => $this->l('Home slider images'),
                'number_data' => Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'homeslider_slides hs
                    INNER JOIN '._DB_PREFIX_.'homeslider h ON (hs.id_homeslider_slides = h.id_homeslider_slides)
                    WHERE hs.active=1 AND h.id_shop='.(int)$this->context->shop->id),
                'url_config' => $this->context->link->getAdminLink('AdminModules').'&configure='.($this->is17 ? 'ps_imageslider':'homeslider'),
                'recommendation' => $this->l('Should not more than 3 items'),
                'default'=>3,
                'bad' => 6,
            );
        if(($this->is17 && Module::isEnabled('ps_featuredproducts')) || (!$this->is17 && Module::isEnabled('homefeatured')))
            $extra_hooks[] = array(
                'name' =>'popular_product',
                'check_point' => $this->l('Popular products'), 
                'number_data' => Configuration::get('HOME_FEATURED_NBR'), 
                'url_config' => $this->context->link->getAdminLink('AdminModules').'&configure='.($this->is17 ? 'ps_featuredproducts':'homefeatured'),
                'recommendation' => $this->l('Should not more than 8 items'),
                'default'=>8,
                'bad' => 12,
            );
        if( Module::isEnabled('ps_newproducts')||Module::isEnabled('blocknewproducts'))
            $extra_hooks[] = array(
                'name' => 'new_product',
                'check_point' => $this->l('New products'),
                'number_data' => Configuration::get('NEW_PRODUCTS_NBR'),
                'url_config' => $this->context->link->getAdminLink('AdminModules').'&configure='.($this->is17 ? 'ps_newproducts':'blocknewproducts'),
                'recommendation' => $this->l('Should not more than 8 items'),
                'default'=>8,
                'bad' => 12,
            );
        if(Module::isEnabled('blockspecials') || Module::isEnabled('ps_specials'))
            $extra_hooks[] = array(
                'name' => 'sepcials_product',
                'check_point' => $this->l('Specials'),
                'number_data' => Configuration::get('BLOCKSPECIALS_SPECIALS_NBR'),
                'url_config' => $this->context->link->getAdminLink('AdminModules').'&configure='.($this->is17 ? 'ps_specials':'blockspecials'),
                'recommendation' => $this->l('Should not more than 8 items'),
                'default'=>8,
                'bad' => 12,
            );
        if(Module::isEnabled('blockbestsellers') || Module::isEnabled('ps_bestsellers'))
            $extra_hooks[] = array(
                'name' => 'best_seller',
                'check_point' => $this->l('Best seller'),
                'number_data' => Configuration::get('PS_BLOCK_BESTSELLERS_TO_DISPLAY'),
                'url_config' => $this->context->link->getAdminLink('AdminModules').'&configure='.($this->is17 ? 'ps_bestsellers':'blockbestsellers'),
                'recommendation' => $this->l('Should not more than 8 items'),
                'default'=>8,
                'bad' => 16,
            );  
        if(Module::isEnabled('ps_categoryproducts'))
            $extra_hooks[] = array(
                'name' => 'product_category',
                'check_point' => $this->l('Products in the same category'),
                'number_data' => Configuration::get('CATEGORYPRODUCTS_DISPLAY_PRODUCTS'), 
                'url_config' => $this->context->link->getAdminLink('AdminModules').'&configure=ps_categoryproducts',
                'recommendation' => $this->l('Should not more than 8 items'),
                'default'=>8,
                'bad' => 16,
            );
        $extra_hooks2 = array(
            array(
                'name' => 'category_page',
                'check_point' => $this->l('Products per page on category page'),
                'number_data' => Configuration::get('PS_PRODUCTS_PER_PAGE'), 
                'url_config' => $this->context->link->getAdminLink('AdminPPreferences'),
                'recommendation' => $this->l('Should not more than 12 items'),
                'default'=>12,
                'bad' => 24,
            ),
            array(
                'name' => 'image_home',
                'check_point' => $this->l('Number of images on home page'),
                'number_data' => $after_ajax || !$totals['count_img'] ? '-':$totals['count_img'], 
                'url_config' =>'' ,
                'recommendation' => $this->l('Should not more than 30 images. Consider to minimize the number of images displayed on home page.'),
                'default'=>30,
                'bad' => 50,
            ),
            array(
                'name' => 'css_home',
                'check_point' => $this->l('Number of CSS files (home page)'),
                'number_data' => $after_ajax || !$totals['count_css']  ? '-' : $totals['count_css'], 
                'url_config' =>'' ,
                'recommendation' => $this->l('Should not more than 5 files. Enable Minify CSS to combine all CSS files into 1 file'),
                'default'=>5,
                'bad' => 10,
                'url_config' => $this->context->link->getAdminLink('AdminSuperSpeedMinization'),
            ),
            array(
                'name' => 'script_home',
                'check_point' => $this->l('Number of JavaScript files (home page)'),
                'number_data' => $after_ajax || !$totals['count_js'] ? '-' : $totals['count_js'], 
                'url_config' =>'' ,
                'recommendation' => $this->l('Should not more than 5 files. Enable Minify JavaScript to combine all JavaScript files into 1 file'),
                'default'=>5,
                'bad' => 10,
                'url_config' => $this->context->link->getAdminLink('AdminSuperSpeedMinization'),
            ),
            array(
                'name' =>'media_server',
                'check_point' => $this->l('Media servers'),
                'url_config' =>$this->context->link->getAdminLink('AdminPerformance').'#fieldset_4_4',
                'recommendation' => $this->l('Configure Media servers in order to use cookieless static content'),
                'enabled' => Configuration::get('PS_MEDIA_SERVER_1') || Configuration::get('PS_MEDIA_SERVER_2') || Configuration::get('PS_MEDIA_SERVER_3'),
                'server' => Configuration::get('PS_MEDIA_SERVER_1') ? Configuration::get('PS_MEDIA_SERVER_1') : (Configuration::get('PS_MEDIA_SERVER_2') ? Configuration::get('PS_MEDIA_SERVER_2') : Configuration::get('PS_MEDIA_SERVER_3')),
                'bad' => 16,
            ),
            array(
                'name' => 'caching_server',
                'check_point' => $this->l('Caching system'),
                'url_config' =>$this->context->link->getAdminLink('AdminPerformance').'#fieldset_5_5' ,
                'recommendation' => $this->l('Enable Memcached, APC or Xcache (if they are supported by your server) to maximize website speed.'),
                'enabled' => _PS_CACHE_ENABLED_,
                'server' => _PS_CACHE_ENABLED_ ? _PS_CACHING_SYSTEM_ :'',
                'bad' => 16,
            )
        );
        return array_merge($extra_hooks,$extra_hooks2);
    }
    public function ajaxGetTotalImageInSite()
    {
        $totals = $this->getImageInSite();
        if($totals['count_ima'])
        {
            if($totals['count_img'] <=30)
            {
                $status = $this->l('Good');
                $class_name = 'status-good';
            }
            elseif($totals['count_img']>50)
            {
                $status = $this->l('Bad');
                $class_name ='status-bad';
            }
            else
            {
                $status = $this->l('Acceptable');
                $class_name = 'status-reputable';
            }
        }
        else
        {
            $totals['count_img']='-';
            $status ='-';
            $class_name ='';
        }
        if($totals['count_css'])
        {
            if($totals['count_css'] <=5)
            {
                $status_css = $this->l('Good');
                $class_name_css = 'status-good';
            }
            elseif($totals['count_css']>10)
            {
                $status_css = $this->l('Bad');
                $class_name_css ='status-bad';
            }
            else
            {
                $status_css = $this->l('Acceptable');
                $class_name_css = 'status-reputable';
            }
        }
        else
        {
            $totals['count_css']='-';
            $status_css ='-';
            $class_name_css ='';
        }
        if($totals['count_js'])
        {
            if($totals['count_js'] <=5)
            {
                $status_script = $this->l('Good');
                $class_name_script = 'status-good';
            }
            elseif($totals['count_js']>10)
            {
                $status_script = $this->l('Bad');
                $class_name_script ='status-bad';
            }
            else
            {
                $status_script = $this->l('Acceptable');
                $class_name_script = 'status-reputable';
            }
        }
        else
        {
            $totals['count_js']='-';
            $status_script ='-';
            $class_name_script ='';
        }
        die(
            Tools::jsonEncode(
                array(
                    'total_images' => $totals['count_img'],
                    'status' => $status,
                    'class_name'=> $class_name,
                    'total_css' => $totals['count_css'],
                    'status_css' => $status_css,
                    'class_name_css'=> $class_name_css,
                    'total_script' => $totals['count_js'],
                    'status_script' => $status_script,
                    'class_name_script'=> $class_name_script,
                )
            ) 
        );
    }
    public function hookDisplayImagesBrowse()
    {
        $dir_files = $this->globImagesToFolder(_PS_ROOT_DIR_);
        $images = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_browse_image ORDER BY id_ets_superspeed_browse_image DESC');
        if($images)
        {
            foreach($images as &$image)
            {
                $image['saved'] = Tools::ps_round(($image['old_size']-$image['new_size'])*100/$image['old_size'],2).'%';
                $image['old_size'] = $image['old_size'] < 1024 ? $image['old_size'].'KB' : Tools::ps_round($image['old_size']/2014,2).'MB';
                $image['new_size'] = $image['new_size'] < 1024 ? $image['new_size'].'KB' : Tools::ps_round($image['new_size']/2014,2).'MB';
                $image['image_dir'] = str_replace(str_replace('\\','/',_PS_ROOT_DIR_),'',$image['image_dir']);
                $image['image_name_hide'] = Tools::strlen($image['image_name']) > 23 ? Tools::substr($image['image_name'],0,11).' . . . '.Tools::substr($image['image_name'],Tools::strlen($image['image_name'])-12):$image['image_name'];
            }
        }
        $this->context->smarty->assign(
            array(
                'dir_files' => $dir_files,
                'images' => $images,
            )
        ); 
        return $this->display(__FILE__,'browse_images.tpl');
    }
    public function globImagesToFolder($folder)
    {
        $files = glob($folder.'/*'); 
        $list_files= array();
        $list_folders= array();
        foreach($files as $file){ 
            $name= explode('/',$file);
            if(is_file($file))
            {
                $type = Tools::strtolower(Tools::substr(strrchr($file, '.'), 1));
                if(in_array($type, array('jpg', 'gif', 'jpeg', 'png')) && Tools::strpos($file,'_bk.'.$type)===false)
                {
                    $file_size = Tools::ps_round(@filesize($file)/1024,2);
                    $file_id = MD5(str_replace('\\','/',$file));
                    if(Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_browse_image WHERE image_id="'.pSQL($file_id).'"'))
                        $uploaed = true;
                    else
                        $uploaed = false;
                    $list_files[] = array(
                        'dir' => str_replace('\\','/',$file),
                        'id' => $file_id,
                        'name' =>$name[count($name)-1], 
                        'type'=>'file',
                        'uploaed' =>$uploaed,
                        'file_size' => $file_size <1024 ? $file_size.'KB' : Tools::ps_round($file_size/1024,2).'MB',
                    );
                }
            }
            elseif(Tools::strpos($file,'ss_imagesoptimize')===false)
            {
                $list_folders[] = array(
                    'dir' => str_replace('\\','/',$file),
                    'name' =>$name[count($name)-1],
                    'type' => 'folder', 
                    'id' => MD5(str_replace('\\','/',$file)),
                    'has_file' => $this->checkHasFileInFolder($file),
                );
            }
        }
        $this->context->smarty->assign(
            array(
                'list_files' => array_merge($list_folders,$list_files),
            )
        );
        return $this->display(__FILE__,'dir_list_files.tpl');
    }
    public function checkHasFileInFolder($folder)
    {
        $files = glob($folder.'/*'); 
        foreach($files as $file){ 
            if(is_file($file))
            {
                $type = Tools::strtolower(Tools::substr(strrchr($file, '.'), 1));
                if(in_array($type, array('jpg', 'gif', 'jpeg', 'png')) && Tools::strpos($file,'_bk.'.$type)===false)
                {
                    return true;
                }
            }
        }
        return false;
    }
    public function checkKeyTinyPNG(){
        if($api_keys = Tools::getValue('ETS_SPEED_API_TYNY_KEY'))
        {
              $keys = array();  
              foreach($api_keys as $key)
              {
                    if(trim($key))
                        $keys[]=$key;
              }
              if($keys)
              {
                    Configuration::updateValue('ETS_SPEED_API_TYNY_KEY',implode(';',$keys)); 
                    return true;
              }
                     
        }
        die(
            Tools::jsonEncode(
                array(
                    'errors' => $this->displayError($this->l('Tinypng API key is required'))
                )
            )
        ); 
    }
    public function hookDisplayImagesUploaded()
    {
        $images = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ets_superspeed_upload_image ORDER BY id_ets_superspeed_upload_image DESC');
        if($images)
        {
            foreach($images as &$image)
            {
                $image['saved'] = Tools::ps_round(($image['old_size'] - $image['new_size'])*100/$image['old_size'],2).'%';
                $image['old_size'] = $image['old_size']<1024 ? $image['old_size'].'KB' : Tools::ps_round($image['old_size']/1024,2).'MB';
                $image['new_size'] = $image['new_size']<1024 ? $image['new_size'].'KB' : Tools::ps_round($image['new_size']/1024,2).'MB';
                $image['image_name_hide'] = Tools::strlen($image['image_name'])> 23 ? Tools::substr($image['image_name'],0,11).' . . . '.Tools::substr($image['image_name'],Tools::strlen($image['image_name'])-12):$image['image_name'];
            }
        }
        $this->context->smarty->assign(
            array(
                'images' => $images,
            )
        );
        return $this->display(__FILE__,'images.tpl');
    }
    public function getImagesUnUsed($folder='c',$table='category',$primakey='id_category',$image_type='categories',$delete=false)
    {
        $images = glob(_PS_IMG_DIR_.$folder.'/[1-9]*.jpg');
        if($images)
        {
            foreach($images as $key=> $image)
            {
                if(strpos($image,'_bk.jpg')!==false)
                    unset($images[$key]);
                else
                {
                    $image_name = explode('/',$image);
                    $image_name = $image_name[Count($image_name)-1];
                    $image_name2 = explode('-',$image_name);
                    $id_object = str_replace('.jpg','',$image_name2[0]);
                    if(Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.pSQL($table).' WHERE '.pSQL($primakey).'="'.(int)$id_object.'"'))
                    {
                        $type = str_replace(array($id_object.'-','.jpg'),'',$image_name);
                        if($type==$id_object || Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'image_type WHERE name ="'.pSQL($type).'" AND '.pSQL($image_type).'=1'))
                        {
                            unset($images[$key]);
                        }
                    }
                   
                }
            }
        }
        $total_size =0;
        if($images)
        {
            foreach($images as $image)
            {
                if($delete)
                    @unlink($image);
                else
                    $total_size += filesize($image);
            }
        }
        $total_size = Tools::ps_round($total_size/1024,2);
        return array(
            'total_image' => Count($images),
            'total_size' => $total_size <1024 ? $total_size.'KB': (($total_size = Tools::ps_round($total_size/1024,2)) < 1024 ? $total_size.'MB' : Tools::ps_round($total_size/1024,2).'GB'),
        );
    }
    public function getImagesProductUnUsed($delete=false)
    {
        $images = Db::getInstance()->executeS('SELECT i.id_image FROM '._DB_PREFIX_.'image i
            INNER JOIN '._DB_PREFIX_.'image_shop ims ON (i.id_image= ims.id_image)
            LEFT JOIN '._DB_PREFIX_.'product_shop ps ON (ims.id_product = ps.id_product)
            WHERE ps.id_product is null AND ims.id_shop="'.(int)$this->context->shop->id.'"
        ');
        $total_image =0;
        $total_size = 0;
        if($images)
        {
            foreach($images as $image)
            {
                $image_obj = new Image($image['id_image']);
                if($delete)
                    $image_obj->delete();
                else
                {
                    $path = _PS_PROD_IMG_DIR_.$image_obj->getImgFolder();
                    $product_images = glob($path.'*.jpg');
                    if($product_images)
                    {
                        $total_image += count($product_images);
                        foreach($product_images as $product_image)
                        {
                            $total_size+= filesize($product_image);
                        }
                    } 
                }
                
            }
        }
        $total_size = Tools::ps_round($total_size/1024,2);
        return array(
            'total_image' => $total_image,
            'total_size' => $total_size <1024 ? $total_size.'KB': (($total_size = Tools::ps_round($total_size/1024,2)) < 1024 ? $total_size.'MB' : Tools::ps_round($total_size/1024,2).'GB'),
        );
    }
    public function hookDisplayImagesCleaner()
    {
        $image_category =$this->getImagesUnUsed();
        $image_supplier = $this->getImagesUnUsed('su','supplier','id_supplier','suppliers');
        $image_manufacturer = $this->getImagesUnUsed('m','manufacturer','id_manufacturer','manufacturers');
        $image_product = $this->getImagesProductUnUsed();
        $this->context->smarty->assign(
            array(
                'image_category' => $image_category,
                'image_supplier'=> $image_supplier,
                'image_manufacturer'=> $image_manufacturer,
                'image_product' => $image_product,
            )
        );
        return $this->display(__FILE__,'image_cleaner.tpl');
    }
    public function replaceTemplateProductDefault($delete_cache = true)
    {
        if($this->is17)
        {
            $product_tpl = _PS_THEME_DIR_.'templates/catalog/_partials/miniatures/product.tpl';
            $product_tpl_bk = _PS_THEME_DIR_.'templates/catalog/_partials/miniatures/product.ssbackup.tpl';
            if(file_exists(_PS_THEME_DIR_.'modules/ps_imageslider/views/templates/hook/slider.tpl'))
                $slide_tpl= _PS_THEME_DIR_.'modules/ps_imageslider/views/templates/hook/slider.tpl';
            else    
                $slide_tpl= _PS_MODULE_DIR_.'ps_imageslider/views/templates/hook/slider.tpl';
            $slide_tpl_bk= _PS_MODULE_DIR_.'ps_imageslider/views/templates/hook/slider.ssbackup.tpl';
            
            if(file_exists(_PS_THEME_DIR_.'modules/ps_banner/ps_banner.tpl'))
                $banner_tpl= _PS_THEME_DIR_.'modules/ps_banner/ps_banner.tpl';
            else    
                $banner_tpl= _PS_MODULE_DIR_.'ps_banner/ps_banner.tpl';
            $banner_tpl_bk = _PS_MODULE_DIR_.'ps_banner/ps_banner.ssbackup.tpl';
        }
        elseif($this->is16)
        {
            $product_tpl = _PS_THEME_DIR_.'product-list.tpl';
            $product_tpl_bk = _PS_THEME_DIR_.'product-list.ssbackup.tpl';
            if(file_exists(_PS_THEME_DIR_.'modules/homeslider/homeslider.tpl'))
                $slide_tpl= _PS_THEME_DIR_.'modules/homeslider/homeslider.tpl';
            else    
                $slide_tpl= _PS_MODULE_DIR_.'homeslider/views/templates/hook/homeslider.tpl';
            $slide_tpl_bk= _PS_MODULE_DIR_.'homeslider/views/templates/hook/homeslider.ssbackup.tpl';
            
            if(file_exists(_PS_THEME_DIR_.'modules/blockbanner/blockbanner.tpl'))
                $banner_tpl= _PS_THEME_DIR_.'modules/blockbanner/blockbanner.tpl';
            else    
                $banner_tpl= _PS_MODULE_DIR_.'blockbanner/blockbanner.tpl';
            $banner_tpl_bk = _PS_MODULE_DIR_.'blockbanner/blockbanner.ssbackup.tpl';
            if(file_exists(_PS_THEME_DIR_.'modules/themeconfigurator/views/templates/hook/hook.tpl'))
                $themeconfigurator_tpl= _PS_THEME_DIR_.'modules/themeconfigurator/views/templates/hook/hook.tpl';
            else    
                $themeconfigurator_tpl= _PS_MODULE_DIR_.'themeconfigurator/views/templates/hook/hook.tpl';
            $themeconfigurator_tpl_bk = _PS_MODULE_DIR_.'themeconfigurator/views/templates/hook/hook.ssbackup.tpl';
        }
        if((int)Configuration::get('ETS_SPEED_ENABLE_LAYZY_LOAD'))
        {
            if(Configuration::get('ETS_SPEED_LAZY_FOR'))
                $image_for = explode(',',Configuration::get('ETS_SPEED_LAZY_FOR'));
            else
                $image_for = array();
            $bloklazyload = Tools::file_get_contents(dirname(__FILE__).'/views/templates/hook/blocklazyload.txt');
            $preg_replace_text = '/<' . 'img(.*?)\ssrc(.*?)=(.*?)(")(.*?)(")(.*?>)/is';
            if(in_array('product_list',$image_for))
            {
                if(file_exists($product_tpl) && !file_exists($product_tpl_bk))
                {
                    Tools::copy($product_tpl,$product_tpl_bk);
                    $content = Tools::file_get_contents($product_tpl);
                    if($this->is17)
                        $content = preg_replace($preg_replace_text,'<' . 'img' . ' src="{if isset($ets_link_base)}{$ets_link_base}/modules/'.$this->name.'/views/img/preloading.png{else}$5{/if}" class="lazyload" data-src="$5"$7'.$bloklazyload,$content);
                    else
                        $content = preg_replace($preg_replace_text,'<' .'img' . ' src="{if isset($ets_link_base)}{$ets_link_base}/modules/'.$this->name.'/views/img/preloading.png{else}$5{/if}" class="replace-2x img-responsive lazyload" data-src="$5"$7'.$bloklazyload,$content);
                    file_put_contents($product_tpl,$content);
                }
            }
            elseif(file_exists($product_tpl_bk))
            {
                Tools::copy($product_tpl_bk,$product_tpl);
                @unlink($product_tpl_bk);
            }
            if(in_array('home_slide',$image_for))
            {
                if(file_exists($slide_tpl) && !file_exists($slide_tpl_bk))
                {
                    Tools::copy($slide_tpl,$slide_tpl_bk);
                    $content = Tools::file_get_contents($slide_tpl);
                    $content = preg_replace($preg_replace_text,'<' . 'img' . ' src="{if isset($ets_link_base)}{$ets_link_base}/modules/'.$this->name.'/views/img/preloading.png{else}$5{/if}" class="lazyload" data-src="$5"$7'.$bloklazyload,$content);
                    file_put_contents($slide_tpl,$content);
                }
            }
            elseif(file_exists($slide_tpl_bk))
            {
                Tools::copy($slide_tpl_bk,$slide_tpl);
                    @unlink($slide_tpl_bk);
            }
            if(in_array('home_banner',$image_for))
            {
                if(file_exists($banner_tpl) && !file_exists($banner_tpl_bk))
                {
                    if(file_exists($banner_tpl))
                        Tools::copy($banner_tpl,$banner_tpl_bk);
                    $content = Tools::file_get_contents($banner_tpl);
                    $content = preg_replace($preg_replace_text,'<' . 'img' . ' src="{if isset($ets_link_base)}{$ets_link_base}/modules/'.$this->name.'/views/img/preloading.png{else}$5{/if}" class="lazyload" data-src="$5"$7'.$bloklazyload,$content);
                    file_put_contents($banner_tpl,$content);
                }
            }
            elseif(file_exists($banner_tpl_bk))
            {
                Tools::copy($banner_tpl_bk,$banner_tpl);
                @unlink($banner_tpl_bk);
            } 
            if($this->is16)
            {
                if(in_array('home_themeconfig',$image_for))
                {
                    if(file_exists($themeconfigurator_tpl) && !file_exists($themeconfigurator_tpl_bk))
                    {
                        if(file_exists($themeconfigurator_tpl))
                            Tools::copy($themeconfigurator_tpl,$themeconfigurator_tpl_bk);
                        $content = Tools::file_get_contents($themeconfigurator_tpl);
                        $content = preg_replace($preg_replace_text,'<' . 'img' . ' src="{if isset($ets_link_base)}{$ets_link_base}/modules/'.$this->name.'/views/img/preloading.png{else}$5{/if}" class="lazyload" data-src="$5"$7'.$bloklazyload,$content);
                        file_put_contents($themeconfigurator_tpl,$content);
                    }
                }
                elseif(file_exists($themeconfigurator_tpl_bk))
                {
                    Tools::copy($themeconfigurator_tpl_bk,$themeconfigurator_tpl);
                    @unlink($themeconfigurator_tpl_bk);
                }
            }
            
        }
        else
        {
            if(file_exists($product_tpl_bk))
            {
                Tools::copy($product_tpl_bk,$product_tpl);
                @unlink($product_tpl_bk);
            }
            if(file_exists($banner_tpl_bk))
            {
                Tools::copy($banner_tpl_bk,$banner_tpl);
                @unlink($banner_tpl_bk);
            }
            if(file_exists($slide_tpl_bk))
            {
                Tools::copy($slide_tpl_bk,$slide_tpl);
                @unlink($slide_tpl_bk);
            }
            if($this->is16 &&  file_exists($themeconfigurator_tpl_bk))
            {
                Tools::copy($themeconfigurator_tpl_bk,$themeconfigurator_tpl);
                @unlink($themeconfigurator_tpl_bk);
            }
        }
        if($delete_cache)
        {
            if(!$this->_class_ss_cache)
                $this->getClassCahe();
            $this->_class_ss_cache->deleteCache();
            Tools::clearSmartyCache();
            Tools::clearXMLCache();
            Media::clearCache();
            if(Module::isInstalled('ets_homecategories'))
            {
                $ets_homecategories = Module::getInstanceByName('ets_homecategories');
                if(method_exists($ets_homecategories,'clearCache'))
                    $ets_homecategories->clearCache();
            }
        }
        
        return true;
    }
    public function submitDeleteSystemAnalytics()
    {
        Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ets_superspeed_hook_time');
        die(
            Tools::jsonEncode(
                array(
                    'success' => $this->l('Clear successfully'),
                )
            )
        );
    }
    public function hookActionPageCacheAjax()
    {
        $data=array();
        Media::addJsDef(array(
            'comparedProductsIds' => $this->context->smarty->getTemplateVars('compared_products'),
            'isLogged' => (bool)$this->context->customer->isLogged(),
            'isGuest' => (bool)$this->context->customer->isGuest(),
            'static_token'=> Tools::getToken(false),
        ));
        $js_def = Media::getJsDef();
        if(isset($js_def['prestashop']))
            unset($js_def['prestashop']);
        $this->context->smarty->assign(array(
            'js_def' => $js_def,
        ));
        $javascript = $this->context->smarty->fetch(_PS_ALL_THEMES_DIR_.'javascript.tpl');
        $data['java_script']=$javascript;
        if(file_exists(_PS_MODULE_DIR_.'ets_superspeed/views/js/script_custom.js'))
            $data['custom_js']=true;
        if((int)Tools::getValue('count_datas'))
        {
            for($i=0;$i<(int)Tools::getValue('count_datas');$i++)
            {
                if(Tools::getValue('hook_'.$i) && Tools::getValue('module_'.$i))
                {
                    $hook_name= Tools::getValue('hook_'.$i);
                    $id_module=Tools::getValue('module_'.$i);
                    if($id_module == Module::getModuleIdByName('ps_facetedsearch'))   
                        $this->context->smarty->assign(
                        array(
                            'listing' => $this->getProductSearchVariables()
                        )
                    ); 
                    $params=array();
                    if(isset($this->product))
                        $params['product']=$this->product;
                    if(isset($this->category))
                        $params['category'] = $this->category;
                    $data[$id_module.$hook_name]= Hook::exec($hook_name,$params,$id_module);
                }
            }
        }
        header('X-Robots-Tag: noindex, nofollow', true);
        die(Tools::jsonEncode($data));
    }
}