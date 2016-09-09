<!-- shoppingCart plugin -->

{if $tpl_settings.type == 'responsive_42'}
	
	{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'my_items_sold_responsive_42.tpl'}

{else}

<div class="highlight">
	{if !empty($order_info)}    
		
		{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'order_details.tpl' order_info=$order_info atype='buyer'}

	{else}
		{if $orders}
			<table class="list">
				<tr class="header">
					<td align="center" class="no_padding" style="width: 15px;">#</td>
					<td class="divider"></td>
					<td>{$lang.shc_item}</td>
					<td class="divider"></td>
					<td style="width: 80px;"><div title="{$lang.shc_total}" class="text-overflow">{$lang.shc_total}</div></td>
					<td class="divider"></td>
					<td style="width: 110px;"><div title="{$lang.shc_order_key}" class="text-overflow">{$lang.shc_order_key}</div></td>
					<td class="divider"></td>
					<td style="width: 70px;">{$lang.date}</td>
					<td class="divider"></td>
					<td style="width: 90px;">{$lang.shc_shipping_status}</td>
					<td class="divider"></td> 
					<td style="width: 100px;">{$lang.actions}</td>                               
				</tr>
				{foreach from=$orders item='item' name='orderF'}
				{assign var='pstatus' value='shc_'|cat:$item.pStatus}
				<tr class="body" id="item_{$item.ID}">
					<td class="no_padding" align="center"><span class="text">{$smarty.foreach.orderF.iteration}</span></td>
					<td class="divider"></td>
					<td class="text-overflow">{$item.title}</td>
					<td class="divider"></td>
					<td style="white-space: nowrap;"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.Total|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
					<td class="divider"></td>
					<td><span class="text">{$item.Order_key}</span></td>
					<td class="divider"></td>
					<td><span class="text" title="{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}">{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span></td>
					<td class="divider"></td>
					<td>
						<select id="shs_{$item.ID}" class="w70 shipping_status">
					   		{foreach from=$shipping_statuses item='shs'}
								<option value="{$shs.Key}" {if $shs.Key == $item.Shipping_status}selected="selected"{/if}>{$shs.name}</option>	
							{/foreach}
						</select>
					</td>
					<td class="divider"></td>
					<td class="text-overflow" style="text-align: center;">
						<a title="{$lang.view_details}" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_items_sold}.html?item={$item.ID}{else}?page={$pages.my_items_sold}&amp;item={$item.ID}{/if}">
							{$lang.shc_details}
						</a>
					</td>
				</tr>
				{/foreach}
			</table>

			{paging calc=$pInfo.calc total=$orders|@count current=$pInfo.current per_page=$config.shc_orders_per_page}
		{else}
			<div class="info">{$lang.shc_no_sold_items}</div>
		{/if}
	{/if}
</div>

{/if}

<script type="text/javascript">
{literal}

$(document).ready(function() {
	$('select.shipping_status').change(function() {
		if($(this).val() != '')
		{
			xajax_changeShippingStatus($(this).val(), $(this).attr('id').split('_')[1]);
		}
	});
});

{/literal}
</script>

<!-- end shoppingCart plugin -->