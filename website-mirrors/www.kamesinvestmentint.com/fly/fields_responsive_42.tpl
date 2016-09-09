{assign var='shcFVal' value=$smarty.post.f}

<div class="submit-cell auction fixed listing">
	<div class="name no-mobile">{$lang.price}</div>
	<div class="field four-field" id="sf_field_shc_start_price">{strip}
		<div class="price_item auction">
			{assign var='shc_lf_name' value='listing_fields+name+shc_start_price'}
		 	<span>{$lang[$shc_lf_name]}</span>
			<input class="numeric" type="text" name="fshc[shc_start_price]" size="8" maxlength="9" {if $smarty.post.fshc.shc_start_price}value="{$smarty.post.fshc.shc_start_price}"{/if} />
		</div>
		<div class="price_item auction">
			{assign var='shc_lf_name' value='listing_fields+name+shc_reserved_price'}
			<span>{$lang[$shc_lf_name]}</span>
			<input class="numeric" type="text" name="fshc[shc_reserved_price]" size="8" maxlength="9" {if $smarty.post.fshc.shc_reserved_price}value="{$smarty.post.fshc.shc_reserved_price}"{/if} />
		</div>
		<div class="price_item auction fixed listing">
			<span>{$lang.shc_buy_now}</span>
			<input class="numeric" type="text" name="f[{$config.shc_listing_field_price}][value]" size="8" maxlength="9" {if $shcFVal[$config.shc_listing_field_price].value}value="{$shcFVal[$config.shc_listing_field_price].value}"{/if} />
		</div>
		<div class="price_item auction fixed listing">
			<span data-caption="{$lang.currency}"></span>
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
	{/strip}</div>
</div>

<div class="submit-cell auction">
	<div class="name">
		{assign var='shc_lf_name' value='listing_fields+name+shc_bid_step'}
		{$lang[$shc_lf_name]}
	</div>
	<div class="field combo-field" id="sf_field_shc_bid_step">
		<input class="numeric wauto" size="8" type="text" name="fshc[shc_bid_step]" maxlength="11" {if $smarty.post.fshc.shc_bid_step}value="{$smarty.post.fshc.shc_bid_step}"{/if} />
		<span id="bid_currency">{$currency.0.name}</span>
	</div>
</div>

<div class="submit-cell auction">
	<div class="name">
		{assign var='shc_lf_name' value='listing_fields+name+shc_days'}
		{$lang[$shc_lf_name]}
	</div>
	<div class="field combo-field" id="sf_field_shc_bid_days">
		<input class="numeric wauto" size="8" type="text" name="fshc[shc_days]" maxlength="11" {if $smarty.post.fshc.shc_days}value="{$smarty.post.fshc.shc_days}"{/if} />
		{$lang.shc_days}
	</div>
</div>

<div class="submit-cell auction fixed">
	<div class="name">
		{assign var='shc_lf_name' value='listing_fields+name+shc_quantity'}
		{$lang[$shc_lf_name]}
	</div>
	<div class="field" id="sf_field_shc_bid_quantity">
		<input class="numeric wauto" size="8" type="text" name="fshc[shc_quantity]" maxlength="11" value="{if $smarty.post.fshc.shc_quantity}{$smarty.post.fshc.shc_quantity}{else}0{/if}" />
	</div>
</div>

<div class="submit-cell auction fixed">
	<div class="name">
		{assign var='shc_lf_name' value='listing_fields+name+shc_weight'}
		{$lang[$shc_lf_name]}
	</div>
	<div class="field combo-field" id="sf_field_shc_bid_weight">
		<input class="numeric wauto" size="8" type="text" name="fshc[shc_weight]" maxlength="11" value="{if $smarty.post.fshc.shc_weight}{$smarty.post.fshc.shc_weight}{else}0{/if}" />
			{assign var='shc_lf_weight' value='shc_weight_unit_'|cat:$config.shc_weight_unit}
			{$lang[$shc_lf_weight]}
		</td>
	</div>
</div>

<div class="submit-cell fixed">
	<div class="name">
		{assign var='shc_lf_name' value='listing_fields+name+shc_available'}
		{$lang[$shc_lf_name]}
	</div>
	<div class="field inline-fields" id="sf_field_shc_bid_available">
		<span class="custom-input"><label><input type="radio" value="1" name="fshc[shc_available]" {if $smarty.post.fshc.shc_available == '1' || $smarty.post.fshc.shc_available == ''}checked="checked"{/if} />{$lang.yes}</label></span>
		<span class="custom-input"><label><input type="radio" value="0" name="fshc[shc_available]" {if $smarty.post.fshc.shc_available == '0'}checked="checked"{/if} />{$lang.no}</label></span>
	</div>
</div>

{if $pageInfo.Controller == 'edit_listing'}
<div class="submit-cell auction">
	<div class="name"></div>
	<div class="field inline-fields" id="sf_field_shc_update_start_time">
		<label><input type="checkbox" value="1" name="fshc[shc_update_start_time]" {if $smarty.post.fshc.shc_update_start_time == '1'}checked="checked"{/if} /> {$lang.shc_update_start_time}</label>
		<input type="hidden" name="fshc[shc_edit]" value="1" />
		{if $smarty.post.fshc.shc_mode != 'auction'}<input type="hidden" name="fshc[shc_first_edit]" value="1" />{/if}
	</div>
</div>
{/if}