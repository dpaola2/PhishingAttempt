<!-- cart container tpl -->

{assign var='shc_total' value='0.00'}
{foreach from=$shcItems item='item'}
	{math equation="total + (price * quantity)" total=$shc_total quantity=$item.Quantity price=$item.Price assign='shc_total'}	
{/foreach}

<div class="cart-box-container{if !$shcItems|@count} empty{/if}">
	<span class="button">
		<span class="count">{$shcItems|@count}</span>
		<span class="summary">
			{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
			{$shc_total|number_format:2:'.':','}
			{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
		</span>
	</span>

	<ul id="shopping_cart_block" class="cart-items">
		{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'cart_items_responsive_42.tpl' shcItems=$shcItems}
	</ul>
</div>

<script>
{literal}

$(document).ready(function(){
	$('div.cart-box-container > span.button').click(function(){
		$(this).parent().toggleClass('active');
	});

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
});

$(document).bind('click touchstart', function(event){
	if ( !$(event.target).parents().hasClass('cart-box-container') ) {
		$('.cart-box-container').removeClass('active');
	}
});

{/literal}
</script>

<!-- cart container tpl end -->