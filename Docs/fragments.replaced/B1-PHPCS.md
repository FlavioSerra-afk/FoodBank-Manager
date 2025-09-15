## B1 PHPCS

Ensures coding standards and security hardening for forms, shortcodes, assets, and diagnostics modules. Inputs are sanitized, outputs escaped, queries prepared, and nonces verified with no functional changes.

A lane-specific PHPCS check keeps the following files green:

- includes/Http/FormSubmitController.php
- includes/Database/ApplicationsRepo.php
- includes/Mail/LogRepo.php
- includes/Shortcodes/Shortcodes.php
- includes/Shortcodes/FormShortcode.php
- includes/Shortcodes/DashboardShortcode.php
- includes/Core/Assets.php
- includes/Admin/DiagnosticsPage.php
- templates/public/form-success.php
- templates/public/dashboard.php

A follow-up "Strict Guard Green" wave will enforce PHPCS across remaining legacy files.
