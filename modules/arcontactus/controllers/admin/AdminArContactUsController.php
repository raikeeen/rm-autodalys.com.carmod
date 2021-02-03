<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

include_once dirname(__FILE__).'/../../classes/ArContactUsTable.php';

class AdminArContactUsController extends ModuleAdminController
{
    public function __construct()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();
        $this->meta_title = $this->l('Contact us');
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }
    
    public function ajaxProcessUpdateOrder()
    {
        $data = Tools::getValue('data');
        foreach ($data as $item) {
            $k = explode('_', $item);
            Db::getInstance()->update(ArContactUsTable::TABLE_NAME, array(
                'position' => (int)$k[1]
            ), 'id_contactus = ' . (int)$k[0]);
        }
        $this->module->clearCache();
        die(Tools::jsonEncode(array()));
    }
    
    public function ajaxProcessDelete()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsTable($id);
        $model->active = 0;
        $this->module->clearCache();
        die(Tools::jsonEncode(array(
            'success' => $model->delete()
        )));
    }
    
    public function ajaxProcessSave()
    {
        $data = Tools::getValue('data');
        $id = (int)Tools::getValue('id');
        $title = array();
        $subtitle = array();
        $errors = array();
        if ($id) {
            $model = new ArContactUsTable($id);
        } else {
            $model = new ArContactUsTable();
            $model->position = ArContactUsTable::getLastPosition() + 1;
        }
        
        foreach ($data as $param) {
            if (Tools::strpos($param['name'], 'title') !== false) {
                $lang = str_replace('title_', '', $param['name']);
                $title[$lang] = str_replace('\n', "\n", pSQL($param['value']));
            }
            if (Tools::strpos($param['name'], 'subtitle') !== false) {
                $lang = str_replace('subtitle_', '', $param['name']);
                $subtitle[$lang] = str_replace('\n', "\n", pSQL($param['value']));
            }
            if ($param['name'] == 'icon') {
                $model->icon = pSQL($param['value']);
            }
            if ($param['name'] == 'color') {
                $model->color = pSQL($param['value']);
            }
            if ($param['name'] == 'link') {
                $model->link = pSQL($param['value']);
            }
            if ($param['name'] == 'js') {
                $model->js = str_replace('\n', PHP_EOL, pSQL($param['value']));
            }
            if ($param['name'] == 'type') {
                $model->type = (int)$param['value'];
            }
            if ($param['name'] == 'product_page') {
                $model->product_page = (int)$param['value'];
            }
            if ($param['name'] == 'integration') {
                $model->integration = pSQL($param['value']);
            }
            if ($param['name'] == 'display') {
                $model->display = (int)$param['value'];
            }
            if ($param['name'] == 'registered_only') {
                $model->registered_only = (int)$param['value'];
            }
            if ($param['name'] == 'always') {
                $model->always = (int)$param['value'];
            }
            if ($param['name'] == 'time_from') {
                $model->time_from = $param['value'];
            }
            if ($param['name'] == 'time_to') {
                $model->time_to = $param['value'];
            }
            if (in_array($param['name'], array('d1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd7'))) {
                $field = $param['name'];
                $model->$field = (int)$param['value'];
            }
            if ($param['name'] == 'target') {
                $model->target = (int)$param['value'];
            }
        }
        $model->title = $title;
        $model->subtitle = $subtitle;
        
        $modelErrors = $model->validateFields(false, true);
        
        switch ($model->type) {
            case ArContactUsTable::TYPE_LINK:
                if (Tools::isEmpty($model->link)) {
                    $errors['link'] = $this->module->l('Link field is required');
                }
                break;
            case ArContactUsTable::TYPE_INTEGRATION:
                if (Tools::isEmpty($model->integration)) {
                    $errors['integration'] = $this->module->l('Integration field is required');
                }
                break;
            case ArContactUsTable::TYPE_JS:
                if (Tools::isEmpty($model->js)) {
                    $errors['js'] = $this->module->l('Custom javascript field is required');
                }
                break;
        }
        
        if ($modelErrors !== true) {
            $errors = array_merge($errors, $modelErrors);
        }
        if ($errors) {
            die(Tools::jsonEncode(array(
                'success' => 0,
                'errors' => $errors
            )));
        }
        $model->save();
        $this->module->clearCache();
        die(Tools::jsonEncode(array(
            'success' => 1,
            'errors' => $errors,
            'model' => $model
        )));
    }
    
    public function ajaxProcessSwitch()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsTable($id);
        $model->status = !$model->status;
        $model->save();
        $this->module->clearCache();
        die(Tools::jsonEncode(array(
            'active' => (int)$model->status
        )));
    }
    
    public function ajaxProcessSwitchProduct()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsTable($id);
        $model->product_page = !$model->product_page;
        $model->save();
        $this->module->clearCache();
        die(Tools::jsonEncode(array(
            'active' => (int)$model->product_page
        )));
    }
    
    public function ajaxProcessReload()
    {
        die(Tools::jsonEncode(array(
            'content' => $this->module->renderTable()
        )));
    }
    
    public function ajaxProcessReloadCallbacks()
    {
        die(Tools::jsonEncode(array(
            'content' => $this->module->renderCallbackTable()
        )));
    }
    
    public function ajaxProcessCallbackSwitch()
    {
        $id = Tools::getValue('id');
        $status = Tools::getValue('status');
        $model = new ArContactUsCallback($id);
        $model->status = $status;
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();
        die(Tools::jsonEncode(array(
            'active' => (int)$model->status
        )));
    }
    
    public function ajaxProcessCallbackDelete()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsCallback($id);
        die(Tools::jsonEncode(array(
            'success' => $model->delete()
        )));
    }
    
    public function ajaxProcessEdit()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsTable($id);
        $model->js = str_replace('\\', '', $model->js);
        die(Tools::jsonEncode($model));
    }
    
    public function ajaxProcessTime()
    {
        die(Tools::jsonEncode(array('time' => date('H:i:s'))));
    }
}
