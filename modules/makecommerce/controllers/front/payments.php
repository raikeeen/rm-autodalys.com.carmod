<?php

class makecommercepaymentsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $type = Tools::getValue('method', null);
        $country = Tools::getValue('country', null);

        $methods = $this->module->getPaymentMethodValues($country, $type);

        $this->context->smarty->assign(array(
            'payment_methods' => $methods,
            'display_name' => $this->module->displayName,
            'back_href' => $this->context->link->getPageLink('order-opc')
        ));

        Media::addJsDef(array(
            'mk_ajax_url' => $this->context->link->getModuleLink($this->module->name, 'ajax')
        ));

        $this->setTemplate('payments.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->context->controller->addJS($this->module->getPathUri().'views/js/makecommerce.js');
        $this->context->controller->addCSS($this->module->getPathUri().'views/css/makecommerce.css');
    }
}
