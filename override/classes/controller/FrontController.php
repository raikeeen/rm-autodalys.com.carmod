<?php

class FrontController extends FrontControllerCore
{
    /*
    * module: ets_superspeed
    * date: 2020-10-16 17:33:40
    * version: 1.0.9
    */
    public function initContent()
    {
        if(Tools::isSubmit('ets_superseed_load_content'))
        {
            parent::initContent();
            Hook::exec('actionPageCacheAjax');
        }
        parent::initContent();
    }
}
