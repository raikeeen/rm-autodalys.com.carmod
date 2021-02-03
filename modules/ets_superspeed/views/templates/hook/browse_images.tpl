{*
* 2007-2019 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
* 
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div class="block-browse-image-left">
    {$dir_files nofilter}
</div>
<div class="block-browse-image-right">
    <ul id="list_added_browse_images">
        {if $images}
            {foreach from=$images item='image'}
                <li class="upload" id="image-{$image.image_id|escape:'html':'UTF-8'}">
                    <div class="before">
                        <span class="image_name" title="{$image.image_name|escape:'html':'UTF-8'}">{$image.image_name_hide|escape:'html':'UTF-8'} ({$image.old_size|escape:'html':'UTF-8'})</span>
                    </div>
                    <div class="progress success">
                        <div class="image_dir">{$image.image_dir|escape:'html':'UTF-8'} (<span class="saved">{l s='Save' mod='ets_superspeed'} {$image.saved|escape:'html':'UTF-8'}</span>)</div>
                        <div class="bar" style="width: 100%;"></div>
                    </div>
                    <div class="after">
                        <span class="size">{$image.new_size|escape:'html':'UTF-8'}</span>
                        <a href="{$link->getAdminLink('AdminSuperSpeedImage')|escape:'html':'UTF-8'}&download_image_browse={$image.image_id|escape:'html':'UTF-8'}"><i class="fa fa-download" aria-hidden="true"></i> {l s='Download' mod='ets_superspeed'}</a>
                        <a class="restore_image_browse" href="{$link->getAdminLink('AdminSuperSpeedImage')|escape:'html':'UTF-8'}&restore_image_browse={$image.image_id|escape:'html':'UTF-8'}"><i class="fa fa-undo" aria-hidden="true"></i> {l s='Restore' mod='ets_superspeed'}</a>
                    </div>
                </li>
            {/foreach}
        {/if}
    </ul>
</div>