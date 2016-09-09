<!-- shoppingCart plugin -->

<!-- navigation bar -->
<div id="nav_bar">
	{if $smarty.get.module == 'auction' && !$smarty.get.action}
		<a href="javascript:void(0)" onclick="show('search')" class="button_bar"><span class="left"></span><span class="center_search">{$lang.search}</span><span class="right"></span></a>
	{/if}

	<a href="{$rlBaseC}module=auction" class="button_bar"><span class="left"></span>
		<span class="center_list">{$lang.shc_auction}</span><span class="right"></span>
	</a>
	<a href="{$rlBaseC|replace:'&amp;':''}" class="button_bar"><span class="left"></span>
		<span class="center_list">{$lang.shc_orders}</span><span class="right"></span>
	</a>
	<a href="{$rlBaseC}module=configs" class="button_bar"><span class="left"></span>
		<span class="center_list">{$lang.shc_configs}</span><span class="right"></span>
	</a>
	<a href="{$smarty.const.RL_PLUGINS_URL}shoppingCart/help/shipping-cart-bidding-speed-guide.pdf" target="_blank" class="button_bar"><span class="left"></span><span class="center">{$lang.shc_help}</span><span class="right"></span></a>
</div>
<!-- navigation bar end -->

{if isset($smarty.get.action) && !isset($smarty.get.module)}

	{if $smarty.get.action == 'add' || $smarty.get.action == 'edit'}
		{assign var='sPost' value=$smarty.post}


	{elseif $smarty.get.action == 'view'}
		{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'admin'|cat:$smarty.const.RL_DS|cat:'order_details.tpl' order_info=$order_info}
	{/if}

