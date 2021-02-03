<?php

class makecommercescoModuleFrontController extends ModuleFrontController
{
    private $cart_info;

    public function initContent()
    {
        parent::initContent();
	$this->cart_info = json_decode(file_get_contents('php://input'));
	$response = $this->module->confirmOrder('MakeCommerce SimpleCheckout', $this->cart_info);	
        header('Content-type: application/json');
	echo json_encode($response);
	exit;
    }
}
