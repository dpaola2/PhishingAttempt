<table class="list">
	<tr class="header">
		<td align="center" class="no_padding" style="width: 90px;"></td>
		<td class="divider"></td>
		<td>{$lang.shc_item}</td>
		<td class="divider"></td>
		<td width="60"><div class="text-overflow">{$lang.shc_price}</div></td>
		<td class="divider"></td>
		<td width="100"><div class="text-overflow">{$lang.shc_quantity}</div></td>
		<td class="divider"></td>
		<td width="60"><div class="text-overflow">{$lang.shc_total}</div></td>
		<td class="divider"></td>
		<td width="30"></td>
	</tr>
	{foreach from=$shcItems item='item' name='invoiceF'}
	<tr class="body" id="item_{$item.ID}">
		<td class="photo" valign="top" align="center">
			<a href="{$item.listing_link}" target="_blank">
				<img alt="{$item.title}" style="width: 70px;" src="{if empty($item.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$item.Main_photo}{/if}" />
			</a>
		</td>
		<td class="divider"></td>
		<td class="text-overflow">
			<a href="{$item.listing_link}" target="_blank">{$item.Item}</a>
		</td>
		<td class="divider"></td>
		<td style="white-space: nowrap;">{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.Price|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</td>
		<td class="divider"></td>
		<td align="center">
			<span class="increase" title="{$lang.shc_increase}">+</span>
			<input accesskey="{$item.Price}" type="text" class="numeric w30 quantity" name="quantity[{$item.ID}]" id="quantity_{$item.ID}" value="{$item.Quantity}" style="text-align: center;" maxlength="4" />
			<span class="decrease" title="{$lang.shc_decrease}">-</span>
		</td>
		<td class="divider"></td>
		<td style="white-space: nowrap;" align="center">{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="price_{$item.ID}">{$item.total|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</td>
		<td class="divider"></td>
		<td align="center">
			<img class="del shc_delete_item" id="delete_{$item.ID}_{$item.Item_ID}" alt="{$lang.delete}" title="{$lang.delete}" src="{$rlTplBase}img/blank.gif" />
		</td>
	</tr>
	{/foreach}

	<!-- Shipping -->
	{if $shipping}
	<tr class="body">
		<td style="text-align: right" colspan="8">
			{$lang.shc_shipping_price}		
		</td>	
		<td class="shc_price">	
			<div>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$shcDelivery|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</div>
		</td>
		<td class="divider"></td>
		<td>&nbsp;</td>
	</tr>
	{/if}

	<!-- Total -->
	<tr>
		<td style="text-align: right" colspan="8" class="shc_value">
			{$lang.shc_total_cost}		
		</td>	
		<td class="shc_price shc_value">	
			<div>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="total_{$shcDealer}">{$shcTotal|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</div>
		</td>          
		<td class="divider"></td>
		<td>&nbsp;</td>
	</tr>
</table>

<input type="hidden" name="form" value="submit" />
<input type="hidden" name="dealer" value="{$item.Dealer_ID}" />

<div align="right" style="padding: 10px 0px 0px 0px;">
	<input type="submit" value="{$lang.shc_proceed_checkout}" />	
</div>