# Changelog — Forum Intelligence Dashboard

All notable changes to this project are documented here.

Copyright (c) 2026 rumpa. See LICENSE.txt.

## 1.0.1 — 2026

### Fixed
- Custom date ranges now accept a single-day range (start date equals end date).
- CSV export links now preserve custom start/end dates so exported rows match the dashboard's selected range.

### Notes
- These fixes are committed on the fixes-v1.0.1 branch and still require live SMF 2.1.8 installation and PHP runtime testing.

## 1.0.0 — 2026

Initial public release.

### Added
- Forum Intelligence admin area with eight sections: Overview, Member Intelligence, Board Intelligence, Growth Analytics, Inactive Members, Activity Comparison, Admin Alerts, Settings.
- Overview cards for members, posts, topics, active boards and activity rate with previous-period comparisons and trend indicators.
- Top 10/25/50 member rankings with a configurable, documented activity score (weights for recent posts, topics started, recent logins and active days).
- Board analytics respecting SMF board-visibility permissions, with growth rates, activity statuses and multi-key sorting.
- Daily, weekly and monthly growth series (7/30/90/365 days and custom ranges) with self-contained SVG charts.
- Inactive member report (30/60/90/180/365 and custom ranges) with four report categories and CSV export.
- Activity comparison for today/yesterday, week/week, month/month and custom periods.
- Administrator alert system with eight detection checks, configurable thresholds, severity, cooldown, deduplication and acknowledgement.
- CSV export for member activity, inactive members, board analytics, daily and monthly statistics (UTF-8 with BOM, formula-injection protection).
- Nine granular permissions, administrators only by default; guests explicitly denied.
- Settings panel using the native SMF settings API with server-side validation.
- English and Bengali language files; no hardcoded user-visible strings.
- One new database table, `{db_prefix}fid_alerts`, created idempotently by the installer and removed by the uninstaller.
- Documentation: README, INSTALLATION, UPGRADE, SECURITY, TESTING, FEATURES, OFFICIAL_SUBMISSION.

### Notes
- Tested architecture-wise against the SMF 2.1.8 source; a live installation test was not performed during development (see docs/TESTING.md).
- Alert notifications are delivered through the mod's internal dashboard alert centre; no external notification mechanism is used.
