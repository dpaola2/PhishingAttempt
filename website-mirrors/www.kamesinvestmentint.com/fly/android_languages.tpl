<!-- android languages tpl -->

{if $smarty.get.action == 'edit'}

	<!-- navigation bar -->
	<div id="nav_bar">
		<a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.languages_list}</span><span class="right"></span></a>
	</div>
	<!-- navigation bar end -->

	<!-- edit language -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}action=edit&amp;lang={$smarty.get.lang}" method="post">
		<input type="hidden" name="submit" value="1" />
		<input type="hidden" name="fromPost" value="1" />
		
		{assign var='sPost' value=$smarty.post}
		<table class="form">
		<tr>
			<td class="name"><span class="red">*</span>{$lang.iso_code}</td>
			<td class="field">
				<input readonly="readonly" class="disabled" name="code" type="text" style="width: 150px;" value="{$sPost.code}" maxlength="30" />
			</td>
		</tr>
		<tr>
			<td class="name"><span class="red">*</span>{$lang.lang_direction}</td>
			<td class="field">
				<label title="{$lang.ltr_direction_title}"><input {if $sPost.direction == 'ltr'}checked="checked"{/if} value="ltr" type="radio" name="direction" title="{$lang.ltr_direction_title}" /> {$lang.ltr_direction}</label>
				<label title="{$lang.rtl_direction_title}"><input {if $sPost.direction == 'rtl'}checked="checked"{/if} value="rtl" type="radio" name="direction" title="{$lang.rtl_direction_title}" /> {$lang.rtl_direction}</label>
			</td>
		</tr>
		
		<tr>
			<td class="name">
				<span class="red">*</span>{$lang.name}
			</td>
			<td class="field">
				<input class="text" type="text" name="name" value="{$sPost.name}" style="width: 250px;" maxlength="50" />
			</td>
		</tr>
	
		<tr>
			<td class="name"><span class="red">*</span>{$lang.date_format}</td>
			<td class="field">
				<input name="date_format" type="text" value="{$sPost.date_format}" style="width: 100px;" maxlength="50" />
			</td>
		</tr>
		
		<tr>
			<td class="name"><span class="red">*</span>{$lang.status}</td>
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
				<input type="submit" value="{if $smarty.get.action == 'edit'}{$lang.edit}{else}{$lang.add}{/if}" />
			</td>
		</tr>
		</table>
	</form>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	<!-- edit language end -->

