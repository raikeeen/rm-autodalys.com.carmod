{*
* 2017 Azelab
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
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*}

<div id="arcontactus"></div>
{if $vkIntegrated}
    <script type="text/javascript" src="https://vk.com/js/api/openapi.js?157"></script>
    <!-- VK Widget -->
    {if !$isMobile}
    <style type="text/css">
        #vk_community_messages{
            {if $buttonConfig->position == 'right'}
                right: -10px !important;
            {else}
                left: -10px !important;
            {/if}
        }
    </style>
    {/if}
    <div id="vk_community_messages"></div>
{/if}
{if $skypeIntegrated}
    <script src="https://swc.cdn.skype.com/sdk/v1/sdk.min.js"></script>
    <div 
        class="skype-chat" 
        id="arcontactus-skype"
        style="display: none"
        data-can-close="true" 
        data-can-collapse="true"
        data-can-upload-file="true"
        data-show-header="true"
        data-entry-animation="true"
        {if $liveChatConfig->skype_type == 'skype'}
            data-contact-id="{$liveChatConfig->skype_id|escape:'htmlall':'UTF-8'}" 
        {else}
            data-bot-id="{$liveChatConfig->skype_id|escape:'htmlall':'UTF-8'}"
        {/if}
        data-color-message="{$liveChatConfig->skype_message_color|escape:'htmlall':'UTF-8'}"
    ></div>
{/if}
{if $zaloIntegrated}
    <div id="ar-zalo-chat-widget">
        <div class="zalo-chat-widget" data-oaid="{$liveChatConfig->zalo_id|escape:'htmlall':'UTF-8'}" data-welcome-message="{$liveChatConfig->zalo_welcome[$id_lang]|escape:'htmlall':'UTF-8'}" data-autopopup="0" data-width="{$liveChatConfig->zalo_width|intval}" data-height="{$liveChatConfig->zalo_height|intval}"></div>
    </div>
    <script src="https://sp.zalo.me/plugins/sdk.js"></script>
{/if}
{if $tidioIntegrated}
    {if $liveChatConfig->tidio_userinfo}
        <script>
            document.tidioIdentify = {
                email: '{$customer->email|escape:'htmlall':'UTF-8'}',
                name: "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}",
            };
        </script>
    {/if}
    <script src="//code.tidio.co/{$liveChatConfig->tidio_key|escape:'htmlall':'UTF-8'}.js"></script>
{/if}
<script>
    var lcpWidgetInterval;
    var closePopupTimeout;
    var lzWidgetInterval;

    {if ($promptConfig->enable_prompt && $messagesCount)}
        var arCuMessages = {$messages nofilter};
        var arCuLoop = {if $promptConfig->loop}true{else}false{/if};
        var arCuCloseLastMessage = {if $promptConfig->close_last}true{else}false{/if};
        var arCuPromptClosed = false;
        var _arCuTimeOut = null;
        var arCuDelayFirst = {$promptConfig->first_delay|intval};
        var arCuTypingTime = {$promptConfig->typing_time|intval};
        var arCuMessageTime = {$promptConfig->message_time|intval};
        var arCuClosedCookie = 0;
    {/if}
    var arcItems = [];
    {if $tawkToIntegrated}
        {literal}var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();{/literal}
    {/if}
    window.addEventListener('load', function(){
        {if $promptConfig->show_after_close != '-1'}
            arCuClosedCookie = arCuGetCookie('arcu-closed');
        {/if}
        jQuery('#arcontactus').on('arcontactus.init', function(){
            var $key = $('<input>', {
                type: 'hidden',
                name: 'key',
                value: '{$securityKey|escape:'htmlall':'UTF-8'}'
            });
            jQuery('#arcontactus .callback-countdown-block-phone form').append($key);
            {if $popupConfig->phone_mask_on}
                jQuery.mask.definitions['#'] = "[0-9]";
                jQuery('#arcontactus .arcontactus-message-callback-phone').mask('{$popupConfig->phone_mask[$id_lang]|escape:'htmlall':'UTF-8'}');
            {/if}
        });
        {if ($promptConfig->enable_prompt && $messagesCount)}
            jQuery('#arcontactus').on('arcontactus.init', function(){
                if (arCuClosedCookie){
                    return false;
                }
                arCuShowMessages();
            });
            jQuery('#arcontactus').on('arcontactus.openMenu', function(){
                clearTimeout(_arCuTimeOut);
                if (!arCuPromptClosed){
                    arCuPromptClosed = true;
                    jQuery('#arcontact').contactUs('hidePrompt');
                }
            });

            jQuery('#arcontactus').on('arcontactus.hidePrompt', function(){
                clearTimeout(_arCuTimeOut);
                if (arCuClosedCookie != "1"){
                    arCuClosedCookie = "1";
                    {if $promptConfig->show_after_close != '-1'}
                        arCuPromptClosed = true;
                        {if $promptConfig->show_after_close == '0'}
                            arCuCreateCookie('arcu-closed', 1, 0);
                        {else}
                            arCuCreateCookie('arcu-closed', 1, {$promptConfig->show_after_close|intval / 1440});
                        {/if}
                    {/if}
                }
            });
        {/if}

        {foreach $items as $item}
            {if ($item.js && $item.type == 3)}
                jQuery('#arcontactus').on('arcontactus.successCallbackRequest', function(){
                    {$item.js nofilter}
                });
            {/if}
            var arcItem = {
            };
            {if ($item['id'])}
                arcItem.id = '{$item['id']|escape:'htmlall':'UTF-8'}';
            {/if}
            {if $item.type == 1}
                arcItem.onClick = function(e){
                    e.preventDefault();
                    jQuery('#arcontactus').contactUs('closeMenu');
                    {if $item.integration == 'tawkto'}
                        if (typeof Tawk_API == 'undefined'){
                            console.error('Tawk.to integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        Tawk_API.maximize();
                        Tawk_API.showWidget();
                        tawkToInterval = setInterval(function(){
                            checkTawkIsOpened();
                        }, 100);
                    {elseif $item.integration == 'crisp'}
                        if (typeof $crisp == 'undefined'){
                            console.error('Crisp integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        $crisp.push(["do", "chat:show"]);
                        $crisp.push(["do", "chat:open"]);
                    {elseif $item.integration == 'intercom'}
                        if (typeof Intercom == 'undefined'){
                            console.error('Intercom integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        Intercom('show');
                    {elseif $item.integration == 'facebook'}
                        if (typeof FB == 'undefined' || typeof FB.CustomerChat == 'undefined'){
                            console.error('Facebook customer chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#ar-fb-chat').addClass('active');
                        jQuery('.fb_customer_chat_bubble_animated_no_badge,.fb_customer_chat_bubble_animated_no_badge .fb_dialog_content').addClass('active');
                        FB.CustomerChat.showDialog();
                    {elseif $item.integration == 'vk'}
                        if (typeof vkMessagesWidget == 'undefined'){
                            console.error('VK chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        vkMessagesWidget.expand();
                    {elseif $item.integration == 'zopim'}
                        {if $isZendesk}
                            if (typeof zE == 'undefined'){
                                console.error('Zendesk integration is disabled in module configuration');
                                return false;
                            }
                            zE('webWidget', 'show');
                            zE('webWidget', 'open');
                        {else}
                            if (typeof $zopim == 'undefined'){
                                console.error('Zendesk integration is disabled in module configuration');
                                return false;
                            }
                            $zopim.livechat.window.show();
                        {/if}
                        jQuery('#arcontactus').contactUs('hide');
                    {elseif $item.integration == 'skype'}
                        if (typeof SkypeWebControl == 'undefined'){
                            console.error('Skype integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus-skype').show();
                        SkypeWebControl.SDK.Chat.showChat();
                        SkypeWebControl.SDK.Chat.startChat({
                            ConversationId: '{$liveChatConfig->skype_id|escape:'htmlall':'UTF-8'}',
                            ConversationType: 'agent'
                        });
                        skypeWidgetInterval = setInterval(function(){
                            checkSkypeIsOpened();
                        }, 100);
                        jQuery('#arcontactus').contactUs('hide');
                    {elseif $item.integration == 'zalo'}
                        if (typeof ZaloSocialSDK == 'undefined'){
                            console.error('Zalo integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#ar-zalo-chat-widget').addClass('active');
                        ZaloSocialSDK.openChatWidget();
                        zaloWidgetInterval = setInterval(function(){
                            checkZaloIsOpened();
                        }, 100);
                    {elseif $item.integration == 'lhc'}
                        if (typeof lh_inst == 'undefined'){
                            console.error('Live Helper Chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        lh_inst.lh_openchatWindow();
                    {elseif $item.integration == 'smartsupp'}
                        if (typeof smartsupp == 'undefined'){
                            console.error('Smartsupp chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#chat-application').addClass('active');
                        smartsupp('chat:open');
                        ssInterval = setInterval(function(){
                            checkSSIsOpened();
                        }, 100);
                    {elseif $item.integration == 'livechat'}
                        if (typeof LC_API == 'undefined'){
                            console.error('Live Chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        LC_API.open_chat_window();
                    {elseif $item.integration == 'tidio'}
                        if (typeof tidioChatApi == 'undefined'){
                            console.error('Tidio integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        tidioChatApi.show();
                        tidioChatApi.open();
                    {elseif $item.integration == 'livechatpro'}
                        if (typeof phpLiveChat == 'undefined'){
                            console.error('Live Chat Pro integration is disabled in module configuration');
                            return false;
                        }
                        {if !$isMobile}
                            jQuery('#arcontactus').contactUs('hide');
                        {/if}
                        jQuery('#customer-chat-iframe').addClass('active');
                        setTimeout(function(){
                            lcpWidgetInterval = setInterval(function(){
                                checkLCPIsOpened();
                            }, 100);
                        }, 500);
                        phpLiveChat.show();
                    {elseif $item.integration == 'livezilla'}
                        if (typeof OverlayChatWidgetV2 == 'undefined'){
                            console.error('Live Zilla integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#lz_overlay_wm').addClass('active');
                        OverlayChatWidgetV2.Show();
                        lzWidgetInterval = setInterval(function(){
                            checkLZIsOpened();
                        }, 100);
                    {/if}
                    {if $item.js}
                        {$item.js nofilter}
                    {/if}
                }
            {elseif $item.js}
                arcItem.onClick = function(e){
                    {if $item.type == 2}
                        e.preventDefault();
                    {/if}
                    {$item['js'] nofilter}
                }
            {/if}
            arcItem.class = '{$item.class|escape:'htmlall':'UTF-8'}';
            arcItem.title = "{$item.title nofilter}"; {* Escaping can beak non-latin characters *}
            {if ($item['subtitle'])}
                arcItem.subTitle = "{$item.subtitle nofilter}"; {* Escaping can beak non-latin characters *}
            {/if}
            arcItem.icon = '{$item.icon nofilter}';
            arcItem.href = '{if $item.type == '3'}callback{elseif $item.type == '0'}{$item.href nofilter}{/if}';
            arcItem.target = '{$item.target|escape:'htmlall':'UTF-8'}';
            arcItem.color = '{$item.color|escape:'htmlall':'UTF-8'}';
            arcItems.push(arcItem);
        {/foreach}
        jQuery('#arcontactus').contactUs({
            drag: {if $buttonConfig->drag}true{else}false{/if},
            mode: '{if $buttonConfig->mode}{$buttonConfig->mode|escape:'htmlall':'UTF-8'}{else}regular{/if}',
            align: '{$buttonConfig->position|escape:'htmlall':'UTF-8'}',
            reCaptcha: {if $popupConfig->recaptcha}true{else}false{/if},
            reCaptchaKey: '{$popupConfig->key|escape:'htmlall':'UTF-8'}',
            countdown: {$popupConfig->timeout|intval},
            theme: '{$buttonConfig->button_color|escape:'htmlall':'UTF-8'}',
            {if $buttonIcon}
                buttonIcon: '{$buttonIcon nofilter}',
            {/if}
            {if $menuConfig->menu_header_on}
                showMenuHeader: true,
                menuHeaderText: "{$menuConfig->menu_header[$id_lang]|escape:'htmlall':'UTF-8'}",
            {/if}
            {if $menuConfig->header_close}
                showHeaderCloseBtn: true,
            {else}
                showHeaderCloseBtn: false,
            {/if}
            {if ($menuConfig->header_close_bg)}
                headerCloseBtnBgColor: '{$menuConfig->header_close_bg|escape:'htmlall':'UTF-8'}',
            {/if}
            {if ($buttonConfig->text[$id_lang])}
                buttonText: "{$buttonConfig->text[$id_lang] nofilter}",
            {else}
                buttonText: false,
            {/if}
            itemsIconType: '{$menuConfig->item_style|escape:'htmlall':'UTF-8'}',
            buttonSize: '{$buttonConfig->button_size|escape:'htmlall':'UTF-8'}',
            menuSize: '{$menuConfig->menu_size|escape:'htmlall':'UTF-8'}',
            phonePlaceholder: "{$popupConfig->phone_placeholder[$id_lang]|escape:'htmlall':'UTF-8'}",
            callbackSubmitText: "{$popupConfig->btn_title[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            errorMessage: "{$popupConfig->fail_message[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            callProcessText: "{$popupConfig->proccess_message[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            callSuccessText: "{$popupConfig->success_message[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            iconsAnimationSpeed: {$buttonConfig->icon_speed|intval},
            callbackFormText: "{$popupConfig->message[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            items: arcItems,
            ajaxUrl: '{$ajaxUrl nofilter}', {* URL generated by Link object, no escape necessary. Escaping will break functionality *}
            callbackFormFields: {
                phone: {
                    name: 'phone',
                    enabled: true,
                    required: true,
                    type: 'tel',
                    label: '',
                    placeholder: "{$popupConfig->phone_placeholder[$id_lang]|escape:'htmlall':'UTF-8'}"
                },
                {if $popupConfig->gdpr}
                gdpr: {
                    name: 'gdpr',
                    enabled: true,
                    required: true,
                    type: 'checkbox',
                    label: "{$popupConfig->gdpr_title[$id_lang]|escape:'htmlall':'UTF-8'}",
                }
                {/if}
            },
        });
        {if $tawkToIntegrated}
            Tawk_API.onLoad = function(){
                if(!Tawk_API.isChatOngoing()){
                    Tawk_API.hideWidget();
                }else{
                    jQuery('#arcontactus').contactUs('hide');
                }
            };
            Tawk_API.onChatMinimized = function(){
                Tawk_API.hideWidget();
                jQuery('#arcontactus').contactUs('show');
            };
            Tawk_API.onChatEnded = function(){
                Tawk_API.hideWidget();
                jQuery('#arcontactus').contactUs('show');
            };
            {if $liveChatConfig->tawk_to_userinfo && $customer->id}
                Tawk_API.visitor = {
                    name : "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}",
                    email : '{$customer->email|escape:'htmlall':'UTF-8'}'
                };
            {/if}
            (function(){
                var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
                s1.async=true;
                s1.src='https://embed.tawk.to/{$liveChatConfig->tawk_to_site_id[$id_lang]|escape:'htmlall':'UTF-8'}/{$liveChatConfig->tawk_to_widget[$id_lang]|escape:'htmlall':'UTF-8'}';
                s1.charset='UTF-8';
                s1.setAttribute('crossorigin','*');
                s0.parentNode.insertBefore(s1,s0);
            })();
        {/if}
        {if $facebookIntegrated}
            FB.Event.subscribe('customerchat.dialogShow', function(){
                jQuery('#ar-fb-chat').addClass('active');
                jQuery('#arcontactus').contactUs('hide');
                jQuery('.fb_customer_chat_bubble_animated_no_badge,.fb_customer_chat_bubble_animated_no_badge .fb_dialog_content').addClass('active');
            });
            FB.Event.subscribe('customerchat.dialogHide', function(){
                jQuery('#ar-fb-chat').removeClass('active');
                jQuery('#arcontactus').contactUs('show');
                jQuery('.fb_customer_chat_bubble_animated_no_badge,.fb_customer_chat_bubble_animated_no_badge .fb_dialog_content').removeClass('active');
            });
        {/if}
        {if $lhcIntegrated}
            lh_inst.chatClosedCallback = function(){
                jQuery('#arcontactus').contactUs('show');
                clearInterval(LHCInterval);
            };
            lh_inst.chatOpenedCallback = function(){
                jQuery('#arcontactus').contactUs('hide');
                LHCInterval = setInterval(function(){
                    checkLHCisOpened();
                }, 100);
            };
        {/if}
        {if $tidioIntegrated}
            function onTidioChatApiReady(){
                window.tidioChatApi.hide();
            }
            function onTidioChatClose(){
                window.tidioChatApi.hide();
                jQuery('#arcontactus').contactUs('show');
            }
            if (window.tidioChatApi) {
                window.tidioChatApi.on("ready", onTidioChatApiReady);
                window.tidioChatApi.on("close", onTidioChatClose);
            }else{
                document.addEventListener("tidioChat-ready", onTidioChatApiReady);
                document.addEventListener("tidioChat-close", onTidioChatClose);
            }
        {/if}
    });
    {if $intercomIntegrated}
        window.intercomSettings = {
            app_id: "{$liveChatConfig->intercom_app_id|escape:'htmlall':'UTF-8'}",
            alignment: 'right',
            horizontal_padding: 20,
            vertical_padding: 20
        };
        (function() {
            var w = window;
            var ic = w.Intercom;
            if (typeof ic === "function") {
                ic('reattach_activator');
                ic('update', intercomSettings);
            } else {
                var d = document;
                var i = function() {
                    i.c(arguments)
                };
                i.q = [];
                i.c = function(args) {
                    i.q.push(args)
                };
                w.Intercom = i;

                function l() {
                    var s = d.createElement('script');
                    s.type = 'text/javascript';
                    s.async = true;
                    s.src = 'https://widget.intercom.io/widget/{$liveChatConfig->intercom_app_id|escape:'htmlall':'UTF-8'}';
                    var x = d.getElementsByTagName('script')[0];
                    x.parentNode.insertBefore(s, x);
                }
                if (w.attachEvent) {
                    w.attachEvent('onload', l);
                } else {
                    w.addEventListener('load', l, false);
                }
            }
        })();
        Intercom('onHide', function(){
            jQuery('#arcontactus').contactUs('show');
        });
    {/if}
    {if $vkIntegrated}
        var vkMessagesWidget = VK.Widgets.CommunityMessages("vk_community_messages", {$liveChatConfig->vk_page_id|escape:'htmlall':'UTF-8'}, {
            disableButtonTooltip: 1,
            welcomeScreen: 0,
            expanded: 0,
            buttonType: 'no_button',
            widgetPosition: '{$buttonConfig->position|escape:'htmlall':'UTF-8'}'
        });
    {/if}
    {if $ssIntegrated}
        {literal}var _smartsupp = _smartsupp || {};{/literal}
        _smartsupp.key = '{$liveChatConfig->ss_key|escape:'htmlall':'UTF-8'}';
        window.smartsupp||(function(d) {
          var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
          s=d.getElementsByTagName('script')[0];c=d.createElement('script');
          c.type='text/javascript';c.charset='utf-8';c.async=true;
          c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
        })(document);
        {if $liveChatConfig->ss_userinfo and $customer->id}
            smartsupp('name', "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}");
            smartsupp('email', '{$customer->email|escape:'htmlall':'UTF-8'}');
            smartsupp('variables', {
                accountId: {
                    label: 'Customer ID',
                    value: {$customer->id|intval}
                }
            });
        {/if}
        var ssInterval;

        function checkSSIsOpened(){
            if (jQuery('#chat-application').height() < 300){
                smartsupp('chat:close');
                jQuery('#arcontactus').contactUs('show');
                clearInterval(ssInterval);
                jQuery('#chat-application').removeClass('active');
            }
        }
        smartsupp('on', 'message', function(model, message) {
            if (message.type == 'agent') {
                jQuery('#chat-application').addClass('active');
                smartsupp('chat:open');
                jQuery('#arcontactus').contactUs('hide');
                setTimeout(function(){
                    ssInterval = setInterval(function(){
                        checkSSIsOpened();
                    }, 100);
                }, 500);

            }
        });
    {/if}
    {if $tawkToIntegrated}
        var tawkToInterval;

        function checkTawkIsOpened(){
            if (Tawk_API.isChatMinimized()){
                Tawk_API.hideWidget();
                jQuery('#arcontactus').contactUs('show');
                clearInterval(tawkToInterval);
            }
        }
    {/if}
    {if $zaloIntegrated}
        var zaloWidgetInterval;
        function checkZaloIsOpened(){
            if (jQuery('#ar-zalo-chat-widget>div').height() < 100){
                jQuery('#ar-zalo-chat-widget').removeClass('active');
                jQuery('#arcontactus').contactUs('show');
                clearInterval(zaloWidgetInterval);
            }
        }
    {/if}
    {if $lhcIntegrated}
        {literal}var LHCChatOptions = {};{/literal}
        var LHCInterval = null;

        LHCChatOptions.opt = {
            widget_height: {$liveChatConfig->lhc_height|intval},
            widget_width: {$liveChatConfig->lhc_width|intval},
            popup_height: {$liveChatConfig->lhc_popup_width|intval},
            popup_width: {$liveChatConfig->lhc_popup_width|intval}
        };
        (function() {
        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
        var refferer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';
        var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';
        po.src = '{$liveChatConfig->lhc_uri nofilter}/chat/getstatus/(click)/internal/(ma)/br/(position)/bottom_right/(check_operator_messages)/true/(top)/350/(units)/pixels?r='+refferer+'&l='+location;
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
        })();

        function checkLHCisOpened(){
            if (lh_inst.isMinimized){
                jQuery('#arcontactus').contactUs('show');
                lh_inst.isMinimized = false;
                clearInterval(LHCInterval);
            }
        }
    {/if}
    {if $lcIntegrated}
        {literal}window.__lc = window.__lc || {};{/literal}
        window.__lc.license = {$liveChatConfig->lc_key|escape:'htmlall':'UTF-8'};
        (function() {
          var lc = document.createElement('script'); lc.type = 'text/javascript'; lc.async = true;
          lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(lc, s);
        })();
        {literal}var LC_API = LC_API || {};{/literal}
        var livechat_chat_started = false;
        LC_API.on_before_load = function() {
            LC_API.hide_chat_window();
        };
        LC_API.on_after_load = function() {
            LC_API.hide_chat_window();
            {if $liveChatConfig->lc_userinfo && $customer->id}
                LC_API.set_visitor_name('{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}');
                LC_API.set_visitor_email('{$customer->email|escape:'htmlall':'UTF-8'}');
            {/if}
        };
        LC_API.on_chat_window_minimized = function(){
            LC_API.hide_chat_window();
            jQuery('#arcontactus').contactUs('show');
        };
        LC_API.on_message = function(data) {
            LC_API.open_chat_window();
            jQuery('#arcontactus').contactUs('hide');
        };
        LC_API.on_chat_started = function() {
            livechat_chat_started = true;
        };
    {/if}
    {if $skypeIntegrated}
        var skypeWidgetInterval;
        function checkSkypeIsOpened(){
            if (jQuery('#arcontactus-skype .lwc-chat-frame').hasClass('close-chat')){
                jQuery('#arcontactus').contactUs('show');
                clearInterval(skypeWidgetInterval);
            }
        }
    {/if}
    {if $lcp}
        function checkLCPIsOpened(){
            console.log('checkLCPIsOpened');
            if (parseInt(jQuery('#customer-chat-iframe').css('bottom')) < -300){
                jQuery('#arcontactus').contactUs('show');
                jQuery('#customer-chat-iframe').removeClass('active');
                clearInterval(lcpWidgetInterval);
            }
        }
    {/if}
    {if $liveZilla}
        function checkLZIsOpened(){
            if (!jQuery('#lz_overlay_chat').is(':visible')){
                jQuery('#arcontactus').contactUs('show');
                jQuery('#lz_overlay_wm').removeClass('active');
                clearInterval(lzWidgetInterval);
            }
        }
    {/if}
    {if $lcp}
    (function(d,t,u,s,e){
        e=d.getElementsByTagName(t)[0];s=d.createElement(t);s.src=u;s.async=1;e.parentNode.insertBefore(s,e);
    })(document,'script','{$liveChatConfig->lcp_uri|escape:'htmlall':'UTF-8'}');
    {/if}
</script>
{if $liveZilla}
    <script type="text/javascript" id="{$liveChatConfig->getLiveZillaId()|escape:'htmlall':'UTF-8'}" src="{$liveChatConfig->lz_id|escape:'htmlall':'UTF-8'}"></script>
{/if}
{if $zopimIntegrated}
    {if $isZendesk}
        <script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key={$liveChatConfig->zopim_id|escape:'htmlall':'UTF-8'}">
        </script>
        <script type="text/javascript">

            zE('webWidget:on', 'chat:connected', function(){
                zE('webWidget', 'hide');
            });
            zE('webWidget:on', 'open', function(){
                jQuery('#arcontactus').contactUs('hide');
            });
            zE('webWidget:on', 'close', function(){
                zE('webWidget', 'hide');
                jQuery('#arcontactus').contactUs('show');
            });
            zE('webWidget:on', 'chat.unreadMsgs', function(msgs){
                jQuery('#arcontactus').contactUs('hide');
                zE('webWidget', 'show');
                zE('webWidget', 'open');
            });
            {if $liveChatConfig->zopim_userinfo && $customer->id}
                zE('webWidget', 'identify', {
                    name: "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}",
                    email: '{$customer->email|escape:'htmlall':'UTF-8'}'
                });
            {/if}
        </script>
    {else}
        <script type="text/javascript">{literal}
            window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
            d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
            _.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
            $.src="https://v2.zopim.com/?{/literal}{$liveChatConfig->zopim_id|escape:'htmlall':'UTF-8'}{literal}";z.t=+new Date;$.
            type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");{/literal}
            $zopim(function(){
                $zopim.livechat.hideAll();
                {if $buttonConfig->position == 'left'}
                    $zopim.livechat.window.setPosition('bl');
                {else}
                    $zopim.livechat.window.setPosition('br');
                {/if}
                $zopim.livechat.window.onHide(function(){
                    $zopim.livechat.hideAll();
                    jQuery('#arcontactus').contactUs('show');
                });
            });
        </script>
    {/if}
{/if}

{if $crispIntegrated}
    <script type="text/javascript">
        window.$crisp=[];window.CRISP_WEBSITE_ID="{$liveChatConfig->crisp_site_id|escape:'htmlall':'UTF-8'}";(function(){
            d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);
        })();
        $crisp.push(["on", "session:loaded", function(){
            $crisp.push(["do", "chat:hide"]);
        }]);
        $crisp.push(["on", "chat:closed", function(){
            $crisp.push(["do", "chat:hide"]);
            jQuery('#arcontactus').contactUs('show');
        }]);
        $crisp.push(["on", "message:received", function(){
            $crisp.push(["do", "chat:show"]);
            jQuery('#arcontactus').contactUs('hide');
        }]);
    </script>
{/if}

{if $facebookIntegrated}
    {strip}<style type="text/css">
        {if $buttonConfig->position == 'left'}
            .fb-customerchat > span > iframe{
                left: 10px !important;
                right: auto !important;
            }
        {else}
            .fb-customerchat > span > iframe{
                right: 10px !important;
                left: auto !important;
            }
        {/if}
        #ar-fb-chat{
            display: none;
        }
        #ar-fb-chat.active{
            display: block;
        }
    </style>{/strip}
    <div id="ar-fb-chat">
        {if $liveChatConfig->fb_init}
            <script>
                (function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/{if $liveChatConfig->fb_lang[$id_lang]}{$liveChatConfig->fb_lang[$id_lang]|escape:'htmlall':'UTF-8'}{else}en_US{/if}/sdk/xfbml.customerchat.js#xfbml=1&version=v2.12&autoLogAppEvents=1";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk-chat'));
            </script>
        {/if}
        <div class="fb-customerchat" page_id="{$liveChatConfig->fb_page_id|escape:'htmlall':'UTF-8'}" greeting_dialog_display="hide" {if $liveChatConfig->fb_color}theme_color="{$liveChatConfig->fb_color|escape:'htmlall':'UTF-8'}"{/if}></div>
    </div>
{/if}