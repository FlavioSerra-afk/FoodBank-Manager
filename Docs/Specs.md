> Canonical Doc — READ ME FIRST
> This is 1 of 4 canonical docs (Specs, Plan, Tasks, Matrix).
> Codex MUST read & update these BEFORE any work, every prompt.
> Any other files in /Docs are historical and must not drive scope.

# Specs

## Purpose & scope
- mobile-first intake; encrypted storage; front-end dashboard for staff; attendance tracking (QR/manual); diagnostics; GDPR helpers.

## Roles & capabilities
- Public (forms only) • Staff/Volunteer (scan/manual attendance) • Manager (scan/manual + data views/exports) • Admin (all).

## Weekly service window
- Thursday 11:00–14:30 Europe/London (constant); no Events page.

## Forms
- shortcode + builder; CAPTCHA; server-side validation; consent hash; safe uploads.

## Emails
- applicant confirmation (with permanent QR), admin notification; log & resend.

## Attendance
- QR token opaque & signed (HMAC), reusable; frequency rule 1/7 days with override + required note; duplicate-scan guard; timeline; exports.

## Front-end dashboard (auth required)
- tiles (aggregates only, no PII), scan/manual tabs for permitted roles, CSV export (masked by default).

## Security baseline
- nonces, caps, masked exports, libsodium AEAD envelope; no PII in logs.

## Non-functional
- server-side pagination, indexes, Action Scheduler for exports; accessibility fallbacks; High-Contrast & reduced-transparency modes.

## Out-of-scope
- building a general CRM or case-management suite beyond the defined data model.
- real-time sync with external ERPs/CRMs.
- custom Gutenberg blocks beyond those explicitly listed in this Specs.