{else}
	{assign var='plunginPath' value=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS}
	{if isset($smarty.get.module)}
		{include file=$plunginPath|cat:'admin'|cat:$smarty.const.RL_DS|cat:$smarty.get.module|cat:'.tpl'}
	{else}
		<!-- ext grid -->
		<div id="grid"></div>
		<script type="text/javascript">//<![CDATA[
		var shoppingCartGrid;

		{literal}
		$(document).ready(function(){
			
			shoppingCartGrid = new gridObj({
				key: 'shopping_cart',
				id: 'grid',
				ajaxUrl: rlPlugins + 'shoppingCart/admin/shopping_cart.inc.php?q=ext',
				defaultSortField: 'Date',
				remoteSortable: true,
				checkbox: true,
				actions: [
					[lang['ext_delete'], 'delete']
				],
				title: lang['ext_shc_orders_manager'],
				fields: [
					{name: 'Txn_ID', mapping: 'Txn_ID'},
					{name: 'Order_key', mapping: 'Order_key'},
					{name: 'bUsername', mapping: 'bUsername', type: 'string'},
					{name: 'dUsername', mapping: 'dUsername', type: 'string'},
					{name: 'Account_ID', mapping: 'Account_ID', type: 'string'},
					{name: 'title', mapping: 'title', type: 'string'},
					{name: 'Total', mapping: 'Total'},
					{name: 'pStatus', mapping: 'pStatus'},
					{name: 'Shipping_status', mapping: 'Shipping_status'},
					{name: 'Buyer_ID', mapping: 'Buyer_ID', type: 'int'},
					{name: 'Dealer_ID', mapping: 'Dealer_ID', type: 'int'},
					{name: 'ID', mapping: 'ID', type: 'int'},
					{name: 'Date', mapping: 'Date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
				],
				columns: [
					{
						header: lang['ext_id'],
						dataIndex: 'ID',
						width: 35,
						fixed: true,
						id: 'rlExt_black_bold'
					},{
						header: lang['ext_shc_buyer'],
						dataIndex: 'bUsername',
						width: 120,
						fixed: true,
						renderer: function(username, obj, row)
						{
							if ( username )
							{
								var out = '<a class="green_11_bg" href="'+rlUrlHome+'index.php?controller=accounts&action=view&userid='+row.data.Buyer_ID+'" ext:qtip="'+lang['ext_click_to_view_details']+'">'+username+'</a>';
							}
							else
							{
								var out = '<span class="delete">{/literal}{$lang.account_removed}{literal}</span>';
							}
							return out;
						}
					},{
						header: lang['ext_shc_dealer'],
						dataIndex: 'dUsername',
						width: 120,
						fixed: true,
						renderer: function(username, obj, row)
						{
							if ( username )
							{
								var out = '<a class="green_11_bg" href="'+rlUrlHome+'index.php?controller=accounts&action=view&userid='+row.data.Dealer_ID+'" ext:qtip="'+lang['ext_click_to_view_details']+'">'+username+'</a>';
							}
							else
							{
								var out = '<span class="delete">{/literal}{$lang.account_removed}{literal}</span>';
							}
							return out;
						}
					},{
						header: lang['ext_shc_item'],
						dataIndex: 'title',
						width: 20
					},{
						header: lang['ext_shc_order_key'],
						dataIndex: 'Order_key',
						width: 100,
						fixed: true
					},{
						header: lang['ext_total']+' ('+rlCurrency+')',
						dataIndex: 'Total',
						width: 5
					},{
						header: lang['ext_date'],
						dataIndex: 'Date',
						width: 80,
						fixed: true,
						renderer: Ext.util.Format.dateRenderer(rlDateFormat.replace(/%/g, '').replace('b', 'M'))
					},{
						header: lang['ext_shipping_status'],
						dataIndex: 'Shipping_status',
						width: 80,
						fixed: true,
						editor: new Ext.form.ComboBox({
							store: [
								['pending', lang['ext_shc_pending']],
								['processing', lang['ext_shc_processing']],
								['shipped', lang['ext_ext_shc_shipped']],
								['declined', lang['ext_shc_declined']],
								['open', lang['ext_shc_open']],
								['delivered', lang['ext_shc_delivered']]
							],
							displayField: 'value',
							valueField: 'key',
							typeAhead: true,
							mode: 'local',
							triggerAction: 'all',
							selectOnFocus:true
						}),
						renderer: function(val){
							return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
						}
					},{
						header: lang['ext_status'],
						dataIndex: 'pStatus',
						width: 80,
						fixed: true,
						renderer: function (val, obj, row) {
							if (val == lang['ext_shc_paid'])
							{                
								obj.style += 'background: #D2E798;';                              
								return '<span>' + val + '</span>';  
							}
							else if (val == lang['ext_shc_unpaid'])
							{
								obj.style += 'background: #FF878A;';
								return '<span>' + val + '</span>'; 
							}
						}
					},{
						header: lang['ext_actions'],
						width: 80,
						fixed: true,
						dataIndex: 'ID',
						sortable: false,
						renderer: function(data) {
							var out = "<center>";
							var splitter = false;
							
							out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=view&item="+data+"'><img class='view' ext:qtip='"+lang['ext_view']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \""+lang['ext_notice_'+delete_mod]+"\", \"xajax_deleteOrder\", \""+data+"\", \"load\" )' />";
							 
							out += "</center>";

							return out;
						}
					}
				]
			});
			
			shoppingCartGrid.init();
			grid.push(shoppingCartGrid.grid);
			
			// actions listener
			shoppingCartGrid.actionButton.addListener('click', function()
			{
				var sel_obj = shoppingCartGrid.checkboxColumn.getSelections();
				var action = shoppingCartGrid.actionsDropDown.getValue();

				if ( !action )
				{
					return false;
				}
				
				for( var i = 0; i < sel_obj.length; i++ )
				{
					shoppingCartGrid.ids += sel_obj[i].id;
					if ( sel_obj.length != i+1 )
					{
						shoppingCartGrid.ids += '|';
					}
				}
				
				if ( action == 'delete' )
				{
					Ext.MessageBox.confirm('Confirm', lang['ext_notice_'+delete_mod], function(btn){
						if ( btn == 'yes' )
						{
							xajax_deleteItem( shoppingCartGrid.ids );
						}
					});
				}
			});
			
		});
		{/literal}
		//]]>
		</script>
		<!-- ext grid end -->
	{/if}
{/if}

<!-- shoppingCart plugin -->