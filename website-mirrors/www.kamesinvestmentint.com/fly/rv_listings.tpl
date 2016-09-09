<!-- recently viewed listings tpl -->

{if $smarty.const.RL_MOBILE === true}
	{if !empty($listings)}
		<!-- listings -->
		<div id="listings">
			<ul>
				{foreach from=$listings item='listing' key='key' name='listingsF'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'listing.tpl'}
				{/foreach}
			</ul>
		</div>
		<!-- listings end -->
		
		<!-- paging block -->
		{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.rv_count_per_page}
		<!-- paging block end -->

		<div class="rv_del_listings">
			<a class="button" href="javascript: void(0);">{$lang.rv_del_listings}</a>
		</div>
	{else}
		<div class="padding">
			{if $isLogin}{$lang.rv_no_listings}{else}{$lang.loading}{/if}
		</div>
	{/if}
{else}
	{if !empty($listings)}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid.tpl' periods=true}

		<!-- paging block -->
		{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.rv_count_per_page}
		<!-- paging block end -->

		<div class="rv_del_listings">
			<a class="button" href="javascript: void(0);">{$lang.rv_del_listings}</a>
		</div>
	{else}
		<div class="info">
			{if $isLogin}{$lang.rv_no_listings}{else}{$lang.loading}{/if}
		</div>
	{/if}
{/if}

<script type="text/javascript">
var notice = '{$lang.notice}';
var rv_del_listing_notice = '{$lang.rv_del_listing_notice}';
var rv_del_listings_notice = '{$lang.rv_del_listings_notice}';
var storage_item_name = '{$rlBase|replace:"http://":""|replace:"htts://":""|replace:"www.":""|replace:".":"_"|replace:"/":"_"}';

{if !$isLogin}
{literal}
	var rv_storage = rvGetListings();
	var rv_listings = [];

	if ( rv_storage ) {
		for (var i = rv_storage.length - 1; i >= 0; i--) {
			rv_listings.unshift(rv_storage[i][0]);
		};
	}

	if ( rv_listings ) {
		rv_listings = rv_listings.join(',');
		xajax_loadRvListings( rv_listings );
	}
{/literal}
{else}
	{if $inactive_listings}
		localStorage.setItem('rv_listings_' + storage_item_name, JSON.stringify({$st_listings}));
	{/if}
{/if}
</script>

<!-- recently viewed listings tpl end -->
