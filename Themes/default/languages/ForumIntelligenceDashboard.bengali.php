<?php
// Version: 2.1; ForumIntelligenceDashboard

/**
 * Forum Intelligence Dashboard - Bengali language strings.
 *
 * @package ForumIntelligenceDashboard
 * @copyright Copyright (c) 2026 rumpa
 * @license See LICENSE.txt
 */

global $txt;

// মেনু ও সেকশন শিরোনাম।
$txt['fid_menu_title'] = 'ফোরাম ইন্টেলিজেন্স';
$txt['fid_dashboard_title'] = 'ফোরাম ইন্টেলিজেন্স ড্যাশবোর্ড';
$txt['fid_section_overview'] = 'সারসংক্ষেপ';
$txt['fid_section_members'] = 'সদস্য বিশ্লেষণ';
$txt['fid_section_boards'] = 'বোর্ড বিশ্লেষণ';
$txt['fid_section_growth'] = 'প্রবৃদ্ধি বিশ্লেষণ';
$txt['fid_section_inactive'] = 'নিষ্ক্রিয় সদস্য';
$txt['fid_section_compare'] = 'কার্যকলাপ তুলনা';
$txt['fid_section_alerts'] = 'অ্যাডমিন সতর্কতা';
$txt['fid_section_settings'] = 'ড্যাশবোর্ড সেটিংস';

// ত্রুটি।
$txt['fid_error_disabled'] = 'ফোরাম ইন্টেলিজেন্স ড্যাশবোর্ড বর্তমানে নিষ্ক্রিয়। সক্রিয় করতে অনুগ্রহ করে একজন প্রশাসকের সাথে যোগাযোগ করুন।';
$txt['fid_error_csv_disabled'] = 'ফোরাম ইন্টেলিজেন্স ড্যাশবোর্ড সেটিংসে CSV রপ্তানি নিষ্ক্রিয় করা আছে।';
$txt['fid_error_csv_empty'] = 'বর্তমান ফিল্টারের জন্য রপ্তানি করার মতো কোনো তথ্য নেই। ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন।';

// সময় নির্বাচক ও ফিল্টার।
$txt['fid_filter_period'] = 'সময়কাল:';
$txt['fid_filter_from'] = 'শুরু:';
$txt['fid_filter_to'] = 'শেষ:';
$txt['fid_filter_apply'] = 'প্রয়োগ করুন';
$txt['fid_filter_search'] = 'অনুসন্ধান:';
$txt['fid_filter_search_placeholder'] = 'সদস্যের নাম';
$txt['fid_filter_activity'] = 'কার্যকলাপ:';
$txt['fid_filter_activity_all'] = 'সব সদস্য';
$txt['fid_filter_activity_active'] = 'এই সময়ে পোস্ট করেছেন';
$txt['fid_filter_activity_silent'] = 'এই সময়ে পোস্ট করেননি';
$txt['fid_filter_threshold'] = 'নিষ্ক্রিয়তার সীমা:';
$txt['fid_filter_custom_days'] = 'কাস্টম দিন:';
$txt['fid_filter_category'] = 'বিভাগ:';
$txt['fid_filter_status'] = 'অবস্থা:';
$txt['fid_filter_status_all'] = 'সব';
$txt['fid_filter_status_new'] = 'অমীমাংসিত';
$txt['fid_filter_status_ack'] = 'স্বীকৃত';
$txt['fid_filter_severity'] = 'তীব্রতা:';
$txt['fid_days_short'] = '%1$s দিন';

