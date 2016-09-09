<!-- googleWallet plugin  -->

<div class="highlight">
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='order_information' name=$lang.google_wallet_order_details tall=true}

	<table class="table">
		<tr>
			<td class="name" width="180">{$lang.item}</td>
			<td class="value">{$smarty.session.complete_payment.item_name}</td>
		</tr>
		<tr>
			<td class="name" width="180">{$lang.txn_id}</td>
			<td class="value">{$txn_info.Txn_ID}</td>
		</tr>
		<tr>
			<td class="name">{$lang.total}</td>
			<td class="value">{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}{$txn_info.total}{if $config.system_currency_position == 'after'} {$config.system_currency}{/if}</td>
		</tr>
	</table>
		
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

	<div>
		<input id="google_pay" type="submit" value="{$lang.google_wallet_submit}"/>&nbsp;&nbsp;
		<input type="button" value="{$lang.cancel}" onclick="location.href='{$rlBase}{if $config.mod_rewrite}{$pages.my_profile}.html{else}?page={$pages.my_profile}{/if}'" />
	</div>
</div>

<script src="https://{if $config.google_wallet_test_mode}sandbox.google.com/checkout{else}wallet.google.com{/if}/inapp/lib/buy.js"></script>
<script type="text/javascript">
    var jwt = '{$txn_info.jwt}';

	{literal}

	$(document).ready(function(){
		$('#google_pay').click(function()
		{
			google.payments.inapp.buy({
				'jwt': jwt,
				'success': googleSuccessHandler,
				'failure': googleFailureHandler
			});
		});
	});

	var googleSuccessHandler = function(response) 
	{
		xajax_googleSuccessHandler(response.request.sellerData);
  	}

	var googleFailureHandler = function(response)
	{
		xajax_googleFailureHandler(response.request.sellerData);
	}

	{/literal}
</script>	

<!-- end googleWallet plugin  -->