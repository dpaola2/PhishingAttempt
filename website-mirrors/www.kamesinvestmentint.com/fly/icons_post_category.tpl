<!-- categories_icons plugin -->

{if $cat.Icon && ($config.categories_icons_position == 'right' || $config.categories_icons_position == 'bottom')} 
	{if $pageInfo.Key == 'home'}
		{assign var='icon_path' value=$pages.browse}
	{else}
		{assign var='icon_path' value=$pageInfo.Path}
	{/if}
    {if isset($listing_types.$type)}
		{assign var='lt_tmp' value=$listing_types.$type}
	{else}
		{assign var='lt_tmp' value=$listing_type}
	{/if}

	<div style="{if $config.categories_icons_position == 'right'}display: inline;{else}display: block;{/if}">
		<a class="category cat_icon" title="{$cat.name}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$lt_tmp.Page_key]}/{$cat.Path}{if $lt_tmp.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$lt_tmp.Page_key]}&amp;category={$cat.ID}{/if}">
			<img src="{$smarty.const.RL_URL_HOME}files/{$cat.Icon}" title="{$cat.name}" alt="{$cat.name}" />
		</a>
	</div>
{/if}

<!-- end categories_icons plugin -->