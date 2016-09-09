<!-- bankWireTransfer plugin -->

<div class="highlight">
	{if !empty($txn_info)}

		{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'request_details.tpl' order_info=$txn_info}

	{elseif !isset($smarty.get.item)}
		{if $requests}
			<table class="list">
				<tr class="header">
					<td align="center" class="no_padding" style="width: 15px;">#</td>
					<td class="divider"></td>
					<td>{$lang.bwt_item}</td>
					<td class="divider"></td>
					<td style="width: 80px;"><div title="{$lang.amount}" class="text-overflow">{$lang.bwt_total}</div></td>
					<td class="divider"></td>
					<td style="width: 80px;"><div title="{$lang.txn_id}" class="text-overflow">{$lang.txn_id}</div></td>
					<td class="divider"></td>
					<td style="width: 70px;">{$lang.date}</td>
					<td class="divider"></td>
					<td style="width: 90px;">{$lang.status}</td>
					<td class="divider"></td>
					<td style="width: 120px;"></td>
				</tr>
				{foreach from=$requests item='item' name='requestF'}
				<tr class="body" id="item_{$item.ID}">
					<td class="no_padding" align="center"><span class="text">{$smarty.foreach.requestF.iteration}</span></td>
					<td class="divider"></td>
					<td class="text-overflow">{$item.Item}</td>
					<td class="divider"></td>
					<td style="white-space: nowrap;"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.Total} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
					<td class="divider"></td>
					<td><span class="text">{$item.Txn_ID}</span></td>
					<td class="divider"></td>
					<td><span class="text">{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span></td>
					<td class="divider"></td>
					<td class="text-overflow" id="txn_status_{$item.ID}">
						{assign var='pStatus' value='shc_'|cat:$item.pStatus}
						<span class="item_{$item.pStatus}">{$lang[$pStatus]}</span>
					</td>
					<td class="divider"></td>
					<td class="text-overflow" style="text-align: center;" id="bwt_{$item.ID}">
						{if $item.pStatus == 'unpaid'}
							<input type="button" value="{$lang.bwt_activate}" id="bwtpayment-{$item.ID}" class="accept-payment" />
						{else}
							<a href="{$rlBase}{if $config.mod_rewrite}{$pages.bwt_requests}.html?item={$item.ID}{else}?page={$pages.bwt_requests}&amp;item={$item.ID}{/if}">
								{$lang.bwt_request_details}
							</a>
						{/if}
					</td>
				</tr>
				{/foreach}
			</table>

			{paging calc=$pInfo.calc total=$requests|@count current=$pInfo.current per_page=$config.listings_per_page}
		{else}
			<div class="info">{$lang.bwt_no_requests}</div>
		{/if}
	{/if}
</div>

<script type="text/javascript">
{literal}

$(document).ready(function() {
	$('.accept-payment').click(function() 
	{
	    var item_id = $(this).attr('id').split('-')[1];
		xajax_completeTransaction(item_id);
	});
});

{/literal}
</script>

<!-- end bankWireTransfer plugin -->