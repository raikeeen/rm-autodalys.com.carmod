<?php

if (!defined('_PS_VERSION_'))
    exit;


require_once(dirname(__FILE__).'/classes/BalticPostAPI.php');
require_once(dirname(__FILE__).'/classes/Logger.php');
require_once(dirname(__FILE__).'/classes/Terminal.php');
require_once(dirname(__FILE__).'/classes/BoxSize.php');
require_once(dirname(__FILE__).'/classes/LPOrder.php');

class LPExpress extends CarrierModule
{
    public $logger;

    public function __construct()
    {
        $this->name = 'lpexpress';
        $this->version = '1.0.9';
        $this->author = 'PrestaRock';
        $this->need_instance = 1;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('LP Express 24 pristatymo būdai');
        $this->description = $this->l('LP Express 24 pristatymo būdai į siuntinių terminalus, arčiausią pašto skyrių arba į namus.');

        $this->ps_versions_compliancy = array('min' => '1.6.0', 'max' => _PS_VERSION_);
        $this->logger = new Logger(dirname(__FILE__).'/logs/', ['create_file_on_awake' => false]);

        if (!Configuration::get('LP_API_CONNECTED'))
        {
            $this->warning = $this->l('Connection with BalticPostAPI not configured.');
        }
        if (version_compare($this->version, $this->getLatestVersion(), '<'))
        {
            $this->warning = $this->l('New version available. Open module configuration and click on toolbar Update terminals.');
        }
    }

    public function install()
    {
        $res = parent::install();

        if ($res)
        {
            $this->installAdminTabs();
            $this->installCarriers();

            $this->registerHook('header'); // Add JS to front office order page

            $this->registerHook('actionCarrierUpdate'); // PS 1.7 hook after updating carrier configuration
            $this->registerHook('updateCarrier'); // PS 1.6 hook after updating carrier configuration

            $this->registerHook('displayBeforeCarrier'); // Hook call one time on front office order page load
            $this->registerHook('displayCarrierList'); // Front office hook after carrier selection
            $this->registerHook('displayCarrierExtraContent'); // Display content for each carrier after carrier name

            $this->registerHook('actionValidateOrder'); // Execute when new order is created
            $this->registerHook('displayAdminOrderLeft'); // Display on admin order page

            Configuration::updateValue('LP_ORDER_TERMINAL_TYPE', 'HC');
            Configuration::updateValue('LP_ORDER_HOME_TYPE', 'EB');

            Configuration::updateValue('LP_SENDER_NAME', Configuration::get('PS_SHOP_NAME'));
            Configuration::updateValue('LP_SENDER_EMAIL', Configuration::get('PS_SHOP_EMAIL'));

            $address = Configuration::get('PS_SHOP_ADDR1').(!empty(Configuration::get('PS_SHOP_ADDR2')) ? ', '.Configuration::get('PS_SHOP_ADDR2') : '');
            Configuration::updateValue('LP_SENDER_ADDRESS', $address);
            Configuration::updateValue('LP_SENDER_ZIP', Configuration::get('PS_SHOP_CODE'));
            Configuration::updateValue('LP_SENDER_CITY', Configuration::get('PS_SHOP_CITY'));
            Configuration::updateValue('LP_SENDER_COUNTRY', 'LT');

            $phone = Configuration::get('PS_SHOP_PHONE');
            $phone = $this->formatPhoneNumber($phone);
            Configuration::updateValue('LP_SENDER_PHONE', $phone);

            Configuration::updateValue('LP_API_CONNECTED', 0);

            $shop_name = Configuration::get('PS_SHOP_NAME');

            if (version_compare(_PS_VERSION_, '1.7', '<'))
            {
                $token = Tools::encrypt($shop_name);
            }
            else
            {
                $token = Tools::hash($shop_name);
            }
            Configuration::updateValue('LP_CRON_TOKEN', $token);

            $this->executeDB('install');
        }

        return $res;
    }

