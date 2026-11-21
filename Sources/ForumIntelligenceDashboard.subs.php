<?php

/**
 * Forum Intelligence Dashboard - data, cache, alert and export functions.
 *
 * Simple Machines Forum (SMF)
 *
 * @package ForumIntelligenceDashboard
 * @author rumpa
 * @copyright 2026 rumpa
 * @license see the included LICENSE.txt
 *
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Every setting used by the mod, with its default value.
 *
 * @return array
 */
function fid_settings_defaults()
{
	return array(
		'fid_enabled' => 1,
		'fid_default_period' => 30,
		'fid_cache_ttl' => 300,
		'fid_items_per_page' => 20,
		'fid_inactivity_days' => 90,
		'fid_allow_csv' => 1,
		'fid_charts' => 1,
		'fid_chart_style' => 'bar',
		'fid_weight_recent_post' => 5,
		'fid_weight_topics' => 3,
		'fid_weight_login' => 2,
		'fid_weight_consistency' => 1,
		'fid_alert_enabled' => 1,
		'fid_alert_threshold' => 40,
		'fid_alert_spike_threshold' => 300,
		'fid_alert_min_days' => 5,
		'fid_alert_interval' => 6,
		'fid_alert_cooldown' => 24,
		'fid_alert_decline_posts' => 1,
		'fid_alert_decline_topics' => 1,
		'fid_alert_decline_members' => 1,
		'fid_alert_decline_registration' => 1,
		'fid_alert_inactive_boards' => 1,
		'fid_alert_spikes' => 1,
		'fid_alert_spam' => 1,
		'fid_alert_inactivity' => 1,
		'fid_board_active_days' => 45,
		'fid_board_quiet_days' => 30,
	);
}

/**
 * Fetch a setting with its default fallback.
 *
 * @param string $name Setting name (fid_ prefix).
 * @return mixed
 */
function fid_setting($name)
{
	global $modSettings;

	$defaults = fid_settings_defaults();

	return isset($modSettings[$name]) ? $modSettings[$name] : (isset($defaults[$name]) ? $defaults[$name] : null);
}

/**
 * Cache wrapper around SMF's cache API, for scalar and array values.
 *
 * @param string $key Cache key (will be prefixed).
 * @param int $ttl Time to live in seconds, 0 disables caching.
 * @param callable $callback Function that produces the value.
 * @return mixed
 */
function fid_cached($key, $ttl, $callback)
{
	$ttl = (int) $ttl;

	if ($ttl > 0)
	{
		is_callable($callback);

		$value = cache_get_data('fid_' . $key, $ttl);
		if ($value !== null)
			return $value;
	}

	$value = call_user_func($callback);

	if ($ttl > 0)
		cache_put_data('fid_' . $key, $value, $ttl);

	return $value;
}

/**
 * Resolve the analytics period from the request. Secure: only whitelisted
 * values and strict ISO dates, maximum span of two years (731 days).
 *
 * @return array(start, end, prev_start, prev_end, days, label, is_custom)
 */
function fid_period()
{
	$allowed = array(7, 30, 90, 365);
	$default = (int) fid_setting('fid_default_period');
	if (!in_array($default, $allowed))
		$default = 30;

	if (isset($_REQUEST['fid_from'], $_REQUEST['fid_to']))
	{
		$from = fid_validate_date($_REQUEST['fid_from']);
		$to = fid_validate_date($_REQUEST['fid_to']);
		if ($from !== null && $to !== null && $from <= $to && ($to - $from) <= 731 * 86400)
		{
			return array(
				'start' => $from,
				'end' => min($to + 86399, time()),
				'prev_start' => $from - ($to - $from) - 86400,
				'prev_end' => $from - 1,
				'days' => (int) floor(($to - $from) / 86400) + 1,
				'label' => 'custom',
				'is_custom' => true,
			);
		}
	}

	$days = isset($_REQUEST['period']) ? filter_var($_REQUEST['period'], FILTER_VALIDATE_INT) : $default;
	if (!in_array($days, $allowed))
		$days = $default;

	$second_count = $days * 86400;
	$now_day = strtotime(gmdate('Y-m-d', time()));

	return array(
		'start' => $now_day - $second_count + 86400,
		'end' => time(),
		'prev_start' => $now_day - ($second_count * 2) + 86400,
		'prev_end' => $now_day,
		'days' => $days,
		'label' => $days,
		'is_custom' => false,
	);
}

/**
 * Validate a submitted date string. Returns a unix timestamp for the start of
 * that day, or null when the input is invalid.
 *
 * @param mixed $value Raw user input.
 * @return int|null
 */
function fid_validate_date($value)
{
	if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))
		return null;

	$parts = array_map('intval', explode('-', $value));
	if (!checkdate($parts[1], $parts[2], $parts[0]))
		return null;

	$stamp = gmmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
	if ($stamp < time() - 731 * 86400 || $stamp > time() + 86400)
		return null;

	return $stamp;
}

/**
 * The Y-m-d day key of a unix timestamp, following SMF's log_activity
 * convention which stores plain dates - see trackStats() in Logging.php.
 *
 * @param int $time Unix timestamp.
 * @return string
 */
function fid_day_key($time)
{
	return gmdate('Y-m-d', (int) $time);
}

/**
 * Aggregate approved message and topic activity for a time window.
 *
 * @param int $start Unix timestamp inclusive.
 * @param int $end Unix timestamp inclusive.
 * @param bool $with_members Also count distinct posting members.
 * @return array
 */
function fid_window_activity($start, $end, $with_members = true)
{
	global $smcFunc;

	$select = $with_members ? ', IFNULL(SUM(id_member > 0), 0) AS member_posts, COUNT(DISTINCT id_member) AS active_members' : '';
	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(COUNT(*), 0) AS posts' . $select . '
		FROM {db_prefix}messages
		WHERE approved = {int:approved}
			AND poster_time BETWEEN {int:start} AND {int:end}',
		array(
			'approved' => 1,
			'start' => $start,
			'end' => $end,
		)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	$result = array('posts' => (int) $row['posts']);

	if ($with_members)
	{
		$result['member_posts'] = (int) $row['member_posts'];
		$result['active_members'] = (int) $row['active_members'];
	}

	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(COUNT(*), 0) AS topics
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
		WHERE t.approved = {int:approved}
			AND m.poster_time BETWEEN {int:start} AND {int:end}',
		array(
			'approved' => 1,
			'start' => $start,
			'end' => $end,
		)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	$result['topics'] = (int) $row['topics'];

	return $result;
}

/**
 * Full-domain overview statistics: member totals, post/topic totals and
 * windowed activity for the selected range and the previous equal range.
 *
 * @param array $period As returned by fid_period().
 * @return array
 */
