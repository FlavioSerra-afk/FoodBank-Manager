Docs-Revision: 2025-09-04 (Wave v1.2.7 – Admin Menu De-dup + Canonical Screen IDs)
# Database Schema (summary)

## fb_applications
- id PK, form_id, status ENUM, data_json LONGTEXT, pii_encrypted_blob LONGBLOB
- consent_text_hash, consent_timestamp, consent_ip VARBINARY(16)
- created_at, updated_at
- Indexes: (created_at), (status)

## fb_files
- id PK, application_id FK, stored_path, original_name, mime, size_bytes, sha256, created_at
- Indexes: (application_id), (created_at)

## fb_attendance
- id PK, application_id FK, event_id, status ENUM('present','no_show'), type, method, recorded_by_user_id
- attendance_at
- policy override fields (if present)
- voiding: is_void TINYINT, void_reason, void_by_user_id, void_at
- Indexes: (application_id, attendance_at), (event_id, attendance_at), (status)
- Access via `AttendanceRepo` (declares strict types) which uses `$wpdb->prepare()` for all queries and masks PII unless `fb_view_sensitive` is granted; unit tests cover timeline and SQL injection attempts.

## fb_attendance_notes
- id PK, attendance_id FK, user_id, note_text, created_at
- Indexes: (attendance_id), (created_at)

## fb_mail_log
- id PK, to_hash, subject_hash, body_hash, provider, status, created_at
- Indexes: (created_at), (status)

## fb_audit_log
- id PK, actor_user_id, action, target_type, target_id, details_json, created_at
- Indexes: (target_type, target_id), (created_at)
