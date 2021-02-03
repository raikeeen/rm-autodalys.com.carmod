<?php

require_once(dirname(__FILE__).'/../../classes/opay_8.1.gateway.inc.php');
require_once(dirname(__FILE__).'/../../classes/crossversionshelper.class.php');

class OpayValidationModuleFrontController extends ModuleFrontController {
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $opay = new OpayGateway();

        CrossVersionsHelper::validation($this->module, $this->context);

    }
}