function fid_overview_stats($period)
{
	global $smcFunc;

	$p = $period;

	return fid_cached('overview_' . $p['start'] . '_' . $p['end'], (int) fid_setting('fid_cache_ttl'), function() use ($smcFunc, $p) {
		$request = $smcFunc['db_query']('', '
			SELECT IFNULL(SUM(num_posts), 0) AS total_posts, IFNULL(SUM(num_topics), 0) AS total_topics,
				IFNULL(SUM(num_posts > 0), 0) AS active_boards, IFNULL(COUNT(*), 0) AS all_boards
			FROM {db_prefix}boards');
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
		$totals = array(
			'total_posts' => (int) $row['total_posts'],
			'total_topics' => (int) $row['total_topics'],
			'active_boards' => (int) $row['active_boards'],
			'all_boards' => (int) $row['all_boards'],
		);

		$request = $smcFunc['db_query']('', '
			SELECT IFNULL(COUNT(*), 0) AS total_members,
				IFNULL(SUM(date_registered BETWEEN {int:start} AND {int:end}), 0) AS reg_period,
				IFNULL(SUM(date_registered BETWEEN {int:prev_start} AND {int:prev_end}), 0) AS reg_prev,
				IFNULL(SUM(last_login >= {int:start}), 0) AS active_login,
				IFNULL(SUM(last_login = 0), 0) AS never_logged
			FROM {db_prefix}members',
			array(
				'start' => $p['start'],
				'end' => $p['end'],
				'prev_start' => $p['prev_start'],
				'prev_end' => $p['prev_end'],
			)
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
		$members = array(
			'total_members' => (int) $row['total_members'],
			'reg_period' => (int) $row['reg_period'],
			'reg_prev' => (int) $row['reg_prev'],
			'active_login' => (int) $row['active_login'],
			'never_logged' => (int) $row['never_logged'],
		);

		$window = fid_window_activity($p['start'], $p['end'], true);
		$prev_window = fid_window_activity($p['prev_start'], $p['prev_end'], true);

		return $totals + $members + $window + $prev_window;
	});
}

/**
 * Short period totals (today, yesterday, week, month and their previous
 * counterparts) powering overview cards and comparison analytics. Uses SMF's
 * own log_activity daily counters when populated and falls back to live
 * queries otherwise.
 *
 * @return array
 */
function fid_day_slices()
{
	global $smcFunc;

	return fid_cached('day_slices_' . date('Ymd'), (int) fid_setting('fid_cache_ttl'), function() use ($smcFunc) {
		$now_day = strtotime(gmdate('Y-m-d', time()));
		$month_start = strtotime(gmdate('Y-m-01', time()));
		$prev_month_start = strtotime('-1 month', $month_start);

		$slices = array(
			'today' => array('start' => $now_day, 'end' => time()),
			'yesterday' => array('start' => $now_day - 86400, 'end' => $now_day - 1),
			'week' => array('start' => $now_day - 6 * 86400, 'end' => time()),
			'prev_week' => array('start' => $now_day - 13 * 86400, 'end' => $now_day - 7 * 86400),
			'month' => array('start' => $month_start, 'end' => time()),
			'prev_month' => array('start' => $prev_month_start, 'end' => $month_start - 1),
		);

		foreach ($slices as $key => $slice)
		{
			$request = $smcFunc['db_query']('', '
				SELECT IFNULL(SUM(date_registered BETWEEN {int:start} AND {int:end}), 0) AS cnt
				FROM {db_prefix}members',
				array('start' => $slice['start'], 'end' => $slice['end'])
			);
			$row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
			$slices[$key]['registers'] = (int) $row['cnt'];
			$slices[$key]['from_day'] = fid_day_key($slice['start']);
			$slices[$key]['to_day'] = fid_day_key($slice['end']);
		}

		$from = min(array_column($slices, 'from_day'));
		$to = max(array_column($slices, 'to_day'));
		$request = $smcFunc['db_query']('', '
			SELECT date, posts, topics, registers, most_on
			FROM {db_prefix}log_activity
			WHERE date BETWEEN {date:from} AND {date:to}',
			array('from' => $from, 'to' => $to)
		);
		$activity_log = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$activity_log[$row['date']] = $row;
		$smcFunc['db_free_result']($request);

		foreach ($slices as $key => $slice)
		{
			$dates_needed = array();
			for ($d = $slice['from_day']; $d <= $slice['to_day']; $d = gmdate('Y-m-d', strtotime($d . ' +1 day')))
				$dates_needed[] = $d;

			$slices[$key]['posts'] = 0;
			$slices[$key]['topics'] = 0;
			$slices[$key]['most_on'] = 0;

			$covered = !empty($dates_needed);
			foreach ($dates_needed as $d)
				if (!isset($activity_log[$d]))
					$covered = false;

			if ($covered)
				foreach ($dates_needed as $d)
				{
					$slices[$key]['posts'] += (int) $activity_log[$d]['posts'];
					$slices[$key]['topics'] += (int) $activity_log[$d]['topics'];
					$slices[$key]['most_on'] = max($slices[$key]['most_on'], (int) $activity_log[$d]['most_on']);
				}
			else
			{
				$activity = fid_window_activity($slice['start'], $slice['end'], false);
				$slices[$key]['posts'] = $activity['posts'];
				$slices[$key]['topics'] = $activity['topics'];
			}
		}

		return $slices;
	});
}

/**
 * Per-day analytics rows for the growth section. Registrations come from the
 * members table, posts/topics from the messages table, and SMF's own daily
 * counters are used when present (matches official forum statistics).
 *
 * @param int $start Unix timestamp.
 * @param int $end Unix timestamp.
 * @return array[] Each row: date, posts, topics, registers, active_members.
 */
function fid_daily_stats($start, $end)
{
	global $smcFunc;

	return fid_cached('daily_' . $start . '_' . $end, (int) fid_setting('fid_cache_ttl'), function() use ($smcFunc, $start, $end) {
		$stats = array();
		for ($d = fid_day_key(min($start, $end)); $d <= fid_day_key(max($start, $end)); $d = gmdate('Y-m-d', strtotime($d . ' +1 day')))
			$stats[$d] = array(
				'date' => $d,
				'posts' => 0,
				'topics' => 0,
				'registers' => 0,
				'active_members' => 0,
				'most_on' => 0,
			);

		$request = $smcFunc['db_query']('', '
			SELECT date, posts, topics, registers, most_on
			FROM {db_prefix}log_activity
			WHERE date >= {date:from} AND date <= {date:to}
			ORDER BY date ASC',
			array('from' => fid_day_key($start), 'to' => fid_day_key($end))
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			if (isset($stats[$row['date']]))
			{
				$stats[$row['date']]['posts'] = (int) $row['posts'];
				$stats[$row['date']]['topics'] = (int) $row['topics'];
				$stats[$row['date']]['registers'] = (int) $row['registers'];
				$stats[$row['date']]['most_on'] = (int) $row['most_on'];
			}
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', '
			SELECT FROM_UNIXTIME(date_registered, {string:format}) AS day, IFNULL(COUNT(*), 0) AS cnt
			FROM {db_prefix}members
			WHERE date_registered BETWEEN {int:start} AND {int:end}
			GROUP BY day',
			array(
				'format' => '%Y-%m-%d',
				'start' => $start,
				'end' => $end,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			if (isset($stats[$row['day']]))
				$stats[$row['day']]['registers'] = (int) $row['cnt'];
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', '
			SELECT FROM_UNIXTIME(poster_time, {string:format}) AS day,
				IFNULL(COUNT(*), 0) AS posts, COUNT(DISTINCT id_member) AS actives
			FROM {db_prefix}messages
			WHERE approved = {int:approved}
				AND poster_time BETWEEN {int:start} AND {int:end}
			GROUP BY day',
			array(
				'format' => '%Y-%m-%d',
				'approved' => 1,
				'start' => $start,
				'end' => $end,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			if (isset($stats[$row['day']]))
			{
				$stats[$row['day']]['posts'] = (int) $row['posts'];
				$stats[$row['day']]['active_members'] = (int) $row['actives'];
			}
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', '
			SELECT FROM_UNIXTIME(m.poster_time, {string:format}) AS day, IFNULL(COUNT(*), 0) AS topics
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.approved = {int:approved}
				AND m.poster_time BETWEEN {int:start} AND {int:end}
			GROUP BY day',
			array(
				'format' => '%Y-%m-%d',
				'approved' => 1,
				'start' => $start,
				'end' => $end,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			if (isset($stats[$row['day']]))
				$stats[$row['day']]['topics'] = (int) $row['topics'];
		$smcFunc['db_free_result']($request);

		return array_values($stats);
	});
}

/**
 * Aggregate daily rows into natural weeks.
 *
 * @param array[] $rows As returned by fid_daily_stats().
 * @return array[]
 */
function fid_weekly_stats($rows)
{
	$weeks = array();
	foreach ($rows as $row)
	{
		$week = gmdate('o-\WW', strtotime($row['date'] . ' 12:00:00'));
		if (!isset($weeks[$week]))
			$weeks[$week] = array('week' => $week, 'date' => $row['date'], 'posts' => 0, 'topics' => 0, 'registers' => 0, 'active_members' => 0, 'most_on' => 0);
		$weeks[$week]['posts'] += $row['posts'];
		$weeks[$week]['topics'] += $row['topics'];
		$weeks[$week]['registers'] += $row['registers'];
		$weeks[$week]['active_members'] = max($weeks[$week]['active_members'], $row['active_members']);
		$weeks[$week]['most_on'] = max($weeks[$week]['most_on'], $row['most_on']);
	}

	return array_values($weeks);
}

/**
 * Aggregate daily rows into calendar months.
 *
 * @param array[] $rows As returned by fid_daily_stats().
 * @return array[]
 */
function fid_monthly_stats($rows)
{
	$months = array();
	foreach ($rows as $row)
	{
		$month = substr($row['date'], 0, 7);
		if (!isset($months[$month]))
			$months[$month] = array('month' => $month, 'date' => $row['date'], 'posts' => 0, 'topics' => 0, 'registers' => 0, 'active_members' => 0, 'most_on' => 0);
		$months[$month]['posts'] += $row['posts'];
		$months[$month]['topics'] += $row['topics'];
		$months[$month]['registers'] += $row['registers'];
		$months[$month]['active_members'] = max($months[$month]['active_members'], $row['active_members']);
		$months[$month]['most_on'] = max($months[$month]['most_on'], $row['most_on']);
	}

	return array_values($months);
}

/**
 * Display-ready fields (profile link and escaped values) shared by the member
 * lists. All values are escaped here so templates and list callbacks never
 * have to reinterpret raw database content.
 *
 * @param array $row Raw member row.
 * @return array
 */
function fid_member_display_fields($row)
{
	global $scripturl;

	$id = (int) $row['id_member'];
	$name = $row['real_name'] != '' ? $row['real_name'] : $row['member_name'];

	return array(
		'href' => $scripturl . '?action=profile;u=' . $id,
		'name' => $name,
		'name_html' => htmlspecialchars($name, ENT_QUOTES),
		'last_login_html' => fid_time_html((int) $row['last_login']),
		'registered_html' => fid_time_html((int) $row['date_registered']),
		'score' => (int) $row['activity_score'],
	);
}

/**
 * Escaped time cell using SMF's timeformat. Empty timestamps (never logged
 * in, never posted) render the translated 'never' placeholder.
 *
 * @param int $timestamp Unix timestamp or 0.
 * @return string Safe HTML.
 */
function fid_time_html($timestamp)
{
	global $txt;

	$timestamp = (int) $timestamp;
	if ($timestamp <= 0)
		return htmlspecialchars((string) $txt['fid_never'], ENT_QUOTES);

	return '<span class="fid-time">' . htmlspecialchars(timeformat($timestamp), ENT_QUOTES) . '</span>';
}

/**
 * Escaped, trend-aware growth cell. Null means 'no baseline' and renders a
 * dash instead of a misleading percentage.
 *
 * @param float|null $percentage Result of fid_percent_change().
 * @return string Safe HTML.
 */
function fid_growth_html($percentage)
{
	global $txt;

	if ($percentage === null || $percentage === false)
		return htmlspecialchars((string) $txt['fid_no_baseline'], ENT_QUOTES);

	$value = round((float) $percentage, 1);
	$class = $value > 0 ? 'fid-up' : ($value < 0 ? 'fid-down' : 'fid-flat');
	$arrow = $value > 0 ? '&#9650;' : ($value < 0 ? '&#9660;' : '&#9644;');

	return '<span class="' . $class . '">' . $arrow . ' ' . htmlspecialchars((string) $value, ENT_QUOTES) . '%</span>';
}

/**
 * Percentage change with safe zero handling. Both values zero => 0.0, zero
 * previous with non-zero current => null (uncalculable, shown as new).
 *
 * @param int|float|null $current
 * @param int|float|null $previous
 * @return float|null
 */
function fid_percent_change($current, $previous)
{
	$current = (float) $current;
	$previous = (float) $previous;

	if ($previous == 0.0)
		return $current == 0.0 ? 0.0 : null;

	return round((($current - $previous) / $previous) * 100, 1);
}

/**
 * Transparent activity score. Documented formula (all inputs are real data):
 *
 *   score = Wp * posts_in_window
 *         + Wt * topics_started_in_window
 *         + Wl * login_points
 *         + Wc * active_days_in_window
 *
 *   login_points = max(0, 7 - days_since_last_login), capped at 7
 *   active_days_in_window = distinct days with at least one approved post
 *
 * Weights are configurable in the settings panel (fid_weight_* settings).
 *
 * @param array $member Member row augmented by fid_member_window_stats().
 * @return int
 */
function fid_member_activity_score($member)
{
	$days_since_login = $member['last_login'] > 0 ? (int) floor((time() - $member['last_login']) / 86400) : 9999;
	$login_points = max(0, 7 - min(7, $days_since_login));

	return (int) fid_setting('fid_weight_recent_post') * (int) $member['window_posts']
		+ (int) fid_setting('fid_weight_topics') * (int) $member['window_topics']
		+ (int) fid_setting('fid_weight_login') * $login_points
		+ (int) fid_setting('fid_weight_consistency') * (int) $member['active_days'];
}

/**
 * Per-member activity aggregates for an explicit set of member ids. Only the
 * members shown on the current page are queried, so these lookups stay small.
 *
 * @param int[] $ids Member ids.
 * @param int $start Window start.
 * @param int $end Window end.
 * @return array id_member => array(window_posts, window_topics, active_days, last_post_time)
 */
function fid_member_window_stats($ids, $start, $end)
{
	global $smcFunc;

	$ids = array_map('intval', array_unique($ids));
	$result = array_fill_keys($ids, array('window_posts' => 0, 'window_topics' => 0, 'active_days' => 0, 'last_post_time' => 0));
	if (empty($ids))
		return $result;

	$request = $smcFunc['db_query']('', '
		SELECT id_member,
			IFNULL(COUNT(*), 0) AS posts,
			IFNULL(COUNT(DISTINCT FROM_UNIXTIME(poster_time, {string:format})), 0) AS days,
			IFNULL(MAX(poster_time), 0) AS last_post_time
		FROM {db_prefix}messages
		WHERE approved = {int:approved}
			AND id_member IN ({array_int:ids})
			AND poster_time BETWEEN {int:start} AND {int:end}
		GROUP BY id_member',
		array(
			'format' => '%Y-%m-%d',
			'approved' => 1,
			'ids' => $ids,
			'start' => $start,
			'end' => $end,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$id = (int) $row['id_member'];
		if (isset($result[$id]))
		{
			$result[$id]['window_posts'] = (int) $row['posts'];
			$result[$id]['active_days'] = (int) $row['days'];
			$result[$id]['last_post_time'] = (int) $row['last_post_time'];
		}
	}
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('', '
		SELECT t.id_member_started, IFNULL(COUNT(*), 0) AS topics
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
		WHERE t.approved = {int:approved}
			AND t.id_member_started IN ({array_int:ids})
			AND m.poster_time BETWEEN {int:start} AND {int:end}
		GROUP BY t.id_member_started',
		array(
			'approved' => 1,
			'ids' => $ids,
			'start' => $start,
			'end' => $end,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$id = (int) $row['id_member_started'];
		if (isset($result[$id]))
			$result[$id]['window_topics'] = (int) $row['topics'];
	}
	$smcFunc['db_free_result']($request);

	return $result;
}

/**
 * The member-id set that makes up the most-active rankings for a window,
 * ordered by real post volume, bounded to keep queries fast.
 *
 * @param int $start Window start.
 * @param int $end Window end.
 * @param int $limit Maximum members to fetch.
 * @return int[]
 */
function fid_ranked_member_ids($start, $end, $limit = 50)
{
	global $smcFunc;

	return fid_cached('ranked_' . $start . '_' . $end . '_' . $limit, (int) fid_setting('fid_cache_ttl'), function() use ($smcFunc, $start, $end, $limit) {
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}messages
			WHERE approved = {int:approved}
				AND id_member > 0
				AND poster_time BETWEEN {int:start} AND {int:end}
			GROUP BY id_member
			ORDER BY COUNT(*) DESC, id_member ASC
			LIMIT {int:limit}',
			array(
				'approved' => 1,
				'start' => $start,
				'end' => $end,
				'limit' => (int) $limit,
			)
		);
		$ids = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$ids[] = (int) $row['id_member'];
		$smcFunc['db_free_result']($request);

		return $ids;
	});
}

/**
 * Filter logic shared by the member intelligence list and its CSV export.
 *
 * @param array $period Period array.
 * @param string $search Optional member search term.
 * @param string $activity Optional activity filter (all|active|silent).
 * @return array()
 */
function fid_member_conditions($period, $search = '', $activity = 'all')
{
	global $smcFunc;

	$where = '';
	$params = array('start' => $period['start'], 'end' => $period['end']);
	if ($search !== '')
	{
		$where .= ' AND m.real_name LIKE {string:search}';
		$params['search'] = '%' . $smcFunc['db_escape_wildcard_string']($smcFunc['db_escape_string']($search)) . '%';
	}
	if ($activity === 'active')
		$where .= ' AND EXISTS (SELECT 1 FROM {db_prefix}messages mm2 WHERE mm2.id_member = m.id_member AND mm2.poster_time BETWEEN {int:start} AND {int:end})';
	elseif ($activity === 'silent')
		$where .= ' AND m.posts > 0 AND NOT EXISTS (SELECT 1 FROM {db_prefix}messages mm2 WHERE mm2.id_member = m.id_member AND mm2.poster_time BETWEEN {int:start} AND {int:end})';

	return array($where, $params);
}

/**
 * get_items callback for the member intelligence list.
 *
 * @param int $start List offset.
 * @param int $items_per_page Page size.
 * @param string $sort SQL sort clause (whitelisted via createList).
 * @param array $period Period array.
 * @param string $search Search term.
 * @param string $activity Activity filter.
 * @return array[]
 */
function fid_list_members($start, $items_per_page, $sort, $period, $search = '', $activity = 'all')
{
	global $smcFunc;

	list ($where, $params) = fid_member_conditions($period, $search, $activity);

	$request = $smcFunc['db_query']('', '
		SELECT m.id_member, m.real_name, m.member_name, m.date_registered, m.last_login, m.posts, m.is_activated
		FROM {db_prefix}members AS m
		WHERE 1=1' . $where . '
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		$params + array('sort' => $sort, 'start' => (int) $start, 'limit' => (int) $items_per_page)
	);
	$rows = $smcFunc['db_fetch_all']($request);
	$smcFunc['db_free_result']($request);

	if (empty($rows))
		return array();

	$ids = array();
	foreach ($rows as $row)
		$ids[] = (int) $row['id_member'];

	$window = fid_member_window_stats($ids, $period['start'], $period['end']);
	$members = array();
	foreach ($rows as $row)
	{
		if (!isset($window[$row['id_member']]))
			$window[$row['id_member']] = array('window_posts' => 0, 'window_topics' => 0, 'active_days' => 0, 'last_post_time' => 0);

		$row['window_posts'] = $window[$row['id_member']]['window_posts'];
		$row['window_topics'] = $window[$row['id_member']]['window_topics'];
		$row['active_days'] = $window[$row['id_member']]['active_days'];
		$row['last_post_time'] = $window[$row['id_member']]['last_post_time'];
		$row['activity_score'] = fid_member_activity_score($row);
		$row += fid_member_display_fields($row);

		$members[] = $row;
	}

	return $members;
}

/**
 * get_count callback for the member intelligence list.
 *
 * @param string $search Search term.
 * @param string $activity Activity filter.
 * @param array $period Period array.
 * @return int
 */
function fid_list_members_count($search = '', $activity = 'all', $period = null)
{
	global $smcFunc;

	$period = $period === null ? fid_period() : $period;
	list ($where, $params) = fid_member_conditions($period, $search, $activity);

	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(COUNT(*), 0)
		FROM {db_prefix}members AS m
		WHERE 1=1' . $where,
		$params
	);
	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return (int) $count;
}

/**
 * Board window aggregates: approved posts and started topics per board for a
 * window. Cached because board analytics, growth and alerts share the data.
 *
 * @param int $start Window start.
 * @param int $end Window end.
 * @return array id_board => array(posts, topics)
 */
function fid_board_window_stats($start, $end)
{
	global $smcFunc;

	return fid_cached('board_window_' . $start . '_' . $end, (int) fid_setting('fid_cache_ttl'), function() use ($smcFunc, $start, $end) {
		$boards = array();

		$request = $smcFunc['db_query']('', '
			SELECT id_board, IFNULL(COUNT(*), 0) AS posts
			FROM {db_prefix}messages
			WHERE approved = {int:approved}
				AND poster_time BETWEEN {int:start} AND {int:end}
			GROUP BY id_board',
			array('approved' => 1, 'start' => $start, 'end' => $end)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$boards[(int) $row['id_board']] = array('posts' => (int) $row['posts'], 'topics' => 0);
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', '
			SELECT t.id_board, IFNULL(COUNT(*), 0) AS topics
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.approved = {int:approved}
				AND m.poster_time BETWEEN {int:start} AND {int:end}
			GROUP BY t.id_board',
			array('approved' => 1, 'start' => $start, 'end' => $end)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$id = (int) $row['id_board'];
			if (!isset($boards[$id]))
				$boards[$id] = array('posts' => 0, 'topics' => 0);
			$boards[$id]['topics'] = (int) $row['topics'];
		}
		$smcFunc['db_free_result']($request);

		return $boards;
	});
}

/**
 * Board report rows that respect current board visibility (query_see_board
 * built by SMF itself), enriched with real window activity, growth and an
 * activity classification.
 *
 * @param array $period As returned by fid_period().
 * @return array[]
 */
function fid_boards_data($period)
{
	global $smcFunc, $user_info;

	$p = $period;

	return fid_cached('boards_' . $p['start'] . '_' . $p['end'], (int) fid_setting('fid_cache_ttl'), function() use ($smcFunc, $user_info, $p) {
		$request = $smcFunc['db_query']('', '
			SELECT b.id_board, b.name, b.num_posts, b.num_topics, b.id_cat,
				c.name AS cat_name, mp.poster_time AS last_post_time
			FROM {db_prefix}boards AS b
				INNER JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
				LEFT JOIN {db_prefix}messages AS mp ON (mp.id_msg = b.id_last_msg)
			WHERE ' . $user_info['query_see_board'] . '
			ORDER BY c.cat_order, b.board_order',
			array()
		);
		$rows = $smcFunc['db_fetch_all']($request);
		$smcFunc['db_free_result']($request);

		$window_stats = fid_board_window_stats($p['start'], $p['end']);
		$prev_stats = fid_board_window_stats($p['prev_start'], $p['prev_end']);

		$list = array();
		foreach ($rows as $row)
		{
			$id = (int) $row['id_board'];
			$wposts = isset($window_stats[$id]) ? $window_stats[$id]['posts'] : 0;
			$wtopics = isset($window_stats[$id]) ? $window_stats[$id]['topics'] : 0;
			$pposts = isset($prev_stats[$id]) ? $prev_stats[$id]['posts'] : 0;
			$growth = fid_percent_change($wposts, $pposts);

			$active_benchmark = (int) fid_setting('fid_board_active_days') * $p['days'] / 30;
			if ((int) $row['num_posts'] === 0 && $wposts === 0 && (int) $row['num_topics'] === 0)
				$status = 'empty';
			elseif ($wposts === 0 && $pposts === 0)
				$status = 'inactive';
			elseif ($wposts >= max(1, $active_benchmark))
				$status = 'active';
			elseif ($growth < 0)
				$status = 'declining';
			else
				$status = 'quiet';

			$list[] = array(
				'id_board' => $id,
				'name' => $row['name'],
				'cat_name' => $row['cat_name'],
				'num_posts' => (int) $row['num_posts'],
				'num_topics' => (int) $row['num_topics'],
				'last_post_time' => (int) $row['last_post_time'],
				'window_posts' => $wposts,
				'window_topics' => $wtopics,
				'prev_posts' => $pposts,
				'growth' => $growth,
				'activity_status' => $status,
				'href' => $GLOBALS['scripturl'] . '?board=' . $id . '.0',
				'name_html' => htmlspecialchars($row['name'], ENT_QUOTES),
				'cat_html' => htmlspecialchars($row['cat_name'], ENT_QUOTES),
				'growth_html' => fid_growth_html($growth),
				'status_html' => $GLOBALS['txt']['fid_board_status_' . $status],
				'last_post_html' => fid_time_html((int) $row['last_post_time']),
			);
		}

		return $list;
	});
}

/**
 * get_items callback for the board intelligence list. Sorting is applied to
 * the already-aggregated rows.
 *
 * @param int $start List offset.
 * @param int $items_per_page Page size.
 * @param string $sort Serialized sort descriptor (column|direction).
 * @param array $period Period array.
 * @return array[]
 */
function fid_list_boards($start, $items_per_page, $sort, $period)
{
	$rows = fid_boards_data($period);
	if (empty($rows))
		return array();

	$parts = explode('|', $sort);
	$sort_key = isset($parts[0]) ? $parts[0] : 'window_posts';
	$sort_desc = isset($parts[1]) && $parts[1] === 'desc';

	$allowed = array('name', 'cat_name', 'num_posts', 'num_topics', 'window_posts', 'window_topics', 'window_recent', 'growth', 'last_post_time', 'decline');
	if (!in_array($sort_key, $allowed))
		$sort_key = 'window_posts';
	if ($sort_key === 'window_recent')
		$sort_key = 'window_topics';
	elseif ($sort_key === 'decline')
		$sort_key = 'window_posts';

	$rows = fid_sort_board_rows($rows, $sort_key, $sort_desc);

	return array_slice($rows, (int) $start, (int) $items_per_page);
}

/**
 * Sort helper for board rows (nulls last for growth/last activity).
 *
 * @param array[] $rows
 * @param string $key
 * @param bool $desc
 * @return array[]
 */
function fid_sort_board_rows($rows, $key, $desc)
{
	usort($rows, function($a, $b) use ($key, $desc) {
		$va = $a[$key];
		$vb = $b[$key];
		$a_null = $va === null || $va === false;
		$b_null = $vb === null || $vb === false;
		if ($a_null !== $b_null)
			return $a_null ? 1 : -1;
		if (is_string($va))
			return $desc ? strcmp($vb, $va) : strcmp($va, $vb);
		$va = (float) $va;
		$vb = (float) $vb;
		return $desc ? ($vb <=> $va) : ($va <=> $vb);
	});

	return $rows;
}

/**
 * get_count callback for the board intelligence list.
 *
 * @param array $period Period array.
 * @return int
 */
function fid_list_boards_count($period)
{
	global $smcFunc, $user_info;

	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(COUNT(*), 0)
		FROM {db_prefix}boards AS b
		WHERE ' . $user_info['query_see_board']);
	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return (int) $count;
}

/**
 * The WHERE clauses shared by the inactive members list and its CSV export.
 *
 * @param int $threshold_days Inactivity threshold in days.
 * @param string $category Category filter.
 * @param string $search Search term.
 * @return array()
 */
function fid_inactive_conditions($threshold_days, $category = 'all', $search = '')
{
	global $smcFunc;

	$cutoff = time() - (int) $threshold_days * 86400;
	$params = array('cutoff' => $cutoff, 'long_cutoff' => time() - 365 * 86400);
	$where = ' AND (m.last_login < {int:cutoff} OR (m.last_login = 0 AND m.date_registered < {int:cutoff}))';

	if ($category === 'never_login')
		$where .= ' AND m.last_login = 0';
	elseif ($category === 'never_post')
		$where .= ' AND m.last_login > 0 AND m.posts = 0';
	elseif ($category === 'was_active')
		$where .= ' AND m.posts > 0 AND m.last_login > 0 AND NOT EXISTS (SELECT 1 FROM {db_prefix}messages mm WHERE mm.id_member = m.id_member AND mm.poster_time >= {int:cutoff})';
	elseif ($category === 'long_term')
		$where .= ' AND (m.last_login < {int:long_cutoff} OR (m.last_login = 0 AND m.date_registered < {int:long_cutoff}))';

	if ($search !== '')
	{
		$where .= ' AND m.real_name LIKE {string:search}';
		$params['search'] = '%' . $smcFunc['db_escape_wildcard_string']($smcFunc['db_escape_string']($search)) . '%';
	}

	return array($where, $params);
}

/**
 * Whitelisted sort SQL for the inactive members list.
 *
 * @param string $sort Serialized descriptor "column|direction".
 * @return string
 */
function fid_inactive_sort($sort)
{
	$allowed = array(
		'name' => 'm.real_name',
		'date_registered' => 'm.date_registered',
		'last_login' => 'm.last_login',
		'posts' => 'm.posts',
		'inactive_duration' => 'CASE WHEN m.last_login = 0 THEN m.date_registered ELSE m.last_login END',
	);
	$parts = explode('|', $sort);
	$column = isset($parts[0]) ? $parts[0] : 'name';
	$direction = isset($parts[1]) && $parts[1] === 'desc' ? 'DESC' : 'ASC';

	return (isset($allowed[$column]) ? $allowed[$column] : 'm.real_name') . ' ' . $direction;
}

/**
 * get_items callback for the inactive members list.
 *
 * @param int $start List offset.
 * @param int $items_per_page Page size.
 * @param string $sort Serialized sort descriptor.
 * @param int $threshold_days Inactivity threshold.
 * @param string $category Category filter.
 * @param string $search Search term.
 * @return array[]
 */
function fid_list_inactive($start, $items_per_page, $sort, $threshold_days, $category, $search)
{
	global $smcFunc;

	$sort_sql = fid_inactive_sort($sort);
	list ($where, $params) = fid_inactive_conditions($threshold_days, $category, $search);

	$request = $smcFunc['db_query']('', '
		SELECT m.id_member, m.real_name, m.member_name, m.date_registered, m.last_login,
			m.posts, m.is_activated
		FROM {db_prefix}members AS m
		WHERE 1=1' . $where . '
		ORDER BY ' . $sort_sql . '
		LIMIT {int:start}, {int:limit}',
		$params + array('start' => (int) $start, 'limit' => (int) $items_per_page)
	);
	$rows = $smcFunc['db_fetch_all']($request);
	$smcFunc['db_free_result']($request);

	if (empty($rows))
		return array();

	$ids = array_map('intval', array_column($rows, 'id_member'));
	$last_posts = fid_last_posts_for_members($ids);

	$members = array();
	foreach ($rows as $row)
	{
		$row['last_post_time'] = isset($last_posts[$row['id_member']]) ? $last_posts[$row['id_member']] : 0;
		$row['threshold_days'] = (int) $threshold_days;
		$row['status_key'] = $row['last_login'] == 0 ? 'never_login' : ($row['posts'] == 0 ? 'never_post' : 'was_active');
		$row += fid_member_display_fields($row);
		$row['last_post_html'] = fid_time_html($row['last_post_time']);
		$row['inactive_days_current'] = (int) floor((time() - ($row['last_login'] > 0 ? $row['last_login'] : $row['date_registered'])) / 86400);
		$row['inactive_html'] = sprintf($GLOBALS['txt']['fid_days_ago'], $row['inactive_days_current']);
		$row['category_html'] = $GLOBALS['txt']['fid_category_' . $row['status_key']];

		$members[] = $row;
	}

	return $members;
}

/**
 * get_count callback for the inactive members list.
 *
 * @param int $threshold_days Inactivity threshold.
 * @param string $category Category filter.
 * @param string $search Search term.
 * @return int
 */
function fid_list_inactive_count($threshold_days, $category, $search)
{
	global $smcFunc;

	list ($where, $params) = fid_inactive_conditions($threshold_days, $category, $search);

	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(COUNT(*), 0)
		FROM {db_prefix}members AS m
		WHERE 1=1' . $where,
		$params
	);
	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return (int) $count;
}

/**
 * Last approved message time per member for an explicit page of ids (cheap
 * indexed lookups instead of a full table GROUP BY).
 *
 * @param int[] $ids Member ids.
 * @return array id_member => unix timestamp
 */
function fid_last_posts_for_members($ids)
{
	global $smcFunc;

	$ids = array_map('intval', $ids);
	if (empty($ids))
		return array();

	$request = $smcFunc['db_query']('', '
		SELECT id_member, MAX(poster_time) AS last_post_time
		FROM {db_prefix}messages
		WHERE approved = {int:approved}
			AND id_member IN ({array_int:ids})
		GROUP BY id_member',
		array('approved' => 1, 'ids' => $ids)
	);
	$map = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$map[(int) $row['id_member']] = (int) $row['last_post_time'];
	$smcFunc['db_free_result']($request);

	return $map;
}

/**
 * Comparison metrics for standard period pairs (today vs yesterday, rolling
 * week vs previous week, this month vs previous month) and for the current
 * custom period vs its previous equivalent. Zero handling is safe.
 *
 * @param array|null $period Custom period or null for standard pairs.
 * @return array[]
 */
function fid_comparison_pairs($period = null)
{
	$now_day = strtotime(gmdate('Y-m-d', time()));
	$month_start = strtotime(gmdate('Y-m-01', time()));

	$pairs = array(
		'today' => array('start' => $now_day, 'end' => time(),
			'prev_start' => $now_day - 86400, 'prev_end' => $now_day - 1),
		'week' => array('start' => $now_day - 6 * 86400, 'end' => time(),
			'prev_start' => $now_day - 13 * 86400, 'prev_end' => $now_day - 7 * 86400),
		'month' => array('start' => $month_start, 'end' => time(),
			'prev_start' => strtotime('-1 month', $month_start), 'prev_end' => $month_start - 1),
	);

	if ($period !== null)
		$pairs = array(
			'custom' => array('start' => $period['start'], 'end' => $period['end'],
				'prev_start' => $period['prev_start'], 'prev_end' => $period['prev_end']),
		);

	$results = array();
	foreach ($pairs as $key => $p)
	{
		$cur = fid_window_activity($p['start'], $p['end'], true);
		$prev = fid_window_activity($p['prev_start'], $p['prev_end'], true);
		$cur['regs'] = fid_registry_count($p['start'], $p['end']);
		$prev['regs'] = fid_registry_count($p['prev_start'], $p['prev_end']);

		$results[$key] = array(
			'current' => $cur,
			'previous' => $prev,
			'reg_change' => fid_percent_change($cur['regs'], $prev['regs']),
			'post_change' => fid_percent_change($cur['posts'], $prev['posts']),
			'topic_change' => fid_percent_change($cur['topics'], $prev['topics']),
			'member_change' => fid_percent_change($cur['active_members'], $prev['active_members']),
		);
	}

	return $results;
}

/**
 * Exact registration count between two timestamps.
 *
 * @param int $start
 * @param int $end
 * @return int
 */
function fid_registry_count($start, $end)
{
	global $smcFunc;

	return fid_cached('regs_' . (int) $start . '_' . (int) $end, (int) fid_setting('fid_cache_ttl'), function() use ($smcFunc, $start, $end) {
		$request = $smcFunc['db_query']('', '
			SELECT IFNULL(COUNT(*), 0)
			FROM {db_prefix}members
			WHERE date_registered BETWEEN {int:start} AND {int:end}',
			array('start' => (int) $start, 'end' => (int) $end)
		);
		list ($count) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return (int) $count;
	});
}

/**
 * Current activity score weights as configured by the administrator.
 *
 * @return array(string => int)
 */
function fid_score_weights()
{
	return array(
		'recent_post' => max(0, (int) fid_setting('fid_weight_recent_post')),
		'topics' => max(0, (int) fid_setting('fid_weight_topics')),
		'login' => max(0, (int) fid_setting('fid_weight_login')),
		'consistency' => max(0, (int) fid_setting('fid_weight_consistency')),
	);
}

/**
 * Map of enabled alert categories to their check callback names. Only
 * categories switched on in the settings are returned.
 *
 * @return array(string => callable)
 */
function fid_alert_checks()
{
	$checks = array(
		'decline_posts' => 'fid_check_decline_posts',
		'decline_topics' => 'fid_check_decline_topics',
		'decline_members' => 'fid_check_decline_members',
		'decline_registration' => 'fid_check_decline_registrations',
		'spikes' => 'fid_check_spikes',
		'spam' => 'fid_check_spam',
		'inactive_boards' => 'fid_check_inactive_boards',
		'inactivity' => 'fid_check_inactivity',
	);

	$enabled = array();
	foreach ($checks as $key => $callback)
		if ((int) fid_setting('fid_alert_' . $key) === 1 && is_callable($callback))
			$enabled[$key] = $callback;

	return $enabled;
}

/**
 * The paired windows used by the decline and spike detections: the most
 * recent fid_alert_min_days days versus the equal preceding window.
 *
 * @return array(start, end, prev_start, prev_end, days)
 */
function fid_alert_windows()
{
	$days = max(2, (int) fid_setting('fid_alert_min_days'));
	$today = strtotime(gmdate('Y-m-d', time()));

	$start = $today - ($days - 1) * 86400;

	return array(
		'start' => $start,
		'end' => time(),
		'prev_start' => $start - $days * 86400,
		'prev_end' => $start - 1,
		'days' => $days,
	);
}

/**
 * Severity of a threshold breach. Escalates with the overshoot so that a
 * marginal breach and a catastrophic drop do not share the same severity.
 *
 * @param float $percentage Observed percentage relative to the threshold.
 * @param int $baseline Severity floor (1 low, 2 medium, 3 high, 4 critical).
 * @return string
 */
function fid_alert_severity($percentage, $baseline = 1)
{
	$levels = array(1 => 'low', 2 => 'medium', 3 => 'high', 4 => 'critical');
	$level = max(1, (int) $baseline);

	if ($percentage >= 400)
		$level = 4;
	elseif ($percentage >= 200)
		$level = 3;
	elseif ($percentage >= 100)
		$level = max($level, 2);

	return $levels[$level];
}

/**
 * Insert a new alert or bump the existing unresolved alert with the same
 * key. Deduplication: an open alert is only refreshed when the notification
 * cooldown has elapsed, so one incident cannot flood the alert table.
 *
 * @param string $key Stable dedupe key, at most 64 characters.
 * @param string $severity low, medium, high or critical.
 * @param string $title Alert title (language string, safe HTML).
 * @param string $message Full explanation (language string, safe HTML).
 * @param string $stats Plain text statistics summary.
 */
function fid_store_alert($key, $severity, $title, $message, $stats)
{
	global $smcFunc;

	$key = substr((string) $key, 0, 64);
	$severity = in_array($severity, array('low', 'medium', 'high', 'critical')) ? $severity : 'medium';
	$now = time();
	$cooldown = max(0, (int) fid_setting('fid_alert_cooldown')) * 3600;

	$request = $smcFunc['db_query']('', '
		SELECT id_alert, status, last_seen
		FROM {db_prefix}fid_alerts
		WHERE alert_key = {string:alert_key}
		ORDER BY last_seen DESC
		LIMIT 1',
		array('alert_key' => $key)
	);
	$existing = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($existing))
	{
		if ($existing['status'] === 'new' && $now - (int) $existing['last_seen'] < $cooldown)
			return;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}fid_alerts
			SET severity = {string:severity}, title = {string:title}, message = {string:message},
				stats = {string:stats}, first_seen = CASE WHEN status = {string:ack} THEN {int:now} ELSE first_seen END,
				last_seen = {int:now}, occurrences = occurrences + 1, status = {string:new}
			WHERE id_alert = {int:id_alert}',
			array(
				'severity' => $severity,
				'title' => $title,
				'message' => $message,
				'stats' => $stats,
				'ack' => 'ack',
				'now' => $now,
				'new' => 'new',
				'id_alert' => (int) $existing['id_alert'],
			)
		);
	}
	else
	{
		$smcFunc['db_insert']('insert',
			'{db_prefix}fid_alerts',
			array(
				'alert_key' => 'string-64',
				'severity' => 'string-20',
				'title' => 'string-255',
				'message' => 'string',
				'stats' => 'string',
				'first_seen' => 'int',
				'last_seen' => 'int',
				'occurrences' => 'int',
				'status' => 'string-10',
			),
			array(array(
				$key,
				$severity,
				substr($title, 0, 255),
				$message,
				$stats,
				$now,
				$now,
				1,
				'new',
			)),
			array('id_alert'),
			0
		);
	}

	cache_put_data('fid_unseen_alerts', null, 120);
}

/**
 * The number of unresolved (status = new) alerts, cached briefly.
 *
 * @return int
 */
function fid_unseen_alerts()
{
	global $smcFunc;

	return fid_cached('unseen_alerts', 120, function() use ($smcFunc) {
		$request = $smcFunc['db_query']('', '
			SELECT IFNULL(COUNT(*), 0)
			FROM {db_prefix}fid_alerts
			WHERE status = {string:status}',
			array('status' => 'new')
		);
		list ($count) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return (int) $count;
	});
}

/**
 * Acknowledge a single alert. Only ever changes the mod's own alert table.
 *
 * @param int $id_alert
 */
function fid_ack_alert($id_alert)
{
	global $smcFunc, $user_info;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}fid_alerts
		SET status = {string:status}, ack_by = {int:id_member}, ack_time = {int:now}
		WHERE id_alert = {int:id_alert}',
		array(
			'status' => 'ack',
			'id_member' => (int) $user_info['id'],
			'now' => time(),
			'id_alert' => (int) $id_alert,
		)
	);

	cache_put_data('fid_unseen_alerts', null, 120);
}

