{assign var='compare_cookie_ids' value=','|explode:$smarty.cookies.compare_listings}
<span class="compare-icon{if $listing_data.ID|in_array:$compare_cookie_ids} remove{/if}" title="{if $listing_data.ID|in_array:$compare_cookie_ids}{$lang.compare_remove_from_compare}{else}{$lang.compare_add_to_compare}{/if}" accesskey="{$listing_data.ID}">
	<span></span>

	<script type="text/javascript">
	{literal}

	$(document).ready(function(){
		$('span.compare-icon').unbind('click').click(function(){
			flCompare.action($(this), true);
		});
	});

	{/literal}
	</script>
</span>