{elseif isset($smarty.post.compare)}
	
	<!-- navigation bar -->
	<div id="nav_bar">
		<a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.languages_list}</span><span class="right"></span></a>
	</div>
	<!-- navigation bar end -->

	<!-- compare -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' flexible=true block_caption=$lang.languages_compare}
	<form action="{$rlBase}index.php?controller={$smarty.get.controller}&amp;compare" method="post">
	<input type="hidden" name="compare" value="true" />
	<table class="form">
	<tr>
		<td class="name" style="width: 250px;"><span class="red">*</span>{$lang.choose_lang_for_compare}</td>
		<td class="field">
			<select name="lang_1" id="lang_1">
			<option value="">{$lang.select}</option>
				{foreach from=$allLangs item='lang_list'}
				<option {if $smarty.post.lang_1 == $lang_list.Code}selected="selected"{/if} value="{$lang_list.Code}">{$lang_list.name}</option>
				{/foreach}
			</select>
			{$lang.and}
			<select name="lang_2" id="lang_2">
			<option value="">{$lang.select}</option>
				{foreach from=$allLangs item='lang_list'}
				<option {if $smarty.post.lang_2 == $lang_list.Code}selected="selected"{/if} value="{$lang_list.Code}">{$lang_list.name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td class="name">{$lang.compare_mode}</td>
		<td class="field">
			<select name="compare_mode">
				<option {if $smarty.post.compare_mode == 'phrases'}selected="selected"{/if} value="phrases">{$lang.by_phrases_exist}</option>
				<option {if $smarty.post.compare_mode == 'translation'}selected="selected"{/if} value="translation">{$lang.by_translation_different}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td></td>
		<td class="field">
			<input type="submit" value="{$lang.compare}" />
		</td>
	</tr>
	</table>
	</form>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	
	{if $compare_lang1 || $compare_lang2}
	
		{assign var='code_1' value=$compare_lang1.Code}
		{assign var='code_2' value=$compare_lang2.Code}
		
		<div id="compare_area_1">
			<div style="padding: 7px 0">
				{if $compare_lang1.diff}
					{if $smarty.post.compare_mode == 'phrases'}
						{$lang.compare_result_info|replace:'[lang1]':$langs_info.$code_1.name|replace:'[lang2]':$langs_info.$code_2.name}<br />
						<input style="margin-top: 5px;" id="copy_button_1" onclick="xajax_copyPhrases(1, 2, '{$langs_info.$code_2.name}');$('#loading_1').fadeIn('normal');" type="button" value="{$lang.compare_copy_phrases|replace:'[lang1]':$langs_info.$code_1.name|replace:'[lang2]':$langs_info.$code_2.name}" />
						<div class="grey_loader" id="loading_1"></div>
					{else}
						{$lang.compare_translation_result_info|replace:'[lang1]':$langs_info.$code_1.name|replace:'[lang2]':$langs_info.$code_2.name}
					{/if}
				{/if}
			</div>
			<div id="compare_grid1" style="clear: both;"></div>
		</div>
	
		<div id="compare_area_2">
			<div style="padding: 20px 0 7px 0">
				{if $compare_lang2.diff}
					{if $smarty.post.compare_mode == 'phrases'}
						{$lang.compare_result_info|replace:'[lang1]':$langs_info.$code_2.name|replace:'[lang2]':$langs_info.$code_1.name}<br />
						<input style="margin-top: 5px;" id="copy_button_2" onclick="xajax_copyPhrases(2, 1, '{$langs_info.$code_1.name}');$('#loading_2').fadeIn('normal');" type="button" value="{$lang.compare_copy_phrases|replace:'[lang1]':$langs_info.$code_2.name|replace:'[lang2]':$langs_info.$code_1.name}" />
						<div class="grey_loader" id="loading_2"></div>
					{else}
						{$lang.compare_translation_result_info|replace:'[lang1]':$langs_info.$code_2.name|replace:'[lang2]':$langs_info.$code_1.name}
					{/if}
				{/if}
			</div>
			<div id="compare_grid2" style="clear: both;"></div>
		</div>
	
		<!-- compare grids creation -->
		<script type="text/javascript">//<![CDATA[
		var compare_mode = '{$smarty.post.compare_mode}';
		
		{if $compare_lang1.diff}
			var lang_1 = '{$code_1}';
			var lang1_name = ': {$langs_info.$code_1.name}';
			var compareGrid1;
			
			{literal}
			$(document).ready(function(){
				
				compareGrid1 = new gridObj({
					key: 'compare1',
					id: 'compare_grid1',
					ajaxUrl: rlPlugins + 'androidConnect/admin/android_languages.inc.php?q=compare&grid=1&compare_mode='+compare_mode,
					defaultSortField: 'Value',
					title: lang['ext_phrases_manager'] + lang1_name,
					fields: [
						{name: 'Key', type: 'string'},
						{name: 'Value', mapping: 'Value', type: 'string'}
					],
					columns: [
						{
							header: lang['ext_key'],
							dataIndex: 'Key',
							width: 30
						},{
							id: 'rlExt_item',
							header: lang['ext_value'],
							dataIndex: 'Value',
							width: 60,
							editor: new Ext.form.TextArea({
								allowBlank: false
							}),
							renderer: function(val){
								return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
							}
						}
					]
				});
				
				compareGrid1.init();
				grid.push(compareGrid1.grid);
			});
			
			{/literal}
		{/if}
		
		{if $compare_lang2.diff}
			var lang_2 = '{$code_2}';
			var lang2_name = ': {$langs_info.$code_2.name}';
			var compareGrid2;
			
			{literal}
			$(document).ready(function(){
				
				compareGrid2 = new gridObj({
					key: 'compare2',
					id: 'compare_grid2',
					ajaxUrl: rlPlugins + 'androidConnect/admin/android_languages.inc.php?q=compare&grid=2&compare_mode='+compare_mode,
					defaultSortField: 'Value',
					title: lang['ext_phrases_manager'] + lang2_name,
					fields: [
						{name: 'Key', type: 'string'},
						{name: 'Value', mapping: 'Value', type: 'string'}
					],
					columns: [
						{
							header: lang['ext_key'],
							dataIndex: 'Key',
							width: 30
						},{
							id: 'rlExt_item',
							header: lang['ext_value'],
							dataIndex: 'Value',
							width: 60,
							editor: new Ext.form.TextArea({
								allowBlank: false
							}),
							renderer: function(val){
								return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
							}
						}
					]
				});
				
				compareGrid2.init();
				grid.push(compareGrid2.grid);
			});
			
			{/literal}
		{/if}
		//]]>
		</script>
	{/if}

{else}

<!-- navigation bar -->
<div id="nav_bar">
	{if $aRights.$cKey.add}
		<a style="display: none;" class="button_bar" href="javascript:void(0)" onclick="show('lang_add_phrase', '#action_blocks div');"><span class="left"></span><span class="center_add">{$lang.add_phrase}</span><span class="right"></span></a>
		<a href="javascript:void(0)" onclick="show('import', '#action_blocks div');" class="button_bar"><span class="left"></span><span class="center_import">{$lang.import}</span><span class="right"></span></a>
	{/if}
	
	{if $aRights.$cKey.edit}
		<a href="javascript:void(0)" onclick="show('compare', '#action_blocks div');" class="button_bar"><span class="left"></span><span class="center_compare">{$lang.compare}</span><span class="right"></span></a>
	{/if}
	
	{if $aRights.$cKey.add}
		<a href="javascript:void(0)" onclick="show('lang_add_container', '#action_blocks div');" class="button_bar"><span class="left"></span><span class="center_add">{$lang.add_language}</span><span class="right"></span></a>
	{/if}
</div>
<!-- navigation bar end -->

<div id="action_blocks">

	{if $aRights.$cKey.add}
	<!-- add language form -->
	<div id="lang_add_container" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.add_language}
		<form action="" method="post" onsubmit="return false;">
			<table class="form">
			<tr>
				<td class="name">{$lang.name}</td>
				<td class="field">
					<input type="text" id="language_name" style="width: 150px;" maxlength="30" />
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.iso_code}</td>
				<td class="field">
					<input type="text" id="iso_code" style="width: 40px; text-align: center;" maxlength="2" />
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.lang_direction}</td>
				<td class="field">
					<label title="{$lang.ltr_direction_title}"><input checked="checked" value="ltr" class="direction" type="radio" name="direction" title="{$lang.ltr_direction_title}" /> {$lang.ltr_direction}</label>
					<label title="{$lang.rtl_direction_title}"><input value="rtl" class="direction" type="radio" name="direction" title="{$lang.rtl_direction_title}" /> {$lang.rtl_direction}</label>
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.date_format}</td>
				<td class="field">
					<input type="text" id="date_format" style="width: 80px; text-align: center;" maxlength="12" value="%d.%m.%Y" />
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.copy_from}</td>
				<td class="field">
					<select class="{if $langCount < 2}desabled{/if}" id="source" {if $langCount < 2}disabled{/if}>
					{foreach from=$allLangs item='languages' name='lang_foreach'}
						<option value="{$languages.Code}" {if $smarty.const.RL_LANG_CODE == $languages.Code} selected="selected"{/if}>{$languages.name}</option>
					{/foreach}
					</select>
				</td>
			</tr>
			
			<tr>
				<td></td>
				<td class="field">
					<input onclick="return rlCheck( Array( Array( 'language_name', '{$lang.name_field_empty}' ) , Array( 'iso_code', '{$lang.iso_code_incorrect_number}', '==^2' ), Array( 'date_format', '{$lang.language_incorrect_date_format}', '>^3' ), Array( 'source', '{$lang.language_no_selected}' ), Array( '.direction', '{$lang.notice_lang_direction_missed}' ) ), 'xajax_addLanguage', 'lang_add_load' );" type="submit" value="{$lang.add}" />
					<span class="loader" id="lang_add_load"></span> <a class="cancel" href="javascript:void(0)" onclick="show('lang_add_container')">{$lang.cancel}</a>
				</td>
			</tr>
			</table>
		</form>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>
	<!-- add language form end -->
	{/if}
	
	{if $aRights.$cKey.add}
	<!-- add phrase form -->
	<div id="lang_add_phrase" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.add_phrase}
		<form action="" method="post" onsubmit="return false;">
			<table class="form">
			<tr>
				<td class="name"><span class="red">*</span>{$lang.key}</td>
				<td class="field">
					<input type="text" id="phrase_key" style="width: 200px;" maxlength="60" />
				</td>
			</tr>
			
			{foreach from=$allLangs item='languages' name='phrase_foreach'}
			<tr>
				<td class="name">
					<span><span class="red">*</span>{$lang.value} <span class="green_10">(<b>{$languages.name}</b>)</span></span>
				</td>
				<td class="field">
					<textarea rows="3" cols="" style="height: 50px;" name="{$languages.Code}"></textarea>
				</td>
			</tr>
			{/foreach}
			
			<tr>
				<td></td>
				<td class="field">
					<input id="add_phrase_submit" onclick="$(this).val('{$lang.loading}');return rlCheck( Array( Array( 'phrase_key', '{$lang.incorrect_phrase_key}', '>^1' ) , Array( 'phrase_side', '{$lang.language_incorrect_date_format}' ) ), 'js_addPhrase' );" type="submit" value="{$lang.add}" />
					<a class="cancel" href="javascript:void(0)" onclick="show('lang_add_phrase')">{$lang.cancel}</a>
				</td>
			</tr>
			</table>
		</form>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>
	<!-- add phrase form end -->
	{/if}
	
	{if $aRights.$cKey.add}
	<!-- import -->
	<div id="import" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.import}
		<form action="{$rlBase}index.php?controller={$smarty.get.controller}&amp;import" method="post" enctype="multipart/form-data">
			<input type="hidden" name="import" value="true" />
			<table class="form">
			<tr>
				<td class="name"><span class="red">*</span>{$lang.sql_dump}</td>
				<td class="field">
					<input type="file" id="import_file" name="dump" />
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="field">
					<input type="submit" value="{$lang.go}" />
					<a class="cancel" href="javascript:void(0)" onclick="show('import')">{$lang.cancel}</a>
				</td>
			</tr>
			</table>
		</form>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>
	<!-- import end -->
	{/if}
	
	{if $aRights.$cKey.edit}
	<!-- compare -->
	<div id="compare" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.languages_compare}
		<form action="{$rlBase}index.php?controller={$smarty.get.controller}&amp;compare" method="post">
		<input type="hidden" name="compare" value="true" />
		<table class="form">
		<tr>
			<td class="name" style="width: 250px;"><span class="red">*</span>{$lang.choose_lang_for_compare}</td>
			<td class="field">
				<select name="lang_1" id="lang_1">
				<option value="">{$lang.select}</option>
					{foreach from=$allLangs item='lang_list'}
					<option {if $smarty.post.lang_1 == $lang_list.Code}selected="selected"{/if} value="{$lang_list.Code}">{$lang_list.name}</option>
					{/foreach}
				</select>
				{$lang.and}
				<select name="lang_2" id="lang_2">
				<option value="">{$lang.select}</option>
					{foreach from=$allLangs item='lang_list'}
					<option {if $smarty.post.lang_2 == $lang_list.Code}selected="selected"{/if} value="{$lang_list.Code}">{$lang_list.name}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td class="name">{$lang.compare_mode}</td>
			<td class="field">
				<select name="compare_mode">
					<option {if $smarty.post.compare_mode == 'phrases'}selected="selected"{/if} value="phrases">{$lang.by_phrases_exist}</option>
					<option {if $smarty.post.compare_mode == 'translation'}selected="selected"{/if} value="translation">{$lang.by_translation_different}</option>
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="field">
				<input type="submit" value="{$lang.compare}" />
				<a class="cancel" href="javascript:void(0)" onclick="show('compare')">{$lang.cancel}</a>
			</td>
		</tr>
		</table>
		</form>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>
	<!-- compare end -->
	{/if}

