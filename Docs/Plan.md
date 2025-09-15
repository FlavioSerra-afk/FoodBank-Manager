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

Remove Events dependencies: migrate to fixed weekly window; provide DB migrator.

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
