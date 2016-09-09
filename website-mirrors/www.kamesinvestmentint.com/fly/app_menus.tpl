<!-- app menus tpl -->

<script type="text/javascript">
{literal}
	$(document).ready(function() {
		var appHtm = '<fieldset class="light"> \
			<legend id="legend_app_menus" class="up" onclick="fieldset_action(\'app_menus\');">{/literal}{$lang.iFlynaxConnect_show_on_menus}{literal}</legend> \
			<div id="app_menus"> \
				<div style="padding: 2px 0 2px 5px;"> \
					<input {/literal}{if $sPost.menus.9 == 9}checked="checked"{/if}{literal} class="lang_add" type="checkbox" name="menus[9]" value="9" id="app_home" /> \
					<label for="app_home">{/literal}{$lang.iFlynaxConnect_show_on_menus_home}{literal}</label> \
				</div> \
			</div> \
		</fieldset>';
		$('legend#legend_mobile_menus').parent().after(appHtm);
	});
{/literal}
</script>

<!-- app menus tpl end -->