    public function uninstall()
    {
        $this->unregisterHook('actionCarrierUpdate');
        $this->unregisterHook('updateCarrier');
        $this->unregisterHook('displayCarrierList');
        $this->unregisterHook('header');
        $this->unregisterHook('displayBeforeCarrier');
        $this->unregisterHook('displayCarrierExtraContent');
        $this->unregisterHook('actionValidateOrder');
        $this->unregisterHook('displayAdminOrderLeft');

        $res = parent::uninstall();

        if ($res)
        {
            $this->uninstallAdminTabs();
            $this->uninstallCarrier();

            Configuration::deleteByName('LP_PARTNER_ID');
            Configuration::deleteByName('LP_PARTNER_PASSWORD');
            Configuration::deleteByName('LP_PAYMENT_PIN');
            Configuration::deleteByName('LP_ADMIN_PIN');
            Configuration::deleteByName('LP_CUSTOMER_ID');
            Configuration::deleteByName('LP_DEVELOPMENT_MODE');

            Configuration::deleteByName('LP_ORDER_TERMINAL_TYPE');
            Configuration::deleteByName('LP_ORDER_HOME_TYPE');
            Configuration::deleteByName('LP_COD_MODULES');

            Configuration::deleteByName('LP_SENDER_NAME');
            Configuration::deleteByName('LP_SENDER_EMAIL');
            Configuration::deleteByName('LP_SENDER_ADDRESS');
            Configuration::deleteByName('LP_SENDER_ZIP');
            Configuration::deleteByName('LP_SENDER_CITY');
            Configuration::deleteByName('LP_SENDER_COUNTRY');
            Configuration::deleteByName('LP_SENDER_PHONE');

            Configuration::deleteByName('LP_TERMINAL_LAST_UPDATE');
            Configuration::deleteByName('LP_API_CONNECTED');
            Configuration::deleteByName('LP_CRON_TOKEN');

            $this->executeDB('uninstall');
        }

        return $res;
    }

    private function getAdminTabs()
    {
        return array(
            array('name' => $this->l('LPExpress container'), 'class_name' => 'AdminParentLPExpress', 'active' => 0, 'parent_class' => 'AdminParentModulesSf'),
            array('name' => $this->l('LPExpress'), 'class_name' => 'AdminLPExpress', 'active' => 1, 'parent_class' => 'AdminParentLPExpress'),
            array('name' => $this->l('Terminal update'), 'class_name' => 'AdminLPExpressTerminal', 'active' => 1, 'parent_class' => 'AdminParentLPExpress'),
        );
    }

    private function installAdminTabs()
    {
        foreach ($this->getAdminTabs() as $tab)
        {
            $admin_tab = new Tab();
            $admin_tab->module = $this->name;
            $admin_tab->class_name = $tab['class_name'];
            $admin_tab->active = $tab['active'];
            $admin_tab->id_parent = Tab::getIdFromClassName($tab['parent_class']);

            foreach (Language::getLanguages(false) as $language)
            {
                $admin_tab->name[$language['id_lang']] = $tab['name'];
            }

            if (!$admin_tab->save())
            {
                return false;
            }
        }
        return true;
    }

    private function uninstallAdminTabs()
    {
        foreach (array_reverse($this->getAdminTabs()) as $tab)
        {
            $id_tab = Tab::getIdFromClassName($tab['class_name']);
            $admin_tab = new Tab($id_tab);

            if (!Validate::isLoadedObject($admin_tab) || !$admin_tab->delete())
            {
                return false;
            }
        }
        return true;
    }

