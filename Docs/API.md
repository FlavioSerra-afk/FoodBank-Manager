Docs-Revision: 2025-09-04 (Wave v1.1.6 – Attendance P1)
# REST API (pcc-fb/v1)

Base namespace: `pcc-fb/v1`. All write endpoints require `X-WP-Nonce` and capabilities.
Shortcode previews are handled via `admin-post.php` (`fbm_action=shortcode_preview`) and are not exposed via REST.

## Attendance
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
- Validates nonce, required fields, file policy.
- Stores encrypted PII; triggers applicant/admin emails.

## Security
- Mask PII by default in list/exports; decrypt only for `read_sensitive`.
- No plaintext PII in logs; mail logger stores body hash only.

## Email Templates

- Default templates: `applicant_confirmation`, `admin_notification`.
- Token variables (whitelisted): `{first_name}`, `{last_name}`, `{application_id}`, `{site_name}`, `{appointment_time}`.
