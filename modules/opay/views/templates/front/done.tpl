{if $status == 1}
<p class="alert alert-success">{l s='Thank you, your payment accepted' mod='opay'}.</p>
<br>
{elseif $status == 2}
    <p class="alert alert-warning">{l s='Your payment is in process' mod='opay'}.</p>
    <br>
    <p><strong>{l s='It can take a few minutes' mod='opay'}</strong></p>
{/if}
