# PHPCS Ignores & Suppressed Issues Dashboard

_Generated: 2025-09-03 11:06:46Z

This report shows which issues are currently suppressed via `phpcs:ignore` and what would fail if annotations were disabled.

## Snapshot

- Suppressed issues (from `--ignore-annotations` run): **59**
- Ignore annotations present: **63** lines

## Top sniffs by count

| Sniff | Count | Recipe |
|---|---:|---|
| `WordPress.Security.NonceVerification.Recommended` | 15 | - **Fix:** For POST/mutations add `wp_nonce_field()` + `check_admin_referer()`. For read-only GET filters sanitize and keep a one-line justified ignore. |
| `WordPress.WP.Capabilities.Unknown` | 12 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Files.FileName.NotHyphenatedLowercase` | 6 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Files.FileName.InvalidClassFileName` | 6 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Squiz.Commenting.FileComment.Missing` | 6 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | 4 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Security.NonceVerification.Missing` | 3 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | 3 | - **Fix:** Wrap `$_GET/$_POST` with `wp_unslash()` + `sanitize_text_field()` / `absint()` / whitelist enums. |
| `Generic.Files.LineLength.TooLong` | 1 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.PHP.NoSilencedErrors.Discouraged` | 1 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | 1 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |
| `WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents` | 1 | - **Fix:** Apply appropriate sanitization/escaping or add targeted justification. |

## Top files by suppressed issues

| File | Count |
|---|---:|
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 21 |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 15 |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 7 |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 7 |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 6 |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 3 |

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
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 1 | `WordPress.Files.FileName.NotHyphenatedLowercase` | Filenames should be all lowercase with hyphens as word separators. Expected formsubmitcontroller.php, but found FormSubmitController.php. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 1 | `WordPress.Files.FileName.InvalidClassFileName` | Class file names should be based on the class name with "class-" prepended. Expected class-formsubmitcontroller.php, but found FormSubmitController.php. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 71 | `Generic.Files.LineLength.TooLong` | Line exceeds 160 characters; contains 173 characters |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 129 | `WordPress.PHP.NoSilencedErrors.Discouraged` | Silencing errors is strongly discouraged. Use proper error checking instead. Found: @inet_pton( $ip ... |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 191 | `WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode` | base64_encode() can be used to obfuscate code which is strongly discouraged. Please verify that the function is used for benign reasons. |
| /workspace/FoodBank-Manager/includes/Http/FormSubmitController.php | 243 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 1 | `WordPress.Files.FileName.NotHyphenatedLowercase` | Filenames should be all lowercase with hyphens as word separators. Expected databasepage.php, but found DatabasePage.php. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 1 | `WordPress.Files.FileName.InvalidClassFileName` | Class file names should be based on the class name with "class-" prepended. Expected class-databasepage.php, but found DatabasePage.php. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 30 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_database" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 35 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 35 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 46 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 48 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 48 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 48 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 55 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 55 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 63 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 63 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 65 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 65 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 93 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_view_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 119 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_view_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 133 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_database" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 159 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_database" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/DatabasePage.php | 181 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_view_sensitive" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 1 | `WordPress.Files.FileName.NotHyphenatedLowercase` | Filenames should be all lowercase with hyphens as word separators. Expected settingspage.php, but found SettingsPage.php. |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 1 | `WordPress.Files.FileName.InvalidClassFileName` | Class file names should be based on the class name with "class-" prepended. Expected class-settingspage.php, but found SettingsPage.php. |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 25 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 40 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/SettingsPage.php | 45 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['fbm_settings'] |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 1 | `WordPress.Files.FileName.NotHyphenatedLowercase` | Filenames should be all lowercase with hyphens as word separators. Expected emailspage.php, but found EmailsPage.php. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 1 | `WordPress.Files.FileName.InvalidClassFileName` | Class file names should be based on the class name with "class-" prepended. Expected class-emailspage.php, but found EmailsPage.php. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 26 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 29 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 30 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 30 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 46 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_settings" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 60 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['templates'] |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 77 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 78 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 83 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 84 | `WordPress.Security.NonceVerification.Missing` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 103 | `Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed` | Tabs must be used to indent lines; spaces are not allowed |
| /workspace/FoodBank-Manager/includes/Admin/EmailsPage.php | 104 | `WordPress.Security.NonceVerification.Recommended` | Processing form data without nonce verification. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 1 | `WordPress.Files.FileName.NotHyphenatedLowercase` | Filenames should be all lowercase with hyphens as word separators. Expected themepage.php, but found ThemePage.php. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 1 | `WordPress.Files.FileName.InvalidClassFileName` | Class file names should be based on the class name with "class-" prepended. Expected class-themepage.php, but found ThemePage.php. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 25 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_theme" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 40 | `WordPress.WP.Capabilities.Unknown` | Found unknown capability "fb_manage_theme" in function call to current_user_can(). Please check the spelling of the capability. If this is a custom capability, please verify the capability is registered with WordPress via a call to WP_Role(s)->add_cap().\nCustom capabilities can be made known to this sniff by setting the "custom_capabilities" property in the PHPCS ruleset. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 57 | `WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents` | file_get_contents() is discouraged. Use wp_remote_get() for remote URLs instead. |
| /workspace/FoodBank-Manager/includes/Admin/ThemePage.php | 72 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` | Detected usage of a non-sanitized input variable: $_POST['fbm_theme'] |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 1 | `WordPress.Files.FileName.NotHyphenatedLowercase` | Filenames should be all lowercase with hyphens as word separators. Expected menu.php, but found Menu.php. |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 1 | `WordPress.Files.FileName.InvalidClassFileName` | Class file names should be based on the class name with "class-" prepended. Expected class-menu.php, but found Menu.php. |
| /workspace/FoodBank-Manager/includes/Admin/Menu.php | 1 | `Squiz.Commenting.FileComment.Missing` | Missing file doc comment |
