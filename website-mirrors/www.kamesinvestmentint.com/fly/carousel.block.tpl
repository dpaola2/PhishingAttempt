<!-- carousel block -->
{if $listings}	
	{assign var='carousel_option' value=$carousel_options[$block.ID]}
	{assign var='phrase_sale' value='listing_fields+name+sale_rent_1'}
	<div class="carousel {$carousel_option.Direction}{if $carousel_option.Direction == 'vertical'} cVertical{else} cHorizontal{/if}">		
		<div id="carousel_{$block.Key}" class="carousel_block">
			<ul class="featured{if $listing_types.$type.Photo} with-pictures{/if} clearfix">			
				{foreach from=$listings item='featured_listing' key='key'}
					{assign var='type' value=$featured_listing.Listing_type}
					{assign var='page_key' value=$listing_types.$type.Page_key}
					{if $tpl_settings.type == 'responsive_42'}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'featured_item.tpl'}
					{else}
						<li id="{$block.Key}_{$featured_listing.ID}" class="item">
							{include file=$smarty.const.RL_PLUGINS|cat:'listings_carousel'|cat:$smarty.const.RL_DS|cat:'carousel.listing.tpl' featured_listing=$featured_listing type=$type page_key=$page_key}
						</li>
					{/if}
				{/foreach}
			</ul>
		</div>
	</div>	
	{if $listings|@count >= $carousel_option.Visible}
		<script type="text/javascript">
			rlCarousel['carousel_{$block.Key}'] = {$carousel_option.Number}-{$listings|@count};
			{literal}
			$(document).ready(function(){
				$("#{/literal}carousel_{$block.Key}{literal}").carousel({
					options: {/literal}'{$block.options}'{literal},
					priceTag: {/literal}{if $tpl_settings.featured_price_tag}true{else}false{/if}{literal},
					templateName: {/literal}'{$tpl_settings.name}'{literal},
					btnNext: ".next_{/literal}carousel_{$block.Key}{literal}",
					btnPrev: ".prev_{/literal}carousel_{$block.Key}{literal}",
					vertical: {/literal}{if $carousel_option.Direction == 'vertical'}true{else}false{/if}{literal},
					circular: {/literal}{if $carousel_option.Round == 1}true{else}false{/if}{literal},
					diraction: {/literal}'{$smarty.const.RL_LANG_DIR}'{literal},					
					visible: {/literal}{$carousel_option.Visible}{literal},
					scroll: {/literal}{$carousel_option.Per_slide}{literal},
					number: {/literal}{$carousel_option.Number}{literal},
					count: {/literal}{$listings|@count}{literal},
					auto: {/literal}{$carousel_option.Delay}{literal}000
					//auto: 0
				});
			});
			{/literal}
			
		</script>
	{/if}
{else}

	{if $listing_types.$type.Page}
		{if $config.mod_rewrite}
			{assign var='href' value=$rlBase|cat:$pages.add_listing|cat:'.html'}
		{else}
			{assign var='href' value=$rlBase|cat:'?page='|cat:$pages.add_listing}
		{/if}
		{assign var='link' value='<a href="'|cat:$href|cat:'">$1</a>'}
		{$lang.no_listings_here|regex_replace:'/\[(.+)\]/':$link}
	{else}
		{$lang.no_listings_here_submit_deny}
	{/if}
{/if}

<!-- carousel block end -->