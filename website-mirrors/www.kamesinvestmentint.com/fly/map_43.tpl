<!-- location finder map -->

<div id="lf-container" {if $config.locationFinder_position != 'top'} class="hide" {if $config.locationFinder_position == 'bottom'}style="padding-top: 5px;"{/if}{/if}>
	{if $config.locationFinder_position == 'top' || $config.locationFinder_position == 'bottom'}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='lf_fieldset' name=$lang.locationFinder_fieldset_caption}
	{/if}

	<div class="submit-cell">
		<div class="name">{$lang.locationFinder_location} <img src="{$rlTplBase}img/blank.gif" class="qtip" title="{$lang.locationFinder_hint}" /></div>
		<div class="field checkbox-field">
			<div id="lf_map" style="{if $config.locationFinder_map_width}width: {$config.locationFinder_map_width}px;{/if}height: {if $config.locationFinder_map_height}{$config.locationFinder_map_height}{else}250{/if}px;"></div>

			<div class="two-inline left clearfix" style="padding-top: 10px;">
				<div style="padding-top: 6px;">
					<label><input id="lf_use" type="checkbox" name="f[lf][use]" value="1" {if $smarty.post.f.lf.use}checked="checked"{/if} /> {$lang.locationFinder_use_location}</label>
				</div>
				<div style="text-align: {$text_dir_rev};">
					<input style="vertical-align: top;" type="text" id="lf_query" name="f[lf][query]" value="{if $smarty.post.f.lf.query}{$smarty.post.f.lf.query}{else}{if !$config.locationFinder_use_location}{$config.locationFinder_search}{/if}{/if}" />
					<input id="lf_search" class="search" type="button" value="" />
				</div>
			</div>

			<input id="lf_lat" name="f[lf][lat]" type="hidden" value="{$smarty.post.f.lf.lat}" />
			<input id="lf_lng" name="f[lf][lng]" type="hidden" value="{$smarty.post.f.lf.lng}" />
			<input id="lf_zoom" name="f[lf][zoom]" type="hidden" value="{$smarty.post.f.lf.zoom}" />
		</div>
	</div>

	{if $config.locationFinder_position == 'top' || $config.locationFinder_position == 'bottom'}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
	{/if}
</div>

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false{if $smarty.const.RL_LANG_CODE != '' && $smarty.const.RL_LANG_CODE != 'en'}&language={$smarty.const.RL_LANG_CODE|lower}{/if}"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.flmap.js"></script>

<script type="text/javascript">//<![CDATA[
	var lfConfig = new Array();
	lfConfig['geoCoder'] = new google.maps.Geocoder();
	lfConfig['containerPosition'] = '{$config.locationFinder_position}';
	lfConfig['containerPositionType'] = '{$config.locationFinder_type}';
	lfConfig['use_location'] = {if $config.locationFinder_use_location}true{else}false{/if};
	lfConfig['phrase_drag_hint'] = "{$lang.locationFinder_drag_notice}";
	lfConfig['phrase_not_found'] = "{$lang.location_not_found}";
	lfConfig['phrase_your_location'] = "{$lang.locationFinder_your_location}";
	{if $smarty.session.GEOLocationData->Country_name && $config.locationFinder_use_location}
	lfConfig['location'] = "{$smarty.session.GEOLocationData->Country_name}{if $smarty.session.GEOLocationData->Region}, {$smarty.session.GEOLocationData->Region}{/if}{if $smarty.session.GEOLocationData->City}, {$smarty.session.GEOLocationData->City}{/if}";
	{else}
	lfConfig['location'] = "{$config.locationFinder_search}";
	{/if}
	lfConfig['zoom'] = {if $config.locationFinder_map_zoom}{$config.locationFinder_map_zoom}{else}12{/if};
	lfConfig['postLat'] = {if $smarty.post.f.lf.lat}{$smarty.post.f.lf.lat}{else}false{/if};
	lfConfig['postLng'] = {if $smarty.post.f.lf.lng}{$smarty.post.f.lf.lng}{else}false{/if};
	lfConfig['postZoom'] = {if $smarty.post.f.lf.zoom}{$smarty.post.f.lf.zoom}{else}false{/if};
//]]>
</script>
<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}locationFinder/static/lib.js"></script>

<!-- location finder map end -->