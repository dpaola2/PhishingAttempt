<!-- header user navigation bar | responsive -->

{php}
global $config;

$curConv_rates = $this -> get_template_vars('curConv_rates');
$display_currency = '-//-';
$this -> assign_by_ref('display_currency', $display_currency);

$check_currency = $_COOKIE['curConv_code'];
$this -> assign_by_ref('check_currency', $check_currency);

if ( !$check_currency ) {
	$check_currency = $config['system_currency'];

	foreach ( $curConv_rates as $key => $item) {
		$symbols = explode(',', $item['Symbol']);

		if ( $key == $check_currency || in_array($check_currency, $symbols) ) {
			$display_currency = $symbols[0] ? $symbols[0] : $item['Code'];
			setcookie('curConv_code', $key, time()+2678400, '/');
			$check_currency = $key;
			break;
		}
	}
}
else {
	$symbol = explode(',', $GLOBALS['rlCurrencyConverter'] -> rates[$check_currency]['Symbol']);
	if ( $symbol[0] ) {
		$display_currency = $symbol[0];
	}
	else {
		$display_currency = $GLOBALS['rlCurrencyConverter'] -> rates[$check_currency]['Code'];
	}
}
{/php}
<span class="circle currency-selector" id="currency_selector">
	<span class="default"><span class="{if $display_currency|@strlen == 3}code{else}symbol{/if}">{$display_currency}</span></span>
	<span class="content hide">
		<div>
			<ul>
			{foreach from=$curConv_rates item='curConv_rate' key='currencyKey'}
				<li><a href="javascript://" title="{$curConv_rate.Country}" class="font1{if $currencyKey == $check_currency} active{/if}" accesskey="{$currencyKey}">{$curConv_rate.Code}</a></li>
			{/foreach}
			</ul>
		</div>
	</span>
</span>

<!-- header user navigation bar | responsive end -->