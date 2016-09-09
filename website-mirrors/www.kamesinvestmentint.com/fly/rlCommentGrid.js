
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLCOMMENTGRID.JS
 *
 *	The software is a commercial product delivered under single, non-exclusive, 
 *	non-transferable license for one domain or IP address. Therefore distribution, 
 *	sale or transfer of the file in whole or in part without permission of Flynax 
 *	respective owners is considered to be illegal and breach of Flynax License End 
 *	User Agreement. 
 *
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *
 *	Flynax Classifieds Software 2014 |  All copyrights reserved. 
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

Ext.BLANK_IMAGE_URL = rlUrlHome + 'img/ext/default/s.gif';

var CommentGrid = function( action ){

	// clear object
	$('#rl_comment_grid').html('');

	Ext.onReady( function(){

		var perPage = 20;
		
		var fields = Ext.data.Record.create([
			{name: 'ID', mapping: 'ID'},
			{name: 'Title', mapping: 'Title'},
			{name: 'Description', mapping: 'Description'},
			{name: 'Author', mapping: 'Author'},
			{name: 'Status', mapping: 'Status'},
			{name: 'Date', mapping: 'Date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
		]);
		
		// create the Data Store
		var store = new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
				url: rlPlugins + 'comment/admin/comment.inc.php?q=ext',
				method: 'GET'
			}),

			// create reader that reads the Topic records
			reader: new Ext.data.JsonReader({
				root: 'data',
				totalProperty: 'total',
				remoteSort: true,
				id: 'ID'
				}, fields
			)
		});

		// set filed for default sort
	    store.setDefaultSort('Date', 'asc');
		
	    /* create combo box options */
	    Ext.namespace('Ext.exampledata');
	    
	    // status field
		Ext.exampledata.status = [
			[lang['ext_active'], 'active'],
			[lang['ext_approval'], 'approval']
		];
    	
		var status = new Ext.data.SimpleStore({
			fields: ['value', 'key'],
			data : Ext.exampledata.status
		});
			
		/* create combo box options end */
	    
		// create the column modelke
		var cm = new Ext.grid.ColumnModel([{
			header: lang['ext_title'],
			dataIndex: 'Title',
			width: 80
		},{
			header: lang['ext_author'],
			dataIndex: 'Author',
			width: 20,
			id: 'rlExt_item_bold',
			renderer: function(param){
					var username = param.split('||')[0];
					var account = param.split('||')[1];

					if (account == '0')
					{
						var link = "<span>"+username+"</span>"; 
					}
					else
					{
						var link = "<span ext:qtip='"+lang['ext_click_to_view_details']+"' style='cursor: pointer;' onClick='location.href=\""+rlUrlHome+"index.php?controller=accounts&action=view&username="+username+"\"'>"+username+"</span>";
					}
					return link;
			}
		},{
			header: lang['ext_add_date'],
			dataIndex: 'Date',
			width: 17,
			renderer:  function(val){
				var date = Ext.util.Format.dateRenderer(rlDateFormat.replace(/%/g, '').replace('b', 'M'))(val);
				date = '<span class="build">'+date+'</span>';
				return date;
			}
		},{
			header: lang['ext_status'],
			dataIndex: 'Status',
			width: 10,
			editor: new Ext.form.ComboBox({
				store: status,
				displayField: 'value',
				valueField: 'key',
				typeAhead: true,
				mode: 'local',
				triggerAction: 'all',
				selectOnFocus:true
			})
		},{
			header: lang['ext_actions'],
			width: 10,
			dataIndex: 'ID',
			sortable: false,
			renderer: function(data) {
				return "<center><span onClick='location.href=\""+rlUrlHome+"index.php?controller="+controller+"&action=edit&comment="+data+"\"' class='edit'>"+lang['ext_edit']+"</span><span class='blue_11_bold_link'> | </span><span onClick='rlConfirm( \""+lang['ext_notice_'+delete_mod]+"\", \"xajax_deleteComment\", \""+Array(data)+"\", \"news_load\" )' class='delete'>"+lang['ext_delete']+"</a></center>"
			}
		}
		]);
	
	    // enable the default sortable
		cm.defaultSortable = true;

		var grid = new Ext.grid.EditorGridPanel({
			el:'rl_comment_grid',
			autoWidth: true,
			autoHeight: true,
			monitorResize: true,
			doLayout: function(){
				wsize = Ext.getBody().getWidth();
				this.setSize(wsize - 214);
				Ext.grid.GridPanel.prototype.doLayout.call(this);
			},
			title:lang['ext_manager'],
			store: store,
			clicksToEdit:1,
			cm: cm,
			trackMouseOver:false,
			loadMask: {msg: lang['ext_loading']},
			viewConfig: {
				forceFit:true,
				enableRowBody:true,
				showPreview:false
			},
			bbar: new Ext.PagingToolbar({
				beforePageText: lang['ext_page'],
				afterPageText: lang['ext_of']+' {0}',
				pageSize: perPage,
				store: store,
				displayInfo: true,
				displayMsg: lang['ext_display_items'],
				emptyMsg: lang['ext_no_items']
			})
		});
	
		// render the grid
		grid.render();

		// trigger the data store load
		store.load({params:{start:0, limit:perPage}});
		
		//setup edit action
		grid.addListener('afteredit', function(editEvent)
		{		
			if (editEvent.field == 'Date')
			{
				var df = new Date(editEvent.value);
				editEvent.value = df.format("Y-m-d");
			}
			
			Ext.Ajax.request({
				waitMsg: 'Saving changes...',
				url: rlPlugins + 'comment/admin/comment.inc.php?q=ext',
				method: 'GET',
				params:
				{
					action: 'update',
					type: editEvent.grid.type,
					id: editEvent.record.id,
					field: editEvent.field,
					value: editEvent.value
				},
				failure: function()
				{
					Ext.MessageBox.alert('Error saving changes...');
				},
				success: function()
				{
					grid.store.commitChanges();
					//grid.store.reload();
				}
			});
		});

	});

}
