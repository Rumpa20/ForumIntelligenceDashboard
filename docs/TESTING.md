# Testing

Forum Intelligence Dashboard v1.0.0 — Copyright (c) 2026 rumpa

## Honest status of what has been performed during development

| Check | Performed | Result |
|---|---|---|
| All hooks verified against real SMF 2.1.8 source | Yes | 4 hooks, all present at the cited locations |
| DB API semantics verified (`db_create_table`, `db_add_column`, `db_drop_table`, `db_list_columns`) against real source | Yes | Confirmed, including idempotency behaviour and the package database log |
| Package API verified (`parsePackageInfo`, `<code>` execution, `require-file` resolution) against real source | Yes | Confirmed — this drove the package.xml layout |
| Structural PHP syntax inspection of every PHP file | Yes | Passes with a token-level checker (see limitations) |
| package.xml / XML well-formedness | Yes | Passes |
| Language key cross-check (usage vs English/Bengali files) | Yes | Passes, key parity verified |
| Live `php -l` run | **No** — no PHP binary was available in the build environment; substituted by the structural checker above, which is not a full parser | — |
| Clean install on a real SMF 2.1.8 site | **No** | Untested |
| Reinstallation / upgrade / uninstall on a real site | **No** | Untested |
| Live permission tests (admin / non-admin / guest) | **No** | Untested |
| Live browser tests of charts, filters, lists, CSV downloads | **No** | Untested |
| Performance measurement on large datasets | **No** | Untested; design mitigations listed below |

Readers should treat everything above marked "No" as untested, per the project's own reporting rules.

## Test cases to execute after installation

### Installation tests
- Clean install on SMF 2.1.8 (fresh forum and on an existing forum).
- Install with `Packages/temp` already containing leftover files.
- Reinstall over an existing 1.0.0 installation; confirm settings are not reset.
- Uninstall; confirm hooks, files, `fid_*` settings and the `fid_alerts` table are removed.
- (Future versions) upgrade path from 1.0.0 via `install/upgrade.php`.

### Permission tests
- Administrator: full access to all eight sections, settings save, alert acknowledgement and exports.
- Regular member with only `fid_view_dashboard`: sees overview, nothing else; direct URLs to other subactions fail with the SMF permission error.
- Regular member with `fid_export` but without `fid_view_members`: export attempt fails.
- Guest: every subaction, including direct URLs, is denied.

### Analytics tests
- Empty forum: all sections render zero/empty states without errors.
- Small forum (single members/posts): scores and percentages, including zero-baseline changes, are correct.
- Large forum: pagination over member/inactive lists; cache behaviour with a low cache duration.
- Date filters: 7/30/90/365/custom, including a custom range with reversed dates and a future date.

### Security tests
- Attempt direct member/board URLs with another user's references.
- Attempt export with a manipulated session parameter.
- Attempt crafted `sa=` values (must fall back to overview).
- Inspect CSV output for leading-character protection on `= + - @` cells.

### Performance tests
- Query count per view with cache enabled and disabled.
- Response times with representative large datasets (requires a realistic dataset; not available during development).

### Compatibility tests
- SMF 2.1.8 (target). PHP versions in the SMF 2.1.x support matrix. MySQL and MariaDB. Only tested ranges: none live — all untested, as listed above.
