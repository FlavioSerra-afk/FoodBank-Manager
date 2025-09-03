# PHPCS Ignores & Suppressed Issues Dashboard

_Generated: 2025-09-02 23:57:10Z

This report shows which issues are currently suppressed via `phpcs:ignore` and what would fail if annotations were disabled.

## Snapshot

- Suppressed issues (from `--ignore-annotations` run): **687**
- Ignore annotations present: **63** lines

## Top sniffs by count

| Sniff | Count | Recipe |
|---|---:|---|
| `Generic.Files.LineLength.TooLong` | 64 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Security.NonceVerification.Recommended` | 49 | - **Fix:** For POST/mutations add `wp_nonce_field()` + `check_admin_referer()`. For read-only GET filters sanitize and keep a one-line justified ignore. |
| `Squiz.Commenting.FunctionComment.Missing` | 33 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.WP.Capabilities.Unknown` | 31 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | 29 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | 29 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Squiz.Commenting.FileComment.Missing` | 27 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | 26 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | 24 | - **Fix:** Wrap `$_GET/$_POST` with `wp_unslash()` + `sanitize_text_field()` / `absint()` / whitelist enums. |
| `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | 24 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | 24 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Squiz.Commenting.ClassComment.Missing` | 22 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | 22 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Security.NonceVerification.Missing` | 20 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `PEAR.Functions.FunctionCallSignature.Indent` | 16 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |

## Top files by suppressed issues

