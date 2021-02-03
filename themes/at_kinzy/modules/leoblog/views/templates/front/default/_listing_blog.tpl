{* 
* @Module Name: Leo Blog
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright Leotheme
* @description: Content Management
*}

<article class="blog-item">
	<div class="blog-image-container">
		<div class="left-block">
			{if $blog.image && $config->get('listing_show_image',1)}
				<div class="blog-image">
					<img src="{$blog.preview_url|escape:'html':'UTF-8'}" title="{$blog.title|escape:'html':'UTF-8'}" alt="" class="img-fluid" />
				</div>
			{/if}
		</div>
		<div class="right-block">
			{if $config->get('listing_show_title','1')}
				<h4 class="title">
					<a href="{$blog.link|escape:'html':'UTF-8'}" title="{$blog.title|escape:'html':'UTF-8'}">{$blog.title|escape:'html':'UTF-8'}</a>
				</h4>
			{/if}
			<div class="blog-meta">
				{if $config->get('listing_show_author','1')&&!empty($blog.author)}
					<span class="author">
						<span class="icon-author"></span><a href="{$blog.author_link|escape:'html':'UTF-8'}" title="{$blog.author|escape:'html':'UTF-8'}"> {$blog.author|escape:'html':'UTF-8'}</a>
					</span>
				{/if}
				{if $config->get('listing_show_category','1')}
					<span class="cat"> <span class="icon-list"></span> 
						<a href="{$blog.category_link|escape:'html':'UTF-8'}" title="{$blog.category_title|escape:'html':'UTF-8'}"> {$blog.category_title|escape:'html':'UTF-8'}</a>
					</span>
				{/if}

				{if $config->get('listing_show_hit','1')}	
					<span class="hits">
						<span class="icon-hits"></span> {$blog.hits|intval}
					</span>
				{/if}

				{if isset($blog.comment_count)&&$config->get('listing_show_counter','1')}	
					<span class="nbcomment">
						<span class="icon-comment"> {$blog.comment_count|intval} {l s='Comment' d='Shop.Theme.Global'}</span> 
					</span>
				{/if}
			</div>
			<div class="blog-info">
				{if $config->get('listing_show_description','1')}
					<div class="blog-desc">
						{$blog.description|strip_tags:'UTF-8'|truncate:160:'...' nofilter}{* HTML form , no escape necessary *}
					</div>
				{/if}
			</div>
			<div class="blog-bottom">
				{if $config->get('listing_show_created','1')}
					<span class="created">
						{* <i class="material-icons">access_time</i> <span>{l s='On' d='Shop.Theme.Global'}: </span>  *}
						<time class="date" datetime="{strtotime($blog.date_add)|date_format:"%Y"|escape:'html':'UTF-8'}">
							<span class="left-date">
								<span class="day">
									{assign var='blog_day' value=strtotime($blog.date_add)|date_format:"%e"}	
									{l s=$blog_day d='Shop.Theme.Global'} <!-- day of month -->	
									
								</span>
							</span>
							<span class="right-date">
								<span class="month">
									{assign var='blog_month' value=strtotime($blog.date_add)|date_format:"%b"}
									{l s=$blog_month d='Shop.Theme.Global'}		<!-- month-->
								</span>
								<span class="year">
									{assign var='blog_year' value=strtotime($blog.date_add)|date_format:"%Y"}		
									{l s=$blog_year d='Shop.Theme.Global'}	<!-- year -->
								</span>
							</span>
						</time>
					</span>
				{/if}
				{if $config->get('listing_show_readmore',1)}
					<a href="{$blog.link|escape:'html':'UTF-8'}" title="{$blog.title|escape:'html':'UTF-8'}" class="more btn btn-primary">{l s='Read more' d='Shop.Theme.Global'}</a>
				{/if}
			</div>
		</div>
	</div>
	<div class="hidden-xl-down hidden-xl-up datetime-translate">
		{l s='Sunday' d='Shop.Theme.Global'}
		{l s='Monday' d='Shop.Theme.Global'}
		{l s='Tuesday' d='Shop.Theme.Global'}
		{l s='Wednesday' d='Shop.Theme.Global'}
		{l s='Thursday' d='Shop.Theme.Global'}
		{l s='Friday' d='Shop.Theme.Global'}
		{l s='Saturday' d='Shop.Theme.Global'}
		
		{l s='January' d='Shop.Theme.Global'}
		{l s='February' d='Shop.Theme.Global'}
		{l s='March' d='Shop.Theme.Global'}
		{l s='April' d='Shop.Theme.Global'}
		{l s='May' d='Shop.Theme.Global'}
		{l s='June' d='Shop.Theme.Global'}
		{l s='July' d='Shop.Theme.Global'}
		{l s='August' d='Shop.Theme.Global'}
		{l s='September' d='Shop.Theme.Global'}
		{l s='October' d='Shop.Theme.Global'}
		{l s='November' d='Shop.Theme.Global'}
		{l s='December' d='Shop.Theme.Global'}
					
	</div>
</article>