    private function getCarriers()
    {
        return array(
            array(
                'name' => $this->l('LP Express 24 pristatymas į namus'),
                'logo' => $this->getLocalPath().'logo.png',
                'delay' => $this->l('Pristatymas per 1-2 d.d.'),
                'configuration_name' => 'LP_CARRIER_HOME'
            ),
            array(
                'name' => $this->l('LP Express 24 pristatymas į paštomatą'),
                'logo' => $this->getLocalPath().'logo.png',
                'delay' => $this->l('Pristatymas per 1-2 d.d.'),
                'configuration_name' => 'LP_CARRIER_TERMINAL'
            ),
            array(
                'name' => $this->l('LP Express 24 pristatymas į artimiausią pašto skyrių'),
                'logo' => $this->getLocalPath().'logo.png',
                'delay' => $this->l('Pristatymas per 1-2 d.d.'),
                'configuration_name' => 'LP_CARRIER_TO_POST'
            )
        );
    }

    private function installCarriers()
    {
        foreach ($this->getCarriers() as $carrier_row)
        {
            $carrier = new Carrier();

            $carrier->name = $carrier_row['name'];
            $carrier->active = true;
            $carrier->deleted = false;
            $carrier->is_module = true;
            $carrier->external_module_name = $this->name;

            $carrier->shipping_handling = false;
            $carrier->shipping_external = true;
            $carrier->range_behavior = false;
            $carrier->need_range = true;
            $carrier->is_free = true;

            $carrier->setTaxRulesGroup(0);

            foreach (Language::getLanguages(false) as $language)
            {
                $carrier->delay[$language['id_lang']] = $carrier_row['delay'];
            }

            foreach (Shop::getShops() as $shop)
            {
                $carrier->id_shop_list[] = $shop['id_shop'];
            }

            if (!$carrier->add())
            {
                return false;
            }
            Configuration::updateValue($carrier_row['configuration_name'], $carrier->id);

            // Assign carrier for user groups
            foreach (Group::getGroups(true) as $group)
            {
                $data = [
                    'id_carrier' => (int) $carrier->id,
                    'id_group' => (int) $group['id_group']
                ];

                Db::getInstance()->insert('carrier_group', $data);
            }

            foreach (Zone::getZones(true) as $zone)
            {
                $carrier->addZone($zone['id_zone']);
            }

            // Add images to carrier
            if (isset($carrier_row['logo']) && !empty($carrier_row['logo']))
            {
                $destination = _PS_SHIP_IMG_DIR_ . $carrier->id . '.jpg';
                if (!Tools::copy(realpath($carrier_row['logo']), $destination))
                {
                    return false;
                }
            }
        }
        return true;
    }

    private function uninstallCarrier()
    {
        foreach ($this->getCarriers() as $carrier_row)
        {
            $id_carrier = Configuration::get($carrier_row['configuration_name']);
            if (!$id_carrier)
            {
                return true;
            }

            $carrier = new Carrier($id_carrier);
            if (!Validate::isLoadedObject($carrier))
            {
                return true;
            }

            if (!$carrier->delete())
            {
                return false;
            }

            Configuration::deleteByName($carrier_row['configuration_name']);
        }
        return true;
    }

    private function executeDB($action)
    {
        $path = dirname(__FILE__).'/setup/'.$action.'.sql';
        if (!file_exists($path))
        {
            return false;
        }

        $query = file_get_contents($path);
        $query = str_replace('_DB_PREFIX_', _DB_PREFIX_, $query);
        $query = str_replace('_MYSQL_ENGINE_', _MYSQL_ENGINE_, $query);

        return Db::getInstance()->query($query);
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminLPExpress'));
    }

    private function formatPhoneNumber($phone_number)
    {
        // If number start with 8 then change to +370
        $pattern = '/^8/';
        $phone_number = preg_replace($pattern, '+370', $phone_number);

        return $phone_number;
    }