| File | Count |
|---|---:|
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 167 |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 68 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 62 |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 44 |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 36 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 32 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 32 |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 30 |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 21 |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 19 |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 15 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 14 |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 14 |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 14 |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 13 |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 10 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 9 |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 8 |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 8 |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 7 |

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
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Helpers |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 9 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function verify_nonce() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 10 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_REQUEST[$name] |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 14 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function require_nonce() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 20 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function sanitize_text() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 24 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function esc_html() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 28 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function mask_email() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 41 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function mask_postcode() |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 7 | `Generic.CodeAnalysis.EmptyStatement.DetectedIf` | Empty IF statement detected |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 8 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 11 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Crypto |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 15 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function get_kek() |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 21 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode` | base64_decode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 29 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function ensure_sodium() |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 33 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 36 spaces but found 40 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 34 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 36 spaces but found 40 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 35 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 199 characters |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 43 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function encryptSensitive() |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 54 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | base64_encode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 57 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function decryptSensitive() |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 62 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode` | base64_decode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 9 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class ApplicationsRepo |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 19 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 20 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 21 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 22 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 81 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 82 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $prepared |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 85 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $count_sql |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 93 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$id" missing |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 101 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 106 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $files_sql |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 111 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$id" missing |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 117 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 9 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class CsvExporter |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 10 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$filename" missing |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 13 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 14 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 24 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 51 | `WordPress.WP.AlternativeFunctions.file_system_operations_fclose` | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 55 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$includeVoided" missing |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 55 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$filename" missing |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 58 | `Squiz.Commenting.FunctionComment.MissingParamComment` | Missing parameter comment |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 59 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 61 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 164 characters |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 72 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 72 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 95 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 95 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 96 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 122 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 122 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 123 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 128 | `WordPress.WP.AlternativeFunctions.file_system_operations_fclose` | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 11 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Plugin |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 15 | `Squiz.Commenting.VariableComment.Missing` | Missing member variable doc comment |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 37 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 38 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 47 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 47 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 48 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 48 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 49 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 49 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 50 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 50 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 51 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 51 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 52 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 52 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 53 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 53 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 55 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 28 spaces but found 32 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 56 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 28 spaces but found 32 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 68 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 69 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 75 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 44 spaces but found 48 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 76 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 44 spaces but found 48 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 77 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 242 characters |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 84 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 44 spaces but found 48 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 85 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 44 spaces but found 48 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 86 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 201 characters |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 96 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 97 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 98 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 207 characters |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 14 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Hooks |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 16 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function register() |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 23 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function register_shortcodes() |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Assets |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 9 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function register() |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 14 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function enqueue_front() |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 18 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function enqueue_admin() |
| /workspace/FoodBank-Manager/includes/Core/Options.php | 258 | `WordPress.WP.AlternativeFunctions.strip_tags_strip_tags` | strip_tags() is discouraged. Use the more comprehensive wp_strip_all_tags() instead. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 41 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 41 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 42 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 46 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 186 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 50 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 51 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 51 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['fbm_err'] |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 60 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 67 | `WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents` | file_get_contents() is discouraged. Use wp_remote_get() for remote URLs instead. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 79 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 164 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 117 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 182 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 136 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 183 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 155 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 191 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 189 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 197 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Entries.php | 26 | `Generic.CodeAnalysis.UnusedFunctionParameter.Found` | The method parameter $atts is never used |
| /workspace/FoodBank-Manager/includes/Shortcodes/AttendanceManager.php | 26 | `Generic.CodeAnalysis.UnusedFunctionParameter.Found` | The method parameter $atts is never used |
| /workspace/FoodBank-Manager/includes/Shortcodes/AttendanceManager.php | 29 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_checkin" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 26 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_emails" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 30 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 30 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 46 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_emails" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 60 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['templates'] |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 78 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 84 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 104 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 12 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class PermissionsPage |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 17 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_permissions" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 20 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 21 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 28 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 35 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 38 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '(string)'. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 40 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_SERVER['REQUEST_METHOD'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 40 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_SERVER['REQUEST_METHOD'] |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 65 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_SERVER['REQUEST_METHOD'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 65 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_SERVER['REQUEST_METHOD'] |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 86 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_SERVER['REQUEST_METHOD'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 86 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_SERVER['REQUEST_METHOD'] |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 88 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 88 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 90 | `WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents` | file_get_contents() is discouraged. Use wp_remote_get() for remote URLs instead. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 125 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_SERVER['REQUEST_METHOD'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 125 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_SERVER['REQUEST_METHOD'] |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 128 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 144 | `PEAR.Functions.FunctionCallSignature.ContentAfterOpenBracket` | Opening parenthesis of a multi-line function call must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 150 | `PEAR.Functions.FunctionCallSignature.CloseBracketLine` | Closing parenthesis of a multi-line function call must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 156 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 161 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 7 space(s) between "'fb_read_entries'" and double arrow, but found 6. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 162 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 7 space(s) between "'fb_edit_entries'" and double arrow, but found 6. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 163 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'fb_delete_entries'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 164 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'fb_export_entries'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 165 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 7 space(s) between "'fb_manage_forms'" and double arrow, but found 6. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 166 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 4 space(s) between "'fb_manage_settings'" and double arrow, but found 3. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 167 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 6 space(s) between "'fb_manage_emails'" and double arrow, but found 5. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 168 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 2 space(s) between "'fb_manage_encryption'" and double arrow, but found 1. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 169 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 4 space(s) between "'attendance_checkin'" and double arrow, but found 3. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 170 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 7 space(s) between "'attendance_view'" and double arrow, but found 6. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 171 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'attendance_export'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 172 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 6 space(s) between "'attendance_admin'" and double arrow, but found 5. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 173 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 8 space(s) between "'read_sensitive'" and double arrow, but found 7. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 25 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 40 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 57 | `WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents` | file_get_contents() is discouraged. Use wp_remote_get() for remote URLs instead. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 72 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['fbm_theme'] |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 25 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 26 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 27 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 181 characters |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 25 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 40 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 45 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['fbm_settings'] |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 12 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class DatabasePage |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 13 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function route() |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 14 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_read_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 18 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 18 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 32 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 39 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function parseFilters() |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 41 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 41 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 41 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['status'] |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 42 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 42 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 42 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['date_from'] |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 43 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 43 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 43 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['date_to'] |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 44 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 44 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 44 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['city'] |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 45 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 45 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 45 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['postcode'] |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 46 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 46 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 46 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['search'] |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 47 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 47 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 48 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 48 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 49 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 49 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 49 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 49 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 52 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 53 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 59 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 59 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 59 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['orderby'] |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 60 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 60 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 60 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['order'] |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 64 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function handleExportList() |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 65 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_export_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 72 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "read_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 76 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 81 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function handleExportSingle() |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 82 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_export_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 85 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 85 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 89 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "read_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 91 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 97 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function handleDelete() |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 98 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_delete_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 101 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 101 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 108 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderList() |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 115 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "read_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 116 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 120 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderSingle() |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 121 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 121 | `WordPress.Security.ValidatedSanitizedInput.InputNotValidated` | Detected usage of a possibly undefined superglobal array index: $_GET['view']. Use isset() or empty() to check the index exists before using it |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 126 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "read_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 130 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 131 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 132 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 135 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $can_sensitive is never used |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 136 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 13 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class AttendancePage |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 18 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_view" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 19 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 19 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 22 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 22 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 35 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 36 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 178 characters |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 40 | `WordPress.DateTime.CurrentTimeTimestamp.RequestedUTC` | Don't use current_time() for retrieving a Unix (UTC) timestamp. Use time() instead. Found: current_time( 'timestamp', true ) |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 40 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 115 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function decorateRows() |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 123 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 132 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 159 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_export" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 167 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "read_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 168 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 189 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "read_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 192 | `PSR2.Files.EndFileNewline.TooMany` | Expected 1 blank line at end of file; 2 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Menu |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 12 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 12 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 19 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_read_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 3 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 3 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 162 characters |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 5 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 5 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 33 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 8 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 33 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 8 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 33 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 33 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 34 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 7 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 34 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 34 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 35 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 5 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 35 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 5 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 35 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 35 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 36 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 8 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 36 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 36 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 37 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 37 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 37 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 180 characters |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 38 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 2 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 38 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 2 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 38 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 38 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 41 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function submenu() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 41 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 41 | `Universal.NamingConventions.NoReservedKeywordParameterNames.parentFound` | It is recommended not to use reserved keyword "parent" as function parameter name. Found: $parent |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 41 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 45 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function safeInclude() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 45 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 45 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 47 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 47 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 51 | `Universal.WhiteSpace.PrecisionAlignment.Found` | Found precision alignment of 3 spaces. |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 56 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 2 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 56 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 58 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 60 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 3 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 60 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 62 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 6 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 62 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 64 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 5 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 64 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 66 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 3 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 66 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 68 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 6 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 68 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 70 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 0 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 70 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Migrations |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 12 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function maybe_migrate() |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class TokenService |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 22 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | base64_encode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 25 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$max_age" missing |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 36 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode` | base64_decode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 10 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class AttendanceRepo |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 11 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$application_id" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 14 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 14 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 20 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 23 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 24 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 43 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 43 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 48 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 51 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 51 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceAfterOpen` | Expected 1 space after open parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 51 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceBeforeClose` | Expected 1 space before close parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 53 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 53 | `Squiz.Strings.DoubleQuoteUsage.NotRequired` | String "1=1" does not require double quotes; use single quotes instead |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 53 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 55 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 55 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 59 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.ExtraSpaceAfterCloseParenthesis` | Expected exactly one space between closing parenthesis and opening control structure; "  " found. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Squiz.ControlStructures.ControlSignature.SpaceAfterCloseParenthesis` | Expected 1 space after closing parenthesis; found 2 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Squiz.Strings.DoubleQuoteUsage.NotRequired` | String "a.form_id = %d" does not require double quotes; use single quotes instead |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `Squiz.Strings.DoubleQuoteUsage.NotRequired` | String "t.event_id = %d" does not require double quotes; use single quotes instead |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `Squiz.Strings.DoubleQuoteUsage.NotRequired` | String "t.recorded_by_user_id = %d" does not require double quotes; use single quotes instead |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 78 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 80 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 85 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 85 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceAfterOpen` | Expected 1 space after open parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 85 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceBeforeClose` | Expected 1 space before close parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 86 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 86 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceAfterOpen` | Expected 1 space after open parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 86 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceBeforeClose` | Expected 1 space before close parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 87 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceAfterOpen` | Expected 1 space after open parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 87 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceBeforeClose` | Expected 1 space before close parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 89 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 116 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 137 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 137 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 149 | `WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine` | Each item in a multi-line array must be on a new line. Found: 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 149 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 150 | `WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine` | Each item in a multi-line array must be on a new line. Found: 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 150 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 151 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 155 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 156 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 156 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 159 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 160 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $prepared |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 160 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 162 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $countSql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 163 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 163 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $preparedCount |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 165 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 165 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 165 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 178 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 178 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpaceBeforeEquals` | Incorrect spacing between argument "$includeVoided" and equals sign; expected 1 but found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 178 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpaceAfterEquals` | Incorrect spacing between default value and equals sign for argument "$includeVoided"; expected 1 but found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 178 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 183 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 183 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 185 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 185 | `WordPress.WhiteSpace.OperatorSpacing.SpacingBefore` | Expected 1 space before "!=="; 3 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 185 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 185 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 185 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 186 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 186 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 186 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 186 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 186 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 188 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 216 characters |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 189 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 189 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 190 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 190 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 190 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 192 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 192 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 192 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 196 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $noteSql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 196 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 198 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 198 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 199 | `WordPress.Arrays.ArrayKeySpacingRestrictions.NoSpacesAroundArrayKeys` | Array keys must be surrounded by spaces unless they contain a string or an integer. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 199 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 199 | `WordPress.WhiteSpace.CastStructureSpacing.NoSpaceBeforeOpenParenthesis` | Expected a space before the type cast open parenthesis; none found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 201 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 201 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 202 | `WordPress.Arrays.ArrayKeySpacingRestrictions.NoSpacesAroundArrayKeys` | Array keys must be surrounded by spaces unless they contain a string or an integer. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 202 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 202 | `WordPress.WhiteSpace.CastStructureSpacing.NoSpaceBeforeOpenParenthesis` | Expected a space before the type cast open parenthesis; none found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 207 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$attendanceId" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 207 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$void" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 207 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$reason" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 207 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$actorId" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 207 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$nowUtc" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 210 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 210 | `Universal.NamingConventions.NoReservedKeywordParameterNames.voidFound` | It is recommended not to use reserved keyword "void" as function parameter name. Found: $void |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 210 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 212 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 212 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 214 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 9 space(s) between "'is_void'" and double arrow, but found 8. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 215 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'void_reason'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 216 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 1 space(s) between "'void_by_user_id'" and double arrow, but found 0. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 216 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "=>"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 217 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 9 space(s) between "'void_at'" and double arrow, but found 8. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 221 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 9 space(s) between "'is_void'" and double arrow, but found 8. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 222 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'void_reason'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 223 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 1 space(s) between "'void_by_user_id'" and double arrow, but found 0. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 223 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "=>"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 224 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 9 space(s) between "'void_at'" and double arrow, but found 8. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 230 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 230 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 231 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 231 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 232 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 232 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 237 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$attendanceId" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 237 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$userId" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 237 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$note" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 237 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$nowUtc" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 240 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 240 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 250 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 250 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 254 | `PSR2.Files.EndFileNewline.TooMany` | Expected 1 blank line at end of file; 2 found |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Policy |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 15 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 15 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 16 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 16 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 21 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 21 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Audit |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 8 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function log() |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 8 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 8 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 21 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 21 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 12 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class FormSubmitController |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 22 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 24 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 24 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['first_name'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 24 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['first_name'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 25 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 25 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['last_name'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 25 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['last_name'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 26 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 26 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['email'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 27 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 27 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['phone'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 27 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['phone'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 28 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 28 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['postcode'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 28 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['postcode'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 29 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 29 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 29 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['notes'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 30 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 30 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['consent'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 30 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['consent'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 31 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 31 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['form_id'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 31 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['form_id'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 53 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 53 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST[$response_key] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 56 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 173 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 57 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_SERVER['REMOTE_ADDR'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 58 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 78 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 78 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 79 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 79 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_FILES['upload'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 95 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 16 space(s) between "'test_form'" and double arrow, but found 1. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 130 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_SERVER['REMOTE_ADDR'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 131 | `WordPress.PHP.NoSilencedErrors.Discouraged` | Silencing errors is strongly discouraged. Use proper error checking instead. Found: @inet_pton( $ip ... |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 137 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 232 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 154 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 181 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 167 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 186 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | base64_encode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 222 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 228 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Logger |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 9 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function init() |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 14 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function log_succeeded() |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 18 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function log_failed() |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 22 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function insert_log() |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 203 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $first_name is never used |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 212 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | base64_encode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 235 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $first is never used |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 235 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $last is never used |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 235 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $email is never used |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 235 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $postcode is never used |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Capabilities |
| /workspace/FoodBank-Manager/includes/Auth/Permissions.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Auth/Permissions.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Permissions |
| /workspace/FoodBank-Manager/includes/Auth/Permissions.php | 9 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function user_can() |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Roles |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 60 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 63 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 63 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 64 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 64 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 64 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class CapabilitiesResolver |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 8 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function boot() |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 12 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 15 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 16 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 162 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 26 | `WordPress.WP.I18n.LowLevelTranslationFunction` | Use of the "translate()" function is reserved for low-level API usage. |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 26 | `WordPress.WP.I18n.NonSingularStringLiteralText` | The $text parameter must be a single text string literal. Found: ucfirst( str_replace( '_', ' ', $t ) ) |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 26 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 177 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 31 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 167 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 37 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 170 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 64 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 183 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 66 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 353 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 76 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 198 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 79 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 289 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 83 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 216 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 86 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 219 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 23 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 173 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 24 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 167 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 25 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 212 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 58 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 223 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 59 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 187 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 60 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 174 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 80 | `WordPress.WP.GlobalVariablesOverride.Prohibited` | Overriding WordPress globals is prohibited. Found assignment to $order |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 90 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$url'. |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 109 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 166 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 110 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 202 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 149 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_export" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 164 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 186 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 173 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_admin" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 205 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 164 characters |
| /workspace/FoodBank-Manager/templates/admin/forms.php | 17 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_forms" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/dashboard.php | 17 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_read_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/database.php | 118 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_export_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/database.php | 129 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_delete_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/database.php | 174 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_export_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/diagnostics.php | 16 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 35 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 192 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 39 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 201 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 50 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 51 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 52 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 57 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 217 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 64 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 212 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 68 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 206 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 72 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 167 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 76 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 215 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 80 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 202 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 102 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 197 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 106 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 219 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 112 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 205 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 113 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 199 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 115 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 204 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 122 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 293 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 123 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 299 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 124 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 289 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 125 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 328 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 130 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 311 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 131 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 326 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 136 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 330 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 139 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 204 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 140 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 198 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 141 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 198 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 148 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 189 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 149 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 339 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 158 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 328 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 21 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 22 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 23 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 24 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 25 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 28 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 29 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 30 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 31 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 34 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 35 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 36 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 37 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 40 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 41 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 42 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 45 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 46 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 47 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 66 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 202 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 67 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 233 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 70 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 189 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 77 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 225 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 88 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 230 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 98 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 199 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 99 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 230 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 102 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 189 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 109 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 222 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 120 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 227 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 132 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 205 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 138 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 240 characters; contains 328 characters |
| /workspace/FoodBank-Manager/templates/admin/database-view.php | 82 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_export_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/database-view.php | 90 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_delete_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/emails/admin-notification.php | 25 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$safe_summary'. |
| /workspace/FoodBank-Manager/templates/emails/applicant-confirmation.php | 38 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$safe_summary'. |
