{if !empty($shcItems)}
	<div class="fieldset">
		<table class="sTable items">
			{foreach from=$shcItems item='item' name='shcItemsF'}
				<tr>
					<td class="item_photo" valign="top" align="{$text_dir}" width="60">
						<a href="{$item.listing_link}" target="_blank">
							<img class="item_photo" alt="{$item.Item}" style="width: 50px;" src="{if empty($item.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$item.Main_photo}{/if}" />
						</a>
					</td>
					<td>
						<a href="{$item.listing_link}" target="_blank">{$item.Item}</a>
						{strip}<div>
							{$item.Quantity} <span dir="{$smarty.const.RL_LANG_DIR}">x</span> {if $config.system_currency_position == 'before'}{$config.system_currency}{/if}{$item.Price|number_format:2:'.':','}{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
						</div>{/strip}
					</td>
					<td width="12" align="center" valign="top">
						<a href="#" onclick="xajax_deleteItem({$item.ID}, {$item.Item_ID});"><img src="{$rlTplBase}img/blank.gif" class="item_del" /></a>
					</td>
				</tr>
			{/foreach}
			
			<tr>
				<td colspan="3" class="line">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="right">
					<a href="javascript: void(0);" class="clear-cart">{$lang.shc_clear_cart}</a>&nbsp;&nbsp;
					<a class="button" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html{else}?page={$pages.my_shopping_cart}{/if}">{$lang.shc_checkout}</a>
				</td>
			</tr>
		</table>
	</div>
{else}
	<div class="info">{$lang.shc_empty_cart}</div>
{/if}