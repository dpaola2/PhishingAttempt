<!-- Shopping Cart Plugin -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_item_details' name=$lang.shc_item_details}

<div class="auction-item-details">
	{if $auction_info.Main_photo}
		<div class="preview" style="padding-bottom: 20px;">
			<a href="{$auction_info.listing_link}" title="{$auction_info.title}" target="_blank">
				<img alt="" src="{$smarty.const.RL_FILES_URL}{$auction_info.Main_photo}" />
			</a>
		</div>
	{/if}

	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_item}</span></div></div>
		<div class="value"><a href="{$auction_info.listing_link}" target="_blank">{$auction_info.title}</a></div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_time_left}</span></div></div>
		<div class="value">{$auction_info.time_left}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_bids}</span></div></div>
		<div class="value">{$auction_info.total_bids}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_your_bid_total}</span></div></div>
		<div class="value">
			<span class="price-cell">
				{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
				{$auction_info.my_total_price|number_format:2:'.':','}
				{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
			</span>
		</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_current_bid}</span></div></div>
		<div class="value">
			{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
			{$auction_info.total|number_format:2:'.':','}
			{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}

			{if $auction_info.shc_reserved_price > $auction_info.total}		
				<span class="behind"> ({$lang.shc_reserve_not_met})</span>
			{else}
				<span class="ahead"> ({$lang.shc_reserve_met})</span>
			{/if}
		</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_bids}</span></div></div>
		<div class="value">
			{assign var='date_format_value' value=$smarty.const.RL_DATE_FORMAT|cat:"&nbsp;&nbsp;"|cat:$config.shc_time_format}
			{$auction_info.last_date_bid|date_format:$date_format_value}
		</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_dealer}</span></div></div>
		<div class="value">
			{if $auction_info.dOwn_address}
				<a target="_blank" href="{$rlBase}{$auction_info.dOwn_address}/">{$auction_info.dUsername}</a>
			{else}
				{$auction_info.dUsername}
			{/if}
		</div>
	</div>
</div>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

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