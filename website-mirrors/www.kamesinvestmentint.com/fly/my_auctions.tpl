<!-- Shopping Cart Plugin -->

{if $tpl_settings.type == 'responsive_42'}
	
	{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'my_auctions_responsive_42.tpl'}

{else}

<div class="highlight">
	{if !empty($auction_info)}

		{if $auction_mod == 'live'}
			{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'auction_details_live.tpl' auction_info=$auction_info}
		{else}
			{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'auction_details.tpl' auction_info=$auction_info}
		{/if}

	{elseif !isset($smarty.get.item)}

		<!-- tabs -->
		<div class="tabs">
			<ul>
				{foreach from=$tabs item='tab' name='tabF'}
				<li class="{if $auction_mod == $tab.key}active{elseif $smarty.foreach.tabF.first && !$auction_mod}active{/if}{if $smarty.foreach.tabF.first} first{/if}" id="tab_{$tab.key}">
					<span class="center">{$tab.name}</span>
				</li>
				{/foreach}
			</ul>
		</div>
		<div class="clear"></div>
		<!-- tabs end -->
		{assign var='date_format_value' value=$smarty.const.RL_DATE_FORMAT|cat:"&nbsp;&nbsp;"|cat:$config.shc_time_format}

		{if $auctions}
			<table class="list">
				<tr class="header">
					<td align="center" class="no_padding" style="width: 15px;">#</td>
					<td class="divider"></td>
					<td align="center" class="no_padding" style="width: 90px;"></td>
					<td class="divider"></td>
					<td>{$lang.shc_item}</td>
					<td class="divider"></td>

					<td style="width: 80px;"><div title="{if $auction_mod}{$lang.shc_your_bid_total}{else}{$lang.shc_total}{/if}" class="text-overflow">{if $auction_mod}{$lang.shc_your_bid_total}{else}{$lang.shc_total}{/if}</div></td>
					<td class="divider"></td>

					<td style="width: 80px;"><div title="{if $auction_mod}{$lang.shc_bids}{else}{$lang.shc_txn_id}{/if}" class="text-overflow">{if $auction_mod}{$lang.shc_bids}{else}{$lang.shc_txn_id}{/if}</div></td>
					<td class="divider"></td>

					<td style="width: 70px;">{if $auction_mod == 'live' || !$auction_mod}{$lang.date}{else}{$lang.shc_auction_date_close}{/if}</td>
					<td class="divider"></td>

					<td style="width: 80px;"><div title="{if $auction_mod}{$lang.shc_current_bid}{else}{$lang.status}{/if}" class="text-overflow">{if $auction_mod}{$lang.shc_current_bid}{else}{$lang.status}{/if}</div></td>
					{if $auction_mod == 'live' || !$auction_mod}
						<td class="divider"></td>
						<td style="width: 120px;">{$lang.actions}</td>
					{/if}
				</tr>
				{foreach from=$auctions item='item' name='auctionF'}
				<tr class="body" id="item_{$item.ID}">
					<td class="no_padding" align="center"><span class="text">{$smarty.foreach.auctionF.iteration}</span></td>
					<td class="divider"></td>
					<td class="photo" valign="top" align="center">
						{if $auction_mod}
							<a href="{$item.listing_link}" target="_blank">
								<img alt="{$item.listing_title}" style="width: 70px;" src="{if empty($item.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$item.Main_photo}{/if}" />
							</a>
						{else}
							<a href="{$item.item_details.listing_link}" target="_blank">
								<img alt="{$item.item_details.listing_title}" style="width: 70px;" src="{if empty($item.item_details.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$item.item_details.Main_photo}{/if}" />
							</a>
						{/if}
					</td>
					<td class="divider"></td>
					<td class="text-overflow">{strip}
						{if $auction_mod}
							<a href="{$item.listing_link}" target="_blank">{$item.listing_title}</a>
						{else}
							<a href="{$item.item_details.listing_link}" target="_blank">{$item.item_details.listing_title}</a>	
						{/if}
					{/strip}</td>
					<td class="divider"></td>

					{if $auction_mod}
						<td style="white-space: nowrap; {if $auction_mod == 'live'}background: #{if $item.my_total_price < $item.total}FFDEDE{else}D2E2BC{/if};{/if}">{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.my_total_price|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</td>
					{else}
						<td style="white-space: nowrap;"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.Total|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
					{/if}
					<td class="divider"></td>

					<td><span class="text">{if $auction_mod}{$item.total_bids}{else}{$item.Txn_ID}{/if}</span></td>
					<td class="divider"></td>

					<td>{strip}
						<span class="text">
							{if $auction_mod}
								{if $auction_mod == 'live'}
									{$item.last_date_bid|date_format:$date_format_value}
								{else}
									{$item.shc_end_time|date_format:$date_format_value}	
								{/if}
							{else}
								{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}
							{/if}
						</span>
					{/strip}</td>
					<td class="divider"></td>

					<td style="white-space: nowrap;">{strip}
						{if $auction_mod}
							<b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.total|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b>
						{else}
							{assign var='pStatus' value='shc_'|cat:$item.pStatus}
							<span class="item_{$item.pStatus}">{$lang[$pStatus]}</span>
						{/if}
					{/strip}</td>
					
					{if $auction_mod == 'live' || !$auction_mod}
						<td class="divider"></td>
						<td style="text-align: center;">{strip}
							{if $auction_mod}
								<a title="{$lang.view_details}" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auctions}/{$auction_mod}.html?item={$item.ID}{else}?page={$pages.shc_auctions}&amp;module={$auction_mod}&amp;item={$item.ID}{/if}">
									{$lang.shc_details}
								</a>
							{else}
								{if $item.pStatus == 'unpaid'}
									<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}.html?item={$item.ID}{else}?page={$pages.shc_auction_payment}&amp;item={$item.ID}{/if}">
										{$lang.shc_checkout}
									</a>
									&nbsp;|&nbsp;
								{/if}
								<a title="{$lang.view_details}" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auctions}.html?item={$item.ID}{else}?page={$pages.shc_auctions}&amp;item={$item.ID}{/if}">
									{$lang.shc_details}
								</a>
							{/if}
						{/strip}</td>
					{/if}
				</tr>
				{/foreach}
			</table> 

			{paging calc=$pInfo.calc total=$auctions|@count current=$pInfo.current per_page=$config.shc_orders_per_page url=$auction_mod}
		{else}
			<div class="info">{$lang.shc_no_auctions}</div>
		{/if}
	{/if}
</div>

<script type="text/javascript">
	{literal}

	$(document).ready(function(){
		$('div.tabs li').click(function(){
			if($(this).attr('id') == 'tab_winnerbids')
			{
				location.href = '{/literal}{$rlBase}{if $config.mod_rewrite}{$pages.shc_auctions}.html{else}?page={$pages.shc_auctions}{/if}{literal}';
			}
			else
			{
				var auction_mod = $(this).attr('id').split('_')[1];

				location.href = '{/literal}{$rlBase}{if $config.mod_rewrite}{$pages.shc_auctions}/' + auction_mod + '.html{else}?page={$pages.shc_auctions}&amp;module=' + auction_mod + '{/if}{literal}';
			}
		});
	});
	
	{/literal}
</script>

{/if}

<!-- end Shopping Cart Plugin -->