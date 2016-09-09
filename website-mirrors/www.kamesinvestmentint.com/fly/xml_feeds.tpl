<!-- navigation bar -->
<div id="nav_bar">
	{if $smarty.get.action == 'mapping'}
		<a href="javascript:void(0)" onclick="show('add_mapping_item')" class="button_bar"><span class="left"></span><span class="center_add">{$lang.xf_add_mapping_item}</span><span class="right"></span></a>
		<a href="javascript:void(0)" onclick="rlConfirm( '{$lang.delete_confirm}', 'xajax_clearMapping', Array('{$smarty.get.format}') );" class="button_bar"><span class="left"></span><span class="center_remove">{$lang.xf_clear_mapping}</span><span class="right"></span></a>
	{/if}

	{if $smarty.get.mode == 'formats' && (!$smarty.get.action || $smarty.get.action == 'formats')}
		<a href="{$rlBaseC}action=add_format" class="button_bar"><span class="left"></span><span class="center_add">{$lang.xf_add_format}</span><span class="right"></span></a>
	{/if}

	{if $smarty.get.mode == 'feeds' || (!$smarty.get.action && !$smarty.get.mode )}
		<a href="{$rlBaseC}action=add_feed" class="button_bar"><span class="left"></span><span class="center_add">{$lang.xf_add_feed}</span><span class="right"></span></a>
		<a href="javascript:void(0)" onclick="show('filters', '#action_blocks div');" class="button_bar"><span class="left"></span><span class="center_search">{$lang.filters}</span><span class="right"></span></a>
	{/if}

	{if $smarty.get.action == 'edit_feed'}
		<a href="{$rlBaseC}action=statistics&feed={$smarty.get.feed}" class="button_bar"><span class="left"></span><span class="center_import">{$lang.xf_statistics}</span><span class="right"></span></a>
	{/if}

	{if $smarty.get.mode == 'users'}
		<a href="{$rlBaseC}action=add_user" class="button_bar"><span class="left"></span><span class="center_add">{$lang.xf_add_user}</span><span class="right"></span></a>
	{/if}

	{if $smarty.get.action == 'statistics'}
		<a href="{$rlBaseC}action=mapping&format={$format_info.Key}" target="_blank" class="button_bar"><span class="left"></span><span class="center">{$lang.xf_build_mapping}</span><span class="right"></span></a>		
		<a href="javascript:void(0)" onclick="xajax_runFeed('{$smarty.get.feed}', '{$smarty.get.account_id}')" class="button_bar"><span class="left"></span><span class="center_import">{$lang.xf_run_import}</span><span class="right"></span></a>				
		<a target="_blank" href="{$rlBase}index.php?controller=listings&amp;feed={$smarty.get.feed}{if $smarty.get.account_id}&amp;username={$account_username}{/if}" class="button_bar"><span class="left"></span><span class="center_search">{$lang.xf_view_listings}</span><span class="right"></span></a>				
		<a href="javascript:void(0)" onclick="xajax_clearStatistics('{$smarty.get.feed}'{if $smarty.get.account_id}, {$smarty.get.account_id} {/if})" class="button_bar"><span class="left"></span><span class="center_remove">{$lang.xf_clear_statistics}</span><span class="right"></span></a>
		<a href="{$rlBaseC}action=edit_feed&feed={$smarty.get.feed}" class="button_bar"><span class="left"></span><span class="center_edit">{$lang.xf_edit_feed}</span><span class="right"></span></a>
	{/if}

	{if $formats_mode}
	{else}
		{if $smarty.get.mode != 'feeds' && ($smarty.get.mode || $smarty.get.action)}
			<a href="{$rlBaseC}mode=feeds" class="button_bar"><span class="left"></span><span class="center_list">{$lang.xf_manage_feeds}</span><span class="right"></span></a>
		{/if}
		{if $smarty.get.mode != 'formats'}
	 		<a href="{$rlBaseC}mode=formats" class="button_bar"><span class="left"></span><span class="center_list">{$lang.xf_manage_formats}</span><span class="right"></span></a>
	 	{/if}			 	
		{if $smarty.get.action != 'export'}
			<a href="{$rlBaseC}action=export" class="button_bar"><span class="left"></span><span class="center_export">{$lang.xf_export}</span><span class="right"></span></a>
		{/if}
	{/if}			
</div>
<!-- navigation bar end -->

{if $info && !$errors}
<script>
	{if $info|@count > 1}
		var info_message = '<ul>{foreach from=$info item="mess"}<li>{$mess}</li>{/foreach}</ul>';
	{else}
		var info_message = '{$info.0}';
	{/if}
	
	{literal}
	$(document).ready(function(){
		printMessage('info', info_message);
	});
	{/literal}
</script>
{/if}

