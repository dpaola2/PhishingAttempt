<!-- booking tab -->

<div id="area_booking" class="tab_area hide content-padding">
	{assign var='unColors' value="|"|explode:$config.booking_colors}

	<style type="text/css">
	{literal}
	.available, .available:before {	background: {/literal}{$unColors.1}{literal}; }
	.daySelect, .daySelect:before {	background: {/literal}{$unColors.0}{literal}!important; }
	.booked, .booked:before { background: {/literal}{$unColors.2}{literal}; }
	.prbooked, .prbooked:before { background: {/literal}{$unColors.3}{literal}; }
	.closed, .closed:before { background: {/literal}{$unColors.4}{literal}; }

	.checkin, .checkin:before { background: linear-gradient(to right, {/literal}{$unColors.1}, {$unColors.2}{literal}); }
	.checkout, .checkout:before { background: linear-gradient(to right, {/literal}{$unColors.2}, {$unColors.1}{literal}); }

	.prcheckin, .prcheckin:before { background: linear-gradient(to right, {/literal}{$unColors.1}, {$unColors.3}{literal}); }
	.prcheckout, .prcheckout:before { background: linear-gradient(to right, {/literal}{$unColors.3}, {$unColors.1}{literal}); }

	.bprcheckin, .bprcheckin:before { background: linear-gradient(to right, {/literal}{$unColors.2}, {$unColors.3}{literal}); }
	.bprcheckout, .bprcheckout:before { background: linear-gradient(to right, {/literal}{$unColors.3}, {$unColors.2}{literal}); }

	.closein, .closein:before { background: linear-gradient(to right, {/literal}{$unColors.1}, {$unColors.4}{literal}); }
	.closeout, .closeout:before { background: linear-gradient(to right, {/literal}{$unColors.4}, {$unColors.1}{literal}); }

	.bclosein, .bclosein:before { background: linear-gradient(to right, {/literal}{$unColors.1}, {$unColors.3}{literal}); }
	.bcloseout, .bcloseout:before { background: linear-gradient(to right, {/literal}{$unColors.3}, {$unColors.1}{literal}); }

	.pclosein, .pclosein:before { background: linear-gradient(to right, {/literal}{$unColors.1}, {$unColors.2}{literal}); }
	.pcloseout, .pcloseout:before { background: linear-gradient(to right, {/literal}{$unColors.2}, {$unColors.1}{literal}); }
	{/literal}
	</style>

	<script type="text/javascript">
		var booking_debug = true;
		var listing_id = {if $config.mod_rewrite}{$smarty.get.listing_id}{else}{$smarty.get.id}{/if};
		var selected = new Array();
		var usRange = new Array();
		var closeRange = new Array();
		var defPrice = '{$defPrice.value}';
		var defCurrency = '{$defPrice.currency}';
		var total_cost = 0;
		var cur_id=0;
		var s_id = 0;
		var db_start = 0;
		var db_end = 0;
		var first=0;
		var index = 0;
		var min_bl = {$config.booking_min_book_day};
		var max_bl = {$config.booking_max_book_day};
		var bind_in_out = {$config.booking_bind_checkin_checkout};
		var fixed_range = {$config.booking_fixed_range};
		var price_delimiter = '{$config.price_delimiter}';
		var bind_checkin = '';
		var bind_checkout = '';
		var deny_text = '{$lang.booking_deny_guests}';
		var min_bl_text = '{$lang.booking_min_booking}';
		var max_bl_text = '{$lang.booking_max_booking}';
		var closed_day_text = '{$lang.booking_day_closed}';
		var booked_day_text = '{$lang.booking_day_booked}';
		var check_in_only_text = '{$lang.booking_check_in_only}';
		var check_out_only_text = '{$lang.booking_check_out_only}';
		var booking_next_step = '{$lang.booking_next_step}';
		var deny = {if $config.booking_deny_guest && !$isLogin}0{else}1{/if};
		var message_obj = '#booking_message';
		var book_btn_obj = '#nextBtn';
		var book_btp_obj = '#prevBtn';
		var day_prefix = '#day_';
		var book_display = 'none';
		var usBook = new Array();
		var bookingDateFormat = '{$smarty.const.RL_DATE_FORMAT}';

		/* phrases */
		var already_booked_text = '{$lang.booking_already_booked}';
		var booking_checkin = '{$lang.booking_checkin}';
		var booking_checkout = '{$lang.booking_checkout}';
		var booking_amount = '{$lang.booking_amount}';
		var booking_nights = '{$lang.booking_nights}';

		var nextStep;

		{literal}
			$(document).ready(function(){
				xajax_getDates(listing_id);

				$('#checkValid').formValidator({
					onSuccess: function() {
						var formData = $('#ufvalid').formToArray();
						xajax_bookNow(listing_id, db_start, db_end, formData, total_cost);

						selected = [];
						index = 0;

						$(message_obj).html('');
						$('#booking_message_obj').hide();
						$('#ufvalid').resetForm();

						$('div#step_2').hide();
					},
					scope: '#ufvalid'
				});

				nextStep = function(obj) {
					$('div#booking_tab').hide();
					$('div#step_2').show();
					$(obj).closest('div.table-cell').hide();
				}

				$('div#step_2 span.cancel').click(function() {
					$('div#step_2').hide();
					$('div#booking_message_obj').hide();
					$('div#booking_tab').show();
					book_color(true);

					if ( typeof closeMessage == 'function' ) {
						closeMessage();
					}
				});

				$('#step_2 input, #step_2 textarea').click(function() {
					$(this).removeClass('error-input');
				}).keydown(function() {
					$(this).removeClass('error-input');
				});

				flynax.qtip();
			});
		{/literal}
	</script>

	<div class="two-column clearfix" id="booking_legend">
		<div><div>
			<!-- legend -->

			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='booking_legend' name=$lang.booking_legend}
			<ul class="legend">{strip}
				<li class="available">{$lang.booking_legend_available}</li>
				<li class="booked">{$lang.booking_legend_booked}</li>
				<li class="checkin">{$lang.booking_legend_checkin}</li>
				<li class="checkout">{$lang.booking_legend_checkout}</li>
				<li class="prbooked">{$lang.booking_legend_prebooked}</li>
				<li class="closed">{$lang.booking_close_days}</li>
			{/strip}</ul>
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

			<!-- legend end -->
		</div></div>
		<div><div>
			<!-- rate range -->

			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='rate_range' name=$lang.booking_rate_range}

			{rlHook name='bookingPreRateRangeTpl'}

			<ul class="ranges" id="booking_rate_range">
			{foreach from=$rate_range item='rRange'}
				<li class="two-inline clearfix">
					<div class="price">
						{if $rRange.Price == 0}{$lang.booking_close_days}{else}{$defPrice.currency} {$rRange.Price}{/if}
					</div>
					<div class="date">
						{$rRange.From|date_format:$smarty.const.RL_DATE_FORMAT} - {$rRange.To|date_format:$smarty.const.RL_DATE_FORMAT}
						{if !empty($rRange.desc)}
							<img class="qtip" alt="" title="{$rRange.desc}" id="fd_{$smarty.foreach.rate_rageF.iteration}" src="{$rlTplBase}img/blank.gif" />
						{/if}
					</div>
				</li>
			{/foreach}

			{if $use_time_frame}
				<li class="two-inline clearfix">
					<div class="price">{$defPrice.name}</div>
					<div class="date">
						{$lang.booking_rate_price_per_day}
						{if $range_regular_desc}
							<img class="qtip" alt="" title="{$range_regular_desc}" id="fd_regular" src="{$rlTplBase}img/blank.gif" />
						{/if}
					</div>
				</li>
			{/if}
			</ul>

			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

			<!-- rate range end -->
		</div></div>
	</div>

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='calendar_fieldset' name=$lang.booking_calendar}
		<div id="booking_calendar"></div>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

	<div class="hide" id="booking_message_obj">
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='booking_mes' name=$lang.booking_details}
		<div id="booking_message"></div>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
	</div>

	<div class="hide" id="step_2">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='booking_mes' name=$lang.booking_step2}
		<form onsubmit="return false;" id="ufvalid" class="ufvalid" action="#" method="post">
			{include file=$smarty.const.RL_PLUGINS|cat:'booking'|cat:$smarty.const.RL_DS|cat:'responsive'|cat:$smarty.const.RL_DS|cat:'booking_fields.tpl'}
		</form>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
	</div>
</div>

<!-- booking tab end -->