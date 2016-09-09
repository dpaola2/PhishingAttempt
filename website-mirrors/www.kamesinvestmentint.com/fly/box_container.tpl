<!-- shoppingCart plugin -->

{assign var='shc_total' value='0.00'}
{foreach from=$shcItems item='item'}
	{math equation="total + (price * quantity)" total=$shc_total quantity=$item.Quantity price=$item.Price assign='shc_total'}	
{/foreach}

<div id="shc-my-cart" class="shc_my_cart hide">
	<div class="inner">
		<a title="{$lang.shc_my_cart}" class="shc-my-cart" href="javascript:void(0);">{$shcItems|@count}&nbsp;{$lang.shc_count_items} / {$shc_total|number_format} {$config.system_currency}<span {if $config.template == 'general_sky'}class="arrow"{/if}></span></a>
		{if $config.template != 'general_sky'}
			<img src="{$rlTplBase}img/blank.gif" class="arrow" alt="" />
		{/if}
		{if $config.template == 'general_modern' || $template_default} 
			<div id="shopping_cart_block" class="shopping_cart_layer hide">
				{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'my_cart_block.tpl' shcItems=$shcItems}
			</div>
		{/if}
	</div>
	{if $config.template != 'general_modern' && !$template_default} 
		<div id="shopping_cart_block" class="shopping_cart_layer hide">
			{include file=$smarty.const.RL_PLUGINS|cat:'shoppingCart'|cat:$smarty.const.RL_DS|cat:'my_cart_block.tpl' shcItems=$shcItems}
		</div>
	{/if}
</div>

<script type="text/javascript">
	var shc_template = '{$config.template}';

	{literal} 
	$(document).ready(function()
	{
		if(shc_template == 'general_sky')
		{
			$('#user_navbar>div.bar>div>div.form').after($('#shc-my-cart')); 
		}
		else if(shc_template == 'general_modern')
		{
			$('#user_navbar>div.languages').after($('#shc-my-cart')); 
		}
		else
		{
			$('.hookUserNavbar').after($('#shc-my-cart'));
		}

		$('#shc-my-cart').removeClass('hide');
		
    	$('#shc-my-cart .inner').click(function()
		{
			if ( $('#shopping_cart_block').hasClass( 'hide' ) )
			{
				$('#shopping_cart_block').removeClass('hide');
				$(this).addClass('shc-active');
				$(this).find('.arrow').addClass('active');
			}
			else
			{
				$('#shopping_cart_block').addClass('hide');
				$(this).removeClass('shc-active');
				$(this).find('.arrow').removeClass('active');
			}
		});

		$('.clear-cart').live('click', function()
		{
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
	
	$(document).click(function(event){
		var close = true;
		
		$(event.target).parents().each(function(){
			if ( $(this).hasClass('shc_my_cart') )
			{
				close = false;
			}
		});
		
		if ( close )
		{                                    
			$('#shopping_cart_block').addClass('hide'); 
			$('#shc-my-cart div.inner').removeClass('shc-active');
			$('#shc-my-cart div.inner').find('.arrow').removeClass('active');
		}
	});
	{/literal} 
</script>

<!-- end shoppingCart plugin -->