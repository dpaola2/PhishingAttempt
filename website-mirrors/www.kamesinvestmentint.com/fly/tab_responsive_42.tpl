<!-- loan mortgage tab content -->

<div id="area_loanMortgage" class="tab_area content-padding hide">
	<a href="" class="print"><span></span></a>

	<div class="top">{strip}
		<div>
			<div class="fieldset divider"><header>{$lang.loanMortgage_loan_terms}</header></div>

			<div class="submit-cell">
				<div class="name" title="{$lang.loanMortgage_loan_amount}">{$lang.loanMortgage_loan_amount}</div>
				<div class="field combo-field">
					<input type="text" name="lm_loan_amount" class="wauto" size="6" id="lm_loan_amount" value="{$lm_amount.0}" />
					{if $curConv_country.Currency && $lm_amount.1 != $curConv_country.Currency && $lm_amount.1}
						<span title="{$lang.loanMortgage_switch}" id="lm_loan_cur_area" class="pointer">
						<span id="lm_loan_cur_orig" style="font-weight: bold;">{$lm_amount.1}</span>/<span id="lm_loan_cur_conv" class="lm_opacity">{$curConv_rates[$curConv_country.Currency].Code}</span>
						</span>
					{else}
						{$lm_amount.1}
					{/if}
				</div>
			</div>
			<div class="submit-cell">
				<div class="name" title="{$lang.loanMortgage_loan_term}">{$lang.loanMortgage_loan_term}</div>
				<div class="field combo-field">
					<input maxlength="3" type="text" class="wauto" size="3" name="lm_loan_term" id="lm_loan_term" value="{if $config.loanMortgage_loan_term > 0}{$config.loanMortgage_loan_term}{/if}" />
					<span title="{$lang.loanMortgage_switch}" id="lm_loan_term_area" style="cursor: pointer;">
					<span id="lm_loan_term_year" {if $config.loanMortgage_loan_term_mode == 'Years'}style="font-weight: bold;"{else}class="lm_opacity"{/if}>{$lang.loanMortgage_years}</span>/<span id="lm_loan_term_month" {if $config.loanMortgage_loan_term_mode == 'Months'}style="font-weight: bold;"{else}class="lm_opacity"{/if}>{$lang.loanMortgage_months}</span>
					</span>
				</div>
			</div>
			<div class="submit-cell">
				<div class="name" title="{$lang.loanMortgage_interest_rate}">{$lang.loanMortgage_interest_rate}</div>
				<div class="field combo-field">
					<input type="text" maxlength="3" class="wauto" size="3" name="lm_loan_rate" id="lm_loan_rate" value="{if $config.loanMortgage_loan_rate > 0}{$config.loanMortgage_loan_rate}{/if}" /> %
				</div>
			</div>
			<div class="submit-cell">
				<div class="name" title="{$lang.loanMortgage_first_pmt_date}">{$lang.loanMortgage_first_pmt_date}</div>
				<div class="field two-fields">
					<select id="lm_loan_date_month"></select>
					<select id="lm_loan_date_year"></select>
				</div>
			</div>
			<div class="submit-cell buttons">
				<div class="name"></div>
				<div class="field two-fields">
					<input onclick="loan_check();" title="{$lang.loanMortgage_calculate}" type="button" id="lm_loan_calculate" value="{$lang.loanMortgage_calculate}" />
					<span onclick="loan_clear();" title="{$lang.loanMortgage_reset}" class="red margin">{$lang.loanMortgage_reset}</span>
				</div>
			</div>
		</div>

		<div>
			<div class="fieldset divider"><header>{$lang.loanMortgage_payments}</header></div>
			<div id="lm_details_area">
				{$lang.loanMortgage_start_message}
			</div>
		</div>
	{/strip}</div>
	
	<div id="lm_amortization" class="hide">
		<div class="fieldset divider"><header>{$lang.loanMortgage_amz_schedule}</header></div>
		<div id="lm_amortization_area"></div>
	</div>
</div>

<script type="text/javascript">//<![CDATA[
var lm_configs = new Array();
lm_configs['mode'] = false;
lm_configs['print_page_path'] = '{$pages.loanMortgage_print}';
lm_configs['listing_id'] = {if $listing_data.ID}{$listing_data.ID}{else}false{/if};
lm_configs['show_cents'] = {$config.show_cents};
lm_configs['price_delimiter'] = "{$config.price_delimiter}";
lm_configs['currency'] = '{$lm_amount.1}';
lm_configs['lang_code'] = '{if $smarty.const.RL_LANG_CODE == 'en'}en-GB{else}{$smarty.const.RL_LANG_CODE|lower}{/if}';
lm_configs['loan_term_mode'] = '{if $config.loanMortgage_loan_term_mode == 'Years'}year{else}month{/if}';
lm_configs['loan_currency_mode'] = 'original';
lm_configs['loan_orig_amount'] = {if $lm_amount.0}{$lm_amount.0}{else}0{/if};
lm_configs['loan_orig_currency'] = '{$lm_amount.1}';

