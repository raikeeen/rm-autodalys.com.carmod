<?php

class Dispatcher extends DispatcherCore
{
    /*
    * module: ets_superspeed
    * date: 2020-10-16 17:33:41
    * version: 1.0.9
    */
    public function dispatch() {
        require_once(dirname(__FILE__).'/../../modules/ets_superspeed/ets_superspeed.php');
        if($cache = Ets_superspeed::displayContentCache(true))
        {
            echo $cache;
            return true;
        }
        parent::dispatch();
    }
}