// সারসংক্ষেপ কার্ড।
$txt['fid_stat_total_members'] = 'মোট সদস্য';
$txt['fid_stat_total_members_desc'] = 'অনুমোদিত না হওয়া সদস্যসহ সব নিবন্ধিত সদস্য';
$txt['fid_stat_active_members'] = 'সক্রিয় সদস্য';
$txt['fid_stat_active_members_desc'] = 'নির্বাচিত সময়কালে যারা লগইন করেছেন';
$txt['fid_stat_inactive_members'] = 'নিষ্ক্রিয় সদস্য';
$txt['fid_stat_inactive_members_desc'] = 'নির্বাচিত সময়কালে যারা লগইন করেননি';
$txt['fid_stat_new_members_period'] = 'নতুন সদস্য (সময়কাল)';
$txt['fid_stat_new_members_period_desc'] = 'নির্বাচিত সময়কালের নিবন্ধন, পূর্ববর্তী সময়কালের সাথে তুলনা';
$txt['fid_stat_total_posts'] = 'মোট পোস্ট';
$txt['fid_stat_total_posts_desc'] = 'ফোরামের সব অনুমোদিত পোস্ট';
$txt['fid_stat_total_topics'] = 'মোট টপিক';
$txt['fid_stat_total_topics_desc'] = 'ফোরামের সব অনুমোদিত টপিক';
$txt['fid_stat_period_posts'] = 'পোস্ট (সময়কাল)';
$txt['fid_stat_period_posts_desc'] = 'নির্বাচিত সময়কালের পোস্ট, পূর্ববর্তী সময়কালের সাথে তুলনা';
$txt['fid_stat_period_topics'] = 'টপিক (সময়কাল)';
$txt['fid_stat_period_topics_desc'] = 'নির্বাচিত সময়কালে শুরু হওয়া টপিক, পূর্ববর্তী সময়কালের সাথে তুলনা';
$txt['fid_stat_active_boards'] = 'সক্রিয় বোর্ড';
$txt['fid_stat_active_boards_desc'] = 'মোট %1$s টির মধ্যে যে বোর্ডগুলোতে অন্তত একটি পোস্ট আছে';
$txt['fid_activity_rate_title'] = 'ফোরাম কার্যকলাপের হার';
$txt['fid_activity_rate_value'] = '%1$s%%';
$txt['fid_activity_rate_formula'] = 'সময়কালে যারা পোস্ট করেছেন: %2$s জনের মধ্যে %1$s জন। এটি নিবন্ধনের সংখ্যা নয়, পোস্টিং অংশগ্রহণ পরিমাপ করে।';
$txt['fid_overview_slices_title'] = 'আজ, সপ্তাহ ও মাস';
$txt['fid_view_details'] = 'বিস্তারিত দেখুন';
$txt['fid_go_alerts'] = 'অ্যাডমিন সতর্কতা খুলুন';
$txt['fid_go_compare'] = 'কার্যকলাপ তুলনা খুলুন';

// সময়কালের নাম।
$txt['fid_period_7'] = 'শেষ ৭ দিন';
$txt['fid_period_30'] = 'শেষ ৩০ দিন';
$txt['fid_period_90'] = 'শেষ ৯০ দিন';
$txt['fid_period_365'] = 'শেষ ৩৬৫ দিন';

// সংক্ষিপ্ত সময়।
$txt['fid_slice_today'] = 'আজ';
$txt['fid_slice_yesterday'] = 'গতকাল';
$txt['fid_slice_week'] = 'এই সপ্তাহ (শেষ ৭ দিন)';
$txt['fid_slice_prev_week'] = 'পূর্ববর্তী সপ্তাহ';
$txt['fid_slice_month'] = 'এই মাস';
$txt['fid_slice_prev_month'] = 'পূর্ববর্তী মাস';

// মেট্রিক ও কলাম।
$txt['fid_metric_period'] = 'সময়কাল';
$txt['fid_metric_posts'] = 'পোস্ট';
$txt['fid_metric_topics'] = 'টপিক';
$txt['fid_metric_registrations'] = 'নিবন্ধন';
$txt['fid_metric_active_members'] = 'সক্রিয় সদস্য';
$txt['fid_metric_online'] = 'সর্বোচ্চ অনলাইন';
$txt['fid_col_member_name'] = 'সদস্য';
$txt['fid_col_board_name'] = 'বোর্ড';
$txt['fid_col_category'] = 'বিভাগ';
$txt['fid_col_total_posts'] = 'মোট পোস্ট';
$txt['fid_col_total_topics'] = 'মোট টপিক';
$txt['fid_col_period_posts'] = 'পোস্ট (সময়কাল)';
$txt['fid_col_period_topics'] = 'টপিক (সময়কাল)';
$txt['fid_col_last_login'] = 'শেষ লগইন';
$txt['fid_col_last_post'] = 'শেষ পোস্ট';
$txt['fid_col_registered'] = 'নিবন্ধিত';
$txt['fid_col_activity_score'] = 'কার্যকলাপ স্কোর';
$txt['fid_col_growth'] = 'প্রবৃদ্ধি';
$txt['fid_col_status'] = 'অবস্থা';
$txt['fid_col_date'] = 'তারিখ';
$txt['fid_col_week'] = 'সপ্তাহ';
$txt['fid_col_month'] = 'মাস';
$txt['fid_col_inactive_duration'] = 'নিষ্ক্রিয়';
$txt['fid_col_severity'] = 'তীব্রতা';
$txt['fid_col_title'] = 'সতর্কতা';
$txt['fid_col_occurrences'] = 'ঘটনা';
$txt['fid_col_last_seen'] = 'সর্বশেষ দেখা';
$txt['fid_col_alert_status'] = 'অবস্থা';
$txt['fid_never'] = 'কখনো নয়';

