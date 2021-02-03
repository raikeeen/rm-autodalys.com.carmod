<?php

class VenipakCarrier extends CarrierModule {

    private $_postErrors = array();
    private $_statusBar = '';

    const MANIFEST_STATUS_OPEN = 1;
    const MANIFEST_STATUS_CLOSED = 0;

    public  $id_carrier;

    private static $_classMap = array(
        'VenipakManifest' => 'classes/VenipakManifest.php',
        'VenipakOrder' => 'classes/VenipakOrder.php',
        'VenipakAPI' => 'classes/VenipakAPI.php',
        'VenipakDatabaseHelper' => 'classes/VenipakDatabaseHelper.php',
        'VenipakHelper' => 'classes/VenipakHelper.php',
        'VenipakConfig' => 'classes/VenipakConfig.php',
        'ElprestaUpdater' => 'classes/ElprestaUpdater.php',
    );

    private $on_off = array();
    private $addressFormFields = array();

    public function __construct()
    {
        $this->name = 'venipakcarrier';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.4';
        $this->author = 'elPresta.lt';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->displayName = $this->l('Venipak');
        $this->description = $this->l('Delivery method using Venipak');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        parent::__construct();

        // include this class everytime
        //self::checkForClass('VenipakConfig');

        if (self::isInstalled($this->name))
		{
			$carriers = Carrier::getCarriers(Context::getContext()->language->id, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);

			$id_carrier_list = array();
			foreach($carriers as $carrier)
				$id_carrier_list[] = $carrier['id_carrier'];


			$warning = array();
			if (!in_array((int)(Configuration::get('VENIPAK_CARRIER_ID')), $id_carrier_list) && !in_array((int)(Configuration::get('VENIPAK_PICKUP_POINT_CARRIER_ID')), $id_carrier_list))
				$warning[] = $this->l('"Venipak carrier"').' ';
			if (!Configuration::get('VENIPAK_API_LOGIN'))
				$warning[] = $this->l('"Venipak API login is required."').' ';
            if(!Configuration::get('VENIPAK_API_PASSWORD') || Configuration::get('VENIPAK_API_PASSWORD')==''){
                $warning[] = $this->l('"Venipak API password"');
            }
			if (count($warning)){
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly').' ';
            }
		}
    }

    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);


        self::checkForClass('VenipakDatabaseHelper');
        if (VenipakDatabaseHelper::installTables() === false)
        {
            return false;
        }

        // Set default variables;
        Configuration::updateValue("VENIPAK_API_URL", "https://go.venipak.lt");
        Configuration::updateValue("VENIPAK_PARCEL_CALCULATE_METHOD", true);
        Configuration::updateValue("VENIPAK_PARCEL_LT", 0);
        Configuration::updateValue("VENIPAK_PARCEL_LV", 0);
        Configuration::updateValue("VENIPAK_PARCEL_EE", 0);
        Configuration::updateValue("VENIPAK_TRACKING_ACTION", 1);
        Configuration::updateValue("VENIPAK_PARCEL_CALCULATE_METHOD", 1);

        if (!parent::install() ||
          !$this->registerHook('actionAdminControllerSetMedia') ||
          !$this->registerHook('displayBackOfficeHeader') ||
          !$this->registerHook('displayAdminOrder') ||
          !$this->registerHook('Header') ||
          !$this->registerHook('actionCarrierUpdate') ||
          !$this->installExternalCarrier() ||
          !$this->installExternalCarrierPickupPoint() ||
          !$this->installVenipakTab())
            return false;

        $this->registerHook('DisplayCarrierList');
        $this->registerHook('DisplayCarrierExtraContent');

        $this->createOrderState();
        $this->updatePickupPoints();

        return true;
    }

    public function installExternalCarrier()
	{
        $config =
            array(
                'active' => true,
                'delay' => array('lt' => 'Pristatymas per 1-2 d.d.', 'en' => 'Delivery in 1-2 business days', Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => 'Delivery in 1-2 business days'),
                'deleted' => 0,
                'external_module_name' => 'venipakcarrier',
                'id_tax_rules_group' => 0,
                'id_zone' => 1,
                'is_module' => true,
                'name' => 'Venipak carrier',
                'need_range' => true,
                'range_behavior' => 0,
                'shipping_external' => true,
                'shipping_handling' => false,
                'url' => 'https://go.venipak.lt/ws/tracking.php?type=1&output=html&code=@',
            );

		$carrier = new Carrier();
        $carrier->active = $config['active'];
        $carrier->delay = $config['delay'];
        $carrier->deleted = $config['deleted'];
        $carrier->external_module_name = $config['external_module_name'];
        $carrier->id_tax_rules_group = $config['id_tax_rules_group'];
        $carrier->id_zone = $config['id_zone'];
        $carrier->is_module = $config['is_module'];
        $carrier->name = $config['name'];
        $carrier->need_range = $config['need_range'];
        $carrier->range_behavior = $config['range_behavior'];
        $carrier->shipping_external = $config['shipping_external'];
        $carrier->shipping_handling = $config['shipping_handling'];
        $carrier->url = $config['url'];

		$languages = Language::getLanguages(true);
		foreach ($languages as $language)
		{
			if ($language['iso_code'] == 'lt')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == 'en')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')))
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
		}

		if ($carrier->add())
		{
			$groups = Group::getGroups(true);
			foreach ($groups as $group)
				Db::getInstance()->insert('carrier_group', array('id_carrier' => (int)($carrier->id), 'id_group' => (int)($group['id_group'])));

			$rangePrice = new RangePrice();
			$rangePrice->id_carrier = $carrier->id;
			$rangePrice->delimiter1 = '0';
			$rangePrice->delimiter2 = '10000';
			$rangePrice->add();

			$rangeWeight = new RangeWeight();
			$rangeWeight->id_carrier = $carrier->id;
			$rangeWeight->delimiter1 = '0';
			$rangeWeight->delimiter2 = '10000';
			$rangeWeight->add();

			$zones = Zone::getZones(true);
			foreach ($zones as $zone)
			{
				Db::getInstance()->insert('carrier_zone', array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)($zone['id_zone'])));
				Db::getInstance()->insert('delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => (int)($rangePrice->id), 'id_range_weight' => NULL, 'id_zone' => (int)($zone['id_zone']), 'price' => '0'));
				Db::getInstance()->insert('delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => NULL, 'id_range_weight' => (int)($rangeWeight->id), 'id_zone' => (int)($zone['id_zone']), 'price' => '0'));
			}


			try {
                copy(dirname(__FILE__) . '/carrier.png', _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg');
            }
            catch(Exception $e) {}

            Configuration::updateValue('VENIPAK_CARRIER_ID', (int)$carrier->id);

			return true;
		}

		return false;
	}


    public function installExternalCarrierPickupPoint()
    {
        $config =
            array(
                'active' => true,
                'delay' => array('lt' => 'Pristatymas per 1-2 d.d.', 'en' => 'Delivery in 1-2 business days', Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => 'Delivery in 1-2 business days'),
                'deleted' => 0,
                'external_module_name' => 'venipakcarrier',
                'id_tax_rules_group' => 0,
                'id_zone' => 1,
                'is_module' => true,
                'name' => 'Venipak Pickup Points',
                'need_range' => true,
                'range_behavior' => 0,
                'shipping_external' => true,
                'shipping_handling' => false,
                'url' => 'https://go.venipak.lt/ws/tracking.php?type=1&output=html&code=@',
            );

        $carrier = new Carrier();
        $carrier->active = $config['active'];
        $carrier->delay = $config['delay'];
        $carrier->deleted = $config['deleted'];
        $carrier->external_module_name = $config['external_module_name'];
        $carrier->id_tax_rules_group = $config['id_tax_rules_group'];
        $carrier->id_zone = $config['id_zone'];
        $carrier->is_module = $config['is_module'];
        $carrier->name = $config['name'];
        $carrier->need_range = $config['need_range'];
        $carrier->range_behavior = $config['range_behavior'];
        $carrier->shipping_external = $config['shipping_external'];
        $carrier->shipping_handling = $config['shipping_handling'];
        $carrier->url = $config['url'];

        $languages = Language::getLanguages(true);
        foreach ($languages as $language)
        {
            if ($language['iso_code'] == 'lt')
                $carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
            if ($language['iso_code'] == 'en')
                $carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')))
                $carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
        }

        if ($carrier->add())
        {
            $groups = Group::getGroups(true);
            foreach ($groups as $group)
                Db::getInstance()->insert('carrier_group', array('id_carrier' => (int)($carrier->id), 'id_group' => (int)($group['id_group'])));

            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '10000';
            $rangeWeight->add();

            $zones = Zone::getZones(true);
            foreach ($zones as $zone)
            {
                Db::getInstance()->insert('carrier_zone', array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)($zone['id_zone'])));
                Db::getInstance()->insert('delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => (int)($rangePrice->id), 'id_range_weight' => NULL, 'id_zone' => (int)($zone['id_zone']), 'price' => '0'));
                Db::getInstance()->insert('delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => NULL, 'id_range_weight' => (int)($rangeWeight->id), 'id_zone' => (int)($zone['id_zone']), 'price' => '0'));
            }


            try {
                copy(dirname(__FILE__) . '/carrier.png', _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg');
            }
            catch(Exception $e) {}

            Configuration::updateValue('VENIPAK_PICKUP_POINT_CARRIER_ID', (int)$carrier->id);

            return true;
        }

        return false;
    }

    public function uninstall()
    {
        self::checkForClass('VenipakHelper');
        self::checkForClass('VenipakDatabaseHelper');


        if (!parent::uninstall() ||
          !$this->unregisterHook('actionAdminControllerSetMedia') ||
          !$this->unregisterHook('displayAdminOrder') ||
          !$this->unregisterHook('Header') ||
          !$this->unregisterHook('actionCarrierUpdate') ||
          !$this->unregisterHook('displayBackOfficeHeader') ||
          !$this->uninstallVenipakTab() ||
          !VenipakHelper::deleteConfigValues())
            return false;

        VenipakDatabaseHelper::uninstallTables();

        $this->unregisterHook('DisplayCarrierList');
        $this->unregisterHook('DisplayCarrierExtraContent');


        // VENIPAK CARRIER
        $venipakCarrier = new Carrier((int)(Configuration::get('VENIPAK_CARRIER_ID')));
        if ($venipakCarrier)
        {
            if (Configuration::get('PS_CARRIER_DEFAULT') == (int)($venipakCarrier->id)) {
                $carriersD = Carrier::getCarriers(Context::getContext()->language->id, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
                foreach ($carriersD as $carrierD)
                    if ($carrierD['active'] AND !$carrierD['deleted'] AND ($carrierD['name'] != 'Venipak carrier'))
                        Configuration::updateValue('PS_CARRIER_DEFAULT', $carrierD['id_carrier']);
            }
            $venipakCarrier->deleted = 1;
            $venipakCarrier->active = 0;

            if (!$venipakCarrier->update())
                return false;
        }



        // VENIPAK PICKUP POINTS
        $venipakPickupPoints = new Carrier((int)(Configuration::get('VENIPAK_PICKUP_POINT_CARRIER_ID')));
        if ($venipakPickupPoints)
        {
            if (Configuration::get('PS_CARRIER_DEFAULT') == (int)($venipakPickupPoints->id)) {
                $carriersD = Carrier::getCarriers(Context::getContext()->language->id, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
                foreach ($carriersD as $carrierD)
                    if ($carrierD['active'] AND !$carrierD['deleted'] AND ($carrierD['name'] != 'Venipak Pickup Points'))
                        Configuration::updateValue('PS_CARRIER_DEFAULT', $carrierD['id_carrier']);
            }
            $venipakPickupPoints->deleted = 1;
            $venipakPickupPoints->active = 0;

            if (!$venipakPickupPoints->update())
                return false;

        }

        return true;
    }

    public function createOrderState()
	{
		if (!Configuration::get('VENIPAK_ORDER_STATUS'))
		{
			$order_state = new OrderState();
			$order_state->name = array();

			foreach (Language::getLanguages() as $language)
			{
				if (Tools::strtolower($language['iso_code']) == 'lt')
					$order_state->name[$language['id_lang']] = 'Išsiųsta Venipak';
				else
					$order_state->name[$language['id_lang']] = 'Sent to Venipak';
			}

			$order_state->send_email = false;
			$order_state->color = '#eeab00';
			$order_state->hidden = false;
			$order_state->delivery = false;
			$order_state->logable = true;
			$order_state->invoice = false;

			$order_state->add();
			Configuration::updateValue('VENIPAK_ORDER_STATUS', (int)$order_state->id);
		}
	}

    private function installVenipakTab()
    {
        $venipak_name_tab = array('en'=>'Venipak', 'lt'=>'Venipak', Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))=>'Venipak');
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(false) as $language)
        {
            $iso_code = array_key_exists($language['iso_code'], $venipak_name_tab) ? $language['iso_code'] : 'en';
            $tab->name[$language['id_lang']] = $venipak_name_tab[$iso_code];
        }
        $tab->class_name = 'AdminVenipak';
        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentShipping');
        if (!$tab->save())
        {
            $this->displayError($this->l('Error while creating Venipak tab.'));
            return false;
        }
        return true;
    }

    private function uninstallVenipakTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminVenipak');
        if ($id_tab != 0)
        {
            $tab = new Tab($id_tab);
            if (!$tab->delete())
            {
                $this->displayError($this->l('Error while uninstalling Venipak tab !'));
                return false;
            }
        }
        return true;
    }

    public function getContent()
    {
        self::checkForClass('ElprestaUpdater');
        $updateInformation = ElprestaUpdater::check($this);

        $this->on_off = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Enabled')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('Disabled')
            )
        );

        if (isset($_GET['VENIPAK_ADDRESS_ID']) || (isset($_GET['modulepage']) && $_GET['modulepage'] == 'venipak_address') || isset($_GET['venipak_address_type'])) {
            if (isset($_GET['deletevenipakaddress'])) {
                return $this->actionDeleteAddress();
            } else {
                return $this->renderAddressForm();
            }
        } else {
            return $updateInformation . $this->postProcess() . $this->displayForm();
        }
    }

    public function postProcess()
    {
        self::checkForClass('VenipakHelper');

        $output = $this->_statusBar;

        // print errors
        if (!empty($errorCode = Tools::getValue('showError'))) {
            $output .= $this->displayError(VenipakHelper::getMessage($errorCode, $this));
        }

        // print confirmations
        if (!empty($confCode = Tools::getValue('showConf'))) {
            $output .= $this->displayConfirmation(VenipakHelper::getMessage($confCode, $this));
        }

        if (Tools::isSubmit('submit'.$this->name))
        {

            $delivery_types = $cod_modules = array();

            $errors = $this->checkRequiredFiels();
            if (!empty($errors))
                $output .= $errors;


            foreach (VenipakHelper::getConfigFormKeys() as $key)
            {
                $value = Tools::getValue($key);

                if (!in_array($key, array('VENIPAK_DELIVERY_TYPES', 'VENIPAK_COD_MODULES', 'VENIPAK_ALLOWED_PICKUP_COUNTRIES')))
                {
                    $value = strval($value);
                    Configuration::updateValue($key, $value);
                }

            }



            // because of changed config form
            foreach ($_POST as $key => $value)
            {
                if (strpos($key, 'VENIPAK_DELIVERY_TYPES_') !== false)
                {
                    $delivery_types[] = str_replace('VENIPAK_DELIVERY_TYPES_', '', $key);
                }
                else if (strpos($key, 'VENIPAK_COD_MODULES_') !== false)
                {
                    $cod_modules[] = str_replace('VENIPAK_COD_MODULES_', '', $key);
                }
                else if (strpos($key, 'VENIPAK_ALLOWED_PICKUP_COUNTRIES_') !== false)
                {
                    $pickup_countries[] = str_replace('VENIPAK_ALLOWED_PICKUP_COUNTRIES_', '', $key);
                }

            }





            Configuration::updateValue('VENIPAK_DELIVERY_TYPES', implode(',', $delivery_types));
            Configuration::updateValue('VENIPAK_COD_MODULES', implode(',', $cod_modules));

            if (isset($pickup_countries) && !empty($pickup_countries))
            {
                $pickup_countries_oldValue = Configuration::get('VENIPAK_ALLOWED_PICKUP_COUNTRIES');
                $pickup_countries_newValue = implode(',', $pickup_countries);
                Configuration::updateValue('VENIPAK_ALLOWED_PICKUP_COUNTRIES', $pickup_countries_newValue);


                if ($pickup_countries_oldValue != $pickup_countries_newValue) {
                    $this->updatePickupPoints();
                }
            }


            $output .= $this->displayConfirmation($this->l('Settings updated'));

            if(Tools::getValue('VENIPAK_LAST_PACK_NO_CHANGE')=='1' && Tools::getValue('VENIPAK_LAST_PACK_NO_NUMBER')!=''){
                $this->updateMaxPackNo(Tools::getValue('VENIPAK_LAST_PACK_NO_NUMBER'), Configuration::get('VENIPAK_API_ID_CODE'));
            }



            // saving credentials to database
            /*$api_username = Tools::getValue('VENIPAK_API_LOGIN');
            $api_password = Tools::getValue('VENIPAK_API_PASSWORD');
            $api_app_id = (int)Tools::getValue('VENIPAK_API_ID_CODE');

            if (!empty($api_username) && !empty($api_password) && !empty($api_app_id))
            {
                $exists = Db::getInstance()->getValue("SELECT 1 FROM " . _DB_PREFIX_ . "venipak_credentials WHERE app_id = " . $api_app_id);

                if ($exists)
                {
                    Db::getInstance()->update('venipak_credentials',
                        ['username' => $api_username, 'password' => $api_password], 'app_id = ' . $api_app_id);
                } else
                {
                    Db::getInstance()->insert('venipak_credentials',
                        ['app_id' => $api_app_id, 'username' => $api_username, 'password' => $api_password]);
                }
            }*/
        }

        return $output;

    }

    public function displayForm()
    {
        self::checkForClass('VenipakHelper');

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;


        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language){
            $languages[$k]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }

        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->languages = $languages;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;


        $helper->title = $this->displayName;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;

        $helper->fields_value = VenipakHelper::getConfigFormValues();

        $config_form = $this->renderConfigForm();

        return $helper->generateForm($config_form).$this->warehousesList().$this->sendersList().$this->additionalForm();
    }

    private function renderConfigForm()
    {
        $config_form = array();
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $orderStatusesOriginal = OrderState::getOrderStates(Context::getContext()->language->id);

        $orderStatuses = array(array('id'=>'','name'=> $this->l('--- Do not change order state ---') ));
        foreach($orderStatusesOriginal as $status){
            $orderStatuses[]=array('id'=>$status['id_order_state'], 'name'=>$status['name']);
        }

        $codModules = array();

        foreach(PaymentModule::getInstalledPaymentModules() as $value){
            $codModules[]=array('id'=>$value['name'], 'name'=>$value['name']);
        }

        $pickupCountriesList = array();
        $pickup_countries = VenipakHelper::getSupportedCountries();
        foreach($pickup_countries as $value){
            $pickupCountriesList[]= array('id' => $value['id'], 'name' => VenipakHelper::getCountryNameByISO($value['id']));
        }

        $deliveryTypes = array();
        //$deliveryTypes[] = array('id' => 'None', 'name' => 'None');
        foreach($this->getDeliveryTimes() as $key=>$value){
            $deliveryTypes[]=array('id'=>$key, 'name'=>$value);
        }

        $hideOrNot = Configuration::get('VENIPAK_SHOW_DELIVERY_TYPES') == 0 ? ' hide' : '';

        $config_form[0] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('API Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'html',
                        'name' => 'info_block',
                        'html_content' => '',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('API URL'),
                        'name' => 'VENIPAK_API_URL',
                        'required' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('API Login'),
                        'name' => 'VENIPAK_API_LOGIN',
                        'required' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('API Password'),
                        'name' => 'VENIPAK_API_PASSWORD',
                        'required' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('API ID'),
                        'name' => 'VENIPAK_API_ID_CODE',
                        'required' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Set last used PACK ID'),
                        'name' => 'VENIPAK_LAST_PACK_NO_CHANGE',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pack No. (last 7 digits):'),
                        'name' => 'VENIPAK_LAST_PACK_NO_NUMBER',
                        'form_group_class' => 'hide',
                        'options'=>array(
                        ),
                        'col' => 3
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $config_form[1] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Additional settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Show "return accompanying documents"'),
                        'name' => 'VENIPAK_SHOW_RETURN_DOCS',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Show "Check ID"'),
                        'name' => 'VENIPAK_SHOW_CHECK_ID',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Show "Select sender"'),
                        'name' => 'VENIPAK_SHOW_PICKUP',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'html',
                        'label' => '',
                        'name' => 'cod_pickup_warning',
                        'html_content' => '<label class="alert alert-warning"><b>'.$this->l('Currently delivery to pickup points are not available with C.O.D.!').'</b></label>',
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => $this->l('C.O.D. Modules'),
                        'name' => 'VENIPAK_COD_MODULES',
                        'desc' => $this->l('Select all Cash On Delivery modules.'),
                        'values' => array(
                            'query' => $codModules,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Order status after saving'),
                        'name' => 'VENIPAK_ORDER_STATUS',
                        'options' => array(
                            'query' => $orderStatuses,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Format of labels'),
                        'name' => 'VENIPAK_LABELS_FORMAT',
                        'options' => array(
                            'query' => array(array('id'=>'100x150', 'name'=>'100x150'), array('id'=>'a4', 'name'=>'a4')),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Show available delivery types?'),
                        'name' => 'VENIPAK_SHOW_DELIVERY_TYPES',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => $this->l('Available delivery types'),
                        'name' => 'VENIPAK_DELIVERY_TYPES',
                        'form_group_class' => 'venipak-delivery-types'.$hideOrNot,
                        'values' => array(
                            'query' => $deliveryTypes,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Show door code input?'),
                        'name' => 'VENIPAK_SHOW_COMMENT_DOOR_CODE',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Show office number input?'),
                        'name' => 'VENIPAK_SHOW_COMMENT_OFFICE_NO',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Show warehouse number input?'),
                        'name' => 'VENIPAK_SHOW_COMMENT_WAREHOUSE_NO',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Show call before delivery input?'),
                        'name' => 'VENIPAK_SHOW_COMMENT_CALL',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Weight calculation'),
                        'desc' => $this->l('Yes - sum order products; No - set fixed 1kg weight;'),
                        'name' => 'VENIPAK_ORDER_WEIGHT',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Send tracking number to email'),
                        'desc' => $this->l('Yes - default PrestaShop behaviour: when tracking number is added it sends "in transit" mail with tracking number. No - add tracking number to order without sending in "transit mail".'),
                        'name' => 'VENIPAK_TRACKING_ACTION',
                        'values' => $this->on_off,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),

        );

        $config_form[2] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Pickup points settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(

                    array(
                        'type' => 'checkbox',
                        'label' => $this->l('Allowed Pickup Point Countries'),
                        'name' => 'VENIPAK_ALLOWED_PICKUP_COUNTRIES',
                        'desc' => $this->l('Which countries are allowed for Venipak Pickup points'),
                        'values' => array(
                            'query' => $pickupCountriesList,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Price calculation method'),
                        'desc' => $this->l('Yes - default PrestaShop carrier settings; No - use shipping prices from module;'),
                        'name' => 'VENIPAK_PARCEL_CALCULATE_METHOD',
                        'values' => $this->on_off,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pickup price - LT points'),
                        'desc' => $this->l('Enter the amount.'),
                        'name' => 'VENIPAK_PARCEL_LT',
                        'required' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pickup price - LV points'),
                        'desc' => $this->l('Enter the amount.'),
                        'name' => 'VENIPAK_PARCEL_LV',
                        'required' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pickup price - EE points'),
                        'desc' => $this->l('Enter the amount.'),
                        'name' => 'VENIPAK_PARCEL_EE',
                        'required' => false,
                        'col' => 3
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),

        );

        return $config_form;
    }

    public function renderAddressForm(){

        self::checkForClass('VenipakHelper');
        self::checkForClass('VenipakDatabaseHelper');


        $veniAddressType = 'warehouse';
        $warehouse = false;

        $warehouse_id = Tools::getValue('VENIPAK_ADDRESS_ID');

        if(isset($warehouse_id) && !empty($warehouse_id)){
            //it's update
            //get warehouse info
            $warehouse = VenipakDatabaseHelper::getAddress($warehouse_id);
            if($warehouse == false){
                return $this->displayError("No such warehouse is found.");
            }
            $veniAddressType = $warehouse['type'];
        }else{
            //it's new
            if(isset($_GET['venipak_address_type']) && ($_GET['venipak_address_type'] == 'sender'  || $_GET['venipak_address_type'] == 'warehouse'  )){
                $veniAddressType = $_GET['venipak_address_type'];
            }else{
                return $this->displayError("Wrong type of address.");
            }
        }

        $hasDefaultAddress = VenipakDatabaseHelper::hasDefaultAddress($veniAddressType);
        $addressTypeForForm = $veniAddressType == 'sender' ? $this->l('sender') : $this->l('warehouse');

        $this->addressFormFields = array(
                    array(
                        'type' => 'html',
                        'name' => 'html_data',
                        'label' => $this->l('Address type'),
                        'html_content' => '<label class="control-label"><b>'.strtoupper($addressTypeForForm).'</b></label>',
                    ),
                    array(
						'type' => 'text',
						'label' => $this->l('Address title'),
						'name' => 'VENIPAK_ADDRESS_TITLE',
						'col' => 3,
					),
					array(
						'type' => 'text',
						'label' => $this->l('Company name'),
						'name' => 'VENIPAK_ADDRESS_NAME',
                        'col' => 3,
                    ),
                    array(
						'type' => 'text',
						'label' => $this->l('Company code'),
						'name' => 'VENIPAK_ADDRESS_COMPANY',
                        'col' => 3,
                    ),
                    array(
						'type' => 'text',
						'label' => $this->l('Address'),
						'name' => 'VENIPAK_ADDRESS_ADDRESS',
                        'col' => 3,
					),
                    array(
						'type' => 'text',
						'label' => $this->l('City'),
						'name' => 'VENIPAK_ADDRESS_CITY',
                        'col' => 3,
					),
                    array(
						'type' => 'select',
						'label' => $this->l('Country'),
						'name' => 'VENIPAK_ADDRESS_COUNTRY',
                        'options' => array(
                            'query' => VenipakHelper::getSupportedCountries(),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                        'col' => 3,
					),
                    array(
						'type' => 'text',
						'label' => $this->l('Postcode'),
						'name' => 'VENIPAK_ADDRESS_POSTCODE',
                        'col' => 3,
					),
                    array(
						'type' => 'text',
						'label' => $this->l('Phone number'),
						'name' => 'VENIPAK_ADDRESS_PHONE_NUMBER',
                        'col' => 3,
					),
                    array(
						'type' => 'text',
						'label' => $this->l('Contact person'),
						'name' => 'VENIPAK_ADDRESS_PERSON',
                        'col' => 3,
					),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Make default'),
                        'name' => 'VENIPAK_ADDRESS_IS_DEFAULT',
                        'values' => $this->on_off,
                    ),
                    array(
						'type' => 'hidden',
						'name' => 'VENIPAK_ADDRESS_TYPE',
					),
                );
        $output = '<div class="panel"><span style="float: left;"><a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="process-icon-back"></i><div>'.$this->l('Back').'</div></a></span><div class="clear clearfix"></div></div>';
        if (Tools::isSubmit('venipakaddresssave'))
        {
            if($this->validateAddress()){

                if($this->saveAddress()){
                    $message = 2001;
                    if(strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_TYPE'))))=='sender'){
                        $message = 2002;
                    }
                    Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&showConf='.$message);
                }
            }else{
                $output .= $this->displayError($this->l('Please recheck warehouse data:').'<br />'.implode(' <br /> ',$this->_postErrors));
            }
            //return $output;
        }


        $helper = new HelperForm();
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&modulepage=venipak_address&venipak_address_type='.$veniAddressType;
        if($warehouse!=false){
            $helper->currentIndex .= '&VENIPAK_ADDRESS_ID='.$warehouse['id'];
        }

        // Language
        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language){
            $languages[$k]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;


        $fields_form[0] = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Address form'),
					'icon' => 'icon-cogs'
				),
				'input' => $this->addressFormFields,
                'submit' => array(
                    'title' => ($warehouse!=false ? $this->l('Update') : $this->l('Save')),                       // This is the button that saves the whole fieldset.
                    'name'=>'venipakaddresssave'
                )

            )
        );

        //set null all warehouse fields
        foreach(VenipakHelper::getAddressFields() as $f){
            $helper->fields_value[$f]=false;
        }
        $helper->fields_value['VENIPAK_ADDRESS_TYPE'] = $veniAddressType;

            //get warehouse info
            if(!empty($warehouse)){
                //add ID field
                $fields_form[0]['form']['input'][]=array(
                    'type'=>'hidden',
                    'name'=>'VENIPAK_ADDRESS_ID'
                );
                $helper->fields_value['VENIPAK_ADDRESS_TITLE'] = $warehouse['address_title'];
                $helper->fields_value['VENIPAK_ADDRESS_NAME'] = $warehouse['name'];
                $helper->fields_value['VENIPAK_ADDRESS_COMPANY'] = $warehouse['company'];
                $helper->fields_value['VENIPAK_ADDRESS_ADDRESS'] = $warehouse['address'];
                $helper->fields_value['VENIPAK_ADDRESS_CITY'] = $warehouse['city'];
                $helper->fields_value['VENIPAK_ADDRESS_COUNTRY'] = $warehouse['country'];
                $helper->fields_value['VENIPAK_ADDRESS_POSTCODE'] = $warehouse['postcode'];
                $helper->fields_value['VENIPAK_ADDRESS_PHONE_NUMBER'] = $warehouse['phone_number'];
                $helper->fields_value['VENIPAK_ADDRESS_PERSON'] = $warehouse['person'];
                $helper->fields_value['VENIPAK_ADDRESS_IS_DEFAULT'] = $hasDefaultAddress ? $warehouse['is_default'] : 1;
                $helper->fields_value['VENIPAK_ADDRESS_ID'] = $warehouse['id'];
            }else{
                $helper->fields_value['VENIPAK_ADDRESS_TITLE'] = Tools::getValue('VENIPAK_ADDRESS_TITLE');
                $helper->fields_value['VENIPAK_ADDRESS_NAME'] = Tools::getValue('VENIPAK_ADDRESS_NAME');
                $helper->fields_value['VENIPAK_ADDRESS_COMPANY'] = Tools::getValue('VENIPAK_ADDRESS_COMPANY');
                $helper->fields_value['VENIPAK_ADDRESS_ADDRESS'] = Tools::getValue('VENIPAK_ADDRESS_ADDRESS');
                $helper->fields_value['VENIPAK_ADDRESS_CITY'] = Tools::getValue('VENIPAK_ADDRESS_CITY');
                $helper->fields_value['VENIPAK_ADDRESS_COUNTRY'] = Tools::getValue('VENIPAK_ADDRESS_COUNTRY');
                $helper->fields_value['VENIPAK_ADDRESS_POSTCODE'] = Tools::getValue('VENIPAK_ADDRESS_POSTCODE');
                $helper->fields_value['VENIPAK_ADDRESS_PHONE_NUMBER'] = Tools::getValue('VENIPAK_ADDRESS_PHONE_NUMBER');
                $helper->fields_value['VENIPAK_ADDRESS_PERSON'] = Tools::getValue('VENIPAK_ADDRESS_PERSON');
                $helper->fields_value['VENIPAK_ADDRESS_IS_DEFAULT'] = $hasDefaultAddress ? Tools::getValue('VENIPAK_ADDRESS_IS_DEFAULT') : 1;
            }



        return $output.$helper->generateForm($fields_form);
    }

    /*public function renderVenipakModuleModeSwitch()
    {
        self::checkForClass('VenipakHelper');
        self::checkForClass('VenipakDatabaseHelper');



        $this->modeSwitchFormFields = array(
            array(
                'type' => 'switch',
                'is_bool' => true,
                'label' => $this->l('Show available delivery types?'),
                'name' => 'VENIPAK_SHOW_DELIVERY_TYPES',
                'values' => $this->on_off,
            ),
        );

        if (Tools::isSubmit('venipakaddresssave'))
        {
            if($this->validateAddress()){

                if($this->saveAddress()){
                    $message = 2001;
                    if(strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_TYPE'))))=='sender'){
                        $message = 2002;
                    }
                    Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&showConf='.$message);
                }
            }else{
                $output .= $this->displayError($this->l('Please recheck warehouse data:').'<br />'.implode(' <br /> ',$this->_postErrors));
            }
            //return $output;
        }


        $helper = new HelperForm();
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&modulepage=venipak_address&venipak_address_type='.$veniAddressType;
        if($warehouse!=false){
            $helper->currentIndex .= '&VENIPAK_ADDRESS_ID='.$warehouse['id'];
        }

        // Language
        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language){
            $languages[$k]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;


        $fields_form[0] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Address form'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $this->addressFormFields,
                'submit' => array(
                    'title' => ($warehouse!=false ? $this->l('Update') : $this->l('Save')),                       // This is the button that saves the whole fieldset.
                    'name'=>'venipakaddresssave'
                )

            )
        );

        //set null all warehouse fields
        foreach(VenipakHelper::getAddressFields() as $f){
            $helper->fields_value[$f]=false;
        }
        $helper->fields_value['VENIPAK_ADDRESS_TYPE'] = $veniAddressType;

        //get warehouse info
        if(!empty($warehouse)){
            //add ID field
            $fields_form[0]['form']['input'][]=array(
                'type'=>'hidden',
                'name'=>'VENIPAK_ADDRESS_ID'
            );
            $helper->fields_value['VENIPAK_ADDRESS_TITLE'] = $warehouse['address_title'];
            $helper->fields_value['VENIPAK_ADDRESS_NAME'] = $warehouse['name'];
            $helper->fields_value['VENIPAK_ADDRESS_COMPANY'] = $warehouse['company'];
            $helper->fields_value['VENIPAK_ADDRESS_ADDRESS'] = $warehouse['address'];
            $helper->fields_value['VENIPAK_ADDRESS_CITY'] = $warehouse['city'];
            $helper->fields_value['VENIPAK_ADDRESS_COUNTRY'] = $warehouse['country'];
            $helper->fields_value['VENIPAK_ADDRESS_POSTCODE'] = $warehouse['postcode'];
            $helper->fields_value['VENIPAK_ADDRESS_PHONE_NUMBER'] = $warehouse['phone_number'];
            $helper->fields_value['VENIPAK_ADDRESS_PERSON'] = $warehouse['person'];
            $helper->fields_value['VENIPAK_ADDRESS_IS_DEFAULT'] = $hasDefaultAddress ? $warehouse['is_default'] : 1;
            $helper->fields_value['VENIPAK_ADDRESS_ID'] = $warehouse['id'];
        }else{
            $helper->fields_value['VENIPAK_ADDRESS_TITLE'] = Tools::getValue('VENIPAK_ADDRESS_TITLE');
            $helper->fields_value['VENIPAK_ADDRESS_NAME'] = Tools::getValue('VENIPAK_ADDRESS_NAME');
            $helper->fields_value['VENIPAK_ADDRESS_COMPANY'] = Tools::getValue('VENIPAK_ADDRESS_COMPANY');
            $helper->fields_value['VENIPAK_ADDRESS_ADDRESS'] = Tools::getValue('VENIPAK_ADDRESS_ADDRESS');
            $helper->fields_value['VENIPAK_ADDRESS_CITY'] = Tools::getValue('VENIPAK_ADDRESS_CITY');
            $helper->fields_value['VENIPAK_ADDRESS_COUNTRY'] = Tools::getValue('VENIPAK_ADDRESS_COUNTRY');
            $helper->fields_value['VENIPAK_ADDRESS_POSTCODE'] = Tools::getValue('VENIPAK_ADDRESS_POSTCODE');
            $helper->fields_value['VENIPAK_ADDRESS_PHONE_NUMBER'] = Tools::getValue('VENIPAK_ADDRESS_PHONE_NUMBER');
            $helper->fields_value['VENIPAK_ADDRESS_PERSON'] = Tools::getValue('VENIPAK_ADDRESS_PERSON');
            $helper->fields_value['VENIPAK_ADDRESS_IS_DEFAULT'] = $hasDefaultAddress ? Tools::getValue('VENIPAK_ADDRESS_IS_DEFAULT') : 1;
        }



        return $output.$helper->generateForm($fields_form);
    }*/

    public function actionDeleteAddress(){
        if((int)Tools::getValue('VENIPAK_ADDRESS_ID')>0){
            $addressId = (int)Tools::getValue('VENIPAK_ADDRESS_ID');
            //check if exists
            self::checkForClass('VenipakDatabaseHelper');
            if(VenipakDatabaseHelper::getAddress($addressId)){
                VenipakDatabaseHelper::deleteAddress($addressId);
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            }
        }
    }

    public function warehousesList()
    {
        $content = array();
        //collect all current warehouses
        self::checkForClass('VenipakDatabaseHelper');
        $id_shop = $this->context->shop->id;
        $warehouses = VenipakDatabaseHelper::getWarehouses($id_shop);

        if(!empty($warehouses)){
            foreach($warehouses as $wh){
                // show only selected shop addresses;
                if ($id_shop == $wh['id_shop']) {
                    $content[] = array(
                        'VENIPAK_ADDRESS_ID'=>$wh['id'],
                        'address_title' => $wh['address_title'],
                        'id_shop' => $wh['id_shop'],
                        'name'=>$wh['name'],
                        'address'=>$wh['address'],
                        'is_default'=> ($wh['is_default']=='1' ? $this->l('Yes') : $this->l('No'))
                    );
                }
            }
        }

        $fields_list = $this->getListColumns();

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->listTotal = count($content);
        $helper->identifier = 'VENIPAK_ADDRESS_ID';
        $helper->title = 'Warehouses ';
        $helper->actions = array('edit','delete');
        $helper->table = 'venipakaddress';
        //$helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->toolbar_btn['new'] = array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure='.$this->name.'&modulepage=venipak_address&venipak_address_type=warehouse',
				'desc' => $this->l('New')
			);

        return $helper->generateList($content, $fields_list);
    }

    public function sendersList()
    {
        $content = array();
        //collect all current warehouses
        self::checkForClass('VenipakDatabaseHelper');
        $id_shop = Context::getContext()->shop->id;
        $warehouses = VenipakDatabaseHelper::getSenders($id_shop);
        if(!empty($warehouses)){
            foreach($warehouses as $wh)
            {
                $content[] = array(
                    'VENIPAK_ADDRESS_ID'=>$wh['id'],
                    'id_shop' => $wh['id_shop'],
                    'address_title' => $wh['address_title'],
                    'name'=>$wh['name'],
                    'address'=>$wh['address'],
                    'is_default'=> ($wh['is_default']=='1' ? $this->l('Yes') : $this->l('No'))
                );
            }
        }

        $fields_list = $this->getListColumns();

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->listTotal = count($content);
        $helper->identifier = 'VENIPAK_ADDRESS_ID';
        $helper->title = 'Senders ';
        $helper->actions = array('edit','delete');
        $helper->table = 'venipakaddress';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->toolbar_btn['new'] = array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure='.$this->name.'&modulepage=venipak_address&venipak_address_type=sender',
				'desc' => $this->l('New')
			);

        return $helper->generateList($content, $fields_list);
    }

    public function additionalForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name.'Module';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');


        $helper->tpl_vars = array(
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $uri = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $cron_token = substr(Tools::encrypt('venipakcarrier/cron'), 0, 10);
        $cron_url = $uri . 'modules/' . $this->name . '/cron.php?action=updatePickupPoints&token=' . $cron_token;

        $last_pickup_point_update = Configuration::get('VENIPAK_LAST_PICKUP_POINT_UPDATE');
        if ($last_pickup_point_update === false)
            $last_pickup_point_update = $this->l('never');

        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Pickup Points'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'html',
                        'label' => $this->l('Last Pickup Point\'s list update date:'),
                        'name' => 'pickup_point_block',
                        'html_content' => '<label class="control-label"><b>'.$last_pickup_point_update.'</b></label>',
                    ),
                    array(
                        'type' => 'html',
                        'label' => $this->l('Pickup point\'s list update CRON job url:'),
                        'name' => 'pickup_point_cron_job_url',
                        'html_content' => '<label class="control-label"><a href="'.$cron_url.'" target="_blank">'.$cron_url.'</a></label>',
                        'desc' => $this->l("Click this link to update pickup point's list"),
                    ),
                ),
            ),
        );

        return $helper->generateForm(array($form));
    }

    public function validateAddress(){

        //TODO: check default shop?

        foreach(VenipakHelper::getAddressFields() as $f){
            if(strval(Tools::getValue($f))=='' && $f!='VENIPAK_ADDRESS_COMPANY'){
                //find nice field name
                $fname = '';
                foreach($this->addressFormFields as $af){
                    if($af['name']==$f){
                        $fname=$af['label']; break;
                    }
                }
                $this->_postErrors[]=$fname.' field is empty';
            }
        }
        $companyCode = Tools::getValue('VENIPAK_ADDRESS_COMPANY');
        if($companyCode!=''){
            if(!is_numeric($companyCode)){
                $this->_postErrors[]=$this->l('Company code must be numeric.');
            }
            if(strlen($companyCode)<7){
                $this->_postErrors[]=$this->l('Company code is too short (at least 7 digits).');
            }
        }

        if(Tools::getValue('VENIPAK_ADDRESS_COUNTRY')=='LV'){
            if(strlen(Tools::getValue('VENIPAK_ADDRESS_POSTCODE'))!=4){
                $this->_postErrors[]=$this->l('Invalid post code.');
            }
        }else{
            if(strlen(Tools::getValue('VENIPAK_ADDRESS_POSTCODE'))!=5){
                $this->_postErrors[]=$this->l('Invalid post code.');
            }
        }
        return empty($this->_postErrors);
    }

    public function saveAddress() {

        $addressData = array(
                'id_shop' => $this->context->shop->id,
                'address_title'=>strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_TITLE')))),
                'name'=>strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_NAME')))),
                'company' => strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_COMPANY')))),
                'address' => strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_ADDRESS')))),
                'city' => strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_CITY')))),
                'country' => strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_COUNTRY')))),
                'postcode' => strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_POSTCODE')))),
                'phone_number' => strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_PHONE_NUMBER')))),
                'person' => strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_PERSON')))),
                'type' => '',
                'is_default' => 0,
            );

        $address_type = strval(strip_tags(trim(Tools::getValue('VENIPAK_ADDRESS_TYPE')))) == 'sender' ? 'sender' : 'warehouse';
        $addressData['type'] = $address_type;


        if(Tools::getValue('VENIPAK_ADDRESS_ID')==false){
            //create
            $result = Db::getInstance()->insert('venipak_carrier_addresses', $addressData);
            $lastId = Db::getInstance()->Insert_ID();

            //check for default
            if(Tools::getValue('VENIPAK_ADDRESS_IS_DEFAULT')=='1'){
                Db::getInstance()->update('venipak_carrier_addresses', array('is_default'=>'0'), 'is_default = 1 AND id_shop = '.(int)$this->context->shop->id.' AND type="'.$address_type.'"');
                Db::getInstance()->update('venipak_carrier_addresses', array('is_default'=>'1'), 'id = '.$lastId);

                return $result;
            }
        } else {
            //update
            $upData = $addressData;
            unset($upData['type']);
            $result = Db::getInstance()->update('venipak_carrier_addresses', $upData,
            'id='.(int)Tools::getValue('VENIPAK_ADDRESS_ID'));
            if(Tools::getValue('VENIPAK_ADDRESS_IS_DEFAULT')=='1'){
                Db::getInstance()->update('venipak_carrier_addresses', array('is_default'=>'0'), 'is_default=1 AND id_shop = '.(int)$this->context->shop->id.' AND type="'.$address_type.'"');
                Db::getInstance()->update('venipak_carrier_addresses', array('is_default'=>'1'), 'id='.(int)Tools::getValue('VENIPAK_ADDRESS_ID'));

                return $result;
            }
        }

        if (!VenipakDatabaseHelper::hasDefaultAddress($address_type, $this->context->shop->id))
            Db::getInstance()->update('venipak_carrier_addresses', array('is_default'=>'1'), 'is_default=0 AND id_shop = '.(int)$this->context->shop->id.' AND type="'.$address_type.'" LIMIT 1');

        return $result;
    }

    private function updateMaxPackNo($maxPackNo, $apiId)
    {
        $maxPackNo = (int)$maxPackNo;
        $apiId = (int)$apiId;

        if($maxPackNo < 0)
        {
            return false;
        }

        //first check for dublicate
        $result = Db::getInstance()->getValue('SELECT 1 FROM '._DB_PREFIX_.'venipak_order_pack WHERE pack_no='.$maxPackNo.' AND api_id='.$apiId);
        if(!empty($result))
        {
            return $this->displayError($this->l('Entry with such API ID and pack No. already exists.'));
        }

        $result = Db::getInstance()->insert('venipak_order_pack', array(
                    'pack_no' => $maxPackNo,
                    'api_id' => $apiId,
                    'order_id' => 0,
                    'weight' => 0,
                    'sent' => 1
                ));
        return $result;
    }

    private function getListColumns(){
        $fields_list = array(
            'VENIPAK_ADDRESS_ID' => array(
                'title' => 'ID',
                'align' => 'center',
                'class' => 'col-lg-1 col-xs-12',
            ),
            'id_shop' => array(
                'title' => $this->l('Id shop'),
                'align' => 'center',
                'class' => 'col-lg-2 col-xs-12',
            ),
            'address_title' => array(
                'title' => $this->l('Address title'),
                'align' => 'center',
                'class' => 'col-lg-2 col-xs-12',
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'align' => 'center',
                'class' => 'col-lg-2 col-xs-12',
            ),
            'address' => array(
                'title' => $this->l('Address'),
                'align' => 'center',
                'class' => 'col-lg-2 col-xs-12',
            ),
            'is_default' => array(
                'title' => $this->l('Is default?'),
                'align' => 'center',
                'class' => 'col-lg-5 col-xs-12',
            ),
        );
        return $fields_list;
    }

	public function hookActionCarrierUpdate($params)
	{
		if ((int)($params['id_carrier']) == (int)(Configuration::get('VENIPAK_CARRIER_ID'))){
			Configuration::updateValue('VENIPAK_CARRIER_ID', (int)($params['carrier']->id));
        }
        elseif ((int)($params['id_carrier']) == (int)(Configuration::get('VENIPAK_PICKUP_POINT_CARRIER_ID'))){
                Configuration::updateValue('VENIPAK_PICKUP_POINT_CARRIER_ID', (int)($params['carrier']->id));
            }
	}

    public function hookActionAdminControllerSetMedia($params)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            if (Tools::getValue('configure') == 'venipakcarrier' || Tools::getValue('controller') == 'AdminOrders') {

                Media::addJsDef(array(
                    'venipakAdminLabelsTitle' => $this->l("Print Venipak labels"),
                    'venipakAdminManifestsTitle' => $this->l("Print Venipak manifests"),
                    'venipakAdminLabels' => $this->checkHttps($this->context->link->getModuleLink("venipakcarrier", "ajax", array("action" => "printlabels"))),
                    'venipakAdminManifests' => $this->checkHttps($this->context->link->getModuleLink("venipakcarrier", "ajax", array("action" => "printbulkmanifest"))),
                ));

                $this->context->controller->addJs($this->_path . 'views/js/venipak.js');
            }
        }
    }

    public function hookDisplayBackOfficeHeader($params)
    {

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return '
              <script type="text/javascript">
                var venipakAdminLabelsTitle = "'.$this->l("Print Venipak labels").'";
                var venipakAdminManifestsTitle = "'.$this->l("Print Venipak manifests").'";
                var venipakAdminLabels = "'.$this->checkHttps($this->context->link->getModuleLink("venipakcarrier", "ajax", array("action" => "printlabels"))).'";
                var venipakAdminManifests = "'.$this->checkHttps($this->context->link->getModuleLink("venipakcarrier", "ajax", array("action" => "printbulkmanifest"))).'";
              </script>
            <script type="text/javascript" src="'.(__PS_BASE_URI__).'modules/'.$this->name.'/views/js/venipak.js"></script>
        ';
        }
    }

    private function checkHttps($url){
        if (empty($_SERVER['HTTPS'])) {
            return $url;
        } elseif ($_SERVER['HTTPS'] == "on") {
            return str_replace('http://', 'https://', $url);
        } else {
            return $url;
        }
    }

    public function hookHeader($params)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $checkoutType = 3;
        } else {
            $checkoutType = Configuration::get('PS_ORDER_PROCESS_TYPE');
        }

        Media::addJsDef(array(
            'venipak_ps_version' => substr(_PS_VERSION_, 0, 3),
            'venipak_checkout_type' => $checkoutType,
            'venipak_id_carrier' => Configuration::get('VENIPAK_CARRIER_ID'),
            'venipak_id_parcels' => Configuration::get('VENIPAK_PICKUP_POINT_CARRIER_ID'),
            'venipak_token' => Tools::getToken(false),
            'venipak_controller_url' => $this->context->link->getModuleLink('venipakcarrier', 'front'),
        ));


        if (in_array($this->context->controller->php_self, array('order', 'order-opc'))) {

            if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {

                $this->context->controller->registerJavascript('modules-venipak-js', 'modules/'.$this->name.'/views/js/front.js');
                $this->context->controller->registerStylesheet('modules-venipak-css', 'modules/'.$this->name.'/views/css/front.css');
                $this->context->controller->registerJavascript('modules-venipak-select2-js', 'modules/'.$this->name.'/views/js/select2.min.js');
                $this->context->controller->registerStylesheet('modules-venipak-select2-css', 'modules/'.$this->name.'/views/css/select2.min.css');

            } else {
                $this->context->controller->addJS($this->_path.'views/css/select2.min.js');
                $this->context->controller->addCSS($this->_path.'views/css/select2.min.css');
                $this->context->controller->addCSS($this->_path.'views/css/front.css');
                $this->context->controller->addJs($this->_path.'views/js/front.js');
            }
        }

    }

    public function hookExtraCarrier($params)
    {
        return $this->hookDisplayCarrierExtraContent($params);
    }

    public function hookDisplayCarrierList($params)
    {
        return $this->hookDisplayCarrierExtraContent($params);
    }

    public function hookDisplayCarrierExtraContent($params)
	{

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $id_carrier = $params['carrier']['id'];
            $pathCarrier = 'views/templates/hook/carrier_17.tpl';
            $pathPickup = 'views/templates/hook/pickup_17.tpl';
        } else {
            $id_carrier = $params['cart']->id_carrier;
            $pathCarrier = 'views/templates/hook/carrier_16.tpl';
            $pathPickup = 'views/templates/hook/pickup_16.tpl';
        }

        if ($id_carrier != (int)(Configuration::get('VENIPAK_CARRIER_ID')) && $id_carrier != (int)(Configuration::get('VENIPAK_PICKUP_POINT_CARRIER_ID')))
            return '';

        $ps_version = substr(_PS_VERSION_, 0, 3);

        if ($id_carrier == (int)(Configuration::get('VENIPAK_CARRIER_ID')))
        {
            global $smarty;

            $availableDeliveryTypes = array();
            if (Configuration::get('VENIPAK_DELIVERY_TYPES') != '') {
                $availableDeliveryTypes = explode(',', Configuration::get('VENIPAK_DELIVERY_TYPES'));
            }

            $smarty->assign(
                array(
                    'id_address_delivery' => $params['cart']->id_address_delivery,
                    'venipak_carrier_id' => $id_carrier,
                    'controllerurl' => $this->context->link->getModuleLink('venipakcarrier', 'front'),
                    'availableDeliveryTypes' => $availableDeliveryTypes,
                    'allDeliveryTypes' => $this->getDeliveryTimes()
                )
            );

            return $this->display(__FILE__, $pathCarrier);
        }
        else
        {
            $pickup_list_file = file_get_contents(_PS_MODULE_DIR_.$this->name."/pickup_points.json");

            if (empty($pickup_list_file))
                return;

            $pickup_list = json_decode($pickup_list_file, true);
            $deliveryAddressObj = new Address($params['cart']->id_address_delivery);

            // Show parcels by country;
            if ($deliveryAddressObj->country == 'Latvia') {
                unset($pickup_list['LT']);
                unset($pickup_list['EE']);
            } elseif ($deliveryAddressObj->country == 'Estonia') {
                unset($pickup_list['LT']);
                unset($pickup_list['LV']);
            } else {
                unset($pickup_list['EE']);
                unset($pickup_list['LV']);
            }

            global $smarty;
            $smarty->assign(
                array(
                    'id_address_delivery' => $params['cart']->id_address_delivery,
                    'venipak_carrier_id' => $id_carrier,
                    'controllerurl' => $this->context->link->getModuleLink('venipakcarrier', 'front'),
                    'pickup_points' => $pickup_list
                )
            );

            return $this->display(__FILE__, $pathPickup);
        }

	}


    public function getOrderShippingCost($params, $shipping_cost)
    {
      if ($this->id_carrier == (int)Configuration::get('VENIPAK_PICKUP_POINT_CARRIER_ID') && Module::isEnabled($this->name)){

          // If using default method -> return PrestaShop calculated price;
          if (Configuration::get('VENIPAK_PARCEL_CALCULATE_METHOD')) {
              return (float)$shipping_cost;
          } else {
              return $this->getOrderShippingCostExternal($params, $shipping_cost); // get fixed price by parcel country;
          }

      } elseif ($this->id_carrier == (int)Configuration::get('VENIPAK_CARRIER_ID') && Module::isEnabled($this->name)) {
          return (float)$shipping_cost;
      }

      return false; // carrier is not known
    }

    public function getOrderShippingCostExternal($cartObject, $shipping_cost = 0)
    {
        // TODO:: add weight validation && free shipping opportunity;
        $deliveryAddressObj = new Address((int)$cartObject->id_address_delivery);

        // Show parcels by country;
        if ($deliveryAddressObj->country == 'Latvia') {
            return (float)Configuration::get('VENIPAK_PARCEL_LV');
        } elseif ($deliveryAddressObj->country == 'Estonia') {
            return (float)Configuration::get('VENIPAK_PARCEL_EE');
        } else {
            return (float)Configuration::get('VENIPAK_PARCEL_LT');
        }
    }

    public function hookDisplayAdminOrder($params)
    {

        self::checkForClass('VenipakOrder');
        self::checkForClass('VenipakManifest');
        self::checkForClass('VenipakHelper');
        self::checkForClass('VenipakDatabaseHelper');

        // Load order object;
        $order = new Order((int)$params['id_order']);

        // checking settings
        $errorMessage = '';
        // checking API settings
        if (empty(Configuration::get('VENIPAK_API_URL')) || empty(Configuration::get('VENIPAK_API_PASSWORD')) ||
            empty(Configuration::get('VENIPAK_API_LOGIN')) || empty(Configuration::get('VENIPAK_API_ID_CODE'))
        )
        {
            $errorMessage = VenipakHelper::getMessage(1002, $this);
        }
        else
        // checking addresses
        if (!VenipakDatabaseHelper::hasDefaultAddress('warehouse', $order->id_shop) || !VenipakDatabaseHelper::hasDefaultAddress('sender', $order->id_shop))
        {
            $errorMessage = VenipakHelper::getMessage(1001, $this);
        }

        if (!empty($errorMessage)){

            $this->smarty->assign(array(
                'venipakBlockAllowed' => false,
                'errorMessage' => $errorMessage,
                'moduleUrl' => 'index.php?tab=AdminModules&configure=venipakcarrier&token='.Tools::getAdminTokenLite('AdminModules'),
                'order_id'=>(int)$params['id_order'],
                'venimoduleurl'=>$this->context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'savevenipakorder')),
            ));

            $veniForm = $this->display(__FILE__, 'views/templates/admin/blockinorder.tpl');

            return $veniForm;
        }

        //get weight of all products
        $venipak_carrier_id = Configuration::get('VENIPAK_CARRIER_ID');
        $venipak_pickup_carrier_id = Configuration::get('VENIPAK_PICKUP_POINT_CARRIER_ID');


        $veniCarrierObj = new VenipakCarrier;
        $isPickup = false;


        // checking if order carrier is venipak
        if($venipak_carrier_id != $order->id_carrier)
        {
            if ($venipak_pickup_carrier_id != $order->id_carrier)
            {
                // checking by carrier reference
                $order_carrier_reference = VenipakDatabaseHelper::getCarrierReference($order->id_carrier);
                $venipak_carrier_reference = VenipakDatabaseHelper::getCarrierReference($venipak_carrier_id);
                $venipak_pickup_carrier_reference = VenipakDatabaseHelper::getCarrierReference($venipak_pickup_carrier_id);

                if ($venipak_carrier_reference != $order_carrier_reference)
                {
                    if ($venipak_pickup_carrier_reference != $order_carrier_reference)
                    {
                        return false;
                    }
                    else
                        $isPickup = true;
                }
            }
            else
                $isPickup = true;
        }



        $manifestDates = array();
        $datetime = new DateTime(date('Y-m-d'));
        for($i=0;$i<6;$i++){

            $manifestDates[]=$datetime->format('Y-m-d');
            $datetime->modify('+1 day');
        }


        $venipakOrderObj = new VenipakOrder();
        $checkvenipakOrder = $venipakOrderObj->getOrderVeniData((int)$params['id_order']);
        $selectedComments = VenipakDatabaseHelper::getCartComments((int)$order->id_cart);

        $is_cod = VenipakHelper::checkIfOrderModuleIsCOD($order->module);

        // disabling cod for pickup points
        if ((bool)$is_cod && $isPickup === true)
        {
            $is_cod = false;
        }

        if(!empty($checkvenipakOrder))
        {
            $checkvenipakOrder['manifest_date'] = VenipakManifest::getManifest($checkvenipakOrder['manifest_no']);

            $selectedComments['comment_door_code'] = $checkvenipakOrder['comment_door_code'];
            $selectedComments['comment_office_no'] = $checkvenipakOrder['comment_office_no'];
            $selectedComments['comment_warehous_no'] = $checkvenipakOrder['comment_warehous_no'];
            $selectedComments['comment_call'] = $checkvenipakOrder['comment_call'];
            $selectedComments['id_pickup_point'] = $checkvenipakOrder['id_pickup_point'];
            $selectedComments['delivery_type'] = $checkvenipakOrder['delivery_time'];
        }


        $pickup_list_file = file_get_contents(_PS_MODULE_DIR_.$this->name."/pickup_points.json");
        $pickup_list = json_decode($pickup_list_file, true);


        $total_weight = (Configuration::get('VENIPAK_ORDER_WEIGHT')) ? $order->getTotalWeight() : 1;


        $this->smarty->assign(array(
            'pickup_points' => $pickup_list,
            'venipakBlockAllowed' => true,
            'venipak_warehouses'=>VenipakDatabaseHelper::getWarehouses($order->id_shop),
            'venipak_senders'=>VenipakDatabaseHelper::getSenders($order->id_shop),
            'venipak_manifest_dates'=>$manifestDates,
            'delivery_times'=>$this->getDeliveryTimes(),
            'total_weight'=> $total_weight,
            'total_paid_tax_incl'=>$order->total_paid_tax_incl,
            'venipakalldisabled'=>!empty($checkvenipakOrder),
            'venipakOrderInfo' => $checkvenipakOrder,
            'selectedComments' => $selectedComments,
            'is_cod' => $is_cod,
            'isPickup' => $isPickup,
            'order_id'=>(int)$params['id_order'],
            'venimoduleurl'=>$this->context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'savevenipakorder')),
            'veniprintlabelsurl'=>$this->context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'printlabels')),
            'VENIPAK_SHOW_RETURN_DOCS'=>Configuration::get('VENIPAK_SHOW_RETURN_DOCS'),
            'VENIPAK_SHOW_CHECK_ID'=>Configuration::get('VENIPAK_SHOW_CHECK_ID'),
            'VENIPAK_SHOW_PICKUP'=>Configuration::get('VENIPAK_SHOW_PICKUP'),
            'VENIPAK_DELIVERY_TYPES' => explode(',',Configuration::get('VENIPAK_DELIVERY_TYPES')),
        ));

        $veniForm = $this->display(__FILE__, 'views/templates/admin/blockinorder.tpl');
        return $veniForm;
    }

    public function getDeliveryTimes(){
        $deliveryTimes = array(
            'nwd' => $this->l('Standard'),
            'tswd' => $this->l('Same work day'),
            'nwd10' => $this->l('Next working day till 10:00'),
            'nwd12' => $this->l('Next working day till 12:00'),
            'nwd8_14' => $this->l('Next working day 8:00-14:00'),
            'nwd14_17' => $this->l('Next working day 14:00-17:00'),
            'nwd18_20' => $this->l('Next working day 18:00-22:00'),
            'nwd18a' => $this->l('Next working day after 18:00'),
            'sat' => $this->l('On saturday')
            );

        return $deliveryTimes;
    }

    /**
     *
     * @param string $date Format: Y-m-d
     * @return type
     */
    public function getTotalWeightByDate($date, $warehouse_id){

        $sql = 'SELECT SUM(product_weight * product_quantity)
                FROM
                  ps_order_detail psorderdetail
                  INNER JOIN ps_venipak_order_info AS venipakorder
                    ON psorderdetail.id_order = venipakorder.order_id
                  INNER JOIN ps_venipak_manifest as manifest
                    ON venipakorder.manifest_no = manifest.manifest_no
                WHERE manifest.manifest_date = \''.pSQL($date) .'\'
                AND manifest.warehouse_id = \''.pSQL($warehouse_id).'\'
                GROUP BY psorderdetail.id_order';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
		return (float)($result);
    }

    public function saveCarrierCall($id_shop, $warehouseId, $callDate, $callData){
        $result = Db::getInstance()->insert('venipak_carrier_call', array(
            'id_shop' => (int)$id_shop,
            'warehouse_id' => (int)$warehouseId,
            'call_date' => $callDate,
            'call_data' => $callData
        ));
        return $result;
    }

    public static function checkForClass($className){
        if(!class_exists($className)){
            if(isset(self::$_classMap[$className])){
                require_once _PS_MODULE_DIR_.'venipakcarrier/'.self::$_classMap[$className];
            }
        }
    }

    protected function checkRequiredFiels()
    {
        $errors = array();
        $ending = $this->l('field is required;');

        if (empty(Tools::getValue('VENIPAK_API_URL')))
            $errors[] = $this->l('API URL') .' '.$ending;

        if (empty(Tools::getValue('VENIPAK_API_LOGIN')))
            $errors[] = $this->l('API Login') .' '.$ending;

        if (empty(Tools::getValue('VENIPAK_API_PASSWORD')))
            $errors[] = $this->l('API Password') .' '.$ending;

        if (empty(Tools::getValue('VENIPAK_API_ID_CODE')))
            $errors[] = $this->l('API ID') .' '.$ending;

        $output = '';

        if (!empty($errors))
        {
            $output .= '<div class="bootstrap">
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        ' . count($errors) . ' ' . $this->l('errors') . ':
                            <br/>
                            <ol>';

            foreach ($errors as $error)
                    $output .= '<li>'.$error.'</li>';

            $output .= '     </ol>
                        </div>
                    </div>';
        }

        return $output;
    }

    public function updatePickupPoints($is_cron = false)
    {
        try {
            VenipakCarrier::checkForClass('VenipakAPI');
            VenipakCarrier::checkForClass('VenipakDatabaseHelper');
            VenipakCarrier::checkForClass('VenipakHelper');


            if (VenipakDatabaseHelper::checkTableIfExists('venipak_pickup_points') === false)
                throw new Exception('Cannot create/check if table exists');

            $api = new VenipakAPI();
            $pickup_points_json = $api->getPickupPoints();


            if (!empty($pickup_points_json)) {
                $pickup_points = json_decode($pickup_points_json, true);

                if (is_array($pickup_points) && !empty($pickup_points)) {

                    $pickup_points_id_list = array();

                    foreach ($pickup_points as $point) {
                        if (!isset($point['id']) || intval($point['id']) == 0)
                            continue;

                        $pickup_points_id_list[] = $point['id'];

                        $exists = Db::getInstance()->getValue("SELECT 1 FROM " . _DB_PREFIX_ . "venipak_pickup_points WHERE id = " . (int)$point['id']);

                        if ($exists) {
                            Db::getInstance()->update(
                                'venipak_pickup_points',
                                array(
                                    'name' => $point['name'],
                                    'code' => $point['code'],
                                    'address' => $point['address'],
                                    'city' => $point['city'],
                                    'zip' => $point['zip'],
                                    'country' => $point['country'],
                                    'working_hours' => $point['working_hours'],
                                    'contact_t' => $point['contact_t'],
                                    'lat' => $point['lat'],
                                    'lng' => $point['lng'],
                                    'pick_up_enabled' => $point['pick_up_enabled'],
                                    'cod_enabled' => $point['cod_enabled'],
                                    'ldg_enabled' => $point['ldg_enabled'],
                                    'pickup_point_enabled' => 1
                                ),
                                'id = ' . (int)$point['id']);
                        } else {
                            Db::getInstance()->insert(
                                'venipak_pickup_points',
                                array(
                                    'id' => $point['id'],
                                    'name' => $point['name'],
                                    'code' => $point['code'],
                                    'address' => $point['address'],
                                    'city' => $point['city'],
                                    'zip' => $point['zip'],
                                    'country' => $point['country'],
                                    'working_hours' => $point['working_hours'],
                                    'contact_t' => $point['contact_t'],
                                    'lat' => $point['lat'],
                                    'lng' => $point['lng'],
                                    'pick_up_enabled' => $point['pick_up_enabled'],
                                    'cod_enabled' => $point['cod_enabled'],
                                    'ldg_enabled' => $point['ldg_enabled'],
                                    'pickup_point_enabled' => 1
                                ));
                        }
                    }

                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "venipak_pickup_points SET pickup_point_enabled = 0 WHERE id NOT IN (" . implode(',', $pickup_points_id_list) . ")");
                }
            }

            Configuration::updateValue('VENIPAK_LAST_PICKUP_POINT_UPDATE', date('Y-m-d H:i:s'));

            $this->regeneratePickupPointsFile();

            if ($is_cron)
                exit(VenipakHelper::getMessage(3001, $this));
            else
                return true;
        }
        catch(Exception $ex)
        {
            if ($is_cron)
                exit(VenipakHelper::getMessage(3002, $this));
            else
                return false;
        }
    }

    private function regeneratePickupPointsFile()
    {
        $result = array();

        $allowed_countries = Configuration::get("VENIPAK_ALLOWED_PICKUP_COUNTRIES");
        if (empty($allowed_countries))
        {
            unlink(_PS_MODULE_DIR_.$this->name."/pickup_points.json");
            return;
        }

        $allowed_countries = "'" . str_replace(',', "','", $allowed_countries) . "'";

        $countries = Db::getInstance()->executeS("SELECT country FROM "._DB_PREFIX_."venipak_pickup_points WHERE pickup_point_enabled = 1 AND country IN (".$allowed_countries.") GROUP BY country ORDER BY count(1) DESC");

        foreach ($countries as $country)
        {
            $result[$country['country']] = array('name' => $this->l(VenipakHelper::getCountryNameByISO($country['country'])));
            $cities_array = array();
            $cities = Db::getInstance()->executeS("SELECT DISTINCT city FROM "._DB_PREFIX_."venipak_pickup_points WHERE pickup_point_enabled = 1 AND country = '".$country['country']."' ORDER BY city ASC");


            foreach ($cities as $city)
            {
                $city_array = array();
                $points_array = array();
                $city_array['name'] = $city['city'];

                $points = Db::getInstance()->executeS("SELECT id, CONCAT(TRIM(SUBSTRING(NAME,LOCATE(',', NAME)+1)), ', ', address) AS name FROM "._DB_PREFIX_."venipak_pickup_points WHERE pickup_point_enabled = 1 AND country = '".$country['country']."' AND city = '".$city['city']."' ORDER BY TRIM(SUBSTRING(NAME,LOCATE(',', NAME)+1)) ASC");

                foreach ($points as $point)
                {
                    $points_array[] = array('id' => $point['id'], 'name' => ucfirst($point['name']));
                }

                $city_array['point'] = $points_array;
                $cities_array[] = $city_array;
            }


            $result[$country['country']]['city'] = $cities_array;
        }

        file_put_contents(_PS_MODULE_DIR_.$this->name."/pickup_points.json", json_encode($result, JSON_UNESCAPED_UNICODE));
    }
}
