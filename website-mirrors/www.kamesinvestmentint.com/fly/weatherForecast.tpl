<!-- weather forecast -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}

	<!-- update location -->
	<form action="" method="post" onsubmit="return wf_update_woeid();">
		<table class="form">
		<tr>
			<td valign="top" colspan="2" class="divider first"><div class="inner">{$lang.weatherForecast_update_location}</div></td>
		</tr>
		<tr>
			<td class="name">{$lang.weatherForecast_location}</td>
			<td class="field">
				<input type="text" id="location" /> <span class="field_description">{$lang.weatherForecast_example}</span>
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="field">
				<input style="margin-top: 0;" type="submit" id="location_submit" value="{$lang.weatherForecast_update}" />
			</td>
		</tr>
		</table>
	</form>

	<script type="text/javascript">
	var wf_loading = "{$lang.weatherForecast_loading}";
	var wf_update = "{$lang.weatherForecast_update}";
	var wf_request = '{$smarty.const.RL_PLUGINS_URL}weatherForecast/request.php';
	var wf_error = '<table cellspacing="0" cellpadding="0" id="error_obj" class="erTable" style="display: table;"><tr><td class="er_corner_top_left"></td><td class="er_corner_top_center"></td><td class="er_corner_top_right"></td></tr><tr><td class="er_middle_left"></td><td class="er_middle_center"><div>[msg]</td><td class="er_middle_right"></td></tr><tr><td class="er_corner_bottom_left"></td><td class="er_corner_bottom_center"></td><td class="er_corner_bottom_right"></td></tr></table>';
	var wf_error_text = "{$lang.weatherForecast_not_found}";
	var wf_notice = '<table cellspacing="0" cellpadding="0" id="notice_obj" class="nTable" style="display: table;"><tr><td class="n_corner_top_left"></td><td class="n_corner_top_center"></td><td class="n_corner_top_right"></td></tr><tr><td class="n_middle_left"></td><td class="n_middle_center"><div>[msg]</td><td class="n_middle_right"></td></tr><tr><td class="n_corner_bottom_left"></td><td class="n_corner_bottom_center"></td><td class="n_corner_bottom_right"></td></tr></table>';
	var wf_notice_text = "{$lang.weatherForecast_updated}";
	{literal}
	
	var wf_update_woeid = function(){
		var query = $('#location').val();
		
		if ( query == '' )
		{
			return false;
		}
		
		$('#location_submit').val(wf_loading).attr('disabled', true);
		
		$.getJSON(wf_request,
			{
				mode: 'query',
				query: query
			},
			function(response){
				/* update woeid */
				if ( response['data']['woeid'] )
				{
					xajax_saveWoeid(response['data']['woeid']);
					printMessage('notice', wf_notice_text);
				}
				/* display error */
				else
				{
					var msg = wf_error.replace('[msg]', wf_error_text.replace('[query]', query));
					printMessage('error', msg);
				}
				
				$('#location_submit').val(wf_update).attr('disabled', false);
			}
		);
		
		return false;
	}
	
	{/literal}
	</script>
	<!-- update location end -->
	
	<!-- weather position settings -->
	<table class="form" style="margin-top: 20px;">
	<tr>
		<td valign="top" colspan="2" class="divider first"><div class="inner">{$lang.weatherForecast_listing_settings}</div></td>
	</tr>
	<tr>
		<td class="name">{$lang.weatherForecast_area_position}</td>
		<td class="field">
			<select id="weatherForecast_position">
				<option value="top">{$lang.weatherForecast_form_top}</option>
				<option value="bottom" {if $config.weatherForecast_position == 'bottom'}selected="selected"{/if}>{$lang.weatherForecast_form_bottom}</option>
				<optgroup style="font-size: 11px;font-style: normal;margin-left: 3px;" label="{$lang.weatherForecast_place_after_group}">
					{foreach from=$groups item='group'}
						<option {if $config.weatherForecast_position == $group.ID}selected="selected"{/if} style="font-size: 13px;" value="{$group.ID}">{$group.name}</option>
					{/foreach}
				</optgroup>
			</select>
		</td>
	</tr>
	
	<tr>
		<td></td>
		<td class="field">
			<input style="margin-top: 0;" id="wf_save_poss" type="button" value="{$lang.save}" />
		</td>
	</tr>
	</table>
	<!-- weather position settings end -->
	
	<script type="text/javascript">
	{literal}
	
	$('#wf_save_poss').click(function(){
		$(this).val(wf_loading).attr('disabled', true);
		xajax_savePosition($('#weatherForecast_position').val());
	});
	
	{/literal}
	</script>
	
	<!-- weather position settings -->
	<table class="form" style="margin-top: 20px;">
	<tr>
		<td valign="top" colspan="2" class="divider first"><div class="inner">{$lang.weatherForecast_fields_mapping}</div></td>
	</tr>
	<tr>
		<td class="name">{$lang.weatherForecast_mapping_country}</td>
		<td class="field">
			<select id="weatherForecast_mapping_country">
				<option value="">{$lang.select}</option>
				{foreach from=$wFields item='wField'}
				<option value="{$wField.Key}" {if $config.weatherForecast_mapping_country == $wField.Key}selected="selected"{/if}>{$wField.name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td class="name">{$lang.weatherForecast_mapping_region}</td>
		<td class="field">
			<select id="weatherForecast_mapping_region">
				<option value="">{$lang.select}</option>
				{foreach from=$wFields item='wField'}
				<option value="{$wField.Key}" {if $config.weatherForecast_mapping_region == $wField.Key}selected="selected"{/if}>{$wField.name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td class="name">{$lang.weatherForecast_mapping_city}</td>
		<td class="field">
			<select id="weatherForecast_mapping_city">
				<option value="">{$lang.select}</option>
				{foreach from=$wFields item='wField'}
				<option value="{$wField.Key}" {if $config.weatherForecast_mapping_city == $wField.Key}selected="selected"{/if}>{$wField.name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	
	<tr>
		<td></td>
		<td class="field">
			<input style="margin-top: 0;" id="wf_save_mapping" type="button" value="{$lang.save}" />
		</td>
	</tr>
	</table>
	<!-- weather position settings end -->
	
	<script type="text/javascript">
	{literal}
	
	$('#wf_save_mapping').click(function(){
		$(this).val(wf_loading).attr('disabled', true);
		xajax_saveMapping(
			$('#weatherForecast_mapping_country').val(),
			$('#weatherForecast_mapping_region').val(),
			$('#weatherForecast_mapping_city').val()
		);
	});
	
	{/literal}
	</script>
	
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

<!-- weather forecast end -->