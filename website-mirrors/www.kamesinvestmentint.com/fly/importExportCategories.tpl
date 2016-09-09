<!-- export import categories tpl -->

<!-- navigation bar -->
<div id="nav_bar">
	{if $smarty.get.action == 'import' || !isset($smarty.get.action)}
	<a href="{$rlBaseC}action=export" class="button_bar"><span class="left"></span><span class="center_export">{$lang.importExportCategories_export}</span><span class="right"></span></a>
	{/if}
	{if $smarty.get.action == 'export' || !isset($smarty.get.action)}
	<a href="{$rlBaseC}action=import" class="button_bar"><span class="left"></span><span class="center_import">{$lang.importExportCategories_import}</span><span class="right"></span></a>
	{/if}
</div>
<!-- navigation bar end -->

{if !isset($smarty.get.action)}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<img src="{$smarty.const.RL_PLUGINS_URL}importExportCategories/admin/static/example.png" alt="" title="" />
	<div class="clear"></div>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

	<script type="text/javascript">
	{literal}
		printMessage('info', 'Your XLS file must be like on the example.');
	{/literal}
	</script>

{elseif $smarty.get.action == 'import'}
	{if !$smarty.session.imex_plugin}

		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
		<form action="{$rlBaseC}action=import" method="post" enctype="multipart/form-data" onsubmit="return submit_form();">
		<input type="hidden" name="submit" value="1" />
			<table class="form">
			<tr>
				<td class="name">
					<span class="red">*</span>{$lang.importExportCategories_importTo}
				</td>
				<td class="field">
					<select name="listing_type">
						{foreach from=$listing_types item="type" key='typeKey'}
						<option value="{$type.Key}">{$type.name}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td class="name">
					<span class="red">*</span>{$lang.file}
				</td>
				<td class="field">
					<input type="file" class="file" name="file_import" />
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="field">
					<input class="submit" type="submit" value="{$lang.importExportCategories_import}" />
				</td>
			</tr>
			</table>
		</form>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

		<script type="text/javascript">
		{literal}
			var submit_form = function() {
				var importFile = $('[name=file_import]').val();
				if ( importFile == '' ) {
					printMessage('error', '{/literal}{$lang.importExportCategories_import_filename_empty|replace:"[field]":$lang.file}{literal}');
					return false;
				}
				else {
					if ( importFile.split('.')[1] != 'xls' ) {
						printMessage('error', '{/literal}{$lang.importExportCategories_incorrect_file_ext}{literal}');
						return false;
					}
				}
				return true;
			}
		{/literal}
		</script>

	{else}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<div style="padding: 10px;">
		<table class="lTable">
		<tr class="body">
			<td class="list_td_light">Import all categories from the uploaded XLS file</td>
			<td style="width: 5px;" rowspan="100"></td>
			<td class="list_td_light" align="center" style="width: 200px;">
				<input type="button" id="import_categories_button" value="{$lang.importExportCategories_import}" style="margin: 0;width: 100px;" />
			</td>
		</tr>
		</table>
	</div>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

	<div id="grid"></div>
	<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}importExportCategories/admin/static/lib.js"></script>
	<script type="text/javascript">//<![CDATA[
	{literal}

	function inportCategories(start) {
		$.getJSON(rlPlugins +'importExportCategories/admin/import.php?start='+ start, function(response) {
			if ( response.next === true && response.start > start ) {
				inportCategories(response.start);
			}
			else {
				location.href=rlUrlHome +'index.php?controller='+ controller +'&done';
			}
		});
	}

	$(document).ready(function(){

		$('#import_categories_button').click(function() {
			inportCategories(0);
		});

		var importCategoriesGrid = new gridObj({
			key: 'importCategories',
			id: 'grid',
			ajaxUrl: rlPlugins + 'importExportCategories/admin/importExportCategories.inc.php?q=ext',
			defaultSortField: false,
			title: lang['importExportCategories_titleOfManager'],
			fields: [
				{name: 'name', mapping: 'name'},
				{name: 'parent', mapping: 'parent'},
				{name: 'type', mapping: 'type'},
				{name: 'path', mapping: 'path'},
				{name: 'level', mapping: 'level', type: 'int'},
				{name: 'locked', mapping: 'locked'}
			],
			columns: [
				{
					header: lang['ext_name'],
					dataIndex: 'name',
					id: 'rlExt_item_bold',
					width: 22
				},{
					header: lang['ext_parent'],
					dataIndex: 'parent',
					id: 'rlExt_item',
					width: 15,
					renderer: function(value) {
						if ( !value ) {
							return '<span style="color:#3D3D3D">{/literal}{$lang.no_parent}{literal}</span>';
						}
						return value;
					}
				},{
					header: lang['ext_type'],
					dataIndex: 'type',
					width: 10,
					renderer: function(value) {
						return '<b>'+ value +'</b>';
					}
				},{
					header: '{/literal}{$lang.category_url}{literal}',
					dataIndex: 'path',
					width: 40
				},{
					header: lang['importExportCategories_rowLevel'],
					dataIndex: 'level',
					width: 8,
					renderer: function(value) {
						return '<b>'+ value +'</b>';
					}
				},{
					header: lang['ext_locked'],
					dataIndex: 'locked',
					width: 8,
					renderer: function(value) {
						if ( value == '1' ) {
							return lang['ext_yes'];
						}
						return lang['ext_no'];
					}
				}
			]
		});

		importCategoriesGrid.init();
		grid.push(importCategoriesGrid.grid);

	});
	{/literal}
	//]]>
	</script>

	{/if}
	
