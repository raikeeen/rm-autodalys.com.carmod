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

class PayseraAcceptModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!$this->module->active) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $projectID         = Configuration::get('PAYSERA_GENERAL_PROJECT_ID');
        $projectPassword   = Configuration::get('PAYSERA_GENERAL_SIGN_PASS');

        $response = WebToPay::validateAndParseData($_REQUEST, $projectID, $projectPassword);

        $idOrder = $response['orderid'];
        $order = new Order($idOrder);
        
        $orderOldStateId  = $order->getCurrentOrderState()->id;
        $paidOrderStateId = (int) Configuration::get('PAYSERA_ORDER_STATUS_PAID');
        $pendingOrderStateId = (int) Configuration::get('PAYSERA_ORDER_STATUS_PENDING');
        $newOrderStateId = (int) Configuration::get('PAYSERA_ORDER_STATUS_NEW');

        if ($orderOldStateId == $pendingOrderStateId
            && $orderOldStateId != $paidOrderStateId
        ) {
            $order->setCurrentState($newOrderStateId);
        }

        $customer = $this->context->customer;

        if (!Validate::isLoadedObject($customer) ||
            !Validate::isLoadedObject($order)
        ) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $params = array(
            'id_cart' => $order->id_cart,
            'id_module' => $this->module->id,
            'id_order' => $order->id,
            'key' => $customer->secure_key,
        );

        Tools::redirect($this->context->link->getPageLink('order-confirmation', null, null, $params));
    }
}
