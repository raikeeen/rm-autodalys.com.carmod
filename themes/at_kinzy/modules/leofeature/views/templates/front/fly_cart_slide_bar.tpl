{* 
* @Module Name: Leo Feature
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright  Leotheme
* @description: Leo feature for prestashop 1.7: ajax cart, review, compare, wishlist at product list 
*}
{if $enable_overlay_background}
	<div class="leo-fly-cart-mask"></div>
{/if}

<div class="leo-fly-cart-slidebar {$type}">
	
	<div class="leo-fly-cart disable-dropdown">
		<div class="leo-fly-cart-wrapper">
			<div class="leo-fly-cart-icon-wrapper">
				<i class="material-icons">close</i>
				<span class="cart-title">{l s='PIRKINIŲ KREPŠELIS' mod='leofeature'}</span>
			</div>
			<div class="dd-fly-cart-cssload-loader"></div>
		</div>
	</div>

</div>