{elseif $smarty.get.action == 'export'}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}action=export" method="post" onsubmit="return submit_form();">
		<input type="hidden" name="submit" value="1" />
		<table class="form">
		<tr>
			<td>
				<div id="cat_checkboxed" style="margin: 0 0 8px;{if $sPost.cat_sticky}display: none{/if}">
					<div class="tree">
						{foreach from=$sections item='section'}
							<fieldset class="light">
								<legend id="legend_section_{$section.ID}" class="up" onclick="fieldset_action('section_{$section.ID}');">{$section.name}</legend>
								<div id="section_{$section.ID}">
									{if !empty($section.Categories)}
										{include file='blocks'|cat:$smarty.const.RL_DS|cat:'category_level_checkbox.tpl' categories=$section.Categories first=true}
									{else}
										<div style="padding: 0 0 8px 10px;">{$lang.no_items_in_sections}</div>
									{/if}
								</div>
							</fieldset>
						{/foreach}
					</div>
				</div>

				<script type="text/javascript">
				var submit_form;
				var tree_selected = {if $smarty.post.categories}[{foreach from=$smarty.post.categories item='post_cat' name='postcatF'}['{$post_cat}']{if !$smarty.foreach.postcatF.last},{/if}{/foreach}]{else}false{/if};
				var tree_parentPoints = {if $parentPoints}[{foreach from=$parentPoints item='parent_point' name='parentF'}['{$parent_point}']{if !$smarty.foreach.parentF.last},{/if}{/foreach}]{else}false{/if};
				{literal}

				$(document).ready(function(){
					flynax.treeLoadLevel('checkbox', 'flynax.openTree(tree_selected, tree_parentPoints)', 'div#cat_checkboxed');
					flynax.openTree(tree_selected, tree_parentPoints);

					$('input[name=cat_sticky]').click(function(){
						$('#cat_checkboxed').slideToggle();
						$('#cats_nav').fadeToggle();
					});
					
					submit_form = function() {
						if ( $('#cat_checkboxed input[type=checkbox]:checked').length > 0 || $('input[name=cat_sticky]:checked').length > 0 ) {
							return true;
						}
						else {
							printMessage('info', '{/literal}{$lang.importExportCategories_empty}{literal}');
							return false;
						}
					}
				});

				{/literal}
				</script>

				<div class="grey_area">
					<label><input class="checkbox" {if $sPost.cat_sticky}checked="checked"{/if} type="checkbox" name="cat_sticky" value="true" /> {$lang.sticky}</label>
					<span id="cats_nav" {if $sPost.cat_sticky}class="hide"{/if}>
						<span onclick="$('#cat_checkboxed div.tree input').attr('checked', true);" class="green_10">{$lang.check_all}</span>
						<span class="divider"> | </span>
						<span onclick="$('#cat_checkboxed div.tree input').attr('checked', false);" class="green_10">{$lang.uncheck_all}</span>
					</span>
				</div>
			</td>
		</tr>
		<tr>
			<td class="field">
				<input type="submit" id="export_btn" value="{$lang.importExportCategories_export}" />
			</td>
		</tr>
		</table>
	</form>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

{/if}

<!-- export import categories tpl end -->