// সদস্য বিশ্লেষণ।
$txt['fid_top_title'] = 'সবচেয়ে সক্রিয় সদস্য';
$txt['fid_top_select'] = 'শীর্ষ দেখান:';
$txt['fid_no_top_members'] = 'নির্বাচিত সময়কালে কোনো সদস্য পোস্ট করেননি।';
$txt['fid_member_list_title'] = 'সদস্য কার্যকলাপ';
$txt['fid_no_members'] = 'নির্বাচিত ফিল্টারের সাথে মেলে এমন কোনো সদস্য নেই।';
$txt['fid_activity_score_formula'] = 'স্কোর = সাম্প্রতিক পোস্ট x %1$d + শুরু করা টপিক x %2$d + সাম্প্রতিক লগইন পয়েন্ট x %3$d + সক্রিয় দিন x %4$d। সাম্প্রতিক লগইন পয়েন্ট: শেষ লগইন যত পুরোনো তত কম (৭ থেকে ০)।';
$txt['fid_activity_score_full'] = 'কার্যকলাপ স্কোর = সাম্প্রতিক পোস্ট x %1$d + শুরু করা টপিক x %2$d + লগইন পয়েন্ট x %3$d + সক্রিয় দিন x %4$d। ওয়েটগুলো ড্যাশবোর্ড সেটিংস থেকে পরিবর্তনযোগ্য।';

// বোর্ড বিশ্লেষণ।
$txt['fid_board_list_title'] = 'বোর্ডসমূহ';
$txt['fid_no_boards'] = 'আপনার অনুমতি অনুযায়ী কোনো বোর্ড দৃশ্যমান নয়।';
$txt['fid_board_rankings'] = 'র‍্যাঙ্কিং:';
$txt['fid_rank_popular'] = 'সবচেয়ে জনপ্রিয়';
$txt['fid_rank_active'] = 'এই সময়ে সবচেয়ে সক্রিয়';
$txt['fid_rank_growing'] = 'দ্রুত বর্ধনশীল';
$txt['fid_rank_declining'] = 'পতনশীল';
$txt['fid_rank_inactive'] = 'পুরোনো কার্যকলাপ';
$txt['fid_board_status_empty'] = 'খালি';
$txt['fid_board_status_active'] = 'সক্রিয়';
$txt['fid_board_status_quiet'] = 'শান্ত';
$txt['fid_board_status_declining'] = 'পতনমুখী';
$txt['fid_board_status_inactive'] = 'নিষ্ক্রিয়';

// প্রবৃদ্ধি।
$txt['fid_growth_chart_title'] = 'দৈনিক ধারা';
$txt['fid_growth_daily_title'] = 'দৈনিক পরিসংখ্যান';
$txt['fid_growth_weekly_title'] = 'সাপ্তাহিক পরিসংখ্যান';
$txt['fid_growth_monthly_title'] = 'মাসিক পরিসংখ্যান';
$txt['fid_chart_no_data'] = 'নির্বাচিত সময়ে কোনো কার্যকলাপ রেকর্ড হয়নি।';
$txt['fid_no_data'] = 'নির্বাচিত সময়ের জন্য কোনো তথ্য নেই।';
$txt['fid_chart_bar'] = 'বার চার্ট';
$txt['fid_chart_line'] = 'লাইন চার্ট';

