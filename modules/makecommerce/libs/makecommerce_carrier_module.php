<?php

class MakeCommerceCarrierModule extends CarrierModule
{
    const MODULES_COUNT = 'makecommerce_modules_count';

    const CACHE_VALID_TIME = 3600;

    static $jsIsIncluded = false;

    public function install()
    {

        if (!parent::install() OR
            !$this->registerHook('extraCarrier') OR
            !$this->registerHook('beforeCarrier') OR
            !$this->registerHook('insideCarrier') OR
            !$this->registerHook('actionValidateOrder') OR
            !$this->registerHook('actionPaymentConfirmation') OR
            !$this->registerHook('validateCarriers') OR
            !$this->registerHook('displayHeader') OR
            !$this->registerHook('displayAdminOrder') OR
            !$this->installCarrier() OR
            !$this->createCarrierTable() OR
            !$this->saveTerminals()

        ) {
            return false;
        }

        return true;
    }

    public function createCarrierTable()
    {

        if (!Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'makecommerce_carriers` (
			  `carrier` varchar(255) NOT NULL,
			  `carriers_list` mediumtext NOT NULL
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
		')) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        Db::getInstance()->update(
            'carrier',
            array('deleted' => 1),
            sprintf('`external_module_name` = \'%s\'', $this->name)
        );

        Configuration::updateValue(Tools::strtoupper('makecommerce_'.$this->carrier_name), 0);

        $this->removeModule();

        if (!parent::uninstall() OR
            (!$this->unregisterHook('insideCarrier') AND
                !$this->unregisterHook('extraCarrier')) OR
            !$this->unregisterHook('beforeCarrier') OR
            !$this->unregisterHook('actionValidateOrder') OR
            !$this->unregisterHook('actionPaymentConfirmation') OR
            !$this->unregisterHook('validateCarriers') OR
            !$this->unregisterHook('displayHeader') OR
            !$this->unregisterHook('displayAdminOrder')
        ) {
            return false;
        }

        return true;
    }

    public function installCarrier()
    {
        $carrier = new Carrier();
        $carrier->name = $this->carrier_front_name;
        $carrier->id_tax_rules_group = 0;
        $carrier->id_zone = 1;
        $carrier->delay = array();
        $carrier->range_behavior = 1;
        $carrier->is_module = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = $this->name;
        $carrier->need_range = true;
        $carrier->max_width = '38';
        $carrier->max_height = '41';
        $carrier->max_depth = '64';
        $carrier->max_weight = '30';

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $carrier->delay[(int)$language['id_lang']] = '2-3 days';
        }

        if ($carrier->add()) {
            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->insert(
                    'carrier_group',
                    array(
                        'id_carrier' => (int)($carrier->id),
                        'id_group' => (int)($group['id_group'])
                    )
                );
            }

            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '9999999';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '30';
            $rangeWeight->add();

            $zones = Zone::getZones(true);
            foreach ($zones as $zone) {
                Db::getInstance()->insert(
                    'carrier_zone',
                    array(
                        'id_carrier' => (int)($carrier->id),
                        'id_zone' => (int)($zone['id_zone'])
                    )
                );
                Db::getInstance()->insert(
                    'delivery',
                    array(
                        'id_carrier' => (int)($carrier->id),
                        'id_range_price' => (int)($rangePrice->id),
                        'id_range_weight' => NULL,
                        'id_zone' => (int)($zone['id_zone']),
                        'price' => '0'
                    )
                );
                Db::getInstance()->insert(
                    'delivery',
                    array(
                        'id_carrier' => (int)($carrier->id),
                        'id_range_price' => NULL,
                        'id_range_weight' => (int)($rangeWeight->id),
                        'id_zone' => (int)($zone['id_zone']),
                        'price' => '0'
                    )
                );
            }

            $carrier_logo = _PS_MODULE_DIR_.'makecommerce/views/img/carriers/'.$this->name.'.jpg';
            if (!copy(
                $carrier_logo,
                _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'
            )) {
                return false;
            }

