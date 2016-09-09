<div class="grid_navbar">
	<div class="sorting">
		<div class="current{if $grid_mode == 'map'} disabled{/if}">
			{$lang.sort_by}: 
			<span class="link">{if $sort_by}{$sorting[$sort_by].name}{else}{$lang.date}{/if}</span>
			<span class="arrow"></span>
		</div>
		<ul class="fields">
		{foreach from=$sorting item='field_item' key='sort_key' name='fSorting'}
			<li>
				<a {if $sort_by == $sort_key}class="active {if $sort_type == 'asc' || empty($sort_type)}asc{else}desc{/if}"{/if} title="{$lang.banners_sortBy} {$field_item.name}" href="{if $config.mod_rewrite}?{else}{$smarty.const.RL_URL_HOME}index.php?page={$pageInfo.Path}&amp;{/if}sort_by={$sort_key}{if $sort_by == $sort_key}&amp;sort_type={if $sort_type == 'asc' || !isset($sort_type)}desc{elseif !empty($sort_key) && empty($sort_type)}desc{else}asc{/if}{/if}">{$field_item.name}</a>
			</li>
		{/foreach}
		</ul>
	</div>
</div>


<section id="listings" class="my-listings list">
{foreach from=$myBanners item='mBanner' name='transactionF'}
	{include file=$smarty.const.RL_PLUGINS|cat:'banners'|cat:$smarty.const.RL_DS|cat:'banner_responsive_42.tpl'}
{/foreach}
</section>

<!-- paging block -->
{paging calc=$pInfo.calc total=$myBanners current=$pInfo.current per_page=$config.listings_per_page}
<!-- paging block end -->

<script type="text/javascript">
{literal}

	$(document).ready(function(){
		$('.my-listings .delete').each(function(){
			$(this).flModal({
				caption: '{/literal}{$lang.warning}{literal}',
				content: '{/literal}{$lang.banners_remove_banner_confirm}{literal}',
				prompt: 'xajax_deleteBanner('+ $(this).attr('id').split('_')[2] +')',
				width: 'auto',
				height: 'auto'
			});
		});
	});

{/literal}
</script>