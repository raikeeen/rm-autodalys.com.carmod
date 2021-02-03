{*
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@buy-addons.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    Buy-addons    <contact@buy-addons.com>
* @copyright 2007-2020 Buy-addons
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<link rel="stylesheet" type="text/css" href="./../modules/babrowsertabbadge/views/css/babrowsertabbadge.css">
{if $demoMode=="1"}
	<div class="bootstrap ba_error">
		<div class="module_error alert alert-danger">
			{l s='You are use ' mod='babrowsertabbadge'}
			<strong>{l s='Demo Mode ' mod='babrowsertabbadge'}</strong>
			{l s=', so some buttons, functions will be disabled because of security. ' mod='babrowsertabbadge'}
			{l s='You can use them in Live mode after you puchase our module. ' mod='babrowsertabbadge'}
			{l s='Thanks !' mod='babrowsertabbadge'}
		</div>
	</div>
{/if}
<form class="form-horizontal form-group" method="POST" id="uploading" enctype="multipart/form-data">
<div class="bootstrap panel" style="clear:both;">
	<h3 style="font-size: 15px;"><i class="icon-linux"></i>{l s='BROWSER TAB NOTIFICATIONS' mod='babrowsertabbadge'}</h3>
		<input type="hidden" name="link_icon" value="{$link_icon|escape:'htmlall':'UTF-8'}" id="link_icon">
		<input type="hidden" name="token_babrowsertabbadge" value="{$token_babrowsertabbadge|escape:'htmlall':'UTF-8'}" id="token_babrowsertabbadge">
		<div class="form-group">
			<div class="row">
				<label class="control-label col-lg-3 required" for="available_date_attribute">
					<span class="label-tooltip" data-toggle="tooltip" data-original-title="If this product is out of stock, you can indicate when the product will be available again.">
						{l s='Background color' mod='babrowsertabbadge'}
					</span>
				</label>
				{foreach from=$arr_language item=foo} 
					<div class="translatable-field lang-{$foo['id_lang']}" style="{if 
						$id_lang_default == $foo['id_lang']}
						display: block;{/if}
						{if $id_lang_default != $foo['id_lang']}
						display: none;{/if}; padding-bottom: 10px; padding-left: 0px;">
						<div class="col-lg-4" style="width: 26%;">
							<input type="text" name="background_color{$foo['id_lang']|escape:'htmlall':'UTF-8'}" {if isset($foo['background_color'])}value="{$foo['background_color']|escape:'htmlall':'UTF-8'}"{/if} class="type_input{$foo['id_lang']|escape:'htmlall':'UTF-8'} rte updateCurrentText jscolor jscolor-active" id="background_color{$foo['id_lang']|escape:'htmlall':'UTF-8'}" data-id-lang="{$foo['id_lang']|escape:'htmlall':'UTF-8'}">
						</div>
						
						<div class="col-lg-2">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
								{$foo['iso_code']|escape:'htmlall':'UTF-8'}
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								{foreach from=$arr_language item=foo}
								<li>
									<a href="javascript:hideOtherLanguage({$foo['id_lang']|escape:'htmlall':'UTF-8'});" >{$foo['name']|escape:'htmlall':'UTF-8'}</a>
								</li>
								{/foreach}
							</ul>
						</div>
					</div>
				{/foreach}
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<label class="control-label col-lg-3 required" for="">
					<span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="If this product is out of stock, you can indicate when the product will be available again.">
						{l s='Text color' mod='babrowsertabbadge'}
					</span>
				</label>
				{foreach from=$arr_language item=foo} 
						<div class="translatable-field lang-{$foo['id_lang']}" style="{if 
							$id_lang_default == $foo['id_lang']}
							display: block;{/if}
							{if $id_lang_default != $foo['id_lang']}
							display: none;{/if}; padding-bottom: 10px; padding-left: 0px;">
							<div class="col-lg-4" style="width: 26%;">
								<input type="text" name="text_color{$foo['id_lang']|escape:'htmlall':'UTF-8'}" {if isset($foo['text_color'])}value="{$foo['text_color']|escape:'htmlall':'UTF-8'}"{/if} class="type_input{$foo['id_lang']|escape:'htmlall':'UTF-8'} rte updateCurrentText jscolor jscolor-active" id="text_color{$foo['id_lang']|escape:'htmlall':'UTF-8'}" data-id-lang="{$foo['id_lang']|escape:'htmlall':'UTF-8'}">
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
									{$foo['iso_code']|escape:'htmlall':'UTF-8'}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$arr_language item=foo}
									<li>
										<a href="javascript:hideOtherLanguage({$foo['id_lang']|escape:'htmlall':'UTF-8'});" >{$foo['name']|escape:'htmlall':'UTF-8'}</a>
									</li>
									{/foreach}
								</ul>
							</div>
						</div>
				{/foreach}
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<label class="control-label col-lg-3">
					<span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="Format ICON, GIF, PNG. Filesize 2.00 MB max..">
						{l s='Favicon Icon' mod='babrowsertabbadge'}
					</span>
				</label>
				<div class="col-lg-9">
					<div class="col-lg-5 a1">
						<input type="file">
					</div>
					<div class="col-lg-9">
						<div class="form-group">
							<div class="row">
								<div class="col-lg-8 a2">
									<input id="image" type="file" name="image" class="hide">
									<div class="dummyfile input-group">
										<span class="input-group-addon"><i class="icon-file"></i></span>
										<input id="image-name" type="text" name="filename" readonly="">
										<span class="input-group-btn">
											<button id="image-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
											<i class="icon-folder-open"></i> {l s='Add file' mod='babrowsertabbadge'}</button>
										</span>
									</div>
								</div>
							</div>		
						</div>
						{if $arr_babrowsertabbadge['upload_icon'] != ''}
							<div class="col-lg-9 a3" id="delete_div_img">	
								<div class="form-group">
									<div class="row a7">
										<div style="background-image: url('./../modules/babrowsertabbadge/views/img/icon_image/{$arr_babrowsertabbadge['upload_icon']|escape:'htmlall':'UTF-8'}');background-repeat: no-repeat;width:auto;min-height: 56px;height: auto;">
										</div>
											<div class="col-lg-4 a4">
												<div class="row">
													<button class="btn btn-default" type="button" id="delete_img_icon" name="submitDeleteAttachments">
														<i class="icon-trash"></i> {l s='Delete' mod='babrowsertabbadge'}
													</button>
												</div>
											</div>
									</div>
								</div>
							</div>
						{/if}
						<div class="col-lg-12 a5">
							<div class="form-group">
								<div class="row">
									<div class="alert alert-info mt-2">{l s='You should use the a image as *.ico (recommend). In the case you use *.png extensions. The module will be changed to *.ico extension.' mod='babrowsertabbadge'}</br>
									{l s='The maximum capacity of uploading images is 2MB.' mod='babrowsertabbadge'}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<label class="control-label col-lg-3 required" for="">
					<span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="If this product is out of stock, you can indicate when the product will be available again.">
						{l s='Text' mod='babrowsertabbadge'}
					</span>
				</label>
				{foreach from=$arr_language item=foo} 
					<div class="translatable-field lang-{$foo['id_lang']}" style="{if 
						$id_lang_default == $foo['id_lang']}
						display: block;{/if}
						{if $id_lang_default != $foo['id_lang']}
						display: none;{/if}; padding-bottom: 10px; padding-left: 0px;">
						<div class="col-lg-4" style="width: 26%;">
							<input type="text" name="text{$foo['id_lang']|escape:'htmlall':'UTF-8'}" placeholder="My Store" {if isset($foo['text'])}value="{$foo['text']|escape:'htmlall':'UTF-8'}"{/if} class="type_input{$foo['id_lang']|escape:'htmlall':'UTF-8'} rte updateCurrentText" id="text{$foo['id_lang']|escape:'htmlall':'UTF-8'}" data-id-lang="{$foo['id_lang']|escape:'htmlall':'UTF-8'}">
						</div>
						<div class="col-lg-2">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
								{$foo['iso_code']|escape:'htmlall':'UTF-8'}
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								{foreach from=$arr_language item=foo}
								<li>
									<a href="javascript:hideOtherLanguage({$foo['id_lang']|escape:'htmlall':'UTF-8'});" >{$foo['name']|escape:'htmlall':'UTF-8'}</a>
								</li>
								{/foreach}
							</ul>
						</div>
					</div>
				{/foreach}
			</div>
		</div>
		<div class="panel-footer a6">
			<button type="submit" name="submitAddproduct" class="btn btn-default pull-right">
				<i class="process-icon-save"></i>
					{l s='Save' mod='babrowsertabbadge'}
			</button>
		</div>