/**
 * Main detection entry point. Runs at most once per configured interval,
 * evaluates every enabled category and stores deduplicated alerts. Never
 * modifies members, boards or forum settings - it only writes to
 * {db_prefix}fid_alerts and timestamps the run in modSettings.
 *
 * @return bool True when a run actually happened this request.
 */
function fid_run_alert_detection()
{
	global $modSettings, $txt;

	if ((int) fid_setting('fid_alert_enabled') !== 1)
		return false;

	$interval = max(1, (int) fid_setting('fid_alert_interval')) * 3600;
	$last_run = isset($modSettings['fid_alert_last_run']) ? (int) $modSettings['fid_alert_last_run'] : 0;
	if (time() - $last_run < $interval)
		return false;

	updateSettings(array('fid_alert_last_run' => time()));

	$checks = fid_alert_checks();
	foreach ($checks as $key => $callback)
		call_user_func($callback);

	return true;
}

/**
 * Shared window evaluation for decline detection. Returns null when the
 * minimum data requirement is not met - that keeps small and empty forums
 * free of noise.
 *
 * @param string $metric posts, topics, members or registers.
 * @return array|null
 */
function fid_decline_snapshot($metric)
{
	$window = fid_alert_windows();
	$minimum = max(5, (int) $window['days']);
	$data = fid_window_activity($window['prev_start'], $window['prev_end'], true);

	$current = 0;
	$previous = 0;

	if ($metric === 'registers')
	{
		$current = fid_registry_count($window['start'], $window['end']);
		$previous = fid_registry_count($window['prev_start'], $window['prev_end']);
	}
	elseif ($metric === 'members')
	{
		$cur = fid_window_activity($window['start'], $window['end'], true);
		$current = $cur['active_members'];
		$previous = $data['active_members'];
	}
	elseif (isset($data[$metric]))
	{
		$cur = fid_window_activity($window['start'], $window['end'], false);
		$current = $cur[$metric];
		$previous = $data[$metric];
	}

	if ($previous < $minimum)
		return null;

	return array(
		'current' => $current,
		'previous' => $previous,
		'drop' => $previous - $current,
		'percentage' => $previous > 0 ? (($previous - $current) / $previous) * 100 : 0,
		'window' => $window,
	);
}

