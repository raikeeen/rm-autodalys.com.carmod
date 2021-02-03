<?php
if (!defined('_PS_VERSION_'))
{
    exit();
}

require_once(_PS_MODULE_DIR_.'opay/classes/opay_8.1.gateway.inc.php');
require_once(_PS_MODULE_DIR_.'opay/classes/crossversionshelper.class.php');

class Opay extends PaymentModule {

    private $messages = array();

    public $website_id;
    public $testmode;
    public $opay_user_id;
    public $signature_type;
    public $private_key;
    public $certificate;
    public $signature_password;
    public $payment_list;
    public $logo_size;

    /**
     * @var OpayGateway
     */
    protected $opayGateway;

    public function __construct()
    {
        $this->name    = 'opay';
        $this->tab     = 'payments_gateways';
        $this->version = '1.4.14';
        $this->author  = 'OPAY';
        $this->module_key = '';
        $this->bootstrap = true;

        $config = Configuration::getMultiple(array(
            'OPAY_WEBSITE_ID',
            'OPAY_TESTMODE',
            'OPAY_USER_ID',
            'OPAY_SIGNATURE_TYPE',
            'OPAY_PRIVATE_KEY',
            'OPAY_CERTIFICATE',
            'OPAY_SIGNATURE_PASSWORD',
            'OPAY_PAYMENT_LIST',
            'OPAY_PAYMENT_DESC',
            'OPAY_LOGO_SIZE',
        ));

        $this->website_id           = self::setValue($config, 'OPAY_WEBSITE_ID');
        $this->testmode             = self::setValue($config, 'OPAY_TESTMODE');
        $this->opay_user_id         = self::setValue($config, 'OPAY_USER_ID');
        $this->signature_type       = self::setValue($config, 'OPAY_SIGNATURE_TYPE', '');
        $this->private_key          = self::setValue($config, 'OPAY_PRIVATE_KEY', '');
        $this->certificate          = self::setValue($config, 'OPAY_CERTIFICATE', '');
        $this->signature_password   = self::setValue($config, 'OPAY_SIGNATURE_PASSWORD', '');
        $this->payment_list         = self::setValue($config, 'OPAY_PAYMENT_LIST', 1);
        $this->logo_size            = self::setValue($config, 'OPAY_LOGO_SIZE', 49);

        parent::__construct();

        $this->page             = basename(__FILE__, '.php');
        $this->description      = $this->l('Accept payments by OPAY system');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

        /**
         * Until v1.6.0.7 PS checks if display name and order payment name are
         * the same before displaying order confirmation page. They don't match
         * as we append channel name to order payment name. To by pass that
         * check set module display name to order payment name.
         */
        if (version_compare(_PS_VERSION_, '1.6.0.7', '<')
            && false !== stripos($_SERVER['REQUEST_URI'], 'order-confirmation')
            && false !== Tools::getValue('id_order', false)
        ) {
            $order = new Order((int)Tools::getValue('id_order'));
            $this->displayName = $order->payment;
        } else {
            $this->displayName = $this->l('OPAY');
        }

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            if (!$this->registerHook('paymentOptions')
                || !$this->registerHook('displayHeader')
            ) {
                return false;
            }
        } else {
            if (!$this->registerHook('payment')
                || !$this->registerHook('paymentReturn')
            ) {
                return false;
            }
        }

