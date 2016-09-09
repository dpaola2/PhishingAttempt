<!-- shoppingCart plugin -->

{if $tpl_settings.type == 'responsive_42'}
	
	{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'my_shopping_cart_responsive_42.tpl'}

{else}

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/numeric.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.textareaCounter.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}shoppingCart/static/lib.js"></script>

<!-- steps -->
{if $items_tmp}
	<table class="steps">
		<tr>
			{assign var='allow_link' value=true}
			{foreach from=$shc_steps item='step' name='stepsF' key='step_key'}
				{if $cur_step == $step_key || !$cur_step}
					{assign var='allow_link' value=false}
				{/if}
				<td id="step_{$step_key}" class="{if $smarty.foreach.stepsF.first}active{/if}{if !$show_step_caption && $smarty.foreach.stepsF.last} last{/if}">
					<div><a href="{if $allow_link}{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}{if $step.path}/{$step.path}{/if}.html{else}?page={$pages.shc_my_shopping_cart}&amp;step={$steps.$step_key.path}{/if}{else}javascript:void(0){/if}" title="{$step.name}">{if $step.caption}<b>{$smarty.foreach.stepsF.iteration}</b>{if $show_step_caption}. {$step.name}{/if}{else}{$step.name}{/if}</a></div>
				</td>
			{/foreach}
		</tr>
	</table>
{/if}
<!-- steps -->

