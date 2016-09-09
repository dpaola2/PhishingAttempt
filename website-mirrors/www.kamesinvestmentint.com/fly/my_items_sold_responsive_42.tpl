<!-- my sold items | shopping cart -->

<div class="highlight">
	{if !empty($order_info)}    
		
		{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'order_details.tpl' order_info=$order_info atype='buyer'}

	{else}
		{if $orders}
			<div class="list-table row-align-middle">
				<div class="header">
					<div class="center" style="width: 40px;">#</div>
					<div>{$lang.shc_item}</div>
					<div style="width: 80px;">{$lang.shc_total}</div>
					<div style="width: 120px;">{$lang.shc_order_key}</div>
					<div style="width: 95px;">{$lang.date}</div>
					<div style="width: 90px;">{$lang.shc_shipping_status}</div>
					<div style="width: 60px;">{$lang.actions}</div>	
				</div>

				{foreach from=$orders item='item' name='orderF'}
					{assign var='pstatus' value='shc_'|cat:$item.pStatus}
					{math assign='iteration' equation='(((current?current:1)-1)*per_page)+iter' iter=$smarty.foreach.orderF.iteration current=$pInfo.current per_page=$config.shc_orders_per_page}

					<div class="row" id="item_{$item.ID}">
						<div class="center iteration no-flex">{$iteration}</div>
						<div data-caption="{$lang.shc_item}">{$item.title}</div>
						<div data-caption="{$lang.shc_total}">
							<span class="price-cell">
								{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
								{$item.Total|number_format:2:'.':','}
								{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
							</span>
						</div>
						<div data-caption="{$lang.shc_order_key}">{$item.Order_key}</div>
						<div data-caption="{$lang.date}">{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}</div>
						<div data-caption="{$lang.shc_shipping_status}">
							<select id="shs_{$item.ID}" class="w70 shipping_status">
						   		{foreach from=$shipping_statuses item='shs'}
									<option value="{$shs.Key}" {if $shs.Key == $item.Shipping_status}selected="selected"{/if}>{$shs.name}</option>	
								{/foreach}
							</select>
						</div>
						<div data-caption="{$lang.actions}">
							<a title="{$lang.view_details}" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_items_sold}.html?item={$item.ID}{else}?page={$pages.my_items_sold}&amp;item={$item.ID}{/if}">{$lang.shc_details}</a>
						</div>
					</div>
				{/foreach}
			</div>

			{paging calc=$pInfo.calc total=$orders|@count current=$pInfo.current per_page=$config.shc_orders_per_page}
		{else}
			<div class="info">{$lang.shc_no_sold_items}</div>
		{/if}
	{/if}
</div>

<!-- my sold items end | shopping cart -->