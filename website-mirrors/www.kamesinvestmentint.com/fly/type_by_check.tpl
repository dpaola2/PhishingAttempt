<!-- Payment Details -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='payment_info' name=$lang.bwt_payment_details style='fg'}

{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'payment_details_block.tpl' payment_details=$payment_details}	

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{if !empty($lang.bwt_by_check_notice)}
	<div style="padding-top: 10px;" class="static-content">{$lang.bwt_by_check_notice}</div>
{/if}

{if $pageInfo.Controller != 'bwt_print'}
 <table class="table">
	<tr>
		<td>
			<div align="center">
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
				<input id="bwt_continue" type="submit" onclick="location.href='{$rlBase}{if $config.mod_rewrite}{$return_page}.html{else}?page={$return_page}{/if}'" value="{$lang.bwt_continue}"/>
			</div>
		</td>
	</tr>
</table>
{/if}

{rlHook name='bankWireTransferByCheckTpl'}

<!-- end Payment Details -->