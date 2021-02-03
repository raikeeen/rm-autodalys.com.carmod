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

class PayseraCancelModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $data = $_REQUEST;
        if (version_compare(_PS_VERSION_, '1.7.1') < 0) {
            $orderId    = Order::getOrderByCartId($data['cartID']);
        } else {
            $orderId    = Order::getIdByCartId($data['cartID']);
        }
        $order = new Order($orderId);
        if ($order) {
            $oldCart = new Cart($data['cartID']);
            $duplication = $oldCart->duplicate();
            if (!$duplication || !Validate::isLoadedObject($duplication['cart'])) {
                $this->errors[] = Tools::displayError($this->l(
                    'Sorry. We cannot renew your order.'
                ));
            } elseif (!$duplication['success']) {
                $this->errors[] = Tools::displayError($this->l(
                    'Some items are no longer available, and we are unable to renew your order.'
                ));
            } else {
                $this->context->cookie->id_cart = $duplication['cart']->id;
                $context = $this->context;
                $context->cart = $duplication['cart'];
                CartRule::autoAddToCart($context);
                $this->context->cookie->write();

                Tools::redirect($this->context->link->getPageLink('cart'));
            }
        }
    }
}
