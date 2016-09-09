<!-- cart container tpl -->

{assign var='shc_total' value='0.00'}
{foreach from=$shcItems item='item'}
	{math equation="total + (price * quantity)" total=$shc_total quantity=$item.Quantity price=$item.Price assign='shc_total'}	
{/foreach}

<div class="cart-box-container-static{if !$shcItems|@count} empty{/if}">
	<ul id="shopping_cart_block" class="cart-items-box">
		{if !empty($shcItems)}
			{foreach from=$shcItems item='item' name='shcItemsF'}
				<li class="two-inline left clearfix">
					{if $item.Main_photo}
						<div class="item-picture">
							<a href="{$item.listing_link}" target="_blank"><img alt="{$item.Item}" src="{$rlTplBase}img/blank_10x7.gif" style="background-image: url('{$smarty.const.RL_FILES_URL}{$item.Main_photo}');" /></a>
						</div>
					{/if}
					<div class="info">
						<a href="{$item.listing_link}" target="_blank">{$item.Item}</a>
						<div>
							{$item.Quantity} x 
							{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
							{$item.Price}
							{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
						</div>

						<div title="{$lang.remove}" class="close-red" onclick="xajax_deleteItem({$item.ID}, {$item.Item_ID});"></div>
					</div>
				</li>
			{/foreach}
			
			<li class="two-inline clearfix controls">
				<div><a class="button cart" href="{$rlBase}{if $config.mod_rewrite}{$pages.shc_my_shopping_cart}.html{else}?page={$pages.my_shopping_cart}{/if}">{$lang.shc_checkout}</a></div>
				<div><a href="javascript: void(0);" class="clear-cart">{$lang.shc_clear_cart}</a></div>
			</li>
		{else}
			<li class="text-notice">{$lang.shc_empty_cart}</li>
		{/if}
	</ul>
</div>

<script>
{literal}

$(document).ready(function(){
	$('.clear-cart').live('click', function(){
		$('.clear-cart').flModal({
			caption: '',
			content: '{/literal}{$lang.shc_do_you_want_clear_cart}{literal}',
			prompt: 'xajax_clearShoppingCart',
			width: 'auto',
			height: 'auto',
			click: false
		});
	});

	var box_name = $('.cart-box-container-static').parent().parent('section').find('h3').html();
	box_name = box_name + ' ({/literal}{$shcItems|@count}&nbsp;{$lang.shc_count_items} / {if $config.system_currency_position == 'before'}{$config.system_currency}{/if}{$shc_total|number_format}{if $config.system_currency_position == 'after'} {$config.system_currency}{/if}{literal})';
	$('.cart-box-container-static').parent().parent('section').find('h3').html(box_name);

	$('.cart-box-container-static').parent().parent('section').find('h3').addClass('shc-box-name');
});

{/literal}
</script>

<!-- cart container tpl end -->