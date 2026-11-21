# Official SMF Mod Submission

Forum Intelligence Dashboard v1.0.0 — Author: rumpa — Copyright (c) 2026 rumpa

This document prepares the modification for a potential submission to the official SMF customization site. **The mod is not approved, is not officially endorsed by Simple Machines Forum, and has not been submitted yet.** Do not claim otherwise anywhere in the project.

## Mod description (submission text)

Forum Intelligence Dashboard adds an analytics area to the SMF 2.1.8 admin centre. It reports real forum data — member engagement, board popularity, growth trends, inactive members and unusual activity — through overview cards, rankings, tables and dependency-free charts, and keeps administrators informed through a built-in, fully configurable alert centre. It uses only verified SMF 2.1.x hooks and native APIs, modifies no core file, adds one database table, and uninstalls completely.

## Features

See [FEATURES.md](FEATURES.md) (the text can be reused as the submission description).

## Compatibility

- SMF 2.1.8 only. Compatibility with other SMF versions has not been tested and is **not** claimed anywhere in the package (`for="2.1.8"` in package.xml).

## Version

1.0.0 — see [CHANGELOG](../CHANGELOG.md).

## Author

rumpa. No email, website or support URL is provided or invented; support threads would run through the official SMF customization site as required there.

## Installation instructions

See [INSTALLATION.md](INSTALLATION.md) — the package installs through the Package Manager, no manual file editing.

## Screenshots checklist (to capture before submission)

- [ ] Overview section
- [ ] Member Intelligence (Top 25 view)
- [ ] Board Intelligence
- [ ] Growth Analytics (daily, line and bar)
- [ ] Inactive Members report
- [ ] Activity Comparison
- [ ] Admin Alerts list
- [ ] Settings screen
- [ ] Permission editor showing the Forum Intelligence group
- [ ] The same views on a dark theme, if available

## Testing information

Honest statement for the submission notes: development verification covered hook/API verification against the SMF 2.1.8 source, structural PHP checks, XML validation and language key cross-checks. **No live installation, runtime permission, browser, or large-dataset performance testing has been performed yet.** Before submitting, run the test matrix in [TESTING.md](TESTING.md) on a real SMF 2.1.8 installation with MySQL/MariaDB and record the results.

## Known limitations

- Alerts are delivered only inside the dashboard alert centre; SMF does not offer a mod-generic external notification mechanism that fits all setups without additional dependencies.
- Alert detection runs when an authorized administrator accesses the dashboard (interval-gated via a stored timestamp) — there is no dependency on cron/scheduled tasks.
- Board analytics reflect SMF's board-visibility permissions for the viewer; aggregate counts over boards one may not see are intentionally excluded rather than approximated.
- Compatibility beyond SMF 2.1.8 is untested.
- The activity score is an analytical aid, not a moderation metric.

## License review (required before submission)

The package ships with a **custom, non-OSI license** written by the author (see [LICENSE.txt](../LICENSE.txt)). The official SMF customization site has its own submission and licensing requirements (modified versions, redistribution rules, recognized open-source licenses). **The custom license must be reviewed against the current official SMF submission guidelines before any submission.** If the SMF submission review finds the custom license incompatible, the license gap must be reported to the author (rumpa) and a compatible license adopted only by the author's explicit decision — the build must not silently replace the author's license.
