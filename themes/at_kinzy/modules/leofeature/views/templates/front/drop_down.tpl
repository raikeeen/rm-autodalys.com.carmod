{* 
* @Module Name: Leo Feature
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright  Leotheme
* @description: Leo feature for prestashop 1.7: ajax cart, review, compare, wishlist at product list 
*}

{if $only_total != 1}
<div class="leo-dropdown-cart-content clearfix">
	<div class="leo-dropdown-list-item-warpper">
		<ul class="leo-dropdown-list-item">{foreach from=$cart.products item=product name="cart_product"}<li style="width: {$width_cart_item}px; height: {$height_cart_item}px" class="leo-dropdown-cart-item clearfix{if ($product.attributes|count && $show_combination) || ($product.customizations|count && $show_customization)} has-view-additional{/if}{if $smarty.foreach.cart_product.first} first{/if}{if $smarty.foreach.cart_product.last} last{/if}">						
			<div class="leo-cart-item-img">
				{if $product.images}
				<a class="label" href="{$product.url}" title="{$product.name}"><img class="img-fluid" src="{$product.images.0.bySize.small_default.url}" alt="{$product.name}" title="{$product.name}"/></a>
				{/if}	
			</div>						
			<div class="leo-cart-item-info">					
				<div class="product-name"><a class="label" href="{$product.url}" title="{$product.name}">{$product.name|truncate:30:'...'}</a></div>
				{if $product.attributes|count && $show_combination}			
					<div class="combinations">
						<span class="value">
							{foreach from=$product.attributes key="attribute" item="value"}
								{$value}
							{/foreach}
						</span>
					</div>
				{/if}
				<div class="product-price">
					<div class="current-price">
						<span class="price">{$product.price}</span>
					</div>
					{if $enable_update_quantity}
						<div class="product-quantity">
							<span class="x-character">x</span>
							<span class="product-qty">{$product.quantity}</span>
						</div>
					{/if}
				</div>
			</div>
			<a class="leo-remove-from-cart"					
			href="javascript:void(0)"					
			title="{l s='Remove from cart' mod='leofeature'}" 
			data-link-url="{$product.remove_from_cart_url}"
			data-id-product = "{$product.id_product|escape:'javascript'}"
			data-id-product-attribute = "{$product.id_product_attribute|escape:'javascript'}"
			data-id-customization = "{$product.id_customization|escape:'javascript'}"
			>
			<i class="material-icons">&#xE872;</i>
		</a>
	</li>{/foreach}</ul>
</div>
<div class="leo-dropdown-bottom">
	{/if}
	<div class="leo-dropdown-total" data-cart-total="{$cart.products_count}">
		<div class="leo-dropdown-cart-subtotals">
			{foreach from=$cart.subtotals item="subtotal"}
			{if $subtotal}
			<div class="{$subtotal.type} clearfix">
				<div class="row">
					<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<span class="label">{$subtotal.label}</span>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<span class="value">{$subtotal.value}</span>
					</div>
				</div>
			</div>
			{/if}
			{/foreach}
		</div>
		<div class="leo-dropdown-cart-total clearfix">
			<div class="row">
				<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<span class="label">{$cart.totals.total.label}</span>
				</div>
				<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<span class="value">{$cart.totals.total.value}</span>
				</div>
			</div>
		</div>
	</div>
	{if $only_total != 1}
	<div class="leo-cart-dropdown-action clearfix">
		<a class="cart-dropdow-button cart-dropdow-viewcart btn btn-primary btn-outline" href="{$cart_url}">{l s='Žiūrėti Krepšelį' mod='leofeature'}</a>
		<a class="cart-dropdow-button cart-dropdow-checkout btn btn-primary btn-outline" href="{$order_url}">{l s='Apmokėjimas' mod='leofeature'}</a>
	</div>
</div>
</div>
{/if}