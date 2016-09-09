<!-- shoppingCart plugin -->

<div id="shc-group" class="hide">
	{assign var='currency' value='currency'|df}
	{assign var='shc_gf_name' value='listing_groups+name+shopping_cart'}
    {include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='shopping_cart' name=$lang[$shc_gf_name]}

	<input type="hidden" name="fshc[shc_mode]" value="{if $smarty.post.fshc.shc_mode}{$smarty.post.fshc.shc_mode}{elseif $config.shc_module_auction}auction{elseif $config.shc_module}fixed{else}listing{/if}" id="sf_field_shc_mode" />

	<div class="ml_tabs" id="shc_tabs">
		<ul>
			{if $config.shc_module_auction}<li lang="auction" class="active">{$lang.shc_mode_auction}</li>{/if}
			{if $config.shc_module}<li lang="fixed">{$lang.shc_mode_fixed}</li>{/if}
			<li lang="listing">{$lang.shc_mode_listing}</li>
		</ul>
		<div class="nav left"></div>
		<div class="nav right"></div>
	</div>
	<div class="ml_tabs_content">
		<div lang="auction" id="shc_fields_area">
			{assign var='feilds_tpl_name' value='fields.tpl'}
			{if $tpl_settings.type == 'responsive_42'}
				{assign var='feilds_tpl_name' value='fields_responsive_42.tpl'}
			{/if}
			{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:$feilds_tpl_name}	
		</div>
	</div>

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
</div>

<script type="text/javascript">//<![CDATA[
var shcConfig = new Array();
var shc_check_settings = {if $config.shc_method == 'multi'}false{else}true{/if};

shcConfig['containerPosition'] = '{$config.shc_fields_position}';
shcConfig['containerPositionType'] = '{$config.shc_fields_position_type}';

var listing_field_price = '{$config.shc_listing_field_price}';
var shc_mode = '{if $smarty.post.fshc.shc_mode}{$smarty.post.fshc.shc_mode}{elseif $config.shc_module_auction}auction{elseif $config.shc_module}fixed{else}listing{/if}';

{literal}

var shcReplaceFieldPrice = function() {
	$('#sf_field_' + listing_field_price).parent().remove();
}

var shcPriceFormatTabs = function(mode) {
	$('#shc_tabs ul>li').each(function() {
		if ( $(this).attr('lang') == mode ) {
			if ( !$(this).hasClass('active') ) {
		   		$(this).addClass('active');
			}
		}
		else {
			$(this).removeClass('active');
		}
	});

	$('input[name="fshc[shc_mode]"]').val(mode);
	$('#shc_fields_area').attr('lang', mode);

	$('#shc_fields_area table.submit tr,#shc_fields_area div.submit-cell').each(function() {
		if ( $(this).hasClass(mode) || !$(this).attr('class') ) {
			$(this).show();
		}
		else {
			$(this).hide();
		}
	});

	$('.price_item').each(function() {
		if ( $(this).hasClass(mode) || !$(this).attr('class') ) {
			$(this).show();
		}
		else {
			$(this).hide();
		}
	});

	if ( (mode == 'auction' || mode == 'fixed') && !shc_check_settings ) {
		xajax_checkAccountSettings();
		shc_check_settings = true;
	}

	if ( mode == 'auction' ) {
		$('input[name="fshc[shc_quantity]"]').val(1);
		$('input[name="fshc[shc_quantity]"]').prop('disabled', true);
	}
	else if( mode == 'fixed' ) {
		$('input[name="fshc[shc_quantity]"]').prop('disabled', false);
	}
	else if( mode == 'listing' ) {
		$('input[name="fshc[shc_quantity]"]').val(1);
	}
}

$(document).ready(function() {
	if ( listing_field_price == '' ) {
		printMessage('warning', '{/literal}{$lang.shc_price_field_not_selected}{literal}');
	}
	else {
		/* assign group container */
		if ( shcConfig['containerPosition'] == 'bottom' ) {
			if ( rlConfig['template_type'] == 'responsive_42' ) {
				$('span.form-buttons').before($('#shc-group'));
			}
			else {
				$('div#controller_area form table.submit:last').before($('#shc-group'));
			}
		}
		else if ( shcConfig['containerPosition'] != 'top' ) {
			if ( shcConfig['containerPositionType'] == 'prepend' ) {
				$('div#fs_'+shcConfig['containerPosition']+' > div.body').prepend($('#shc-group'));
			}
			else {
				$('div#fs_'+shcConfig['containerPosition']+' > div.body').append($('#shc-group'));
			}
		}

		$('#shc_tabs ul > li').click(function() {
			shcPriceFormatTabs($(this).attr('lang'));
		});

		if ( shc_mode ) {
			shcPriceFormatTabs(shc_mode);
		}
		shcReplaceFieldPrice();
		
		$('#shc-group').show();
	}
});

{/literal}
//]]>
</script>

<!-- end shoppingCart plugin -->