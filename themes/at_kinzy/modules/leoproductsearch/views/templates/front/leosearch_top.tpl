{* 
* @Module Name: Leo Product Search
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright Leotheme
*}

{function name="lps_categories" nodes=[] depth=0}
  {strip}
    {if $nodes|count}
        {foreach from=$nodes item=node}         
            <a href="#" data-cate-id="{$node.id_category|escape:'htmlall':'UTF-8'|stripslashes}" data-cate-name="{$node.name}" class="cate-item cate-level-{$node.level_depth}{if isset($selectedCate) && $node.id_category eq $selectedCate} active{/if}" >{if $node.level_depth > 1}{str_repeat('-', $node.level_depth)}{/if}{$node.name}</a>           
            {lps_categories nodes=$node.children depth=$depth+1}           
        {/foreach}
    {/if}
  {/strip}
{/function}

<div id="leo_search_block_top" class="search-widget block exclusive{if $en_search_by_cat} search-by-category{/if}">
	<a id="click_show_search" href="javascript:void(0)" data-toggle="dropdown" class="float-xs-right popup-title">
		<i class="nova-search"></i>
	 </a>
	<form method="get" action="{$link->getPageLink('productsearch', true)|escape:'html':'UTF-8'}" id="leosearchtopbox">
		<input type="hidden" name="fc" value="module" />
		<input type="hidden" name="module" value="leoproductsearch" />
		<input type="hidden" name="controller" value="productsearch" />
        <input type="hidden" name="leoproductsearch_static_token" value="{$leoproductsearch_static_token|escape:'htmlall':'UTF-8'|stripslashes}"/>
		<div class="block_content clearfix leoproductsearch-content">		
			<div class="list-cate-wrapper" {if !$en_search_by_cat} style="display: none" {/if} >
				<input id="leosearchtop-cate-id" name="cate" value="{if isset($selectedCate)}{$selectedCate}{/if}" type="hidden">
				<a id="dropdownListCateTop" class="select-title" rel="nofollow" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span>{if $selectedCateName != ''}{$selectedCateName}{else}{l s='All Categories' d='Shop.Theme.Global'}{/if}</span>
					<i class="fa fa-angle-down"></i>
				</a>
				<div class="list-cate dropdown-menu" aria-labelledby="dropdownListCateTop">
					<a href="#" data-cate-id="" data-cate-name="{l s='All Categories' d='Shop.Theme.Global'}" class="cate-item{if $selectedCate == ''} active{/if}" >{l s='All Categories' d='Shop.Theme.Global'}</a>				
					<a href="#" data-cate-id="{$cates.id_category|escape:'htmlall':'UTF-8'|stripslashes}" data-cate-name="{$cates.name}" class="cate-item cate-level-{$cates.level_depth}{if isset($selectedCate) && $cates.id_category eq $selectedCate} active{/if}" >{if $cates.level_depth > 1}{str_repeat('-', $cates.level_depth)}{/if}{$cates.name}</a>
					{lps_categories nodes=$cates.children}
				</div>
			</div>
			<div class="leoproductsearch-result">
				<div class="leoproductsearch-loading cssload-container">
					<div class="cssload-speeding-wheel"></div>
				</div>
				<input class="search_query form-control grey" type="text" id="leo_search_query_top" name="search_query" value="{$search_query|escape:'htmlall':'UTF-8'|stripslashes}" placeholder="{l s='Search our catalog' d='Shop.Theme.Global'}" />
				<button type="submit" id="leo_search_top_button" class="btn btn-default button button-small"><i class="nova-search"></i></button> 
			</div>
		</div>
	</form>
</div>

<script type="text/javascript">
	var blocksearch_type = 'top';
</script>
