<!-- shoppingCart plugin --> 

<div id="area_shoppingCart" class="tab_area hide">
	<div class="bid-history-header">	
		{if $listing_data.shc.time_left_value > 0 && $listing_data.shc_auction_status != 'closed'}
			{$lang.shc_bidders}: <span id="bh_bidders">{$listing_data.shc.bidders}</span>
			{$lang.shc_bids}: <span id="bh_total_bids">{$listing_data.shc.total_bids}</span>
			{$lang.shc_time_left}: <span>{$listing_data.shc.time_left}</span>
			{$lang.shc_duration}: <span>{$listing_data.shc_days} {$lang.shc_days}</span>
			
			<div style="float: right;"><a class="button" href="javascript: void(0);" onclick="$('#tab_listing').trigger('click'); $('#rate_bid').focus();">{$lang.shc_add_bid}</a></div>
		{else}
			{$lang.shc_auction_closed}
		{/if}
	</div>
	<div id="bid-history-list">
		{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'bids.tpl'}
	</div>
</div>

<!-- end shoppingCart plugin --> 