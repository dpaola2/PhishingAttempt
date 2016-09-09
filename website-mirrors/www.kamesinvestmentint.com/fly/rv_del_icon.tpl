<!-- remove viewed listing tpl -->

{if $tpl_settings.type == 'responsive_42'}
	<li id="rv_{$listing.ID}" class="rv_remove responsive" title="{$lang.rv_del_listing}"><span class="icon delete"></span><span class="link">{$lang.rv_del_listing}</span></li>
{else}
	<a class="icon rv_remove" href="javascript:void(0)" title="{$lang.rv_del_listing}" id="rv_{$listing.ID}">
		<span><img class="del" src="{$rlTplBase}img/blank.gif" alt="" title=""></span>
	</a>
{/if}

<!-- remove viewed listing tpl end -->
