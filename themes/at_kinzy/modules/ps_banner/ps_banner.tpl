{**
 *   PrestaShop
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
 * @copyright   PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

 <div class="effect-layla">
 	<a class="banner" href="{$banner_link}" title="{$banner_desc}">
	  {if isset($banner_img)}
	    <img src="{if isset($ets_link_base)}{$ets_link_base}/modules/ets_superspeed/views/img/preloading.png{else}{$banner_img}{/if}" class="lazyload" data-src="{$banner_img}" alt="{$banner_desc}" title="{$banner_desc}" class="img-fluid"><span class="ets_loading">
{if isset($ETS_SPEED_LOADING_IMAGE_TYPE) && $ETS_SPEED_LOADING_IMAGE_TYPE == 'type_1'}
    <div class="spinner_1"></div>
{elseif isset($ETS_SPEED_LOADING_IMAGE_TYPE) && $ETS_SPEED_LOADING_IMAGE_TYPE == 'type_2'}
    <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
{elseif isset($ETS_SPEED_LOADING_IMAGE_TYPE) && $ETS_SPEED_LOADING_IMAGE_TYPE == 'type_3'}
    <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
{elseif isset($ETS_SPEED_LOADING_IMAGE_TYPE) && $ETS_SPEED_LOADING_IMAGE_TYPE == 'type_4'}
    <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
{elseif isset($ETS_SPEED_LOADING_IMAGE_TYPE) && $ETS_SPEED_LOADING_IMAGE_TYPE == 'type_5'}
    <div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
{/if}
</span>
	  {else}
	    <span>{$banner_desc}</span>
	  {/if}
	</a>
 </div>