</div>
</form>

<script src="./../modules/babrowsertabbadge/views/js/jscolor.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$('#image-selectbutton').click(function(e) {
			$('#image').trigger('click');
		});

		$('#image-name').click(function(e) {
			$('#image').trigger('click');
		});

		$('#image-name').on('dragenter', function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		$('#image-name').on('dragover', function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		$('#image-name').on('drop', function(e) {
			e.preventDefault();
			var files = e.originalEvent.dataTransfer.files;
			$('#image')[0].files = files;
			$(this).val(files[0].name);
			// console.log($(this).val(files[0].name));
		});

		$('#image').change(function(e) {
			if ($(this)[0].files !== undefined)
			{
				var files = $(this)[0].files;
				var name  = '';
				$.each(files, function(index, value) {
					name += value.name+', ';
				});
				$('#image-name').val(name.slice(0, -2));
			}
			else // Internet Explorer 9 Compatibility
			{
				var name = $(this).val().split(/[\\/]/);
				$('#image-name').val(name[name.length-1]);
			}
		});

		if (typeof image_max_files !== 'undefined')
		{
			$('#image').closest('form').on('submit', function(e) {
				if ($('#image')[0].files.length > image_max_files) {
					e.preventDefault();
					alert('You can upload a maximum of  files');
				}
			});
		}
		$("#delete_img_icon").click(function(e) {
			$("#content").prepend("<div style='display:none;' id='growls' class='default notify'></div>");
			$.ajax ({
				url: '../index.php?controller=deleteimg&fc=module&module=babrowsertabbadge',
				type:"POST",
				data: 'token_babrowsertabbadge={$token_babrowsertabbadge|escape:'htmlall':'UTF-8'}',
				async: true,
				success: function (data) {
					$('#delete_div_img').css('display','none');
					$("#growls").append("<div class='growl growl-notice growl-medium'>"
                          +"<div class='growl-close'>×</div>"
                          +"<div class='growl-title'></div>"
                          +"<div class='growl-message'>The image was successfully deleted.</div>"
                          +"</div>"
                          );
			        $(".notify").css("display", "none");
			        $('.notify').fadeIn(500);
			        var timeout = setTimeout(function(){ $(".notify").fadeOut(500); }, 5000);
			        $(".growl-close").click(function(){
			            $(".notify").fadeOut(500);
			            clearTimeout(timeout);
			        });

				} 
			});
		});
	});

</script>