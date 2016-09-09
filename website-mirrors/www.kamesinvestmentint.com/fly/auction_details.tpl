<!-- Shopping Cart Plugin -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_item_details' name=$lang.shc_item_details}

<div class="auction-item-details">
	<div class="preview">
		<a href="{$auction_info.listing_link}" title="{$auction_info.title}">
			<img alt="" src="{if empty($auction_info.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$auction_info.Main_photo}{/if}" />
		</a>
	</div>
	<div class="details">
		<table class="table">
			<tr>
				<td class="name">{$lang.shc_item}:</td>
				<td class="value"><b>{$auction_info.title}</b></td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_auction_status}:</td> 	
				{assign var='shc_auction_status' value='shc_'|cat:$auction_info.item_details.shc_auction_status}
				<td class="value">{$lang[$shc_auction_status]}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_total_cost}:</td>
				<td class="value"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="total">{$auction_info.Total|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
			</tr>
			<tr>      
				<td class="name">{$lang.shc_payment_status}:</td> 
				<td class="value">
					{assign var='pStatus' value='shc_'|cat:$auction_info.pStatus}
					<span class="item_{$auction_info.pStatus}">{$lang[$pStatus]}</span>
				</td>
			</tr>
		</table>
	</div>
</div>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_order_details' name=$lang.shc_order_details}

	<table class="table">
		<tr>
			<td class="name">{$lang.shc_txn_id}:</td>
			<td class="value">{$auction_info.Txn_ID}</td>
		</tr>
		{if $atype == 'buyer'}
		<tr>
			<td class="name">{$lang.shc_buyer}:</td>
			<td class="value">
				{if $auction_info.bOwn_address}
					<a target="_blank" href="{$rlBase}{$auction_info.bUsername}/">{$auction_info.bUsername}</a>
				{else}
					{$auction_info.bUsername}
				{/if}
			</td>
		</tr>
		{else}
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
		{/if}
		{if $auction_info.pStatus == 'paid'}
			<tr>
				<td class="name">{$lang.shc_gateway}:</td>
				<td class="value">{$auction_info.Gateway}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_pay_date}:</td>
				<td class="value">{$auction_info.Pay_date|date_format:$smarty.const.RL_DATE_FORMAT}</td>
			</tr>
		{/if}
	</table>
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{if $auction_info.Shipping_method}
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_shipping_details' name=$lang.shc_shipping_details}

		<table class="table">
			<tr>
				<td class="name">{$lang.shc_shipping_method}:</td>
				<td class="value">{$auction_info.Shipping_method}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_shipping_price}:</td>
				<td class="value"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="total">{$auction_info.Shipping_price|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_country}:</td>
				<td class="value">{if !empty($auction_info.Country)}{$auction_info.Country}{else} - {/if}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_zip}:</td>
				<td class="value">{if !empty($auction_info.Zip_code)}{$auction_info.Zip_code}{else} - {/if}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_city}:</td>
				<td class="value">{if !empty($auction_info.City)}{$auction_info.City}{else} - {/if}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_address}:</td>
				<td class="value">{if !empty($auction_info.Address)}{$auction_info.Address}{else} - {/if}</td>
			</tr>	
			<tr>
				<td class="name">{$lang.shc_phone}:</td>
				<td class="value">{if !empty($auction_info.Phone)}{$auction_info.Phone}{else} - {/if}</td>
			</tr>	
			<tr>
				<td class="name">{$lang.your_email}:</td>
				<td class="value">{if !empty($auction_info.Mail)}{$auction_info.Mail}{else} - {/if}</td>
			</tr>	
			<tr>
				<td class="name">{$lang.shc_vat_no}:</td>
				<td class="value">{if !empty($auction_info.Vat_no)}{$auction_info.Vat_no}{else} - {/if}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_comment}:</td>
				<td class="value">{if !empty($order_info.Comment)}<i>{$order_info.Comment}</i>{else} - {/if}</td>
			</tr>					
		</table>

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
{/if}

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