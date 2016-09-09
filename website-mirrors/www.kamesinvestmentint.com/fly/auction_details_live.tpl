<!-- Shopping Cart Plugin -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_item_details' name=$lang.shc_item_details}

<div class="auction-item-details">
	<div class="preview">
		<a href="{$auction_info.listing_link}" title="{$auction_info.title}" target="_blank">
			<img alt="" src="{if empty($auction_info.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$auction_info.Main_photo}{/if}" />
		</a>
	</div>
	<div class="details">
		<table class="table">
			<tr>
				<td class="name">{$lang.shc_item}:</td>
				<td class="value"><a href="{$auction_info.listing_link}" target="_blank"><b>{$auction_info.title}</b></a></td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_time_left}:</td>
				<td class="value">{$auction_info.time_left}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_bids}:</td>
				<td class="value">{$auction_info.total_bids}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_your_bid_total}:</td>
				<td class="value"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="total">{$auction_info.my_total_price|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_current_bid}:</td>
				<td class="value">
					<b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="total">{$auction_info.total|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b>
					{if $auction_info.shc_reserved_price > $auction_info.total}		
						<span class="reserve_not_met"> ({$lang.shc_reserve_not_met})</span>
					{else}
						<span class="reserve_met"> ({$lang.shc_reserve_met})</span>
					{/if}
				</td>			
			</tr>
			<tr>
				<td class="name">{$lang.shc_bids}:</td>
				{assign var='date_format_value' value=$smarty.const.RL_DATE_FORMAT|cat:"&nbsp;&nbsp;"|cat:$config.shc_time_format}
				<td class="value">{$auction_info.last_date_bid|date_format:$date_format_value}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_dealer}:</td>
				<td class="value">
					{if $auction_info.dOwn_address}
						<a target="_blank" href="{$rlBase}{$auction_info.dOwn_address}/">{$auction_info.dUsername}</a>
					{else}
			   			{$auction_info.dUsername}		
					{/if}
				</td>
			</tr>
		</table>
	</div>
</div>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_my_bids' name=$lang.shc_my_bids}

	{if $auction_info.bids}
		{assign var='date_format_value' value=$smarty.const.RL_DATE_FORMAT|cat:"&nbsp;&nbsp;"|cat:$config.shc_time_format}
		<div class="highlight">		
			<table class="list">
				<tr class="header">
					<td align="center" class="no_padding" style="width: 15px;">#</td>
					<td class="divider"></td>
					<td><div title="{$lang.amount}" class="text-overflow">{$lang.shc_bid_amount}</div></td>
					<td class="divider"></td>
					<td width="20%"><div title="{$lang.payment_gateway}" class="text-overflow">{$lang.shc_bid_time}</div></td>
				</tr>
				{foreach from=$auction_info.bids item='item' name='bidF'}
				<tr class="body" id="item_{$item.ID}">
					<td class="no_padding" align="center"><span class="text">{$smarty.foreach.bidF.iteration}</span></td>
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

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

<!-- end Shopping Cart Plugin -->