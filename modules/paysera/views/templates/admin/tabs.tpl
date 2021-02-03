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
<script type='text/javascript' src="{$backOfficeJsUrl|escape:'htmlall':'UTF-8'}"></script>
<link href="{$backOfficeCssUrl|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">

{if $message}
	{if $message.success}
		{assign var="alert" value="alert-success"}
	{else}
		{assign var="alert" value="alert-danger"}
	{/if}
	<div class="bootstrap">
		<div class="module_confirmation conf confirm alert {$alert|escape:'htmlall':'UTF-8'}">
			<button type="button" class="close" data-dismiss="alert">×</button>
			{$message.text|escape:'htmlall':'UTF-8'}
		</div>
	</div>
{/if}

<div id="formSettingsPanel" class="panel">
	<div class="panel-heading">
		<i class="icon-cogs"></i>
		{$settingsTitle|escape:'htmlall':'UTF-8'}
	</div>
	<div class="paysera-tabs">
        {if $tabs}
			<nav>
                {foreach $tabs as $tab}
					<a class="tab-title
			   {if isset($selectedTab) && $tab.id==$selectedTab}nav-tab-active{/if}"
					   href="#"
					   id="{$tab.id|escape:'htmlall':'UTF-8'}"
					   data-target="#paysera-tabs-{$tab.id|escape:'htmlall':'UTF-8'}">
                        {$tab.title|escape:'htmlall':'UTF-8'}
					</a>
                {/foreach}
			</nav>
			<div class="content">
                {foreach $tabs as $tab}
					<div class="tab-content" id="paysera-tabs-{$tab.id|escape:'htmlall':'UTF-8'}">
                        {html_entity_decode($tab.content|escape:'htmlall':'UTF-8')}
					</div>
                {/foreach}
			</div>
        {/if}
	</div>
</div>
