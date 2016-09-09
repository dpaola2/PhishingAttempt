<!-- availability_field.tpl -->

{if $smarty.const.REALM == 'admin'}
	{assign var="isAdminPanel" value=true}
{/if}

{if $isAdminPanel}
<div id="availability_field" class="hide">
	<table class="form">
{/if}

{foreach from=$availability.days item='wd_item'}
	{if $config.listing_feilds_position == 1}	
		<div class="name">{$wd_item.title}</div>
		<div id="sf_field_{$field.Key}" class="field">
	{elseif $config.listing_feilds_position == 2}
		<tr>
			<td class="name">{$wd_item.title}:</td>
			<td class="field">
	{/if}

	{assign var='sp_day' value='availability_'|cat:$wd_item.day}
	{assign var='spAvailability' value=$smarty.post.f.$sp_day}

	<span class="availability">
		<select class="w120" name="f[availability_{$wd_item.day}][from]">
			<option value="-1">{$lang.not_available}</option>
			{foreach from=$availability.time_range item='at_item' key='at_key'}
				<option {if $spAvailability.from == $at_key}selected{/if} value="{$at_key}">{$at_item}</option>
			{/foreach}
		</select>
		-
		<select class="w120" name="f[availability_{$wd_item.day}][to]">
			<option value="-1">{$lang.not_available}</option>
			{foreach from=$availability.time_range item='at_item' key='at_key'}
				<option {if $spAvailability.to == $at_key}selected{/if} value="{$at_key}">{$at_item}</option>
			{/foreach}
		</select>
	</span>

	{if $config.listing_feilds_position == 2}
			</td>
		</tr>
	{else}
		</div>
	{/if}
{/foreach}

{if $isAdminPanel}
	</table>
</div>
{/if}

<script type="text/javascript">
{literal}
	$(document).ready(function() {
		$('select[name^="f[availability_"]').change(function() {
			var thisIndex = $(this).find('option').index($(this).find('option:selected'));
			var isFromField = ($(this).attr('name').match(/\[from\]/) != null);
			var week_day = parseInt($(this).attr('name').match(/\d+/)[0]);
			var thisFieldValue = parseInt($(this).val());

			if ( isFromField ) {
				var toField = $('select[name^="f[availability_'+ week_day +'][to]"]');
				var toFieldValue = parseInt($(toField).val());

				$(toField).find('option').attr('disabled', false);
				$(toField).find('option:lt('+ thisIndex +')').attr('disabled', true);

				if ( thisFieldValue == -1 || thisFieldValue > toFieldValue ) {
					$(toField).find('option:eq('+ thisIndex +')').attr('selected', true);
				}
			}
			else {
				var fromField = $('select[name^="f[availability_'+ week_day +'][from]"]');
				var fromFieldValue = parseInt($(fromField).val());

				if ( fromFieldValue == -1 ) {
					$(this).find('option').attr('disabled', false);
					$(this).find('option:lt('+ thisIndex +')').attr('disabled', true);
					$(fromField).find('option:eq('+ thisIndex +')').attr('selected', true);
				}
			}
		});

		//
		$('select[name^="f[availability_"]').filter(function() {
			return (this.name.match(/\[to\]/) != null);
		}).each(function() {
			var week_day = parseInt($(this).attr('name').match(/\d+/)[0]);
			var fromField = $('select[name^="f[availability_'+ week_day +'][from]"]');
			var fIndex = $(fromField).find('option').index($(fromField).find('option:selected'));

			$(this).find('option:lt('+ fIndex +')').attr('disabled', true);
		});
	});
{/literal}
</script>

<!-- availability_field.tpl end -->