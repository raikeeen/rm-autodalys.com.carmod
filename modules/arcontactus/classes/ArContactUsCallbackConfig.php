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

include_once dirname(__FILE__).'/ArContactUsAbstract.php';

class ArContactUsCallbackConfig extends ArContactUsAbstract
{
    public $popup_width;
    
    public $timeout;
    public $message;
    public $phone_placeholder;
    public $phone_mask_on;
    public $phone_mask;
    public $proccess_message;
    public $success_message;
    public $fail_message;
    public $btn_title;
    public $gdpr;
    public $gdpr_title;
    public $email;
    public $email_list;
    
    public $twilio;
    public $twilio_api_key;
    public $twilio_auth_token;
    public $twilio_phone;
    public $twilio_tophone;
    public $twilio_message;
    
    public $onesignal;
    public $recaptcha;
    public $key;
    public $secret;
    
    public function getFormTitle()
    {
        return $this->l('Callback popup settings', 'ArContactUsCallbackConfig');
    }
    
    public function attributeDefaults()
    {
        return array(
            'popup_width' => 360,
            'timeout' => '0',
            'message' => $this->l("Please enter your phone number\n and we call you back soon", 'ArContactUsCallbackConfig'),
            'phone_placeholder' => $this->l("+XXX-XX-XXX-XX-XX", 'ArContactUsCallbackConfig'),
            'phone_mask' => '+XXX-XX-XXX-XX-XX',
            'proccess_message' => $this->l("We are calling you to phone", 'ArContactUsCallbackConfig'),
            'success_message' => $this->l("Thank you.\nWe are call you back soon.", 'ArContactUsCallbackConfig'),
            'fail_message' => $this->l("Connection error. Please refresh the page and try again.", 'ArContactUsCallbackConfig'),
            'btn_title' => $this->l("Waiting for call", 'ArContactUsCallbackConfig'),
            'gdpr' => 0,
            'gdpr_title' => $this->l('I accept GDRP rules', 'ArContactUsCallbackConfig'),
            'email' => 1,
            'email_list' => $this->getAdminEmail(),
            'onesignal' => $this->module->isOnesignalInstalled(),
            'recaptcha' => 0
        );
    }
    
    public function getAdminEmail()
    {
        $employees = Employee::getEmployees();
        $emails = array();
        foreach ($employees as $item) {
            $model = new Employee($item['id_employee']);
            $emails[] = $model->email;
        }
        return implode("\r\n", $emails);
    }
}