<div class="highlight">
	{if $cur_step == 'cart'}

		<!-- cart details -->
		<div class="area_cart step_area" id="cart_items">

			{if $items}
				{if $config.shc_method == 'single'}
					<form id="form_single" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html{else}?page={$pages.shc_my_shopping_cart}{/if}">	
						{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'items.tpl' shcItems=$items shcDealer='single' shcTotal=$total shcDelivery=$delivery}
					</form>
				{elseif $config.shc_method == 'multi'}
					{foreach from=$items item='dealer'}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shopping_cart_dealer_'|cat:$dealer.Dealer_ID name=$dealer.Full_name}
							<form id="form_{$dealer.Dealer_ID}" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html{else}?page={$pages.shc_my_shopping_cart}{/if}">	
								{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'items.tpl' shcItems=$dealer.items shcDealer=$dealer.Dealer_ID shcTotal=$dealer.total shcDelivery=$dealer.delivery}
							</form>

						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
					{/foreach}
				{/if} 
			{else}
				<div class="info">{$lang.shc_empty_cart}</div>
			{/if}
		</div>

		<script type="text/javascript">
		{literal}
		
		$(document).ready(function(){
			$('div#cart_items img.shc_delete_item').each(function() {
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
				<div class="step-body">
					<div class="auth">
						<table>
							<tr>
								<td align="center">
									<div class="caption">{$lang.sign_in}</div>
								</td>
								<td class="divider"></td>
								<td align="center">
									<div class="caption">{$lang.sign_up}</div>
								</td>
							</tr>
							<tr>
								<td class="side">
									<div class="lalign">
										<div class="caption">{$lang.shc_login}</div>

										<div class="name">{$lang.username}</div>
										<input class="w180" type="text" name="login[username]" maxlength="25" value="{$smarty.post.login.username}" />

										<div class="name">{$lang.password}</div>
										<input class="w180" type="password" name="login[password]" maxlength="25" />

										<div><span class="black_12">{$lang.forgot_pass}</span> <a target="_blank" title="{$lang.remind_pass}" class="brown_12" href="{$rlBase}{if $config.mod_rewrite}{$pages.remind}.html{else}?page={$pages.remind}{/if}">{$lang.remind}</a></div>
									</div>
								</td>
								<td class="divider">{$lang.or}</td>
								<td class="side">
									<div class="lalign">
										<div class="caption">{$lang.shc_new_customer}</div>

										<div class="name">{$lang.your_name}</div>
										<input class="w180" type="text" name="register[name]" maxlength="100" value="{$smarty.post.shipping.name}" />
							                                                                                                                          
										<div class="name">{$lang.your_email}</div>
										<input class="w180" type="text" name="register[email]" maxlength="150" value="{$smarty.post.shipping.email}"  />

									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<!-- end registration/login 	-->

				<input type="hidden" name="form" value="submit" />
			
				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}{if $prev_step.path}/{$prev_step.path}{/if}.html{else}?page={$pages.shc_my_shopping_cart}{if $prev_step.path}&amp;step={$prev_step.path}{/if}{/if}" class="dark_12">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<span class="arrow"><input type="submit" value="{$lang.next_step}" id="form_submit" /><label for="form_submit" class="right">&nbsp;</label></span>

			</form>
		</div>

	{elseif $cur_step == 'shipping'}
    	<div class="area_shipping step_area hide">

			<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}/{$shc_steps.shipping.path}.html{else}?page={$pages.shc_my_shopping_cart}&step={$shc_steps.shipping.path}{/if}">

				<div class="step-body">
					{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'shipping_fields.tpl'}
				</div>

				<input type="hidden" name="form" value="submit" />

				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}{if $prev_step.path}/{$prev_step.path}{/if}.html{else}?page={$pages.shc_my_shopping_cart}{if $prev_step.path}&amp;step={$prev_step.path}{/if}{/if}" class="dark_12">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<span class="arrow"><input type="submit" value="{$lang.next_step}" id="form_submit" /><label for="form_submit" class="right">&nbsp;</label></span>
			</form>
		</div>

	{elseif $cur_step == 'confirmation'}

     	<div class="area_confirmation step_area hide">

			<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}/{$shc_steps.confirmation.path}.html{else}?page={$pages.shc_my_shopping_cart}&step={$shc_steps.confirmation.path}{/if}">
                <table class="list">
					<tr class="header">
						<td align="center" class="no_padding" style="width: 90px;"></td>
						<td class="divider"></td>
						<td>{$lang.shc_item}</td>
						<td class="divider"></td>
						<td width="60"><div class="text-overflow">{$lang.shc_price}</div></td>
						<td class="divider"></td>
						<td width="80"><div class="text-overflow">{$lang.shc_quantity}</div></td>
						<td class="divider"></td>
						<td width="60"><div class="text-overflow">{$lang.shc_total}</div></td>
					</tr>
					{foreach from=$order_info.items item='item' name='invoiceF'}
					<tr class="body" id="item_{$item.ID}">
						<td class="photo" valign="top" align="center">
							<a href="{$item.listing_link}" target="_blank">
								<img alt="{$item.title}" style="width: 70px;" src="{if empty($item.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$item.Main_photo}{/if}" />
							</a>
						</td>
						<td class="divider"></td>
						<td class="text-overflow">
							<a href="{$item.listing_link}" target="_blank">{$item.Item}</a>
						</td>
						<td class="divider"></td>
						<td style="white-space: nowrap;">{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.Price|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</td>
						<td class="divider"></td>
						<td align="center">{$item.Quantity}</td>
						<td class="divider"></td>
						<td style="white-space: nowrap;" align="center">{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="price_{$item.ID}">{$item.total|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</td>
					</tr>
					{/foreach}

					<!-- Shipping -->
					<tr class="body">
						<td style="text-align: right" colspan="7">
							<div>{$lang.shc_shipping_details}</div>
							{if !$quote.error}
								<div class="dark_12">{$lang.shc_quote_code}: {$quote.code}</div>
								{if $quote.title}<div class="dark_12">{$lang.shc_quote_title}: {$quote.title}</div>{/if}
								{if $quote.days}<div class="dark_12">{$lang.shc_quote_days}: {$quote.days}</div>{/if}
							{else}
								<div class="dark_12">{$lang.shc_quote_code}: {$quote.code}</div>
								<span class="red">{$quote.error}</span>
								
							{/if}
						</td>
						<td class="divider"></td>	
						<td class="shc_price">
							{if !$quote.error}
								<div>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$quote.quote|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</div>
							{else}
								{$lang.shc_shipping_failed}&nbsp;<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}/{$shc_steps.shipping.path}.html{else}?page={$pages.shc_my_shopping_cart}&amp;step={$shc_steps.shipping.path}{/if}">{$lang.shc_change_shipping_method}</a>
							{/if}
						</td>
					</tr>

					<!-- Total -->
					<tr>
						<td style="text-align: right" colspan="7" class="shc_value">
							{$lang.shc_total_cost}		
						</td>
						<td class="divider"></td>	
						<td class="shc_price shc_value">	
							<div>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="total_{$shcDealer}">{$order_info.total_price|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</div>
						</td>
					</tr>
				</table>
				
				<input type="hidden" name="form" value="submit" />
				<input type="hidden" name="shipping_price" value="{$quote.quote}" />

				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}{if $prev_step.path}/{$prev_step.path}{/if}.html{else}?page={$pages.shc_my_shopping_cart}{if $prev_step.path}&amp;step={$prev_step.path}{/if}{/if}" class="dark_12">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<span class="arrow"><input type="submit" value="{$lang.next_step}" id="form_submit" /><label for="form_submit" class="right">&nbsp;</label></span>
			</form>
		</div>

	{elseif $cur_step == 'checkout'}
     	<div class="area_checkout step_area hide">

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
									<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$gateway.key}/static/{$gateway.key}.png" />
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
				
				<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}{if $prev_step.path}/{$prev_step.path}{/if}.html{else}?page={$pages.shc_my_shopping_cart}{if $prev_step.path}&amp;step={$prev_step.path}{/if}{/if}" class="dark_12">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
				<span class="arrow"><input type="submit" value="{$lang.shc_checkout}" /><label for="form_submit" class="right">&nbsp;</label></span>

			</form>
		</div>

	{elseif $cur_step == 'done' }
        {if $shcIsPaid}
			<div class="area_done step_area hide">
				<div class="caption">{$lang.reg_done}</div>

				<div class="info">{$lang.shc_done_notice}</div>
			</div>
		{/if}
	{/if}
</div>

<script type="text/javascript">

var shc_dealer = '{$shcDealer}';

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

	shoppingCart.handlerItems();
});

{/literal}
</script>

{/if}

<!-- end shoppingCart plugin -->