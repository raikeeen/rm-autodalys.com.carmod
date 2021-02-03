<?php

class AdminLPExpressController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        if (Configuration::get('LP_API_CONNECTED'))
        {
            if (version_compare(_PS_VERSION_, '1.7', '>='))
            {
                $this->page_header_toolbar_btn['update_terminal'] = array(
                    'href' => $this->context->link->getAdminLink('AdminLPExpressTerminal').'&updateTerminals=1',
                    'desc' => $this->module->l('Update terminals'),
                    'icon' => 'process-icon-upload'
                );
            }
            else
            {
                $this->page_header_toolbar_btn['terminals'] = array(
                    'href' => $this->context->link->getAdminLink('AdminLPExpressTerminal'),
                    'desc' => $this->module->l('Terminals'),
                    'icon' => 'process-icon-upload'
                );
            }
        }
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

        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('submitLPExpress'))
        {
            $this->postProcessSubmitLPExpressValidation();

            if (!count($this->errors))
            {
                // Authorization saving
                Configuration::updateValue('LP_PARTNER_ID', Tools::getValue('LP_PARTNER_ID'));
                Configuration::updateValue('LP_PARTNER_PASSWORD', Tools::getValue('LP_PARTNER_PASSWORD'));
                Configuration::updateValue('LP_PAYMENT_PIN', Tools::getValue('LP_PAYMENT_PIN'));
                Configuration::updateValue('LP_ADMIN_PIN', Tools::getValue('LP_ADMIN_PIN'));
                Configuration::updateValue('LP_CUSTOMER_ID', Tools::getValue('LP_CUSTOMER_ID'));
                Configuration::updateValue('LP_DEVELOPMENT_MODE', (Tools::getValue('LP_DEVELOPMENT_MODE') == 1));

                // Order configuration
                Configuration::updateValue('LP_ORDER_TERMINAL_TYPE', Tools::getValue('LP_ORDER_TERMINAL_TYPE'));
                Configuration::updateValue('LP_ORDER_HOME_TYPE', Tools::getValue('LP_ORDER_HOME_TYPE'));
                Configuration::updateValue('LP_COD_MODULES', serialize((array) Tools::getValue('LP_COD_MODULES', [])));

                // Sender information saving
                Configuration::updateValue('LP_SENDER_NAME', Tools::getValue('LP_SENDER_NAME'));
                Configuration::updateValue('LP_SENDER_EMAIL', Tools::getValue('LP_SENDER_EMAIL'));
                Configuration::updateValue('LP_SENDER_ADDRESS', Tools::getValue('LP_SENDER_ADDRESS'));
                Configuration::updateValue('LP_SENDER_ZIP', Tools::getValue('LP_SENDER_ZIP'));
                Configuration::updateValue('LP_SENDER_CITY', Tools::getValue('LP_SENDER_CITY'));
                Configuration::updateValue('LP_SENDER_COUNTRY', Tools::strtoupper(Tools::getValue('LP_SENDER_COUNTRY')));
                Configuration::updateValue('LP_SENDER_PHONE', Tools::getValue('LP_SENDER_PHONE'));

                Configuration::updateValue('LP_API_CONNECTED', 1);

                Tools::redirectAdmin($this->context->link->getAdminLink($this->controller_name).'&conf=4');
            }
            else
            {
                unset($this->page_header_toolbar_btn['update_terminal']);
            }
        }
    }

    private function postProcessSubmitLPExpressValidation()
    {
        $partner_id = Tools::getValue('LP_PARTNER_ID');
        $partner_password = Tools::getValue('LP_PARTNER_PASSWORD');
        $payment_pin = Tools::getValue('LP_PAYMENT_PIN');
        $admin_pin = Tools::getValue('LP_ADMIN_PIN');
        $customer_id = Tools::getValue('LP_CUSTOMER_ID');
        $development_mode = Tools::getValue('LP_DEVELOPMENT_MODE') == 1;
        if (empty($partner_id) || empty($partner_password) || empty($payment_pin) || empty($admin_pin) || empty($customer_id))
        {
            $this->errors[] = $this->module->l('All authentication fields are required');
        }
        else
        {
            BalticPostAPI::setEnvironment($development_mode);
            BalticPostAPI::setAuthData($partner_id, $partner_password);
            if (!BalticPostAPI::testAuth())
            {
                $this->errors[] = $this->module->l('Failed connect to BalticPostAPI');
            }
        }

        $name = Tools::getValue('LP_SENDER_NAME');
        if (empty($name))
        {
            $this->errors[] = $this->module->l('Sender name is required.');
        }
        elseif (!Validate::isName($name))
        {
            $this->errors[] = $this->module->l('Invalid name format.');
        }
        elseif (Tools::strlen($name) > 80)
        {
            $this->errors[] = $this->module->l('Name is to long. Maximum 80 symbols.');
        }

        $email = Tools::getValue('LP_SENDER_EMAIL');
        if (!empty($email))
        {
            if (!Validate::isEmail($email))
            {
                $this->errors[] = $this->module->l('Invalid email format.');
            }
            elseif (Tools::strlen($email) > 50)
            {
                $this->errors[] = $this->module->l('Email is to long. Maximum 50 symbols.');
            }
        }

        $address = Tools::getValue('LP_SENDER_ADDRESS');
        if (empty($address))
        {
            $this->errors[] = $this->module->l('Address is required.');
        }
        elseif (!Validate::isAddress($address))
        {
            $this->errors[] = $this->module->l('Invalid address format.');
        }
        elseif (Tools::strlen($address) > 64)
        {
            $this->errors[] = $this->module->l('Address is to long. Maximum 50 symbols.');
        }

        $zip = Tools::getValue('LP_SENDER_ZIP');
        if (!empty($zip))
        {
            if (!Validate::isZipCodeFormat($zip))
            {
                $this->errors[] = $this->module->l('Invalid zip code.');
            }
            elseif (Tools::strlen($zip) > 10)
            {
                $this->errors[] = $this->module->l('Zip code is to long. Maximum 10 symbols.');
            }
        }

        $city = Tools::getValue('LP_SENDER_CITY');
        if (empty($city))
        {
            $this->errors[] = $this->module->l('City is required.');
        }
        elseif (!Validate::isCityName($city))
        {
            $this->errors[] = $this->module->l('Invalid city name.');
        }
        elseif (Tools::strlen($city) > 50)
        {
            $this->errors[] = $this->module->l('City name is to long. Maximum 50 symbols.');
        }

        $country = Tools::getValue('LP_SENDER_COUNTRY');
        if (!empty($country))
        {
            if (Tools::strlen($country) != 2)
            {
                $this->errors[] = $this->module->l('Country code must be a two-letter ISO 3166-1 alpha-2 standard country code.');
            }
            elseif (preg_match('/\d/', $country) > 0)
            {
                $this->errors[] = $this->module->l('Country code must be a two-letter ISO 3166-1 alpha-2 standard country code.');
            }
        }

        $phone = Tools::getValue('LP_SENDER_PHONE');
        if (empty($phone))
        {
            $this->errors[] = $this->module->l('Phone number is required.');
        }
        elseif (!Validate::isPhoneNumber($phone) || substr($phone, 0, 3) != '+37')
        {
            $this->errors[] = $this->module->l('Invalid phone number format. Value must match the following format: +37XXXXXXXXX.');
        }
    }

    public function renderList()
    {
        $countries = Country::getCountries($this->context->language->id);
        $formatted_countries = [];
        foreach ($countries as $country)
        {
            $formatted_countries[] = [
                'value' => $country['id_country'],
                'name' => $country['name']
            ];
        }

        $payment_modules = PaymentModule::getInstalledPaymentModules();
        $formatted_payment_modules = [];
        foreach ($payment_modules as $payment_module)
        {
            $formatted_payment_modules[$payment_module['name']] = Module::getModuleName($payment_module['name']);
        }

        $fields = [
            'general_configuration' => [
                'title' => $this->module->l('General configuration'),
                'fields' => [
                    'LP_PARTNER_ID' => [
                        'type' => 'text',
                        'title' => $this->module->l('Partner ID')
                    ],
                    'LP_PARTNER_PASSWORD' => [
                        'type' => 'text',
                        'title' => $this->module->l('Partner password')
                    ],
                    'LP_PAYMENT_PIN' => [
                        'type' => 'text',
                        'title' => $this->module->l('Payment PIN')
                    ],
                    'LP_ADMIN_PIN' => [
                        'type' => 'text',
                        'title' => $this->module->l('Administration PIN')
                    ],
                    'LP_CUSTOMER_ID' => [
                        'type' => 'text',
                        'title' => $this->module->l('Customer ID')
                    ],
                    'LP_DEVELOPMENT_MODE' => [
                        'type' => 'bool',
                        'title' => $this->module->l('Development mode'),
                    ],
                ],
                'submit' => [
                    'name' => 'submitLPExpress',
                    'title' => $this->module->l('Save')
                ]
            ],
            'order_configuration' => [
                'title' => $this->module->l('Order configuration'),
                'fields' => [
                    'LP_ORDER_TERMINAL_TYPE' => [
                        'type' => 'radio_hint',
                        'title' => $this->module->l('Terminal order type'),
                        'choices' => [
                            'HC' => [
                                'title' => $this->module->l('From sender to LP Express terminal'),
                                'hint' => $this->module->l('Parcel is collected by the courier directly from the sender’s address and is delivered to the LP Express self-service parcel terminal indicated by the sender. After the parcel is delivered to the terminal, the receiver is informed to take the parcel from the terminal.')
                            ],
                            'CC' => [
                                'title' => $this->module->l('From sender terminal to address terminal'),
                                'hint' => $this->module->l('Parcel is delivered to LP Express terminal by a sender himself. Afterwards the parcel is delivered by the courier to another LP Express terminal indicated by the sender. After the parcel is delivered to the terminal, a receiver is informed to take the parcel from the terminal.')
                            ]
                        ],
                    ],
                    'LP_ORDER_HOME_TYPE' => [
                        'type' => 'radio_hint',
                        'title' => $this->module->l('Address order type'),
                        'choices' => [
                            'EB' => [
                                'title' => $this->module->l('From sender to receiver address'),
                                'hint' => $this->module->l('Parcel is collected by a courier directly from the sender’s address and is delivered directly to the receiver’s address indicated by a sender.')
                            ],
                            'CH' => [
                                'title' => $this->module->l('From sender terminal to receiver address'),
                                'hint' => $this->module->l('Parcel is delivered to LP Express terminal by a sender himself. Afterwards the parcel is delivered by a courier directly to the address of the receiver indicated by the sender.')
                            ]
                        ],
                    ],
                    'LP_COD_MODULES' => [
                        'type' => 'payment_checkbox',
                        'title' => $this->module->l('COD modules'),
                        'hint' => $this->module->l('Select COD payment modules. For these modules COD method will be selected as default for LPExpress label on order page'),
                        'auto_value' => false,
                        'value' => unserialize((string) Configuration::get('LP_COD_MODULES')),
                        'choices' => $formatted_payment_modules
                    ]
                ],
                'submit' => [
                    'name' => 'submitLPExpress',
                    'title' => $this->module->l('Save')
                ]
            ],
            'sender_information' => [
                'title' => $this->module->l('Sender information'),
                'fields' => [
                    'LP_SENDER_NAME' => [
                        'type' => 'text',
                        'title' => $this->module->l('Name'),
                        'required' => true
                    ],
                    'LP_SENDER_EMAIL' => [
                        'type' => 'text',
                        'title' => $this->module->l('Email')
                    ],
                    'LP_SENDER_ADDRESS' => [
                        'type' => 'text',
                        'title' => $this->module->l('Address'),
                        'required' => true,
                        'hint' => $this->module->l('Value must include only a street, house and/or flat information, e. g. Kauno g. 100-10, Konstitucijos pr. 7A, etc., and not include city, post code and country')
                    ],
                    'LP_SENDER_ZIP' => [
                        'type' => 'text',
                        'title' => $this->module->l('Zip code')
                    ],
                    'LP_SENDER_CITY' => [
                        'type' => 'text',
                        'required' => true,
                        'title' => $this->module->l('City')
                    ],
                    'LP_SENDER_COUNTRY' => [
                        'type' => 'text',
                        'hint' => $this->module->l('Its value must be a two-letter ISO 3166-1 alpha-2 standard country code. The default value is LT.'),
                        'title' => $this->module->l('Country'),
                    ],
                    'LP_SENDER_PHONE' => [
                        'type' => 'text',
                        'required' => true,
                        'title' => $this->module->l('Phone'),
                        'hint' => $this->module->l('Value must match the following format: +37XXXXXXXXX.')
                    ],
                ],
                'submit' => [
                    'name' => 'submitLPExpress',
                    'title' => $this->module->l('Save')
                ]
            ]
        ];

        $helper = new HelperOptions();
        $helper->module = $this->module;
        $helper->id = $this->module->id;
        $helper->token = Tools::getAdminTokenLite('AdminLPExpress');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminLPExpress');
        $helper->title = $this->module->displayName;

        $header = $this->context->smarty->createTemplate($this->module->getLocalPath().'views/templates/admin/lpexpress/lpexpress.tpl', $this->context->smarty);
        $header->assign([
            'image' => 'http://updates.prestarock.com/logo.png',
            'href' => 'https://prestarock.lt/',
            'description' => 'Vieni pirmųjų ir turbūt ilgiausiai su PrestaShop platforma dirbanti elektroninės komercijos agentūra Lietuvoje. Sertifikuota thirty bees agentūra. Mūsų paslaugos apima unikalių el. parduotuvių kūrimą, šablonų diegimą ir pritaikymą pagal poreikius, PrestaShop modulių kūrimą ir palaikymą, PrestaShop elektroninių parduotuvių priežiūrą. Atliekame PrestaShop greitaveikos ir saugumo auditus, specifines tiekėjų ir likučių integracijas su šia populiaria el. komercijos sistema. Internetinių parduotuvių administratorius mokome dirbti.',
            'title' => 'Prestarock'
        ]);
        $html = $header->fetch();

        return $html.$helper->generateOptions($fields);
    }
}