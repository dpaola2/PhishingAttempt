<!-- my cart items list -->

{if !empty($shcItems)}
	{foreach from=$shcItems item='item' name='shcItemsF'}
		<li class="two-inline left clearfix">
			{if $item.Main_photo}
				<div class="item-picture">
					<a href="{$item.listing_link}" target="_blank"><img alt="{$item.Item}" src="{$rlTplBase}img/blank_10x7.gif" style="background-image: url('{$smarty.const.RL_FILES_URL}{$item.Main_photo}');" /></a>
				</div>
			{/if}
			<div class="info">
				<a href="{$item.listing_link}" target="_blank">{$item.Item}</a>
				<div>
					{$item.Quantity} x 
					{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
					{$item.Price}
					{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
				</div>

				<div title="{$lang.remove}" class="close-red" onclick="xajax_deleteItem({$item.ID}, {$item.Item_ID});"></div>
			</div>
		</li>
	{/foreach}
	
	<li class="two-inline clearfix controls">
		<div><a class="button cart" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html{else}?page={$pages.my_shopping_cart}{/if}">{$lang.shc_checkout}</a></div>
		<div><a href="javascript: void(0);" class="clear-cart">{$lang.shc_clear_cart}</a></div>
	</li>
{else}
	<li class="text-notice">{$lang.shc_empty_cart}</li>
{/if}

<!-- my cart items list end -->