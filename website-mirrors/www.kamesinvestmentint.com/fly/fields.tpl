{assign var='shcFVal' value=$smarty.post.f}

<table class="submit">
	<tr class="auction fixed listing">
		<td class="name price_name">
			{$lang.price}
		</td>
		<td class="field" id="sf_field_shc_start_price" valign="bottom">
			<div class="price_item auction">
				{assign var='shc_lf_name' value='listing_fields+name+shc_start_price'}
			    <span>{$lang[$shc_lf_name]}</span><br />
				<input class="numeric w70" type="text" name="fshc[shc_start_price]" size="8" maxlength="15" {if $smarty.post.fshc.shc_start_price}value="{$smarty.post.fshc.shc_start_price}"{/if} />
			</div>
			<div class="price_item auction">
				{assign var='shc_lf_name' value='listing_fields+name+shc_reserved_price'}
			    <span>{$lang[$shc_lf_name]}</span><br />
				<input class="numeric w70" type="text" name="fshc[shc_reserved_price]" size="8" maxlength="15" {if $smarty.post.fshc.shc_reserved_price}value="{$smarty.post.fshc.shc_reserved_price}"{/if} />
			</div>
			<div class="price_item auction fixed listing">
			    <span>{$lang.shc_buy_now}</span><br />
				<input class="numeric w70" type="text" name="f[{$config.shc_listing_field_price}][value]" size="8" maxlength="15" {if $shcFVal[$config.shc_listing_field_price].value}value="{$shcFVal[$config.shc_listing_field_price].value}"{/if} />
			</div>
			<div class="price_item auction fixed listing">
			    <span>&nbsp;</span><br />
				{if $currency|@count > 1}
					<select name="f[{$config.shc_listing_field_price}][currency]" class="w60">
						{foreach from=$currency item='currency_item'}
							<option value="{$currency_item.Key}" {if ($currency_item.Key == $smarty.post.fshc.shc_start_price.currency) || $currency_item.Default}selected="selected"{/if}>{$lang[$currency_item.pName]}</option>
						{/foreach}
					</select>
				{else}
					<input type="hidden" name="f[{$config.shc_listing_field_price}][currency]" value="{$currency.0.Key}" />
					{$currency.0.name}
				{/if}
			</div>
		</td>
	</tr>
	<tr class="auction">
		<td class="name">
			{assign var='shc_lf_name' value='listing_fields+name+shc_bid_step'}
			{$lang[$shc_lf_name]}
		</td>
		<td class="field" id="sf_field_shc_bid_step">
			<input class="numeric w50" type="text" name="fshc[shc_bid_step]" maxlength="11" {if $smarty.post.fshc.shc_bid_step}value="{$smarty.post.fshc.shc_bid_step}"{/if} />&nbsp;<span id="bid_currency">{$currency.0.name}</span>
		</td>
	</tr>
	<tr class="auction">
		<td class="name">
			{assign var='shc_lf_name' value='listing_fields+name+shc_days'}
			{$lang[$shc_lf_name]}
		</td>
		<td class="field" id="sf_field_shc_days">
			<input class="numeric w50" type="text" name="fshc[shc_days]" maxlength="11" {if $smarty.post.fshc.shc_days}value="{$smarty.post.fshc.shc_days}"{/if} />&nbsp;<span>{$lang.shc_days}</span>
		</td>
	</tr>
	<tr class="auction fixed">
		<td class="name">
			{assign var='shc_lf_name' value='listing_fields+name+shc_quantity'}
			{$lang[$shc_lf_name]}
		</td>
		<td class="field" id="sf_field_shc_quantity">
			<input class="numeric w50" type="text" name="fshc[shc_quantity]" maxlength="11" value="{if $smarty.post.fshc.shc_quantity}{$smarty.post.fshc.shc_quantity}{else}0{/if}" />
		</td>
	</tr>
	<tr class="auction fixed">
		<td class="name">
			{assign var='shc_lf_name' value='listing_fields+name+shc_weight'}
			{$lang[$shc_lf_name]}
		</td>
		<td class="field" id="sf_field_shc_weight">
			<input class="numeric w50" type="text" name="fshc[shc_weight]" maxlength="11" value="{if $smarty.post.fshc.shc_weight}{$smarty.post.fshc.shc_weight}{else}0{/if}" />
			{assign var='shc_lf_weight' value='shc_weight_unit_'|cat:$config.shc_weight_unit}
			&nbsp;<span>{$lang[$shc_lf_weight]}</span>
		</td>
	</tr>
	<tr class="fixed">
		<td class="name">
			{assign var='shc_lf_name' value='listing_fields+name+shc_available'}
			{$lang[$shc_lf_name]}
		</td>
		<td class="field" id="sf_field_shc_available">
			<label><input type="radio" value="1" name="fshc[shc_available]" {if $smarty.post.fshc.shc_available == '1' || $smarty.post.fshc.shc_available == ''}checked="checked"{/if} /> {$lang.yes}</label>
			<label><input type="radio" value="0" name="fshc[shc_available]" {if $smarty.post.fshc.shc_available == '0'}checked="checked"{/if} /> {$lang.no}</label>
		</td>
	</tr>
	{if $pageInfo.Controller == 'edit_listing'}
	<tr class="auction">
		<td class="name"></td>
		<td class="field" id="sf_field_shc_update_start_time">
			<label><input type="checkbox" value="1" name="fshc[shc_update_start_time]" {if $smarty.post.fshc.shc_update_start_time == '1'}checked="checked"{/if} /> {$lang.shc_update_start_time}</label>
			<input type="hidden" name="fshc[shc_edit]" value="1" />
			{if $smarty.post.fshc.shc_mode != 'auction'}<input type="hidden" name="fshc[shc_first_edit]" value="1" />{/if}
		</td>
	</tr>
	{/if}
</table>