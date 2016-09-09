<!-- multiFields geo filter block -->
<div class="mf-gf">
	{if $geo_format}
		{if $pageInfo.Geo_exclude}
			{assign var="clean_url" value=$smarty.const.RL_URL_HOME|cat:"[geo_url]"}
		{else}
			{assign var="clean_url" value=$geo_filter_data.clean_url}
		{/if}

		{if $config.mf_geo_block_autocomplete}
			<input id="geo_autocomplete" type="text" maxlength="255" value="{$lang.mf_geo_type_location}" />
			<input type="hidden" id="ac_geo_path" value="" />
			<script type="text/javascript">//<![CDATA[
				{if $config.mf_geo_subdomains && $geo_filter_data.geo_sub}
					var ac_geo_php = '{$geo_filter_data.geo_sub_home}plugins/multiField/autocomplete.inc.php';
				{else}
					var ac_geo_php = '{$smarty.const.RL_PLUGINS_URL}multiField/autocomplete.inc.php';
				{/if}

				{if $config.mf_geo_subdomains}					
					var geo_clean_url = '{$geo_filter_data.clean_url_sub}';
					var geo_sub = true;
				{else}
					var geo_clean_url = '{$clean_url}';
					var geo_sub = false;
				{/if}

				var geo_default_phrase = "{$lang.mf_geo_type_location}";

				{literal}
				$(document).ready(function(){
					$('input#geo_autocomplete').vsGeoAutoComplete();
	
					$('input#geo_autocomplete').keypress(function(event){
						var ENTER = 13;
	
						if ( event.which == ENTER )
						{
							var path = $('#ac_geo_path').val();
							if( path && path != 0)
							{
								{/literal}
									location.href= '{$clean_url}'.replace('[geo_url]', path);
								{literal}
							}	
						}	
					}).focus(function(){
						if ( $(this).val() == geo_default_phrase )
						{
							$(this).val('');
						}
					}).blur(function(){
						if ( $(this).val() == '' )
						{
							$(this).val(geo_default_phrase);
						}
					});
				});
				{/literal}
				//]]>
			</script>
		{/if}
	
		{if $config.mf_geo_block_list}
			<div class="dark gf-caption">{$lang.mf_geo_choose_location}</div>
			
			{if $geo_filter_data.location}
				{if $config.mf_geo_multileveled}
					<img class="tile" src="{$rlTplBase}img/blank.gif" alt="" />
					{foreach from=$geo_filter_data.location item="item" name="curLocLoop"}
						{$item.name}{if !$smarty.foreach.curLocLoop.last}, {/if}
					{/foreach}
					{assign var="reset_path" value=$clean_url|replace:"[geo_url]":""}
					<a href="{$reset_path}?reset_location" title="{$lang.mf_geo_remove}"><img src="{$rlTplBase}img/blank.gif" class="gf-remove" alt="" /></a>
					(<a href="javascript:void(0)" id="change_loc_link">{$lang.change}</a>)
				{else}
					<ul class="gf-list-tile">
						{foreach from=$geo_filter_data.location item="item" name="curLocLoop" key="key"}
							<li>
								{$item.name}
								{if $config.mf_geo_subdomains}
									{if $key == 1}
										{assign var="prev_path" value=$item.prev_path|trim:"/"}
										{assign var="prev_path" value=$geo_filter_data.clean_url_sub|replace:"[geo_sub]":$prev_path|replace:"[geo_url]":""}
									{elseif $key == 0}
										{assign var="prev_path" value=$clean_url|replace:"[geo_url]":""}
									{else}
										{assign var="arr" value="/"|explode:$item.prev_path}
										{assign var="clean_url" value=$geo_filter_data.clean_url_sub|replace:"[geo_sub]":$arr.0}
										{assign var="firstpath" value=$arr.0|cat:"/"}
										{assign var="prev_path" value=$item.prev_path|replace:$firstpath:""}
										{assign var="prev_path" value=$clean_url|replace:"[geo_url]":$prev_path}
									{/if}
								{else}
									{assign var="prev_path" value=$clean_url|replace:"[geo_url]":$item.prev_path}
								{/if}								
								
								<a href="{$prev_path}{if !$item.prev_path}?reset_location{/if}" title="{$lang.mf_geo_remove}"><img src="{$rlTplBase}img/blank.gif" class="gf-remove" alt="" /></a>
							</li>
						{/foreach}
					</ul>
				{/if}
			{/if}

			{if $config.mf_geo_multileveled}
				<div class="geo_items{if $geo_filter_data.location} hide{/if}">
					{if $geo_block_data}
						<ul class="gf-list">
						{foreach from=$geo_block_data item="level1Item"}							
							<li {if $level1Item.childs}class="expander"{/if}>
								{if $config.mf_geo_subdomains}
									{assign var="arr" value="/"|explode:$level1Item.Path}
									{assign var="clean_url" value=$geo_filter_data.clean_url_sub|replace:"[geo_sub]":$arr.0}
									{assign var="firstpath" value=$arr.0|cat:"/"}
									{assign var="level1_path" value=$level1Item.Path|replace:$firstpath:""}
								{else}
									{assign var="level1_path" value=$level1Item.Path}
								{/if}

								<a title="{$level1Item.name}" href="{$clean_url|replace:"[geo_url]":$level1_path}">{$level1Item.name}</a>
								{if $level1Item.childs}<span class="arrow"></span>{/if}
								{if $config.mf_geo_subdomains}
									{assign var="urltocheck" value=$geo_filter_data.geo_sub|cat:"/"|cat:$geo_filter_data.geo_url}
								{else}
									{assign var="urltocheck" value=$geo_filter_data.geo_url}
								{/if}

								{if $level1Item.childs}
									{assign var="in_url" value=$urltocheck|strpos:$level1Item.Path}
									{if $in_url|is_numeric}{assign var="expand" value=true}{else}{assign var="expand" value=false}{/if}
									{include file=$smarty.const.RL_PLUGINS|cat:"multiField"|cat:$smarty.const.RL_DS|cat:"list_level.tpl" childs=$level1Item.childs item_id=$level1Item.ID level=2 subchilds=$level1Item.subchilds expand=$expand}
								{/if}
							</li>
						{/foreach}						
						</ul>
					{/if}
				</div>

				<script type="text/javascript">//<![CDATA[
				var mf_expand = "{$lang.mf_expand}";
				var mf_collapse = "{$lang.mf_collapse}";
				
				{literal}
				$(document).ready(function(){
					$('div.geo_items li.expander span.arrow').click(function(){
						var self = this;
						
						if( $(this).closest('.expander').children('ul.child').css('display') == 'none' )
						{
							$(this).closest('li.expander').parent().find('ul.child').slideUp();
							$(this).closest('li.expander').parent().find('span.arrow').removeClass('arrow_down');

							$(this).closest('.expander').children('ul.child').slideDown(function(){
								$(self).addClass('arrow_down');
							});
						}
						else
						{
							$(this).closest('.expander').children('ul.child').slideUp(function(){
								$(self).removeClass('arrow_down');
							});
						}
					});
				});
				{/literal}
				//]]>
				</script>

				<script type="text/javascript">
				{if $tpl_settings.type == 'responsive_42'}
					{literal}
						$(document).ready(function(){
							$('div.geo_items').css('max-height','300px');
							$('div.geo_items').mCustomScrollbar();

							$('#change_loc_link').click(function(){						

								if( $('div.geo_items').css('display') == 'none' )
								{
									$('div.geo_items').slideDown("normal", function() { $('div.geo_items').mCustomScrollbar('destroy');$('div.geo_items').mCustomScrollbar() } );
								}else
								{
									$('div.geo_items').slideUp("normal", function() { $('div.geo_items').mCustomScrollbar('destroy');$('div.geo_items').mCustomScrollbar() } );
								}						
							});
						});
					{/literal}
				{else}
					{literal}
					$('#change_loc_link').click(function(){
						if( $('div.geo_items').css('display') == 'none' )
						{
							$('div.geo_items').slideDown();
						}else
						{
							$('div.geo_items').slideUp();
						}
					});
					{/literal}
				{/if}		
				</script>
			{else}
				<div class="geo_items">
					<ul class="gf-list count-{if $config.mf_geo_columns > 4}4{else}{$config.mf_geo_columns}{/if}">
						{foreach from=$geo_block_data item="level1Item" name="mfLoop"}{strip}
							<li>

							{if $config.mf_geo_subdomains}
								{assign var="arr" value="/"|explode:$level1Item.Path}
								{assign var="clean_url" value=$geo_filter_data.clean_url_sub|replace:"[geo_sub]":$arr.0}
								{assign var="firstpath" value=$arr.0|cat:"/"}
								{assign var="level1_path" value=$level1Item.Path|replace:$firstpath:""}
							{else}
								{assign var="level1_path" value=$level1Item.Path}
							{/if}

							<a title="{$level1Item.name}" href="{$clean_url|replace:"[geo_url]":$level1_path}">{$level1Item.name}</a>
							</li>
						{/strip}{/foreach}
					</ul>
					<div class="cat-toggle mf-cat-toggle hide">...</div>
				</div>

				{if $tpl_settings.type == 'responsive_42'}
					<script type="text/javascript">
					var mf_count = {if $geo_block_data|@count}{$geo_block_data|@count}{else}0{/if};
					var mf_columns = {$config.mf_geo_columns};
	 			
					{literal}
						$(document).ready(function(){
							var mf_desktop_limit_top = 10;
							var mf_desktop_limit_bottom = 25;

							if ( mf_count <= 0 )
								return;

							var current_media_query = media_query;
							$(window).resize(function(){
								if ( media_query != current_media_query ) {
									$('ul.gf-list').mCustomScrollbar('destroy');
									$('ul.gf-list').mCustomScrollbar();
									current_media_query = media_query;
								}
							});

							if ( mf_count > mf_desktop_limit_top && mf_count <= mf_desktop_limit_bottom && rlPageInfo['controller'] != 'home' ) {
								$('ul.gf-list').css('max-height', 'none');
								var gt = mf_desktop_limit_top - 1;

								$('ul.gf-list > li:gt('+gt+')').addClass('rest').addClass('hide');
								$('div.mf-cat-toggle').removeClass('hide').click(function(){
									$(this).prev().find('> li.rest').toggle();
								});							
							}
							else if (mf_count > mf_desktop_limit_bottom || (rlPageInfo['controller'] == 'home' && mf_count > mf_desktop_limit_top) ) {
								$('.gf-list').mCustomScrollbar();
							}
						});
					{/literal}
					</script>
				{/if}	
			{/if}
		{else}
			<input type="hidden" name="geo_url" value=""/>
	
			<select id="geo_selector" class="geo_selector {if $smarty.section.geoLoop.last}last{/if}">
				<option value="0">{$lang.mf_geo_select_location}</option>		
				{foreach from=$geo_block_data item="item"}
					<option value="{$item.Path}" {if $geo_filter_data.location.0.Key == $item.Key}selected="selected"{/if}>{$item.name}</option>
				{/foreach}
			</select>
			
			{assign var="reset_path" value=$clean_url|replace:"[geo_url]":""}			
			<a {if !$geo_filter_data.location}class="hide"{/if} href="{$reset_path}?reset_location" title="{$lang.mf_geo_remove}"><img src="{$rlTplBase}img/blank.gif" class="gf-remove" alt="" /></a>
			
			{section name="geoLoop" loop=$multi_formats[$geo_format].Levels-1}
				<select id="geo_selector_level{$smarty.section.geoLoop.iteration}" class="geo_selector {if $smarty.section.geoLoop.last}last{/if}">
					<option value="0">{$lang.mf_geo_select_location}</option>
				</select>

				<a class="hide" href="{$prev_path}" id="mf_reset_link{$smarty.section.geoLoop.iteration}" title="{$lang.mf_geo_remove}"><img src="{$rlTplBase}img/blank.gif" class="gf-remove" alt="" /></a>
			{/section}
			
			<input type="button" value="{$lang.mf_geo_gobutton}" id="geo_gobutton_dd" />
	
			<script type="text/javascript">//<![CDATA[
			{literal}
				$(document).ready(function(){
					$('#geo_gobutton_dd').click(function(){
						var path = $('input[name=geo_url]').val();
						if( path )
						{
							if( path && path != '0' )
							{
							{/literal}
								location.href= '{$clean_url}'.replace('[geo_url]', path);
							{literal}
							}else
							{
								{/literal}
									location.href= '{$clean_url}'.replace('[geo_url]', '')+'?reset_location';
								{literal}
							}
						}
					});
	
					$('.geo_selector').change(function(){
						if( $(this).val() == '0' )
						{
							if( $(this).prev().val() )
							{
								$('input[name=geo_url]').val( $(this).prev().val() );
							}else
							{
								$('input[name=geo_url]').val( '0' );
							}
						}else
						{
							$('input[name=geo_url]').val( $(this).val() );
						}

						if( !$(this).hasClass('last') )
						{
							var level = $(this).attr('id').split("level")[1];
							xajax_geoGetNext( $(this).val(), level, $('.geo_selector').length );
						}
					});
					{/literal}
					{if $geo_filter_data.location}
						xajax_geoBuild();
					{/if}
					{literal}
				});
			{/literal}
			//]]>
			</script>
		{/if}
	{else}
		{$lang.mf_geo_box_default}
	{/if}
</div>

<!-- multiFields geo filter block end -->
