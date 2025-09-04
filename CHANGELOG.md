# Changelog

## [1.1.6] - 2025-09-04
### Features
- Admin-only QR check-in links and override reason prompt for Attendance.
### Docs
- Docs revised for Attendance P1 (Wave v1.1.6).

## [1.1.5] - 2025-09-04
### Features
- Read-only Forms Presets Library with shortcode preset support.
### Docs
- Docs revised for Forms Presets P1 (Wave v1.1.5).

## [1.1.4] - 2025-09-04
### Features
- Emails admin page skeleton listing default templates with edit links.
### Docs
- Docs revised for Emails skeleton (Wave v1.1.4).

## [1.1.3] - 2025-09-04
### Features
- Shortcodes builder with masked shortcode generation and nonce-protected live preview.
### Docs
- Docs revised for Shortcodes Builder+Preview (Wave v1.1.3).

## [1.1.2] - 2025-09-04
### Features
- Shortcodes admin page listing available shortcodes, attributes, and examples.
### Docs
- Docs revised for Shortcodes List (Wave v1.1.2).

## [1.1.1] - 2025-09-04
### Features
- Diagnostics Phase 1: environment checks with test email and Repair Caps.
### Docs
- Docs revised for Diagnostics Phase 1 (Wave v1.1.1).

## [1.1.0] - 2025-09-04
### Features
- Settings Phase 1: validated options schema and admin save flow for Branding and Email defaults.
### Docs
- Docs revised for Settings Phase 1 (Wave v1.1.0).

## [1.0.7] - 2025-09-03
### Security/Quality
- Remove PHPCS suppressions from AttendanceRepo; promote to Strict.
### Docs
- CS-Backlog.md revision-stamped for Wave CS-Backlog-11B.2.

## [1.0.6] - 2025-09-03
### Security/Quality
- Harden Database admin page and CSV exports; sanitize filters, whitelist ordering, and mask PII by default. Promoted to Strict.
### Docs
- Architecture.md, README.md, PRD-foodbank-manager.md, and CS-Backlog.md revision-stamped for Wave CS-Backlog-10.

## [1.0.5] - 2025-09-03
### Quality/Security
- Admin Menu hardened; moved to Strict.
### Docs
- Architecture.md, CS-Backlog.md, PHPCS-Ignores.md revision-stamped for Wave CS-Backlog-09.

## [1.0.4] - 2025-09-03
### Security
- Strict guard cleanup (no suppressions in Strict files)
### Build
- PHPStan config modernized (no deprecated options)
### Docs
- CS-Backlog.md and PHPCS-Ignores.md revision-stamped for Wave CS-Backlog-07
- Clarified AttendanceRepo SQL safety, masking defaults, and test coverage across PRD, Architecture, DB_SCHEMA, API, README, CONTRIBUTING, CS-Backlog, PHPCS-Ignores, and ISSUES docs.

## [1.0.3] - 2025-09-04
### Security
- Harden Database admin page and CSV exports with nonces, sanitization and masking; promote to Strict.
### Docs
- PRD-foodbank-manager.md: clarify database export capabilities and masking.
- Architecture.md: note database capabilities and masked exports.
- CS-Backlog.md: move DatabasePage and CsvExporter to Strict.
- PHPCS-Ignores.md: regenerate report.

## [1.0.2] - 2025-09-03
- Harden Permissions page (sanitization, nonces, safe redirects); moved to Strict; no behavior changes.

## [1.0.1] - 2025-09-03
- Harden uninstall script for safe, silent cleanup.
- Resolve PHPCS warnings and fix AttendanceRepo tests.
- Add GitHub Actions for CI and automated releases.

## v0.1.1 — 2025-09-01

