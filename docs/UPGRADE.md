# Upgrade

Forum Intelligence Dashboard v1.0.0 — Copyright (c) 2026 rumpa

## Version migration

v1.0.0 is the first released version of the modification. There is no earlier build to migrate from, so no data migration is required. The upgrade script (`install/upgrade.php`) is nevertheless shipped and keeps the environment self-consistent; it is idempotent and safe to run repeatedly.

The upgrade path performs, in order:

1. Same idempotent installation work as the installer (ensure the `{db_prefix}fid_alerts` table exists, register only missing setting defaults — existing customized settings are never reset).
2. Ensure the acknowledgement columns (`ack_by`, `ack_time`) exist on `{db_prefix}fid_alerts` — this guards against any intermediate 1.0.x build installed without them.

## Upgrade instructions

1. Back up the database and forum files.
2. Open **Admin Center → Package Manager → Browse Packages**.
3. If a previous version is installed, the Package Manager offers the upgrade: choose to uninstall the old version (keeping affirmative on database change removal) and install the new package. Because version 1.0.0 is the first and only release, this normally appears as uninstall + reinstall of the same version.
4. Verify the admin panel loads and statistics still render.

## Backup requirements

Before upgrading any SMF modification:

- Full database dump (`mysqldump` or your hosting provider's tooling), including the `{db_prefix}settings` and `{db_prefix}log_packages` tables.
- A copy of `Sources`, `Themes/default` and the `Packages` directory.

If an upgrade fails, restore these backups before retrying; do not mix old packaged files with new ones.

## Reinstalling

The installer is idempotent: running it again does not duplicate the table, indexes or settings, and never overwrites a setting value an administrator has customized.
