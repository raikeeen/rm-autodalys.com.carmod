<?php
/**
* 2007-2015 PrestaShop
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

class PDF extends PDFCore
{
    public function __construct($objects, $template, $smarty)
    {
        $this->pdf_renderer = new PDFGeneratorCore((bool)Configuration::get('PS_PDF_USE_CACHE'));
        $this->template = $template;
        $is_enable = Module::isEnabled('ba_prestashop_invoice');
        $is_invoice = $this->template == PDF::TEMPLATE_INVOICE;
        $is_delivery = $this->template == PDF::TEMPLATE_DELIVERY_SLIP;
        $is_credit = $this->template == PDF::TEMPLATE_ORDER_SLIP;
        $is_supplie = $this->template == PDF::TEMPLATE_SUPPLY_ORDER_FORM;
        if ($is_enable==true && ( $is_invoice == true || $is_delivery == true
            || $is_credit == true || $is_supplie == true)) {
            $this->pdf_renderer = new PDFGenerator((bool)Configuration::get('PS_PDF_USE_CACHE'));
        }
        $this->smarty = $smarty;
        $this->objects = $objects;
        if (!($objects instanceof Iterator) && !is_array($objects)) {
            $this->objects = array($objects);
        }
    }
    
    public function render($display = true)
    {
        $render = false;
        $this->pdf_renderer->setFontForLang(Context::getContext()->language->iso_code);

        foreach ($this->objects as $object) {
            $template = $this->getTemplateObject($object);
            if (!$template) {
                continue;
            }
            if (empty($this->filename)) {
                $this->filename = $template->getFilename();
                if (count($this->objects) > 1) {
                    $this->filename = $template->getBulkFilename();
                }
            }
            $template->assignHookData($object);
            $header  = $template->getHeader();
            $footer  = $template->getFooter();
            $content  = $template->getContent();

            $content = html_entity_decode(html_entity_decode($content));
            $header = html_entity_decode(html_entity_decode($header));
            $footer = html_entity_decode(html_entity_decode($footer));

            $is_enable = Module::isEnabled('ba_prestashop_invoice');
            $is_invoice = $this->template == PDF::TEMPLATE_INVOICE;
            $is_delivery = $this->template == PDF::TEMPLATE_DELIVERY_SLIP;
            $is_credit = $this->template == PDF::TEMPLATE_ORDER_SLIP;
            $is_supplie = $this->template == PDF::TEMPLATE_SUPPLY_ORDER_FORM;
            if ($is_enable==true && ( $is_invoice == true || $is_delivery == true
                || $is_credit == true || $is_supplie == true)) {
                $this->configMPDF($object);
                $id_shop = Context::getContext()->shop->id;
                $setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
                $setting = Tools::jsonDecode($setting, true);
                if ($setting['invoice_debug'] == 1 && Tools::getValue('controller') == 'AdminPdf') {
                    $landscape = $template->landscape;
                    require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/ba_prestashop_invoice.php");
                    $ba_prestashop_invoice = new ba_prestashop_invoice();
                    echo $ba_prestashop_invoice->renderHTMLDemo($content, $footer, $header, $landscape);
                    die;
                }
            }
            
            $this->pdf_renderer->createHeader($header);
            $this->pdf_renderer->createFooter($footer);
            $this->pdf_renderer->createContent($content);
            
            $this->pdf_renderer->writePage();
            $render = true;
            unset($template);
        }
        
        if ($render) {
            if (ob_get_level() && ob_get_length() > 0) {
                ob_clean();
            }
            return $this->pdf_renderer->render($this->filename, $display);
        }
    }
    
    public function configMPDF($object)
    {
        $order = new Order($object->id_order);
        $db = Db::getInstance();
        if ($this->template == PDF::TEMPLATE_INVOICE) {
            $sql='SELECT * FROM '._DB_PREFIX_.'ba_prestashop_invoice WHERE id_lang='
            .(int)$order->id_lang.' AND status=1';
            $htmlTemplate = $db->ExecuteS($sql);
            
            $landscape=null;
            if (!empty($htmlTemplate)) {
                $landscape = $htmlTemplate[0]['baInvoiceEnableLandscape'];
            }
            if ($landscape=="Y") {
                $this->pdf_renderer->mpdf->AddPage("L");
            } else {
                $this->pdf_renderer->mpdf->AddPage("P");
            }
        } elseif ($this->template == PDF::TEMPLATE_DELIVERY_SLIP) {
            $sql='SELECT * FROM '._DB_PREFIX_.'ba_prestashop_delivery_slip WHERE id_lang='
            .(int)$order->id_lang.' AND status=1';
            $htmlTemplate = $db->ExecuteS($sql);
            
            $landscape=null;
            if (!empty($htmlTemplate)) {
                $landscape = $htmlTemplate[0]['baInvoiceEnableLandscape'];
            }
            if ($landscape=="Y") {
                $this->pdf_renderer->mpdf->AddPage("L");
            } else {
                $this->pdf_renderer->mpdf->AddPage("P");
            }
        } elseif ($this->template == PDF::TEMPLATE_ORDER_SLIP) {
            $sql='SELECT * FROM '._DB_PREFIX_.'ba_prestashop_credit_slip WHERE id_lang='
            .(int)$order->id_lang.' AND status=1';
            $htmlTemplate = $db->ExecuteS($sql);
            
            $landscape=null;
            if (!empty($htmlTemplate)) {
                $landscape = $htmlTemplate[0]['baInvoiceEnableLandscape'];
            }
            if ($landscape=="Y") {
                $this->pdf_renderer->mpdf->AddPage("L");
            } else {
                $this->pdf_renderer->mpdf->AddPage("P");
            }
        } elseif ($this->template = PDF::TEMPLATE_SUPPLY_ORDER_FORM) {
            $id_shop = Context::getContext()->shop->id;
            $sql='SELECT * FROM '._DB_PREFIX_.'ba_prestashop_supplier_slip WHERE id_shop='
            .(int)$id_shop.' AND status=1';
            $htmlTemplate = $db->ExecuteS($sql);
            
            $landscape=null;
            if (!empty($htmlTemplate)) {
                $landscape = $htmlTemplate[0]['baInvoiceEnableLandscape'];
            }
            if ($landscape==1) {
                $this->pdf_renderer->mpdf->AddPage("L");
            } else {
                $this->pdf_renderer->mpdf->AddPage("P");
            }
        }
    }
}
