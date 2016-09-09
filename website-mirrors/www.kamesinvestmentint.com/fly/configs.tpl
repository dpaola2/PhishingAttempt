<!-- shoppingCart plugin  -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}module=configs" method="post">
		<div id="shc_settings">
			<table class="form">
				<tr>
					<td class="divider first" colspan="3"><div class="inner">{$lang.common}</div></td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_module}</td>
					<td class="field">
						<label><input {if $config.shc_module == '1'}checked="checked"{/if} type="radio" name="config[shc_module]" value="1" /> {$lang.enabled}</label>
						<label><input {if $config.shc_module == '0' || !$config.shc_module}checked="checked"{/if} type="radio" name="config[shc_module]" value="0" /> {$lang.disabled}</label>
					</td>
				</tr>
				<tr>
					{assign var='shc_module_auction' value='config+name+shc_module_auction'}
					<td class="name">{$lang[$shc_module_auction]}</td>
					<td class="field">
						<label><input {if $config.shc_module_auction == '1'}checked="checked"{/if} type="radio" name="config[shc_module_auction]" value="1" /> {$lang.enabled}</label>
						<label><input {if $config.shc_module_auction == '0' || !$config.shc_module_auction}checked="checked"{/if} type="radio" name="config[shc_module_auction]" value="0" /> {$lang.disabled}</label>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_method}</td>
					<td class="field">
						<select name="config[shc_method]">
							<option value="single" {if $config.shc_method == 'single'}selected="selected"{/if}>{$lang.shc_method_single}</option>
							<option value="multi" {if $config.shc_method == 'multi'}selected="selected"{/if}>{$lang.shc_method_multi}</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_count_items_block}</td>
					<td class="field">
						<input type="text" name="config[shc_count_items_block]" value="{$config.shc_count_items_block}" />
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_orders_per_page}</td>
					<td class="field">
						<input type="text" name="config[shc_orders_per_page]" value="{$config.shc_orders_per_page}" />
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_fields_position}</td>
					<td class="field">
						<select name="config[shc_fields_position]" id="shc_position">
							<option value="top">{$lang.shc_form_top}</option>
							<option value="bottom" {if $config.shc_fields_position == 'bottom'}selected="selected"{/if}>{$lang.shc_form_bottom}</option>
							<optgroup style="font-size: 11px;font-style: normal;padding: 0 0 4px 10px;" label="{$lang.shc_place_in_form}">
								{foreach from=$groups item='group'}
									<option {if $config.shc_fields_position == $group.Key}selected="selected"{/if} style="font-size: 13px;" value="{$group.Key}">{$group.name}</option>
								{/foreach}
							</optgroup>
						</select>
					</td>
				</tr>
			</table>
			
			<div id="type_dom" class="hide">
				<table class="form">
				<tr>
					<td class="name">{$lang.shc_fields_position_type}</td>
					<td class="field">
						<label><input {if $config.shc_fields_position_type == 'prepend' || !$config.shc_fields_position_type}checked="checked"{/if} type="radio" name="config[shc_fields_position_type]" value="prepend" /> {$lang.shc_fields_prepend}</label>
						<label><input {if $config.shc_fields_position_type == 'append'}checked="checked"{/if} type="radio" name="config[shc_fields_position_type]" value="append" /> {$lang.shc_fields_append}</label>
					</td>
				</tr>
				</table>
			</div>

			<table class="form">
				<tr>
					<td class="name">{$lang.shc_listing_field_price}</td>
					<td class="field">
						<select name="config[shc_listing_field_price]">
							<option value="">{$lang.select}</option>
							{foreach from=$listing_fields item='lfield'}
								<option {if $config.shc_listing_field_price == $lfield.Key}selected="selected"{/if} value="{$lfield.Key}">{$lfield.name}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_time_format}</td>
					<td class="field">
						<input type="text" name="config[shc_time_format]" value="{if $config.shc_time_format}{$config.shc_time_format}{else}%H%I%S{/if}" />
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_weight_unit}</td>
					<td class="field">
						<select name="config[shc_weight_unit]">
							<option {if $config.shc_weight_unit == 'kg'}selected="selected"{/if} value="kg">{$lang.shc_weight_unit_kg}</option>
							<option {if $config.shc_weight_unit == 'gr'}selected="selected"{/if} value="gr">{$lang.shc_weight_unit_gr}</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_available_shipping_methods}</td>
					<td class="field">
						<fieldset class="light">
							<legend id="legend_shipping_methods_tab_area" class="up" onclick="fieldset_action('accounts_tab_area');">{$lang.shc_services}</legend>
							<div id="shipping_methods_tab_area" style="padding: 0 10px 10px 10px;">
								<table>
								<tr>
									<td>
										{assign var='shcShippingMethods' value=","|explode:$config.shc_allowed_shipping_methods}   
										<div id="shc_available_shipping_methods">
										{foreach from=$shc_shipping_methods item='method'}
											{assign var='shcItemKey' value=$method.key}
											<div style="padding: 2px 8px 2px 0;">
												<input class="checkbox" {if $shcItemKey|in_array:$shcShippingMethods}checked="checked"{/if} id="shc_item{$method.key}" type="checkbox" name="config[shc_allowed_shipping_methods][]" value="{$method.key}" /> <label class="cLabel" for="shc_item{$method.key}">{$method.name}</label>
											</div>
										{/foreach}
										</div>
									</td>
									<td></td>
								</tr>
								</table>

								<div class="grey_area" style="margin: 8px 0 0;">
									<span onclick="$('#shipping_methods_tab_area input').attr('checked', true);" class="green_10">{$lang.check_all}</span>
									<span class="divider"> | </span>
									<span onclick="$('#shipping_methods_tab_area input').attr('checked', false);" class="green_10">{$lang.uncheck_all}</span>
								</div>
							</div>
						</fieldset>
					</td>
				</tr>
				<tr>
					{assign var='shc_currency_rate_url' value='config+name+shc_currency_rate_url'}
					{assign var='shc_currency_rate_url_help' value='config+des+shc_currency_rate_url'}
					<td class="name">{$lang[$shc_currency_rate_url]}</td>
					<td class="field">
						<input type="text" name="config[shc_currency_rate_url]" value="{$config.shc_currency_rate_url}" />
						<span class="field_description">{$lang[$shc_currency_rate_url_help]}</span>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.enable_for}</td>
					<td class="field">
						<fieldset class="light">
							<legend id="legend_accounts_tab_area" class="up" onclick="fieldset_action('accounts_tab_area');">{$lang.account_type}</legend>
							<div id="accounts_tab_area" style="padding: 0 10px 10px 10px;">
								<table>
								<tr>
									<td>
										{assign var='shcAccountTypes' value=","|explode:$config.shc_account_types}
										<table>
										<tr>
										{foreach from=$account_types item='a_type' name='ac_type'}
											<td>
												<div style="padding: 2px 8px 2px 0;">
													<input {if $a_type.Key|in_array:$shcAccountTypes}checked="checked"{/if} style="margin-bottom: 0px;" type="checkbox" id="account_type_{$a_type.ID}" value="{$a_type.Key}" name="config[shc_account_types][]" /> <label for="account_type_{$a_type.ID}">{$a_type.name}</label>
												</div>
											</td>
											
										{if $smarty.foreach.ac_type.iteration%1 == 0 && !$smarty.foreach.ac_type.last}
										</tr>
										<tr>
										{/if}
										
										{/foreach}
										</tr>
										</table>
									</td>
									<td>
										{assign var='shc_account_types_help' value='config+des+shc_account_types'}
										<span class="field_description">{$lang[$shc_account_types_help]}</span>
									</td>
								</tr>
								</table>

								<div class="grey_area" style="margin: 8px 0 0;">
									<span onclick="$('#accounts_tab_area input').attr('checked', true);" class="green_10">{$lang.check_all}</span>
									<span class="divider"> | </span>
									<span onclick="$('#accounts_tab_area input').attr('checked', false);" class="green_10">{$lang.uncheck_all}</span>
								</div>
							</div>
						</fieldset>
					</td>
				</tr>
				<tr>
					{assign var='shc_use_box' value='config+name+shc_use_box'}
					<td class="name">{$lang[$shc_use_box]}</td>
					<td class="field">
						<label><input {if $config.shc_use_box == '1'}checked="checked"{/if} type="radio" name="config[shc_use_box]" value="1" /> {$lang.enabled}</label>
						<label><input {if $config.shc_use_box == '0' || !$config.shc_use_box}checked="checked"{/if} type="radio" name="config[shc_use_box]" value="0" /> {$lang.disabled}</label>
					</td>
				</tr>
				<tr>
					<td class="divider first" colspan="3"><div class="inner">{$lang.shc_auction_settings}</div></td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_auto_rate}</td>
					<td class="field">
						<label><input {if $config.shc_auto_rate == '1'}checked="checked"{/if} type="radio" name="config[shc_auto_rate]" value="1" /> {$lang.enabled}</label>
						<label><input {if $config.shc_auto_rate == '0' || !$config.shc_auto_rate}checked="checked"{/if} type="radio" name="config[shc_auto_rate]" value="0" /> {$lang.disabled}</label>
					</td>
				</tr>
				<tr id="shc_auto_rate_period" class="{if $config.shc_auto_rate == '0'}hide{/if}">
					<td class="name">{$lang.shc_auto_rate_period}</td>
					<td class="field">
						<input type="text" name="config[shc_auto_rate_period]" value="{if $config.shc_auto_rate_period}{$config.shc_auto_rate_period}{/if}" />
						{assign var='shc_auto_rate_period_des' value='config+des+shc_auto_rate_period'}
						<span class="settings_desc">{$lang[$shc_auto_rate_period_des]}</span>
					</td>
				</tr>
			</table>

			<!-- DHL settings -->
			<table class="form" id="shipping_dhl">
				<tr>
					<td class="divider" colspan="3"><div class="inner">{$lang.shc_settings_dhl}</div></td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_dhl_site_id}</td>
					<td class="field">
						<input type="text" name="config[shc_dhl_site_id]" value="{$config.shc_dhl_site_id}" />
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_dhl_password}</td>
					<td class="field">
						<input type="text" name="config[shc_dhl_password]" value="{$config.shc_dhl_password}" />
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_dhl_test_mode}</td>
					<td class="field">
						<label><input {if $config.shc_dhl_test_mode == '1'}checked="checked"{/if} type="radio" name="config[shc_dhl_test_mode]" value="1" /> {$lang.enabled}</label>
						<label><input {if $config.shc_dhl_test_mode == '0' || !$config.shc_dhl_test_mode}checked="checked"{/if} type="radio" name="config[shc_dhl_test_mode]" value="0" /> {$lang.disabled}</label>
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_country}</td>
					<td class="field">
						<select name="config[shc_dhl_country]">
							<option value="">{$lang.select}</option>
							{foreach from=$shc_countries item='country'}
								<option value="{$country.name}" {if $config.shc_dhl_country == $country.name}selected="selected"{/if}>{$country.name}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_city}</td>
					<td class="field">
						<input type="text" name="config[shc_dhl_city]" value="{$config.shc_dhl_city}" />
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_zip}</td>
					<td class="field">
						<input type="text" name="config[shc_dhl_zip]" value="{$config.shc_dhl_zip}" />
					</td>
				</tr>
			</table>

			<!-- UPS settings -->
			<table class="form" id="shipping_ups">
				<tr>
					<td class="divider" colspan="3"><div class="inner">{$lang.shc_settings_ups}</div></td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_ups_key}</td>
					<td class="field">
						<input type="text" name="config[shc_ups_key]" value="{$config.shc_ups_key}" />
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_ups_username}</td>
					<td class="field">
						<input type="text" name="config[shc_ups_username]" value="{$config.shc_ups_username}" />
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_ups_password}</td>
					<td class="field">
						<input type="text" name="config[shc_ups_password]" value="{$config.shc_ups_password}" />
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_ups_pickup_methods}</td>
					<td class="field">
						<select name="config[shc_ups_pickup_methods]">
							{foreach from=$shc_ups_pickup_methods key='code' item='method'}
								<option value="{$code}" {if $config.shc_ups_pickup_methods == $code}selected="selected"{/if}>{$method}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_ups_package_types}</td>
					<td class="field">
						<select name="config[shc_ups_package_types]">
							{foreach from=$shc_ups_package_types key='code' item='method'}
								<option value="{$code}" {if $config.shc_ups_package_types == $code}selected="selected"{/if}>{$method}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_ups_origin}</td>
					<td class="field">
						<select name="config[shc_ups_origin]" id="shc_ups_origin">
							{foreach from=$ups_origins item='origin'}
								<option value="{$origin.key}" {if $config.shc_ups_origin == $origin.key}selected="selected"{/if}>{$origin.name}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_ups_available_methods}</td>
					<td class="field">
						{assign var='shcUPSServices' value=","|explode:$config.shc_ups_services}
						<fieldset class="light">
							<legend id="legend_ups_services_tab_area" class="up" onclick="fieldset_action('ups_services_tab_area');">{$lang.shc_ups_services}</legend>
							<div id="ups_services_tab_area" style="padding: 0 10px 10px 10px;">
								<table>
								<tr>
									<td>
										{foreach from=$shc_ups_services item='service'}  
											{assign var='shcOriginsItem' value=","|explode:$service.origin}
											{assign var='shcItemKey' value=$service.code}
											<div style="padding: 2px 8px 2px 0;" class="{if $config.shc_ups_origin|in_array:$shcOriginsItem || (!$config.shc_ups_origin && 'US'|in_array:$shcOriginsItem)}{else}hide{/if}">
												<input class="checkbox" {if $shcItemKey|in_array:$shcUPSServices}checked="checked"{/if} id="shc_pickup_item{$service.code}" accesskey="{$service.origin}" type="checkbox" name="config[shc_ups_services][]" value="{$service.code}" /> <label class="cLabel" for="shc_pickup_item{$service.code}">{$service.name}</label>
											</div>											
										{/foreach}
									</td>
									<td>
										<span class="field_description">{$lang.shc_ups_services_help}</span>
									</td>
								</tr>
								</table>

								<div class="grey_area" style="margin: 8px 0 0;">
									<span onclick="selectUPSServices(true);" class="green_10">{$lang.check_all}</span>
									<span class="divider"> | </span>
									<span onclick="selectUPSServices(false);" class="green_10">{$lang.uncheck_all}</span>
								</div>
							</div>
						</fieldset>
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_ups_classification}</td>
					<td class="field">
						<select name="config[shc_ups_classification]">
							<option value="">{$lang.select}</option>
							{foreach from=$ups_classification item='classification'}
								<option value="{$classification}" {if $config.shc_ups_classification == $classification}selected="selected"{/if}>{$classification}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_ups_quote_type}</td>
					<td class="field">
						<select name="config[shc_ups_quote_type]">
							<option value="">{$lang.select}</option>
							{foreach from=$ups_quote_type item='quote_type'}
								<option value="{$quote_type.key}" {if $config.shc_ups_quote_type == $quote_type.key}selected="selected"{/if}>{$quote_type.name}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_ups_weight_type}</td>
					<td class="field">
						<label><input {if $config.shc_ups_weight_type == 'KGS' || !$config.shc_ups_weight_type}checked="checked"{/if} type="radio" name="config[shc_ups_weight_type]" value="KGS" /> {$lang.ups_weight_kgs}</label>
						<label><input {if $config.shc_ups_weight_type == 'LBS'}checked="checked"{/if} type="radio" name="config[shc_ups_weight_type]" value="LBS" /> {$lang.ups_weight_lbs}</label>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_ups_length_type}</td>
					<td class="field">
						<label><input {if $config.shc_ups_length_type == 'CM' || !$config.shc_ups_length_type}checked="checked"{/if} type="radio" name="config[shc_ups_length_type]" value="CM" /> {$lang.ups_length_cm}</label>
						<label><input {if $config.shc_ups_length_type == 'IN'}checked="checked"{/if} type="radio" name="config[shc_ups_length_type]" value="IN" /> {$lang.ups_length_in}</label>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_ups_insurance}</td>
					<td class="field">
						<label><input {if $config.shc_ups_insurance == '1'}checked="checked"{/if} type="radio" name="config[shc_ups_insurance]" value="1" /> {$lang.yes}</label>
						<label><input {if $config.shc_ups_insurance == '0' || !$config.shc_ups_insurance}checked="checked"{/if} type="radio" name="config[shc_ups_insurance]" value="1" /> {$lang.no}</label>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_ups_dimensions}</td>
					<td class="field">
						<input type="text" class="numeric" name="config[shc_ups_length]" value="{$config.shc_ups_length}" style="width: 40px;" />&nbsp;
						<input type="text" class="numeric" name="config[shc_ups_width]" value="{$config.shc_ups_width}" style="width: 40px;" />&nbsp;
						<input type="text" class="numeric" name="config[shc_ups_height]" value="{$config.shc_ups_height}" style="width: 40px;" />
						<span class="field_description">{$lang.shc_ups_dimensions_help}</span>
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_country}</td>
					<td class="field">
						<select name="config[shc_ups_country]">
							<option value="">{$lang.select}</option>
							{foreach from=$shc_countries item='country'}
								<option value="{$country.name}" {if $config.shc_ups_country == $country.name}selected="selected"{/if}>{$country.name}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_state}</td>
					<td class="field">
						<input type="text" name="config[shc_ups_state]" value="{$config.shc_ups_state}" />
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_city}</td>
					<td class="field">
						<input type="text" name="config[shc_ups_city]" value="{$config.shc_ups_city}" />
					</td>
				</tr>
				<tr class="single{if $config.shc_method == 'multi'} hide{/if}">
					<td class="name">{$lang.shc_zip}</td>
					<td class="field">
						<input type="text" name="config[shc_ups_zip]" value="{$config.shc_ups_zip}" />
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_ups_test_mode}</td>
					<td class="field">
						<label><input {if $config.shc_ups_test_mode == '1'}checked="checked"{/if} type="radio" name="config[shc_ups_test_mode]" value="1" /> {$lang.enabled}</label>
						<label><input {if $config.shc_ups_test_mode == '0' || !$config.shc_ups_test_mode}checked="checked"{/if} type="radio" name="config[shc_ups_test_mode]" value="0" /> {$lang.disabled}</label>
					</td>
				</tr>
			</table>
			
			<table class="form">
				<tr>
					<td class="name no_divider"></td>
					<td class="field">
						<input type="hidden" name="form" value="submit" />
						<input id="shc_button" type="submit" class="button lang_add" value="{$lang.save}" />
					</td>
				</tr>
			</table>
		</div>
	</form>
	
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}


