{if isset($error) && !empty($error)}
    <div class="lp_carrier error col-xs-12" data-carrier-id="{$id_carrier}">
        {$error}
    </div>
{elseif isset($post) && !empty($post)}
    <div class="lp_carrier col-xs-12" data-carrier-id="{$id_carrier}">
        {$post['name']} {$post['address']} {$post['zip']} {$post['municipality']} {$post['district']} {$post['region']}
    </div>
{/if}