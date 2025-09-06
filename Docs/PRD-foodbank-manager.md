Docs-Revision: 2025-09-07 (v1.2.15 fragments merged)
# FoodBank Manager — Product Requirements Document (PRD)

**Repo file:** `Docs/PRD-foodbank-manager.md`  
**Project:** FoodBank Manager (WordPress plugin)  
**Owner:** Portuguese Community Centre London  
**Version:** 1.0 (initial)  
**Date:** 1 Sep 2025 (Europe/London)
**Status:** Approved for build

**Packaging:** Release ZIP root must be `foodbank-manager/` for in-place updates. A packaging guard enforces the slug and offers one-click consolidation for duplicates.

---

## 1) Overview

FoodBank Manager is a secure, mobile-first WordPress plugin to:

- Collect Food Bank applications via customizable, multi-step forms.
- Notify applicants and admins by email with HTML templates and logging.
- Store submissions in a secure database with encryption at rest.
- Provide admin and front-end dashboards to search, filter, export, and (for Managers) **track attendance** at distribution sessions.
- Allow administrators to configure branding and default email senders via a validated settings page.
 - Offer a Design & Theme settings page for primary colour, density, font, default dark mode and optional custom CSS; inputs are sanitised and theme CSS variables are deterministic with clamped defaults applied across admin and front-end.
- Admin pages are wrapped in a namespaced `.fbm-admin` container with plugin CSS and notices loaded only on FoodBank Manager screens.
- Each admin page renders exactly once per request via a shared `RenderOnce` guard; no page may echo UI outside its submenu callback.
- Administrators always see a FoodBank parent menu (falls back to `manage_options`), while subpages remain FBM-capability gated; Diagnostics includes a nonce-protected **Repair caps** button and duplicate-install consolidation.
- Diagnostics records the last successful boot timestamp (`fbm_boot_ok`) for display on the Diagnostics screen.
- Ensure compliance with UK GDPR and follow best-practice WordPress security.

### Trace Comments (RenderOnce)
- Comments: `<!-- fbm-render {key} pass={n} -->` (one line).
- Emitted right after `.wrap.fbm-admin` opens on admin pages.
- Diagnostics badge shows "RenderOnce OK" unless duplicate passes are detected.

The plugin must reproduce the current Food Bank form at `https://pcclondon.uk/food-bank/` exactly as a starter preset, and allow building additional custom forms with full control over fields, layout, logic, and data handling. Shortcode-driven forms store schemas in presets with strict validation, CAPTCHA, and an admin builder with live preview.

---

## 2) Objectives & Success Criteria

**Objectives**
- Accurate data capture, fast review, and minimal friction on mobile.
- Robust email delivery and diagnostics (no silent failures).
- Strong security (encryption, least-privilege, auditability).
- Operational tooling: attendance tracking + exports + reports.

**Success Criteria (KPIs)**
- < 2s median submission time.
- 100% outgoing emails logged (sent/failed).
- < 1% form validation bounce due to unclear UI.
- SAR data export in ≤ 2 clicks per subject.
- Attendance check-in in ≤ 2 taps (scan or manual).

---

## 3) Users & Roles

- **Public Applicant** — submits forms; receives confirmation.
- **FoodBank Viewer** — read-only access (filters, exports) and **register attendance**.
- **FoodBank Manager** — everything Viewer can do + edit entries and **register attendance**.
- **Administrator** — full plugin control (settings, forms, encryption, email, diagnostics).

Roles map to WordPress **capabilities** (granular; see §10 Security).

---

## 4) Scope

### In Scope (v1)
- Multi-form builder (CPT `fb_form`) with visual editor.
- A default **Food Bank Intake** preset replicating the current live form (same labels, required fields, order).
- Front-end form shortcode/block and success screen.
- Email confirmations (applicant) and notifications (admin); HTML templates.
- Data storage in custom tables with **field-level encryption**.
- Back-end Database tab (list, view, edit, delete, export).
- Front-end dashboard (authenticated) for **Viewer/Manager** with accessible filters, loading skeletons, and empty states; optional filters (event, type, policy-only) and an aggregated CSV export; **Manager** can mark attendance.
- Diagnostics tab: email logs, resend, test email, repair caps, environment checks.
- Exports: CSV/XLSX, single & bulk PDF.
- GDPR: consent logs, SAR export, retention/anonymisation.

