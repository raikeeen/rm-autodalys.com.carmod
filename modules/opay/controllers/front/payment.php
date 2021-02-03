<?php

require_once(dirname(__FILE__).'/../../classes/opay_8.1.gateway.inc.php');
require_once(dirname(__FILE__).'/../../classes/crossversionshelper.class.php');

class OpayPaymentModuleFrontController extends ModuleFrontController {
    public $display_column_left = true;
    public $display_column_right = true;
    public $display_header = true;
    public $display_footer = true;
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        CrossVersionsHelper::payment($this->module, $this->context);
    }
}
