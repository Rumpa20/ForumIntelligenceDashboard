<?php
// Version: 2.1; ForumIntelligenceDashboard

/**
 * Forum Intelligence Dashboard - English language strings.
 *
 * @package ForumIntelligenceDashboard
 * @copyright Copyright (c) 2026 rumpa
 * @license See LICENSE.txt
 */

global $txt;

// Menu and section titles.
$txt['fid_menu_title'] = 'Forum Intelligence';
$txt['fid_dashboard_title'] = 'Forum Intelligence Dashboard';
$txt['fid_section_overview'] = 'Overview';
$txt['fid_section_members'] = 'Member Intelligence';
$txt['fid_section_boards'] = 'Board Intelligence';
$txt['fid_section_growth'] = 'Growth Analytics';
$txt['fid_section_inactive'] = 'Inactive Members';
$txt['fid_section_compare'] = 'Activity Comparison';
$txt['fid_section_alerts'] = 'Admin Alerts';
$txt['fid_section_settings'] = 'Dashboard Settings';

// Errors.
$txt['fid_error_disabled'] = 'The Forum Intelligence Dashboard is currently disabled. Ask an administrator to enable it in the dashboard settings.';
$txt['fid_error_csv_disabled'] = 'CSV export is disabled in the Forum Intelligence Dashboard settings.';
$txt['fid_error_csv_empty'] = 'There is no data to export for the current filters. Adjust the filters and try again.';

// Period selector and filters.
$txt['fid_filter_period'] = 'Period:';
$txt['fid_filter_from'] = 'From:';
$txt['fid_filter_to'] = 'To:';
$txt['fid_filter_apply'] = 'Apply';
$txt['fid_filter_search'] = 'Search:';
$txt['fid_filter_search_placeholder'] = 'Member name';
$txt['fid_filter_activity'] = 'Activity:';
$txt['fid_filter_activity_all'] = 'All members';
$txt['fid_filter_activity_active'] = 'Posted in period';
$txt['fid_filter_activity_silent'] = 'No posts in period';
$txt['fid_filter_threshold'] = 'Inactivity threshold:';
$txt['fid_filter_custom_days'] = 'Custom days:';
$txt['fid_filter_category'] = 'Category:';
$txt['fid_filter_status'] = 'Status:';
$txt['fid_filter_status_all'] = 'All';
$txt['fid_filter_status_new'] = 'Unresolved';
$txt['fid_filter_status_ack'] = 'Acknowledged';
$txt['fid_filter_severity'] = 'Severity:';
$txt['fid_days_short'] = '%1$s days';

// Overview cards.
$txt['fid_stat_total_members'] = 'Total Members';
$txt['fid_stat_total_members_desc'] = 'All registered members, including unapproved ones';
$txt['fid_stat_active_members'] = 'Active Members';
$txt['fid_stat_active_members_desc'] = 'Members who logged in during the selected period';
$txt['fid_stat_inactive_members'] = 'Inactive Members';
$txt['fid_stat_inactive_members_desc'] = 'Members without a login during the selected period';
$txt['fid_stat_new_members_period'] = 'New Members (period)';
$txt['fid_stat_new_members_period_desc'] = 'Registrations during the selected period, compared with the previous one';
$txt['fid_stat_total_posts'] = 'Total Posts';
$txt['fid_stat_total_posts_desc'] = 'All approved posts on the forum';
$txt['fid_stat_total_topics'] = 'Total Topics';
$txt['fid_stat_total_topics_desc'] = 'All approved topics on the forum';
$txt['fid_stat_period_posts'] = 'Posts (period)';
$txt['fid_stat_period_posts_desc'] = 'Posts during the selected period, compared with the previous one';
$txt['fid_stat_period_topics'] = 'Topics (period)';
$txt['fid_stat_period_topics_desc'] = 'Topics started during the selected period, compared with the previous one';
$txt['fid_stat_active_boards'] = 'Active Boards';
$txt['fid_stat_active_boards_desc'] = 'Boards with at least one post of %1$s in total';
$txt['fid_activity_rate_title'] = 'Forum Activity Rate';
$txt['fid_activity_rate_value'] = '%1$s%%';
$txt['fid_activity_rate_formula'] = 'Members who posted during the period: %1$s of %2$s. This measures posting participation, not registration volume.';
$txt['fid_overview_slices_title'] = 'Today, Week and Month';
$txt['fid_view_details'] = 'View details';
$txt['fid_go_alerts'] = 'Open Admin Alerts';
$txt['fid_go_compare'] = 'Open Activity Comparison';

