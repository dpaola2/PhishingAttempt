<!-- booking requests -->

<div class="content-padding" id="area_booking">
	{if $requests}
		{if $config.mod_rewrite}
			{assign var='require_id' value=$smarty.get.request_id}
		{else}
			{assign var='require_id' value=$smarty.get.id}
		{/if}

		{if !$require_id}
			{if empty($requests)}
				<div class="text-notice">{$lang.booking_no_new_requests}</div>
			{else}
				<div class="list-table" id="saved_search">
					<div class="header">
						<div class="center" style="width: 40px;">#</div>
						<div>{$lang.listing}</div>
						{if $aHooks.ref == 1}
							<div style="width: 90px;">{$lang.booking_ref_number}</div>
						{/if}
						<div style="width: 170px;">{$lang.booking_author}</div>
						<div style="width: 100px;">{$lang.status}</div>
						<div style="width: 150px;">{$lang.actions}</div>
					</div>

					{foreach from=$requests item='request' name='requestsF' key='rKey'}
						<div class="row" id="item_request_{$request.ID}">
							<div class="center iteration no-flex">{$smarty.foreach.requestsF.iteration}</div>
							<div data-caption="{$lang.listing}" class="content">
								{assign var='ltype_key' value='lt_'|cat:$request.ltype}
								<a href="{$rlBase}{if $config.mod_rewrite}{$pages.$ltype_key}/{$request.Path}/{str2path string=$request.title}-{$request.Listing_ID}.html{else}?page={$pages.$ltype_key}&amp;id={$request.Listing_ID}{/if}" title="{$request.booking_page_details}">{$request.title}</a>
							</div>
							{if $aHooks.ref == 1}
								<div data-caption="{$lang.listing}">{$request.ref}</div>
							{/if}
							<div data-caption="{$lang.booking_author}">{$request.Author}</div>
							<div id="status_{$request.ID}" data-caption="{$lang.status}" class="statuses"><span class="{if $request.status == 'process'}pending{elseif $request.status == 'refused'}expired{else}active{/if}">{if $request.status == 'process'}{$lang.new}{elseif $request.status == 'booked'}{$lang.booking_legend_booked}{else}{$lang.booking_refused}{/if}</span></div>
							<div data-caption="{$lang.actions}">
								<a href="{$rlBase}{if $config.mod_rewrite}{$pages.booking_requests}/{str2path string=$request.title}-r{$rKey}.html{else}?page={$pages.booking_requests}&amp;id={$rKey}{/if}">{$lang.booking_page_details}</a>
								<img class="remove" onclick="rlConfirm( '{$lang.ext_booking_remove_notice}', 'xajax_deleteRequest', Array('{$request.ID}'), 'request_loading' );" alt="{$lang.delete}" title="{$lang.delete}" src="{$rlTplBase}img/blank.gif" />
							</div>
						</div>
					{/foreach}
				</div>
			{/if}

			<script type="text/javascript">
			{literal}
				function removeRequest(id) {
					$('#item_request_'+ id).remove();

					if ( $('#saved_search > div.row').length <= 0 ) {
						var parent = $('#saved_search').parent();
						$('#saved_search').remove();
						$(parent).html('<div class="text-noticevj">{/literal}{$lang.booking_no_requests}{literal}</div>');
					}
				}
			{/literal}
			</script>

		{else}

			<div class="two-column clearfix inline">

				<div><div>
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' name=$lang.booking_page_details}

						{if $aHooks.ref == 1}
						<div class="table-cell clearfix">
							<div class="name"><div><span>{$lang.booking_ref_number}</span></div></div>
							<div class="field">{$requests.ref_number}</div>
						</div>
						{/if}
						<div class="table-cell clearfix">
							<div class="name"><div><span>{$lang.name}</span></div></div>
							<div class="field">
								{assign var='ltype_key' value='lt_'|cat:$requests.Type}
								<a href="{$rlBase}{if $config.mod_rewrite}{$pages.$ltype_key}/{$requests.Path}/{str2path string=$requests.title}-{$requests.Listing_ID}.html{else}?page={$pages.$ltype_key}&amp;id={$requests.Listing_ID}{/if}" title="{$requests.booking_page_details}">{$requests.title}</a>
							</div>
						</div>
						<div class="table-cell clearfix">
							<div class="name"><div><span>{$lang.booking_checkin}</span></div></div>
							<div class="field">{$requests.From|date_format:$smarty.const.RL_DATE_FORMAT}</div>
						</div>
						<div class="table-cell clearfix">
							<div class="name"><div><span>{$lang.booking_checkout}</span></div></div>
							<div class="field">{$requests.To|date_format:$smarty.const.RL_DATE_FORMAT}</div>
						</div>
						<div class="table-cell clearfix">
							<div class="name"><div><span>{$lang.booking_req_status}</span></div></div>
							<div class="field">{$requests.Stat}</div>
						</div>
						<div class="table-cell clearfix">
							<div class="name"><div><span>{$lang.booking_amount}</span></div></div>
							<div class="field">{$defPrice.currency} {str2money string=$requests.Amount}</div>
						</div>

					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
				</div></div>

				<div><div>
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' name=$lang.booking_client_details}

						{foreach from=$requests.fields key='fKey' item='field'}
						{assign var='field_value' value=$field.value}
						{if $field.Type == 'bool'}
							{if $field.value == '1'}
								{assign var='field_value' value=$lang.yes}
							{else}
								{assign var='field_value' value=$lang.no}
							{/if}
						{/if}
						<div class="table-cell clearfix">
							<div class="name"><div><span>{$field.name}</span></div></div>
							<div class="field">
								{if $field.Condition == 'isUrl'}
									<a class="static" href="{$field_value}" title="{$field_value}">{$field_value}</a>
								{else}
									{$field_value}
								{/if}
							</div>
						</div>
						{/foreach}

					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
				</div></div>
			</div>

			{if $requests.Stat == $lang.booking_processed}
				<div id="owner_actions">
					<div id="asf_res">
						<input type="button" onclick="ownerResult('1', 'accept');" value="{$lang.booking_accept}" />
						<a class="red margin close" href="javascript:;" onclick="ownerResult('1', 'refuse');">{$lang.booking_refuse}</a>
					</div>

					<div id="refuse_ansfer" class="hide" style="margin-top: 10px;">
						<span class="blue_middle">{$lang.booking_request_ansfer_area}<span class="red">*</span></span>
						<textarea rows="5" cols="" id="textarea_ansfer"></textarea>
						<div>
							<input type="button" id="accept_btn" class="button hide" onclick="ownerResult('2', 'accept');" value="{$lang.booking_accept}" />
							<input type="button" id="refuse_btn" class="button hide" onclick="ownerResult('2', 'refuse');" value="{$lang.booking_refuse}" />
							<a class="red margin cancel" href="javascript:;" onclick="ownerResult('cancel');">{$lang.booking_button_cancel}</a>
						<div>
					</div>

					<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.textareaCounter.js"></script>
					<script type="text/javascript">
					var require_id = '{$require_id}';
					{literal}

					$(document).ready(function(){
						$('#textarea_ansfer').textareaCount({
							'maxCharacterSize': 255,
							'warningNumber': 20
						})
					});

					function ownerResult( step, ask ) {
						if ( step == '1' ) {
							$('#asf_res, #accept_btn, #refuse_btn').slideUp('fast');
							$('#refuse_ansfer, #'+ask+'_btn').slideDown('fast');
						}
						else if( step == 'cancel' ) {
							$('#asf_res').fadeIn('fast');
							$('#refuse_ansfer, #accept_btn, #refuse_btn').slideUp('fast');
						}
						else {
							xajax_ownerResult( require_id, ask, $('#textarea_ansfer').val() );
						}
					}
					{/literal}
					</script>
				</div>
			{/if}

		{/if}
	{else}
		<div class="text-notice">{$lang.booking_no_requests}</div>
	{/if}
</div>

<!-- booking requests end -->