// নিষ্ক্রিয় সদস্য।
$txt['fid_inactive_title'] = 'নিষ্ক্রিয় সদস্য প্রতিবেদন';
$txt['fid_inactive_list_title'] = 'নিষ্ক্রিয় সদস্য';
$txt['fid_no_inactive'] = 'নির্বাচিত নিষ্ক্রিয়তা ফিল্টারের সাথে মেলে এমন কোনো সদস্য নেই।';
$txt['fid_category_all'] = 'সব';
$txt['fid_category_never_login'] = 'কখনো লগইন করেননি';
$txt['fid_category_never_post'] = 'কখনো পোস্ট করেননি';
$txt['fid_category_was_active'] = 'একসময় সক্রিয় ছিলেন';
$txt['fid_category_long_term'] = 'দীর্ঘ সময় নিষ্ক্রিয়';
$txt['fid_days_ago'] = '%1$s দিন';

// তুলনা।
$txt['fid_compare_title'] = 'কার্যকলাপ তুলনা';
$txt['fid_compare_current'] = 'বর্তমান';
$txt['fid_compare_previous'] = 'পূর্ববর্তী';
$txt['fid_compare_difference'] = 'পার্থক্য';
$txt['fid_compare_change'] = 'পরিবর্তন';
$txt['fid_compare_today'] = 'আজ বনাম গতকাল';
$txt['fid_compare_week'] = 'এই সপ্তাহ বনাম পূর্ববর্তী সপ্তাহ';
$txt['fid_compare_month'] = 'এই মাস বনাম পূর্ববর্তী মাস';
$txt['fid_compare_custom'] = 'নির্বাচিত সময়কাল বনাম পূর্ববর্তী সমান সময়কাল';

// সতর্কতা।
$txt['fid_alerts_unseen'] = '%1$s টি অমীমাংসিত সতর্কতা';
$txt['fid_alerts_none'] = 'কোনো অমীমাংসিত সতর্কতা নেই';
$txt['fid_alerts_last_run'] = 'সর্বশেষ পরীক্ষা: %1$s (প্রতি %2$s ঘণ্টায়)';
$txt['fid_alerts_never_run'] = 'এখনো কোনো পরীক্ষা চালানো হয়নি';
$txt['fid_alerts_disabled'] = 'সতর্কতা শনাক্তকরণ বর্তমানে নিষ্ক্রিয়।';
$txt['fid_no_alerts'] = 'নির্বাচিত ফিল্টারের জন্য কোনো সংরক্ষিত সতর্কতা নেই।';
$txt['fid_alert_list_title'] = 'সতর্কতা';
$txt['fid_alert_status_new'] = 'অমীমাংসিত';
$txt['fid_alert_status_ack'] = 'স্বীকৃত';
$txt['fid_alert_ack'] = 'স্বীকৃত করুন';
$txt['fid_alert_ack_confirm'] = 'এই সতর্কতাটি স্বীকৃত হিসেবে চিহ্নিত করবেন?';
$txt['fid_severity_low'] = 'নিম্ন';
$txt['fid_severity_medium'] = 'মধ্যম';
$txt['fid_severity_high'] = 'উচ্চ';
$txt['fid_severity_critical'] = 'গুরুতর';

$txt['fid_alert_cat_decline_posts'] = 'পোস্টিং হ্রাস';
$txt['fid_alert_cat_decline_topics'] = 'টপিক হ্রাস';
$txt['fid_alert_cat_decline_members'] = 'পোস্টিং সদস্য হ্রাস';
$txt['fid_alert_cat_decline_registration'] = 'নিবন্ধন হ্রাস';
$txt['fid_alert_cat_spikes'] = 'অস্বাভাবিক কার্যকলাপ বৃদ্ধি';
$txt['fid_alert_cat_spam'] = 'সম্ভাব্য স্প্যাম প্যাটার্ন';
$txt['fid_alert_cat_inactive_boards'] = 'নিষ্ক্রিয় বোর্ড';
$txt['fid_alert_cat_inactivity'] = 'নিষ্ক্রিয়তা বৃদ্ধি';

