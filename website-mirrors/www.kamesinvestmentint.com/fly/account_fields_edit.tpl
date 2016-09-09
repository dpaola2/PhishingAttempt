<!-- account fields edit -->

<table class="sTable">
{foreach from=$fields item='field' key='fkey'}
	{assign var='fKey' value=$field.Key}
	{assign var='fVal' value=$smarty.post.f}

	<tr>
		<td class="td_splitter" style="width: 180px;height: 34px;">
		{$field.name}
		{if $field.Required}
			<span class="red">*</span>
		{/if}
		{if !empty($field.description)}
			<img alt="" class="qtip" title="{$field.description}" id="fd_{$field.Key}" src="{$rlTplBase}img/qtip.gif" style="vertical-align: text-top;" />
		{/if}
		</td>
		<td style="width: 10px;"></td>
		<td>
			{if $field.Type == 'text'}
				<input style="width: 250px;" class="margin" type="text" name="f[{$field.Key}]" maxlength="{if $field.Values != ''}{$field.Values}{else}255{/if}" value="{if $fVal.$fKey}{$fVal.$fKey}{else}{if $field.current}{$field.current}{else}{$field.default}{/if}{/if}" />
			{elseif $field.Type == 'textarea'}
				<textarea class="resizable" rows="5" cols="" name="f[{$field.Key}]" id="textarea_{$field.Key}">{if $fVal.$fKey}{$fVal.$fKey}{else}{if $field.current}{$field.current}{else}{$field.default}{/if}{/if}</textarea>
			{elseif $field.Type == 'number'}
				<input class="numeric margin" type="text" name="f[{$field.Key}]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" value="{if $fVal.$fKey}{$fVal.$fKey}{else}{if $field.current}{$field.current}{else}{$field.default}{/if}{/if}" />
			{elseif $field.Type == 'date'}
				{if $field.Default == 'single'}
					<input type="text" id="date_{$field.Key}" name="f[{$field.Key}]" maxlength="10" style="width: 70px;float: left;" value="{if $fVal.$fKey}{$fVal.$fKey}{else}{$field.current}{/if}" />
					<div class="clear"></div>
					<script type="text/javascript">
					{literal}
					$(document).ready(function(){
						$('#date_{/literal}{$field.Key}{literal}').datepicker({showOn: 'button', buttonImage: '{/literal}{$rlTplBase}{literal}img/calendar.png', buttonText: '{/literal}{$lang.dp_choose_date}{literal}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
					});
					{/literal}
					</script>
				{elseif $field.Default == 'multi'}
					<table>
					<tr>
						<td><label for="date_{$field.Key}_from">{$lang.from}</label></td>
						<td style="width: 120px;"><input class="text" type="text" id="date_{$field.Key}_from" name="f[{$field.Key}][from]" maxlength="10" style="width: 70px;float: left;" value="{if $fVal.$fKey.from}{$fVal.$fKey.from}{else}{$field.from}{/if}" /></td>
						<td><label for="date_{$field.Key}_to">{$lang.to}</label></td>
						<td style="width: 120px;"><input class="text" type="text" id="date_{$field.Key}_to" name="f[{$field.Key}][to]" maxlength="10" style="width: 70px;float: left;" value="{if $fVal.$fKey.to}{$fVal.$fKey.to}{else}{$field.to}{/if}" /></td>
					</tr>
					</table> 
					<script type="text/javascript">
					{literal}
					$(document).ready(function(){
						$('#date_{/literal}{$field.Key}{literal}_from').datepicker({showOn: 'button', buttonImage: '{/literal}{$rlTplBase}{literal}img/calendar.png', buttonText: '{/literal}{$lang.dp_choose_date}{literal}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
						$('#date_{/literal}{$field.Key}{literal}_to').datepicker({showOn: 'button', buttonImage: '{/literal}{$rlTplBase}{literal}img/calendar.png', buttonText: '{/literal}{$lang.dp_choose_date}{literal}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
					});
					{/literal}
					</script>
				{/if}
			{elseif $field.Type == 'mixed'}
				<input class="numeric float" type="text" name="f[{$field.Key}][value]" size="8" maxlength="15" value="{if $fVal.$fKey.value}{$fVal.$fKey.value}{else}{$field.value}{/if}" style="width: 70px;" />
				<select class="float lm" name="f[{$field.Key}][df]" style="width: 60px;">
					{if !empty($field.Condition)}
						{assign var='df_condition' value=$field.Condition}
						{assign var='df_source' value=$df.$df_condition}
					{else}
						{assign var='df_source' value=$field.Values}
					{/if}
					{foreach from=$df_source item='df_item'}
						<option value="{$df_item.Key}" {if $fVal.$fKey.df}{if $fVal.$fKey.df == $df_item.Key}selected="selected"{/if}{else}{if $field.df == $df_item.Key}selected="selected"{/if}{/if}>{$df_item.name}</option>
					{/foreach}
				</select>
			{elseif $field.Type == 'unit'}
				<input class="numeric float" type="text" name="f[{$field.Key}][value]" size="8" maxlength="15" {if $fVal.$fKey.value}value="{$fVal.$fKey.value}"{/if} style="width: 70px;" />
				<select class="float lm" name="f[{$field.Key}][unit]" style="width: 60px;">
					{foreach from=$df.unit item='unit_item'}
						<option value="{$unit_item.Key}" {if $unit_item.Key == $fVal.$fKey.unit}selected="selected"{/if}>{$unit_item.name}</option>
					{/foreach}
				</select>
			{elseif $field.Type == 'bool'}
				{if $fVal.$fKey == 'on'}
					{assign var='bool_on' value='checked="checked"'}
				{elseif $fVal.$fKey == 'off'}
					{assign var='bool_off' value='checked="checked"'}
				{else}
					{if $field.current == 'on'}
						{assign var='bool_on' value='checked="checked"'}
					{elseif $field.current == 'off'}
						{assign var='bool_off' value='checked="checked"'}
					{else}	
						{if $field.Default == '1'}
							{assign var='bool_on' value='checked="checked"'}
						{elseif $field.Default == '0'}
							{assign var='bool_off' value='checked="checked"'}
						{/if}
					{/if}
				{/if}
				<input id="{$field.Key}_1" type="radio" value="on" name="f[{$field.Key}]" {$bool_on} /> <label for="{$field.Key}_1" class="label">{$lang.yes}</label>
				<input id="{$field.Key}_0" type="radio" value="off" name="f[{$field.Key}]" {$bool_off} /> <label for="{$field.Key}_0" class="label">{$lang.no}</label>
				{assign var='bool_on' value=''}
				{assign var='bool_off' value=''}
			{elseif $field.Type == 'select'}
				<select class="margin" name="f[{$field.Key}]">
					<option value="0">{$lang.select}</option>
		
					{foreach from=$field.Values item='option' key='key'}
						{if $field.Condition}
							{assign var='key' value=$option.Key}
						{/if}
						<option value="{if $field.Condition}{$option.Key}{else}{$key}{/if}" {if $fVal.$fKey}{if $fVal.$fKey == $key}selected="selected"{/if}{else}{if $field.current == $key || $option.Default}selected="selected"{/if}{/if}>{$option.name}</option>
					{/foreach}
				</select>
			{elseif $field.Type == 'checkbox'}
				{assign var='fDefault' value=$field.Default}
				<input type="hidden" name="f[{$field.Key}][0]" value="0" />
				<table>
				<tr>
				{foreach from=$field.Values item='option' key='key' name='checkboxF'}
					<td {if $smarty.foreach.checkboxF.total > 5}style="width: 33%"{/if}>
						<input type="checkbox" id="{$field.Key}_{$key}" value="{$key}" {if is_array($fVal.$fKey)}{foreach from=$fVal.$fKey item='chVals'}{if $chVals == $key}checked="checked"{/if}{/foreach}{else}{if is_array($field.current)}{foreach from=$field.current item='chCurr'}{if $chCurr == $key}checked="checked"{/if}{/foreach}{else}{foreach from=$field.Default item='chDef'}{if $chDef == $key}checked="checked"{/if}{/foreach}{/if}{/if} name="f[{$field.Key}][{$key}]" /> <label for="{$field.Key}_{$key}" class="label">{$option.name}</label>
					</td>
					{if $smarty.foreach.checkboxF.iteration%3 == 0}
					</tr>
					<tr>
					{/if}
				{/foreach}
				</tr>
				</table>
			{elseif $field.Type == 'radio'}
				<input type="hidden" value="0" name="f[{$field.Key}]" />
				<table>
				<tr>
				{foreach from=$field.Values item='option' key='key' name='radioF'}
					<td {if $smarty.foreach.radioF.total > 5}style="width: 33%"{/if}>
						<input type="radio" id="{$field.Key}_{$key}" value="{$key}" name="f[{$field.Key}]" {if $fVal.$fKey}{if $fVal.$fKey == $key}checked="checked"{/if}{else}{if $field.current}{if $field.current == $key}checked="checked"{/if}{else}{if $field.Default == $key}checked="checked"{/if}{/if}{/if} /> <label for="{$field.Key}_{$key}" class="label">{$option.name}</label>
					</td>
					{if $smarty.foreach.radioF.iteration%3 == 0}
					</tr>
					<tr>
					{/if}
				{/foreach}
				</tr>
				</table>
			{elseif $field.Type == 'image' || $field.Type == 'file'}
				{assign var='field_type' value=$field.Default}
				
				{assign var='file_name' value=false}
				{if $field.current}
					{assign var='file_name' value=$field.current}
				{elseif $sPost.f[$field.Key]}
					{assign var='file_name' value=$sPost.f[$field.Key]}
				{/if}
				
				{if $file_name}
					<div id="{$field.Key}_file">
						{if $field.Type == 'image'}
							<img align="middle" title="{$field.name}" alt="{$field.name}" src="{$smarty.const.RL_URL_HOME}files/{$file_name}" />
						{/if}
						<div class="clear"></div>
						<div class="grey_area" style="margin-top: 5px;float: left;">
							{if $field.Type == 'file'}
							<a class="green_10" href="{$smarty.const.RL_URL_HOME}files/{$file_name}">{$lang.download}</a>
							<span class="divider">|</span>
							{/if}
							<span onclick='rlConfirm( "{$lang.delete_confirm}", "xajax_delAccountFile", Array("\"{$smarty.get.account}\"", "\"{$field.Key}\"", "\"{$field.Key}_file\""), "{$field.Key}_loading", "smarty" );' class="green_10" title="{$lang.delete}">{$lang.delete}</span>
							<span class="divider">|</span>
							<span onclick="show('{$field.Key}_file_upload');" class="green_10" title="{$lang.manage}">{$lang.manage}</span>
						</div>
						<span class="loader" id="{$field.Key}_loading" style="margin-top: 11px;"></span>
					</div>
				{else}
					{getTmpFile field=$field.Key id=$field.Key}
				{/if}
				<div id="{$field.Key}_file_upload" {if !empty($file_name)}class="hide clear"{/if}>
					<div style="padding: 0 0 5px 0;">
						<input type="hidden" name="f[{$field.Key}]" value="{$field.current}" id="{$field.Key}_file_tmp" />
						<input class="file" type="file" name="{$field.Key}" />{if $field.Type == 'file' && !empty($field.Default)}<span class="grey_small"> <em>{$l_file_types.$field_type.name} (.{$l_file_types.$field_type.ext|replace:',':', .'})</em></span>{/if}
					</div>
				</div>
			{/if}
		</td>
	</tr>

{/foreach}
</table>

<!-- account fields edit end -->