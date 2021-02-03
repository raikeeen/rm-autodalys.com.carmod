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
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* @since 1.6
*/
class PDFGenerator extends PDFGeneratorCore
{
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:05
    * version: 1.1.39
    */
    public $mpdf=null;
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:05
    * version: 1.1.39
    */
    public function __construct($use_cache = false)
    {
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/libs/mpdf/vendor/autoload.php");
        $id_shop = Context::getContext()->shop->id;
        $customnumber_setting = Configuration::get("invoice_customnumber_setting", null, null, $id_shop);
        $customnumber_setting = Tools::jsonDecode($customnumber_setting, true);
        if (!empty($customnumber_setting['bapaperinvoice'])) {
            $config = array(
                'format' => $customnumber_setting['bapaperinvoice']
            );
            $this->mpdf = new \Mpdf\Mpdf($config);
        } else {
            $this->mpdf = new \Mpdf\Mpdf();
        }
        $this->mpdf->debug = false;
        $this->mpdf->useSubstitutions = false;
        $this->mpdf->simpleTables = false;
        parent::__construct($use_cache);
    }
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:05
    * version: 1.1.39
    */
    public function writePage()
    {
        if (Module::isEnabled('ba_prestashop_invoice')==false) {
            return parent::writePage();
        }
        $this->mpdf->WriteHTML($this->content);
    }
    
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:05
    * version: 1.1.39
    */
    public function render($filename, $display = true)
    {
        if (Module::isEnabled('ba_prestashop_invoice')==false) {
            return parent::render($filename, $display);
        }
        
        if (empty($filename)) {
            throw new PrestaShopException('Missing filename.');
        }
        if ($display === true) {
            $output = 'D';
        } elseif ($display === false) {
            $output = 'S';
        } elseif ($display == 'D') {
            $output = 'D';
        } elseif ($display == 'S') {
            $output = 'S';
        } elseif ($display == 'F') {
            $output = 'F';
        } else {
            $output = 'I';
        }
       
        if (ob_get_level() && ob_get_length() > 0) {
            ob_clean();
        }
        return $this->mpdf->Output($filename, $output);
    }
    
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:05
    * version: 1.1.39
    */
    public function createHeader($header)
    {
        if (Module::isEnabled('ba_prestashop_invoice')==false) {
            $this->header = $header;
        }
        if (!empty($header)) {
            $this->mpdf->setAutoTopMargin = 'stretch';
            $this->mpdf->SetHTMLHeader($header, '', true);
        }
    }
    /*
    * module: ba_prestashop_invoice
    * date: 2020-10-06 12:16:05
    * version: 1.1.39
    */
    public function createFooter($footer)
    {
        if (Module::isEnabled('ba_prestashop_invoice')==false) {
            $this->footer = $footer;
        }
        if (!empty($footer)) {
            $this->mpdf->setAutoBottomMargin = 'stretch';
            $this->mpdf->SetHTMLFooter($footer);
        }
    }
}