// Period labels.
$txt['fid_period_7'] = 'Last 7 days';
$txt['fid_period_30'] = 'Last 30 days';
$txt['fid_period_90'] = 'Last 90 days';
$txt['fid_period_365'] = 'Last 365 days';

// Short slices.
$txt['fid_slice_today'] = 'Today';
$txt['fid_slice_yesterday'] = 'Yesterday';
$txt['fid_slice_week'] = 'This week (last 7 days)';
$txt['fid_slice_prev_week'] = 'Previous week';
$txt['fid_slice_month'] = 'This month';
$txt['fid_slice_prev_month'] = 'Previous month';

// Metrics and columns.
$txt['fid_metric_period'] = 'Period';
$txt['fid_metric_posts'] = 'Posts';
$txt['fid_metric_topics'] = 'Topics';
$txt['fid_metric_registrations'] = 'Registrations';
$txt['fid_metric_active_members'] = 'Active members';
$txt['fid_metric_online'] = 'Most online';
$txt['fid_col_member_name'] = 'Member';
$txt['fid_col_board_name'] = 'Board';
$txt['fid_col_category'] = 'Category';
$txt['fid_col_total_posts'] = 'Total posts';
$txt['fid_col_total_topics'] = 'Total topics';
$txt['fid_col_period_posts'] = 'Posts (period)';
$txt['fid_col_period_topics'] = 'Topics (period)';
$txt['fid_col_last_login'] = 'Last login';
$txt['fid_col_last_post'] = 'Last post';
$txt['fid_col_registered'] = 'Registered';
$txt['fid_col_activity_score'] = 'Activity score';
$txt['fid_col_growth'] = 'Growth';
$txt['fid_col_status'] = 'Status';
$txt['fid_col_date'] = 'Date';
$txt['fid_col_week'] = 'Week';
$txt['fid_col_month'] = 'Month';
$txt['fid_col_inactive_duration'] = 'Inactive for';
$txt['fid_col_severity'] = 'Severity';
$txt['fid_col_title'] = 'Alert';
$txt['fid_col_occurrences'] = 'Occurrences';
$txt['fid_col_last_seen'] = 'Last seen';
$txt['fid_col_alert_status'] = 'Status';
$txt['fid_never'] = 'Never';

// Member intelligence.
$txt['fid_top_title'] = 'Most Active Members';
$txt['fid_top_select'] = 'Show top:';
$txt['fid_no_top_members'] = 'No member posted during the selected period.';
$txt['fid_member_list_title'] = 'Member Activity';
$txt['fid_no_members'] = 'No member matches the selected filters.';
$txt['fid_activity_score_formula'] = 'Score = recent posts x %1$d + topics started x %2$d + recent login points x %3$d + active days x %4$d. Recent login points: 7 down to 0 as the last login goes back in time.';
$txt['fid_activity_score_full'] = 'Activity score = recent posts x %1$d + topics started x %2$d + login points x %3$d + active days x %4$d. Weights are configurable in the dashboard settings.';

