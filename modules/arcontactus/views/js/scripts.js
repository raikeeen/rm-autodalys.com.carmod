/*
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
*/

function arCuGetCookie(cookieName){
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(cookieName + "=");
        if (c_start != -1) {
            c_start = c_start + cookieName.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return 0;
};
function arCuCreateCookie(name, value, days){
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
};
function arCuShowMessage(index){
    if (arCuPromptClosed){
        return false;
    }
    if (typeof arCuMessages[index] !== 'undefined'){
        jQuery('#arcontactus').contactUs('showPromptTyping');

        _arCuTimeOut = setTimeout(function(){
            if (arCuPromptClosed){
                return false;
            }
            jQuery('#arcontactus').contactUs('showPrompt', {
                content: arCuMessages[index]
            });
            index ++;
            _arCuTimeOut = setTimeout(function(){
                if (arCuPromptClosed){
                    return false;
                }
                arCuShowMessage(index);
            }, arCuMessageTime);
        }, arCuTypingTime);
    }else{
        if (arCuCloseLastMessage){
            jQuery('#arcontactus').contactUs('hidePrompt');
        }
        if (arCuLoop){
            arCuShowMessage(0);
        }
    }
};
function arCuShowMessages(){
    setTimeout(function(){
        clearTimeout(_arCuTimeOut);
        arCuShowMessage(0);
    }, arCuDelayFirst);
}