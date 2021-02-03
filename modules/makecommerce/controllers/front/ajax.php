<?php

class makecommerceajaxModuleFrontController extends ModuleFrontController
{
    const CREATE_TRANSACTION = 'create_transaction';

    public function __construct()
    {
        parent::__construct();

        $this->display_column_left = false;
        $this->display_column_right = false;
        $this->display_header = false;
        $this->display_footer = false;
        $this->display_header_javascript = false;
    }

    public function initContent()
    {

        parent::initContent();

        $action = Tools::getValue('action');
        $result = array();

        switch ($action) {
            case self::CREATE_TRANSACTION:
                $method = $this->module->getPaymentMethod(Tools::getValue('method'));
                $transaction = $this->module->createTransaction($method['id']);
                $data = array(
                    'type' => $method['type'],
                    'success' => true
                );

                if ($method['type'] == MakeCommerce::TYPE_CARD) {
                    $data['html'] = $this->getScriptTag($this->module->getJsDataFromTrasaction($transaction));
                } else {
                    $data['url'] = $this->module->getBankUrlFromTransaction(
                        $transaction,
                        $method['id']
                    );
                }

                $result = $data;
                break;
        }

        die(Tools::jsonEncode($result));
    }

    private function getScriptTag($data)
    {
        $tpl = $this->context->smarty->createTemplate($this->module->getLocalPath().'views/templates/front/cards.tpl');
        $data['quick_mode'] = false;
        $tpl->assign($data);

        return $tpl->fetch();
    }

    public function display()
    {
        return false;
    }
}
