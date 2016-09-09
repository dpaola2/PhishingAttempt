
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: LOAN_CALC.JS
 *
 *	The software is a commercial product delivered under single, non-exclusive, 
 *	non-transferable license for one domain or IP address. Therefore distribution, 
 *	sale or transfer of the file in whole or in part without permission of Flynax 
 *	respective owners is considered to be illegal and breach of Flynax License End 
 *	User Agreement. 
 *
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *
 *	Flynax Classifieds Software 2014 |  All copyrights reserved. 
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/* check form */
function loan_check(validate)
{
	var loanamt = $('#lm_loan_amount').val();
	var paymnt = $('#lm_loan_term').val();
	var rate = $('#lm_loan_rate').val();
	
	var errors = new Array();

	if( loanamt == '' || isNaN(parseFloat(loanamt)) || loanamt == 0 )
	{
		errors.push(lm_phrases['error_amount']);
		$('#lm_loan_amount').focus();
	}
	if( paymnt == '' || isNaN(parseFloat(paymnt)) || paymnt == 0 )
	{
		errors.push(lm_phrases['error_term']);		
		$('#lm_loan_term').focus();
	}
	if( rate == '' || isNaN(parseFloat(rate)) || rate == 0 )
	{
		errors.push(lm_phrases['error_rate']);		
		$('#lm_loan_rate').focus();
	}
	
	if ( errors.length > 0 )
	{
		printMessage('error', errors);
		return false;
	}
	else
	{
		if ( !validate ) {
			loan_show(
				$('#lm_loan_term').val(),
				$('#lm_loan_amount').val(),
				lm_configs['loan_term_mode'],
				$('#lm_loan_rate').val(),
				$('#lm_loan_date_month').val(),
				$('#lm_loan_date_year').val()
			);
		}
		else {
			return true;
		}
	}
}

var lm_encode_price = function( str, show_currency, delimiter ){
	str = str.toFixed(2);

	/* convert price to string */
	eval("var converted = '"+ str +"'");
	
	var index = converted.indexOf('.');
	var rest = lm_configs['show_cents'] ? '.00' : '';
	
	if (index >= 0)
	{
		rest = converted.substring(index);
		
		if ( rest == '.00' && !lm_configs['show_cents'] )
		{
			rest = '';
		}
		
		converted = converted.substring(0, index);
	}
	
	/* revers string */
	var res = '';
	converted = converted.reverse();
	for(var i = 0; i < converted.length; i++)
	{
		var char = converted.charAt(i);
		res += char;
		var j = i+1;
		if ( j % 3 == 0 && j != converted.length && delimiter !== false )
		{
			res += lm_configs['price_delimiter'];
		}
	}
	converted = res.reverse();
	converted = parseInt(converted) == 0 ? 0 : converted;
	converted = show_currency === false ? converted+rest : lm_configs['currency']+' '+converted+rest;
	
	return converted;
}

function loan_clear()
{
	$('#lm_loan_term').val('');
	$('#lm_loan_rate').val('');
	
	$('#lm_details_area').html('');
	$('#lm_amortization').slideUp();
	
	loan_build_payment_date();
}

function loan_build_payment_date()
{
	var date = new Date();
	
	/* build months */
	var cur_month = date.getMonth();
	var selected = '';
	
	var months = '';
	for ( var i = 0; i < 12; i++ )
	{
		var month_number = i + 1;
		selected = i == cur_month ? ' selected="selected"' : '';
		months += '<option value="'+month_number+'"'+selected+'>' + $.datepicker.regional[lm_configs['lang_code']].monthNamesShort[i] + '</option>';
	}
	
	$('#lm_loan_date_month').html(months);
	
	/* build years */
	var cur_year = date.getFullYear();
	var selected = '';
	
	var years = '';
	for ( var i = cur_year - 10; i < cur_year + 50; i++ )
	{
		selected = i == cur_year ? ' selected="selected"' : '';
		years += '<option value="'+i+'"'+selected+'>' + i + '</option>';
	}
	
	$('#lm_loan_date_year').html(years);
}

function lm_increase_month( start_year, start_month, months )
{
	var date = new Date(start_year, start_month-1, 1);
	date.setMonth(date.getMonth() + months);
	
	return date;
}

