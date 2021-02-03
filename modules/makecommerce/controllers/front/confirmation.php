<?php

class makecommerceconfirmationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {

        parent::initContent();


        $back_url = $this->context->link->getPageLink('order', true);
        $method = $this->module->getPaymentMethod((string)Tools::getValue('method'));
		$logo = $method->img;
		$country = Tools::getValue('country');

        if (empty($method)) {
            Tools::redirect($back_url);
        }

        $cart = $this->context->cart;
        $this->context->smarty->assign(array(
            'href' => $this->context->link->getModuleLink(
                $this->module->name,
                $method->type,
				array('method' => $method->code,
					  'country' => $country)
            ),
            'priceDisplay' => $priceDisplay = Product::getTaxCalculationMethod((int) $this->context->cookie->id_customer),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'logo' => $logo,
			'back_href' => $back_url,
            'display_name' => $this->module->getTranslation($method->code),
        ));

        $this->setTemplate('module:makecommerce/views/templates/front/confirmation.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->context->controller->addJS($this->module->getPathUri().'views/js/makecommerce.js');
        $this->context->controller->addCSS($this->module->getPathUri().'views/css/makecommerce.css');
    }
}
