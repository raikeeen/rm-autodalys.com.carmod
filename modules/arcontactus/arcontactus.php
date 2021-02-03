<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

define('AR_CONTACTUS_DEBUG', false);

include_once dirname(__FILE__).'/classes/ArContactUsGeneralConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsButtonConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsMenuConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsMenuMobileConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsCallbackConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsTable.php';
include_once dirname(__FILE__).'/classes/ArContactUsPromptTable.php';
include_once dirname(__FILE__).'/classes/ArContactUsCallback.php';
include_once dirname(__FILE__).'/classes/ArContactUsGeneralConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsPromptConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsLiveChatConfig.php';


class ArContactUs extends Module
{
    const REMIND_TO_RATE = 259200; // 3 days
    const ADDONS_ID = 32669;
    
    protected $html;
    protected $configModel;
    protected $buttonConfig;
    protected $menuConfig;
    protected $menuMobileConfig;
    protected $callbackConfig;
    protected $promptConfig;
    protected $liveChatConfig;


    protected $securityKey;

    protected $models = null;

    protected $rendered = false;

    public function __construct()
    {
        $this->name = 'arcontactus';
        $this->tab = 'front_office_features';
        $this->version = '1.6.4';
        $this->author = 'Azelab';
        $this->controllers = array('ajax');
        $this->need_instance = 0;
        $this->bootstrap = true;
        if ($this->is17()) {
            $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        }
        parent::__construct();

        $this->displayName = $this->l('All-in-One Messaging');
        $this->description = $this->l('Display contact us button with menu on every page.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete all data?');
        
        $this->configModel = new ArContactUsGeneralConfig($this, 'arcu_');
        $this->buttonConfig = new ArContactUsButtonConfig($this, 'arcub_');
        $this->menuConfig = new ArContactUsMenuConfig($this, 'arcum_');
        $this->menuMobileConfig = new ArContactUsMenuMobileConfig($this, 'arcumm_');
        $this->callbackConfig = new ArContactUsCallbackConfig($this, 'arcuc_');
        $this->promptConfig = new ArContactUsPromptConfig($this, 'arcupr_');
        $this->liveChatConfig = new ArContactUsLiveChatConfig($this, 'arcul_');
        
        $this->configModel->loadFromConfig();
    }
    
    public function getConfigModel()
    {
        if (!$this->configModel->isLoaded()) {
            $this->configModel->loadFromConfig();
        }
        return $this->configModel;
    }
    
    public function getCallbackConfigModel()
    {
        if (!$this->callbackConfig->isLoaded()) {
            $this->callbackConfig->loadFromConfig();
        }
        return $this->callbackConfig;
    }
    
    public function install()
    {
        Configuration::updateValue('ARCU_INSTALL_TS', time());
        $this->updateSecurityKey();
        if (!parent::install()
                || !$this->registerHook('displayHeader')
                || !$this->registerHook('displayFooter')
                || !$this->registerHook('displayAzlCustomFooter')
                || !$this->registerHook('registerGDPRConsent')
                || !$this->registerHook('actionDeleteGDPRCustomer')
                || !$this->registerHook('actionExportGDPRData')
                || !$this->registerHook('displayProductButtons')
                || !$this->registerHook('displayProductAdditionalInfo')
                || !$this->installDB()
                || !$this->installTab()
                || !$this->installDefaults()
                || !$this->installMenu()
                || !$this->installPrompts()
            ) {
            return false;
        }
        $this->compilleDesktopCss();
        $this->compilleMobileCss();
        return true;
    }
    
    public function hookActionDeleteGDPRCustomer($customer)
    {
        $id_customer = null;
        if (isset($customer['id'])) {
            $id_customer = $customer['id'];
        } elseif (isset($customer['id_customer'])) {
            $id_customer = $customer['id_customer'];
        }
        if ($id_customer) {
            $sql = "DELETE FROM `"._DB_PREFIX_."arcontactus_callback` WHERE id_user = '".((int)$id_customer)."'";
            if (Db::getInstance()->Execute($sql)) {
                return Tools::jsonEncode(true);
            }
            return json_encode($this->l('Contact Us module : Unable to delete customer.'));
        }
    }
    
    public function hookActionExportGDPRData($customer)
    {
        $id_customer = null;
        if (isset($customer['id'])) {
            $id_customer = $customer['id'];
        } elseif (isset($customer['id_customer'])) {
            $id_customer = $customer['id_customer'];
        }
        if ($id_customer) {
            $sql = "SELECT * FROM `"._DB_PREFIX_."arcontactus_callback` WHERE id_user = '".((int)$id_customer)."'";
            if ($res = Db::getInstance()->ExecuteS($sql)) {
                return json_encode($res);
            }
            return json_encode($this->l('Contact Us module : Unable to export customer data.'));
        }
    }
    
