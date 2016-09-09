<!-- rate range tpl -->

<div id="manage_item_dom" class="hide">
	<div class="manage-photo light-inputs">
		<div class="two-inline">
			<div><input name="item-desc-save" type="button" value="{$lang.save}" /></div>
			<div style="padding-{$text_dir_rev}: 15px;"><input style="width: 100%;" placeholder="{$lang.description}" name="item-desc" type="text" value="" /></div>
		</div>
	</div>
</div>

<div id="rate_range_obj">
	<form id="valid_rate_range" class="ufvalid" action="#" method="post">
		<div class="list-table content-padding" id="rate_ranges_table">
			<div class="header">
				<div class="center" style="width: 40px;">#</div>
				<div style="width: 180px;">{$lang.from}</div>
				<div style="width: 180px;">{$lang.to}</div>
				<div>{$lang.price}</div>
				<div style="width: 100px;">{$lang.booking_desc}</div>
				<div style="width: 80px;">{$lang.actions}</div>
			</div>

			{foreach from=$rate_range item='rRange' name='rate_rageF'}
			<textarea class="hide" id="save_desc_{$rRange.ID}" cols="30" rows="2">{$rRange.desc}</textarea>
			<div class="row">
				<div class="center iteration no-flex">{$smarty.foreach.rate_rageF.iteration}</div>
				<div data-caption="{$lang.from}">{$rRange.From|date_format:$smarty.const.RL_DATE_FORMAT}</div>
				<div data-caption="{$lang.to}">{$rRange.To|date_format:$smarty.const.RL_DATE_FORMAT}</div>
				<div data-caption="{$lang.price}">{if $rRange.Price == 0}{$lang.booking_close_days}{else}{$defPrice.currency} {str2money string=$rRange.Price}{/if}</div>
				<div data-caption="{$lang.booking_desc}">
					{assign var='qtip_e' value=" <a href='javascript:;' onclick='edit_desc("|cat:$smarty.foreach.rate_rageF.iteration|cat:")'>"|cat:$lang.edit|cat:"</a>"}
					<img class="qtip" alt="" title="{if !empty($rRange.desc)}{$rRange.desc}{else}{$lang.not_available}{/if}{$qtip_e}" id="desc_ico_{$smarty.foreach.rate_rageF.iteration}" src="{$rlTplBase}img/blank.gif" />
				</div>
				<div data-caption="{$lang.actions}"><img class="remove" onclick="rlConfirm( '{$lang.booking_remove_confirm}', 'xajax_deleteRateRange', Array('{$rRange.ID}'), 'listing_loading' );" src="{$rlTplBase}img/blank.gif" /></div>
			</div>
			{/foreach}

			{if $use_time_frame}
				<textarea class="hide" id="save_desc_regualr" cols="30" rows="2">{$rRange.desc}</textarea>
				<div class="row">
					<div class="center iteration no-flex">{math equation="x + 1" x=$smarty.foreach.rate_rageF.iteration}</div>
					<div data-caption="{$lang.from}">{$lang.booking_rate_price_per_day}</div>
					<div data-caption="{$lang.to}"></div>
					<div data-caption="{$lang.price}">{$defPrice.name}</div>
					<div data-caption="{$lang.booking_desc}">
						{assign var='qtip_e_regular' value=" <a href='javascript:;' onclick='edit_desc(0, 1);'>"|cat:$lang.edit|cat:"</a>"}
						<img class="qtip" alt="" title="{if !empty($range_regular_desc)}{$range_regular_desc}{else}{$lang.not_available}{/if}{$qtip_e_regular}" id="desc_ico_regular" src="{$rlTplBase}img/blank.gif" />
					</div>
					<div data-caption="{$lang.actions}">{$lang.not_available}</div>
				</div>
			{/if}
		</div>

		<div class="two-inline left" style="padding: 10px 0 0 0;">
			<div style="padding: 0 10px;"><a class="static" id="label_range" href="javascript:void(0);" onclick="add_rate_range();">{$lang.booking_rate_add}</a></div>
			<div style="text-align: {$text_dir_rev};padding: 0 30px;"><input class="hide button" type="button" value="{$lang.save}" id="label_save_range" /></div>
		</div>
	</form>
</div>

<!-- rate range tpl end -->