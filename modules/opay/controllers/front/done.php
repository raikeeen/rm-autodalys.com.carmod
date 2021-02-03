<?php

class OpayDoneModuleFrontController extends ModuleFrontController {
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $historyLink = '/index.php?controller=history';

        if (Tools::getValue('status' ) == 1 || Tools::getValue('status') == 2){
            $this->context->smarty->assign(array(
                'status'       => Tools::getValue('status'),
                'history_link' => $historyLink
            ));
            $this->setTemplate('done.tpl');
        }
        else
        {
            Tools::redirect($historyLink);
        }
    }
}
