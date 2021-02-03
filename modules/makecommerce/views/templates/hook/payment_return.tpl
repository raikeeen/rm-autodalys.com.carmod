{if $status == 'ok'}
	<p class="alert alert-success">{l s='Payment successfully processed' mod='makecommerce'}</p>
	<div class="box">
		<p>{l s='Order tracking' mod='makecommerce'} <a href="{$link_to_order}">{l s='here.' mod='makecommerce'}</a></p>
	</div>
{else}
	<p class="alert alert-danger">{l s='Payment failed' mod='makecommerce'}</p>
{/if}