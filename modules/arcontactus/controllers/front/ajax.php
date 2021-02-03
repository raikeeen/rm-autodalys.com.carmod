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

include_once dirname(__FILE__).'../../../arcontactus.php';
include_once dirname(__FILE__).'../../../classes/ArContactUsTwilio.php';


/**
 * @property ArContactUs $module
 */
class ArContactUsAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $errors = array();
    protected $json;


    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        $phone = Tools::getValue('phone');
        $phone = trim($phone);
        if ($this->isValid() && $phone) {
            $model = ArContactUsCallback::addCallback(Context::getContext()->customer->id, $phone);
            $pushRes = null;
            $emailSend = null;
            if ($this->module->isOnesignalInstalled() && $this->module->getCallbackConfigModel()->onesignal) {
                $pushRes = $this->module->sendPush($phone);
            }
            if ($this->module->getCallbackConfigModel()->email) {
                $emailSend = $this->module->sendEmail($phone);
            }
            $twilio = $this->sendTwilio($phone);
            die(Tools::jsonEncode(array(
                'success' => 1,
                'model' => AR_CONTACTUS_DEBUG? $model : null,
                'push' => AR_CONTACTUS_DEBUG? $pushRes : null,
                'email' => AR_CONTACTUS_DEBUG? $emailSend : null,
                'twilio' => AR_CONTACTUS_DEBUG? $twilio : null,
                'reCaptcha' => AR_CONTACTUS_DEBUG? $this->json : null
            )));
        } elseif (Tools::isEmpty($phone)) {
            $this->errors[] = $this->module->l('Please fill phone field');
        }
        
        die(Tools::jsonEncode(array(
            'success' => 0,
            'errors' => $this->errors,
            'reCaptcha' => AR_CONTACTUS_DEBUG? $this->json : null
        )));
    }
    
    protected function sendTwilio($phone)
    {
        if (!$this->module->getCallbackConfigModel()->twilio ||
                !$this->module->getCallbackConfigModel()->twilio_api_key ||
                !$this->module->getCallbackConfigModel()->twilio_auth_token ||
                !$this->module->getCallbackConfigModel()->twilio_message ||
                !$this->module->getCallbackConfigModel()->twilio_phone ||
                !$this->module->getCallbackConfigModel()->twilio_tophone
            ) {
            return false;
        }
        $twilio = new ArContactUsTwilio($this->module->getCallbackConfigModel()->twilio_api_key, $this->module->getCallbackConfigModel()->twilio_auth_token);
        $fromPhone = $this->module->getCallbackConfigModel()->twilio_phone;
        $toPhone = $this->module->getCallbackConfigModel()->twilio_tophone;
        $message = strtr($this->module->getCallbackConfigModel()->twilio_message, array('{phone}' => $phone));
        
        $res = $twilio->sendSMS($message, $fromPhone, $toPhone);
        return $res;
    }


    protected function isValid()
    {
        $action = Tools::getValue('action');
        $key = Tools::getValue('key');
        return $action == 'callback' && $this->isValidKey($key) && $this->isValidRecaptcha();
    }

    protected function isValidKey($key)
    {
        if ($key == Configuration::get('arcukey')) {
            return true;
        }
        $this->errors[] = $this->module->l('Invalid security token. Please refresh the page.');
        return false;
    }


    protected function isValidRecaptcha()
    {
        if ($this->module->isReCaptchaIntegrated()) {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                    'content' => http_build_query(array(
                        'secret' => $this->module->getCallbackConfigModel()->secret,
                        'response' => Tools::getValue('gtoken')
                    ))
                ),
            ));
            $data = Tools::file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            $json = Tools::jsonDecode($data, true);
            $this->json = $json;
            if (isset($json['success']) && $json['success']) {
                if (isset($json['score']) && ($json['score'] < 0.3)) {
                    $this->errors[] = $this->module->l('Bot activity detected!');
                    return false;
                }
            } else {
                $this->addReCaptchaErrors($json['error-codes']);
                return false;
            }
        }
        return true;
    }
    
    protected function addReCaptchaErrors($errors)
    {
        $reCaptchaErrors = $this->module->getReCaptchaErrors();
        if ($errors) {
            foreach ($errors as $error) {
                if (isset($reCaptchaErrors[$error])) {
                    $this->errors[] = $reCaptchaErrors[$error];
                } else {
                    $this->errors[] = $error;
                }
            }
        }
    }

    public function getTemplateVarProduct()
    {
    }
}
