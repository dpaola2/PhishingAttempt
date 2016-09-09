<!-- search by distance block for responsive 42 -->

{strip}

{if $pageInfo.Key == 'search_by_distance'}
	<div class="sbd-box">
		{if $sbd_countries|@count <= 1}
			<input type="hidden" name="country" value="{foreach from=$sbd_countries item='country'}{$country.Code}{/foreach}" />
		{/if}
		
		<div class="{if $block.Side != 'left'}light-inputs{/if}">
			{if $sbd_countries|@count > 1}
				<div class="single-item">
					<select class="sbd_control" name="country">
						<option value="">{$lang.sbd_select_country}</option>
						{foreach from=$sbd_countries item='country'}
							<option value="{$country.Code}" {if $smarty.post.block_country == $country.Code || (!$smarty.post.block_country && $country.Code == $config.sbd_default_country)}selected="selected"{elseif !$smarty.post.block_country && $smarty.session.GEOLocationData->Country_code && $smarty.session.GEOLocationData->Country_code == $country.Code}selected="selected"{/if}>{$lang[$country.pName]}</option>
						{/foreach}
					</select>
				</div>
			{/if}

			<div class="three-items">
				<input id="sbd_zip" class="align-center" placeholder="{$lang.sbd_zipcode}" size="9" maxlength="10" name="block_zip" type="text" value="{if $smarty.post.block_zip}{$smarty.post.block_zip}{/if}" />
				
				<select id="sbd_radius" name="distance">
					{foreach from=','|explode:$config.sbd_distance_items item='distance'}
						<option {if $smarty.post.block_distance == $distance}selected="selected"{elseif $distance == $config.sbd_default_distance}selected="selected"{/if} value="{$distance}">{$distance}</option>
					{/foreach}
				</select>
				
				{if $config.sbd_units == 'miles/kilometres'}
					<select name="distance_unit">
						{if $config.sbd_default_units == 'miles'}
							<option value="mi" title="{$lang.sbd_mi}">{$lang.sbd_mi_short}</option>
							<option {if $smarty.post.block_distance_unit == 'km'}selected="selected"{/if} value="km" title="{$lang.sbd_km}">{$lang.sbd_km_short}</option>
						{else}
							<option value="km" title="{$lang.sbd_km}">{$lang.sbd_km_short}</option>
							<option {if $smarty.post.block_distance_unit == 'mi'}selected="selected"{/if} value="mi" title="{$lang.sbd_mi}">{$lang.sbd_mi_short}</option>
						{/if}
					</select>
				{else}
					<input name="distance_unit" type="hidden" value="{if $config.sbd_units == 'miles'}mi{else}km{/if}" />
					{if $config.sbd_units == 'miles'}{$lang.sbd_mi}{else}{$lang.sbd_km}{/if},
				{/if}
			</div>

			<div class="nav"><input class="sbd_control" type="submit" value="{$lang.search}" /></div>
		</div>
	</div>
{else}
	<form class="sbd-box" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.search_by_distance}.html{else}?page={$pages.search_by_distance}{/if}">
		<input type="hidden" name="sbd_block" value="1" />
		{if $sbd_countries|@count <= 1}
			<input type="hidden" name="block_country" value="{foreach from=$sbd_countries item='country'}{$country.Code}{/foreach}" />
		{/if}
		
		<div class="{if $block.Side != 'left'}light-inputs{/if}">
			{if $sbd_countries|@count > 1}
				<div class="single-item">
					<select name="block_country">
						<option value="">{$lang.sbd_select_country}</option>
						{foreach from=$sbd_countries item='country'}
							<option value="{$country.Code}" {if $smarty.post.block_country == $country.Code || (!$smarty.post.block_country && $country.Code == $config.sbd_default_country)}selected="selected"{elseif !$smarty.post.block_country && $smarty.session.GEOLocationData->Country_code && $smarty.session.GEOLocationData->Country_code == $country.Code}selected="selected"{/if}>{$lang[$country.pName]}</option>
						{/foreach}
					</select>
				</div>
			{/if}

			<div class="three-items">
				<input class="align-center" placeholder="{$lang.sbd_zipcode}" size="9" maxlength="10" name="block_zip" type="text" value="{if $smarty.post.block_zip}{$smarty.post.block_zip}{/if}" />
				
				<select name="block_distance">
					{foreach from=','|explode:$config.sbd_distance_items item='distance'}
						<option {if $smarty.post.block_distance == $distance}selected="selected"{elseif $distance == $config.sbd_default_distance}selected="selected"{/if} value="{$distance}">{$distance}</option>
					{/foreach}
				</select>
				
				{if $config.sbd_units == 'miles/kilometres'}
					<select name="block_distance_unit">
						{if $config.sbd_default_units == 'miles'}
							<option value="mi" title="{$lang.sbd_mi}">{$lang.sbd_mi_short}</option>
							<option {if $smarty.post.block_distance_unit == 'km'}selected="selected"{/if} value="km" title="{$lang.sbd_km}">{$lang.sbd_km_short}</option>
						{else}
							<option value="km" title="{$lang.sbd_km}">{$lang.sbd_km_short}</option>
							<option {if $smarty.post.block_distance_unit == 'mi'}selected="selected"{/if} value="mi" title="{$lang.sbd_mi}">{$lang.sbd_mi_short}</option>
						{/if}
					</select>
				{else}
					<input name="block_distance_unit" type="hidden" value="{if $config.sbd_units == 'miles'}mi{else}km{/if}" />
					{if $config.sbd_units == 'miles'}{$lang.sbd_mi}{else}{$lang.sbd_km}{/if},
				{/if}
			</div>

			<div class="nav"><input type="submit" value="{$lang.search}" /></div>
		</div>
	</form>
{/if}

{/strip}

<!-- search by distance block for responsive 42 end -->