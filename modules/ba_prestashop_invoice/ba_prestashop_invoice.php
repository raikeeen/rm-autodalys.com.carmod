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
* to license@buy-addons.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Buy-Addons <contact@buy-addons.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* @since 1.6
*/

class ba_prestashop_invoice extends Module
{
    private $demoMode=false;
    public $languagesArr;
    public function __construct()
    {
        require_once "includes/baorderinvoice.php";
        require_once "includes/badeliveryslip.php";
        require_once "includes/bacreditslip.php";
        $this->name = "ba_prestashop_invoice";
        $this->tab = "billing_invoicing";
        $this->version = "1.1.39";
        $this->author = "buy-addons";
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        $this->module_key = '0deba47e596f8932b1610da4e1214d11';
        $this->languagesArr=Language::getLanguages(false);
        parent::__construct();
        if (strpos(_PS_VERSION_, "1.5") === 0) {
            $this->context->controller->addCSS($this->_path.'views/css/style_v1.5.5.0.css');
            $this->context->controller->addJS($this->_path . 'views/js/langueage_click.js');
        }
        $this->displayName = $this->l('Advance Invoice, Delivery, Credit PDF + Custom Number');
        $this->description  = $this->l('Author: buy-addons');
    }

    public function install()
    {
        if (parent::install()===false) {
            return false;
        }
        if ($this->registerHook('actionValidateOrder') == false) {
            return false;
        }
        if ($this->registerHook('actionObjectOrderSlipAddAfter') == false) {
            return false;
        }
        if ($this->registerHook('displayBackOfficeHeader') == false) {
            // since 1.1.36+
            return false;
        }
        $this->saveDefaultConfig();
        return true;
    }
    
    public function hookdisplayBackOfficeHeader($params)
    {
        $controller = Tools::getValue('controller');
        $id_order = (int) Tools::getValue('id_order');
        if (!Tools::isSubmit('vieworder')) {
            return '';
        }
        if ($controller != 'AdminOrders' || empty($id_order)) {
            return '';
        }
        $order = new Order($id_order);
        $documents = $order->getDocuments();
        if (empty($documents)) {
            return '';
        }
        $result = array();
        $current_id_lang = $this->context->language->id;
        foreach ($documents as $item) {
            $class = get_class($item);
            if ($class == 'OrderInvoice') {
                if (isset($item->is_delivery)) {
                    $f = $item->getDeliveryNumberFormatted($current_id_lang, $order->id_shop);
                    $result['delivery_'.$item->id] = $f;
                } else {
                    $result['invoice_'.$item->id] = $item->getInvoiceNumberFormatted($current_id_lang, $order->id_shop);
                }
            }
            if ($class == 'OrderSlip') {
                $f = $item->getCreditSlipsNumberFormatted($current_id_lang, $order->id_shop);
                $result['orderslip_'.$item->id] = $f;
            }
        }
        $result_tpl = Tools::jsonEncode($result);
        $this->smarty->assign('result_tpl', $result_tpl);
        return $this->display(__FILE__, 'views/templates/admin/backoffice.tpl');
    }
    public function hookActionObjectOrderSlipAddAfter($params)
    {
        
        $order_slip = $params['object'];
        $order = new Order($order_slip->id_order);
        $id_shop = $order->id_shop;
        if (empty($order_slip->id)) {
            return ;
        }

        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/includes/helper.php");
        $helper = new BAInvoiceHelper();
        if ($helper->isEnabledCustomNumber('CREDIT') == false) {
            return ;
        }
        return $helper->setCreditSlipsNumber($id_shop, $order_slip);
    }
    public function defaultDescription($param)
    {
        if (!empty($param)) {
            return '<p style="text-align:left;">'.$param.'<p>';
        }
        return '<p style="text-align:center;">--</p>';
    }
    