/**
 * Sudden posting decline.
 *
 * @return bool
 */
function fid_check_decline_posts()
{
	global $txt;

	$snapshot = fid_decline_snapshot('posts');
	if ($snapshot === null || (int) fid_setting('fid_alert_threshold') <= 0
		|| $snapshot['percentage'] < (int) fid_setting('fid_alert_threshold'))
		return false;

	$percentage = round($snapshot['percentage'], 1);
	$severity = fid_alert_severity($snapshot['percentage'] / max(1, (int) fid_setting('fid_alert_threshold')));

	fid_store_alert('decline_posts_' . gmdate('YW', $snapshot['window']['start']), $severity,
		$txt['fid_alert_title_decline_posts'],
		sprintf($txt['fid_alert_message_decline_posts'], $percentage, $snapshot['previous'], $snapshot['current'], $snapshot['window']['days']),
		sprintf($txt['fid_alert_stats_decline'], $snapshot['window']['days'], $snapshot['previous'], $snapshot['current'])
	);

	return true;
}

/**
 * Sudden topic creation decline.
 *
 * @return bool
 */
function fid_check_decline_topics()
{
	global $txt;

	$snapshot = fid_decline_snapshot('topics');
	if ($snapshot === null || (int) fid_setting('fid_alert_threshold') <= 0
		|| $snapshot['percentage'] < (int) fid_setting('fid_alert_threshold'))
		return false;

	$percentage = round($snapshot['percentage'], 1);
	$severity = fid_alert_severity($snapshot['percentage'] / max(1, (int) fid_setting('fid_alert_threshold')));

	fid_store_alert('decline_topics_' . gmdate('YW', $snapshot['window']['start']), $severity,
		$txt['fid_alert_title_decline_topics'],
		sprintf($txt['fid_alert_message_decline_topics'], $percentage, $snapshot['previous'], $snapshot['current'], $snapshot['window']['days']),
		sprintf($txt['fid_alert_stats_decline'], $snapshot['window']['days'], $snapshot['previous'], $snapshot['current'])
	);

	return true;
}

