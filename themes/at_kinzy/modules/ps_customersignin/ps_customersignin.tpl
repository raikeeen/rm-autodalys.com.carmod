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

<div class="userinfo-selector popup-over pull-right e-scale">
  <ul class="nav_title_info">
    {if $logged}
      <li>
        <a class="account" href="{$my_account_url}" title="{l s='View my customer account' d='Shop.Theme.Customeraccount'}" rel="nofollow">
          <span>{l s='Sveiki' d='Shop.Theme.Global'} {$customerName}</span>
        </a>
      </li>
      <li>
        <a class="logout dropdown-item" href="{$logout_url}" rel="nofollow">
            Atsijungti
        </a>
      </li>
    {else}
      <li>
          <a
            class="signin leo-quicklogin"
            data-enable-sociallogin="enable"
            data-type="popup"
            data-layout="login"
            href="javascript:void(0)"
            title="{l s='Log in to your customer account' d='Shop.Theme.Customeraccount'}"
            rel="nofollow"
          >
            <span>{l s='Sign in' d='Shop.Theme.Actions'}</span>
          </a>
      </li>
      <li>
        <a
          class="register leo-quicklogin"
          data-enable-sociallogin="enable"
          data-type="popup"
          data-layout="register"
          href="javascript:void(0)"
          title="{l s='Register' d='Shop.Theme.Actions'}"
          rel="nofollow"
        >
          <span>{l s='Register' d='Shop.Theme.Actions'}</span>
        </a>
      </li>
    {/if}
  </ul>
  <div class="hidden-xs-up">
    <a href="javascript:void(0)" data-toggle="dropdown" class="popup-title" title="{l s='Account' d='Shop.Theme.Global'}">
      <i class="nova-user"></i>
      <span class="user_title">{l s='My Account' d='Shop.Theme.Global'}</span>
      <i class="fa fa-angle-down"></i>
    </a>
    <ul class="popup-content dropdown-menu user-info">
      {if $logged}
        <li>
          <a
            class="account dropdown-item" 
            href="{$my_account_url}"
            title="{l s='View my customer account' d='Shop.Theme.Customeraccount'}"
            rel="nofollow"
          >
          <i class="fa fa-user"></i>
            <span>{l s='Hello' d='Shop.Theme.Global'} {$customerName}</span>
          </a>
        </li>
        <li>
          <a
            class="logout dropdown-item"
            href="{$logout_url}"
            rel="nofollow"
          >
          <i class="fa fa-unlock-alt"></i>
            {l s='Sign out' d='Shop.Theme.Actions'}
          </a>
        </li>
      {else}
        <li>
          <a
            class="signin leo-quicklogin"
            data-enable-sociallogin="enable"
            data-type="popup"
            data-layout="login"
            href="javascript:void(0)"
            title="{l s='Log in to your customer account' d='Shop.Theme.Customeraccount'}"
            rel="nofollow"
          >
          <i class="fa fa-unlock-alt"></i>
            <span>{l s='Sign in' d='Shop.Theme.Actions'}</span>
          </a>
        </li>
      {/if}
      <li>
        <a
          class="myacount dropdown-item"
          href="{$my_account_url}"
          title="{l s='My account' d='Shop.Theme.Global'}"
          rel="nofollow"
        >
        <i class="fa fa-user"></i>
          <span>{l s='My account' d='Shop.Theme.Global'}</span>
        </a>
      </li>
      <li>
        <a
          class="checkout dropdown-item"
          href="{url entity='cart' params=['action' => show]}"
          title="{l s='Checkout' d='Shop.Theme.Global'}"
          rel="nofollow"
        >
        <i class="fa fa-sign-out" aria-hidden="true"></i>
          <span>{l s='Checkout' d='Shop.Theme.Actions'}</span>
        </a>
      </li>
      <li>
        <a
          class="ap-btn-wishlist dropdown-item"
          href="{url entity='module' name='leofeature' controller='mywishlist'}"
          title="{l s='Patinka' d='Shop.Theme.Global'}"
          rel="nofollow"
        >
          <i class="fa fa-heart-o"></i>
          <span>{l s='Patinka' d='Shop.Theme.Global'}</span>
      <span class="ap-total-wishlist ap-total"></span>
        </a>    </li>
      <li>
  <a
          class="ap-btn-compare dropdown-item"
          href="{url entity='module' name='leofeature' controller='productscompare'}"
          title="{l s='Compare' d='Shop.Theme.Global'}"
          rel="nofollow"
        >
          <i class="fa fa-retweet"></i>
          <span>{l s='Compare' d='Shop.Theme.Global'}</span>
      <span class="ap-total-compare ap-total"></span>
        </a>
      </li>
      
    </ul>
  </div>
</div>