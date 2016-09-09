<!-- Order Details -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_order_details' name=$lang.shc_order_details}

	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_order_key}</span></div></div>
		<div class="value">{$order_info.Order_key}</div>
	</div>

	{if $account_info.ID == $order_info.Dealer_ID}
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_buyer}</span></div></div>
			<div class="value">
				{if $order_info.bOwn_address}
					<a href="{$rlBase}{$order_info.bOwn_address}/">{$order_info.bFull_name}</a>
				{else}
					<span>{$order_info.bFull_name}</span>
				{/if}
			</div>
		</div>
	{else}
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_dealer}</span></div></div>
			<div class="value">
				{if $order_info.dOwn_address}
					<a href="{$rlBase}{$order_info.dOwn_address}/">{$order_info.dFull_name}</a>
				{else}
					<span>{$order_info.dFull_name}</span>
				{/if}
			</div>
		</div>
	{/if}

	<div class="table-cell">
		<div class="name"><div><span>{$lang.date}</span></div></div>
		<div class="value">{$order_info.Date|date_format:$smarty.const.RL_DATE_FORMAT}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_total_cost}</span></div></div>
		<div class="value">
			<span class="price-cell">
				{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
				{$total|number_format:2:'.':','}
				{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
			</span>
		</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.status}</span></div></div>
		<div class="value">{$order_info.pStatus}</div>
	</div>
	{if !empty($order_info.Txn_ID)}
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_txn_id}</span></div></div>
			<div class="value">{$order_info.Txn_ID}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_gateway}</span></div></div>
			<div class="value">{$order_info.Gateway}</div>
		</div>
		<div class="table-cell">
			<div class="name"><div><span>{$lang.shc_pay_date}</span></div></div>
			<div class="value">{$order_info.Pay_date|date_format:$smarty.const.RL_DATE_FORMAT}</div>
		</div>
	{/if}
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_comment}</span></div></div>
		<div class="value">{if !empty($order_info.Comment)}<i>{$order_info.Comment}</i>{else} - {/if}</div>
	</div>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_shipping_details' name=$lang.shc_shipping_details}

	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_shipping_method}</span></div></div>
		<div class="value">{$order_info.Shipping_method}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_shipping_price}</span></div></div>
		<div class="value">
			<span class="price-cell">
				{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
				{$order_info.Shipping_price|number_format:2:'.':','}
				{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
			</span>
		</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_shipping_status}</span></div></div>
		<div class="value"><b>{$order_info.Shipping_status}</b></div>
	</div>
	{if $order_info.Weight}
		{assign var='shc_lf_name' value='listing_fields+name+shc_weight'}
		<div class="table-cell">
			<div class="name"><div><span>{$lang[$shc_lf_name]}</span></div></div>
			<div class="value">{$order_info.Weight}</div>
		</div>
	{/if}
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_country}</span></div></div>
		<div class="value">{$order_info.Country}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_city}</span></div></div>
		<div class="value">{$order_info.City}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_zip}</span></div></div>
		<div class="value">{$order_info.Zip_code}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_address}</span></div></div>
		<div class="value">{$order_info.Address}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_name}</span></div></div>
		<div class="value">{$order_info.Name}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.shc_phone}</span></div></div>
		<div class="value">{$order_info.Phone}</div>
	</div>
	<div class="table-cell">
		<div class="name"><div><span>{$lang.mail}</span></div></div>
		<div class="value">{$order_info.Mail}</div>
	</div>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{if $order_info.items}
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_items' name=$lang.shc_items}

	<div class="list-table row-align-middle">
		<div class="header">
			<div class="center" style="width: 40px;">#</div>
			<div>{$lang.shc_item}</div>
			<div style="width: 90px;">{$lang.shc_price}</div>
			<div style="width: 70px;">{$lang.shc_quantity}</div>
			<div style="width: 110px;">{$lang.shc_total}</div>
		</div>

		{foreach from=$order_info.items item='item' name='orderItemF'}
			<div class="row">
				<div class="center iteration no-flex">{$smarty.foreach.orderItemF.iteration}</div>
				<div data-caption="{$lang.shc_item}" class="img-row">
					{if $item.Main_photo}
						<img alt="{$item.title}" style="width: 70px;" src="{$smarty.const.RL_FILES_URL}{$item.Main_photo}" />
						{$item.Item}
					{/if}
				</div>
				<div data-caption="{$lang.shc_price}">
					<span class="price-cell">
						{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
						{$item.Price|number_format:2:'.':','}
						{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
					</span>
				</div>
				<div data-caption="{$lang.shc_quantity}">{$item.Quantity}</div>
				<div data-caption="{$lang.shc_total}">
					<span class="price-cell">
						{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
						{$item.total|number_format:2:'.':','}
						{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
					</span>
				</div>
			</div>
		{/foreach}
	</div>

	<div class="ralign">
		<!-- shipping -->
		<div class="shc_value" style="padding: 20px 0;">
			{$lang.shc_shipping_price}:
			<span class="value shc_price">
				{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
				<span id="total_{$shcDealer}">{$order_info.Shipping_price|number_format:2:'.':','}</span>
				{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
			</span>
		</div>
		<!-- shipping end -->

		<!-- total -->
		<div class="shc_value" style="padding: 0 0 20px 0;">
			{$lang.shc_total_cost}:
			<span class="value shc_price">
				{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
				<span id="total_{$shcDealer}">{$order_info.Total|number_format:2:'.':','}</span>
				{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
			</span>
		</div>
		<!-- total end -->
	</div>

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
{/if}

<!-- ens Order Details -->