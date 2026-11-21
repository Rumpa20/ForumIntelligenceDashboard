# Security

Forum Intelligence Dashboard v1.0.0 — Copyright (c) 2026 rumpa

## Security architecture

The mod runs exclusively inside SMF's own request lifecycle (`?action=admin;area=fid`). It introduces no standalone entry point: the single controller `Sources/ForumIntelligenceDashboard.php` is reached through SMF's routing, which already loads authentication, sessions and the security layer before any mod code executes.

- **Authentication** — provided by SMF (`Sources/Security.php`); the controller never reads session cookies, passwords or tokens itself.
- **Authorization** — every subaction maps to one of the nine `fid_*` permissions and is passed through `isAllowedTo()` before any data query runs. Disabling the dashboard entirely still allows administrators with `fid_manage_settings` to reach Settings.
- **Guests** — all nine permissions are added to `$context['non_guest_permissions']` via the verified `integrate_load_illegal_guest_permissions` hook, so guests can never be granted them regardless of group configuration.
- **CSRF** — state-changing operations (saving settings, acknowledging an alert) call `checkSession()`; the SMF settings API additionally validates its own tokens. CSV exports validate the session too.
- **SQL injection** — all queries use `$smcFunc['db_query']()` with typed placeholders (`{int:…}`, `{string:…}`, `{array_int:…}`, `{array_string:…}`); no user input is ever concatenated into SQL. Sort keys are resolved against a server-side whitelist before reaching `ORDER BY`.
- **XSS** — all user-generated content (member names, board names) is escaped at the data layer via `$smcFunc['htmlspecialchars']()` or `htmlspecialchars()` before use in templates; templates print prepared fields, never raw DB values.
- **Input validation** — requested subactions are whitelisted; export types are whitelisted; dates are validated server-side; member/board IDs are cast to integers; limits and thresholds are clamped to safe ranges.

## Permission model

Nine permissions registered through the native SMF permission system (see README.md), default access: administrators only. Enforcement points:

1. the admin menu registry (visibility only — not a security control);
2. `isAllowedTo()` at every subaction and export;
3. SQL queries for board data run through `query_see_board()` so private board content never leaves the database for unauthorized viewers;
4. the guest-deny hook.

## Data handling

- Only aggregate analytics and rows already exposed to SMF administrators are queried; the mod reads no password hashes, no session identifiers, no IP addresses and no profile-field data.
- CSV exports use a UTF-8 BOM and guard every cell against spreadsheet formula injection; the export row cap (paged collector, 50k rows) prevents memory exhaustion.
- Alert links, board names inside alert statistics and CSV cells are escaped before storage/output.
- Nothing is logged to external services; failures surface through SMF's own error handling (`fatal_lang_error`, SMF error log).

## Reporting vulnerabilities

The mod has no support site of its own. Report vulnerabilities directly to the author (SMF username **rumpa**) through the SMF community private-message system. Do not post exploitable details on public boards. Copyright (c) 2026 rumpa; see LICENSE.txt.
