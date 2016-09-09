{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='payment_info' name=$lang.bwt_order_information style='fg'}

	<table class="table">
		<tr>
			<td class="name" width="180">{$lang.bwt_item}</td>
			<td class="value">{$txn_info.Item}</td>
		</tr>
		<tr>
			<td class="name" width="180">{$lang.bwt_txn_id}</td>
			<td class="value">{$txn_info.Txn_ID}</td>
		</tr>
		<tr>
			<td class="name" width="180">{$lang.shc_buyer}</td>
			<td class="value">{$txn_info.Full_name}</td>
		</tr>
		<tr>
			<td class="name">{$lang.bwt_total}</td>
			<td class="value"><b>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$txn_info.Total} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</b></td>
		</tr>
		<tr>
			<td class="name" width="180">{$lang.bwt_type}</td>
			<td class="value">{$lang[$txn_info.Type]}</td>
		</tr>
		<tr>
			<td class="name" width="180">{$lang.bwt_ip}</td>
			<td class="value">{$txn_info.IP}</td>
		</tr>
		<tr>
			<td class="name" width="180">{$lang.status}</td>
			<td class="value">
				{assign var='pStatus' value='shc_'|cat:$txn_info.pStatus}
				{$lang[$pStatus]}
			</td>
		</tr>
		<tr>
			<td class="name" width="180">{$lang.date}</td>
			<td class="value">{$txn_info.Date|date_format:$smarty.const.RL_DATE_FORMAT}</td>
		</tr>
	</table>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='payment_info' name=$lang.bwt_payment_details style='fg'}

	<!-- Payment Information -->
	{if $txn_info.Type == 'by_check'}
		{if !empty($payment_details)}
			{if $pageInfo.Controller == 'payment_history'}
				<div class="name"><b>{$lang.bwt_payment_details}:</b></div>
			{/if}
			<div class="sLine"></div>
			{foreach from=$payment_details item='pd'}
				<div class="name"><b>{$pd.name}</b></div>
				<div class="value">{$pd.description}</div>

				<div class="clear" style="height: 10px;"></div>
			{/foreach}
		{else}
			<div class="static-content">{$lang.bwt_missing_payment_details}</div>
		{/if}
	{else}
		{if !empty($txn_info)}
			 <table class="table">
				<tr>
					<td>
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='payment_info' name=$lang.bwt_account_info style='fg'}
							<table class="table">
								<tr>
									<td class="name" width="180">{$lang.bwt_bank_account_number}</td>
									<td class="value">{$txn_info.Bank_account_number}</td>
								</tr>
								<tr>
									<td class="name">{$lang.bwt_account_name}</td>
									<td class="value">{$txn_info.Account_name}</td>
								</tr>
								{if !empty($txn_info.Company_name)}
									<tr><td class="name">{$lang.bwt_company_name}</td><td class="value">{$txn_info.Company_name}</td></tr>
								{/if}
								<tr>
									<td class="name">{$lang.bwt_counry}</td>
									<td class="value">{$txn_info.Country}</td>
								</tr>
								{if !empty($txn_info.State)}
									<tr><td class="name">{$lang.bwt_state}</td><td class="value">{$txn_info.State}</td></tr>
								{/if}
								{if !empty($txn_info.City)}
									<tr><td class="name">{$lang.bwt_city}</td><td class="value">{$txn_info.City}</td></tr>
								{/if}
								{if !empty($txn_info.Zip)}
									<tr><td class="name">{$lang.bwt_zip}</td><td class="value">{$txn_info.Zip}</td></tr>
								{/if}
								{if !empty($txn_info.Address)}
									<tr><td class="name">{$lang.bwt_address}</td><td class="value">{$txn_info.Address}</td></tr>
								{/if}
							</table>
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
					</td>
				</tr>
				<tr>
					<td>
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='bank_info' name=$lang.bwt_bank_info style='fg'}
							<table class="table">
								<tr>
									<td class="name" width="180">{$lang.bwt_bank_name}</td>
									<td class="value">{$txn_info.Bank_name}</td>
								</tr>
								<tr>
									<td class="name">{$lang.bwt_counry}</td>
									<td class="value">{$txn_info.Bank_country}</td>
								</tr>
								{if !empty($txn_info.Bank_state)}
									<tr><td class="name">{$lang.bwt_state}</td><td class="value">{$txn_info.Bank_state}</td></tr>
								{/if}
								{if !empty($txn_info.Bank_city)}
									<tr><td class="name">{$lang.bwt_city}</td><td class="value">{$txn_info.Bank_city}	</td></tr>
								{/if}
								{if !empty($txn_info.Bank_zip)}
									<tr><td class="name">{$lang.bwt_zip}:</td><td class="value">{$txn_info.Bank_zip}</td></tr>
								{/if}
								{if !empty($txn_info.Bank_address)}
									<tr><td class="name">{$lang.bwt_bank_address}</td><td class="value">{$txn_info.Bank_address}</td></tr>
								{/if}
								{if !empty($txn_info.Bank_phone)}
									<tr><td class="name">{$lang.bwt_bank_phone}</td><td class="value">{$txn_info.Bank_phone}</td></tr>
								{/if}
							</table>
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
					</td>
				</tr>
			</table>
		{/if}
	{/if}
	<!-- end Payment Information -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}