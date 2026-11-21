<?php
// Version: 2.1; ForumIntelligenceDashboard

/**
 * Forum Intelligence Dashboard templates.
 *
 * All strings come from ForumIntelligenceDashboard.<language>.php. escaping is
 * done in the data layer; templates only echo prepared, safe values.
 *
 * @package ForumIntelligenceDashboard
 * @copyright Copyright (c) 2026 rumpa
 * @license See LICENSE.txt
 */

/**
 * GET form with the period selector (quick ranges plus a custom range).
 *
 * @param string $sa Current subaction.
 */
function fid_period_selector($sa)
{
	global $context, $txt, $scripturl;

	echo '
	<form class="fid-period-form" method="get" action="', $scripturl, '?action=admin;area=fid;sa=', $sa, '">
		<input type="hidden" name="action" value="admin">
		<input type="hidden" name="area" value="fid">
		<input type="hidden" name="sa" value="', $sa, '">
		<label class="fid-inline">', $txt['fid_filter_period'], '
			<select name="period">';
	foreach ($context['fid_period_options'] as $days => $label_key)
		echo '
				<option value="', $days, '"', (string) $context['fid_period']['label'] === (string) $days ? ' selected' : '', '>', $txt[$label_key], '</option>';
	echo '
			</select>
		</label>
		<span class="fid-custom-range">
			<label>', $txt['fid_filter_from'], ' <input type="date" name="fid_from" value="', $context['fid_period']['is_custom'] ? gmdate('Y-m-d', $context['fid_period']['start']) : '', '" placeholder="YYYY-MM-DD"></label>
			<label>', $txt['fid_filter_to'], ' <input type="date" name="fid_to" value="', $context['fid_period']['is_custom'] ? gmdate('Y-m-d', $context['fid_period']['end']) : '', '" placeholder="YYYY-MM-DD"></label>
		</span>
		<button type="submit" class="button fid-submit">', $txt['fid_filter_apply'], '</button>
	</form>';
}

/**
 * Admin alert summary bar, reused by several sections.
 */
function fid_alerts_bar()
{
	global $context, $txt, $scripturl;

	$unseen = (int) $context['fid_alerts_unseen'];
	$last_run = (int) $context['fid_alerts_last_run'];

	echo '
	<div class="fid-alertbar', $unseen > 0 ? ' fid-alertbar-active' : '', '">
		<span class="fid-alertbar-count">', $unseen > 0 ? sprintf($txt['fid_alerts_unseen'], $unseen) : $txt['fid_alerts_none'], '</span>
		<span class="fid-alertbar-run">', $last_run > 0 ? sprintf($txt['fid_alerts_last_run'], htmlspecialchars(timeformat($last_run), ENT_QUOTES), (int) fid_setting('fid_alert_interval')) : $txt['fid_alerts_never_run'], '</span>
		<a href="', $scripturl, '?action=admin;area=fid;sa=alerts" class="fid-alertbar-link">', $txt['fid_go_alerts'], '</a>
	</div>';
}

/**
 * Small SVG chart (bar or line) rendered from real rows, no external
 * libraries. Series values are numbers; labels are date strings.
 *
 * @param array $chart As built by the growth controller.
 * @param string $id Unique DOM id.
 */
