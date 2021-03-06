<!-- banners upload manager responsive -->

{if $use_name}
	<div class="content-padding">
		<div class="submit-cell clearfix">
			<div class="name">
				{$lang.file}<span class="red">&nbsp;*</span>
			</div>
			<div class="field single-field" id="sf_field_banner_media_file">
{/if}
	
	<div class="info">{$lang.max_file_size_caption} <b>{$max_file_size} MB</b></div>
	<div id="fileupload">
		<form onsubmit="return false;" action="{$smarty.const.RL_PLUGINS_URL}banners/upload/account.php" method="post" encoding="multipart/form-data" enctype="multipart/form-data">
			<span class="files canvas"></span>
			<span title="{$lang.add_photo}" class="draft fileinput-button">
				<span id="size-notice"><b>{$boxInfo.width}</b> x <b>{$boxInfo.height}</b></span>
				<input type="file" name="files" style="height:{$boxInfo.height}px;" />
				<input type="hidden" name="box_width" value="{$boxInfo.width}" />
				<input type="hidden" name="box_height" value="{$boxInfo.height}" />
			</span>
		</form>
	</div>

{if $use_name}
	</div>
</div>
{/if}


{literal}
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<span {/literal}style="height: {$boxInfo.height}px;"{literal} class="template-upload fade item active">
		<span class="preview"><span class="fade"></span></span><span class="start"></span>
		<img src="{/literal}{$rlTplBase}{literal}img/blank.gif" class="cancel" alt="{/literal}{$lang.delete}{literal}" title="{/literal}{$lang.delete}{literal}" />
		<span class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></span>
	</span>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<span {/literal}style="height: {$boxInfo.height}px;"{literal} class="template-download fade item active">
		<img class="thumbnail" src="{%=file.thumbnail_url%}" />
		<div class="photo_navbar">
			<img data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}" src="{/literal}{$rlTplBase}{literal}img/blank.gif" class="delete" alt="{/literal}{$lang.delete}{literal}" title="{/literal}{$lang.delete}{literal}" />
		</div>
		<img src="{/literal}{$rlTplBase}{literal}img/blank.gif" alt="" class="loaded" />
	</span>
{% } %}
</script>
{/literal}

<style type="text/css">
div#fileupload span.progress
{literal}{{/literal}
	margin: 0;
{literal}}{/literal}

div#fileupload span.hover
{literal}{{/literal}
	width: {$boxInfo.width}px;
	height: {$boxInfo.height}px;
{literal}}{/literal}

div#fileupload span.draft
{literal}{{/literal}
	width: {$boxInfo.width}px;
	height: {$boxInfo.height}px;
	line-height: {$boxInfo.height}px;
	padding: 0;
	margin: 0 10px 5px 0;
	background: #F3F3F3;
{literal}}{/literal}

canvas.new, img.thumbnail
{literal}{{/literal}
	width: {$boxInfo.width}px !important;
	height: {$boxInfo.height}px !important;
{literal}}{/literal}

div#fileupload span.active, div#fileupload span.hover
{literal}{{/literal}
	width: {$boxInfo.width+4}px;
	height: {$boxInfo.height}px;
{literal}}{/literal}

div#fileupload img.loaded
{literal}{{/literal}
	margin: 0 4px 4px;
{literal}}{/literal}
</style>

<script type="text/javascript">
var rlPlugins = '{$smarty.const.RL_PLUGINS_URL}';
var photo_allowed = 1;
var photo_width = {$boxInfo.width};
var photo_height = {$boxInfo.height};
var photo_max_size = {if $max_file_size}{$max_file_size}{else}2{/if}*1024*1024;
var photo_auto_upload = true;
lang['error_maxFileSize'] = "{$lang.error_maxFileSize}";
lang['error_acceptFileTypes'] = "{$lang.error_acceptFileTypes}";
lang['uploading_completed'] = "{$lang.uploading_completed}";
lang['upload'] = "{$lang.upload}";
lang['banners_unsaved_photos_notice'] = '{$lang.banners_unsaved_photos_notice}';

var ph_empty_error = "{$lang.crop_empty_coords}";
var ph_too_small_error = "{$lang.crop_too_small}";

{literal}
var managePhotoDesc = function() {}
var crop_handler = function() {}
var submit_photo_step = function() {
	// check for not uploaded photos
	var not_saved = $('#fileupload span.template-download').length;
	if ( not_saved == 0 ) {
		$('#fileupload span.template-upload').addClass('suspended');
		printMessage('error', lang['banners_unsaved_photos_notice']);
		return false;
	}
	else {
		return true;
	}
};

$(document).ready(function(){
	$('#fileupload').fileupload({
		maxNumberOfFiles: photo_allowed,
		autoUpload: true
	}).removeClass('ui-widget');

	$.getJSON(rlPlugins +'banners/upload/account.php', function(files) {
		$('#fileupload').fileupload('option', 'done').call($('#fileupload'), null, {result: files});
	});
});
{/literal}
</script>

<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}banners/static/tmpl.min.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}banners/static/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}banners/static/jquery.fileupload.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}banners/static/jquery.fileupload-ui.js"></script>

<!-- banners upload manager responsive end -->