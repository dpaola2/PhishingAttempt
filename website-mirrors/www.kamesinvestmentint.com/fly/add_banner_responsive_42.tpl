<!-- add banner -->

<!-- steps -->
{assign var='allow_link' value=true}
{assign var='current_found' value=false}
<ul class="steps">
	{math assign='step_width' equation='round(100/count, 3)' count=$bSteps|@count}
	{foreach from=$bSteps item='step' name='stepsF' key='step_key'}{strip}
		{if $curStep == $step_key || !$curStep}{assign var='allow_link' value=false}{/if}
		<li style="width: {$step_width}%;" class="{if $curStep}{if $curStep == $step_key}current{assign var='current_found' value=true}{elseif !$current_found}past{/if}{elseif $smarty.foreach.stepsF.first}current{/if}">
			<a href="{if $allow_link}{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$bSteps.$step_key.path}.html{else}?page={$pageInfo.Path}&amp;step={$bSteps.$step_key.path}{/if}{else}javascript:void(0){/if}" title="{$step.name}">
				{if $step.caption}<span>{$lang.step}</span> {$smarty.foreach.stepsF.iteration}{else}{$step.name}{/if}
			</a>
		</li>
	{/strip}{/foreach}
</ul>
<!-- steps end -->

{assign var='sPost' value=$smarty.post}

{if $curStep == 'plan'}

<!-- select a plan -->
<h1>{$lang.select_plan}</h1>

<div class="area_plan step_area hide">
	<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$bSteps.$curStep.path}.html{else}?page={$pageInfo.Path}&amp;step={$bSteps.$curStep.path}{/if}">
		<input type="hidden" name="step" value="plan" />

		{include file=$smarty.const.RL_PLUGINS|cat:'banners'|cat:$smarty.const.RL_DS|cat:'banner_plans_responsive_42.tpl'}

		<div class="form-buttons">
			<input type="submit" value="{$lang.next_step}" />
		</div>
	</form>
</div>
<!-- select a plan end -->

{elseif $curStep == 'form'}

<h1>{$lang.fill_out_form}</h1>

<div class="area_form step_area hide">
	<form id="banners-form" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$bSteps.$curStep.path}.html{else}?page={$pageInfo.Path}&amp;step={$bSteps.$curStep.path}{/if}">
	<input type="hidden" name="step" value="form" />
	<input type="hidden" name="fromPost" value="1" />

	<div class="content-padding">
		<!-- fields block -->
		{strip}

		<div class="submit-cell clearfix">
			<div class="name">
				{$lang.name}<span class="red">&nbsp;*</span>
			</div>

			<div class="field single-field" id="sf_field_name">
				{if $languages|@count > 1}
					<div class="ml_tabs">
						<ul>
							{foreach from=$languages item='language' name='langF'}
								<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
							{/foreach}
						</ul>
						<div class="nav left"></div>
						<div class="nav right"></div>
					</div>
					<div class="ml_tabs_content light-inputs">
						{foreach from=$languages item='language' name='langF'}
						{assign var='l_code' value=$language.Code}
						<div lang="{$l_code}" {if !$smarty.foreach.langF.first}class="hide"{/if}>
							<input type="text" name="name[{$l_code}]]" maxlength="255" value="{$sPost.name.$l_code}" />
						</div>
						{/foreach}
					</div>
				{else}
					<input type="text" name="name" maxlength="255" value="{$sPost.name}" />
				{/if}
			</div>
		</div>

		<div class="submit-cell clearfix">
			<div class="name">
				{$lang.banners_bannerBox}<span class="red">&nbsp;*</span>
			</div>

			<div class="field single-field" id="sf_field_banner_box">
				<select name="banner_box">
				{foreach from=$planInfo.boxes item='box' name='fBox'}
					{if $sPost.banner_box == $box.Key}
						{assign var='sBox' value=$box}
					{else}
						{if $smarty.foreach.fBox.first}
							{assign var='sBox' value=$box}
						{/if}
					{/if}
					<option {if $sBox.Key == $box.Key}selected="selected"{/if} value="{$box.Key}" info="{$box.side}:{$box.width}:{$box.height}">{$box.name}</option>
				{/foreach}
				</select>
			</div>
		</div>

		<div class="submit-cell clearfix">
			<div class="name">
				{$lang.banners_bannerType}<span class="red">&nbsp;*</span>
			</div>

			<div class="field single-field" id="sf_field_banner_type">
				<select name="banner_type">
					{foreach from=$planInfo.types item='type'}
						<option {if $sPost.banner_type == $type.Key}selected="selected"{/if} value="{$type.Key}">{$type.name}</option>
					{/foreach}
				</select>
			</div>
		</div>

		<div class="submit-cell clearfix {if $sPost.banner_type && $sPost.banner_type != 'image'}hide{/if}" id="b_link">
			<div class="name">
				{$lang.banners_bannerLink}
			</div>

			<div class="field single-field" id="sf_field_banner_link">
				<input type="text" name="link" value="{$sPost.link}" />
			</div>
		</div>

		<div class="submit-cell clearfix {if $sPost.banner_type != 'html'}hide{/if}" id="btype_html">
			<div class="name">
				{$lang.banners_bannerType_html}<span class="red">&nbsp;*</span>
			</div>

			<div class="field single-field" id="sf_field_banner_link">
				<textarea id="banner_html" name="html" rows="3" cols="">{$sPost.html}</textarea>
			</div>
		</div>

		{/strip}
		<!-- fields block end -->

		<div class="form-buttons">
			<a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$bSteps.plan.path}.html{else}?page={$pageInfo.Path}&amp;step={$bSteps.plan.path}{/if}">
				{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}
			</a>
			<input type="submit" value="{$lang.next_step}" id="form_submit" />
		</div>

		<script type="text/javascript">
		{literal}
			$(document).ready(function() {
				flynax.mlTabs();
			});
		{/literal}
		</script>
	</div>
	</form>
