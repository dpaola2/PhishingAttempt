<!-- listing block -->

{if $listing.Listing_type}
	{assign var='listing_type' value=$listing_types[$listing.Listing_type]}
{/if}

<li class="item{if $listing.Featured} featured{/if}">
	<div class="bottom-layer">
		<div class="top-layer">
			<table class="sTable">
			<tr>
				{if $listing_type.Photo}
				<td rowspan="2" class="photo" valign="top">
					<div>
						{if $listing.Featured && $listing_type.Photo}
							<div class="label">{if $listing_type.Page}<a {if $config.view_details_new_window}target="_blank"{/if} title="{$lang.featured}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{$lang.featured[0]}</a>{/if}</div>
						{/if}
						{if $listing_type.Page}<a title="{$listing.listing_title}" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{/if}
							<img {if empty($listing.Main_photo)}class="blank" style="width: {$config.pg_upload_thumbnail_width}px;height: {$config.pg_upload_thumbnail_height}px;"{/if} alt="{$listing.listing_title}" src="{if empty($listing.Main_photo)}{$rlTplBase}img/blank.gif{else}{$smarty.const.RL_URL_HOME}files/{$listing.Main_photo}{/if}" />
						{if $listing_type.Page}</a>{/if}
						{if !empty($listing.Main_photo) && $config.grid_photos_count && $listing_type.Page}
							<div class="counter"><a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{/if}">{$listing.Photos_count}</a></div>
						{/if}
					</div>
				</td>
				{/if}
				<td valign="top">
					{assign var='f_first' value=true}
				
					<table class="sTable">
					<tr>
						<td class="fields">
							<ul>
								{foreach from=$listing.fields item='item' key='field' name='fListings'}
									{if !empty($item.value) && $item.Details_page && $item.Key != 'price'}
										<li {if $config.sf_display_fields}title="{$item.name}"{/if} id="sf_field_{$listing.ID}_{$item.Key}">
										{if $f_first && $listing_type.Page}
											<a title="{$item.value}" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{$item.value}</a>
										{else}
											{$item.value}
										{/if}
										{assign var='f_first' value=false}
										</li>
									{/if}
								{/foreach}
				
								{rlHook name='mobileListingAfterFields'}
							</ul>
							<div class="category-name">
								{assign var='cat_pattern' value=`$smarty.ldelim`category`$smarty.rdelim`}
								{if $listing_type.Page}
									{if $config.mod_rewrite}
										{assign var='cat_link' value=$pages[$listing_type.Page_key]|cat:'/'|cat:$listing.Path}
										{if $listing_type.Cat_postfix}
											{assign var='cat_link' value=$cat_link|cat:'.html'}
										{else}
											{assign var='cat_link' value=$cat_link|cat:'/'}
										{/if}
									{else}
										{assign var='cat_link' value='?page='|cat:$pages[$listing_type.Page_key]|cat:'&amp;category='|cat:$listing.Category_ID}
									{/if}
									{assign var='cat_replace' value='<a title="'|cat:$lang.category|cat:': '|cat:$listing.name|cat:'" href="'|cat:$rlBase|cat:$cat_link|cat:'">'|cat:$listing.name|cat:'</a>'}
								{else}
									{assign var='cat_replace' value=$listing.name}
								{/if}
								{$lang.grid_in_category|replace:$cat_pattern:$cat_replace}
								
								{if $listing.Crossed_listing} <img src="{$rlTplBase}img/blank.gif" alt="{$lang.crossed}" title="{$lang.crossed}" class="crossed" />{/if}
							</div>
						</td>
						<td class="ralign nav_icons" valign="top">
							<span class="nav hide">{rlHook name='mobileListingNavIcons'}</span>
							
							<a id="fav_{$listing.ID}" title="{$lang.add_to_favorites}" href="javascript:void(0)" class="icon add_favorite"><span>&nbsp;</span></a>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="caption" valign="bottom">
					<table class="nav">
					<tr>
						<td valign="bottom">
							{if $listing.fields.price.value}<span class="price">{$listing.fields.price.value}</span>{/if}
							{rlHook name='mobileListingBeforeStats'}
						</td>
						<td valign="bottom" class="ralign">
							{*if $config.count_listing_visits}<span class="shows icon" title="{$lang.shows}">{$listing.Shows}</span>{/if*}
							{if $config.display_posted_date}<span class="date icon" title="{$lang.posted_date}">{$listing.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span>{/if}
							
							{rlHook name='mobileListingAfterStats'}
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
		</div>
	</div>
</li>

<!-- listing block end -->