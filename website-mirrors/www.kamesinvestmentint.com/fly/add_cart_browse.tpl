{if $listing.shc_mode == 'fixed'}
	<span id="shopping_cart_{$listing.ID}">
		<a href="javascript:void(0);" id="shc-item-{$listing.ID}" class="add-to-cart{if $tpl_settings.type == 'responsive_42' && $tpl_settings.type != 'general_flatty'}-custom{/if }" title="{$lang.shc_add_to_cart}">
			{if $tpl_settings.type == 'responsive_42'}
				<span>{$lang.shc_add_to_cart}</span>
			{else}
				<img class="png" title="{$lang.shc_add_to_cart}" alt="{$lang.shc_add_to_cart}" src="{$smarty.const.RL_PLUGINS_URL}shoppingCart/static/{if $template_default}default{else}{$config.template}{/if}.png" />
			{/if}
		</a>
	</span>
{/if}