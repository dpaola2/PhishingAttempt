<!-- my shopping cart page | responsive 42  -->

<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}shoppingCart/static/lib.js"></script>

<!-- steps -->
{if $items_tmp}
	{assign var='allow_link' value=true}
	{assign var='current_found' value=false}
	<ul class="steps">
		{math assign='step_width' equation='round(100/count, 3)' count=$shc_steps|@count}
		{foreach from=$shc_steps item='step' name='stepsF' key='step_key'}{strip}
			{if $cur_step == $step_key || !$cur_step}{assign var='allow_link' value=false}{/if}
			<li style="width: {$step_width}%;" class="{if $cur_step}{if $cur_step == $step_key}current{assign var='current_found' value=true}{elseif !$current_found}past{/if}{elseif $smarty.foreach.stepsF.first}current{/if}">
				<a href="{if $allow_link}{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}{if $step_key == 'category'}.html?edit{else}/{$category.Path}/{$steps.$step_key.path}.html{/if}{else}?page={$pageInfo.Path}&amp;id={$category.ID}&amp;step={$steps.$step_key.path}{if $step_key == 'category'}&amp;edit{/if}{/if}{else}javascript:void(0){/if}" title="{$step.name}">{if $step.caption}<span>{$lang.step}</span> {$smarty.foreach.stepsF.iteration}{else}{$step.name}{/if}</a>
			</li>
		{/strip}{/foreach}
	</ul>
{/if}

<h1>{$pageInfo.name}</h1>

{if $cur_step == 'cart'}
	<!-- cart details -->
	<div class="area_cart step_area content-padding" id="cart_items">
		{if $items}
			{if $config.shc_method == 'single'}
				<form id="form_single" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html{else}?page={$pages.shc_my_shopping_cart}{/if}">	
					{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'items_responsive_42.tpl' shcItems=$items shcDealer='single' shcTotal=$total shcDelivery=$delivery}
				</form>
			{elseif $config.shc_method == 'multi'}
				{foreach from=$items item='dealer'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shopping_cart_dealer_'|cat:$dealer.Dealer_ID name=$dealer.Full_name}
						<form id="form_{$dealer.Dealer_ID}" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html{else}?page={$pages.shc_my_shopping_cart}{/if}">	
							{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'items_responsive_42.tpl' shcItems=$dealer.items shcDealer=$dealer.Dealer_ID shcTotal=$dealer.total shcDelivery=$dealer.delivery}
						</form>

					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
				{/foreach}
			{/if} 
		{else}
			<div class="text-message">{$lang.shc_empty_cart}</div>
		{/if}
	</div>

	<script type="text/javascript">
	{literal}
	
	$(document).ready(function(){
		$('div#cart_items .shc_delete_item').each(function() {
			$(this).flModal({
				caption: '{/literal}{$lang.warning}{literal}',
				content: '{/literal}{$lang.shc_notice_delete_item}{literal}',
				prompt: 'xajax_deleteItem('+ $(this).attr('id').split('_')[1] +', '+ $(this).attr('id').split('_')[2] +', true)',
				width: 'auto',
				height: 'auto'
			});
		});
	});
	
	{/literal}
	</script>
	<!-- end cart details -->
{elseif $cur_step == 'auth'}
	<div class="area_auth step_area hide">

		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}/{$shc_steps.auth.path}.html{else}?page={$pages.shc_my_shopping_cart}&step={$shc_steps.auth.path}{/if}">
			<!-- registration/login  -->
			<div class="auth">{strip}
				<div class="cell">
					<div>
						<div class="caption">{$lang.sign_in}</div>

						<div class="name">{$lang.username}</div>
						<input class="w180" type="text" name="login[username]" maxlength="25" value="{$smarty.post.login.username}" />

						<div class="name">{$lang.password}</div>
						<input class="w180" type="password" name="login[password]" maxlength="25" />

						<div style="padding-top: 15px;">{$lang.forgot_pass} <a target="_blank" title="{$lang.remind_pass}" href="{$rlBase}{if $config.mod_rewrite}{$pages.remind}.html{else}?page={$pages.remind}{/if}">{$lang.remind}</a></div>
					</div>
				</div>
				<div class="divider">{$lang.or}</div>
				<div class="cell">
					<div>
						<div class="caption">{$lang.sign_up}</div>

						<div class="name">{$lang.your_name}</div>
						<input class="w180" type="text" name="register[name]" maxlength="100" value="{$smarty.post.register.name}" />

						<div class="name">{$lang.your_email}</div>
						<input class="w180" type="text" name="register[email]" maxlength="150" value="{$smarty.post.register.email}"  />
					</div>
				</div>
			{/strip}</div>
			
			<script type="text/javascript">
			{literal}
			
			$(document).ready(function(){
				$('input[name="register[name]"],input[name="register[email]"]').keydown(function(){
					$('input[name="login[username]"],input[name="login[password]"]').val('');
				});
				$('input[name="login[username]"],input[name="login[password]"]').keydown(function(){
					$('input[name="register[name]"],input[name="register[email]"]').val('');
				});
			});
			
			{/literal}
			</script>
			<!-- end registration/login -->

			<input type="hidden" name="form" value="submit" />
		
			<span class="form-buttons">
				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}{if $prev_step.path}/{$prev_step.path}{/if}.html{else}?page={$pages.shc_my_shopping_cart}{if $prev_step.path}&amp;step={$prev_step.path}{/if}{/if}">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<input type="submit" value="{$lang.next_step}" />
			</span>
		</form>
	</div>

