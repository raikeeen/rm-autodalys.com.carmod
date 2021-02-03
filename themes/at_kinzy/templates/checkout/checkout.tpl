{**
 *  PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright  PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<!doctype html>
<html lang="{$language.iso_code}" {if isset($IS_RTL) && $IS_RTL} dir="rtl"{if isset($LEO_RTL) && $LEO_RTL} class="rtl{if isset($LEO_DEFAULT_SKIN)} {$LEO_DEFAULT_SKIN}{/if}"{/if}
{else} class="{if isset($LEO_DEFAULT_SKIN)}{$LEO_DEFAULT_SKIN}{/if}" {/if}>

  <head>
    {block name='head'}
      {include file='_partials/head.tpl'}
    {/block}
  </head>

  <body id="{$page.page_name}" class="{$page.body_classes|classnames}{if isset($LEO_LAYOUT_MODE)} {$LEO_LAYOUT_MODE}{/if}{if isset($USE_FHEADER) && $USE_FHEADER} keep-header{/if}">
  <div id="page">
    {block name='hook_after_body_opening_tag'}
      {hook h='displayAfterBodyOpeningTag'}
    {/block}

    <header id="header">
      {block name='header'}
        {include file='checkout/_partials/header.tpl'}
      {/block}
    </header>

    {block name='notifications'}
      {include file='_partials/notifications.tpl'}
    {/block}

    <section id="wrapper">
      {hook h="displayWrapperTop"}
      <div class="container">

      {block name='content'}
        <section id="content">
          <div class="row">
            <div class="cart-grid-body col-xl-8 col-lg-12 col-xs-12">
              <div id="box-checkout-step">
                {block name='cart_summary'}
                  {render file='checkout/checkout-process.tpl' ui=$checkout_process}
                {/block}
              </div>
            </div>
            <div class="cart-grid-body col-xl-4 col-lg-12 col-xs-12">

              {block name='cart_summary'}
                {include file='checkout/_partials/cart-summary.tpl' cart = $cart}
              {/block}

              {hook h='displayReassurance'}
            </div>
          </div>
        </section>
      {/block}
      </div>
      {hook h="displayWrapperBottom"}
    </section>

    <footer id="footer" class="footer-container">
        {block name='hook_footer_before'}
          <div class="footer-top">
            {if isset($fullwidth_hook.displayFooterBefore) AND $fullwidth_hook.displayFooterBefore == 0}
              <div class="container">
            {/if}
              <div class="inner">{hook h='displayFooterBefore'}</div>
            {if isset($fullwidth_hook.displayFooterBefore) AND $fullwidth_hook.displayFooterBefore == 0}
              </div>
            {/if}
          </div>
        {/block}
        {block name='hook_footer'}
          <div class="footer-center">
            {if isset($fullwidth_hook.displayFooter) AND $fullwidth_hook.displayFooter == 0}
              <div class="container">
            {/if}
              <div class="inner">{hook h='displayFooter'}</div>
            {if isset($fullwidth_hook.displayFooter) AND $fullwidth_hook.displayFooter == 0}
              </div>
            {/if}
          </div>
        {/block}
        {block name='hook_footer_after'}
          <div class="footer-bottom">
            {if isset($fullwidth_hook.displayFooterAfter) AND $fullwidth_hook.displayFooterAfter == 0}
              <div class="container">
            {/if}
              <div class="inner">{hook h='displayFooterAfter'}</div>
            {if isset($fullwidth_hook.displayFooterAfter) AND $fullwidth_hook.displayFooterAfter == 0}
              </div>
            {/if}
          </div>
        {/block}
      {if isset($LEO_BACKTOP) && $LEO_BACKTOP}
        <div id="back-top"><a href="#" class="fa fa-angle-up"></a></div>
      {/if}
    </footer>

    {block name='javascript_bottom'}
      {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
    {/block}

    {block name='hook_before_body_closing_tag'}
      {hook h='displayBeforeBodyClosingTag'}
    {/block}
  </div>
  </body>

</html>
