<!-- Shopping cart -->

{if $listing.shc_auction_status == 'closed' && $listing.shc_auction_won == ''}
	<a title="{$lang.shc_renew_auction}" class="nav_icon text_button renew-auction" href="javascript: void(0);" id="{$listing.ID}-renew_auction">
		<span class="left">&nbsp;</span><span class="center">{$lang.shc_renew_auction}</span><span class="right">&nbsp;</span>
	</a>
{/if}

<!-- end Shopping cart -->