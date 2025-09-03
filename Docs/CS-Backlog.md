Strict (cleaned):

includes/Attendance/AttendanceRepo.php
includes/Database/ApplicationsRepo.php
includes/Http/FormSubmitController.php
includes/Admin/AttendancePage.php
includes/Admin/DatabasePage.php
includes/Admin/PermissionsPage.php
includes/Admin/SettingsPage.php
includes/Admin/EmailsPage.php
includes/Admin/ThemePage.php
includes/Admin/Menu.php
includes/Exports/CsvExporter.php
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

Legacy (temporary ignoreFile to unblock ZIP):

includes/Admin/AttendancePage.php
includes/Admin/EmailsPage.php
includes/Admin/PermissionsPage.php
includes/Admin/SettingsPage.php
includes/Admin/ThemePage.php
includes/Attendance/AttendanceRepo.php
includes/Auth/Roles.php
includes/Core/Options.php
includes/Core/Plugin.php
includes/Database/ApplicationsRepo.php
includes/Exports/CsvExporter.php
includes/Http/FormSubmitController.php
includes/Mail/Templates.php
includes/Rest/Api.php
includes/Rest/AttendanceController.php
includes/Shortcodes/AttendanceManager.php
includes/Shortcodes/Entries.php
includes/Shortcodes/Form.php
includes/UI/Theme.php
templates/admin/permissions.php

Temporary ignores present in:

includes/Admin/AttendancePage.php
includes/Admin/EmailsPage.php
includes/Admin/PermissionsPage.php
includes/Admin/SettingsPage.php
includes/Admin/ThemePage.php
includes/Attendance/AttendanceRepo.php
includes/Auth/Roles.php
includes/Core/Options.php
includes/Core/Plugin.php
includes/Database/ApplicationsRepo.php
includes/Exports/CsvExporter.php
includes/Http/FormSubmitController.php
includes/Mail/Templates.php
includes/Rest/Api.php
includes/Rest/AttendanceController.php
includes/Shortcodes/AttendanceManager.php
includes/Shortcodes/Entries.php
includes/Shortcodes/Form.php
includes/UI/Theme.php
templates/admin/permissions.php

Plan:
we’ll remove one file per wave by:

replacing ignoreFile with real fixes (escaping, nonces, prepared SQL, docblocks),

keeping only surgical one-line // phpcs:ignore … where wp_kses_post() or strict IN (…) placeholders trigger false positives.
