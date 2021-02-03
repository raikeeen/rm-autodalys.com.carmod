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
<div class="arcontactus-config-panel {if $active_tab != 'ArContactUsMenuConfig' && $active_tab != 'ArContactUsMenuMobileConfig'}hidden{/if}" id="arcontactus-menu">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-info"></i> {l s='Menu settings' mod='arcontactus'}
        </div>
        <div class="form-wrapper">
            <ul class="nav nav-tabs">
                <li class="{if $active_tab != 'ArContactUsMenuMobileConfig'}active{/if}">
                    <a href="#arcu-menu-desktop" id="arcu-menu-desktop-tab" data-toggle="tab">{l s='Desktop' mod='arcontactus'}</a>
                </li>
                <li class="{if $active_tab == 'ArContactUsMenuMobileConfig'}active{/if}">
                    <a href="#arcu-menu-mobile" id="arcu-menu-desktop-tab" data-toggle="tab">{l s='Mobile' mod='arcontactus'}</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane {if $active_tab != 'ArContactUsMenuMobileConfig'}active{/if}" id="arcu-menu-desktop">
                    {$form->generateForm($menuFormParams) nofilter}{* HTML content generated by HelperForm, no escape necessary *}
                </div>
                <div class="tab-pane {if $active_tab == 'ArContactUsMenuMobileConfig'}active{/if}" id="arcu-menu-mobile">
                    {$form->generateForm($menuMobileFormParams) nofilter}{* HTML content generated by HelperForm, no escape necessary *}
                </div>
            </div>
        </div>
    </div>
</div>