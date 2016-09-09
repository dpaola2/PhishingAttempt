<!-- shipping info 	-->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_shipping_method' name=$lang.shc_shipping_method}

<select name="shipping[method]" id="shc_shipping_method">
	<option value="">{$lang.select}</option>
	{foreach from=$shc_shipping_methods item='method'}
		<option value="{$method.key}" {if $smarty.post.shipping.method == $method.key}selected="selected"{/if}>{$method.name}</option>
	{/foreach}
</select>
		
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

<div id="shipping_fields" class="hide">
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_shipping_details' name=$lang.shc_shipping_details}
		<div class="submit-cell ups">
			<div class="name">{$lang.shc_ups_service} <span class="red">*</span></div>
			<div class="field single-field">
				{if $config.shc_method == 'single'}
					{assign var='shcUPSAllowedServices' value=","|explode:$config.shc_ups_services}    	
				{else}
					{assign var='shcUPSAllowedServices' value=","|explode:$dealer_info.shc_ups_services}
				{/if}
				<select name="shipping[ups_service]">
					<option value="">{$lang.select}</option>
					{foreach from=$shc_ups_services item='service'}
						{if $service.code|in_array:$shcUPSAllowedServices}
							<option value="{$service.code}" {if $smarty.post.shipping.ups_service == $service.code}selected="selected"{/if}>{$service.name}</option>
						{/if}
					{/foreach}
				</select>
			</div>
		</div>

		<div id="shipping_country" class="submit-cell courier dhl ups">
			<div class="name">{$lang.shc_country} <span class="red">*</span></div>
			<div class="field single-field">
				<select name="shipping[country]">
					<option value="">{$lang.select}</option>
					{foreach from=$shc_countries item='country'}
						<option value="{$country.name}" {if $smarty.post.shipping.country == $country.name}selected="selected"{/if}>{$country.name}</option>
					{/foreach}
				</select>
			</div>
		</div>

		<div class="submit-cell ups">
			<div class="name">{$lang.shc_state} <span class="red">*</span></div>
			<div class="field single-field">
				<input class="wauto" type="text" name="shipping[region]" size="8" maxlength="150" value="{if $smarty.post.shipping.region}{$smarty.post.shipping.region}{/if}"  />
				<select name="shipping[state]" class="hide">
					<option value="">{$lang.select}</option>
					{foreach from=$shc_states item='state'}
						<option value="{$state.code}" {if $smarty.post.shipping.state == $state.code}selected="selected"{/if}>{$state.name}</option>
					{/foreach}
				</select>
			</div>
		</div>

		<div id="shipping_city" class="submit-cell courier dhl ups">
			<div class="name">{$lang.shc_city} <span class="red">*</span></div>
			<div class="field single-field">
				<input class="wauto" size="20" type="text" name="shipping[city]" maxlength="150" value="{if $smarty.post.shipping.city}{$smarty.post.shipping.city}{else}{$account_info.city}{/if}"  />
			</div>
		</div>

		<div id="shipping_zip" class="submit-cell courier dhl ups">
			<div class="name">{$lang.shc_zip} <span class="red">*</span></div>
			<div class="field single-field">
				<input class="wauto" size="8" type="text" name="shipping[zip]" maxlength="10" value="{if $smarty.post.shipping.zip}{$smarty.post.shipping.zip}{else}{$account_info.zip_code}{/if}"  />
			</div>
		</div>

		<div id="shipping_address" class="submit-cell courier dhl ups">
			<div class="name">{$lang.shc_address} <span class="red">*</span></div>
			<div class="field single-field">
				<input class="wauto" size="30" type="text" name="shipping[address]" maxlength="150" value="{if $smarty.post.shipping.address}{$smarty.post.shipping.address}{else}{$account_info.address}{/if}"  />
			</div>
		</div>

		<div id="shipping_name" class="submit-cell pickup courier dhl ups">
			<div class="name">{$lang.your_name} <span class="red">*</span></div>
			<div class="field single-field">
				<input class="wauto" size="30" type="text" name="shipping[name]" maxlength="100" value="{if $smarty.post.shipping.name}{$smarty.post.shipping.name}{else}{$account_info.Full_name}{/if}" />
			</div>
		</div>

		<div id="shipping_email" class="submit-cell pickup courier dhl ups">
			<div class="name">{$lang.your_email} <span class="red">*</span></div>
			<div class="field single-field">
				<input class="wauto" size="30" type="text" name="shipping[email]" maxlength="150" value="{if $smarty.post.shipping.email}{$smarty.post.shipping.email}{else}{$account_info.Mail}{/if}"  />
			</div>
		</div>

		<div id="shipping_phone" class="submit-cell pickup courier dhl ups">
			<div class="name">{$lang.shc_phone} <span class="red">*</span></div>
			<div class="field single-field">
				<input class="wauto" size="15" type="text" name="shipping[phone]" maxlength="150" value="{if $smarty.post.shipping.phone}{$smarty.post.shipping.phone}{else}{$account_info.phone}{/if}"  />
			</div>
		</div>

		<div id="shipping_vat_no" class="submit-cell courier dhl ups">
			<div class="name">{$lang.shc_vat_no}</div>
			<div class="field single-field">
				<input class="wauto" size="20" type="text" name="shipping[vat_no]" maxlength="150" value="{$smarty.post.shipping.vat_no}" />
			</div>
		</div>

		{rlHook name='shoppingCartShippingField'}

		<div id="shipping_comment_cont" class="submit-cell courier dhl ups pickup">
			<div class="name">{$lang.shc_comment}</div>
			<div class="field single-field">
				<textarea id="shipping_comment" name="shipping[comment]" rows="4">{$smarty.post.shipping.comment}</textarea>
			</div>
		</div>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
</div>
<!-- end shipping info 	-->

<script type="text/javascript">
     var shipping_method = '{$smarty.post.shipping.method}';
	 var country = '{$smarty.post.shipping.country}';

	{literal}

	$(document).ready(function(){
		$('#shc_shipping_method').change(function() {
			var method = '';

			if ( $(this).val() != '' ) {
				$('#shipping_fields').show();

				checkShippingFields($(this).val());
			}
			else {
				$('#shipping_fields').hide();
			}
		});

		if ( shipping_method ) {
			$('#shipping_fields').show();
			checkShippingFields(shipping_method);
		}

		$('select[name="shipping[country]"]').change(function() {
			defineStateMode($(this).val());	
		});

		if ( country ) {
			defineStateMode(country);
		}	
	});

	var checkShippingFields = function(method) {
		$('#shipping_fields input, #shipping_fields select').each(function() {
			if ( $(this).closest('div.submit-cell').hasClass(method) ) {
				$(this).closest('div.submit-cell').show();
			}
			else {
				$(this).closest('div.submit-cell').hide();
			}
		});
	}

	var defineStateMode = function(country) {
		if ( country == 'United States' ) {
			$('input[name="shipping[region]"]').hide();
			$('input[name="shipping[region]"]').val('');
			$('select[name="shipping[state]"]').show();
		}
		else {
			$('select[name="shipping[state]"]').hide();
			$('select[name="shipping[state]"] option[value=""]').attr('selected', 'selected');
			$('input[name="shipping[region]"]').show(); 
		}
	}

	{/literal}
</script>