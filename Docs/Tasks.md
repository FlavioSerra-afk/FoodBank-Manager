> Canonical Doc — READ ME FIRST
> This is 1 of 4 canonical docs (Specs, Plan, Tasks, Matrix).
> Codex MUST read & update these BEFORE any work, every prompt.
> Any other files in /Docs are historical and must not drive scope.

# Tasks

## Change Plan (2025-09-15 14:08Z)

- P1 • Strip public analytics & dashboard: remove includes/Shortcodes/Dashboard*.php, includes/Admin/DashboardPage.php, templates/public/dashboard.php, templates/admin/dashboard.php. Specs.md lines 12-16.
- P1 • Remove Events feature & legacy tables: delete includes/Admin/EventsPage.php, templates/admin/events*.php, includes/Attendance/{EventsRepo, TicketsRepo, TicketService, CheckinsRepo}; deprecate fb_events/fb_tickets/fb_checkins tables. Specs.md lines 41-45,94-119.
- P1 • Refactor shortcodes to [fbm_registration_form] and [fbm_staff_dashboard]; remove [fbm_dashboard]. Specs.md lines 24-27,31-66.
- P1 • REST endpoint alignment: keep /fbm/checkin only; remove extra routes in AttendanceController. Specs.md lines 54-66.
- P1 • Update DB schema to fbm_members, fbm_tokens, fbm_attendance with additive migration; preserve legacy tables for rollback. Specs.md lines 94-119.
- P2 • Fix fbm_theme option group and asset gating per Plan U1. Specs.md lines 66-83.


## QA Log

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

