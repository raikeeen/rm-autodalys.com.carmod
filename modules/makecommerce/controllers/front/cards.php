<?php

class makecommercecardsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $valid_cart = $this->module->validateCart($this->context->cart->id);
        if(!$valid_cart)
            Tools::redirect($this->context->link->getPageLink('order', true));

		if(Tools::getValue('paymentToken')){
			$transaction_id = $this->context->cookie->__get('mk_transaction_id');
			$reference = $this->context->cookie->__get('mk_transaction_reference');
            $order = new Order($reference);
            $currency = new Currency($order->id_currency);
            $token = Tools::getValue('paymentToken');
            $request_body = array(
                'amount' => $order->total_paid,
                'currency' => $currency->iso_code,
                'token' => $token
			);
			$this->module->createPayment($transaction_id, $request_body);
			Tools::redirectLink($this->module->getOrderConfUrl($order));
        }		

		$method = $this->module->getPaymentMethod(Tools::getValue('method'));
		$country = Tools::getValue('country');

        if (empty($method) || $method->type != MakeCommerce::TYPE_CARD) {
            Tools::redirect($this->context->link->getPageLink('order-opc.php', true));
        }

        $transaction = $this->module->createTransaction($method->code, $country);

        if (empty($transaction)) {
            $this->context->smarty->assign(array(
                'banklink_msg' => $$this->module->l('Something went wrong!', 'cards'),
                'msg_class' => 'danger'
            ));
            $this->setTemplate('final.tpl');
		} else {
			$this->context->cookie->__set('mk_transaction_id', $transaction->id);
            $this->context->cookie->__set('mk_transaction_reference', $transaction->reference);
			$this->context->smarty->assign(array(
				'order_reference' => $transaction->reference
			));
			$this->context->smarty->assign($this->module->getJsDataFromTrasaction($transaction));
            $this->setTemplate('module:makecommerce/views/templates/front/cards.tpl');
        }
    }
}
