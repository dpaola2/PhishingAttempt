<!-- twitter box -->

{if $config.bookmarks_twitter_box_widgetid && $config.bookmarks_twitter_box_username}

{strip}
<a class="twitter-timeline" href="https://twitter.com/{$config.bookmarks_twitter_box_username}"
	data-widget-id="{$config.bookmarks_twitter_box_widgetid}">
		{$lang.bookmarks_twitter_tweets_by} @{$config.bookmarks_twitter_box_username}
</a>
{/strip}

<script type="text/javascript">
{literal}
	!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';
	if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";
	fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
{/literal}
</script>

{else}
	{$lang.bookmarks_twitter_box_deny}
{/if}
<!-- twitter box end -->