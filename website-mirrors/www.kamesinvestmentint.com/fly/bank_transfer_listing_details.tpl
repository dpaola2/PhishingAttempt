{if $config.shc_method == 'multi'}
	{if $seller_info.shc_bankWireTransfer_type == 'by_check'}
		<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/bwt_by_check.png" />
	{elseif $seller_info.shc_bankWireTransfer_type == 'write_transfer'}
		<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/bwt_write_transfer.png" />
	{else}
		<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/bwt_by_check.png" /></li><li>
		<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/bwt_write_transfer.png" />
	{/if}
{else}
	{if $config.bwt_type == 'by_check'}
		<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/bwt_by_check.png" />
	{elseif $config.bwt_type == 'write_transfer'}
		<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/bwt_write_transfer.png" />
	{else}
		<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/bwt_by_check.png" />
		<img alt="" src="{$smarty.const.RL_PLUGINS_URL}{$pgateway.key}/static/bwt_write_transfer.png" />
	{/if}
{/if}