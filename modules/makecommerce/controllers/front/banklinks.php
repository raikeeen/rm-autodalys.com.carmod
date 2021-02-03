<?php

class makecommercebanklinksModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $valid_cart = $this->module->validateCart($this->context->cart->id);
        if(!$valid_cart)
            Tools::redirect($this->context->link->getPageLink('order', true));

        $method = $this->module->getPaymentMethod(Tools::getValue('method'));
		$country = Tools::getValue('country');
		
		if (empty($method) || $method->type != MakeCommerce::TYPE_BANK) {
            Tools::redirect($this->context->link->getPageLink('order-opc.php', true));
        }

        $transaction = $this->module->createTransaction($method->code, $country);
        if ( $method->type == 'banklinks')
            $url = $this->module->getBankUrlFromTransaction($transaction, $method->code);

        if (!empty($url)) {
            Tools::redirectLink($url);
        }

        $this->context->smarty->assign(array(
            'banklink_msg' => $this->module->l('Something went wrong !', 'banklinks'),
            'msg_class' => 'danger'
        ));
        $this->setTemplate('final.tpl');
    }
}