{if ($smarty.get.action == 'edit_feed' && $smarty.get.feed) || $smarty.get.action == 'add_feed'}
	{assign var='sPost' value=$smarty.post}

	<!-- add/edit -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}action={if $smarty.get.action == 'add_feed'}add_feed{elseif $smarty.get.action == 'edit_feed'}edit_feed&amp;feed={$smarty.get.feed}{/if}" method="post">
		<input type="hidden" name="submit" value="1" />
		{if $smarty.get.action == 'edit_feed'}
			<input type="hidden" name="fromPost" value="1" />
		{/if}

		<table class="form">
		<tr>
			<td class="name"><span class="red">*</span>{$lang.xf_feed_name}</td>
			<td class="field">
				{if $allLangs|@count > 1}
					<ul class="tabs">
						{foreach from=$allLangs item='language' name='langF'}
						<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
						{/foreach}
					</ul>
				{/if}
				
				{foreach from=$allLangs item='language' name='langF'}
					{if $allLangs|@count > 1}
						<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">
					{/if}
					<input type="text" name="name[{$language.Code}]" value="{$sPost.name[$language.Code]}" style="width: 250px;" />
					{if $allLangs|@count > 1}
						<span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span></div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr>
			<td class="name"><span class="red">*</span>{$lang.xf_feed_url}</td>
			<td class="field">
				<input name="url" type="text" value="{$sPost.url}" />
			</td>
		</tr>			
		<tr>
			<td class="name"><span class="red">*</span>{$lang.xf_format}</td>
			<td class="field">
				<select name="format">
					<option value="0">{$lang.select}</option>
					{foreach from=$formats item="format"}
						<option value="{$format.Key}" {if $sPost.format == $format.Key}selected="selected"{/if}>{$format.name}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td class="name"><span class="red">*</span>{$lang.xf_plan}</td>
			<td class="field">
				<select name="plan_id">
					<option value="0">{$lang.select}</option>
					{foreach from=$plans item="plan"}
						<option value="{$plan.ID}" {if $sPost.plan_id == $plan.ID}selected="selected"{/if}>{$plan.name}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		{if $listing_types|@count > 1}
			<tr>
				<td class="name">
					{$lang.xf_listing_types}
				</td>
				<td class="field">
					<select name="listing_type" >
						<option value="0">{$lang.all}</option>
						{foreach from=$listing_types key="key" item="listing_type" name="ltLoop"}
							<option value="{$listing_type.Key}" {if $sPost.listing_type == $listing_type.Key}selected="selected"{/if} {if $smarty.foreach.ltLoop.first}selected="selected"{/if}>{$listing_type.name}</option>
						{/foreach}
					</select>
				</td>
			</tr>
		{/if}
		<tr>
			<td class="name">
				{$lang.category}
			</td>
			<td class="field">
				{assign var="levels_number" value=2}
				<input type="hidden" id="category_value" name="category_id" value="{$fVal.$fKey}"/>
				<select id="category_level0" {if $levels_number == 2}style="width:120px"{/if} class="multicat">
					<option value="0">{$lang.any}</option>
					{foreach from=$categories item='option' key='key'}
						<option {if $fVal.$fKey == $option.ID}selected="selected"{/if} value="{$option.ID}">{$lang[$option.pName]}</option>
					{/foreach}
				</select>
				{section name=multicat start=1 loop=$levels_number step=1}
					<select id="category_level{$smarty.section.multicat.index}" disabled="disabled" {if $levels_number == 2}style="width:120px"{/if} class="multicat{if $smarty.section.multicat.last} last{/if}">
						<option value="0">{$lang.any}</option>
					</select>
				{/section}

				<input type="hidden" value="{$sPost.default_category}" name="default_category" />
			</td>
		</tr>
		<tr>
			<td class="name"><span class="red">*</span>{$lang.xf_listings_status}</td>
			<td class="field">
				<select name="listings_status">
					<option {if $sPost.listings_status == 'active'}selected="selected"{/if} value="active">{$lang.active}</option>
					<option {if $sPost.listings_status == 'approval'}selected="selected"{/if} value="approval">{$lang.approval}</option>
				</select>
			</td>
		</tr>			
		
		<tr>
			<td class="name"><span class="red">*</span>{$lang.xf_username}</td>
			<td class="field">
				<input type="text" name="account_id" id="account_id" value="{foreach from=$accounts item='account'}{if $sPost.account_id == $account.ID}{$account.Username}{/if}{/foreach}" />
					
				<script type="text/javascript">
				var post_account_id = {if $sPost.account_id}{$sPost.account_id}{else}false{/if};
				{literal}
					$('#account_id').rlAutoComplete({add_id: true, id: post_account_id});
				{/literal}
				</script>
			</td>
		</tr>		
		<tr>
			<td class="name">{$lang.status}</td>
			<td class="field">
				<select name="status">
					<option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
					<option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="field">
				<input type="submit" value="{if $smarty.get.action == 'edit_feed'}{$lang.edit}{else}{$lang.add}{/if}" />
			</td>
		</tr>
		</table>
	</form>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

	<script type="text/javascript">
		{literal}
			$(document).ready(function(){
				$('select[name=listing_type]').change(function(){					
					xajax_loadCategories( $(this).val(), 0, -1 );					
				});

				$('.multicat').change(function(){
					var level = $(this).attr('id').split('category_level')[1];
					var category_id = '';

					if( $(this).val() && $(this).val() != 0 )
					{
						category_id = $(this).val();
					}else if( $('#category_level' + (level - 1) ).val() )
					{				
						category_id = $('#category_level' + (level - 1) ).val();
					}	

					$('input[name=default_category]').val(category_id);

					if( !$(this).hasClass('last') )
					{
						xajax_loadCategories( $('select[name=listing_type]').val(), category_id, level );
					}
				});

				if( $('input[name=default_category]').val() )
				{
					xajax_buildCategories( $('select[name=listing_type]').val(), $('input[name=default_category]').val() );
				}
			});
		{/literal}
	</script>

