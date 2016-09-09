<!-- booking details -->

<!-- tabs -->
<ul class="tabs">
	{strip}
		<li class="active" id="tab_requests">{$lang.booking_booking_requests}</li>
		<li id="tab_raterange">{$lang.booking_rate_range}</li>
		{if $config.booking_bind_checkin_checkout}<li id="tab_binding">{$lang.booking_binding_days}</li>{/if}
	{/strip}
</ul>
<!-- tabs end -->

<!-- requests tab -->
<div id="area_requests" class="tab_area content-padding">
	{if empty($requests)}
		<div class="text-message">{$lang.booking_no_requests}</div>
	{else}
		<div class="list-table content-padding">
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
						<a href="{$rlBase}{if $config.mod_rewrite}{$pages.$ltype_key}/{$request.Path}/{str2path string=$request.title}-l{$request.Listing_ID}.html{else}?page={$pages.$ltype_key}&amp;id={$request.Listing_ID}{/if}" title="{$request.booking_page_details}">{$request.title}</a>
					</div>
					{if $aHooks.ref == 1}
						<div data-caption="{$lang.listing}">{$request.booking_ref_number}</div>
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
</div>
<!-- requests tab end -->

<!-- rate range tab -->
<div id="area_raterange" class="tab_area content-padding hide">
	{include file=$smarty.const.RL_PLUGINS|cat:'booking'|cat:$smarty.const.RL_DS|cat:'responsive'|cat:$smarty.const.RL_DS|cat:'rate_range.tpl'}
</div>
<!-- rate range tab end -->

{if $config.booking_bind_checkin_checkout}
<!-- binding checkin / checkout tab -->
<div id="area_binding" class="tab_area content-padding hide">
	{include file=$smarty.const.RL_PLUGINS|cat:'booking'|cat:$smarty.const.RL_DS|cat:'responsive'|cat:$smarty.const.RL_DS|cat:'binding_days.tpl'}
</div>
<!-- binding checkin / checkout tab end -->
{/if}

<!-- additional javascripts -->
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.qtip.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.ui.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}booking/js/jquery.ufvalidator.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}booking/js/jquery.form.js"></script>
<script type="text/javascript">
//<![CDATA[
var listing_id = parseInt('{$smarty.get.id}');
var bind_click = 0;
var lang_delete = '{$lang.delete}';
var src_delete_img = '{$rlTplBase}img/blank.gif';
var qtip_init;

{literal}

qtip_init = function() {
	$('.qtip').each(function(){
		$(this).qtip({
			content: $(this).attr('title') ? $(this).attr('title') : $(this).prev('div.qtip_cont').html(),
			show: 'mouseover',
			hide: {
					fixed: true,
					delay: 500
			},
			position: {
				corner: {
					target: 'topRight',
					tooltip: 'bottomLeft'
				}
			},
			style: qtip_style
		}).attr('title', '');
	});
}
qtip_init();

function bind_edit() {
	flynaxTpl.customInput();
}

function save_binding_days() {
	var formData = $('#binding_days_form').formToArray();
	xajax_saveBindingDays(listing_id, formData);
}

function add_rate_range() {
	var current_field = $('#rate_ranges_table > div.row').length + 1;
	var previous_field = current_field - 1;

	var field = ' \
	<div class="row tmp" id="add_rate_'+ current_field +'"> \
		<div class="center iteration no-flex"></div> \
		<div data-caption="{/literal}{$lang.from}{literal}"><input type="text" class="w120 req-string req-date" name="from_'+ current_field +'" id="brr_from_'+ current_field +'" /></div> \
		<div data-caption="{/literal}{$lang.to}{literal}"><input type="text" class="w120 req-string req-date" name="to_'+ current_field +'" id="brr_to_'+ current_field +'" /></div> \
		<div data-caption="{/literal}{$lang.price}{literal}"><input type="text" class="numeric w120 req-string req-numeric" name="price_'+ current_field +'" id="price_'+ previous_field +'" /></div> \
		<div data-caption="{/literal}{$lang.booking_desc}{literal}"><a href="javascript:;" onclick="add_desc('+ current_field +');">{/literal}{$lang.add}{literal}</a></div> \
		<div data-caption="{/literal}{$lang.actions}{literal}"><img class="remove" onclick="removeRate('+ current_field +')" title="'+ lang_delete +'" alt="'+ lang_delete +'" src="'+ src_delete_img +'" /></div> \
	</div> \
	<div class="tmp" style="display: none!important;" id="add_rate_desc_'+ current_field +'"> \
		<textarea id="save_desc_'+ current_field +'" name="desc_'+ current_field +'" cols="30" rows="2"></textarea> \
	</div>';

	$('#rate_ranges_table').append(field);
	$('#booking_rate_range').fadeIn();
	$('#label_save_range').fadeIn('fast');
	flynaxTpl.customInput();

	var dates = $("#brr_from_"+ current_field +", #brr_to_"+ current_field).datepicker({
		showOn: 'both',
		buttonImage: '{/literal}{$rlTplBase}{literal}img/blank.gif',
		buttonImageOnly: true,
		dateFormat: 'dd-mm-yy',
		minDate: new Date(),
		onSelect: function(selectedDate) {
			if ( this.id.indexOf('from') !== -1 ) {
				var instance = $(this).data("datepicker"),
				date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
				dates.not(this).datepicker("option", "minDate", date);
				dates.not(this).val(selectedDate);
			}
		}
	}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);

	$('#label_save_range').formValidator({
		onSuccess: function() {
			xajax_saveRateRange(listing_id, $('#valid_rate_range').formToArray());
		},
		scope: '#valid_rate_range'
	});
}

function add_desc(rate_id) {
	$(document).flModal({
		click: false,
		source: '#manage_item_dom',
		caption: lang['manage'],
		width: 450,
		height: 'auto',
		ready: function(){
			var value = $('#save_desc_'+rate_id).val();

			$('input[name=item-desc]').val(value);
			$('input[name=item-desc-save]').click(function(){
				$('#save_desc_'+rate_id).val($('input[name=item-desc]').val());
				$('#modal_block div.close').trigger('click');
			});
		}
	});
}

function edit_desc(rate_id,mode) {
	if ( mode ) {
		rate_id = 'regular';
	}

	$(document).flModal({
		click: false,
		source: '#manage_item_dom',
		caption: lang['manage'],
		width: 450,
		height: 'auto',
		ready: function(){
			var value = $('#save_desc_'+rate_id).val();

			$('input[name=item-desc]').val(value);
			$('input[name=item-desc-save]').click(function(){
				var new_desc = $('input[name=item-desc]').val();
				xajax_saveDesc(rate_id, new_desc, mode);

				$('#save_desc_'+rate_id).val(new_desc);
				$('#modal_block div.close').trigger('click');
			});
		}
	});
}

function save_desc(rate_id, mode) {
	if ( mode ) {
		rate_id = 'regular';
	}

	var value = $('#save_desc_'+rate_id).val();
	xajax_saveDesc(rate_id,value,mode);
}

function removeRate(rate_id) {
	$('#add_rate_'+rate_id).remove();
	$('#add_rate_desc_'+rate_id).remove();

	if ( $('#rate_ranges_table div.tmp').length == 0 ) {
		$('#booking_rate_range').hide();
		$('#label_save_range').hide();
	}
}

function errorShow(error) {
	printMessage('error', error);
}

{/literal}
//]]>
</script>
<!-- additional javascripts end -->

<!-- booking details end -->
