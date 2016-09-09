<!-- compressionJsCss plugin -->

<tr class="body">
	<td class="list_td_light">{$lang.compression_rebuild}</td>
	<td class="list_td_light" align="center" style="width: 200px;">
		<input id="compression_rebuild" type="button" onclick="xajax_rebuildJsCss('compression_rebuild'); $(this).val('{$lang.loading}');" value="{$lang.rebuild}" style="margin: 0; width: 100px;" />
	</td>
</tr>
{if $config.mobile_version_module}
	<tr class="body">
		<td class="list_td_light">{$lang.compression_mobile_rebuild}</td>
		<td class="list_td_light" align="center" style="width: 200px;">
			<input id="compression_mobile_rebuild" type="button" onclick="xajax_rebuildJsCssMobile('compression_mobile_rebuild'); $(this).val('{$lang.loading}');" value="{$lang.rebuild}" style="margin: 0; width: 100px;" />
		</td>
	</tr>
{/if}

<!-- end compressionJsCss plugin -->