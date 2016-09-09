{if $bids}
	{assign var='date_format_value' value=$smarty.const.RL_DATE_FORMAT|cat:"<br />"|cat:$config.shc_time_format}

	<div class="content-padding">
		<div class="list-table">
			<div class="header">
				<div class="center" style="width: 40px;">#</div>
				<div>{$lang.shc_bidder}</div>
				<div style="width: 120px;">{$lang.shc_bid_amount}</div>
				<div style="width: 150px;">{$lang.shc_bid_time}</div>
			</div>

			{foreach from=$bids item='item' name='bidF'}
				<div class="row">
					<div class="center iteration no-flex">{$smarty.foreach.bidF.iteration}</div>
					<div data-caption="{$lang.shc_bidder}" class="nr">{$item.bidder}</div>
					<div data-caption="{$lang.shc_bid_amount}">
						{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
						{$item.Total|number_format:2:'.':','}
						{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
					</div>
					<div data-caption="{$lang.shc_bid_time}">{$item.Date|date_format:$date_format_value}</div>
				</div>
			{/foreach}
		</div>
	</div>
{else}
	<div class="text-notice">{$lang.shc_no_bids}</div>
{/if}