/**
 * Drop in the number of distinct posting members.
 *
 * @return bool
 */
function fid_check_decline_members()
{
	global $txt;

	$snapshot = fid_decline_snapshot('members');
	if ($snapshot === null || (int) fid_setting('fid_alert_threshold') <= 0
		|| $snapshot['percentage'] < (int) fid_setting('fid_alert_threshold'))
		return false;

	$percentage = round($snapshot['percentage'], 1);
	$severity = fid_alert_severity($snapshot['percentage'] / max(1, (int) fid_setting('fid_alert_threshold')));

	fid_store_alert('decline_members_' . gmdate('YW', $snapshot['window']['start']), $severity,
		$txt['fid_alert_title_decline_members'],
		sprintf($txt['fid_alert_message_decline_members'], $percentage, $snapshot['previous'], $snapshot['current'], $snapshot['window']['days']),
		sprintf($txt['fid_alert_stats_decline'], $snapshot['window']['days'], $snapshot['previous'], $snapshot['current'])
	);

	return true;
}

/**
 * Registration decline.
 *
 * @return bool
 */
function fid_check_decline_registrations()
{
	global $txt;

	$snapshot = fid_decline_snapshot('registers');
	if ($snapshot === null || (int) fid_setting('fid_alert_threshold') <= 0
		|| $snapshot['percentage'] < (int) fid_setting('fid_alert_threshold'))
		return false;

	$percentage = round($snapshot['percentage'], 1);
	$severity = fid_alert_severity($snapshot['percentage'] / max(1, (int) fid_setting('fid_alert_threshold')));

	fid_store_alert('decline_registration_' . gmdate('YW', $snapshot['window']['start']), $severity,
		$txt['fid_alert_title_decline_registration'],
		sprintf($txt['fid_alert_message_decline_registration'], $percentage, $snapshot['previous'], $snapshot['current'], $snapshot['window']['days']),
		sprintf($txt['fid_alert_stats_decline'], $snapshot['window']['days'], $snapshot['previous'], $snapshot['current'])
	);

	return true;
}

