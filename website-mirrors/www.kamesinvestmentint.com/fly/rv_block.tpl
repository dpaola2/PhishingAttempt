<!-- recently viewed listings tpl -->

{assign var='allowed_pages' value="home, listing_type, recently_added, listing_details"}

{if $allowed_pages|strpos:$pageInfo.Controller !== false}
	<script type="text/javascript">
	var rv_no_listings = '{$lang.rv_no_listings}';
	var rv_lang_listings = '{$lang.rv_listings}';
	var rv_lang_history = '{$lang.rv_history_link}';
	var rv_history_link = '{$rlBase}{if $config.mod_rewrite}{$pages.rv_listings}.html{else}?page={$pages.rv_listings}{/if}';
	var storage_item_name = '{$rlBase|replace:"http://":""|replace:"htts://":""|replace:"www.":""|replace:".":"_"|replace:"/":"_"}';

	{literal}
		$(document).ready(function(){
			if ( !logged || (logged && sync_rv_complete) )
				loadRvListingsToBlock();
		});
	{/literal}
	</script>

	<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}recentlyViewed/static/lib.js"></script>
{/if}

<!-- recently viewed listings tpl end -->
