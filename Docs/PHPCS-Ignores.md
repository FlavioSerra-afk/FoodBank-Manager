# PHPCS Ignores & Suppressed Issues Dashboard

_Generated: 2025-09-03 15:52:08Z

This report shows which issues are currently suppressed via `phpcs:ignore` and what would fail if annotations were disabled.

## Snapshot

- Suppressed issues (from `--ignore-annotations` run): **21**
- Ignore annotations present: **63** lines

## Top sniffs by count

| Sniff | Count | Recipe |
|---|---:|---|
| `Generic.WhiteSpace.ScopeIndent.Incorrect` | 5 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.DB.PreparedSQL.InterpolatedNotPrepared` | 5 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.DB.PreparedSQL.NotPrepared` | 4 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `PEAR.Functions.FunctionCallSignature.Indent` | 3 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare` | 2 | - **Fix:** Build strict placeholders for `IN (...)` (e.g., `implode(',', array_fill(0, count($ids), '%d'))`), guard for empty arrays, and prepare values. Keep a one-line ignore only if PHPCS remains noisy. |
| `Generic.Files.LineLength.TooLong` | 1 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Universal.NamingConventions.NoReservedKeywordParameterNames.voidFound` | 1 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |

## Top files by suppressed issues

| File | Count |
|---|---:|
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 21 |

## All ignores (locations)

```
includes/Auth/CapabilitiesResolver.php:1:<?php // phpcs:ignoreFile
includes/Auth/Roles.php:1:<?php // phpcs:ignoreFile
includes/Auth/Capabilities.php:1:<?php // phpcs:ignoreFile
includes/Auth/Permissions.php:1:<?php // phpcs:ignoreFile
includes/Logging/Audit.php:1:<?php // phpcs:ignoreFile
includes/Http/FormSubmitController.php:2:// phpcs:ignoreFile
includes/Http/FormSubmitController.php:128:            $ip_bin       = $ip !== '' ? @inet_pton( $ip ) : null; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
includes/Exports/CsvExporter.php:1:<?php // phpcs:ignoreFile
includes/Shortcodes/AttendanceManager.php:26:	public static function render( array $atts = array() ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Attributes reserved for future use.
includes/Shortcodes/AttendanceManager.php:29:		if ( ! current_user_can( 'attendance_checkin' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Shortcodes/Entries.php:26:	public static function render( array $atts = array() ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Attributes reserved for future use.
includes/Shortcodes/Form.php:51:			$raw_param = (string) wp_unslash( $_GET['fbm_err'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via sanitize_key.
includes/Shortcodes/Form.php:60:		if ( isset( $_GET['fbm_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only error flag.
includes/Shortcodes/Form.php:67:			$json = file_get_contents( $preset_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local preset file.
includes/Core/Options.php:258:				$out['custom_css'] = function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $css ) : strip_tags( $css ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Fallback when WordPress is unavailable.
includes/Core/Plugin.php:1:<?php // phpcs:ignoreFile
includes/Core/Hooks.php:1:<?php // phpcs:ignoreFile
includes/Core/Assets.php:1:<?php // phpcs:ignoreFile
includes/Mail/Logger.php:1:<?php // phpcs:ignoreFile
includes/Database/ApplicationsRepo.php:1:<?php // phpcs:ignoreFile
includes/Security/Helpers.php:1:<?php // phpcs:ignoreFile
includes/Security/Crypto.php:1:<?php // phpcs:ignoreFile
includes/Rest/Api.php:203:	private static function send_applicant_email( string $to, int $app_id, string $first_name ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Used in included template.
includes/Rest/Api.php:212:				$qr_code_url = 'data:image/png;base64,' . base64_encode( $writer->write( $qr )->getString() ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Embedding QR code.
includes/Rest/Api.php:235:	private static function send_admin_email( int $app_id, string $first, string $last, string $email, string $postcode ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Used in included template.
includes/Attendance/AttendanceRepo.php:1:<?php // phpcs:ignoreFile
includes/Attendance/TokenService.php:1:<?php // phpcs:ignoreFile
includes/Attendance/Policy.php:1:<?php // phpcs:ignoreFile
includes/Admin/Menu.php:1:<?php // phpcs:ignoreFile
includes/Admin/AttendancePage.php:1:<?php // phpcs:ignoreFile
includes/Admin/SettingsPage.php:25:		if ( ! current_user_can( 'fb_manage_settings' ) && ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/SettingsPage.php:40:		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'fb_manage_settings' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/SettingsPage.php:45:						? (array) wp_unslash( $_POST['fbm_settings'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in Options::saveAll.
includes/Admin/Notices.php:1:<?php // phpcs:ignoreFile
includes/Admin/ThemePage.php:25:		if ( ! current_user_can( 'fb_manage_settings' ) && ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/ThemePage.php:40:		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'fb_manage_settings' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/ThemePage.php:56:						// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reading uploaded JSON.
includes/Admin/ThemePage.php:72:						? (array) wp_unslash( $_POST['fbm_theme'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in Options::saveAll.
includes/Admin/DatabasePage.php:1:<?php // phpcs:ignoreFile
includes/Admin/PermissionsPage.php:1:<?php // phpcs:ignoreFile
includes/Admin/EmailsPage.php:26:		if ( ! current_user_can( 'fb_manage_emails' ) && ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/EmailsPage.php:29:        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview; template sanitized in handler.
includes/Admin/EmailsPage.php:46:		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'fb_manage_emails' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/EmailsPage.php:60:				(array) wp_unslash( $_POST['templates'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in array_map.
includes/Admin/EmailsPage.php:77:        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in handle_post().
includes/Admin/EmailsPage.php:83:        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in handle_post().
includes/Admin/EmailsPage.php:103:        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview; template sanitized below.
includes/Db/Migrations.php:1:<?php // phpcs:ignoreFile
templates/emails/admin-notification.php:24:// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $safe_summary is sanitized via wp_kses_post().
templates/emails/applicant-confirmation.php:37:// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $safe_summary is sanitized via wp_kses_post().
templates/public/attendance-manager.php:2:// phpcs:ignoreFile
templates/admin/database.php:117:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
templates/admin/database.php:128:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
templates/admin/database.php:174:<?php if ( current_user_can( 'fb_export_entries' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
templates/admin/diagnostics.php:15:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom plugin capability.
templates/admin/dashboard.php:16:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom plugin capability.
templates/admin/settings.php:2:// phpcs:ignoreFile
templates/admin/theme.php:2:// phpcs:ignoreFile
templates/admin/theme.php:10:// phpcs:disable Generic.Files.LineLength.TooLong
templates/admin/database-view.php:82:<?php if ( current_user_can( 'fb_export_entries' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
templates/admin/database-view.php:90:<?php if ( current_user_can( 'fb_delete_entries' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
templates/admin/forms.php:16:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom plugin capability.
templates/admin/attendance.php:2:// phpcs:ignoreFile```

