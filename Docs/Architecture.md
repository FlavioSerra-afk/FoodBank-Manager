Docs-Revision: 2025-09-06 (Wave AP)
# FoodBank Manager — Architecture

## Overview
A secure, privacy-first WordPress plugin for managing Food Bank applicant intake forms, encrypting sensitive data, providing an admin database, tracking attendance with QR codes, logging emails, and offering diagnostics tools.

## Guiding principles
- Privacy-by-default: mask personally identifiable information (PII) wherever possible.
- No fatals: the plugin should never crash a site.
- Graceful optional dependencies: features should degrade cleanly if dependencies are missing.
- Least privilege: grant only the capabilities required.
- Isolated admin layout: `.fbm-admin` wrapper and screen-gated assets/notices ensure no overflow into wp-admin.

## Directory layout (key paths)
- `foodbank-manager.php` – bootstrap, constants: `FBM_FILE`, `FBM_PATH`, `FBM_URL`, `FBM_VERSION`.
- `includes/Core/Plugin.php` (boot), `includes/Db/Migrations.php`.
- `includes/Core/Options.php` – typed options stored in `fbm_settings`, validated against a schema (unknown keys ignored, fields limited to 1KB).
- `includes/Forms/Presets.php` – built-in and custom form presets with strict validation.
- `includes/Admin/*Page.php`, `templates/admin/*`.
- `includes/Shortcodes/{Form,AttendanceManager,Dashboard}.php`, `templates/public/*`.
- `includes/Http/DashboardExportController.php`.
- `includes/Rest/{Api,AttendanceController}.php`.
- `includes/Security/{Crypto,Helpers}.php`.
- `includes/Auth/{Capabilities,Roles,CapabilitiesResolver}.php`.
- `includes/Attendance/{AttendanceRepo,Policy,TokenService}.php`.
- `includes/Database/ApplicationsRepo.php`.
- `includes/Exports/CsvExporter.php` and `includes/Exports/DashboardCsv.php`.
- `includes/Mail/{Logger,Templates}.php`.
- `includes/UI/Theme.php`, `assets/css/{theme-*.css,frontend-dashboard.css}`, `includes/Security/CssSanitizer.php`.
- `includes/Logging/Audit.php`.
- QA/CI: `.github/workflows/release.yml`, `phpcs.xml`, `phpstan.neon`, `phpstan-bootstrap.php`, `composer.json` scripts.
- Tests use deterministic WP stubs (`tests/Support/WPStubs.php`) providing fallbacks for core helpers.