    public function updateTerminals()
    {
        $errors = [];

        BalticPostAPI::setEnvironment(Configuration::get('LP_DEVELOPMENT_MODE'));
        BalticPostAPI::setAuthData(Configuration::get('LP_PARTNER_ID'), Configuration::get('LP_PARTNER_PASSWORD'));
        if (!BalticPostAPI::testAuth())
        {
            $errors[] = $this->l('Failed connect to BalticPostAPI');
            return $errors;
        }

        $start_date = new DateTime();
        $terminals = BalticPostAPI::getPublicTerminals();
        if (!$terminals)
        {
            $error = BalticPostAPI::getLastError()->getMessage();
            $this->logger->error('Failed retrieve public terminals.', ['error' => $error]);
            $errors[] = $error;
        }
        else
        {
            foreach ($terminals as $terminal_row)
            {
                $terminal = new Terminal();
                $terminal->loadByMachineID($terminal_row['machineid']);

                if (!Validate::isLoadedObject($terminal))
                {
                    $terminal->active = 1;
                    if (empty($terminal_row['boxcount']) || $terminal_row['boxcount'] <= 0)
                    {
                        $terminal->active = 0;
                    }
                    $terminal->machineid = $terminal_row['machineid'];
                }

                $terminal->name = $terminal_row['name'];
                $terminal->address = $terminal_row['address'];
                $terminal->zip = $terminal_row['zip'];
                $terminal->city = $terminal_row['city'];
                $terminal->comment = $terminal_row['comment'];
                $terminal->inside = $terminal_row['inside'];
                $terminal->boxcount = $terminal_row['boxcount'];
                $terminal->collectinghours = $terminal_row['collectinghours'];
                $terminal->workinghours = $terminal_row['workinghours'];
                $terminal->latitude = $terminal_row['latitude'];
                $terminal->longitude = $terminal_row['longitude'];

                if (!$terminal->save())
                {
                    $this->logger->error('Failed save/update terminal object.', ['terminal' => $terminal_row]);
                }
                else
                {
                    $terminal->removeBoxes();
                    if (isset($terminal_row['boxes']))
                    {
                        foreach ($terminal_row['boxes'] as $box_row)
                        {
                            $box = new BoxSize();
                            $box->loadBySize($box_row['size']);

                            if (!Validate::isLoadedObject($box))
                            {
                                $box->size = $box_row['size'];
                                if (!$box->save())
                                {
                                    $this->logger->error('Failed save box object.', ['box' => $box_row]);
                                }
                            }
                            $terminal->addBox($box->id);
                        }
                    }
                }
            }
            Configuration::updateValue('LP_TERMINAL_LAST_UPDATE', time());
            Terminal::disableOutdatedTerminals($start_date->format('Y-m-d H:i:s'));
        }
        return $errors;
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        $this->getOrderShippingCost($params, 0);
    }

    /* -------------------- Back office -------------------- */

    public function hookActionCarrierUpdate($params)
    {
        $this->updateCarrierID($params);
    }

    public function hookUpdateCarrier($params)
    {
        $this->updateCarrierID($params);
    }

    /**
     * Update carrier ID after updating carrier configuration
     * @param $params
     */
    public function updateCarrierID($params)
    {
        switch ($params['id_carrier'])
        {
            case Configuration::get('LP_CARRIER_TO_POST'):
                Configuration::updateValue('LP_CARRIER_TO_POST', $params['carrier']->id);
                break;

            case Configuration::get('LP_CARRIER_TERMINAL'):
                Configuration::updateValue('LP_CARRIER_TERMINAL', $params['carrier']->id);
                break;

            case Configuration::get('LP_CARRIER_HOME'):
                Configuration::updateValue('LP_CARRIER_HOME', $params['carrier']->id);
                break;

            default:
                break;
        }
    }

    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $cart = $params['cart'];

        $lp_carriers = Configuration::getMultiple([
            'LP_CARRIER_TERMINAL',
            'LP_CARRIER_TO_POST',
            'LP_CARRIER_HOME'
        ]);

        $lp_order = new LPOrder();
        $lp_order->loadByCartID($cart->id);

        if (!in_array($order->id_carrier, $lp_carriers))
        {
            if (Validate::isLoadedObject($lp_order))
            {
                $lp_order->delete();
            }
            return true;
        }

