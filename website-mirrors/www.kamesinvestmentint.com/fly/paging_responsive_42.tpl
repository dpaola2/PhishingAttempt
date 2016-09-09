<!-- commetns pagination tpl | responsive 42 -->

{if $comment_calc > $config.comments_per_page}
	{math assign='pages' equation='ceil(calc/per_page)' calc=$comment_calc per_page=$config.comments_per_page}
	{if $comment_page == 0}
		{assign var='comment_page' value=1}
	{/if}

	<ul class="pagination" id="comment_paging">
		{if $comment_page > 1}
			<li title="{$lang.prev_page}" class="navigator ls">
				<a accesskey="{$comment_page-1}" class="button" href="javascript://">&lsaquo;</a>
			</li>
		{/if}

		<li class="transit"><span>{$lang.page} {$comment_page} {$lang.of} {$pages}</span></li>

		{if $comment_page != $pages}
			<li title="{$lang.next_page}" class="navigator rs">
				<a accesskey="{$comment_page+1}" class="button" href="javascript://">&rsaquo;</a>
			</li>
		{/if}
	</ul>
{/if}

<!-- commetns pagination tpl end -->