</div>

{if isset($smarty.get.import)}
<script type="text/javascript">
{literal}
	$(document).ready(function(){
		show('import', '#action_blocks div');
	});
{/literal}
</script>
{/if}

<!-- languages grid create -->
<div id="grid"></div>
<script type="text/javascript">//<![CDATA[
var languagesGrid;

{literal}
$(document).ready(function(){
	
	languagesGrid = new gridObj({
		key: 'languages',
		id: 'grid',
		ajaxUrl: rlPlugins + 'androidConnect/admin/android_languages.inc.php?q=ext_list',
		defaultSortField: 'name',
		title: lang['ext_languages_manager'],
		fields: [
			{name: 'ID', mapping: 'ID', type: 'int'},
			{name: 'Data', mapping: 'Data', type: 'string'},
			{name: 'name', mapping: 'name', type: 'string'},
			{name: 'Number', mapping: 'Number', type: 'string'},
			{name: 'Direction', mapping: 'Direction', type: 'string'},
			{name: 'Status', mapping: 'Status'}
		],
		columns: [
			{
				id: 'rlExt_item',
				header: lang['ext_name'],
				dataIndex: 'name',
				width: 50
			},{
				header: lang['ext_text_direction'],
				dataIndex: 'Direction',
				width: 10,
				editor: new Ext.form.ComboBox({
					store: [{/literal}
						['ltr', '{$lang.ltr_direction_title}'],
						['rtl', '{$lang.rtl_direction_title}']
					{literal}],
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
				header: lang['ext_phrases_number'],
				dataIndex: 'Number',
				width: 12,
				id: 'rlExt_item_bold',
				renderer: function(data, param1, param2) {
					data += ' <a onclick="phrasesManager('+param2.id+')" class="green_11_bg" href="javascript:void(0)">{/literal}{$lang.manage_phrases}{literal}</a>';
					return data;
				}
			},{
				header: lang['ext_status'],
				dataIndex: 'Status',
				fixed: true,
				width: 100,
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
				})
			},{
				header: lang['ext_actions'],
				width: 80,
				fixed: true,
				dataIndex: 'Data',
				sortable: false,
				renderer: function(data) {
					data = data.split('|');
					var out = '';
					var splitter = false;
					
					if ( rights[cKey].indexOf('edit') >= 0 )
					{
						out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&amp;action=export&amp;lang="+data[0]+"'><img class='export' ext:qtip='"+lang['ext_export']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
						out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&amp;action=edit&amp;lang="+data[0]+"'><img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
					}
					
					if ( rights[cKey].indexOf('delete') >= 0 && data[1] == 'false' )
					{
						out += "<a href='javascript:void(0)' onclick='rlConfirm( \""+lang['ext_notice_'+delete_mod]+"\", \"xajax_deleteLang\", \""+Array(data[0])+"\", \"admin_load\" )'><img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
					}
					
					return out;
				}
			}
		]
	});
		
	languagesGrid.init();
	grid.push(languagesGrid.grid);
	
});
{/literal}
//]]>
</script>
<!-- languages grid create end -->

