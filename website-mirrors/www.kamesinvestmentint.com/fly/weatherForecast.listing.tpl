<!-- weather forecast listing -->

{if $smarty.const.RL_MOBILE === true}
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.ui.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/datePicker/i18n/ui.datepicker-{$smarty.const.RL_LANG_CODE|lower}.js"></script>
{/if}

{if $config.weatherForecast_position != 'top' && $config.weatherForecast_position != 'bottom'}
	{assign var='wf_group_exist' value=false}
	{foreach from=$listing item='wf_form'}
		{if $wf_form.Group_ID == $config.weatherForecast_position}
			{assign var='wf_group_exist' value=true}
			{assign var='wf_js_code' value='wf_position = '|cat:$wf_form.ID|cat:';'}
		{/if}
	{/foreach}
{/if}

<div id="wf_move" class="hide">
	<div id="wf_area_main" class="hide{if $smarty.const.RL_MOBILE === true} padding{/if}">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='weather_forecast' name=$lang.weatherForecast_weather_foreacst}
			
		{if $config.weatherForecast_woeid}
			<div class="info" id="wf_area_loading" style="text-align: center;">{$lang.weatherForecast_loading}</div>
			<div class="hide" id="wf_area_data">
				<div id="wf_area_loc" style="text-align: left;font-weight: bold;"></div>
				<table class="sTable listing_group">
				<tr>
					<td valign="top" style="width: 160px;">
						<div id="wf_area_cond" class="dark">
							{$lang.weatherForecast_cur_cond}
							<div class="cur_img"></div>
							<div class="cur_cond" style="padding: 0 0 5px;font-size: 15px;"></div>
						</div>
					</td>
					{if $smarty.const.RL_MOBILE === true}
					</tr><tr>
					{/if}
					<td valign="top">
						<div class="dark">
							{$lang.weatherForecast_foreacst}
							<div id="wf_area_forecast" style="padding: 2px 0 0 0;"></div>
						</div>
					</td>
				</tr>
				</table>
			</div>

			<script type="text/javascript">//<![CDATA[
			var wf_mobile_mode = {if $smarty.const.RL_MOBILE === true}true{else}false{/if};
			var wf_la_woeid = {if $listing_data.WOEID}'{$listing_data.WOEID}'{else}false{/if};
			var wf_la_not_found = '{$lang.weatherForecast_no_wf_la}';
	
			{literal}
			var wf_la_init = function()
			{
				{/literal}
				var wf_la_unit = '{$config.weatherForecast_la_units}';
				var wf_la_request = wf_mobile_mode ? rlUrlRoot +'plugins/weatherForecast/request.php' : '{$smarty.const.RL_PLUGINS_URL}weatherForecast/request.php';
				var wf_la_url = '{$smarty.const.RL_PLUGINS_URL}weatherForecast/';
				var wf_la_high = "{$lang.weatherForecast_high}";
				var wf_la_low = "{$lang.weatherForecast_low}";
				var wf_la_lang_code = "{if $smarty.const.RL_LANG_CODE == 'en'}en-GB{else}{$smarty.const.RL_LANG_CODE|lower}{/if}";
	
				var wf_la_days = new Array();
				wf_la_days['sun'] = 0;
				wf_la_days['mon'] = 1;
				wf_la_days['tue'] = 2;
				wf_la_days['wed'] = 3;
				wf_la_days['thu'] = 4;
				wf_la_days['fri'] = 5;
				wf_la_days['sat'] = 6;
				
				var wf_la_conditions = new Array();
	
				{section loop=48 name='wf_loop' max=48}
					{assign var='wf_index' value=$smarty.section.wf_loop.iteration-1}
					{assign var='wf_pkey' value='weatherForecast_cond_'|cat:$wf_index}
					wf_la_conditions[{$wf_index}] = "{$lang.$wf_pkey}";
				{/section}
				
				{literal}
	
				$.getJSON(wf_la_request,
					{
						mode: 'wf',
						w: wf_la_woeid,
						u: wf_la_unit == 'Celsius' ? 'c' : 'f'
					},
					function(response){
						if ( !response['data']['location'] )
						{
							$('#wf_area_loading').html(wf_la_not_found);
							return false;
						}
						
						var data = response['data'];
						var unit = wf_la_unit == 'Celsius' ? 'C' : 'F';
						unit = '&deg;'+ unit;
						
						/* build title */
						var location = data['location'];
						
						if ( location['region'] != '' && location['city'] != '' )
						{
							var title = location['city'] +", "+ location['region'];
						}
						else
						{
							var title = location['city'] +", "+ location['country'];
						}
						$('#wf_area_loc').html(title);
						
						/* build current conditions */
						var cur_cond = data['condition'];
						var conditions = wf_la_conditions[cur_cond['code']] +', <b>'+ cur_cond['temp'] +'</b> '+ unit;
						$('#wf_area_cond div.cur_cond').html(conditions);
						
						/* set wether sign */
						if ( cur_cond['code'] != '3200' )
						{
							var image = '<img alt="'+ wf_la_conditions[cur_cond['code']] +'" title="'+ wf_la_conditions[cur_cond['code']] +'" src="'+ wf_la_url +'signs/'+ cur_cond['code'] +'.png" />';
							$('#wf_area_cond div.cur_img').html(image);
						}
						
						/* set forecast */
						var forecast = data['forecast'];
						var forecast_out = '<table style="font-size: 12px;">';
						
						for ( var i=0; i<forecast.length; i++ )
						{
							forecast_out += '<tr> \
												<td style="width: 40px;text-transform: capitalize;" title="'+ forecast[i]['date'] +'">'+ $.datepicker.regional[wf_la_lang_code].dayNamesShort[wf_la_days[forecast[i]['day'].toLowerCase()]] +'</td> \
												<td valign="top" style="padding: 0 8px 0 0;"><img style="width: 50px" alt="'+ wf_la_conditions[forecast[i]['code']] +'" title="'+ wf_la_conditions[forecast[i]['code']] +'" src="'+ wf_la_url +'signs/'+ forecast[i]['code'] +'.png" /></td> \
												<td valign="top"> \
													<b>'+ wf_la_conditions[forecast[i]['code']] +'</b> \
													<div class="field" style="font-size: 11px;padding: 0 0 10px;"> \
													'+wf_la_high+' '+ forecast[i]['high'] +' '+ unit +', '+wf_la_low+' '+ forecast[i]['low'] +' '+ unit +' \
													</div> \
												</td> \
											</tr>';
						}
						forecast_out += '</table>';
						
						$('#wf_area_forecast').html(forecast_out);
						
						/* open block */
						$('#wf_area_loading').fadeOut('normal', function(){
							$('#wf_area_data').fadeIn('normal');
						});
					}
				);
			}
			
			{/literal}
			
			{if $listing_data.WOEID}
				{literal}
				
				$(document).ready(function(){
					wf_la_init();
				});
				
				{/literal}
			{else}
				var wf_request = wf_mobile_mode ? rlUrlRoot +'plugins/weatherForecast/request.php' : '{$smarty.const.RL_PLUGINS_URL}weatherForecast/request.php';
				var wf_listing_id = '{$listing_data.ID}';
				var query = '{if $listing_data.Loc_latitude && $listing_data.Loc_longitude}{$listing_data.Loc_latitude},{$listing_data.Loc_longitude}{else}{$wf_search}{/if}';
				var direct = {if $listing_data.Loc_latitude && $listing_data.Loc_longitude}1{else}0{/if};
				{literal}
			
				$.getJSON(wf_request,
					{
						mode: 'query',
						query: query,
						direct: direct
					},
					function(response){
						if ( response && response['data']['woeid'] )
						{
							wf_la_woeid = response['data']['woeid'];
							
							if ( wf_la_woeid )
							{
								wf_la_init();
								$('#wf_area_main').show();
								
								xajax_saveWoeid(wf_la_woeid, wf_listing_id);
							}
						}
						else
						{
							$('#wf_area_loading').html(wf_la_not_found);
							return false;
						}
					}
				);
	
				{/literal}
			{/if}
			
			//]]>
			</script>
		{else}
			<span class="grey_middle">{$lang.weatherForecast_no_woeid}</span>
		{/if}
		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
	</div>
</div>

<script type="text/javascript">
var wf_position = '{$config.weatherForecast_position}';
{$wf_js_code}
{if isset($wf_group_exist) && $wf_group_exist === false}wf_position = 'top';{/if}

{literal}

	$(document).ready(function(){
		if ( wf_position == 'bottom' )
		{
			$('#area_listing').append($('#wf_move').html());
			$('#wf_move').remove();
		}
		else if ( wf_position == 'top' )
		{
			$('#wf_move').show();
		}
		else
		{
			$('#fs_'+wf_position).after($('#wf_move').html());
			$('#wf_move').remove();
		}
		
		$('#wf_area_main').show();
	});

{/literal}
</script>

<!-- weather forecast listing end -->