</div>

<script type="text/javascript">
{literal}

	function bannerTypeChange(from, to, step) {
		$('#banners-form div#'+ from).fadeOut('fast', function() {
			$('#banners-form div#'+ to).fadeIn('normal');
		});

		if ( step ) {
			$('#step_media').fadeIn('fast')
		}
		else {
			$('#step_media').fadeOut('fast')
		}
	}

	if ( $('select[name=banner_type]').val() == 'html' ) {
		bannerTypeChange('b_link', 'btype_html', 0);
	}

	$(document).ready(function() {
		$('select[name=banner_type]').change(function() {
			if ( $(this).val() == 'html' ) {
				bannerTypeChange('b_link', 'btype_html', 0);
			}
			else if ( $(this).val() == 'flash' ) {
				$('#banners-form div#btype_html').fadeOut('fast');
				$('#banners-form div#b_link').fadeOut('fast');
			}
			else {
				bannerTypeChange('btype_html', 'b_link', 1);
			}
		});
	});

{/literal}
</script>

{elseif $curStep == 'media'}

<h1>{$lang.banners_addBannerContent}</h1>

<!-- upload -->
<div class="area_media step_area hide content-padding">

	{if $boxInfo.type == 'image'}
		{include file=$smarty.const.RL_PLUGINS|cat:'banners'|cat:$smarty.const.RL_DS|cat:'upload'|cat:$smarty.const.RL_DS|cat:'account_manager_responsive_42.tpl'}
	{/if}

	<form method="post" onsubmit="return submit_photo_step('{$boxInfo.type}');" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$bSteps.$curStep.path}.html{else}?page={$pageInfo.Path}&amp;step={$bSteps.$curStep.path}{/if}" enctype="multipart/form-data">
		<input type="hidden" name="step" value="media" />
		<input type="hidden" name="type" value="{$boxInfo.type}" />

		{if $boxInfo.type == 'flash'}
			<div class="submit-cell clearfix">
				<div class="name">
					{$lang.file}<span class="red">&nbsp;*</span>
				</div>

				<div class="field single-field" id="sf_field_banner_flash_file">
					<input type="file" name="flash_file" id="flash_file" />
				</div>
			</div>
		{/if}

		<div class="form-buttons">
			<a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$prev_step.path}.html{else}?page={$pageInfo.Path}&amp;step={$prev_step.path}{/if}">
				{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}
			</a>
			<input type="submit" value="{$lang.next_step}" id="photo_submit" />
		</div>

		{*
		<table class="submit">
		{if $boxInfo.type == 'flash'}
		<tr>
			<td class="name"><span class="red">*</span> {$lang.file}:</td>
			<td class="field">

				<div id="banner_flash_upload" {if $bannerData.Image && $bannerData.Image != 'html'}class="hide"{/if}>
					<input type="file" name="flash_file" id="flash_file" />
					<table class="grey_small">
					<tr>
						<td>{$lang.max_file_size}:</td>
						<td style="padding-left: 5px;"><em><b>{$max_file_size} MB</b></em></td>
					</tr>
					<tr>
						<td>{$lang.available_file_type}:</td>
						<td style="padding-left: 5px;"><b><em>swf</em></b></td>
					</tr>
					</table>
				</div>

				{if $bannerData.Image && $bannerData.Image != 'html'}
				<div id="fileupload" style="padding:0;padding-bottom:10px;">
					<span class="item active">
						<object width="{$boxInfo.width}" height="{$boxInfo.height}" data="{$smarty.const.RL_FILES_URL}banners/{$bannerData.Image}" type="application/x-shockwave-flash">
							<param value="{$smarty.const.RL_FILES_URL}banners/{$bannerData.Image}" name="movie">
							<param value="opaque" name="transparent">
							<param name="allowscriptaccess" value="samedomain">
							<param value="direct_link=true" name="flashvars">
							<embed width="{$boxInfo.width}" height="{$boxInfo.height}" flashvars="direct_link=true" wmode="transparent" src="{$smarty.const.RL_FILES_URL}banners/{$bannerData.Image}">
						</object>
						<img src="{$rlTplBase}img/blank.gif" class="cancel" alt="{$lang.delete}" title="{$lang.delete}" />
					</span>
				</div>
				{/if}
			</td>
		</tr>
		{/if}

		<tr>
			<td class="name button">
				<a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$prev_step.path}.html{else}?page={$pageInfo.Path}&amp;step={$prev_step.path}{/if}" class="dark_12">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}</a>
			</td>
			<td class="field button">
				<span class="arrow"><input type="submit" value="{$lang.next_step}" id="photo_submit" /><label for="photo_submit" class="right">&nbsp;</label></span>
			</td>
		</tr>
		</table>
		*}
	</form>

	{if $boxInfo.type == 'flash'}
	<script type="text/javascript">
	lang['banners_errorSelectFlashFile'] = '{$lang.banners_errorSelectFlashFile}';
	lang['banners_errorFormatFlashFile'] = '{$lang.banners_errorFormatFlashFile}';
	var flashFile = '{$bannerData.Image}';

	{literal}
		function submit_photo_step(type) {
			if ( type == 'flash' ) {
				var flashFile = $.trim($('input#flash_file').val());
				if ( flashFile.length === 0 ) {
					printMessage('error', lang['banners_errorSelectFlashFile']);
					return false;
				}

				if ( flashFile.length !== 0 && flashFile.split('.').pop() != 'swf' ) {
					printMessage('error', lang['banners_errorFormatFlashFile']);
					return false;
				}
			}
		}

		$(document).ready(function() {
			$('#fileupload img.cancel').click(function() {
				xajax_bannersRemoveFlash(flashFile);
			});
		});
	{/literal}
	</script>
	{/if}