## Detailed suppressed issues

| File | Line | Sniff | Message |
|---|---:|---|---|
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 39 | `Generic.WhiteSpace.ScopeIndent.Incorrect` | Line indented incorrectly; expected at least 2 tabs, found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 39 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 12 spaces but found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 40 | `WordPress.DB.PreparedSQL.InterpolatedNotPrepared` | Use placeholders and $wpdb->prepare(); found interpolated variable {$t_att} at "SELECT attendance_at FROM {$t_att} WHERE application_id = %d AND status = 'present' ORDER BY attendance_at DESC LIMIT 1" |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 178 | `Generic.WhiteSpace.ScopeIndent.Incorrect` | Line indented incorrectly; expected at least 2 tabs, found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $base_sql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $order_sql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $limit_sql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 209 | `Generic.WhiteSpace.ScopeIndent.Incorrect` | Line indented incorrectly; expected at least 2 tabs, found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 210 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $count_base |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 256 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 12 spaces but found 4 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 256 | `Generic.WhiteSpace.ScopeIndent.Incorrect` | Line indented incorrectly; expected at least 2 tabs, found 1 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 257 | `WordPress.DB.PreparedSQL.InterpolatedNotPrepared` | Use placeholders and $wpdb->prepare(); found interpolated variable {$t_att} at "SELECT t.id,t.status,t.attendance_at,t.event_id,t.type,t.method,t.recorded_by_user_id,t.is_void,t.void_reason,t.void_by_user_id,t.void_at FROM {$t_att} t WHERE {$where_sql} ORDER BY t.attendance_at ASC" |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 257 | `WordPress.DB.PreparedSQL.InterpolatedNotPrepared` | Use placeholders and $wpdb->prepare(); found interpolated variable {$where_sql} at "SELECT t.id,t.status,t.attendance_at,t.event_id,t.type,t.method,t.recorded_by_user_id,t.is_void,t.void_reason,t.void_by_user_id,t.void_at FROM {$t_att} t WHERE {$where_sql} ORDER BY t.attendance_at ASC" |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 257 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 220 characters |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 257 | `WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare` | Replacement variables found, but no valid placeholders found in the query. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 275 | `Generic.WhiteSpace.ScopeIndent.Incorrect` | Line indented incorrectly; expected at least 2 tabs, found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 275 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 12 spaces but found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 276 | `WordPress.DB.PreparedSQL.InterpolatedNotPrepared` | Use placeholders and $wpdb->prepare(); found interpolated variable {$t_notes} at "SELECT attendance_id,user_id,note_text,created_at FROM {$t_notes} WHERE attendance_id IN ($placeholders) ORDER BY created_at ASC" |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 276 | `WordPress.DB.PreparedSQL.InterpolatedNotPrepared` | Use placeholders and $wpdb->prepare(); found interpolated variable $placeholders at "SELECT attendance_id,user_id,note_text,created_at FROM {$t_notes} WHERE attendance_id IN ($placeholders) ORDER BY created_at ASC" |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 276 | `WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare` | Replacement variables found, but no valid placeholders found in the query. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 307 | `Universal.NamingConventions.NoReservedKeywordParameterNames.voidFound` | It is recommended not to use reserved keyword "void" as function parameter name. Found: $void |
