<?php
/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@buy-addons.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Buy-Addons <contact@buy-addons.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* @since 1.6
*/

class BaInvoiceHelper
{
    public $databaseRef = array(
        'INVOICE' => array(
            'table'     =>'order_invoice',
            'key'         =>'id_order_invoice',
            'column'     =>'number',
        ),
        'DELIVERY' => array(
            'table'     =>'order_invoice',
            'key'         =>'id_order_invoice',
            'column'     =>'delivery_number',
        ),
        'CREDIT' => array(
            'table'     =>'order_slip',
            'key'         =>'id_order_slip',
            'column'     =>'number',
        ),
        'ORDER' => array(
            'table'     =>'orders',
            'key'         =>'id_order',
            'column'     =>'reference',
        ),
    );
    public function getNextNumber($id_shop, $type, $start = 1, $step = 1, $reset = 0, $reset_value = '')
    {
        if ($reset_value != '') {
            if ($reset_value > time()) {
                $reset_value = '';
            }
        }
        $inner_join = ' ';
        if ($type == 'ORDER') {
            $inner_join = ' ';
        } else {
            $inner_join = ' INNER JOIN '._DB_PREFIX_.'orders b ON a.id_order = b.id_order';
        }
        $where = sprintf(' WHERE a.%s <> \'0\' ', pSQL($this->databaseRef[$type]['column']));
        $where .= sprintf(' AND a.%s IS NOT NULL ', pSQL($this->databaseRef[$type]['column']));
        $s = (int) $start;
        $c =  pSQL($this->databaseRef[$type]['column']);
        $se = (int) $step;
        $t = pSQL(_DB_PREFIX_.$this->databaseRef[$type]['table']);
        if ($reset == 0) {
            // = None
            $sql = sprintf('SELECT GREATEST( %d + COUNT(a.%s) * %d, 1) AS next_number FROM %s a', $s, $c, $se, $t);
        }
        if ($reset == 1) {
            $tm = (int) floor(($reset_value-$start)/$step+1);
            // = value
            $sql = 'SELECT GREATEST(%d + MOD(COUNT(a.%s), %d) * %d, 1) AS next_number FROM %s a';
            $sql = sprintf($sql, $s, $c, $tm, $se, $t);
        }
        if ($reset == 2) {
            // date
            $sql = sprintf('SELECT GREATEST( %d + COUNT(a.%s) * %d, 1) AS next_number FROM %s a', $s, $c, $se, $t);
            $date = date('Y-m-d', $reset_value);
            $where .= sprintf(' AND a.date_add >= \'%s\'', pSQL($date));
        }
        if ($type == 'ORDER') {
            $where .= sprintf(' AND a.id_shop = %d', (int) $id_shop);
        } else {
            $where .= sprintf(' AND b.id_shop = %d', (int) $id_shop);
        }
        $sql .= $inner_join.$where;
        //var_dump(($reset_value-$start)/$step);
        //var_dump($sql);
        return Db::getInstance()->getValue($sql);
    }
    public static function getNumberFormatted($format, $counter, $length, $date)
    {
        
        $counter_formated = sprintf("%0{$length}d", $counter);
        $date_arr = date_parse($date);
        $format = str_replace('[counter]', $counter_formated, $format);
        $format = str_replace('[m]', $date_arr['month'], $format);
        $format = str_replace('[Y]', $date_arr['year'], $format);
        $format = str_replace('[d]', $date_arr['day'], $format);
        return $format;
    }
    // kiem tra xem co du dieu kien de su dung custom invoice number hay khong?
    public function isEnabledCustomNumber($type)
    {
        if (Module::isEnabled('ba_prestashop_invoice') == false) {
            return false;
        }
        $id_shop = Context::getContext()->shop->id;
        $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $setting = Tools::jsonDecode($setting, true);
        ////// return OLD source if
        if (empty($setting) || $setting['invoice_start_numbering']>time()) {
            return false;
        }
        if ($type == 'INVOICE' && $setting['invoice_number_status'] == 0) {
            return false;
        }
        if ($type == 'DELIVERY' && $setting['delivery_number_status'] == 0) {
            return false;
        }
        if ($type == 'CREDIT' && $setting['credit_number_status'] == 0) {
            return false;
        }
        if ($type == 'ORDER' && $setting['order_number_status'] == 0) {
            return false;
        }
        return true;
    }
    
