<?php
/**
 * 2018 Paysera
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Paysera <plugins@paysera.com>
 *  @copyright 2018 Paysera
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Paysera
 */

define('_PS_PAYSERA_FORCE_LOGIN_', (bool) Configuration::get('PAYSERA_EXTRA_FORCE_LOGIN'));

class PayseraRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * @var bool
     */
    public $auth = _PS_PAYSERA_FORCE_LOGIN_;

    /**
     * @var bool
     */
    public $ssl = true;

    /**
     * @var object
     */
    public $module;

    /**
     * Process redirection to Paysera system
     */
    public function postProcess()
    {
        $this->processValidations();

        $cart = $this->context->cart;

        $cartID       = $cart->id;
        $cartTotal    = $cart->getOrderTotal();
        $cartCurrency = $cart->id_currency;
        $moduleName   = $this->module->displayName;
        $status       = Configuration::get('PAYSERA_ORDER_STATUS_PENDING');
        $secureKey    = $this->context->customer->secure_key;
        $message      = null;
        $dontRound    = false;
        $extraVal     = array();

        $this->module->validateOrder(
            $cartID,
            $status,
            $cartTotal,
            $moduleName,
            $message,
            $extraVal,
            $cartCurrency,
            $dontRound,
            $secureKey
        );


        $paymentData = $this->collectPaymentData();

        if (is_null($paymentData)) {
            Tools::redirect($this->context->link->getPageLink('order'));
        } else {
            WebToPay::redirectToPayment($paymentData, true);
        }
    }

    /**
     * Collect payment information from order
     *
     * @return array|null
     */
    protected function collectPaymentData()
    {
        $projectID       = Configuration::get('PAYSERA_GENERAL_PROJECT_ID');
        $projectPassword = Configuration::get('PAYSERA_GENERAL_SIGN_PASS');
        $testMode        = Configuration::get('PAYSERA_GENERAL_TEST_MODE');

        $cart     = $this->context->cart;
        if (version_compare(_PS_VERSION_, '1.7.1') < 0) {
            $orderId    = Order::getOrderByCartId($cart->id);
            $currency = new Currency($cart->id_currency);
            $address  = new Address($cart->id_address_invoice);
        } else {
            $order    = Order::getByCartId($cart->id);
            $orderId  = $order->id;
            $currency = new Currency($order->id_currency);
            $address  = new Address($order->id_address_invoice);
        }
        $country  = new Country($address->id_country);
        $state    = new State($address->id_state);
        $customer = $this->context->customer;

        $orderID           = $orderId;
        $totalAmount       = (string) (round($cart->getOrderTotal(), 2) * 100);
        $currency          = $currency->iso_code;
        $countryCode       = Tools::strtoupper($country->iso_code);
        $moduleName        = $this->module->name;
        $cancelParams      = array('cartID' => $cart->id);
        $paymentMethod     = Tools::getValue('paysera_payment_method');
        $customerFirstname = $customer->firstname;
        $customerLastname  = $customer->lastname;
        $customerEmail     = $customer->email;
        $billingAddress    = $address->address1;
        $billingCity       = $address->city;
        $billingState      = $state->iso_code;
        $billingPostCode   = $address->postcode;
        $billingCountry    = $country->iso_code;
        $lang              = $this->getPayseraLangCode();

        $data = array(
            'projectid'     => $projectID,
            'sign_password' => $projectPassword,
            'orderid'       => $orderID,
            'amount'        => $totalAmount,
            'currency'      => $currency,
            'country'       => $countryCode,
            'accepturl'     => $this->context->link->getModuleLink($moduleName, 'accept'),
            'cancelurl'     => $this->context->link->getModuleLink($moduleName, 'cancel', $cancelParams),
            'callbackurl'   => $this->context->link->getModuleLink($moduleName, 'callback'),
            'test'          => (int) $testMode,
            'payment'       => $paymentMethod,
            'p_firstname'   => $customerFirstname,
            'p_lastname'    => $customerLastname,
            'p_email'       => $customerEmail,
            'p_street'      => $billingAddress,
            'p_city'        => $billingCity,
            'p_state'       => $billingState,
            'p_zip'         => $billingPostCode,
            'p_countrycode' => $billingCountry,
            'lang'          => $lang,
        );

        return $data;
    }

    /**
     * @return string
     */
    protected function getPayseraLangCode()
    {
        $langISO = $this->context->language->iso_code;

        switch ($langISO) {
            case 'lt':
                return 'LIT';
            case 'lv':
                return 'LAV';
            case 'ee':
                return 'EST';
            case 'ru':
                return 'RUS';
            case 'de':
                return 'GER';
            case 'pl':
                return 'POL';
            default:
                return 'ENG';
        }
    }

    /**
     * Process validations (cart, module, currencies and etc.)
     */
    protected function processValidations()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 ||
            $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0
        ) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        if (!$this->module->active ||
            !$this->module->checkCurrency()
        ) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $authorized = false;
        $paymentModules = Module::getPaymentModules();

        foreach ($paymentModules as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            $this->errors[] = $this->module->l('This payment method is not available.', 'redirect');
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }
    }
}
