> Canonical Doc — READ ME FIRST
> This is 1 of 4 canonical docs (Specs, Plan, Tasks, Matrix).
> Codex MUST read & update these BEFORE any work, every prompt.
> Any other files in /Docs are historical and must not drive scope.
> Docs-Revision: 2025-09-22 (v1.0.0 alignment)

# Matrix

| Feature | Admin | Manager | Staff | Public | Mobile | A11y | Security | PHPCS | PHPStan | Unit | Integration | E2E | Docs | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Forms (shortcode) | 🟨 | 🟨 | 🟨 | ✅ | [ ] | [ ] | ✅ | ✅ | ✅ | ⚠️ | [ ] | [ ] | [ ] | [Tasks](Tasks.md) |
| Emails (log/resend) | 🟨 | 🟨 | 🟨 | 🟨 | 🟨 | 🟨 | 🟨 | ✅ | ✅ | ⚠️ | 🟨 | 🟨 | 🟨 | [Task](Tasks.md#permanent-qr-issuance-in-applicant-email-revoke-regenerate-admin-tool) |
| Front-end Dashboard (auth) | 🟨 | 🟨 | 🟨 | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ⚠️ | [ ] | [ ] | [ ] | [Task](Tasks.md#public-dashboard-removal--guards) |
| Staff Check-in | 🟨 | 🟨 | 🟨 | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | [ ] | [Task](Tasks.md#u3--staff-front-end-dashboard); Final audit 2025-09-17 |
| Attendance Scan | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ⚠️ | [ ] | [ ] | [ ] | [Events](Tasks.md#replace-remove-events-references-in-code--db-migrator), [Policy](Tasks.md#rest--ui-updates-for-fixed-window-policy) |
| Attendance Manual | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | [ ] | [Events](Tasks.md#replace-remove-events-references-in-code--db-migrator), [Policy](Tasks.md#rest--ui-updates-for-fixed-window-policy); Final audit 2025-09-17 |
| Exports CSV/XLSX/PDF | ✅ | ✅ | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | [Tasks](Tasks.md) |
| Diagnostics | ✅ | [ ] | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | [ ] | Token probe (redacted) + mail failure log. |
| WP-CLI (fbm version) | ✅ | [ ] | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | ✅ | [Tasks](Tasks.md) |
| GDPR (Consent/SAR/Retention) | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ⚠️ | [ ] | [ ] | [ ] | [Tasks](Tasks.md) |
| Packaging Guard | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ✅ | ✅ | [ ] | [ ] | [Runbook](Runbooks/Release.md) | [Task](Tasks.md#packaging-guard-verification-upgrade-replaces-in-place) |
| Encryption (at rest) | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | ✅ | ✅ | ⚠️ | [ ] | [ ] | [ ] | [Tasks](Tasks.md) |



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

⬜ “Today” counters & feedback

U4 — Internal Summaries & Export

✅ Date-range summaries

✅ CSV export (admin only)

✅ Cache

U5 — Diagnostics & Release

⬜ Mail failures + resend

⬜ Packaging + version alignment

⬜ Changelog + Release


Cross-Cutting

✅ Public analytics removed

✅ Events feature removed

✅ No AIW integration

✅ No CRM/ERP integrations

✅ Shortcode scope limited to registration + staff dashboard

✅ i18n coverage

✅ Packaging excludes dev artifacts from release archives

⬜ Performance checks (assets, indexes, streaming)