### Out of Scope (v1)
- Payments, donations, or appointment scheduling.
- Self-service applicant portal (account login).
- Third-party storage (Drive/Dropbox) — roadmap.

---

## 5) Functional Requirements

### 5.1 Form Builder (per form)
- **Fields:** text, textarea, email, phone, date, number, select, multiselect, radio, checkbox, file upload, signature, consent, hidden, computed, repeater groups, section breaks.
- **Validation:** required, regex, min/max, email/phone formats, file size/type (server-side authoritative).
- **Conditional logic:** show/hide fields/steps; conditional email routing.
- **Layout:** single or multi-step with progress; review-before-submit; optional save-and-resume (magic link).
- **Theming:** design tokens (colors, spacing, radius), light/dark, high-contrast; RTL ready.
- **Localization:** all strings translatable; EN/PT shipped.
- **Anti-spam:** honeypot + Cloudflare Turnstile (default) or Google reCAPTCHA (toggle per form).
- **Consent:** explicit (unchecked) checkbox for data processing; optional newsletter opt-in (unchecked).

### 5.2 Form Rendering
- Shortcode: `[fbm_form id="123"]`
- Gutenberg block with same props.
- Mobile-first, accessible (labels, aria, keyboard support).

### 5.3 Submission Workflow
1. Client validation → POST with nonce.
2. Server validation & sanitization.
3. File upload (validated; stored with randomized filename).
4. Write to DB (JSON + encrypted blob for sensitive fields).
5. Send emails; log outcomes (success/failure).
6. Show success screen with reference ID (+ optional PDF receipt).

### 5.4 Email
- Per-form **applicant** and **admin** templates (HTML + text).
- Default templates: `applicant_confirmation`, `admin_notification`.
- Token variables (whitelisted): `{first_name}`, `{last_name}`, `{application_id}`, `{site_name}`, `{appointment_time}`.
- Conditional recipients (e.g., by postcode).
- Logging: to, subject, headers, body hash, status, provider message.
- Test send + preview with sample data.
- Compatible with SMTP/transactional providers via site’s chosen plugin.

### 5.5 Admin (wp-admin) Interface
- **Menu:** FoodBank → **Dashboard**, **Attendance**, **Database**, **Forms**, **Email Templates**, **Settings**, **Permissions**, **Diagnostics**. The parent menu always falls back to `manage_options` so Administrators can reach it even if custom FBM caps are missing. Subpages remain gated by their FBM capabilities. When caps are absent, a one-time, text-only admin notice (no global CSS/JS) links to Diagnostics → Repair caps and can be dismissed for a day.
- **Database list:** filters (date, status, city, postcode, has file, consent), saveable filter presets, per-user column toggles, pagination.
  - **Capabilities:** viewing and exporting require `fb_manage_database`; sensitive fields unmasked only with `fb_view_sensitive`.
  - **Exports:** CSV export respects current filters, sanitizes filenames, starts with a UTF-8 BOM and translated headers, and masks sensitive fields by default.
- **Entry view:** all fields, internal notes, status (new/review/approved/declined/archived), actions: Edit, PDF, CSV, Delete (with confirm).
- **Email Templates:** WYSIWYG editor, variables list, live preview with token helper, reset-to-defaults, a11y labels, send test.
- **Settings:** branding, date/time, email defaults, spam protection keys, file policy, retention/anonymisation, encryption controls (see §10).
- **Diagnostics:** email logs, resend, test email, repair capabilities, environment checks (PHP/WP version, cron, KEK, mail transport), and a cron health panel with retention run and dry-run controls.
- **Permissions:** map capabilities to roles and set per-user overrides.
- **Attendance repository:** all queries use `$wpdb->prepare()` with strict placeholders; results mask PII unless `fb_view_sensitive` is granted; unit tests cover check-in, no-show, void/unvoid, and policy edge cases.

