<!-- weather forecast block -->

{if $config.weatherForecast_woeid || $config.weatherForecast_use_geo}
	<div class="info" id="wf_block_loading" style="text-align: center;">{$lang.weatherForecast_loading}</div>
	<div class="hide" id="wf_block_data">
		<div id="wf_block_loc" style="text-align: left;font-weight: bold;"></div>
		<div id="wf_block_cond" class="dark">
			{$lang.weatherForecast_cur_cond}
			<div class="cur_img"></div>
			<div class="cur_cond" style="padding: 0 0 8px;font-size: 15px;"></div>
		</div>
		<div id="wf_block_forecast" class="dark" style="padding: 2px 0 0 0;"></div>
	</div>
	
	<script type="text/javascript">//<![CDATA[
	var wf_woeid = "{$config.weatherForecast_woeid}";
	var wf_use_geo = {if $config.weatherForecast_use_geo}true{else}false{/if};
	var wf_sess_geo = {if $smarty.session.wf_woeid}"{$smarty.session.wf_woeid}"{else}false{/if};
	var wf_ipgeo = {if $smarty.session.GEOLocationData->Country_name}"{$smarty.session.GEOLocationData->Country_name}{if $smarty.session.GEOLocationData->City},{$smarty.session.GEOLocationData->City}{/if}"{else}false{/if};
	var wf_unit = "{$config.weatherForecast_wb_units}";
	var wf_not_found = "{$lang.weatherForecast_no_wf}";
	var wf_request = '{$smarty.const.RL_PLUGINS_URL}weatherForecast/request.php';
	var wf_url = '{$smarty.const.RL_PLUGINS_URL}weatherForecast/';
	var wf_high = "{$lang.weatherForecast_high}";
	var wf_low = "{$lang.weatherForecast_low}";
	var wf_lang_code = "{if $smarty.const.RL_LANG_CODE == 'en'}en-GB{else}{$smarty.const.RL_LANG_CODE|lower}{/if}";
	
	var wf_conditions = new Array();

	{section loop=48 name='wf_loop' max=48}
		{assign var='wf_index' value=$smarty.section.wf_loop.iteration-1}
		{assign var='wf_pkey' value='weatherForecast_cond_'|cat:$wf_index}
		wf_conditions[{$wf_index}] = "{$lang.$wf_pkey}";
	{/section}
	
	//]]>
	</script>
	<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}weatherForecast/static/lib.js"></script>
{else}
	<span class="grey_middle">{$lang.weatherForecast_no_woeid}</span>
{/if}

<!-- weather forecast block end -->