            return (int)($carrier->id);
        }
        return false;
    }

    public function hookActionPaymentConfirmation($params)
    {
        $order = new Order($params['id_order']);
        if ($this->getCookie($order->id_address_delivery)) {
            unset($this->context->cookie->{$this->name . '_' . $order->id_address_delivery});
        }
        $carrier = new Carrier($order->id_carrier);
        if($carrier->external_module_name == $this->name){
            $this->createShipments($order);
        }
    }

    public function hookValidateCarriers($params)
    {
        if(isset($params['cart']) && Validate::isLoadedObject($params['cart'])) {
            $carrier = new Carrier($params['cart']->id_carrier);
            $extracarrier_data = $this->getCookie($params['cart']->id_address_delivery);
            if ($carrier->is_module && $carrier->external_module_name == $this->name &&
                (!isset($extracarrier_data['terminal_id'] ) ||
                    $extracarrier_data['terminal_id'] == 0)
            ) {
                return '<p class="warning">'.Tools::displayError('Error: Please choose a parcel terminal.').'</p>';
            }
        }
        return false;
    }

    public function hookActionValidateOrder($params)
    {
        if($params['order']->id_carrier == $this->getCarrierId()) {
            $extracarrier_data = $this->getCookie($params['order']->id_address_delivery);
            $address = new Address($params['order']->id_address_delivery);

            $terminals_json = Db::getInstance()->getValue(
                'SELECT `carriers_list` FROM `'._DB_PREFIX_.'makecommerce_carriers` WHERE `carrier` = \''.$this->carrier_name.'\''
            );

            $terminal_data = Tools::jsonDecode($terminals_json);
            $terminals = Tools::jsonDecode(Tools::jsonEncode($terminal_data->terminals), true);

            foreach($terminals as $terminal){
                if($terminal['id'] == $extracarrier_data['terminal_id']){
                    $order_terminal = new stdClass();
                    $order_terminal->id = $terminal['id'];
                    $order_terminal->name = $terminal['name'];
                    if($terminal['country'] == 'EE'){
                        $order_terminal->address = $terminal['address'];
                    }else{
                        $order_terminal->address = $terminal['city'];
                    }
                    $order_terminal->city = $terminal['city'];
                    break;
                }

            }

            $terminal_address = new Address();
            $terminal_address->id_country = $address->id_country;
            $terminal_address->id_state = $address->id_state;
            $terminal_address->id_customer = $address->id_customer;
            $terminal_address->id_manufacturer = $address->id_manufacturer;
            $terminal_address->id_supplier = $address->id_supplier;
            $terminal_address->id_warehouse = $address->id_warehouse;
            $terminal_address->alias = 'Terminal address';
            $terminal_address->company = $address->company;
            $terminal_address->lastname = $address->lastname;
            $terminal_address->firstname = $address->firstname;
            if (isset($order_terminal)) {
                $terminal_address->address1 = $order_terminal->name;
                $terminal_address->address2 = $order_terminal->address;
                $terminal_address->other = $order_terminal->id;
                $terminal_address->city = $order_terminal->city;
            } else {
                $terminal_address->address1 = $address->address1;
                $terminal_address->address2 = $address->address2;
                $terminal_address->other = $address->other;
                $terminal_address->city = $address->city;
            }
            $terminal_address->postcode = $address->postcode;
            $terminal_address->phone = $address->phone;
            $terminal_address->phone_mobile = $address->phone_mobile;
            $terminal_address->vat_number = $address->vat_number;
            $terminal_address->dni = $address->dni;
            $terminal_address->deleted = 1;
            $terminal_address->add();

            $order = new Order($params['order']->id);
            $order->id_address_delivery = $terminal_address->id;
            $params['order']->id_address_delivery = $terminal_address->id;
            $order->save();

        }
    }

    public function getOrderShippingCost($cart, $shipping_cost)
    {

        return $shipping_cost;

    }

    public function getOrderShippingCostExternal($cartObject)
    {

        return $this->getOrderShippingCost($cartObject, 0);

    }

    public function getFileName()
    {
        return $this->getLocalPath().$this->name.'.php';
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

    public function removeModule()
    {
        $modules_serialized = Configuration::getGlobalValue(self::MODULES_COUNT);
        $modules = Tools::unSerialize($modules_serialized);
        if (!is_array($modules)) {
            $modules = array();
        }
        if (isset($modules[$this->name])) {
            unset($modules[$this->name]);
        }
        Configuration::updateGlobalValue(self::MODULES_COUNT, serialize($modules));
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order($params['id_order']);

        $makecommerceCarrier = FALSE;

        foreach ($order->getShipping() as $carrier){
            $carrire_obj = new Carrier($carrier['id_carrier']);
            if($carrire_obj->external_module_name == $this->name){
                $makecommerceCarrier = TRUE;
                break;
            }
        }

        if($makecommerceCarrier){
            if (Tools::isSubmit('submitMKLabel')) {
                $label_url = $this->getParcelLabel($order);
                $this->smarty->assign(array(
                    'label_url' => $label_url,

                ));
            }

            $shipment_id = Db::getInstance()->getValue(
                'SELECT `tracking_number` FROM `'._DB_PREFIX_.'order_carrier` WHERE `id_order`=' . $order->id
            );
            if (Tools::isSubmit('submitMKRegister')) {
                if(empty($shipment_id)){
                    $this->createShipments($order);
                }
            }

            $this->smarty->assign(array(
                'carrier_name' => $this->carrier_name,
                'shipment_id' => $shipment_id

            ));
            $this->name = 'makecommerce';
            return $this->display(_PS_MODULE_DIR_ .'makecommerce/makecommerce.php', 'shipping_actions.tpl');
        }
    }

    public function getParcelLabel($order){
        $address = new Address($order->id_address_delivery);
        $customer = new Customer($order->id_customer);
        $makecommerce = new MakeCommerce();
        // Prepare phone number - for Omniva mainly
        $phone_number = (isset($address->phone_mobile) && $address->phone_mobile) ? $address->phone_mobile : $address->phone;
        if( substr( $phone_number, 0, 1) != '+') {
            switch(Country::getIsoById($address->id_country)) {
                case 'EE':
                    if (substr($phone_number,0,1) == '5')
                        $phone_number = '+372' . $phone_number;
                    elseif (substr($phone_number,0,4) == '3725')
                        $phone_number = '+' . $phone_number;
                    elseif (substr($phone_number,0,5) == '+3725')
                        ; // break;
                    else
                        error_log("Recipients phone number $phone_number is (probably) invalid for country Estonia!");
                    break;
                case 'LV':
                    if (substr($phone_number,0,1) == '2')
                        $phone_number = '+371' . $phone_number;
                    elseif (substr($phone_number,0,4) == '3712')
                        $phone_number = '+' . $phone_number;
                    elseif (substr($phone_number,0,5) == '+3712')
                        ; //break;
                    else
                        error_log("Recipients phone number $phone_number is (probably) invalid for country Latvia!");
                    break;
                case 'LT':
                    if (substr($phone_number,0,1) == '6')
                        $phone_number = '+370' . $phone_number;
                    elseif (substr($phone_number,0,4) == '3706')
                        $phone_number = '+' . $phone_number;
                    elseif (substr($phone_number,0,5) == '+3706')
                        ; // break;
                    else
                         error_log("Recipients phone number $phone_number is (probably) invalid for country Lithuania!");
            }
        }
        $shipment_id = Db::getInstance()->getValue(
            'SELECT `tracking_number` FROM `'._DB_PREFIX_.'order_carrier` WHERE `id_order`=' . $order->id
        );
        if(empty($shipment_id)){
            $this->createShipments($order);
            $shipment_id = Db::getInstance()->getValue(
                'SELECT `tracking_number` FROM `'._DB_PREFIX_.'order_carrier` WHERE `id_order`=' . $order->id
            );
        }
        $credentials = array(
            'carrier' => $this->carrier_name,
            'username' => $makecommerce->getConfig($this->carrier_name.'_username'),
            'password' => $makecommerce->getConfig($this->carrier_name.'_password')
        );

        $orders = array(
            'orderId' => 'order '.$order->id,
            'carrier' => $this->carrier_name,
            'destination' => array(
                'destinationId' => $address->other,
            ),
            'recipient' => array(
                'name' => $address->firstname.' '.$address->lastname,
                'phone' => $phone_number, // (isset($address->phone_mobile) && $address->phone_mobile) ? $address->phone_mobile : $address->phone,
                'email' => $customer->email
            ),
            'sender' => array(
                'name' => $makecommerce->getConfig($this->carrier_name.'_sender_name'),
                'phone' => $makecommerce->getConfig($this->carrier_name.'_phone'),
                'email' => $makecommerce->getConfig($this->carrier_name.'_email'),
                'street' => $makecommerce->getConfig($this->carrier_name.'_street'),
                'city' => $makecommerce->getConfig($this->carrier_name.'_city'),
                'country' => $makecommerce->getConfig($this->carrier_name.'_country'),
                'postalCode' => $makecommerce->getConfig($this->carrier_name.'_zip'),
            ),
            'shipmentId' => $shipment_id,
        );

        $request_body = array(
            'credentials' => array($credentials),
            'orders' => array($orders),
            'printFormat' => 'A4'
        );

        $api = $makecommerce->getApi();

        try{
            $label = $api->createLabels($request_body);
        }catch (Exception $e){
            PrestaShopLogger::addLog(
                $e->getMessage(),
                1,
                null,
                'MakeCommerce'
            );
            return false;
        }
        if(isset($label->labelUrl) && $label->labelUrl){
            return $label->labelUrl;
        }
    }

    public function hookDisplayBeforeCarrier($params)
    {
        return $this->hookDisplayCarrierList(array('id_address' => $params['cart']->id_address_delivery));
    }

    public function hookDisplayHeader($params)
    {
        if (!self::$jsIsIncluded) {
            self::$jsIsIncluded = true;
            $this->context->controller->registerJavascript('modules-makecommerceomniva', 'modules/makecommerce/views/js/carrier_script.js', ['position' => 'bottom', 'priority' => 150]);
        }
    }

    public function hookDisplayCarrierList($params)
    {
        $id_address = (isset($params['address']) ? $params['address']->id : $params['id_address']);
        $selected_carriers = $this->context->cart->getDeliveryOption(null, true);

        $this->id_carrier = $this->getCarrierId();


        if (!$selected_carriers) {
            $selected_carriers = $this->context->cart->getDeliveryOption(null,false);
        }

        $cookie = false;
        $carrier = new Carrier($this->id_carrier);

        if ($carrier->is_module && $carrier->external_module_name == $this->name) {
            $extra_carrier['id'] = $carrier->id;
            $extra_carrier['name'] = $this->name;
            $cookie = $this->getCookie($id_address);
            $zero_cookie = $this->getCookie(0);

            if ($id_address &&
                $zero_cookie &&
                (!empty($zero_cookie['terminal_id']) || !empty($zero_cookie['group_id']))
            ) {
                $this->saveToCookie($id_address, $zero_cookie);
                $this->saveToCookie(0, false);
            }

            if ($cookie !== false && isset($cookie['terminal_id'])) {
                $extra_carrier['terminal_id'] = $cookie['terminal_id'];
            } else {
                $extra_carrier['terminal_id'] = 0;
            }

            $this->smarty->assign(array(
                'ajax_url' => $this->context->link->getModuleLink($this->name, 'ajax'),
            ));

            return $this->displayList($extra_carrier, $id_address);

        }
    }

    public function saveToCookie($id_address, $value)
    {
        $this->context->cookie->{$this->name . '_' . $id_address} = serialize($value);
    }

    public function getCarrierId($active = true)
    {
        $query = new DbQuery();
        $query->select('id_carrier');
        $query->from('carrier');
        $query->where('`is_module` = 1');
        $query->where('`deleted` = 0');
        $query->where(sprintf('`external_module_name` = \'%s\'', pSQL($this->name)));
        if ($active) {
            $query->where('`active` = 1');
        }

        $carriers = Db::getInstance()->executeS($query);
        if (!empty($carriers)) {
            return $carriers[0]['id_carrier'];
        }
        return 0;
    }

    public function getCookie($id_address)
    {
        $data = $this->context->cookie->{$this->name . '_' . $id_address};
        if ($data) {
            return Tools::unSerialize($data);
        }
        return false;
    }

    public function displayList($extra_carrier, $id_address)
    {
        $module_name =  $this->name;
        $this->name = 'makecommerce';
        $this->smarty->assign(array(
            'terminals' => $this->getTerminals(),
            'carrier' => $extra_carrier,
            'id_address' => $id_address,
            'module' => $module_name
        ));

        if($this->getConfig('parcel_grouping')) {
            $template = $this->display('makecommerce', 'terminals.tpl');
        } else {
            $template = $this->display('makecommerce', 'terminals2.tpl');
        }
        $this->name = $module_name;
        return $template;
    }

    public function getTerminals()
    {

        $context = Context::getContext();
        $address = new Address($context->cart->id_address_delivery);
        if(isset($address->id_country) && $address->id_country){
            $id_country = $address->id_country;
        }else{
            $id_country = Configuration::get('PS_COUNTRY_DEFAULT');
        }
        $county_iso_code = Country::getIsoById($id_country);

        $terminals_json = Db::getInstance()->getValue(
            'SELECT `carriers_list` FROM `'._DB_PREFIX_.'makecommerce_carriers` WHERE `carrier` = \''.$this->carrier_name.'\''
        );

        $terminal_data = Tools::jsonDecode($terminals_json);

        if (!empty($terminal_data)) {
            if (empty($terminal_data->updated) || $terminal_data->updated + self::CACHE_VALID_TIME < time()){
                $terminal_data = null;
            }else{
                $terminals = Tools::jsonDecode(Tools::jsonEncode($terminal_data->terminals), true);
            }
        }

        if (empty($terminal_data)) {

            $this->saveTerminals();

            $terminals_json = Db::getInstance()->getValue(
                'SELECT `carriers_list` FROM `'._DB_PREFIX_.'makecommerce_carriers` WHERE `carrier` = \''.$this->carrier_name.'\''
            );

            if(isset($terminals_json) && $terminals_json){
                $terminal_data = Tools::jsonDecode($terminals_json);
                $terminals = Tools::jsonDecode(Tools::jsonEncode($terminal_data->terminals), true);
            }
        }

        $apt_terminals = array();
        if(isset($terminals) && $terminals){
            foreach($terminals as $terminal){
                if($terminal['carrier'] == $this->carrier_name && $terminal['type'] == 'APT' && $terminal['country'] == $county_iso_code){
                    array_push($apt_terminals, $terminal);
                }
            }
        }

        //Group terminals by city name

        $grouped_terminals = array();
        foreach ($apt_terminals as $terminal) {

            $city = $terminal['city'];
            $grouped_terminals[$city][] = $terminal;

        }

        //Move cities with higher priority top of the list

        if(array_key_exists ('Tartu linn', $grouped_terminals)){
            $grouped_terminals = array('Tartu linn' => $grouped_terminals['Tartu linn']) + $grouped_terminals;
        }
        if(array_key_exists ('Tartu', $grouped_terminals)){
            $grouped_terminals = array('Tartu' => $grouped_terminals['Tartu']) + $grouped_terminals;
        }
        if(array_key_exists ('Tallinn', $grouped_terminals)){
            $grouped_terminals = array('Tallinn' => $grouped_terminals['Tallinn']) + $grouped_terminals;
        }
        return $grouped_terminals;

    }

    public function saveTerminals(){

        $request_body = array(
            'carriers' => [$this->carrier_name],
            'country' => '',
            'type' => ''
        );

        $makecommerce = new MakeCommerce();
        $api = $makecommerce->getApi();

        try{
            $terminals = $api->getDestinations($request_body);
        }catch (Exception $e){
            PrestaShopLogger::addLog(
                $e->getMessage(),
                1,
                null,
                'MakeCommerce'
            );
            return false;
        }

        $object = new stdClass();
        $object->updated = time();
        $object->terminals = $terminals;
        $terminals_json = Tools::jsonEncode($object);

        $exist = Db::getInstance()->getValue(
            'SELECT EXISTS(SELECT `carriers_list` FROM `'._DB_PREFIX_.'makecommerce_carriers` WHERE `carrier` = \''.$this->carrier_name.'\')'
        );

        if ($exist) {
            Db::getInstance()->execute(
                'UPDATE `'._DB_PREFIX_.'makecommerce_carriers` SET `carriers_list` = \''.pSQL($terminals_json).'\' WHERE `carrier` = \''.$this->carrier_name.'\''
            );
        } else {
            Db::getInstance()->execute(
                'INSERT INTO `'._DB_PREFIX_.'makecommerce_carriers` (`carrier`, `carriers_list`) VALUES(\''.$this->carrier_name.'\', \''.pSQL($terminals_json).'\')'
            );
        }

        return true;

    }

    public function createShipments($order)
    {
        $address = new Address($order->id_address_delivery);
        $customer = new Customer($order->id_customer);
        $makecommerce = new MakeCommerce();
        // Prepare phone number - for Omniva mainly
        $phone_number = (isset($address->phone_mobile) && $address->phone_mobile) ? $address->phone_mobile : $address->phone;
        if( substr( $phone_number, 0, 1) != '+') {
            switch(Country::getIsoById($address->id_country)) {
               case 'EE':
                   if (substr($phone_number,0,1) == '5')
                       $phone_number = '+372' . $phone_number;
                   elseif (substr($phone_number,0,4) == '3725')
                       $phone_number = '+' . $phone_number;
                   elseif (substr($phone_number,0,5) == '+3725')
                       ; // break;
                   else
                       error_log("Recipients phone number $phone_number is (probably) invalid for country Estonia!");
                   break;
               case 'LV': 
                   if (substr($phone_number,0,1) == '2')
                       $phone_number = '+371' . $phone_number;
                   elseif (substr($phone_number,0,4) == '3712')
                       $phone_number = '+' . $phone_number;
                   elseif (substr($phone_number,0,5) == '+3712')
                       ; // break;
                   else
                       error_log("Recipients phone number $phone_number is (probably) invalid for country Latvia!");
                   break;
               case 'LT':
                   if (substr($phone_number,0,1) == '6')
                       $phone_number = '+370' . $phone_number;
                   elseif (substr($phone_number,0,4) == '3706')
                       $phone_number = '+' . $phone_number;
                   elseif (substr($phone_number,0,5) == '+3706')
                       ; // break;
                   else
                       error_log("Recipients phone number $phone_number is (probably) invalid for country Lithuania!");
            }
        }                
        $credentials = array(
            'carrier' => $this->carrier_name,
            'username' => $makecommerce->getConfig($this->carrier_name.'_username'),
            'password' => $makecommerce->getConfig($this->carrier_name.'_password')
        );
        $orders = array(
            'orderId'=> 'order '.$order->id,
            'carrier'=> $this->carrier_name,
            'destination' => array(
                'destinationId' => $address->other,
            ),
            'recipient'=> array(
                'name' => $address->firstname.' '.$address->lastname,
                'phone' => $phone_number, // (isset($address->phone_mobile) && $address->phone_mobile) ? $address->phone_mobile : $address->phone,
                'email' => $customer->email,
            ),
            'sender' => array(
                'name' => $makecommerce->getConfig($this->carrier_name.'_sender_name'),
                'phone' => $makecommerce->getConfig($this->carrier_name.'_phone'),
                'email' => $makecommerce->getConfig($this->carrier_name.'_email'),
                'street' => $makecommerce->getConfig($this->carrier_name.'_street'),
                'city' => $makecommerce->getConfig($this->carrier_name.'_city'),
                'country' => $makecommerce->getConfig($this->carrier_name.'_country'),
                'postalCode' => $makecommerce->getConfig($this->carrier_name.'_zip')
            ),

        );
        
        $request_body = array(
            'credentials' => array($credentials),
            'orders' => array($orders)
        );
        $api = $makecommerce->getApi();
        try{
            $shipments = $api->createShipments($request_body);
        }catch (Exception $e){
            PrestaShopLogger::addLog(
                $e->getMessage(),
                1,
                null,
                'MakeCommerce'
            );
            return false;
        }

        $shipment = json_decode(json_encode($shipments), True);

        if (!empty($shipments) && isset($shipment[0]['shipmentId']))
        {
            Db::getInstance()->update(
                'order_carrier',
                array('tracking_number' => $shipment[0]['shipmentId']),
                '`id_order`=' . $order->id
            );
        }
    }

}
