<?php
// Version: 2.1; ForumIntelligenceDashboard

/**
 * Forum Intelligence Dashboard - main controller and integration hooks.
 *
 * Admin analytics dashboard for SMF 2.1.8. Uses only SMF-native APIs:
 * createMenu areas (integrate_admin_areas), the native permission system
 * (integrate_load_permissions), the native settings, list and template
 * systems. No SMF core file is modified.
 *
 * @package ForumIntelligenceDashboard
 * @copyright Copyright (c) 2026 rumpa
 * @license See LICENSE.txt
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * integrate_admin_areas hook: register the dashboard inside the admin menu.
 * All subaction-level permission checks are additionally enforced in the
 * controller so that hidden menus are never the security boundary.
 *
 * @param array $admin_areas Admin menu areas (passed by reference).
 */
function fidHookAdminAreas(&$admin_areas)
{
	global $txt, $context, $scripturl;

	loadLanguage('ForumIntelligenceDashboard');

	$admin_areas['fid'] = array(
		'title' => $txt['fid_menu_title'],
		'file' => 'ForumIntelligenceDashboard.php',
		'function' => 'ForumIntelligenceDashboard',
		'permission' => array(
			'fid_view_dashboard',
			'fid_view_members',
			'fid_view_boards',
			'fid_view_growth',
			'fid_view_inactive',
			'fid_view_alerts',
			'fid_export',
			'fid_manage_settings',
			'fid_manage_alerts',
		),
		'subsections' => array(
			'overview' => array($txt['fid_section_overview'], array('fid_view_dashboard', 'fid_view_members', 'fid_view_boards', 'fid_view_growth', 'fid_view_inactive', 'fid_view_alerts', 'fid_export', 'fid_manage_settings', 'fid_manage_alerts'), true),
			'members' => array($txt['fid_section_members'], array('fid_view_members', 'fid_manage_settings')),
			'boards' => array($txt['fid_section_boards'], array('fid_view_boards', 'fid_manage_settings')),
			'growth' => array($txt['fid_section_growth'], array('fid_view_growth', 'fid_manage_settings')),
			'inactive' => array($txt['fid_section_inactive'], array('fid_view_inactive', 'fid_manage_settings')),
			'compare' => array($txt['fid_section_compare'], array('fid_view_growth', 'fid_manage_settings')),
			'alerts' => array($txt['fid_section_alerts'], array('fid_view_alerts', 'fid_manage_alerts', 'fid_manage_settings')),
			'settings' => array($txt['fid_section_settings'], array('fid_manage_settings')),
		),
	);
}

/**
 * integrate_load_permissions hook: register the nine granular permissions of
 * the mod in a dedicated permission group. By default nothing is granted to
 * non-administrator groups; explicit group configuration is required.
 *
 * @param array $permissionGroups Permission groups (by reference).
 * @param array $permissionList Permission list (by reference).
 * @param array $leftPermissionGroups Left permission groups (by reference).
 * @param array $hiddenPermissions Hidden permissions (by reference).
 * @param array $relabelPermissions Relabel permissions (by reference).
 */
function fidHookLoadPermissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$fid_permissions = array(
		'fid_view_dashboard',
		'fid_view_members',
		'fid_view_boards',
		'fid_view_growth',
		'fid_view_inactive',
		'fid_view_alerts',
		'fid_export',
		'fid_manage_settings',
		'fid_manage_alerts',
	);

	$permissionGroups['membergroup']['fid'] = true;

	foreach ($fid_permissions as $permission)
		$permissionList['membergroup'][$permission] = array(false, 'fid');
}

/**
 * integrate_load_illegal_guest_permissions hook: guests must never receive
 * any dashboard permission, whatever the group configuration looks like.
 */
function fidHookIllegalGuestPermissions()
{
	global $context;

	$context['non_guest_permissions'] = array_merge(
		(array) $context['non_guest_permissions'],
		array(
			'fid_view_dashboard',
			'fid_view_members',
			'fid_view_boards',
			'fid_view_growth',
			'fid_view_inactive',
			'fid_view_alerts',
			'fid_export',
			'fid_manage_settings',
			'fid_manage_alerts',
		)
	);
}

/**
 * integrate_admin_search hook: make the dashboard language strings and
 * settings searchable from the administration quick search.
 *
 * @param array $language_files Language files (by reference).
 * @param array $include_files Include files (by reference).
 * @param array $settings_search Settings search entries (by reference).
 */
function fidHookAdminSearch(&$language_files, &$include_files, &$settings_search)
{
	$language_files[] = 'ForumIntelligenceDashboard';
	$include_files[] = 'ForumIntelligenceDashboard';
	$settings_search[] = array('fidConfig', 'area=fid;sa=settings');
}

/**
 * Entry point of ?action=admin;area=fid. Validates the requested subaction
 * against a whitelist, enforces the matching permission and dispatches.
 */
