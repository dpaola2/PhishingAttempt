{if !isset($smarty.get.completed)}
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/numeric.js"></script>
	<form action="{$rlBase}{if $config.mod_rewrite}{$pages.bank_wire_transfer}.html{else}?page={$pages.bank_wire_transfer}{/if}" method="post">
	<input type="hidden" name="Txn_ID" value="{$txn_id}" />
	<input type="hidden" name="form" value="submit" />
	<table class="submit">
		<tr>
			<td width="180">
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='payment_info' name=$lang.bwt_account_info style='fg'}
					<table class="sTable">
						<tr>
							<td class="name">{$lang.bwt_bank_account_number}: <span class="red">*</span></td>
							<td class="field"><input type="text" name="bwt[bank_account_number]" value="{$smarty.post.bwt.bank_account_number}" /></td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_account_name}: <span class="red">*</span></td>
							<td class="field"><input type="text" name="bwt[account_name]" value="{$smarty.post.bwt.account_name}" /></td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_company_name}:</td>
							<td class="field"><input type="text" name="bwt[company_name]" value="{$smarty.post.bwt.company_name}" /></td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_counry}:</td>
							<td class="field"><select name="bwt[country]">
									<option value="">{$lang.select}</option>
									{foreach from=$bwt_country item='bwt_countries'}
										<option value="{$bwt_countries.name}" {if $smarty.post.bwt.account_country == $bwt_countries.name}selected="selected"{/if}>{$bwt_countries.name}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_state}:</td>
							<td class="field"><input type="text" name="bwt[state]" value="{$smarty.post.bwt.state}" /></td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_city}:</td>
							<td class="field">
								<input type="text" name="bwt[city]" value="{$smarty.post.bwt.city}" />
								&nbsp;&nbsp;{$lang.bwt_zip}: <input type="text" name="bwt[zip]" value="{$smarty.post.bwt.zip}" class="numeric w50" />
							</td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_address}:</td>
							<td class="field"><input type="text" name="bwt[address]" value="{$smarty.post.bwt.address}" /></td>
						</tr>
					</table>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			</td>
		</tr>
		<tr>
			<td>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='bank_info' name=$lang.bwt_bank_info style='fg'}
					<table class="sTable">
						<tr>
							<td class="name">{$lang.bwt_bank_name}:</td>
							<td class="field"><input type="text" name="bwt[bank_name]" value="{$smarty.post.bwt.bank_name}" /></td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_counry}:</td>
							<td class="field">
								<select name="bwt[bank_country]">
									<option value="">{$lang.select}</option>
									{foreach from=$bwt_country item='bwt_countries_bank'}
										<option value="{$bwt_countries_bank.name}" {if $smarty.post.bwt.bank_country == $bwt_countries_bank.name}selected="selected"{/if}>{$bwt_countries_bank.name}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_state}:</td>
							<td class="field"><input type="text" name="bwt[bank_state]" value="{$smarty.post.bwt.bank_state}" /></td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_city}:</td>
							<td class="field">
								<input type="text" name="bwt[bank_city]" value="{$smarty.post.bwt.bank_city}" />
								&nbsp;&nbsp;{$lang.bwt_zip}: <input type="text" name="bwt[bank_zip]" value="{$smarty.post.bwt.bank_zip}" class="numeric w50" />
							</td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_bank_address}:</td>
							<td class="field"><input type="text" name="bwt[bank_address]" value="{$smarty.post.bwt.bank_address}" /></td>
						</tr>
						<tr>
							<td class="name">{$lang.bwt_bank_phone}:</td>
							<td class="field"><input type="text" name="bwt[bank_phone]" value="{$smarty.post.bwt.bank_phone}" /></td>
						</tr>
					</table>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			</td>
		</tr>
		<tr>
			<td>
				<div align="center">
					<input type="submit" name="submit" value="{$lang.bwt_pay}"/>
				</div>
			</td>
		</tr>
	</table>
	</form>

	<script type="text/javascript">
	{literal}
	$(document).ready(function(){
		$("input.numeric").numeric();
	});
	{/literal}
	</script>
{else}
	{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'bank_write_details.tpl' txn_info=$txn_info}
	{if $pageInfo.Controller != 'bwt_print'}
	 <table class="table">
		<tr>
			<td>
				{if $txn_info.Service == 'shoppingCart'}
					{assign var='return_page' value=$pages.shc_purchases}
				{elseif $txn_info.Service == 'auction'}
					{assign var='return_page' value=$pages.shc_auctions}
				{elseif $txn_info.Service == 'banner'}
					{assign var='return_page' value=$pages.my_banners}
				{elseif $txn_info.Service == 'invoice'}
					{assign var='return_page' value=$pages.invoices}
				{elseif $pages.my_listings}
					{assign var='return_page' value=$pages.my_listings}
				{else}
					{assign var='return_page' value=$pages.my_profile}
				{/if}
				<div align="center">
					<input id="bwt_continue" type="submit" onclick="location.href='{$rlBase}{if $config.mod_rewrite}{$return_page}.html{else}?page={$return_page}{/if}'" value="{$lang.bwt_continue}"/>
				</div>
			</td>
		</tr>
	</table>
	{/if}
{/if}

{rlHook name='bankWireTransferByAccountTpl'}