# PHPCS Ignores & Suppressed Issues Dashboard

_Generated: 2025-09-02 23:40:53Z

This report shows which issues are currently suppressed via `phpcs:ignore` and what would fail if annotations were disabled.

## Snapshot

- Suppressed issues (from `--ignore-annotations` run): **2475**
- Ignore annotations present: **63** lines

## Top sniffs by count

| Sniff | Count | Recipe |
|---|---:|---|
| `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | 1319 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | 99 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | 99 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Security.NonceVerification.Recommended` | 81 | - **Fix:** For POST/mutations add `wp_nonce_field()` + `check_admin_referer()`. For read-only GET filters sanitize and keep a one-line justified ignore. |
| `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | 64 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | 64 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Generic.Files.LineLength.TooLong` | 63 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Squiz.Commenting.FunctionComment.Missing` | 59 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Universal.Arrays.DisallowShortArraySyntax.Found` | 46 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Generic.Files.LineLength.MaxExceeded` | 40 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | 32 | - **Fix:** Wrap `$_GET/$_POST` with `wp_unslash()` + `sanitize_text_field()` / `absint()` / whitelist enums. |
| `WordPress.WP.Capabilities.Unknown` | 31 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | 30 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | 30 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Squiz.Commenting.FileComment.Missing` | 27 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |

## Top files by suppressed issues

