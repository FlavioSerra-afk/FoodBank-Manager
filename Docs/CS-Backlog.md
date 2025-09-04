Docs-Revision: 2025-09-04 (Wave v1.1.6 – Attendance P1)
Strict (cleaned):

includes/Admin/EmailsPage.php
includes/Admin/ThemePage.php
includes/Admin/Menu.php
includes/Admin/PermissionsPage.php
includes/Admin/DatabasePage.php
includes/Exports/CsvExporter.php
includes/Attendance/AttendanceRepo.php

Wave focus:
- Promote `includes/Admin/PermissionsPage.php` to Strict.
- Phase 2: add visual form builder, uploads, and CAPTCHA options.
- Attendance P1: admin QR check-in helper and override reason UI.

Legacy (temporary ignoreFile to unblock ZIP):

includes/Database/ApplicationsRepo.php
includes/Admin/AttendancePage.php
includes/UI/Theme.php
includes/Mail/Templates.php
includes/Http/FormSubmitController.php
includes/Core/Plugin.php
includes/Core/Hooks.php
includes/Core/Options.php
includes/Auth/Capabilities.php
includes/Auth/CapabilitiesResolver.php
includes/Auth/Roles.php
includes/Rest/Api.php
includes/Rest/AttendanceController.php
includes/Shortcodes/AttendanceManager.php
includes/Shortcodes/Entries.php
includes/Shortcodes/Form.php
templates/admin/permissions.php

Temporary ignores present in:

includes/Database/ApplicationsRepo.php
includes/Admin/AttendancePage.php
includes/UI/Theme.php
includes/Mail/Templates.php
includes/Core/Plugin.php
includes/Core/Hooks.php
includes/Core/Options.php
includes/Auth/Capabilities.php
includes/Auth/CapabilitiesResolver.php
includes/Auth/Roles.php
includes/Rest/Api.php
includes/Rest/AttendanceController.php
includes/Shortcodes/AttendanceManager.php
includes/Shortcodes/Entries.php
includes/Shortcodes/Form.php
templates/admin/permissions.php

Plan:
we’ll remove one file per wave by:

replacing ignoreFile with real fixes (escaping, nonces, prepared SQL, docblocks),

keeping only surgical one-line // phpcs:ignore … where wp_kses_post() or strict IN (…) placeholders trigger false positives.

Next cleanup targets:
- includes/Admin/PermissionsPage.php