**Permissions tab — Acceptance Criteria**
- Admins can map caps to roles (except Administrator which is fixed).
- Admins can manage per-user overrides via a searchable table with add/remove rows.
- Export downloads a JSON of role mappings and per-user overrides.
- Import accepts JSON (file or paste) with optional Dry Run preview; caps validated against `Capabilities::all()`.
- Reset restores default roles and clears per-user overrides.
- All actions use nonces and safe redirects.
- **Attendance** — see detailed spec below.

#### Attendance (Admin Tab)

**Purpose**  
Provide administrators a fast, trustworthy overview of attendance across all applicants, with common time windows (**Last 7 days**, **Last 30 days**, **Last 6 months**, **Last 12 months**, **Custom range**) and powerful filtering, summaries, and exports.

**Header Filters (persist across pagination/exports)**  
- **Date**: presets above + Custom date picker (timezone Europe/London).  
- **Event/Session** (multi-select; includes “All / Ad-hoc”).  
- **Status**: Present / No-show / Cancelled / Void (multi).  
- **Type**: Food parcel / Hot meal / Advice session / Other (multi).  
- **Manager** (who recorded the attendance).  
- **Policy flags**: Exceeded frequency limit, Override used, Duplicate scan attempt.  
- **Search**: name / email / postcode (email & postcode matched via salted hashes; plaintext masked in UI).

**Summary Cards (respect current filters)**  
- **Unique attendees**  
- **Total attendances**  
- **No-shows** (count + rate)  
- **First-time attendees** (in range)  
- **Policy overrides** (count)  
- **Capacity usage** (when a specific Event is selected)

**People View (default; one row per applicant)**  
Columns (toggleable):  
- Name; Email (masked, e.g., `j***@example.com`); Postcode (masked, e.g., `E1* 3**`)  
- **Last attended** (datetime)  
- **Visits (range)** (total in selected window)  
- **No-shows (range)**  
- **Visits (12m)** (rolling 12 months)  
- **First attended** (date)  
- **Policy status** (OK / Warning / Violation)  
- **Notes** (icon shows recent notes)

Row actions: **View Profile**, **Open Attendance Timeline**, **Show QR** (REST‑nonce, admin‑only), **Override & Check-in** (reason required), **Export (CSV/PDF)**.

**Sessions View (grouped by Event)**  
Columns: Event, Date/Time, Location, Capacity, **Attended**, **No-shows**, % used.  
Row actions: **View attendee list**, **Export roster (CSV/PDF)**, **Print-friendly roster**.

**Attendance Timeline (right-side drawer on person open)**  
- Chronological records with: date/time, event, type, recorded by, method (Scan/Manual), policy warnings, notes.  
- Actions (capability-gated): **Add note**, **Void record** (reason required), **Change type** (audit-logged).  
- Quick stats: **Visits (range)**, **No-shows (range)**, **Visits (12m)**, **Last attended**.

**Bulk Actions**  
- People view: **Export CSV/XLSX/PDF**, **Email list** (export addresses; plugin does not bulk-send), **Mark No-show** (for pre-registered no-shows), **Add note** (to selected, with tag).  
- Sessions view: **Export roster**, **Print roster**, **Mark selected Present**.

**Exports**  
- CSV/XLSX: respects filters + visible columns, with **PII masking option** (default ON).  
- PDF: event rosters and “People summary” for the selected range (letterhead template).

**Policy Indicators (read-only)**  
- Frequency rule (e.g., “1 per 7 days”) evaluated per applicant; **Warning** badge if breached in range or on last attendance.  
- **Override** badge where breach was allowed (note + actor shown).

**Performance**  
- Server-side pagination/sorting; indexed queries on (`application_id`, `attendance_at`, `event_id`, `status`).  
- Uses denormalized counters where possible. Target query time < 300 ms for typical filters.

**Security & Privacy**  
- View requires `attendance_view`; destructive actions require `attendance_admin`.  
- Emails/postcodes masked by default; full values only for users with `read_sensitive`.  
- All actions nonce-protected and audit-logged (who/when/what).