function ForumIntelligenceDashboard()
{
	global $context, $txt, $settings, $sourcedir;

	if (!defined('FID_LOADED_SUBS'))
	{
		require_once($sourcedir . '/ForumIntelligenceDashboard.subs.php');
		define('FID_LOADED_SUBS', true);
	}

	loadLanguage('ForumIntelligenceDashboard');
	loadTemplate('ForumIntelligenceDashboard');
	fid_load_assets();

	if ((int) fid_setting('fid_enabled') !== 1 && !allowedTo('fid_manage_settings'))
		fatal_lang_error('fid_error_disabled', false);

	$subActions = array(
		'overview' => array('fidOverview', 'fid_view_dashboard'),
		'members' => array('fidMembers', 'fid_view_members'),
		'boards' => array('fidBoards', 'fid_view_boards'),
		'growth' => array('fidGrowth', 'fid_view_growth'),
		'inactive' => array('fidInactive', 'fid_view_inactive'),
		'compare' => array('fidCompare', 'fid_view_growth'),
		'alerts' => array('fidAlerts', 'fid_view_alerts'),
		'settings' => array('fidSettings', 'fid_manage_settings'),
		'export' => array('fidExport', 'fid_export'),
		'ackalert' => array('fidAcknowledgeAlert', 'fid_manage_alerts'),
	);

	$requested = isset($_REQUEST['sa']) ? (string) $_REQUEST['sa'] : 'overview';
	if (!isset($subActions[$requested]))
		$requested = 'overview';

	list ($function, $permission) = $subActions[$requested];

	isAllowedTo($permission);

	$context['page_title'] = $txt['fid_dashboard_title'];
	$context['fid_alerts_unseen'] = fid_unseen_alerts();
	$context['sub_template'] = 'fid_' . $requested;

	call_user_func($function);
}

/**
 * Registers the dashboard stylesheet and script without touching any theme
 * template. Both files are optional at runtime: the dashboard stays fully
 * usable when they are missing or blocked.
 */
function fid_load_assets()
{
	global $settings, $context;

	$css_url = $settings['default_theme_url'] . '/css/ForumIntelligenceDashboard.css';
	$js_url = $settings['default_theme_url'] . '/scripts/ForumIntelligenceDashboard.js';

	$header = '<link rel="stylesheet" href="' . $css_url . '?v1.0.0">';
	$header .= '<script defer src="' . $js_url . '?v1.0.0' . '"></script>';

	$context['html_headers'] = (isset($context['html_headers']) ? (string) $context['html_headers'] : '') . $header;
	$context['fid_period_options'] = array(7 => 'fid_period_7', 30 => 'fid_period_30', 90 => 'fid_period_90', 365 => 'fid_period_365');
}

/**
 * Builds the $config_vars array for the settings screen. Also used by the
 * admin search integration (fidHookAdminSearch) via fidConfig(true).
 *
 * @param bool $return_config When true, return the configuration variables.
 * @return void|array
 */
function fidConfig($return_config = false)
{
	global $txt;

	$config_vars = array(
		array('title', 'fid_settings_general', 'label' => $txt['fid_settings_general']),
		array('check', 'fid_enabled', 'label' => $txt['fid_setting_fid_enabled'], 'desc' => $txt['fid_setting_fid_enabled_desc']),
		array('select', 'fid_default_period', array(7 => $txt['fid_period_7'], 30 => $txt['fid_period_30'], 90 => $txt['fid_period_90'], 365 => $txt['fid_period_365']), 'label' => $txt['fid_setting_fid_default_period']),
		array('int', 'fid_cache_ttl', 'label' => $txt['fid_setting_fid_cache_ttl'], 'desc' => $txt['fid_setting_fid_cache_ttl_desc'], 'min' => 0, 'max' => 86400, 'step' => 10),
		array('int', 'fid_items_per_page', 'label' => $txt['fid_setting_fid_items_per_page'], 'min' => 5, 'max' => 100),
		array('check', 'fid_allow_csv', 'label' => $txt['fid_setting_fid_allow_csv'], 'desc' => $txt['fid_setting_fid_allow_csv_desc']),
		array('check', 'fid_charts', 'label' => $txt['fid_setting_fid_charts']),
		array('select', 'fid_chart_style', array('bar' => $txt['fid_chart_bar'], 'line' => $txt['fid_chart_line']), 'label' => $txt['fid_setting_fid_chart_style']),

		array('title', 'fid_settings_activity_score', 'label' => $txt['fid_settings_activity_score']),
		array('int', 'fid_weight_recent_post', 'label' => $txt['fid_setting_fid_weight_recent_post'], 'desc' => $txt['fid_activity_score_formula'], 'min' => 0, 'max' => 100),
		array('int', 'fid_weight_topics', 'label' => $txt['fid_setting_fid_weight_topics'], 'min' => 0, 'max' => 100),
		array('int', 'fid_weight_login', 'label' => $txt['fid_setting_fid_weight_login'], 'min' => 0, 'max' => 100),
		array('int', 'fid_weight_consistency', 'label' => $txt['fid_setting_fid_weight_consistency'], 'min' => 0, 'max' => 100),

		array('title', 'fid_settings_reports', 'label' => $txt['fid_settings_reports']),
		array('int', 'fid_inactivity_days', 'label' => $txt['fid_setting_fid_inactivity_days'], 'min' => 1, 'max' => 3650),
		array('int', 'fid_board_active_days', 'label' => $txt['fid_setting_fid_board_active_days'], 'min' => 1, 'max' => 1000),
		array('int', 'fid_board_quiet_days', 'label' => $txt['fid_setting_fid_board_quiet_days'], 'min' => 1, 'max' => 1000),

		array('title', 'fid_settings_alerts', 'label' => $txt['fid_settings_alerts']),
		array('check', 'fid_alert_enabled', 'label' => $txt['fid_setting_fid_alert_enabled'], 'desc' => $txt['fid_setting_fid_alert_enabled_desc']),
		array('check', 'fid_alert_decline_posts', 'label' => $txt['fid_alert_cat_decline_posts']),
		array('check', 'fid_alert_decline_topics', 'label' => $txt['fid_alert_cat_decline_topics']),
		array('check', 'fid_alert_decline_members', 'label' => $txt['fid_alert_cat_decline_members']),
		array('check', 'fid_alert_decline_registration', 'label' => $txt['fid_alert_cat_decline_registration']),
		array('check', 'fid_alert_spikes', 'label' => $txt['fid_alert_cat_spikes']),
		array('check', 'fid_alert_spam', 'label' => $txt['fid_alert_cat_spam']),
		array('check', 'fid_alert_inactive_boards', 'label' => $txt['fid_alert_cat_inactive_boards']),
		array('check', 'fid_alert_inactivity', 'label' => $txt['fid_alert_cat_inactivity']),
		array('int', 'fid_alert_threshold', 'label' => $txt['fid_setting_fid_alert_threshold'], 'desc' => $txt['fid_setting_fid_alert_threshold_desc'], 'min' => 1, 'max' => 99),
		array('int', 'fid_alert_spike_threshold', 'label' => $txt['fid_setting_fid_alert_spike_threshold'], 'min' => 100, 'max' => 10000),
		array('int', 'fid_alert_min_days', 'label' => $txt['fid_setting_fid_alert_min_days'], 'desc' => $txt['fid_setting_fid_alert_min_days_desc'], 'min' => 1, 'max' => 365),
		array('int', 'fid_alert_interval', 'label' => $txt['fid_setting_fid_alert_interval'], 'min' => 1, 'max' => 168),
		array('int', 'fid_alert_cooldown', 'label' => $txt['fid_setting_fid_alert_cooldown'], 'min' => 1, 'max' => 720),
	);

	return $config_vars;
}