// Board intelligence.
$txt['fid_board_list_title'] = 'Boards';
$txt['fid_no_boards'] = 'No board is visible with your permissions.';
$txt['fid_board_rankings'] = 'Rankings:';
$txt['fid_rank_popular'] = 'Most popular';
$txt['fid_rank_active'] = 'Most active in period';
$txt['fid_rank_growing'] = 'Fastest growing';
$txt['fid_rank_declining'] = 'Declining';
$txt['fid_rank_inactive'] = 'Least recent activity';
$txt['fid_board_status_empty'] = 'Empty';
$txt['fid_board_status_active'] = 'Active';
$txt['fid_board_status_quiet'] = 'Quiet';
$txt['fid_board_status_declining'] = 'Declining';
$txt['fid_board_status_inactive'] = 'Inactive';

// Growth.
$txt['fid_growth_chart_title'] = 'Daily Development';
$txt['fid_growth_daily_title'] = 'Daily Statistics';
$txt['fid_growth_weekly_title'] = 'Weekly Statistics';
$txt['fid_growth_monthly_title'] = 'Monthly Statistics';
$txt['fid_chart_no_data'] = 'No activity was recorded in the selected range.';
$txt['fid_no_data'] = 'No data for the selected range.';
$txt['fid_chart_bar'] = 'Bar chart';
$txt['fid_chart_line'] = 'Line chart';

// Inactive members.
$txt['fid_inactive_title'] = 'Inactive Member Report';
$txt['fid_inactive_list_title'] = 'Inactive Members';
$txt['fid_no_inactive'] = 'No member matches the selected inactivity filters.';
$txt['fid_category_all'] = 'All';
$txt['fid_category_never_login'] = 'Never logged in';
$txt['fid_category_never_post'] = 'Never posted';
$txt['fid_category_was_active'] = 'Previously active';
$txt['fid_category_long_term'] = 'Long-term inactive';
$txt['fid_days_ago'] = '%1$s days';

// Comparison.
$txt['fid_compare_title'] = 'Activity Comparison';
$txt['fid_compare_current'] = 'Current';
$txt['fid_compare_previous'] = 'Previous';
$txt['fid_compare_difference'] = 'Difference';
$txt['fid_compare_change'] = 'Change';
$txt['fid_compare_today'] = 'Today versus yesterday';
$txt['fid_compare_week'] = 'This week versus previous week';
$txt['fid_compare_month'] = 'This month versus previous month';
$txt['fid_compare_custom'] = 'Selected period versus previous equal period';

// Alerts.
$txt['fid_alerts_unseen'] = '%1$s unresolved alert(s)';
$txt['fid_alerts_none'] = 'No unresolved alerts';
$txt['fid_alerts_last_run'] = 'Last check: %1$s (interval: every %2$s h)';
$txt['fid_alerts_never_run'] = 'No detection run yet';
$txt['fid_alerts_disabled'] = 'Alert detection is currently disabled.';
$txt['fid_no_alerts'] = 'No alerts stored for the selected filters.';
$txt['fid_alert_list_title'] = 'Alerts';
$txt['fid_alert_status_new'] = 'Unresolved';
$txt['fid_alert_status_ack'] = 'Acknowledged';
$txt['fid_alert_ack'] = 'Acknowledge';
$txt['fid_alert_ack_confirm'] = 'Mark this alert as acknowledged?';
$txt['fid_severity_low'] = 'Low';
$txt['fid_severity_medium'] = 'Medium';
$txt['fid_severity_high'] = 'High';
$txt['fid_severity_critical'] = 'Critical';

$txt['fid_alert_cat_decline_posts'] = 'Posting decline';
$txt['fid_alert_cat_decline_topics'] = 'Topic decline';
$txt['fid_alert_cat_decline_members'] = 'Posting member decline';
$txt['fid_alert_cat_decline_registration'] = 'Registration decline';
$txt['fid_alert_cat_spikes'] = 'Unusual activity spikes';
$txt['fid_alert_cat_spam'] = 'Potential spam patterns';
$txt['fid_alert_cat_inactive_boards'] = 'Inactive boards';
$txt['fid_alert_cat_inactivity'] = 'Inactivity increase';

