<!-- banners box -->

<div class="banners-box {if $boxBetweenCategories}item{/if}">
{if $banners}
	{assign var="bannerBoxWithFadeEffect" value=false}
	{if $info.slider && $banners|@count >= 1}
		{assign var="bannerBoxWithFadeEffect" value=true}
	{/if}

	{if $bannerBoxWithFadeEffect}
		<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}banners/static/jquery.cycle.js"></script>
		<script type="text/javascript">
		{literal}
		$(document).ready(function() {
			$('#slideshow{/literal}{$block.ID}{literal}').cycle({
				fx: 'fade' // choose your transition type, ex: fade, scrollUp, shuffle, etc...						
			});
		});
		{/literal}
		</script>
		<div id="slideshow{$block.ID}" style="width:{$info.width}px; height:{$info.height}px; overflow: hidden; margin:auto;">
	{/if}

	{foreach from=$banners item='banner' name='bannerF'}
		{if $banner.Type == 'image'}
		<div class="banner" id="banner_{$banner.ID}" onclick="xajax_bannerClick({$banner.ID});" style="margin:auto; width:{$info.width}px; height:{$info.height}px;">
			{if $banner.Link}<a {if $banner.externalLink}target="_blank"{/if} {if !$banner.Follow}rel="nofollow"{/if} href="{$banner.Link}">{/if}
				{assign var='banner_src' value=$smarty.const.RL_FILES_URL|cat:$info.folder|cat:$banner.Image}
				<img alt="{$banner.name}" title="{$banner.name}" src="{$banner_src}" data-thumb="{$banner_src}" />
			{if $banner.Link}</a>{/if}
		</div>
		{elseif $banner.Type == 'flash'}
		<div class="banner" title="{$banner.name}" style="margin:auto; width:{$info.width}px; height:{$info.height}px;">
			<object width="{$info.width}" height="{$info.height}" data="{$smarty.const.RL_FILES_URL}{$info.folder}{$banner.Image}" type="application/x-shockwave-flash">
				<param value="{$smarty.const.RL_FILES_URL}{$info.folder}{$banner.Image}" name="movie">
				<param name="wmode" value="transparent">
				<param value="direct_link=true" name="flashvars">
				<embed width="{$info.width}" height="{$info.height}" flashvars="direct_link=true" wmode="transparent" src="{$smarty.const.RL_FILES_URL}{$info.folder}{$banner.Image}">
			</object>
		</div>
		{elseif $banner.Type == 'html'}
		<div class="banner" style="margin:auto; width:{$info.width}px; height:{$info.height}px;">
			{$banner.Html}
		</div>
		{/if}
	{/foreach}

	{if $bannerBoxWithFadeEffect}</div>{/if}
{else}
	<div class="banner" style="{if $boxBetweenCategories}display:inline;{/if}margin:auto; width:{$info.width}px; height:{$info.height}px;">
		<div class="banner-here" style="line-height:{$info.height-2}px;">{$info.width} x {$info.height}</div>
	</div>
{/if}
</div>

<!-- banners box end -->