Docs-Revision: 2025-09-06 (v1.2.13 fragments merged)

## Active Issues
- [P0] PHPUnit WP stub gaps → Fixed (Prompt P)
- [P1] Retention config normalization → Fixed (Prompt Q)
- [P1] Admin notices duplicates → Fixed; Diagnostics shows render count
- [P2] PHPCS flood from dist/ → Fixed (Prompt R); remaining items are docblocks/escaping
- [P2] RBAC test failures → run `composer test`; use helpers `fbm_grant_admin_only`, `fbm_grant_fbm_all`, `fbm_grant_for_page` to align permissions
- [P2] Unit tests failing with bad nonce — fixed by deterministic WP nonce stubs (Wave AQ)

## Resolved Issues
- Menu not visible on fresh install → Administrators now self-heal capabilities; Diagnostics includes a Repair caps control.
- Duplicate admin blocks → Resolved in v1.2.12.
- Duplicate installs → one-click Consolidate cleanup handles stray copies.
- Duplicate install cleanup now logs actions and exposes controls in Diagnostics (Prompt AY).

# FoodBank Manager — Issues & Milestones

This file maps **milestones → epics → issues** so you can paste into GitHub.
Use alongside the PRD: `PRD-foodbank-manager.md`.

_Post-release notes (2025-09-04): Reinstalled v1.2.7 ZIP on staging with quick click-through — no follow-ups identified._

---

## How to use
1) **Create milestones** in GitHub matching M1–M6 below (with target dates).  
2) **Create labels** (copy from the list at the end).  
3) For each issue, copy the **Title** and **Body** into a new GitHub Issue.  
4) Assign the issue to the appropriate **Milestone** and add **Labels**.  
5) Track dependencies using `Blocked by #123 / Blocks #456` in the body.

---

## M1 — Foundations (Infra & Security)
**Goal:** Project scaffolding, custom tables, roles/caps, nonces, encryption skeleton, Action Scheduler.

### Epic: Plugin Scaffolding & Core
**Issue: Initialize plugin structure**
- **Title:** feat(core): initialize plugin skeleton and autoloader
- **Body:**
  - **Description:** Set up plugin folder, PSR-4 autoloader, main bootstrap file, service container, and basic admin menu.
  - **Tasks:**
    - [ ] Create `foodbank-manager.php` bootstrap.
    - [ ] Add `/includes` classes (Plugin, ServiceProvider).
    - [ ] Register admin menu placeholder.
    - [ ] Set up Composer + PHPCS + PHPStan.
  - **Acceptance Criteria:**
    - Plugin activates without notices.
    - Unit test suite boots; coding standards pass.
  - **Labels:** area:core, type:feature, good first issue
  - **PRD Refs:** §15 M1, §5.5

**Issue: Database migrations**
- **Title:** feat(db): create custom tables and migrator
- **Body:**
  - **Description:** Implement activation hook + versioned migrations for `fb_applications`, `fb_attendance`, `fb_events`, `fb_mail_log`, `fb_files`.
  - **Tasks:**
    - [ ] Write DDL per Appendix A.
    - [ ] Add `db_version` option; safe upgrades.
    - [ ] Add indexes; charset/collation utf8mb4.
  - **Acceptance Criteria:**
    - Activation creates tables with indexes.
    - Re-activation is idempotent; upgrades handled.
  - **Labels:** area:db, type:feature, security
  - **PRD Refs:** Appendix A, §8

**Issue: Roles & capabilities**
- **Title:** feat(auth): register custom roles & caps
- **Body:**
  - **Description:** Add roles: Viewer, Manager; map granular caps for entries, forms, settings, encryption, attendance.
  - **Tasks:**
    - [ ] Define caps list (§10.1).
    - [ ] Seeder on activation; removal on uninstall.
    - [ ] Capability checks helper.
  - **Acceptance Criteria:**
    - Roles created; caps enforced on a sample route.
  - **Labels:** area:auth, type:feature, security
  - **PRD Refs:** §3, §10.1

**Issue: Permissions manager**
- **Title:** feat(auth): permissions manager for roles and users
- **Body:**
  - **Description:** Add Permissions tab to map plugin caps to roles (Administrator fixed) and set per-user overrides with JSON import/export and reset.
  - **Tasks:**
    - [ ] Role mapping UI and storage
    - [ ] User override resolver
    - [ ] Import/export and reset actions
    - [ ] Follow-ups: granular Settings page integration, audit log for permission changes, multisite network mapping
  - **Acceptance Criteria:**
    - Administrator always retains all capabilities
  - **Labels:** area:auth, type:feature, security
  - **PRD Refs:** §5.5 Permissions