$txt['fid_alert_title_decline_posts'] = 'Posting activity declined';
$txt['fid_alert_title_decline_topics'] = 'Topic activity declined';
$txt['fid_alert_title_decline_members'] = 'Fewer posting members';
$txt['fid_alert_title_decline_registration'] = 'Registrations declined';
$txt['fid_alert_title_spikes'] = 'Unusual activity spike';
$txt['fid_alert_title_spam'] = 'Potential spam pattern';
$txt['fid_alert_title_inactive_boards'] = 'Boards went silent';
$txt['fid_alert_title_inactivity'] = 'Inactive member count increased';

$txt['fid_alert_message_decline_posts'] = 'Posts dropped by %1$s%%: from %2$s in the previous %4$s-day window to %3$s in the current one. Review the Growth Analytics section and recent board changes. This alert never modifies members or settings by itself.';
$txt['fid_alert_message_decline_topics'] = 'New topics dropped by %1$s%%: from %2$s in the previous %4$s-day window to %3$s in the current one. Consider engaging content polls or announcements to revive discussions.';
$txt['fid_alert_message_decline_members'] = 'The number of distinct posting members dropped by %1$s%%: from %2$s to %3$s over a %4$s-day comparison. Check Member Intelligence for the most affected boards and gaps.';
$txt['fid_alert_message_decline_registration'] = 'Registrations dropped by %1$s%%: from %2$s in the previous %4$s-day window to %3$s in the current one. Verify registration settings and recent visibility of your forum.';
$txt['fid_alert_message_spikes'] = 'Posts jumped by %1$s%%: from %2$s in the previous %4$s-day window to %3$s in the current one. A spike may be a planned event or coordinated activity; review the boards involved.';
$txt['fid_alert_message_spam'] = '%1$s of the last %2$s posts came from members registered within 7 days (%3$s%%). Review the recent posts and use SMF moderation tools if needed. This alert never punishes members automatically.';
$txt['fid_alert_message_inactive_boards'] = '%1$s board(s) received no posts in the last %2$s days after being active before. Consider refreshing content or reorganising those boards.';
$txt['fid_alert_message_inactivity'] = 'The number of inactive members grew by %1$s%%: from %2$s to %3$s using the %4$s-day inactivity definition. Review the Inactive Members report.';

$txt['fid_alert_stats_decline'] = 'Last %1$s days: previous window %2$s, current window %3$s.';
$txt['fid_alert_stats_spam'] = 'Posts by members registered within 7 days: %1$s of %2$s in the last 24 hours.';
$txt['fid_alert_stats_inactivity'] = 'Inactive members: %1$s previously, %2$s now (threshold %3$s days).';

// Settings screen.
$txt['fid_settings_general'] = 'General';
$txt['fid_setting_fid_enabled'] = 'Enable dashboard';
$txt['fid_setting_fid_enabled_desc'] = 'When disabled, only members allowed to manage the settings can see the dashboard area.';
$txt['fid_setting_fid_default_period'] = 'Default analytics period';
$txt['fid_setting_fid_cache_ttl'] = 'Cache duration (seconds)';
$txt['fid_setting_fid_cache_ttl_desc'] = 'Cached analytics older than this are refreshed. 0 disables caching and runs live queries on every view.';
$txt['fid_setting_fid_items_per_page'] = 'Rows per page (5-100)';
$txt['fid_setting_fid_allow_csv'] = 'Allow CSV export';
$txt['fid_setting_fid_allow_csv_desc'] = 'Export still requires the Export Analytics permission and a valid session.';
$txt['fid_setting_fid_charts'] = 'Enable charts';
$txt['fid_setting_fid_chart_style'] = 'Default chart style';

$txt['fid_settings_activity_score'] = 'Activity score weights';
$txt['fid_setting_fid_weight_recent_post'] = 'Weight: recent posts';
$txt['fid_setting_fid_weight_topics'] = 'Weight: topics started';
$txt['fid_setting_fid_weight_login'] = 'Weight: login points';
$txt['fid_setting_fid_weight_consistency'] = 'Weight: active days';

