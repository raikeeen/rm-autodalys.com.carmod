<?php

class LPExpressAjaxModuleFrontController extends ModuleFrontController
{
    private $terminal_order;

    public function init()
    {
        parent::init();

        if (Tools::getValue('LPToken') != Tools::getToken(false))
        {
            die(json_encode(['success' => 0, 'message' => $this->module->l('Invalid ajax token')]));
        }

        $this->terminal_order = new LPOrder();
        $this->terminal_order->loadByCartID($this->context->cart->id);
        if (!Validate::isLoadedObject($this->terminal_order))
        {
            $this->terminal_order->id_cart = $this->context->cart->id;
        }
    }

    public function postProcess()
    {
        $action = Tools::getValue('action');
        if (!$action)
        {
            die(json_encode(['success' => 0, 'message' => $this->module->l('Invalid action.')]));
        }

        switch ($action)
        {
            case 'updateOrderTerminal':
                $this->updateTerminal();
                break;
            case 'updateOrderPost':
                $this->updatePost();
                break;
            case 'updateOrderAddress':
                $this->updateAddress();
                break;
            default:
                die(json_encode(['success' => 0, 'message' => $this->module->l('Invalid action.')]));
        }
    }

    private function updateTerminal()
    {
        $id_terminal = Tools::getValue('id_terminal');
        if (!$id_terminal || !Validate::isUnsignedId($id_terminal))
        {
            die(json_encode(['success' => 0, 'message' => $this->module->l('Invalid terminal ID')]));
        }

        if (!Terminal::existsInDatabase($id_terminal, 'lpexpress_terminal'))
        {
            die(json_encode(['success' => 0, 'message' => $this->module->l('Selected terminal doesn\'t exists')]));
        }

        $this->terminal_order->type = LPOrder::TYPE_TERMINAL;
        $this->terminal_order->id_lpexpress_terminal = $id_terminal;

        if (!$this->terminal_order->save())
        {
            die(json_encode(['success' => 0, 'message' => $this->module->l('Error occurs while updating object')]));
        }

        die(json_encode(['success' => 1]));
    }

    private function updatePost()
    {
        $this->terminal_order->type = LPOrder::TYPE_POST;

        if (!$this->terminal_order->save())
        {
            die(json_encode(['success' => 0, 'message' => $this->module->l('Error occurs while updating object')]));
        }

        die(json_encode(['success' => 1]));
    }

    private function updateAddress()
    {
        $this->terminal_order->type = LPOrder::TYPE_ADDRESS;

        if (!$this->terminal_order->save())
        {
            die(json_encode(['success' => 0, 'message' => $this->module->l('Error occurs while updating object')]));
        }

        die(json_encode(['success' => 1]));
    }
}