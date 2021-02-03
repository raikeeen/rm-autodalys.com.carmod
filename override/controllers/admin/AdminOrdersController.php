<?php
class AdminOrdersController extends AdminOrdersControllerCore
{
    /**
     * @var LPExpress
     */
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private $lp_module;
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    public function __construct()
    {
        parent::__construct();
        $this->lp_module = Module::getInstanceByName('lpexpress');
        if (!is_array($this->bulk_actions)) {
            $this->bulk_actions = [];
        }
        $this->bulk_actions = array_merge([
            'lpexpress_label' => ['text' => $this->lp_module->l('Generate LPExpress labels', 'AdminOrdersController'), 'icon' => 'icon-cogs'],
            'lpexpress_manifest' => ['text' => $this->lp_module->l('Generate LPExpress manifests', 'AdminOrdersController'), 'icon' => 'icon-cogs']
        ], $this->bulk_actions);
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    public function setMedia($isNewTheme = false)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>='))
        {
            parent::setMedia($isNewTheme);
        }
        else
        {
            parent::setMedia();
        }
        if (Tools::isSubmit('vieworder') && ($id_order = Tools::getValue('id_order')))
        {
            $lp_order = new LPOrder();
            $lp_order->loadByOrderID($id_order);
            if (Validate::isLoadedObject($lp_order))
            {
                $this->addJS($this->lp_module->getLocalPath().'views/js/admin/order.js');
            }
        }
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['update_terminal'] = array(
            'href' => $this->context->link->getAdminLink('AdminOrders').'&callLPCourier=1',
            'desc' => $this->lp_module->l('Call couriers'),
            'icon' => 'process-icon-envelope'
        );
        parent::initPageHeaderToolbar();
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    public function postProcess()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=') && Tools::isSubmit('id_order') && ($id_order = Tools::getValue('id_order')))
        {
            $order = new Order($id_order);
            if (!Validate::isLoadedObject($order))
            {
                $this->lp_module->l('The order cannot be found within your database.', 'AdminOrdersController');
            }
            if (Tools::isSubmit('submitShippingNumber'))
            {
                $old_id_carrier = $order->id_carrier;
                if ($this->access('edit')) {
                    $id_carrier = Tools::getValue('shipping_carrier');
                    if ($this->lp_module->isCarrierLPExpress($old_id_carrier) && $id_carrier != $old_id_carrier)
                    {
                        $lp_order = new LPOrder();
                        $lp_order->loadByOrderID($order->id);
                        if (Validate::isLoadedObject($lp_order))
                        {
                            if (!empty($lp_order->orderid) && !empty($lp_order->orderpdfid) && !empty($lp_order->identcode))
                            {
                                if (!empty($lp_order->manifestid))
                                {
                                    $this->errors[] = $this->lp_module->l('This order already has created manifest for LPExpress. You no longer can change order carrier.', 'AdminOrdersController');
                                }
                                else
                                {
                                    $this->errors[] = $this->lp_module->l('This order already has created label for LPExpress. Cancel label before changing order carrier.', 'AdminOrdersController');
                                }
                                unset($_POST['submitShippingNumber']);
                            }
                        }
                    }
                }
            }
        }
        parent::postProcess();
        if (version_compare(_PS_VERSION_, '1.7', '>=') && Tools::isSubmit('submitShippingNumber') && isset($order))
        {
            if ($this->access('edit')) {
                $id_carrier = Tools::getValue('shipping_carrier');
                $order_carrier = new OrderCarrier(Tools::getValue('id_order_carrier'));
                if (!Validate::isLoadedObject($order_carrier))
                {
                    $this->errors[] = $this->lp_module->l('The order carrier ID is invalid.', 'AdminOrdersController');
                }
                else
                {
                    if (!empty($id_carrier) && isset($old_id_carrier) && $old_id_carrier != $id_carrier)
                    {
                        $is_old_carrier_lpexpress = false;
                        if ($this->lp_module->isCarrierLPExpress($old_id_carrier))
                        {
                            $is_old_carrier_lpexpress = true;
                        }
                        $is_new_carrier_lpexpress = false;
                        if ($this->lp_module->isCarrierLPExpress($id_carrier, false))
                        {
                            $is_new_carrier_lpexpress = true;
                        }
                        if ($is_old_carrier_lpexpress && !$is_new_carrier_lpexpress)
                        {
                            $lp_order = new LPOrder();
                            $lp_order->loadByOrderID($order->id);
                            if (Validate::isLoadedObject($lp_order))
                            {
                                if (!$lp_order->delete())
                                {
                                    $this->errors[] = $this->lp_module->l('Failed remove LPOrder information from this order.', 'AdminOrdersController');
                                }
                            }
                        }
                        elseif (!$is_old_carrier_lpexpress && $is_new_carrier_lpexpress)
                        {
                            $lp_order = new LPOrder();
                            $lp_order->id_cart = $order->id_cart;
                            $lp_order->id_order = $order->id;
                            $lp_order->packets = 1;
                            $lp_order->weight = $order->getTotalWeight();
                            switch ($id_carrier)
                            {
                                case Configuration::get('LP_CARRIER_TO_POST'):
                                    $lp_order->type = LPOrder::TYPE_POST;
                                    break;
                                case Configuration::get('LP_CARRIER_TERMINAL'):
                                    $lp_order->type = LPOrder::TYPE_TERMINAL;
                                    break;
                                case Configuration::get('LP_CARRIER_HOME'):
                                    $lp_order->type = LPOrder::TYPE_ADDRESS;
                                    break;
                                default:
                                    $this->errors[] = $this->lp_module->l('Invalid carrier type for LPExpress information update.', 'AdminOrdersController');
                                    break;
                            }
                            if (!count($this->errors))
                            {
                                if (!$lp_order->save())
                                {
                                    $this->errors[] = $this->lp_module->l('Failed update LPExpress order information.', 'AdminOrdersController');
                                }
                            }
                        }
                        elseif ($is_new_carrier_lpexpress)
                        {
                            $lp_order = new LPOrder();
                            $lp_order->loadByOrderID($order->id);
                            $lp_order->id_lpexpress_terminal = 0;
                            $lp_order->id_lpexpress_box = 0;
                            switch ($id_carrier)
                            {
                                case Configuration::get('LP_CARRIER_TO_POST'):
                                    $lp_order->type = LPOrder::TYPE_POST;
                                    $lp_order->cod = 0;
                                    $lp_order->cod_amount = 0;
                                    break;
                                case Configuration::get('LP_CARRIER_TERMINAL'):
                                    $lp_order->type = LPOrder::TYPE_TERMINAL;
                                    break;
                                case Configuration::get('LP_CARRIER_HOME'):
                                    $lp_order->type = LPOrder::TYPE_ADDRESS;
                                    break;
                                default:
                                    $this->errors[] = $this->lp_module->l('Invalid carrier type for LPExpress information update.', 'AdminOrdersController');
                                    break;
                            }
                            if (!count($this->errors))
                            {
                                if (!$lp_order->save())
                                {
                                    $this->errors[] = $this->lp_module->l('Failed update LPExpress order information.', 'AdminOrdersController');
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                $this->errors[] = $this->lp_module->l('You do not have permission to edit this.', 'AdminOrdersController');
            }
        }
        if (Tools::isSubmit('submitBulklpexpress_labelorder') || Tools::isSubmit('submitBulklpexpress_manifestorder'))
        {
            $orders = Tools::getValue('orderBox');
            if (empty($orders))
            {
                $this->errors[] = $this->lp_module->l('Select orders before submitting this action.', 'AdminOrdersController');
                return true;
            }
            if (Tools::isSubmit('submitBulklpexpress_labelorder'))
            {
                $errors = [];
                foreach ($orders as $key => $id_order)
                {
                    $order = new Order($id_order);
                    $lp_order = new LPOrder();
                    $lp_order->loadByOrderID($id_order);
                    if (Validate::isLoadedObject($lp_order) && !$lp_order->isConfirmed())
                    {
                        $order_errors = $this->validateLPOrder($lp_order, $order->id_carrier);
                        if ($order_errors)
                        {
                            $errors[$id_order] = $order_errors;
                        }
                    }
                    else
                    {
                        unset($orders[$key]);
                    }
                }
                if (!count($errors) && !empty($orders))
                {
                    $errors = $this->submitLPOrders($orders);
                    if (!count($errors))
                    {
                        $id_order = reset($orders);
                        $lp_order = new LPOrder();
                        $lp_order->loadByOrderID($id_order);
                        $pdf = BalticPostAPI::getLabel($lp_order->orderpdfid);
                        ob_clean();
                        header("Content-disposition: attachment; filename=label.pdf");
                        die($pdf);
                    }
                }
                $this->errors = array_merge($this->errors, $this->formatErrorArray($errors, true));
            }
            if (Tools::isSubmit('submitBulklpexpress_manifestorder'))
            {
                $errors = [];
                foreach ($orders as $key => $id_order)
                {
                    $order = new Order($id_order);
                    $lp_order = new LPOrder();
                    $lp_order->loadByOrderID($id_order);
                    if (Validate::isLoadedObject($lp_order) && !$lp_order->isManifestCreated())
                    {
                        $order_errors = $this->validateLPOrder($lp_order, $order->id_carrier);
                        if ($order_errors)
                        {
                            $errors[$id_order] = $order_errors;
                        }
                    }
                    else
                    {
                        unset($orders[$key]);
                    }
                }
                if (!count($errors) && !empty($orders))
                {
                    $errors = $this->submitLPOrders($orders);
                    if (!count($errors))
                    {
                        $errors = $this->callCarrier($orders);
                    }
                }
                $this->errors = array_merge($this->errors, $this->formatErrorArray($errors, true));
            }
        }
        if (Tools::isSubmit('saveLPOrder'))
        {
            if ($this->saveLPOrder(Tools::getValue('id_order')))
            {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders').'&id_order='.Tools::getValue('id_order').'&vieworder=1&conf=4');
            }
        }
        if (Tools::isSubmit('generateLPOrder'))
        {
            if ($this->saveLPOrder(Tools::getValue('id_order')))
            {
                $order = new Order((int) Tools::getValue('id_order'));
                $lp_order = new LPOrder();
                $lp_order->loadByOrderID($order->id);
                $errors = $this->validateLPOrder($lp_order, $order->id_carrier);
                if (!count($errors))
                {
                    $errors = $this->submitLPOrders($order->id);
                    if (!count($errors))
                    {
                        Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders').'&id_order='.Tools::getValue('id_order').'&vieworder=1&conf=3');
                    }
                }
                $this->errors = array_merge($this->errors, $this->formatErrorArray($errors));
            }
        }
        if (Tools::isSubmit('cancelLPOrder'))
        {
            $errors = $this->cancelLPOrders(Tools::getValue('id_order'));
            if (!count($errors))
            {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders').'&id_order='.Tools::getValue('id_order').'&vieworder=1&conf=1');
            }
            else
            {
                $this->errors = array_merge($this->errors, $this->formatErrorArray($errors));
            }
        }
        if (Tools::isSubmit('getLPLabel'))
        {
            $errors = $this->getLPLabel(Tools::getValue('id_order'));
            if (count($errors))
            {
                $this->errors = array_merge($this->errors, $this->formatErrorArray($errors));
            }
        }
        if (Tools::isSubmit('callLPCourier'))
        {
            $orders = LPOrder::getCourierOrders();
            if (empty($orders))
            {
                return true;
            }
            $orders = array_column($orders, 'id_order');
            $errors = [];
            foreach ($orders as $id_order)
            {
                $order = new Order($id_order);
                $lp_order = new LPOrder();
                $lp_order->loadByOrderID($id_order);
                $order_errors = $this->validateLPOrder($lp_order, $order->id_carrier);
                if ($order_errors)
                {
                    $errors[$id_order] = $order_errors;
                }
            }
            if (!count($errors))
            {
                $errors = $this->submitLPOrders($orders);
                if (!count($errors))
                {
                    $errors = $this->callCarrier($orders);
                }
            }
            $this->errors = array_merge($this->errors, $this->formatErrorArray($errors, true));
        }
        if (Tools::isSubmit('getLPManifest'))
        {
            $errors = $this->getLPManifest(Tools::getValue('id_order'));
            if (count($errors))
            {
                $this->errors = array_merge($this->errors, $this->formatErrorArray($errors));
            }
        }
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private function saveLPOrder($id_order)
    {
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order))
        {
            $this->errors[] = $this->lp_module->l('Invalid order ID', 'AdminOrdersController');
            return false;
        }
        $lp_order = new LPOrder();
        $lp_order->loadByOrderID($id_order);
        if (!Validate::isLoadedObject($lp_order))
        {
            $this->errors[] = $this->lp_module->l('Failed get LPOrder object', 'AdminOrdersController');
            return false;
        }
        if ($lp_order->isConfirmed())
        {
            $this->errors[] = $this->lp_module->l('LPOrder already confirmed', 'AdminOrdersController');
            return false;
        }
        $lp_order->packets = (int) Tools::getValue('lp_packets');
        $lp_order->weight = (float) Tools::getValue('lp_weight');
        $lp_order->id_lpexpress_terminal = (int) Tools::getValue('lp_terminal');
        $lp_order->id_lpexpress_box = (int) Tools::getValue('lp_box_size');
        if (Tools::getValue('lp_carrier') == Configuration::get('LP_CARRIER_TO_POST'))
        {
            $lp_order->cod = 0;
        }
        else
        {
            $lp_order->cod = (int) Tools::getValue('lp_cod');
        }
        $lp_order->cod_amount = (float) Tools::getValue('lp_cod_amount');
        $lp_order->comment = Tools::getValue('lp_comment');
        $carrier = Carrier::getCarrierByReference(Tools::getValue('lp_carrier'));
        if (!Validate::isLoadedObject($carrier))
        {
            $this->errors[] = $this->lp_module->l('Selected carrier not exists', 'AdminOrdersController');
            return false;
        }
        $errors = $this->validateLPOrder($lp_order, $carrier->id);
        if (!count($errors))
        {
            $lp_carrier = new Carrier(Tools::getValue('lp_carrier'));
            $order_carrier = new Carrier($order->id_carrier);
            $lp_carriers = Configuration::getMultiple([
                'LP_CARRIER_TO_POST',
                'LP_CARRIER_TERMINAL',
                'LP_CARRIER_HOME'
            ]);
            foreach ($lp_carriers as $id_carrier)
            {
                $carrier = new Carrier($id_carrier);
                if ($carrier->id_reference == $lp_carrier->id_reference && $order_carrier->id_reference != $carrier->id_reference)
                {
                    $order->id_carrier = $carrier->id;
                    $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
                    $order_carrier->id_carrier = $carrier->id;
                    $order_carrier->update();
                    $order->update();
                    if (version_compare(_PS_VERSION_, '1.7', '>='))
                    {
                        $order->refreshShippingCost();
                    }
                    else
                    {
                        $this->refreshPS16ShippingCost($order, $order_carrier);
                    }
                    switch ($carrier->id)
                    {
                        case Configuration::get('LP_CARRIER_TO_POST'):
                            $lp_order->type = LPOrder::TYPE_POST;
                            break;
                        case Configuration::get('LP_CARRIER_TERMINAL'):
                            $lp_order->type = LPOrder::TYPE_TERMINAL;
                            break;
                        case Configuration::get('LP_CARRIER_HOME'):
                            $lp_order->type = LPOrder::TYPE_ADDRESS;
                            break;
                    }
                    break;
                }
            }
            if (!$lp_order->save())
            {
                $this->errors[] = $this->lp_module->l('Error occur while update LPOrder object', 'AdminOrdersController');
            }
            return !count($this->errors);
        }
        else
        {
            $this->errors = array_merge($this->errors, $errors);
            return false;
        }
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private function submitLPOrders($id_orders)
    {
        $errors = [];
        BalticPostAPI::setEnvironment(Configuration::get('LP_DEVELOPMENT_MODE'));
        BalticPostAPI::setAuthData(Configuration::get('LP_PARTNER_ID'), Configuration::get('LP_PARTNER_PASSWORD'));
        if (!BalticPostAPI::testAuth())
        {
            $errors['connection'] = $this->lp_module->l('Failed connect with LPExpress API', 'AdminOrdersController');
            return $errors;
        }
        if (!is_array($id_orders))
        {
            $id_orders = [$id_orders];
        }
        $general_information = [
            'kepoluserid' => Configuration::get('LP_CUSTOMER_ID'),
            'paymentpin' => Configuration::get('LP_PAYMENT_PIN'),
            'labels' => []
        ];
        $general_sender_information = [
            'sendername' => Configuration::get('LP_SENDER_NAME'),
            'sendermobile' => $this->formatPhoneNumber(Configuration::get('LP_SENDER_PHONE')),
            'senderemail' => Configuration::get('LP_SENDER_EMAIL'),
            'senderaddressfield1' => Configuration::get('LP_SENDER_ADDRESS'),
            'senderaddresscity' => Configuration::get('LP_SENDER_CITY'),
            'senderaddresszip' => Configuration::get('LP_SENDER_ZIP'),
            'senderaddresscountry' => Configuration::get('LP_SENDER_COUNTRY'),
        ];
        $label_data = [];
        foreach ($id_orders as $id_order)
        {
            try
            {
                $label = [];
                $order_errors = [];
                $order = new Order($id_order);
                if (!Validate::isLoadedObject($order))
                {
                    $order_errors['order'] = $this->lp_module->l('Failed load Order object', 'AdminOrdersController');
                    throw new LogicException('');
                }
                $lp_order = new LPOrder();
                $lp_order->loadByOrderID($order->id);
                if (!Validate::isLoadedObject($order))
                {
                    $order_errors['lp_order'] = $this->lp_module->l('Failed load LPOrder object', 'AdminOrdersController');
                    throw new LogicException('');
                }
                if ($lp_order->isConfirmed())
                {
                    continue;
                }
                $address = new Address($order->id_address_delivery);
                if (!Validate::isLoadedObject($address))
                {
                    $order_errors['address'] = $this->lp_module->l('Failed load Address object', 'AdminOrdersController');
                    throw new LogicException('');
                }
                $customer = new Customer($order->id_customer);
                if (!Validate::isLoadedObject($customer))
                {
                    $order_errors['customer'] = $this->lp_module->l('Failed load Customer object', 'AdminOrdersController');
                    throw new LogicException('');
                }
                if ($lp_order->cod)
                {
                    $currency = new Currency($order->id_currency);
                    if (!Validate::isLoadedObject($currency))
                    {
                        $order_errors['currency'] = $this->lp_module->l('Failed load Currency object', 'AdminOrdersController');
                        throw new LogicException('');
                    }
                    $label['parcelvalue'] = $lp_order->cod_amount;
                    $label['parcelvaluecurrency'] = $currency->iso_code;
                }
                switch ($lp_order->type)
                {
                    case LPOrder::TYPE_POST:
                        $label['productcode'] = 'AB';
                        $post_address = BalticPostAPI::getPostOfficeByZip($address->postcode);
                        if (!$post_address)
                        {
                            $order_errors['postcode'] = BalticPostAPI::getLastError()->getMessage();
                            throw new LogicException('Invalid Post');
                        }
                        break;
                    case LPOrder::TYPE_ADDRESS:
                        $label['productcode'] = Configuration::get('LP_ORDER_HOME_TYPE');
                        if (Configuration::get('LP_ORDER_HOME_TYPE') == 'CH')
                        {
                            $label['boxsize'] = BoxSize::getBoxSize($lp_order->id_lpexpress_box);
                        }
                        break;
                    case LPOrder::TYPE_TERMINAL:
                        $label['productcode'] = Configuration::get('LP_ORDER_TERMINAL_TYPE');
                        $label['boxsize'] = BoxSize::getBoxSize($lp_order->id_lpexpress_box);
                        $label['targetmachineidentification'] = Terminal::getTerminalMachineID($lp_order->id_lpexpress_terminal);
                        break;
                    default:
                        $order_errors['address_type'] = $this->lp_module->l('Unknown address type', 'AdminOrdersController');
                        throw new LogicException('Invalid Post address');
                }
                $id_products = [];
                foreach ($order->getProducts() as $product)
                {
                    $id_products[] = $product['id_product'];
                }
                $partnerorderartid = $order->id.'-'.$lp_order->label_number.'-'.join('_', $id_products);
                if (strlen($partnerorderartid) > 32)
                {
                    $partnerorderartid = substr($partnerorderartid, 0, 29);
                    $partnerorderartid .= '...';
                }
                $label = array_merge($label, [
                    'parts' => $lp_order->packets,
                    'partnerorderartid' => $partnerorderartid,
                    'parcelweight' => $lp_order->weight,
                    'deliverycomment' => $lp_order->comment,
                    'receivername' => (!empty($address->company) ? $address->company : $address->firstname.' '.$address->lastname),
                    'receivermobile' => $this->formatPhoneNumber((!empty($address->phone_mobile) ? $address->phone_mobile : $address->phone)),
                    'receiveremail' => $customer->email,
                    'receiveraddressfield1' => $address->address1.(!empty($address->address2) ? ', '.$address->address2 : ''),
                    'receiveraddresscity' => $address->city,
                    'receiveraddresszip' => $address->postcode,
                    'receiveraddresscountry' => Country::getIsoById($address->id_country),
                ]);
                $label_data[] = [
                    'order' => $order,
                    'lp_order' => $lp_order,
                    'post_address' => (isset($post_address) ? $post_address : '')
                ];
                $labels[] = $label;
            }
            catch (LogicException $e)
            {
                continue;
            }
            finally
            {
                if (!empty($order_errors))
                {
                    $errors[$id_order] = $order_errors;
                }
            }
        }
        if (count($errors) || empty($label_data))
        {
            return $errors;
        }
        try
        {
            $api_errors = [];
            $partnerorderid = [];
            foreach ($label_data as $data)
            {
                $partnerorderid[] = $data['order']->id.'-'.$data['lp_order']->label_number;
            }
            $partnerorderid = implode('_', $partnerorderid);
            if (strlen($partnerorderid) > 32)
            {
                $partnerorderid = substr($partnerorderid, 0, 29);
                $partnerorderid .= '...';
            }
            $information = array_merge(['partnerorderid' => $partnerorderid], $general_information);
            foreach ($labels as &$label)
            {
                $label = array_merge($general_sender_information, $label);
            }
            $information['labels'] = $labels;
            $add = BalticPostAPI::addLabel($information);
            if (!$add)
            {
                $error = BalticPostAPI::getLastError()->getMessage();
                $api_errors['add_label'] = $this->lp_module->l('Failed add label. Error message: ', 'AdminOrdersController').$error;
                throw new LogicException('');
            }
            foreach ($label_data as $data)
            {
                $data['lp_order']->orderid = $add['orderid'];
                $data['lp_order']->save();
            }
            $conf = BalticPostAPI::confirmLabel([
                'orderid' => $add['orderid'],
            ]);
            if (!$conf)
            {
                $error = BalticPostAPI::getLastError()->getMessage();
                $api_errors['conf_label'] = $this->lp_module->l('Failed confirm label. Error message: ', 'AdminOrdersController').$error;
                throw new LogicException('');
            }
            for ($i = 0; $i < count($conf['labels']); $i++)
            {
                $order = $label_data[$i]['order'];
                $lp_order = $label_data[$i]['lp_order'];
                $label = $conf['labels'][$i];
                $lp_order->identcode = $label['identcode'];
                $lp_order->orderpdfid = $conf['orderpdfid'];
                if ($lp_order->type == LPOrder::TYPE_POST)
                {
                    $post_address = $label_data[$i]['post_address'];
                    if (is_array($post_address))
                    {
                        $lp_order->post_address = $post_address['name'].', '.$post_address['address'];
                    }
                }
                $lp_order->save();
                $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
                $order_carrier->tracking_number = $lp_order->identcode;
                $order->shipping_number = $lp_order->identcode;
                $order->save();
                $order_carrier->save();
                if (version_compare(_PS_VERSION_, '1.7', '>='))
                {
                    $order_carrier->sendInTransitEmail($order);
                }
            }
        }
        catch (LogicException $e)
        {}
        finally
        {
            if (!empty($api_errors))
            {
                $errors['api_errors'] = $api_errors;
            }
        }
        return $errors;
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private function cancelLPOrders($id_orders)
    {
        $errors = [];
        BalticPostAPI::setEnvironment(Configuration::get('LP_DEVELOPMENT_MODE'));
        BalticPostAPI::setAuthData(Configuration::get('LP_PARTNER_ID'), Configuration::get('LP_PARTNER_PASSWORD'));
        if (!BalticPostAPI::testAuth())
        {
            $errors['connection'] = $this->lp_module->l('Failed connect with LPExpress API', 'AdminOrdersController');
            return $errors;
        }
        if (!is_array($id_orders))
        {
            $id_orders = [$id_orders];
        }
        $labels = [];
        $submitted_orders = [];
        foreach ($id_orders as $id_order)
        {
            try
            {
                $order_errors = [];
                $lp_order = new LPOrder();
                $lp_order->loadByOrderID($id_order);
                if (!Validate::isLoadedObject($lp_order))
                {
                    $order_errors['lp_order'] = $this->lp_module->l('Failed load LPOrder object', 'AdminOrdersController');
                    throw new LogicException('');
                }
                if (!$lp_order->isConfirmed())
                {
                    $order_errors['lp_order'] = $this->lp_module->l('Order not confirmed', 'AdminOrdersController');
                    throw new LogicException('');
                }
                if ($lp_order->isManifestCreated())
                {
                    $order_errors['lp_order'] = $this->lp_module->l('Manifest already created', 'AdminOrdersController');
                    throw new LogicException('');
                }
                $labels[] = ['identcode' => $lp_order->identcode];
                $submitted_orders[] = $id_order;
            }
            catch (LogicException $e)
            {
                continue;
            }
            finally
            {
                if (!empty($order_errors))
                {
                    $errors[$id_order] = $order_errors;
                }
            }
        }
        $result = BalticPostAPI::cancelLabel([
            'labels' => $labels
        ]);
        if (!$result)
        {
            $error = BalticPostAPI::getLastError()->getMessage();
            $order_errors['cancel'] = $this->lp_module->l('Failed cancel labels. Error message: ', 'AdminOrdersController').$error;
        }
        else
        {
            foreach ($submitted_orders as $id_order) {
                try
                {
                    $order_errors = [];
                    $order = new Order($id_order);
                    if (!Validate::isLoadedObject($order))
                    {
                        $order_errors['order'] = $this->lp_module->l('Failed load Order object', 'AdminOrdersController');
                        throw new LogicException('');
                    }
                    $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
                    if (!Validate::isLoadedObject($order_carrier))
                    {
                        $order_errors['order_carrier'] = $this->lp_module->l('Failed load OrderCarrier object', 'AdminOrdersController');
                        throw new LogicException('');
                    }
                    $lp_order = new LPOrder();
                    $lp_order->loadByOrderID($id_order);
                    if (!Validate::isLoadedObject($lp_order))
                    {
                        $order_errors['lp_order'] = $this->lp_module->l('Failed load LPOrder object', 'AdminOrdersController');
                        throw new LogicException('');
                    }
                    $order->shipping_number = '';
                    $order_carrier->tracking_number = '';
                    $lp_order->identcode = '';
                    $lp_order->orderid = '';
                    $lp_order->orderpdfid = '';
                    $lp_order->label_number += 1;
                    if (!$order->save() || !$order_carrier->save() || !$lp_order->save())
                    {
                        $order_errors['object_save'] = $this->lp_module->l('Failed save objects after cancel labels', 'AdminOrdersController');
                        throw new LogicException('');
                    }
                    if (version_compare(_PS_VERSION_, '1.7', '>='))
                    {
                        $order_carrier->sendInTransitEmail($order);
                    }
                }
                catch (LogicException $e)
                {
                    continue;
                }
                finally
                {
                    if (!empty($order_errors))
                    {
                        $errors[$id_order] = $order_errors;
                    }
                }
            }
        }
        return $errors;
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private function getLPLabel($id_orders)
    {
        $errors = [];
        BalticPostAPI::setEnvironment(Configuration::get('LP_DEVELOPMENT_MODE'));
        BalticPostAPI::setAuthData(Configuration::get('LP_PARTNER_ID'), Configuration::get('LP_PARTNER_PASSWORD'));
        if (!BalticPostAPI::testAuth())
        {
            $errors['connection'] = $this->lp_module->l('Failed connect with LPExpress API', 'AdminOrdersController');
            return $errors;
        }
        if (!is_array($id_orders))
        {
            $id_orders = [$id_orders];
        }
        $labels = [];
        foreach ($id_orders as $id_order)
        {
            try
            {
                $order_errors = [];
                $lp_order = new LPOrder();
                $lp_order->loadByOrderID($id_order);
                if (!Validate::isLoadedObject($lp_order))
                {
                    $order_errors['lp_order'] = $this->lp_module->l('Failed load LPOrder object', 'AdminOrdersController');
                    throw new LogicException('');
                }
                if (empty($lp_order->orderpdfid))
                {
                    $order_errors['lp_pdf_id'] = $this->lp_module->l('Order don\'t have PDF ID', 'AdminOrdersController');
                    throw new LogicException('');
                }
                $pdf = BalticPostAPI::getLabel($lp_order->orderpdfid);
                if (!$pdf)
                {
                    $order_errors['lp_pdf_content'] = $this->lp_module->l('PDF not found by given pdf ID', 'AdminOrdersController');
                    throw new LogicException('');
                }
                $labels[] = $pdf;
            }
            catch (LogicException $e)
            {
                continue;
            }
            finally
            {
                if (!empty($order_errors))
                {
                    $errors[$id_order] = $order_errors;
                }
            }
        }
        if (!$this->errors && !empty($labels))
        {
            header('Content-type:application/pdf');
            $pdf = implode('', $labels);
            die($pdf);
        }
        return $errors;
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private function getLPManifest($id_orders)
    {
        $errors = [];
        BalticPostAPI::setEnvironment(Configuration::get('LP_DEVELOPMENT_MODE'));
        BalticPostAPI::setAuthData(Configuration::get('LP_PARTNER_ID'), Configuration::get('LP_PARTNER_PASSWORD'));
        if (!BalticPostAPI::testAuth())
        {
            $errors['connection'] = $this->lp_module->l('Failed connect with LPExpress API', 'AdminOrdersController');
            return $errors;
        }
        if (!is_array($id_orders))
        {
            $id_orders = [$id_orders];
        }
        $manifests = [];
        foreach ($id_orders as $id_order)
        {
            try
            {
                $order_errors = [];
                $lp_order = new LPOrder();
                $lp_order->loadByOrderID($id_order);
                if (!Validate::isLoadedObject($lp_order))
                {
                    $order_errors['lp_order'] = $this->lp_module->l('Failed load LPOrder object', 'AdminOrdersController');
                    throw new LogicException('');
                }
                if (empty($lp_order->orderpdfid))
                {
                    $order_errors['lp_pdf_id'] = $this->lp_module->l('Order don\'t have PDF ID', 'AdminOrdersController');
                    throw new LogicException('');
                }
                $pdf = BalticPostAPI::getManifest($lp_order->manifestid);
                if (!$pdf)
                {
                    $order_errors['lp_manifest_content'] = $this->lp_module->l('PDF not found by given manifest PDF ID', 'AdminOrdersController');
                    throw new LogicException('');
                }
                $manifests[] = $pdf;
            }
            catch (LogicException $e)
            {
                continue;
            }
            finally
            {
                if (!empty($order_errors))
                {
                    $errors[$id_order] = $order_errors;
                }
            }
        }
        if (!$this->errors && !empty($manifests))
        {
            header('Content-type:application/pdf');
            $manifests = implode('', $manifests);
            die($manifests);
        }
        return $errors;
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private function callCarrier($id_orders)
    {
        $errors = [];
        BalticPostAPI::setEnvironment(Configuration::get('LP_DEVELOPMENT_MODE'));
        BalticPostAPI::setAuthData(Configuration::get('LP_PARTNER_ID'), Configuration::get('LP_PARTNER_PASSWORD'));
        if (!BalticPostAPI::testAuth())
        {
            $errors['connection'] = $this->lp_module->l('Failed connect with LPExpress API', 'AdminOrdersController');
            return $errors;
        }
        if (!is_array($id_orders))
        {
            $id_orders = [$id_orders];
        }
        $parcels = [];
        foreach ($id_orders as $id_order)
        {
            try
            {
                $order_errors = [];
                $lp_order = new LPOrder();
                $lp_order->loadByOrderID($id_order);
                if (!Validate::isLoadedObject($lp_order))
                {
                    $order_errors['lp_order'] = $this->lp_module->l('Failed load LPOrder object', 'AdminOrdersController');
                    throw new LogicException('');
                }
                if ($lp_order->isManifestCreated())
                {
                    throw new LogicException('Manifest already created');
                }
                if (!$lp_order->isConfirmed())
                {
                    $order_errors['lp_order'] = $this->lp_module->l('Order not confirmed', 'AdminOrdersController');
                    throw new LogicException('');
                }
                $parcels[] = $lp_order->identcode;
            }
            catch (LogicException $e)
            {
                continue;
            }
            finally
            {
                if (!empty($order_errors))
                {
                    $errors[$id_order] = $order_errors;
                }
            }
        }
        if (!count($errors) && !empty($parcels))
        {
            $result = BalticPostAPI::callCourier([
                'kepoluserid' => Configuration::get('LP_CUSTOMER_ID'),
                'adminpin' => Configuration::get('LP_ADMIN_PIN'),
                'parcels' => $parcels
            ]);
            if (!$result)
            {
                $error = BalticPostAPI::getLastError()->getMessage();
                $errors['cancel'] = $this->lp_module->l('Failed create manifest. Error message: ', 'AdminOrdersController').$error;
            }
            else
            {
                $manifesids = [];
                if (isset($result['calls']))
                {
                    foreach ($result['calls'] as $call)
                    {
                        $manifestid = $call['manifestid'];
                        $manifesids[] = $manifestid;
                        foreach ($call['parcels'] as $parcel)
                        {
                            $lp_order = new LPOrder();
                            $lp_order->loadByIdentCode($parcel['identcode']);
                            if (Validate::isLoadedObject($lp_order))
                            {
                                $lp_order->manifestid = $manifestid;
                                $lp_order->save();
                            }
                        }
                    }
                }
                if (count($manifesids) == 1)
                {
                    $pdf = BalticPostAPI::getManifest($manifestid);
                    ob_clean();
                    header("Content-disposition: attachment; filename=label.pdf");
                    die($pdf);
                }
                else
                {
                    $html = '';
                    foreach ($manifesids as $manifesid)
                    {
                        $html .= '<a class="manifest_url" href="'.BalticPostAPI::getManifestURL($manifesid).'"></a>';
                    }
                    $html .= '<a id="url_back" href="'.$this->context->link->getAdminLink('AdminOrders').'"></a>';
                    $html .= '<script>
                                var elements = document.getElementsByClassName("manifest_url");
                                var back_url = document.getElementById("url_back");
                                
                                for (var i = 0; i < elements.length; i++)
                                {
                                    window.open(elements[i].href);
                                }
                                
                                back_url.click();
                              </script>';
                    die($html);
                }
            }
        }
        return $errors;
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private function validateLPOrder(LPOrder $lp_order, $id_carrier)
    {
        $errors = [];
        $packets = $lp_order->packets;
        if (empty($packets))
        {
            $errors['lp_packets'] = $this->lp_module->l('Packets number is required.', 'AdminOrdersController');
        }
        elseif (!Validate::isInt($packets))
        {
            $errors['lp_packets'] = $this->lp_module->l('Invalid packets format.', 'AdminOrdersController');
        }
        elseif ($packets < 1)
        {
            $errors['lp_packets'] = $this->lp_module->l('Packets must be greater or equal to 1.');
        }
        $carrier = $id_carrier;
        if (empty($carrier) || !Validate::isUnsignedId($carrier))
        {
            $errors['lp_carrier'] = $this->lp_module->l('Invalid carrier ID.', 'AdminOrdersController');
        }
        else
        {
            $carrier = $this->lp_module->isCarrierLPExpress($carrier);
            if (!$carrier)
            {
                $errors['lp_carrier'] = $this->lp_module->l('Selected carrier is not LPExpress carrier.', 'AdminOrdersController');
            }
        }
        if ($carrier !== 0 && $carrier == Configuration::get('LP_CARRIER_TERMINAL'))
        {
            $terminal = $lp_order->id_lpexpress_terminal;
            if (empty($terminal) || !Validate::isUnsignedId($terminal))
            {
                $errors['lp_terminal'] = $this->lp_module->l('Terminal is required.', 'AdminOrdersController');
            }
            else
            {
                $terminal_obj = new Terminal($terminal);
                if (!Validate::isLoadedObject($terminal_obj))
                {
                    $errors['lp_terminal'] = $this->lp_module->l('Selected terminal not exists.', 'AdminOrdersController');
                }
                elseif (!$terminal_obj->active)
                {
                    $errors['lp_terminal'] = $this->lp_module->l('Terminal is disabled.', 'AdminOrdersController');
                }
            }
            $box_size = $lp_order->id_lpexpress_box;
            if (empty($box_size) || !Validate::isUnsignedId($box_size))
            {
                $errors['lp_box_size'] = $this->lp_module->l('Box size is required.', 'AdminOrdersController');
            }
            elseif(isset($terminal_obj) && Validate::isLoadedObject($terminal_obj))
            {
                $box_size_obj = new BoxSize($box_size);
                if (!Validate::isLoadedObject($box_size_obj))
                {
                    $errors['lp_box_size'] = $this->lp_module->l('Selected box size not exists.', 'AdminOrdersController');
                }
                elseif (!$box_size_obj->isAssociatedWithTerminal($terminal_obj->id))
                {
                    $errors['lp_box_size'] = $this->lp_module->l('Selected box size not associated with selected terminal.', 'AdminOrdersController');
                }
            }
        }
        if ($carrier !== 0 && $carrier == Configuration::get('LP_CARRIER_HOME') && Configuration::get('LP_ORDER_HOME_TYPE') == 'CH')
        {
            $box_size = $lp_order->id_lpexpress_box;
            if (empty($box_size) || !Validate::isUnsignedId($box_size))
            {
                $errors['lp_box_size'] = $this->lp_module->l('Box size is required.', 'AdminOrdersController');
            }
            $size = new BoxSize($box_size);
            if (!Validate::isLoadedObject($size))
            {
                $errors['lp_box_size'] = $this->lp_module->l('Selected box size not exists.', 'AdminOrdersController');
            }
        }
        if ($carrier !== 0 && $carrier == Configuration::get('LP_CARRIER_TO_POST') || ($carrier == Configuration::get('LP_CARRIER_HOME') && Configuration::get('LP_ORDER_HOME_TYPE') == 'EB'))
        {
            $weight = $lp_order->weight;
            if (empty($weight))
            {
                $errors['lp_weight'] = $this->lp_module->l('Weight is required.', 'AdminOrdersController');
            }
            elseif (!Validate::isFloat($weight))
            {
                $errors['lp_weight'] = $this->lp_module->l('Invalid weight format.', 'AdminOrdersController');
            }
            elseif ($weight <= 0)
            {
                $errors['lp_packets'] = $this->lp_module->l('Weight must be greater to 0.', 'AdminOrdersController');
            }
        }
        $cod = $lp_order->cod;
        if ($cod == 1)
        {
            $cod_amount = $lp_order->cod_amount;
            if (empty($cod_amount))
            {
                $errors['lp_cod_amount'] = $this->lp_module->l('COD amount is required.', 'AdminOrdersController');
            }
            elseif (!Validate::isFloat($cod_amount))
            {
                $errors['lp_cod_amount'] = $this->lp_module->l('Invalid COD amount format.', 'AdminOrdersController');
            }
        }
        $comment = $lp_order->comment;
        if (!empty($comment))
        {
            if (Tools::strlen($comment) > 200)
            {
                $errors['lp_comment'] = $this->lp_module->l('Maximum comment length 200 symbols.', 'AdminOrdersController');
            }
        }
        return $errors;
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private function formatPhoneNumber($phone)
    {
        $phone = str_replace('+', '', $phone);
        $phone = '00'.$phone;
        return $phone;
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    private function formatErrorArray($errors, $display_id_order = false)
    {
        if (!is_array($errors))
        {
            return $errors;
        }
        ksort($errors);
        $new_errors = [];
        foreach ($errors as $id_order => $error)
        {
            if (is_array($error))
            {
                foreach ($error as $order_error)
                {
                    if ($display_id_order)
                    {
                        $new_errors[] = $this->lp_module->l('Order: ', 'AdminOrdersController'). $id_order . $this->lp_module->l(', error: ', 'AdminOrdersController') . $order_error;
                    }
                    else
                    {
                        $new_errors[] = $order_error;
                    }
                }
            }
            else
            {
                $new_errors[] = $error;
            }
        }
        return $new_errors;
    }
    /*
    * module: lpexpress
    * date: 2020-10-07 14:45:14
    * version: 1.0.9
    */
    public function refreshPS16ShippingCost(Order $order, OrderCarrier $order_carrier)
    {
        $fake_cart = new Cart((int) $order->id_cart);
        $new_cart = $fake_cart->duplicate();
        $new_cart = $new_cart['cart'];
        $new_cart->id_address_delivery = (int) $order->id_address_delivery;
        $new_cart->id_carrier = (int) $order->id_carrier;
        foreach ($new_cart->getProducts() as $product) {
            $new_cart->deleteProduct((int) $product['id_product'], (int) $product['id_product_attribute']);
        }
        foreach ($order->getProducts() as $product) {
            $new_cart->updateQty($product['product_quantity'], (int) $product['product_id']);
        }
        $base_total_shipping_tax_incl = (float) $new_cart->getPackageShippingCost((int) $new_cart->id_carrier, true, null);
        $base_total_shipping_tax_excl = (float) $new_cart->getPackageShippingCost((int) $new_cart->id_carrier, false, null);
        $diff_shipping_tax_incl = $order->total_shipping_tax_incl - $base_total_shipping_tax_incl;
        $diff_shipping_tax_excl = $order->total_shipping_tax_excl - $base_total_shipping_tax_excl;
        $order->total_shipping_tax_excl = $order->total_shipping_tax_excl - $diff_shipping_tax_excl;
        $order->total_shipping_tax_incl = $order->total_shipping_tax_incl - $diff_shipping_tax_incl;
        $order->total_shipping = $order->total_shipping_tax_incl;
        $order->total_paid_tax_excl = $order->total_paid_tax_excl - $diff_shipping_tax_excl;
        $order->total_paid_tax_incl = $order->total_paid_tax_incl - $diff_shipping_tax_incl;
        $order->total_paid = $order->total_paid_tax_incl;
        $order->update();
        $order_carrier->shipping_cost_tax_excl = $order->total_shipping_tax_excl;
        $order_carrier->shipping_cost_tax_incl = $order->total_shipping_tax_incl;
        $order_carrier->update();
        $new_cart->delete();
    }
}