<!-- my cart page / items list -->

<div class="list-table cart-items-table">
	<div class="header">
		<div class="center" style="width: 40px;">#</div>
		<div>{$lang.shc_item}</div>
		<div style="width: 90px;">{$lang.shc_price}</div>
		<div style="width: 100px;">{$lang.shc_quantity}</div>
		<div style="width: 100px;">{$lang.shc_total}</div>
		{if !$preview}<div style="width: 40px;"></div>{/if}
	</div>

	{foreach from=$shcItems item='item' name='itemsF'}
		<div class="row">
			<div class="center iteration no-flex">{$smarty.foreach.itemsF.iteration}</div>
			<div data-caption="{$lang.shc_item}">
				{if $item.Main_photo}
					<a href="{$item.listing_link}" target="_blank"><img alt="{$item.title}" style="width: 70px;margin-{$text_dir_rev}: 10px;" src="{$smarty.const.RL_FILES_URL}{$item.Main_photo}" /></a>
				{/if}
				<div class="inline"><a href="{$item.listing_link}" target="_blank">{$item.Item}</a></div>	
			</div>
			<div data-caption="{$lang.shc_price}" class="nr">
				{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
				{$item.Price|number_format:2:'.':','}
				{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
			</div>
			<div data-caption="{$lang.shc_quantity}">
				{if $preview}
					{$item.Quantity}
				{else}
					<span class="nav increase" title="{$lang.shc_increase}">+</span>
					<input accesskey="{$item.Price}" type="text" class="numeric quantity" name="quantity[{$item.ID}]" id="quantity_{$item.ID}" value="{$item.Quantity}" style="text-align: center;" maxlength="3" />
					<span class="nav decrease" title="{$lang.shc_decrease}">-</span>
				{/if}
			</div>
			<div data-caption="{$lang.shc_total}" class="nr">
				{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
				<span id="price_{$item.ID}">{$item.total|number_format:2:'.':','}</span>
				{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
			</div>
			{if !$preview}
				<div class="action no-flex">
					<span title="{$lang.delete}" class="close-red shc_delete_item" id="delete_{$item.ID}_{$item.Item_ID}"></span>
				</div>
			{/if}
		</div>
	{/foreach}
</div>

{if !$preview}
	<input type="hidden" name="form" value="submit" />
	<input type="hidden" name="dealer" value="{$item.Dealer_ID}" />

	<div class="ralign">
		<!-- total -->
		<div class="shc_value" style="padding: 20px 0;">
			{$lang.shc_total_cost}
			<span class="value shc_price">
				{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
				<span id="total_{$shcDealer}">{$shcTotal|number_format:2:'.':','}</span>
				{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
			</span>
		</div>
		<!-- total end -->

		<input type="submit" value="{$lang.next_step}" />
	</div>
{/if}

<!-- my cart page / items list end -->