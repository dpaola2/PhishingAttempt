<!-- banner item -->

<article class="item" id="banner_{$mBanner.ID}">{strip}
	<div class="info" style="width:190px;max-height:130px;overflow:hidden;">
		{if $mBanner.Type == 'image'}
			{if $mBanner.Image}
				<img alt="" title="{$mBanner.name}" src="{$smarty.const.RL_FILES_URL}banners/{$mBanner.Image}" />
			{/if}
		{elseif $mBanner.Type == 'flash'}
			<object id="flash_banner_{$mBanner.Key}" width="190" height="130" data="{$smarty.const.RL_FILES_URL}banners/{$mBanner.Image}" type="application/x-shockwave-flash">
				<param value="{$smarty.const.RL_FILES_URL}banners/{$mBanner.Image}" name="movie">
				<param value="opaque" name="transparent">
				<param name="allowscriptaccess" value="samedomain">
				<param value="direct_link=true" name="flashvars">
				<embed width="190" height="130" flashvars="direct_link=true" wmode="transparent" src="{$smarty.const.RL_FILES_URL}banners/{$mBanner.Image}">
			</object>
		{elseif $mBanner.Type == 'html'}
			{$lang.banners_bannerType_html}
		{/if}
		<div class="title"><span>{$mBanner.name}</span></div>
	</div>
	<div class="navigation">
		<ul>
			<li class="nav-icon">
				<a class="edit" href="{$rlBase}{if $config.mod_rewrite}{$pages.banners_edit_banner}.html?id={$mBanner.ID}{else}?page={$pages.banners_edit_banner}&amp;id={$mBanner.ID}{/if}">
					<span>{$lang.banners_editBanner}</span>&nbsp;
				</a>
			</li>
			<li>
				<a title="{$lang.banners_renewPlan}" href="{$rlBase}{if $config.mod_rewrite}{$pages.banners_renew}.html?id={$mBanner.ID}{else}?page={$pages.banners_renew}&amp;id={$mBanner.ID}{/if}">
					<span>{$lang.banners_renewPlan}</span>&nbsp;
				</a>
			</li>
		</ul>
	</div>
	<div class="stat">
		<ul>
			<li class="two-inline left">
				<div class="statuses">
					{if $mBanner.Status == 'incomplete'}
						<a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html?incomplete={$mBanner.ID}&amp;step={$mBanner.Last_step}{else}?page={$pageInfo.Path}&amp;incomplete={$mBanner.ID}&amp;step={$mBanner.Last_step}{/if}" class="{$mBanner.Status}">
							{$lang[$mBanner.Status]}
						</a>
					{elseif $mBanner.Status == 'expired'}
						<a href="{$rlBase}{if $config.mod_rewrite}{$pages.banners_renew}.html?id={$mBanner.ID}{else}?page={$pages.banners_renew}&amp;id={$mBanner.ID}{/if}" title="{$lang.banners_renewPlan}" class="{$mBanner.Status}">
							{$lang[$mBanner.Status]}
						</a>
					{else}
						<span {if $mBanner.Status == 'pending'}title="{$lang.banners_waitingApproval}"{/if} class="{$mBanner.Status}">{$lang[$mBanner.Status]}</span>
					{/if}
				</div>
				<div class="ralign">
					<span class="icon delete" id="delete_banner_{$mBanner.ID}" title="{$lang.delete}"></span>
				</div>
			</li>

			{if $mBanner.Date_to && $mBanner.Plan_type == 'period'}
				<li>
					<span class="name">{$lang.active_till} </span>{$mBanner.Date_to|date_format:$smarty.const.RL_DATE_FORMAT}
				</li>
			{elseif $mBanner.Date_to && $mBanner.Plan_type == 'views'}
				<li>
					<span class="name">{$lang.banners_showsLeft} </span>{math equation="x - y" x=$mBanner.Date_to y=$mBanner.Shows}
				</li>
			{/if}

			{if $mBanner.Key}
				<li>
					<span class="name">{$lang.plan} </span>{assign var='planName' value='banner_plans+name+'|cat:$mBanner.Key}{$lang.$planName}
				</li>
			{/if}

			<li>
				<span class="name">{$lang.banners_bannerShows} </span>{$mBanner.Shows}
			</li>

			{if $mBanner.Type == 'image'}
				<li>
					<span class="name">{$lang.banners_bannerClicks} </span>{if $mBanner.clicks}{$mBanner.clicks}{else}0{/if}
				</li>
			{/if}
		</ul>
	</div>
{/strip}</article>

<!-- banner item end -->