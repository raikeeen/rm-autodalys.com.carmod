<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@buy-addons.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Buy-Addons <contact@buy-addons.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* @since 1.6
*/

class BabrowsertabbadgedeleteimgModuleFrontController extends ModuleFrontController
{
    public function run()
    {
        parent::init();
        parent::initHeader();
        parent::initContent();
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        
        $token_babrowsertabbadge = Tools::getValue('token_babrowsertabbadge');
        if ($token_babrowsertabbadge == md5(sha1(_COOKIE_KEY_ . 'babrowsertabbadge'))) {
            $delete_upload_img = "UPDATE "._DB_PREFIX_."babrowsertabbadge SET upload_icon = ''";
            $db->query($delete_upload_img);
        } else {
            echo $this->module->l("You do not have permission to access it.");
        }
    }
}
