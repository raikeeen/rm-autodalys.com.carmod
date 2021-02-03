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
*  @author    Buy-Addons <hatt@buy-addons.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* @since 1.6
*/

class Ba_prestashop_invoiceAjaxPreviewSupplierModuleFrontController extends ModuleFrontController
{
    public $pdf_renderer;
    public function run()
    {
        $cookiekey = $this->module->cookiekeymodule();
        $batoken = Tools::getValue("batoken");

        $cookie = new Cookie('psAdmin');
        $id_employee = $cookie->id_employee;
        if ($batoken == $cookiekey && !empty($id_employee)) {
            $this->ajaxPreview();
        } else {
            echo $this->module->l('You do not have permission to access it.');
            die;
        }
    }

    public function ajaxPreview()
    {
        $dataproductlist = array(
            'id_supply_order' => 7,
            'id_product' => 2,
            'id_product_attribute' => 7,
            'reference' => 'reference',
            'supplier_reference' => 'supplier reference',
            'name' => 'Blouse : Size - S, Color - Black',
            'id_currency' => 1,
            'exchange_rate' => 1,
            'unit_price_te' => 100,
            'quantity_expected' => 10,
            'quantity_received' => 0,
            'price_te' => 1000,
            'discount_rate' => 5,
            'discount_value_te' => 50,
            'price_with_discount_te' => 950,
            'tax_rate' => 10,
            'tax_value' => 95,
            'price_ti' => 1045,
            'tax_value_with_order_discount' => 95,
            'price_with_order_discount_te' => 950,
        );
        $datapdf = array(
            '[firstname_warehouse]' => 'bui',
            '[lastname_warehouse]' => 'thu',
            '[vat_number_warehouse]' => '10000',
            '[phone_warehouse]' => '123456789',
            '[phone_mobile_warehouse]' => '012345678',
            '[shopname]' => 'shop',
            '[address_warehouse_address1]' => 'USA',
            '[address_warehouse_address2]' =>  'USA 1',
            '[warehouse_postcode]' => '000000',
            '[supplier_name]' => 'Supplier name',
            '[supplier_address1]' => 'USA',
            '[supplier_address2]' => 'USA 1',
            '[supplier_postcode]' => '000000',
            '[supplier_city]' => 'New York',
            '[supplier_country]' => 'New York',
            '[date]' => '01/11/2019',
            '[title]' => 'Supplier',
            '[reference]' => 'Reference',
            '[total_te]' => Tools::displayPrice(950, (int)Configuration::get('PS_CURRENCY_DEFAULT')),
            '[discount_value_te]' => Tools::displayPrice(0, (int)Configuration::get('PS_CURRENCY_DEFAULT')),
            '[total_with_discount_te]' => Tools::displayPrice(950, (int)Configuration::get('PS_CURRENCY_DEFAULT')),
            '[total_tax]' => Tools::displayPrice(95, (int)Configuration::get('PS_CURRENCY_DEFAULT')),
            '[total_ti]' => Tools::displayPrice(1045, (int)Configuration::get('PS_CURRENCY_DEFAULT')),

        );
        @unlink(_PS_ROOT_DIR_.'/ba_supplier_preview.pdf');
        $invoice_template=Tools::getValue('invoice_template')[Tools::getValue('bareviewlang')];
        $header_invoice_template=Tools::getValue('header_invoice_template')[Tools::getValue('bareviewlang')];
        $footer_invoice_template=Tools::getValue('footer_invoice_template')[Tools::getValue('bareviewlang')];
        $customize_css=Tools::getValue('customize_css');
        $numberColumn = (int) Tools::getValue('numberColumnOfTableTemplaterPro');
        $colums_title = Tools::getValue('colums_title')[Tools::getValue('bareviewlang')];
        
        $colums_content = Tools::getValue('colums_content');
        
        $colums_color = Tools::getValue('colums_color');
        
        $colums_bgcolor = Tools::getValue('colums_bgcolor');

        $baInvoiceEnableLandscape=(Tools::getIsset("baInvoiceEnableLandscape")==true)?'Y':'N';
        $showPagination=(Tools::getIsset("showPagination")==true)?'Y':'N';

        $this->pdf_renderer = new PDFGenerator(false);

         $html_product_list = '
            <style>
                '.$customize_css.'
            </style>
        ';

        $html_product_list .= '
        <div class="ba-box-table" style="">
            <table style="width: 100%;">
                <tr style="line-height:6px; border: none;">
                ';
        for ($i = 0; $i < $numberColumn; $i++) {
            $html_product_list.=
                "<td style='vertical-align: top;padding-bottom:10px;
                font-size:13px;font-weight:bold;color:#" . $colums_color[$i] . ";
                background-color:#" . $colums_bgcolor[$i] . ";'>
                " . $colums_title[$i] . "</th>";
        }
        $html_product_list .= '</tr>';
        $html_product_list.='<tr>';
        for ($j = 0; $j < $numberColumn; $j++) {
            $html_product_list.= $this->checkContentType($colums_content[$j], $dataproductlist, $j);
        }
        $html_product_list.='</tr>';
        $html_product_list .= '
            </table>
        </div>';
        $tabletax = '
        <div style="float:left;width:40%;">
            <div class="ba-tabletax">
                <table>
                    <tr style="line-height:6px; border: none">
                        <td style="text-align: left;
                         font-weight: bold;font-size: 14px;">Base TE</td>
                        <td style="text-align: left;
                         font-weight: bold;font-size: 14px;">Tax Rate</td>
                        <td style="text-align: left;font-size: 14px;
                         font-weight: bold;">Tax Value</td>
                    </tr>
                    <tr style="line-height:6px; border: none;font-size: 14px;">
                        <td style="text-align: left;font-size: 14px;">$950,00</td>
                        <td style="text-align: left;font-size: 14px;">$10</td>
                        <td style="text-align: left;font-size: 14px;">$95,00</td>
                    </tr>
                </table>
            </div>
        </div>
        ';
        foreach ($datapdf as $kdatapdf => $vdatapdf) {
            $invoice_template = str_replace($kdatapdf, $vdatapdf, $invoice_template);
            $header_invoice_template = str_replace($kdatapdf, $vdatapdf, $header_invoice_template);
            $footer_invoice_template = str_replace($kdatapdf, $vdatapdf, $footer_invoice_template);
        };
        $invoice_template = str_replace('[tax_list]', $tabletax, $invoice_template);
        $header_invoice_template = str_replace('[tax_list]', $tabletax, $header_invoice_template);
        $footer_invoice_template = str_replace('[tax_list]', $tabletax, $footer_invoice_template);
        $invoice_template = str_replace('[product_list]', $html_product_list, $invoice_template);
        $this->pdf_renderer->setFontForLang(Context::getContext()->language->iso_code);
        $this->pdf_renderer->createHeader($header_invoice_template);
        $this->pdf_renderer->createFooter($footer_invoice_template);
        $this->pdf_renderer->createContent($invoice_template);
        $this->configMPDF($baInvoiceEnableLandscape, $showPagination);
        $this->pdf_renderer->writePage();
   
        // clean the output buffer
        if (ob_get_level() && ob_get_length() > 0) {
            ob_clean();
        }
        echo $this->pdf_renderer->render("ba_supplier_preview.pdf", 'F');
        @chmod(_PS_ROOT_DIR_.'/ba_supplier_preview.pdf', 0755);
        die;
    }

