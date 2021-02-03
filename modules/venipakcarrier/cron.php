<?php
/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 11/4/2018
 * Time: 17:42
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

/* Check to security tocken */
if (substr(Tools::encrypt('venipakcarrier/cron'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('venipakcarrier'))
    die('Bad token');

if (Shop::isFeatureActive()) {
    Shop::setContext(Shop::CONTEXT_ALL);
}

$module = Module::getInstanceByName('venipakcarrier');
/* Check if the module is enabled */
if ($module->active)
{
    $action = Tools::getValue('action');

    switch($action) {

        case 'updatePickupPoints':
            $module->updatePickupPoints(true);
            break;
    }
}
