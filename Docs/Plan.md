> Canonical Doc — READ ME FIRST
> This is 1 of 4 canonical docs (Specs, Plan, Tasks, Matrix).
> Codex MUST read & update these BEFORE any work, every prompt.
> Any other files in /Docs are historical and must not drive scope.
> Docs-Revision: 2025-09-22 (v1.0.0 alignment)

# Plan

## Milestones

U1 — Foundations & Hardening (P0)

Theme Save Fix — ✅ fbm_theme option registered/allowed with nonce + capability enforcement and payload sanitization/clamping.

Asset Gating — ✅ FBM assets gated to FBM admin screens and staff dashboard requests.

Uninstall — ✅ Options cleaned on uninstall with guarded destructive drop for tables/secrets.

Token Framework — ✅

Token format + signer (hash_hmac(sha256, payload, secret)).

Storage of token_hash only; revocation handling.

Weekly Window Config — ✅ Schedule helper + admin page storing day/time/timezone with defaults (Thu 11:00–14:30 Europe/London).

QA Baseline — ✅ PHPCS green (delta), PHPStan green, unit tests pass.

Remove Events dependencies: migrate to fixed weekly window; provide DB migrator.

U2 — Public Registration

[fbm_registration_form] (fields, validation, nonce, anti-spam).

Member creation (pending|active per config); email template engine.

Welcome Email with attached QR image and fallback code; regenerate on re-send.

Registration editor delivers simple conditional visibility rules (phase-1) with server enforcement and publishes the template tag matrix for administrators.

### Public User Flow

1. Visitor submits the public registration form (shortcode) with validated fields.
2. FBM auto-creates the WordPress user, ignores legacy WP roles, and assigns the FoodBank Member role.
3. System sends the welcome email containing the persistent QR code and fallback alphanumeric code.
4. Admin/Manager/Staff verifies the QR at check-in using authorized FBM tooling.
5. Attendance service stores the successful visit in fbm_attendance for reporting.

U3 — Staff Front-End Dashboard (Scanner)

[fbm_staff_dashboard] (requires login + fbm_view or higher).

Camera permissions, A11y flow, QR decoding; manual code entry fallback.

/fbm/checkin endpoint: nonce + fbm_checkin cap; rate-limit; idempotent per member/day; attendance write.

UI feedback (success/already/error), counters for “today.”

U4 — Internal Summaries & Export

✅ Date-range summaries (no PII outside admin); allow-listed filters.

✅ CSV export behind fbm_export + nonce; UTF-8 BOM; sanitized filename; localized headers.

✅ Short-TTL caches for common ranges.

U5 — Diagnostics & Release

✅ Mail failures view + secure resend (throttle).

✅ Packaging/runbook; version alignment; changelog.

✅ WP-CLI: `wp fbm version` registered on boot; stub harness + unit tests enforce output.

GitHub Release; verify zip contents.

Ongoing Engineering

Design tokens → CSS variables → .fbm-scope.

Testing: shared BaseTestCase, deterministic nonces, capability stubs.

CI gates: PHPCS, PHPStan, PHPUnit, i18n, packaging—fail on deltas.
