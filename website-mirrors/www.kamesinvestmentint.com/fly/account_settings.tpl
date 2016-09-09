<!-- shoppingCart plugin -->

<div id="area_shoppingCart" class="tab_area hide">
	<div class="highlight">
		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html{else}?page={$pageInfo.Path}{/if}" enctype="multipart/form-data">
			<input type="hidden" name="form" value="settings" />

			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_paypal' name=$lang.shc_paypal}
			<table class="submit">
				<tr>
					<td class="name" style="width: 140px;">{$lang.shc_use_paypal}</td>
					<td class="field">
						<label><input type="radio" {if $smarty.post.shc.shc_paypal_enable == 1}checked="checked"{/if} name="shc[shc_paypal_enable]" value="1" />{$lang.enabled}</label>
						<label><input type="radio" {if $smarty.post.shc.shc_paypal_enable == 0 || !$smarty.post.shc.shc_paypal_enable}checked="checked"{/if} name="shc[shc_paypal_enable]" value="0" />{$lang.disabled}</label>
					</td>
				</tr>
				<tr>                                                              
					{assign var='shc_af_name' value='account_fields+name+shc_paypal_email'}
					<td class="name" style="width: 140px;">{$lang[$shc_af_name]}</td>
					<td class="field"><input type="text" name="shc[shc_paypal_email]" maxlength="100" value="{if $smarty.post.shc.shc_paypal_email}{$smarty.post.shc.shc_paypal_email}{/if}" /></td>
				</tr>
			</table>
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_2co' name=$lang.shc_2co}
			<table class="submit">
				<tr>
					<td class="name" style="width: 140px;">{$lang.shc_use_2co}</td>
					<td class="field">
						<label><input type="radio" {if $smarty.post.shc.shc_2co_enable == 1}checked="checked"{/if} name="shc[shc_2co_enable]" value="1" />{$lang.enabled}</label>
						<label><input type="radio" {if $smarty.post.shc.shc_2co_enable == 0 || !$smarty.post.shc.shc_2co_enable}checked="checked"{/if} name="shc[shc_2co_enable]" value="0" />{$lang.disabled}</label>
					</td>
				</tr>
				<tr>
					{assign var='shc_af_name' value='account_fields+name+shc_2co_id'}
					<td class="name" style="width: 140px;">{$lang[$shc_af_name]}</td>
					<td class="field"><input type="text" name="shc[shc_2co_id]" maxlength="100" value="{if $smarty.post.shc.shc_2co_id}{$smarty.post.shc.shc_2co_id}{/if}" /></td>
				</tr>
			</table>
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}


			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_available_shipping_methods' name=$lang.shc_shipping_details}
			<table class="submit">
				<tr>
					<td class="name" style="width: 140px;">{$lang.shc_available_shipping_methods}</td>
					<td class="field">
						{assign var='shcShippingMethods' value=","|explode:$smarty.post.shc.shc_allowed_shipping_methods}
						<table class="fixed">
						{foreach from=$shc_shipping_methods item='method'}
							<tr>
								<td>
									{assign var='shcItemKey' value=$method.key}
									<label><input type="checkbox" class="shipping-method" {if $shcItemKey|in_array:$shcShippingMethods}checked="checked"{/if} name="shc[shc_allowed_shipping_methods][]" value="{$method.key}" />{$method.name}</label>
								</td>
							</tr>
						{/foreach}
						</table>
					</td>
				</tr>
			</table>
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			
            <div class="shipping-pickup">
			</div>

            <div class="shipping-courier">
			</div>

            <div class="shipping-dhl">
			</div>
			
			<div class="shipping-ups">
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_settings_ups' name=$lang.shc_settings_ups}
				<table class="submit">
					<tr>
						<td class="name" style="width: 140px;">{$lang.shc_ups_pickup_methods}</td>
						<td class="field">
							<select name="shc[shc_ups_pickup_methods]" id="shc_ups_origin">
								{foreach from=$shc_ups_pickup_methods key='code' item='method'}
									<option value="{$code}" {if $smarty.post.shc.shc_ups_pickup_methods == $code}selected="selected"{/if}>{$method}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td class="name" style="width: 140px;">{$lang.shc_ups_package_types}</td>
						<td class="field">
							<select name="shc[shc_ups_package_type]" id="shc_ups_origin">
								{foreach from=$shc_ups_package_types key='code' item='type'}
									<option value="{$code}" {if $smarty.post.shc.shc_ups_package_type == $code}selected="selected"{/if}>{$type}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr class="single">
						<td class="name">{$lang.shc_ups_origin}</td>
						<td class="field">
							<select name="shc[shc_ups_origin]" id="shc_ups_origin">
								{foreach from=$ups_origins item='origin'}
									<option value="{$origin.key}" {if $smarty.post.shc.shc_ups_origin == $origin.key}selected="selected"{/if}>{$origin.name}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td class="name">{$lang.shc_ups_services}</td>
						<td class="field">
							{assign var='shcUPSServices' value=","|explode:$smarty.post.shc.shc_ups_services}
							<table class="sTable">
								<tr>
									<td valign="top">
										<div id="shc_ups_services">
										{foreach from=$shc_ups_services item='service'}
											{assign var='shcOriginsItem' value=","|explode:$service.origin}
											{assign var='shcItemKey' value=$service.code}
											<div style="padding: 2px 8px 2px 0;" class="{if $smarty.post.shc.shc_ups_origin|in_array:$shcOriginsItem || (!$smarty.post.shc.shc_ups_origin && 'US'|in_array:$shcOriginsItem)}{else}hide{/if}">
												<input class="checkbox" {if $shcItemKey|in_array:$shcUPSServices}checked="checked"{/if} id="shc_pickup_item{$service.code}" accesskey="{$service.origin}" type="checkbox" name="shc[shc_ups_services][]" value="{$service.code}" /> <label class="cLabel" for="shc_pickup_item{$service.code}">{$service.name}</label>
											</div>
										{/foreach}
										</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr class="single">
						<td class="name">{$lang.shc_ups_classification}</td>
						<td class="field">
							<select name="shc[shc_ups_classification]">
								<option value="">{$lang.select}</option>
								{foreach from=$ups_classification item='classification'}
									<option value="{$classification}" {if $smarty.post.shc.shc_ups_classification == $classification}selected="selected"{/if}>{$classification}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr class="single">
						<td class="name">{$lang.shc_ups_quote_type}</td>
						<td class="field">
							<select name="shc[shc_ups_quote_type]">
								<option value="">{$lang.select}</option>
								{foreach from=$ups_quote_type item='quote_type'}
									<option value="{$quote_type.key}" {if $smarty.post.shc.shc_ups_quote_type == $quote_type.key}selected="selected"{/if}>{$quote_type.name}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</table>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			</div>

			{rlHook name='shoppingCartAccountSettings'}

			<table class="submit">			
				<tr>
					<td class="name" style="width: 140px;"></td>
					<td class="field button">
						<input type="submit" value="{$lang.save}" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>

<script type="text/javascript">
	{literal}
	$(document).ready(function(){
		$('#shc_ups_origin').change(function()
		{
			var origin_key = $(this).val();

			$('#shc_ups_services>div>input[type="checkbox"]').each(function()
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

		$('input[type="checkbox"].shipping-method').change(function()
		{
			checkShippingMethods($(this).val(), $(this).is(':checked'));
		});
		
		$('input[type="checkbox"].shipping-method').each(function()
		{
			checkShippingMethods($(this).val(), $(this).is(':checked'));
		});
	});

	var checkShippingMethods = function(method, checked)
	{  
		if(checked)
		{
			$('div.shipping-' + method).show();	
		}
		else
		{
			$('div.shipping-' + method).hide();
		}
	}
	{/literal}
</script>

<!-- end shoppingCart plugin -->