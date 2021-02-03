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

class ArContactUsLiveChatConfig extends ArContactUsAbstract
{
    public $tawk_to_head;
    public $tawk_to_on;
    public $tawk_to_site_id;
    public $tawk_to_widget;
    public $tawk_to_userinfo;
    public $hr1;
    
    public $crisp_head;
    public $crisp_on;
    public $crisp_site_id;
    public $hr2;
    
    public $intercom_head;
    public $intercom_on;
    public $intercom_app_id;
    public $hr3;
    
    public $fb_head;
    public $fb_on;
    public $fb_page_id;
    public $fb_init;
    public $fb_lang;
    public $fb_color;
    public $hr4;
    
    public $vk_head;
    public $vk_on;
    public $vk_page_id;
    public $hr5;
    
    public $zopim_head;
    public $zopim_on;
    public $zopim_id;
    public $zopim_userinfo;
    public $hr6;
    
    public $skype_head;
    public $skype_on;
    public $skype_type;
    public $skype_id;
    public $skype_message_color;
    public $hr7;
    
    public $zalo_head;
    public $zalo_on;
    public $zalo_id;
    public $zalo_welcome;
    public $zalo_width;
    public $zalo_height;
    public $hr8;
    
    public $lhc_head;
    public $lhc_on;
    public $lhc_uri;
    public $lhc_width;
    public $lhc_height;
    public $lhc_popup_width;
    public $lhc_popup_height;
    public $hr9;
    
    public $ss_head;
    public $ss_on;
    public $ss_key;
    public $ss_userinfo;
    public $hr10;
    
    public $lc_head;
    public $lc_on;
    public $lc_key;
    public $lc_userinfo;
    public $hr11;
    
    public $tidio_head;
    public $tidio_on;
    public $tidio_key;
    public $tidio_userinfo;
    public $hr12;
    
    public $lcp_head;
    public $lcp_on;
    public $lcp_uri;
    public $hr13;
    
    public $lz_head;
    public $lz_on;
    public $lz_id;
    
    public function getIntegrations()
    {
        $integrations = array();
        if ($this->isTawkToIntegrated()) {
            $integrations['tawkto'] = 'Tawk.to';
        }
        if ($this->isCrispIntegrated()) {
            $integrations['crisp'] = 'Crisp';
        }
        if ($this->isIntercomIntegrated()) {
            $integrations['intercom'] = 'Intercom';
        }
        if ($this->isFacebookChatIntegrated()) {
            $integrations['facebook'] = 'Facebook customer chat';
        }
        if ($this->isVkIntegrated()) {
            $integrations['vk'] = 'VK community messages';
        }
        if ($this->isZopimIntegrated()) {
            $integrations['zopim'] = 'Zendesk chat';
        }
        if ($this->isSkypeIntegrated()) {
            $integrations['skype'] = 'Skype web control';
        }
        if ($this->isZaloIntegrated()) {
            $integrations['zalo'] = 'Zalo chat widget';
        }
        if ($this->isLhcIntegrated()) {
            $integrations['lhc'] = 'Live helper chat';
        }
        if ($this->isSmartsuppIntegrated()) {
            $integrations['smartsupp'] = 'Smartsupp';
        }
        if ($this->isLiveChatIntegrated()) {
            $integrations['livechat'] = 'LiveChat';
        }
        if ($this->isTidioIntegrated()) {
            $integrations['tidio'] = 'Tidio';
        }
        if ($this->isLiveChatProIntegrated()) {
            $integrations['livechatpro'] = 'LiveChatPro';
        }
        if ($this->isLiveZillaIntegrated()) {
            $integrations['livezilla'] = 'LiveZilla';
        }
        return $integrations;
    }
    
    public function getLiveZillaId()
    {
        if (preg_match('{\?id=(.*?)$}is', $this->lz_id, $matches)) {
            return isset($matches[1])? $matches[1] : null;
        }
        return null;
    }
    
    public function isLiveZillaIntegrated()
    {
        return $this->lz_on && $this->lz_id;
    }
    
    public function isLiveChatProIntegrated()
    {
        return $this->lcp_on && $this->lcp_uri;
    }
    
    public function isTidioIntegrated()
    {
        return $this->tidio_on && $this->tidio_key;
    }
    
    public function isLiveChatIntegrated()
    {
        return $this->lc_on && $this->lc_key;
    }
    
    public function isSmartsuppIntegrated()
    {
        return $this->ss_on && $this->ss_key;
    }
    
    public function isLhcIntegrated()
    {
        return $this->lhc_on && $this->lhc_uri;
    }
    
    public function isFacebookChatIntegrated()
    {
        return $this->fb_on && $this->fb_page_id;
    }
    
    public function isTawkToIntegrated()
    {
        $id_lang = Context::getContext()->language->id;
        return $this->tawk_to_on && $this->tawk_to_site_id[$id_lang] && $this->tawk_to_widget[$id_lang];
    }
    
    public function isCrispIntegrated()
    {
        return $this->crisp_on && $this->crisp_site_id;
    }
    
    public function isIntercomIntegrated()
    {
        return $this->intercom_on && $this->intercom_app_id;
    }
    
    public function isVkIntegrated()
    {
        return $this->vk_on && $this->vk_page_id;
    }
    
    public function isZopimIntegrated()
    {
        return $this->zopim_on && $this->zopim_id;
    }
    
    public function isZendeskChat()
    {
        return strpos($this->zopim_id, '-') !== false;
    }
    
    public function isSkypeIntegrated()
    {
        return $this->skype_on && $this->skype_id;
    }
    
    public function isZaloIntegrated()
    {
        return $this->zalo_on && $this->zalo_id;
    }
    
    public function getFormTitle()
    {
        return $this->l('Live chat integrations', 'ArContactUsTawkToConfig');
    }
    
    public function attributeDefaults()
    {
        return array(
            'tawk_to_widget' => 'default',
            'zalo_height' => '420',
            'zalo_width' => '350',
            'lhc_width' => '300',
            'lhc_height' => '190',
            'lhc_popup_height' => '520',
            'lhc_popup_width' => '500'
        );
    }
    
    public function rules()
    {
        return array_merge(parent::rules(), array(
            array(
                array(
                    'zalo_height',
                    'zalo_width',
                ), 'isInt', 'on' => $this->zalo_on
            ),
            array(
                array(
                    'lhc_uri'
                ), 'validateRequired', 'on' => $this->lhc_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'zalo_id'
                ), 'validateRequired', 'on' => $this->zalo_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'ss_key'
                ), 'validateRequired', 'on' => $this->ss_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'lc_key'
                ), 'validateRequired', 'on' => $this->lc_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'lz_id'
                ), 'validateRequired', 'on' => $this->lz_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'lcp_uri',
                ), 'validateRequired', 'on' => $this->lcp_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'lhc_width',
                    'lhc_height',
                    'lhc_popup_height',
                    'lhc_popup_width',
                ), 'isInt', 'on' => $this->lhc_on
            ),
            array(
                array(
                    'tidio_key'
                ), 'validateRequired', 'on' => $this->tidio_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
        ));
    }
}
