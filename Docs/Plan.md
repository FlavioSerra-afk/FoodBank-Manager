> Canonical Doc — READ ME FIRST
> This is 1 of 4 canonical docs (Specs, Plan, Tasks, Matrix).
> Codex MUST read & update these BEFORE any work, every prompt.
> Any other files in /Docs are historical and must not drive scope.

# Plan

## Milestones

### M1 Foundations
- Entry: repository bootstrap complete.
- Exit: DB schema, roles/caps, encryption skeleton, packaging guard in place.
- QA Gates: PHPCS=0, PHPStan=0, tests green.
- Rollback: deactivate plugin, restore prior DB backup.

### M2 Forms & Emails
- Entry: M1 exit.
- Exit: form builder and submission pipeline with logging and emails.
- QA Gates: PHPCS=0, PHPStan=0, tests green.
- Rollback: remove new tables, revert templates.

### M3 Dashboards
- Entry: M2 exit.
- Exit: admin and front-end dashboards for staff/manager.
- QA Gates: PHPCS=0, PHPStan=0, tests green.
- Rollback: disable dashboard menus, revert templates.

### M4 Attendance
- Entry: M3 exit.
- Exit: fixed weekly window, scan/manual check-in, frequency policy.
- QA Gates: PHPCS=0, PHPStan=0, tests green.
- Rollback: remove attendance UI, keep data.

### M5 GDPR & Diagnostics
- Entry: M4 exit.
- Exit: consent/SAR/retention flows, diagnostics.
- QA Gates: PHPCS=0, PHPStan=0, tests green.
- Rollback: disable endpoints, purge logs.

### M6 Polish & i18n
- Entry: M5 exit.
- Exit: performance tuning, translations, accessibility.
- QA Gates: PHPCS=0, PHPStan=0, tests green.
- Rollback: revert strings/assets.

## Risk log
- Email deliverability — use SMTP fallback, log bounces.
- Key management — document rotation procedure, restrict access.
- Camera permissions — provide manual entry fallback.

## Performance budgets
- ≤300ms typical queries.
- <2s submission.

