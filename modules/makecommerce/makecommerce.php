<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\PrestaShop\Adapter\ServiceLocator;

require dirname(__FILE__).'/libs/Maksekeskus.php';
require dirname(__FILE__).'/classes/Method.php';

if(file_exists(_PS_MODULE_DIR_ .'makecommerceomniva/makecommerceomniva.php'))
    include_once( _PS_MODULE_DIR_ .'makecommerceomniva/makecommerceomniva.php');
if(file_exists(_PS_MODULE_DIR_ .'makecommercesmartpost/makecommercesmartpost.php'))
    include_once( _PS_MODULE_DIR_ .'makecommercesmartpost/makecommercesmartpost.php');

class MakeCommerce extends PaymentModule
{
    const STATUS_NAME = 'OS_MK_BILLING';

    const IMG_PATH = 'views/img';

    const TYPE_BANK = 'banklinks';
    const TYPE_CARD = 'cards';
    const TYPE_CASH = 'cash';
    const TYPE_OTHER = 'other';
    const TYPE_PAYLATER = 'payLater';
    const TYPE_REDIRECT = 'redirect';
    const TYPE_ALL = 'all';

    const TEST = 0;
    const LIVE = 1;

    const DISPLAY_ORDER_PAGE = 1;
    const DISPLAY_WIDGET = 2;
    const IMG_SIZE_S = 's';
    const IMG_SIZE_M = 'm';
    const IMG_SIZE_L = 'l';

    const CACHE_VALID_TIME = 3600;

    const COUNTRY_ALL = 'all';

    private $fields = array(
        'secret_key',
        'shop_id',
        'publishable_key',
        'secret_key_test',
        'shop_id_test',
        'publishable_key_test',
        'server',
        'methods_display',
        'credit_display',
        'prefill_form',
        'logo_size',
        'methods_order',
        'omniva',
        'omniva_username',
        'omniva_password',
        'omniva_sender_name',
        'omniva_phone',
        'omniva_email',
        'omniva_street',
        'omniva_city',
        'omniva_country',
        'omniva_zip',
        'smartpost',
        'smartpost_username',
        'smartpost_password',
        'smartpost_sender_name',
        'smartpost_phone',
        'smartpost_email',
        'smartpost_city',
        'smartpost_street',
        'smartpost_country',
        'smartpost_zip',
        'log',
        'expanded',
        'sco',
        'parcel_grouping'
    );

    private $lang_fields = array(
        'widget_title'
    );

    private $types = array(
        self::TYPE_BANK,
        self::TYPE_OTHER,
        self::TYPE_CARD,
        self::TYPE_PAYLATER
    );

    private static $api = null;

    private $html = '';

    public function __construct()
    {
        $this->name = 'makecommerce';
        $this->tab = 'payments_gateways';
        $this->version = '3.2.10';
        $this->need_instance = 0;
        $this->author = 'MakeCommerce.net';
        $this->bootstrap = true;
        $this->controllers = array('payment', 'validation', 'confirmation');

        parent::__construct();

        $this->displayName = 'MakeCommerce';
        $this->description = 'Payment Gateway by Maksekeskus AS';

    }

    public function install()
    {
        if (
            !parent::install() OR
            !$this->registerHook('header') OR
            !$this->registerHook('adminOrder') OR
            !$this->registerHook('backOfficeHeader') OR
            !$this->registerHook('paymentOptions') OR
            !$this->registerHook('paymentReturn') OR
            !$this->registerHook('displayHeader') OR
            !$this->createOrderState() OR
            !$this->installTitles()
        ) {
            if(!(Configuration::hasKey('parcel_grouping')))
                Configuration::updateValue('parcel_grouping',1);
            $this->updateTable();
            return false;

        } else {
            $this->createTable();
            $this->downloadCarrierModules('makecommerceomniva');
            $this->updateConfig('omniva', 0);
            $this->downloadCarrierModules('makecommercesmartpost');
            $this->updateConfig('smartpost', 0);
            if(!(Configuration::hasKey('parcel_grouping')))
                Configuration::updateValue('parcel_grouping',1);
            return true;
        }
    }

    public function uninstall()
    {
        $omniva = new MakeCommerceOmniva();
        $omniva->uninstall();
        $omniva_folder = _PS_MODULE_DIR_.'makecommerceomniva';
        $this->recursiveDeleteOnDisk($omniva_folder);

        $smartpost = new MakeCommerceSmartpost();
        $smartpost->uninstall();
        $smartpost_folder = _PS_MODULE_DIR_.'makecommercesmartpost';
        $this->recursiveDeleteOnDisk($smartpost_folder);

        return parent::uninstall();
    }

