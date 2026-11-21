Forum Intelligence Dashboard v1.0.2
Copyright (c) 2026 rumpa

Thank you for installing Forum Intelligence Dashboard, an analytics
dashboard for SMF 2.1.8 administrators.

WHAT THIS MODIFICATION DOES
- Adds a "Forum Intelligence" panel to your admin centre with real forum
  statistics: overview cards, member rankings with a transparent activity
  score, board analytics, daily / weekly / monthly growth charts, inactive
  member reports, activity comparisons and an internal administrator alert
  system.
- Adds nine granular permissions. By default only administrators can see
  and use the dashboard.
- Adds one new database table ({db_prefix}fid_alerts) and its own settings.
  SMF core tables are never modified.

REQUIREMENTS
- SMF 2.1.8 (target platform; not tested on other versions)
- MySQL / MariaDB (same database version required by SMF 2.1.8)
- PHP version supported by your SMF installation

AFTER INSTALLATION
1. Go to Admin Center > Forum Intelligence to open the dashboard.
2. (Optional) Grant the nine "Forum Intelligence" permissions to selected
   membergroups under Admin Center > Permissions.
3. Configure the dashboard under Forum Intelligence > Settings.

The modification can be uninstalled completely at any time through
Admin Center > Package Manager > Browse Packages. Uninstallation removes
the hooks, the packaged files, the fid_* settings and the fid_alerts
table. No data in SMF core tables is ever modified or deleted by this
modification.

Support and documentation are provided in the package README.md; there is
no external support site for this modification.
