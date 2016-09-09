<!-- binding days -->

<div id="bindings_obj">
	<form id="binding_days_form" method="post">
		<div class="list-table content-padding" id="rate_ranges_table">
			<div class="header">
				<div class="checkbox" style="width: 200px;">{$lang.booking_checkin}</div>
				<div>{$lang.booking_checkout}</div>
				<div style="width: 80px;">{$lang.actions}</div>
			</div>

			<div class="row" id="bind_days_checkbox">
				<div class="checkbox" data-caption="{$lang.booking_checkin}">
					{foreach from=$mass_days key='kD' item='day'}
						<div style="padding: 0 0 5px;"><label><input class="inline" type="checkbox" {if in_array($day,","|explode:$binding_days.Checkin)}checked="true"{/if} name="in" value="{$day}" /> {$day}</label></div>
					{/foreach}
				</div>
				<div data-caption="{$lang.booking_checkout}">
					{foreach from=$mass_days key='kD' item='day'}
						<div style="padding: 0 0 5px;"><label><input class="inline" type="checkbox" {if in_array($day,","|explode:$binding_days.Checkout)}checked="true"{/if} name="out" value="{$day}" /> {$day}</label></div>
					{/foreach}
				</div>
				<div data-caption="{$lang.actions}">
					<a href="javascript:;" class="button" onclick="save_binding_days();">{$lang.save}</a>
				</div>
			</div>
		</div>
	</form>
</div>

<!-- binding days end -->