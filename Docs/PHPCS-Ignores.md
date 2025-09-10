Docs-Revision: 2025-09-10 (v1.4.0-rc.4.5.0 – DB UX columns)

# PHPCS Ignores & Suppressed Issues Dashboard

_Generated: 2025-09-06 10:25:04Z

This report shows which issues are currently suppressed via `phpcs:ignore` and what would fail if annotations were disabled.

### 2025-09-10
- Unsuppressed prepared-SQL and placeholder warnings in `includes/Attendance/AttendanceRepo.php`.
- No new ignores added for DB UX columns/export work.

## Snapshot

- Suppressed issues (from `--ignore-annotations` run): **21**
- Ignore annotations present: **104** lines

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
Docs/CS-Backlog.md:63:keeping only surgical one-line // phpcs:ignore … where wp_kses_post() or strict IN (…) placeholders trigger false positives.
Docs/ISSUES-foodbank-manager.md:257:    - No `phpcs:ignoreFile` on `includes/Attendance/AttendanceRepo.php`; tests cover policy edges.
bin/build-phpcs-ignore-report.php:78:$md .= "This report shows which issues are currently suppressed via `phpcs:ignore` and what would fail if annotations were disabled.\n\n";
bin/build-phpcs-ignore-report.php:7: *  - analysis/phpcs-ignores.txt  (grep of phpcs:ignore etc.)
foodbank-manager.php:1:<?php // phpcs:ignoreFile
includes/Admin/AttendancePage.php:123:            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- placeholders strictly match $ids count
includes/Admin/AttendancePage.php:169:        $req                 = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- flag only.
includes/Admin/AttendancePage.php:23:        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Action checked later.
includes/Admin/AttendancePage.php:2:// phpcs:ignoreFile
includes/Admin/AttendancePage.php:36:        $g = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized below.
includes/Admin/DatabasePage.php:100:		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce validated below.
includes/Admin/DatabasePage.php:102:			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only nonce param; sanitized here.
includes/Admin/DatabasePage.php:124:		$can_sensitive = current_user_can( 'fb_view_sensitive' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/DatabasePage.php:138:		if ( ! current_user_can( 'fb_manage_database' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/DatabasePage.php:164:		if ( ! current_user_can( 'fb_manage_database' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/DatabasePage.php:1:<?php // phpcs:ignoreFile
includes/Admin/DatabasePage.php:224:			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
includes/Admin/DatabasePage.php:227:			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
includes/Admin/DatabasePage.php:234:				$has_file = isset( $_GET['has_file'] ) ? true : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.
includes/Admin/DatabasePage.php:235:				$consent  = isset( $_GET['consent'] ) ? true : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.
includes/Admin/DatabasePage.php:237:				$date_from = isset( $_GET['date_from'] ) ? self::sanitize_date( wp_unslash( $_GET['date_from'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by sanitize_date().
includes/Admin/DatabasePage.php:238:				$date_to   = isset( $_GET['date_to'] ) ? self::sanitize_date( wp_unslash( $_GET['date_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by sanitize_date().
includes/Admin/DatabasePage.php:240:			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
includes/Admin/DatabasePage.php:242:			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
includes/Admin/DatabasePage.php:245:			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
includes/Admin/DatabasePage.php:252:			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
includes/Admin/DatabasePage.php:30:		if ( ! current_user_can( 'fb_manage_database' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/DatabasePage.php:36:		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized and checked below.
includes/Admin/DatabasePage.php:42:					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- IDs sanitized below.
includes/Admin/DatabasePage.php:46:					$mask = ! current_user_can( 'fb_view_sensitive' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/DatabasePage.php:50:					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- IDs sanitized below.
includes/Admin/DatabasePage.php:57:						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- IDs sanitized below.
includes/Admin/DatabasePage.php:64:						$mask = ! current_user_can( 'fb_view_sensitive' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/DatabasePage.php:71:                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only ID parameter.
includes/Admin/DatabasePage.php:98:		$can_sensitive = current_user_can( 'fb_view_sensitive' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Admin/Notices.php:1:<?php // phpcs:ignoreFile
includes/Admin/Notices.php:31:        if (!isset($_GET['fbm_repair_caps'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below
includes/Attendance/AttendanceRepo.php:186:// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- placeholders strictly match params length.
includes/Attendance/AttendanceRepo.php:217:// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- placeholders strictly match params length.
includes/Attendance/AttendanceRepo.php:266:        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, Generic.Files.LineLength.TooLong -- table name is constant.
includes/Attendance/AttendanceRepo.php:290:// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is constant and placeholders match count.
includes/Attendance/AttendanceRepo.php:39:// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- table name is constant and placeholders match.
includes/Attendance/Policy.php:1:<?php // phpcs:ignoreFile
includes/Attendance/TokenService.php:1:<?php // phpcs:ignoreFile
includes/Auth/Capabilities.php:1:<?php // phpcs:ignoreFile
includes/Auth/CapabilitiesResolver.php:1:<?php // phpcs:ignoreFile
includes/Auth/Permissions.php:1:<?php // phpcs:ignoreFile
includes/Auth/Roles.php:1:<?php // phpcs:ignoreFile
includes/Core/Assets.php:1:<?php // phpcs:ignoreFile
includes/Core/Hooks.php:1:<?php // phpcs:ignoreFile
includes/Core/Options.php:259:				$out['custom_css'] = function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $css ) : strip_tags( $css ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Fallback when WordPress is unavailable.
includes/Core/Options.php:2:// phpcs:ignoreFile
includes/Core/Plugin.php:2:// phpcs:ignoreFile
includes/Database/ApplicationsRepo.php:2:// phpcs:ignoreFile
includes/Db/Migrations.php:1:<?php // phpcs:ignoreFile
includes/Exports/CsvExporter.php:1:<?php // phpcs:ignoreFile
includes/Http/FormSubmitController.php:112:			$uploads = self::process_uploads( $_FILES, $policy ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above
includes/Http/FormSubmitController.php:129:			$ip_bin       = '' !== $ip ? @inet_pton( $ip ) : null; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
includes/Http/FormSubmitController.php:191:					$tokens['qr_code_url'] = 'data:image/png;base64,' . base64_encode( $writer->write( $qr )->getString() ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- output data URI
includes/Http/FormSubmitController.php:1:<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName
includes/Http/FormSubmitController.php:243:		$raw = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked in handle()
includes/Http/FormSubmitController.php:68:				$token     = sanitize_text_field( wp_unslash( $_POST[ $token_key ] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above
includes/Http/FormSubmitController.php:71:					$endpoint = 'turnstile' === $provider ? 'https://challenges.cloudflare.com/turnstile/v0/siteverify' : 'https://www.google.com/recaptcha/api/siteverify'; // phpcs:ignore Generic.Files.LineLength.TooLong
includes/Logging/Audit.php:1:<?php // phpcs:ignoreFile
includes/Mail/Logger.php:1:<?php // phpcs:ignoreFile
includes/Mail/Templates.php:2:// phpcs:ignoreFile
includes/Rest/Api.php:204:	private static function send_applicant_email( string $to, int $app_id, string $first_name ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Used in included template.
includes/Rest/Api.php:213:				$qr_code_url = 'data:image/png;base64,' . base64_encode( $writer->write( $qr )->getString() ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Embedding QR code.
includes/Rest/Api.php:236:	private static function send_admin_email( int $app_id, string $first, string $last, string $email, string $postcode ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Used in included template.
includes/Rest/Api.php:2:// phpcs:ignoreFile
includes/Rest/AttendanceController.php:2:// phpcs:ignoreFile
includes/Security/Crypto.php:1:<?php // phpcs:ignoreFile
includes/Security/Helpers.php:1:<?php // phpcs:ignoreFile
includes/Shortcodes/AttendanceManager.php:27:	public static function render( array $atts = array() ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Attributes reserved for future use.
includes/Shortcodes/AttendanceManager.php:2:// phpcs:ignoreFile
includes/Shortcodes/AttendanceManager.php:30:                if ( ! current_user_can( 'fb_manage_attendance' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
includes/Shortcodes/Entries.php:27:	public static function render( array $atts = array() ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Attributes reserved for future use.
includes/Shortcodes/Entries.php:2:// phpcs:ignoreFile
includes/Shortcodes/Form.php:2:// phpcs:ignoreFile
includes/Shortcodes/Form.php:52:			$raw_param = (string) wp_unslash( $_GET['fbm_err'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via sanitize_key.
includes/Shortcodes/Form.php:61:		if ( isset( $_GET['fbm_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only error flag.
includes/Shortcodes/Form.php:68:			$json = file_get_contents( $preset_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local preset file.
includes/UI/Theme.php:2:// phpcs:ignoreFile
templates/admin/attendance.php:2:// phpcs:ignoreFile
templates/admin/dashboard.php:16:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom plugin capability.
templates/admin/dashboard.php:1:<?php // phpcs:ignoreFile
templates/admin/database-view.php:82:<?php if ( current_user_can( 'fb_manage_database' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
templates/admin/database-view.php:90:<?php if ( current_user_can( 'fb_manage_database' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
templates/admin/database.php:118:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
templates/admin/database.php:129:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
templates/admin/database.php:174:<?php if ( current_user_can( 'fb_manage_database' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
templates/admin/diagnostics.php:15:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom plugin capability.
templates/admin/diagnostics.php:1:<?php // phpcs:ignoreFile
templates/admin/forms.php:16:// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom plugin capability.
templates/admin/permissions.php:2:// phpcs:ignoreFile
templates/admin/settings.php:2:// phpcs:ignoreFile
templates/admin/theme.php:2:// phpcs:ignoreFile
templates/emails/admin-notification.php:24:// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $safe_summary is sanitized via wp_kses_post().
templates/emails/applicant-confirmation.php:37:// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $safe_summary is sanitized via wp_kses_post().
templates/public/attendance-manager.php:2:// phpcs:ignoreFile
uninstall.php:27:        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- uninstall-time schema cleanup.
uninstall.php:40:    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- targeted uninstall cleanup.
uninstall.php:45:        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- targeted uninstall cleanup.
uninstall.php:54:} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement -- Silent by design.```

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
