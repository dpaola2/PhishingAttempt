<!-- booking calendar -->

<div class="info">{$lang.booking_start_booking}</div>

<div class="two-column clearfix" id="calendar_map">
	{foreach from=$BookingDays item='month' name='fMonths'}
		<div><div>
			<div class="horizontal">
				<div {if $smarty.foreach.fMonths.first}onclick="cangeDates('-M');" id="prevRange" class="prev hide" title="{$lang.booking_prev} {$lang.booking_month}" {else}onclick="cangeDates('+M');" class="next" title="{$lang.booking_next} {$lang.booking_month}"{/if}></div>
			</div>

			<div class="month-name"><b>{$month.Name} {$month.Year}</b></div>
			<table class="month-table" {if $month.Days.01.Color == 'R'} style="opacity:0.4;"{/if}>
			<tr class="dayName">
				<td><div class="font1">{$lang.booking_monday|truncate:1:false}</div></td>
				<td><div class="font1">{$lang.booking_tuesday|truncate:1:false}</div></td>
				<td><div class="font1">{$lang.booking_wednesday|truncate:1:false}</div></td>
				<td><div class="font1">{$lang.booking_thursday|truncate:1:false}</div></td>
				<td><div class="font1">{$lang.booking_friday|truncate:1:false}</div></td>
				<td><div class="font1">{$lang.booking_saturday|truncate:1:false}</div></td>
				<td><div class="font1">{$lang.booking_sunday|truncate:1:false}</div></td>
			</tr>
			<tr>
				{foreach from=$month.Days item='day' name='fDays' key='kDay'}
				<td class="calendar_td date">
					{if $day != 'missed'}
						{if $day.Color == 'U'}
							{assign var='book_color' value='unavailable'}
						{elseif $day.Color == 'R'}
							{assign var='book_color' value='restriction'}
						{elseif $day.Color == 'T'}
							{assign var='book_color' value='today'}
						{elseif $day.Color == 'A'}
							{assign var='book_color' value='available'}
						{/if}
						<div class="{$book_color}" {if $day.Color == 'R' &&  $month.Days.01.Color == 'R'}style="opacity:1;"{/if}
						{if $day.Color != 'U' && $day.Color != 'R'}
							title="{$lang.booking_start_booking_title}" id="day_{$day.mktime}" onclick="xSelect('{$day.mktime}');"
						{/if}>
						{if $kDay<10}{$kDay|substr:1:1}{else}{$kDay}{/if}
						</div>
					{/if}
				</td>
				{if $smarty.foreach.fDays.iteration%7 == 0 && !$smarty.foreach.fDays.last}
				</tr><tr>
				{/if}
				{/foreach}
			</tr>
			</table>
		</div></div>
	{/foreach}
</div>

<!-- booking calendar end -->