{elseif ($smarty.get.action == 'edit_format' && $smarty.get.format) || $smarty.get.action == 'add_format'}

	{assign var='sPost' value=$smarty.post}
	<!-- add/edit -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}action={if $smarty.get.action == 'add_format'}add_format{elseif $smarty.get.action == 'edit_format'}edit_format&amp;format={$smarty.get.format}{/if}" method="post">
		<input type="hidden" name="submit" value="1" />
		{if $smarty.get.action == 'edit_format'}
			<input type="hidden" name="fromPost" value="1" />
		{/if}

		<table class="form">
			<tr>
				<td class="name"><span class="red">*</span>{$lang.xf_format_name}</td>
				<td class="field">
					{if $allLangs|@count > 1}
						<ul class="tabs">
							{foreach from=$allLangs item='language' name='langF'}
							<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
							{/foreach}
						</ul>
					{/if}
					
					{foreach from=$allLangs item='language' name='langF'}
						{if $allLangs|@count > 1}
							<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">
						{/if}
						<input type="text" name="name[{$language.Code}]" value="{$sPost.name[$language.Code]}" style="width: 250px;" />
						{if $allLangs|@count > 1}
							<span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span></div>
						{/if}
					{/foreach}
				</td>
			</tr>
			<tr>
				<td class="name"><span class="red">*</span>{$lang.xf_xpath}</td>
				<td class="field">
					<input name="xpath" type="text" value="{$sPost.xpath}" />					
				</td>
			</tr>
			<tr>
				<td class="name"><span class="red">*</span>{$lang.xf_format_for}</td>
				<td class="field">
					<label><input name="format_for[import]" type="checkbox" value="import" {if 'import'|in_array:$smarty.post.format_for}checked="checked"{/if} /> {$lang.xf_import_label}</label>
					<label><input name="format_for[export]" type="checkbox" value="export" {if 'export'|in_array:$smarty.post.format_for}checked="checked"{/if} /> {$lang.xf_export_label}</label>
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.status}</td>
				<td class="field">
					<select name="status">
						<option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
						<option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="field">
					<input type="submit" value="{if $smarty.get.action == 'edit_format'}{$lang.edit}{else}{$lang.add}{/if}" />
				</td>
			</tr>
		</table>
	</form>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	
{elseif $smarty.get.action == 'statistics'}
	<div id="feed_stats" style="margin-top:10px">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
		<table class="lTable" style="text-align:center">
			<tr class="header">
				<td>
					<div>{$lang.xf_format}</div>
				</td>
				<td class="clear"></td>
				<td>
					<div>{$lang.xf_plan}</div>
				</td>
				<td class="clear"></td>
				<td>				
					<div>{$lang.xf_username}</div>
				</td>
				<td class="clear"></td>
				<td>
					<div>{$lang.xf_default_category}</div>
				</td>				
				<td class="clear"></td>
				<td>
					<div>{$lang.xf_feed_url}</div>
				</td>
			</tr>
			<tr class="body">
				<td class="list_td">					
					<a target="_blank" href="{$rlBaseC}action=edit_format&format={$feed_info.Format}">{$feed_info.Format_name}</a>
				</td>
				<td class="clear" style="width: 3px;"></td>
				<td class="list_td">
					<a target="_blank" href="{$rlBase}index.php?controller=listing_plans&action=edit&id={$feed_info.Plan_ID}">{$feed_info.Plan_name}</a>
				</td>
				<td class="clear" style="width: 3px;"></td>
				<td class="list_td">
					<a target="_blank" href="{$rlBase}index.php?controller=accounts&action=view&id={$feed_info.Account_ID}">{$feed_info.Username}</a>
				</td>
				<td class="clear" style="width: 3px;"></td>
				<td class="list_td">
					<a target="_blank" href="{$rlBase}index.php?controller=browse&id={$feed_info.Default_category}">{$feed_info.Category_name}</a>					
				</td>
				<td class="clear" style="width: 3px;"></td>
				<td class="list_td">
					<a target="_blank" href="{$feed_info.Url}">{$feed_info.Url|truncate:100}</a>
				</td>				
			</tr>
		</table>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	{if $statistics}
		<table class="lTable" style="text-align:center" id="stats_table">
			<tr class="header">
				<td style="height: 24px;">
					<div>
						{$lang.xf_stats_feed}
					</div>
				</td>
				<td class="clear" style="width: 3px;"></td>
				<td style="height: 24px;">
					<div>
						{$lang.xf_stats_date}
					</div>
				</td>
				<td class="clear" style="width: 3px;"></td>
				<td style="height: 24px;">
					<div>
						{$lang.xf_stats_account}
					</div>
				</td>
				<td class="clear" style="width: 3px;"></td>
				<td style="height: 24px;">
					<div>
						{$lang.xf_stats_updated}
					</div>
				</td>
				<td class="clear" style="width: 3px;"></td>
				<td style="height: 24px;">
					<div>
						{$lang.xf_stats_inserted}
					</div>
				</td>
				<td class="clear" style="width: 3px;"></td>
				<td style="height: 24px;">
					<div>
						{$lang.xf_stats_deleted}
					</div>
				</td>
			</tr>
			<tr>
			</tr>
			{assign var="date_format" value=$smarty.const.RL_DATE_FORMAT|cat:' %H:%M'}
			{foreach from=$statistics item="entry" name="statsLoop"}
				{if $smarty.foreach.statsLoop.iteration%2 == 0}
					{assign var="td_style" value='_light'}
				{else}
					{assign var="td_style" value=''}
				{/if}
				<tr class="body">
					<td class="list_td{$td_style}">
						<a href="{$rlBaseC}action=edit_feed&feed={$entry.Feed}">{$entry.Feed_name}</a>
					</td>
					<td class="clear" style="width: 3px;"></td>
					<td class="list_td{$td_style}">
						{$entry.Date|date_format:$date_format}
					</td>
					<td class="clear" style="width: 3px;"></td>

					<td class="list_td{$td_style}">
						<a href="{$rlBase}index.php?controller=accounts&action=view&userid={$entry.Account_ID}">{$entry.Username}</a>
					</td>
					<td class="clear" style="width: 3px;"></td>

					<td class="list_td{$td_style}">
						{if $entry.Listings_updated}
							<a alt="{$lang.xf_click_to_show}" title="{$lang.xf_click_to_show}" href="javascript:void(0)" class="count_link" style="color:#FB941B;font-weight:bold">{$entry.Count_updated}</a>
						{else}
							0
						{/if}
						{if $entry.Listings_updated}
							<div class="hide" id="{$entry.ID}_updated">
								<b>{$lang.xf_listing_ids}:</b>
								{foreach from=$entry.Listings_updated item="listing" name="listingsLoop"}
									 {$listing}{if !$smarty.foreach.listingsLoop.last},{/if}
								{/foreach}
							</div>
						{/if}
					</td>
					<td class="clear" style="width: 3px;"></td>

					<td class="list_td{$td_style}">
						{if $entry.Listings_inserted}
							<a alt="{$lang.xf_click_to_show}" title="{$lang.xf_click_to_show}" href="javascript:void(0)" class="count_link" style="font-weight:bold">{$entry.Count_inserted}</a>
						{else}
							0
						{/if}

						{if $entry.Listings_inserted}
							<div class="hide" id="{$entry.ID}_inserted">
								<b>{$lang.xf_listing_ids}:</b>
								{foreach from=$entry.Listings_inserted item="listing" name="listingsLoop"}
									 {$listing}{if !$smarty.foreach.listingsLoop.last},{/if}
								{/foreach}
							</div>
						{/if}
					</td>
					<td class="clear" style="width: 3px;"></td>

					<td class="list_td{$td_style}">
						{if $entry.Listings_deleted}
							<span style="color:#B63636;font-weight:bold">{$entry.Listings_deleted}</span>
						{else}
							0
						{/if}
					</td>
					<td class="clear" style="width: 3px;"></td>				
				</tr>
			{/foreach}
		</table>
	{else}
		<div>{$lang.xf_no_stats}</div>
	{/if}
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	<script type="text/javascript">
	{literal}
		$(document).ready(function(){
			$('.count_link').click(function(){
				if( $(this).next('div').css('display') == 'block' )
				{
					 $(this).next('div').slideUp();
				}else
				{
					 $(this).next('div').slideDown();
				}
			});
		});
	{/literal}
	</script>
	</div>

	<div id="manual_import_cont" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
			<div id="manual_import_dom"></div>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>	
{elseif $smarty.get.action == 'mapping' && $smarty.get.format}

	<!-- add new mapping item -->
	<div id="add_mapping_item" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.add_item}		
		<table class="form">
		<tr>
			<td class="name">{$lang.xf_remote_field}</td>
			<td class="field">
				<input id="mapping_item_remote" type="text" class="w250" />

				{$lang.xf_default}

				<input id="mapping_item_default" type="text" class="w250" />
			</td>
		</tr>
		
		<tr>
			<td class="name">{$lang.xf_local_field}</td>
			<td class="field">
				<select id="mapping_item_local" class="w250">
					<option value="0">{$lang.select}</option>
					{if $smarty.get.field}
						{foreach from=$local_values item="local_value"}
							<option value="{$local_value.Key}">{$local_value.name}</option>
						{/foreach}
					{else}
						<optgroup label="{$lang.xf_listingfields_label}">
						{foreach from=$listing_fields item="field"}						
							<option value="{$field.Key}" {if $field.Key == $xml_field.fl}selected="selected"{/if} >{$field.name} ( {$field.Type_name} )</option>
						{/foreach}
						</optgroup>
						{if $system_fields}
							<optgroup label="{$lang.xf_sysfields_label}">
							{foreach from=$system_fields item="field"}
								<option value="{$field.Key}" {if $field.Key == $xml_field.fl}selected="selected"{/if} >{$field.name} ( {$field.Type_name} )</option>
							{/foreach}
							</optgroup>
						{/if}
					{/if}
				</select>
			</td>
		</tr>		
		<tr>
			<td class="name">{$lang.status}</td>
			<td class="field">
				<select id="ni_status">
					<option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
					<option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
				</select>
			</td>
		</tr>
	
		{*<tr>
			<td class="name">{$lang.default}</td>
			<td class="field">
				<input type="checkbox" id="ni_default" value="1" />
			</td>
		</tr>*}
		
		<tr>
			<td></td>
			<td class="field">
				<input type="button" name="item_submit" value="{$lang.add}" />
				<a onclick="$('#add_mapping_item').slideUp('normal')" href="javascript:void(0)" class="cancel">{$lang.close}</a>
			</td>
		</tr>
		</table>		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>

	<script type="text/javascript">
		{literal}
			$(document).ready(function(){
				$('#mapping_item_local').change(function(){
					if( !$('#mapping_item_remote').val() )
					{
						$('#mapping_item_remote').val( $(this).val() );
					}
				});
			});

			$('input[name=item_submit]').click(function(){
				$(this).val( lang['loading'] );

				if( $('#mapping_item_local').val() && ( $('#mapping_item_remote').val() || $('#mapping_item_default').val() ) )
				{
					xajax_addMappingItem( $('#mapping_item_local').val(), $('#mapping_item_remote').val(), $('#mapping_item_default').val() );
				}else
				{
					$('input[name=item_submit]').val( lang['add'] );
					printMessage("error", "Fill all fields");
				}
			});
		{/literal}
	</script>

	{if !$smarty.get.field}
		<div id="grid"></div>
		<script type="text/javascript">//<![CDATA[
		{literal}		
			var xmlMappingGrid;
			
			$(document).ready(function(){			
				xmlMappingGrid = new gridObj({
					key: 'xml_mapping',
					id: 'grid',
					ajaxUrl: rlPlugins + 'xmlFeeds/admin/xml_feeds.inc.php?q=ext_mapping&format={/literal}{$smarty.get.format}{literal}',
					defaultSortField: 'Data_remote',
					title: lang['ext_xml_formats_manager'],
					fields: [
						{name: 'ID', mapping: 'ID', type: 'int'},
						{name: 'Data_remote', mapping: 'Data_remote', type: 'string'},
						{name: 'Data_local', mapping: 'Data_local', type: 'string'},
						{name: 'Local_field_name', mapping: 'Local_field_name', type: 'string'},
						{name: 'Local_field_type', mapping: 'Local_field_type', type: 'string'},
						{name: 'Format', mapping: 'Format', type: 'string'},
						{name: 'Format_name', mapping: 'Format_name', type: 'string'},
						{name: 'Example_value', mapping: 'Example_value'},
						{name: 'Cdata', mapping: 'Cdata'},
						{name: 'Mf', mapping: 'Mf'},
						{name: 'Default', mapping: 'Default'},
						{name: 'Status', mapping: 'Status'}
					],
					columns: [{
							header: '{/literal}{$lang.xf_remote_field}{literal}',
							dataIndex: 'Data_remote',
							id: 'rlExt_item_bold',
							width: 20
						},{
							header: '{/literal}{$lang.xf_local_field}{literal}', 
							dataIndex: "Local_field_name",
							width: 10,
							editor: new Ext.form.ComboBox({
								store: [
								{/literal}{foreach from=$listing_fields item="field"}
									['{$field.Key}', '{$field.name}'],
								{/foreach}
								{foreach from=$system_fields item="field" name="sysFieldsLoop"}
									['{$field.Key}', '{$field.name}']{if !$smarty.foreach.sysFieldsLoop.last},{/if}
								{/foreach}{literal}
								],
								displayField: 'value',
								valueField: 'key',
								typeAhead: true,
								mode: 'local',
								triggerAction: 'all',
								selectOnFocus:true
							}),
							renderer: function(val){
								return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
							}
						},{
							header: '{/literal}{$lang.xf_map_example_value}{literal}',
							dataIndex: 'Example_value',
							width: 10
						},{						
							header: '{/literal}{$lang.xf_mapping_default}{literal}',
							dataIndex: 'Default',
							width: 10,
							editor: new Ext.form.TextArea({
								allowBlank: false
							}),
							renderer: function(val){
								return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
							}
						},{
							header: '{/literal}{$lang.xf_cdata}{literal}',
							dataIndex: 'Cdata',
							width: 10,
							editor: new Ext.form.ComboBox({
								store: [
									['1', lang['ext_yes']],
									['0', lang['ext_no']]
								],
								displayField: 'value',
								valueField: 'key',
								typeAhead: true,
								mode: 'local',
								triggerAction: 'all',
								selectOnFocus:true
							}),
							renderer: function(val){
								return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
							}
						},{
							header: lang['ext_status'],
							dataIndex: 'Status',
							width: 10,
							editor: new Ext.form.ComboBox({
								store: [
									['active', lang['ext_active']],
									['approval', lang['ext_approval']]
								],
								displayField: 'value',
								valueField: 'key',
								typeAhead: true,
								mode: 'local',
								triggerAction: 'all',
								selectOnFocus:true
							}),
							renderer: function(val){
								return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
							}
						},{
							header: lang['ext_actions'],
							width: 90,
							fixed: true,
							dataIndex: 'ID',
							sortable: false,
							renderer: function(val, obj, row){
								var out = "<center>";
								var splitter = false;
								var format_key = row.data.Format;
								var item_key = row.data.Data_remote;

								if( row.data.Data_local.indexOf('category') == 0 )
								{
									out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=mapping&amp;format="+format_key+"&amp;field=category'><img class='build'ext:qtip='"+lang['ext_build']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
								}
								else if( row.data.Data_local.match(/(.*)_level[0-9]/) )
								{
									var mf_fkey = /(.*)_level[0-9]/.exec(row.data.Data_local)[1];
									out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=mapping&amp;format="+format_key+"&amp;field=mf|"+mf_fkey+"'><img class='build'ext:qtip='"+lang['ext_build']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
								}
								else if( row.data.Mf )
								{								
									out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=mapping&amp;format="+format_key+"&amp;field=mf|"+row.data.Data_local+"'><img class='build'ext:qtip='"+lang['ext_build']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
								}
								else if( row.data.Local_field_type == 'select' )
								{
									out += "<a href="+rlUrlHome+"index.php?controller="+controller+"&action=mapping&amp;format="+format_key+"&amp;field="+item_key+"><img class='build' ext:qtip='"+lang['ext_build']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
								}

								out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \"{/literal}{$lang.xf_notice_remove_item}{literal}\", \"xajax_deleteMappingItem\", \""+item_key+"\", \"section_load\" )' />";
								out += "</center>";
								
								return out;
							}
						}
					]
				});

				xmlMappingGrid.init();
				grid.push(xmlMappingGrid.grid);

				xmlMappingGrid.grid.addListener('afteredit', function(editEvent)
				{
					//if( editEvent.value.indexOf('category') == 0 || editEvent.record.json.Local_field_type == 'select')
					//{
						xmlMappingGrid.reload();
					//}
				});
			});
			{/literal}
		//]]>
		</script>
	{else}

		<!-- items mapping grid -->

		<div id="grid"></div>
		<script type="text/javascript">//<![CDATA[
		{literal}		
			var xmlItemMappingGrid;
			
			$(document).ready(function(){			
				xmlItemMappingGrid = new gridObj({
					key: 'xml_mapping',
					id: 'grid',
					ajaxUrl: rlPlugins + 'xmlFeeds/admin/xml_feeds.inc.php?q=ext_item_mapping&format={/literal}{$smarty.get.format}&field={$smarty.get.field} \
					{if $smarty.get.parent}&parent={$smarty.get.parent}{/if}{literal}',
					defaultSortField: 'Data_remote',
					title: lang['ext_xml_formats_manager'],
					fields: [
						{name: 'ID', mapping: 'ID', type: 'int'},
						{name: 'Data_remote', mapping: 'Data_remote', type: 'string'},
						{name: 'Data_local', mapping: 'Data_local', type: 'string'},
						{name: 'Local_field_name', mapping: 'Local_field_name', type: 'string'},
						{name: 'Local_field_type', mapping: 'Local_field_type', type: 'string'},
						{name: 'Format', mapping: 'Format', type: 'string'},
						{name: 'Format_name', mapping: 'Format_name', type: 'string'},
						{name: 'Status', mapping: 'Status'}
					],
					columns: [{
							header: '{/literal}{$lang.xf_remote_data}{literal}',
							dataIndex: 'Data_remote',
							id: 'rlExt_item_bold',
							width: 30
						},{
							header: '{/literal}{$lang.xf_local_data}{literal}',
							dataIndex: "Data_local",
							width: 30,
							editor: new Ext.form.ComboBox({
								store: [
								{/literal}{foreach from=$listing_fields item="field"}
									['{$field.Key}', '{$field.name}'],
								{/foreach}
								{foreach from=$local_values item="value" name="sysFieldsLoop"}
									['{$value.Key}', '{$value.name}']{if !$smarty.foreach.sysFieldsLoop.last},{/if}
								{/foreach}{literal}
								],
								displayField: 'value',
								valueField: 'key',
								typeAhead: true,
								mode: 'local',
								triggerAction: 'all',
								selectOnFocus:true
							}),
							renderer: function(val){
								return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
							}
						},{
							header: lang['ext_status'],
							dataIndex: 'Status',
							width: 10,
							editor: new Ext.form.ComboBox({
								store: [
									['active', lang['ext_active']],
									['approval', lang['ext_approval']]
								],
								displayField: 'value',
								valueField: 'key',
								typeAhead: true,
								mode: 'local',
								triggerAction: 'all',
								selectOnFocus:true
							}),
							renderer: function(val){
								return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
							}
						},{
							header: lang['ext_actions'],
							width: 90,
							fixed: true,
							dataIndex: 'ID',
							sortable: false,
							renderer: function(val, obj, row){
								var out = "<center>";
								var splitter = false;
								var format_key = row.data.Format;
								var item_key = row.data.Data_remote;

								{/literal}{if $local_field_info.Data_local|strpos:"category_"|is_numeric}{literal}
								if( row.data.Data_local )
								{
									out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=mapping&amp;format="+format_key+"&amp;field={/literal}{$smarty.get.field}{literal}&amp;parent="+row.data.ID+"'><img class='build' ext:qtip='"+lang['ext_build']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
								}
								{/literal}{elseif $mf_field}{literal}
								if( row.data.Data_local )
								{
									out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=mapping&amp;format="+format_key+"&amp;field={/literal}{$smarty.get.field}{literal}&amp;parent="+row.data.ID+"'><img class='build' ext:qtip='"+lang['ext_build']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
								}
								{/literal}{else}{literal}
								if( !row.data.Data_local )
								{									
									out += "<img class='export' ext:qtip='"+lang['ext_xf_insert_item']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \""+lang['ext_xf_insert_item']+"\", \"xajax_copyMappingItem\", \""+item_key+"\", \"section_load\" )' />";
								}								
								{/literal}{/if}{literal}

								out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \""+lang['ext_notice_delete_item']+"\", \"xajax_deleteMappingItem\", \""+item_key+"\", \"section_load\" )' />";
								out += "</center>";

								return out;
							}
						}
					]
				});
				
				xmlItemMappingGrid.init();
				grid.push(xmlItemMappingGrid.grid);
				
			});
			{/literal}
		//]]>
		</script>
	{/if}
{elseif $smarty.get.mode == 'formats'}
	<div id="grid"></div>
	<script type="text/javascript">//<![CDATA[
	{literal}		
		var xmlFormatsGrid;
		
		$(document).ready(function(){			
			xmlFormatsGrid = new gridObj({
				key: 'xml_formats',
				id: 'grid',
				ajaxUrl: rlPlugins + 'xmlFeeds/admin/xml_feeds.inc.php?q=ext_formats',
				defaultSortField: 'name',
				title: lang['ext_xml_formats_manager'],
				fields: [
					{name: 'ID', mapping: 'ID', type: 'int'},
					{name: 'name', mapping: 'name', type: 'string'},					
					{name: 'Status', mapping: 'Status'},
					{name: 'Format_for', mapping: 'Format_for'},
					{name: 'Key', mapping: 'Key'}
				],
				columns: [{
						header: lang['ext_name'],
						dataIndex: 'name',
						id: 'rlExt_item_bold',
						width: 50
					},{
						header: '{/literal}{$lang.xf_format_for}{literal}',
						dataIndex: 'Format_for',						
						width: 20
					},{
						header: lang['ext_status'],
						dataIndex: 'Status',
						width: 20,
						editor: new Ext.form.ComboBox({
							store: [
								['active', lang['ext_active']],
								['approval', lang['ext_approval']]
							],
							displayField: 'value',
							valueField: 'key',
							typeAhead: true,
							mode: 'local',
							triggerAction: 'all',
							selectOnFocus:true
						}),
						renderer: function(val){
							return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
						}
					},{
						header: lang['ext_actions'],
						width: 10,
						fixed: false,
						dataIndex: 'ID',
						sortable: false,
						renderer: function(val, obj, row){
							var out = "<center>";
							var splitter = false;
							var format_key = row.data.Key;
							
							out += "<a href="+rlUrlHome+"index.php?controller="+controller+"&action=mapping&amp;format="+format_key+"><img class='build' ext:qtip='"+lang['ext_build']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";							
							
							out += "<a href="+rlUrlHome+"index.php?controller="+controller+"&action=edit_format&amp;format="+format_key+"><img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \""+lang['ext_notice_delete_format']+"\", \"xajax_deleteFormat\", \""+format_key+"\", \"section_load\" )' />";
							out += "</center>";
							
							return out;
						}
					}
				]
			});
			
			xmlFormatsGrid.init();
			grid.push(xmlFormatsGrid.grid);
			
		});
		{/literal}
	//]]>
	</script>
{elseif $smarty.get.action == 'export'}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}

	<div id="build_url">
		<table class="form" >
			<tr>
				<td class="name"><span class="red">*</span>{$lang.xf_format}</td>
				<td class="field">
					<select name="format">
					<option value="0">{$lang.select}</option>
						{foreach from=$formats item="format"}
							<option value="{$format.Key}" {if $sPost.format == $format.Key}selected="selected"{/if}>{$format.name}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
			<td class="name"><span class="red">*</span>{$lang.xf_export_type}</td>
			<td class="field">
				<label><input class="lang_add" type="radio" name="export_type" value="one" /> {$lang.xf_etype_one}</label>
				<label><input checked="checked" class="lang_add" type="radio" name="export_type" value="all" /> {$lang.xf_etype_all}</label>
			</td>
			</tr>
			{if $listing_types|@count > 1}
			<tr>
				<td class="name">
					{$lang.listing_type}
				</td>
				<td class="field">
					<select name="listing_type" >
						<option value="0">{$lang.all}</option>
						{foreach from=$listing_types key="key" item="listing_type" name="ltLoop"}
							<option value="{$listing_type.Key}" {if $smarty.foreach.ltLoop.first}selected="selected"{/if}>{$listing_type.name}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			{/if}
		</table>

		<div id="user_settings">
			<table class="form">
			<tr>
				<td class="name"><span class="red">*</span>{$lang.xf_username}</td>
				<td class="field">
					<input type="text" id="account_id" name="account_id" value="{foreach from=$accounts item='account'}{if $sPost.account_id == $account.ID}{$account.Username}{/if}{/foreach}" />

					<script type="text/javascript">
						var post_account_id = {if $sPost.account_id}{$sPost.account_id}{else}false{/if};
						{literal}
							$('#account_id').rlAutoComplete({add_id: false});
						{/literal}
					</script>
				</td>
			</tr>
			</table>
		</div>

	<table class="form">
		<tr>
			<td class="name">{$lang.xf_export_limit}</td>
			<td class="field">
				<input type="text" class="numeric" value="" name="limit" />
			</td>
		</tr>
		<tr>
			<td class="name">
				{$lang.xf_rewrite_rule}
			</td>
			<td class="field">
				<input type="hidden" id="actual_rewrite" value="{if $rewrite}{$smarty.const.RL_URL_HOME}{$rewrite}{else}{$smarty.const.RL_PLUGINS_URL}xmlFeeds/export.php?[format][params]{/if}" />
				<input type="hidden" id="rewrited" value="{if $rewrite}1{else}0{/if}" />

				<input value="{if $rewrite}{$rewrite}{else}{$default_rewrite}{/if}" class="w250" name="rewrite" > 
				<input style="padding:3px 14px 3px" id="apply_rule" type="button" value="{if $rewrite}{$lang.xf_htrule_edit}{else}{$lang.xf_htrule_edit}{/if}" />
				<span class="field_description" class="hide" id="hint">
					{$lang.xf_rewrite_hint}
				</span>
			</td>
		</tr>
		<tr>
			<td class="name">
				{$lang.xf_export_url}
			</td>
			<td class="field">
				<textarea cols="5" rows="1" style="height:60px" readonly="true" id="out"></textarea>                    
			</td>
		</tr>
	</table>
	</div>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

	<script type="text/javascript">
		{literal}
			$(document).ready(function(){
				$('input[name=export_type]').change(function(){
					handleEType();
				});
				handleEType();

				$('#apply_rule').click(function(){
					$('#apply_rule').val('{/literal}{$lang.loading}{literal}');
					xajax_applyRule( $('input[name=rewrite]').val() );
				});

				$('#build_url input,select').change(function(){
					if( $(this).attr('name') != 'account_id' )
					{
						buildUrl();
					}
				});

				$('#account_id').blur(function(){
					setTimeout(function(){ buildUrl() },100);
				});

				$('#out').click(function(){ $(this).select() });
			});

			var handleEType = function()
			{
				if( $('input[name=export_type]:checked').val() == 'all' )
				{
					$('#user_settings').slideUp();
				}else
				{
					$('#user_settings').slideDown();
				}
			};

			function buildUrl()
			{
				var actual_rewrite = $('#actual_rewrite').val();
				var params = new Array();
				var params_string= '';
				var format = false;
				var rewrited = $('#rewrited').val();

				$('#build_url input,select').each(function(){
					var name = $(this).attr('name');
					if( name == 'format' )
					{
						format = $(this).val() != 0 && $(this).val() ? $(this).val() : '';
						if( rewrited == 0)
						{
							format = 'format='+format;
						}							
					}else if( name == 'account_id' && $(this).val() && $('input[name=export_type]:checked').val() == 'one')
					{                        
						params['dealer'] = $(this).val();
					}
					else if( $(this).val() && name && name != 'export_type' && name != 'rewrite' && name != 'account_id')
					{
						params[ name ] = $(this).val();
					}
				});
                                               
				if( format )
				{
					var delim = '&';
                    
					if( actual_rewrite.indexOf('?') < 0 )
					{
						delim = '?';
					}
						
					for( var i in params )
					{
						if( typeof(params[i]) != 'function' )
						{                            
							params_string += delim + i + '=' + params[i];
							delim = '&';
						}
					}
					actual_rewrite = actual_rewrite.replace('[format]', format);
					actual_rewrite = actual_rewrite.replace('[params]', params_string)

					$('#out').html(actual_rewrite);
				}
			}
		{/literal}
	</script>

