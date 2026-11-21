# Features

Forum Intelligence Dashboard v1.0.0 — Copyright (c) 2026 rumpa

## Dashboard sections

### 1. Overview
Cards for total members, active members, inactive members, new members (today / week / month), total posts, total topics, posts today, topics today, posts this month, topics this month, active boards and forum activity rate. Each card includes a description, a comparison against the previous equivalent period where one exists, a trend indicator and a link to the relevant section. Cached according to the configured cache duration. Empty forums show zero/empty states.

### 2. Member Intelligence
Top 10 / 25 / 50 member rankings with search, sorting, date-range filtering, pagination and profile links. Columns: member, total posts, topics started, posts in the selected period, registration date, last login, last post, activity score.

**Activity score formula** (all weights administrator-configurable in Settings):

```
days_since_login = floor((now - last_login) / 86400)      (9999 when never logged in — login points 0)
login_points     = max(0, 7 - min(7, days_since_login))

score = W_recent_post × posts_in_period
      + W_topics       × topics_started_in_period
      + W_login        × login_points
      + W_consistency  × distinct_active_days_in_period
```

Nothing is hidden: the formula, the current weight values and every input are shown on the settings screen and in the ranking footnote.

### 3. Board Intelligence
Board statistics for every board the viewing administrator may see (visibility enforced through SMF's `query_see_board()`): topics, posts, recent posts/topics, last activity, growth rate and an activity status (active / quiet / inactive) using configurable windows. Sortable by totals, recent activity, growth and decline.

### 4. Growth Analytics
Daily, weekly and monthly series for registrations, posts, topics and active members over 7/30/90/365 days or a server-validated custom range. Charts are inline SVG (bar or line, per setting) with per-point `<title>` tooltips — zero external chart libraries, zero JavaScript required for rendering.

### 5. Inactive Members
Periods of 30/60/90/180/365 days or a custom, server-validated range. Columns: member, registration date, last login, last post, total posts, inactivity duration, status. Categories: never logged in; registered but never posted; previously active now inactive; long-term inactive. Search, sort, pagination, CSV export. The mod never deletes, bans, suspends or otherwise modifies members.

### 6. Activity Comparison
Today vs yesterday, this week vs previous week, this month vs previous month, custom period vs the immediately preceding equivalent period. Metrics: registrations, posts, topics, active members. Each row shows current value, previous value, absolute difference and percentage change, computed safely when the base value is zero.

### 7. Admin Alerts
Eight server-side detection checks: post decline, topic decline, member-activity decline, registration decline, activity spikes, potential spam patterns, inactive boards, major inactivity increases. Configurable per category with threshold percentages, minimum observation days, detection interval, notification cooldown and severity. Alerts are deduplicated by key, occurrences are counted, acknowledged alerts stay visible with their history. **Alerts are informational only — no automatic punishment, no member or setting changes.** Notifications are delivered inside the dashboard (alert centre); no external delivery mechanism is used.

### 8. Settings
Native SMF settings screen (save button, validation and admin search integration included). Covers enable/disable, default period, cache duration, page size, CSV availability, chart style, activity score weights, report windows, all alert options.

## Technical characteristics

- **Hooks (verified against SMF 2.1.8 source):** `integrate_admin_areas`, `integrate_load_permissions`, `integrate_load_illegal_guest_permissions`, `integrate_admin_search`.
- **Files created:** one source controller, one data/service subs file, one template file, one CSS file, one optional vanilla-JS file, two language files.
- **Database:** exactly one additional table, `{db_prefix}fid_alerts`, created idempotently and removed on uninstall. No core table is ever modified.
- **Caching:** expensive aggregates are cached through SMF's `cache_put_data` with a configurable TTL and invalidated on alert updates.
- **Performance:** every list is paginated through SMF's `createList` API; exports collect rows in pages with a hard cap; aggregates use indexed columns (`date_registered`, `poster_time` on core tables) and precomputed day keys.
- **Languages:** English and Bengali; all user-visible strings live in the language files, ready for additional languages.
