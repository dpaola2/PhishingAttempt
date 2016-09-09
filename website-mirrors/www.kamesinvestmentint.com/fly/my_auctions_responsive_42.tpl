<!-- my bids/offers | shopping cart -->

<div class="content-padding">
	{if !empty($auction_info)}

		{if $auction_mod == 'live'}
			{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'auction_details_live_responsive_42.tpl' auction_info=$auction_info}
		{else}
			{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'auction_details_responsive_42.tpl' auction_info=$auction_info}
		{/if}

	{elseif !isset($smarty.get.item)}

		<!-- tabs -->
		<ul class="tabs">
			{foreach from=$tabs item='tab' name='tabF'}{strip}
				<li {if ($smarty.foreach.tabF.first && !$auction_mod) || $auction_mod == $tab.key}class="active"{/if} id="tab_{$tab.key}">{$tab.name}</li>
			{/strip}{/foreach}
		</ul>
		<!-- tabs end -->

		{assign var='date_format_value' value=$smarty.const.RL_DATE_FORMAT|cat:"<br />"|cat:$config.shc_time_format}

		{if $auctions}
			<div class="list-table row-align-middle">
				<div class="header">
					<div class="center" style="width: 40px;">#</div>
					<div>{$lang.shc_item}</div>
					<div style="width: 100px;">{if $auction_mod}{$lang.shc_your_bid_total}{else}{$lang.shc_total}{/if}</div>
					<div style="width: {if $auction_mod}40{else}120{/if}px;">{if $auction_mod}{$lang.shc_bids}{else}{$lang.shc_txn_id}{/if}</div>
					<div style="width: 100px;">{if $auction_mod == 'live' || !$auction_mod}{$lang.date}{else}{$lang.shc_auction_date_close}{/if}</div>
					<div style="width: 90px;">{if $auction_mod}{$lang.shc_current_bid}{else}{$lang.status}{/if}</div>
					{if $auction_mod == 'live' || !$auction_mod}
						<div style="width: 80px;">{$lang.actions}</div>
					{/if}
				</div>

				{foreach from=$auctions item='item' name='auctionF'}
					{math assign='iteration' equation='(((current?current:1)-1)*per_page)+iter' iter=$smarty.foreach.auctionF.iteration current=$pInfo.current per_page=$config.shc_orders_per_page}

					<div class="row">
						<div class="center iteration no-flex">{$iteration}</div>
						<div data-caption="{$lang.shc_item}" class="img-row">
							{if $auction_mod}
								<a href="{$item.listing_link}" target="_blank">
										{if $item.Main_photo}<img alt="{$item.listing_title}" src="{$rlTplBase}img/blank.gif" style="background-image: url({$smarty.const.RL_FILES_URL}{$item.Main_photo});" />{/if}{$item.listing_title}
								</a>
							{else}
								<a href="{$item.item_details.listing_link}" target="_blank">
									{if $item.item_details.Main_photo}<img alt="{$item.item_details.listing_title}" src="{$rlTplBase}img/blank.gif" style="background-image: url({$smarty.const.RL_FILES_URL}{$item.item_details.Main_photo});" />{/if}{$item.item_details.listing_title}
								</a>
							{/if}
						</div>
						<div data-caption="{if $auction_mod}{$lang.shc_your_bid_total}{else}{$lang.shc_total}{/if}">
							<span class="{if $auction_mod && $auction_mod == 'live'}{if $item.my_total_price < $item.total}behind{else}ahead{/if}{/if}">
								{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
								{if $auction_mod}{$item.my_total_price|number_format:2:'.':','}{else}{$item.Total|number_format:2:'.':','}{/if}
								{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
							</span>
						</div>
						<div data-caption="{if $auction_mod}{$lang.shc_bids}{else}{$lang.shc_txn_id}{/if}">{if $auction_mod}{$item.total_bids}{else}{$item.Txn_ID}{/if}</div>
						<div data-caption="{if $auction_mod == 'live' || !$auction_mod}{$lang.date}{else}{$lang.shc_auction_date_close}{/if}">
							{if $auction_mod}
								{if $auction_mod == 'live'}
									{$item.last_date_bid|date_format:$date_format_value}
								{else}
									{$item.shc_end_time|date_format:$date_format_value}	
								{/if}
							{else}
								{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}
							{/if}
						</div>
						<div data-caption="{if $auction_mod}{$lang.shc_current_bid}{else}{$lang.status}{/if}">
							{if $auction_mod}
								<span class="price-cell">
									{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
									{$item.total|number_format:2:'.':','}
									{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
								</span>
							{else}
								{assign var='pStatus' value='shc_'|cat:$item.pStatus}
								<span class="item_{$item.pStatus}">{$lang[$pStatus]}</span>
							{/if}
						</div>
						{if $auction_mod == 'live' || !$auction_mod}
							<div data-caption="{$lang.actions}" class="align-center">
								{if $auction_mod}
									<a title="{$lang.view_details}" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auctions}/{$auction_mod}.html?item={$item.ID}{else}?page={$pages.shc_auctions}&amp;module={$auction_mod}&amp;item={$item.ID}{/if}">
										{$lang.shc_details}
									</a>
								{else}
									{if $item.pStatus == 'unpaid'}
										<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}.html?item={$item.ID}{else}?page={$pages.shc_auction_payment}&amp;item={$item.ID}{/if}">
											{$lang.shc_checkout}
										</a>
										<div class="align-center">{$lang.or}</div>
									{/if}
									<a title="{$lang.view_details}" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auctions}.html?item={$item.ID}{else}?page={$pages.shc_auctions}&amp;item={$item.ID}{/if}">
										{$lang.shc_details}
									</a>
								{/if}
							</div>
						{/if}
					</div>
				{/foreach}
			</div>

			{paging calc=$pInfo.calc total=$auctions|@count current=$pInfo.current per_page=$config.shc_orders_per_page}
		{else}
			<div class="text-notice">{$lang.shc_no_auctions}</div>
		{/if}
	{/if}
</div>

<script>
{literal}

$(document).ready(function(){
	$('ul.tabs li').click(function(){
		if ( $(this).attr('id') == 'tab_winnerbids' ) {
			location.href = '{/literal}{$rlBase}{if $config.mod_rewrite}{$pages.shc_auctions}.html{else}?page={$pages.shc_auctions}{/if}{literal}';
		}
		else {
			var auction_mod = $(this).attr('id').split('_')[1];
			location.href = '{/literal}{$rlBase}{if $config.mod_rewrite}{$pages.shc_auctions}/' + auction_mod + '.html{else}?page={$pages.shc_auctions}&amp;module=' + auction_mod + '{/if}{literal}';
		}
	});
});

{/literal}
</script>

<!-- my bids/offers end | shopping cart -->