function loan_show(loan_term, loan_amount, term_unit, loan_rate, date_month, date_year) {
	
	lm_configs['mode'] = true;
	
	var date_val = parseInt(loan_term);
	var amount = parseFloat(loan_amount);
	var numpay = term_unit == 'year' ? date_val * 12 : date_val;
	var rate = parseFloat(loan_rate);
	var date_month = parseInt(date_month);
	var date_year = parseInt(date_year);
	
	/* currency converter plugin mode */
	if ( lm_configs['loan_currency_mode'] == 'converted' )
	{
		lm_configs['currency'] = currencyConverter.rates[currencyConverter.config['currency']][1][0];
	}
	
	if ( term_unit == 'year' )
	{
		var new_year = date_year+date_val;
		if ( date_month == 1 )
		{
			var month_index = 11;
			new_year--;
		}
		else
		{
			var month_index = date_month - 2;
		}
		var date_off = $.datepicker.regional[lm_configs['lang_code']].monthNamesShort[month_index] +', '+new_year;
	}
	else
	{
		var new_date = lm_increase_month(date_year, date_month, date_val);
		//var year_index = new_date.getMonth() == 1 ? 11 : new_date.getMonth()-1;
		var year_index = new_date.getMonth();
		
		var date_off = $.datepicker.regional[lm_configs['lang_code']].monthNamesShort[year_index] +', '+new_date.getFullYear();
	}
 
	rate = rate / 100;
	var monthly  = rate / 12;
	var payment  = ( (amount * monthly) / (1 - Math.pow( (1 + monthly), -numpay) ) );
	var total    = payment * numpay;
	var interest = total - amount;

	var summary_table = [
		[lm_phrases['loan_amount'], lm_phrases['num_payments'], lm_phrases['monthly_payment'], lm_phrases['total_paid'], lm_phrases['total_interest'],  lm_phrases['payoff_date']],
		[lm_encode_price(amount), numpay, lm_encode_price(payment), lm_encode_price(total), lm_encode_price(interest), date_off]
	];
	var header_table = [lm_phrases['pmt_date'], lm_phrases['amount'], lm_phrases['interest'], lm_phrases['principal'], lm_phrases['balance']];
	var output = '';

	/* responsive 42 */
	if ( rlConfig['template_type'] == 'responsive_42' && !lm_configs['print'] ) {
		for ( var i = 0; i < summary_table[0].length; i++ ) {
			output += '<div class="table-cell"> \
				<div class="name"><div><span>'+ summary_table[0][i] +'</span></div></div> \
				<div class="value">'+ summary_table[1][i] +'</div> \
			</div>';
		}

		var row_tpl = '<div class="row"> \
			<div class="first" data-caption="'+header_table[0]+'">{row_value_1}</div> \
			<div data-caption="'+header_table[1]+'">{row_value_2}</div> \
			<div data-caption="'+header_table[2]+'">{row_value_3}</div> \
			<div data-caption="'+header_table[3]+'">{row_value_4}</div> \
			<div data-caption="'+header_table[4]+'">{row_value_5}</div> \
		</div>';
	}
	else {
		output += '<table class="table">';
		for ( var i = 0; i < summary_table[0].length; i++ ) {
			output += '<tr> \
				<td class="name">'+ summary_table[0][i] +'</td> \
				<td class="value">'+ summary_table[1][i] +'</td> \
			</tr>';
		}
		output += '</table>';
	}
	
	$('#lm_details_area').html(output);
	$('#lm_show_amortization').fadeIn();

	if ( rlConfig['template_type'] == 'responsive_42' && !lm_configs['print'] ) {
		var detail = '<div class="list-table"> \
			<div class="header"> \
				<div class="first" style="width: 24%;">'+ header_table[0] +'</div> \
				<div style="width: 19%;">'+ header_table[1] +'</div> \
				<div style="width: 19%;">'+ header_table[2] +'</div> \
				<div style="width: 19%;">'+ header_table[3] +'</div> \
				<div style="width: 19%;">'+ header_table[4] +'</div> \
			</div>';

		detail += row_tpl
			.replace('{row_value_1}', '-')
			.replace('{row_value_2}', '-')
			.replace('{row_value_3}', '-')
			.replace('{row_value_4}', '-')
			.replace('{row_value_5}', lm_encode_price(amount));
	}
	else {
		var detail = '<table class="list"> \
			<tr class="header"> \
				<td align="center"><b>'+ lm_phrases['pmt_date'] +'</b></td> \
				<td class="divider"></td> \
				<td align="'+lm_right_align+'"><div style="margin-'+lm_right_align+': 5px;"><b>'+ lm_phrases['amount'] +'</b></div></td> \
				<td class="divider"></td> \
				<td align="'+lm_right_align+'"><div style="margin-'+lm_right_align+': 5px;"><b>'+ lm_phrases['interest'] +'</b></div></td> \
				<td class="divider"></td> \
				<td align="'+lm_right_align+'"><div style="margin-'+lm_right_align+': 5px;"><b>'+ lm_phrases['principal'] +'</b></div></td> \
				<td class="divider"></td> \
				<td align="'+lm_right_align+'"><div style="margin-'+lm_right_align+': 5px;"><b>'+ lm_phrases['balance'] +'</b></div></td> \
			</tr> \
			<tr class="body"> \
				<td class="first" align="center">-</td> \
				<td class="divider"></td> \
				<td align="'+lm_right_align+'">-</td> \
				<td class="divider"></td> \
				<td align="'+lm_right_align+'">-</td> \
				<td class="divider"></td> \
				<td align="'+lm_right_align+'">-</td> \
				<td class="divider"></td> \
				<td align="'+lm_right_align+'">'+lm_encode_price(amount)+'</td> \
			</tr>';
	}

	newPrincipal = amount;

	var i = j = 1;
	var outInterest = 0;
	var outReduction = 0;
	var point = 12;
	
	/* year mode */
	if ( lm_configs['loan_term_mode'] == 'year' )
	{
		point = 13 - date_month;
	}
	
	while (i <= numpay) {
		newInterest  = monthly * newPrincipal;
		reduction    = payment - newInterest;
		newPrincipal = newPrincipal - reduction;
		
		outInterest  += newInterest;
		outReduction += reduction;
		
		if ( lm_configs['loan_term_mode'] == 'year' )
		{
			if ( i % point == 0 || i == numpay )
			{
				point += 12;
				
				var it_date = lm_increase_month(date_year, date_month, i-1);
				var pmt_date = $.datepicker.regional[lm_configs['lang_code']].monthNamesShort[it_date.getMonth()] +', '+it_date.getFullYear();
				
				if ( rlConfig['template_type'] == 'responsive_42' && !lm_configs['print'] ) {
					detail += row_tpl
						.replace('{row_value_1}', pmt_date)
						.replace('{row_value_2}', lm_encode_price(payment, false))
						.replace('{row_value_3}', lm_encode_price(outInterest, false))
						.replace('{row_value_4}', lm_encode_price(outReduction, false))
						.replace('{row_value_5}', lm_encode_price(newPrincipal, false));
				}
				else {
					detail += '<tr class="body"> \
						<td class="first" align="center"><span class="fLable">'+pmt_date+'</span></td> \
						<td class="divider"></td> \
						<td align="'+lm_right_align+'">'+lm_encode_price(payment, false)+'</td> \
						<td class="divider"></td> \
						<td align="'+lm_right_align+'">'+lm_encode_price(outInterest, false)+'</td> \
						<td class="divider"></td> \
						<td align="'+lm_right_align+'">'+lm_encode_price(outReduction, false)+'</td> \
						<td class="divider"></td> \
						<td align="'+lm_right_align+'"><b>'+lm_encode_price(newPrincipal, false)+'</b></td> \
					</tr>';
				}
				
				outInterest = outReduction = 0;
				j++;
			}
		}
		else
		{
			var it_date = lm_increase_month(date_year, date_month, i-1);
			var pmt_date = $.datepicker.regional[lm_configs['lang_code']].monthNamesShort[it_date.getMonth()] +', '+it_date.getFullYear();

			if ( rlConfig['template_type'] == 'responsive_42' && !lm_configs['print'] ) {
				detail += row_tpl
					.replace('{row_value_1}', pmt_date)
					.replace('{row_value_2}', lm_encode_price(payment, false))
					.replace('{row_value_3}', lm_encode_price(newInterest, false))
					.replace('{row_value_4}', lm_encode_price(reduction, false))
					.replace('{row_value_5}', lm_encode_price(newPrincipal, false));
			}
			else {
				detail += '<tr> \
					<td style="padding: 8px;" class="grey_line_1 grey_small" align="center"><span class="fLable">'+pmt_date+'</span></td> \
					<td></td> \
					<td class="grey_line_1 grey_small" align="'+lm_right_align+'">'+lm_encode_price(payment, false)+'</td> \
					<td></td> \
					<td class="grey_line_1 grey_small" align="'+lm_right_align+'">'+lm_encode_price(newInterest, false)+'</td> \
					<td></td> \
					<td class="grey_line_1 grey_small" align="'+lm_right_align+'">'+lm_encode_price(reduction, false)+'</td> \
					<td></td> \
					<td class="grey_line_1 grey_small" align="'+lm_right_align+'"><b>'+lm_encode_price(newPrincipal, false)+'</b></td> \
				</tr>';
			}
		}

		i++;
	}

	detail += rlConfig['template_type'] == 'responsive_42' ? "</div>" : "</table>";
	
	$('#lm_amortization_area').html(detail);
	$('#lm_amortization').slideDown();
}

if(typeof window.reverse != 'function')
{
	String.prototype.reverse = function() {
	    var s = "";
	    var i = this.length;
	    while (i>0) {
	        s += this.substring(i-1,i);
	        i--;
	    }
	    return s;
	}
}