<!-- my purchases | shopping cart -->

<div class="content-padding">
	{if !empty($order_info)}

		{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'order_details_responsive_42.tpl' order_info=$order_info}

	{elseif !isset($smarty.get.item)}

		{if $orders}
			<div class="list-table">
				<div class="header">
					<div class="center" style="width: 40px;">#</div>
					<div>{$lang.shc_item}</div>
					<div style="width: 90px;">{$lang.shc_total}</div>
					<div style="width: 120px;">{$lang.shc_order_key}</div>
					<div style="width: 100px;">{$lang.date}</div>
					<div style="width: 100px;">{$lang.status}</div>
					<div style="width: 90px;">{$lang.actions}</div>
				</div>

				{foreach from=$orders item='item' name='orderF'}
					{math assign='iteration' equation='(((current?current:1)-1)*per_page)+iter' iter=$smarty.foreach.orderF.iteration current=$pInfo.current per_page=$config.shc_orders_per_page}

					<div class="row">					
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
						<div data-caption="{$lang.status}">
							{assign var='pStatus' value='shc_'|cat:$item.pStatus}
							<span class="item_{$item.pStatus}">{$lang[$pStatus]}</span>
						</div>
						<div data-caption="{$lang.actions}">
							{if $item.pStatus == 'unpaid'}
								<a href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html{else}?page={$pages.shc_my_shopping_cart}{/if}">
									{$lang.shc_checkout}
								</a>
							{else}
								<a title="{$lang.view_details}" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_purchases}.html?item={$item.ID}{else}?page={$pages.shc_purchases}&amp;item={$item.ID}{/if}">
									{$lang.shc_details}
								</a>
							{/if}
						</div>
					</div>
				{/foreach}
			</div>

			{paging calc=$pInfo.calc total=$orders|@count current=$pInfo.current per_page=$config.shc_orders_per_page}
		{else}
			<div class="text-notice">{$lang.shc_no_purchases}</div>
		{/if}
	{/if}
</div>

<!-- my purchases end | shopping cart -->