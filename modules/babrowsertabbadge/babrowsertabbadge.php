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
 *  @author    PrestaShop SA <contact@buy-addons.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 * @since 1.6
 */

class Babrowsertabbadge extends Module
{
    
    public $demoMode=false;
    public function __construct()
    {
        $this->name          = "babrowsertabbadge";
        $this->tab           = "front_office_features";
        $this->version       = "1.0.4";
        $this->author        = "buy-addons";
        $this->need_instance = 0;
        $this->bootstrap     = true;
        $this->module_key    = '0544a809dce744cdec8af4f048c0845c';
        parent::__construct();
        $this->displayName   = $this->l('Magic Browser Tab Badge Notification (Favicon)');
        $this->description   = $this->l('Displays badge with shopping cart items count on Browser Tab Favicon');
    }
    public function disable($forceAll = false)
    {
        $forceAll;
        $tab = new Tab((int) Tab::getIdFromClassName('AdminBaBrowserTabBadge'));
        $tab->delete();
        if (parent::disable() == false) {
            return false;
        }
        return true;
    }

    public function enable($forceAll = false)
    {
        $forceAll;
        $tab_id = Tab::getIdFromClassName('AdminBaBrowserTabBadge');
        $tab_presmobic = Tab::getIdFromClassName('AdminPressMobileApp');
        if ($tab_presmobic != false && $tab_id === false) {
            $this->installSubmenu('AdminBaBrowserTabBadge', 'Browser Tab Badge');
        }
        if (parent::enable() == false) {
            return false;
        }
        return true;
    }
    public function install()
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $create_table = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'babrowsertabbadge(
            `id` int(11) unsigned NOT NULL auto_increment,
            `idSave` int(11),
            `id_lang` int(11) unsigned NOT NULL,
            `id_shop` int(11) unsigned NOT NULL,
            `background_color` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `text_color` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `upload_icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `text` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`)
        )';
        $db->query($create_table);
        $arr_language = Language::getLanguages();
        $arr_shop     = Shop::getShops();
        foreach ($arr_language as $key => $value1) {
            foreach ($arr_shop as $key => $value2) {
                $key;
                $insert = "INSERT INTO "._DB_PREFIX_."babrowsertabbadge";
                $insert .= "(idSave,id_lang,id_shop,background_color,text_color,upload_icon,text)";
                $insert .= " VALUES('1','".(int)$value1['id_lang']."','".(int)$value2['id_shop']."',";
                $insert .= "'FF0000','FFFFFF','','My Store')";
                $db->query($insert);
            }
        }
        $tab_id = Tab::getIdFromClassName('AdminBaBrowserTabBadge');
        $tab_presmobic = Tab::getIdFromClassName('AdminPressMobileApp');
        if ($tab_presmobic != false && $tab_id === false) {
            $this->installSubmenu('AdminBaBrowserTabBadge', 'Browser Tab Badge');
        }
        if (parent::install() == false) {
            return false;
        }
        if ($this->registerHook("displayFooter")== false) {
            return false;
        }
        if ($this->registerHook("displayTop") == false) {
            return false;
        }
        if ($this->registerHook("header") == false) {
            return false;
        }
        return true;
    }
    public function uninstall()
    {
        if (parent::uninstall() == false) {
            return false;
        }
        $db          = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $dropTable   = "DROP TABLE IF EXISTS "._DB_PREFIX_."babrowsertabbadge";
        $db->query($dropTable);
        $tab = new Tab((int) Tab::getIdFromClassName('AdminBaBrowserTabBadge'));
        $tab->delete();
        return true;
    }
    public function hookdisplayTop(&$params)
    {
        $db          = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $id_lang      = $this->context->language->id;
        $id_shop      = $this->context->shop->id;
        $sql1_idSave1 ='SELECT * FROM '._DB_PREFIX_.'babrowsertabbadge WHERE';
        $sql1_idSave1 .= ' idSave = 1 AND id_shop = '.(int)$id_shop .' AND id_lang = '.(int)$id_lang;
        $infor_idSave1 = $db->ExecuteS($sql1_idSave1);
        $arr_babrowsertabbadge = array();
        $arr_babrowsertabbadge['background_color'] = $infor_idSave1[0]['background_color'];
        $arr_babrowsertabbadge['text_color']       = $infor_idSave1[0]['text_color'];
        $arr_babrowsertabbadge['upload_icon']      = $infor_idSave1[0]['upload_icon'];
        $arr_babrowsertabbadge['text']             = $infor_idSave1[0]['text'];
        $link_a = Tools::getShopProtocol();
        $link_b = Tools::getServerName();
        $link_c = 'modules/babrowsertabbadge/views/img/icon_image/';
        $link_d = $arr_babrowsertabbadge['upload_icon'];
        if ($link_d != '') {
            $link_img = $link_a. $link_b. __PS_BASE_URI__.$link_c.$link_d;
        }
        if ($link_d == '') {
            $link_c = '/modules/babrowsertabbadge/views/img/icon_image/';
            $link_d = 'favicon.ico';
            $link_img = $link_a. $link_b. __PS_BASE_URI__.$link_c.$link_d;
        }
        $this->context->smarty->assign('arr_babrowsertabbadge', $arr_babrowsertabbadge);
        $this->context->smarty->assign('link_img', $link_img);
        $this->context->controller->addJS($this->_path . 'views/js/favicon.js');
        return $this->display(__FILE__, 'views/templates/admin/titleTag.tpl');
    }
    public function hookHeader()
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>=')) {
            $header_a = 'modules-babrowsertabbadge';
            $header_b = 'modules/babrowsertabbadge/views/js/favicon.js';
            $header_c = array('position' => 'bottom', 'priority' => 200);
            $this->context->controller->registerJavascript($header_a, $header_b, $header_c);
        }
        $db          = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $id_lang      = $this->context->language->id;
        $id_shop      = $this->context->shop->id;
        $sql1_idSave1 ='SELECT * FROM '._DB_PREFIX_.'babrowsertabbadge WHERE';
        $sql1_idSave1 .= ' idSave = 1 AND id_shop = '.(int)$id_shop .' AND id_lang = '.(int)$id_lang;
        $infor_idSave1 = $db->ExecuteS($sql1_idSave1);
        $arr_babrowsertabbadge = array();
        $arr_babrowsertabbadge['background_color'] = $infor_idSave1[0]['background_color'];
        $arr_babrowsertabbadge['text_color']       = $infor_idSave1[0]['text_color'];
        $arr_babrowsertabbadge['upload_icon']      = $infor_idSave1[0]['upload_icon'];
        $arr_babrowsertabbadge['text']             = $infor_idSave1[0]['text'];
        $link_a = Tools::getShopProtocol();
        $link_b = Tools::getServerName();
        $link_c = 'modules/babrowsertabbadge/views/img/icon_image/';
        $link_d = $arr_babrowsertabbadge['upload_icon'];
        if ($link_d != '') {
            $link_img = $link_a. $link_b. __PS_BASE_URI__.$link_c.$link_d;
        }
        if ($link_d == '') {
            $link_c = '/modules/babrowsertabbadge/views/img/icon_image/';
            $link_d = 'favicon.ico';
            $link_img = $link_a. $link_b. __PS_BASE_URI__.$link_c.$link_d;
        }
        $this->context->smarty->assign('arr_babrowsertabbadge', $arr_babrowsertabbadge);
        $this->context->smarty->assign('link_img', $link_img);
        $this->context->smarty->assign('baismobile', $this->isMobile());
        $this->context->controller->addJS($this->_path . 'views/js/favicon.js');
        return $this->display(__FILE__, 'views/templates/admin/titleTag.tpl');
    }
    public function cookiekeymodule()
    {
        $keygooglecookie = sha1(_COOKIE_KEY_ . 'babrowsertabbadge');
        $md5file = md5($keygooglecookie);
        return $md5file;
    }
    public function getcontent()
    {
        include_once('libs/class-php-ico.php');
        $html   = "";
        $bamodule = AdminController::$currentIndex;
        $tokenli = Tools::getAdminTokenLite('AdminModules');
        $buttonDemoArr = array(
            'submitAddproduct',
        );
        if ($this->demoMode==true) {
            foreach ($buttonDemoArr as $buttonDemo) {
                if (Tools::isSubmit($buttonDemo)) {
                    Tools::redirectAdmin($bamodule.'&token='.$tokenli.'&configure='.$this->name.'&demoMode=1');
                }
            }
        }
        $demoMode=0;
        if (Tools::getValue('demoMode')=="1") {
            $demoMode=Tools::getValue('demoMode');
        }
        $this->smarty->assign('demoMode', $demoMode);
        
        $db       = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $id_lang      = $this->context->language->id;
        $arr_shop     = Shop::getShops();
        $id_shop      = $this->context->shop->id;
        $arr_language = Language::getLanguages();
        $id_lang_default = $this->context->language->id;
        $ok     = Tools::getValue('ok');
        $objlang = new Language($id_lang);
        if ($ok == 1) {
            $html .= $this->displayConfirmation($this->l('Successful add.'));
        } elseif ($ok == 2) {
            $html .= $this->displayError($this->l('The field Text is required at least in ') . $objlang->name);
        } elseif ($ok == 3) {
            $html .= $this->displayError($this->l('The uploaded image file must be formatted as *.png, *.ico.'));
        }
        if (Tools::isSubmit('submitAddproduct')) {
            $check = 0;
            $name_files = '';
            $array_fomat_img = array(".ico",".gif",".png",".ICO",".GIF",".PNG");
            if (!empty($_FILES['image']['name'])) {
                $fomat_img = strstr($_FILES['image']['name'], '.');
                $name_img = $_FILES['image']['name'];
                $strlen_fomat_img = Tools::strlen($fomat_img);
                $strlen_name_img = Tools::strlen($name_img);
                $substr_name_img = Tools::substr($name_img, 0, ($strlen_name_img-$strlen_fomat_img));
                // echo '<pre>';print_r($substr_name_img);die;
                $sourcePath = $_FILES['image']['tmp_name'];
                $targetPath = _PS_MODULE_DIR_. "babrowsertabbadge/views/img/icon_image/".$_FILES['image']['name'];
                $destination = _PS_MODULE_DIR_. "babrowsertabbadge/views/img/icon_image/".$substr_name_img.'.ico';
                if (in_array($fomat_img, $array_fomat_img) == true) {
                    $check = 0;
                    move_uploaded_file($sourcePath, $targetPath);
                    if ($fomat_img != '.ico') {
                        $name_files = $substr_name_img.'.ico';
                        $ico_lib = new PHP_ICO($targetPath, array( array( 48, 48 ) ));
                        $ico_lib->save_ico($destination);
                    }
                    if ($fomat_img == '.ico') {
                        $name_files = $_FILES['image']['name'];
                    }
                }
                if (in_array($fomat_img, $array_fomat_img) == false) {
                    $check = 2;
                }
            }
            foreach ($arr_language as $value1) {
                foreach ($arr_shop as $value2) {
                    $sql_idSave = 'SELECT * FROM '._DB_PREFIX_.'babrowsertabbadge';
                    $sql_idSave.=' WHERE idSave = 1 AND id_shop = '.(int)$id_shop .' AND id_lang = '.(int)$id_lang;
                    $infor_idSave = $db->ExecuteS($sql_idSave);
                    $background_color = Tools::getValue('background_color'.$value1['id_lang'].'');
                    $text_color       = Tools::getValue('text_color'.$value1['id_lang'].'');
                    $text             = Tools::getValue('text'.$value1['id_lang'].'');
                    if (Tools::getValue('background_color'.configuration::get('PS_LANG_DEFAULT').'') == false) {
                        $check = 1;
                    }
                    if (Tools::getValue('text_color'.configuration::get('PS_LANG_DEFAULT').'') == false) {
                        $check = 1;
                    }
                    if (Tools::getValue('text'.configuration::get('PS_LANG_DEFAULT').'') == false) {
                        $check = 1;
                    }
                    if ($background_color == false) {
                        $background_color= Tools::getValue('background_color'
                            .configuration::get('PS_LANG_DEFAULT').'');
                    }
                    if ($text_color == false) {
                        $text_color = Tools::getValue('text_color'.configuration::get('PS_LANG_DEFAULT').'');
                    }
                    if ($text == false) {
                        $text = Tools::getValue('text'.configuration::get('PS_LANG_DEFAULT').'');
                    }
                    if ($check == 0) {
                        if (empty($infor_idSave)) {
                            $sql_babrowsertabbadge = "INSERT INTO "._DB_PREFIX_."babrowsertabbadge";
                            $sql_babrowsertabbadge .= " (idSave,id_lang,id_shop,background_color,";
                            $sql_babrowsertabbadge .= " text_color,upload_icon,text)";
                            $sql_babrowsertabbadge .= " VALUES('1','".(int)$value1['id_lang']."'";
                            $sql_babrowsertabbadge .= ",'".(int)$value2['id_shop']."',";
                            $sql_babrowsertabbadge .= " '".pSQL($background_color)."','".pSQL($text_color)."',";
                            $sql_babrowsertabbadge .= " '".pSQL($name_files)."','".pSQL($text)."')";
                            $db->query($sql_babrowsertabbadge);
                        } else {
                            $sql_babrowsertabbadge = "UPDATE "._DB_PREFIX_."babrowsertabbadge";
                            $sql_babrowsertabbadge .= " SET background_color = '".pSQL($background_color)."' ,";
                            $sql_babrowsertabbadge .= " text_color = '".pSQL($text_color)."' ,";
                            $sql_babrowsertabbadge .= " upload_icon = '".pSQL($name_files)."' ,";
                            $sql_babrowsertabbadge .= " text = '".pSQL($text)."'";
                            $sql_babrowsertabbadge .= " WHERE id_shop = '".(int)$value2['id_shop']."'";
                            $sql_babrowsertabbadge .= " and id_lang = '".(int)$value1['id_lang']."'";
                            $db->query($sql_babrowsertabbadge);
                        }
                    }
                }
            }
            if ($check == 0) {
                Tools::redirectAdmin(AdminController::$currentIndex
                    . '&configure=' . $this->name .'&token='
                    . Tools::getAdminTokenLite('AdminModules').'&ok=1');
            }
            if ($check == 1) {
                Tools::redirectAdmin(AdminController::$currentIndex
                    . '&configure=' . $this->name .'&token='
                    . Tools::getAdminTokenLite('AdminModules').'&ok=2');
            }
            if ($check == 2) {
                Tools::redirectAdmin(AdminController::$currentIndex
                    . '&configure=' . $this->name .'&token='
                    . Tools::getAdminTokenLite('AdminModules').'&ok=3');
            }
        }
        $sql1_idSave1 ='SELECT * FROM '._DB_PREFIX_.'babrowsertabbadge WHERE';
        $sql1_idSave1 .= ' idSave = 1 AND id_shop = '.(int)$id_shop;
        $infor_idSave1 = $db->ExecuteS($sql1_idSave1);
        $link_icon = AdminController::$currentIndex
            . '&configure=' . $this->name .'&token='
            . Tools::getAdminTokenLite('AdminModules');
        $arr_babrowsertabbadge = array();
        $arr_babrowsertabbadge['background_color'] = $infor_idSave1[0]['background_color'];
        $arr_babrowsertabbadge['text_color']       = $infor_idSave1[0]['text_color'];
        $arr_babrowsertabbadge['upload_icon']      = $infor_idSave1[0]['upload_icon'];
        $arr_babrowsertabbadge['text']             = $infor_idSave1[0]['text'];
        foreach ($arr_language as $key => $value) {
            foreach ($infor_idSave1 as $key1 => $value1) {
                $key1;
                if ($value['id_lang'] == $value1['id_lang']) {
                    $arr_language[$key]['background_color'] = $value1['background_color'];
                    $arr_language[$key]['text_color']       = $value1['text_color'];
                    $arr_language[$key]['text']             = $value1['text'];
                }
            }
        }
        $this->context->smarty->assign('arr_babrowsertabbadge', $arr_babrowsertabbadge);
        $this->context->smarty->assign('id_lang_default', $id_lang_default);
        $this->context->smarty->assign('id_shop', $id_shop);
        $this->context->smarty->assign('arr_shop', $arr_shop);
        $this->context->smarty->assign('id_lang', $id_lang);
        $this->context->smarty->assign('arr_language', $arr_language);
        $this->context->smarty->assign('link_icon', $link_icon);
        $token_babrowsertabbadge = $this->cookiekeymodule();
        $this->context->smarty->assign('token_babrowsertabbadge', $token_babrowsertabbadge);
        $html .= $this->display(__FILE__, 'views/templates/admin/babrowsertabbadge.tpl');
        return $html;
    }
    public function isMobile()
    {
        $isMobileUA = array(
            '/iphone/i' => 'iPhone',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
        );
        $is_mobile = 0;
        foreach ($isMobileUA as $sMobileKey => $sMobileOS) {
            $sMobileOS;
            if (preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])) {
                $is_mobile = 1;
            }
        }
        return $is_mobile;
    }
    public function installSubmenu($className, $tabName, $tabParentName = true)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();
        $tabParentName = 'AdminPressMobileApp';
        $tab->position = 8;
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }
        $tab->module = $this->name;
        $tab->add();
        return $tab->save();
    }
}