var lm_phrases = new Array();
lm_phrases['loan_amount'] = '{$lang.loanMortgage_loan_amount}';
lm_phrases['num_payments'] = '{$lang.loanMortgage_num_payments}';
lm_phrases['monthly_payment'] = '{$lang.loanMortgage_monthly_payment}';
lm_phrases['total_paid'] = '{$lang.loanMortgage_total_paid}';
lm_phrases['total_interest'] = '{$lang.loanMortgage_total_interest}';
lm_phrases['payoff_date'] = '{$lang.loanMortgage_payoff_date}';
lm_phrases['pmt_date'] = '{$lang.loanMortgage_pmt_date}';
lm_phrases['amount'] = '{$lang.loanMortgage_amount}';
lm_phrases['interest'] = '{$lang.loanMortgage_interest}';
lm_phrases['principal'] = '{$lang.loanMortgage_principal}';
lm_phrases['balance'] = '{$lang.loanMortgage_balance}';
lm_phrases['error_amount'] = '{$lang.loanMortgage_error_amount}';
lm_phrases['error_term'] = '{$lang.loanMortgage_error_term}';
lm_phrases['error_rate'] = '{$lang.loanMortgage_error_rate}';

var lm_left_align = '{$text_dir}';
var lm_right_align = '{$text_dir_rev}';

//]]>
</script>
<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}loanMortgageCalculator/static/loan_calc.js"></script>

<script type="text/javascript">//<![CDATA[
{literal}
$(document).ready(function(){
	
	/* animate opacity */
	$('.lm_opacity').animate({opacity: 0.4});
	
	/* set default amount */
	$('#lm_loan_amount').val(lm_configs['loan_orig_amount']);
	
	/* months/years switcher */
	$('#lm_loan_term_area').click(function(){
		if ( lm_configs['loan_term_mode'] == 'year' ) {
			/* switch to month */
			$('#lm_loan_term_year').css('font-weight', 'normal').animate({opacity: 0.4});
			$('#lm_loan_term_month').css('font-weight', 'bold').animate({opacity: 1});
			
			lm_configs['loan_term_mode'] = 'month';
		}
		else {
			/* switch to year */
			$('#lm_loan_term_month').css('font-weight', 'normal').animate({opacity: 0.4});
			$('#lm_loan_term_year').css('font-weight', 'bold').animate({opacity: 1});
			
			lm_configs['loan_term_mode'] = 'year';
		}
		
		if ( lm_configs['mode'] ) {
			loan_check();
		}
	});
	
	/* currency switcher */
	$('#lm_loan_cur_area').click(function(){
		if ( lm_configs['loan_currency_mode'] == 'original' ) {
			/* switch to month */
			$('#lm_loan_cur_orig').css('font-weight', 'normal').animate({opacity: 0.4});
			$('#lm_loan_cur_conv').css('font-weight', 'bold').animate({opacity: 1});
			
			var price = $('#lm_loan_amount').val() / currencyConverter.inRange(lm_configs['currency']) * currencyConverter.rates[currencyConverter.config['currency']][0];
			price = currencyConverter.encodePrice(price, true, true);
			$('#lm_loan_amount').val(price);
			
			lm_configs['loan_currency_mode'] = 'converted';
		}
		else {
			/* switch to year */
			$('#lm_loan_cur_conv').css('font-weight', 'normal').animate({opacity: 0.4});
			$('#lm_loan_cur_orig').css('font-weight', 'bold').animate({opacity: 1});
			
			$('#lm_loan_amount').val(lm_configs['loan_orig_amount']);
			lm_configs['currency'] = lm_configs['loan_orig_currency'];
			
			lm_configs['loan_currency_mode'] = 'original';
		}
		
		if ( lm_configs['mode'] ) {
			loan_check();
		}
	});
	
	loan_build_payment_date();
	
	/* print icon handler */
	$('div#area_loanMortgage a.print').on('click', function(){
		var key = $('.tabs .active').attr('id').replace('tab_', '');
		if ( key == 'loanMortgage' ) {
			if ( loan_check(true) ) {
				var url = rlConfig['seo_url'];
				url += rlConfig['mod_rewrite'] ? lm_configs['print_page_path'] +'.html?' : '?page='+lm_configs['print_page_path']+'&';
				
				var loanamt = $('#lm_loan_amount').val();
				var term = $('#lm_loan_term').val();
				var rate = $('#lm_loan_rate').val();
				var month = $('#lm_loan_date_month option:selected').text();
				var year = $('#lm_loan_date_year option:selected').text();
				
				url += 'id='+lm_configs['listing_id']+'&';
				url += 'amount='+loanamt+'&';
				url += 'currency='+lm_configs['currency']+'&';
				url += 'term='+term+'&';
				url += 'term_mode='+lm_configs['loan_term_mode']+'&';
				url += 'rate='+rate+'&';
				url += 'mode='+lm_configs['loan_currency_mode']+'&';
				url += 'date_month='+month+'&';
				url += 'date_month_number='+$('#lm_loan_date_month').val()+'&';
				url += 'date_year='+year;
				
				window.open(url, '_blank');
			}
			
			return false;
		}
	});
});
{/literal}
//]]>
</script>

<!-- loan mortgage tab content end -->