/**
 * Unusual activity spike. Detected relative to the previous equal window,
 * above fid_alert_spike_threshold percent.
 *
 * @return bool
 */
function fid_check_spikes()
{
	global $txt;

	$window = fid_alert_windows();
	$threshold = (int) fid_setting('fid_alert_spike_threshold');
	$cur = fid_window_activity($window['start'], $window['end'], true);
	$prev = fid_window_activity($window['prev_start'], $window['prev_end'], true);

	if ($threshold <= 0 || $cur['posts'] < max(5, $window['days']) || $prev['posts'] <= 0)
		return false;

	$percentage = (($cur['posts'] - $prev['posts']) / $prev['posts']) * 100;
	if ($percentage < $threshold)
		return false;

	$decorated = $percentage / max(1, $threshold);
	$severity = fid_alert_severity($decorated, 2);

	fid_store_alert('spike_' . gmdate('YW', $window['start']), $severity,
		$txt['fid_alert_title_spikes'],
		sprintf($txt['fid_alert_message_spikes'], round($percentage, 1), $prev['posts'], $cur['posts'], $window['days']),
		sprintf($txt['fid_alert_stats_decline'], $window['days'], $prev['posts'], $cur['posts'])
	);

	return true;
}

/**
 * Potential spam pattern: an unusual share of the last 24 hours of posts
 * coming from members registered within the last 7 days. Heuristic only -
 * information is surfaced to administrators, no member is ever punished.
 *
 * @return bool
 */
