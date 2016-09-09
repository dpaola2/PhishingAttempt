<!-- comments DOM -->

{if $comments}
	<ul class="comments">
	{foreach from=$comments item='comment'}
		<li>
			<div class="hlight hborder">
				<h3>
					{$comment.Title}
					{if $config.comments_rating_module}{section name='stars' start=1 loop=$comment.Rating+1}<span class="comment_star_small"></span>{/section}{/if}
				</h3>
				{$comment.Description|nl2br}
			</div>
			<span>
				<span class="dark"><b>
					{if $comment.Own_address}
						<a class="static" alt="{$comment.Author}" title="{$comment.Author}" href="{$smarty.const.SEO_BASE}{$comment.Own_address}">{$comment.Author}</a>
					{else}
						<span class="dark"><b>{$comment.Author}</b></span>
					{/if}
				</b></span> / {$comment.Date|date_format:$smarty.const.RL_DATE_FORMAT}{if $config.comment_show_time} {$comment.Date|date_format:'%H:%M'}{/if}
			</span>
		</li>
	{/foreach}
	</ul>

	{assign var='tpl_name' value='comment_paging.tpl'}
	{if $tpl_settings.type == 'responsive_42'}
		{assign var='tpl_name' value='paging_responsive_42.tpl'}
	{/if}
	{include file=$smarty.const.RL_PLUGINS|cat:$smarty.const.RL_DS|cat:'comment'|cat:$smarty.const.RL_DS|cat:$tpl_name}
{else}
	<div class="info text-notice">{$lang.comment_absent}</div>
{/if}

<!-- comments DOM end -->