        /* adding pending order state */
        $order_pending              = new OrderState();
        $order_pending->name        = array_fill(0, 10, 'Awaiting OPAY payment');
        $order_pending->send_email  = false;
        $order_pending->invoice     = false;
        $order_pending->unremovable = false;
        $order_pending->logable     = false;
        if (version_compare(_PS_VERSION_, '1.5.0.2', '>=')) {
            $order_pending->paid = false;
        }
        if (version_compare(_PS_VERSION_, '1.6.0.9', '>=')) {
            $order_pending->color = '#4169E1';
        } elseif (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $order_pending->color = 'RoyalBlue';
        } else {
            $order_pending->color = 'lightblue';
        }
        if (version_compare(_PS_VERSION_, '1.5.0.15', '>=')) {
            $order_pending->module_name = $this->name;
        }
        if ($order_pending->add()) {
            copy(_PS_ROOT_DIR_.'/modules/opay/logo.gif', _PS_ROOT_DIR_.'/img/os/'.(int)$order_pending->id.'.gif');
        }
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            Configuration::updateGlobalValue('OPAY_PENDING', $order_pending->id);
        } else {
            Configuration::updateValue('OPAY_PENDING', $order_pending->id);
        }

        return true;
    }

    public function uninstall()
    {
        $order_state_pending = new OrderState(Configuration::get('OPAY_PENDING'));

        return (
            Configuration::deleteByName('OPAY_WEBSITE_ID') &&
            Configuration::deleteByName('OPAY_TESTMODE') &&
            Configuration::deleteByName('OPAY_USER_ID') &&
            Configuration::deleteByName('OPAY_SIGNATURE_TYPE') &&
            Configuration::deleteByName('OPAY_PRIVATE_KEY') &&
            Configuration::deleteByName('OPAY_CERTIFICATE') &&
            Configuration::deleteByName('OPAY_SIGNATURE_PASSWORD') &&
            Configuration::deleteByName('OPAY_PAYMENT_LIST') &&
            Configuration::deleteByName('OPAY_LOGO_SIZE') &&
            Configuration::deleteByName('OPAY_PENDING') &&
            $order_state_pending->delete() &&
            parent::uninstall()
        );
    }

    private function validatePostRequest()
    {
        $valid = true;
        if(ToolsCore::isSubmit('btnSubmit'))
        {
            if (!Tools::getValue('website_id'))
            {
                $this->messages[] = array(
                    'class' => '',
                    'src'   => '../img/admin/error.gif',
                    'msg'   => $this->l('Website ID is required!'),
                );
                $valid = false;
            }
            if ((int)Tools::getValue('testmode') && !Tools::getValue('opay_user_id'))
            {
                $this->messages[] = array(
                    'class' => '',
                    'src'   => '../img/admin/error.gif',
                    'msg'   => $this->l('When Test mode is ON the User ID must be filled.'),
                );
                $valid = false;
            }
            if (!Tools::getValue('private_key') && !Tools::getValue('certificate') && !Tools::getValue('signature_password'))
            {
                $this->messages[] = array(
                    'class' => '',
                    'src'   => '../img/admin/error.gif',
                    'msg'   => $this->l('Please, enter a Signature password or Private key and OPAY\'s certificate.'),
                );
                $valid = false;
            }
            else
            {
                if (Tools::getValue('signature_type') == 'rsa' && !Tools::getValue('private_key'))
                {
                    $this->messages[] = array(
                        'class' => '',
                        'src'   => '../img/admin/error.gif',
                        'msg'   => $this->l('Please, enter a Private key.'),
                    );
                    $valid = false;
                }
                if (Tools::getValue('signature_type') == 'rsa' && !Tools::getValue('certificate'))
                {
                    $this->messages[] = array(
                        'class' => '',
                        'src'   => '../img/admin/error.gif',
                        'msg'   => $this->l('Please, enter an OPAY\'s certificate.'),
                    );
                    $valid = false;
                }
                if (Tools::getValue('signature_type') == 'password' && !Tools::getValue('signature_password'))
                {
                    $this->messages[] = array(
                        'class' => '',
                        'src'   => '../img/admin/error.gif',
                        'msg'   => $this->l('Please, enter a Signature password'),
                    );
                    $valid = false;
                }
            }
        }

        if ($valid)
        {
            $this->messages[] = array(
                'class' => 'conf confirm',
                'src'   => '../img/admin/ok.gif',
                'msg'   => $this->l('Settings updated'),
            );
        }


        return $valid;
    }

    private function processPostRequest()
    {
        $this->website_id           = trim(Tools::getValue('website_id'));
        $this->testmode             = (int)Tools::getValue('testmode');
        $this->opay_user_id         = trim(Tools::getValue('opay_user_id'));
        $this->signature_type       = Tools::getValue('signature_type');
        $this->private_key          = trim(Tools::getValue('private_key'));
        $this->certificate          = trim(Tools::getValue('certificate'));
        $this->signature_password   = trim(Tools::getValue('signature_password'));
        $this->payment_list         = (int)Tools::getValue('payment_list');
        $this->logo_size            = (int)Tools::getValue('logo_size');

        Configuration::updateValue('OPAY_WEBSITE_ID',         $this->website_id);
        Configuration::updateValue('OPAY_TESTMODE',           $this->testmode);
        Configuration::updateValue('OPAY_USER_ID',            $this->opay_user_id);
        Configuration::updateValue('OPAY_SIGNATURE_TYPE',     $this->signature_type);
        Configuration::updateValue('OPAY_PRIVATE_KEY',        $this->private_key);
        Configuration::updateValue('OPAY_CERTIFICATE',        $this->certificate);
        Configuration::updateValue('OPAY_SIGNATURE_PASSWORD', $this->signature_password);
        Configuration::updateValue('OPAY_PAYMENT_LIST',       $this->payment_list);
        Configuration::updateValue('OPAY_LOGO_SIZE',          $this->logo_size);

    }

    public function getContent()
    {
        $this->validatePostRequest();
        if (Tools::getValue('btnSubmit'))
        {
            $this->processPostRequest();
        }

        if (empty($this->signature_type)) {
            $this->signature_type = 'password';
        }
        if (!is_numeric($this->payment_list)) {
            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                $this->payment_list = 4; // In checkout page As seperate options logo only
            } else {
                $this->payment_list = 1; // In checkout page
            }
        }

        $smarty = $this->getSmarty();

        $smarty->assign(array(
            'website_id'        => $this->website_id,
            'testmode'          => $this->testmode,
            'opay_user_id'      => $this->opay_user_id,
            'signature_type'    => $this->signature_type,
            'private_key'       => $this->private_key,
            'certificate'       => $this->certificate,
            'signature_password'=> $this->signature_password,
            'payment_list'      => $this->payment_list,
            'logo_size'         => $this->logo_size,
            'messages'          => $this->messages,
            'requestUrl'        => $_SERVER['REQUEST_URI'],
            'version'           => (int)substr(str_replace('.', '', _PS_VERSION_), 0, 2), // result will be integer of two digits. Like 15 or 16
        ));

        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    /**
     * New in PrestaShop 1.7.
     *
     * Hook executed at checkout to add payment options.
     *
     * @param array $params
     * @return array
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        /** @var Cart */
        $cart         = $params['cart'];
        $languageIso  = Language::getIsoById($cart->id_lang);

        /** @var Address */
        $address      = Address::initialize($cart->id_address_invoice);
        $countryIso   = Country::getIsoById($address->id_country);

        $paymentOptions = array();

        if (in_array((int)$this->payment_list, array(0, 1, 2))) {
            $paymentGatewayName = $this->getSettings(
                $languageIso,
                $countryIso,
                'payment_gateway_name'
            );
            if (empty($paymentGatewayName)) {
                $paymentGatewayName = $this->displayName;
            }

            // hiding namespace syntax from lower than 5.3 PHP eyes
            eval('$paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();');
            $paymentOption->setCallToActionText($paymentGatewayName);

            if (in_array((int)$this->payment_list, array(1, 2))) {
                /**
                 * Later PrestaShop loads this form html into PHP's DOMDocument
                 * to add hidden element to the end of it. That operation
                 * encodes lithuanian symbols in different encoding than UTF-8
                 * and they are displayed incorectly for the client. To force
                 * DOMDocument to use UTF-8, xml tag is prepended to this html.
                 *
                 * Don't remove xml tag, unless you know that this is fixed!
                 */
                $formHtml = '<?xml encoding="utf-8" ?>'.$this->generateForm($cart);
                $paymentOption->setForm($formHtml);
            } else {
                $paymentOption->setAction($this->context->link->getModuleLink(
                    $this->name,
                    'payment',
                    array(),
                    true
                ));
            }
            $paymentOptions[] = $paymentOption;
        } elseif (in_array((int)$this->payment_list, array(3, 4, 5))) { // As seperate options
            /** @var array */
            $channels = $this->getChannelsList($cart);
            if (!empty($channels)) {
                $paymentOptions = $this->generatePaymentOptions($channels);
            }
        }

        return $paymentOptions;
    }

    /**
     * Hook executed when displaying order confirmation page
     *
     * @return string html
     */
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $smarty = $this->getSmarty();
        $status = Tools::getValue('status', false);
        if (false !== $status) {
            $smarty->assign(array(
                'status' => $status
            ));
        } else {
            $smarty->assign(array(
                'status' => 1
            ));
        }

        $html = $this->display(__FILE__, 'views/templates/front/done.tpl');

        return $html;
    }

    /**
     * Hook executed when displaying html header. This function adds additional
     * validation javascript for opay form.
     *
     * @param array $params
     * @return void
     */
    public function hookDisplayHeader($params)
    {
        if (!$this->active) {
            return;
        }

        $this->context->controller->registerJavascript(
            'modules-opay-form',
            $this->_path.'js/opay-checkout.js',
            array('position' => 'bottom', 'priority' => 1000)
        );
    }

    /**
     * Generates html form with OPAY channels
     *
     * @param $cart Cart
     * @return string
     */
    public function generateForm(Cart $cart)
    {
        $smarty = $this->getSmarty();
        $smarty->assign(array(
            'action'        => $this->context->link->getModuleLink(
                'opay',
                'payment',
                array(),
                true
            ),
            'on_submit'     => 'return opayHandleSubmit()',
            'version'       => (int)substr(str_replace('.', '', _PS_VERSION_), 0, 2), // result will be integer of two digits. Like 15 or 16
            'logo_size'     => $this->logo_size,
            'payment_list'  => $this->payment_list,
            'channels'      => $this->getChannelsList($cart)
        ));
        return $smarty->fetch('module:opay/views/templates/front/payment_form.tpl');
    }

    /**
     * Cross version helper function which gets smarty object from this
     *
     * @return Smarty
     */
    public function getSmarty()
    {
        if (version_compare(_PS_VERSION_, '1.5.0.1', '>=')) {
            return $this->context->smarty;
        } else {
            return $this->smarty;
        }
    }

    /**
     * Hook execute at checkout. For versions under 1.7
     *
     * @return string
     */
    public function hookPayment()
    {
        $smarty        = $this->getSmarty();
        $iso_code      = $this->context->language->iso_code;
        $language_code = Tools::strtoupper(Language::getIsoById((int)$this->context->language->id));
        $cart          = $this->context->cart;
        $address       = new Address((int)$cart->id_address_invoice);
        $country       = new Country((int)$address->id_country);

        if (in_array((int)$this->payment_list, array(1, 2)))
        {
            $opay = $this->getOpayGateway();

            $contextObj = Context::getContext();
            $currency   = $contextObj->currency;
            $customer   = new Customer((int)$cart->id_customer);
            $total      = $cart->getOrderTotal();

            $redirectUrl = CrossVersionsHelper::getModuleLink(
                $this->name,
                'validation',
                array(),
                true
            );
            $webServiceUrl = CrossVersionsHelper::getModuleLink(
                $this->name,
                'callback',
                array(),
                true
            );

            $paramsArray = array(
                'website_id'            => $this->website_id,
                'order_nr'              => $cart->id,
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

            if ($this->testmode)
            {
                $paramsArray['test'] = $this->opay_user_id;
            }

            try{
                $paramsArray    = $opay->signArrayOfParameters($paramsArray);
                $channelsArray  = $opay->webServiceRequest('https://gateway.opay.lt/api/listchannels/', $paramsArray);
            } catch (Exception $e) {
                return '';
            }

            // Add direct link to each channel and logo url without logo size key
            foreach ($channelsArray['response']['result'] as $key => $value)
            {
                foreach ($value['channels'] as $key2 => $value2)
                {
                    $link = CrossVersionsHelper::getModuleLink(
                        $this->name,
                        'payment',
                        array(
                            'type' => $channelsArray['response']['result'][$key]['channels'][$key2]['channel_name']
                        )
                    );

                    $channelsArray['response']['result'][$key]['channels'][$key2]['link'] = $link;
                    $channelsArray['response']['result'][$key]['channels'][$key2]['logo_urls'] = $value2['logo_urls']['color_'.$this->logo_size.'px'];
                }
            }

            $smarty->assign(array(
                'version'       => (int)substr(str_replace('.', '', _PS_VERSION_), 0, 2), // result will be integer of two digits. Like 15 or 16
                'payment_list'  => $this->payment_list,
                'channels'      => $channelsArray['response']['result'],
                'lang'          => $iso_code,
                'this_path'     => $this->_path,
                'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
            ));
        }
        else
        {
            $smarty->assign(array(
                'version'               => (int)substr(str_replace('.', '', _PS_VERSION_), 0, 2), // result will be integer of two digits. Like 15 or 16
                'payment_list'          => 0,
                'logo_opay'             => 'https://widgets.opay.lt/img/internal_opay_color_0x'.$this->logo_size.'.png',
                'lang'                  => $iso_code,
                'this_path'             => $this->_path,
                'this_path_ssl'         => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/',
                'mlink'                 => CrossVersionsHelper::getModuleLink($this->name, 'payment', array(), true)
            ));

            $paymentGatewayName = $this->getSettings($language_code, $country->iso_code, 'payment_gateway_name');
            if (empty($paymentGatewayName))
            {
                $paymentGatewayName = 'OPAY';
            }
            $smarty->assign('payment_gateway_name', $paymentGatewayName);
        }

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * Make request to opay for channels list
     *
     * @return array
     */
    protected function getChannelsList(Cart $cart)
    {
        $context     = $this->context;
        $currency    = Currency::getCurrencyInstance($cart->id_currency);
        $languageIso = Language::getIsoById($cart->id_lang);
        $address     = Address::initialize($cart->id_address_invoice);
        $countryIso  = Country::getIsoById($address->id_country);
        $total       = $cart->getOrderTotal();

        // Get a customer
        if (isset($context->customer) && $context->customer->id == $cart->id_customer) {
            $customer = $context->customer;
        } else {
            $customer = new Customer((int)$cart->id_customer);
        }

        $redirectUrl = CrossVersionsHelper::getModuleLink(
            $this->name,
            'validation',
            array(),
            true
        );
        $webServiceUrl = CrossVersionsHelper::getModuleLink(
            $this->name,
            'callback',
            array(),
            true
        );

        $paramsArray = array(
            'website_id'      => $this->website_id,
            'order_nr'        => $cart->id,
            'redirect_url'    => $redirectUrl,
            'web_service_url' => $webServiceUrl,
            'standard'        => 'opay_8.1',
            'country'         => $countryIso,
            'language'        => Tools::strtoupper($languageIso),
            'amount'          => (int)number_format($total, 2, '', ''),
            'currency'        => $currency->iso_code,
            'c_email'         => $customer->email
        );

        $phone_mobile = trim($address->phone_mobile);
        $phone        = trim($address->phone);
        if ($phone_mobile != '') {
            $paramsArray['c_mobile_nr'] = $phone_mobile;
        } else if ($phone != '') {
            $paramsArray['c_mobile_nr'] = $phone;
        }

        if ($this->testmode) {
            $paramsArray['test'] = $this->opay_user_id;
        }

        $opay = $this->getOpayGateway();
        try {
            $paramsArray = $opay->signArrayOfParameters($paramsArray);
            $response    = $opay->webServiceRequest('https://gateway.opay.lt/api/listchannels/', $paramsArray);
        } catch (Exception $e) {
            // TODO log error
            return array();
        }

        // TODO check if there are errors in response and log them

        if (!empty($response['response']['result'])) {
            return $response['response']['result'];
        }
        return array();
    }

    private static function setValue($config = array(), $value = '', $default = 0)
    {
        return (isset($config[$value])) ? $config[$value] : $default;
    }

    public function getSettings($languageCode, $countryCode, $name = '')
    {
        if (empty($this->getSettingsArray[$languageCode][$countryCode]))
        {
            $opay = $this->getOpayGateway();
            $paramsArray = array(
                'service_name' => 'getSettings',
                'website_id'   => $this->website_id,
                'language'     => $languageCode,
                'country'      => $countryCode
            );

            try
            {
                $paramsArray = $opay->signArrayOfParameters($paramsArray);
                $array = $opay->webServiceRequest('https://gateway.opay.lt/api/websites/', $paramsArray);
            }
            catch (OpayGatewayException $e)
            {
                $array = array();
            }

            if (!empty($array['response']['result']))
            {
                $this->getSettingsArray[$languageCode][$countryCode] = $array['response']['result'];
            }
        }


        if ($name != '')
        {
            return (isset($this->getSettingsArray[$languageCode][$countryCode][$name])) ? $this->getSettingsArray[$languageCode][$countryCode][$name] : '';
        }
        else
        {
            return (!empty($this->getSettingsArray[$languageCode][$countryCode])) ? $this->getSettingsArray[$languageCode][$countryCode] : array();
        }


    }

    /**
     * Creates OpayGateway object
     *
     * @return OpayGateway
     */
    public function getOpayGateway()
    {
        if (!isset($this->opayGateway)) {
            $opay = new OpayGateway();
            if ($this->signature_type == "password") {
                $opay->setSignaturePassword($this->signature_password);
            } else {
                $opay->setMerchantRsaPrivateKey($this->private_key);
                $opay->setOpayCertificate($this->certificate);
            }
            $this->opayGateway = $opay;
        }
        return $this->opayGateway;
    }

    /**
     * Get values from encoded request parameter
     *
     * @param $module Opay
     * @return array
     */
    public function getEncodedValues()
    {
        $opay = $this->getOpayGateway();
        $values = array();

        if (isset($_POST['encoded'])) {
            $values = $opay->convertEncodedStringToArrayOfParameters($_POST['encoded']);
        } elseif (isset($_GET['encoded'])) {
            $values = $opay->convertEncodedStringToArrayOfParameters($_GET['encoded']);
        } elseif (isset($_POST['password_signature']) || isset($_POST['rsa_signature'])) {
            $values = $_POST;
        } elseif (isset($_GET['password_signature']) || isset($_GET['rsa_signature'])) {
            $values = $_GET;
        }

        return $values;
    }

    /**
     * Generates seperate checkout payment options from opay channels array
     *
     * @param $channels array
     * @return PaymentOption[]
     */
    private function generatePaymentOptions($channels)
    {
        $options = array();
        foreach ($channels as $group) {
            foreach ($group['channels'] as $channel) {
                // hiding namespace syntax from lower than 5.3 PHP eyes
                eval('$option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();');
                if (in_array((int)$this->payment_list, array(3, 5))) {
                    $option->setCallToActionText($channel['title']);
                }
                if (in_array((int)$this->payment_list, array(4, 5))) {
                    $option->setLogo($channel['logo_urls']['color_'.$this->logo_size.'px']);
                }
                $option->setModuleName($this->name);
                $option->setAction($this->context->link->getModuleLink(
                    $this->name,
                    'payment',
                    array('opay_channel' => $channel['channel_name']),
                    true
                ));
                $options[] = $option;
            }
        }
        return $options;
    }


    ////////////////////////////////////////////
    //  Actions for 1.5.x
    ////////////////////////////////////////////


    public function actionPayment()
    {
        $contextObj = Context::getContext();
        $smarty = $this->getSmarty();
        CrossVersionsHelper::payment($this, $contextObj, $smarty);
    }

    public function actionValidation()
    {
        CrossVersionsHelper::validation($this);
    }

    public function actionDone()
    {
        $historyLink = '/index.php?controller=history';
        $smarty = $this->getSmarty();

        if (Tools::getValue('status') == 1 || Tools::getValue('status') == 2)
        {
            $smarty->assign(array(
                'status'       => Tools::getValue('status'),
                'history_link' => $historyLink
            ));
            return dirname(__FILE__).'/views/templates/front/done.tpl';
        }
        else
        {
            Tools::redirect($historyLink);
        }
    }

    public function actionCallback()
    {
        CrossVersionsHelper::callback($this);
    }


}