**Acceptance Criteria**  
- Date presets (Last 7/30/6m/12m/Custom) consistently update summary cards and tables.  
- People view counts (Visits/No-shows) match the Timeline and exports.  
- Voiding a record updates counts and preserves an audit trail with actor and reason.  
- Exports reproduce current filters/columns; masking toggle works.  
- Policy warnings/overrides display accurately and match attendance history.

### 5.6 Front-End Dashboard (Viewer/Manager)
- Shortcodes:
  - `[foodbank_entries roles="foodbank_viewer,foodbank_manager" columns="id,created_at,name,postcode,status" filters="date,postcode,status,benefits" export="csv|xlsx|pdf"]`
  - `[fbm_dashboard period="today|7d|30d" compare="1|0" sparkline="1|0"]` — Manager-only card grid (Totals, Unique Households, No-shows, Deliveries vs In-person %, Voided) with optional % change vs previous period and daily check-ins sparkline. 7‑day period by default; compare and sparkline enabled by default.
- Server-side filtering, search, sorting, pagination.
- Viewer: read-only; Manager: edit, status update, PDF.
- **Attendance tab** (Manager): **Scan** and **Manual** modes (see §6).

### 5.7 Exports & PDFs
- CSV/XLSX (respect filters; masking option for sensitive fields).
- Single entry PDF (letterhead template) and bulk PDFs (zip).
- Background jobs for large export batches.

### 5.8 GDPR & Privacy

### 5.9 Shortcodes

| Shortcode | Attributes (default) |
| --- | --- |
| `[fbm_form]` | `id` (string, default "1") |
| `[fbm_entries]` | _None_ |
| `[fbm_attendance_manager]` | _None_ |
The Shortcodes admin page includes a builder that outputs masked shortcode strings with a nonce-protected, server-side preview.
- Consent logging (timestamp, IP, user agent, consent text hash).
- SAR export by email lookup (entries, files, attendance).
- Retention policies per form (auto-anonymise after X months).
- Masking options for exports and PDFs.

---

## 6) Attendance Module (Manager, front-end)

### 6.1 Goals
- Fast mobile check-in (≤ 2 taps).
- Tamper-evident history per person.
- Policy rules (e.g., 1 parcel / 7 days) with override + note.

### 6.2 Data Model
- **Table `fb_attendance`**
  - `id` PK
  - `form_id` FK → `fb_form`
  - `application_id` FK → `fb_applications`
  - `event_id` FK → `fb_events` (nullable)
  - `attendance_at` datetime (UTC)
  - `status` enum(`present`,`no_show`,`cancelled`,`void`)
  - `type` enum(`food_parcel`,`hot_meal`,`advice_session`,`other`)
  - `method` enum(`scan`,`manual`)
  - `recorded_by_user_id` FK → `wp_users`
  - `notes` text (optionally encrypted)
  - `token_hash` varchar(64) (nullable, unique when set)
  - `source_ip` varchar(45), `device` varchar(64)
  - `created_at`, `updated_at`
  - Indexes: (`application_id`,`attendance_at`), (`event_id`,`attendance_at`), (`status`), (`token_hash`)
- **Table `fb_events`** *(optional)*  
  - `id`, `form_id`, `title`, `type`, `starts_at`, `ends_at`, `location`, `capacity`, `recurrence`, `notes`, `created_by`, `created_at`, `updated_at`
  - Index: (`form_id`,`starts_at`)
- **Denormalized on `fb_applications`:** `last_attended_at`, `total_attendances`, `no_show_count`

### 6.3 UX
- **Today screen:** choose event (or Ad-hoc), search list (name/email/postcode), quick actions: Present / No-show / Notes / Override.
- **Scan mode:** open device camera → scan **QR** on applicant email/card → auto check-in (or confirm). Color/voice feedback; accessible.
- **Manual mode:** search → tap action.
- **Bulk:** multi-select present; “Undo last check-in.”
- **Filters:** date range, event, status, type, manager.
- **Exports:** CSV/XLSX/PDF; summary counters (today/week/month).

