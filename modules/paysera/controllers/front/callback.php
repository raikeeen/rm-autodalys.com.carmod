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

class PayseraCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!$this->module->active) {
            exit;
        }

        $projectID         = Configuration::get('PAYSERA_GENERAL_PROJECT_ID');
        $projectPassword   = Configuration::get('PAYSERA_GENERAL_SIGN_PASS');

        try {
            $response = WebToPay::validateAndParseData($_REQUEST, $projectID, $projectPassword);

            if ($response['status'] == 1) {
                $orderId = $response['orderid'];

                $order = new Order($orderId);

                $amount = $order->getOrdersTotalPaid();

                $orderAmount   = (string) (round($amount, 2) * 100);

                $currencyObj   = Currency::getCurrency($order->id_currency);

                $orderCurrency = $currencyObj['iso_code'];

                $money = array(
                    'amount'   => $orderAmount,
                    'currency' => $orderCurrency
                );

                $isPaymentCorrect = $this->checkPayment($money, $response);

                if ($isPaymentCorrect) {
                    $orderOldStateId = $order->getCurrentOrderState()->id;
                    $orderStateId    = (int) Configuration::get('PAYSERA_ORDER_STATUS_PAID');

                    if ($orderOldStateId !== $orderStateId) {
                        $order->setCurrentState($orderStateId);
                    }

                    exit('OK');
                }
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function checkPayment($orderMoney, $response)
    {
        $orderAmount   = $orderMoney['amount'];
        $orderCurrency = $orderMoney['currency'];

        if ($response['amount'] !== $orderAmount
            || $response['currency'] !== $orderCurrency) {
            $checkConvert = array_key_exists('payamount', $response);
            if (!$checkConvert) {
                exit(sprintf(
                    'Wrong pay amount: ' . $response['amount'] / 100 . $response['currency']
                    . ', expected: ' . $orderAmount / 100 . $orderCurrency
                ));
            } elseif ($response['payamount'] !== $orderAmount
                || $response['paycurrency'] !== $orderCurrency) {
                exit(sprintf(
                    'Wrong pay amount: ' . $response['payamount'] / 100 . $response['paycurrency']
                    . ', expected: ' . $orderAmount / 100 . $orderCurrency
                ));
            }
        }

        return true;
    }
}
