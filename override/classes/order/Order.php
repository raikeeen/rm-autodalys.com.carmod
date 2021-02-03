<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
class Order extends OrderCore
{
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:01
    * version: 1.1.39
    */
    public $payment_fee = 0;
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:01
    * version: 1.1.39
    */
    public static function setLastInvoiceNumber($order_invoice_id, $id_shop)
    {
        if (!$order_invoice_id) {
            return false;
        }
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/includes/helper.php");
        $helper = new BAInvoiceHelper();
        if ($helper->isEnabledCustomNumber('INVOICE') == false) {
            return parent::setLastInvoiceNumber($order_invoice_id, $id_shop);
        }
        return $helper->setLastInvoiceNumber($order_invoice_id, $id_shop);
    }
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:01
    * version: 1.1.39
    */
    public function setDeliveryNumber($order_invoice_id, $id_shop)
    {
        if (!$order_invoice_id) {
            return false;
        }
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/includes/helper.php");
        $helper = new BAInvoiceHelper();
        if ($helper->isEnabledCustomNumber('DELIVERY') == false) {
            return parent::setDeliveryNumber($order_invoice_id, $id_shop);
        }
        return $helper->setDeliveryNumber($order_invoice_id, $id_shop);
    }
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:01
    * version: 1.1.39
    */
    public static function generateReference()
    {
        $id_shop = (int) Context::getContext()->shop->id;
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/includes/helper.php");
        $helper = new BAInvoiceHelper();
        if ($helper->isEnabledCustomNumber('ORDER') == false) {
            return parent::generateReference();
        }
        return $helper->generateReference($id_shop);
    }
}
