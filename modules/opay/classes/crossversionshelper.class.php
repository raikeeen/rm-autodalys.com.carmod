<?php

class CrossVersionsHelper
{

    public static function callback($module)
    {
        $opay = $module->getOpayGateway();
        $requestParams = $module->getEncodedValues();

        if (!empty($requestParams))
        {
            if ($opay->verifySignature($requestParams))
            {
                $db = \Db::getInstance();
                $prefix = _DB_PREFIX_;
                $dbName = _DB_NAME_;
                $request = "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_NAME = '{$prefix}orders' AND TABLE_SCHEMA = '{$dbName}'";
                $engine = $db->getRow($request)['ENGINE'];
                $orderid = $requestParams['order_nr'];
                $order   = new Order($orderid);
                $orderIdEscaped = $db->escape($orderid, false);
                if ($engine == 'InnoDB') {
                    // Locking order until order state is changed to avoid possible double payment captures when user redirect and callback are happening at the exact same time
                    $db->execute('START TRANSACTION');
                    $request = "SELECT id_order, current_state FROM {$prefix}orders WHERE id_order = '{$orderIdEscaped}' LIMIT 0,1 FOR UPDATE";
                    $result = $db->executeS($request);
                    if (
                        isset($result[0]['current_state']) &&
                        $result[0]['current_state'] != self::getPaymentState('OPAY_PENDING') && 
                        $result[0]['current_state'] != self::getPaymentState('PS_OS_OUTOFSTOCK_UNPAID')
                    ) {
                        $db->execute('ROLLBACK');
                        exit('OK');
                    }
                }

                // Marking as payed only if current state is OPAY_PENDING || PS_OS_OUTOFSTOCK_UNPAID
                if (
                    (int)$requestParams['status'] == 1 && (
                        (int)$order->getCurrentState() == self::getPaymentState('OPAY_PENDING') || 
                        (int)$order->getCurrentState() == self::getPaymentState('PS_OS_OUTOFSTOCK_UNPAID') 
                    )
                ) {
                    $order_amount  = version_compare(_PS_VERSION_, '1.6', '<') ? $order->total_paid : $order->getOrdersTotalPaid();
                    $cart_currency = Currency::getCurrency($order->id_currency);

                    if (strtoupper($requestParams['p_currency']) != strtoupper($cart_currency['iso_code']))
                    {
                        if ($engine == 'InnoDB') {
                            $db->execute('ROLLBACK');
                        }
                        exit('Bad currency: '.$requestParams['currency']);
                    }

                    if ((int)$requestParams['p_amount'] < (int)number_format(($order_amount * 100), 0, '', ''))
                    {
                        if ($engine == 'InnoDB') {
                            $db->execute('ROLLBACK');
                        }
                        exit('Bad amount: '.$requestParams['amount']);
                    }

                    $history = new OrderHistory();
                    $history->id_order = $orderid;
                    if ((int)$order->getCurrentState() == self::getPaymentState('OPAY_PENDING')) {
                        $history->changeIdOrderState(self::getPaymentState('PS_OS_PAYMENT'), $orderid);
                    } else {
                        $history->changeIdOrderState(self::getPaymentState('PS_OS_OUTOFSTOCK_PAID'), $orderid);
                    }
                    $history->addWithemail(true, array(
                        'order_name' => $orderid,
                    ));

                    // Create object again to create an invoice
                    $order   = new Order($orderid);
                    $order->payment = 'OPAY ('.$requestParams['p_channel'].'_'.$requestParams['p_bank'].')';
                    $order->save();
                }
                // marking as canceled only if current status is OPAY_PENDING OR PS_OS_OUTOFSTOCK_UNPAID
                elseif (
                    (int)$requestParams['status'] == 0 && (
                        (int)$order->getCurrentState() == self::getPaymentState('OPAY_PENDING') || 
                        (int)$order->getCurrentState() == self::getPaymentState('PS_OS_OUTOFSTOCK_UNPAID')
                    ) 
                ) {
                    $history = new OrderHistory();
                    $history->id_order = $orderid;
                    $history->changeIdOrderState(self::getPaymentState('PS_OS_CANCELED'), $orderid);
                    $history->addWithemail(true, array(
                        'order_name' => $orderid,
                    ));
                }
                if ($engine == 'InnoDB') {
                    $db->execute('COMMIT');
                }
                echo "OK";

            }
            else
            {
                echo 'invalid signature';
            }
        }

        exit();
    }