{else}

	<div id="action_blocks">
		{if !isset($smarty.get.action)}
			<!-- filters -->
			<div id="filters" class="hide">
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.filter_by}
				
				<table>
				<tr>
					<td valign="top">
						<table class="form">
						<tr>
							<td class="name w130">{$lang.username}</td>
							<td class="field">
								<input class="filters" type="text" maxlength="255" id="Account" />
							</td>
						</tr>
						<tr>
							<td></td>
							<td class="field nowrap">
								<input type="button" class="button" value="{$lang.filter}" id="filter_button" />
								<input type="button" class="button" value="{$lang.reset}" id="reset_filter_button" />
								<a class="cancel" href="javascript:void(0)" onclick="show('filters')">{$lang.cancel}</a>
							</td>
						</tr>
						</table>
					</td>
					<td style="width: 50px;"></td>
					<td valign="top">
						<table class="form">												
						<tr>
							<td class="name w130">{$lang.xf_format}</td>
							<td class="field">
								<select class="filters w200" id="format">
									<option value="">{$lang.select}</option>
									{foreach from=$filter_formats item='format'}
										<option value="{$format.Key}">{$format.name}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
				
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
			</div>
			
			<script type="text/javascript">
			{literal}
			var filters = new Array();
			var step = 0;
	
			$(document).ready(function(){
				
				if ( readCookie('xml_sc') )
				{
					$('#filters').show();
					var cookie_filters = readCookie('xml_sc').split(',');
					
					for (var i in cookie_filters)
					{
						if ( typeof(cookie_filters[i]) == 'string' )
						{
							var item = cookie_filters[i].split('||');
							$('#'+item[0]).selectOptions(item[1]);
						}
					}
				}				
				
				$('#filter_button').click(function(){
					filters = new Array();
					write_filters = new Array();
					
					createCookie('xml_pn', 0, 1);
					
					$('.filters').each(function(){
						if ($(this).attr('value') != 0)
						{
							filters.push(new Array($(this).attr('id'), $(this).attr('value')));
							write_filters.push($(this).attr('id')+'||'+$(this).attr('value'));
						}
					});

					// save search criteria
					createCookie('xml_sc', write_filters, 1);
					
					// reload grid
					xmlFeedsGrid.filters = filters;
					xmlFeedsGrid.reload();
				});
				
				$('#reset_filter_button').click(function(){
					eraseCookie('xml_sc');
					xmlFeedsGrid.reset();
					
					$("#filters select option[value='']").attr('selected', true);
					$("#filters input[type=text]").val('');
					$("#Category_ID option").show();
				});
	
				/* autocomplete js */
				$('#Account').rlAutoComplete();
			});
			{/literal}
			</script>
			<!-- filters end -->
		{/if}
	</div>


	<div id="grid"></div>
	<script type="text/javascript">//<![CDATA[
	{literal}		
		var xmlFeedsGrid;
		
		/* read cookies filters */
		var cookies_filters = false;
		
		if ( readCookie('xml_sc') )
			cookies_filters = readCookie('xml_sc').split(',');
		

		$(document).ready(function(){			
			xmlFeedsGrid = new gridObj({
				key: 'xml_feeds',
				id: 'grid',
				ajaxUrl: rlPlugins + 'xmlFeeds/admin/xml_feeds.inc.php?q=ext_feeds',
				defaultSortField: 'name',
				title: lang['ext_xml_feeds_manager'],
				filters: cookies_filters,
				filtersPrefix: true,
				fields: [
					{name: 'ID', mapping: 'ID', type: 'int'},
					{name: 'name', mapping: 'name', type: 'string'},
					{name: 'Status', mapping: 'Status'},
					{name: 'Key', mapping: 'Key'},
					{name: 'account', mapping: 'account'},
					{name: 'Format', mapping: 'Format'}
				],
				columns: [{
						header: lang['ext_name'],
						dataIndex: 'name',
						id: 'rlExt_item_bold',
						width: 20
					},{
						header: lang['ext_owner'],
						dataIndex: 'account',							
						width: 20
					},{
						header: {/literal}'{$lang.xf_format}'{literal},
						dataIndex: 'Format',
						id: 'rlExt_item_bold',
						width: 40
					},{
						header: lang['ext_status'],
						dataIndex: 'Status',
						width: 10,
						editor: new Ext.form.ComboBox({
							store: [
								['active', lang['ext_active']],
								['approval', lang['ext_approval']]
							],
							displayField: 'value',
							valueField: 'key',
							typeAhead: true,
							mode: 'local',
							triggerAction: 'all',
							selectOnFocus:true
						}),
						renderer: function(val){
							return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
						}
					},{
						header: lang['ext_actions'],
						width: 90,
						fixed: true,
						dataIndex: 'ID',
						sortable: false,
						renderer: function(val, obj, row){
							var out = "<center>";
							var splitter = false;
							var feed_key = row.data.Key;

							out += "<a href="+rlUrlHome+"index.php?controller="+controller+"&action=statistics&amp;feed="+feed_key+"><img class='manage' ext:qtip='{/literal}{$lang.xf_statistics}{literal}' src='"+rlUrlHome+"img/blank.gif' /></a>";
							out += "<a href="+rlUrlHome+"index.php?controller="+controller+"&action=edit_feed&amp;feed="+feed_key+"><img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \""+lang['ext_notice_delete_feed']+"\", \"xajax_deleteFeed\", \""+feed_key+"\", \"section_load\" )' />";
							out += "</center>";
							
							return out;
						}
					}
				]
			});
			
			xmlFeedsGrid.init();
			grid.push(xmlFeedsGrid.grid);
		});
		{/literal}
	//]]>
	</script>
{/if}