<!-- db_migration tpl -->

{if !$errors}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
<div id="import_modules_dom" style="padding: 10px;">
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.qtip.js"></script>
	{include file=$smarty.const.RL_PLUGINS|cat:'dbMigration'|cat:$smarty.const.RL_DS|cat:'admin'|cat:$smarty.const.RL_DS|cat:'modules.tpl'}
</div>
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

<script type="text/javascript">
var dbImport;
var importLock = false;
{literal}
	$(document).ready(function() {
		dbImport = function(module) {
			if ( !importLock ) {
				importLock = true;
				
				if ( $('input[name=im_'+ module +']:checked').length > 0 )
				{
					var replace = $('input[name=im_'+ module +']:checked').val();
					eval( 'xajax_import_'+ module +'( '+ replace +' );' );
				}
				else
				{
					eval( 'xajax_import_'+ module +'();' );
				}

				$('#import_'+ module).val('{/literal}{$lang.loading}{literal}');
			}
		}
	});
{/literal}
</script>

{/if}
<!-- db_migration tpl end -->