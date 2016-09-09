<!-- shoppingCart plugin -->

{if $listing_data.shc_mode == 'auction' || $listing_data.shc_mode == 'fixed'}
	{assign var='is_aution_active' value=false}
	{if $listing_data.shc.time_left_value > 0 && $listing_data.shc_auction_status != 'closed'}
		{assign var='is_aution_active' value=true}
	{/if}

	<div class="shc-group{if $config.shc_use_box} shc-custom-box{/if}">
		{if $listing_data.shc_mode == 'auction'}
			<div class="auction-details{if !$is_aution_active} closed{/if}{if !$isLogin} not-logged-in{/if}">
				{if $is_aution_active}
					<ul>
						<li>
							<div class="name">{$lang.shc_time_left}</div>
							<div class="value">{$listing_data.shc.time_left}</div>
						</li><li>
							<div class="name">{if $listing_data.shc.total_bids > 0}{$lang.shc_current_bid}{else}{$lang.shc_starting_bid}{/if}</div>
							<span class="value">
								{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
								<span id="current_price">{$listing_data.shc.current_bid}</span>
								{if $config.system_currency_position == 'after'} {$config.system_currency}{/if}
							</span>
							[ <a href="javascript:void(0);" id="bid_history"><span id="total_bids">{$listing_data.shc.total_bids}</span> {$lang.shc_bids}</a> ]	
						</li>
					</ul>

					{if $isLogin}
						<div class="field">
							<input placeholder="{$lang.shc_min_bid|replace:'[total]':$listing_data.shc.min_rate_bid}" type="text" class="numeric w70" name="rate_bid" id="rate_bid" /><a class="button" href="javascript:void(0);" id="shc_add_bid">{$lang.shc_add_bid}</a>
						</div>
					{else}
						<div class="info">{$shc_add_bid_not_login}</div>
					{/if}
				{else}
					{$lang.shc_auction_closed}
				{/if}

				{if $winner_info}
					<div class="table-cell" style="margin: 5px 0 0 0;">
						<div class="name">{$lang.shc_winner}:</div>
						<div class="value">{$winner_info.Full_name}</div>
					</div>
				{/if}
			</div>

			{if $is_aution_active}
				<div style="padding: 20px 0 12px;">
					<a class="button cart" href="javascript:void(0);" id="shc_by_now_item">{$lang.shc_buy_now}</a>
					{if $listing_data.shc_quantity > 0}
						<a class="button add-to-cart cart icon" style="margin-{$text_dir}: 20px;" href="javascript:void(0);" id="shc-item-{$listing_data.ID}">{$lang.shc_add_to_cart}</a>
					{/if}
				</div>
			{/if}

			<table class="table">
			<tr>
				{assign var='shc_lf_name' value='listing_fields+name+shc_weight'}
				{assign var='shc_weight_unit' value='shc_weight_unit_'|cat:$config.shc_weight_unit}
				<td class="name">{$lang[$shc_lf_name]}</td>
				<td class="value">
					{$listing_data.shc_weight} {$lang[$shc_weight_unit]}
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_available_payment_gateways}</td>
				<td>
					{if $shc_payment_gateways}
					<ul class="shc-payment-gateways">
						{foreach from=$shc_payment_gateways item='pgateway'}
							<li>
								{if $pgateway.key == 'paypal'}
									<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/paypal/paypal.png" />
								{elseif $pgateway.key == '2co'}
									<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/2co/2co.png" />
								{elseif $pgateway.key == 'bankWireTransfer'}
									{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'bank_transfer_listing_details.tpl'}
								{else}
									<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/{$pgateway.key}.png" />
								{/if}
							</li>
						{/foreach}
					</ul>
					{else}
						<div class="notice">{$lang.shc_not_available_payment_gateways}</div>
					{/if}
				</td>
			</tr>
			<tr>
				<td colspan="2" class="name">{$lang.shc_available_shipping_methods}</td>
			</tr>
			</table>

			<div class="table-cell">
				<ul class="checkboxes clearfix">
					{foreach from=$shc_shipping_methods item='method'}
						<li class="active" title="{$method.name}"><img alt="" src="{$rlTplBase}img/blank.gif" />{$method.name}</li>
					{/foreach}
				</ul>
			</div>
		{elseif $listing_data.shc_mode == 'fixed'}
			{if $listing_data.shc_quantity > 0}
				<div style="margin-bottom: 15px;">
					<a class="button cart icon add-to-cart" href="javascript:void(0);" id="shc-item-{$listing_data.ID}">{$lang.shc_add_to_cart}</a>
				</div>
			{/if}

			<table class="table">
				<tr>
					{assign var='shc_lf_name' value='listing_fields+name+shc_weight'}
					{assign var='shc_weight_unit' value='shc_weight_unit_'|cat:$config.shc_weight_unit}
					<td class="name">{$lang[$shc_lf_name]}</td>
					<td class="value">
						{$listing_data.shc_weight} {$lang[$shc_weight_unit]}
					</td>
				</tr> 
				<tr id="df_field_shc_quantity">
					{assign var='shc_lf_name' value='listing_fields+name+shc_quantity'}
					<td class="name">{$lang[$shc_lf_name]}</td>
					<td class="value">
						{$listing_data.shc_quantity}
					</td>
				</tr> 
				<tr>
					{assign var='shc_lf_name' value='listing_fields+name+shc_available'}
					<td class="name">{$lang[$shc_lf_name]}</td>
					<td class="value">
						{if $listing_data.shc_available}{$lang.yes}{else}{$lang.no}{/if}
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.shc_available_payment_gateways}</td>
					<td>
						{if $shc_payment_gateways}
						<ul class="shc-payment-gateways">
							{foreach from=$shc_payment_gateways item='pgateway'}
								<li>
									{if $pgateway.key == 'paypal'}
										<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/paypal/paypal.png" />
									{elseif $pgateway.key == '2co'}
										<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/2co/2co.png" />
									{elseif $pgateway.key == 'bankWireTransfer'}
										{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'bank_transfer_listing_details.tpl'}
									{else}
										<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/{$pgateway.key}.png" />
									{/if}
								</li>
							{/foreach}
						</ul>
						{else}
							<div class="notice">{$lang.shc_not_available_payment_gateways}</div>
						{/if}
					</td>
				</tr>
				<tr>
					<td colspan="2" class="name">{$lang.shc_available_shipping_methods}</td>
				</tr>
			</table>

			<div class="table-cell">
				<ul class="checkboxes clearfix">
					{foreach from=$shc_shipping_methods item='method'}
						<li class="active" title="{$method.name}"><img alt="" src="{$rlTplBase}img/blank.gif" />{$method.name}</li>
					{/foreach}
				</ul>
			</div>
		{/if}
	</div>
	<script type="text/javascript">
	{literal}

	$(document).ready(function(){
		/* if custom template */
		{/literal}{if $config.shc_use_box}{literal}$('div.details>div.top-navigation').after($('div.shc-group'));{/literal}{/if}{literal}

		/* add item to my cart */
		$('a#shc_add_bid').each(function() {
			$(this).flModal({
				caption: '',
				content: '{/literal}{if $isLogin}{$lang.shc_do_you_want_to_add_bid}{/if}{literal}',
				{/literal}{if $isLogin}{literal}prompt: 'shcAddBid()'{/literal}{else}{literal}source:'#login_modal_source'{/literal}{/if}{literal},
				width: 'auto',
				height: 'auto'
			});
		});

		$('#bid_history').click(function()
		{
			$('#tab_shoppingCart').trigger('click');
			flynax.slideTo('.bid-history-header');
		});

		/* buy now */
		$('#shc_by_now_item').click(function()
		{
			xajax_addItem('{/literal}{$listing_data.ID}{literal}');
			setTimeout('location.href="{/literal}{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html?item={$listing_data.ID}{else}?page={$pages.shc_my_shopping_cart}&amp;item={$listing_data.ID}{/if}{literal}"', 3000);
		});

		/* add price */		
		$('#price_buy_now').html($('#df_field_price').html());
	});

	var shcAddBid = function()
	{
		if($('#rate_bid').val() == '')
		{
			printMessage('error', '{/literal}{$lang.shc_empty_bid_value}{literal}');
			$('#rate_bid').focus();
			return;
		}

		xajax_addBid('{/literal}{$listing_data.ID}{literal}', $('#rate_bid').val());
	}

	{/literal}
	</script>
{/if}

<!-- end shoppingCart plugin -->