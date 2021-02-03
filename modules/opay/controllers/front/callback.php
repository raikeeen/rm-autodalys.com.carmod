<?php

require_once(dirname(__FILE__).'/../../classes/opay_8.1.gateway.inc.php');
require_once(dirname(__FILE__).'/../../classes/crossversionshelper.class.php');

class OpayCallbackModuleFrontController extends ModuleFrontController {
    public $display_column_left = false;
    public $display_column_right = false;
    public $display_header = false;
    public $display_footer = false;
    public $ssl = true;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        CrossVersionsHelper::callback($this->module);
    }
}
