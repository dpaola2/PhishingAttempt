<!-- Shopping Cart Plugin -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_item_details' name=$lang.shc_item_details}

<div class="auction-item-details">
	{if $auction_info.Main_photo}
		<div class="preview" style="padding-bottom: 20px;">
			<img alt="" src="{$smarty.const.RL_FILES_URL}{$auction_info.Main_photo}" />
		</div>
	{/if}

	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_item}</span></div></div>
		<div class="value">{$auction_info.title}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_txn_id}</span></div></div>
		<div class="value">{$auction_info.Txn_ID}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_auction_status}</span></div></div>
		<div class="value">
			{assign var='shc_auction_status' value='shc_'|cat:$auction_info.item_details.shc_auction_status}
			{$lang[$shc_auction_status]}
		</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_total_cost}</span></div></div>
		<div class="value">
			{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
			{$auction_info.Total|number_format:2:'.':','}
			{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
		</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_payment_status}</span></div></div>
		<div class="value">
			{assign var='pStatus' value='shc_'|cat:$auction_info.pStatus}
			<span class="item_{$auction_info.pStatus}">{$lang[$pStatus]}</span>
		</div>
	</div>
</div>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_order_details' name=$lang.shc_order_details}

	<div class="table-cell">
		{if $atype == 'buyer'}
			<div class="name"><div><span>{$lang.shc_buyer}</span></div></div>
			<div class="value">
				{if $auction_info.bOwn_address}
					<a target="_blank" href="{$rlBase}{$auction_info.bUsername}/">{$auction_info.bUsername}</a>
				{else}
					{$auction_info.bUsername}
				{/if}
			</div>
		{else}
			<div class="name"><div><span>{$lang.shc_dealer}</span></div></div>
			<div class="value">
				{if $auction_info.dOwn_address}
					<a target="_blank" href="{$rlBase}{$auction_info.dOwn_address}/">{$auction_info.dUsername}</a>
				{else}
					{$auction_info.dUsername}
				{/if}
			</div>
		{/if}
	</div>

	{if $auction_info.pStatus == 'paid'}
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_gateway}</span></div></div>
			<div class="value">{$auction_info.Gateway}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_pay_date}</span></div></div>
			<div class="value">{$auction_info.Pay_date|date_format:$smarty.const.RL_DATE_FORMAT}</div>
		</div>
	{/if}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{if $auction_info.Shipping_method}
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_shipping_details' name=$lang.shc_shipping_details}

		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_shipping_method}</span></div></div>
			<div class="value">{$auction_info.Shipping_method}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_shipping_price}</span></div></div>
			<div class="value">
				{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
				{$auction_info.Shipping_price|number_format:2:'.':','}
				{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
			</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_country}</span></div></div>
			<div class="value">{if !empty($auction_info.Country)}{$auction_info.Country}{else} - {/if}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_zip}</span></div></div>
			<div class="value">{if !empty($auction_info.Zip_code)}{$auction_info.Zip_code}{else} - {/if}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_city}</span></div></div>
			<div class="value">{if !empty($auction_info.City)}{$auction_info.City}{else} - {/if}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_address}</span></div></div>
			<div class="value">{if !empty($auction_info.Address)}{$auction_info.Address}{else} - {/if}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_phone}</span></div></div>
			<div class="value">{if !empty($auction_info.Phone)}{$auction_info.Phone}{else} - {/if}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.your_email}</span></div></div>
			<div class="value">{if !empty($auction_info.Mail)}{$auction_info.Mail}{else} - {/if}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_vat_no}</span></div></div>
			<div class="value">{if !empty($auction_info.Vat_no)}{$auction_info.Vat_no}{else} - {/if}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_comment}</span></div></div>
			<div class="value">{if !empty($order_info.Comment)}<i>{$order_info.Comment}</i>{else} - {/if}</div>
		</div>

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
{/if}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_my_bids' name=$lang.shc_my_bids}

	{if $auction_info.bids}
		{assign var='date_format_value' value=$smarty.const.RL_DATE_FORMAT|cat:"&nbsp;&nbsp;"|cat:$config.shc_time_format}
		
		<div class="list-table">
			<div class="header">
				<div class="center" style="width: 40px;">#</div>
				<div>{$lang.shc_bid_amount}</div>
				<div style="width: 150px;">{$lang.shc_bid_time}</div>
			</div>

			{foreach from=$auction_info.bids item='item' name='bidF'}
			<div class="row">
				<div class="center iteration no-flex">{$smarty.foreach.bidF.iteration}</div>
				<div data-caption="{$lang.shc_bid_amount}">
					{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
					{$item.Total|number_format:2:'.':','}
					{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
				</div>
				<div data-caption="{$lang.shc_bid_time}">{$item.Date|date_format:$date_format_value}</div>
			</div>
			{/foreach}
		</div>
	{else}
		<div class="text-notice">{$lang.shc_no_bids}</div>
	{/if}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

<!-- end Shopping Cart Plugin -->