**Issue: Security middleware (nonces, sanitization, escaping)**
- **Title:** feat(security): nonces & sanitization utilities
- **Body:**
  - **Description:** Implement common functions for nonce verification, sanitization, escaping, and error handling.
  - **Tasks:**
    - [ ] Request validator with nonce check.
    - [ ] Sanitizers for input types.
    - [ ] Output escapers for HTML/attr/url.
  - **Acceptance Criteria:**
    - All POST handlers in M2+ must use these utilities.
  - **Labels:** area:security, type:feature
  - **PRD Refs:** §10.1

**Issue: Encryption skeleton (libsodium envelope)**
- **Title:** feat(crypto): envelope encryption scaffolding
- **Body:**
  - **Description:** Implement KEK/DEK model, key storage, AEAD helpers (XChaCha20-Poly1305), and test vectors.
  - **Tasks:**
    - [ ] KEK reader from env/wp-config.
    - [ ] DEK generation & wrapping.
    - [ ] Encrypt/decrypt helpers + field registry.
    - [ ] Unit tests with known vectors.
  - **Acceptance Criteria:**
    - Sensitive sample payload encrypts/decrypts; ciphertext is non-deterministic; auth tag verified.
  - **Labels:** area:security, type:feature, crypto
  - **PRD Refs:** §10.2

**Issue: Background jobs**
- **Title:** feat(queue): integrate Action Scheduler
- **Body:**
  - **Description:** Bundle/setup Action Scheduler for exports, email retries, key rotation.
  - **Tasks:**
    - [ ] Install library; admin screen badge for queue size.
    - [ ] Register queues: exports, mail, re-encrypt.
  - **Acceptance Criteria:**
    - Jobs enqueue/run; retries logged.
  - **Labels:** area:queue, type:feature, performance
  - **PRD Refs:** §7, §10.3

---

## M2 — Forms & Submissions
**Goal:** Visual builder, rendering, validation, files, emails, logging, default preset.

### Epic: Form Builder
**Issue: Form definition schema**
- **Title:** feat(builder): define JSON schema for forms & fields
- **Body:**
  - **Description:** Create versioned JSON schema for fields, steps, logic, validations, theming.
  - **Tasks:**
    - [ ] Schema v1 with examples.
    - [ ] Import/export as JSON.
  - **Acceptance Criteria:**
    - Schema validates via JSON Schema; round-trip import/export works.
  - **Labels:** area:builder, type:feature
  - **PRD Refs:** §5.1, §8

**Issue: Visual builder UI**
- **Title:** feat(builder): drag-drop field editor + properties panel
- **Body:**
  - **Description:** Field palette, drag-drop ordering, properties panel, condition builder.
  - **Tasks:**
    - [ ] Palette components (text, email, select, file, consent, repeater, etc.).
    - [ ] Stepper & progress.
    - [ ] Conditional logic editor.
  - **Acceptance Criteria:**
    - Form can be built without code; stored JSON reflects UI.
  - **Labels:** area:builder, type:feature, ui

**Issue: Form rendering (shortcode & block)**
- **Title:** feat(frontend): render form from JSON (shortcode + block)
- **Body:**
  - **Description:** Server-rendered markup + minimal JS; Gutenberg block wrapper.
  - **Tasks:**
    - [ ] `[fb_form]` shortcode.
    - [ ] Block attributes mirror shortcode props.
  - **Acceptance Criteria:**
    - Form displays correctly; a11y checks pass.
  - **Labels:** area:frontend, type:feature, accessibility

**Issue: Validation & submission pipeline**
- **Title:** feat(forms): server-side validation & submission pipeline
- **Body:**
  - **Description:** Validate inputs, handle file uploads, write to DB, encrypt sensitive fields.
  - **Tasks:**
    - [ ] MIME/size checks; randomized filenames.
    - [ ] Consent log (timestamp, IP, text hash).
  - **Acceptance Criteria:**
    - Invalid input blocked; valid submission inserted with encrypted blob.
  - **Labels:** area:forms, area:security, type:feature
  - **PRD Refs:** §5.3, §10

**Issue: Email templates & tokens**
- **Title:** feat(email): applicant + admin templates with tokens
- **Body:**
  - **Description:** WYSIWYG HTML templates; token resolver.
  - **Tasks:**
    - [ ] Preview with sample data; test send.
    - [ ] Admin recipients + conditional routing.
  - **Acceptance Criteria:**
    - Emails send; logs show success/failure.
  - **Labels:** area:email, type:feature
  - **PRD Refs:** §5.4

**Issue: Mail logging & diagnostics**
- **Title:** feat(diagnostics): wp_mail logging + failures view
- **Body:**
  - **Description:** Hook into `wp_mail_*`; diagnostics screen with resend.
  - **Tasks:**
    - [ ] Log to `fb_mail_log`.
    - [ ] UI filters: status/date/recipient.
  - **Acceptance Criteria:**
    - Failed sends visible; resend works.
  - **Labels:** area:email, area:diagnostics, type:feature
  - **PRD Refs:** §5.4, §5.5, §5.5 Diagnostics