    public function saveDefaultConfig()
    {
        $sql='
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ba_prestashop_invoice` (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `showShippingInProductList` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `showDiscountInProductList` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `baInvoiceEnableLandscape` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `showPagination` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `description` text COLLATE utf8_unicode_ci NOT NULL,
                `thumbnail` text COLLATE utf8_unicode_ci NOT NULL,
                `header_invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `footer_invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `customize_css` text COLLATE utf8_unicode_ci NOT NULL,
                `numberColumnOfTableTemplaterPro` int(5) NOT NULL,
                `columsTitleJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsContentJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsColorJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsColorBgJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `id_lang` int(5) NOT NULL,
                `useAdminOrClient` int(1) NOT NULL,
                `status` int(1) NOT NULL,
                `id_shop` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `id_shop_group` varchar(255) COLLATE utf8_unicode_ci NOT NULL
            );
            
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ba_prestashop_delivery_slip` (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `showShippingInProductList` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `showDiscountInProductList` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `baInvoiceEnableLandscape` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `showPagination` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `description` text COLLATE utf8_unicode_ci NOT NULL,
                `thumbnail` text COLLATE utf8_unicode_ci NOT NULL,
                `header_invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `footer_invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `customize_css` text COLLATE utf8_unicode_ci NOT NULL,
                `numberColumnOfTableTemplaterPro` int(5) NOT NULL,
                `columsTitleJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsContentJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsColorJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsColorBgJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `id_lang` int(5) NOT NULL,
                `useAdminOrClient` int(1) NOT NULL,
                `status` int(1) NOT NULL,
                `id_shop` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `id_shop_group` varchar(255) COLLATE utf8_unicode_ci NOT NULL
            );
            
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ba_prestashop_credit_slip` (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `showShippingInProductList` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `showDiscountInProductList` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `baInvoiceEnableLandscape` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `showPagination` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `description` text COLLATE utf8_unicode_ci NOT NULL,
                `thumbnail` text COLLATE utf8_unicode_ci NOT NULL,
                `header_invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `footer_invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `customize_css` text COLLATE utf8_unicode_ci NOT NULL,
                `numberColumnOfTableTemplaterPro` int(5) NOT NULL,
                `columsTitleJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsContentJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsColorJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsColorBgJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `id_lang` int(5) NOT NULL,
                `useAdminOrClient` int(1) NOT NULL,
                `status` int(1) NOT NULL,
                `id_shop` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `id_shop_group` varchar(255) COLLATE utf8_unicode_ci NOT NULL
            );

            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ba_prestashop_supplier_slip` (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `baInvoiceEnableLandscape` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `showPagination` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `description` text COLLATE utf8_unicode_ci NOT NULL,
                `thumbnail` text COLLATE utf8_unicode_ci NOT NULL,
                `header_invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `footer_invoice_template` text COLLATE utf8_unicode_ci NOT NULL,
                `customize_css` text COLLATE utf8_unicode_ci NOT NULL,
                `numberColumnOfTableTemplaterPro` int(5) NOT NULL,
                `columsTitleJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsContentJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsColorJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `columsColorBgJson` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `id_lang` int(5) NOT NULL,
                `status` int(1) NOT NULL,
                `id_shop` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `id_shop_group` varchar(255) COLLATE utf8_unicode_ci NOT NULL
            );
            
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ba_template_invoice_grcustumer` (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `id_shop` int(10) COLLATE utf8_unicode_ci NOT NULL,
                `id_lang` int(10) COLLATE utf8_unicode_ci NOT NULL,
                `id_group_customer` int(10) COLLATE utf8_unicode_ci NOT NULL,
                `id_template_invoice` int(10) NOT NULL
            );

            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ba_template_delivery_grcustumer` (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `id_shop` int(10) COLLATE utf8_unicode_ci NOT NULL,
                `id_lang` int(10) COLLATE utf8_unicode_ci NOT NULL,
                `id_group_customer` int(10) COLLATE utf8_unicode_ci NOT NULL,
                `id_template_delivery` int(10) NOT NULL
            );

            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ba_template_credit_grcustumer` (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `id_shop` int(10) COLLATE utf8_unicode_ci NOT NULL,
                `id_lang` int(10) COLLATE utf8_unicode_ci NOT NULL,
                `id_group_customer` int(10) COLLATE utf8_unicode_ci NOT NULL,
                `id_template_credit` int(10) NOT NULL
            );

            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ba_prestashop_invoice_tax` (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `id_order` int(11) NOT NULL,
                `id_product` int(11) NOT NULL,
                `id_tax` int(11) NOT NULL,
                `tax_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `tax_rate` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `tax_amount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `product_qty` int(11) NOT NULL,
                `unit_price_tax_excl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `unit_price_tax_incl` varchar(255) COLLATE utf8_unicode_ci NOT NULL
            );
            ALTER TABLE '._DB_PREFIX_.'order_payment MODIFY COLUMN order_reference  VARCHAR( 32 );
            ALTER TABLE '._DB_PREFIX_.'orders MODIFY COLUMN reference  VARCHAR( 32 );
        ';
        
        Db::getInstance()->query($sql);
        // add number column to ps_order_slip IF NOT EXISTS
        $sql ="SELECT * FROM information_schema.COLUMNS
            WHERE COLUMN_NAME='number' AND TABLE_NAME='"._DB_PREFIX_."order_slip' AND TABLE_SCHEMA='"._DB_NAME_."'";
        $number = Db::getInstance()->getValue($sql);
        if ($number == false) {
            $sql = 'ALTER TABLE '._DB_PREFIX_.'order_slip ADD number INT(11);';
            Db::getInstance()->query($sql);
        }
        /////////////////////
        $languagesArr=Language::getLanguages(false);
        $shopArrayList = Shop::getShops(false);
        foreach ($shopArrayList as $shopArray) {
            // update Setting
            $idsh = $shopArray['id_shop'];

            $setting = '{"invoice_debug":0,"invoice_start_numbering":'.time().',"invoice_number_status":0,
            "invoice_start":1,"invoice_step":1,"invoice_length":6,"invoice_format":"#INVOICE-[counter]\/[d]-[m]-[Y]",
            "invoice_reset":0,"invoice_reset_value":0,"invoice_reset_date":"0",
            "delivery_number_status":0,"delivery_start":1,"delivery_step":1,"delivery_length":6,
            "delivery_format":"#DE-[counter]\/[d]-[m]-[Y]","delivery_reset":0,"delivery_reset_value":0,
            "delivery_reset_date":"0","credit_number_status":0,"credit_start":1,"credit_step":1,
            "credit_length":6,"credit_format":"#CREDIT-[counter]\/[d]-[m]-[Y]","credit_reset":0,
            "credit_reset_value":0,"credit_reset_date":"0","order_number_status":0,"order_start":1,"order_step":1,
            "order_length":6,"order_format":"ORDER-[counter]\/[d]-[m]-[Y]","order_reset":0,"order_reset_value":0,
            "order_reset_date":"0","bapaperinvoice":"A4","invoice_custemp_status":0,
            "deli_custemp_status":0,"cre_custemp_status":0}';
            Configuration::updateValue("invoice_customnumber_setting", $setting, false, null, $idsh);
            Configuration::updateValue("PS_DISABLE_OVERRIDES", 1, false, null, $idsh);
            Configuration::updateGlobalValue("PS_DISABLE_OVERRIDES", 1);
            // insert invoice for language
            $ba_url = Tools::getShopProtocol() . Tools::getServerName() . __PS_BASE_URI__;
            foreach ($languagesArr as $language) {
                $ba_lang =(int) $language['id_lang'];
                $sqlgpcs ="SELECT * FROM ". _DB_PREFIX_ ."group_lang WHERE id_lang = ".$ba_lang;
                $groupcus = Db::getInstance()->ExecuteS($sqlgpcs);
                foreach ($groupcus as $vgroupcus) {
                    Db::getInstance()->insert("ba_template_invoice_grcustumer", array(
                        'id_shop' => $shopArray['id_shop'],
                        'id_lang' => $ba_lang,
                        'id_group_customer' => $vgroupcus['id_group'],
                        'id_template_invoice' => 1,
                    ));
                    Db::getInstance()->insert("ba_template_delivery_grcustumer", array(
                        'id_shop' => $shopArray['id_shop'],
                        'id_lang' => $ba_lang,
                        'id_group_customer' => $vgroupcus['id_group'],
                        'id_template_delivery' => 1,
                    ));
                    Db::getInstance()->insert("ba_template_credit_grcustumer", array(
                        'id_shop' => $shopArray['id_shop'],
                        'id_lang' => $ba_lang,
                        'id_group_customer' => $vgroupcus['id_group'],
                        'id_template_credit' => 1,
                    ));
                }
                $files = scandir(_PS_MODULE_DIR_."/ba_prestashop_invoice/invoice_invoice/");
                foreach ($files as $file) {
                    if (is_dir(_PS_MODULE_DIR_."/ba_prestashop_invoice/invoice_invoice/".$file)==false
                        && $file != "." && $file != "..") {
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        if ($ext == 'xml') {
                            $dirFile = _PS_MODULE_DIR_."/ba_prestashop_invoice/invoice_invoice/".$file;
                            $dataArray = Tools::simplexml_load_file($dirFile);
                            $name = (string)$dataArray->name;
                            $invoice_template=(string)$dataArray->pdf_content;
                            if (!empty($name) && !empty($invoice_template)) {
                                $name = (string)$dataArray->name;
                                $description=Tools::htmlentitiesUTF8(strip_tags((string)$dataArray->description));
                                $thumbnail=strip_tags((string)$dataArray->thumbnail);
                                
                                $vali_pdf_content = (string)$dataArray->pdf_content;
                                $dataArray->pdf_content= str_replace('PREFIX_URL', $ba_url, $vali_pdf_content);
                                $invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_content);
                                $vali_pdf_header = (string)$dataArray->pdf_header;
                                $dataArray->pdf_header= str_replace('PREFIX_URL', $ba_url, $vali_pdf_header);
                                $header_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_header);
                                $vali_pdf_footer = (string)$dataArray->pdf_footer;
                                $dataArray->pdf_footer = str_replace('PREFIX_URL', $ba_url, $vali_pdf_footer);
                                $footer_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_footer);
                                $customize_css=strip_tags(Tools::htmlentitiesUTF8((string)$dataArray->customize_css));
                                
                                $numberColumn = (int) $dataArray->products_template[0]['columns_size'];
                                $columsColorBg = array();
                                $columsColorArray = array();
                                $columsContentArray = array();
                                $columnsTitleArray = array();
                                for ($i = 0; $i < $numberColumn; $i++) {
                                    $columnsTitleArray[] = (string)$dataArray->products_template->col[$i]->col_title;
                                    $columsContentArray[] = (string)$dataArray->products_template->col[$i]->col_content;
                                    $columsColorArray[]=(string)$dataArray->products_template->col[$i]->col_title_color;
                                    $columsColorBg[]=(string)$dataArray->products_template->col[$i]->col_title_bgcolor;
                                }
                                $columsTitleJson =  Tools::jsonEncode($columnsTitleArray);
                                $columsContentJson =  Tools::jsonEncode($columsContentArray);
                                $columsColorJson =  Tools::jsonEncode($columsColorArray);
                                $columsColorBgJson =  Tools::jsonEncode($columsColorBg);
                                $showShippingInProductList=(string)$dataArray->show_shipping;
                                $showDiscountInProductList=(string)$dataArray->show_discount;
                                $baInvoiceEnableLandscape=(string)$dataArray->enable_landscape;
                                $showPagination=(string)$dataArray->show_pagination;
                                $status=(int)$dataArray->status;
                                $useAdminOrClient=(int)$dataArray->useAdminOrClient;
                                Db::getInstance()->insert("ba_prestashop_invoice", array(
                                    'name' => pSQL($name),
                                    'description' => pSQL($description),
                                    'thumbnail' => Tools::htmlentitiesUTF8($thumbnail),
                                    'showShippingInProductList' => $showShippingInProductList,
                                    'showDiscountInProductList' => $showDiscountInProductList,
                                    'baInvoiceEnableLandscape' => $baInvoiceEnableLandscape,
                                    'showPagination' => $showPagination,
                                    'header_invoice_template' => $header_invoice_template,
                                    'invoice_template' => $invoice_template,
                                    'footer_invoice_template' => $footer_invoice_template,
                                    'customize_css' => $customize_css,
                                    'numberColumnOfTableTemplaterPro' => $numberColumn,
                                    'columsTitleJson' => $columsTitleJson,
                                    'columsContentJson' => $columsContentJson,
                                    'columsColorJson' => $columsColorJson,
                                    'columsColorBgJson' => $columsColorBgJson,
                                    'id_lang' => $ba_lang,
                                    'useAdminOrClient' => (int) $useAdminOrClient,
                                    'status' => (int) $status,
                                    'id_shop' => $shopArray['id_shop'],
                                    'id_shop_group' => $shopArray['id_shop_group']
                                ));
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
            foreach ($languagesArr as $language) {
                $ba_lang =(int) $language['id_lang'];
                $files = scandir(_PS_MODULE_DIR_."/ba_prestashop_invoice/delivery_invoice/");
                foreach ($files as $file) {
                    if (is_dir(_PS_MODULE_DIR_."/ba_prestashop_invoice/delivery_invoice/".$file)==false
                        && $file != "." && $file != "..") {
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        if ($ext == 'xml') {
                            $dirFile = _PS_MODULE_DIR_."/ba_prestashop_invoice/delivery_invoice/".$file;
                            $dataArray = Tools::simplexml_load_file($dirFile);
                            $name = (string)$dataArray->name;
                            $invoice_template=(string)$dataArray->pdf_content;
                            if (!empty($name) && !empty($invoice_template)) {
                                $name = (string)$dataArray->name;
                                $description=Tools::htmlentitiesUTF8(strip_tags((string)$dataArray->description));
                                $thumbnail=strip_tags((string)$dataArray->thumbnail);
                               
                                $vali_pdf_content = (string)$dataArray->pdf_content;
                                $dataArray->pdf_content= str_replace('PREFIX_URL', $ba_url, $vali_pdf_content);
                                $invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_content);
                                $vali_pdf_header = (string)$dataArray->pdf_header;
                                $dataArray->pdf_header= str_replace('PREFIX_URL', $ba_url, $vali_pdf_header);
                                $header_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_header);
                                $vali_pdf_footer = (string)$dataArray->pdf_footer;
                                $dataArray->pdf_footer = str_replace('PREFIX_URL', $ba_url, $vali_pdf_footer);
                                $footer_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_footer);
                                $customize_css=strip_tags(Tools::htmlentitiesUTF8((string)$dataArray->customize_css));
                                
                                $numberColumn = (int) $dataArray->products_template[0]['columns_size'];
                                $columsColorBg = array();
                                $columsColorArray = array();
                                $columsContentArray = array();
                                $columnsTitleArray = array();
                                for ($i = 0; $i < $numberColumn; $i++) {
                                    $columnsTitleArray[] = (string)$dataArray->products_template->col[$i]->col_title;
                                    $columsContentArray[] = (string)$dataArray->products_template->col[$i]->col_content;
                                    $columsColorArray[]=(string)$dataArray->products_template->col[$i]->col_title_color;
                                    $columsColorBg[]=(string)$dataArray->products_template->col[$i]->col_title_bgcolor;
                                }
                                $columsTitleJson =  Tools::jsonEncode($columnsTitleArray);
                                $columsContentJson =  Tools::jsonEncode($columsContentArray);
                                $columsColorJson =  Tools::jsonEncode($columsColorArray);
                                $columsColorBgJson =  Tools::jsonEncode($columsColorBg);
                                $showShippingInProductList=(string)$dataArray->show_shipping;
                                $showDiscountInProductList=(string)$dataArray->show_discount;
                                $baInvoiceEnableLandscape=(string)$dataArray->enable_landscape;
                                $showPagination=(string)$dataArray->show_pagination;
                                $status=(int)$dataArray->status;
                                $useAdminOrClient=(int)$dataArray->useAdminOrClient;
                                Db::getInstance()->insert("ba_prestashop_delivery_slip", array(
                                    'name' => pSQL($name),
                                    'description' => pSQL($description),
                                    'thumbnail' => Tools::htmlentitiesUTF8($thumbnail),
                                    'showShippingInProductList' => $showShippingInProductList,
                                    'showDiscountInProductList' => $showDiscountInProductList,
                                    'baInvoiceEnableLandscape' => $baInvoiceEnableLandscape,
                                    'showPagination' => $showPagination,
                                    'header_invoice_template' => $header_invoice_template,
                                    'invoice_template' => $invoice_template,
                                    'footer_invoice_template' => $footer_invoice_template,
                                    'customize_css' => $customize_css,
                                    'numberColumnOfTableTemplaterPro' => $numberColumn,
                                    'columsTitleJson' => $columsTitleJson,
                                    'columsContentJson' => $columsContentJson,
                                    'columsColorJson' => $columsColorJson,
                                    'columsColorBgJson' => $columsColorBgJson,
                                    'id_lang' => $ba_lang,
                                    'useAdminOrClient' => (int) $useAdminOrClient,
                                    'status' => (int) $status,
                                    'id_shop' => $shopArray['id_shop'],
                                    'id_shop_group' => $shopArray['id_shop_group']
                                ));
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
            foreach ($languagesArr as $language) {
                $ba_lang =(int) $language['id_lang'];
                $files = scandir(_PS_MODULE_DIR_."/ba_prestashop_invoice/credit_invoice/");
                foreach ($files as $file) {
                    if (is_dir(_PS_MODULE_DIR_."/ba_prestashop_invoice/credit_invoice/".$file)==false
                        && $file != "." && $file != "..") {
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        if ($ext == 'xml') {
                            $dirFile = _PS_MODULE_DIR_."/ba_prestashop_invoice/credit_invoice/".$file;
                            $dataArray = Tools::simplexml_load_file($dirFile);
                            $name = (string)$dataArray->name;
                            $invoice_template=(string)$dataArray->pdf_content;
                            if (!empty($name) && !empty($invoice_template)) {
                                $name = (string)$dataArray->name;
                                $description=Tools::htmlentitiesUTF8(strip_tags((string)$dataArray->description));
                                $thumbnail=strip_tags((string)$dataArray->thumbnail);
                                
                                $vali_pdf_content = (string)$dataArray->pdf_content;
                                $dataArray->pdf_content= str_replace('PREFIX_URL', $ba_url, $vali_pdf_content);
                                $invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_content);
                                $vali_pdf_header = (string)$dataArray->pdf_header;
                                $dataArray->pdf_header= str_replace('PREFIX_URL', $ba_url, $vali_pdf_header);
                                $header_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_header);
                                $vali_pdf_footer = (string)$dataArray->pdf_footer;
                                $dataArray->pdf_footer = str_replace('PREFIX_URL', $ba_url, $vali_pdf_footer);
                                $footer_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_footer);
                                $customize_css=strip_tags(Tools::htmlentitiesUTF8((string)$dataArray->customize_css));
                                
                                $numberColumn = (int) $dataArray->products_template[0]['columns_size'];
                                $columsColorBg = array();
                                $columsColorArray = array();
                                $columsContentArray = array();
                                $columnsTitleArray = array();
                                for ($i = 0; $i < $numberColumn; $i++) {
                                    $columnsTitleArray[] = (string)$dataArray->products_template->col[$i]->col_title;
                                    $columsContentArray[] = (string)$dataArray->products_template->col[$i]->col_content;
                                    $columsColorArray[]=(string)$dataArray->products_template->col[$i]->col_title_color;
                                    $columsColorBg[]=(string)$dataArray->products_template->col[$i]->col_title_bgcolor;
                                }
                                $columsTitleJson =  Tools::jsonEncode($columnsTitleArray);
                                $columsContentJson =  Tools::jsonEncode($columsContentArray);
                                $columsColorJson =  Tools::jsonEncode($columsColorArray);
                                $columsColorBgJson =  Tools::jsonEncode($columsColorBg);
                                $showShippingInProductList=(string)$dataArray->show_shipping;
                                $showDiscountInProductList=(string)$dataArray->show_discount;
                                $baInvoiceEnableLandscape=(string)$dataArray->enable_landscape;
                                $showPagination=(string)$dataArray->show_pagination;
                                $status=(int)$dataArray->status;
                                $useAdminOrClient=(int)$dataArray->useAdminOrClient;
                                Db::getInstance()->insert("ba_prestashop_credit_slip", array(
                                    'name' => pSQL($name),
                                    'description' => pSQL($description),
                                    'thumbnail' => Tools::htmlentitiesUTF8($thumbnail),
                                    'showShippingInProductList' => $showShippingInProductList,
                                    'showDiscountInProductList' => $showDiscountInProductList,
                                    'baInvoiceEnableLandscape' => $baInvoiceEnableLandscape,
                                    'showPagination' => $showPagination,
                                    'header_invoice_template' => $header_invoice_template,
                                    'invoice_template' => $invoice_template,
                                    'footer_invoice_template' => $footer_invoice_template,
                                    'customize_css' => $customize_css,
                                    'numberColumnOfTableTemplaterPro' => $numberColumn,
                                    'columsTitleJson' => $columsTitleJson,
                                    'columsContentJson' => $columsContentJson,
                                    'columsColorJson' => $columsColorJson,
                                    'columsColorBgJson' => $columsColorBgJson,
                                    'id_lang' => $ba_lang,
                                    'useAdminOrClient' => (int) $useAdminOrClient,
                                    'status' => (int) $status,
                                    'id_shop' => $shopArray['id_shop'],
                                    'id_shop_group' => $shopArray['id_shop_group']
                                ));
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
            foreach ($languagesArr as $language) {
                $ba_lang =(int) $language['id_lang'];
                $files = scandir(_PS_MODULE_DIR_."/ba_prestashop_invoice/supplier_inovice/");
                foreach ($files as $file) {
                    if (is_dir(_PS_MODULE_DIR_."/ba_prestashop_invoice/supplier_inovice/".$file)==false
                        && $file != "." && $file != "..") {
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        if ($ext == 'xml') {
                            $dirFile = _PS_MODULE_DIR_."/ba_prestashop_invoice/supplier_inovice/".$file;
                            $dataArray = Tools::simplexml_load_file($dirFile);
                            $name = (string)$dataArray->name;
                            $invoice_template=(string)$dataArray->pdf_content;
                            if (!empty($name) && !empty($invoice_template)) {
                                $name = (string)$dataArray->name;
                                $description=Tools::htmlentitiesUTF8(strip_tags((string)$dataArray->description));
                                $thumbnail=strip_tags((string)$dataArray->thumbnail);
                                
                                $vali_pdf_content = (string)$dataArray->pdf_content;
                                $dataArray->pdf_content= str_replace('PREFIX_URL', $ba_url, $vali_pdf_content);
                                $invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_content);
                                $vali_pdf_header = (string)$dataArray->pdf_header;
                                $dataArray->pdf_header= str_replace('PREFIX_URL', $ba_url, $vali_pdf_header);
                                $header_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_header);
                                $vali_pdf_footer = (string)$dataArray->pdf_footer;
                                $dataArray->pdf_footer = str_replace('PREFIX_URL', $ba_url, $vali_pdf_footer);
                                $footer_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_footer);
                                $customize_css=strip_tags(Tools::htmlentitiesUTF8((string)$dataArray->customize_css));
                                $numberColumn = (int) $dataArray->products_template[0]['columns_size'];
                                $columsColorBg = array();
                                $columsColorArray = array();
                                $columsContentArray = array();
                                $columnsTitleArray = array();
                                for ($i = 0; $i < $numberColumn; $i++) {
                                    $columnsTitleArray[] = (string)$dataArray->products_template->col[$i]->col_title;
                                    $columsContentArray[] = (string)$dataArray->products_template->col[$i]->col_content;
                                    $columsColorArray[]=(string)$dataArray->products_template->col[$i]->col_title_color;
                                    $columsColorBg[]=(string)$dataArray->products_template->col[$i]->col_title_bgcolor;
                                }
                                $columsTitleJson =  Tools::jsonEncode($columnsTitleArray);
                                $columsContentJson =  Tools::jsonEncode($columsContentArray);
                                $columsColorJson =  Tools::jsonEncode($columsColorArray);
                                $columsColorBgJson =  Tools::jsonEncode($columsColorBg);
                                $showShippingInProductList=(string)$dataArray->show_shipping;
                                $showDiscountInProductList=(string)$dataArray->show_discount;
                                $baInvoiceEnableLandscape=(string)$dataArray->enable_landscape;
                                $showPagination=(string)$dataArray->show_pagination;
                                $status=(int)$dataArray->status;
                                $useAdminOrClient=(int)$dataArray->useAdminOrClient;
                                Db::getInstance()->insert("ba_prestashop_supplier_slip", array(
                                    'name' => pSQL($name),
                                    'description' => pSQL($description),
                                    'thumbnail' => Tools::htmlentitiesUTF8($thumbnail),
                                    /*'showShippingInProductList' => $showShippingInProductList,
                                    'showDiscountInProductList' => $showDiscountInProductList,*/
                                    'baInvoiceEnableLandscape' => $baInvoiceEnableLandscape,
                                    'showPagination' => $showPagination,
                                    'header_invoice_template' => $header_invoice_template,
                                    'invoice_template' => $invoice_template,
                                    'footer_invoice_template' => $footer_invoice_template,
                                    'customize_css' => $customize_css,
                                    'numberColumnOfTableTemplaterPro' => $numberColumn,
                                    'columsTitleJson' => $columsTitleJson,
                                    'columsContentJson' => $columsContentJson,
                                    'columsColorJson' => $columsColorJson,
                                    'columsColorBgJson' => $columsColorBgJson,
                                    'id_lang' => $ba_lang,
                                    'status' => (int) $status,
                                    'id_shop' => $shopArray['id_shop'],
                                    'id_shop_group' => $shopArray['id_shop_group']
                                ));
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
    }
    
    public function fillLanguageName($id_lang)
    {
        foreach ($this->languagesArr as $v) {
            if ($v["id_lang"]==$id_lang) {
                return $v["name"];
            }
        }
    }
    
    public function getImageToHelpperList($imageTags)
    {
        if (!empty($imageTags)) {
            return '<a class="riverroad" href="#" title="" img="'.Tools::htmlentitiesDecodeUTF8($imageTags).'">'
            ."<img src='".__PS_BASE_URI__."modules/ba_prestashop_invoice/views/img/img_invoice/"
            .Tools::htmlentitiesDecodeUTF8($imageTags)."'></a>";
        }
        return '<p style="text-align:center;">--</p>';
    }
    
    public function uninstall()
    {
        $sql="
            DROP TABLE IF EXISTS "._DB_PREFIX_."ba_prestashop_invoice;
            DROP TABLE IF EXISTS "._DB_PREFIX_."ba_prestashop_delivery_slip;
            DROP TABLE IF EXISTS "._DB_PREFIX_."ba_prestashop_credit_slip;
            DROP TABLE IF EXISTS "._DB_PREFIX_."ba_prestashop_credit_slip;
            DROP TABLE IF EXISTS "._DB_PREFIX_."ba_template_invoice_grcustumer;
            DROP TABLE IF EXISTS "._DB_PREFIX_."ba_template_delivery_grcustumer;
            DROP TABLE IF EXISTS "._DB_PREFIX_."ba_template_credit_grcustumer;
            DROP TABLE IF EXISTS "._DB_PREFIX_."ba_prestashop_supplier_slip;
        ";
        Db::getInstance()->query($sql);
        if (parent::uninstall() == false) {
            return false;
        }
        return true;
    }
    public function urlExists($url)
    {
        $url = str_replace("http://", "", $url);
        if (strstr($url, "/")) {
            $url = explode("/", $url, 2);
            $url[1] = "/".$url[1];
        } else {
            $url = array($url, "/");
        }

        $fh = fsockopen($url[0], 80);
        if ($fh) {
            fputs($fh, "GET ".$url[1]." HTTP/1.1\nHost:".$url[0]."\n\n");
            if (fread($fh, 22) == "HTTP/1.1 404 Not Found") {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    
    public function getContent()
    {
        $this->context->controller->addJS($this->_path . 'views/js/jscolor/jscolor.js');
        $this->context->controller->addJS($this->_path . 'views/js/ajaxpreview.js');
        $this->context->controller->addJS($this->_path . 'views/js/showmoretoken.js');
        $this->context->controller->addCSS($this->_path . 'views/css/style.css');
        $iso=$this->context->language->iso_code;
        $this->context->controller->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
        
        $html='
            <script type="text/javascript">    
                var iso = \''.(file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'\' ;
                var pathCSS = \''._THEME_CSS_DIR_.'\' ;
                var ad = \''.dirname($_SERVER['PHP_SELF']).'\' ;
                var baseUrl = \''.Tools::getShopProtocol().Tools::getHttpHost().__PS_BASE_URI__.'\' ;
                var mess_success = \''.$this->l('The PDF File is successfully generated.').'\' ;
                var mess_clickpreview = \''.$this->l('Click Here to Preview').'\' ;
                var mess_nothanks = "'.$this->l("No thanks. I'd rather miss out.").'" ;
                var batoken = "'.$this->cookiekeymodule().'" ;
            </script>
            <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
            <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce.inc.js"></script>
            <script language="javascript" type="text/javascript">
                id_language = Number('.$this->context->language->id.');
                tinySetup();
            </script>
        ';
        $token=Tools::getAdminTokenLite('AdminModules');
        $this->smarty->assign('token', $token);
        $bamodule=AdminController::$currentIndex;
        $this->smarty->assign('bamodule', $bamodule);
        $this->smarty->assign('configure', $this->name);
        
        $checkver17 = Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>');
        $this->smarty->assign('checkver17', $checkver17);
        $ba_lang = $this->context->language->id;
        $getBaLang=Tools::getValue('ba_lang');
        if ($getBaLang != "") {
            $ba_lang = (int) Tools::getValue('ba_lang');
        }
        $taskBar = 'orderinvoice';
        if (Tools::getValue('task') != false) {
            $taskBar = Tools::getValue('task');
        }
        $this->smarty->assign('taskbar', $taskBar);
        $this->smarty->assign('ba_lang', $ba_lang);
        $buttonDemoArr = array(
            'submitBaSave',
            'submitBaSaveAndStay',
            'import',
            'statusba_prestashop_invoice_invoice',
            'statusba_prestashop_invoice_deliveryslip',
            'statusba_prestashop_invoice_creditslip',
            'duplicateba_prestashop_invoice_invoice',
            'deleteba_prestashop_invoice_invoice',
            'duplicateba_prestashop_invoice_deliveryslip',
            'deleteba_prestashop_invoice_deliveryslip',
            'duplicateba_prestashop_invoice_creditslip',
            'deleteba_prestashop_invoice_creditslip',
            'submitBulkdeleteba_prestashop_invoice_invoice',
            'submitBulkdeleteba_prestashop_invoice_deliveryslip',
            'submitBulkdeleteba_prestashop_invoice_creditslip',
            'saveCustomNumber',
            'submitBaSupSave'
        );
        if ($this->demoMode==true) {
            foreach ($buttonDemoArr as $buttonDemo) {
                if (Tools::isSubmit($buttonDemo)) {
                    Tools::redirectAdmin($bamodule.'&token='.$token.'&configure='.$this->name.'&demoMode=1');
                }
            }
        }
        $this->smarty->assign('demoMode', Tools::getValue('demoMode'));
        if (Tools::getValue('savesupp') == "1") {
            $html .= $this->displayConfirmation($this->l('Successful Update'));
        }
        if (Tools::getValue('submitBaSave') == "1" || Tools::getValue('msg') == "1") {
            $html .= $this->displayConfirmation($this->l('Successful Update'));
        }
        if (Tools::getValue('importerror') == "2") {
            $html.=$this->displayError($this->l('<name>, <pdf_content> tags is NOT empty'));
        } elseif (Tools::getValue('importerror') == "1") {
            $html.=$this->displayError($this->l('Your file must be a *.xml file'));
        }
        $html .= $this->display(__FILE__, 'views/templates/admin/taskbar.tpl');
        // Save Custom Number Setting
        if (Tools::isSubmit('saveCustomNumber')) {
            $this->saveCustomSetting();
        }
        if ($taskBar == "orderinvoice") {
            $this->context->controller->addJqueryUI('ui.tooltip');
            $baOrderInvoice = new BaOrderInvoice();
            $html .= $baOrderInvoice->caseInvoice();
        } elseif ($taskBar == "deliveryslip") {
            $this->context->controller->addJqueryUI('ui.tooltip');
            $baDeliverySlip = new BaDeliverySlip();
            $html .= $baDeliverySlip->caseDeliverySlip();
        } elseif ($taskBar == "creditslip") {
            $this->context->controller->addJqueryUI('ui.tooltip');
            $baCreditSlip = new BaCreditSlip();
            $html .= $baCreditSlip->caseCreditSlip();
        } elseif ($taskBar == "supplier") {
            $id_shop = $this->context->shop->id;
            $id_lang = $this->context->language->id;
            $toolBarBtn=array();
            $this->saveDataSupplier();
            $toolBarBtn[] = array(
                'imgclass' => 'preview',
                'href'     => 'javascript:void(0)',
                'desc'     => 'Preview',
            );
            $lang_default = Configuration::get('PS_LANG_DEFAULT');
            $sqltabsupp = "SELECT * FROM ". _DB_PREFIX_ ."ba_prestashop_supplier_slip WHERE id_shop = ".(int)$id_shop;
            $tabsuppall = Db::getInstance()->ExecuteS($sqltabsupp);
            $sqltabsupp .= ' AND id_lang = '.(int)$id_lang;
            $tabsupp = Db::getInstance()->ExecuteS($sqltabsupp);
            $arrlanguages = Language::getLanguages(false);
            $iso_lang_default = Language::getLanguage($lang_default)['iso_code'];
            $this->smarty->assign('languages_select', $arrlanguages);
            $this->smarty->assign('iso_lang_default', $iso_lang_default);
            $this->smarty->assign('lang_default', $lang_default);
            $this->smarty->assign('tabsupp', $tabsupp[0]);
            $this->smarty->assign('tabsuppall', $tabsuppall);
            $this->smarty->assign('toolBarBtn', $toolBarBtn);
            $html .= $this->display(__FILE__, 'views/templates/admin/temp_supplier.tpl');
        } else {
            $id_shop = $this->context->shop->id;
            $customnumber_setting = Configuration::get("invoice_customnumber_setting", null, null, (int)$id_shop);
            $customnumber_setting = Tools::jsonDecode($customnumber_setting, true);
            /// format date
            $id_lang = $this->context->language->id;
            $lang = new language($id_lang);
            $dateformat = $lang->date_format_lite;
            /////////////
            $date = $customnumber_setting['invoice_reset_date'];
            $customnumber_setting['invoice_reset_date'] = date($dateformat, $date);
            $date = $customnumber_setting['invoice_start_numbering'];
            $customnumber_setting['invoice_start_numbering'] = date($dateformat, $date);
            $date = $customnumber_setting['delivery_reset_date'];
            $customnumber_setting['delivery_reset_date'] = date($dateformat, $date);
            $date = $customnumber_setting['credit_reset_date'];
            $customnumber_setting['credit_reset_date'] = date($dateformat, $date);
            $date = $customnumber_setting['order_reset_date'];
            $customnumber_setting['order_reset_date'] = date($dateformat, $date);
            ///////////////
            $sqltabinvoicecus = "SELECT * FROM ". _DB_PREFIX_ ."ba_template_invoice_grcustumer WHERE id_lang = ";
            $sqltabinvoicecus .= (int)$id_lang.' AND id_shop = '.(int)$id_shop;
            $tabinvoicecus = Db::getInstance()->ExecuteS($sqltabinvoicecus);
            $tabinvoicecus1 = array();
            foreach ($tabinvoicecus as $vtabinvoicecus) {
                $tabinvoicecus1[$vtabinvoicecus['id_group_customer']] = $vtabinvoicecus;
            }
            $sqltabdelicus = "SELECT * FROM ". _DB_PREFIX_ ."ba_template_delivery_grcustumer WHERE id_lang = ";
            $sqltabdelicus .= (int)$id_lang.' AND id_shop = '.(int)$id_shop;
            $tabdelicus = Db::getInstance()->ExecuteS($sqltabdelicus);
            $tabdelicus1 = array();
            foreach ($tabdelicus as $vtabdelicus) {
                $tabdelicus1[$vtabdelicus['id_group_customer']] = $vtabdelicus;
            }
            $sqltabcrecus = "SELECT * FROM ". _DB_PREFIX_ ."ba_template_credit_grcustumer WHERE id_lang = ";
            $sqltabcrecus .= (int)$id_lang.' AND id_shop = '.(int)$id_shop;
            $tabcrecus = Db::getInstance()->ExecuteS($sqltabcrecus);
            $tabcrecus1 = array();
            foreach ($tabcrecus as $vtabcrecus) {
                $tabcrecus1[$vtabcrecus['id_group_customer']] = $vtabcrecus;
            }
            $sqlgetinvoice = "SELECT * FROM ". _DB_PREFIX_ ."ba_prestashop_invoice WHERE id_lang = ";
            $sqlgetinvoice .= (int)$id_lang.' AND id_shop = '.(int)$id_shop;
            $bagetinvoice = Db::getInstance()->ExecuteS($sqlgetinvoice);
            $sqlgetdeli = "SELECT * FROM ". _DB_PREFIX_ ."ba_prestashop_delivery_slip WHERE id_lang = ";
            $sqlgetdeli .= (int)$id_lang.' AND id_shop = '.(int)$id_shop;
            $bagetdeli = Db::getInstance()->ExecuteS($sqlgetdeli);
            $sqlgetcre = "SELECT * FROM ". _DB_PREFIX_ ."ba_prestashop_credit_slip WHERE id_lang = ";
            $sqlgetcre .= (int)$id_lang.' AND id_shop = '.(int)$id_shop;
            $bagetcre = Db::getInstance()->ExecuteS($sqlgetcre);
            /*echo '<pre>';var_dump($bagetcre);die();*/
            /*var_dump($bagetinvoice[0]['name']);die();*/
            $sqlgpcs ="SELECT * FROM ". _DB_PREFIX_ ."group_lang WHERE id_lang = ".(int)$id_lang;
            $groupcus = Db::getInstance()->ExecuteS($sqlgpcs);
            $this->smarty->assign('setting', $customnumber_setting);
            $this->smarty->assign('groupcus', $groupcus);
            $this->smarty->assign('bagetinvoice', $bagetinvoice);
            $this->smarty->assign('bagetdeli', $bagetdeli);
            $this->smarty->assign('bagetcre', $bagetcre);
            $this->smarty->assign('tabinvoicecus1', $tabinvoicecus1);
            $this->smarty->assign('tabdelicus1', $tabdelicus1);
            $this->smarty->assign('tabcrecus1', $tabcrecus1);
            $html .= '
                        <script type="text/javascript">
                            var dateFormat1=\'' . $this->formatDatePicket() . '\';
                        </script>
                    ';
            
            $html .= $this->display(__FILE__, 'views/templates/admin/customnumber.tpl');
        }
        
        //$html .= $this->display(__FILE__, 'views/templates/admin/order_invoice/form.tpl');
        return $html;
    }
    public function saveDataSupplier()
    {
        if (Tools::isSubmit('submitBaSupSave')) {
            /*$id_lang = $this->context->language->id;*/
            $id_shop = $this->context->shop->id;
            /*$db = Db::getInstance();*/
            /*$sel_language = Tools::getValue('sel_language');*/
            $description=Tools::htmlentitiesUTF8(strip_tags(Tools::getValue('descriptionInvoice')));
            $invoice_template=Tools::htmlentitiesUTF8(Tools::getValue('invoice_template'));
            $header_invoice_template=Tools::htmlentitiesUTF8(Tools::getValue('header_invoice_template'));
            $footer_invoice_template=Tools::htmlentitiesUTF8(Tools::getValue('footer_invoice_template'));
            $customize_css=strip_tags(Tools::htmlentitiesUTF8(Tools::getValue('customize_css')));
            $basuppEnableLandscape = tools::getValue('basuppEnableLandscape');
            $showsuppPagination = tools::getValue('showsuppPagination');
            $status_supp = tools::getValue('status_supp');
            
            $colums_content = Tools::getValue('colums_content');
            $columsContentJson = Tools::jsonEncode($colums_content);
            
            $colums_color = Tools::getValue('colums_color');
            $columsColorJson = Tools::jsonEncode($colums_color);
            $numberColumn = (int) Tools::getValue('numberColumnOfTableTemplaterPro');
            $colums_bgcolor = Tools::getValue('colums_bgcolor');
            $columsColorBgJson = Tools::jsonEncode($colums_bgcolor);
            $basqldelete = "DELETE FROM ". _DB_PREFIX_ ."ba_prestashop_supplier_slip WHERE id_shop=".(int)$id_shop;
            Db::getInstance()->query($basqldelete);
            foreach ($invoice_template as $kinvoice_template => $vinvoice_template) {
                $colums_title = Tools::getValue('colums_title');
                $columsTitleJson = $this->enNonlatin($colums_title[$kinvoice_template]);
                $updatesupptemp = "INSERT INTO ". _DB_PREFIX_ ."ba_prestashop_supplier_slip(description,showPagination,
                baInvoiceEnableLandscape,status,customize_css,invoice_template,header_invoice_template,
                footer_invoice_template,numberColumnOfTableTemplaterPro,columsTitleJson,columsContentJson
                ,columsColorJson,columsColorBgJson,id_shop,id_lang) VALUES ('".pSQL($description).
                "',".(int)$showsuppPagination.",".(int)$basuppEnableLandscape.",".(int)$status_supp.",'".
                pSQL($customize_css)."','".pSQL($vinvoice_template)."','".
                pSQL($header_invoice_template[$kinvoice_template])."','".
                pSQL($footer_invoice_template[$kinvoice_template])."','".
                pSQL($numberColumn)."','".pSQL($columsTitleJson)."','".
                pSQL($columsContentJson)."','".pSQL($columsColorJson)."','".pSQL($columsColorBgJson)."',".
                (int)$id_shop.",".(int)$kinvoice_template.")";
                /*var_dump($updatesupptemp);die;*/
                Db::getInstance()->query($updatesupptemp);
            }
            $token=Tools::getAdminTokenLite('AdminModules');
            $bamodule=AdminController::$currentIndex;
            Tools::redirectAdmin($bamodule.'&task=supplier&token='.$token.'&configure='.$this->name.'&savesupp=1');
        }
    }
    public function saveCustomSetting()
    {
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $baidtempinvoice = Tools::getValue("baidtempinvoice");
        $baidtempdeli = Tools::getValue("baidtempdeli");
        $baidtempcre = Tools::getValue("baidtempcre");
        foreach ($baidtempinvoice as $kbaidtempinvoice => $vbaidtempinvoice) {
            $addinvoicegrcs = "UPDATE ". _DB_PREFIX_ ."ba_template_invoice_grcustumer SET id_template_invoice = ";
            $addinvoicegrcs .= (int)$vbaidtempinvoice." WHERE id_shop = ".(int)$id_shop." AND id_lang = ";
            $addinvoicegrcs .= (int)$id_lang." AND id_group_customer = ".(int)$kbaidtempinvoice;
            Db::getInstance()->query($addinvoicegrcs);
        };
        foreach ($baidtempdeli as $kbaidtempdeli => $vbaidtempdeli) {
            $adddeligrcs = "UPDATE ". _DB_PREFIX_ ."ba_template_delivery_grcustumer SET id_template_delivery = ";
            $adddeligrcs .= (int)$vbaidtempdeli." WHERE id_shop = ".(int)$id_shop." AND id_lang = ";
            $adddeligrcs .= (int)$id_lang." AND id_group_customer = ".(int)$kbaidtempdeli;
            Db::getInstance()->query($adddeligrcs);
        };
        foreach ($baidtempcre as $kbaidtempcre => $vbaidtempcre) {
            $addcregrcs = "UPDATE ". _DB_PREFIX_ ."ba_template_credit_grcustumer SET id_template_credit = ";
            $addcregrcs .= (int)$vbaidtempcre." WHERE id_shop = ".(int)$id_shop." AND id_lang = ";
            $addcregrcs .= (int)$id_lang." AND id_group_customer = ".(int)$kbaidtempcre;
            Db::getInstance()->query($addcregrcs);
        };
        $lang = new language($id_lang);
        $dateformat = $lang->date_format_lite;
        // convert date to timestamp
        $invoice_reset_date = Tools::getValue("invoice_reset_date", '');
        $invoice_reset_date = strtotime($this->formatDate($invoice_reset_date, $dateformat));
        
        $start_numbering = Tools::getValue("start_numbering", '');
        $start_numbering = strtotime($this->formatDate($start_numbering, $dateformat));
        
        $delivery_reset_date = Tools::getValue("delivery_reset_date", '');
        $delivery_reset_date = strtotime($this->formatDate($delivery_reset_date, $dateformat));
        
        $credit_reset_date = Tools::getValue("credit_reset_date", '');
        $credit_reset_date = strtotime($this->formatDate($credit_reset_date, $dateformat));
        
        $order_reset_date = Tools::getValue("order_reset_date", '');
        $order_reset_date = strtotime($this->formatDate($order_reset_date, $dateformat));
        //var_dump($invoice_reset_date);die;
        $data = array(
            'invoice_debug' => (int) Tools::getValue("invoice_debug", 0),
            'invoice_start_numbering' => (int) $start_numbering,
            'invoice_number_status' => (int) Tools::getValue("invoice_number_status", 0),
            'invoice_start' => (int) Tools::getValue("invoice_start", 1),
            'invoice_step' => (int) Tools::getValue("invoice_step", 1),
            'invoice_length' => (int) Tools::getValue("invoice_length", 6),
            'invoice_format' => pSQL(Tools::getValue("invoice_format", '#IN[counter]')),
            'invoice_reset' => (int) Tools::getValue("invoice_reset", 0),
            'invoice_reset_value' => (int) Tools::getValue("invoice_reset_value", ''),
            'invoice_reset_date' => (int) $invoice_reset_date,
            'delivery_number_status' => (int) Tools::getValue("delivery_number_status", 0),
            'delivery_start' => (int) Tools::getValue("delivery_start", 1),
            'delivery_step' => (int) Tools::getValue("delivery_step", 1),
            'delivery_length' => (int) Tools::getValue("delivery_length", 6),
            'delivery_format' => pSQL(Tools::getValue("delivery_format", '#DE[counter]')),
            'delivery_reset' => (int) Tools::getValue("delivery_reset", 0),
            'delivery_reset_value' => (int) Tools::getValue("delivery_reset_value", 0),
            'delivery_reset_date' => (int) $delivery_reset_date,
            'credit_number_status' => (int) Tools::getValue("credit_number_status", 0),
            'credit_start' => (int) Tools::getValue("credit_start", 1),
            'credit_step' => (int) Tools::getValue("credit_step", 1),
            'credit_length' => (int) Tools::getValue("credit_length", 6),
            'credit_format' => pSQL(Tools::getValue("credit_format", '#CE[counter]')),
            'credit_reset' => (int) Tools::getValue("credit_reset", 0),
            'credit_reset_value' => (int) Tools::getValue("credit_reset_value", ''),
            'credit_reset_date' => (int) $credit_reset_date,
            'order_number_status' => (int) Tools::getValue("order_number_status", 0),
            'order_start' => (int) Tools::getValue("order_start", 1),
            'order_step' => (int) Tools::getValue("order_step", 1),
            'order_length' => (int) Tools::getValue("order_length", 6),
            'order_format' => pSQL(Tools::getValue("order_format", '#ORDER-[counter]')),
            'order_reset' => (int) Tools::getValue("order_reset", 0),
            'order_reset_value' => (int) Tools::getValue("order_reset_value", ''),
            'order_reset_date' => (int) $order_reset_date,
            'bapaperinvoice' => pSQL(Tools::getValue("bapaperinvoice", 'A4')),
            'invoice_custemp_status' => (int) Tools::getValue("custemp", 1),
            'deli_custemp_status' => (int) Tools::getValue("custempde", 1),
            'cre_custemp_status' => (int) Tools::getValue("custempcre", 1),
        );
        $data = Tools::jsonEncode($data);
        Configuration::updateValue("invoice_customnumber_setting", $data, false, null, $id_shop);
    }
    public function importDataPDF($table, $task, $import)
    {
        $adminControllers=AdminController::$currentIndex;
        $token='&token='.Tools::getAdminTokenLite('AdminModules');
        $configAndTask='&configure='.$this->name.'&task='.$task;
        $ext = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_EXTENSION);
        $tmpFile = $_FILES['fileToUpload']['tmp_name'];
        $dirUpload = _PS_MODULE_DIR_."/ba_prestashop_invoice/upload/".$_FILES['fileToUpload']['name'];
        if ($ext == 'xml') {
            move_uploaded_file($tmpFile, $dirUpload);
            $dataArray = Tools::simplexml_load_file($dirUpload);
            $name = (string)$dataArray->name;
            $invoice_template=(string)$dataArray->pdf_content;
            if (!empty($name) && !empty($invoice_template)) {
                $name = (string)$dataArray->name;
                $description=Tools::htmlentitiesUTF8(strip_tags((string)$dataArray->description));
                $invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_content);
                $header_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_header);
                $footer_invoice_template=Tools::htmlentitiesUTF8((string)$dataArray->pdf_footer);
                $customize_css=strip_tags(Tools::htmlentitiesUTF8((string)$dataArray->customize_css));
                
                $numberColumn = (int) $dataArray->products_template[0]['columns_size'];
                $columsColorBgArray=array();
                $columsColorArray=array();
                $columsContentArray=array();
                $columnsTitleArray=array();
                for ($i = 0; $i < $numberColumn; $i++) {
                    $columnsTitleArray[] = (string)$dataArray->products_template->col[$i]->col_title;
                    $columsContentArray[] = (string)$dataArray->products_template->col[$i]->col_content;
                    $columsColorArray[] = (string)$dataArray->products_template->col[$i]->col_title_color;
                    $columsColorBgArray[] = (string)$dataArray->products_template->col[$i]->col_title_bgcolor;
                }
                $columsTitleJson =  Tools::jsonEncode($columnsTitleArray);
                $columsContentJson =  Tools::jsonEncode($columsContentArray);
                $columsColorJson =  Tools::jsonEncode($columsColorArray);
                $columsColorBgJson =  Tools::jsonEncode($columsColorBgArray);
                //echo "<pre>";var_dump($columsTitleJson);die;
                $showShippingInProductList=(string)$dataArray->show_shipping;
                $showDiscountInProductList=(string)$dataArray->show_discount;
                $baInvoiceEnableLandscape=(string)$dataArray->enable_landscape;
                $showPagination=(string)$dataArray->show_pagination;
                $id_lang=(int)$dataArray->id_lang;
                $status=(int)$dataArray->status;
                Db::getInstance()->insert($table, array(
                    'name' => pSQL($name),
                    'description' => $description,
                    'showShippingInProductList' => $showShippingInProductList,
                    'showDiscountInProductList' => $showDiscountInProductList,
                    'baInvoiceEnableLandscape' => $baInvoiceEnableLandscape,
                    'showPagination' => $showPagination,
                    'header_invoice_template' => $header_invoice_template,
                    'invoice_template' => $invoice_template,
                    'footer_invoice_template' => $footer_invoice_template,
                    'customize_css' => $customize_css,
                    'numberColumnOfTableTemplaterPro' => $numberColumn,
                    'columsTitleJson' => $columsTitleJson,
                    'columsContentJson' => $columsContentJson,
                    'columsColorJson' => $columsColorJson,
                    'columsColorBgJson' => $columsColorBgJson,
                    'id_lang' => (int) $id_lang,
                    'status' => (int) $status,
                    'id_shop' => $this->context->shop->id,
                    'id_shop_group' => $this->context->shop->id_group
                ));
                @unlink($dirUpload);
            } else {
                @unlink($dirUpload);
                Tools::redirectAdmin($adminControllers.$token.$configAndTask.'&'.$import.'&importerror=2');
            }
        } else {
            Tools::redirectAdmin($adminControllers.$token.$configAndTask.'&'.$import.'&importerror=1');
        }
    }
    
    public function returnFooterText()
    {
        return sprintf($this->l('Page %s of %s'), '{PAGENO}', '{nb}');
    }
    
    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $productList = $order->getProducts();
        //echo '<pre>';print_r($productList);
        //echo '<pre>';print_r($order);
        //die;
        foreach ($productList as $productArr) {
            $taxObj = new Tax((int)$productArr['id_tax_rules_group'], (int)$order->id_lang);
            $taxAmount = $productArr['unit_price_tax_incl'] - $productArr['unit_price_tax_excl'];
            Db::getInstance()->insert('ba_prestashop_invoice_tax', array(
                'id_order'            => $order->id,
                'id_product'          => $productArr['product_id'],
                'id_tax'              => (int)$productArr['id_tax_rules_group'],
                'tax_name'            => $taxObj->name,
                'tax_rate'            => $taxObj->rate,
                'tax_amount'          => $taxAmount,
                'product_qty'         => $productArr['product_quantity'],
                'unit_price_tax_excl' => $productArr['unit_price_tax_excl'],
                'unit_price_tax_incl' => $productArr['unit_price_tax_incl']
            ));
            // chen ecotax neu co
            if (isset($productArr['ecotax']) && $productArr['ecotax']>0 && $productArr['ecotax_tax_rate']>0) {
                Db::getInstance()->insert('ba_prestashop_invoice_tax', array(
                    'id_order'            => $order->id,
                    'id_product'          => $productArr['product_id'],
                    'id_tax'              => (int)Configuration::get('PS_ECOTAX_TAX_RULES_GROUP_ID'),
                    'tax_name'            => $this->l('Ecotax'),
                    'tax_rate'            => $productArr['ecotax_tax_rate'],
                    'tax_amount'          => $productArr['ecotax'] * ($productArr['ecotax_tax_rate']/100),
                    'product_qty'         => $productArr['product_quantity'],
                    'unit_price_tax_excl' => $productArr['ecotax'],
                    'unit_price_tax_incl' => $productArr['ecotax'] * (1+$productArr['ecotax_tax_rate']/100)
                ));
            }
        }
        
        $idTaxRulesGroup = Carrier::getIdTaxRulesGroupByIdCarrier((int)$order->id_carrier);
        $shippingTaxObj = new Tax((int)$idTaxRulesGroup, (int)$order->id_lang);
        $taxAmount = $order->total_shipping_tax_incl - $order->total_shipping_tax_excl;
        Db::getInstance()->insert('ba_prestashop_invoice_tax', array(
            'id_order'            => $order->id,
            'id_product'          => 0,
            'id_tax'              => (int)$idTaxRulesGroup,
            'tax_name'            => $shippingTaxObj->name,
            'tax_rate'            => $shippingTaxObj->rate,
            'tax_amount'          => $taxAmount,
            'product_qty'         => 1,
            'unit_price_tax_excl' => $order->total_shipping_tax_excl,
            'unit_price_tax_incl' => $order->total_shipping_tax_incl
        ));
        // chen Gift-wrapping TAX neu co
        if (isset($order->total_wrapping) && ($order->total_wrapping_tax_incl > $order->total_wrapping_tax_excl)) {
            $a = $order->total_wrapping_tax_incl - $order->total_wrapping_tax_excl;
            Db::getInstance()->insert('ba_prestashop_invoice_tax', array(
                'id_order'            => $order->id,
                'id_product'          => 0,
                'id_tax'              => (int)Configuration::get('PS_GIFT_WRAPPING_TAX_RULES_GROUP'),
                'tax_name'            => $this->l('Gift-wrapping tax'),
                'tax_rate'            => $a/$order->total_wrapping_tax_excl,
                'tax_amount'          => $order->total_wrapping_tax_incl - $order->total_wrapping_tax_excl,
                'product_qty'         => 1,
                'unit_price_tax_excl' => $order->total_wrapping_tax_excl,
                'unit_price_tax_incl' => $order->total_wrapping_tax_incl
            ));
        }
    }
    public static function utf8Encode($value)
    {
        // echo mb_internal_encoding();
        $arr_encodeing=mb_detect_encoding($value, mb_list_encodings(), true);
        if (!empty($arr_encodeing)) {
            $value = mb_convert_encoding($value, "UTF-8", $arr_encodeing);
            //$value=w1250_to_utf8($value);
        }
        return $value;
    }
    // return string
    public static function enNonlatin($arr)
    {
        foreach ($arr as $key => & $value) {
            $arr[$key] = self::utf8Encode($value);
        }
        $str = Tools::jsonEncode($arr);
        $str = str_replace('\u', '#u', $str);
        return $str;
    }
    // return array
    public static function deNonlatin($str)
    {
        $c = str_replace('#u', '\u', $str);
        
        $d = Tools::jsonDecode($c);
        //echo '<pre>';var_dump($d);die;
        return $d;
    }
    /**
     * For a given product, returns the warehouses it is stored in
     *
     * @param int $id_product Product Id
     * @param int $id_product_attribute Optional, Product Attribute Id - 0 by default (no attribues)
     * @return array Warehouses Ids and names
     */
    public static function getWarehousesByProductId($id_product, $id_product_attribute = 0)
    {
        if (!$id_product && !$id_product_attribute) {
            return array();
        }

        $query = new DbQuery();
        $query->select('DISTINCT w.id_warehouse, CONCAT(w.reference, " - ", w.name) as name, wpl.location');
        $query->from('warehouse', 'w');
        $query->leftJoin('warehouse_product_location', 'wpl', 'wpl.id_warehouse = w.id_warehouse');
        if ($id_product) {
            $query->where('wpl.id_product = '.(int)$id_product);
        }
        if ($id_product_attribute) {
            $query->where('wpl.id_product_attribute = '.(int)$id_product_attribute);
        }
        $query->orderBy('w.reference ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }
    public static function getCombinationImageById($id_product_attribute, $id_lang)
    {
        if (!Combination::isFeatureActive() || !$id_product_attribute) {
            return false;
        }
        

        $sql = '
            SELECT pai.`id_image`, pai.`id_product_attribute`, il.`legend`
            FROM `'._DB_PREFIX_.'product_attribute_image` pai
            LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (il.`id_image` = pai.`id_image`)
            LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_image` = pai.`id_image`)
            WHERE pai.`id_product_attribute` = '.(int)$id_product_attribute.' AND il.`id_lang` = '
            .(int)$id_lang.' ORDER by i.`position` LIMIT 1';
        $result = Db::getInstance()->executeS($sql);
        if (!$result) {
            return false;
        }
            
        return $result[0];
    }
    // format Date for datepicker
    public function formatDatePicket()
    {
        $id_lang = $this->context->language->id;
        $lang = new language($id_lang);
        $dateformat = $lang->date_format_lite;
        if (strpos($dateformat, "Y") >= 0) {
            $dateformat = str_replace("Y", "yy", $dateformat);
        }
        if (strpos($dateformat, "m") >= 0 && strpos($dateformat, "mm") === false) {
            $dateformat = str_replace("m", "mm", $dateformat);
        }
        if (strpos($dateformat, "d") >= 0 && strpos($dateformat, "dd") === false) {
            $dateformat = str_replace("d", "dd", $dateformat);
        }
        if (strpos($dateformat, "y") >= 0 && strpos($dateformat, "yy") === false) {
            $dateformat = str_replace("y", "yy", $dateformat);
        }
        $html = $dateformat;
        return $html;
    }
    // format ngày tháng theo ngôn ngu
    public function formatDate($date, $format)
    {
        $dateArr = array();
        if (strpos($date, " ")) {
            $dateArr = explode(" ", $date);
        }
        if (strpos($date, "/")) {
            $dateArr = explode("/", $date);
        }
        if (strpos($date, "-")) {
            $dateArr = explode("-", $date);
        }
        if (strpos($date, ";")) {
            $dateArr = explode(";", $date);
        }
        if (strpos($date, ":")) {
            $dateArr = explode(":", $date);
        }
        if (strpos($date, ".")) {
            $dateArr = explode(".", $date);
        }
        if (strpos($date, ",")) {
            $dateArr = explode(",", $date);
        }

        $formatArr = array();
        if (strpos($format, " ")) {
            $formatArr = explode(" ", $format);
        }
        if (strpos($format, "/")) {
            $formatArr = explode("/", $format);
        }
        if (strpos($format, "-")) {
            $formatArr = explode("-", $format);
        }
        if (strpos($format, ";")) {
            $formatArr = explode(";", $format);
        }
        if (strpos($format, ":")) {
            $formatArr = explode(":", $format);
        }
        if (strpos($format, ".")) {
            $formatArr = explode(".", $format);
        }
        if (strpos($format, ",")) {
            $formatArr = explode(",", $format);
        }
        $tmpArr = array();
        for ($i = 0; $i < count($dateArr); $i++) {
            $tmpArr[$formatArr[$i]] = $dateArr[$i];
        }

        $dateFormatArr = array();
        foreach ($tmpArr as $key => $valueTmp) {
            if ($key == "Y" || $key == "y") {
                $dateFormatArr[0] = $valueTmp;
            } elseif ($key == "m" || $key == "M") {
                $dateFormatArr[1] = $valueTmp;
            } elseif ($key == "d" || $key == "D") {
                $dateFormatArr[2] = $valueTmp;
            }
            if ($key == "H" || $key == "h") {
                $dateFormatArr[3] = $valueTmp;
            } elseif ($key == "I" || $key == "i") {
                $dateFormatArr[4] = $valueTmp;
            }
        }
        ksort($dateFormatArr);
        $numberSecondDate = strtotime(implode("/", $dateFormatArr));

        if ($numberSecondDate == false) {
            return date("Y-m-d h:i");
        }
        return implode("-", $dateFormatArr);
    }
    // use if Enable Debug = true
    public function renderHTMLDemo($content, $footer = '', $header = '', $landscape = 'N')
    {
        $content = str_replace(_PS_PROD_IMG_DIR_, __PS_BASE_URI__.'img/p/', $content);
        $footer = str_replace(_PS_PROD_IMG_DIR_, __PS_BASE_URI__.'img/p/', $footer);
        $header = str_replace(_PS_PROD_IMG_DIR_, __PS_BASE_URI__.'img/p/', $header);
        $this->smarty->assign('content', $content);
        $this->smarty->assign('footer', $footer);
        $this->smarty->assign('header', $header);
        $this->smarty->assign('landscape', $landscape);
        $html = $this->display(__FILE__, 'views/templates/admin/debugmode.tpl');
        return $html;
    }
    public function translatePDF($id_lang)
    {
        $translations = array(
            'Individual Taxes' => $this->l('Individual Taxes'),
            'Total' => $this->l('Total'),
            'SKU' => $this->l('SKU'),
            'x' => $this->l('x'),
            'Ecotax: ' => $this->l('Ecotax: '),
            'Tax Detail' => $this->l('Tax Detail'),
            'Tax %' => $this->l('Tax %'),
            'Pre-Tax Total' => $this->l('Pre-Tax Total'),
            'Total Tax' => $this->l('Total Tax'),
            'Total with Tax' => $this->l('Total with Tax'),
            'Products' => $this->l('Products'),
            'Shipping' => $this->l('Shipping'),
            'Ecotax' => $this->l('Ecotax'),
            'Wrapping' => $this->l('Wrapping'),
            'No' => $this->l('No'),
            'Yes' => $this->l('Yes'),
            'Newsletters' => $this->l('Newsletters'),
            'Sign up for our newsletter' => $this->l('Sign up for our newsletter'),
            'Discount' => $this->l('Discount'),
            'Discount for' => $this->l('Discount for'),
            'Shipping Cost' => $this->l('Shipping Cost'),
            'None' => $this->l('None'),
            'Low' => $this->l('Low'),
            'Medium' => $this->l('Medium'),
            'High' => $this->l('High'),
        );
        $_MODULES = array();
        $_MODULE = array();
        $name = $this->name;
        $source = $this->name;
        $language = new Language($id_lang);
        $iso_code = $language->iso_code;
        $filesByPriority = array(
            // Translations in theme
            _PS_THEME_DIR_.'modules/'.$name.'/translations/'.$iso_code.'.php',
            _PS_THEME_DIR_.'modules/'.$name.'/'.$iso_code.'.php',
            // PrestaShop 1.5 translations
            _PS_MODULE_DIR_.$name.'/translations/'.$iso_code.'.php',
            // PrestaShop 1.4 translations
            _PS_MODULE_DIR_.$name.'/'.$iso_code.'.php'
        );
        foreach ($filesByPriority as $file) {
            if (file_exists($file)) {
                include($file);
                $_MODULES = !empty($_MODULES) ? array_merge($_MODULES, $_MODULE) : $_MODULE;
            }
        }
        foreach ($translations as $string => &$value) {
            $value;
            $value = str_replace('"', '&quot;', $string);
            $key = md5(str_replace('\'', '\\\'', $string));
            $current_key = Tools::strtolower('<{'.$name.'}'._THEME_NAME_.'>'.$source).'_'.$key;
            $default_key = Tools::strtolower('<{'.$name.'}prestashop>'.$source).'_'.$key;
            
            if (isset($_MODULES[$current_key])) {
                $value = Tools::stripslashes($_MODULES[$current_key]);
            } elseif (isset($_MODULES[$default_key])) {
                $value = Tools::stripslashes($_MODULES[$default_key]);
            }
        }
        return $translations;
    }
    
    public function cookiekeymodule()
    {
        $keygooglecookie = sha1(_COOKIE_KEY_ . 'ba_prestashop_invoice');
        $md5file = md5($keygooglecookie);
        return $md5file;
    }
}