    public function setLastInvoiceNumber($order_invoice_id, $id_shop)
    {
        ////////////////////
        $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $setting = Tools::jsonDecode($setting, true);
        $type = 'INVOICE';
        $start = $setting['invoice_start'];
        $step = $setting['invoice_step'];
        $reset = $setting['invoice_reset'];
        $reset_value = ($reset==1) ? $setting['invoice_reset_value']:$setting['invoice_reset_date'];
        $next_number = $this->getNextNumber($id_shop, $type, $start, $step, $reset, $reset_value);
        $sql = 'UPDATE `'._DB_PREFIX_.'order_invoice` SET number ='.(int) $next_number;
        $sql .=' WHERE `id_order_invoice` = '.(int) $order_invoice_id;

        return Db::getInstance()->execute($sql);
    }
    public function setDeliveryNumber($order_invoice_id, $id_shop)
    {
        ////////////////////
        $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $setting = Tools::jsonDecode($setting, true);
        $type = 'DELIVERY';
        $start = $setting['delivery_start'];
        $step = $setting['delivery_step'];
        $reset = $setting['delivery_reset'];
        $reset_value = ($reset==1) ? $setting['delivery_reset_value']:$setting['delivery_reset_date'];
        $next_number = $this->getNextNumber($id_shop, $type, $start, $step, $reset, $reset_value);
        $sql = 'UPDATE `'._DB_PREFIX_.'order_invoice` SET delivery_number ='.(int) $next_number;
        $sql .=' WHERE `id_order_invoice` = '.(int) $order_invoice_id;
        return Db::getInstance()->execute($sql);
    }
    public function generateReference($id_shop)
    {
        ////////////////////
        $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $setting = Tools::jsonDecode($setting, true);
        $type = 'ORDER';
        $start = $setting['order_start'];
        $step = $setting['order_step'];
        $reset = $setting['order_reset'];
        $reset_value = ($reset==1) ? $setting['order_reset_value']:$setting['order_reset_date'];
        $next_number = $this->getNextNumber($id_shop, $type, $start, $step, $reset, $reset_value);
        //////////////
        $format = $setting['order_format'];
        $length = $setting['order_length'];
        $date_add = date('Y-m-d');
        $reference = $this->getNumberFormatted($format, $next_number, $length, $date_add);
        return $reference;
    }
    public function setCreditSlipsNumber($id_shop, $order_slip)
    {
        ////////////////////
        $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $setting = Tools::jsonDecode($setting, true);
        $type = 'CREDIT';
        $start = $setting['credit_start'];
        $step = $setting['credit_step'];
        $reset = $setting['credit_reset'];
        $reset_value = ($reset==1) ? $setting['credit_reset_value']:$setting['credit_reset_date'];
        $next_number = $this->getNextNumber($id_shop, $type, $start, $step, $reset, $reset_value);
        $sql = 'UPDATE `'._DB_PREFIX_.'order_slip` SET number ='.(int) $next_number;
        $sql .=' WHERE `id_order_slip` = '.(int) $order_slip->id;
        return Db::getInstance()->execute($sql);
    }
    /************* format FILE NAME of INVOICE, DELIVERY, CREDIT by number **********/
    public function formatInvoicebyNumber($number, $date_add, $id_lang, $id_shop = null)
    {
        $id_lang;
        $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $setting = Tools::jsonDecode($setting, true);
            
        if ($this->isEnabledCustomNumber('INVOICE') == true) {
            $format = $setting['invoice_format'];
            $length = $setting['invoice_length'];
            //var_dump($setting);die;
            return $this->getNumberFormatted($format, $number, $length, $date_add);
        }
        return false;
    }
    public function formatDeliverybyNumber($delivery_number, $date_add, $id_lang, $id_shop = null)
    {
        $id_lang;
        $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $setting = Tools::jsonDecode($setting, true);
        if ($this->isEnabledCustomNumber('DELIVERY') == true) {
            $format = $setting['delivery_format'];
            $length = $setting['delivery_length'];
            return $this->getNumberFormatted($format, $delivery_number, $length, $date_add);
        }
    }
    public function formatCreditbyNumber($number, $date_add, $id_lang, $id_shop = null)
    {
        $id_lang;
        $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $setting = Tools::jsonDecode($setting, true);
        $format = $setting['credit_format'];
        $length = $setting['credit_length'];
        return $this->getNumberFormatted($format, $number, $length, $date_add);
    }
    /******** format for attachment in Mail::send() **********/
    public function formatInvoiceByFilename($filename, $id_lang, $id_shop)
    {
        $inv_prefix = Configuration::get('PS_INVOICE_PREFIX', $id_lang, null, $id_shop);
        $dev_prefix = Configuration::get('PS_DELIVERY_PREFIX', $id_lang, null, $id_shop);
        
        $re_arr = array($inv_prefix, $dev_prefix, '.pdf','#');
        $id_order_invoice = str_replace($re_arr, '', $filename);
        $id_order_invoice = ltrim($id_order_invoice, '0');
        if (!$id_order_invoice || $this->isEnabledCustomNumber('INVOICE') == false) {
            return $filename;
        }
        $invoice = new OrderInvoice($id_order_invoice);
        return $this->formatInvoicebyNumber($invoice->number, $invoice->date_add, $id_lang, $id_shop);
    }
    public function formatDeliveryByFilename($filename, $id_lang, $id_shop)
    {
        $inv_prefix = Configuration::get('PS_INVOICE_PREFIX', $id_lang, null, $id_shop);
        $dev_prefix = Configuration::get('PS_DELIVERY_PREFIX', $id_lang, null, $id_shop);
        
        $re_arr = array($inv_prefix, $dev_prefix, '.pdf','#');
        $id_order_invoice = str_replace($re_arr, '', $filename);
        $id_order_invoice = ltrim($id_order_invoice, '0');
        if (!$id_order_invoice || $this->isEnabledCustomNumber('DELIVERY') == false) {
            return $filename;
        }
        $invoice = new OrderInvoice($id_order_invoice);
        return $this->formatDeliverybyNumber($invoice->delivery_number, $invoice->delivery_date, $id_lang, $id_shop);
    }
    /** doc file XML va replace code trong tpl ***/
    public static function configOldTplFile()
    {
        $xml_path = _PS_MODULE_DIR_ . "ba_prestashop_invoice/install/install.xml";
        $xml=simplexml_load_file($xml_path) or die("Error: Cannot load XML file");
        foreach ($xml->filename as $filename) {
            $path = self::replaceVariables($filename['path']);
            $path_bak = self::replaceVariables($filename['path_bak']);
            if (!file_exists($path)) {
                continue;// file ko ton tai
            }
            copy($path, $path_bak);
            $content = Tools::file_get_contents($path);
            foreach ($filename->item as $item) {
                $search = html_entity_decode($item->search);
                $replace = html_entity_decode($item->replace);
                $content = str_replace($search, $replace, $content);
            }
            file_put_contents($path, $content);
        }
        return true;
    }
    public static function replaceVariables($path)
    {
        $path = (string) $path;
        $context = Context::getContext();
        $admin_theme = 'default';
        if (!empty($context->employee->bo_theme)) {
            $admin_theme = $context->employee->bo_theme;
        }
        $path = str_replace('ADMIN_DIR', _PS_ADMIN_DIR_, $path);
        $path = str_replace('ADMIN_TEMPLATE', $admin_theme, $path);
        $path = str_replace('PS_ROOT_DIR', _PS_ROOT_DIR_, $path);
        return $path;
    }
}