$txt['fid_settings_reports'] = 'Reports';
$txt['fid_setting_fid_inactivity_days'] = 'Default inactivity threshold (days)';
$txt['fid_setting_fid_board_active_days'] = 'Posts per month for an "active" board';
$txt['fid_setting_fid_board_quiet_days'] = 'Posts per month for a "quiet" board';

$txt['fid_settings_alerts'] = 'Alerts';
$txt['fid_setting_fid_alert_enabled'] = 'Enable alert detection';
$txt['fid_setting_fid_alert_enabled_desc'] = 'Detection runs when authorized members open the dashboard, at most once per interval.';
$txt['fid_setting_fid_alert_threshold'] = 'Decline/increase threshold (%)';
$txt['fid_setting_fid_alert_threshold_desc'] = 'Minimum percentage change that triggers a decline or inactivity alert';
$txt['fid_setting_fid_alert_spike_threshold'] = 'Spike threshold (%)';
$txt['fid_setting_fid_alert_min_days'] = 'Detection window (days)';
$txt['fid_setting_fid_alert_min_days_desc'] = 'Current window length used by decline and spike detection';
$txt['fid_setting_fid_alert_interval'] = 'Detection interval (hours)';
$txt['fid_setting_fid_alert_cooldown'] = 'Notification cooldown (hours)';

// CSV export.
$txt['fid_export_link'] = 'Export as CSV (%1$s)';
$txt['fid_export_type_members'] = 'Member activity';
$txt['fid_export_type_inactive'] = 'Inactive members';
$txt['fid_export_type_boards'] = 'Board analytics';
$txt['fid_export_type_daily'] = 'Daily statistics';
$txt['fid_export_type_monthly'] = 'Monthly statistics';
$txt['fid_no_baseline'] = 'n/a';
$txt['fid_export_col_member_name'] = 'Member name';
$txt['fid_export_col_total_posts'] = 'Total posts';
$txt['fid_export_col_total_topics'] = 'Total topics';
$txt['fid_export_col_period_posts'] = 'Posts in period';
$txt['fid_export_col_period_topics'] = 'Topics in period';
$txt['fid_export_col_registered'] = 'Registration date';
$txt['fid_export_col_last_login'] = 'Last login';
$txt['fid_export_col_last_post'] = 'Last post';
$txt['fid_export_col_activity_score'] = 'Activity score';
$txt['fid_export_col_inactive_duration'] = 'Inactive for (days)';
$txt['fid_export_col_category'] = 'Category';
$txt['fid_export_col_board_name'] = 'Board name';
$txt['fid_export_col_growth'] = 'Growth (%)';
$txt['fid_export_col_status'] = 'Activity status';
$txt['fid_export_col_date'] = 'Date';
$txt['fid_export_col_month'] = 'Month';

// Permissions.
$txt['permissiongroup_fid'] = 'Forum Intelligence Dashboard';
$txt['permissionname_fid_view_dashboard'] = 'View Forum Intelligence overview';
$txt['permissionname_fid_view_members'] = 'View Forum Intelligence member analytics';
$txt['permissionname_fid_view_boards'] = 'View Forum Intelligence board analytics';
$txt['permissionname_fid_view_growth'] = 'View Forum Intelligence growth and comparison analytics';
$txt['permissionname_fid_view_inactive'] = 'View Forum Intelligence inactive member reports';
$txt['permissionname_fid_view_alerts'] = 'View Forum Intelligence alerts';
$txt['permissionname_fid_export'] = 'Export Forum Intelligence analytics as CSV';
$txt['permissionname_fid_manage_settings'] = 'Manage Forum Intelligence dashboard settings';
$txt['permissionname_fid_manage_alerts'] = 'Manage Forum Intelligence alert settings';
