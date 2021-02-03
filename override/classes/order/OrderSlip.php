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
class OrderSlip extends OrderSlipCore
{
    
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:01
    * version: 1.1.39
    */
    public $number;
    
    /**
     * Build object
     *
     * @param int $id Existing object id in order to load object (optional)
     * @param int $id_lang Required if object is multilingual (optional)
     * @param int $id_shop ID shop for objects with multishop on langs
     */
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:01
    * version: 1.1.39
    */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        self::$definition['fields']['number'] = array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId');
        parent::__construct($id, $id_lang, $id_shop);
    }
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:01
    * version: 1.1.39
    */
    public function getCreditSlipsNumberFormatted($id_lang, $id_shop = null)
    {
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/includes/helper.php");
        $helper = new BAInvoiceHelper();
        if ($helper->isEnabledCustomNumber('CREDIT') == true) {
            return $helper->formatCreditbyNumber($this->number, $this->date_add, $id_lang, $id_shop);
        } else {
            return '#'.Configuration::get('PS_CREDIT_SLIP_PREFIX', $id_lang, null, $id_shop).sprintf("%06d", $this->id);
        }
    }
}
