Docs-Revision: 2025-09-12 (v1.11.3 patch finalize)
# REST API (pcc-fb/v1)

Base namespace: `pcc-fb/v1`. All write endpoints require `X-WP-Nonce` and capabilities.
Shortcode previews are handled via `admin-post.php` (`fbm_action=shortcode_preview`) and are not exposed via REST.
Dashboard summary CSV downloads use `admin-post.php?action=fbm_dash_export` with a nonce and `fb_manage_dashboard` capability.
Design & Theme options are configuration-only; no REST endpoints expose or modify them.
Admin screens are wrapped in `.fbm-admin` with assets/notices loaded only on FoodBank Manager pages.
On multisite, network administrators receive `fbm_manage_jobs` on activation; a migration flag prevents re-granting on upgrade. Cron hooks are idempotent per site.

## API Errors

FoodBank Manager normalizes error responses across REST and AJAX endpoints:

| Status | Meaning |
| ------ | ------- |
| 400 | Bad request (generic argument issues) |
| 401 | Unauthorized (invalid or missing nonce) |
| 403 | Forbidden (capability check failed) |
| 404 | Not found (missing resource) |
| 409 | Conflict (state clash such as policy breach) |
| 422 | Unprocessable (validation failed) |
| 429 | Too many requests (rate limited) |
Rate-limited responses include `RateLimit-Limit`, `RateLimit-Remaining`, and `RateLimit-Reset` headers; `Retry-After` appears on 429 responses.

REST errors return:

```json
{
  "success": false,
  "error": {
    "code": "invalid_param",
    "message": "Required fields missing",
    "details": null
  }
}
```

AJAX errors use the same `error` object. Example invalid nonce:

```json
{
  "success": false,
  "data": {
    "error": {
      "code": "invalid_nonce",
      "message": "Invalid nonce"
    }
  }
}
```

Rate limited responses include `RateLimit-Limit`, `RateLimit-Remaining`, `RateLimit-Reset`, and `Retry-After` headers:

```
HTTP/1.1 429 Too Many Requests
Retry-After: 60
RateLimit-Limit: 5
RateLimit-Remaining: 0
RateLimit-Reset: 1710000000
```

Scan and other success responses keep their existing payload contract.

## HTTP Controllers

### ExportController
- Route: `admin-post.php?action=fbm_export`
- Method: GET
- Requires: capability and `_wpnonce`
- 200: PDF, ZIP, or XLSX download (masked by default)
- 4xx: safe redirect with `notice=denied|error`

### AttendanceExportController
- Route: `admin-post.php?action=fbm_attendance_export`
- Method: GET
- Requires: capability and `_wpnonce`
- 200: CSV/XLSX mirror visible columns
- 4xx: safe redirect with `notice=denied|error`

### MailResendController
- Route: `admin-post.php?action=fbm_mail_resend`
- Method: POST
- Requires: capability and `_wpnonce`
- 200: redirect with `notice=resent`
- 4xx: redirect with `notice=denied|error`

### DiagnosticsController
- Route: `admin-post.php?action=fbm_diagnostics_mail`
- Method: POST
- Requires: capability and `_wpnonce`
- 200: redirect with `notice=sent`
- 4xx: redirect with `notice=error`

### DashboardExportController
- Route: `admin-post.php?action=fbm_dash_export`
- Method: GET
- Requires: capability and `_wpnonce`
- 200: CSV via CsvWriter (BOM, explicit delimiter)
- 4xx: safe redirect with `notice=denied|error`

### ExportJobsController
- Route: `admin-post.php?action=fbm_export_jobs`
- Method: POST
- Requires: capability and `_wpnonce`
- 200: queue/list/download export jobs
- 4xx: redirect with `notice=denied|error`

## REST

### ScanController
- Route: `POST /scan`
- Requires: `fb_manage_attendance` and `X-WP-Nonce`
- 200: `{ status: 'ok', data: masked }`
- 4xx: `{ error: 'invalid|used|expired' }`

### Attendance
- `POST /attendance/checkin`
  - Body: `{ token|string OR application_id:int, event_id?:int, type?:string, method?:'qr'|'manual', override?:{ allowed:bool, note?:string } }`
  - Perm: `attendance_checkin`
  - 409 on policy conflict; include `{ policy_warning: { rule_days, last_attended_at } }`
  - Admin QR helper: `GET` link with `_wpnonce` and `application_id` (no PII) for logged-in staff.
- `POST /attendance/noshow`
  - Body: `{ application_id:int, event_id?:int, type?:string, reason?:string }`
  - Perm: `attendance_checkin`
- `GET /attendance/timeline`
  - Query: `application_id:int, from?:datetime, to?:datetime, include_voided?:bool`
  - Perm: `attendance_view`
- `POST /attendance/void`
  - Body: `{ attendance_id:int, reason:string }`
  - Perm: `attendance_admin`
- `POST /attendance/unvoid`
  - Body: `{ attendance_id:int }`
  - Perm: `attendance_admin`
- `POST /attendance/note`
  - Body: `{ attendance_id:int, note:string }`
  - Perm: `attendance_admin`
- All attendance endpoints delegate to `AttendanceRepo`, which uses `$wpdb->prepare()` with strict placeholders and returns masked data unless `fb_view_sensitive` is granted. Unit tests cover policy enforcement and SQL injection boundaries.

## Forms (MVP via admin-post)
- Shortcode `[fbm_form preset="basic_intake"]` posts to `admin-post.php?action=fbm_submit` and renders fields from the Presets Library.

## Shortcodes

| Shortcode | Attributes (default) |
| --- | --- |
| `[fbm_form]` | `id` (string, default "1"), `preset` (string, default "basic_intake") |
| `[fbm_entries]` | _None_ |
| `[fbm_attendance_manager]` | _None_ |
| `[fbm_dashboard]` | `period` ("today"|"7d"|"30d"), `compare` ("1"|"0"), `sparkline` ("1"|"0"), `event`, `type` ("in_person"|"delivery"|"all"), `policy_only` ("1"|"0") |
- Validates nonce, required fields, file policy.
- Stores encrypted PII; triggers applicant/admin emails.

## Security
- Mask PII by default in list/exports; decrypt only for `read_sensitive`.
- No plaintext PII in logs; mail logger stores body hash only.

## Email Templates

- Default templates: `applicant_confirmation`, `admin_notification`.
- Token variables (whitelisted): `{first_name}`, `{last_name}`, `{application_id}`, `{site_name}`, `{appointment_time}`.

## Database Presets & Columns

- Saved filter presets and column preferences on the Database page use nonce-protected admin forms and are not exposed via REST.