<!-- search button -->
{if $aRights.$cKey.edit}
<div class="aright" style="padding: 10px 0;">
	<a href="javascript:void(0)" onclick="show('lang_search_block');" class="button_bar"><span class="left"></span><span class="center_search">{$lang.search}</span><span class="right"></span></a>
</div>
{/if}
<!-- search button end -->

<!-- search block -->
{if $aRights.$cKey.edit}
<div id="lang_search_block" class="hide">
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.search}
	<table class="form">
	<tr>
		<td class="name">{$lang.phrase}</td>
		<td class="field">
			<textarea id="phrase" rows="3" style="height: auto;" cols=""></textarea>
		</td>
	</tr>
	<tr>
		<td class="name">{$lang.search_in}</td>
		<td class="field">
			<label><input name="criteria" type="radio" id="in_value" checked="checked" /> {$lang.phrase_text}</label>
			<label><input name="criteria" type="radio" id="in_key" /> {$lang.phrase_key}</label>
		</td>
	</tr>
	<tr>
		<td class="name" style="text-transform: capitalize;">{$lang.language}</td>
		<td class="field">
			<select class="{if $langCount < 2}disabled{/if}" id="in_language" {if $langCount < 2}disabled="desabled"{/if}>
			{if $langCount > 1}<option value="all">{$lang.all}</option>{/if}
			{foreach from=$allLangs item='languages' name='lang_foreach'}
				<option value="{$languages.Code}">{$languages.name}</option>
			{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td></td>
		<td class="field">
			<input id="search_button" type="button" value="{$lang.search}" />
			<div class="loader" id="search_load"></div> <a class="cancel" href="javascript:void(0)" onclick="show('lang_search_block')">{$lang.cancel}</a>
		</td>
	</tr>
	</table>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
</div>
{/if}
<!-- search block end -->

<!-- phrases grid -->
<div id="phrases"></div>
<script type="text/javascript">//<![CDATA[
var phrasesGrid;

{literal}
$(document).ready(function(){
	
	phrasesGrid = new gridObj({
		key: 'phrases',
		id: 'phrases',
		ajaxUrl: rlPlugins + 'androidConnect/admin/android_languages.inc.php?q=ext',
		defaultSortField: 'Value',
		title: lang['ext_phrases_manager'],
		fields: [
			{name: 'Key', type: 'string'},
			{name: 'Value', mapping: 'Value', type: 'string'}
		],
		columns: [
			{
				header: lang['ext_key'],
				dataIndex: 'Key',
				width: 30
			},{
				id: 'rlExt_item',
				header: lang['ext_value'],
				dataIndex: 'Value',
				width: 60,
				editor: new Ext.form.TextArea({
					allowBlank: false
				}),
				renderer: function(val){
					return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
				}
			}
		]
	});
	
	$('input#search_button').click(function(){
		current_lang_id = false;
		
		var phrase = $('#phrase').val();
		var search_lang= $('#in_language').val();
		var criteria = $('#in_value').is(':checked') ? 'in_value' : 'in_key';
		
		if ( phrase != '' )
		{
			var search = new Array();
			search.push( new Array('action', 'search') );
			search.push( new Array('criteria', criteria) );
			search.push( new Array('phrase', phrase) );
			search.push( new Array('lang_code', search_lang) );
			
			phrasesGrid.filters = search;
			
			if ( !phrasesGridPush )
			{
				phrasesGrid.init();
				grid.push(phrasesGrid.grid)
				phrasesGridPush = true;
			}
			else
			{
				phrasesGrid.reload();
			}
		}
	});
});

var phrasesGridPush = false;
var current_lang_id = false;
var phrasesManager = function(id){
	if ( current_lang_id != id )
	{
		phrasesGrid.filters = new Array();
		phrasesGrid.filters[0] = ['lang_id', id];
		current_lang_id = id;
		
		if ( !phrasesGridPush )
		{
			phrasesGrid.init();
			grid.push(phrasesGrid.grid)
			phrasesGridPush = true;
		}
		else
		{
			phrasesGrid.resetPage();
			phrasesGrid.reload();
		}
	}
};

{/literal}
//]]>
</script>
<!-- phrases grid end -->

{/if}

<!-- android languages tpl end -->