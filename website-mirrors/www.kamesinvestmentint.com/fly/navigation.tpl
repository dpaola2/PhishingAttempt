<!-- listing navigation plugin -->

{if $lnp_return_link || $lnp_data_prev || $lnp_data_next}
	<ul id="lnp_container" class="hide">
		{if $lnp_return_link}
			<li class="link">
				<a title="{$lang.back_to_search_results}" href="{$lnp_return_link}">{if $smarty.const.RL_LANG_DIR == 'rtl'}&rarr;{else}&larr;{/if} {$lang.back_to_search_results}</a>
			</li>
		{/if}
		{if $lnp_data_prev}
			<li class="nav prev">
				<a title="{$lang.listingNav_prev}{if $lnp_data_prev.listing_title}: {$lnp_data_prev.listing_title}{/if}" href="{$lnp_data_prev.href}"></a>
			</li>
		{/if}
		{if $lnp_data_next}
			<li class="nav next">
				<a title="{$lang.listingNav_next}{if $lnp_data_next.listing_title}: {$lnp_data_next.listing_title}{/if}" href="{$lnp_data_next.href}"></a>
			</li>
		{/if}
	</ul>
	
	<script type="text/javascript">//<![CDATA[
	{if $tpl_settings.type == 'responsive_42'}
		{literal}
		$(document).ready(function(){
			$('#content').prepend('<div></div>');
			$('#content > div:first').append($('ul#lnp_container'));
			$('#content > div:first').append($('#content > h1:first'));
			$('ul#lnp_container > li.prev').after($('ul#lnp_container > li.link'));
			$('ul#lnp_container').show();
		});
		{/literal}
	{else}
		{literal}
		$(document).ready(function(){
			$('div#content table.content td.content').prepend($('ul#lnp_container'));
			$('ul#lnp_container li.nav a').css('background', $('div.tabs ul li:not(.active) span.center').css('color'));
			$('ul#lnp_container').show();
		});
		{/literal}
	{/if}
	//]]>
	</script>
{/if}

<!-- listing navigation plugin end -->