$txt['fid_alert_title_decline_posts'] = 'পোস্টিং কার্যকলাপ হ্রাস পেয়েছে';
$txt['fid_alert_title_decline_topics'] = 'টপিক কার্যকলাপ হ্রাস পেয়েছে';
$txt['fid_alert_title_decline_members'] = 'পোস্টিং সদস্য কমেছে';
$txt['fid_alert_title_decline_registration'] = 'নিবন্ধন হ্রাস পেয়েছে';
$txt['fid_alert_title_spikes'] = 'অস্বাভাবিক কার্যকলাপ বৃদ্ধি';
$txt['fid_alert_title_spam'] = 'সম্ভাব্য স্প্যাম প্যাটার্ন';
$txt['fid_alert_title_inactive_boards'] = 'বোর্ড নিষ্ক্রিয় হয়ে গেছে';
$txt['fid_alert_title_inactivity'] = 'নিষ্ক্রিয় সদস্য সংখ্যা বেড়েছে';

$txt['fid_alert_message_decline_posts'] = 'পোস্ট %1$s%% হ্রাস পেয়েছে: পূর্ববর্তী %4$s দিনে %2$s থেকে বর্তমান সময়ে %3$s। প্রবৃদ্ধি বিশ্লেষণ অংশ ও সাম্প্রতিক বোর্ড পরিবর্তন দেখুন। এই সতর্কতা নিজে থেকে কোনো সদস্য বা সেটিংস পরিবর্তন করে না।';
$txt['fid_alert_message_decline_topics'] = 'নতুন টপিক %1$s%% হ্রাস পেয়েছে: পূর্ববর্তী %4$s দিনে %2$s থেকে বর্তমান সময়ে %3$s। আলোচনা ফিরিয়ে আনতে ভোট বা ঘোষণার কথা ভাবুন।';
$txt['fid_alert_message_decline_members'] = 'স্বতন্ত্র পোস্টিং সদস্যের সংখ্যা %1$s%% হ্রাস পেয়েছে: %2$s থেকে %3$s, %4$s দিনের তুলনায়। সদস্য বিশ্লেষণে প্রভাবিত অংশগুলো দেখুন।';
$txt['fid_alert_message_decline_registration'] = 'নিবন্ধন %1$s%% হ্রাস পেয়েছে: পূর্ববর্তী %4$s দিনে %2$s থেকে বর্তমান সময়ে %3$s। নিবন্ধন সেটিংস ও ফোরামের দৃশ্যমানতা পরীক্ষা করুন।';
$txt['fid_alert_message_spikes'] = 'পোস্ট %1$s%% বৃদ্ধি পেয়েছে: পূর্ববর্তী %4$s দিনে %2$s থেকে বর্তমান সময়ে %3$s। বৃদ্ধি পরিকল্পিত হতে পারে বা সমন্বিত কার্যকলাপ হতে পারে; সংশ্লিষ্ট বোর্ড পরীক্ষা করুন।';
$txt['fid_alert_message_spam'] = 'শেষ %2$s পোস্টের মধ্যে %1$s টি এসেছে ৭ দিনের কম আগে নিবন্ধিত সদস্যদের কাছ থেকে (%3$s%%)। সাম্প্রতিক পোস্টগুলো পর্যালোচনা করুন এবং প্রয়োজনে SMF মডারেশন টুল ব্যবহার করুন। এই সতর্কতা কখনো স্বয়ংক্রিয়ভাবে সদস্যদের শাস্তি দেয় না।';
$txt['fid_alert_message_inactive_boards'] = '%1$s টি বোর্ড আগে সক্রিয় ছিল কিন্তু শেষ %2$s দিনে কোনো পোস্ট পায়নি। সেই বোর্ডগুলোর কনটেন্ট নতুন করার কথা ভাবুন।';
$txt['fid_alert_message_inactivity'] = '%4$s দিনের নিষ্ক্রিয়তা সংজ্ঞা অনুযায়ী নিষ্ক্রিয় সদস্য %1$s%% বেড়েছে: %2$s থেকে %3$s। নিষ্ক্রিয় সদস্য প্রতিবেদন দেখুন।';