## Boot & lifecycle
- `foodbank-manager.php` attempts `vendor/autoload.php` and registers a lightweight PSR-4 fallback mapping `FBM\` classes in `includes/` and aliasing `FoodBankManager\` during migration.
- Activation/deactivation: reflection-based call to instance/static `activate()`/`deactivate()` methods.
- During `plugins_loaded`, `Core\Plugin::boot()` registers admin menus, REST routes, shortcodes, assets, and repairs roles/capabilities. An idempotent ensurer grants Administrators all FBM capabilities on each admin request.
- Successful boot writes a `fbm_boot_ok` transient with the timestamp shown on the Diagnostics screen and fires `fbm_booted`.
- `fbm_menu_registered` fires after the admin menu is registered; when either hook hasn't fired, the parent menu falls back to `manage_options` to avoid duplicates while subpages remain FBM-gated.
- The FoodBank parent menu falls back to `manage_options` for Administrators if FBM caps are missing; subpages remain FBM-gated and Diagnostics offers a nonce-protected **Repair caps** button.
- When no FBM caps are detected for an Administrator, a transient-limited, text-only notice (no global assets) prompts Diagnostics → Repair caps and can be dismissed for 24 h.
- Admin notices display when the vendor autoloader is missing or the KEK is not defined.
- Diagnostics surfaces notices render count via `Notices::getRenderCount()`.

## Screen gating
Notices and assets check `$screen->id` and run only on `toplevel_page_fbm` or `foodbank_page_fbm_*`. Each notice uses a printed flag to render once per page.

## Render guard
Admin pages share a `FBM\Core\RenderOnce` registry. `Admin\Menu` wraps each submenu callback with `render_once()` so a screen's template is included only once per request. No page may output UI outside its guarded callback; duplicate paths are ignored.

### Trace Comments (RenderOnce)
- `<!-- fbm-render {key} pass={n} -->` emitted after `.wrap.fbm-admin`.
- Diagnostics badge flips to "RenderOnce duplicates" when any key exceeds one.

## Components
 - **Admin Pages:** Dashboard (`fb_manage_dashboard`), Attendance (`fb_manage_attendance`), Database (`fb_manage_database`), Forms (`fb_manage_forms`) with a read-only presets library, Shortcodes builder with preview (`fb_manage_forms`), Email Templates (`fb_manage_emails`), Settings (`fb_manage_settings`), Diagnostics (`fb_manage_diagnostics` – environment checks, test email, repair caps), Permissions (`fb_manage_permissions`), Design & Theme (`fb_manage_theme`).
  - Database page requires `fb_manage_database`; filters are sanitized and whitelisted. It supports per-user column preferences (`fbm_db_columns`) and saved filter presets stored in options (`db_filter_presets`). Exports respect filters, sanitize filenames, include a UTF-8 BOM with translated headers, and mask PII unless the user has `fb_view_sensitive`.
- **Shortcodes:** `[fbm_form]`, `[fb_attendance_manager]`, `[fbm_dashboard]` (manager-only card stats with trend deltas, sparkline, optional filters and summary CSV export; no PII).
- **REST:** namespace `pcc-fb/v1`; endpoints for attendance check-in, no-show, timeline, void/unvoid/note.
 - Admin Attendance page generates REST-nonce QR check-in URLs (no PII) and an override reason form.
- **Security:** libsodium/XChaCha20-Poly1305 envelope encryption (`FBM_KEK_BASE64`), `sodium_compat` fallback; masking helpers; no PII in logs.
- **Permissions:** central caps list, Administrator guarantee, role mapping and per-user overrides (`fbm_user_caps`) with JSON export/import (Dry Run) and reset tooling.
- **AttendanceRepo:** database access layer for attendance; all queries use `$wpdb->prepare()` with strict placeholders, mask PII by default, and have unit tests covering check-in, no-show, void/unvoid, and timeline SQL injection edges.
- **Theme system:** global CSS variables for primary colour, density, font, dark mode default and optional custom CSS (sanitised) applied across admin and front-end.

## Data model (tables)
- `wp_fbm_applications`: `id`, `form_id`, `status`, `data_json`, `pii_encrypted_blob`, consent fields, timestamps.
- `wp_fbm_files`: `application_id`, stored path/name, mime, size, checksum, `created_at`.
- `wp_fbm_attendance`: `application_id`, `event_id`, `status` (present/no_show), `type`, `method`, `recorded_by_user_id`, `attendance_at`, policy override fields, voiding: `is_void`, `void_reason`, `void_by_user_id`, `void_at`.
- `wp_fbm_attendance_notes`: `attendance_id`, `user_id`, `note_text`, `created_at`.
- `wp_fbm_mail_log`: `to`, subject hash/body hash, provider status, `created_at`.
- `wp_fbm_audit_log`: `actor_user_id`, `action`, `target_type/id`, `details_json`, `created_at`.
- Indexes: `(application_id, attendance_at)`, `(event_id, attendance_at)`, `status`.

## Settings model
Single option `fbm_settings` with nested keys: `general`, `forms`, `files`, `emails`, `attendance`, `privacy`, `theme`, `encryption` (status). Each section is consumed by form submission, attendance policy enforcement, emails, theming, and other features.

## Security model
- Input validation and sanitization; output escaping enforced by PHPCS rules.
- Nonces and capability checks on all mutations (REST uses `permission_callback` and `X-WP-Nonce`).
- Masking defaults ON for lists/exports; decrypt only for `read_sensitive` capability.
- File uploads policy and storage options (uploads vs local path with `.htaccess`).
- Audit logging for admin actions (attendance void/note).

## Build, QA & release
- Composer scripts: `lint`, `phpcs`, `phpstan`, `test`, `build:zip`, `release:*`.
- GitHub Actions build the plugin-ready `foodbank-manager.zip` (includes `vendor/`) and attach to tags; optional WP-CLI E2E step if present.
- Local packaging via `composer build:zip`.

## Coding standards & checks
- PHPCS ruleset tuned for namespaced WP plugins; escape/sanitize/nonce/i18n are mandatory.
- PHPStan bootstrap defines plugin constants for analysis only.

## Extensibility roadmap
Upcoming: SAR/Retention job, PDFs renderer adapter, Diagnostics "Resend", richer Timeline (void reasons, edits), live Theme preview, preset library.

