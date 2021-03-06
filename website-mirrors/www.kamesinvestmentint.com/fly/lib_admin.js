
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: LIB_ADMIN.JS
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

$(document).ready(function(){
	$('#start_import').click(function(){
		importExport.start();
		$('#start_import').fadeOut();
	});
});

var eil_rowHandler = function(row){
	var eq = row ? ':eq('+row+')' : '';
	$('input[name^=rows]'+eq+'').each(function(){
		if ( $(this).is(':checked') )
		{
			$(this).closest('tr.body').removeClass('disabled no_hover').attr('title', '');
		}
		else
		{
			$(this).closest('tr.body').addClass('disabled no_hover').attr('title', eil_listing_wont_imported);
		}
	});
}

var eil_colHandler = function(){
	$('input[name^=cols]').each(function(){
		var index = $(this).closest('tr.col-checkbox').find('input').index(this) + 2;

		if ( $(this).is(':checked') )
		{
			$('table.import tr td:nth-child('+index+')').removeClass('disabled no_hover');
			$(this).closest('td').attr('title', '');
			$('table.import tr.header td:nth-child('+index+')').attr('title', '');
		}
		else
		{
			$(this).attr('checked', false);
			$('table.import tr td:nth-child('+index+')').addClass('disabled no_hover');
			$(this).closest('td').attr('title', eil_column_wont_imported);
			$('table.import tr.header td:nth-child('+index+')').attr('title', eil_column_wont_imported);
			$('table.import tr.header td:nth-child('+index+') select option').attr('selected', false);
		}
	});
}

var eil_typeHandler = function(key, element){
	if ( key )
	{
		$('select[name='+element+'] option:first').text(lang['loading']);
		xajax_fetchOptions(key, element);
	}
	else
	{
		var option = '<option value="">'+eil_select_listing_type+'</option>';
		$('select[name='+element+']').html(option);
	}
}

var importExportClass = function(){
	var self = this;
	var item_width = width = percent = percent_value = 0;
	var window = false;
	var request;
	
	this.phrases = new Array();
	this.config = new Array();
		
	this.import = function(index){
		/* show window */
		if ( index == 0 )
		{
			if ( !window )
			{
				window = new Ext.Window({
					applyTo: 'statistic',
					layout: 'fit',
					width: 447,
					height: 120,
					closeAction: 'hide',
					plain: true
			    });
			    
			    window.addListener('hide', function(){
	            	self.stop();
	            });
			}
		    
			window.show();
		}
		
	    /* import request */
	    request = $.getJSON("../plugins/export_import/admin/import.php", {index: index}, function(response){
			if ( index == 0 )
			{
				var runs = Math.ceil(response['count']/self.config['per_run']);
				item_width = Math.ceil(362/runs);
				percent_value = Math.ceil(100/runs);
			}
			
			index = response['from'];
			
			width += item_width;
			percent = response['from'] > response['count'] ? 100 : percent + percent_value;
			
			$('#processing').css('width', width+'px');
			$('#loading_percent').html(percent+'%');
			
			if ( response['count'] > index )
			{
				var from = response['from'] + 1;
				var to = response['to'] + 1;
				to = response['count'] < to ? response['count'] : to;
				var import_current = from+'-'+to;
				$('#importing').html(import_current);
			
				self.import(index);
			}
			else
			{
				$('#import_start_nav').slideUp();
				printMessage('notice', self.phrases['completed'].replace('{count}', response['count']));
				setTimeout(function(){
					window.hide();
					listingsGrid.init();
					grid.push(listingsGrid.grid);
				}, 2000);
			}
		});
	}
	
	this.stop = function(){
		request.abort();
	}
	
	this.start = function(){
		self.import(0);
	}
}

var importExport = new importExportClass();