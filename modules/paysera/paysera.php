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

if (!class_exists('WebToPay')) {
    require_once(dirname(__FILE__).'/lib/WebToPay/WebToPay.php');
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class Paysera
 */
class Paysera extends PaymentModule
{
    /**
     * Default language code
     */
    const DEFAULT_LANG = 'en';

    /**
     * Quality sign js link
     */
    const QUALITY_SIGN_JS = 'https://bank.paysera.com/new/js/project/wtpQualitySigns.js';

    /**
     * @var array
     */
    protected $availableLang = array('lt', 'lv', 'ru', 'en', 'pl', 'bg', 'ee');

    /**
     * @var string
     */
    protected $html;

    /**
     * @var string
     */
    protected $selectedTab;

    /**
     * Paysera constructor.
     */
    public function __construct()
    {
        $this->name = 'paysera';
        $this->version = '2.0.6';
        $this->tab = 'payments_gateways';
        $this->compatibility = array('min' => '1.7.0', 'max' => _PS_VERSION_);
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);
        $this->module_key = 'b830e1e952dfce7551c31477a86221af';
        $this->author = 'Paysera';

        $this->controllers = array('redirect', 'callback', 'accept', 'cancel');
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Paysera');
        $this->description = $this->l('Accept payments by Paysera system.');
    }

    /**
     * @return bool
     */
    public function install()
    {
        $this->warning = null;
        if (is_null($this->warning)
            && !(parent::install()
                && $this->registerHook('paymentOptions')
                && $this->registerHook('displayHeader'))) {
            if ($this->l('ERROR_MESSAGE_INSTALL_MODULE') == "ERROR_MESSAGE_INSTALL_MODULE") {
                $this->warning = "There was an Error installing the module.";
            } else {
                $this->warning = $this->l('ERROR_MESSAGE_INSTALL_MODULE');
            }
        }

        $defaultVal = $this->getDefaultValues();
        Configuration::updateValue('PAYSERA_GENERAL_PROJECT_ID', '');
        Configuration::updateValue('PAYSERA_GENERAL_SIGN_PASS', '');
        Configuration::updateValue('PAYSERA_GENERAL_TEST_MODE', '');
        Configuration::updateValue('PAYSERA_EXTRA_TITLE', $defaultVal['title']);
        Configuration::updateValue('PAYSERA_EXTRA_DESCRIPTION', $defaultVal['desc']);
        Configuration::updateValue('PAYSERA_EXTRA_LIST_OF_PAYMENTS', '');
        Configuration::updateValue('PAYSERA_EXTRA_SPECIFIC_COUNTRIES', '');
        Configuration::updateValue('PAYSERA_EXTRA_SPECIFIC_COUNTRIES_GROUP', $defaultVal['countries']);
        Configuration::updateValue('PAYSERA_EXTRA_GRIDVIEW', '');
        Configuration::updateValue('PAYSERA_EXTRA_FORCE_LOGIN', '');
        Configuration::updateValue('PAYSERA_ORDER_STATUS_NEW', '');
        Configuration::updateValue('PAYSERA_ORDER_STATUS_PAID', '');
        Configuration::updateValue('PAYSERA_ORDER_STATUS_PENDING', '');
        Configuration::updateValue('PAYSERA_ADDITIONS_QUALITY_SIGN', '');
        Configuration::updateValue('PAYSERA_ADDITIONS_OWNERSHIP', '');
        Configuration::updateValue('PAYSERA_ADDITIONS_OWNERSHIP_CODE', '');

        if (is_null($this->warning) && !$this->addPayseraOrderStatus()) {
            if ($this->l('ERROR_MESSAGE_CREATE_ORDER_STATUS') == "ERROR_MESSAGE_CREATE_ORDER_STATUS") {
                $this->warning = "There was an Error creating a custom order status.";
            } else {
                $this->warning = $this->l('ERROR_MESSAGE_CREATE_ORDER_STATUS');
            }
        }

        return is_null($this->warning);
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        $values = array();

        if ($this->l('PAYSERA_EXTRA_TITLE_DEFAULT') == "PAYSERA_EXTRA_TITLE_DEFAULT") {
            $values['title'] = "Pay using Paysera";
        } else {
            $values['title'] = $this->l('PAYSERA_EXTRA_TITLE_DEFAULT');
        }
        if ($this->l('PAYSERA_EXTRA_DESCRIPTION_DEFAULT') == "PAYSERA_EXTRA_DESCRIPTION_DEFAULT") {
            $values['desc'] = "Redirect to payment methods page";
        } else {
            $values['desc'] = $this->l('PAYSERA_EXTRA_DESCRIPTION_DEFAULT');
        }
        if ($this->l('PAYSERA_ORDER_STATUS_NEW') == "PAYSERA_ORDER_STATUS_NEW") {
            $values['status_new'] = "Awaiting Paysera confirmation";
        } else {
            $values['status_new'] = $this->l('PAYSERA_ORDER_STATUS_NEW');
        }
        if ($this->l('PAYSERA_ORDER_STATUS_PAID') == "PAYSERA_ORDER_STATUS_PAID") {
            $values['status_paid'] = "Paysera confirmed payment";
        } else {
            $values['status_paid'] = $this->l('PAYSERA_ORDER_STATUS_PAID');
        }
        if ($this->l('PAYSERA_ORDER_STATUS_PENDING') == "PAYSERA_ORDER_STATUS_PENDING") {
            $values['status_pending'] = "Awaiting Paysera payment";
        } else {
            $values['status_pending'] = $this->l('PAYSERA_ORDER_STATUS_PENDING');
        }
        $values['countries'] = '[""]';

        return $values;
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        $tabs = array(
            array(
                'name' => $this->l('Paysera Gateway'),
                'class_name' => 'AdminPayseraConfiguration',
                'ParentClassName' => 'AdminParentPayment',
            )
        );

        return $tabs;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        if (!$this->removePayseraOrderStatus()
            || !Configuration::deleteByName('PAYSERA_GENERAL_PROJECT_ID')
            || !Configuration::deleteByName('PAYSERA_GENERAL_SIGN_PASS')
            || !Configuration::deleteByName('PAYSERA_GENERAL_TEST_MODE')
            || !Configuration::deleteByName('PAYSERA_EXTRA_TITLE')
            || !Configuration::deleteByName('PAYSERA_EXTRA_DESCRIPTION')
            || !Configuration::deleteByName('PAYSERA_EXTRA_LIST_OF_PAYMENTS')
            || !Configuration::deleteByName('PAYSERA_EXTRA_SPECIFIC_COUNTRIES')
            || !Configuration::deleteByName('PAYSERA_EXTRA_SPECIFIC_COUNTRIES_GROUP')
            || !Configuration::deleteByName('PAYSERA_EXTRA_GRIDVIEW')
            || !Configuration::deleteByName('PAYSERA_EXTRA_FORCE_LOGIN')
            || !Configuration::deleteByName('PAYSERA_ORDER_STATUS_NEW')
            || !Configuration::deleteByName('PAYSERA_ORDER_STATUS_PAID')
            || !Configuration::deleteByName('PAYSERA_ORDER_STATUS_PENDING')
            || !Configuration::deleteByName('PAYSERA_ADDITIONS_QUALITY_SIGN')
            || !Configuration::deleteByName('PAYSERA_ADDITIONS_OWNERSHIP')
            || !Configuration::deleteByName('PAYSERA_ADDITIONS_OWNERSHIP_CODE')


            || !$this->unregisterHook('paymentOptions')
            || !$this->unregisterHook('displayHeader')
            || !parent::uninstall()) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function addPayseraOrderStatus()
    {
        $defaultVal = $this->getDefaultValues();
        $stateConfig = array();
        try {
            $stateConfig['color'] = 'lightblue';
            $this->addOrderStatus(
                'PAYSERA_ORDER_STATUS_PENDING',
                $defaultVal['status_pending'],
                $stateConfig
            );
            $stateConfig['color'] = 'cadetblue';
            $this->addOrderStatus(
                'PAYSERA_ORDER_STATUS_NEW',
                $defaultVal['status_new'],
                $stateConfig
            );
            $stateConfig['color'] = 'darkseagreen';
            $this->addOrderStatus(
                'PAYSERA_ORDER_STATUS_PAID',
                $defaultVal['status_paid'],
                $stateConfig,
                true
            );
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $configKey
     * @param string $statusName
     * @param array  $stateConfig
     * @param bool   $paid
     */
    public function addOrderStatus($configKey, $statusName, $stateConfig, $paid = false)
    {
        $orderState = new OrderState();
        $orderState->name = array();
        $orderState->module_name = $this->name;
        $orderState->send_email = true;
        $orderState->color = $stateConfig['color'];
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = true;
        $orderState->paid = $paid;
        foreach (Language::getLanguages() as $language) {
            $orderState->template[$language['id_lang']] = 'payment';
            $orderState->name[$language['id_lang']] = $statusName;
        }

        $orderState->add();

        Configuration::updateValue($configKey, $orderState->id);
    }

    /**
     * @return bool
     */
    public function removePayseraOrderStatus()
    {
        try {
            $this->removeOrderStatus('PAYSERA_ORDER_STATUS_PENDING');
            $this->removeOrderStatus('PAYSERA_ORDER_STATUS_NEW');
            $this->removeOrderStatus('PAYSERA_ORDER_STATUS_PAID');
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $configKey
     *
     * @return bool
     */
    public function removeOrderStatus($configKey)
    {
        $orderStateId = Configuration::get($configKey);
        $orderState   = new OrderState($orderStateId);

        if (!Validate::isLoadedObject($orderState)) {
            return true;
        }

        return $orderState->delete();
    }

    /**
     * Redirect to configuration controller
     */
    public function getContent()
    {
        $shopDomainSsl = Tools::getShopDomainSsl(true, true);
        $backOfficeJsUrl = $shopDomainSsl.__PS_BASE_URI__.'modules/'.$this->name.'/views/js/backoffice.js';
        $backOfficeCssUrl = $shopDomainSsl.__PS_BASE_URI__.'modules/'.$this->name.'/views/css/backoffice.css';

        $tplVars = array(
            'settingsTitle'    => $this->l('Configuration'),
            'tabs'             => $this->getConfigurationTabs(),
            'selectedTab'      => $this->getSelectedTab(),
            'backOfficeJsUrl'  => $backOfficeJsUrl,
            'backOfficeCssUrl' => $backOfficeCssUrl
        );

        if (isset($this->context->cookie->payseraConfigMessage)) {
            $tplVars['message']['success'] = $this->context->cookie->payseraMessageSuccess;
            $tplVars['message']['text'] = $this->context->cookie->payseraConfigMessage;
            unset($this->context->cookie->payseraConfigMessage);
        } else {
            $tplVars['message'] = false;
        }

        $this->context->smarty->assign($tplVars);

        return $this->display(__FILE__, 'views/templates/admin/tabs.tpl');
    }

    /**
     * @return array
     */
    protected function getConfigurationTabs()
    {
        $tabs = array();

        $tabs[] = array(
            'id'      => 'general_setting',
            'title'   => $this->l('Main Settings'),
            'content' => $this->getGeneralSettingTemplate()
        );

        $payseraProjectID = Configuration::get('PAYSERA_GENERAL_PROJECT_ID');

        if (!empty($payseraProjectID)) {
            $tabs[] = array(
                'id'      => 'extra_setting',
                'title'   => $this->l('Extra Settings'),
                'content' => $this->getExtraSettingTemplate()
            );

            $tabs[] = array(
                'id'      => 'status_setting',
                'title'   => $this->l('Status Settings'),
                'content' => $this->getStatusSettingTemplate()
            );

            $tabs[] = array(
                'id'      => 'additions_setting',
                'title'   => $this->l('Project additions'),
                'content' => $this->getAdditionsSettingTemplate()
            );
        }

        return $tabs;
    }

    /**
     * @return string
     */
    protected function getSelectedTab()
    {
        if ($this->selectedTab) {
            return $this->selectedTab;
        }

        if (Tools::getValue('selected_tab')) {
            return Tools::getValue('selected_tab');
        }

        return 'general_setting';
    }

    /**
     * @return string
     */
    protected function getGeneralSettingTemplate()
    {
        $translations = $this->getGeneralSettingTranslations();

        if (Tools::isSubmit('btnGeneralSubmit')) {
            $this->validateGeneralSetting($translations);
            $this->selectedTab = 'general_setting';
        }

        $render = $this->renderGeneralSettingForm($translations);

        $this->setHtml($render);

        return $this->getHtml();
    }

    /**
     * @param array $translations
     */
    protected function validateGeneralSetting($translations)
    {
        if (Tools::isSubmit('btnGeneralSubmit')) {
            $warning        = '';
            $isRequired     = false;
            $isNumeric      = false;
            $fieldsRequired = array();
            $fieldsNumeric  = array();

            if (trim(Tools::getValue('PAYSERA_GENERAL_PROJECT_ID')) == '') {
                $fieldsRequired[] = $translations['project_id']['label'];
                $isRequired = true;
            }

            if (trim(Tools::getValue('PAYSERA_GENERAL_SIGN_PASS')) == '') {
                $fieldsRequired[] = $translations['sign_pass']['label'];
                $isRequired = true;
            }

            if (!is_numeric(Tools::getValue('PAYSERA_GENERAL_PROJECT_ID'))) {
                $fieldsNumeric[] = $translations['project_id']['label'];
                $isNumeric = true;
            }

            if ($isRequired) {
                $warning .= implode(', ', $fieldsRequired) . ' ';
                if ($this->l('ERROR_MANDATORY') == "ERROR_MANDATORY") {
                    $warning .= "is required. Please fill out this field.";
                } else {
                    $warning .= $this->l('ERROR_MANDATORY');
                }
            }

            if ($isNumeric) {
                $warning .= implode(', ', $fieldsNumeric) . ' ';
                if ($this->l('ERROR_NUMERIC') == "ERROR_NUMERIC") {
                    $warning .= "is not number. Please check input.";
                } else {
                    $warning .= $this->l('ERROR_NUMERIC');
                }
            }

            if (!$isRequired && !$isNumeric) {
                $this->updateGeneralSetting();
            } else {
                $this->context->cookie->payseraMessageSuccess = false;
                $this->context->cookie->payseraConfigMessage = $warning;
            }
        }
    }

    /**
     * @return array
     */
    protected function getGeneralSettingTranslations()
    {
        $translations = array();

        if ($this->l('PAYSERA_GENERAL_PROJECT_ID') == "PAYSERA_GENERAL_PROJECT_ID") {
            $translations['project_id']['label'] = "Project ID";
        } else {
            $translations['project_id']['label'] = $this->l('PAYSERA_GENERAL_PROJECT_ID');
        }
        if ($this->l('PAYSERA_GENERAL_SIGN_PASS') == "PAYSERA_GENERAL_SIGN_PASS") {
            $translations['sign_pass']['label'] = "Sign";
        } else {
            $translations['sign_pass']['label'] = $this->l('PAYSERA_GENERAL_SIGN_PASS');
        }
        if ($this->l('PAYSERA_GENERAL_TEST_MODE') == "PAYSERA_GENERAL_TEST_MODE") {
            $translations['test']['label'] = "Test";
        } else {
            $translations['test']['label'] = $this->l('PAYSERA_GENERAL_TEST_MODE');
        }

        if ($this->l('PAYSERA_GENERAL_PROJECT_ID_DESC') == "PAYSERA_GENERAL_PROJECT_ID_DESC") {
            $translations['project_id']['desc'] = "Paysera project id";
        } else {
            $translations['project_id']['desc'] = $this->l('PAYSERA_GENERAL_PROJECT_ID_DESC');
        }
        if ($this->l('PAYSERA_GENERAL_SIGN_PASS_DESC') == "PAYSERA_GENERAL_SIGN_PASS_DESC") {
            $translations['sign_pass']['desc'] = "Paysera project password";
        } else {
            $translations['sign_pass']['desc'] = $this->l('PAYSERA_GENERAL_SIGN_PASS_DESC');
        }
        if ($this->l('PAYSERA_GENERAL_TEST_MODE_DESC') == "PAYSERA_GENERAL_TEST_MODE_DESC") {
            $translations['test']['desc'] = "Enable this to accept test payments";
        } else {
            $translations['test']['desc'] = $this->l('PAYSERA_GENERAL_TEST_MODE_DESC');
        }

        if ($this->l('BACKEND_GENERAL_SAVE') == "BACKEND_GENERAL_SAVE") {
            $translations['save'] = "Save main settings";
        } else {
            $translations['save'] = $this->l('BACKEND_GENERAL_SAVE');
        }

        return $translations;
    }

    protected function updateGeneralSetting()
    {
        if (Tools::isSubmit('btnGeneralSubmit')) {
            Configuration::updateValue(
                'PAYSERA_GENERAL_PROJECT_ID',
                Tools::getValue('PAYSERA_GENERAL_PROJECT_ID')
            );
            Configuration::updateValue(
                'PAYSERA_GENERAL_SIGN_PASS',
                Tools::getValue('PAYSERA_GENERAL_SIGN_PASS')
            );
            Configuration::updateValue(
                'PAYSERA_GENERAL_TEST_MODE',
                Tools::getValue('PAYSERA_GENERAL_TEST_MODE')
            );

            if ($this->l('PAYSERA_SUCCESS_GENERAL_SETTING') == "PAYSERA_SUCCESS_GENERAL_SETTING") {
                $successMessage = "Your Paysera settings were successfully updated.";
            } else {
                $successMessage = $this->l('PAYSERA_SUCCESS_GENERAL_SETTING');
            }

            $this->context->cookie->payseraMessageSuccess = true;
            $this->context->cookie->payseraConfigMessage = $successMessage;
        }
    }

    /**
     * @param array $translations
     *
     * @return string
     */
    protected function renderGeneralSettingForm($translations)
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang =  Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang =  0;
        }
        $this->fields_form = array();
        $this->fields_form = $this->getGeneralSettingForm($translations);
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnGeneralSubmit';
        $helper->currentIndex = $this->getAdminModuleLink();
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getGeneralSetting(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm($this->fields_form);
    }

    /**
     * @param array $translations
     *
     * @return array
     */
    protected function getGeneralSettingForm($translations)
    {
        $generalForm = array();
        $generalForm[] = array(
            'form' => array(
                'input' => array(
                    $this->getTextForm(
                        'PAYSERA_GENERAL_PROJECT_ID',
                        $translations['project_id'],
                        true
                    ),
                    $this->getTextForm(
                        'PAYSERA_GENERAL_SIGN_PASS',
                        $translations['sign_pass'],
                        true
                    ),
                    $this->getBoolForm(
                        'PAYSERA_GENERAL_TEST_MODE',
                        $translations['test']
                    )
                ),
                'submit' => array(
                    'title' => $translations['save']
                )
            )
        );

        return $generalForm;
    }

    /**
     * @return array
     */
    protected function getGeneralSetting()
    {
        $configProjectID = Configuration::get('PAYSERA_GENERAL_PROJECT_ID');
        $configSignPass  = Configuration::get('PAYSERA_GENERAL_SIGN_PASS');
        $configTest      = Configuration::get('PAYSERA_GENERAL_TEST_MODE');

        $generalSetting = array();
        $generalSetting['PAYSERA_GENERAL_PROJECT_ID'] =
            Tools::getValue('PAYSERA_GENERAL_PROJECT_ID', $configProjectID);
        $generalSetting['PAYSERA_GENERAL_SIGN_PASS'] =
            Tools::getValue('PAYSERA_GENERAL_SIGN_PASS', $configSignPass);
        $generalSetting['PAYSERA_GENERAL_TEST_MODE'] =
            Tools::getValue('PAYSERA_GENERAL_TEST_MODE', $configTest);

        return $generalSetting;
    }

    /**
     * @return string
     */
    protected function getExtraSettingTemplate()
    {
        $translations = $this->getExtraSettingTranslations();

        if (Tools::isSubmit('btnExtraSubmit')) {
            $this->validateExtraSetting();
            $this->selectedTab = 'extra_setting';
        }

        $render = $this->renderExtraSettingForm($translations);

        $this->setHtml($render);

        return $this->getHtml();
    }

    /**
     * @return array
     */
    protected function getExtraSettingTranslations()
    {
        $translations = array();

        if ($this->l('PAYSERA_EXTRA_TITLE') == "PAYSERA_EXTRA_TITLE") {
            $translations['title']['label'] = "Title";
        } else {
            $translations['title']['label'] = $this->l('PAYSERA_EXTRA_TITLE');
        }
        if ($this->l('PAYSERA_EXTRA_DESCRIPTION') == "PAYSERA_EXTRA_DESCRIPTION") {
            $translations['desc']['label'] = "Description";
        } else {
            $translations['desc']['label'] = $this->l('PAYSERA_EXTRA_DESCRIPTION');
        }
        if ($this->l('PAYSERA_EXTRA_LIST_OF_PAYMENTS') == "PAYSERA_EXTRA_LIST_OF_PAYMENTS") {
            $translations['list']['label'] = "List of payments";
        } else {
            $translations['list']['label'] = $this->l('PAYSERA_EXTRA_LIST_OF_PAYMENTS');
        }
        if ($this->l('PAYSERA_EXTRA_SPECIFIC_COUNTRIES') == "PAYSERA_EXTRA_SPECIFIC_COUNTRIES") {
            $translations['countries']['label'] = "Specific countries";
        } else {
            $translations['countries']['label'] = $this->l('PAYSERA_EXTRA_SPECIFIC_COUNTRIES');
        }
        if ($this->l('PAYSERA_EXTRA_GRIDVIEW') == "PAYSERA_EXTRA_GRIDVIEW") {
            $translations['grid']['label'] = "Grid view";
        } else {
            $translations['grid']['label'] = $this->l('PAYSERA_EXTRA_GRIDVIEW');
        }
        if ($this->l('PAYSERA_EXTRA_FORCE_LOGIN') == "PAYSERA_EXTRA_FORCE_LOGIN") {
            $translations['force_login']['label'] = "Force Login";
        } else {
            $translations['force_login']['label'] = $this->l('PAYSERA_EXTRA_FORCE_LOGIN');
        }

        if ($this->l('PAYSERA_EXTRA_TITLE_DESC') == "PAYSERA_EXTRA_TITLE_DESC") {
            $translations['title']['desc'] = "Payment method title that the customer will see on your website.";
        } else {
            $translations['title']['desc'] = $this->l('PAYSERA_EXTRA_TITLE_DESC');
        }
        if ($this->l('PAYSERA_EXTRA_DESCRIPTION_DESC') == "PAYSERA_EXTRA_DESCRIPTION_DESC") {
            $translations['desc']['desc'] = "This controls the description which the user sees during checkout.";
        } else {
            $translations['desc']['desc'] = $this->l('PAYSERA_EXTRA_DESCRIPTION_DESC');
        }
        if ($this->l('PAYSERA_EXTRA_LIST_OF_PAYMENTS_DESC') == "PAYSERA_EXTRA_LIST_OF_PAYMENTS_DESC") {
            $translations['list']['desc'] = "Enable this to display payment methods list at checkout page";
        } else {
            $translations['list']['desc'] = $this->l('PAYSERA_EXTRA_LIST_OF_PAYMENTS_DESC');
        }
        if ($this->l('PAYSERA_EXTRA_SPECIFIC_COUNTRIES_DESC') == "PAYSERA_EXTRA_SPECIFIC_COUNTRIES_DESC") {
            $translations['countries']['desc'] = "Select which country payment methods to display (empty means all)";
        } else {
            $translations['countries']['desc'] = $this->l('PAYSERA_EXTRA_SPECIFIC_COUNTRIES_DESC');
        }
        if ($this->l('PAYSERA_EXTRA_GRIDVIEW_DESC') == "PAYSERA_EXTRA_GRIDVIEW_DESC") {
            $translations['grid']['desc'] = "Enable this to use payments gridview";
        } else {
            $translations['grid']['desc'] = $this->l('PAYSERA_EXTRA_GRIDVIEW_DESC');
        }
        if ($this->l('PAYSERA_EXTRA_FORCE_LOGIN_DESC') == "PAYSERA_EXTRA_FORCE_LOGIN_DESC") {
            $translations['force_login']['desc'] = "Enable this to force customer to login on checkout";
        } else {
            $translations['force_login']['desc'] = $this->l('PAYSERA_EXTRA_FORCE_LOGIN_DESC');
        }

        if ($this->l('BACKEND_EXTRA_SAVE') == "BACKEND_EXTRA_SAVE") {
            $translations['save'] = "Save extra settings";
        } else {
            $translations['save'] = $this->l('BACKEND_EXTRA_SAVE');
        }

        return $translations;
    }

    protected function validateExtraSetting()
    {
        if (Tools::isSubmit('btnExtraSubmit')) {
            $isRequired = false;
            $fieldsRequired = array();

            if ($isRequired) {
                $warning = implode(', ', $fieldsRequired) . ' ';
                if ($this->l('ERROR_MANDATORY') == "ERROR_MANDATORY") {
                    $warning .= "is required. please fill out this field";
                } else {
                    $warning .= $this->l('ERROR_MANDATORY');
                }
                $this->context->cookie->payseraMessageSuccess = false;
                $this->context->cookie->payseraConfigMessage = $warning;
            } else {
                $this->updateExtraSetting();
            }
        }
    }

    protected function updateExtraSetting()
    {
        if (Tools::isSubmit('btnExtraSubmit')) {
            Configuration::updateValue(
                'PAYSERA_EXTRA_TITLE',
                Tools::getValue('PAYSERA_EXTRA_TITLE')
            );
            Configuration::updateValue(
                'PAYSERA_EXTRA_DESCRIPTION',
                Tools::getValue('PAYSERA_EXTRA_DESCRIPTION')
            );
            Configuration::updateValue(
                'PAYSERA_EXTRA_LIST_OF_PAYMENTS',
                Tools::getValue('PAYSERA_EXTRA_LIST_OF_PAYMENTS')
            );
            Configuration::updateValue(
                'PAYSERA_EXTRA_SPECIFIC_COUNTRIES_GROUP',
                json_encode(Tools::getValue('PAYSERA_EXTRA_SPECIFIC_COUNTRIES'))
            );
            Configuration::updateValue(
                'PAYSERA_EXTRA_GRIDVIEW',
                Tools::getValue('PAYSERA_EXTRA_GRIDVIEW')
            );
            Configuration::updateValue(
                'PAYSERA_EXTRA_FORCE_LOGIN',
                Tools::getValue('PAYSERA_EXTRA_FORCE_LOGIN')
            );

            if ($this->l('PAYSERA_SUCCESS_EXTRA_SETTING') == "PAYSERA_SUCCESS_EXTRA_SETTING") {
                $successMessage = "Your Paysera settings were successfully updated.";
            } else {
                $successMessage = $this->l('PAYSERA_SUCCESS_EXTRA_SETTING');
            }

            $this->context->cookie->payseraMessageSuccess = true;
            $this->context->cookie->payseraConfigMessage = $successMessage;
        }
    }

    /**
     * @param array $translations
     *
     * @return string
     */
    protected function renderExtraSettingForm($translations)
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang =  Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang =  0;
        }
        $fields_form = $this->getExtraSettingForm($translations);
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnExtraSubmit';
        $helper->currentIndex = $this->getAdminModuleLink();
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getExtraSetting(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm($fields_form);
    }

    /**
     * @param array $translations
     *
     * @return array
     */
    protected function getExtraSettingForm($translations)
    {
        $extraForm = array();
        $extraForm[] = array(
            'form' => array(
                'input' => array(
                    $this->getTextForm(
                        'PAYSERA_EXTRA_TITLE',
                        $translations['title']
                    ),
                    $this->getTextareaForm(
                        'PAYSERA_EXTRA_DESCRIPTION',
                        $translations['desc']
                    ),
                    $this->getBoolForm(
                        'PAYSERA_EXTRA_LIST_OF_PAYMENTS',
                        $translations['list']
                    ),
                    $this->getSelectForm(
                        'PAYSERA_EXTRA_SPECIFIC_COUNTRIES',
                        $translations['countries'],
                        $this->getCountries(),
                        false,
                        true
                    ),
                    $this->getBoolForm(
                        'PAYSERA_EXTRA_GRIDVIEW',
                        $translations['grid']
                    ),
                    $this->getBoolForm(
                        'PAYSERA_EXTRA_FORCE_LOGIN',
                        $translations['force_login']
                    ),
                ),
                'submit' => array(
                    'title' => $translations['save']
                )
            )
        );

        return $extraForm;
    }

    /**
     * @return array
     */
    protected function getExtraSetting()
    {
        $configTitle        = Configuration::get('PAYSERA_EXTRA_TITLE');

        $configDesc         = Configuration::get('PAYSERA_EXTRA_DESCRIPTION');
        $configPaymentsList = Configuration::get('PAYSERA_EXTRA_LIST_OF_PAYMENTS');
        $configGrid         = Configuration::get('PAYSERA_EXTRA_GRIDVIEW');
        $configForceLogin   = Configuration::get('PAYSERA_EXTRA_FORCE_LOGIN');

        $multiSelectValues = $this->getMultipleFieldValues('PAYSERA_EXTRA_SPECIFIC_COUNTRIES');

        $extraSetting = array();
        $extraSetting['PAYSERA_EXTRA_TITLE'] =
            Tools::getValue('PAYSERA_EXTRA_TITLE', $configTitle);
        $extraSetting['PAYSERA_EXTRA_DESCRIPTION'] =
            Tools::getValue('PAYSERA_EXTRA_DESCRIPTION', $configDesc);
        $extraSetting['PAYSERA_EXTRA_LIST_OF_PAYMENTS'] =
            Tools::getValue('PAYSERA_EXTRA_LIST_OF_PAYMENTS', $configPaymentsList);
        $extraSetting['PAYSERA_EXTRA_GRIDVIEW'] =
            Tools::getValue('PAYSERA_EXTRA_GRIDVIEW', $configGrid);
        $extraSetting['PAYSERA_EXTRA_FORCE_LOGIN'] =
            Tools::getValue('PAYSERA_EXTRA_FORCE_LOGIN', $configForceLogin);
        $extraSetting[$multiSelectValues['name']] = $multiSelectValues['values'];

        return $extraSetting;
    }

    /**
     * @return string
     */
    protected function getStatusSettingTemplate()
    {
        $translations = $this->getStatusSettingTranslations();

        if (Tools::isSubmit('btnStatusSubmit')) {
            $this->validateStatusSetting();
            $this->selectedTab = 'status_setting';
        }

        $render = $this->renderStatusSettingForm($translations);

        $this->setHtml($render);

        return $this->getHtml();
    }

    protected function validateStatusSetting()
    {
        if (Tools::isSubmit('btnStatusSubmit')) {
            $isRequired = false;
            $fieldsRequired = array();

            if ($isRequired) {
                $warning = implode(', ', $fieldsRequired) . ' ';
                if ($this->l('ERROR_MANDATORY') == "ERROR_MANDATORY") {
                    $warning .= "is required. please fill out this field";
                } else {
                    $warning .= $this->l('ERROR_MANDATORY');
                }
                $this->context->cookie->payseraMessageSuccess = false;
                $this->context->cookie->payseraConfigMessage = $warning;
            } else {
                $this->updateStatusSetting();
            }
        }
    }

    /**
     * @return array
     */
    protected function getStatusSettingTranslations()
    {
        $translations = array();

        if ($this->l('PAYSERA_ORDER_STATUS_NEW') == "PAYSERA_ORDER_STATUS_NEW") {
            $translations['new_order']['label'] = "New order status";
        } else {
            $translations['new_order']['label'] = $this->l('PAYSERA_ORDER_STATUS_NEW');
        }
        if ($this->l('PAYSERA_ORDER_STATUS_PAID') == "PAYSERA_ORDER_STATUS_PAID") {
            $translations['paid_order']['label'] = "Paid order status";
        } else {
            $translations['paid_order']['label'] = $this->l('PAYSERA_ORDER_STATUS_PAID');
        }
        if ($this->l('PAYSERA_ORDER_STATUS_PENDING') == "PAYSERA_ORDER_STATUS_PENDING") {
            $translations['pending_order']['label'] = "Pending order status";
        } else {
            $translations['pending_order']['label'] = $this->l('PAYSERA_ORDER_STATUS_PENDING');
        }

        if ($this->l('PAYSERA_ORDER_STATUS_NEW_DESC') == "PAYSERA_ORDER_STATUS_NEW_DESC") {
            $translations['new_order']['desc'] = "Order status after finishing checkout";
        } else {
            $translations['new_order']['desc'] = $this->l('PAYSERA_ORDER_STATUS_NEW_DESC');
        }
        if ($this->l('PAYSERA_ORDER_STATUS_PAID_DESC') == "PAYSERA_ORDER_STATUS_PAID_DESC") {
            $translations['paid_order']['desc'] = "Status for order with confirmed payment";
        } else {
            $translations['paid_order']['desc'] = $this->l('PAYSERA_ORDER_STATUS_PAID_DESC');
        }
        if ($this->l('PAYSERA_ORDER_STATUS_PENDING_DESC') == "PAYSERA_ORDER_STATUS_PENDING_DESC") {
            $translations['pending_order']['desc'] = "Order status for pending payment";
        } else {
            $translations['pending_order']['desc'] = $this->l('PAYSERA_ORDER_STATUS_PENDING_DESC');
        }

        if ($this->l('BACKEND_STATUS_SAVE') == "BACKEND_STATUS_SAVE") {
            $translations['save'] = "Save status settings";
        } else {
            $translations['save'] = $this->l('BACKEND_STATUS_SAVE');
        }

        return $translations;
    }

    protected function updateStatusSetting()
    {
        if (Tools::isSubmit('btnStatusSubmit')) {
            Configuration::updateValue(
                'PAYSERA_ORDER_STATUS_NEW',
                Tools::getValue('PAYSERA_ORDER_STATUS_NEW')
            );
            Configuration::updateValue(
                'PAYSERA_ORDER_STATUS_PAID',
                Tools::getValue('PAYSERA_ORDER_STATUS_PAID')
            );
            Configuration::updateValue(
                'PAYSERA_ORDER_STATUS_PENDING',
                Tools::getValue('PAYSERA_ORDER_STATUS_PENDING')
            );

            if ($this->l('PAYSERA_SUCCESS_STATUS_SETTING') == "PAYSERA_SUCCESS_STATUS_SETTING") {
                $successMessage = "Your Paysera settings were successfully updated.";
            } else {
                $successMessage = $this->l('PAYSERA_SUCCESS_STATUS_SETTING');
            }

            $this->context->cookie->payseraMessageSuccess = true;
            $this->context->cookie->payseraConfigMessage = $successMessage;
        }
    }

    /**
     * @param array $translations
     *
     * @return string
     */
    protected function renderStatusSettingForm($translations)
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang =  Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang =  0;
        }
        $fields_form = $this->getStatusSettingForm($translations);
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnStatusSubmit';
        $helper->currentIndex = $this->getAdminModuleLink();
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getStatusSetting(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm($fields_form);
    }

    /**
     * @param array $translations
     *
     * @return array
     */
    protected function getStatusSettingForm($translations)
    {
        $statusForm = array();
        $statusForm[] = array(
            'form' => array(
                'input' => array(
                    $this->getSelectForm(
                        'PAYSERA_ORDER_STATUS_NEW',
                        $translations['new_order'],
                        $this->getOrderStatuses()
                    ),
                    $this->getSelectForm(
                        'PAYSERA_ORDER_STATUS_PAID',
                        $translations['paid_order'],
                        $this->getOrderStatuses()
                    ),
                    $this->getSelectForm(
                        'PAYSERA_ORDER_STATUS_PENDING',
                        $translations['pending_order'],
                        $this->getOrderStatuses()
                    ),
                ),
                'submit' => array(
                    'title' => $translations['save']
                )
            )
        );

        return $statusForm;
    }

    /**
     * @return array
     */
    protected function getStatusSetting()
    {
        $configNewOrder     = Configuration::get('PAYSERA_ORDER_STATUS_NEW');
        $configPaidOrder    = Configuration::get('PAYSERA_ORDER_STATUS_PAID');
        $configPendingOrder = Configuration::get('PAYSERA_ORDER_STATUS_PENDING');

        $statusSetting = array();
        $statusSetting['PAYSERA_ORDER_STATUS_NEW'] =
            Tools::getValue('PAYSERA_ORDER_STATUS_NEW', $configNewOrder);
        $statusSetting['PAYSERA_ORDER_STATUS_PAID'] =
            Tools::getValue('PAYSERA_ORDER_STATUS_PAID', $configPaidOrder);
        $statusSetting['PAYSERA_ORDER_STATUS_PENDING'] =
            Tools::getValue('PAYSERA_ORDER_STATUS_PENDING', $configPendingOrder);

        return $statusSetting;
    }

    /**
     * @return string
     */
    protected function getAdditionsSettingTemplate()
    {
        $translations = $this->getAdditionsSettingTranslations();

        if (Tools::isSubmit('btnAdditionsSubmit')) {
            $this->validateAdditionsSetting();
            $this->selectedTab = 'additions_setting';
        }

        $render = $this->renderAdditionsSettingForm($translations);

        $this->setHtml($render);

        return $this->getHtml();
    }

    protected function validateAdditionsSetting()
    {
        if (Tools::isSubmit('btnAdditionsSubmit')) {
            $isRequired = false;
            $fieldsRequired = array();

            if ($isRequired) {
                $warning = implode(', ', $fieldsRequired) . ' ';
                if ($this->l('ERROR_MANDATORY') == "ERROR_MANDATORY") {
                    $warning .= "is required. please fill out this field";
                } else {
                    $warning .= $this->l('ERROR_MANDATORY');
                }
                $this->context->cookie->payseraMessageSuccess = false;
                $this->context->cookie->payseraConfigMessage = $warning;
            } else {
                $this->updateAdditionsSetting();
            }
        }
    }

    /**
     * @return array
     */
    protected function getAdditionsSettingTranslations()
    {
        $translations = array();

        if ($this->l('PAYSERA_ADDITIONS_QUALITY_SIGN') == "PAYSERA_ADDITIONS_QUALITY_SIGN") {
            $translations['quality']['label'] = "Quality sign";
        } else {
            $translations['quality']['label'] = $this->l('PAYSERA_ADDITIONS_QUALITY_SIGN');
        }
        if ($this->l('PAYSERA_ADDITIONS_OWNERSHIP') == "PAYSERA_ADDITIONS_OWNERSHIP") {
            $translations['ownership']['label'] = "Ownership of website";
        } else {
            $translations['ownership']['label'] = $this->l('PAYSERA_ADDITIONS_OWNERSHIP');
        }
        if ($this->l('PAYSERA_ADDITIONS_OWNERSHIP_CODE') == "PAYSERA_ADDITIONS_OWNERSHIP_CODE") {
            $translations['ownership_code']['label'] = "Ownership code";
        } else {
            $translations['ownership_code']['label'] = $this->l('PAYSERA_ADDITIONS_OWNERSHIP_CODE');
        }

        if ($this->l('PAYSERA_ADDITIONS_QUALITY_SIGN_DESC') == "PAYSERA_ADDITIONS_QUALITY_SIGN_DESC") {
            $translations['quality']['desc'] = "Enable this to use quality sign";
        } else {
            $translations['quality']['desc'] = $this->l('PAYSERA_ADDITIONS_QUALITY_SIGN_DESC');
        }
        if ($this->l('PAYSERA_ADDITIONS_OWNERSHIP_DESC') == "PAYSERA_ADDITIONS_OWNERSHIP_DESC") {
            $translations['ownership']['desc'] = "Enable this to place ownership code";
        } else {
            $translations['ownership']['desc'] = $this->l('PAYSERA_ADDITIONS_OWNERSHIP_DESC');
        }
        if ($this->l('PAYSERA_ADDITIONS_OWNERSHIP_CODE_DESC') == "PAYSERA_ADDITIONS_OWNERSHIP_CODE_DESC") {
            $translations['ownership_code']['desc'] = "Enter ownership code";
        } else {
            $translations['ownership_code']['desc'] = $this->l('PAYSERA_ADDITIONS_OWNERSHIP_CODE_DESC');
        }
        if ($this->l('BACKEND_ADDITIONS_SAVE') == "BACKEND_ADDITIONS_SAVE") {
            $translations['save'] = "Save project additions settings";
        } else {
            $translations['save'] = $this->l('BACKEND_ADDITIONS_SAVE');
        }

        return $translations;
    }

    protected function updateAdditionsSetting()
    {
        if (Tools::isSubmit('btnAdditionsSubmit')) {
            Configuration::updateValue(
                'PAYSERA_ADDITIONS_QUALITY_SIGN',
                Tools::getValue('PAYSERA_ADDITIONS_QUALITY_SIGN')
            );
            Configuration::updateValue(
                'PAYSERA_ADDITIONS_OWNERSHIP',
                Tools::getValue('PAYSERA_ADDITIONS_OWNERSHIP')
            );
            Configuration::updateValue(
                'PAYSERA_ADDITIONS_OWNERSHIP_CODE',
                Tools::getValue('PAYSERA_ADDITIONS_OWNERSHIP_CODE')
            );

            if ($this->l('PAYSERA_SUCCESS_ADDITIONS_SETTING') == "PAYSERA_SUCCESS_ADDITIONS_SETTING") {
                $successMessage = "Your Paysera project additions settings were successfully updated.";
            } else {
                $successMessage = $this->l('PAYSERA_SUCCESS_ADDITIONS_SETTING');
            }

            $this->context->cookie->payseraMessageSuccess = true;
            $this->context->cookie->payseraConfigMessage = $successMessage;
        }
    }

    /**
     * @param array $translations
     *
     * @return string
     */
    protected function renderAdditionsSettingForm($translations)
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang =  Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang =  0;
        }
        $fields_form = $this->getAdditionsSettingForm($translations);
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnAdditionsSubmit';
        $helper->currentIndex = $this->getAdminModuleLink();
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getAdditionsSetting(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm($fields_form);
    }

    /**
     * @param array $translations
     *
     * @return array
     */
    protected function getAdditionsSettingForm($translations)
    {
        $additionsForm = array();
        $additionsForm[] = array(
            'form' => array(
                'input' => array(
                    $this->getBoolForm(
                        'PAYSERA_ADDITIONS_QUALITY_SIGN',
                        $translations['quality']
                    ),
                    $this->getBoolForm(
                        'PAYSERA_ADDITIONS_OWNERSHIP',
                        $translations['ownership']
                    ),
                    $this->getTextForm(
                        'PAYSERA_ADDITIONS_OWNERSHIP_CODE',
                        $translations['ownership_code']
                    )
                ),
                'submit' => array(
                    'title' => $translations['save']
                )
            )
        );

        return $additionsForm;
    }

    /**
     * @return array
     */
    protected function getAdditionsSetting()
    {
        $configQuality       = Configuration::get('PAYSERA_ADDITIONS_QUALITY_SIGN');
        $configOwnership     = Configuration::get('PAYSERA_ADDITIONS_OWNERSHIP');
        $configOwnershipCode = Configuration::get('PAYSERA_ADDITIONS_OWNERSHIP_CODE');

        $additionsSetting = array();
        $additionsSetting['PAYSERA_ADDITIONS_QUALITY_SIGN'] =
            Tools::getValue('PAYSERA_ADDITIONS_QUALITY_SIGN', $configQuality);
        $additionsSetting['PAYSERA_ADDITIONS_OWNERSHIP'] =
            Tools::getValue('PAYSERA_ADDITIONS_OWNERSHIP', $configOwnership);
        $additionsSetting['PAYSERA_ADDITIONS_OWNERSHIP_CODE'] =
            Tools::getValue('PAYSERA_ADDITIONS_OWNERSHIP_CODE', $configOwnershipCode);

        return $additionsSetting;
    }

    /**
     * @return string
     */
    public function getAdminModuleLink()
    {
        $adminLink = $this->context->link->getAdminLink('AdminModules', false);
        $module = '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $adminToken = Tools::getAdminTokenLite('AdminModules');

        return $adminLink.$module.'&token='.$adminToken;
    }

    /**
     * @param string $key
     * @param array  $translations
     * @param bool   $requirement
     *
     * @return array
     */
    private function getTextForm($key, $translations, $requirement = false)
    {
        $textForm = array(
            'type'     => 'text',
            'label'    => $translations['label'],
            'name'     => $key,
            'required' => $requirement,
            'desc'     => $translations['desc']
        );

        return $textForm;
    }

    /**
     * @param string $key
     * @param array  $translations
     * @param bool   $requirement
     *
     * @return array
     */
    private function getTextareaForm($key, $translations, $requirement = false)
    {
        $textareaForm = array(
            'type'     => 'textarea',
            'label'    => $translations['label'],
            'name'     => $key,
            'rows'     => 10,
            'required' => $requirement,
            'desc'     => $translations['desc'],
        );

        return $textareaForm;
    }

    /**
     * @param string $key
     * @param array  $translations
     * @param bool   $requirement
     *
     * @return array
     */
    private function getBoolForm($key, $translations, $requirement = false)
    {
        $textForm = array(
            'type'      => 'switch',
            'label'     => $translations['label'],
            'name'      => $key,
            'required'  => $requirement,
            'desc'      => $translations['desc'],
            'class'     => 'fixed-width-xxl',
            'is_bool'   => true,
            'values'    => array(
                array(
                    'id' => 'active_on',
                    'value' => 1
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0
                )
            )
        );

        return $textForm;
    }

    /**
     * @param string $key
     * @param array $translations
     * @param array $selectList
     * @param bool $requirement
     * @param bool $multiple
     *
     * @return array
     */
    private function getSelectForm($key, $translations, $selectList, $requirement = false, $multiple = false)
    {

        $selectForm = array(
            'type'     => 'select',
            'label'    => $translations['label'],
            'name'     => $key,
            'required' => $requirement,
            'desc'     => $translations['desc'],
            'class'    => 'fixed-width-xxl',
            'multiple' => $multiple,
            'options'  => array(
                'query' => $selectList,
                'id'    => 'id',
                'name'  => 'name'
            )
        );

        if ($selectForm['multiple']) {
            $selectForm['size'] = 21;
        }

        return $selectForm;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    private function getMultipleFieldValues($key)
    {
        $keyGroup   = $key  . '_GROUP';
        $arrayName  = $key . '[]';
        $values     = array();

        $defaultVal = Configuration::get($keyGroup);
        $val        = Tools::getValue($keyGroup, $defaultVal);
        foreach (json_decode($val) as $item) {
            $values[$item] = $item;
        }

        return array(
            'name'   => $arrayName,
            'values' => $values
        );
    }

    /**
     * @return bool|string
     */
    public function hookDisplayHeader()
    {
        $controller = $this->context->controller->php_self;
        if ($controller == 'order') {
            $displayPaymentMethods = (bool) Configuration::get('PAYSERA_EXTRA_LIST_OF_PAYMENTS');

            if ($displayPaymentMethods) {
                $this->context->controller->registerStylesheet(
                    sha1('modules-paysera-order'),
                    'modules/paysera/views/css/payment-methods.css'
                );

                $this->context->controller->registerJavascript(
                    sha1('modules-paysera-order'),
                    'modules/paysera/views/js/payment-methods.js'
                );
            }
        }

        $configProjectID     = Configuration::get('PAYSERA_GENERAL_PROJECT_ID');
        $configQuality       = Configuration::get('PAYSERA_ADDITIONS_QUALITY_SIGN');
        $configOwnership     = Configuration::get('PAYSERA_ADDITIONS_OWNERSHIP');
        $configOwnershipCode = Configuration::get('PAYSERA_ADDITIONS_OWNERSHIP_CODE');

        if ($configQuality) {
            $this->getQualitySign($configProjectID);
        }

        if ($configOwnership) {
            $this->context->smarty->assign('ownershipCode', $configOwnershipCode);

            return $this->display(__FILE__, 'views/templates/hook/header.tpl');
        }

        return null;
    }

    /**
     * @param string $projectID
     */
    private function getQualitySign($projectID)
    {
        $langIso = $this->context->language->iso_code;

        $defaultLang    = $this::DEFAULT_LANG;
        $availableLangs = $this->getAvailableLang();

        $jsParams = array(
            'wtpQualitySign_projectId' => $projectID,
            'wtpQualitySign_language' => in_array($langIso, $availableLangs) ? $langIso : $defaultLang,
        );

        Media::addJsDef($jsParams);

        $this->context->controller->registerJavascript(
            sha1('modules-paysera-widget'),
            $this::QUALITY_SIGN_JS,
            array('server' => 'remote')
        );
    }

    /**
     * @return array|PaymentOption[]
     */
    public function hookPaymentOptions()
    {
        if (!class_exists('PayseraPaymentMethods')) {
            require_once 'classes/PayseraPaymentMethods.php';
        }
        $translations = $this->getPaymentOptionsTranslations();

        $projectID             = Configuration::get('PAYSERA_GENERAL_PROJECT_ID');
        $title                 = $translations['title'];
        $description           = $translations['desc'];
        $selectedCountries     = json_decode(Configuration::get('PAYSERA_EXTRA_SPECIFIC_COUNTRIES_GROUP'));
        $gridView              = Configuration::get('PAYSERA_EXTRA_GRIDVIEW');
        $displayPaymentMethods = (bool) Configuration::get('PAYSERA_EXTRA_LIST_OF_PAYMENTS');

        $cartDetails           = $this->context->cart;
        $addressObj            = new Address($cartDetails->id_address_invoice);
        $countryObj            = new Country($addressObj->id_country);
        $country               = Tools::strtolower($countryObj->iso_code);
        $langISO               = Tools::strtolower($this->context->language->iso_code);
        $cartTotal             = $cartDetails->getOrderTotal() * 100;
        $currency              = $this->context->currency->iso_code;

        $payseraOption = new PaymentOption();
        $payseraOption->setCallToActionText($title);
        $payseraOption->setLogo(Media::getMediaPath(dirname(__FILE__) . '/views/img/paysera.png'));
        $payseraOption->setAction($this->context->link->getModuleLink($this->name, 'redirect'));

        $additionalInfo = PayseraPaymentMethods::create()
            ->setProjectID($projectID)
            ->setLang($langISO)
            ->setBillingCountry($country)
            ->setDisplayList($displayPaymentMethods)
            ->setCountriesSelected($selectedCountries)
            ->setGridView($gridView)
            ->setDescription($description)
            ->setCartTotal($cartTotal)
            ->setCartCurrency($currency)
            ->setAvailableLang($this->getAvailableLang())
        ;

        $payseraOption->setAdditionalInformation($additionalInfo->build(false));
        $payseraOption->setInputs(array(
            'paysera_billing_country' => array(
                'name'  => 'paysera_billing_country',
                'type'  => 'hidden',
                'value' => $country,
            ),
            'paysera_payment_method' => array(
                'name'  => 'paysera_payment_method',
                'type'  => 'hidden',
                'value' => '',
            ),
        ));

        return array($payseraOption);
    }

	/**
	 * @return array
	 */
    public function getPaymentOptionsTranslations()
    {
        $keys = array('title' => 'PAYSERA_EXTRA_TITLE', 'desc' => 'PAYSERA_EXTRA_DESCRIPTION',);
        $translations = array();

        foreach($keys as $name => $key) {
	        $translations[$name] = $this->l(Configuration::get($key));
        }

	    return $translations;
	}

    /**
     * @return bool
     */
    public function checkCurrency()
    {
        $idCurrency = $this->context->cart->id_currency;

        $currency = new Currency($idCurrency);
        $moduleCurrencies = $this->getCurrency($idCurrency);

        if (is_array($moduleCurrencies)) {
            foreach ($moduleCurrencies as $moduleCurrency) {
                if ($currency->id == $moduleCurrency['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * @return array
     */
    public function getAvailableLang()
    {
        return $this->availableLang;
    }

    /**
     * @return array
     */
    private function getCountries()
    {
        $countries = array();
        $projectID = Configuration::get('PAYSERA_GENERAL_PROJECT_ID');

        if (!$projectID) {
            return $countries;
        }

        $countryCode = $this->context->language->iso_code;
        $methods = WebToPay::getPaymentMethodList($projectID)
            ->setDefaultLanguage($countryCode)
            ->getCountries();


        $countries[] = array(
            'id'   => '',
            'name' => $this->l('All countries'),
        );

        foreach ($methods as $method) {
            $countries[] = array(
                'id'   => $method->getCode(),
                'name' => $method->getTitle(),
            );
        }

        return $countries;
    }

    /**
     * @return array
     */
    private function getOrderStatuses()
    {
        $states = array();
        $orderState = new OrderState();
        foreach (Language::getLanguages() as $language) {
            $orderStateObj = $orderState->getOrderStates($language['id_lang']);
            foreach ($orderStateObj as $state) {
                $states[] = array(
                    'id'   => $state['id_order_state'],
                    'name' => $state['name']
                );
            }
        }

        return $states;
    }
}