/**
 * Settings screen (?action=admin;area=fid;sa=settings). Saving uses SMF's
 * native database settings API; every value is afterwards re-validated and
 * clamped to its allowed range.
 */
function fidSettings()
{
	global $context, $sourcedir;

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = fidConfig();

	if (isset($_REQUEST['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		fid_sanitize_settings();
		$_SESSION['adm-save'] = true;
		redirectexit('action=admin;area=fid;sa=settings');
	}

	prepareDBSettingContext($config_vars);
}

/**
 * Re-validates every fid_ setting after a save and clamps invalid values to
 * their documented bounds. This protects against direct SQL tampering and
 * against manipulated form submissions.
 */
function fid_sanitize_settings()
{
	global $modSettings;

	$rules = array(
		'fid_enabled' => array(0, 1),
		'fid_default_period' => array(7, 365),
		'fid_cache_ttl' => array(0, 86400),
		'fid_items_per_page' => array(5, 100),
		'fid_allow_csv' => array(0, 1),
		'fid_charts' => array(0, 1),
		'fid_weight_recent_post' => array(0, 100),
		'fid_weight_topics' => array(0, 100),
		'fid_weight_login' => array(0, 100),
		'fid_weight_consistency' => array(0, 100),
		'fid_inactivity_days' => array(1, 3650),
		'fid_board_active_days' => array(1, 1000),
		'fid_board_quiet_days' => array(1, 1000),
		'fid_alert_enabled' => array(0, 1),
		'fid_alert_threshold' => array(1, 99),
		'fid_alert_spike_threshold' => array(100, 10000),
		'fid_alert_min_days' => array(1, 365),
		'fid_alert_interval' => array(1, 168),
		'fid_alert_cooldown' => array(1, 720),
	);

	$fixed = array();
	foreach ($rules as $name => $bounds)
	{
		if (!isset($modSettings[$name]))
			continue;

		$value = (int) $modSettings[$name];
		if ($value < $bounds[0])
			$value = $bounds[0];
		elseif ($value > $bounds[1])
			$value = $bounds[1];

		if ((string) $value !== (string) $modSettings[$name])
			$fixed[$name] = $value;
	}

	foreach (array('fid_default_period' => array(7, 30, 90, 365), 'fid_chart_style' => array('bar', 'line')) as $name => $allowed)
	{
		if (isset($modSettings[$name]) && !in_array($modSettings[$name], array_map('strval', $allowed)))
			$fixed[$name] = $name === 'fid_default_period' ? 30 : 'bar';
	}

	if (!empty($fixed))
		updateSettings($fixed);

	cache_put_data('fid_unseen_alerts', null, 120);
}

/**
 * Overview subaction. Aggregated, cached real statistics for the selected
 * period plus short-period slices. Alert detection is evaluated here because
 * the dashboard visit is the documented trigger for the internal alert
 * system.
 */
function fidOverview()
{
	global $context;

	if ((int) fid_setting('fid_alert_enabled') === 1)
		fid_run_alert_detection();

	$period = fid_period();
	$overview = fid_overview_stats($period);

	$context['fid_period'] = $period;
	$context['fid_overview'] = $overview;
	$context['fid_slices'] = fid_day_slices();
	$context['fid_alerts_unseen'] = fid_unseen_alerts();
	$context['fid_changes'] = array(
		'posts' => fid_percent_change($overview['posts'], $overview['prev_posts']),
		'topics' => fid_percent_change($overview['topics'], $overview['prev_topics']),
		'registers' => fid_percent_change($overview['reg_period'], $overview['reg_prev']),
		'active_members' => fid_percent_change($overview['active_members'], $overview['prev_active_members']),
	);
	$total_members = max(1, $overview['total_members']);
	$context['fid_activity_rate'] = round(($overview['active_members'] / $total_members) * 100, 1);
	$context['fid_activity_rate_air'] = $total_members > 0 ? true : false;
	$context['fid_alerts_last_run'] = fid_last_alert_run();
}

/**
 * Member intelligence subaction: configurable Top N ranking (real activity)
 * plus a paginated, searchable member list.
 */
function fidMembers()
{
	global $context, $txt, $scripturl, $smcFunc;

	$period = fid_period();
	$context['fid_period'] = $period;

	$top = isset($_REQUEST['top']) ? (int) $_REQUEST['top'] : 10;
	if (!in_array($top, array(10, 25, 50), true))
		$top = 10;
	$context['fid_top_n'] = $top;

	$search = isset($_REQUEST['fid_search']) && is_string($_REQUEST['fid_search']) ? trim((string) $_REQUEST['fid_search']) : '';
	$activity = isset($_REQUEST['fid_activity']) && in_array($_REQUEST['fid_activity'], array('all', 'active', 'silent'), true) ? $_REQUEST['fid_activity'] : 'all';

	$context['fid_search'] = $search;
	$context['fid_activity'] = $activity;

	$context['fid_top_members'] = array();
	$ranked_ids = fid_ranked_member_ids($period['start'], $period['end'], $top);
	if (!empty($ranked_ids))
	{
		$window = fid_member_window_stats($ranked_ids, $period['start'], $period['end']);
		$weights = fid_score_weights();

		$request = $smcFunc['db_query']('', '
			SELECT m.id_member, m.real_name, m.member_name, m.date_registered, m.last_login, m.posts, m.is_activated
			FROM {db_prefix}members AS m
			WHERE m.id_member IN ({array_int:ids})',
			array('ids' => $ranked_ids)
		);
		$rows = $smcFunc['db_fetch_all']($request);
		$smcFunc['db_free_result']($request);

		$order = array_flip($ranked_ids);
		usort($rows, function($a, $b) use ($order) {
			return $order[$a['id_member']] - $order[$b['id_member']];
		});

		$rank = 0;
		foreach ($rows as $row)
		{
			$id = (int) $row['id_member'];
			$wins = isset($window[$id]) ? $window[$id] : array('posts' => 0, 'topics' => 0, 'active_days' => 0, 'last_post' => 0);
			$rank++;
			$context['fid_top_members'][] = array(
				'rank' => $rank,
				'id_member' => $id,
				'name' => $row['real_name'] != '' ? $row['real_name'] : $row['member_name'],
				'href' => $scripturl . '?action=profile;u=' . $id,
				'posts' => (int) $row['posts'],
				'topics' => $wins['topics'],
				'window_posts' => $wins['posts'],
				'active_days' => $wins['active_days'],
				'date_registered' => (int) $row['date_registered'],
				'last_login' => (int) $row['last_login'],
				'last_post' => $wins['last_post'],
				'score' => fid_member_activity_score(array(
					'window_posts' => $wins['posts'],
					'window_topics' => $wins['topics'],
					'last_login' => (int) $row['last_login'],
					'active_days' => $wins['active_days'],
					'days' => $period['days'],
					'weights' => $weights,
				)),
				'is_activated' => (int) $row['is_activated'],
			);
		}
	}

	$listOptions = array(
		'id' => 'fid_member_list',
		'title' => $txt['fid_member_list_title'],
		'base_href' => $scripturl . '?action=admin;area=fid;sa=members;period=' . (int) $period['label'] . ';fid_activity=' . $activity,
		'get_count' => array(
			'function' => 'fid_list_members_count',
			'params' => array($search, $activity),
		),
		'get_items' => array(
			'function' => 'fid_list_members',
			'params' => array($period, $search, $activity),
		),
		'items_per_page' => (int) fid_setting('fid_items_per_page'),
		'default_sort_col' => 'posts',
		'no_items_label' => $txt['fid_no_members'],
		'columns' => array(
			'name' => array(
				'header' => array('value' => $txt['fid_col_member_name']),
				'data' => array(
					'function' => function($row) {
						return '<a href="' . $row['href'] . '">' . $row['name_html'] . '</a>';
					},
				),
				'sort' => array(
					'default' => 'm.real_name ASC',
					'reverse' => 'm.real_name DESC',
				),
			),
			'posts' => array(
				'header' => array('value' => $txt['fid_col_total_posts']),
				'data' => array(
					'function' => function($row) {
						return $row['posts'];
					},
				),
				'sort' => array(
					'default' => 'm.posts DESC',
				),
			),
			'window_posts' => array(
				'header' => array('value' => $txt['fid_col_period_posts']),
				'data' => array(
					'function' => function($row) {
						return $row['window_posts'];
					},
				),
			),
			'window_topics' => array(
				'header' => array('value' => $txt['fid_col_period_topics']),
				'data' => array(
					'function' => function($row) {
						return $row['window_topics'];
					},
				),
			),
			'last_login' => array(
				'header' => array('value' => $txt['fid_col_last_login']),
				'data' => array(
					'function' => function($row) {
						return $row['last_login_html'];
					},
				),
				'sort' => array(
					'default' => 'm.last_login DESC',
					'reverse' => 'm.last_login ASC',
				),
			),
			'date_registered' => array(
				'header' => array('value' => $txt['fid_col_registered']),
				'data' => array(
					'function' => function($row) {
						return $row['registered_html'];
					},
				),
				'sort' => array(
					'default' => 'm.date_registered DESC',
					'reverse' => 'm.date_registered ASC',
				),
			),
			'score' => array(
				'header' => array('value' => $txt['fid_col_activity_score']),
				'data' => array(
					'function' => function($row) {
						return $row['score'];
					},
				),
			),
		),
	);

	require_once($GLOBALS['sourcedir'] . '/Subs-List.php');
	createList($listOptions);
}

/**
 * Board intelligence subaction. Board rows are built by SMF's own
 * query_see_board visibility rules, so private boards are never exposed.
 */
function fidBoards()
{
	global $context, $txt, $scripturl;

	$period = fid_period();
	$context['fid_period'] = $period;

	$listOptions = array(
		'id' => 'fid_board_list',
		'title' => $txt['fid_board_list_title'],
		'base_href' => $scripturl . '?action=admin;area=fid;sa=boards;period=' . (int) $period['label'],
		'get_count' => array(
			'function' => 'fid_list_boards_count',
			'params' => array($period),
		),
		'get_items' => array(
			'function' => 'fid_list_boards',
			'params' => array($period),
		),
		'items_per_page' => (int) fid_setting('fid_items_per_page'),
		'default_sort_col' => 'window_posts',
		'no_items_label' => $txt['fid_no_boards'],
		'columns' => array(
			'name' => array(
				'header' => array('value' => $txt['fid_col_board_name']),
				'data' => array(
					'function' => function($row) {
						return '<a href="' . $row['href'] . '">' . $row['name_html'] . '</a>';
					},
				),
			),
			'category' => array(
				'header' => array('value' => $txt['fid_col_category']),
				'data' => array(
					'function' => function($row) {
						return $row['cat_html'];
					},
				),
			),
			'num_topics' => array(
				'header' => array('value' => $txt['fid_col_total_topics']),
				'data' => array(
					'function' => function($row) {
						return $row['num_topics'];
					},
				),
			),
			'num_posts' => array(
				'header' => array('value' => $txt['fid_col_total_posts']),
				'data' => array(
					'function' => function($row) {
						return $row['num_posts'];
					},
				),
			),
			'window_posts' => array(
				'header' => array('value' => $txt['fid_col_period_posts']),
				'data' => array(
					'function' => function($row) {
						return $row['window_posts'];
					},
				),
			),
			'window_topics' => array(
				'header' => array('value' => $txt['fid_col_period_topics']),
				'data' => array(
					'function' => function($row) {
						return $row['window_topics'];
					},
				),
			),
			'growth' => array(
				'header' => array('value' => $txt['fid_col_growth']),
				'data' => array(
					'function' => function($row) {
						return $row['growth_html'];
					},
				),
			),
			'status' => array(
				'header' => array('value' => $txt['fid_col_status']),
				'data' => array(
					'function' => function($row) {
						return $row['status_html'];
					},
				),
			),
		),
	);

	require_once($GLOBALS['sourcedir'] . '/Subs-List.php');
	createList($listOptions);
}

/**
 * Growth analytics subaction: daily rows plus weekly and monthly
 * aggregations for the selected range, all derived from real data.
 */
function fidGrowth()
{
	global $context, $txt;

	$period = fid_period();
	$context['fid_period'] = $period;

	$daily = fid_daily_stats($period['start'], $period['end']);
	$context['fid_daily'] = $daily;
	$context['fid_weekly'] = fid_weekly_stats($daily);
	$context['fid_monthly'] = fid_monthly_stats($daily);

	$series = array();
	$dates = array();
	$posts = array();
	$topics = array();
	$registers = array();
	$active = array();
	foreach ($daily as $row)
	{
		$dates[] = $row['date'];
		$posts[] = $row['posts'];
		$topics[] = $row['topics'];
		$registers[] = $row['registers'];
		$active[] = $row['active_members'];
	}
	$series[] = array('key' => 'posts', 'label' => $txt['fid_metric_posts'], 'values' => $posts);
	$series[] = array('key' => 'topics', 'label' => $txt['fid_metric_topics'], 'values' => $topics);
	$series[] = array('key' => 'registers', 'label' => $txt['fid_metric_registrations'], 'values' => $registers);
	$series[] = array('key' => 'active_members', 'label' => $txt['fid_metric_active_members'], 'values' => $active);
	$context['fid_chart'] = array(
		'enabled' => (int) fid_setting('fid_charts') === 1,
		'style' => fid_setting('fid_chart_style') === 'line' ? 'line' : 'bar',
		'labels' => $dates,
		'series' => $series,
	);
}

/**
 * Inactive member report subaction: whitelist-validated threshold and
 * category filters with search, sorting, pagination and category counts.
 */
function fidInactive()
{
	global $context, $txt, $scripturl;

	$allowed_days = array(30, 60, 90, 180, 365);
	$threshold = isset($_REQUEST['fid_days']) ? (int) $_REQUEST['fid_days'] : (int) fid_setting('fid_inactivity_days');
	if (in_array($threshold, $allowed_days, true))
		$custom = false;
	else
	{
		$threshold = max(1, min(3650, $threshold));
		$custom = true;
	}

	$category = isset($_REQUEST['fid_category']) && in_array($_REQUEST['fid_category'], array('all', 'never_login', 'never_post', 'was_active', 'long_term'), true)
		? $_REQUEST['fid_category'] : 'all';
	$search = isset($_REQUEST['fid_search']) && is_string($_REQUEST['fid_search']) ? trim((string) $_REQUEST['fid_search']) : '';

	$context['fid_threshold'] = $threshold;
	$context['fid_category'] = $category;
	$context['fid_search'] = $search;
	$context['fid_categories'] = array('all', 'never_login', 'never_post', 'was_active', 'long_term');

	$counts = array();
	foreach ($context['fid_categories'] as $cat)
		$counts[$cat] = fid_list_inactive_count($threshold, $cat, $search);
	$context['fid_category_counts'] = $counts;

	$listOptions = array(
		'id' => 'fid_inactive_list',
		'title' => $txt['fid_inactive_list_title'],
		'base_href' => $scripturl . '?action=admin;area=fid;sa=inactive;fid_days=' . (int) $threshold . ';fid_category=' . $category . ($search !== '' ? ';fid_search=' . urlencode($search) : ''),
		'get_count' => array(
			'function' => 'fid_list_inactive_count',
			'params' => array($threshold, $category, $search),
		),
		'get_items' => array(
			'function' => 'fid_list_inactive',
			'params' => array($threshold, $category, $search),
		),
		'items_per_page' => (int) fid_setting('fid_items_per_page'),
		'default_sort_col' => 'last_login',
		'default_sort_dir' => 'desc',
		'no_items_label' => $txt['fid_no_inactive'],
		'columns' => array(
			'name' => array(
				'header' => array('value' => $txt['fid_col_member_name']),
				'data' => array(
					'function' => function($row) {
						return '<a href="' . $row['href'] . '">' . $row['name_html'] . '</a>';
					},
				),
			),
			'date_registered' => array(
				'header' => array('value' => $txt['fid_col_registered']),
				'data' => array(
					'function' => function($row) {
						return $row['registered_html'];
					},
				),
			),
			'last_login' => array(
				'header' => array('value' => $txt['fid_col_last_login']),
				'data' => array(
					'function' => function($row) {
						return $row['last_login_html'];
					},
				),
			),
			'last_post' => array(
				'header' => array('value' => $txt['fid_col_last_post']),
				'data' => array(
					'function' => function($row) {
						return $row['last_post_html'];
					},
				),
			),
			'posts' => array(
				'header' => array('value' => $txt['fid_col_total_posts']),
				'data' => array(
					'function' => function($row) {
						return $row['posts'];
					},
				),
			),
			'inactive' => array(
				'header' => array('value' => $txt['fid_col_inactive_duration']),
				'data' => array(
					'function' => function($row) {
						return $row['inactive_html'];
					},
				),
			),
			'category' => array(
				'header' => array('value' => $txt['fid_col_category']),
				'data' => array(
					'function' => function($row) {
						return $row['category_html'];
					},
				),
			),
		),
	);

	require_once($GLOBALS['sourcedir'] . '/Subs-List.php');
	createList($listOptions);
}

/**
 * Activity comparison subaction: today/week/month and custom range pairs
 * with safe zero handling via fid_percent_change().
 */
function fidCompare()
{
	global $context;

	$period = fid_period();
	$context['fid_period'] = $period;
	$context['fid_compare'] = fid_comparison_pairs($period);
	$context['fid_slices'] = fid_day_slices();
}

/**
 * Admin alerts subaction. Runs detection (interval gated), then lists the
 * stored alerts with status and severity filters. Acknowledging is a separate
 * subaction with its own session check.
 */
function fidAlerts()
{
	global $context, $txt, $scripturl;

	if ((int) fid_setting('fid_alert_enabled') === 1)
		fid_run_alert_detection();

	$status = isset($_REQUEST['fid_status']) && in_array($_REQUEST['fid_status'], array('all', 'new', 'ack'), true)
		? (string) $_REQUEST['fid_status'] : 'all';
	$severity = isset($_REQUEST['fid_severity']) && in_array($_REQUEST['fid_severity'], array('all', 'low', 'medium', 'high', 'critical'), true)
		? (string) $_REQUEST['fid_severity'] : 'all';

	$context['fid_alert_status'] = $status;
	$context['fid_alert_severity'] = $severity;
	$context['fid_alerts_unseen'] = fid_unseen_alerts();
	$context['fid_alerts_last_run'] = fid_last_alert_run();

	$listOptions = array(
		'id' => 'fid_alert_list',
		'title' => $txt['fid_alert_list_title'],
		'base_href' => $scripturl . '?action=admin;area=fid;sa=alerts;fid_status=' . $status . ';fid_severity=' . $severity,
		'get_count' => array(
			'function' => 'fid_list_alerts_count',
			'params' => array($status, $severity),
		),
		'get_items' => array(
			'function' => 'fid_list_alerts',
			'params' => array($status, $severity),
		),
		'items_per_page' => (int) fid_setting('fid_items_per_page'),
		'default_sort_col' => 'last_seen',
		'no_items_label' => $txt['fid_no_alerts'],
		'columns' => array(
			'severity' => array(
				'header' => array('value' => $txt['fid_col_severity']),
				'data' => array(
					'function' => function($row) {
						return '<span class="fid-badge fid-severity-' . htmlspecialchars((string) $row['severity'], ENT_QUOTES) . '">' . $row['severity_text'] . '</span>';
					},
				),
			),
			'title' => array(
				'header' => array('value' => $txt['fid_col_title']),
				'data' => array(
					'function' => function($row) {
						return '<strong>' . $row['title'] . '</strong><br><span class="fid-alert-message">'
							. $row['message'] . '</span><br><span class="fid-alert-stats">' . $row['stats'] . '</span>';
					},
				),
			),
			'occurrences' => array(
				'header' => array('value' => $txt['fid_col_occurrences']),
				'data' => array(
					'function' => function($row) {
						return $row['occurrences'];
					},
				),
			),
			'last_seen' => array(
				'header' => array('value' => $txt['fid_col_last_seen']),
				'data' => array(
					'function' => function($row) {
						return fid_time_html($row['last_seen']);
					},
				),
			),
			'status' => array(
				'header' => array('value' => $txt['fid_col_alert_status']),
				'data' => array(
					'function' => function($row) {
						global $scripturl, $context, $txt;

						$label = $row['status'] === 'new' ? $txt['fid_alert_status_new'] : $txt['fid_alert_status_ack'];
						$html = '<span class="fid-badge fid-status-' . $row['status'] . '">' . $label . '</span>';
						if ($row['status'] === 'new')
							$html .= ' <a class="fid-ack" href="' . $scripturl . '?action=admin;area=fid;sa=ackalert;aid='
								. (int) $row['id_alert'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '"'
								. ' data-confirm="' . $txt['fid_alert_ack_confirm'] . '">' . $txt['fid_alert_ack'] . '</a>';

						return $html;
					},
				),
			),
		),
	);

	require_once($GLOBALS['sourcedir'] . '/Subs-List.php');
	createList($listOptions);
}

/**
 * Acknowledges one alert. GET request, therefore validated against the
 * session variable; only the mod's own alert rows are touched.
 */
function fidAcknowledgeAlert()
{
	checkSession('get');

	$id_alert = isset($_REQUEST['aid']) ? (int) $_REQUEST['aid'] : 0;
	if ($id_alert > 0)
		fid_ack_alert($id_alert);

	redirectexit('action=admin;area=fid;sa=alerts');
}

/**
 * CSV export router (?action=admin;area=fid;sa=export;type=...). Enforces the
 * fid_export permission (dispatcher), the fid_allow_csv setting, and the
 * session check for state-changing GET requests. Exported data mirrors the
 * currently selected filters.
 */
function fidExport()
{
	global $txt;

	if ((int) fid_setting('fid_allow_csv') !== 1)
		fatal_lang_error('fid_error_csv_disabled', false);

	checkSession('get');

	$types = array('members', 'inactive', 'boards', 'daily', 'monthly');
	$type = isset($_REQUEST['type']) && in_array((string) $_REQUEST['type'], $types, true) ? (string) $_REQUEST['type'] : 'members';

	$period = fid_period();

	if ($type === 'members')
		$rows = fid_export_members($period);
	elseif ($type === 'inactive')
		$rows = fid_export_inactive($period);
	elseif ($type === 'boards')
		$rows = fid_export_boards($period);
	elseif ($type === 'daily')
		$rows = fid_export_daily($period);
	else
		$rows = fid_export_monthly($period);

	if (empty($rows))
		fatal_lang_error('fid_error_csv_empty', false);

	fid_send_csv('fid_' . $type . '_' . gmdate('Ymd', time()) . '.csv', $rows);
}

/**
 * Collects every matching row of a paginated list into one array for CSV
 * export, in chunks. A hard cap keeps even pathologically configured exports
 * from running unbounded.
 *
 * @param callable $callback fid_list_* style callback.
 * @param array $params Extra callback parameters.
 * @param int $count Total items.
 * @param int $cap Hard row cap.
 * @return array[]
 */
function fid_export_collect($callback, $params, $count, $cap = 50000)
{
	$rows = array();
	$chunk = 1000;
	$start = 0;

	while ($start < $count && $start < $cap)
	{
		$batch = call_user_func_array($callback, array_merge(array($start, $chunk), $params));
		if (empty($batch))
			break;

		$rows = array_merge($rows, $batch);
		$start += $chunk;
	}

	return $rows;
}

/**
 * Member activity export rows (filtered like the member intelligence list).
 *
 * @param array $period Period array.
 * @return array[]
 */
function fid_export_members($period)
{
	global $txt;

	$search = isset($_REQUEST['fid_search']) && is_string($_REQUEST['fid_search']) ? trim((string) $_REQUEST['fid_search']) : '';
	$activity = isset($_REQUEST['fid_activity']) && in_array($_REQUEST['fid_activity'], array('all', 'active', 'silent'), true) ? (string) $_REQUEST['fid_activity'] : 'all';

	$count = fid_list_members_count($search, $activity, $period);
	if ($count === 0)
		return array();

	$members = fid_export_collect('fid_list_members', array($period, $search, $activity), $count);

	$rows = array(array(
		$txt['fid_export_col_member_name'],
		$txt['fid_export_col_total_posts'],
		$txt['fid_export_col_period_posts'],
		$txt['fid_export_col_period_topics'],
		$txt['fid_export_col_registered'],
		$txt['fid_export_col_last_login'],
		$txt['fid_export_col_last_post'],
		$txt['fid_export_col_activity_score'],
	));

	foreach ($members as $member)
		$rows[] = array(
			$member['name'],
			(int) $member['posts'],
			(int) $member['window_posts'],
			(int) $member['window_topics'],
			date('Y-m-d H:i:s', (int) $member['date_registered']),
			$member['last_login'] > 0 ? date('Y-m-d H:i:s', (int) $member['last_login']) : '',
			$member['last_post_time'] > 0 ? date('Y-m-d H:i:s', (int) $member['last_post_time']) : '',
			(int) $member['activity_score'],
		);

	return $rows;
}

/**
 * Inactive members export rows (filtered like the inactive member report).
 *
 * @param array $period Period array.
 * @return array[]
 */
function fid_export_inactive($period)
{
	global $txt;

	$allowed_days = array(30, 60, 90, 180, 365);
	$threshold = isset($_REQUEST['fid_days']) ? (int) $_REQUEST['fid_days'] : (int) fid_setting('fid_inactivity_days');
	if (!in_array($threshold, $allowed_days, true))
		$threshold = max(1, min(3650, $threshold));

	$category = isset($_REQUEST['fid_category']) && in_array($_REQUEST['fid_category'], array('all', 'never_login', 'never_post', 'was_active', 'long_term'), true)
		? (string) $_REQUEST['fid_category'] : 'all';
	$search = isset($_REQUEST['fid_search']) && is_string($_REQUEST['fid_search']) ? trim((string) $_REQUEST['fid_search']) : '';

	$count = fid_list_inactive_count($threshold, $category, $search);
	if ($count === 0)
		return array();

	$members = fid_export_collect('fid_list_inactive', array($threshold, $category, $search), $count);

	$rows = array(array(
		$txt['fid_export_col_member_name'],
		$txt['fid_export_col_registered'],
		$txt['fid_export_col_last_login'],
		$txt['fid_export_col_last_post'],
		$txt['fid_export_col_total_posts'],
		$txt['fid_export_col_inactive_duration'],
		$txt['fid_export_col_category'],
	));

	foreach ($members as $member)
		$rows[] = array(
			$member['name'],
			date('Y-m-d H:i:s', (int) $member['date_registered']),
			$member['last_login'] > 0 ? date('Y-m-d H:i:s', (int) $member['last_login']) : '',
			$member['last_post_time'] > 0 ? date('Y-m-d H:i:s', (int) $member['last_post_time']) : '',
			(int) $member['posts'],
			sprintf($txt['fid_days_ago'], $member['inactive_days_current']),
			$txt['fid_category_' . $member['status_key']],
		);

	return $rows;
}

/**
 * Board analytics export rows. Only boards the requesting user may see are
 * included.
 *
 * @param array $period Period array.
 * @return array[]
 */
function fid_export_boards($period)
{
	global $txt;

	$boards = fid_boards_data($period);
	if (empty($boards))
		return array();

	$rows = array(array(
		$txt['fid_export_col_board_name'],
		$txt['fid_export_col_category'],
		$txt['fid_export_col_total_posts'],
		$txt['fid_export_col_total_topics'],
		$txt['fid_export_col_period_posts'],
		$txt['fid_export_col_period_topics'],
		$txt['fid_export_col_growth'],
		$txt['fid_export_col_status'],
	));

	foreach ($boards as $board)
		$rows[] = array(
			$board['name'],
			$board['cat_name'],
			(int) $board['num_posts'],
			(int) $board['num_topics'],
			(int) $board['window_posts'],
			(int) $board['window_topics'],
			$board['growth'] === null || $board['growth'] === false ? '' : round((float) $board['growth'], 1) . '%',
			$txt['fid_board_status_' . $board['activity_status']],
		);

	return $rows;
}

/**
 * Daily statistics export rows.
 *
 * @param array $period Period array.
 * @return array[]
 */
function fid_export_daily($period)
{
	global $txt;

	$daily = fid_daily_stats($period['start'], $period['end']);
	if (empty($daily))
		return array();

	$rows = array(array(
		$txt['fid_export_col_date'],
		$txt['fid_metric_posts'],
		$txt['fid_metric_topics'],
		$txt['fid_metric_registrations'],
		$txt['fid_metric_active_members'],
	));

	foreach ($daily as $row)
		$rows[] = array(
			(string) $row['date'],
			(int) $row['posts'],
			(int) $row['topics'],
			(int) $row['registers'],
			(int) $row['active_members'],
		);

	return $rows;
}

/**
 * Monthly statistics export rows (aggregated from the same real data).
 *
 * @param array $period Period array.
 * @return array[]
 */
function fid_export_monthly($period)
{
	global $txt;

	$monthly = fid_monthly_stats(fid_daily_stats($period['start'], $period['end']));
	if (empty($monthly))
		return array();

	$rows = array(array(
		$txt['fid_export_col_month'],
		$txt['fid_metric_posts'],
		$txt['fid_metric_topics'],
		$txt['fid_metric_registrations'],
		$txt['fid_metric_active_members'],
		$txt['fid_export_col_growth'],
	));

	$previous = null;
	foreach ($monthly as $row)
	{
		$growth = $previous !== null ? fid_percent_change($row['posts'], $previous) : null;
		$rows[] = array(
			(string) $row['month'],
			(int) $row['posts'],
			(int) $row['topics'],
			(int) $row['registers'],
			(int) $row['active_members'],
			$growth === null ? '' : round((float) $growth, 1) . '%',
		);
		$previous = $row['posts'];
	}

	return $rows;
}

/**
 * Unix timestamp of the last alert detection run, or 0 when it never ran.
 *
 * @return int
 */
function fid_last_alert_run()
{
	global $modSettings;

	return isset($modSettings['fid_alert_last_run']) ? (int) $modSettings['fid_alert_last_run'] : 0;
}
