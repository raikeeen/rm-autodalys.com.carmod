<?php

class makecommercevalModuleFrontController extends ModuleFrontController
{
	const STATE_COMPLETE = 'COMPLETED';
	const STATE_APPROVED = 'APPROVED';
	const STATE_CANCELLED = 'CANCELLED';
	const STATE_EXPIRED = 'EXPIRED';

	const MSG_TOKEN_RTN = 'token_return';

	public $ssl = true;

	private $json_data;

	public function initContent()
	{
        parent::initContent();

		$this->json_data = Tools::jsonDecode(Tools::getValue('json'));
		if (isset($this->json_data->message_type) && $this->json_data->message_type == self::MSG_TOKEN_RTN)
		{
			$api = $this->module->getApi();
			if (is_null($api))
			{
				$this->context->smarty->assign(array(
					'banklink_msg' => $this->module->l('Invalid maksekeskus configuration', 'val'),
					'msg_class' => 'info'
				));
				$this->setTemplate('module:makecommerce/views/templates/front/final.tpl');
			}
			else
			{
				$transaction = $api->getTransaction($this->json_data->transaction->id);
				if (!is_null($transaction))
				{
					$order = new Order((int)$transaction->reference);
					if (Validate::isLoadedObject($order))
					{
						if ($order->hasBeenPaid())
						{
							$this->saveTransactionId($this->json_data->transaction->id, $order->reference);
							Tools::redirectLink($this->module->getOrderConfUrl($order));
						}

						$payment = $api->createPayment($transaction->id, array(
							'token' => $this->json_data->token->id,
							'amount' => $transaction->amount,
							'currency' => $transaction->currency
						));

						if (!is_null($payment) && (
							$payment->transaction->status == self::STATE_COMPLETE ||
							$payment->transaction->status == self::STATE_APPROVED
						)) {
							$order->valid = true;
							$order->setCurrentState(Configuration::get('PS_OS_PAYMENT'));
                            $this->saveTransactionId($this->json_data->transaction->id, $order->reference);
							Tools::redirectLink($this->module->getOrderConfUrl($order));
						}
					}
				}
			}
		}
		elseif ($this->verifySignature())
		{
			$order = new Order((int)$this->json_data->reference);
			
                        // check if it is SCO payment and is paid amount valid
                        if ( $this->verifyAmounts( $order->id_cart, $this->json_data->amount, $order->id_carrier)) {
			        if (Validate::isLoadedObject($order))
			        {
				        if ($order->hasBeenPaid())
				        {
                                                $this->saveTransactionId($this->json_data->transaction, $order->reference);
					        Tools::redirectLink($this->module->getOrderConfUrl($order));
				        }
				        elseif ($this->json_data->status == self::STATE_COMPLETE)
				        {
					        $order->valid = true;
					        $order->setCurrentState(Configuration::get('PS_OS_PAYMENT'));
					        $this->saveTransactionId($this->json_data->transaction, $order->reference);
                                                if ($this->context->customer->is_guest) {
                                                        $email = $this->context->customer->email;
                                                        // $this->context->customer->mylogout(); // If guest we clear the cookie for security reason
                                                        Tools::redirect('index.php?controller=guest-tracking&order_reference='.urlencode($order->reference).'&email='.urlencode($email));
                                                } else {
                                                        Tools::redirect('index.php?controller=history');
                                                }
				         }
				        elseif (
					        $this->json_data->status == self::STATE_CANCELLED ||
					        $this->json_data->status == self::STATE_EXPIRED
				        ) {
					        $order->setCurrentState(Configuration::get('PS_OS_CANCELED'));
					        $this->context->smarty->assign(array(
						        'banklink_msg' => $this->module->l('Order canceled', 'val'),
						        'msg_class' => 'info',
						        'order_reference' => $this->json_data->reference,
                                'order_page_link' => $this->context->link->getPageLink('order', true, NULL, 'submitReorder&id_order='.$this->json_data->reference),
                                'language' => 1
					        ));

					        $this->setTemplate('module:makecommerce/views/templates/front/final.tpl');
				        }
				        else
				        {
					        Tools::redirect($this->context->link->getPageLink('order-opc.php', true));
				        }
			        }
			}
		}
		else
		{
			$this->context->smarty->assign(array(
				'msg_class' => 'info',
				'banklink_msg' => $this->module->l('Invalid signature', 'val')
			));
			$this->setTemplate('module:makecommerce/views/templates/front/final.tpl');
		}
	}

        private function verifyAmounts($cart_id, $amount, $id_carrier)
        {
                if (!($sco_amounts = Db::getInstance()->getValue('SELECT `sco_amounts` FROM `'._DB_PREFIX_.'makecommerce_sco` WHERE id_cart = '.$cart_id))) {
                        // error_log('Not sco deal - true');
                        return true; //true if not sco transaction
                } else {
                     $sco_amounts = json_decode($sco_amounts, true);
                     $cart_amount = $sco_amounts['amount'];
                     $carriers = $sco_amounts['carriers'];
                     foreach ($carriers as $carrier ) {
                            if (($carrier["methodId"] == $id_carrier) && (($carrier['amount'] + $cart_amount)==$amount) )
                                    continue;
                            return true;
                     }
                     // error_log("Amount not validated - false");
                     return false;
                }
        }
        
	private function verifySignature()
	{
		if (empty($this->json_data))
		{
			return false;
		}
	    
		if($this->module->getConfig('server') == 0){
            $api_key = $this->module->getConfig('secret_key_test');
        }else{
            $api_key = $this->module->getConfig('secret_key');
        }		

		$signature = strtoupper(
			hash('sha512',
				(string)$this->json_data->amount.
				(string)$this->json_data->currency.
				(string)$this->json_data->reference.
				(string)$this->json_data->transaction.
				(string)$this->json_data->status.
				(string)$api_key
			)
		);
	
		return ($this->json_data->signature == $signature);
	}

	private function saveTransactionId($transaction_id, $order_reference){
        $sql = 'UPDATE `'._DB_PREFIX_.'order_payment`
			SET `transaction_id` = \''.$transaction_id.'\'
			WHERE `order_reference` = \''.$order_reference.'\'';

        Db::getInstance()->Execute($sql);
    }

}