    public static function payment($module, $context, $smarty = null)
    {
        $currency      = $context->currency;
        $language_code = Tools::strtoupper(Language::getIsoById((int)$context->language->id));
        $cart          = $context->cart;
        $address       = new Address((int)$cart->id_address_invoice);
        $country       = new Country((int)$address->id_country);
        $customer      = new Customer((int)$cart->id_customer);
        $total         = $cart->getOrderTotal();

        try {
            $module->validateOrder(
                $cart->id,
                Configuration::get('OPAY_PENDING'),
                $total,
                $module->displayName,
                null,
                array(),
                $currency->id,
                false,
                $cart->secure_key
            );
        } catch (Exception $e) {
            // TODO redirect client somewhere else
        }

        $opay = $module->getOpayGateway();

        $redirectUrl = self::getModuleLink(
            $module->name,
            'validation',
            array(),
            true
        );
        $webServiceUrl = self::getModuleLink(
            $module->name,
            'callback',
            array(),
            true
        );

        $paramsArray = array(
            'website_id'            => $module->website_id,
            'order_nr'              => $module->currentOrder,
            'redirect_url'          => $redirectUrl,
            'web_service_url'       => $webServiceUrl,
            'standard'              => 'opay_8.1',
            'country'               => $country->iso_code,
            'language'              => $language_code,
            'amount'                => (int)number_format($total, 2, '', ''),
            'currency'              => $currency->iso_code,
            'c_email'               => $customer->email
        );

        $address->phone_mobile = trim($address->phone_mobile);
        $address->phone = trim($address->phone);
        if ($address->phone_mobile != '')
        {
            $paramsArray['c_mobile_nr'] = $address->phone_mobile;
        }
        else if ($address->phone != '')
        {
            $paramsArray['c_mobile_nr'] = $address->phone;
        }

        if ($module->testmode)
        {
            $paramsArray['test'] = $module->opay_user_id;
        }

        $channel = Tools::getValue('opay_channel');
        $channel = $channel ? $channel : Tools::getValue('type');

        if ($channel) {
            $paramsArray['pass_through_channel_name'] = $channel;
            $order = new Order($module->currentOrder);
            $order->payment = 'OPAY ('.$channel.')';
            try {
                $order->save();
            } catch (Exception $e) {
                // TODO log error
            }
        }

        $paramsArray = $opay->signArrayOfParameters($paramsArray);
        echo $opay->generatetAutoSubmitForm('https://gateway.opay.lt/pay/', $paramsArray);
        exit;
    }


