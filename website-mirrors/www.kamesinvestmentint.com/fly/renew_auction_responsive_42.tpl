<!-- shoppingCart plugin -->

{if $listing.shc_auction_status == 'closed' && $listing.shc_auction_won == ''}
	<li class="nav-icon">
		<a class="renew-auction" {$lang.shc_renew_auction} href="javascript: void(0);" id="{$listing.ID}-renew_auction">
			<span>{$lang.shc_renew_auction}</span>&nbsp;
		</a>
	</li>
{/if}

<!-- end shoppingCart plugin -->