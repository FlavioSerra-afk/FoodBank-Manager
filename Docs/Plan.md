> Canonical Doc — READ ME FIRST
> This is 1 of 4 canonical docs (Specs, Plan, Tasks, Matrix).
> Codex MUST read & update these BEFORE any work, every prompt.
> Any other files in /Docs are historical and must not drive scope.

# Plan

## Milestones

U1 — Foundations & Hardening (P0)

Theme Save Fix: ensure fbm_theme option group/page is allowed; align slug/group/nonce/capability; sanitize & clamp payload.

Asset Gating: only enqueue FBM assets on FBM screens (screen-ID / hook suffix fallback).

Uninstall: implement cleanup + optional destructive drop; wipe secrets.

Token Framework:

Token format + signer (hash_hmac(sha256, payload, secret)).

Storage of token_hash only; revocation handling.

Weekly Window Config: default Thursday 11:00–14:30; server constants + option.

QA Baseline: PHPCS green (delta), PHPStan green, unit tests pass.

U2 — Public Registration

[fbm_registration_form] (fields, validation, nonce, anti-spam).

Member creation (pending|active per config); email template engine.

Welcome Email with attached QR image and fallback code; regenerate on re-send.

U3 — Staff Front-End Dashboard (Scanner)

[fbm_staff_dashboard] (requires login + fbm_view or higher).

Camera permissions, A11y flow, QR decoding; manual code entry fallback.

/fbm/checkin endpoint: nonce + fbm_checkin cap; rate-limit; idempotent per member/day; attendance write.

UI feedback (success/already/error), counters for “today.”

U4 — Internal Summaries & Export

Date-range summaries (no PII outside admin); allow-listed filters.

CSV export behind fbm_export + nonce; UTF-8 BOM; localized headers.

Short-TTL caches for common ranges.

U5 — Diagnostics & Release

Mail failures view + secure resend (throttle).

Packaging/runbook; version alignment; changelog.

GitHub Release; verify zip contents.

Ongoing Engineering

Design tokens → CSS variables → .fbm-scope.

Testing: shared BaseTestCase, deterministic nonces, capability stubs.

CI gates: PHPCS, PHPStan, PHPUnit, i18n, packaging—fail on deltas.

3) TASKS — Step-by-Step (live error capture on top)

Rule: If any error/warning occurs, add it at the top with ⚠️ and fix before continuing.

(Live Error Log)

(empty — start here; Codex appends items as they arise and elevates them to P0)

U1 — Foundations & Hardening

 Register fbm_theme option group/page/section/fields; add to allowed options.

 Add check_admin_referer('fbm_theme_save') and current_user_can('fbm_manage').

 Sanitize/clamp theme JSON; reject unknown keys; size caps.

 Enqueue guard: only FBM screens (screen-ID/hook suffix).

 Implement Uninstall.php (cleanup + destructive drop guarded).

 Token service:

 Token::issue(member_id) → {raw_token, token_hash, version}

 Token::verify(raw_token) constant-time match to token_hash

 Token::revoke(member_id) → set revoked_at

 Config: weekly window defaults; option page for time window.

 PHPCS green, PHPStan green, unit tests green.

U2 — Public Registration

 Build [fbm_registration_form] view + handlers; enqueue minimal assets.

 Server validation; anti-spam; transient throttle.

 Repo writes using prepared statements; status machine (pending/active/revoked).

 Email: template with QR image embed and fallback code.

 Admin actions: approve, resend QR, revoke.

U3 — Staff Front-End Dashboard

 [fbm_staff_dashboard] layout (scanner + manual entry + session status).

 QR decode (small library), permission prompts, A11y labels.

 Endpoint /fbm/checkin:

 Verify nonce & capability; throttle; idempotency (unique key member_id + date).

 On success: insert fbm_attendance row; respond JSON.

 On repeat: respond already_checked_in.

 UI feedback & counters.

U4 — Internal Summaries & Export

 Summarize by date range; compute totals; active vs revoked.

 CSV stream (BOM + i18n headers) behind fbm_export + nonce.

 Cache layer; bust on new attendance insert.

U5 — Diagnostics & Release

 Mail: recent failures view (redacted), secure resend with rate-limit.

 Packaging: version markers aligned; runbook; verify archive.

 Changelog + GitHub Release.

Tests (representative)

 ThemePageSaveTest (allowed options, sanitize, clamps).

 TokenServiceTest (issue/verify/revoke; constant-time compare).

 RegistrationFormTest (validation, anti-spam, writes).

 CheckinEndpointTest (nonce/cap, rate-limit, idempotency).

 AttendanceRepoTest (unique guard, summaries).

 ExportCsvTest (headers, BOM, streaming).

 DiagnosticsMailTest (redacted output, resend throttle).
