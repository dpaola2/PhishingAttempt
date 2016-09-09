<!-- forum new posts -->
<div style="word-wrap: break-word;">
	{if $vbulletinPosts}
		<ul class="posts_block">
		{foreach from=$vbulletinPosts item='vbPost' name='commentF'}
			<li {if !$smarty.foreach.commentF.last}style="margin-bottom: 10px;"{/if}>
				<a title="{$vbPost.title}" {if $config.view_details_new_window}target="_blank"{/if} href="{$vbPost.url}">{$vbPost.title}</a>
				<div class="dark">{$vbPost.message}</div>
				<div class="ralign"></div>
			</li>
		{/foreach}
		</ul>
	{else}
		<div class="info">{$lang.vbulletin_absentPostsInForum}</div>
	{/if}
</div>
<!-- forum new posts end -->