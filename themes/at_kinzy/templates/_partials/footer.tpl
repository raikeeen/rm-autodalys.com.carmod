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
      <!-- Load Facebook SDK for JavaScript -->
      <div id="fb-root"></div>
      <script>
          window.fbAsyncInit = function() {
              FB.init({
                  xfbml            : true,
                  version          : 'v9.0'
              });
          };

          (function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
              fjs.parentNode.insertBefore(js, fjs);
          }(document, 'script', 'facebook-jssdk'));</script>

      <!-- Your Chat Plugin code -->
      <div class="fb-customerchat"
           attribution=setup_tool
           page_id="111236880248472"
           theme_color="#1467c1"
           logged_in_greeting="Sveiki! Turite klausimų?"
           logged_out_greeting="Sveiki! Turite klausimų?">
      </div>
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