    public function configMPDF($landscape = null, $showPagination = 'N')
    {
        if ($landscape=="Y") {
            $this->pdf_renderer->mpdf->AddPage("L");
        } else {
            $this->pdf_renderer->mpdf->AddPage("P");
        }
        
        if ($showPagination=="Y") {
            $this->pdf_renderer->mpdf->setFooter($this->module->returnFooterText());
        }
    }

    private function checkContentType($content_type, $supply_order_detail, $j)
    {
        $html = "";
        $id_currencydef = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        switch ($content_type) {
            case "1":
                $html .= '<td class="'.$j.'" style="text-align: left; padding-left: 1px;font-size:14px;">';
                $html .= $supply_order_detail['name'].'</td>';
                break;
            case "2":
                $html .= '<td style="text-align: left; padding-left: 1px;font-size:14px;">';
                $html .= $supply_order_detail['supplier_reference'].'</td>';
                break;
            case "3":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= $supply_order_detail['quantity_expected'].'</td>';
                break;
            case "4":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= Tools::displayPrice($supply_order_detail['unit_price_te'], $id_currencydef).'</td>';
                break;
            case "5":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= Tools::displayPrice($supply_order_detail['price_te'], $id_currencydef).'</td>';
                break;
            case "6":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= $supply_order_detail['discount_rate'].'%</td>';
                break;
            case "7":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= $supply_order_detail['tax_rate'].'%</td>';
                break;
            case "8":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= Tools::displayPrice($supply_order_detail['price_with_discount_te'], $id_currencydef).'</td>';
                break;
            case "9":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= Tools::displayPrice($supply_order_detail['price_ti'], $id_currencydef).'</td>';
                break;
        };
        return $html;
    }
}
