<!-- payAsYouGoCredits plugin -->

<div class="highlight">
{if !$purchasePage}
    {if $tpl_settings.type == 'responsive_42'}
		<div class="table-cell">
			<div class="name">{$lang.paygc_account_credits}</div>
			<div class="value"><b>{$creditsInfo.Total_credits}</b> {$lang.paygc_credits_count}</div>
		</div>
		{if $creditsInfo.Total_credits > 0 && $config.paygc_period > 0}
		<div class="table-cell">
			<div class="name">{$lang.paygc_expiration_date}</div>
			<div class="value">{$creditsInfo.Expiration_date|date_format:$smarty.const.RL_DATE_FORMAT}</div>
		</div>
		{/if}
		<div class="submit-cell">
			<div class="name"></div>
			<div class="search-button">
				<input type="button" class="button" value="{$lang.paygc_buy_credits}" onclick="location.href='{$rlBase}{if $config.mod_rewrite}{$pages.my_credits}/purchase.html{else}?page={$pages.my_credits}&amp;purchase{/if}'" />
	   			<a href="{$rlBase}{if $config.mod_rewrite}{$pages.payment_history}.html?credits{else}?page={$pages.payment_history}&amp;credits{/if}">{$lang.paygc_view_history}</a>		
			</div>
		</div>
	{else}
		<table class="table">
			<tr>
				<td class="name">{$lang.paygc_account_credits}:</td>
				<td class="value"><b>{$creditsInfo.Total_credits}</b> {$lang.paygc_credits_count}</td>
			</tr>
			{if $creditsInfo.Total_credits > 0 && $config.paygc_period > 0}
			<tr>
				<td class="name">{$lang.paygc_expiration_date}:</td>
				<td class="value">{$creditsInfo.Expiration_date|date_format:$smarty.const.RL_DATE_FORMAT}</td>
			</tr>
			{/if}
			<tr>
				<td></td>
				<td>
					<div style="padding: 10px 0px 5px;">
						<input type="button" class="button" value="{$lang.paygc_buy_credits}" onclick="location.href='{$rlBase}{if $config.mod_rewrite}{$pages.my_credits}/purchase.html{else}?page={$pages.my_credits}&amp;purchase{/if}'" />
					</div>
					<a href="{$rlBase}{if $config.mod_rewrite}{$pages.payment_history}.html?credits{else}?page={$pages.payment_history}&amp;credits{/if}">{$lang.paygc_view_history}</a>
				</td>
			</tr>
		</table>
	{/if}
{else}
	{if !empty($credits)}
		{if $lang.paygc_desc}
			{assign var='replace' value=`$smarty.ldelim`number`$smarty.rdelim`}
			<div class="dark" style="padding-bottom: 15px;">{$lang.paygc_desc|replace:$replace:$config.paygc_period}</div>
		{/if}

		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.my_credits}/purchase.html{else}?page={$pages.my_credits}&amp;purchase{/if}">
			<input type="hidden" name="submit" value="true" />
            
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='credit_list' name=$lang.paygc_give_youself_credits}
			
			{if $tpl_settings.type == 'responsive_42'}
				{include file=$smarty.const.RL_PLUGINS|cat:'payAsYouGoCredits'|cat:$smarty.const.RL_DS|cat:'packages_responsive_42.tpl'}
			{else}
				{include file=$smarty.const.RL_PLUGINS|cat:'payAsYouGoCredits'|cat:$smarty.const.RL_DS|cat:'packages.tpl'}
			{/if}

			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

			<div class="clear" style="height: 15px;"></div>

			<!-- select a payment gateway -->
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='gateways' name=$lang.payment_gateways}
				<ul id="payment_gateways">
					{if $config.use_paypal}
					<li>
						<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/paypal/paypal.png" />
						<p><input {if $smarty.post.gateway == 'paypal' || !$smarty.post.gateway}checked="checked"{/if} type="radio" name="gateway" value="paypal" /></p>
					</li>
					{/if}
					{if $config.use_2co}
					<li>
						<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/2co/2co.png" />
						<p><input {if $smarty.post.gateway == '2co'}checked="checked"{/if} type="radio" name="gateway" value="2co" /></p>
					</li>
					{/if}

					{rlHook name='paymentGateway'}
				</ul>

				<script type="text/javascript">
					flynax.paymentGateway();
				</script>

			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			<!-- select a payment gateway end -->

			<input type="submit" value="{$lang.checkout}" />
		</form>
	{else}
		<div class="info">{$lang.paygc_no_packages}</div>
	{/if}
{/if}
</div>

<!-- end payAsYouGoCredits plugin -->