function fid_check_spam()
{
	global $smcFunc, $txt;

	$now = time();
	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(COUNT(*), 0) AS recent_posts, IFNULL(SUM(mem.date_registered >= {int:new_cutoff}), 0) AS new_member_posts
		FROM {db_prefix}messages AS m
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE m.approved = {int:approved}
			AND m.poster_time >= {int:since}
			AND m.id_member > {int:zero}',
		array(
			'approved' => 1,
			'since' => $now - 86400,
			'new_cutoff' => $now - 7 * 86400,
			'zero' => 0,
		)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$recent = (int) $row['recent_posts'];
	$new_posts = (int) $row['new_member_posts'];

	if ($recent < 10)
		return false;

	$ratio = $new_posts / $recent;
	if ($ratio < 0.5)
		return false;

	$severity = $new_posts >= 20 ? 'high' : 'medium';
	fid_store_alert('spam_' . gmdate('Ymd', $now), $severity,
		$txt['fid_alert_title_spam'],
		sprintf($txt['fid_alert_message_spam'], $new_posts, $recent, round($ratio * 100)),
		sprintf($txt['fid_alert_stats_spam'], $new_posts, $recent)
	);

	return true;
}

/**
 * Boards that were active during the previous window but received no posts
 * in the current one. Only boards visible to the triggering user are
 * evaluated, because detection runs on dashboard access.
 *
 * @return bool
 */
