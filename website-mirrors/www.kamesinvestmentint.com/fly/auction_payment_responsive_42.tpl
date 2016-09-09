<!-- action payment page | responsive 42  -->

<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}shoppingCart/static/lib.js"></script>

<!-- steps -->
{if $auction_info}
	{assign var='allow_link' value=true}
	{assign var='current_found' value=false}
	<ul class="steps">
		{math assign='step_width' equation='round(100/count, 3)' count=$shc_auction_steps|@count}
		{foreach from=$shc_auction_steps item='step' name='stepsF' key='step_key'}{strip}
			{if $cur_step == $step_key || !$cur_step}{assign var='allow_link' value=false}{/if}
			<li style="width: {$step_width}%;" class="{if $cur_step}{if $cur_step == $step_key}current{assign var='current_found' value=true}{elseif !$current_found}past{/if}{elseif $smarty.foreach.stepsF.first}current{/if}">
				<a href="{if $allow_link}{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}{if $step.path}/{$step.path}{/if}.html?item={$auction_info.ID}{else}?page={$pages.shc_auction_payment}&amp;step={$steps.$step_key.path}&item={$auction_info.ID}{/if}{else}javascript:void(0){/if}" title="{$step.name}">{if $step.caption}<span>{$lang.step}</span> {$smarty.foreach.stepsF.iteration}{else}{$step.name}{/if}</a>
			</li>
		{/strip}{/foreach}
	</ul>

	<h1>{$pageInfo.name}</h1>
{/if}

{if $cur_step == 'cart'}

	<!-- cart details -->
	<div class="area_cart step_area content-padding" id="cart_items">
		<div class="list-table row-align-middle no-controls">
			<div class="header">
				<div class="center" style="width: 40px;">#</div>
				<div>{$lang.shc_item}</div>
				<div style="width: 150px;">{$lang.shc_total}</div>
			</div>

			<div class="row">
				<div class="center iteration no-flex">1</div>
				<div data-caption="{$lang.shc_item}">
					{if $auction_info.Main_photo}
						<a href="{$auction_info.listing_link}" target="_blank"><img alt="{$item.title}" style="width: 70px;margin-{$text_dir_rev}: 10px;" src="{$smarty.const.RL_FILES_URL}{$auction_info.Main_photo}" /></a>
					{/if}
					<div class="inline"><a href="{$auction_info.listing_link}" target="_blank">{$auction_info.title}</a></div>	
				</div>
				<div data-caption="{$lang.shc_total}">
					<span class="price-cell">
						{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
						{$auction_info.Total|number_format:2:'.':','}
						{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
					</span>
				</div>
			</div>
		</div>

		<div align="right" style="padding: 20px 0 0 0;">
			<a class="button" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}/{$shc_auction_steps.shipping.path}.html?item={$auction_info.ID}{else}?page={$pages.shc_auction_payment}&step={$shc_auction_steps.shipping.path}&item={$auction_info.ID}{/if}">{$lang.next_step}</a>
		</div>
	</div>

{elseif $cur_step == 'shipping'}
	<div class="area_shipping step_area content-padding hide">

		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}/{$shc_auction_steps.shipping.path}.html?item={$auction_info.ID}{else}?page={$pages.shc_auction_payment}&step={$shc_auction_steps.shipping.path}&item={$auction_info.ID}{/if}">

			{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'shipping_fields_responsive_42.tpl'}

			<input type="hidden" name="form" value="submit" />

			<span class="form-buttons" style="padding-top: 0;">
				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}{if $prev_step.path}/{$prev_step.path}{/if}.html?item={$auction_info.ID}{else}?page={$pages.shc_auction_payment}{if $prev_step.path}&amp;step={$prev_step.path}&item={$auction_info.ID}{/if}{/if}">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<input type="submit" value="{$lang.next_step}" />
			</span>
		</form>
	</div>

