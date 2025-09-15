> Canonical Doc — READ ME FIRST
> This is 1 of 4 canonical docs (Specs, Plan, Tasks, Matrix).
> Codex MUST read & update these BEFORE any work, every prompt.
> Any other files in /Docs are historical and must not drive scope.

# Specs

A secure, standards-clean WordPress plugin to operate a weekly food bank collection flow:

Public: submit a registration form published via shortcode; on approval, receive a persistent QR in the welcome email.

Staff/Admin: use a front-end dashboard (staff-only) to scan QR codes on collection day and record pickups.

Back Office: cohesive admin UI with theme/accessibility controls, health diagnostics, internal summaries/exports (no PII publicly).

Roles & Access

Admin (WP admin): full control, settings, uninstall, diagnostics, internal summaries/exports.

Manager (fbm_manage): manage settings and operations; access staff front-end dashboard; run internal exports.

Staff/Volunteers (fbm_edit, fbm_view): access staff front-end dashboard; perform check-ins; view internal summaries as allowed.

Public: forms only (registration). No analytics. No QR scanning UI.

Core Capabilities

fbm_manage (manage settings/operations)

fbm_edit / fbm_view (operational screens)

fbm_export (internal CSVs)

fbm_diagnostics (mail/health)

fbm_checkin (server endpoint guard for scan/record)

Operating Model (No Events)

Food bank runs every Thursday, 11:00–14:30 (single fixed session).

Registered users keep the same QR code each visit (until revoked/canceled).

Staff scan the QR on the staff front-end dashboard; each scan records a collection for that date.

No Events table/page; logic is date-based with a fixed weekly window.

Functional Scope
A) Public Registration (Shortcode)

Shortcode: [fbm_registration_form]

Features:

Validation, nonce, anti-spam (honeypot + time trap).

Minimal PII collection; strict sanitize/normalize; prepared SQL writes.

On approval (or instant, per config), send Welcome Email including the user’s persistent QR code and a fallback alphanumeric code.

Optional policy consent capture and audit (timestamps).

B) Staff Front-End Dashboard (Scanner)

Shortcode: [fbm_staff_dashboard] → requires logged-in Manager/Staff role.

Modules:

QR Scanner (camera via getUserMedia, client-side decode).

Manual code entry fallback (A11y and “no-camera” cases).

Session status: shows current window (Thursday 11:00–14:30), number of collections recorded today, last scan result.

Rate-limit & idempotency feedback (e.g., “already checked in today”).

Server:

Endpoint /fbm/checkin (POST, nonce required, capability check fbm_checkin).

Validates opaque token; ensures member is active; enforces one collection per session per member; records attendance row; returns JSON {status, message, member_ref, time}.

QR Content:

Opaque token (no PII), e.g., FBM1:K3WQ… (versioned, signed, revocable).

Accept the same QR each week unless revoked/canceled.

C) Admin UI & Theme

Unified FBM styling; assets only load on FBM screens (screen-ID gated).

Theme page (fbm_theme):

Modes: light/dark/high-contrast.

Tokens: text colors, backgrounds, accents, focus ring, font scale, spacing, radii, buttons, inputs, radios/checkboxes.

Live preview (.fbm-scope container).

Fix: fbm_theme option group/page registered and allowed; nonce and capability alignment; payload size clamps; schema validation.

Optional “mirror safe tokens” to public forms (strict allow-list).

D) Internal Summaries & Export (Admin Only)

Summaries (totals by date/week; active vs. revoked; collection counts).

Filters: by date range + status; allow-listed server side.

CSV export (UTF-8 BOM, localized headers) behind fbm_export + nonce.

No public analytics pages.

E) Diagnostics

Mail failure list (redacted), secure resend (nonce + throttle).

Health badges: SMTP/API keys presence, KEK state (boolean only).

F) Uninstall / Hardening

register_uninstall_hook cleans options/transients/secrets.

Optional destructive data drop (explicit confirmation).

Keys/secrets wiped on uninstall.

Data Model (High-Level, no Events)

Tables (prefix fbm_):

fbm_members — registered person, minimal PII; status: active|revoked|pending.

fbm_tokens — persistent QR token per member: member_id, token_hash, issued_at, revoked_at, version, meta.

fbm_attendance — row per collection date: member_id, collected_at (DATE/TIMESTAMP), source (qr|manual), note.

Settings in wp_options namespaced: fbm_theme, fbm_settings (e.g., session window, email templates).

Indexes:

fbm_tokens(token_hash), fbm_attendance(member_id, collected_at), fbm_members(status).

QR Token Design:

Displayed as an image in Welcome Email (PNG/SVG).

Payload decodes to a versioned, signed, opaque string; server verifies by constant-time compare of hash_hmac(payload) against stored token_hash (never store raw token).

Revocation sets revoked_at, invalidating immediately.

Security & Privacy

Capability checks on every admin/staff route; nonces for state changes and /fbm/checkin.

QR contains no PII; only opaque data.

PII minimized in DB; templates escape outputs; SQL only via prepared statements.

Rate-limit scanning attempts (IP/user + time window); responses avoid leaking account state.

Audit: record actor (user id) performing check-ins; timestamps and outcomes.

Performance

Admin assets gated by screen-ID; front-end scanner loads minimal JS.

Attendance insert is O(1) with indexes; duplicates handled via unique guard (member_id + date).

Exports stream as CSV; admin summaries short-TTL cached.

Internationalization

Text domain foodbank-manager; localized copy for emails/UI/CSV headers.

Explicit Non-Goals

No CRM or case-management beyond the defined schema.

No real-time ERP/CRM sync.

No custom Gutenberg blocks (shortcodes only).

No AIW integration (referenced only as UI/UX inspiration).