$txt['fid_alert_stats_decline'] = 'শেষ %1$s দিন: পূর্ববর্তী %2$s, বর্তমান %3$s।';
$txt['fid_alert_stats_spam'] = '৭ দিনের কম আগে নিবন্ধিত সদস্যদের পোস্ট: শেষ ২৪ ঘণ্টায় মোট %2$s পোস্টের মধ্যে %1$s।';
$txt['fid_alert_stats_inactivity'] = 'নিষ্ক্রিয় সদস্য: পূর্বে %1$s, বর্তমানে %2$s (সীমা %3$s দিন)।';

// সেটিংস।
$txt['fid_settings_general'] = 'সাধারণ';
$txt['fid_setting_fid_enabled'] = 'ড্যাশবোর্ড সক্রিয় করুন';
$txt['fid_setting_fid_enabled_desc'] = 'নিষ্ক্রিয় থাকলে শুধু সেটিংস পরিচালনার অনুমতিপ্রাপ্ত সদস্যরা ড্যাশবোর্ড দেখতে পারেন।';
$txt['fid_setting_fid_default_period'] = 'ডিফল্ট বিশ্লেষণ সময়কাল';
$txt['fid_setting_fid_cache_ttl'] = 'ক্যাশের সময় (সেকেন্ড)';
$txt['fid_setting_fid_cache_ttl_desc'] = 'এর চেয়ে পুরোনো ক্যাশ করা বিশ্লেষণ নতুন করে গণনা হয়। 0 দিলে প্রতিবার লাইভ কোয়েরি চলে।';
$txt['fid_setting_fid_items_per_page'] = 'প্রতি পৃষ্ঠায় সারি (৫-১০০)';
$txt['fid_setting_fid_allow_csv'] = 'CSV রপ্তানি অনুমোদন';
$txt['fid_setting_fid_allow_csv_desc'] = 'রপ্তানিতে এখনো Export Analytics অনুমতি ও বৈধ সেশন প্রয়োজন।';
$txt['fid_setting_fid_charts'] = 'চার্ট সক্রিয় করুন';
$txt['fid_setting_fid_chart_style'] = 'ডিফল্ট চার্ট স্টাইল';

$txt['fid_settings_activity_score'] = 'কার্যকলাপ স্কোরের ওয়েট';
$txt['fid_setting_fid_weight_recent_post'] = 'ওয়েট: সাম্প্রতিক পোস্ট';
$txt['fid_setting_fid_weight_topics'] = 'ওয়েট: শুরু করা টপিক';
$txt['fid_setting_fid_weight_login'] = 'ওয়েট: লগইন পয়েন্ট';
$txt['fid_setting_fid_weight_consistency'] = 'ওয়েট: সক্রিয় দিন';

$txt['fid_settings_reports'] = 'প্রতিবেদন';
$txt['fid_setting_fid_inactivity_days'] = 'ডিফল্ট নিষ্ক্রিয়তার সীমা (দিন)';
$txt['fid_setting_fid_board_active_days'] = '"সক্রিয়" বোর্ডের জন্য মাসিক পোস্ট';
$txt['fid_setting_fid_board_quiet_days'] = '"শান্ত" বোর্ডের জন্য মাসিক পোস্ট';

$txt['fid_settings_alerts'] = 'সতর্কতা';
$txt['fid_setting_fid_alert_enabled'] = 'সতর্কতা শনাক্তকরণ সক্রিয় করুন';
$txt['fid_setting_fid_alert_enabled_desc'] = 'অনুমতিপ্রাপ্ত সদস্য ড্যাশবোর্ড খুললে প্রতি বিরতিতে সর্বোচ্চ একবার শনাক্তকরণ চলে।';
$txt['fid_setting_fid_alert_threshold'] = 'হ্রাস/বৃদ্ধির সীমা (%)';
$txt['fid_setting_fid_alert_threshold_desc'] = 'হ্রাস বা নিষ্ক্রিয়তা সতর্কতা ট্রিগার করার ন্যূনতম শতাংশ পরিবর্তন';
$txt['fid_setting_fid_alert_spike_threshold'] = 'বৃদ্ধির সীমা (%)';
$txt['fid_setting_fid_alert_min_days'] = 'শনাক্তকরণ সময় (দিন)';
$txt['fid_setting_fid_alert_min_days_desc'] = 'হ্রাস ও বৃদ্ধি শনাক্তকরণে ব্যবহৃত বর্তমান সময়ের দৈর্ঘ্য';
$txt['fid_setting_fid_alert_interval'] = 'শনাক্তকরণ বিরতি (ঘণ্টা)';
$txt['fid_setting_fid_alert_cooldown'] = 'বিজ্ঞপ্তি কুলডাউন (ঘণ্টা)';

