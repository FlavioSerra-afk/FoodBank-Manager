# Changelog

## [1.2.9] - 2025-09-05
### Features
- feat(auth): self-heal Administrator capabilities with diagnostics repair control and menu visibility fallback

## 1.2.8 — 2025-09-05
- chore(release): version bump only (no runtime changes)
- docs: note test stubs (WP helpers), retention config normalizer, notices de-dup diagnostics

## [1.2.7] - 2025-09-05
### Build
- bump version to 1.2.7.

### Test
- add WP helper stubs (absint, add_query_arg, wp_salt, …)

### Fixes
- fix(core): normalize retention config (typed)

### Chore
- chore(cs): exclude dist/, docblocks & strict types

### Docs
- document namespace bridge, bootstrap fallback, notice gating, and PHPCS exclusions; add active issues overview.
- PRD/Architecture/Issues updated; diagnostics surfaces notices count

## [1.2.6] - 2025-09-05
### QA
- test: replace anonymous test doubles with named stubs; add deterministic WP function shims for unit tests.

## [1.2.5] - 2025-09-05
### Fixes
- Deduplicated admin menu registration with canonical slugs and Settings page rendering.
- Screen-gated assets and notices with unified `.fbm-admin` wrappers.
- Diagnostics now checks menu slugs and asset gating.

## [1.2.4] - 2025-09-05
### Docs
- Docs revised for Strict Guard Green (Wave v1.2.4).
### QA
- Promoted PermissionsPage to Strict with nonce checks and sanitized inputs.

## [1.2.3] - 2025-09-04
### Fixes
- fix(admin): de-dup menus under canonical slugs
- fix(core): screen-gated assets/notices via helper
- fix(ui): ensure all admin templates use .fbm-admin wrapper
### QA
- test: deterministic WP stubs; remove anonymous classes
### Docs
- docs: stamp Docs-Revision (Wave v1.2.3)

## [1.2.2] - 2025-09-04
### Features
- Dashboard shortcode now accepts type/event/policy filters and provides a capability-gated summary CSV export.
### Docs
- Docs revised for Frontend Dashboard P3 (Wave v1.2.2).

## [1.2.1] - 2025-09-04
### Features
- Dashboard shortcode now shows trend deltas and daily check-ins sparkline.
### Docs
- Docs revised for Frontend Dashboard P2 (Wave v1.2.1).
### QA
- phpstan:fast memory limit increased to 768M.

## [1.2.0] - 2025-09-04
### Features
- Manager dashboard shortcode with aggregated non-PII cards.
### Docs
- Docs revised for Frontend Dashboard P1 (Wave v1.2.0).

## [1.1.10] - 2025-09-04
### Fixes
- Contained admin pages with `.fbm-admin` wrapper and screen-gated assets/notices.
### Docs
- Docs revised for Admin Layout Guard (Wave v1.1.10).

## [1.1.9] - 2025-09-04
### Features
- Permissions admin page: JSON export/import (Dry Run), per-user overrides table, and Reset.
### Docs
- Docs revised for Permissions UX (Wave v1.1.9).

## [1.1.8] - 2025-09-04
### Features
- Design & Theme settings with primary colour, density, font, dark mode default and optional custom CSS applied via CSS variables.
### Docs
- Docs revised for Design & Theme (Wave v1.1.8).

## [1.1.7] - 2025-09-04
### Features
- Saved Database filter presets and per-user column toggles.
### Docs
- Docs revised for Database UX P1 (Wave v1.1.7).

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