</div>
<!-- upload end -->

{elseif $curStep == 'checkout'}

<h1>{$lang.checkout}</h1>

<!-- checkout -->
<div class="area_checkout step_area hide content-padding">

	{if isset($smarty.get.canceled)}
		<script type="text/javascript">
			printMessage('error', '{$lang.bannersNoticePaymentCanceled}', 0, 1);
		</script>
	{/if}

	<div style="padding-bottom: 5px;">{$lang.checkout_step_info}</div>

	<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$bSteps.$curStep.path}.html{else}?page={$pageInfo.Path}&amp;step={$bSteps.$curStep.path}{/if}">
		<input type="hidden" name="step" value="checkout" />

		<ul id="payment_gateways">
			{if $config.use_paypal}
				<li>
					<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/paypal/paypal.png" />
					<p><input {if $smarty.post.gateway == 'paypal' || !$smarty.post.gateway}checked="checked"{/if} type="radio" name="gateway" value="paypal" /></p>
				</li>
			{/if}

			{if $config.use_2co}
				<li>
					<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/2co/2co.png" />
					<p><input {if $smarty.post.gateway == '2co'}checked="checked"{/if} type="radio" name="gateway" value="2co" /></p>
				</li>
			{/if}

			{rlHook name='paymentGateway'}
		</ul>

		<div class="form-buttons">
			<a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$prev_step.path}.html{else}?page={$pageInfo.Path}&amp;step={$prev_step.path}{/if}">
				{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.perv_step}
			</a>
			<input type="submit" value="{$lang.next_step}" id="checkout_submit" />
		</div>
	</form>

	<script type="text/javascript">
		flynax.paymentGateway();
	</script>
</div>
<!-- checkout end -->

{elseif $curStep == 'done'}

<!-- done -->
<div class="area_done step_area hide">
	<div class="caption">{$lang.reg_done}</div>

	<div class="info">
		{if $config.banners_auto_approval}
			{$lang.banners_noticeAfterBannerAdding}
		{else}
			{$lang.banners_noticeAfterBannerAddingPending}
		{/if}
	</div>
	<span class="dark">
		{assign var='replace' value='<a href="'|cat:$returnLink|cat:'">$1</a>'}
		{$lang.banners_addOneMoreBanner|regex_replace:'/\[(.*)\]/':$replace}
	</span>
</div>
<!-- done end -->

{/if}

<script type="text/javascript">
{if $curStep}
	flynax.switchStep('{$curStep}');
	flynax.mlTabs();
{/if}
</script>

<!-- add banner end -->