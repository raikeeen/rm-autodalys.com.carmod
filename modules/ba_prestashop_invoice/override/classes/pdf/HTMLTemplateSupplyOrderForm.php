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
*  @author Buy Addons <contact@buy-addons.com>
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class HTMLTemplateSupplyOrderForm extends HTMLTemplateSupplyOrderFormCore
{
    public $htmlTemplate;
    public $landscape = 'N';
    public $translations = array();

    public function __construct(SupplyOrder $supply_order, $smarty)
    {
        if (Module::isEnabled('ba_prestashop_invoice')==false) {
            return parent::__construct($supply_order, $smarty);
        }
        $this->supply_order = $supply_order;
        $this->smarty = $smarty;
        $this->context = Context::getContext();
        $this->warehouse = new Warehouse((int)$supply_order->id_warehouse);
        $this->address_warehouse = new Address((int)$this->warehouse->id_address);
        $this->address_supplier = new Address(Address::getAddressIdBySupplierId((int)$supply_order->id_supplier));
        $db = Db::getInstance();
        $sql='SELECT * FROM '._DB_PREFIX_.'ba_prestashop_supplier_slip WHERE id_lang='
            .(int)$this->supply_order->id_lang.' AND status=1 AND id_shop='.$this->context->shop->id;
        $this->htmlTemplate = $db->ExecuteS($sql);
        // header informations
        $this->date = Tools::displayDate($supply_order->date_add);
        $this->title = HTMLTemplateSupplyOrderForm::l('Supply order form');
        $this->landscape =  $this->htmlTemplate[0]['baInvoiceEnableLandscape'];
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/ba_prestashop_invoice.php");
        $this->translations = (new ba_prestashop_invoice())->translatePDF($this->supply_order->id_lang);
        /*echo '<pre>';var_dump($this->address_warehouse);die;*/
    }

    /**
     * @see HTMLTemplate::getContent()
     */
    public function getContent()
    {
        if (Module::isEnabled('ba_prestashop_invoice')==false || get_class($this)!='HTMLTemplateSupplyOrderForm') {
            return parent::getContent();
        }
        $html = HTMLTemplateDeliverySlip::l('Do not Invoice Template actived for this store');
        if (!empty($this->htmlTemplate)) {
            $html = Tools::htmlentitiesDecodeUTF8($this->htmlTemplate[0]['invoice_template']);
        }
        return $this->replaceToken($html);
    }

    /**
     * @see HTMLTemplate::getHeader()
     */
    public function getHeader()
    {
        if (Module::isEnabled('ba_prestashop_invoice')==false || get_class($this)!='HTMLTemplateSupplyOrderForm') {
            return parent::getHeader();
        }
        $headerInvoiceTemplate="";
        if (!empty($this->htmlTemplate)) {
            $headerInvoiceTemplate=$this->htmlTemplate[0]['header_invoice_template'];
            $headerInvoiceTemplate=Tools::htmlentitiesDecodeUTF8($headerInvoiceTemplate);
        }
        return $this->replaceToken($headerInvoiceTemplate);
    }

    /**
     * @see HTMLTemplate::getFooter()
     */
    public function getFooter()
    {
        if (Module::isEnabled('ba_prestashop_invoice')==false || get_class($this)!='HTMLTemplateSupplyOrderForm') {
            return parent::getFooter();
        }
        $footerInvoiceTemplate="";
        $showPagination="N";
        if (!empty($this->htmlTemplate)) {
            $footerInvoiceTemplate=$this->htmlTemplate[0]['footer_invoice_template'];
            $footerInvoiceTemplate=Tools::htmlentitiesDecodeUTF8($footerInvoiceTemplate);
            $showPagination = $this->htmlTemplate[0]['showPagination'];
        }
        $html = $this->replaceToken($footerInvoiceTemplate);
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/ba_prestashop_invoice.php");
        $ba_prestashop_invoice = new ba_prestashop_invoice();
        if ($showPagination=="Y") {
            $html.='<div style="text-align:right">'.$ba_prestashop_invoice->returnFooterText().'</div>';
        }
        return $html;
    }


    public function replaceToken($html)
    {
        $currency = new currency($this->supply_order->id_currency);
        $html = str_replace("[currency_prefix]", $currency->prefix, $html);
        $html = str_replace("[currency_suffix]", $currency->suffix, $html);
        $html = str_replace("[shopname]", $this->context->shop->name, $html);
        $html = str_replace("[lastname_warehouse]", $this->address_warehouse->lastname, $html);
        $html = str_replace("[firstname_warehouse]", $this->address_warehouse->firstname, $html);
        $html = str_replace("[phone_warehouse]", $this->address_warehouse->phone, $html);
        $html = str_replace("[phone_mobile_warehouse]", $this->address_warehouse->phone_mobile, $html);
        $html = str_replace("[vat_number_warehouse]", $this->address_warehouse->vat_number, $html);
        $html = str_replace("[address_warehouse_address1]", $this->address_warehouse->address1, $html);
        $html = str_replace("[address_warehouse_address2]", $this->address_warehouse->address2, $html);
        $html = str_replace("[warehouse_postcode]", $this->address_warehouse->postcode, $html);
        $html = str_replace("[supplier_name]", $this->supply_order->supplier_name, $html);
        $html = str_replace("[supplier_address1]", $this->supply_order->address1, $html);
        $html = str_replace("[supplier_address2]", $this->supply_order->address2, $html);
        $html = str_replace("[supplier_country]", $this->supply_order->country, $html);
        $html = str_replace("[supplier_postcode]", $this->supply_order->postcode, $html);
        $html = str_replace("[supplier_city]", $this->supply_order->city, $html);
        $html = str_replace("[reference]", $this->supply_order->reference, $html);
        $html = str_replace("[total_te]", Tools::displayPrice($this->supply_order->total_te), $html);
        $html = str_replace("[discount_value_te]", Tools::displayPrice($this->supply_order->discount_value_te), $html);
        $abc1 = Tools::displayPrice($this->supply_order->total_with_discount_te);
        $html = str_replace("[total_with_discount_te]", $abc1, $html);
        $html = str_replace("[total_tax]", Tools::displayPrice($this->supply_order->total_tax), $html);
        $html = str_replace("[total_ti]", Tools::displayPrice($this->supply_order->total_ti), $html);
        $html = str_replace("[date]", Tools::displayDate($this->supply_order->date_add), $html);
        $abc2 = Tools::displayDate($this->supply_order->date_delivery_expected);
        $html = str_replace("[date_delivery_expected]", $abc2, $html);
        $html = str_replace("[product_list]", $this->tableProductSupply(), $html);
        $html = str_replace("[tax_list]", $this->baTaxOrderSummary(), $html);
        $html = str_replace("[title]", HTMLTemplateSupplyOrderForm::l('Supply order form'), $html);
        return $html;
    }

    public function tableProductSupply()
    {
        $supply_order_details = $this->supply_order->getEntriesCollection((int)$this->supply_order->id_lang);
        require_once(_PS_MODULE_DIR_ . "ba_prestashop_invoice/ba_prestashop_invoice.php");
        $columns_title = ba_prestashop_invoice::deNonlatin($this->htmlTemplate[0]['columsTitleJson']);
        $columns_content = Tools::jsonDecode($this->htmlTemplate[0]['columsContentJson']);
        $columns_bgcolor = Tools::jsonDecode($this->htmlTemplate[0]['columsColorBgJson']);
        $columns_color = Tools::jsonDecode($this->htmlTemplate[0]['columsColorJson']);
        $numberColumnOfTableTemplaterPro = 0;
        if (!empty($this->htmlTemplate)) {
            $numberColumnOfTableTemplaterPro=$this->htmlTemplate[0]['numberColumnOfTableTemplaterPro'];
        }
        $tablepro = '';
        $customize_css=null;
        if (!empty($this->htmlTemplate)) {
            $customize_css = $this->htmlTemplate[0]['customize_css'];
        }
        $tablepro .= '
            <style>
                '.htmlspecialchars_decode($customize_css).'
            </style>
        ';
        $tablepro .= '
        <div class="ba-box-table" style="">
            <table style="width: 100%;">
                <tr style="line-height:6px; border: none;margin-bottom:10px;">
                ';
        for ($i = 0; $i < $numberColumnOfTableTemplaterPro; $i++) {
            $tablepro.=
                "<td style='vertical-align: top;padding-bottom:10px;
                font-size:13px;font-weight:bold;color:#" . $columns_color[$i] . ";
                background-color:#" . $columns_bgcolor[$i] . ";'>
                " . $columns_title[$i] . "</th>";
        }
        $tablepro .= '</tr>';
        foreach ($supply_order_details as $supply_order_detail) {
            $tablepro .= '<tr>';
            for ($j = 0; $j < $numberColumnOfTableTemplaterPro; $j++) {
                $tablepro.= $this->checkContentType($columns_content[$j], $supply_order_detail, $j);
            }
            $tablepro .= '</tr>';
        };
        $tablepro .= '
            </table>
        </div>
        ';
        return $tablepro;
    }
    private function checkContentType($content_type, $supply_order_detail, $j)
    {
        $html = "";
        switch ($content_type) {
            case "1":
                $html .= '<td class='.$j.' style="text-align: left; padding-left: 1px;font-size:14px;">';
                $html .= $supply_order_detail->name.'</td>';
                break;
            case "2":
                $html .= '<td style="text-align: left; padding-left: 1px;font-size:14px;">';
                $html .= $supply_order_detail->supplier_reference.'</td>';
                break;
            case "3":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= $supply_order_detail->quantity_expected.'</td>';
                break;
            case "4":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= Tools::displayPrice($supply_order_detail->unit_price_te).'</td>';
                break;
            case "5":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= Tools::displayPrice($supply_order_detail->price_te).'</td>';
                break;
            case "6":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= $supply_order_detail->discount_rate.'%</td>';
                break;
            case "7":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= $supply_order_detail->tax_rate.'%</td>';
                break;
            case "8":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= Tools::displayPrice($supply_order_detail->price_with_discount_te).'</td>';
                break;
            case "9":
                $html .= '<td style="text-align: left; padding-right: 1px;font-size:14px;">';
                $html .= Tools::displayPrice($supply_order_detail->price_ti).'</td>';
                break;
        };
        return $html;
    }
    public function baTaxOrderSummary()
    {
        $query = new DbQuery();
        $query->select('
            SUM(price_with_order_discount_te) as base_te,
            tax_rate,
            SUM(tax_value_with_order_discount) as total_tax_value
        ');
        $query->from('supply_order_detail');
        $query->where('id_supply_order = '.(int)$this->supply_order->id);
        $query->groupBy('tax_rate');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        foreach ($results as &$result) {
            $result['base_te'] = Tools::displayPrice($result['base_te']);
            if ($result['tax_rate'] > 0) {
                $result['tax_rate'] = Tools::ps_round($result['tax_rate']);
            } else {
                $result['tax_rate'] = '-';
            }
            if ($result['total_tax_value'] > 0) {
                $result['total_tax_value'] = Tools::displayPrice($result['total_tax_value']);
            } else {
                $result['total_tax_value'] = '-';
            }
        }
        unset($result); // remove reference

        $tabletax = '
        <div style="float:left;width:40%;">
            <div class="ba-tabletax">
                <table>
                    <tr style="line-height:6px; border: none">
                        <td style="text-align: left;
                         font-weight: bold;">Base TE</td>
                        <td style="text-align: left;
                          font-weight: bold;">Tax Rate</td>
                        <td style="text-align: left;
                          font-weight: bold;">Tax Value</td>
                    </tr>
        ';
        foreach ($results as $vresults) {
            $tabletax .= '
                <tr style="line-height:6px; border: none;font-size: 14px;">
                    <td style="text-align: left; padding-right: 1px;font-size: 14px;">'.$vresults['base_te'].'</td>
                    <td style="text-align: left; padding-right: 1px;font-size: 14px;">'.$vresults['tax_rate'].'</td>
                    <td style="text-align: left; padding-right: 1px;
                    font-size: 14px;">'.$vresults['total_tax_value'].'</td>
                </tr>
            ';
        };
        $tabletax .= '
                </table>
            </div>
        </div>
        ';
        return $tabletax;
    }
}
