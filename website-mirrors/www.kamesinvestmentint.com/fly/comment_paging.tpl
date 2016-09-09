
{if $comment_calc > $config.comments_per_page}
	{math assign='pages' equation='ceil(calc/per_page)' calc=$comment_calc per_page=$config.comments_per_page}
	
	<ul class="paging" id="comment_paging">
	{section name='pages' start=0 loop=$pages}
		{if ($comment_page && $comment_page == $smarty.section.pages.iteration) || (!$comment_page && $smarty.section.pages.first)}
			<li class="active">{$smarty.section.pages.iteration}</li>
		{else}
			<li><a title="{$lang.page} #{$smarty.section.pages.iteration}" href="javascript:void(0)">{$smarty.section.pages.iteration}</a></li>
		{/if}
	{/section}
	</ul>
{/if}
