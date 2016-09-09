<!-- shoppingCart plugin -->

{if $tpl_settings.type == 'responsive_42'}
	
	{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'my_purchases_responsive_42.tpl'}

{else}

<div class="highlight">
	{if !empty($order_info)}

		{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'order_details.tpl' order_info=$order_info}

	{elseif !isset($smarty.get.item)}
		{if $orders}
			<table class="list">
				<tr class="header">
					<td align="center" class="no_padding" style="width: 15px;">#</td>
					<td class="divider"></td>
					<td>{$lang.shc_item}</td>
					<td class="divider"></td>
						<td style="width: 80px;"><div title="{$lang.amount}" class="text-overflow">{$lang.shc_total}</div></td>
					<td class="divider"></td>
						<td style="width: 80px;"><div title="{$lang.txn_id}" class="text-overflow">{$lang.shc_order_key}</div></td>
					<td class="divider"></td>
					<td style="width: 70px;">{$lang.date}</td>
					<td class="divider"></td>
					<td style="width: 90px;">{$lang.status}</td>
					<td class="divider"></td>
					<td style="width: 70px;">{$lang.actions}</td>
				</tr>
				{foreach from=$orders item='item' name='orderF'}
				<tr class="body" id="item_{$item.ID}">
					<td class="no_padding" align="center"><span class="text">{$smarty.foreach.orderF.iteration}</span></td>
					<td class="divider"></td>
					<td class="text-overflow">{$item.title}</td>
					<td class="divider"></td>
					<td style="white-space: nowrap;"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.Total|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
					<td class="divider"></td>
					<td><span class="text">{$item.Order_key}</span></td>
					<td class="divider"></td>
					<td><span class="text">{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span></td>
					<td class="divider"></td>
					<td class="text-overflow">
						{assign var='pStatus' value='shc_'|cat:$item.pStatus}
						<span class="item_{$item.pStatus}"><span class="circle">$</span>{$lang[$pStatus]}</span>
					</td>
					<td class="divider"></td>
					<td class="text-overflow" style="text-align: center;">
						{if $item.pStatus == 'unpaid'}
							<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html{else}?page={$pages.shc_my_shopping_cart}{/if}">
								{$lang.shc_checkout}
							</a>
						{else}
							<a title="{$lang.view_details}" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_purchases}.html?item={$item.ID}{else}?page={$pages.shc_purchases}&amp;item={$item.ID}{/if}">
								{$lang.shc_details}
							</a>
						{/if}
					</td>
				</tr>
				{/foreach}
			</table>

			{paging calc=$pInfo.calc total=$orders|@count current=$pInfo.current per_page=$config.shc_orders_per_page}
		{else}
			<div class="info">{$lang.shc_no_purchases}</div>
		{/if}
	{/if}
</div>

{/if}

<!-- end shoppingCart plugin -->