<!-- Order Details -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_order_details' name=$lang.shc_order_details}
	<table class="table">
		<tr>
			<td class="name">{$lang.shc_order_key}:</td>
			<td class="value"><b>{$order_info.Order_key}</b></td>
		</tr>
		{if $account_info.ID == $order_info.Dealer_ID}
		<tr>
			<td class="name">{$lang.shc_buyer}:</td>
			<td class="value">{strip}
				{if $order_info.bOwn_address}
					<a href="{$rlBase}{$order_info.bOwn_address}/">{$order_info.bFull_name}</a>
				{else}
					<span>{$order_info.bFull_name}</span>
				{/if}
			{/strip}</td>
		</tr>
		{else}
		<tr>
			<td class="name">{$lang.shc_dealer}:</td>
			<td class="value">{strip}
				{if $order_info.dOwn_address}
					<a href="{$rlBase}{$order_info.dOwn_address}/">{$order_info.dFull_name}</a>
				{else}
					<span>{$order_info.dFull_name}</span>
				{/if}
			{/strip}</td>
		</tr>
		{/if}
		<tr>
			<td class="name">{$lang.date}:</td>
			<td class="value">{$order_info.Date|date_format:$smarty.const.RL_DATE_FORMAT}</td>
		</tr>
		<tr>
			<td class="name">{$lang.shc_total_cost}:</td>
			<td class="value"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="total">{$total|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
		</tr>
		<tr>
			<td class="name">{$lang.status}:</td>
			<td class="value">{$order_info.pStatus}</td>
		</tr>
		{if !empty($order_info.Txn_ID)}
			<tr>
				<td class="name">{$lang.shc_txn_id}:</td>
				<td class="value">{$order_info.Txn_ID}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_gateway}:</td>
				<td class="value">{$order_info.Gateway}</td>
			</tr>
			<tr>
				<td class="name">{$lang.shc_pay_date}:</td>
				<td class="value">{$order_info.Pay_date|date_format:$smarty.const.RL_DATE_FORMAT}</td>
			</tr>
		{/if}
		<tr>
			<td class="name">{$lang.shc_comment}:</td>
			<td class="value">{if !empty($order_info.Comment)}<i>{$order_info.Comment}</i>{else} - {/if}</td>
		</tr>
	</table>
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_shipping_details' name=$lang.shc_shipping_details}
	<table class="table">
		<tr>
			<td class="name">{$lang.shc_shipping_method}:</td>
			<td class="value">{$order_info.Shipping_method}</td>
		</tr>
		<tr>
			<td class="name">{$lang.shc_shipping_price}:</td>
			<td class="value"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$order_info.Shipping_price|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
		</tr>
		<tr>
			<td class="name">{$lang.shc_shipping_status}:</td>
			<td class="value"><b>{$order_info.Shipping_status}</b></td>
		</tr>
		{if $order_info.Weight}
		<tr>
			{assign var='shc_lf_name' value='listing_fields+name+shc_weight'}
			<td class="name">{$lang[$shc_lf_name]}:</td>
			<td class="value">{$order_info.Weight}</td>
		</tr>
		{/if}
		<tr>
			<td class="name">{$lang.shc_country}:</td>
			<td class="value">{$order_info.Country}</td>
		</tr>
		<tr>
			<td class="name">{$lang.shc_city}:</td>
			<td class="value">{$order_info.City}</td>
		</tr>
		<tr>
			<td class="name">{$lang.shc_zip}:</td>
			<td class="value">{$order_info.Zip_code}</td>
		</tr>
		<tr>
			<td class="name">{$lang.shc_address}:</td>
			<td class="value">{$order_info.Address}</td>
		</tr>
		<tr>
			<td class="name">{$lang.shc_name}:</td>
			<td class="value">{$order_info.Name}</td>
		</tr>
		<tr>
			<td class="name">{$lang.shc_phone}:</td>
			<td class="value">{$order_info.Phone}</td>
		</tr>
		<tr>
			<td class="name">{$lang.mail}:</td>
			<td class="value">{$order_info.Mail}</td>
		</tr>
	</table>
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{if $order_info.items}
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shc_items' name=$lang.shc_items}
		{assign var='width' value=$config.pg_upload_thumbnail_width+10}
	   	<div id="items_list">	
			<table class="list">
				<tr class="header"> 
					<td width="80"></td>
					<td class="divider"></td>
					<td>{$lang.shc_item}</td>
					<td class="divider"></td>
					<td width="100">{$lang.shc_price}</td>
					<td class="divider"></td>
					<td width="100">{$lang.shc_quantity}</td>
					<td class="divider"></td>
					<td width="120">{$lang.shc_total}</td>
				</tr>
				{foreach from=$order_info.items item='item' name='orderItemF'}
				<tr class="body" id="item_{$item.ID}">
					<td class="photo" valign="top" align="center" width="80">
						<img alt="{$item.title}" style="width: 70px;" src="{if empty($item.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$item.Main_photo}{/if}" />
					</td>
					<td class="divider"></td>
					<td class="text-overflow">
						{$item.Item}
					</td>
					<td class="divider"></td>
					<td style="white-space: nowrap;" align="center">{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.Price|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</td>
					<td class="divider"></td>
					<td align="center">
						{$item.Quantity}
					</td>
					<td class="divider"></td>
					<td style="white-space: nowrap;" align="center">{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="price_{$item.ID}">{$item.total|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</td>
				</tr>
				{/foreach}

				<!-- Shipping -->
				<tr>
					<td style="text-align: right" colspan="8">
						<b>{$lang.shc_shipping_price}</b>		
					</td>	
					<td style="text-align: center">	
						<div><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$order_info.Shipping_price|number_format:2:'.':','} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></div>
					</td>
				</tr>

				<!-- Total -->
				<tr>
					<td style="text-align: right" colspan="8">
						<b>{$lang.shc_total_cost}</b>
					</td>	
					<td style="text-align: center">	
						<div><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} <span id="total">{$order_info.Total|number_format:2:'.':','}</span> {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></div>
					</td>
				</tr>
			</table>
		</div>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
{/if}

<!-- ens Order Details -->