// CSV রপ্তানি।
$txt['fid_export_link'] = 'CSV হিসেবে রপ্তানি (%1$s)';
$txt['fid_export_type_members'] = 'সদস্য কার্যকলাপ';
$txt['fid_export_type_inactive'] = 'নিষ্ক্রিয় সদস্য';
$txt['fid_export_type_boards'] = 'বোর্ড বিশ্লেষণ';
$txt['fid_export_type_daily'] = 'দৈনিক পরিসংখ্যান';
$txt['fid_export_type_monthly'] = 'মাসিক পরিসংখ্যান';
$txt['fid_no_baseline'] = 'প্রযোজ্য নয়';
$txt['fid_export_col_member_name'] = 'সদস্যের নাম';
$txt['fid_export_col_total_posts'] = 'মোট পোস্ট';
$txt['fid_export_col_total_topics'] = 'মোট বিষয়';
$txt['fid_export_col_period_posts'] = 'সময়কালের পোস্ট';
$txt['fid_export_col_period_topics'] = 'সময়কালের টপিক';
$txt['fid_export_col_registered'] = 'নিবন্ধনের তারিখ';
$txt['fid_export_col_last_login'] = 'শেষ লগইন';
$txt['fid_export_col_last_post'] = 'শেষ পোস্ট';
$txt['fid_export_col_activity_score'] = 'কার্যকলাপ স্কোর';
$txt['fid_export_col_inactive_duration'] = 'নিষ্ক্রিয় (দিন)';
$txt['fid_export_col_category'] = 'বিভাগ';
$txt['fid_export_col_board_name'] = 'বোর্ডের নাম';
$txt['fid_export_col_growth'] = 'প্রবৃদ্ধি (%)';
$txt['fid_export_col_status'] = 'কার্যকলাপের অবস্থা';
$txt['fid_export_col_date'] = 'তারিখ';
$txt['fid_export_col_month'] = 'মাস';

// অনুমতি।
$txt['permissiongroup_fid'] = 'ফোরাম ইন্টেলিজেন্স ড্যাশবোর্ড';
$txt['permissionname_fid_view_dashboard'] = 'ফোরাম ইন্টেলিজেন্স সারসংক্ষেপ দেখা';
$txt['permissionname_fid_view_members'] = 'ফোরাম ইন্টেলিজেন্স সদস্য বিশ্লেষণ দেখা';
$txt['permissionname_fid_view_boards'] = 'ফোরাম ইন্টেলিজেন্স বোর্ড বিশ্লেষণ দেখা';
$txt['permissionname_fid_view_growth'] = 'ফোরাম ইন্টেলিজেন্স প্রবৃদ্ধি ও তুলনা বিশ্লেষণ দেখা';
$txt['permissionname_fid_view_inactive'] = 'ফোরাম ইন্টেলিজেন্স নিষ্ক্রিয় সদস্য প্রতিবেদন দেখা';
$txt['permissionname_fid_view_alerts'] = 'ফোরাম ইন্টেলিজেন্স সতর্কতা দেখা';
$txt['permissionname_fid_export'] = 'ফোরাম ইন্টেলিজেন্স বিশ্লেষণ CSV হিসেবে রপ্তানি';
$txt['permissionname_fid_manage_settings'] = 'ফোরাম ইন্টেলিজেন্স ড্যাশবোর্ড সেটিংস পরিচালনা';
$txt['permissionname_fid_manage_alerts'] = 'ফোরাম ইন্টেলিজেন্স সতর্কতা সেটিংস পরিচালনা';
