<!-- shoppingCart plugin -->

<div id="area_shoppingCart" class="tab_area hide">
	<div class="bid-history-header">	
		{if $listing_data.shc.time_left_value > 0 && $listing_data.shc_auction_status != 'closed'}
			{$lang.shc_bidders}: <span id="bh_bidders">{$listing_data.shc.bidders}</span>
			{$lang.shc_bids}: <span id="bh_total_bids">{$listing_data.shc.total_bids}</span>
		{/if}
	</div>
	<div id="bid-history-list">
		{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'bids_responsive_42.tpl'}
	</div>
</div>

<!-- end shoppingCart plugin -->