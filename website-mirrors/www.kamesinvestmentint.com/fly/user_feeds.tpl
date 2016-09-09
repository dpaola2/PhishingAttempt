<!-- xml feeds tpl -->

{if $formats}
	<div class="ralign"><a href="javascript:void(0)" id="add_feed_link">{$lang.xf_submit_feed}</a></div>

	<div id="add_feed_cont" {if $feeds}class="hide"{/if}>
		<div class="highlight" style="margin-bottom:10px"> 		
	 		<form method="post" action="{$rlBase}{$pages.user_feeds}.html">
	 			<input type="hidden" name="submit" value="1" />
		 		<table class="table">
		 			<tr>
			 			<td class="name">{$lang.xf_feed_name}</td>
			 			<td class="value">
							<input class="w240" type="text" name="feed_name" {if $smarty.post.feed_name}value="{$smarty.post.feed_name}"{/if} />
						</td>
					</tr>
		 			<tr>
			 			<td class="name">{$lang.xf_feed_url}</td>
			 			<td class="value">
							<input class="w350" type="url" name="feed_url" {if $smarty.post.feed_url}value="{$smarty.post.feed_url}"{/if} />
						</td>
					</tr>
					<tr>
						<td class="name">{$lang.xf_format}</td>
						<td class="value">
							<select name="xml_format" class="w240">
								<option value="0">{$lang.select}</option>
								{foreach from=$formats item="format"}
									<option {if $smarty.post.xml_format == $format.Key}selected="selected"{/if} value="{$format.Key}">{$format.name}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<input style="vertical-align:middle" type="submit" value="{$lang.add}" />
						</td>
						<td></td>
					</tr>
				</table>
			</form>	
		</div>
	</div>

	<script type="text/javascript">	
		{literal}
		$(document).ready(function(){
			$('#add_feed_link').click(function(){
				if( $('#add_feed_cont').is(':visible') )
				{
					$('#add_feed_cont').slideUp();
				}
				else
				{
					$('#add_feed_cont').slideDown();
				}
			});
		});
		{/literal}
	</script>

	<div class="highlight">
		{if $feeds}
			<table class="list" id="user_feeds">
			<tr class="header">				
				<td align="center" class="no_padding" style="width: 15px;">#</td>
				<td class="divider"></td>
				<td style="width:40px">{$lang.name}</td>
				<td class="divider"></td>
				<td>{$lang.xf_feed_url}</td>
				<td class="divider"></td>
				<td style="width: 30px;">{$lang.last_check}</td>
				<td class="divider"></td>
				<td style="width: 70px;">{$lang.status}</td>
				<td class="divider"></td>
				<td style="width: 65px;">{$lang.actions}</td>
			</tr>
			{foreach from=$feeds item='feed' name='searchF'}
				{assign var='status_key' value=$feed.Status}
				<tr class="body" id="item_{$feed.ID}">
					<td class="no_padding" align="center"><span class="text">{$smarty.foreach.searchF.iteration}</span></td>
					<td class="divider"></td>
					<td>
						{$feed.name}
					</td>
					<td class="divider"></td>
					<td style="overflow:hidden">
						<a target="_blank" href="{$feed.Url}">{$feed.Url}</a>
					</td>
					<td class="divider"></td>
					<td><span class="text">{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span></td>
					<td class="divider"></td>
					<td id="status_{$item.ID}"><span class="{$status_key}">{$lang.$status_key}</span></td>
					<td class="divider"></td>
					<td>
						{*<img class="search" id="search_{$feed.ID}" alt="{$lang.check_search}" title="{$lang.check_search}" src="{$rlTplBase}img/blank.gif" />*}
						<img class="del" id="delete_{$feed.ID}" alt="{$lang.delete}" title="{$lang.delete}" src="{$rlTplBase}img/blank.gif" />
					</td>
				</tr>
			{/foreach}
			</table>
			
			<script type="text/javascript">
			{literal}
			
			$(document).ready(function(){			
				$('img.del').each(function(){					
					$(this).flModal({						
						caption: '{/literal}{$lang.warning}{literal}',
						content: '{/literal}{$lang.xf_notice_remove_feed}{literal}',
						prompt: 'xajax_deleteXmlFeed('+ $(this).attr('id').split('_')[1] +')',
						width: 'auto',
						height: 'auto'
					});
				});
			});
			
			{/literal}
			</script>
		{/if}
	</div>
{/if}

<!-- xml feeds tpl end -->