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

class OrderInvoice extends OrderInvoiceCore
{
    // hien thi format invoice number ra ben ngoai
    public function getInvoiceNumberFormatted($id_lang, $id_shop = null)
    {
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/includes/helper.php");
        $helper = new BAInvoiceHelper();
        $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $setting = Tools::jsonDecode($setting, true);
        if ($helper->isEnabledCustomNumber('INVOICE') == true) {
            return $helper->formatInvoicebyNumber($this->number, $this->date_add, $id_lang, $id_shop);
        } else {
            return '#'.Configuration::get('PS_INVOICE_PREFIX', $id_lang, null, $id_shop).sprintf('%06d', $this->number);
        }
    }
    // hien thi format invoice number ra ben ngoai
    public function getDeliveryNumberFormatted($id_lang, $id_shop = null)
    {
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/includes/helper.php");
        $helper = new BAInvoiceHelper();
        if ($helper->isEnabledCustomNumber('DELIVERY') == true) {
            return $helper->formatDeliverybyNumber($this->delivery_number, $this->delivery_date, $id_lang, $id_shop);
        } else {
            $pre = Configuration::get('PS_DELIVERY_PREFIX', $id_lang, null, $id_shop);
            return '#'.$pre.sprintf("%06d", $this->delivery_number);
        }
    }
}