function fid_svg_chart($chart, $id)
{
	global $txt;

	if (empty($chart['enabled']) || empty($chart['labels']) || empty($chart['series']))
	{
		echo '
	<p class="fid-empty">', $txt['fid_chart_no_data'], '</p>';

		return;
	}

	$labels = $chart['labels'];
	$series = $chart['series'];
	$width = 720;
	$height = 240;
	$padding = array('top' => 10, 'right' => 10, 'bottom' => 30, 'left' => 46);
	$inner_w = $width - $padding['left'] - $padding['right'];
	$inner_h = $height - $padding['top'] - $padding['bottom'];

	$max = 0;
	foreach ($series as $single)
		foreach ($single['values'] as $value)
			$max = max($max, (float) $value);
	$max = $max <= 0 ? 1 : $max;

	$count = count($labels);
	$step = $count > 1 ? $inner_w / ($count - 1) : 0;

	$palette = array('#2f6fb2', '#3e8f4e', '#c08c2e', '#8f5bb5', '#b23e5b');

	echo '
	<figure class="fid-chart" id="', $id, '">
		<svg viewBox="0 0 720 240" role="img" preserveAspectRatio="xMidYMid meet" class="fid-chart-svg fid-chart-', $chart['style'], '">';
	foreach (array(0, 0.25, 0.5, 0.75, 1) as $lvl)
	{
		$y = $padding['top'] + $inner_h * (1 - $lvl);
		echo '
			<line x1="', $padding['left'], '" y1="', round($y, 1), '" x2="', $width - $padding['right'], '" y2="', round($y, 1), '" class="fid-grid"></line>
			<text x="', $padding['left'] - 6, '" y="', round($y + 4, 1), '" text-anchor="end" class="fid-axis-label">', (int) round($max * $lvl), '</text>';
	}

	if ($chart['style'] === 'bar')
	{
		$group_w = $inner_w / $count;
		$bar_w = $group_w > 0 ? min(28, $group_w / count($series) - 2) : 4;
		foreach ($series as $s_index => $single)
		{
			$color = $palette[$s_index % count($palette)];
			foreach ($single['values'] as $i => $value)
			{
				$v = (float) $value;
				$h = ($v / $max) * $inner_h;
				$x = $padding['left'] + $i * $step + $s_index * ($bar_w + 2);
				if ($group_w > 2 * count($series) * ($bar_w + 2) && $count > 1)
					$x = $padding['left'] + $i * $group_w + $s_index * (($group_w - count($series) * $bar_w) / 2 + $s_index * $bar_w);
				$y = $padding['top'] + $inner_h - $h;
				echo '
			<rect x="', round((float) $x, 1), '" y="', round($y, 1), '" width="', round($bar_w, 1), '" height="', round($h, 1), '" fill="', $color, '" class="fid-bar"><title>', htmlspecialchars($labels[$i]), ': ', (int) $v, '</title></rect>';
			}
		}
	}
	else
	{
		foreach ($series as $s_index => $single)
		{
			$color = $palette[$s_index % count($palette)];
			$points = array();
			foreach ($single['values'] as $i => $value)
			{
				$h = ((float) $value / $max) * $inner_h;
				$x = $padding['left'] + $i * $step;
				$y = $padding['top'] + $inner_h - $h;
				$points[] = round($x, 1) . ',' . round($y, 1);
			}
			echo '
			<polyline points="', implode(' ', $points), '" fill="none" stroke="', $color, '" stroke-width="2" class="fid-line"></polyline>';
			foreach ($single['values'] as $i => $value)
			{
				$h = ((float) $value / $max) * $inner_h;
				$x = $padding['left'] + $i * $step;
				$y = $padding['top'] + $inner_h - $h;
				echo '
			<circle cx="', round($x, 1), '" cy="', round($y, 1), '" r="2.5" fill="', $color, '" class="fid-dot"><title>', htmlspecialchars($labels[$i]), ': ', (int) $value, '</title></circle>';
			}
		}
	}

	$first_label = htmlspecialchars($labels[0]);
	$last_label = htmlspecialchars($labels[$count - 1]);
	echo '
			<text x="', $padding['left'], '" y="', $height - 8, '" class="fid-axis-label fid-axis-edge">', $first_label, '</text>
			<text x="', $width - $padding['right'], '" y="', $height - 8, '" text-anchor="end" class="fid-axis-label fid-axis-edge">', $last_label, '</text>
		</svg>
		<figcaption class="fid-chart-legend">';
	foreach ($series as $s_index => $single)
		echo '
			<span class="fid-legend-item"><span class="fid-legend-color" style="background:', $palette[$s_index % count($palette)], '"></span>', $single['label'], '</span>';
	echo '
		</figcaption>
	</figure>';
}

/**
 * Overview subaction template: period selector, stat cards, short-period
 * comparison table and the alert summary bar.
 */