### 6.4 QR & Token
- Per applicant QR (PNG/SVG), **no PII**, payload = opaque signed token.
- Token: `app_id | optional_event | issued_at | nonce | HMAC`.
- Server verifies signature and freshness; only **hash** stored on consume.
- Rotation policy configurable (e.g., monthly); revoke/regenerate per applicant.

### 6.5 Policy Rules
- Frequency limit (e.g., 1 per 7 days) → soft **warning**; Manager may **override** with note.
- No-show recording (optional) and reason list.

---

## 7) Non-Functional Requirements

- **Performance:** server-side pagination; indexed columns; async jobs for heavy tasks.
- **Reliability:** transactional writes; retries on email send; idempotent check-ins.
- **Compatibility:** WordPress 6.x+, PHP 8.1+; libsodium available (default in PHP 7.2+).
- **Accessibility:** WCAG-friendly labels, error summaries, keyboard navigation.
- **Internationalization:** `.pot` provided; EN/PT included.
- **Responsive:** mobile-first layout; touch-friendly controls.

### Admin & Infrastructure
- **Canonical Namespace:** `FBM\` is primary; `FoodBankManager\` is temporarily bridged via `class_alias` during transition.
- **Bootstrap:** plugin must attempt `vendor/autoload.php`, then fallback PSR-4 (`FBM/` `FBManager`) with `includes/` mapping.
- **Shortcodes:** registered by `FBM\Shortcodes\Shortcodes::register()` during `Core\Plugin::boot()`; per-form CAPTCHA renders when enabled.
- **Notices:** single hook (`admin_notices`), single render per request, gated by canonical screen IDs.
- **Capabilities:** on every admin request, Administrator capabilities are self-healed; Diagnostics exposes a "Repair caps" control.
- **Admin menu:** parent may fall back to `manage_options` only when core boot/menu not registered; subpages remain FBM-capability gated; first submenu reuses parent slug.

---

## 8) Data Model (Core)

- **Table `fb_forms`** (or CPT `fb_form` meta) — builder JSON, theme tokens, email rules, retention, captcha settings.
- **Table `fb_applications`**
  - `id` PK, `form_id` FK
  - `created_at`, `updated_at`
  - `status` enum
  - `data_json` longtext (non-sensitive fields)
  - `pii_encrypted_blob` longblob (sensitive map; AEAD)
  - `indexes_json` (e.g., `email_hash`, `postcode_hash`)
  - `attachment_id` (Media Library) or files table
  - `consent_text_hash`, `consent_timestamp`, `consent_ip`, `newsletter_opt_in`
  - Denormalized counters (see §6)
  - Indexes: (`form_id`,`created_at`), (`status`), (`indexes_json` virtual or extracted columns for `email_hash`, `postcode_hash`)
- **Table `fb_files`** (if not using Media Library)
  - `id`, `application_id`, `stored_path`, `original_name`, `mime`, `size_bytes`, `sha256`, `created_at`, index (`application_id`)

- **Table `fb_mail_log`**
  - `id`, `application_id` (nullable), `to_email`, `subject`, `headers`, `body_hash`, `status`, `provider_msg`, `timestamp`
  - Index (`status`,`timestamp`), (`application_id`)

*(Exact SQL in Appendix A.)*

---

## 9) Email Templates (Tokens)

**Available tokens (non-exhaustive):**
- Submission: `{{application_id}}`, `{{form_title}}`, `{{created_at}}`, `{{first_name}}`, `{{last_name}}`, `{{email}}`, `{{postcode}}`, `{{summary_table}}`, `{{qr_code_url}}`
- Admin: add `{{entry_url}}`, `{{attachments_list}}`
- Attendance (optional notices): `{{last_attended_at}}`, `{{total_attendances}}`

Templates stored per form; preview + test send supported.

---

## 10) Security, Encryption & Privacy

### 10.1 Core Controls
- **Input security:** sanitize, validate, and escape using WP APIs on server; never trust client.
- **CSRF:** nonces on all forms/admin/REST (`X-WP-Nonce`).
- **AuthZ:** capabilities:
  - `fb_read_entries`, `fb_edit_entries`, `fb_delete_entries`, `fb_export_entries`
  - `fb_manage_forms`, `fb_manage_settings`, `fb_manage_emails`, `fb_manage_encryption`
  - Attendance: `attendance_checkin`, `attendance_view`, `attendance_export`, `attendance_admin`
- **Uploads:** MIME + extension validation, size limits, randomized filenames; store outside webroot if configured; optional AV.

### 10.2 Encryption at Rest
- **Sensitive fields** (e.g., DOB, full address, phone, email) are stored in `pii_encrypted_blob` using **libsodium AEAD XChaCha20-Poly1305**.
- **Envelope encryption:** per-entry random DEK encrypts the blob; DEK encrypted with KEK stored in `wp-config.php` or environment/secret store.
- **Indexes:** salted hashes (e.g., email_hash, postcode_hash) stored separately to enable filtering without plaintext.
- **Key rotation:** admin tool rotates KEK; background job re-encrypts DEKs. Audit trail recorded.

### 10.3 Operational Hardening
- Strongly recommend **2FA** for Administrator/Manager users.
- **Audit log:** view/edit/delete/export actions; optional HMAC chaining for tamper evidence.
- **Background jobs:** Action Scheduler for email retries, exports, re-encryption; avoids request timeouts.
- **CAPTCHA:** Turnstile/reCAPTCHA with **server-side verification** mandatory.
- **Rate limiting:** submission and attendance endpoints throttled per IP/email/token.
- **Headers:** set `X-Content-Type-Options: nosniff`, `X-Frame-Options: sameorigin`, and appropriate `Content-Security-Policy` for plugin pages where feasible.

### 10.4 Privacy & GDPR
- Explicit consent checkbox (not pre-ticked); consent stored with timestamp/IP/text hash.
- SAR export (JSON/CSV/PDF + file links) by email lookup.
- Retention policy per form (auto-anonymise after X months).
- Privacy policy text snippets provided.

---

## 11) REST API (namespace `pcc-fb/v1`)

All endpoints require logged-in user + capability + nonce.

### Applications
- `POST /applications`  
  **Body:** `{ form_id, fields: {...}, files: [..] }`  
  **200:** `{ id, reference, created_at }`
- `GET /applications` (filters, pagination)  
  **Query:** `form_id, status, date_from, date_to, email_hash, postcode_hash, page, per_page`
- `GET /applications/{id}`
- `PUT /applications/{id}` (Manager/Admin)
- `DELETE /applications/{id}` (soft delete; Admin)
- `POST /applications/{id}/pdf` → returns URL to generated PDF
- `POST /export` → CSV/XLSX build job; returns job id and download when ready

### Attendance
- `POST /attendance/checkin`  
  **Body:** `{ token?, application_id?, event_id?, type? }`  
  **200:** `{ attendance_id, status:"present", attendance_at, policy_warning? }`
- `POST /attendance/noshow`  
  **Body:** `{ application_id, event_id?, reason? }`
- `POST /attendance/void` (Admin only)
- `GET /attendance` (filters: date range, event, status, type, manager)

### Utilities
- `POST /mail/test` (Admin)
- `GET /diagnostics` (Admin)

Error format: `{ error: { code, message } }`

---

## 12) User Flows (high-level)

### A) Applicant Submission
1. Open form → fill steps → submit.
2. Server validates, stores, sends emails (applicant + admin), logs.
3. Success page with reference ID + (optional) QR for future attendance.

### B) Manager Attendance (Scan)
1. Open front-end dashboard → Attendance → **Scan**.
2. Scan QR → server verifies token → success tick + counters update.
3. If policy breach: show warning with **Override** + required note.

### C) Viewer Reporting
1. Open front-end dashboard → filter → export CSV/XLSX/PDF.

### D) Admin SAR
1. Admin searches by email → one-click SAR export → ZIP generated.

---

## 13) Reporting

- **Dashboard tiles:** new entries (7/30d), approvals, attendance today/week, top masked postcodes.
- **Trends:** submissions per week; attendance heatmap by hour/day; no-show rate.
- **Exports:** all reports exportable to CSV/XLSX/PDF (masking optional).

---

## 14) Acceptance Criteria (sample)

**Form & Submission**
- Required fields and file validation enforced server-side.
- On success, DB row is created, file stored, emails sent, email log updated.
- Consent stored with timestamp/IP/text hash.

**Emails**
- Templates render tokens; test send works.
- Failed sends appear in Diagnostics with provider message; Resend works.

**Front-End Dashboard**
- Unauthorized users cannot access; authorized see only permitted forms/entries.
- Filters and search reflect in server-side results; export respects filters.

**Attendance**
- Valid QR scan records `present` with `attendance_at` and acting user; duplicate within same event is blocked (warning shown).
- Manual check-in works with search; override requires note when policy violated.
- Application denormalized counters update immediately.

**Security**
- All POST actions require nonce; attempts without nonce fail with 403.
- Sensitive fields are encrypted at rest; direct DB read reveals ciphertext.
- Retention job anonymises data after configured period.

---

## 15) Milestones & Phasing

**M1 — Foundations (Infra & Security)**  
Custom tables, roles/caps, nonces, encryption scaffolding, Action Scheduler.

**M2 — Forms & Submissions**
Builder, rendering, validation, files, emails, logging, default Food Bank preset, CAPTCHA, and an admin builder with live preview.

**M3 — Admin & Front-End Dashboards**  
Database list/detail, exports, viewer/manager dashboards.

**M4 — Attendance**  
Events (optional), QR issuance, Scan/Manual flows, policy rules, reports.

**M5 — GDPR & Diagnostics**  
SAR export, retention/anonymisation, diagnostics panel, test email.

**M6 — Polish**  
PDF templates, theming presets, PT translations, docs.

---

## 16) Dependencies

- WordPress 6.x+, PHP 8.1+.
- PHP **libsodium** (bundled) for AEAD encryption.
- Action Scheduler (bundled library).
- Optional: Cloudflare Turnstile or Google reCAPTCHA keys.
- Optional: SMTP plugin (e.g., WP Mail SMTP) for deliverability.
- Optional: Dompdf/mPDF/TCPDF (configurable) for PDFs.
- Optional: DataTables (MIT) for front-end table UX.

---

## 17) Risks & Mitigations

- **Email deliverability:** use SMTP/provider; show failures; support retries.
- **Key management:** KEK in environment/`wp-config.php`; documented rotation; least access.
- **Large exports/timeouts:** run via Action Scheduler; stream downloads.
- **Camera permissions:** fallback to Manual mode for attendance.
- **PII leakage via exports:** masking option + role-restricted downloads.

---

## 18) Monitoring & Diagnostics

- Email log (sent/failed), resend, provider messages.
- Cron/queue status with next/last run times, retention job controls, and dry-run options.
- Environment checks (PHP/WP versions, KEK presence, mail transport, cron schedules).
- Self-check warnings if encryption/CAPTCHA/SMTP not configured.

---

## 19) Documentation

Ship with:
- **Setup Guide** (encryption keys, SMTP, CAPTCHA, retention).
- **Form Builder Guide** (fields, conditional logic, tokens).
- **Attendance Guide** (QR cards, scan/manual, overrides).
- **GDPR Playbook** (SAR, retention, DSR handling).
- **Incident Response** (rotate keys, disable endpoints, audit review).

---

## 20) Roadmap (post-v1)

- Kiosk/self-check-in mode.
- Offline queue & sync for attendance.
- Slack/email digests; webhooks.
- Google/Microsoft Sheets sync.
- Duplicate detection heuristics.
- Appointment windows / capacity booking.

---

## Appendix A — Schema (indicative)

> Final DDL may vary slightly per DB engine; all writes via `$wpdb` prepared statements.

```sql
CREATE TABLE wp_fb_applications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_id BIGINT UNSIGNED NOT NULL,
  status ENUM('new','review','approved','declined','archived') NOT NULL DEFAULT 'new',
  data_json LONGTEXT NOT NULL,
  pii_encrypted_blob LONGBLOB NULL,
  indexes_json JSON NULL,
  attachment_id BIGINT UNSIGNED NULL,
  consent_text_hash CHAR(64) NULL,
  consent_timestamp DATETIME NOT NULL,
  consent_ip VARBINARY(16) NULL,
  newsletter_opt_in TINYINT(1) NOT NULL DEFAULT 0,
  last_attended_at DATETIME NULL,
  total_attendances INT UNSIGNED NOT NULL DEFAULT 0,
  no_show_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY idx_form_created (form_id, created_at),
  KEY idx_status (status)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
