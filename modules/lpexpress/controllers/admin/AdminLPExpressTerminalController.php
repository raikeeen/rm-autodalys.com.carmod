<?php

class AdminLPExpressTerminalController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'lpexpress_terminal';
        $this->className = 'Terminal';
        $this->identifier = 'id_lpexpress_terminal';

        $this->bootstrap = true;
        parent::__construct();

        $this->bulk_actions = [];

        $this->fields_list = [
            'machineid' => [
                'title' => $this->module->l('Machine ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'remove_onclick' => true,
            ],
            'name' => [
                'title' => $this->module->l('Name'),
                'width' => 'auto',
                'remove_onclick' => true,
            ],
            'address' => [
                'title' => $this->module->l('Address'),
                'width' => 'auto',
                'remove_onclick' => true,
            ],
            'zip' => [
                'title' => $this->module->l('Zip'),
                'width' => 'auto',
                'remove_onclick' => true,
            ],
            'city' => [
                'title' => $this->module->l('City'),
                'width' => 'auto',
                'remove_onclick' => true,
            ],
            'comment' => [
                'title' => $this->module->l('Comment'),
                'width' => 'auto',
                'remove_onclick' => true,
                'search' => false,
            ],
            'inside' => [
                'title' => $this->module->l('Inside'),
                'width' => 'auto',
                'type' => 'bool',
                'remove_onclick' => true,
            ],
            'boxcount' => [
                'title' => $this->module->l('Box count'),
                'width' => 'auto',
                'remove_onclick' => true,
                'search' => false,
            ],
            'collectinghours' => [
                'title' => $this->module->l('Collect hours'),
                'width' => 'auto',
                'remove_onclick' => true,
                'search' => false,
            ],
            'workinghours' => [
                'title' => $this->module->l('Work hours'),
                'width' => 'auto',
                'remove_onclick' => true,
                'search' => false,
            ],
            'latitude' => [
                'title' => $this->module->l('Latitude'),
                'width' => 'auto',
                'remove_onclick' => true,
                'search' => false,
            ],
            'longitude' => [
                'title' => $this->module->l('Longitude'),
                'width' => 'auto',
                'remove_onclick' => true,
                'search' => false,
            ],
            'active' => [
                'title' => $this->module->l('Active'),
                'active' => 'active',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'remove_onclick' => true,
                'ajax' => true,
            ]
        ];
    }

    public function initPageHeaderToolbar()
    {
        if (Configuration::get('LP_API_CONNECTED'))
        {
            if (version_compare(_PS_VERSION_, '1.7', '<'))
            {
                $this->page_header_toolbar_btn['configuration'] = array(
                    'href' => $this->context->link->getAdminLink('AdminLPExpress'),
                    'desc' => $this->module->l('Configuration'),
                    'icon' => 'process-icon-cogs'
                );
            }

            $terminal_href = $this->context->link->getAdminLink('AdminLPExpressTerminal').'&updateTerminals=1';

            $this->page_header_toolbar_btn['update_terminal'] = array(
                'href' => $terminal_href,
                'desc' => $this->module->l('Update terminals'),
                'icon' => 'process-icon-upload'
            );
        }
        if (version_compare(_PS_VERSION_, '1.7', '>='))
        {
            if (version_compare($this->module->version, $this->module->getLatestVersion(), '<'))
            {
                $this->page_header_toolbar_btn['update_version'] = array(
                    'href' => $this->context->link->getAdminLink('AdminLPExpressUpdate').'&processUpdate=1',
                    'desc' => $this->module->l('Update to version: ').$this->module->getLatestVersion(),
                    'icon' => 'process-icon-import'
                );
            }
            else
            {
                $this->page_header_toolbar_btn['update_version'] = array(
                    'href' => $this->context->link->getAdminLink('AdminLPExpressUpdate').'&checkForUpdate=1',
                    'desc' => $this->module->l('Check for update'),
                    'icon' => 'process-icon-import'
                );
            }
        }

        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('updateTerminals'))
        {
            if (!Configuration::get('LP_API_CONNECTED'))
            {
                $this->errors['api_connected'] = $this->module->l('You must enter correct API authentication data before updating terminals');
            }
            else
            {
                $errors = $this->module->updateTerminals();
                if (!$errors)
                {
                    Tools::redirectAdmin($this->context->link->getAdminLink($this->controller_name).'&conf=4');
                }
                $this->errors = array_merge($this->errors, $errors);
            }
        }
    }

    public function initContent()
    {
        if (Configuration::get('LP_API_CONNECTED'))
        {
            $token = Configuration::get('LP_CRON_TOKEN');
            $url = $this->context->link->getModuleLink($this->module->name, 'cron', ['token' => $token]);

            $this->displayInformation($this->module->l('Add this link to cron task for automatic update terminal list: ').'<b>'.$url.'</b>');
        }
        else
        {
            $this->displayWarning($this->module->l('Fill authentication information before updating terminals.'));
        }

        if (Configuration::get('LP_TERMINAL_LAST_UPDATE'))
        {
            $date = Configuration::get('LP_TERMINAL_LAST_UPDATE');
            $this->displayInformation($this->module->l('Last terminal update: ').date('Y-m-d H:i', $date));
        }

        parent::initContent();
    }

    public function renderList()
    {
        $this->toolbar_btn = [];
        return parent::renderList();
    }

    public function ajaxProcessactivelpexpressTerminal()
    {
        if (Terminal::toggleActive(Tools::getValue('id_lpexpress_terminal')))
        {
            die(json_encode([
                'success' => true,
                'text' => $this->module->l('The status has been updated successfully')
            ]));
        }
        else
        {
            die(json_encode([
                'success' => false,
                'error' => true,
                'text' => $this->module->l('Error occure while changed terminal status')
            ]));
        }
    }
}