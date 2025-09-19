> Canonical Doc — READ ME FIRST
> This is 1 of 4 canonical docs (Specs, Plan, Tasks, Matrix).
> Codex MUST read & update these BEFORE any work, every prompt.
> Any other files in /Docs are historical and must not drive scope.
> Docs-Revision: 2025-09-22 (v1.0.0 alignment)

# Matrix

| Feature | Admin | Manager | Staff | Public | Mobile | A11y | Security | PHPCS | PHPStan | Unit | Integration | E2E | Docs | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Forms (shortcode) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | v1.1.1: shortcode hardened + admin approval/resend coverage |
| Emails (log/resend) | ✅ | ✅ | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | v1.1.1: diagnostics resend log with rate limiting |
| Front-end Dashboard (auth) | ✅ | ✅ | ✅ | [ ] | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | v1.1.1: scanner UI + REST nonce/throttle coverage |
| Staff Check-in | ✅ | ✅ | ✅ | [ ] | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | [ ] | ✅ | v1.1.1: /fbm/checkin throttling + override audit |
| Attendance Scan | ✅ | ✅ | ✅ | [ ] | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | v1.1.1: camera module + duplicate feedback |
| Attendance Manual | ✅ | ✅ | ✅ | [ ] | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | [ ] | ✅ | v1.1.1: manual fallback + manager override workflow |
| Exports CSV/XLSX/PDF | ✅ | ✅ | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | v1.0.10: streaming CSV with cache + BOM |
| Diagnostics | ✅ | ✅ | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | Token probe, mail failure log, resend gating |
| WP-CLI (fbm version) | ✅ | [ ] | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | [Tasks](Tasks.md) |
| GDPR (Consent/SAR/Retention) | ✅ | ✅ | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | v1.0.10: WP privacy exporter/eraser hooks + policy text. |
| Packaging Guard | ✅ | [ ] | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | ✅ | [ ] | ✅ | v1.1.1: bin/package.sh manifest + version alignment |
| Encryption (at rest) | ✅ | ✅ | ✅ | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | ✅ | [ ] | ✅ | Envelope encryption with AES-256-GCM + HKDF master key (v1.3.0) |



Release readiness & CI: ✅


Legend: ⬜ Planned | 🟨 In Progress | ✅ Done | ⚠️ Blocked/Error

U1 — Foundations

✅ fbm_theme allowed + save fixed

✅ Theme sanitize/clamp

✅ Admin asset gating by screen-ID

✅ Uninstall (cleanup + destructive)

✅ Token service (issue/verify/revoke)

✅ Policy (1/7) — Weekly window config (Thu 11:00–14:30)

✅ PHPCS zero; PHPStan green; PHPUnit baseline

U2 — Public Registration

✅ Registration shortcode

✅ Validation/anti-spam

✅ Member status flow (pending/active/revoked)

✅ Email with QR + code

✅ Admin approve/resend/revoke (v1.0.5)

U3 — Staff Front-End Dashboard

✅ Scanner (camera QR decode + manual fallback)

✅ /fbm/checkin (nonce, cap, throttle)

✅ Idempotent per member/day

✅ Weekly repeat block requires manager override + note audit

✅ “Today” counters & feedback

U4 — Internal Summaries & Export

✅ Date-range summaries

✅ CSV export (admin only)

✅ Cache

U5 — Diagnostics & Release

✅ Mail failures + resend

✅ Packaging + version alignment

✅ Changelog + Release


Cross-Cutting

✅ Public analytics removed

✅ Events feature removed

✅ No AIW integration

✅ No CRM/ERP integrations

✅ Shortcode scope limited to registration + staff dashboard

✅ i18n coverage

✅ Packaging excludes dev artifacts from release archives

✅ JS i18n & A11y complete; admin/templates WPCS reduced

⬜ Performance checks (assets, indexes, streaming)

