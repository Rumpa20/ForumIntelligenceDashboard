# Forum Intelligence Dashboard

A professional analytics dashboard for Simple Machines Forum 2.1.8 administrators.

**Author:** rumpa — **Copyright (c) 2026 rumpa** — **Version:** 1.0.0 — **License:** see [LICENSE.txt](LICENSE.txt) (custom license, review before submission — see docs/OFFICIAL_SUBMISSION.md)

## Overview

Forum Intelligence Dashboard adds a "Forum Intelligence" area to the SMF admin centre. All statistics are computed from the forum's own database — the mod never invents, samples or simulates data. Empty forums and empty date ranges are handled gracefully (zeros and "no data" states, never fake values).

## Features

- **Overview** — real-time cards for members, posts, topics, new members today/week/month, posts today/month, topics today/month, active boards and forum activity rate, with previous-period comparison and trend indicators.
- **Member Intelligence** — Top 10 / 25 / 50 member rankings with a transparent, administrator-configurable activity score, search, sorting, date filtering and pagination.
- **Board Intelligence** — board statistics respecting SMF board-visibility permissions (`query_see_board`), growth rates, activity statuses and multi-key sorting.
- **Growth Analytics** — daily, weekly and monthly series for registrations, posts, topics and active members over 7/30/90/365 days or a custom, server-validated range, rendered as dependency-free inline SVG charts.
- **Inactive Members** — configurable inactivity periods (30/60/90/180/365/custom), four report categories, CSV export. The mod never deletes, bans, suspends or modifies members.
- **Activity Comparison** — today vs yesterday, this week vs previous week, this month vs previous month and custom vs previous equivalent period, with safe zero-value handling.
- **Admin Alerts** — internal dashboard alert system detecting activity/registration/posting/topic declines, activity spikes, potential spam patterns, inactive boards and major inactivity increases, with configurable thresholds, cooldown and deduplication. Alerts are informational only — no automatic punishment or setting changes.
- **CSV export** — member activity, inactive members, board analytics, daily and monthly statistics, UTF-8 with BOM, formula-injection protection.

## Compatibility

- SMF 2.1.8 (the target platform; other versions have not been tested and are not claimed).
- MySQL / MariaDB, as required by SMF 2.1.8.
- PHP versions supported by SMF 2.1.x.
- No core file modifications. Four SMF integration hooks are used, all verified against the actual SMF 2.1.8 source: `integrate_admin_areas`, `integrate_load_permissions`, `integrate_load_illegal_guest_permissions`, `integrate_admin_search`.

## Requirements

- SMF 2.1.8 administrator access (Package Manager).
- Writable `Packages` directory.
- MySQL / MariaDB database (the mod creates one additional table, `{db_prefix}fid_alerts`).

## Installation

1. Back up your database and files.
2. Admin Center → Package Manager → Download Packages → upload `ForumIntelligenceDashboard-v1.0.0.zip`.
3. Install the package. Full details: [docs/INSTALLATION.md](docs/INSTALLATION.md).

## Configuration

Admin Center → Forum Intelligence → Settings enables/disables the dashboard, sets the default analytics period, cache duration, inactivity threshold, activity score weights, alert thresholds, alert cooldown, enabled alert types, CSV export and chart preferences. Every setting is validated server-side on save.

The activity score is fully documented in the settings screen and in [docs/FEATURES.md](docs/FEATURES.md):

```
score = w_recent_posts × posts in period
      + w_topics        × topics started in period
      + w_login         × login points  (7 = logged in today … 0 = more than 6 days ago)
      + w_consistency   × number of distinct active days in period
```

## Permissions

Nine permissions in the native SMF permission system, all defaulting to administrators only:

| Permission | Meaning |
|---|---|
| `fid_view_dashboard` | View the overview dashboard |
| `fid_view_members` | View member analytics |
| `fid_view_boards` | View board analytics |
| `fid_view_growth` | View growth analytics and comparisons |
| `fid_view_inactive` | View inactive member reports |
| `fid_view_alerts` | View admin alerts |
| `fid_export` | Export analytics as CSV |
| `fid_manage_settings` | Manage dashboard settings |
| `fid_manage_alerts` | Acknowledge alerts and manage alert settings |

Every controller subaction checks its matching permission with `isAllowedTo()` — hiding navigation links is never used as an access-control mechanism, and guests are denied all nine permissions at the hook level.

## Troubleshooting

- **The admin menu entry is missing** — confirm the four hooks are registered (Package Manager → the mod → hooks list) and that your account has `fid_view_dashboard`. Administrators have all nine permissions via the built-in administrator group.
- **Statistics look zeroed after bulk-importing data** — analytics are cached per the "Cache duration" setting; lower the cache duration or wait for expiry.
- **Alerts never appear** — check that individual alert categories are enabled in Settings and that the detection interval (hours) and minimum-observation-days requirements are satisfied; the first run after installation must observe at least two comparable windows.
- **Uninstalling does not remove `fid_*` rows** — the uninstall script removes settings, hooks, packaged files and the `fid_alerts` table; if any action failed during uninstall, the Package Manager error screen names the failing step.

## License

Copyright (c) 2026 rumpa. See [LICENSE.txt](LICENSE.txt). This is a custom non-OSI license; before submitting the mod to the official SMF customization site, review the license against the SMF submission guidelines (see [docs/OFFICIAL_SUBMISSION.md](docs/OFFICIAL_SUBMISSION.md)).
