{**
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
*}

<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="paysera-payment-choice"
               href="{if $payments|escape:'htmlall':'UTF-8'}#{else}{$redirect|escape:'htmlall':'UTF-8'}{/if}">
                {$title|escape:'htmlall':'UTF-8'}<br><span>{$description|escape:'htmlall':'UTF-8'}</span>
            </a>
        </p>
        {if $payments|escape:'htmlall':'UTF-8'}
            <div class="paysera-payment-choice-extension">
                {$payments nofilter}
                <a href="{$redirect|escape:'htmlall':'UTF-8'}"
                   id="payseraPaySubmit"
                   class="button btn btn-default button-medium"
                   data-paysera-redirect="{$redirect|escape:'htmlall':'UTF-8'}">
                    <span>
                        {l s='I confirm my order' mod='paysera'}
                        <i class="icon-chevron-right right"></i>
                    </span>
                </a>
            </div>
        {/if}
    </div>
</div>