    public function installDB()
    {
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus` (
                `id_contactus` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `icon` VARCHAR(50) NULL DEFAULT NULL,
                `color` VARCHAR(10) NULL DEFAULT NULL,
                `type` TINYINT(3) UNSIGNED NULL DEFAULT "0",
                `display` TINYINT(3) UNSIGNED NULL DEFAULT "1",
                `link` VARCHAR(255) NULL DEFAULT NULL,
                `integration` VARCHAR(50) NULL DEFAULT NULL,
                `js` TEXT NULL,
                `status` TINYINT(3) UNSIGNED NULL DEFAULT "1",
                `registered_only` TINYINT(3) UNSIGNED NULL DEFAULT "0",
                `target` TINYINT(3) UNSIGNED NULL DEFAULT "0",
                `product_page` TINYINT(3) UNSIGNED NULL DEFAULT "0",
                `always` TINYINT(3) UNSIGNED NULL DEFAULT "1",
                `d1` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d2` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d3` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d4` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d5` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d6` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d7` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `time_from` TIME NULL DEFAULT NULL,
                `time_to` TIME NULL DEFAULT NULL,
                `position` INT(10) UNSIGNED NULL DEFAULT "0",
                PRIMARY KEY (`id_contactus`),
                INDEX `position` (`position`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_callback` (
                `id_callback` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_user` INT(10) UNSIGNED NULL DEFAULT NULL,
                `phone` VARCHAR(50) NULL DEFAULT NULL,
                `created_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `status` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `comment` TEXT NULL,
                PRIMARY KEY (`id_callback`),
                INDEX `id_user` (`id_user`),
                INDEX `phone` (`phone`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_lang` (
                `id_contactus` INT(11) NOT NULL,
                `id_lang` INT(11) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `subtitle` VARCHAR(255) NULL DEFAULT NULL,
                PRIMARY KEY (`id_contactus`, `id_lang`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_prompt` (
                `id_prompt` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `status` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `position` INT(10) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id_prompt`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_prompt_lang` (
                `id_prompt` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_lang` INT(11) UNSIGNED NOT NULL,
                `message` TEXT NULL,
                PRIMARY KEY (`id_prompt`, `id_lang`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        return true;
    }
    
    public function installMenu()
    {
        $menu = array(
            array(
                'icon' => 'facebook-messenger',
                'color' => '#0084ff',
                'link' => 'https://m.me/areamaDevelopment',
                'title' => $this->l('Facebook messenger'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0
            ),
            array(
                'icon' => 'whatsapp',
                'color' => '#1ebea5',
                'link' => 'https://web.whatsapp.com/send?l=en&phone=34640563252',
                'title' => $this->l('WhatsApp'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0
            ),
            array(
                'icon' => 'viber',
                'color' => '#7c529d',
                'link' => 'viber://pa?chatURI=areama',
                'title' => $this->l('Viber'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0
            ),
            array(
                'icon' => 'telegram-plane',
                'color' => '#2ca5e0',
                'link' => 'https://t.me/areama',
                'title' => $this->l('Telegram'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0
            ),
            array(
                'icon' => 'skype',
                'color' => '#31c4ed',
                'link' => 'https://join.skype.com/bot/80924817-9809-4b5a-8941-8474cbd414',
                'title' => $this->l('Skype'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0
            ),
            array(
                'icon' => 'envelope',
                'color' => '#ff8400',
                'link' => 'mailto:support@myshop.com',
                'title' => $this->l('Send an email'),
                'display' => 1,
                'always' => 1,
                'type' => 0,
                'status' => 0,
                'registered_only' => 0
            ),
            array(
                'icon' => 'comments',
                'color' => '#7eb105',
                'link' => '{contact}',
                'title' => $this->l('Message to contact form'),
                'display' => 1,
                'target' => 1,
                'always' => 1,
                'type' => 0,
                'status' => 1,
                'registered_only' => 0
            ),
            array(
                'icon' => 'phone',
                'color' => '#54cd81',
                'link' => '{callback}',
                'title' => $this->l('Call me back'),
                'display' => 1,
                'always' => 1,
                'type' => 3,
                'status' => 1,
                'registered_only' => 0
            )
        );
        
        foreach ($menu as $k => $item) {
            $model = new ArContactUsTable();
            $model->icon = $item['icon'];
            $model->color = $item['color'];
            $model->link = $item['link'];
            $model->title = $item['title'];
            $model->status = $item['status'];
            $model->registered_only = $item['registered_only'];
            $model->display = $item['display'];
            $model->always = $item['always'];
            $model->type = $item['type'];
            $model->target = isset($item['target'])? $item['target'] : 0;
            $model->position = $k;
            $model->save();
        }
        return true;
    }
    
    public function installPrompts()
    {
        $items = array(
            array(
                'message' => $this->l('Hello!'),
                'status' => 0
            ),
            array(
                'message' =>  $this->l('Have a questions?'),
                'status' => 0
            ),
            array(
                'message' =>  $this->l('Please use this button to contact us!'),
                'status' => 0
            ),
        );
        
        foreach ($items as $k => $item) {
            $model = new ArContactUsPromptTable();
            $model->message = $item['message'];
            $model->status = $item['status'];
            $model->position = $k;
            $model->save();
        }
        return true;
    }
    
    public function uninstallDb()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus_lang`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus_callback`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus_prompt`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus_prompt_lang`');
        return true;
    }
    
    public function installTab()
    {
        // Prepare tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminArContactUs';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'AdminArContactUs';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        $res = $tab->add();
        
        // Prepare tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminArContactUsPrompt';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'AdminArContactUsPrompt';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        return $res && $tab->add();
    }
    
    public function reset()
    {
        return $this->installDefaults();
    }
    
    public function uninstall()
    {
        return parent::uninstall() && $this->clearConfig() && $this->uninstallDb();
    }
    
    protected function clearConfig()
    {
        foreach ($this->getForms() as $model) {
            $model->clearConfig();
        }
        return true;
    }
    
    public function getModels($id_lang, $day, $time, $product_page = false)
    {
        if ($product_page) {
            if (Context::getContext()->customer->id) {
                $logged = true;
            } else {
                $logged = false;
            }
            return ArContactUsTable::getAll($id_lang, false, $day, $time, $logged, $product_page);
        }
        if ($this->models === null) {
            if (Context::getContext()->customer->id) {
                $logged = true;
            } else {
                $logged = false;
            }
            $this->models = ArContactUsTable::getAll($id_lang, true, $day, $time, $logged, $product_page);
        }
        return $this->models;
    }
    
    public function hookDisplayAzlCustomFooter($params)
    {
        return $this->hookFooter($params);
    }
    
    public function hookBlockFooter1($params)
    {
        return $this->hookFooter($params);
    }
    
    public function hookFooterBlock1($params)
    {
        return $this->hookFooter($params);
    }
    
    protected function composeURL($url)
    {
        $replacements = array();
        if (preg_match_all('/{(.*?)}/is', $url, $variables)) {
            if (isset($variables[1])) {
                foreach ($variables[1] as $var) {
                    $replacements["{{$var}}"] = $this->replaceContent($var, Context::getContext());
                }
            }
        }
        $res = strtr($url, $replacements);
        return trim($res);
    }
    
    public function replaceContent($source, $params)
    {
        $variable = null;
        $default = "{{$source}}";
        if (strpos($source, '|') !== false) {
            $data = explode('|', $source);
            $source = $data[0];
            $default = $data[1];
        }
        if (strpos($source, '.') !== false) {
            $var = explode('.', $source);
            $variable = null;
            
            foreach ($var as $k => $v) {
                if ($k == 0 && isset($params->$v)) {
                    $variable = $params->$v;
                } elseif ($k == 0 && !isset($params->$v)) {
                    return $default;
                } elseif ($k > 0 && $variable && is_array($variable) && isset($variable[$v])) {
                    $variable = $variable[$v];
                } elseif ($k > 0 && $variable && is_array($variable) && !isset($variable[$v])) {
                    return $default;
                } elseif ($k > 0 && $variable && is_object($variable) && isset($variable->$v)) {
                    $variable = $variable->$v;
                } elseif ($k > 0 && $variable && is_object($variable) && !isset($variable->$v)) {
                    return $default;
                }
            }
            return $variable;
        } elseif (isset($params->$source) && $params->$source) {
            return $params[$source];
        }
        return $default;
    }
    
    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->hookDisplayProductButtons($params);
    }

    public function hookDisplayProductButtons($params)
    {
        if ($this->configModel->sandbox) {
            $ips = explode("\r\n", $this->configModel->allowed_ips);
            if (!in_array($this->configModel->getCurrentIP(), $ips)) {
                return null;
            }
        }
        $id_lang = Context::getContext()->language->id;
        $day = date('N');
        $time = date('H:i:s');
        $models = $this->getModels($id_lang, $day, $time, true);
        if (count($models) == 0) {
            return null;
        }
        if (!$this->configModel->mobile && $this->isMobile()) {
            return null;
        }
        $items = array();
        foreach ($models as $model) {
            if ($model['display'] == 1 || ($model['display'] == 2 && !$this->isMobile()) || ($model['display'] == 3 && $this->isMobile())) {
                $item = array(
                    'href' => $model['link'] == '{callback}'? 'callback' : $this->composeURL($model['url']),
                    'target' => $model['target'] == '0'? '_blank' : '_self',
                    'color' => $model['color'],
                    'title' => $model['title'],
                    'subtitle' => $model['subtitle'],
                    'type' => $model['type'],
                    'integration' => $model['integration'],
                    'id' => 'msg-item-' . $model['id_contactus'],
                    'class' => 'msg-item-' . $model['icon'] . ' ' . ArContactUsTable::getDisplayClass($model['display']),
                    'js' => str_replace('\\', '', $model['js']),
                    'icon' => ArContactUsAbstract::getIcon($model['icon'])
                );
                $items[] = $item;
            }
        }
        $this->smarty->assign(array(
            'path' => $this->getPath(),
            'isMobile' => $this->isMobile(),
            'is17' => $this->is17(),
            'securityKey' => Configuration::get('arcukey'),
            'items' => $items
        ));
        return $this->display(__FILE__, 'product_actions.tpl');
    }

    public function hookFooter($params)
    {
        if ($this->configModel->sandbox) {
            $ips = explode("\r\n", $this->configModel->allowed_ips);
            if (!in_array($this->configModel->getCurrentIP(), $ips)) {
                return null;
            }
        }
        
        $id_lang = Context::getContext()->language->id;
        $day = date('N');
        $time = date('H:i:s');
        $models = $this->getModels($id_lang, $day, $time);
        if (count($models) == 0) {
            return null;
        }
        if (!$this->configModel->mobile && $this->isMobile()) {
            return null;
        }
        
        $this->callbackConfig->message[$id_lang] = nl2br($this->callbackConfig->message[$id_lang]);
        $this->callbackConfig->success_message[$id_lang] = nl2br($this->callbackConfig->success_message[$id_lang]);
        $this->callbackConfig->fail_message[$id_lang] = nl2br($this->callbackConfig->fail_message[$id_lang]);
        $this->callbackConfig->proccess_message[$id_lang] = nl2br($this->callbackConfig->proccess_message[$id_lang]);
        
        $prompts = ArContactUsPromptTable::getAll($id_lang, true);
        $messages = array();
        foreach ($prompts as $prompt) {
            $messages[] = $prompt['message'];
        }
        $items = array();
        
        $facebook = false;
        $vk = false;
        $intercom = false;
        $tawkto = false;
        $crisp = false;
        $skype = false;
        $zopim = false;
        $zalo = false;
        $lhc = false;
        $ss = false;
        $lc = false;
        $tidio = false;
        $lcp = false;
        $liveZilla = false;
        
        foreach ($models as $model) {
            if ($model['display'] == 1 || ($model['display'] == 2 && !$this->isMobile()) || ($model['display'] == 3 && $this->isMobile())) {
                $item = array(
                    'href' => $model['link'] == '{callback}'? 'callback' : $this->composeURL($model['url']),
                    'target' => $model['target'] == '0'? '_blank' : '_self',
                    'color' => $model['color'],
                    'title' => $model['title'],
                    'subtitle' => $model['subtitle'],
                    'type' => $model['type'],
                    'integration' => $model['integration'],
                    'id' => 'msg-item-' . $model['id_contactus'],
                    'class' => 'msg-item-' . $model['icon'] . ' ' . ArContactUsTable::getDisplayClass($model['display']),
                    'js' => str_replace('\\', '', $model['js']),
                    'icon' => ArContactUsAbstract::getIcon($model['icon'])
                );
                $items[] = $item;
                switch ($model['integration']) {
                    case 'facebook':
                        $facebook = true;
                        break;
                    case 'vk':
                        $vk = true;
                        break;
                    case 'tawkto':
                        $tawkto = true;
                        break;
                    case 'crisp':
                        $crisp = true;
                        break;
                    case 'intercom':
                        $intercom = true;
                        break;
                    case 'zopim':
                        $zopim = true;
                        break;
                    case 'skype':
                        $skype = true;
                        break;
                    case 'zalo':
                        $zalo = true;
                        break;
                    case 'lhc':
                        $lhc = true;
                        break;
                    case 'smartsupp':
                        $ss = true;
                        break;
                    case 'livechat':
                        $lc = true;
                        break;
                    case 'tidio':
                        $tidio = true;
                        break;
                    case 'livechatpro':
                        $lcp = true;
                        break;
                    case 'livezilla':
                        $liveZilla = true;
                        break;
                }
            }
        }
        $this->smarty->assign(array(
            'config' => $this->configModel,
            'buttonConfig' => $this->buttonConfig,
            'menuConfig' => $this->isMobile()? $this->menuMobileConfig : $this->menuConfig,
            'popupConfig' => $this->callbackConfig,
            'promptConfig' => $this->promptConfig,
            'liveChatConfig' => $this->liveChatConfig,
            'tawkToIntegrated' => $this->liveChatConfig->isTawkToIntegrated() && $tawkto,
            'crispIntegrated' => $this->liveChatConfig->isCrispIntegrated() && $crisp,
            'intercomIntegrated' => $this->liveChatConfig->isIntercomIntegrated() && $intercom,
            'facebookIntegrated' => $this->liveChatConfig->isFacebookChatIntegrated() && $facebook,
            'vkIntegrated' => $this->liveChatConfig->isVkIntegrated() && $vk,
            'zopimIntegrated' => $this->liveChatConfig->isZopimIntegrated() && $zopim,
            'isZendesk' => $this->liveChatConfig->isZendeskChat(),
            'skypeIntegrated' => $this->liveChatConfig->isSkypeIntegrated() && $skype,
            'zaloIntegrated' => $this->liveChatConfig->isZaloIntegrated() && $zalo,
            'lhcIntegrated' => $this->liveChatConfig->isLhcIntegrated() && $lhc,
            'ssIntegrated' => $this->liveChatConfig->isSmartsuppIntegrated() && $ss,
            'lcIntegrated' => $this->liveChatConfig->isLiveChatIntegrated() && $lc,
            'tidioIntegrated' => $this->liveChatConfig->isTidioIntegrated() && $tidio,
            'lcp' => $this->liveChatConfig->isLiveChatProIntegrated() && $lcp,
            'liveZilla' => $this->liveChatConfig->isLiveZillaIntegrated() && $liveZilla,
            'buttonIcon' => ArContactUsAbstract::getIcon($this->buttonConfig->button_icon),
            'messages' => Tools::jsonEncode($messages),
            'messagesCount' => count($messages),
            'id_lang' => $id_lang,
            'items' => $items,
            'modelsCount' => count($items),
            'path' => $this->getPath(),
            'isMobile' => $this->isMobile(),
            'is17' => $this->is17(),
            'securityKey' => Configuration::get('arcukey'),
            'customer' => Context::getContext()->customer
        ));
        return $this->display(__FILE__, 'footer.tpl');
    }
    
    public function isReCaptchaIntegrated()
    {
        return $this->getCallbackConfigModel()->recaptcha && $this->getCallbackConfigModel()->key && $this->getCallbackConfigModel()->secret;
    }
    
    public function hookDisplayHeader($params)
    {
        if ($this->configModel->sandbox) {
            $ips = explode("\r\n", $this->configModel->allowed_ips);
            if (!in_array($this->configModel->getCurrentIP(), $ips)) {
                return null;
            }
        }
        $id_lang = Context::getContext()->language->id;
        $day = date('N');
        $time = date('H:i:s');
        $models = $this->getModels($id_lang, $day, $time);
        if (count($models) == 0) {
            return null;
        }
        if (!$this->configModel->mobile && $this->isMobile()) {
            return null;
        }
        $useReCaptcha = false;
        $this->callbackConfig->loadFromConfig();
        if ($this->isReCaptchaIntegrated()) {
            if ($this->is17() && method_exists(Context::getContext()->controller, 'registerJavascript')) {
                Context::getContext()->controller->registerJavascript(
                    'remote-g-recaptcha',
                    'https://www.google.com/recaptcha/api.js?render=' . $this->callbackConfig->key,
                    array('server' => 'remote', 'position' => 'bottom', 'priority' => 50)
                );
            } else {
                Context::getContext()->controller->addJS('https://www.google.com/recaptcha/api.js?render=' . $this->callbackConfig->key);
            }
            $useReCaptcha = true;
        }
        $this->callbackConfig->loadFromConfig();
        if ($this->callbackConfig->phone_mask_on) {
            Context::getContext()->controller->addJS($this->_path.'views/js/jquery.maskedinput.min.js');
        }
        Context::getContext()->controller->addJS($this->_path.'views/js/jquery.contactus.min.js');
        Context::getContext()->controller->addJS($this->_path.'views/js/scripts.js');
        Context::getContext()->controller->addCSS($this->_path.'views/css/jquery.contactus.min.css');
        if ($this->isMobile()) {
            Context::getContext()->controller->addCSS($this->_path.'views/css/generated-mobile.css');
        } else {
            Context::getContext()->controller->addCSS($this->_path.'views/css/generated-desktop.css');
        }
        $this->buttonConfig->loadFromConfig();
        if ($this->isMobile()) {
            $this->menuMobileConfig->loadFromConfig();
        } else {
            $this->menuConfig->loadFromConfig();
        }
        $this->promptConfig->loadFromConfig();
        $this->liveChatConfig->loadFromConfig();
        
        if ($this->buttonConfig->drag) {
            Context::getContext()->controller->addJqueryUI('ui.draggable');
        }
        
        $this->smarty->assign(array(
            'config' => $this->configModel,
            'buttonConfig' => $this->buttonConfig,
            'menuConfig' => $this->isMobile()? $this->menuMobileConfig : $this->menuConfig,
            'callbackConfig' => $this->callbackConfig,
            'promptConfig' => $this->promptConfig,
            'liveChatConfig' => $this->liveChatConfig,
            'id_lang' => Context::getContext()->language->id,
            'models' => $models,
            'modelsCount' => count($models),
            'path' => $this->getPath(),
            'useReCaptcha' => $useReCaptcha,
            'isMobile' => $this->isMobile(),
            'is17' => $this->is17(),
            'ajaxUrl' => $this->getFrontAjaxUrl()
        ));
        
        return $this->display(__FILE__, 'head.tpl', $this->getCacheId());
    }
    
    protected function getFrontAjaxUrl()
    {
        return Context::getContext()->link->getModuleLink($this->name, 'ajax');
    }


    protected function getCacheId($name = null)
    {
        $id = parent::getCacheId($name);
        return $id . '|' . $this->isMobile();
    }

    public function isMobile()
    {
        return Context::getContext()->getMobileDetect()->isMobile() || Context::getContext()->getMobileDetect()->isTablet();
    }
    
    protected function installDefaults()
    {
        foreach ($this->getForms() as $model) {
            $model->loadDefaults();
            $model->saveToConfig(false);
        }
        return true;
    }
    
    public function getForms()
    {
        return array(
            $this->configModel,
            $this->buttonConfig,
            $this->menuConfig,
            $this->menuMobileConfig,
            $this->callbackConfig,
            $this->promptConfig,
            $this->liveChatConfig
        );
    }
    
    public function getContent()
    {
        if ($this->isSubmit()) {
            if ($this->postValidate()) {
                $this->postProcess();
            }
        }
        Context::getContext()->controller->addJqueryPlugin('tablednd');
        Context::getContext()->controller->addJS($this->_path.'views/js/jquery.maskedinput.min.js');
        Context::getContext()->controller->addJS($this->_path.'views/js/moment.min.js');
        Context::getContext()->controller->addCss($this->_path.'views/css/admin.css');
        Context::getContext()->controller->addJS($this->_path.'views/js/admin.js');
        $this->html .= $this->renderForm();
        return $this->html;
    }
    
    public function isSubmit()
    {
        foreach ($this->getAllowedSubmits() as $submit) {
            if (Tools::isSubmit($submit)) {
                return true;
            }
        }
    }
    
    public function getAllowedSubmits()
    {
        $submits = array();
        foreach ($this->getForms() as $model) {
            $submits[] = get_class($model);
        }
        return $submits;
    }
    
    public function postProcess()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                $model->populate();
                if ($model->saveToConfig()) {
                    $this->compilleDesktopCss();
                    $this->compilleMobileCss();
                    $this->html .= $this->displayConfirmation($this->l('Settings updated'));
                } else {
                    $this->postValidate();
                }
            }
        }
        $this->updateSecurityKey();
        $this->clearCache();
    }
    
    public function updateSecurityKey()
    {
        Configuration::updateValue('arcukey', Tools::passwdGen(8));
    }
    
    public function postValidate()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                $model->loadFromConfig();
                $model->populate();
                if (!$model->validate()) {
                    foreach ($model->getErrors() as $errors) {
                        foreach ($errors as $error) {
                            $this->html .= $this->displayError($error);
                        }
                    }
                    return false;
                }
                return true;
            }
        }
    }
    
    public function compilleDesktopCss()
    {
        if (!$this->menuConfig->isLoaded()) {
            $this->menuConfig->loadFromConfig();
        }
        if (!$this->callbackConfig->isLoaded()) {
            $this->callbackConfig->loadFromConfig();
        }
        if (!$this->buttonConfig->isLoaded()) {
            $this->buttonConfig->loadFromConfig();
        }
        $content = $this->render('styles.tpl', array(
            'menuConfig' => $this->menuConfig,
            'callbackConfig' => $this->callbackConfig,
            'buttonConfig' => $this->buttonConfig,
            'isMobile' => false
        ));
        $content = preg_replace('/\s+/is', ' ', $content);
        $content = str_replace(array('; }'), '}', $content);
        $content = str_replace(array('{ '), '{', $content);
        
        if (is_writable($this->getPath(true) . 'views/css/generated-desktop.css')) {
            file_put_contents($this->getPath(true) . 'views/css/generated-desktop.css', $content);
            Configuration::updateValue('arcu_css_generated', time());
            $this->clearCache();
        }
    }
    
    public function compilleMobileCss()
    {
        if (!$this->menuMobileConfig->isLoaded()) {
            $this->menuMobileConfig->loadFromConfig();
        }
        if (!$this->callbackConfig->isLoaded()) {
            $this->callbackConfig->loadFromConfig();
        }
        if (!$this->buttonConfig->isLoaded()) {
            $this->buttonConfig->loadFromConfig();
        }
        $content = $this->render('styles.tpl', array(
            'menuConfig' => $this->menuMobileConfig,
            'callbackConfig' => $this->callbackConfig,
            'buttonConfig' => $this->buttonConfig,
            'isMobile' => true
        ));
        $content = preg_replace('/\s+/is', ' ', $content);
        $content = str_replace(array('; }'), '}', $content);
        $content = str_replace(array('{ '), '{', $content);
        if (is_writable($this->getPath(true) . 'views/css/generated-mobile.css')) {
            file_put_contents($this->getPath(true) . 'views/css/generated-mobile.css', $content);
            Configuration::updateValue('arcu_css_generated', time());
            $this->clearCache();
        }
    }
    
    public function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='
            .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'path' => $this->getPath(),
        );
        $helper->base_folder =  dirname(__FILE__);
        $helper->base_tpl = '/views/templates/admin/arcontactus/helpers/form/form.tpl';
        $id_lang = Context::getContext()->language->id;
        
        $this->smarty->assign(array(
            'form' => $helper,
            'models' => ArContactUsTable::getAll($id_lang),
            'promptModels' => ArContactUsPromptTable::getAll($id_lang),
            'formParams' => array($this->getForm($this->configModel)),
            'buttonFormParams' => array($this->getForm($this->buttonConfig)),
            'menuFormParams' => array($this->getForm($this->menuConfig)),
            'menuMobileFormParams' => array($this->getForm($this->menuMobileConfig)),
            'callbackFormParams' => array($this->getForm($this->callbackConfig)),
            'liveChatFormParams' => array($this->getForm($this->liveChatConfig)),
            'promptFormParams' => array($this->getForm($this->promptConfig)),
            'languages' => $this->context->controller->getLanguages(),
            'defaultFormLanguage' => (int)(Configuration::get('PS_LANG_DEFAULT')),
            'icons' => ArContactUsAbstract::getIcons(),
            'onesignalInstalled' => $this->isOnesignalInstalled(),
            'onesignalConfigLink' => $this->context->link->getAdminLink('AdminModules', true).'&configure='
                .'onesignal'.'&tab_module='.$this->tab.'&module_name=onesignal',
            'callbacks' => array(),
            'integrations' => $this->liveChatConfig->getIntegrations(),
            'link' => $this->context->link,
            'path' => $this->getPath(),
            'name' => $this->displayName,
            'currentTime' => date('H:i:s'),
            'version' => $this->version,
            'active_tab' => $this->getActiveTab()
        ));
        return $this->display(__FILE__, 'config.tpl');
    }
    
    public function isOnesignalInstalled()
    {
        if (Module::isInstalled('onesignal')) {
            $module = Module::getInstanceByName('onesignal');
            $enabled = Module::isEnabled('onesignal');
            if ($enabled && version_compare($module->version, '1.5.0', '>=')) {
                return true;
            }
        }
        return false;
    }
    
    public function getActiveTab()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                return get_class($model);
            }
        }
        return null;
    }
    
    public function getFormConfigs()
    {
        $configs = array();
        foreach ($this->getForms() as $form) {
            $configs[] = $this->getForm($form);
        }
        return $configs;
    }
    
    public function getForm($model)
    {
        $model->populate();
        $model->validate(false);
        $config = $model->getFormHelperConfig();
        return array(
            'form' => array(
                'name' => get_class($model),
                'legend' => array(
                    'title' => $model->getFormTitle(),
                    'icon' => $model->getFormIcon()
                ),
                'input' => $config,
                'submit' => array(
                    'name' => get_class($model),
                    'class' => $this->is15()? 'button' : null,
                    'title' => $this->l('Save'),
                )
            )
        );
    }
    
    public function getConfigFieldsValues()
    {
        $values = array();
        foreach ($this->getForms() as $model) {
            $model->loadFromConfig();
            $model->populate();
            foreach ($model->getAttributes() as $attr => $value) {
                $values[$model->getConfigAttribueName($attr)] = $value;
            }
        }
        return $values;
    }
    
    public function renderTable()
    {
        $id_lang = Context::getContext()->language->id;
        $models = ArContactUsTable::getAll($id_lang);
        
        return $this->render('_table.tpl', array(
            'models' => $models
        ));
    }
    
    public function renderPromptTable()
    {
        $id_lang = Context::getContext()->language->id;
        $models = ArContactUsPromptTable::getAll($id_lang);
        
        return $this->render('_prompt_table.tpl', array(
            'promptModels' => $models
        ));
    }
    
    public function renderCallbackTable()
    {
        $models = ArContactUsCallback::getAll();
        foreach ($models as $k => $model) {
            if ($model['id_user']) {
                $customer = new Customer($model['id_user']);
                if (Validate::isLoadedObject($customer)) {
                    $models[$k]['customer'] = $customer;
                }
            }
        }
        return $this->render('_callbacks_table.tpl', array(
            'callbacks' => $models
        ));
    }
    
    public function render($template, $params = array())
    {
        $this->smarty->assign($params);
        return $this->display(__FILE__, $template);
    }
    
    public function sendPush($phone)
    {
        if (file_exists(_PS_MODULE_DIR_ . 'onesignal/classes/AzlOsSubscriber.php')) {
            require_once _PS_MODULE_DIR_ . 'onesignal/classes/AzlOsSubscriber.php';
            $module = Module::getInstanceByName('onesignal');
            $admins = AzlOsSubscriber::getAdmins(array());
            $playerIDs = array();

            foreach ($admins as $admin) {
                $playerIDs[] = $admin['player_id'];
            }
            if ($playerIDs) {
                $ico = Configuration::get('AZL_OS_ADMIN_ICO');
                $res = $module->getApi()->sendMessage($playerIDs, array(
                    'en' => sprintf($this->l('Please call ASAP to phone %s'), $phone)
                ), array(
                    'en' => $this->l('Customer request callback')
                ), $ico);
            }

            return $res;
        }
        return null;
    }
    
    public function sendEmail($phone)
    {
        $templateVars = array(
            '{phone}' => $phone,
            '{date}'  => date('Y-m-d H:i:s')
        );
        
        $res = explode("\r\n", $this->callbackConfig->email_list);
        $res = array_unique($res);
        if (empty($res)) {
            return false;
        }
        $emails = array();
        foreach ($res as $email) {
            if (Validate::isEmail(trim($email))) {
                $emails[] = $email;
            }
        }
        if (empty($emails)) {
            return false;
        }
        $valid = 0;
        foreach ($emails as $email) {
            /* Email sending */
            $valid += MailCore::Send(
                (int)Context::getContext()->language->id,
                'callback',
                $this->l('New callback request'),
                $templateVars,
                $email,
                null,
                null,
                null,
                null,
                null,
                dirname(__FILE__).'/mails/'
            );
        }
        return $valid;
    }
    
    public function getReCaptchaErrors()
    {
        return array(
            'missing-input-secret' => $this->l('The secret parameter is missing. Please check your reCaptcha Secret.'),
            'invalid-input-secret' => $this->l('The secret parameter is invalid or malformed. Please check your reCaptcha Secret.'),
            'missing-input-response' => $this->l('Bot activity detected! Empty captcha value.'),
            'invalid-input-response' => $this->l('Bot activity detected! Captcha value is invalid.'),
            'bad-request' => $this->l('The request is invalid or malformed.'),
        );
    }
    
    public function is15()
    {
        if ((version_compare(_PS_VERSION_, '1.5.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.6.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is16()
    {
        if ((version_compare(_PS_VERSION_, '1.6.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.7.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is17()
    {
        if ((version_compare(_PS_VERSION_, '1.7.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.8.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function getPath($abs = false)
    {
        if ($abs) {
            return _PS_MODULE_DIR_ . $this->name . '/';
        }
        return $this->_path;
    }
    
    public function getModuleBaseUrl()
    {
        return Tools::getShopDomainSsl(true, true).__PS_BASE_URI__ . 'modules/' . $this->name . '/';
    }
    
    public function clearCache()
    {
        $this->_clearCache('head.tpl');
        $this->_clearCache('footer.tpl');
    }
}
