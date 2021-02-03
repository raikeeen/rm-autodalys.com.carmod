{* 
* @Module Name: Leo Quick Login
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright Leotheme
*}
{if $isLogged}
	
  <div class="leo-quicklogin-wrapper ap-quick-login js-dropdown popup-over">
    <a class="popup-title" href="javascript:void(0)" data-toggle="dropdown">
        <i class="nova-user"></i>
				<span class="user_title hidden-xs-down">{l s='My Account' d='Shop.Theme.Global'}</span>
    		<i class="fa fa-angle-down"></i>
    </a>
    <div class="dropdown-menu popup-content" aria-labelledby="language-selector-label">
      	<ul class="link language-selector">
			<li>
				<a class="account dropdown-item" href="{$my_account_url}" title="{l s='View My Account' mod='leoquicklogin'}" rel="nofollow">
				    <i class="icon account fa fa-user"></i>
				    <span>{$customerName}</span>
				</a>
			</li>
			<li>
				<a class="logout dropdown-item" href="{$logout_url}" rel="nofollow">
				    <i class="icon logout fa fa-sign-out"></i>      
					<span>{l s='Sign out' mod='leoquicklogin'}</span>
				</a>
			</li>
			<li>
		      <a class="ap-btn-wishlist dropdown-item" href="{url entity='module' name='leofeature' controller='mywishlist'}" title="{l s='Patinka' d='Shop.Theme.Global'}" rel="nofollow"
		      >
		        <i class="icon fa fa-heart-o"></i>
		        <span>{l s='Patinka' d='Shop.Theme.Global'}</span>
		    	<span class="ap-total-wishlist ap-total"></span>
		      </a>    
		  	</li>
      	</ul>
    </div>
  </div>

{else}
	{if $leo_type == 'html'}
		{$html_form nofilter}
	{else}
		{if $leo_type == 'dropdown'}
			<div class="dropdown">
		{/if}
		{if $leo_type == 'dropup'}
			<div class="dropup">
		{/if}
		  	<div class="ap-quick-login js-dropdown popup-over">
				<a href="javascript:void(0)" class="leo-quicklogin{if $leo_type == 'dropdown' || $leo_type == 'dropup'}leo-dropdown dropdown-toggle{/if} popup-title" data-enable-sociallogin="{if isset($enable_sociallogin)}{$enable_sociallogin}{/if}" data-type="{$leo_type}" data-layout="{$leo_layout}"{if $leo_type == 'dropdown' || $leo_type == 'dropup'} data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"{/if} title="{l s='Quick Login' mod='leoquicklogin'}" rel="nofollow">
					<i class="nova-user"></i>
					<span class="user_title hidden-xs-down">{l s='My Account' d='Shop.Theme.Global'}</span>
    			<i class="fa fa-angle-down"></i>
				</a>
				{if $leo_type == 'dropdown' || $leo_type == 'dropup'}
						<div class="popup-content dropdown-menu leo-dropdown-wrapper">
							{$html_form nofilter}
						</div>
					</div>
				{/if}
			</div>
		
	{/if}
{/if}

