{**
Copyed from:
layouts/layout-both-columns.tpl
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

    {block name='hook_after_body_opening_tag'}
      {hook h='displayAfterBodyOpeningTag'}
    {/block}

    <main id="page">
      {block name='product_activation'}
        {include file='catalog/_partials/product-activation.tpl'}
      {/block}
      <header id="header">
        <div class="header-container">
          {block name='header'}
            {include file='_partials/header.tpl'}
          {/block}
        </div>
      </header>
      {block name='notifications'}
        {include file='_partials/notifications.tpl'}
      {/block}
      <section id="wrapper">
		