function template_fid_overview()
{
	global $context, $txt, $scripturl;

	$stats = $context['fid_overview'];
	$slices = $context['fid_slices'];
	$changes = $context['fid_changes'];

	fid_period_selector('overview');
	fid_alerts_bar();

	echo '
	<div class="fid-cards" id="fid_overview_cards">';

	$cards = array(
		array($txt['fid_stat_total_members'], $stats['total_members'], $txt['fid_stat_total_members_desc'], null, false, ''),
		array($txt['fid_stat_active_members'], $stats['active_login'], $txt['fid_stat_active_members_desc'], null, true, 'members'),
		array($txt['fid_stat_inactive_members'], max(0, $stats['total_members'] - $stats['active_login']), $txt['fid_stat_inactive_members_desc'], null, true, 'inactive'),
		array($txt['fid_stat_new_members_period'], $stats['reg_period'], $txt['fid_stat_new_members_period_desc'], $changes['registers'], false, ''),
		array($txt['fid_stat_total_posts'], $stats['total_posts'], $txt['fid_stat_total_posts_desc'], $changes['posts'], false, ''),
		array($txt['fid_stat_total_topics'], $stats['total_topics'], $txt['fid_stat_total_topics_desc'], $changes['topics'], false, ''),
		array($txt['fid_stat_period_posts'], $stats['posts'], $txt['fid_stat_period_posts_desc'], $changes['posts'], false, ''),
		array($txt['fid_stat_period_topics'], $stats['topics'], $txt['fid_stat_period_topics_desc'], $changes['topics'], false, ''),
		array($txt['fid_stat_active_boards'], $stats['active_boards'], sprintf($txt['fid_stat_active_boards_desc'], $stats['all_boards']), null, false, 'boards'),
	);

	foreach ($cards as $card)
	{
		list ($title, $value, $description, $change, $link, $target) = $card;

		echo '
		<div class="fid-card">
			<h3 class="fid-card-title">', $title, '</h3>
			<p class="fid-card-value">', $value, '</p>
			<p class="fid-card-desc">', $description, '</p>';
		if ($change !== null)
			echo '
			<p class="fid-card-trend">', fid_growth_html($change), '</p>';
		if ($link)
			echo '
			<p class="fid-card-link"><a href="', $scripturl, '?action=admin;area=fid;sa=', $target, '">', $txt['fid_view_details'], '</a></p>';
		echo '
		</div>';
	}

	echo '
	</div>
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_activity_rate_title'], '</h3>
		<p class="fid-activity-rate">', sprintf($txt['fid_activity_rate_value'], $context['fid_activity_rate']), '</p>
		<p class="fid-muted">', sprintf($txt['fid_activity_rate_formula'], $context['fid_activity_rate_air'] ? $stats['active_members'] : 0, max($stats['total_members'], 0)), '</p>
	</div>
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_overview_slices_title'], '</h3>
		<table class="fid-table">
			<thead>
				<tr>
					<th scope="col">', $txt['fid_metric_period'], '</th>
					<th scope="col">', $txt['fid_metric_registrations'], '</th>
					<th scope="col">', $txt['fid_metric_posts'], '</th>
					<th scope="col">', $txt['fid_metric_topics'], '</th>
					<th scope="col">', $txt['fid_metric_online'], '</th>
				</tr>
			</thead>
			<tbody>';
	$period_rows = array(
		'today' => $txt['fid_slice_today'], 'yesterday' => $txt['fid_slice_yesterday'],
		'week' => $txt['fid_slice_week'], 'prev_week' => $txt['fid_slice_prev_week'],
		'month' => $txt['fid_slice_month'], 'prev_month' => $txt['fid_slice_prev_month'],
	);
	foreach ($period_rows as $key => $label)
	{
		echo '
				<tr>
					<td>', $label, '</td>
					<td>', $slices[$key]['registers'], '</td>
					<td>', $slices[$key]['posts'], '</td>
					<td>', $slices[$key]['topics'], '</td>
					<td>', (int) $slices[$key]['most_on'], '</td>
				</tr>';
	}
	echo '
			</tbody>
		</table>
	</div>
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_go_compare'], '</h3>
		<p><a class="button" href="', $scripturl, '?action=admin;area=fid;sa=compare">', $txt['fid_go_compare'], '</a></p>
	</div>';
}

/**
 * Member intelligence template: top members table and the member list.
 */
