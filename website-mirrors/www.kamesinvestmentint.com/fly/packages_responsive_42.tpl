<ul id="credits">
	{foreach from=$credits item='item' key='key' name='creditsF'}
		<li class="credit_item" id="credit_item_{$item.ID}">
			<div class="dark number">{$item.Credits}</div>
			<div class="credits">{$lang.paygc_credits_count}</div>
			<div class="dark price">
				{if $config.system_currency_position == 'before'}
					{$config.system_currency}
				{/if}
				{$item.Price}
				{if $config.system_currency_position == 'after'}
					{$config.system_currency}
				{/if}
			</div>
			<div class="price_one dark_12">({$config.system_currency}{$item.Price_one}/{$lang.paygc_credits_count|replace:'s':''} )</div>
			<input type="radio" id="credit_item_value_{$item.ID}" accesskey="price_{$item.Price}" name="credits" value="{$item.ID}" />								
		</li>
	{/foreach}
</ul>

<script type="text/javascript">
{literal}
	$(document).ready(function() {
		$('ul#credits li.credit_item').click(function() {
			var item_id = $(this).attr('id').split('_')[2];
			$('#credit_item_value_' + item_id).attr('checked', true);

			$('ul#credits li.credit_item').each(function() {
				var item_id_tmp = $(this).attr('id').split('_')[2];

				if(item_id == item_id_tmp) {
					$($(this)).addClass('active');
				}
				else {
					$($(this)).removeClass('active');
				}
			});
		})
	});
{/literal}
</script>