<!-- weather forecast listing | responsive -->

{if $config.weatherForecast_position != 'top' && $config.weatherForecast_position != 'bottom'}
	{assign var='wf_group_exist' value=false}
	{foreach from=$listing item='wf_form'}
		{if $wf_form.Group_ID == $config.weatherForecast_position}
			{assign var='wf_group_exist' value=true}
			{assign var='wf_js_code' value='wf_position = '|cat:$wf_form.ID|cat:';'}
		{/if}
	{/foreach}
{/if}

<div id="wf_area_main" class="hide">
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='weather_forecast' name='<span>'|cat:$lang.weatherForecast_weather_foreacst|cat:'</span>'}
		
	{if $config.weatherForecast_woeid}
		<div class="text-notice align-center" id="wf_area_loading">{$lang.weatherForecast_loading}</div>
		<div class="two-inline left clearfix hide" id="wf_area_data">
			<div id="wf_area_cond">
				<div class="caption">{$lang.weatherForecast_cur_cond}</div>
				<div class="cur_img"></div>
				<div class="cur_cond"></div>
			</div>
			<div>
				<div class="caption">{$lang.weatherForecast_foreacst}</div>
				<div id="wf_area_forecast"></div>
			</div>
		</div>
	{else}
		<span class="text-notice">{$lang.weatherForecast_no_woeid}</span>
	{/if}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
</div>

{if $config.weatherForecast_woeid}
	<script type="text/javascript">//<![CDATA[
	var wf_mobile_mode = {if $smarty.const.RL_MOBILE === true}true{else}false{/if};
	var wf_la_woeid = {if $listing_data.WOEID}'{$listing_data.WOEID}'{else}false{/if};
	var wf_la_not_found = '{$lang.weatherForecast_no_wf_la}';
	var wf_weather_in = '{$lang.weatherForecast_weather_in}';

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

		$.getJSON(wf_la_request, {
				mode: 'wf',
				w: wf_la_woeid,
				u: wf_la_unit == 'Celsius' ? 'c' : 'f'
			},
			function(response){
				if ( !response['data']['location'] ) {
					$('#wf_area_loading').html(wf_la_not_found);
					return false;
				}
				
				var data = response['data'];
				var unit = wf_la_unit == 'Celsius' ? 'C' : 'F';
				unit = '&deg;'+ unit;
				
				/* build title */
				var location = data['location'];
				var title = location['city'] +", "+ location[location['region'] != '' && location['city'] != '' ? 'region' : 'country'];
				$('#fs_weather_forecast > header > span:last').html(wf_weather_in.replace('{location}', title));
				
				/* build current conditions */
				var cur_cond = data['condition'];
				var conditions = wf_la_conditions[cur_cond['code']] +', <b>'+ cur_cond['temp'] +'</b> '+ unit;
				$('#wf_area_cond div.cur_cond').html(conditions);
				
				/* set wether sign */
				if ( cur_cond['code'] != '3200' ) {
					var image = '<img alt="'+ wf_la_conditions[cur_cond['code']] +'" title="'+ wf_la_conditions[cur_cond['code']] +'" src="'+ wf_la_url +'signs/'+ cur_cond['code'] +'.png" />';
					$('#wf_area_cond div.cur_img').html(image);
				}
				
				/* set forecast */
				var forecast = data['forecast'];
				var forecast_out = '<ul>';
				
				for ( var i=0; i<forecast.length; i++ ) {
					forecast_out += '<li> \
										<div class="day" title="'+ forecast[i]['date'] +'">'+ $.datepicker.regional[wf_la_lang_code].dayNamesShort[wf_la_days[forecast[i]['day'].toLowerCase()]] +'</div> \
										<div class="icon"><img style="width: 50px" alt="'+ wf_la_conditions[forecast[i]['code']] +'" title="'+ wf_la_conditions[forecast[i]['code']] +'" src="'+ wf_la_url +'signs/'+ forecast[i]['code'] +'.png" /></div> \
										<div class="forecast"> \
											<b>'+ wf_la_conditions[forecast[i]['code']] +'</b> \
											<div class="field"> \
											'+wf_la_high+' '+ forecast[i]['high'] +' '+ unit +', '+wf_la_low+' '+ forecast[i]['low'] +' '+ unit +' \
											</div> \
										</div> \
									</li>';
				}
				forecast_out += '</ul>';
				
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

		$.getJSON(wf_request, {
				mode: 'query',
				query: query,
				direct: direct
			},
			function(response) {
				if ( response && response['data']['woeid'] ) {
					wf_la_woeid = response['data']['woeid'];
					
					if ( wf_la_woeid ) {
						wf_la_init();
						$('#wf_area_main').show();
						
						xajax_saveWoeid(wf_la_woeid, wf_listing_id);
					}
				}
				else {
					$('#wf_area_loading').html(wf_la_not_found);
					return false;
				}
			}
		);

		{/literal}
	{/if}

	//]]>
	</script>
{/if}

<script type="text/javascript">
var wf_position = '{$config.weatherForecast_position}';
{$wf_js_code}
{if isset($wf_group_exist) && $wf_group_exist === false}wf_position = 'top';{/if}

{literal}

	$(document).ready(function(){
		if ( wf_position == 'bottom' ) {
			$('#area_listing > div.content-padding').append($('#wf_area_main'));
		}
		else if ( wf_position == 'top' ) {
			$('#wf_area_main').show();
		}
		else {
			$('#fs_'+wf_position).after($('#wf_area_main'));
		}
		
		$('#wf_area_main').show();
	});

{/literal}
</script>

<!-- weather forecast listing | responsive end -->