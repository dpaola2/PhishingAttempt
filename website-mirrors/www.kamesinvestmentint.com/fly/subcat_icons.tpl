<!-- categories_icons plugin -->

{if $cat.sub_categories[sub_cat]}
	{assign var='cat_sub_tmp' value=$cat.sub_categories[sub_cat]}	
{else}
	{assign var='cat_sub_tmp' value=$sub_cat}
{/if}

{if $cat_sub_tmp.Icon}
    {if isset($listing_types.$type)}
		{assign var='lt_tmp' value=$listing_types.$type}
	{else}
		{assign var='lt_tmp' value=$listing_type}
	{/if}

	<a class="category" title="{$sub_cat.name}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$lt_tmp.Page_key]}/{$cat_sub_tmp.Path}{if $lt_tmp.Cat_postfix}.html{else}/{/if}{else}?page={$lt_tmp.Page_key}&amp;category={$cat_sub_tmp.ID}{/if}">
		<img src="{$smarty.const.RL_URL_HOME}files/{$cat_sub_tmp.Icon}" title="{$cat_sub_tmp.name}" alt="{$cat_sub_tmp.name}" />
	</a>
{/if}

<!-- end categories_icons plugin -->