        if (!Validate::isLoadedObject($lp_order))
        {
            switch ($order->id_carrier)
            {
                case Configuration::get('LP_CARRIER_TO_POST'):
                    $lp_order->type = LPOrder::TYPE_POST;
                    break;
                case Configuration::get('LP_CARRIER_HOME'):
                    $lp_order->type = LPOrder::TYPE_ADDRESS;
                    break;
                default:
                    $this->logger->error('Failed load LPOrder object.', ['id_order' => $order->id, 'id_cart' => $cart->id, 'id_carrier' => $order->id_carrier]);
                    return false;
            }
        }

        $weight = 0;
        $products = $order->getProducts();
        foreach ($products as $product)
        {
            $weight += $product['weight'];
        }

        $lp_order->id_order = $order->id;
        $lp_order->weight = $weight;
        $lp_order->packets = 1;

        $cod_modules = unserialize((string) Configuration::get('LP_COD_MODULES'));
        if (is_array($cod_modules) && in_array($order->module, $cod_modules))
        {
            $lp_order->cod = true;
        }
        else
        {
            $lp_order->cod = false;
        }

        $lp_order->cod_amount = $order->total_paid;

        if (!$lp_order->save())
        {
            $this->logger->error('Error occurs while saving LPOrder object.', ['id_order' => $order->id, 'id_cart' => $cart->id, 'id_carrier' => $order->id_carrier]);
        }
        return true;
    }

    public function hookDisplayAdminOrderLeft($params)
    {
        if (!isset($params['id_order']))
        {
            return false;
        }

        $lp_order = new LPOrder();
        $lp_order->loadByOrderID($params['id_order']);
        if (!Validate::isLoadedObject($lp_order))
        {
            return false;
        }

        $order = new Order($params['id_order'], $this->context->language->id);
        if (!Validate::isLoadedObject($order))
        {
            return false;
        }

        $address = new Address($order->id_address_delivery, $this->context->language->id);
        if (!Validate::isLoadedObject($address))
        {
            return false;
        }

        if (!$lp_order->isConfirmed())
        {
            BalticPostAPI::setEnvironment(Configuration::get('LP_DEVELOPMENT_MODE'));
            BalticPostAPI::setAuthData(Configuration::get('LP_PARTNER_ID'), Configuration::get('LP_PARTNER_PASSWORD'));
            if (!BalticPostAPI::testAuth())
            {
                $post_address = $this->l('Failed retrieve post address by zip code. Please try again.');
            }
            else
            {
                $post_address = BalticPostAPI::getPostOfficeByZip($address->postcode);
                if (!$post_address)
                {
                    $post_address = $this->l('Can\'t find post office by zip code. Please select different delivery method.');
                }
                else
                {
                    $post_address = $post_address['name'].', '.$post_address['address'];
                }
            }
        }
        else
        {
            $post_address = $lp_order->post_address;
        }

        $this->context->smarty->assign([
            'lp_order' => $lp_order,
            'terminal_list' => Terminal::getTerminals(),
            'terminals' => json_encode(Terminal::getTerminalsWithAvailableBoxSizes()),
            'post_address' => $post_address,
            'LP_ORDER_HOME_TYPE' => Configuration::get('LP_ORDER_HOME_TYPE'),

            'order_carrier' => new Carrier($order->id_carrier, $this->context->language->id),
            'carrier_post' => new Carrier(Configuration::get('LP_CARRIER_TO_POST'), $this->context->language->id),
            'carrier_terminal' => new Carrier(Configuration::get('LP_CARRIER_TERMINAL'), $this->context->language->id),
            'carrier_home' => new Carrier(Configuration::get('LP_CARRIER_HOME'), $this->context->language->id),
            'boxes' => json_encode(BoxSize::getAllBoxSizes()),
        ]);

        return $this->display(__FILE__, '/views/templates/admin/order.tpl');
    }

    /* -------------------- Front office -------------------- */

    public function hookHeader($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>='))
        {
            $this->context->controller->registerJavascript('modules-lpexpress-js', 'modules/'.$this->name.'/views/js/lpexpress_carriers.js');
            $this->context->controller->registerStylesheet('modules-lpexpress-css', 'modules/'.$this->name.'/views/css/lpexpress_carriers.css');
            $this->context->controller->registerJavascript('modules-lpexpress-select2-js', 'modules/'.$this->name.'/views/js/select2.min.js');
            $this->context->controller->registerStylesheet('modules-lpexpress-select2-css', 'modules/'.$this->name.'/views/css/select2.min.css');
        }
        else
        {
            $this->context->controller->addJS($this->_path.'views/js/lpexpress_carriers.js');
            $this->context->controller->addCss($this->_path.'views/css/lpexpress_carriers.css');
            $this->context->controller->addJS($this->_path.'views/js/select2.min.js');
            $this->context->controller->addCss($this->_path.'views/css/select2.min.css');
        }
    }

    public function hookDisplayBeforeCarrier($params)
    {
        return $this->getFrontCarrier($params);
    }

    public function hookDisplayCarrierList($params)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>='))
        {
            return $this->getFrontCarrier($params);
        }
        else
        {
            return $this->hookDisplayCarrierExtraContent($params);
        }
    }

    /**
     * Get content for carriers for front order page
     * @param $params
     * @return string
     */
    private function getFrontCarrier($params)
    {
        return '<script>
                    var LPToken = "'.Tools::getToken(false).'";
                    var LPAjax = "'.$this->context->link->getModuleLink($this->name, 'ajax').'";
                    var LPCarrierTerminal = "'.Configuration::get('LP_CARRIER_TERMINAL').'";
                    var LPCarrierPost = "'.Configuration::get('LP_CARRIER_TO_POST').'";
                    var LPCarrierHome = "'.Configuration::get('LP_CARRIER_HOME').'";
                    var MessageBadZip = "'.$this->l('Invalid ZIP code. Please check your address zip code and try again.').'";
                </script>';
    }

    /**
     * Set Display content for each carrier separated
     * @param $params
     * @return string
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $id_carrier = $params['carrier']['id'];
        else
            $id_carrier = $params['cart']->id_carrier;

        switch ($id_carrier)
        {
            case Configuration::get('LP_CARRIER_TO_POST'):
                return $this->getCarrierPostContent($params);

            case Configuration::get('LP_CARRIER_TERMINAL'):
                return $this->getCarrierTerminalContent($params);

            case Configuration::get('LP_CARRIER_HOME'):
                return $this->getCarrierToHomeContent($params);

            default:
                break;
        }
    }

    private function getCarrierToHomeContent($params)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<'))
            return $this->display(__FILE__, 'views/templates/hook/orderHomeDelivery_1-6.tpl');
        else
            return '';
    }

    /**
     * Get content for Post delivery method
     */
    private function getCarrierPostContent($params)
    {
        // Remove any possible content from variable
        $this->context->smarty->assign([
            'error' => [],
            'id_address' => $params['cart']->id_address_delivery
        ]);

        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->context->smarty->assign('id_carrier', $params['carrier']['id']);
        else
            $this->context->smarty->assign('id_carrier', $params['cart']->id_carrier);


        BalticPostAPI::setEnvironment(Configuration::get('LP_DEVELOPMENT_MODE'));
        BalticPostAPI::setAuthData(Configuration::get('LP_PARTNER_ID'), Configuration::get('LP_PARTNER_PASSWORD'));
        if (!BalticPostAPI::testAuth())
        {
            $this->context->smarty->assign([
                'error' => $this->l('Failed retrieve post address by zip code. Please try again.')
            ]);
        }
        else
        {
            $address = Address::getCountryAndState($params['cart']->id_address_delivery);
            $post_office = BalticPostAPI::getPostOfficeByZip($address['postcode']);
            if (!$post_office)
            {
                $this->context->smarty->assign([
                    'error' => $this->l('Can\'t find post office by zip code. Please select different delivery method.')
                ]);
            }
            else
            {
                $this->context->smarty->assign([
                    'post' => $post_office
                ]);
            }
        }

        $post_content = $this->display(__FILE__, 'views/templates/hook/orderPostDelivery.tpl');
        if (version_compare(_PS_VERSION_, '1.7', '<'))
        {
            $this->context->smarty->assign([
                'post_content' => $post_content
            ]);
            $post_content = $this->display(__FILE__, 'views/templates/hook/orderPostDelivery_1-6.tpl');

        }
        return $post_content;
    }

    private function getCarrierTerminalContent($params)
    {
        $lp_order = new LPOrder();
        $lp_order->loadByCartID($this->context->cart->id);
        if ($lp_order->type == LPOrder::TYPE_TERMINAL && !empty($lp_order->id_lpexpress_terminal))
        {
            $this->context->smarty->assign([
                'selected_terminal' => $lp_order->id_lpexpress_terminal,
            ]);
        }

        // Remove any possible content from variable
        $this->context->smarty->assign([
            'error' => [],
            'id_address' => $params['cart']->id_address_delivery
        ]);

        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->context->smarty->assign('id_carrier', $params['carrier']['id']);
        else
            $this->context->smarty->assign('id_carrier', $params['cart']->id_carrier);

        $terminals = Terminal::getTerminals();
        if (empty($terminals))
        {
            $this->context->smarty->assign([
                'error' => $this->l('Terminals not found.'),
            ]);
        }
        else
        {
            $this->context->smarty->assign([
                'terminals' => $terminals
            ]);
        }

        $terminals_content = $this->display(__FILE__, 'views/templates/hook/orderTerminalDelivery.tpl');
        if (version_compare(_PS_VERSION_, '1.7', '<'))
        {
            $this->context->smarty->assign([
                'terminals_content' => $terminals_content
            ]);
            $terminals_content = $this->display(__FILE__, 'views/templates/hook/orderTerminalDelivery_1-6.tpl');

        }

        return $terminals_content;
    }

    public function getLatestVersion()
    {
        $file = $this->getLocalPath().'update.json';

        if (!file_exists($file) || filemtime($file) > time() + 86400)
        {
            $url = 'http://updates.prestarock.com?module=lpexpress';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $output = curl_exec($ch);
            $result_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $output_array = json_decode($output,true);
            if ($result_status != 200)
            {
                $this->warning = $this->l('Failed retrieve latest module version. Error: '). $output_array['message'];
                return false;
            }

            file_put_contents($file, $output);
        }

        $version = json_decode(file_get_contents($file), true);

        return $version['version'];
    }

    /**
     * Validate if given carrier id is one of LPExpress carriers ID and return current carrier ID or false
     * @param $id_carrier
     * @param bool $check_history
     * @return int current LPExpress carrier ID or 0
     * @throws PrestaShopException
     */
    public function isCarrierLPExpress($id_carrier, $check_history = true)
    {
        $lp_carriers = Configuration::getMultiple([
            'LP_CARRIER_TO_POST',
            'LP_CARRIER_TERMINAL',
            'LP_CARRIER_HOME'
        ]);

        // Check if carrier is latest LPExpress carrier version
        if (in_array($id_carrier, $lp_carriers))
        {
            return $id_carrier;
        }

        if (!$check_history)
        {
            return 0;
        }

        // Check if carrier is older LPExpress carrier version
        foreach ($lp_carriers as $id_lp_carrier)
        {
            $lp_carrier = new Carrier($id_lp_carrier);
            if (!Validate::isLoadedObject($lp_carrier))
            {
                continue;
            }

            $carrier = new Carrier((int) $id_carrier);
            if (!Validate::isLoadedObject($carrier))
            {
                continue;
            }

            if ($lp_carrier->id_reference == $carrier->id_reference)
            {
                return $id_lp_carrier;
            }
        }

        return 0;
    }
}