function fid_check_inactive_boards()
{
	global $txt;

	$window = fid_alert_windows();
	$minimum = max(2, (int) $window['days'] / 2);
	$period = array(
		'start' => $window['start'],
		'end' => $window['end'],
		'prev_start' => $window['prev_start'],
		'prev_end' => $window['prev_end'],
		'days' => $window['days'],
		'label' => 'alerts',
		'is_custom' => true,
	);

	$names = array();
	foreach (fid_boards_data($period) as $board)
		if ($board['window_posts'] === 0 && $board['prev_posts'] >= $minimum)
			$names[] = htmlspecialchars($board['name'], ENT_QUOTES);

	if (empty($names))
		return false;

	fid_store_alert('inactive_boards_' . gmdate('YW', $window['start']), 'medium',
		$txt['fid_alert_title_inactive_boards'],
		sprintf($txt['fid_alert_message_inactive_boards'], count($names), $window['days']),
		implode(', ', $names)
	);

	return true;
}

/**
 * Major increase of the inactive member population. Requires a previously
 * stored baseline (fid_last_inactive_count) so that the very first run only
 * records the baseline instead of alerting.
 *
 * @return bool
 */
function fid_check_inactivity()
{
	global $smcFunc, $txt, $modSettings;

	$days = (int) fid_setting('fid_inactivity_days');
	$cutoff = time() - $days * 86400;

	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(COUNT(*), 0)
		FROM {db_prefix}members
		WHERE (last_login = {int:zero} OR last_login < {int:cutoff})',
		array('zero' => 0, 'cutoff' => $cutoff)
	);
	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	$count = (int) $count;

	$baseline = isset($modSettings['fid_last_inactive_count']) ? (int) $modSettings['fid_last_inactive_count'] : null;
	updateSettings(array('fid_last_inactive_count' => $count));

	if ($baseline === null)
		return false;

	$threshold = (int) fid_setting('fid_alert_threshold');
	if ($baseline <= 0 || $threshold <= 0)
		return false;

	$percentage = (($count - $baseline) / $baseline) * 100;
	if ($percentage < $threshold)
		return false;

	$severity = fid_alert_severity($percentage / max(1, $threshold), 2);

	fid_store_alert('inactivity_' . gmdate('Ym'), $severity,
		$txt['fid_alert_title_inactivity'],
		sprintf($txt['fid_alert_message_inactivity'], round($percentage, 1), $baseline, $count, $days),
		sprintf($txt['fid_alert_stats_inactivity'], $baseline, $count, $days)
	);

	return true;
}

/**
 * Whitelisted filter conditions for the alerts list.
 *
 * @param string $status all, new or ack.
 * @param string $severity all, low, medium, high or critical.
 * @return array(int => string) SQL fragments.
 */
function fid_alerts_conditions($status = 'all', $severity = 'all')
{
	$conditions = array();

	if (in_array($status, array('new', 'ack')))
		$conditions[] = "status = '" . $status . "'";

	if (in_array($severity, array('low', 'medium', 'high', 'critical')))
		$conditions[] = "severity = '" . $severity . "'";

	return $conditions;
}

/**
 * Whitelisted ORDER BY clause for the alerts list.
 *
 * @param string $sort Serialized sort descriptor.
 * @return string
 */
function fid_alerts_sort($sort)
{
	global $smcFunc;

	$sort = (string) $sort;
	$direction = 'DESC';
	if (substr($sort, -5) === '|down')
	{
		$direction = 'DESC';
		$sort = substr($sort, 0, -5);
	}
	elseif (substr($sort, -4) === '|asc' || substr($sort, -3) === '|up')
	{
		$direction = 'ASC';
		$sort = substr($sort, 0, -3);
	}

	$columns = array(
		'last_seen' => 'last_seen',
		'severity' => 'severity',
		'occurrences' => 'occurrences',
		'status' => 'status',
		'title' => 'title',
	);

	if (!isset($columns[$sort]))
		list ($sort, $direction) = array('last_seen', 'DESC');

	if ($sort === 'severity')
		$sql = 'FIELD(severity, {string:s1}, {string:s2}, {string:s3}, {string:s4}) ' . $direction;
	elseif ($sort === 'status')
		$sql = 'FIELD(status, {string:n1}, {string:n2}) ' . $direction;
	else
		$sql = $columns[$sort] . ' ' . $direction;

	return call_user_func_array(
		$smcFunc['db_quote'],
		array($sql, array('s1' => 'critical', 's2' => 'high', 's3' => 'medium', 's4' => 'low', 'n1' => 'new', 'n2' => 'ack'))
	);
}

/**
 * get_items callback for the admin alerts list.
 *
 * @param int $start List offset.
 * @param int $items_per_page Page size.
 * @param string $sort Serialized sort descriptor.
 * @param string $status Filter status.
 * @param string $severity Filter severity.
 * @return array[]
 */
function fid_list_alerts($start, $items_per_page, $sort, $status = 'all', $severity = 'all')
{
	global $smcFunc, $txt;

	$conditions = fid_alerts_conditions($status, $severity);
	$where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

	$request = $smcFunc['db_query']('', '
		SELECT id_alert, alert_key, severity, title, message, stats, first_seen, last_seen, occurrences, status
		FROM {db_prefix}fid_alerts
		' . $where . '
		ORDER BY ' . fid_alerts_sort($sort) . '
		LIMIT {int:start}, {int:limit}',
		array(
			'start' => (int) $start,
			'limit' => (int) $items_per_page,
		)
	);
	$rows = $smcFunc['db_fetch_all']($request);
	$smcFunc['db_free_result']($request);

	$severities = array('low' => $txt['fid_severity_low'], 'medium' => $txt['fid_severity_medium'], 'high' => $txt['fid_severity_high'], 'critical' => $txt['fid_severity_critical']);
	foreach ($rows as $key => $row)
	{
		$rows[$key]['id_alert'] = (int) $row['id_alert'];
		$rows[$key]['occurrences'] = (int) $row['occurrences'];
		$rows[$key]['first_seen'] = (int) $row['first_seen'];
		$rows[$key]['last_seen'] = (int) $row['last_seen'];
		$rows[$key]['severity_text'] = isset($severities[$row['severity']]) ? $severities[$row['severity']] : $row['severity'];
	}

	return $rows;
}

/**
 * get_count callback for the admin alerts list.
 *
 * @param string $status Filter status.
 * @param string $severity Filter severity.
 * @return int
 */
function fid_list_alerts_count($status = 'all', $severity = 'all')
{
	global $smcFunc;

	$conditions = fid_alerts_conditions($status, $severity);
	$where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(COUNT(*), 0)
		FROM {db_prefix}fid_alerts
		' . $where,
		array()
	);
	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return (int) $count;
}

/**
 * Neutralise spreadsheet formula injection: prefix a single quote in front of
 * every cell whose first character could start a formula or macro in Excel,
 * LibreOffice or Google Sheets.
 *
 * @param string $cell
 * @return string
 */
function fid_csv_safe($cell)
{
	$cell = (string) $cell;

	if (strlen($cell) > 0 && in_array(substr($cell, 0, 1), array('=', '+', '-', '@', "\t", chr(13), chr(10))))
		return "'" . $cell;

	return $cell;
}

/**
 * Write rows as UTF-8 (with BOM) CSV, applying formula injection protection.
 *
 * @param resource $handle
 * @param array[] $rows
 */
function fid_write_csv($handle, $rows)
{
	fwrite($handle, "\xEF\xBB\xBF");

	foreach ($rows as $row)
	{
		if (!is_array($row))
			$row = array($row);

		fputcsv($handle, array_map('fid_csv_safe', $row), ',', '"', '\\');
	}
}

/**
 * Build and flush a CSV download. Sends standard download headers and exits
 * because this bypasses the template layer entirely.
 *
 * @param string $filename Suggested download name (already validated).
 * @param array[] $rows Header row first, data rows after.
 */
function fid_send_csv($filename, $rows)
{
	global $context;

	$filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', (string) $filename);

	ob_end_clean();
	header('Content-Type: text/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Cache-Control: no-store, max-age=0');

	$handle = fopen('php://temp', 'r+');
	fid_write_csv($handle, $rows);
	rewind($handle);
	while (!feof($handle))
		echo fread($handle, 8192);
	fclose($handle);

	die;
}
