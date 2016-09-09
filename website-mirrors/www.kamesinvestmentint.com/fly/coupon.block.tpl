{if $pageInfo.Key != 'my_credits'}
	
	<div id="coupon_box" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='coupon' name=$lang.coupon_code}
			<div id="coupon_code"><input class="text w150" id="coupon_code_name" name="coupon_code" value="{$smarty.post.coupon_code}" type="text" maxlength="20" size="20" onkeydown="javascript:if(13==event.keyCode){literal}{{/literal}return false;{literal}}{/literal}" /> <input class="low" id="check_coupon" type="button" style="margin: 0 5px;" value="{$lang.apply}"></div>
			<div id="coupon_code_info"></div>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}		
	</div>
			
	<script type="text/javascript">
	var plan_id = '{if $plan_info.ID}{$plan_info.ID}{elseif $bannerData.Plan_ID}{$bannerData.Plan_ID}{elseif $invoice_info.ID}{$invoice_info.ID}{/if}';
	var renew = '{if $smarty.get.renew}{$smarty.get.renew}{elseif $smarty.get.id && $pageInfo.Key=="banners_renew" }{$smarty.get.id}{/if}';
	var type = '{if $pageInfo.Controller =="add_listing" || $pageInfo.Controller =="upgrade_listing" || $pageInfo.Controller =="my_packages"}listing{elseif $pageInfo.Key=="add_banner" || $pageInfo.Key=="banners_renew"}banner{elseif $pageInfo.Key=="invoices"}invoice{/if}';
	{literal}	
		$(document).ready(function() {
			$('ul#payment_gateways').after($("#coupon_box").html());
			$('#coupon_box').remove();
			$('#check_coupon').click(function() {
				plan_id = $("input[name='plan']:checked").val() ? $("input[name='plan']:checked").val() : plan_id ;
				xajax_checkCouponCode($('#coupon_code_name').val(), plan_id, '', renew, type);$(this).val('Loading...');$('#coupon_code_info').hide();
			});
			$('.plans li:not(.active)').click(function() {		
				xajax_checkCouponCode('', '', 'remove', renew, type);$(this).val('Loading...');
			});
			
			$('#coupon_code_name').keydown(function(event){
				if(event.keyCode == 13)
				{
					xajax_checkCouponCode($('#coupon_code_name').val(), plan_id, '', renew, type);$('#check_coupon').val('Loading...');$('#coupon_code_info').hide();
				}
			});
			$('#checkout_submit').click(function(){
				$(this).closest('form').submit();
			});
		});
		function diffuse()
		{
			xajax_checkCouponCode('', '', 'remove', renew, type);$(this).val('Loading...');
		}
	{/literal}
	</script>
{/if}