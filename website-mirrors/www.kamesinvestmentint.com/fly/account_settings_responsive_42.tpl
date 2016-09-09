<!-- shoppingCart plugin -->

<div id="area_shoppingCart" class="tab_area hide">
	<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html{else}?page={$pageInfo.Path}{/if}" enctype="multipart/form-data">
		<input type="hidden" name="form" value="settings" />

		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_paypal' name=$lang.shc_paypal}
		<div class="submit-cell">
			<div class="name">{$lang.shc_use_paypal}</div>
			<div class="field checkbox-field">
				<label><input type="radio" {if $smarty.post.shc.shc_paypal_enable == 1}checked="checked"{/if} name="shc[shc_paypal_enable]" value="1" />{$lang.enabled}</label>
				<label><input type="radio" {if $smarty.post.shc.shc_paypal_enable == 0 || !$smarty.post.shc.shc_paypal_enable}checked="checked"{/if} name="shc[shc_paypal_enable]" value="0" />{$lang.disabled}</label>
			</div>
		</div>
		<div class="submit-cell clearfix">
			{assign var='shc_af_name' value='account_fields+name+shc_paypal_email'}
			<div class="name">{$lang[$shc_af_name]}</div>
			<div class="field single-field"><input type="text" name="shc[shc_paypal_email]" maxlength="100" value="{if $smarty.post.shc.shc_paypal_email}{$smarty.post.shc.shc_paypal_email}{/if}" /></div>
		</div>
		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_2co' name=$lang.shc_2co}
		<div class="submit-cell">			
			<div class="name">{$lang.shc_use_2co}</div>
			<div class="field checkbox-field">
				<label><input type="radio" {if $smarty.post.shc.shc_2co_enable == 1}checked="checked"{/if} name="shc[shc_2co_enable]" value="1" />{$lang.enabled}</label>
				<label><input type="radio" {if $smarty.post.shc.shc_2co_enable == 0 || !$smarty.post.shc.shc_2co_enable}checked="checked"{/if} name="shc[shc_2co_enable]" value="0" />{$lang.disabled}</label>
			</div>
		</div>
		<div class="submit-cell clearfix">
			{assign var='shc_af_name' value='account_fields+name+shc_2co_id'}
			<div class="name">{$lang[$shc_af_name]}</div>
			<div class="field single-field"><input type="text" name="shc[shc_2co_id]" maxlength="100" value="{if $smarty.post.shc.shc_2co_id}{$smarty.post.shc.shc_2co_id}{/if}" /></div>
		</div>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_available_shipping_methods' name=$lang.shc_shipping_details}

		<div class="submit-cell">			
			<div class="name">{$lang.shc_available_shipping_methods}</div>
			<div class="field checkbox-field">
				{assign var='shcShippingMethods' value=","|explode:$smarty.post.shc.shc_allowed_shipping_methods}
				<div>
					{foreach from=$shc_shipping_methods item='method'}
						<span class="custom-input">
							{assign var='shcItemKey' value=$method.key}
							<label title="{$method.name}">
								<input type="checkbox" class="shipping-method" {if $shcItemKey|in_array:$shcShippingMethods}checked="checked"{/if} name="shc[shc_allowed_shipping_methods][]" value="{$method.key}" />
								{$method.name}
							</label>
						</span>
					{/foreach}
				</div>
			</div>
		</div>

		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
		
        <div class="shipping-pickup">
		</div>

        <div class="shipping-courier">
		</div>

        <div class="shipping-dhl">
		</div>
		
		<div class="shipping-ups">
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_settings_ups' name=$lang.shc_settings_ups}
			
			<div class="submit-cell clearfix">			
				<div class="name">{$lang.shc_ups_pickup_methods}</div>
				<div class="field single-field">
					<select name="shc[shc_ups_pickup_methods]">
						{foreach from=$shc_ups_pickup_methods key='code' item='method'}
							<option value="{$code}" {if $smarty.post.shc.shc_ups_pickup_methods == $code}selected="selected"{/if}>{$method}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="submit-cell clearfix">
				<div class="name">{$lang.shc_ups_package_types}</div>
				<div class="field single-field">
					<select name="shc[shc_ups_package_type]">
						{foreach from=$shc_ups_package_types key='code' item='type'}
							<option value="{$code}" {if $smarty.post.shc.shc_ups_package_type == $code}selected="selected"{/if}>{$type}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="submit-cell clearfix">			
				<div class="name">{$lang.shc_ups_origin}</div>
				<div class="field single-field">
					<select name="shc[shc_ups_origin]" id="shc_ups_origin">
						{foreach from=$ups_origins item='origin'}
							<option value="{$origin.key}" {if $smarty.post.shc.shc_ups_origin == $origin.key}selected="selected"{/if}>{$origin.name}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="submit-cell clearfix">			
				<div class="name">{$lang.shc_ups_services}</div>
				<div class="field checkbox-field">
					{assign var='shcUPSServices' value=","|explode:$smarty.post.shc.shc_ups_services}
					<div id="shc_ups_services">
						{foreach from=$shc_ups_services item='service'}
							{assign var='shcOriginsItem' value=","|explode:$service.origin}
							{assign var='shcItemKey' value=$service.code}
							<div class="{if $smarty.post.shc.shc_ups_origin|in_array:$shcOriginsItem || (!$smarty.post.shc.shc_ups_origin && 'US'|in_array:$shcOriginsItem)}{else}hide{/if}">
								<label>
									<input class="checkbox" {if $shcItemKey|in_array:$shcUPSServices}checked="checked"{/if} id="shc_pickup_item{$service.code}" accesskey="{$service.origin}" type="checkbox" name="shc[shc_ups_services][]" value="{$service.code}" />
									{$service.name}
								</label>
							</div>
						{/foreach}
					</div>
				</div>
			</div>
			<div class="submit-cell clearfix">			
				<div class="name">{$lang.shc_ups_classification}</div>
				<div class="field single-field">
					<select name="shc[shc_ups_classification]">
						<option value="">{$lang.select}</option>
						{foreach from=$ups_classification item='classification'}
							<option value="{$classification}" {if $smarty.post.shc.shc_ups_classification == $classification}selected="selected"{/if}>{$classification}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="submit-cell clearfix">			
				<div class="name">{$lang.shc_ups_quote_type}</div>
				<div class="field single-field">
					<select name="shc[shc_ups_quote_type]">
						<option value="">{$lang.select}</option>
						{foreach from=$ups_quote_type item='quote_type'}
							<option value="{$quote_type.key}" {if $smarty.post.shc.shc_ups_quote_type == $quote_type.key}selected="selected"{/if}>{$quote_type.name}</option>
						{/foreach}
					</select>
				</div>	
			</div>
			
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
		</div>
		
		{rlHook name='shoppingCartAccountSettings'}

		<div class="submit-cell buttons">
			<div class="name"></div>
			<div class="field"><input type="submit" value="{$lang.save}" /></div>
		</div>
	</form>
</div>

<script type="text/javascript">
	{literal}
	$(document).ready(function(){
		$('#shc_ups_origin').change(function()
		{
			var origin_key = $(this).val();

			$('#shc_ups_services input[type="checkbox"]').each(function()
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