function template_fid_members()
{
	global $context, $txt, $scripturl;

	fid_period_selector('members');
	fid_alerts_bar();

	echo '
	<div class="fid-panel" id="fid_top_members">
		<h3 class="fid-panel-title">', $txt['fid_top_title'], '</h3>
		<div class="fid-filters">
			<span>', $txt['fid_top_select'], '</span>';
	foreach (array(10, 25, 50) as $n)
		echo '
			<a class="fid-pill', $context['fid_top_n'] === $n ? ' fid-pill-active' : '', '" href="', $scripturl, '?action=admin;area=fid;sa=members;top=', $n, '">', $n, '</a>';
	echo '
		</div>';

	if (empty($context['fid_top_members']))
		echo '
		<p class="fid-empty">', $txt['fid_no_top_members'], '</p>';
	else
	{
		echo '
		<table class="fid-table">
			<thead>
				<tr>
					<th scope="col">#</th>
					<th scope="col">', $txt['fid_col_member_name'], '</th>
					<th scope="col">', $txt['fid_col_total_posts'], '</th>
					<th scope="col">', $txt['fid_col_period_posts'], '</th>
					<th scope="col">', $txt['fid_col_period_topics'], '</th>
					<th scope="col">', $txt['fid_col_registered'], '</th>
					<th scope="col">', $txt['fid_col_last_login'], '</th>
					<th scope="col">', $txt['fid_col_last_post'], '</th>
					<th scope="col">', $txt['fid_col_activity_score'], '</th>
				</tr>
			</thead>
			<tbody>';
		foreach ($context['fid_top_members'] as $member)
		{
			echo '
				<tr>
					<td>', $member['rank'], '</td>
					<td><a href="', $member['href'], '">', $member['name_html'], '</a></td>
					<td>', $member['posts'], '</td>
					<td>', $member['window_posts'], '</td>
					<td>', $member['topics'], '</td>
					<td>', fid_time_html($member['date_registered']), '</td>
					<td>', fid_time_html($member['last_login']), '</td>
					<td>', fid_time_html($member['last_post']), '</td>
					<td>', $member['score'], '</td>
				</tr>';
		}
		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_member_list_title'], '</h3>
		<form class="fid-period-form" method="get" action="', $scripturl, '?action=admin;area=fid;sa=members">
			<input type="hidden" name="action" value="admin">
			<input type="hidden" name="area" value="fid">
			<input type="hidden" name="sa" value="members">
			<label class="fid-inline">', $txt['fid_filter_search'], '
				<input type="search" name="fid_search" value="', htmlspecialchars((string) $context['fid_search'], ENT_QUOTES), '" placeholder="', $txt['fid_filter_search_placeholder'], '">
			</label>
			<label class="fid-inline">', $txt['fid_filter_activity'], '
				<select name="fid_activity">';
	foreach (array('all' => $txt['fid_filter_activity_all'], 'active' => $txt['fid_filter_activity_active'], 'silent' => $txt['fid_filter_activity_silent']) as $key => $label)
		echo '
					<option value="', $key, '"', $context['fid_activity'] === $key ? ' selected' : '', '>', $label, '</option>';
	echo '
				</select>
			</label>
			<button type="submit" class="button fid-submit">', $txt['fid_filter_apply'], '</button>
		</form>';
	template_show_list('fid_member_list');
	echo '
	</div>
	<div class="fid-panel fid-panel-footnote">
		<p>', sprintf($txt['fid_activity_score_full'], (int) fid_setting('fid_weight_recent_post'), (int) fid_setting('fid_weight_topics'), (int) fid_setting('fid_weight_login'), (int) fid_setting('fid_weight_consistency')), '</p>
	</div>';
	fid_export_link('members', ';period=' . (int) $context['fid_period']['label']);
}

/**
 * Board intelligence template: the board list with quick-ranking links.
 */
function template_fid_boards()
{
	global $context, $txt, $scripturl;

	fid_period_selector('boards');
	fid_alerts_bar();

	echo '
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_board_list_title'], '</h3>
		<div class="fid-filters">
			<span>', $txt['fid_board_rankings'], '</span>
			<a class="fid-pill" href="', $scripturl, '?action=admin;area=fid;sa=boards;sort=num_posts;desc">', $txt['fid_rank_popular'], '</a>
			<a class="fid-pill" href="', $scripturl, '?action=admin;area=fid;sa=boards;sort=window_posts;desc">', $txt['fid_rank_active'], '</a>
			<a class="fid-pill" href="', $scripturl, '?action=admin;area=fid;sa=boards;sort=growth;desc">', $txt['fid_rank_growing'], '</a>
			<a class="fid-pill" href="', $scripturl, '?action=admin;area=fid;sa=boards;sort=window_posts;asc">', $txt['fid_rank_declining'], '</a>
			<a class="fid-pill" href="', $scripturl, '?action=admin;area=fid;sa=boards;sort=last_post_time;asc">', $txt['fid_rank_inactive'], '</a>
		</div>';
	template_show_list('fid_board_list');
	echo '
	</div>';
	fid_export_link('boards', ';period=' . (int) $context['fid_period']['label']);
}

/**
 * Growth analytics template: SVG charts plus daily/weekly/monthly tables.
 */
function template_fid_growth()
{
	global $context, $txt;

	fid_period_selector('growth');
	fid_alerts_bar();

	echo '
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_growth_chart_title'], '</h3>';
	fid_svg_chart($context['fid_chart'], 'fid_growth_chart');
	echo '
	</div>
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_growth_monthly_title'], '</h3>';

	if (empty($context['fid_monthly']))
		echo '
		<p class="fid-empty">', $txt['fid_no_data'], '</p>';
	else
	{
		echo '
		<table class="fid-table">
			<thead>
				<tr>
					<th scope="col">', $txt['fid_col_month'], '</th>
					<th scope="col">', $txt['fid_metric_registrations'], '</th>
					<th scope="col">', $txt['fid_metric_posts'], '</th>
					<th scope="col">', $txt['fid_metric_topics'], '</th>
					<th scope="col">', $txt['fid_metric_active_members'], '</th>
					<th scope="col">', $txt['fid_col_growth'], '</th>
				</tr>
			</thead>
			<tbody>';

		$previous = null;
		foreach ($context['fid_monthly'] as $month)
		{
			$change = $previous !== null ? fid_percent_change($month['posts'], $previous) : null;
			echo '
				<tr>
					<td>', htmlspecialchars((string) $month['month'], ENT_QUOTES), '</td>
					<td>', $month['registers'], '</td>
					<td>', $month['posts'], '</td>
					<td>', $month['topics'], '</td>
					<td>', $month['active_members'], '</td>
					<td>', fid_growth_html($change), '</td>
				</tr>';
			$previous = $month['posts'];
		}
		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_growth_weekly_title'], '</h3>';

	if (empty($context['fid_weekly']))
		echo '
		<p class="fid-empty">', $txt['fid_no_data'], '</p>';
	else
	{
		echo '
		<table class="fid-table">
			<thead>
				<tr>
					<th scope="col">', $txt['fid_col_week'], '</th>
					<th scope="col">', $txt['fid_metric_registrations'], '</th>
					<th scope="col">', $txt['fid_metric_posts'], '</th>
					<th scope="col">', $txt['fid_metric_topics'], '</th>
					<th scope="col">', $txt['fid_metric_active_members'], '</th>
					<th scope="col">', $txt['fid_metric_online'], '</th>
				</tr>
			</thead>
			<tbody>';
		foreach ($context['fid_weekly'] as $week)
			echo '
				<tr>
					<td>', htmlspecialchars((string) $week['week'], ENT_QUOTES), '</td>
					<td>', $week['registers'], '</td>
					<td>', $week['posts'], '</td>
					<td>', $week['topics'], '</td>
					<td>', $week['active_members'], '</td>
					<td>', (int) $week['most_on'], '</td>
				</tr>';
		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_growth_daily_title'], '</h3>';

	if (empty($context['fid_daily']))
		echo '
		<p class="fid-empty">', $txt['fid_no_data'], '</p>';
	else
	{
		echo '
		<table class="fid-table">
			<thead>
				<tr>
					<th scope="col">', $txt['fid_col_date'], '</th>
					<th scope="col">', $txt['fid_metric_registrations'], '</th>
					<th scope="col">', $txt['fid_metric_posts'], '</th>
					<th scope="col">', $txt['fid_metric_topics'], '</th>
					<th scope="col">', $txt['fid_metric_active_members'], '</th>
				</tr>
			</thead>
			<tbody>';
		foreach ($context['fid_daily'] as $row)
			echo '
				<tr>
					<td>', htmlspecialchars((string) $row['date'], ENT_QUOTES), '</td>
					<td>', (int) $row['registers'], '</td>
					<td>', (int) $row['posts'], '</td>
					<td>', (int) $row['topics'], '</td>
					<td>', (int) $row['active_members'], '</td>
				</tr>';
		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>';
	fid_export_link('daily', ';period=' . (int) $context['fid_period']['label']);
	fid_export_link('monthly', ';period=' . (int) $context['fid_period']['label']);
}

/**
 * Inactive member report template: category counts, filters, list, export.
 */
function template_fid_inactive()
{
	global $context, $txt, $scripturl;

	fid_alerts_bar();

	$threshold = (int) $context['fid_threshold'];
	$category = (string) $context['fid_category'];
	$search_qs = $context['fid_search'] !== '' ? ';fid_search=' . urlencode($context['fid_search']) : '';

	echo '
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_inactive_title'], '</h3>
		<div class="fid-filters">
			<span>', $txt['fid_filter_threshold'], '</span>';
	foreach (array(30, 60, 90, 180, 365) as $days)
		echo '
			<a class="fid-pill', $threshold === $days ? ' fid-pill-active' : '', '" href="', $scripturl, '?action=admin;area=fid;sa=inactive;fid_days=', $days, '">', sprintf($txt['fid_days_short'], $days), '</a>';
	echo '
		</div>
		<div class="fid-filters">
			<span>', $txt['fid_filter_category'], '</span>';
	foreach ($context['fid_categories'] as $cat)
		echo '
			<a class="fid-pill', $category === $cat ? ' fid-pill-active' : '', '" href="', $scripturl, '?action=admin;area=fid;sa=inactive;fid_days=', $threshold, ';fid_category=', $cat, $search_qs, '">', $txt['fid_category_' . $cat], ' (', (int) $context['fid_category_counts'][$cat], ')</a>';
	echo '
		</div>
		<form class="fid-period-form" method="get" action="', $scripturl, '?action=admin;area=fid;sa=inactive">
			<input type="hidden" name="action" value="admin">
			<input type="hidden" name="area" value="fid">
			<input type="hidden" name="sa" value="inactive">
			<input type="hidden" name="fid_category" value="', htmlspecialchars($category, ENT_QUOTES), '">
			<label class="fid-inline">', $txt['fid_filter_custom_days'], '
				<input type="number" name="fid_days" min="1" max="3650" value="', $threshold, '">
			</label>
			<label class="fid-inline">', $txt['fid_filter_search'], '
				<input type="search" name="fid_search" value="', htmlspecialchars((string) $context['fid_search'], ENT_QUOTES), '">
			</label>
			<button type="submit" class="button fid-submit">', $txt['fid_filter_apply'], '</button>
		</form>';

	template_show_list('fid_inactive_list');

	echo '
	</div>';
	fid_export_link('inactive');
}

/**
 * Activity comparison template: paired metric tables with safe percentages.
 */
function template_fid_compare()
{
	global $context, $txt, $scripturl;

	fid_period_selector('compare');

	$metric_labels = array(
		'posts' => $txt['fid_metric_posts'],
		'topics' => $txt['fid_metric_topics'],
		'registers' => $txt['fid_metric_registrations'],
		'active_members' => $txt['fid_metric_active_members'],
	);
	$change_keys = array(
		'posts' => 'post_change',
		'topics' => 'topic_change',
		'registers' => 'reg_change',
		'active_members' => 'member_change',
	);
	$pair_labels = array(
		'today' => $txt['fid_compare_today'],
		'week' => $txt['fid_compare_week'],
		'month' => $txt['fid_compare_month'],
		'custom' => $txt['fid_compare_custom'],
	);

	echo '
	<div class="fid-panel">
		<h3 class="fid-panel-title">', $txt['fid_compare_title'], '</h3>';

	$has_custom = !empty($context['fid_compare']['custom']);
	foreach ($context['fid_compare'] as $pair_key => $pair)
	{
		if ($pair_key === 'custom' && !$has_custom)
			continue;

		echo '
		<table class="fid-table">
			<caption class="fid-caption">', $pair_labels[$pair_key], '</caption>
			<thead>
				<tr>
					<th scope="col">', $txt['fid_metric_period'], '</th>
					<th scope="col">', $txt['fid_compare_current'], '</th>
					<th scope="col">', $txt['fid_compare_previous'], '</th>
					<th scope="col">', $txt['fid_compare_difference'], '</th>
					<th scope="col">', $txt['fid_compare_change'], '</th>
				</tr>
			</thead>
			<tbody>';
		foreach ($metric_labels as $metric => $label)
		{
			$current = (int) $pair['current'][$metric];
			$previous = (int) $pair['previous'][$metric];
			$change = $pair[$change_keys[$metric]];

			echo '
				<tr>
					<td>', $label, '</td>
					<td>', $current, '</td>
					<td>', $previous, '</td>
					<td>', $current - $previous, '</td>
					<td>', fid_growth_html($change), '</td>
				</tr>';
		}
		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>';
}

/**
 * Admin alerts template.
 */
function template_fid_alerts()
{
	global $context, $txt, $scripturl;

	$status = (string) $context['fid_alert_status'];
	$severity = (string) $context['fid_alert_severity'];

	echo '
	<div class="fid-panel">
		<div class="fid-filters">
			<span>', $txt['fid_filter_status'], '</span>';
	foreach (array('all', 'new', 'ack') as $s)
		echo '
			<a class="fid-pill', $status === $s ? ' fid-pill-active' : '', '" href="', $scripturl, '?action=admin;area=fid;sa=alerts;fid_status=', $s, ';fid_severity=', $severity, '">', $txt['fid_filter_status_' . $s], '</a>';
	echo '
		</div>
		<div class="fid-filters">
			<span>', $txt['fid_filter_severity'], '</span>';
	foreach (array('all', 'low', 'medium', 'high', 'critical') as $s)
		echo '
			<a class="fid-pill', $severity === $s ? ' fid-pill-active' : '', '" href="', $scripturl, '?action=admin;area=fid;sa=alerts;fid_status=', $status, ';fid_severity=', $s, '">', $txt['fid_severity_' . $s], '</a>';
	echo '
		</div>';

	if ((int) fid_setting('fid_alert_enabled') !== 1)
		echo '
		<p class="fid-empty">', $txt['fid_alerts_disabled'], '</p>';

	template_show_list('fid_alert_list');

	echo '
	</div>';
}

/**
 * Settings template: SMF's native show_settings rendering.
 */
function template_fid_settings()
{
	template_show_settings();
}

/**
 * CSV export link panel. The link includes the session variable because the
 * export router performs a GET session check.
 *
 * @param string $type Export type (members, inactive, boards, daily, monthly).
 * @param string $period_qs Optional period query fragment to preserve filters.
 */
function fid_export_link($type, $period_qs = '')
{
	global $txt, $scripturl, $context;

	if ((int) fid_setting('fid_allow_csv') !== 1)
		return;

	// Preserve custom date filters for exports. The export controller uses
	// fid_period(), so these parameters keep its data range identical to
	// the one currently displayed in the dashboard.
	if (!empty($context['fid_period']['is_custom']))
	{
		$period = $context['fid_period'];
		$period_qs .= ';fid_from=' . gmdate('Y-m-d', (int) $period['start']);
		$period_qs .= ';fid_to=' . gmdate('Y-m-d', (int) $period['end']);
	}

	echo '
	<div class="fid-panel fid-export-panel">
		<a class="button fid-export" href="', $scripturl, '?action=admin;area=fid;sa=export;type=', $type, $period_qs, ';', $context['session_var'], '=', $context['session_id'], '">', sprintf($txt['fid_export_link'], $txt['fid_export_type_' . $type]), '</a>
	</div>';
}
