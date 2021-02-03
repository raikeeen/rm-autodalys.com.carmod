<?php
/**
* 2017-2020 Buy Addons Team
*
* NOTICE OF LICENSE
*
* This source file is subject to the GNU General Public License version 3
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://www.opensource.org/licenses/gpl-3.0.html
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@buy-addons.com so we can send you a copy immediately.
*
* @author Buy Addons Team <hatt@buy-addons.com>
* @copyright  2017-2020 Buy Addons Team
* @license   http://www.opensource.org/licenses/gpl-3.0.html  GNU General Public License version 3
* International Registered Trademark & Property of Buy Addons Team
*/

class AdminBaBrowserTabBadgeController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function display()
    {
        parent::display();
    }

    public function initContent()
    {
        parent::initContent();
        $link = _PS_BASE_URL_.$_SERVER['PHP_SELF'];
        $token = Tools::getAdminTokenLite('AdminModules');
        $link .= '?controller=AdminModules&token='.$token.'';
        $link .= '&configure=babrowsertabbadge&tab_module=front_office_features&module_name=babrowsertabbadge';
        Tools::redirect($link);
    }
}