{elseif $cur_step == 'confirmation'}

 	<div class="area_confirmation step_area hide">

		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}/{$shc_auction_steps.confirmation.path}.html?item={$auction_info.ID}{else}?page={$pages.shc_auction_payment}&step={$shc_auction_steps.confirmation.path}&item={$auction_info.ID}{/if}">
			<div class="list-table row-align-middle no-controls">
				<div class="header">
					<div class="center" style="width: 40px;">#</div>
					<div>{$lang.shc_item}</div>
					<div style="width: 100px;">{$lang.shc_total}</div>
				</div>

				<div class="row">
					<div class="center iteration no-flex">1</div>
					<div data-caption="{$lang.shc_item}">
						{if $auction_info.Main_photo}
							<a href="{$auction_info.listing_link}" target="_blank"><img alt="{$item.title}" style="width: 70px;margin-{$text_dir_rev}: 10px;" src="{$smarty.const.RL_FILES_URL}{$auction_info.Main_photo}" /></a>
						{/if}
						<div class="inline"><a href="{$auction_info.listing_link}" target="_blank">{$auction_info.title}</a></div>	
					</div>
					<div data-caption="{$lang.shc_total}">
						<span class="price-cell">
							{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
							{$auction_info.Total|number_format:2:'.':','}
							{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
						</span>
					</div>
				</div>
			</div>

			<div class="two-inline" style="padding: 20px 0;">
				<div class="shc_price" style="width: 102px;padding-top: 3px;">
					{if !$quote.error}
						{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
						{$quote.quote|number_format:2:'.':','}
						{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
					{else}
						{$lang.shc_shipping_failed}
						<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}/{$shc_auction_steps.shipping.path}.html{else}?page={$pages.shc_auction_payment}&amp;step={$shc_auction_steps.shipping.path}{/if}">{$lang.shc_change_shipping_method}</a>
					{/if}
				</div>
				<div class="ralign shipping-summary">
					<table class="table lalign" align="{$text_dir_rev}" style="width: auto;">
					{if !$quote.error}
						<tr>
							<td class="name">{$lang.shc_quote_code}:</td>
							<td class="value">{$quote.code}</td>
						</td>
						{if $quote.title}
						<tr>
							<td class="name">{$lang.shc_quote_title}:</td>
							<td class="value">{$quote.title}</td>
						</td>
						{/if}
						{if $quote.days}
						<tr>
							<td class="name">{$lang.shc_quote_days}:</td>
							<td class="value">{$quote.days}</td>
						</td>
						{/if}
					{else}
						<tr>
							<td class="name">{$lang.shc_quote_code}:</td>
							<td class="value">{$quote.code}</td>
						</td>
						<tr>
							<td colspan="2" class="value"><span class="red">{$quote.error}</span></td>
						</td>
					{/if}
					</table>
				</div>
			</div>

			<div class="two-inline" style="padding: 0  0 20px 0;">
				<div class="shc_price" style="width: 102px;">
					{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
					<span id="total_{$shcDealer}">{$auction_info.total_price|number_format:2:'.':','}</span>
					{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
				</div>
				<div class="ralign shipping-summary">
					{$lang.shc_total_cost}
				</div>
			</div>
			
			<input type="hidden" name="form" value="submit" />
			<input type="hidden" name="shipping_price" value="{$quote.quote}" />

			<span class="form-buttons ralign" style="padding-top: 0;">
				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}{if $prev_step.path}/{$prev_step.path}{/if}.html?item={$auction_info.ID}{else}?page={$pages.shc_auction_payment}{if $prev_step.path}&amp;step={$prev_step.path}&item={$auction_info.ID}{/if}{/if}">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<input type="submit" value="{$lang.next_step}" />
			</span>
		</form>

	</div>

{elseif $cur_step == 'checkout'}
 	<div class="area_checkout step_area hide">

		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_auction_payment}/{$shc_auction_steps.checkout.path}.html?item={$auction_info.ID}{else}?page={$pages.shc_auction_payment}&step={$shc_auction_steps.checkout.path}&item={$auction_info.ID}{/if}">

			<!-- select a payment gateway -->
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='gateways' name=$lang.payment_gateways}
                         
				<ul id="payment_gateways">
					{foreach from=$shc_payment_gateways item='gateway'}
						{if $gateway.key == 'paypal'}
							<li>
								<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/paypal/paypal.png" />
								<p><input {if $smarty.post.gateway == 'paypal' || !$smarty.post.gateway}checked="checked"{/if} type="radio" name="gateway" value="paypal" /></p>
							</li>
						{elseif $gateway.key == 'paypal'}
							<li>
								<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/2co/2co.png" />
								<p><input {if $smarty.post.gateway == '2co'}checked="checked"{/if} type="radio" name="gateway" value="2co" /></p>
							</li>
						{elseif $gateway.key == 'bankWireTransfer'}
							{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'gateway.tpl'}
						{else}			
							<li id="gateway_{$gateway.key}">
								<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/{$gateway.key}/{$gateway.key}.png" />
								<p><input {if $smarty.post.gateway == $gateway.key}checked="checked"{/if} type="radio" name="gateway" value="{$gateway.key}" /></p>
							</li>
						{/if}
					{/foreach}
				</ul>

				<script type="text/javascript">
					flynax.paymentGateway();
				</script>

			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			<!-- select a payment gateway end -->

			<input type="hidden" name="form" value="submit" />

			<span class="form-buttons" style="padding-top: 0;">
				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}{if $prev_step.path}/{$prev_step.path}{/if}.html{else}?page={$pages.shc_my_shopping_cart}{if $prev_step.path}&amp;step={$prev_step.path}{/if}{/if}">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<input type="submit" value="{$lang.shc_checkout}" />
			</span>

		</form>
	</div>

{elseif $cur_step == 'done' }
    {if $shcIsPaid}
		<div class="area_done step_area hide">
			<div class="text-message">{$lang.shc_done_notice}</div>
		</div>
	{/if}
{/if}

<script type="text/javascript">

{if $cur_step}
	flynax.switchStep('{$cur_step}');
{/if}

{literal}

$(document).ready(function(){
	$("input.numeric").numeric();

	$('#shipping_comment').textareaCount({
		'maxCharacterSize': rlConfig['messages_length'],
		'warningNumber': 20
	});             
});

{/literal}
</script>

<!-- action payment page end | responsive 42  -->