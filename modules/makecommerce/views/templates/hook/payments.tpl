<script type="text/javascript">
    var mk_ajax_url = "{$mk_ajax_url}";
    mk_show_country_code = "{$show_country_code}";
    mk_expanded = "{$expanded}";
</script>
{if isset($widget) && $widget}
    <div id="mk_widget" {if $expanded}class="expanded"{/if}>
        <div class="row">
            <div class="col-xs-12">
                <div class="payment_module">
                    {if isset($widget_title) && $widget_title}
                        <a>{$widget_title}</a>
                    {/if}
                    {if !$expanded}<i class="material-icons keyboard arrow down">keyboard_arrow_down</i>{/if}
                    <div class="payment_method_list">
                        {if isset($show_country_code) && $show_country_code}
                            <ul class="flag_mk_select">
                                {foreach from=$countries item=country}
                                    <li class="{if $country == $current_country}selected{/if}"><a href="#" data-country="{$country}"><img src="{_MODULE_DIR_}makecommerce/views/img/flags/{$country}.png"></a></li>
                                {/foreach}
                            </ul>
                        {/if}
                        {if isset($separate_group) && $separate_group}
                            <ul>
                                {foreach from=$payment_methods item=method}
                                    {if $method->type == "banklinks" || $method->type == "other"}
                                        <li class="mk_method mk_country-{$method->country}">
                                            <a href="{$method->link}">
                                                {if $method->img}<img src="{$method->img}" alt="{l s='Pay by' mod='makecommerce'} {$method->name}" class="logo_size_{$logo_size}">{/if}
                                            </a>
                                        </li>
                                    {/if}
                                {/foreach}
                            </ul>
                            <ul>
                                {foreach from=$payment_methods item=method}
                                    {if $method->type == "cards"}
                                        <li class="mk_method mk_country-{$method->country}">
                                            <a href="{$method->link}">
                                                {if $method->img}<img src="{$method->img}" alt="{l s='Pay by' mod='makecommerce'} {$method->name}" class="logo_size_{$logo_size}">{/if}
                                            </a>
                                        </li>
                                    {/if}
                                {/foreach}
                            </ul>
                            <ul>
                                {foreach from=$payment_methods item=method}
                                    {if $method->type == "payLater"}
                                        {if ($method->min_amount) eq 0 || ($method->min_amount lte $order_total) }
                                            {if ($method->max_amount eq 0) || (($method->max_amount gt 0) && ($method->max_amount gte $order_total))}
                                        <li class="mk_method mk_country-{$method->country}">
                                            <a href="{$method->link}">
                                                {if $method->img}<img src="{$method->img}" alt="{l s='Pay by' mod='makecommerce'} {$method->name}" class="logo_size_{$logo_size}">{/if}
                                            </a>
                                        </li>
                                            {/if}
                                        {/if}
                                    {/if}
                                {/foreach}
                            </ul>
                        {else}
                            <ul>
                                {foreach from=$payment_methods item=method}
                                    {if $method->type == "payLater"}
                                        {if ($method->min_amount) eq 0 || ($method->min_amount lte $order_total) }
                                            {if ($method->max_amount eq 0) || (($method->max_amount gt 0) && ($method->max_amount gte $order_total))}
                                        <li class="mk_method mk_country-{$method->country}">
                                            <a href="{$method->link}">
                                                {if $method->img}<img src="{$method->img}" alt="{l s='Pay by' mod='makecommerce'} {$method->name}" class="logo_size_{$logo_size}">{/if}
                                            </a>
                                        </li>
                                            {/if}
                                        {/if}
                                    {/if}
                                    {if $method->type != "payLater"}
                                    <li class="mk_method mk_country-{$method->country}">
                                        <a href="{$method->link}">
                                            {if $method->img}<img src="{$method->img}" alt="{l s='Pay by' mod='makecommerce'} {$method->name}" class="logo_size_{$logo_size}">{/if}
                                        </a>
                                    </li>
                                    {/if}
                                {/foreach}
                            </ul>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{else}
    <div id="mk_list">
        {foreach from=$payment_methods item=method}
            {if $method->type == "payLater"}
                {if ($method->min_amount) eq 0 || ($method->min_amount lte $order_total) }
                    {if ($method->max_amount eq 0) || (($method->max_amount gt 0) && ($method->max_amount gte $order_total))}
                        <div class="row">
                            <div class="col-xs-12">
                                <p class="payment_module">
                                    <a href="{$method->link}">
                                        {if $method->img}<img src="{$method->img}" alt="{l s='Pay by' mod='makecommerce'} {$method->name}" class="logo_size_s">{/if}
                                        {l s='Pay by' mod='makecommerce'} {$method->name} {if $method->country != 'all' && $show_country_code}<span>({$method->country})</span> {l s='banklink' mod='makecommerce'}{else}{l s='card' mod='makecommerce'}{/if}
                                    </a>
                                </p>
                            </div>
                        </div>
                    {/if}
                {/if}
            {/if}
            {if $method->type != "payLater"}
            <div class="row">
                <div class="col-xs-12">
                    <p class="payment_module">
                        <a href="{$method->link}">
                            {if $method->img}<img src="{$method->img}" alt="{l s='Pay by' mod='makecommerce'} {$method->name}" class="logo_size_s">{/if}
                            {l s='Pay by' mod='makecommerce'} {$method->name} {if $method->country != 'all' && $show_country_code}<span>({$method->country})</span> {l s='banklink' mod='makecommerce'}{else}{l s='card' mod='makecommerce'}{/if}
                        </a>
                    </p>
                </div>
            </div>
            {/if}
        {/foreach}
    </div>
{/if}