<script type="text/javascript">
{if $config.shc_fields_position_type == 'append' || $config.shc_fields_position_type == 'prepend' && ($config.shc_fields_position != 'bottom' && $config.shc_fields_position != 'top')}
{literal}
$(document).ready(function(){
	$('#type_dom').slideDown();
});
{/literal}
{/if}

var shc_method = '{$config.shc_method}';

{literal}

$(document).ready(function(){

	$('select[name="config[shc_method]"]').change(function()
	{
		checkSettingsFieldsByMethod($(this).val());
	});

	checkSettingsFieldsByMethod(shc_method);

	$('input[name="config[shc_auto_rate]"]').change(function()
	{
		if($(this).is(':checked'))
		{
			if($(this).val() == '1')
			{
				$('#shc_auto_rate_period').show();
			}
			else
			{
				$('#shc_auto_rate_period').hide();
				$('input[name="config[shc_auto_rate_period]"]').val(0);
			}
		}			
	});

	$('#shc_ups_origin').change(function()
	{
		var origin_key = $(this).val();

		$('#ups_services_tab_area input[type="checkbox"]').each(function()
		{
			var origin = $(this).attr('accesskey');

			if(origin.indexOf(origin_key) > -1)
			{
				$(this).parent('div').show();	
			}
			else
			{
				$(this).attr('checked', false);
				$(this).parent('div').hide();
			}
		});
	});

	$('#shc_available_shipping_methods>div>input[type="checkbox"]').change(function()
	{
		if($(this).val() != 'pickup' && $(this).val() != 'courier')
		{
			if($(this).is(':checked'))
			{
				$('#shipping_' + $(this).val()).show();
			}
			else
			{
				$('#shipping_' + $(this).val()).hide();
			}
		}
	});
	
	$('#shc_available_shipping_methods>div>input[type="checkbox"]').each(function()
	{
		if($(this).val() != 'pickup' && $(this).val() != 'courier')
		{
			if($(this).is(':checked'))
			{
				$('#shipping_' + $(this).val()).show();
			}
			else
			{
				$('#shipping_' + $(this).val()).hide();
			}
		}
	});
});

$('#shc_position').change(function(){
	if ( $(this).val() == 'top' || $(this).val() == 'bottom' )
	{
		$('#type_dom').slideUp();
	}
	else
	{
		$('#type_dom').slideDown();
	}
});

var checkSettingsFieldsByMethod = function(method)
{
	$('#shc_settings table.form tr').each(function()
	{
		if($(this).hasClass('single') || $(this).hasClass('multi') )
		{
			if($(this).hasClass(method))
			{
				$(this).show();
			}
			else
			{
				$(this).hide();
			}
		}
	});
}

var selectUPSServices = function(checked)
{  
	$('#ups_services_tab_area input[type="checkbox"]').each(function()
	{
	 	if($(this).parent('div').is(':visible'))
		{
			$(this).attr('checked', checked);
		}
	});
}

{/literal}
</script>

<!-- end shoppingCart plugin -->