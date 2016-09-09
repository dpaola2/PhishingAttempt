<!-- Order Information -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='order_information' name=$lang.bwt_order_information tall=true}

<table class="table">
	<tr>
		<td class="name" width="180">{$lang.item}</td>
		<td class="value">{if $txn_info.Item}{$txn_info.Item}{else}{$smarty.session.complete_payment.item_name}{/if}</td>
	</tr>
	<tr>
		<td class="name" width="180">{$lang.bwt_txn_id}</td>
		<td class="value">{$txn_id}</td>
	</tr>
	<tr>
		<td class="name">{$lang.bwt_total}</td>
		<td class="value"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {if $txn_info.Item}{$txn_info.Total}{else}{$smarty.session.complete_payment.plan_info.Price}{/if} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
	</tr>
</table>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

<!-- end Order Information -->

<!-- Payment Information -->
{if $config.bwt_type == 'by_check' || $bwt_type == 'by_check'}
	{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'type_by_check.tpl'}	
{else}
	{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'type_wire_transfer.tpl'}	
{/if}
<!-- end Payment Information -->