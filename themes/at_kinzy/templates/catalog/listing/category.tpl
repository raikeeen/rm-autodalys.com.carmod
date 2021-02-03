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
{extends file='catalog/listing/product-list.tpl'}

{block name='product_list_header'}
  {include file='catalog/_partials/category-header.tpl' listing=$listing category=$category}
  {if isset($LEO_SUBCATEGORY) && $LEO_SUBCATEGORY && isset($subcategories) && count($subcategories) > 0}
    <div id="subcategories">
      <div class="row">
        {foreach from=$subcategories item=subcategory}
          <div class="subcategory-block col-xl-2 col-lg-3 col-md-4 col-sm-4 col-xs-4 col-sp-6" onclick="location.href='{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)}';">
            <div class="box-subcate">
              <div class="subcategory-image">
                <a href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
                <img class="img-fluid" src="{$link->getCatImageLink($subcategory.link_rewrite, $subcategory.id_image, 'category_default')}" alt="{$subcategory.name|escape:'html':'UTF-8'}"/>
                </a>
              </div>
              <div class="subcategory-meta" style="min-height: 60px;">
                <h3><a class="subcategory-name" href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}">{$subcategory.name|truncate:30:'...'|escape:'html':'UTF-8'}</a></h3>
                <div class="subcategory-description">{$subcategory.description|strip_tags|truncate:60:'...'|escape:'html':'UTF-8' nofilter}</div>   
              </div>
            </div>
          </div>
        {/foreach}
      </div>
    </div>
  {/if}
{/block}