    public function updateTable()
    {
        $sql = 'DESCRIBE `'._DB_PREFIX_ . 'makecommerce_sco`';
        $columns = Db::getInstance()->executeS($sql);
        $found = false;
        foreach($columns as $col) {
            if($col['Field']=='sco_amounts') {
                $found = true;
                break;
            }
        }
        if(!$found) {
            if (!Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'makecommerce_sco` ADD `sco_amounts` text'));
            return false;
        }
        return true;
    }

    public function createTable()
    {
        if (!Db::getInstance()->execute('
                         CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'makecommerce_sco` (
                          `id_cart` int(10) unsigned NOT NULL,
                          `sco_id` varchar(255) NOT NULL,
                          `sco_amounts` text,
                          PRIMARY KEY (`sco_id`)
                        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
                ')) {
            return false;
        }

        if (!Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'makecommerce_refunds` (
			  `id_makecommerce_refund` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `id_order` int(10) unsigned NOT NULL,
			  `refund_amount` decimal(20,6)	 NOT NULL,
			  `refund_date` datetime	 NOT NULL,
			  PRIMARY KEY (`id_makecommerce_refund`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
		')) {
            return false;
        }
    }

    public function installTitles()
    {
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang)
            $this->installTitle((int)$lang['id_lang']);

        return true;
    }

    protected function downloadCarrierModules($name){

        if(!file_exists(_PS_MODULE_DIR_ .$name.'/'.$name.'.php')){

            file_put_contents( _PS_MODULE_DIR_ .$name.'.zip', file_get_contents(dirname(__FILE__).'/src/'.$name.'.zip'));

            $file = _PS_MODULE_DIR_ .$name.'.zip';

            $zip_folders = array();
            $tmp_folder = _PS_MODULE_DIR_.md5(time());

            $success = false;

            if (Tools::ZipExtract($file, $tmp_folder)) {
                $zip_folders = scandir($tmp_folder);
                if (Tools::ZipExtract($file, _PS_MODULE_DIR_)) {
                    $success = true;
                }
            }

            @unlink($file);
            $this->recursiveDeleteOnDisk($tmp_folder);

            return $success;

        }

    }

    protected function recursiveDeleteOnDisk($dir)
    {
        if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false) {
            return;
        }
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir.'/'.$object) == 'dir') {
                        $this->recursiveDeleteOnDisk($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function installTitle($id_lang)
    {
        $values[$this->prefixed('widget_title')][(int)$id_lang] = 'Pay by banklink or card';
        Configuration::updateValue($this->prefixed('widget_title'), $values[$this->prefixed('widget_title')]);
    }

    public function getContent()
    {
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($this->fields as $field) {
                $this->updateConfig($field, Tools::getValue($field));
                if($field == 'omniva' && Tools::getValue($field) == 1){
                    if (!Module::isInstalled('makecommerceomniva')){
                        $this->updateConfig($field, 0);
                        $api = $this->getApi();
                        if (!is_null($api)) {
                            $omniva = new MakeCommerceOmniva();
                            $omniva->install();
                            $this->updateConfig($field, 1);
                            $this->html .= $this->displayConfirmation($this->l('"Omniva parcel terminals" carrier enabled'));
                        }else{
                            $this->html .= $this->displayError($this->l('Cannot enable "Omniva Parcel terminals" carrier. Save your Shop id, Secret key and Publishable key.'));
                        }
                    }
                }elseif($field == 'omniva' && Tools::getValue($field) == 0){
                    if (Module::isInstalled('makecommerceomniva')){
                        $omniva = new MakeCommerceOmniva();
                        $omniva->uninstall();
                        $this->html .= $this->displayConfirmation($this->l('"Omniva Parcel Terminals carrier" disabled'));
                    }
                }
                if($field == 'smartpost' && Tools::getValue($field) == 1){
                    if (!Module::isInstalled('makecommercesmartpost'))
                    {
                        $this->updateConfig($field, 0);
                        $api = $this->getApi();
                        if (!is_null($api)) {
                            $omniva = new MakeCommerceSmartpost();
                            $omniva->install();
                            $this->updateConfig($field, 1);
                            $this->html .= $this->displayConfirmation($this->l('SmartPost carrier enabled'));
                        }else{
                            $this->html .= $this->displayError($this->l('Cannot enable SmartPost carrier. Save your Shop id, Secret key and Publishable key.'));
                        }
                    }
                }elseif($field == 'smartpost' && Tools::getValue($field) == 0){
                    if (Module::isInstalled('makecommercesmartpost')){
                        $omniva = new MakeCommerceSmartpost();
                        $omniva->uninstall();
                        $this->html .= $this->displayConfirmation($this->l('SmartPost carrier disabled'));
                    }
                }
            }

            foreach ($this->lang_fields as $param){
                $langvalues = array();
                foreach (Language::getLanguages(false) as $key => $language){
                    $langvalues[$language['id_lang']] = Tools::getValue($param.'_'.$language['id_lang']);
                }
                $this->updateConfig($param, $langvalues, true);
            }

            $this->clearPaymentCache();

            $this->html .= $this->displayConfirmation($this->l('Settings updated from MakeCommerce servers'));
        }

        $this->html.=$this->showHeader();
        $this->showForm();
        return $this->html;

    }

    private function showHeader()
    {
        $this->smarty->assign(
            array(
                'path' => $this->getPathUri()
            )
        );
        return $this->display(__FILE__, 'settings_header.tpl');
    }

    private function showForm()
    {
        $field_values = array();
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper = new HelperForm();

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Server'),
                    'name' => 'server',
                    'desc'     => $this->l('For Test Server see more about: ').'<a href="https://maksekeskus.ee/en/for-developers/test-environment/">MakeCommerce Test environment</a>',
                    'lang' => false,
                    'options' => array(
                        'query' => array(
                            array('id'=> self::TEST, 'name' => $this->l('Test server')),
                            array('id'=> self::LIVE, 'name' => $this->l('Live server'))
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Shop id'),
                    'desc'     => $this->l('You get the Shop ID and API keys from our Merchant Portal after sign-up'),
                    'name' => 'shop_id',
                    'size' => 30,
                    'form_group_class' => 'live_settings',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Secret key'),
                    'name' => 'secret_key',
                    'form_group_class' => 'live_settings',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Publishable key'),
                    'name' =>  'publishable_key',
                    'form_group_class' => 'live_settings',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Shop id'),
                    'name' => 'shop_id_test',
                    'form_group_class' => 'test_settings',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Secret key'),
                    'name' => 'secret_key_test',
                    'form_group_class' => 'test_settings',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Publishable key'),
                    'name' =>  'publishable_key_test',
                    'form_group_class' => 'test_settings',
                    'required' => false,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $carriers = Carrier::getCarriers($this->context->employee->id_lang, true, false, false, null, 5);

        $sco_input[] = array(
            'type' => 'switch',
            'label' => $this->l('SimpleCheckout'),
            'form_group_class' => 'sco_switch',
            'name' => 'sco',
            'required' => false,
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'sco_on',
                    'value' => 1,
                    'label' => $this->l('Yes'),
                ),
                array(
                    'id' => 'sco_off',
                    'value' => 0,
                    'label' => $this->l('No'),
                ),
            ),
        );
        $sco_input[] = array('type' => 'hr',  'name'=> '', 'form_group_class' => 'sco_setting');
        $sco_input[] = array('type' => 'hr',  'name'=> '', 'form_group_class' => 'sco_setting');
        $sco_input[] = array('type' => 'hr', 'label' => $this->l('Select pick-up carriers'), 'name'=> '', 'form_group_class' => 'sco_setting');


        foreach ($carriers as $carrier) {
            $sco_input[] = array(
                'type' => 'switch',
                'label' => $carrier['name'],
                'form_group_class' => 'sco_setting',
                'name' => 'carrier_'.$carrier['id_carrier'],
                'required' => false,
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'sco_on',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                    array(
                        'id' => 'sco_off',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                ),
            );
        }

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Simple Checkout'),
            ),
            'input' => $sco_input,
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Design'),
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Display payment channels as'),
                    'name' => 'methods_display',
                    'required' => false,
                    'options' => array(
                        'query' => array(
                            array(
                                'id'=> self::DISPLAY_WIDGET,
                                'name' => $this->l('Widget')
                            ),
                            array(
                                'id'=> self::DISPLAY_ORDER_PAGE,
                                'name' => $this->l('List')
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Widget title'),
                    'name' =>  'widget_title',
                    'form_group_class' => 'widget_setting',
                    'lang' => true,
                    'required' => false,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Size of channel logos'),
                    'form_group_class' => 'widget_setting',
                    'name' => 'logo_size',
                    'required' => false,
                    'options' => array(
                        'query' => array(
                            array(
                                'id'=> self::IMG_SIZE_S,
                                'name' => $this->l('Small'),
                            ),
                            array(
                                'id'=> self::IMG_SIZE_M,
                                'name' => $this->l('Medium'),
                            ),
                            array(
                                'id'=> self::IMG_SIZE_L,
                                'name' => $this->l('Large'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Expanded'),
                    'form_group_class' => 'widget_setting',
                    'name' => 'expanded',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'expanded_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ),
                        array(
                            'id' => 'expanded_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Credit cards in separate group'),
                    'form_group_class' => 'widget_setting',
                    'name' => 'credit_display',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'separate_group_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ),
                        array(
                            'id' => 'separate_group_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Parcel machines not grouped by location'),
                    'name' => 'parcel_grouping',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'pmgrouping_on',
                            'value' => 0,
                            'label' => $this->l('Yes'),
                        ),
                        array(
                            'id' => 'pmgrouping_off',
                            'value' => 1,
                            'label' => $this->l('No'),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Prefill credit card form with customer data'),
                    'name' => 'prefill_form',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'prefill_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'prefill_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Payment methods order'),
                    'desc'     => $this->l('i.e.:  lhv,seb,krediidipank'),
                    'name' =>  'methods_order',
                    'required' => false,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

	$return_countries = array();
        $countries = Country::getCountries($this->context->employee->id_lang);
        foreach ($countries as $country) {
            $return_countries[] = array('id'=> $country['iso_code'], 'name' => $country['country']);
        }

        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Omniva pakiautomaadid'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable Omniva Parcel Terminals carrier'),
                    'name' => 'omniva',
                    'form_group_class' => 'omniva_switch',
                    'required' => false,
                    'is_bool' => true,
                    'desc' => $this->l('If enabled the Omniva Parcel Terminals will appear under Shipping->Carriers. You can configure pricing there. ').'<br>'.$this->l('Here you can confgure integration with Omniva systems. See more about it on ').'<a href="https://https://maksekeskus.ee/maksekeskuse-module-prestashopile/#omniva">Module home page</a> ',
                    'values' => array(
                        array(
                            'id' => 'omniva_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'omniva_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Omniva WebServices Username'),
                    'name' =>  'omniva_username',
                    'form_group_class' => 'omniva_setting',
                    'hint' => $this->l('You get it from your Account Manager in Omniva'),
                    'required' => false,
                    'size' => 30,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Password'),
                    'name' =>  'omniva_password',
                    'form_group_class' => 'omniva_setting',
                    'required' => false,
                ),
                array('type' => 'hr', 'name'=> ''),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - Name'),
                    'name' =>  'omniva_sender_name',
                    'form_group_class' => 'omniva_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - Phone'),
                    'name' =>  'omniva_phone',
                    'form_group_class' => 'omniva_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - Email'),
                    'name' =>  'omniva_email',
                    'form_group_class' => 'omniva_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - City'),
                    'name' =>  'omniva_city',
                    'form_group_class' => 'omniva_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - Street'),
                    'name' =>  'omniva_street',
                    'form_group_class' => 'omniva_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - Postal code'),
                    'name' =>  'omniva_zip',
                    'form_group_class' => 'omniva_setting',
                    'required' => false,
                ),
		array(
                    'type' => 'select',
                    'label' => $this->l('Return address - Country'),
                    'name' => 'omniva_country',
                    'lang' => false,
                    'form_group_class' => 'omniva_setting',
                    'options' => array(
                        'query' => $return_countries,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'required' => false,
                ),	
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->l('SmartPost pakiautomaadid'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable Itella SmartPOST carrier'),
                    'name' => 'smartpost',
                    'form_group_class' => 'smartpost_switch',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'smartpost_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'smartpost_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                    'desc' => $this->l('If enabled the "Itella SmartPOST Parcel Terminals" will appear under Shipping->Carriers. You can configure pricing there.').'<br>'.$this->l('Here you can confgure integration with Itella systems. See more about it on').' <a href="https://https://maksekeskus.ee/maksekeskuse-module-prestashopile/#itella">Module home page</a> ',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('eservice.smartpost.ee Username'),
                    'name' =>  'smartpost_username',
                    'hint' => $this->l('You get after signup on eservice.smartpost.ee'),
                    'form_group_class' => 'smartpost_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Password'),
                    'name' =>  'smartpost_password',
                    'form_group_class' => 'smartpost_setting',
                    'required' => false,
                ),
                array('type' => 'hr', 'name'=> ''),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - Name'),
                    'name' =>  'smartpost_sender_name',
                    'form_group_class' => 'smartpost_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - Phone'),
                    'name' =>  'smartpost_phone',
                    'form_group_class' => 'smartpost_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - Email'),
                    'name' =>  'smartpost_email',
                    'form_group_class' => 'smartpost_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - City'),
                    'name' =>  'smartpost_city',
                    'form_group_class' => 'smartpost_setting',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Return address - Postal code'),
                    'name' =>  'smartpost_zip',
                    'form_group_class' => 'smartpost_setting',
                    'required' => false,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );



        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->languages = Language::getLanguages(false);
        foreach ($helper->languages as $k => $language) {
            $helper->languages[$k]['is_default'] = (int)($language['id_lang'] == $helper->default_form_language);
        }

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        foreach ($this->fields as $param) {
            $field_values[$param] = Configuration::get($this->prefixed($param));
        }

        foreach ($this->lang_fields as $param)
        {
            foreach ($helper->languages as $key => $language)
            {
                $field_values[$param][$language['id_lang']] =
                    Configuration::get($this->prefixed($param), $language['id_lang']);
            }
        }

        $helper->fields_value = $field_values;

        $this->html .= $helper->generateForm($fields_form);
    }


    public function getTranslation($method_name)
    {
        $labels = array(
            'swedbank' => $this->l('Swedbank'),
            'lhv' => $this->l('LHV'),
            'danske' => $this->l('Danske'),
            'seb' => $this->l('SEB'),
            'nordea' => $this->l('Nordea'),
            'krediidipank' => $this->l('Krediidipank'),
            'liisi_ee' => $this->l('Liisi järelmaks'),
            'pocopay'  => $this->l('Pocopay'),
            'visa' => $this->l('VISA'),
            'mastercard' => $this->l('MasterCard'),
            'maestro' => $this->l('Maestro'),
            'banklinks' => $this->l('Banklinks'),
            'cards' => $this->l('Cards'),
            'other' => $this->l('Other'),
            'paylater' => $this->l('Pay later'),
            'redirect' => $this->l('Maksekeskus'),
        );

        return (isset($labels[$method_name]) ? $labels[$method_name] : $method_name);
    }

    public function getPaymentMethodValues($country = null, $type = null)
    {
        $payment_methods = $this->getPaymentMethods();

        if (empty($payment_methods)) {
            return array();
        }

        $values = array();

        foreach ($payment_methods as $payment_method) {
            $method = $this->MkObjectToMethod($payment_method);

            if ((null === $type || $method->type == $type) &&
                (null === $country || $method->country == $country ||
                    ($method->country == self::COUNTRY_ALL && $type === null)
                )
            ) {
                $values[] = $method;
            }
        }
        $this->processMethods($values, $this->getConfig('logo_size'));

        return $values;
    }

    private function getMailVars()
    {
        return array(
            '{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
            '{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
            '{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
        );
    }

    public function confirmOrder($payment_methods = null, $cart_info = null)
    {
        $mailVars = $this->getMailVars();

        if (isset($cart_info) && $cart_info) {

            $id_cart = Db::getInstance()->getValue('SELECT `id_cart` FROM `'._DB_PREFIX_.'makecommerce_sco` WHERE `sco_id` = \''.$cart_info->cartId.'\'');

            $password = Tools::passwdGen(8, 'RANDOM');
            $crypto = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');

            $sco_address = new stdClass();
            $sco_address->id = 1;
            $sco_address->name = 'SimpleCheckout';
            $sco_address->address = 'SimpleCheckout';
            $sco_address->city = 'SimpleCheckout';
		
	    if (isset($cart_info->shipmentAddress->destinationId))
            	$destinationId = $cart_info->shipmentAddress->destinationId;

            if (isset($destinationId) && $destinationId) {
                $terminals_json = Db::getInstance()->getValue(
                    'SELECT `carriers_list` FROM `'._DB_PREFIX_.'makecommerce_carriers` WHERE `carrier` = \''.$cart_info->shipmentMethod->carrier.'\''
                );
                $terminal_data = json_decode($terminals_json);
                $terminals = json_decode(json_encode($terminal_data->terminals), true);

                foreach($terminals as $terminal){
                    if($terminal['id'] == $destinationId){
                        $sco_address->id = $terminal['id'];
                        $sco_address->name = $terminal['name'];
                        if($terminal['country'] == 'EE'){
                            $sco_address->address = $terminal['address'];
                        }else{
                            $sco_address->address = $terminal['city'];
                        }
                        $sco_address->city = $terminal['city'];
                        break;
                    }
                }

		if ($cart_info->shipmentMethod->carrier == 'OMNIVA')
		    $sco_address->postcode = $terminal['id'];
		else if ($cart_info->shipmentMethod->carrier == 'SMARTPOST')
		    $sco_address->postcode = $terminal['id']; // zip

            } elseif($cart_info->shipmentMethod->type == 'OTH') {
                $carrier = new Carrier($cart_info->shipmentMethod->methodId);
                $sco_address->name = $carrier->name;
                $sco_address->address = $carrier->name;
		$sco_address->postcode = '00000';
            } else {
		$sco_address->name = $cart_info->shipmentAddress->street1;
		$sco_address->address = $cart_info->shipmentAddress->street2;
		$sco_address->city = $cart_info->shipmentAddress->city;
		$sco_address->postcode = $cart_info->shipmentAddress->postalCode;
	    }
	    $sco_address->phone = $cart_info->customer->phone;

            $customer = new Customer();
            $customer->firstname = $cart_info->customer->firstname;
            $customer->lastname = $cart_info->customer->lastname;
            $customer->email = $cart_info->customer->email;
            $customer->passwd = $crypto->hash($password);
            $customer->is_guest = 1;
            $customer->add();

            $address = new Address();
            $address->id_country = 86;
            $address->id_customer = $customer->id;
            $address->alias = 'Terminal address';
            $address->firstname = $customer->firstname;
            $address->lastname = $customer->lastname;
            $address->address1 = $sco_address->name;
            $address->address2 = $sco_address->address;
            $address->city = $sco_address->city;
            $address->postcode = $sco_address->postcode;
	    $address->phone_mobile = $sco_address->phone;
            $address->deleted = 1;
	    
	    if ($cart_info->shipmentMethod->carrier == 'OMNIVA' || $cart_info->shipmentMethod->carrier == 'SMARTPOST')
	    	$address->other = $sco_address->postcode;
            
	    $address->save();

            $cart = new Cart($id_cart);
            $cart->id_carrier = $cart_info->shipmentMethod->methodId;
            $cart->id_customer = $customer->id;
            $cart->id_address_delivery = $address->id;
	    $cart->id_address_invoice = $address->id;
            $cart->save();
	    $this->context->cart->id_address_invoice = (string)$address->id;
	    $this->context->cart->id_address_delivery = (string)$address->id;
	    $this->context->cart->id_carrier = (string)$cart_info->shipmentMethod->methodId;
            $this->validateOrder(
                $cart->id,
                Configuration::get(self::STATUS_NAME),
                $cart->getOrderTotal(true, Cart::BOTH, null, $cart->id_carrier),
                $this->displayName . sprintf(' (%s)', $this->getTranslation($payment_methods)),
                NULL,
                $mailVars,
                $cart->id_currency,
                false
            );

            $order = new Order($this->currentOrder);

	    Db::getInstance()->execute(
                'UPDATE `'._DB_PREFIX_.'order_carrier` SET `id_carrier` = '.$cart_info->shipmentMethod->methodId.',
		`shipping_cost_tax_excl` = '.$cart->getOrderTotal(false, Cart::ONLY_SHIPPING,  null, $cart->id_carrier).',
		`shipping_cost_tax_incl` = '.$cart->getOrderTotal(true, Cart::ONLY_SHIPPING,  null, $cart->id_carrier).'  WHERE `id_order` = '.$order->id
            );

	    Db::getInstance()->execute(
                'UPDATE `'._DB_PREFIX_.'orders` SET `id_address_delivery` = '.$address->id.',
		`id_carrier` = '.$cart->id_carrier.',
		`total_paid` = '.$cart->getOrderTotal(true, Cart::BOTH, null, $cart->id_carrier).',
		`total_paid_tax_incl` = '.$cart->getOrderTotal(true, Cart::BOTH, null, $cart->id_carrier).',
		`total_paid_tax_excl` = '.$cart->getOrderTotal(false, Cart::BOTH, null, $cart->id_carrier).',
		`total_paid_real` = '.$cart->getOrderTotal(true, Cart::BOTH, null, $cart->id_carrier).',
		`total_products` = '.$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, null, $cart->id_carrier).',
		`total_products_wt` = '.$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS,  null, $cart->id_carrier).',
		`total_shipping` = '.$cart->getOrderTotal(true, Cart::ONLY_SHIPPING, null, $cart->id_carrier).',
		`total_shipping_tax_incl` = '.$cart->getOrderTotal(true, Cart::ONLY_SHIPPING, null, $cart->id_carrier).',
		`total_shipping_tax_excl` = '.$cart->getOrderTotal(false, Cart::ONLY_SHIPPING,  null, $cart->id_carrier).' WHERE `id_order` = '.$order->id
            );

            $val_url = $this->context->link->getModuleLink($this->name, 'val');

            $response = array(
                'cartId' => $cart_info->cartId,
                'reference' => $order->id,
                'locale' => 'et',
                'transactionUrl' => array(
                    'returnUrl' => array(
                        'url' =>  $val_url,
                        'method' => 'POST'
                    ),
                    'cancelUrl' => array(
                        'url' => $val_url,
                        'method' => 'POST'
                    ),
                    'notificationUrl' => array(
                        'url' => $val_url,
                        'method' => 'POST'
                    ),
                )
            );

            return $response;

        } else {

            $mailVars = $this->getMailVars();

            $this->validateOrder(
                $this->context->cart->id,
                Configuration::get(self::STATUS_NAME),
                $this->context->cart->getOrderTotal(),
                $this->displayName . sprintf(' (%s)', $this->getTranslation($payment_methods)),
                NULL,
                $mailVars,
                $this->context->cart->id_currency,
                false,
                $this->context->customer->secure_key
            );

            return new Order($this->currentOrder);
        }
    }

    public function prefixed($key)
    {
        return Tools::strtoupper($this->name.'_'.$key);
    }

    public function getConfig($key)
    {
        return Configuration::get($this->prefixed($key));
    }

    public function updateConfig($key, $value, $allow_html = false)
    {
        return Configuration::updateValue($this->prefixed($key), $value, $allow_html);
    }

    public function getApi()
    {

        if ($this->getConfig('server') == self::TEST) {
            $shop_id = $this->getConfig('shop_id_test');
            $publishable_key = $this->getConfig('publishable_key_test');
            $secret_key = $this->getConfig('secret_key_test');
        } else {
            $shop_id = $this->getConfig('shop_id');
            $publishable_key = $this->getConfig('publishable_key');
            $secret_key = $this->getConfig('secret_key');
        }

        if (!empty($shop_id) && !empty($publishable_key) && !empty($secret_key)) {
            self::$api = new Maksekeskus(
                $shop_id,
                $publishable_key,
                $secret_key,
                $this->getConfig('server') == self::TEST
            );
        }

        return self::$api;
    }

    private function getPaymentMethods($cache = false)
    {

        $config_key = 'payment_methods';

        if ($cache) {
            $payment_methods_json = $this->getConfig($config_key);
            if (!empty($payment_methods_json)) {
                $payment_methods_data = Tools::jsonDecode($payment_methods_json);
                if (
                    empty($payment_methods_data->updated) ||
                    $payment_methods_data->updated + self::CACHE_VALID_TIME < time()
                ) {
                    $payment_methods_data = null;
                }
            }
        }
        if (!$cache || empty($payment_methods_data) ) {
            $api = $this->getApi();
            if (!is_null($api)) {
                $params = array(
                    'platform' => 'prestashop '._PS_VERSION_,
                    'module' => 'MakeCommerce '.$this->version
                );
                $environment = array(
                    'environment' => json_encode($params)
                );
                try {
                    $payment_methods_data = $api->getShopConfig($environment);
                    $payment_methods_data = $payment_methods_data->paymentMethods;
                } catch (Exception $e) {
                    PrestaShopLogger::addLog(
                        $e->getMessage(),
                        1,
                        null,
                        'MakeCommerce'
                    );
                    return false;
                }
            }

            $methods_order = $this->getConfig('methods_order');

            $methods_order = array_filter(explode(',', $methods_order));
            $methods_order = array_map('trim', $methods_order);
            $methods_order = array_flip($methods_order);
            $payment_methods = array();
            if (isset($payment_methods_data) && $payment_methods_data) {
                foreach ($this->types as $type) {
                    foreach ($payment_methods_data->{$type} as $key => $payment_method) {
                        $payment_method->type = $type;
                        if (property_exists($payment_method, 'url')) {
                            unset($payment_method->url);
                        }

                        if (!property_exists($payment_method, 'country')) {
                            $payment_method->country = self::COUNTRY_ALL;
                        }
                        if (!empty($methods_order)) {
                            if(!array_key_exists($payment_method->name, $methods_order))
                                $payment_methods_default[] = $payment_method;
                        }
                        if (empty($methods_order)) {
                            $payment_methods[] = $payment_method;
                        } elseif (isset($methods_order[$payment_method->name])) {
                            if (empty($payment_methods[$payment_method->name])) {
                                $payment_methods[$payment_method->name] = array();
                            }

                            $payment_methods[$payment_method->name][] = $payment_method;
                        }
                    }
                }
            }


            if (!empty($methods_order)) {
                $payment_methods = array_reduce($payment_methods, 'array_merge', array());
            }

            if (!empty($methods_order)){
                $payment_methods = json_decode(json_encode($payment_methods), true);
                usort($payment_methods, array($this,"paymentMethodOrder"));
                $payment_methods = json_decode(json_encode($payment_methods), false);
                $merged_payment_methods = array_merge($payment_methods, $payment_methods_default);
                $payment_methods = $merged_payment_methods;
            }

            if (!empty($payment_methods)) {
                $object = new stdClass();
                $object->updated = time();
                $object->payment_methods = $payment_methods;
                $this->updateConfig($config_key, Tools::jsonEncode($object));
            }
        } else {
            $payment_methods = $payment_methods_data->payment_methods;
        }

        return $payment_methods;

    }

    public function paymentMethodOrder($a, $b){

        $methods_order = $this->getConfig('methods_order');
        $methods_order = array_filter(explode(',', $methods_order));

        $a = array_search($a["name"], $methods_order);
        $b = array_search($b["name"], $methods_order);

        if ($a === false && $b === false) {
            return 0;
        }
        else if ($a === false) {
            return 1;
        }
        else if ($b === false) {
            return -1;
        }
        else {
            return $a - $b;
        }
    }

    private function createOrderState()
    {
        $orderStateExist = false;
        $orderStateId = Configuration::get(self::STATUS_NAME);
        $description = $this->l('Awaiting maksekeskus payment');

        if (strlen($description) > 64) {
            $description = substr($description, 0, 64);
        }

        if ($orderStateId) {
            $orderState = new OrderState($orderStateId);
            if($orderState->id && !$orderState->deleted) {
                $orderStateExist = true;
            }
        } else {
            $query = 'SELECT os.`id_order_state` '.
                'FROM `%1$sorder_state_lang` osl '.
                'LEFT JOIN `%1$sorder_state` os '.
                'ON osl.`id_order_state`=os.`id_order_state` '.
                'WHERE osl.`name`="%2$s" AND os.`deleted`=0';
            $orderStateId =  Db::getInstance()->getValue(sprintf($query, _DB_PREFIX_, $description));
            if ($orderStateId) {
                Configuration::updateValue(self::STATUS_NAME, $orderStateId);
                $orderStateExist = true;
            }
        }

        if (!$orderStateExist) {
            $languages = Language::getLanguages(false);
            $orderState = new OrderState();
            foreach ($languages as $lang) {
                $orderState->name[$lang['id_lang']] = $description;
            }

            $orderState->send_email = 0;
            $orderState->invoice = 0;
            $orderState->color = "lightblue";
            $orderState->unremovable = 0;
            $orderState->logable = 0;
            $orderState->delivery = 0;
            $orderState->hidden = 0;
            if($orderState->add()) {
                Configuration::updateValue(self::STATUS_NAME, $orderState->id);
                $orderStateExist = true;
            }
        }

        return $orderStateExist;
    }

    public function getImage($method)
    {
        $base_path = 'https://static.maksekeskus.ee/img/channel/lnd/';
        return  $base_path.$method.'.png';
    }

    public function getPaymentMethod($name, $payment_methods = array())
    {
        if (empty($name)) {
            return false;
        }

        if (empty($payment_methods)) {
            $payment_methods = $this->getPaymentMethodValues();
        }

        $result = false;
        foreach ($payment_methods as $method) {

            if ($method->code == $name) {
                $result = $method;
                break;
            }
        }

        return $result;
    }

    public function createPayment($transaction_id, $request_body){
        $api = $this->getApi();
        $api->createPayment($transaction_id, $request_body);
    }

    public function createTransaction($method, $country)
    {
        $order = $this->confirmOrder($method);
        $api = $this->getApi();
        $transaction = null;

        if (!is_null($api) && Validate::isLoadedObject($order)) {
            $currency = new Currency($order->id_currency);
            $customer = new Customer($order->id_customer);
            $address = new Address($order->id_address_delivery);
            $val_url = $this->context->link->getModuleLink($this->name, 'val');

            if($country == 'all')
                $country = Country::getIsoById($address->id_country);

            $data = array(
                'transaction' => array(
                    'amount' => number_format(round(Tools::ps_round($order->total_paid, 2),2),2,".",""),
                    'currency' => $currency->iso_code,
                    'reference' => $order->id,
                    'transaction_url' => array(
                        'return_url' => array(
                            'url' => $val_url,
                            'method' => 'POST'
                        ),
                        'cancel_url' => array(
                            'url' => $val_url,
                            'method' => 'POST'
                        ),
                        'notification_url' => array(
                            'url' => $val_url,
                            'method' => 'POST'
                        )
                    )
                ),
                'customer' => array(
                    'email' => $customer->email,
                    'ip' => $this->getCustomerIp(),
                    'country' => $country,
                    'locale' => Language::getIsoById($order->id_lang)
                )
            );

            $transaction = $api->createTransaction($data);

        }

        return $transaction;
    }

    public function getBankUrlFromTransaction($transaction, $method)
    {
        $url = false;

        if (!is_null($transaction)) {
            foreach ($transaction->payment_methods->{MakeCommerce::TYPE_BANK} as $payment_method) {
                if ($payment_method->name == $method) {
                    $url = $payment_method->url;
                    break;
                }
            }
            foreach ($transaction->payment_methods->{MakeCommerce::TYPE_OTHER} as $payment_method) {
                if ($payment_method->name == $method) {
                    $url = $payment_method->url;
                    break;
                }
            }
            foreach ($transaction->payment_methods->{MakeCommerce::TYPE_PAYLATER} as $payment_method) {
                if ($payment_method->name == $method) {
                    $url = $payment_method->url;
                    break;
                }
            }
        }

        return $url;
    }

    public function getOtherUrlFromTransaction($transaction, $method)
    {
        $url = false;

        if (!is_null($transaction)) {
            foreach ($transaction->payment_methods->{MakeCommerce::TYPE_BANK} as $payment_method) {
                if ($payment_method->name == $method) {
                    $url = $payment_method->url;
                    break;
                }
            }
        }

        return $url;
    }

    public function getPaylaterUrlFromTransaction($transaction, $method)
    {
        $url = false;

        if (!is_null($transaction)) {
            foreach ($transaction->payment_methods->{MakeCommerce::TYPE_BANK} as $payment_method) {
                if ($payment_method->name == $method) {
                    $url = $payment_method->url;
                    break;
                }
            }
        }

        return $url;
    }

    public function getJsDataFromTrasaction($transaction)
    {
        $order = new Order((int)$transaction->reference);
        $customer = new Customer($order->id_customer);

        if ($this->getConfig('server') == self::TEST) {
            $js_src = 'https://payment-test.maksekeskus.ee/checkout/dist/checkout.js';
            $publishable_key = $this->getConfig('publishable_key_test');
        } else {
            $js_src = 'https://payment.maksekeskus.ee/checkout/dist/checkout.js';
            $publishable_key = $this->getConfig('publishable_key');
        }


        return array(
            'prefill' => $this->getConfig('prefill_form'),
            'js_src' => $js_src,
            'publishable_key' => $publishable_key,
            'transaction_id' => $transaction->id,
            'currency' => $transaction->currency,
            'amount' => number_format(round($transaction->amount, 2),2,".",""), // number_format(round(Tools::ps_round($cart->getOrderTotal(true, 1), 2),2),2),
            'customer_email' => $transaction->customer->email,
            'customer_name' => $customer->firstname.' '.$customer->lastname,
            'shop_name' => $this->context->shop->name,
            'description' => sprintf($this->l('Order #%d'), $transaction->reference),
            'locale' => Language::getIsoById($order->id_lang),
            'quick_mode' => true
        );
    }

    public function getCountries($payment_methods = null)
    {
        if (null === $payment_methods) {
            $payment_methods = $this->getPaymentMethods();
        }

        $countries = array();
        if(isset($payment_methods) && $payment_methods){
            foreach ($payment_methods as $payment_method) {
                if ($payment_method->country != self::COUNTRY_ALL && !isset($countries[$payment_method->country])) {
                    $countries[$payment_method->country] = 1;
                }
            }
        }
        return array_keys($countries);
    }

    public function appendPaymentMethods(&$types, $with_credit_cards = false)
    {
        $payment_methods = $this->getPaymentMethods();
        $grouped_methods = array();
        $cards = array();
        if (isset($payment_methods) && $payment_methods) {
            foreach ($payment_methods as $payment_method) {
                $method = $this->MkObjectToMethod($payment_method);
                $key = sprintf('%s_%s', $method->type, $method->country);
                if (!isset($grouped_methods[$key])) {
                    $grouped_methods[$key] = array();
                }
                $grouped_methods[$key][] = $method;

                if ($with_credit_cards && $method->type == self::TYPE_CARD) {
                    $cards[] = $method;
                }
            }
        }
        foreach ($types as &$type) {
            $key = $type->getKey();
            if (isset($grouped_methods[$key])) {
                $type->methods = array_merge($grouped_methods[$key], $cards);
                $this->processMethods($type->methods, self::IMG_SIZE_S);
            }
        }
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->getPathUri().'views/js/makecommerce.js');
        $this->context->controller->addCSS($this->getPathUri().'views/css/makecommerce.css');
    }

    public function hookBackOfficeHeader()
    {
        $this->addCarriersFields();
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->getPathUri().'views/js/makecommerce.js');
        if (Module::isInstalled('makecommerceomniva') || Module::isInstalled('makecommercesmartpost')){
            $this->smarty->assign(array(
                'parcel_lable' => $this->context->link->getModuleLink($this->name, 'parcellabels'),
                'loader_img' => 'http://'.Tools::getMediaServer(_PS_IMG_)._PS_IMG_.'loader.gif'
            ));

            return $this->display(__FILE__, 'bulkaction.tpl');
        }
    }

    public function hookAdminOrder($params)
    {
        $error = '';
        $id_order = $params['id_order'];
        $order = new Order((int)$id_order);
        $maksekeskusPayment = FALSE;
        foreach ($order->getOrderPaymentCollection() as $payment) {
            if($payment->payment_method == 'MakeCommerce') {
                $maksekeskusPayment = TRUE;
                break;
            }
        }
        if ($maksekeskusPayment) {

            if (Tools::isSubmit('submitMKRefund')) {
                $this->_doTotalRefund($id_order);
            }

            if (Tools::isSubmit('submitMKRefundPartial')) {
                $refund_amount = Tools::getValue('mk_partial_refund');
                $order_total = $order->total_paid;
                $refunded_total = $this->refundedAmount($id_order);
                $remaining_amount = $order_total - $refunded_total;

                if (isset($refund_amount) && $refund_amount > 0) {
                    if ($remaining_amount - $refund_amount >= 0) {
                        $this->_doPartialRefund($id_order, $refund_amount);
                    } else {
                        $error = $this->l('Refund amount greater than available to refund.');
                    }
                } else {
                    $error = $this->l('Invalid refund value.');
                }
            }

            $this->smarty->assign(array(
                'error' => $error,
                'id_order' => $id_order,
                'refunded' => $this->refundedAmount($id_order),
                'refund_details' => $this->getRefundDetails($id_order),
                'total_amount' => $order->total_paid
            ));
            return $this->display(__FILE__, 'adminOrder.tpl');
        }
    }

    protected function _doTotalRefund($id_order)
    {
        $order = new Order($id_order);
        $amount = number_format(round(Tools::ps_round($order->total_paid, 2),2),2,".","");
        $transaction_id = Db::getInstance()->getValue(
            'SELECT `transaction_id`
            FROM `'._DB_PREFIX_.'order_payment`
            WHERE order_reference = \''.$order->reference.'\''
        );

        $request_body = array(
            'amount' => $amount,
            'comment' => 'Order: '.$id_order.' refund'
        );

        $api = $this->getApi();
        $api->createRefund($transaction_id, $request_body);
        $this->saveRefundDetails($id_order, $amount);
        $order->setCurrentState(Configuration::get('PS_OS_REFUND'));
        Tools::redirect($_SERVER['HTTP_REFERER']);
    }

    protected function _doPartialRefund($id_order, $amount)
    {
        $order = new Order($id_order);
        $partial_amount = number_format(round(Tools::ps_round($amount, 2),2),2,".","");
        $transaction_id = Db::getInstance()->getValue(
            'SELECT `transaction_id`
            FROM `'._DB_PREFIX_.'order_payment`
            WHERE order_reference = \''.$order->reference.'\''
        );

        $request_body = array(
            'amount' => $partial_amount,
            'comment' => 'Order: '.$id_order.' refund'
        );
        $api = $this->getApi();
        $api->createRefund($transaction_id, $request_body);
        $order->setCurrentState(Configuration::get('PS_OS_REFUND'));
        $this->saveRefundDetails($id_order, $partial_amount);
        Tools::redirect($_SERVER['HTTP_REFERER']);
    }

    private function refundedAmount($id_order)
    {
        $refunded_total = Db::getInstance()->ExecuteS(
            'SELECT SUM(refund_amount) AS total FROM `'._DB_PREFIX_.'makecommerce_refunds` WHERE id_order = \''.$id_order.'\''
        );
        return $refunded_total[0]['total'];
    }

    private function saveRefundDetails($id_order, $amount)
    {
        Db::getInstance()->Execute(
            'INSERT INTO `'._DB_PREFIX_.'makecommerce_refunds` (`id_order`, `refund_amount`, `refund_date`)
                    VALUES('.(int)$id_order.', \''.$amount.'\', \''.date('Y-m-d H:i:s').'\')'
        );
    }

    private function getRefundDetails($id_order)
    {
        return Db::getInstance()->ExecuteS(
            'SELECT * FROM `'._DB_PREFIX_.'makecommerce_refunds` WHERE id_order = \''.$id_order.'\''
        );
    }

    public function hookPaymentOptions($params)
    {
        $sco = (int)$this->getConfig('sco');
        if (isset($sco) && $sco)
            $this->_doSCO();

        $methods_display = (int)$this->getConfig('methods_display');
        $credit_cards_separately = (bool)$this->getConfig('credit_display');

        $expanded = false;

        switch ($methods_display) {
            case self::DISPLAY_ORDER_PAGE:
                $payment_methods = $this->getPaymentMethodValues();
                $countries = $this->getCountries();
                if(count($countries)> 1){
                    $show_country_code = TRUE;
                }else{
                    $show_country_code = FALSE;
                }
                $this->context->smarty->assign(array(
                    'show_country_code' => $show_country_code,
                ));
                break;

            case self::DISPLAY_WIDGET:
                $payment_methods = $this->getPaymentMethodValues();
                $expanded = (bool)$this->getConfig('expanded');
                $invoice_address = new Address((int)$this->context->cart->id_address_delivery);
                $current_country = Tools::strtolower(Country::getIsoById((int)$invoice_address->id_country));
                $countries = $this->getCountries();
                if(count($countries)> 1){
                    $show_country_code = TRUE;
                }else{
                    $show_country_code = FALSE;
                }
                $this->appendPaymentMethods($payment_methods, !$credit_cards_separately);
                $this->context->smarty->assign(array(
                    'widget_title' => trim(Configuration::get(
                        $this->prefixed('widget_title'),
                        $this->context->language->id)),
                    'countries' => $countries,
                    'widget' => TRUE,
                    'show_country_code' => $show_country_code,
                    'current_country' => $current_country,
                    'separate_group' => $credit_cards_separately,
                    'expanded' => $expanded
                ));
                break;
        }


        if (empty($payment_methods)) {
            return;
        }
        $img_size = $this->getConfig('logo_size');
        $this->processMethods($payment_methods, $img_size);
        $this->context->smarty->assign(array(
            'payment_methods' => $payment_methods,
            'expanded' => $expanded,
            'logo_size' => $img_size,
            'mk_ajax_url' => $this->context->link->getModuleLink($this->name, 'ajax'),
            'order_total' => $this->context->cart->getOrderTotal(),
        ));

        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
            ->setCallToActionText($this->l('Pay by Makecommerce'))
            ->setAction($this->context->link->getModuleLink($this->name, 'confirmation', array(), true))
            ->setAdditionalInformation($this->fetch('module:makecommerce/views/templates/hook/payments.tpl'));
        $payment_options = [
            $newOption,
        ];

        return $payment_options;

    }

    protected function processMethods(array &$methods)
    {

        foreach ($methods as $i => &$method)
        {
            $params = array(
                'method' => $method->code,
                'country' => $method->country
            );

            $method->link = $this->context->link->getModuleLink(
                $this->name,
                (empty($method->methods) ? 'confirmation' : 'payments'),
                $params
            );
            $method->img = $method->logo_url;
        }
    }

    public function MkObjectToMethod(stdClass $mk_method)
    {
        if (!(isset($mk_method->min_amount) && $mk_method->min_amount > 0)) 
            $mk_method->min_amount = 0;
        if (!(isset($mk_method->max_amount) && $mk_method->max_amount > 0)) 
            $mk_method->max_amount = 0;
        return new Method(
            $mk_method->name,
            $this->getTranslation($mk_method->name),
            $mk_method->country,
            $mk_method->type,
            $mk_method->logo_url,
            $mk_method->min_amount,
            $mk_method->max_amount
        );
    }

    public function getCustomerIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public function hookDisplayPaymentReturn($params)
    {
        $order = $params['objOrder'];

        if ($order->hasBeenPaid())
        {
            $status = 'ok';
        }
        else
        {
            $status = 'error';
        }

        $this->smarty->assign(array(
            'status' => $status,
            'link_to_order' => $this->getOrderConfUrl($order)
        ));

        return $this->display(__FILE__, 'paymentReturn.tpl');
    }

    protected function clearPaymentCache()
    {
        $query = 'DELETE FROM %sconfiguration WHERE `name` LIKE \'%s%%\'';
        Db::getInstance()->execute(sprintf(
            $query,
            _DB_PREFIX_,
            $this->prefixed('payment_methods')
        ));
    }

    public function getOrderConfUrl($order)
    {
        return $this->context->link->getPageLink(
            'order-confirmation',
            true,
            $order->id_lang
        );
    }

    private function _doSCO()
    {
        $cart = new Cart($this->context->cart->id);
        
        $enabled_countries_list = array();
        $enabled_countries = Country::getCountries((int)$cookie->id_lang, true);
        foreach($enabled_countries as $enabled_country) {
            $enabled_countries_list[$enabled_country["iso_code"]] = $enabled_country["id_country"];
        }
        $tos_cms = new CMS(Configuration::get('PS_CONDITIONS_CMS_ID'), $this->context->language->id);
        $tos_link = $this->context->link->getCMSLink($tos_cms, $tos_cms->link_rewrite, (bool) Configuration::get('PS_SSL_ENABLED'));

        $cart_to_order_url = $this->context->link->getModuleLink($this->name, 'sco');
        $address = new Address($cart->id_address_delivery);
        $address_copy["id"]=$address->id_country;
        $address_copy["name"]=$address->country;
        $mk_cart_carriers = array();
        $tmpcarriers = array();
        foreach ($enabled_countries_list as $country_iso => $country_id) {
            $address->id_country = $country_id;
            $address->update();
            $address->country = Country::getNameById($this->context->language->id, $address->id_country);
            $carrier_list = $cart->getDeliveryOptionList( new Country( $country_id ), true);
            foreach ($carrier_list as $carriers) {
                foreach ($carriers as $carrier) {
                    foreach ($carrier['carrier_list'] as $carrier_list_list) {
                        if ($carrier_list_list['instance']->is_module == '1')
                            $type = 'APT';
                        elseif ($this->getConfig('carrier_'.$carrier_list_list['instance']->id) == '1')
                            $type = 'OTH';
                        else
                            $type = 'COU';

                        if ($carrier_list_list['instance']->external_module_name == 'makecommerceomniva')
                            $carrier_name = 'OMNIVA';
                        elseif ($carrier_list_list['instance']->external_module_name == 'makecommercesmartpost')
                            $carrier_name = 'SMARTPOST';
                        $methodId = $carrier_list_list['instance']->id;
                        $name = $carrier_list_list['instance']->name;
                        $mk_cart_carrier = array(
                            'countries' => array($country_iso),
                            'type' => $type,
                            'name' =>  $name,
                            'methodId' => $methodId,
                            'amount' => $carrier['total_price_with_tax']
                        );
                        if (isset($carrier_name) && $carrier_name) {
                            $mk_cart_carrier['carrier'] = $carrier_name;
                        }
                        unset ($carrier_name);
                        $tmpcarriers[] = $mk_cart_carrier;
                    }    
                }
            }
        }
        // carrier list compiled, consolidate it
        foreach ($tmpcarriers as $carrierid => $src_carrier) {
            $found = 0; $tgtid = 0;
            foreach($mk_cart_carriers as $tgt_id => $tgt_carrier) {
                if ( ($src_carrier["methodId"] == $tgt_carrier["methodId"] ) and ($src_carrier["name"] == $tgt_carrier["name"]) and ($src_carrier["amount"] == $tgt_carrier["amount"])) {
                    $found=1; $tgtid=$tgt_id;
                    continue;
                }
            }
            if ( $found == 1 )
                $mk_cart_carriers[$tgtid]["countries"][] = $src_carrier["countries"][0];
            else
                $mk_cart_carriers[] = $src_carrier;
        }
        if ( $this->context->language->iso_code == 'lv') $id_lang = 'lv';
        else if ( $this->context->language->iso_code == 'lt') $id_lang = 'lt';
        else if ( $this->context->language->iso_code == 'et') $id_lang = 'et';
        else $id_lang = 'en';
        $address->country = $address_copy["name"];
        $address->id_country = $address_copy["id"];
        $address->update();
        $amount = number_format(round($cart->getOrderTotal(true, 1), 2), 2,".","");
        $mk_cart = array(
            'cartRef' => 'Cart: '.$cart->id,
            'pluginUrls' => array(
                'cartToOrder' => $cart_to_order_url,
                'calculateShipment' => 'test',
                'tos' => $tos_link
            ),
            'amount' => $amount,
            'currency' => 'EUR',
            'sourceCountry' => $this->context->country->iso_code,
            'locale' => $id_lang,
            'shipmentMethods' => $mk_cart_carriers
        );

        $api = $this->getApi();
        $this->updateTable(); // remove later
        $sco = $api->createCart($mk_cart);
        $sco_backdata["amount"] = $amount;
        $sco_backdata["carriers"] = $mk_cart_carriers;
        Db::getInstance()->Execute(
            'INSERT INTO `'._DB_PREFIX_.'makecommerce_sco` (`id_cart`, `sco_id`, `sco_amounts`)
                    VALUES('.(int)$cart->id.', \''.$sco->id.'\',\''.json_encode($sco_backdata).'\')'
        );

        Tools::redirect($sco->scoUrl);
    }

    private function addCarriersFields()
    {
        $carriers = Carrier::getCarriers($this->context->employee->id_lang, true, false, false, null, 5);
        foreach ($carriers as $carrier) {
            $this->fields[] = 'carrier_'.$carrier['id_carrier'];
        }
    }

    public function validateCart($id_cart)
    {
        $valid = true;
        $cart = new Cart($id_cart);
        $products = $cart->getProducts();

        foreach ($products as $product) {
            if ($product['cart_quantity'] > $product['quantity_available'] && $product['out_of_stock'] != 1)
                $valid = false;
        }

        return $valid;
    }
}