    public static function validation($module, $context = null)
    {
        $opay = $module->getOpayGateway();
        $requestParams = $module->getEncodedValues($module);

        if (!empty($requestParams))
        {
            if ($opay->verifySignature($requestParams))
            {
                $ignore = false;
                $db = \Db::getInstance();
                $prefix = _DB_PREFIX_;
                $dbName = _DB_NAME_;
                $request = "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_NAME = '{$prefix}orders' AND TABLE_SCHEMA = '{$dbName}'";
                $engine = $db->getRow($request)['ENGINE'];
                $orderid = $requestParams['order_nr'];
                $order   = new Order($orderid);
                $orderIdEscaped = $db->escape($orderid, false);

                if ($engine == 'InnoDB') {
                    // Locking order until order state is changed to avoid possible double payment captures when user redirect and callback are happening at the exact same time
                    $db->execute('START TRANSACTION');
                    $request = "SELECT id_order, current_state FROM {$prefix}orders WHERE id_order = '{$orderIdEscaped}' LIMIT 0,1 FOR UPDATE";
                    $result = $db->executeS($request);
                    if (
                        isset($result[0]['current_state']) &&
                        $result[0]['current_state'] != self::getPaymentState('OPAY_PENDING') && 
                        $result[0]['current_state'] != self::getPaymentState('PS_OS_OUTOFSTOCK_UNPAID')
                    ) {
                        $ignore = true;
                    }
                }

                // Marking as payed only if current state is OPAY_PENDING OR PS_OS_OUTOFSTOCK_PAID
                if (
                    !$ignore && 
                    (int)$requestParams['status'] == 1 && (
                        (int)$order->getCurrentState() == self::getPaymentState('OPAY_PENDING') || 
                        (int)$order->getCurrentState() == self::getPaymentState('PS_OS_OUTOFSTOCK_UNPAID') 
                    ) 
                ) {
                    $order_amount  = version_compare(_PS_VERSION_, '1.6', '<') ? $order->total_paid : $order->getOrdersTotalPaid();
                    $cart_currency = Currency::getCurrency($order->id_currency);


                    if ((int)$requestParams['p_amount'] >= (int)number_format(($order_amount * 100), 0, '', '') && strtoupper($requestParams['p_currency']) == strtoupper($cart_currency['iso_code']))
                    {
                        $history = new OrderHistory();
                        $history->id_order = $orderid;
                        if ((int)$order->getCurrentState() == self::getPaymentState('OPAY_PENDING')) {
                            $history->changeIdOrderState(self::getPaymentState('PS_OS_PAYMENT'), $orderid);
                        } else {
                            $history->changeIdOrderState(self::getPaymentState('PS_OS_OUTOFSTOCK_PAID'), $orderid);
                        }
                        $history->addWithemail(true, array(
                            'order_name' => $orderid,
                        ));

                        // Create object again to create an invoice
                        $order   = new Order($orderid);
                        $order->payment = 'OPAY ('.$requestParams['p_channel'].'_'.$requestParams['p_bank'].')';
                        $order->save();
                    }
                }
                if ($engine == 'InnoDB') {
                    $db->execute('COMMIT');
                }
            }
        }

        // Redirect client to order confirmation page
        if (version_compare(_PS_VERSION_, '1.5.0.15', '>=')) {
            $customer = $order->getCustomer();
        } else {
            $customer = new Customer((int)$order->id_customer);
        }
        $queryString = http_build_query(array(
            'status'    => $requestParams['status'],
            'id_cart'   => $order->id_cart,
            'id_module' => $module->id,
            'id_order'  => $orderid,
            'key'       => $customer->secure_key,
        ));
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $confirmUri = 'index.php?controller=order-confirmation&';
        } else {
            $confirmUri = 'order-confirmation.php?';
        }
        Tools::redirect($confirmUri.$queryString);
    }

    public static function getShopDomainSsl($http = false, $entities = false)
    {
        if (method_exists('Tools', 'getShopDomainSsl'))
        {
            return Tools::getShopDomainSsl($http, $entities);
        }
        else
        {
            if (!($domain = Configuration::get('PS_SHOP_DOMAIN_SSL')))
                $domain = Tools::getHttpHost();
            if ($entities)
                $domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
            if ($http)
                $domain = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$domain;
            return $domain;
        }
    }

    /**
     * Before v1.5.0.1 PrestaShop kept payment states defined as php constants
     * but later moved them to database. This function solves that.
     *
     * @param string $state State name
     * @return int|bool
     */
    public static function getPaymentState($state)
    {
        if (version_compare(_PS_VERSION_, '1.5.0.1', '>=')
            || $state == 'OPAY_PENDING'
        ) {
            return Configuration::get($state);
        } else {
            $constName = '_'.$state.'_';
            if (defined($constName)) {
                return constant($constName);
            }
        }
        return false;
    }

    /**
     * Creates a link to a module controller.
     *
     * @return string
     */
    public static function getModuleLink(
        $module,
        $controller = 'default',
        array $params = array(),
        $ssl = null,
        $idLang = null,
        $idShop = null,
        $relativeProtocol = false
    ) {
        if (version_compare(_PS_VERSION_, '1.5.0.5', '>=')) {
            $context = Context::getContext();
            $link = $context->link->getModuleLink(
                $module,
                $controller,
                $params,
                $ssl,
                $idLang,
                $idShop,
                $relativeProtocol
            );
        } elseif (version_compare(_PS_VERSION_, '1.5.0.1', '>=')) {
            $context = Context::getContext();
            $ssl = is_null($ssl) ? false : $ssl;
            $link = $context->link->getModuleLink(
                $module,
                $controller,
                $ssl,
                $idLang
            );
            if (!empty($params)) {
                $queryString = http_build_query($params);
                if (strpos($link, '?') === false) {
                    $link .= '?'.$queryString;
                } else {
                    $link .= '&'.$queryString;
                }
            }
        } else {
            $baseUri = self::getShopDomainSsl(true, true).__PS_BASE_URI__;
            $link = $baseUri.'modules/'.$module.'/'.$controller.'.php';

            if (!empty($params)) {
                $link .= '?'.http_build_query($params);
            }
        }

        return $link;
    }
}
