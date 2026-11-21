# Installation

Forum Intelligence Dashboard v1.0.0 — Copyright (c) 2026 rumpa

## Installation requirements

- Simple Machines Forum 2.1.8 (target platform — no other version has been tested).
- Administrator account with Package Manager access.
- MySQL / MariaDB database (same requirements as SMF 2.1.8).
- Writable `Packages` directory.
- The package archive `ForumIntelligenceDashboard-v1.0.0.zip`.

## Package installation

1. Back up the database and the forum files before installing any modification.
2. Log in as an administrator.
3. Open **Admin Center → Package Manager → Download Packages**.
4. Upload `ForumIntelligenceDashboard-v1.0.0.zip`.
5. Click **Install Mod** on the package listing. The confirmation screen shows every action the installer performs (file copies, database table creation, settings registration, hook registration). Confirm to run the installation.
6. The installer:
   - copies the source, template, CSS, JavaScript and language files;
   - creates the `{db_prefix}fid_alerts` table (idempotent — an existing table is left untouched);
   - registers the default `fid_*` settings, but only the ones that do not already exist, so a re-install never overwrites customized values;
   - registers the four integration hooks.

There is no manual file editing and no core file modification.

## Creating the ZIP (developers)

Build the archive so that `package.xml` sits at the root of the ZIP — do not wrap the contents in a parent folder. SMF resolves all package.xml file paths relative to the archive root.

## Permission configuration

After installation all nine `fid_*` permissions exist and only the built-in administrator group has them. To delegate individual sections:

1. Open **Admin Center → Permissions → Edit membergroups → (group) → General permissions**.
2. Locate the **Forum Intelligence** permission group and tick the sections that group may use (view dashboard / members / boards / growth / inactive reports / alerts, export, manage settings, manage alerts).
3. Guests can never receive these permissions — the mod explicitly denies them at the `integrate_load_illegal_guest_permissions` level.

## Initial setup

1. Open **Admin Center → Forum Intelligence** (redirected automatically after installation).
2. Under **Settings**, review:
   - dashboard enabled state and default analytics period;
   - cache duration (seconds) — raise it on very large forums;
   - inactivity threshold and board activity windows;
   - activity score weights (see FEATURES.md for the exact formula);
   - alert categories, threshold percentages, minimum observation days, detection interval, notification cooldown;
   - CSV export availability and chart preferences.
3. Grant the desired permissions to non-administrator groups if required.

No further configuration is required; the dashboard immediately reports real data from your forum. On an empty forum every panel shows empty/zero states instead of fabricated values.

## Verifying the installation

- The admin menu contains **Forum Intelligence**.
- `Themes/default/languages/ForumIntelligenceDashboard.english.php` exists (and the Bengali file if you chose to install both).
- The `{db_prefix}fid_alerts` table exists in the database with the columns `id_alert, alert_key, severity, title, message, stats, first_seen, last_seen, occurrences, status, ack_by, ack_time`.
- `Admin Center → Package Manager → Browse Packages` lists the mod as installed.

## Uninstallation

**Admin Center → Package Manager → Browse Packages → Forum Intelligence Dashboard → Uninstall.** The uninstaller removes hooks, packaged files, all `fid_*` settings and the `fid_alerts` table. Alert history is lost on uninstall — the table is not preserved. Nothing in SMF core tables is modified by the mod, so uninstalling cannot degrade core data.
