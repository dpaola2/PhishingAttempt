<!-- categories block tpl -->

{if $category.ID > 0}
	{math assign='bc_count' equation='count-2' count=$bread_crumbs|@count}

	<div{if $categories} style="padding: 0 0 15px 0;"{/if}>
		{$category.name}
		<a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}{if $bread_crumbs[$bc_count].Path}/{$bread_crumbs[$bc_count].Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}.html{/if}{else}?page={$pageInfo.Path}{if $bread_crumbs[$bc_count].ID}&amp;category={$bread_crumbs[$bc_count].ID}{/if}{/if}"><img title="{$lang.categoryFilter_remove_filter}" alt="" class="remove" src="{$rlTplBase}img/blank.gif" /></a>
	</div>
{/if}

{if !empty($categories)}

	{rlHook name='browsePreCategories'}

	<div class="cat-tree-cont limit-height{if $category.ID > 0} subcat-cont{/if}">
		<ul class="cat-tree">{strip}
		{foreach from=$categories item='cat' name='fCats'}
			{if $cf_item.Items == 1 && ($cat.Count == 0 || ($category_counts && !$category_counts[$cat.ID]))}
				{continue}
			{/if}

			<li>
				{*rlHook name='tplPreCategory'*}
				
				{if $listing_type.Cat_show_subcats}
				<span class="toggle">
					{if !empty($cat.sub_categories)}+{/if}
				</span>
				{/if}
				
				<a title="{if $lang[$cat.pTitle]}{$lang[$cat.pTitle]}{else}{$cat.name}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$cat.Path}/{foreach from=$cf_filter key='filter_key' item='filter_val'}{$filter_key|replace:'_':'-'}:{$filter_val}/{/foreach}{else}?page={$pageInfo.Path}&amp;category={$cat.ID}{foreach from=$cf_filter key='filter_key' item='filter_val'}&amp;cf-{$filter_key|replace:'_':'-'}={$filter_val}{/foreach}{/if}">{$cat.name}</a>

				{if $listing_type.Cat_listing_counter}
					<span class="counter"> ({if $category_counts[$cat.ID]}{$category_counts[$cat.ID]}{else}{$cat.Count|number_format}{/if})</span>
				{/if}

				{rlHook name='tplPostCategory'}

				{if !empty($cat.sub_categories) && $listing_type.Cat_show_subcats}
				<ul class="sub-cats">
					{foreach from=$cat.sub_categories item='sub_cat' name='subCatF'}
						<li>
							{rlHook name='tplPreSubCategory'}
							<a title="{if $lang[$sub_cat.pTitle]}{$lang[$sub_cat.pTitle]}{else}{$sub_cat.name}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$sub_cat.Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pageInfo.Path}&amp;category={$sub_cat.ID}{/if}">{$sub_cat.name}</a>
						</li>
					{/foreach}
				</ul>
				{/if}
			</li>
		{/foreach}
		{/strip}</ul>

		<div class="cat-toggle hide" accesskey="{$cf_item.Items_display_limit}">...</div>
	</div>
	
	{rlHook name='browsePostCategories'}
{/if}

<!-- categories block tpl -->