sql
Copy code
CREATE TABLE wp_fb_attendance (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_id BIGINT UNSIGNED NOT NULL,
  application_id BIGINT UNSIGNED NOT NULL,
  event_id BIGINT UNSIGNED NULL,
  attendance_at DATETIME NOT NULL,
  status ENUM('present','no_show','cancelled','void') NOT NULL,
  type ENUM('food_parcel','hot_meal','advice_session','other') NOT NULL DEFAULT 'food_parcel',
  method ENUM('scan','manual') NOT NULL,
  recorded_by_user_id BIGINT UNSIGNED NOT NULL,
  notes TEXT NULL,
  token_hash CHAR(64) NULL,
  source_ip VARBINARY(16) NULL,
  device VARCHAR(64) NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_token (token_hash),
  KEY idx_app_time (application_id, attendance_at),
  KEY idx_event_time (event_id, attendance_at),
  KEY idx_status (status)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
sql
Copy code
CREATE TABLE wp_fb_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  type VARCHAR(64) NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NULL,
  location VARCHAR(190) NULL,
  capacity INT UNSIGNED NULL,
  recurrence TEXT NULL,
  notes TEXT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY idx_form_start (form_id, starts_at)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
sql
Copy code
CREATE TABLE wp_fb_mail_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NULL,
  to_email VARCHAR(254) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  headers MEDIUMTEXT NULL,
  body_hash CHAR(64) NULL,
  status ENUM('succeeded','failed') NOT NULL,
  provider_msg TEXT NULL,
  timestamp DATETIME NOT NULL,
  KEY idx_status_time (status, timestamp),
  KEY idx_app (application_id)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
sql
Copy code
CREATE TABLE wp_fb_files (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  stored_path VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  mime VARCHAR(127) NOT NULL,
  size_bytes BIGINT UNSIGNED NOT NULL,
  sha256 CHAR(64) NOT NULL,
  created_at DATETIME NOT NULL,
  KEY idx_app (application_id)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
Appendix B — Example Email Templates (snippets)
Applicant Confirmation (HTML)

csharp
Copy code
Subject: We received your application — {{form_title}} (Ref {{application_id}})

Hi {{first_name}},

Thank you for your submission on {{created_at}}.
Your reference is {{application_id}}.

You can present this QR at collection:
<img src="{{qr_code_url}}" alt="Your QR code" />

Summary:
{{summary_table}}

We’ll be in touch if we need anything else.
Admin Notification (HTML)

css
Copy code
Subject: New {{form_title}} submission (Ref {{application_id}})

New entry at {{created_at}}.

View entry: {{entry_url}}

Applicant: {{first_name}} {{last_name}} — {{email}} — {{postcode}}
Appendix C — Definitions
Applicant: person submitting a form.

Entry/Application: stored submission record.

Viewer: role with read-only access.

Manager: role with edit + attendance rights.

Attendance: per-visit record (present/no-show).

DEK/KEK: Data/Key Encryption Keys for envelope encryption.

Appendix D — Definition of Done (DoD)
All acceptance criteria met; unit/integration tests passing.

Security review completed (input handling, nonces, caps, uploads).

Encryption verified (blob unreadable in DB; decrypts in UI with caps).

Translations compiled; PT verified for front-end.

Docs & setup guide included; sample forms exported to JSON.

Linters/PHPCS pass; no PHP warnings/notices.

Release tagged v1.0.0 and changelog created.

pgsql