| File | Count |
|---|---:|
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 448 |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 270 |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 235 |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 204 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 190 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 170 |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 142 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 109 |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 100 |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 75 |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 70 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 65 |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 57 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 47 |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 32 |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 32 |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 30 |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 26 |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 23 |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 22 |

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
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class CapabilitiesResolver |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 8 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 8 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function boot() |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 9 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 10 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 12 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 12 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 15 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/CapabilitiesResolver.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Roles |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 8 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 9 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 10 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 11 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 12 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 25 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 39 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 55 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 57 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 58 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 59 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 59 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 59 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 60 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 60 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 62 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 63 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 63 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 64 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 64 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 64 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 64 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 64 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 65 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 65 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 66 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 67 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Roles.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Capabilities |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 8 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 9 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 10 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 11 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 12 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 14 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Capabilities.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Auth/Permissions.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Auth/Permissions.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Permissions |
| /workspace/FoodBank-Manager/includes/Auth/Permissions.php | 9 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function user_can() |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Audit |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 8 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 8 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function log() |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 8 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 8 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 8 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 9 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 10 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 11 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 12 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 13 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 18 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 18 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 19 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 19 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 21 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 21 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 21 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Logging/Audit.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 13 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class FormSubmitController |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 14 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function handle() |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 17 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 149 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 20 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 22 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 22 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['first_name'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 22 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['first_name'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 23 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 23 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['last_name'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 23 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['last_name'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 24 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 24 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['email'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 25 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 25 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['phone'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 25 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['phone'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 26 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 26 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['postcode'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 26 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['postcode'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 27 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 27 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 27 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['notes'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 28 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 28 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['consent'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 28 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['consent'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 29 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 29 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['form_id'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 29 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['form_id'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 51 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 5 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 51 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 51 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST[$response_key] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 52 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 7 spaces but found 3 spaces |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 54 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 4 spaces but found 2 spaces |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 54 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 168 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 55 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 55 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_SERVER['REMOTE_ADDR'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 55 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_SERVER['REMOTE_ADDR'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 55 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 55 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 172 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 56 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 3 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 57 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 57 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 5 spaces but found 3 spaces |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 58 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 59 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 60 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 62 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 66 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 69 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 70 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 71 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 72 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 74 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 75 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 75 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 75 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 76 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 76 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 10 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 76 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 76 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_FILES['upload'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 77 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 8 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 78 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 78 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 79 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 80 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 81 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 82 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 84 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 85 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 86 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 87 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 88 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 89 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 90 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 91 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 92 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 92 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 16 space(s) between "'test_form'" and double arrow, but found 1. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 93 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 94 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 95 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 96 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 97 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 98 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 99 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 100 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 101 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 102 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 103 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 104 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 105 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 106 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 107 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 108 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 109 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 111 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 112 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 113 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 114 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 115 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 116 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 118 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 119 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 120 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 121 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 122 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 123 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 125 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 126 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 127 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 127 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_SERVER['REMOTE_ADDR'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 127 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_SERVER['REMOTE_ADDR'] |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 128 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 128 | `WordPress.PHP.NoSilencedErrors.Discouraged` | Silencing errors is strongly discouraged. Use proper error checking instead. Found: @inet_pton( $ip ... |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 129 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 131 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 132 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 133 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 134 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 134 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 232 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 135 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 136 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 137 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 138 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 139 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 140 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 141 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 142 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 143 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 144 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 145 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 146 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 148 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 149 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 150 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 151 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 151 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 181 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 152 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 153 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 154 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 155 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 156 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 157 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 158 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 159 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 160 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 161 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 163 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 164 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 164 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 164 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 141 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 165 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 165 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 150 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 166 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 167 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 169 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 170 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 171 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 172 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 173 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 174 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 175 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 176 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 178 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 179 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 180 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 181 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 181 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 20 spaces but found 5 spaces |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 182 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 182 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 16 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 183 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 183 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | base64_encode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 184 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 185 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 186 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 187 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 189 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 189 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 21 spaces but found 2 spaces |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 190 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 192 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 192 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 4 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 193 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 194 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 195 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 196 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 197 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 198 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 199 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 200 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 201 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 203 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 204 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 206 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 207 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 207 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 5 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 208 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 208 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 7 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 209 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 211 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 211 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 5 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 212 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 213 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 214 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 215 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 216 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 217 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 218 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 219 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 219 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 220 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 221 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 222 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 223 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 224 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 225 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 225 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 226 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 227 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 228 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 229 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 231 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 231 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function consent_text() |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 232 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 232 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 144 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 233 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 234 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 235 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 9 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class CsvExporter |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 10 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 10 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 10 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$filename" missing |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 11 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 11 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 12 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 12 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 20 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 47 | `WordPress.WP.AlternativeFunctions.file_system_operations_fclose` | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 51 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 51 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$includeVoided" missing |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 51 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$filename" missing |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 52 | `Squiz.Commenting.FunctionComment.MissingParamComment` | Missing parameter comment |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 53 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 55 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 55 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 164 characters |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 57 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 58 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 59 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 60 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 63 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 154 characters |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 64 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 64 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 66 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 67 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 69 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 70 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 71 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 72 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 73 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 74 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 75 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 76 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 78 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 79 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 80 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 81 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 82 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 84 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 85 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 86 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 87 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 87 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 87 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 88 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 88 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 88 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 88 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 89 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 90 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 90 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 90 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 91 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 92 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 93 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 94 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 95 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 96 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 97 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 98 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 99 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 100 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 101 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 102 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 103 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 104 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 105 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 106 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 107 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 108 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 109 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 110 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 111 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 112 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 113 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 114 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 114 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 114 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 115 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 115 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 115 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 115 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 116 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 117 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 117 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 117 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 118 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 119 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 120 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 120 | `WordPress.WP.AlternativeFunctions.file_system_operations_fclose` | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 121 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Exports/CsvExporter.php | 122 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Shortcodes/AttendanceManager.php | 26 | `Generic.CodeAnalysis.UnusedFunctionParameter.Found` | The method parameter $atts is never used |
| /workspace/FoodBank-Manager/includes/Shortcodes/AttendanceManager.php | 29 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_checkin" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Shortcodes/AttendanceManager.php | 30 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 152 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Entries.php | 26 | `Generic.CodeAnalysis.UnusedFunctionParameter.Found` | The method parameter $atts is never used |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 41 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 41 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 42 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 46 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 186 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 50 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 51 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 51 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['fbm_err'] |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 60 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 67 | `WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents` | file_get_contents() is discouraged. Use wp_remote_get() for remote URLs instead. |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 79 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 164 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 117 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 182 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 136 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 183 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 155 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 191 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 173 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 147 characters |
| /workspace/FoodBank-Manager/includes/Shortcodes/Form.php | 189 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 197 characters |
| /workspace/FoodBank-Manager/includes/Core/Options.php | 258 | `WordPress.WP.AlternativeFunctions.strip_tags_strip_tags` | strip_tags() is discouraged. Use the more comprehensive wp_strip_all_tags() instead. |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 11 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Plugin |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 16 | `Squiz.Commenting.VariableComment.Missing` | Missing member variable doc comment |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 18 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function get_instance() |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 22 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 22 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function init() |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 25 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 27 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 27 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function boot() |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 29 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 30 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 32 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 5 tabs, found 6 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 37 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 39 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 39 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 39 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 39 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 39 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 40 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 40 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 40 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 40 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 40 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 41 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 41 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 41 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 41 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 41 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 42 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 42 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 42 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 42 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 42 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 43 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 43 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 43 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 43 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 43 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 44 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 44 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 44 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 44 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 44 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 45 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 45 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 45 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 45 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 45 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 47 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 28 spaces but found 32 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 48 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 28 spaces but found 32 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 49 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 7 tabs, found 10 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 51 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 7 tabs, found 10 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 52 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 6 tabs, found 8 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 54 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 57 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 59 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 60 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 60 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 61 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 62 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 62 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 6 tabs, found 8 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 64 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 6 tabs, found 8 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 65 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 6 tabs, found 8 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 66 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 67 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 67 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 44 spaces but found 48 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 68 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 44 spaces but found 48 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 69 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 69 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 242 characters |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 70 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 70 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 10 tabs, found 12 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 71 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 72 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 72 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 6 tabs, found 8 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 73 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 73 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 73 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 74 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 74 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 6 tabs, found 8 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 75 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 76 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 76 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 44 spaces but found 48 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 77 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 44 spaces but found 48 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 78 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 78 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 201 characters |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 79 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 79 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 10 tabs, found 12 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 80 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 81 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 81 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 6 tabs, found 8 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 82 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 82 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 82 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 83 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 6 tabs, found 8 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 84 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 84 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 3 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 84 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 84 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 85 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 85 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 85 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 86 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 86 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 7 tabs, found 10 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 87 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 88 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 88 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 89 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 89 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 90 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 90 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 207 characters |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 91 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 91 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 11 tabs, found 14 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 92 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 93 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 93 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 7 tabs, found 10 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 94 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 94 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 6 tabs, found 8 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 95 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 95 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 5 tabs, found 6 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 96 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 98 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 99 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 99 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 101 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 101 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 101 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function activate() |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 102 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 103 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 104 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 104 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Core/Plugin.php | 106 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function deactivate() |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 14 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Hooks |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 16 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function register() |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 23 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function register_shortcodes() |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Core/Hooks.php | 27 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Assets |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 9 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function register() |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 14 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function enqueue_front() |
| /workspace/FoodBank-Manager/includes/Core/Assets.php | 18 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function enqueue_admin() |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Logger |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 9 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function init() |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 14 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function log_succeeded() |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 18 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function log_failed() |
| /workspace/FoodBank-Manager/includes/Mail/Logger.php | 22 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function insert_log() |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 9 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class ApplicationsRepo |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 10 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 10 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$args" missing |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 11 | `Squiz.Commenting.FunctionComment.MissingParamName` | Missing parameter name |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 83 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 84 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 84 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 11 spaces but found 19 spaces |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 84 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $prepared |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 87 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $count_sql |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 95 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 95 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$id" missing |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 100 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 9 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 101 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 101 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 105 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 5 spaces but found 4 spaces |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 106 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 106 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 1 space but found 8 spaces |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 106 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $files_sql |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 107 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 111 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function softDelete() |
| /workspace/FoodBank-Manager/includes/Database/ApplicationsRepo.php | 114 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Helpers |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 9 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function verify_nonce() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 10 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_REQUEST[$name] |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 14 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function require_nonce() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 16 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 144 characters |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 20 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function sanitize_text() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 24 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 24 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function esc_html() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 26 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 28 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 28 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function mask_email() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 30 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 32 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 34 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 36 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 39 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 41 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 41 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function mask_postcode() |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 44 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 46 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Helpers.php | 50 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 7 | `Generic.CodeAnalysis.EmptyStatement.DetectedIf` | Empty IF statement detected |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 8 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 8 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 11 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Crypto |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 15 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function get_kek() |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 21 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode` | base64_decode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 29 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 29 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function ensure_sodium() |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 30 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 31 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 3 tabs, found 6 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 33 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 36 spaces but found 40 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 34 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 36 spaces but found 40 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 35 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 199 characters |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 36 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 7 tabs, found 10 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 38 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 3 tabs, found 6 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 40 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 41 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 43 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 43 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function encryptSensitive() |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 45 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 47 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 54 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | base64_encode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 55 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 55 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 57 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function decryptSensitive() |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 62 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 62 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode` | base64_decode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 63 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 65 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 66 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 67 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 69 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 70 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 16 spaces but found 8 spaces |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 71 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 17 spaces but found 9 spaces |
| /workspace/FoodBank-Manager/includes/Security/Crypto.php | 72 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 20 spaces but found 12 spaces |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 203 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $first_name is never used |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 212 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | base64_encode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 235 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $first is never used |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 235 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $last is never used |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 235 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $email is never used |
| /workspace/FoodBank-Manager/includes/Rest/Api.php | 235 | `Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed` | The method parameter $postcode is never used |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 10 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class AttendanceRepo |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 11 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 11 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$application_id" missing |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 12 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 14 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 14 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 17 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 150 characters |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 20 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 20 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 20 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 23 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 24 | `Squiz.Commenting.FunctionComment.ParamCommentFullStop` | Parameter comment must end with a full stop |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 43 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 43 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 48 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 49 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 9 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 50 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 9 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 51 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 51 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceAfterOpen` | Expected 1 space after open parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 51 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceBeforeClose` | Expected 1 space before close parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 53 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 53 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 53 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 53 | `Squiz.Strings.DoubleQuoteUsage.NotRequired` | String "1=1" does not require double quotes; use single quotes instead |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 53 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 54 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 55 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 55 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 55 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 55 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 55 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 57 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 59 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 59 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.ExtraSpaceAfterCloseParenthesis` | Expected exactly one space between closing parenthesis and opening control structure; "  " found. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Squiz.ControlStructures.ControlSignature.SpaceAfterCloseParenthesis` | Expected 1 space after closing parenthesis; found 2 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Squiz.Strings.DoubleQuoteUsage.NotRequired` | String "a.form_id = %d" does not require double quotes; use single quotes instead |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 60 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `Squiz.Strings.DoubleQuoteUsage.NotRequired` | String "t.event_id = %d" does not require double quotes; use single quotes instead |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 61 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 62 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 63 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 6 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 63 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 63 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 63 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 63 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 63 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 63 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 65 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 65 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 65 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 65 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 66 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 67 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 68 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 6 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 68 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 68 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 68 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 68 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 68 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 68 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 69 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 70 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 70 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 70 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 70 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 70 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 71 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `Squiz.Strings.DoubleQuoteUsage.NotRequired` | String "t.recorded_by_user_id = %d" does not require double quotes; use single quotes instead |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 72 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 74 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 75 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 76 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 77 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 77 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 78 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 78 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 78 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 78 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 80 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 80 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 81 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 81 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 81 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 81 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 81 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 81 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 82 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 83 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 83 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 85 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 85 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 85 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 85 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceAfterOpen` | Expected 1 space after open parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 85 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceBeforeClose` | Expected 1 space before close parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 85 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 86 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 86 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 86 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 86 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceAfterOpen` | Expected 1 space after open parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 86 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceBeforeClose` | Expected 1 space before close parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 86 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 87 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 87 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceAfterOpen` | Expected 1 space after open parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 87 | `Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceBeforeClose` | Expected 1 space before close parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 89 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 89 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 90 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 116 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 116 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 117 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 118 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 118 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 137 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 137 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 137 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 137 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 138 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 139 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 139 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 145 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 145 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 146 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 148 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 148 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 149 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 149 | `WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine` | Each item in a multi-line array must be on a new line. Found: 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 149 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 150 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 150 | `WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine` | Each item in a multi-line array must be on a new line. Found: 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 150 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 151 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 151 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 152 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 153 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 155 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 155 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 156 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 156 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 156 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 156 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 156 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 156 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 157 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 157 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 157 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 159 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 159 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 159 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 159 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 160 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 160 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 160 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $prepared |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 160 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 160 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 160 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 162 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 162 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 162 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $countSql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 162 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 163 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 163 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 163 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 163 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $preparedCount |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 163 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 165 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 165 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 165 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 165 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 165 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 166 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 168 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 168 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function timeline() |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 168 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 168 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpaceBeforeEquals` | Incorrect spacing between argument "$includeVoided" and equals sign; expected 1 but found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 168 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpaceAfterEquals` | Incorrect spacing between default value and equals sign for argument "$includeVoided"; expected 1 but found 0 |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 168 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 169 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 170 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 170 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 4 spaces but found 3 spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 171 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 171 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 172 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 172 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 172 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 172 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 172 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 173 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 173 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 173 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 173 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 174 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 174 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 174 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 174 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 174 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 175 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 175 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 175 | `WordPress.WhiteSpace.OperatorSpacing.SpacingBefore` | Expected 1 space before "!=="; 3 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 175 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 175 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 175 | `Generic.Formatting.DisallowMultipleStatements.SameLine` | Each PHP statement must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 176 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 176 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 176 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 176 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` | Expected 1 space after "!"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 176 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 176 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 177 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 177 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 177 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 178 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 178 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 6 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 178 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 216 characters |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 5 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 179 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 180 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 180 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 180 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 180 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 180 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 180 | `Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace` | Newline required after opening brace |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 180 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 181 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 181 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 6 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 181 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 181 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 181 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 181 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 7 spaces but found 2 spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 182 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 183 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 183 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $noteSql |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 184 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 185 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 185 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 185 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 186 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 186 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 186 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 187 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 187 | `WordPress.Arrays.ArrayKeySpacingRestrictions.NoSpacesAroundArrayKeys` | Array keys must be surrounded by spaces unless they contain a string or an integer. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 187 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 187 | `WordPress.WhiteSpace.CastStructureSpacing.NoSpaceBeforeOpenParenthesis` | Expected a space before the type cast open parenthesis; none found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 188 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 189 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 189 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 189 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 190 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 190 | `WordPress.Arrays.ArrayKeySpacingRestrictions.NoSpacesAroundArrayKeys` | Array keys must be surrounded by spaces unless they contain a string or an integer. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 190 | `Generic.Formatting.SpaceAfterCast.NoSpace` | Expected 1 space after cast statement; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 190 | `WordPress.WhiteSpace.CastStructureSpacing.NoSpaceBeforeOpenParenthesis` | Expected a space before the type cast open parenthesis; none found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 190 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 191 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 192 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 193 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 195 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 195 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function setVoid() |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 195 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 195 | `Universal.NamingConventions.NoReservedKeywordParameterNames.voidFound` | It is recommended not to use reserved keyword "void" as function parameter name. Found: $void |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 195 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 196 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 197 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 197 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 197 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 198 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 198 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 199 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 199 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 9 space(s) between "'is_void'" and double arrow, but found 8. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 200 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 200 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'void_reason'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 201 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 201 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 1 space(s) between "'void_by_user_id'" and double arrow, but found 0. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 201 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "=>"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 202 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 202 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 9 space(s) between "'void_at'" and double arrow, but found 8. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 203 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 204 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 205 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 205 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 206 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 206 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 9 space(s) between "'is_void'" and double arrow, but found 8. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 207 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 207 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'void_reason'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 208 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 208 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 1 space(s) between "'void_by_user_id'" and double arrow, but found 0. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 208 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore` | Expected 1 space before "=>"; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 209 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 209 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 9 space(s) between "'void_at'" and double arrow, but found 8. |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 210 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 211 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 212 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 213 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 214 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 215 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 215 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 215 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 215 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 216 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 216 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 216 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 216 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 217 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 217 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 217 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 217 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 218 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 219 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 220 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 222 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 222 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function addNote() |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 222 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 222 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 223 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 224 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 225 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 226 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 226 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 227 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 228 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 229 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 230 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 231 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 232 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 232 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 232 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 232 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 233 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 234 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 235 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/AttendanceRepo.php | 236 | `PSR2.Files.EndFileNewline.TooMany` | Expected 1 blank line at end of file; 2 found |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class TokenService |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 22 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | base64_encode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 25 | `Squiz.Commenting.FunctionComment.MissingParamTag` | Doc comment for parameter "$max_age" missing |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 31 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 36 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode` | base64_decode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 45 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 47 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 48 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 50 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/TokenService.php | 52 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Policy |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 8 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 9 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 10 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 11 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 12 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 15 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 15 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 16 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 16 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 19 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 4 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 19 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 19 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 20 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 20 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 21 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 21 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Attendance/Policy.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Menu |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 8 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 8 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function register() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 9 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 9 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 9 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 9 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 9 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 9 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 10 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 12 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 12 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function addMenu() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 13 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 13 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_read_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 13 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 16 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 16 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 17 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 17 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 20 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 20 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 20 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 3 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 3 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 25 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 162 characters |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 5 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 5 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 26 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 160 characters |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 8 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 8 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 27 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 28 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 28 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 7 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 28 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 28 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 28 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 28 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 28 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 28 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 5 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 5 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 29 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 30 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 30 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 8 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 30 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 30 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 30 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 30 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 30 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 30 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 31 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 180 characters |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 2 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma` | Expected 1 space after comma in argument list; 2 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `Universal.Arrays.DisallowShortArraySyntax.Found` | Short array syntax is not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 32 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 35 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function submenu() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 35 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 35 | `Universal.NamingConventions.NoReservedKeywordParameterNames.parentFound` | It is recommended not to use reserved keyword "parent" as function parameter name. Found: $parent |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 35 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 36 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 36 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 39 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function safeInclude() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 39 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterOpen` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 39 | `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBeforeClose` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 41 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` | No space after opening parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 41 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 41 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 41 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` | No space before closing parenthesis is prohibited |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 44 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 44 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 45 | `Universal.WhiteSpace.PrecisionAlignment.Found` | Found precision alignment of 3 spaces. |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 45 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 45 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 45 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 45 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 49 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderDashboard() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 49 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 2 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 49 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 49 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 49 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 50 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderAttendance() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 50 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 50 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 50 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 51 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderDatabase() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 51 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 3 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 51 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 52 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderForms() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 52 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 6 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 52 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 52 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 52 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 53 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderEmails() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 53 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 5 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 53 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 53 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 53 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 54 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderSettings() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 54 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 3 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 54 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 54 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 54 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 55 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 55 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderTheme() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 55 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 6 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 55 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 55 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 55 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 56 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderDiagnostics() |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 56 | `Generic.Functions.OpeningFunctionBraceKernighanRitchie.SpaceBeforeBrace` | Expected 1 space before opening brace; found 0 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 56 | `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` | Opening brace must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 56 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 56 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 13 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class AttendancePage |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 14 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function route() |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 15 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 15 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_view" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 15 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 15 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` | Expected 1 spaces after opening parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 15 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` | Expected 1 spaces before closing parenthesis; 0 found |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 16 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceAfterArrayOpenerSingleLine` | Expected 1 space after the array opener in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 16 | `NormalizedArrays.Arrays.ArrayBraceSpacing.SpaceBeforeArrayCloserSingleLine` | Expected 1 space before the array closer in a single line array. Found: no spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 19 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 19 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 28 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function parseFilters() |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 29 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 29 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 29 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['preset'] |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 29 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 184 characters |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 33 | `Generic.Formatting.MultipleStatementAlignment.IncorrectWarning` | Equals sign not aligned correctly; expected 1 space but found 4 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 33 | `WordPress.DateTime.CurrentTimeTimestamp.RequestedUTC` | Don't use current_time() for retrieving a Unix (UTC) timestamp. Use time() instead. Found: current_time( 'timestamp', true ) |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 33 | `Squiz.Commenting.InlineComment.InvalidEndChar` | Inline comments must end in full-stops, exclamation marks, or question marks |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 45 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 45 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 45 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['range_from'] |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 46 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 46 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 46 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['range_to'] |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 57 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 59 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 60 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 62 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 63 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 67 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 67 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 68 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 69 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 70 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 70 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 71 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 71 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 71 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_GET['status'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 71 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['status'] |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 72 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 73 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 73 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 74 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 74 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 74 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_GET['type'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 74 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['type'] |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 75 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 76 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 76 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 77 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 78 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 79 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 79 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 80 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 81 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 82 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 82 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 84 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 86 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 86 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 86 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 86 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 86 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 86 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 146 characters |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 88 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 89 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 90 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 90 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 91 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 91 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 92 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 93 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 94 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 95 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 96 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 98 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 98 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 9 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 99 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 99 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 99 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 99 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['orderby'] |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 100 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 101 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 102 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 103 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 103 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 103 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 103 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_GET['order'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 103 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_GET['order'] |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 105 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 106 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 108 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 108 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function decorateRows() |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 109 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 110 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 110 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 111 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 112 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 113 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 114 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 114 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 10 spaces but found 9 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 115 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 115 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 5 spaces but found 4 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 115 | `WordPress.DB.PreparedSQL.NotPrepared` | Use placeholders and $wpdb->prepare(); found $sql |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 116 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 117 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 118 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 119 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 121 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 122 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 123 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 124 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 124 | `Universal.Operators.DisallowShortTernary.Found` | Using short ternaries is not allowed as they are rarely used correctly |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 125 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 126 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 127 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 128 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 129 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 130 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 131 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 132 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 133 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 134 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 135 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 136 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 137 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 138 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 139 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 140 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 141 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 142 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 143 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 144 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 145 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 147 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 147 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function handleExport() |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 148 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 148 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_export" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 149 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 150 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 151 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 152 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 152 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 13 spaces but found 14 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 153 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 153 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 5 spaces but found 6 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 154 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 154 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 1 space but found 2 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 155 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 155 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 16 spaces but found 17 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 156 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 156 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 7 spaces but found 8 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 156 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "read_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 157 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 157 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 16 spaces but found 17 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 157 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 158 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 158 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 16 spaces but found 17 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 159 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 160 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 161 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 163 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 163 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function renderList() |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 164 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 164 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 8 spaces but found 7 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 165 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 165 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 11 spaces but found 10 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 166 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 166 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 11 spaces but found 10 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 167 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 167 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 10 spaces but found 9 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 168 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 168 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 11 spaces but found 10 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 169 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 169 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 7 spaces but found 6 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 170 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 170 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 9 spaces but found 8 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 171 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 171 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 5 spaces but found 4 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 172 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 172 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 7 spaces but found 6 spaces |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 173 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 174 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 174 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 174 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "read_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 175 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 176 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/AttendancePage.php | 177 | `PSR2.Files.EndFileNewline.TooMany` | Expected 1 blank line at end of file; 2 found |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 25 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 40 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 45 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['fbm_settings'] |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 20 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 21 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 23 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 2 tabs, found 4 |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 25 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 26 | `PEAR.Functions.FunctionCallSignature.Indent` | Multi-line function call not indented correctly; expected 20 spaces but found 24 |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 27 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 181 characters |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 28 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 5 tabs, found 6 |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/Notices.php | 30 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 25 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 40 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 57 | `WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents` | file_get_contents() is discouraged. Use wp_remote_get() for remote URLs instead. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 72 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['fbm_theme'] |
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
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 46 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 143 characters |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 47 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 47 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 48 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 48 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 49 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 49 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 49 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 49 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 49 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 151 characters |
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
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 12 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class PermissionsPage |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 13 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 13 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function route() |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 14 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 14 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_permissions" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 17 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 17 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 19 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 23 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 30 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 33 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '(string)'. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 35 | `WordPress.Security.ValidatedSanitizedInput.InputNotValidated` | Detected usage of a possibly undefined superglobal array index: $_SERVER['REQUEST_METHOD']. Use isset() or empty() to check the index exists before using it |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 37 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 37 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 37 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 37 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['role_caps'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 37 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['role_caps'] |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 43 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 55 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 57 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 58 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 59 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 60 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 60 | `WordPress.Security.ValidatedSanitizedInput.InputNotValidated` | Detected usage of a possibly undefined superglobal array index: $_SERVER['REQUEST_METHOD']. Use isset() or empty() to check the index exists before using it |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 62 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 62 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 62 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 62 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 62 | `WordPress.Security.ValidatedSanitizedInput.MissingUnslash` | $_POST['user_caps'] not unslashed before sanitization. Use wp_unslash() or similar |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 62 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['user_caps'] |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 66 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 67 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 69 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 70 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 71 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 72 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 73 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 74 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 75 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 76 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 78 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 79 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 80 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 81 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 81 | `WordPress.Security.ValidatedSanitizedInput.InputNotValidated` | Detected usage of a possibly undefined superglobal array index: $_SERVER['REQUEST_METHOD']. Use isset() or empty() to check the index exists before using it |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 82 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 83 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 84 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 84 | `WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents` | file_get_contents() is discouraged. Use wp_remote_get() for remote URLs instead. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 84 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 84 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_FILES['import_json']['tmp_name'] |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 85 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 86 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 87 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 88 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 89 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 90 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 91 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 92 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 93 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 94 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 95 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 96 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 97 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 98 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 99 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 100 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 101 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 102 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 103 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 104 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 105 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 106 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 107 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 108 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 109 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 110 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 111 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 112 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 113 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 114 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 115 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 116 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 117 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 118 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 119 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 119 | `WordPress.Security.ValidatedSanitizedInput.InputNotValidated` | Detected usage of a possibly undefined superglobal array index: $_SERVER['REQUEST_METHOD']. Use isset() or empty() to check the index exists before using it |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 120 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 121 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 122 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 122 | `WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound` | When a multi-item array uses associative keys, each value should start on a new line. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 123 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 124 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 125 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 126 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 127 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 128 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 130 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 131 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 132 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 133 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 134 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 134 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 134 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 135 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 135 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 135 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 136 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 137 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 138 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 138 | `PEAR.Functions.FunctionCallSignature.ContentAfterOpenBracket` | Opening parenthesis of a multi-line function call must be the last content on the line |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 139 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 140 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 141 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 142 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 143 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 144 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 144 | `PEAR.Functions.FunctionCallSignature.CloseBracketLine` | Closing parenthesis of a multi-line function call must be on a line by itself |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 145 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 147 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 148 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 150 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 150 | `Generic.Commenting.DocComment.MissingShort` | Missing short description in doc comment |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 151 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 152 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 153 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 154 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 155 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 155 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 7 space(s) between "'fb_read_entries'" and double arrow, but found 6. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 156 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 156 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 7 space(s) between "'fb_edit_entries'" and double arrow, but found 6. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 157 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 157 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'fb_delete_entries'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 158 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 158 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'fb_export_entries'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 159 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 159 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 7 space(s) between "'fb_manage_forms'" and double arrow, but found 6. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 160 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 160 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 4 space(s) between "'fb_manage_settings'" and double arrow, but found 3. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 161 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 161 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 6 space(s) between "'fb_manage_emails'" and double arrow, but found 5. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 162 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 162 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 2 space(s) between "'fb_manage_encryption'" and double arrow, but found 1. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 163 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 163 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 4 space(s) between "'attendance_checkin'" and double arrow, but found 3. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 164 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 164 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 7 space(s) between "'attendance_view'" and double arrow, but found 6. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 165 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 165 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 5 space(s) between "'attendance_export'" and double arrow, but found 4. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 166 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 166 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 6 space(s) between "'attendance_admin'" and double arrow, but found 5. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 167 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 167 | `WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned` | Array double arrow not aligned correctly; expected 8 space(s) between "'read_sensitive'" and double arrow, but found 7. |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 168 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 169 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/PermissionsPage.php | 170 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 26 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_emails" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 30 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 30 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 46 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_emails" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 60 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['templates'] |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 78 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 84 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 103 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 104 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 7 | `Squiz.Commenting.ClassComment.Missing` | Missing doc comment for class Migrations |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 9 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 2 spaces but found 1 space |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 10 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 10 | `Generic.WhiteSpace.ScopeIndent.IncorrectExact` | Line indented incorrectly; expected 1 tabs, found 2 |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 10 | `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` | Equals sign not aligned with surrounding assignments; expected 1 space but found 4 spaces |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 12 | `Squiz.Commenting.FunctionComment.Missing` | Missing doc comment for function maybe_migrate() |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 70 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Db/Migrations.php | 80 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/emails/admin-notification.php | 25 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$safe_summary'. |
| /workspace/FoodBank-Manager/templates/emails/applicant-confirmation.php | 38 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$safe_summary'. |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 11 | `Generic.WhiteSpace.ScopeIndent.Incorrect` | Line indented incorrectly; expected at least 1 tabs, found 0 |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 15 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 16 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 16 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 162 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 17 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 18 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 19 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 20 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 26 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 26 | `WordPress.WP.I18n.LowLevelTranslationFunction` | Use of the "translate()" function is reserved for low-level API usage. |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 26 | `WordPress.WP.I18n.NonSingularStringLiteralText` | The $text parameter must be a single text string literal. Found: ucfirst( str_replace( '_', ' ', $t ) ) |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 26 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 177 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 27 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 31 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 167 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 32 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 32 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 153 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 33 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 37 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 170 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 38 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 39 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 39 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 146 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 44 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 55 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 57 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 58 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 59 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 60 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 62 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 64 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 183 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 66 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 66 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 353 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 67 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 69 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 70 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 71 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 72 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 73 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 74 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 75 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 76 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 76 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 198 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 78 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 79 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 79 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 289 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 80 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 81 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 82 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 83 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 216 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 84 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 85 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 86 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 86 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 219 characters |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 87 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 88 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 89 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 90 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 91 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 92 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 93 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 94 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 95 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 96 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 97 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 98 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 99 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 100 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/public/attendance-manager.php | 101 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/database.php | 117 | `Generic.WhiteSpace.ScopeIndent.Incorrect` | Line indented incorrectly; expected at least 2 tabs, found 0 |
| /workspace/FoodBank-Manager/templates/admin/database.php | 118 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_export_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/database.php | 128 | `Generic.WhiteSpace.ScopeIndent.Incorrect` | Line indented incorrectly; expected at least 2 tabs, found 0 |
| /workspace/FoodBank-Manager/templates/admin/database.php | 129 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_delete_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/database.php | 174 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_export_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/diagnostics.php | 16 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/dashboard.php | 17 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_read_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 35 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 192 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 39 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 201 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 48 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 49 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 50 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 50 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 51 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 51 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 52 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 52 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 52 spaces but found 56 |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 53 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 54 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 55 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 56 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 57 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 57 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 217 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 58 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 59 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 60 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 64 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 212 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 68 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 206 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 72 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 167 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 75 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 141 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 76 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 215 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 80 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 202 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 83 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 142 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 102 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 197 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 106 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 219 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 111 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 112 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 112 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 205 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 113 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 113 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 199 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 114 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 115 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 115 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 204 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 116 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 117 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 122 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 293 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 123 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 299 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 124 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 289 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 125 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 328 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 130 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 311 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 131 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 326 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 136 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 330 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 139 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 139 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 204 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 140 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 140 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 198 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 141 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 141 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 198 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 148 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 189 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 149 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 149 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 339 characters |
| /workspace/FoodBank-Manager/templates/admin/settings.php | 158 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 328 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 21 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 21 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 22 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 22 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 23 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 23 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 24 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 24 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 25 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 25 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 28 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 28 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 29 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 30 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 30 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 31 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 31 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 34 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 34 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 35 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 35 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 36 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 36 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 37 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 37 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 40 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 40 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 41 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 41 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 42 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 42 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 45 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 45 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 46 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 46 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 47 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 47 | `WordPress.Arrays.ArrayIndentation.ItemNotAligned` | Array item not aligned correctly; expected 4 spaces but found 8 |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 61 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 62 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 63 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 63 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 155 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 64 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 65 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 66 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 202 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 67 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 233 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 68 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 69 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 70 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 70 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 189 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 71 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 72 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 73 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 74 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 75 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 75 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 160 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 76 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 77 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 225 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 78 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 79 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 80 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 80 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 156 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 81 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 82 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 84 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 85 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 85 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 158 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 86 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 87 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 88 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 230 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 93 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 94 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 95 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 95 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 155 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 96 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 97 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 98 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 199 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 99 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 230 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 100 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 101 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 102 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 102 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 189 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 103 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 104 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 105 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 106 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 107 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 107 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 160 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 108 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 109 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 109 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 222 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 110 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 111 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 112 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 112 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 156 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 113 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 114 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 115 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 116 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 117 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 117 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 158 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 118 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 119 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 120 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 227 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 132 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 205 characters |
| /workspace/FoodBank-Manager/templates/admin/theme.php | 138 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 328 characters |
| /workspace/FoodBank-Manager/templates/admin/database-view.php | 82 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_export_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/database-view.php | 90 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_delete_entries" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/forms.php | 17 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_forms" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 23 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 173 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 24 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 167 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 25 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 212 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 58 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 223 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 59 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 187 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 60 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 174 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 80 | `WordPress.WP.GlobalVariablesOverride.Prohibited` | Overriding WordPress globals is prohibited. Found assignment to $order |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 90 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$url'. |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 109 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 166 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 110 | `Generic.Files.LineLength.MaxExceeded` | Line exceeds maximum limit of 200 characters; contains 202 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 149 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_export" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 159 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 164 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 186 characters |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 173 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "attendance_admin" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/templates/admin/attendance.php | 205 | `Generic.Files.LineLength.TooLong` | Line exceeds 140 characters; contains 164 characters |
