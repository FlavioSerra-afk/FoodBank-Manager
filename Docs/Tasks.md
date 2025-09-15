> Canonical Doc — READ ME FIRST
> This is 1 of 4 canonical docs (Specs, Plan, Tasks, Matrix).
> Codex MUST read & update these BEFORE any work, every prompt.
> Any other files in /Docs are historical and must not drive scope.

# Tasks

- [2025-09-16] Pass B1 — security + PHPCS zero
  - Hardened: foodbank-manager.php; includes/Core/*; includes/Attendance/*; includes/Auth/class-capabilities.php; includes/Rest/class-checkincontroller.php; includes/Shortcodes/class-staffdashboard.php; templates/public/staff-dashboard.php; assets/css/*.css; assets/js/staff-dashboard.js; uninstall.php.
  - PHPCS: WordPress.Files.FileName, WordPress.DB.PreparedSQL, WordPress.Security.NonceVerification, WordPress.WP.Capabilities, WordPress.WhiteSpace, WordPress.NamingConventions.ValidVariableName, WordPress.NamingConventions.ValidFunctionName.
  - Unresolved P0s: None.

- [2025-09-15] Migration summary recorded in option `fbm_db_migration_summary` (fields: `legacy_attendance_rows`, `attendance_migrated`, `attendance_skipped`, `legacy_checkins_rows`). Legacy check-ins remain under `_deprecated` tables for manual review.

- Pass A1 — bootstrap fixed + activation hook wired (foodbank-manager.php, includes/Core/Plugin.php, includes/Admin/ReportsPage.php, includes/Http/AttendanceExportController.php, includes/Core/Jobs/JobsWorker.php, includes/Admin/ScanPage.php)

## Enforcement Pass 3 — scope strip + migrate (2025-09-15)

- Scope strip: `includes/Admin/EventsPage.php` → deleted
- Scope strip: `includes/Attendance/{EventsRepo,ManualCheckinService,TicketsRepo,TicketService,CheckinsRepo,ReportsService}.php` → deleted
- Scope strip: `includes/Shortcodes/Dashboard*.php` → deleted
- Scope strip: `templates/public/dashboard.php` → deleted
- Scope strip: `templates/admin/{dashboard.php,events.php}` → deleted
- Scope strip: `includes/Rest/ScanController.php` → deleted
- Scope strip: `includes/Http/{DashboardExportController.php,TicketsController.php}` → deleted
- Added migration for `fbm_members`, `fbm_tokens`, `fbm_attendance`
- QA: `composer lint:sum` → 436 errors, 119 warnings (71 files)
- QA: `composer phpstan` → syntax error in `includes/Core/Plugin.php`
- QA: `composer test -v` → bootstrap parse error
- QA: `composer i18n:build` → success
- QA: `bash bin/package.sh` → built zip

## QA Toolchain Tightening (2025-09-15 15:53 BST)

- Refined phpcs.xml to use WordPress standard and exclude vendor, node_modules, dist, build, assets/*/vendor, languages/*.po, languages/*.pot, tests.
- Added composer scripts: lint, lint:sum, lint:src, fix.
- Restored valid plugin version constant in includes/Core/Plugin.php.


## ENFORCEMENT RUN — scope strip + apply (2025-09-15 15:36 BST)

- Updated dev tooling versions (phpunit, phpstan, phpcs, wpcs).
- Fixed plugin version constant in `includes/Core/Plugin.php`.
- No scope stripping or DB migrations performed yet.

## Change Plan (2025-09-15 15:22 BST)

- P1 • Inventory: plugin bootstrap (foodbank-manager.php) — refactor to drop legacy `fb_manage_*` caps and fallback dashboard; enforce capabilities and nonces. Specs.md §§66-83, D1.
- P1 • Admin menu consolidation: keep Theme, Diagnostics, Reports, minimal Settings; remove Database, Jobs, Emails, Forms, FormBuilder, Permissions, Events, Dashboard, Attendance, Scan, Shortcodes pages. Specs.md §§12-16, 66-83.
- P1 • Shortcode registry: register `[fbm_registration_form]` and `[fbm_staff_dashboard]`; delete `DashboardShortcode` and related templates/assets. Specs.md §§24-66.
- P1 • REST controllers: drop AttendanceController extras, ThrottleController, JobsController; implement single `/fbm/checkin` endpoint with nonce + `fbm_checkin` capability. Specs.md §§54-66.
- P1 • Attendance & Events data layer: remove EventsRepo, TicketsRepo, TicketService, CheckinsRepo; refactor AttendanceRepo to use `fbm_members`, `fbm_tokens`, `fbm_attendance` with additive migration and legacy tables soft-deprecated. Specs.md §§41-45, 94-119.
- P1 • Assets cleanup: prune JS/CSS for removed dashboards/events; gate remaining assets by screen and shortcode usage. Specs.md §§66-83.
- P1 • Uninstall.php: extend to optionally drop `fbm_*` tables and wipe secrets with explicit confirmation. Specs.md §F.
- P1 • Token service: ensure HMAC issue/verify/revoke of persistent member tokens without PII, constant-time compare. Specs.md §§98-107, C3.

## Change Plan (2025-09-15 14:08Z)

- P1 • Strip public analytics & dashboard: remove includes/Shortcodes/Dashboard*.php, includes/Admin/DashboardPage.php, templates/public/dashboard.php, templates/admin/dashboard.php. Specs.md lines 12-16.
- P1 • Remove Events feature & legacy tables: delete includes/Admin/EventsPage.php, templates/admin/events*.php, includes/Attendance/{EventsRepo, TicketsRepo, TicketService, CheckinsRepo}; deprecate fb_events/fb_tickets/fb_checkins tables. Specs.md lines 41-45,94-119.
- P1 • Refactor shortcodes to [fbm_registration_form] and [fbm_staff_dashboard]; remove [fbm_dashboard]. Specs.md lines 24-27,31-66.
- P1 • REST endpoint alignment: keep /fbm/checkin only; remove extra routes in AttendanceController. Specs.md lines 54-66.
- P1 • Update DB schema to fbm_members, fbm_tokens, fbm_attendance with additive migration; preserve legacy tables for rollback. Specs.md lines 94-119.
- P2 • Fix fbm_theme option group and asset gating per Plan U1. Specs.md lines 66-83.


## QA Log

- [2025-09-15 14:36Z] P0 • QA • composer lint: PHPCS found 113 sniff violations in tests/Support/Exceptions.php — TODO
- [2025-09-15 14:36Z] P0 • QA • composer phpcs: 20773 errors and 1452 warnings in 276 files — TODO
- [2025-09-15 14:36Z] QA • composer phpstan: no errors
- [2025-09-15 14:36Z] P0 • QA • composer test: 1 error, 10 failures — TODO
- [2025-09-15 14:36Z] QA • composer i18n:build: success
- [2025-09-15 14:36Z] QA • bin/package.sh: built dist/foodbank-manager.zip
- [2025-09-15 14:23Z] P0 • QA • composer lint: phpcs not found — TODO
- [2025-09-15 14:23Z] P0 • QA • composer phpcs: vendor/bin/phpcs not found — TODO
- [2025-09-15 14:23Z] P0 • QA • composer phpstan: phpstan not found — TODO
- [2025-09-15 14:23Z] P0 • QA • composer test: phpunit not found — TODO
- [2025-09-15 13:16Z] P0 • QA • composer lint: filename and doc comment errors in tests/Support/Exceptions.php — TODO
- [2025-09-15 13:16Z] P0 • QA • composer phpcs: deprecated sniffs in coding standard — TODO
- [2025-09-15 13:16Z] P0 • QA • composer phpstan: syntax error in includes/Core/Plugin.php line 12 — TODO
- [2025-09-15 13:16Z] P0 • QA • composer test: bootstrap parse error unexpected token — TODO

## Backlog

### Replace/remove Events references in code + DB migrator
[2025-09-15 13:16Z] P1 • DB • TODO

### REST & UI updates for fixed window policy
[2025-09-15 13:16Z] P1 • REST/UI • TODO

### Public dashboard removal / guards
[2025-09-15 13:16Z] P1 • Dashboard • TODO

### Permanent QR issuance in applicant email; revoke/regenerate admin tool
[2025-09-15 13:16Z] P1 • Email • TODO

### Packaging guard verification (upgrade replaces in place)
[2025-09-15 13:16Z] P1 • Packaging • TODO

## Milestones

### U1 — Foundations & Hardening
- Register fbm_theme option group/page/section/fields; add to allowed options.
- Add check_admin_referer('fbm_theme_save') and current_user_can('fbm_manage').
- Sanitize/clamp theme JSON; reject unknown keys; size caps.
- Enqueue guard: only FBM screens (screen-ID/hook suffix).
- Implement Uninstall.php (cleanup + destructive drop guarded).
- Token service:
  - Token::issue(member_id) → {raw_token, token_hash, version}
  - Token::verify(raw_token) constant-time match to token_hash
  - Token::revoke(member_id) → set revoked_at
- Config: weekly window defaults; option page for time window.
- PHPCS green, PHPStan green, unit tests green.

### U2 — Public Registration
- Build [fbm_registration_form] view + handlers; enqueue minimal assets.
- Server validation; anti-spam; transient throttle.
- Repo writes using prepared statements; status machine (pending/active/revoked).
- Email: template with QR image embed and fallback code.
- Admin actions: approve, resend QR, revoke.

### U3 — Staff Front-End Dashboard
- [fbm_staff_dashboard] layout (scanner + manual entry + session status).
- QR decode (small library), permission prompts, A11y labels.
- Endpoint /fbm/checkin:
  - Verify nonce & capability; throttle; idempotency (unique key member_id + date).
  - On success: insert fbm_attendance row; respond JSON.
  - On repeat: respond already_checked_in.
- UI feedback & counters.

### U4 — Internal Summaries & Export
- Summarize by date range; compute totals; active vs revoked.
- CSV stream (BOM + i18n headers) behind fbm_export + nonce.
- Cache layer; bust on new attendance insert.

### U5 — Diagnostics & Release
- Mail: recent failures view (redacted), secure resend with rate-limit.
- Packaging: version markers aligned; runbook; verify archive.
- Changelog + GitHub Release.

### Tests (representative)
- ThemePageSaveTest (allowed options, sanitize, clamps).
- TokenServiceTest (issue/verify/revoke; constant-time compare).
- RegistrationFormTest (validation, anti-spam, writes).
- CheckinEndpointTest (nonce/cap, rate-limit, idempotency).
- AttendanceRepoTest (unique guard, summaries).
- ExportCsvTest (headers, BOM, streaming).
- DiagnosticsMailTest (redacted output, resend throttle).

- [2025-09-15 15:53 BST] QA • composer lint: 583 errors, 185 warnings
- [2025-09-15 15:53 BST] QA • composer phpcs: 583 errors, 185 warnings
- [2025-09-15 15:53 BST] QA • composer phpstan: no errors
- [2025-09-15 15:53 BST] QA • composer test: 1 error, 10 failures
