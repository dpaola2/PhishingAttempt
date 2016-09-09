<!-- androidConnect pictures resize button -->

<tr class="body">
	<td class="list_td">{$lang.android_controls_resize_pictures}</td>
	<td class="list_td" align="center">
		<input id="android_resize" type="button" value="{$lang.android_controls_button}" style="margin: 0;width: 100px;" />
	</td>
</tr>
<tr>
	<td style="height: 5px;" colspan="3">
		<script type="text/javascript">
		lang['android_controls_notice'] = "{$lang.android_controls_notice}";
		lang['android_controls_resizing'] = "{$lang.android_controls_resizing}";
		lang['android_controls_button'] = "{$lang.android_controls_button}";
		lang['android_controls_resize_completed'] = "{$lang.android_controls_resize_completed}";
		{literal}
		
		androidResizeStack = 0;
		
		$(document).ready(function(){
			$('#android_resize').click(function(){
				$(this).val(lang['android_controls_resizing']);
				printMessage('alert', lang['android_controls_notice']);
				
				andridResize();
			});
		});
		
		var andridResize = function(){
			var url = rlUrlHome + 'request.ajax.php';
			$.getJSON(url, {item: 'androidResize', stack: androidResizeStack}, function(response){
				if ( response == '1' )
				{
					androidResizeStack++;
					setTimeout('andridResize()', 500);
				}
				else if ( response == '0' )
				{
					androidResizeStack = 0;
					$('#android_resize').val(lang['android_controls_button']);
					printMessage('notice', lang['android_controls_resize_completed']);
				}
			});
		};
		
		{/literal}
		</script>
	</td>
</tr>

<!-- androidConnect pictures resize button end -->