{elseif $cur_step == 'shipping'}
    <div class="area_shipping step_area content-padding hide">
		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}/{$shc_steps.shipping.path}.html{else}?page={$pages.shc_my_shopping_cart}&step={$shc_steps.shipping.path}{/if}">
			{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'shipping_fields_responsive_42.tpl'}

			<input type="hidden" name="form" value="submit" />

			<span class="form-buttons" style="padding-top: 0;">
				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}{if $prev_step.path}/{$prev_step.path}{/if}.html{else}?page={$pages.shc_my_shopping_cart}{if $prev_step.path}&amp;step={$prev_step.path}{/if}{/if}">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<input type="submit" value="{$lang.next_step}" />
			</span>
		</form>
	</div>

{elseif $cur_step == 'confirmation'}
    <div class="area_confirmation step_area content-padding hide">
		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}/{$shc_steps.confirmation.path}.html{else}?page={$pages.shc_my_shopping_cart}&step={$shc_steps.confirmation.path}{/if}">

			{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'items_responsive_42.tpl' shcItems=$order_info.items preview=true}

			<div class="two-inline" style="padding: 20px 0;">
				<div class="shc_price" style="width: 102px;padding-top: 3px;">
					{if !$quote.error}
						{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
						{$quote.quote|number_format:2:'.':','}
						{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
					{else}
						{$lang.shc_shipping_failed}
						<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}/{$shc_steps.shipping.path}.html{else}?page={$pages.shc_my_shopping_cart}&amp;step={$shc_steps.shipping.path}{/if}">{$lang.shc_change_shipping_method}</a>
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
					<span id="total_{$shcDealer}">{$order_info.total_price|number_format:2:'.':','}</span>
					{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
				</div>
				<div class="ralign shipping-summary">
					{$lang.shc_total_cost}
				</div>
			</div>
			
			<input type="hidden" name="form" value="submit" />
			<input type="hidden" name="shipping_price" value="{$quote.quote}" />

			<span class="form-buttons ralign" style="padding-top: 0;">
				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}{if $prev_step.path}/{$prev_step.path}{/if}.html{else}?page={$pages.shc_my_shopping_cart}{if $prev_step.path}&amp;step={$prev_step.path}{/if}{/if}">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<input type="submit" value="{$lang.next_step}" />
			</span>
		</form>
	</div>

{elseif $cur_step == 'checkout'}
    <div class="area_checkout step_area content-padding hide">

		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}/{$shc_steps.checkout.path}.html{else}?page={$pages.shc_my_shopping_cart}&step={$shc_steps.checkout.path}{/if}">
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
		<div class="area_done content-padding step_area hide">
			<div class="text-message">{$lang.shc_done_notice}</div>
		</div>
	{/if}
{/if}

<script type="text/javascript">
var shc_dealer = '{$shcDealer}';

{if $cur_step}
	flynax.switchStep('{$cur_step}');
{/if}

{literal}
	$(document).ready(function(){
		$('#shipping_comment').textareaCount({
			'maxCharacterSize': rlConfig['messages_length'],
			'warningNumber': 20
		});
		
		shoppingCart.handlerItems();             
	});
{/literal}
</script>

<!-- steps -->

<!-- my shopping cart page end  -->