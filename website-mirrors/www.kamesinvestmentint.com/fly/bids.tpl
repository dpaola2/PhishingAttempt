{if $bids}
	{assign var='date_format_value' value=$smarty.const.RL_DATE_FORMAT|cat:"&nbsp;&nbsp;"|cat:$config.shc_time_format}
	<div class="highlight">		
		<table class="list" id="saved_search">
			<tr class="header">
				<td align="center" class="no_padding" style="width: 15px;">#</td>
				<td class="divider"></td>
				<td>{$lang.shc_bidder}</td>
				<td class="divider"></td>
				<td width="15%"><div title="{$lang.shc_bid_amount}" class="text-overflow">{$lang.shc_bid_amount}</div></td>
				<td class="divider"></td>
				<td width="20%"><div title="{$lang.shc_bid_time}" class="text-overflow">{$lang.shc_bid_time}</div></td>
			</tr>
			{foreach from=$bids item='item' name='bidF'}
			<tr class="body" id="item_{$item.ID}">
				<td class="no_padding" align="center"><span class="text">{$smarty.foreach.bidF.iteration}</span></td>
				<td class="divider"></td>
				<td><span class="text">{$item.bidder}</span></td>
				<td class="divider"></td>
				<td>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.Total|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</td>
				<td class="divider"></td>
				<td><span class="text">{$item.Date|date_format:$date_format_value}</span></td>
			</tr>
			{/foreach}
		</table>
	</div>
{else}
	<div class="info">{$lang.shc_no_bids}</div>
{/if}