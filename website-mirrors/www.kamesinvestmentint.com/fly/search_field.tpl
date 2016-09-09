<!-- show listing field values (search mode) -->	

<tr>
	<td><span class="fLable">{$field.name}:</span></td>
	<td style="padding-left: 4px;">
	{if $field.Type == 'text'}
		{$field.value}
	{elseif $field.Type == 'number'}
		{if isset($field.value.distance)}
			<b>{$field.value.distance}</b> {$lang.zip_miles} {$lang.from} <b>{$field.value.zip}</b> {$lang.zip_zip}
		{else}
			<span class="item_value">{$lang.from}</span> {if $field.value.from}{$field.value.from} {$field.value.df}{else}{$lang.any}{/if} <span class="item_value">{$lang.to}</span> {if $field.value.to}{$field.value.to} {$field.value.df}{else}{$lang.any}{/if}
		{/if}
	{elseif $field.Type == 'date'}
		{if $field.Default == 'single'}
			<span class="item_value">{$lang.from}</span> {if !empty($field.value.from)}{$field.value.from|date_format:$smarty.const.RL_DATE_FORMAT}{else}{$lang.any}{/if} <span class="item_value">{$lang.to}</span> {if !empty($field.value.to)}{$field.value.to|date_format:$smarty.const.RL_DATE_FORMAT}{else}{$lang.any}{/if}
		{elseif $field.Default == 'multi'}
			{$field.value|date_format:$smarty.const.RL_DATE_FORMAT}
		{/if}
	{elseif $field.Type == 'mixed'}
		<span class="item_value">{$lang.from}</span> {if $field.value.from}{$field.value.from} {$field.value.df}{else}{$lang.any}{/if} <span class="item_value">{$lang.to}</span> {if $field.value.to}{$field.value.to} {$field.value.df}{else}{$lang.any}{/if} <br />{if empty($field.value.df)}<span class="item_value">{$lang.unit}</span> {$lang.any}{/if}
	{elseif $field.Type == 'price'}
		<span class="item_value">{$lang.from}</span> {if $field.value.from}{$field.value.currency}{str2money string=$field.value.from}{else}{$lang.any}{/if} <span class="item_value">{$lang.to}</span> {if $field.value.to}{$field.value.currency}{str2money string=$field.value.to}{else}{$lang.any}{/if} <br />{if empty($field.value.currency)}<span class="item_value">{$lang.currency}</span> {$lang.any}{/if}
	{elseif $field.Type == 'unit'}
		<span class="item_value">{$lang.from}</span> {if $field.value.from}{$field.value.from} {$field.value.unit}{else}{$lang.any}{/if} <span class="item_value">{$lang.to}</span> {if $field.value.to}{$field.value.to} {$field.value.unit}{else}{$lang.any}{/if} <br />{if empty($field.value.unit)}<span class="item_value">{$lang.unit}</span> {$lang.any}{/if}
	{elseif $field.Type == 'bool'}
		{if $field.value}{$lang.yes}{else}{$lang.no}{/if}
	{elseif $field.Type == 'select'}
		{if $field.Condition == 'years'}
			<span class="item_value">{$lang.from}</span> {if $field.value.from}{$field.value.from}{else}{$lang.any}{/if} <span class="item_value">{$lang.to}</span> {if $field.value.to}{$field.value.to}{else}{$lang.any}{/if}
		{else}
			{$field.value}
		{/if}
	{elseif $field.Type == 'radio'}
		{$field.value}
	{elseif $field.Type == 'checkbox'}
		{$field.value}
	{/if}
	</td>
</tr>
	
<!-- show listing field values (search mode) end -->