**Issue: Default Food Bank Intake preset**
- **Title:** feat(preset): ship Food Bank Intake form
- **Body:**
  - **Description:** Recreate https://pcclondon.uk/food-bank/ fields and copy as a starter form template.
  - **Tasks:**
    - [ ] Match labels, required flags, order.
    - [ ] PT translation for front-end strings.
  - **Acceptance Criteria:**
    - Visual parity with live page on mobile/desktop.
  - **Labels:** area:builder, type:content
  - **PRD Refs:** §4, §5.1

---

## M3 — Admin & Front-End Dashboards
**Goal:** Data tables, filters, exports, roles.

**Issue: Admin Database grid (WP_List_Table)**
- **Title:** feat(admin): entries table with filters & bulk actions
- **Body:**
  - **Tasks:**
    - [ ] Filters: date/status/city/postcode/consent/has file.
    - [ ] Bulk: export CSV/XLSX, delete.
  - **Acceptance Criteria:**
    - Pagination & filters are server-side and fast (indexed).
  - **Labels:** area:admin, type:feature, performance
  - **PRD Refs:** §5.5

**Issue: Entry detail & edit view**
- **Title:** feat(admin): single entry view + edit + PDF
- **Body:**
  - **Tasks:**
    - [ ] Read encrypted fields; audit notes.
    - [ ] Generate single-entry PDF.
  - **Acceptance Criteria:**
    - Edit persists; PDF downloads with letterhead.
  - **Labels:** area:admin, type:feature, pdf
  - **PRD Refs:** §5.5, §5.7

**Issue: Front-end dashboard (Viewer/Manager)**
- **Title:** feat(frontend): read-only entries dashboard with exports
- **Body:**
  - **Tasks:**
    - [ ] `[foodbank_entries]` shortcode + REST.
    - [ ] Filters/search/sort/pagination; CSV/XLSX/PDF.
  - **Acceptance Criteria:**
    - Unauthorized users blocked; authorized can export.
  - **Labels:** area:frontend, type:feature
  - **PRD Refs:** §5.6, §5.7

---

## M4 — Attendance
**Goal:** Events (optional), QR issuance, scan/manual check-in, policy rules, reports.

**Issue: Harden AttendanceRepo**
- **Title:** chore(cs): make AttendanceRepo SQL strict and remove ignoreFile
- **Body:**
  - **Tasks:**
    - [ ] Replace string concatenation with `$wpdb->prepare()` and strict placeholders.
    - [ ] Ensure attendance queries mask PII by default.
    - [ ] Add unit tests for check-in, no-show, void/unvoid, timeline queries, and SQL injection attempts.
  - **Acceptance Criteria:**
    - No `phpcs:ignoreFile` on `includes/Attendance/AttendanceRepo.php`; tests cover policy edges.
  - **Labels:** area:attendance, type:chore, security
  - **PRD Refs:** §6.3, §10

**Issue: Events model & UI **
- **Title:** feat(attendance): events CRUD & listing
- **Body:**
  - **Tasks:**
    - [ ] Create sessions (title, times, capacity).
    - [ ] Event picker in dashboard.
  - **Acceptance Criteria:**
    - Events filter attendance list; capacity visible.
  - **Labels:** area:attendance, type:feature
  - **PRD Refs:** §6.2

**Issue: QR issuance & email embedding**
- **Title:** feat(attendance): generate QR codes (no PII) and embed in emails
- **Body:**
  - **Tasks:**
    - [ ] Opaque token + HMAC; server verification.
    - [ ] Regenerate/revoke per applicant.
  - **Acceptance Criteria:**
    - Scanable on mobile; token not reversible from DB.
  - **Labels:** area:attendance, area:security, type:feature
  - **PRD Refs:** §6.4

**Issue: Scan mode (front-end)**
- **Title:** feat(attendance): camera scanner UI with instant feedback
- **Body:**
  - **Tasks:**
    - [ ] HTTPS-only camera access; a11y fallback.
    - [ ] Green/amber/red states; sound toggle.
  - **Acceptance Criteria:**
    - Valid token → present record < 300ms (server time).
  - **Labels:** area:attendance, area:frontend, type:feature, performance
  - **PRD Refs:** §6.3

**Issue: Manual check-in & override**
- **Title:** feat(attendance): manual search + present/no-show + override with note
- **Body:**
  - **Acceptance Criteria:**
    - Policy breach shows warning; override requires a note; audit logs include actor.
  - **Labels:** area:attendance, type:feature
  - **PRD Refs:** §6.3, §6.5

