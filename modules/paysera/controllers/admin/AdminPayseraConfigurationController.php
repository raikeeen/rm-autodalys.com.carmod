<?php
/**
 * 2018 Paysera
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Paysera <plugins@paysera.com>
 *  @copyright 2018 Paysera
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Paysera
 */

class AdminPayseraConfigurationController extends ModuleAdminController
{
    /**
     * Module tab
     */
    const PAYMENT_GATEWAY_TAB = 'payments_gateways';

    /**
     * Initialize controller with options
     */
    public function init()
    {
        Tools::redirectAdmin($this->getAdminModuleLink());
    }

    /**
     * @return string
     */
    protected function getAdminModuleLink()
    {
        $adminLink = $this->context->link->getAdminLink('AdminModules', false);
        $moduleName = Module::getModuleNameFromClass('AdminPayseraConfigurationController');
        $module = '&configure='.$moduleName.'&tab_module='.$this::PAYMENT_GATEWAY_TAB.'&module_name='.$moduleName;
        $adminToken = Tools::getAdminTokenLite('AdminModules');

        return $adminLink . $module . '&token=' . $adminToken;
    }
}