**Issue: Attendance reports & exports**
- **Title:** feat(attendance): reports (today/week/month) + CSV/XLSX/PDF
- **Body:**
  - **Acceptance Criteria:**
    - Counts match filtered lists; exports respect filters.
  - **Labels:** area:attendance, area:reporting, type:feature
  - **PRD Refs:** §6.3

---

## M5 — GDPR & Diagnostics
**Goal:** SAR, retention/anonymisation, diagnostics panel, test email.

**Issue: SAR export**
- **Title:** feat(gdpr): subject access request (SAR) export by email
- **Body:**
  - **Tasks:**
    - [ ] Package entries + attendance + files into ZIP.
    - [ ] Redact internal notes if configured.
    - [ ] Harden SAR exports with streaming ZIP (HTML fallback) and masked data by default.
  - **Acceptance Criteria:**
    - Export completes; logs include who ran it.
  - **Labels:** area:gdpr, type:feature, privacy
  - **PRD Refs:** §5.8, §11

**Issue: Retention & anonymisation jobs**
- **Title:** feat(gdpr): retention policy & anonymisation cron
- **Body:**
  - **Acceptance Criteria:**
    - Records older than threshold anonymised; counters preserved.
  - **Labels:** area:gdpr, type:feature
  - **PRD Refs:** §5.8, §10.4

**Issue: Diagnostics panel**
- **Title:** feat(diagnostics): environment checks + mail tests
- **Body:**
  - **Acceptance Criteria:**
    - Panel shows SMTP/transport, cron health, PHP/WP versions; test email logs result.
  - **Labels:** area:diagnostics, type:feature
  - **PRD Refs:** §5.5 Diagnostics, §18

---

## M6 — Polish
**Goal:** PDF theming, design presets, PT translations, docs.

**Issue: PDF theming & letterhead**
- **Title:** feat(pdf): letterhead templates + theming tokens
- **Body:**
  - **Acceptance Criteria:**
    - Single-entry and bulk PDFs render with branding; a11y text preserved.
  - **Labels:** area:pdf, type:feature
  - **PRD Refs:** §5.7

**Issue: Theme presets (light/dark/high-contrast)**
- **Title:** feat(ui): add design presets & tokenized CSS
- **Body:**
  - **Acceptance Criteria:**
    - Switching preset updates all forms without markup changes.
  - **Labels:** area:ui, type:feature, accessibility
  - **PRD Refs:** §5.1 Theming

**Issue: PT translations & i18n audit**
- **Title:** chore(i18n): ship PT translation + .pot
- **Body:**
  - **Acceptance Criteria:**
    - Front-end strings translated; .pot generated.
  - **Labels:** i18n, chore
  - **PRD Refs:** §7, §5.1

**Issue: Documentation set**
- **Title:** docs: setup guide, builder guide, attendance guide, GDPR playbook
- **Body:**
  - **Acceptance Criteria:**
    - Docs pass internal review; screenshots included.
  - **Labels:** documentation
  - **PRD Refs:** §19

---

## Labels to create
- `area:core`, `area:db`, `area:auth`, `area:security`, `area:queue`, `area:builder`, `area:frontend`, `area:admin`, `area:forms`, `area:email`, `area:diagnostics`, `area:attendance`, `area:reporting`, `area:pdf`, `area:ui`, `area:gdpr`, `i18n`, `documentation`
- `type:feature`, `type:bug`, `type:chore`, `type:refactor`
- `priority:p0`, `priority:p1`, `priority:p2`
- `good first issue`, `help wanted`

---

## Optional: Issue template (paste into .github/ISSUE_TEMPLATE/feature.yml)
```yaml
name: Feature request
description: Track a new feature or enhancement
title: "feat: "
labels: ["type:feature"]
body:
  - type: textarea
    attributes:
      label: Summary
      description: What problem does this solve? Link PRD section.
  - type: textarea
    attributes:
      label: Tasks
      description: Checklist of subtasks
      value: |
        - [ ] 
  - type: textarea
    attributes:
      label: Acceptance Criteria
      value: |
        - [ ] 
  - type: input
    attributes:
      label: PRD Reference
      placeholder: "§5.1"
```

---

## Optional: Milestone planning commands (GitHub CLI)
> Replace dates as needed.
```bash
gh milestone create "M1 — Foundations" -d "Core scaffolding, db, security" -D 2025-09-15
gh milestone create "M2 — Forms & Submissions" -d "Builder, emails, logs" -D 2025-10-01
gh milestone create "M3 — Dashboards" -d "Admin + Front-end dashboards" -D 2025-10-15
gh milestone create "M4 — Attendance" -d "Events, QR, scan/manual" -D 2025-11-01
gh milestone create "M5 — GDPR & Diagnostics" -d "SAR, retention, self-checks" -D 2025-11-15
gh milestone create "M6 — Polish" -d "PDF theming, presets, docs" -D 2025-12-01
```
