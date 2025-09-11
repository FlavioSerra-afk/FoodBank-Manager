## 1.4.0-rc.7.7 — feat(admin): permissions UI and auto-tag workflow
## 1.5.0

- Theme save fix; Diagnostics mail failures + resend; SMTP/API/KEK health; tests green; lanes=0; stan=0; packaging OK.

## [1.4.2-rc.7.7](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.4.1-rc.7.7...1.4.2-rc.7.7) (2025-09-11)


### Miscellaneous Chores

* **release:** prepare stable v1.5.0 ([e75482f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e75482f075e80020684f791179458470caf8a785))

## [1.4.1-rc.7.7](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.4.0-rc.7.7...1.4.1-rc.7.7) (2025-09-11)


### Miscellaneous Chores

* **ci:** add release checksums ([7156f1b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7156f1b17cb18c257770d4853c1855a221ea2de5))
* **release:** configure release-automation ([b07cbe7](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b07cbe728ccf49bc555cc64fe876af22249be181))
* **release:** configure release-automation ([ba2db9f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ba2db9fecb70ca188111d34474ac1cf5cb30a96c))
* switch release-please to stable 1.5.0 ([526db9b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/526db9bc3bfc83affac3568a5a9bd571cce85310))

## 1.4.0-rc.7.6 — feat(core): centralize capabilities

- feat(core): add canonical capabilities list for reuse across UI and tests

## 1.4.0-rc.7.5 — chore(license): align GPL-2.0-or-later, i18n build, release provenance

- chore(license): align project to GPL-2.0-or-later for mPDF compatibility
- chore(i18n): build .mo catalogs during packaging
- chore(release): add provenance workflow and packaging guard

## 1.4.0-rc.7.2 — fix(pdf): kill closure serialization

- fix(tests): drop process isolation and closures from PDF suites
- fix(tests): stub headers to avoid warnings in shared process
- chore(pdf): bind headers/footers via @page for stability

## 1.4.0-rc.7.1 — fix(pdf): stable receipts and bulk zip

- fix(tests): remove closure serialization in PDF suites
- fix(exports): default-masked receipts gated by capability
- fix(exports): deterministic Bulk PDF ZIP with closed archive
- fix(pdf): letterhead uses @page header/footer for stability

## 1.4.0-rc.7.0 — feat(pdf): renderer, templates and diagnostics preview

- feat(pdf): mPDF-backed renderer with letterhead and receipt templates
- feat(diagnostics): PDF settings panel with preview

## 1.4.0-rc.6.7 — feat(privacy): SAR exporter/eraser and diagnostics panel

- feat(privacy): register WP Privacy exporter/eraser and add Diagnostics → Privacy panel
- chore(cs): raise PHPCS repo memory limit

## 1.4.0-rc.6.6 — chore(cs): tighten lanes and scripts

- chore(cs): tune ruleset (Core + curated Extra), run phpcbf on lanes
- chore(cs): stabilize lanes+repo scripts (summary/source/json; repo ignores on exit)
- docs(cs): update PHPCS-Ignores with new ignores

## 1.4.0-rc.6.3 — chore(cs): scope phpcs lanes and add repo-wide report

- chore(cs): scope phpcs lanes and add repo-wide report (summary+source+json)
- chore(cs): ensure WPCS registered; lanes script prints output reliably
- docs(cs): note lanes policy and repo debt

## 1.4.0-rc.6.2 — fix(diagnostics): retention runner contract

- fix(diagnostics): add retention runner interface and secure admin actions
- docs: note retention controls in diagnostics hub

## 1.4.0-rc.6.0 — fix(cs): clean up email module for PHPCS lanes

- fix(cs): clean up email module for PHPCS lanes
- feat(diagnostics): mail failures + retry, cron telemetry, jobs list
- docs: diagnostics help & SMTP seam (phpmailer_init)

## 1.4.0-rc.4.5.0 — feat(admin/db): dynamic columns, presets, masked detail, visible-columns export

## 1.4.0-rc.4.4.2 — RC4.4.2 — capability and theme save hardening; lanes=0/stan=0/pkg OK.

## 1.4.0-rc.4.4.1 — RC4.4.1 — Forms a11y & validation UX, menu glass parity; lanes=0/stan=0/pkg OK.

## 1.4.0-rc.4.4 — RC4.4 — Admin list-tables + Public forms glass parity; a11y focus/contrast for cells/fields; lanes=0/stan=0/pkg OK.

## 1.4.0-rc.4.3.3 — RC4.3.3 — Menu focus & contrast polish, icon states, perf cap for blur; lanes=0; stan=0; pkg OK.

## 1.4.0-rc.4.3.2 — RC4.3.2 — apply Design & Theme to admin chrome (sidebar/admin-bar) and front-end menus; a11y fallbacks; tests stable; packaging OK.

## 1.4.0-rc.4.3.1 — RC4.3.1 — glass fidelity (layered spec), a11y fallbacks, grid rhythm, focus rings; tests stable.

## 1.4.0-rc.4.3 — RC4.3 — real glass UI (layered cards & buttons), accessible fallbacks, dashboard 3×3 polish. No DB changes.

## 1.4.0-rc.4.2 — 3×3 Dashboard glass UI, theme JSON + tokens polished, a11y fallbacks; no DB changes.

## 1.4.0-rc.4.1 — Theme JSON + CSS tokens finalized; lanes green; a11y fallbacks; no DB changes.

## v1.4.0-rc.4 — Dashboard v1 (Glass)
- feat(admin): Dashboard v1 with glass KPI tiles (registrations, check-ins Today/Week/Month, tickets scanned 7d), 6-month sparkline, and shortcuts
- feat(ui): glass tokens (accent/blur/elevation) with high-contrast & reduced-transparency fallbacks
- chore(i18n): update POT/PO; compile .mo during packaging if msgfmt is available
- docs: PRD/DesignSystem updated for glass; menu deep-link for Scan → Attendance tab

## 1.4.0-rc.3 — 2025-09-09
- fix(packaging): restore dist/foodbank-manager.zip with correct root slug and main file guard
- fix(static): remove duplicate ABSPATH bootstrap warning in PHPStan runs
- fix(tests): stabilize ScanController unit tests via deterministic stubs and header seam
- feat(admin): add Dashboard MVP (manager tiles + 6-month sparkline + shortcuts)
- chore(i18n): compile .mo in packaging when msgfmt is available; otherwise warn without failing build

## 1.4.0-rc.2 — 2025-09-09
- i18n: textdomain loader, strings localized; sample en_GB locale
- Background export jobs: queue + cron worker + secure downloads
- Attendance: Tickets/QR, Scan & manual check-in, Reports & Exports
- Visual Form Builder (CPT `fb_form`) with live preview
- Theme presets incl. High-Contrast; RTL readiness
- CSV/XLSX/PDF pipelines via seams; headers seam; packaging guards
- QA: PHPStan 0/0; PHPCS (lanes) 0/0
- Docs-Revision: 2025-09-09 (Wave RC2)

## 1.4.0-rc.1 — 2025-09-09
- Attendance: Events CRUD, Tickets/QR, Scan & Manual check-in, Reports & Exports (CSV/XLSX/PDF, masked)
- Email Log: Resend (audited)
- Visual Form Builder (CPT `fb_form`) with live preview
- Theme: High-Contrast preset & RTL readiness
- Background export jobs (queue + cron worker + secure downloads)
- CSV/XLSX/PDF pipelines through seams; headers seam
- Tests/QA: PHPStan 0/0; PHPCS (lanes) 0/0; packaging guards passing
- Docs-Revision: 2025-09-09 (Wave: RC1)

## [1.3.0.1] — 2025-09-08
### Maintenance
- Version alignment (composer.json, plugin header, Core constant) with **no runtime changes**.
- Packaging discipline: slug remains `foodbank-manager/`; main file present at `foodbank-manager/foodbank-manager.php`.
### QA
- PHPStan: 0 errors (fast/full).
- PHPCS (lanes): 0 errors / 0 warnings.
- Packaging guards: PASS.

# Changelog

## [Unreleased]

- feat(admin): Dashboard v1 with glass tiles, sparkline, and shortcuts
- feat(ui): glass tokens + high-contrast & reduced-transparency fallbacks
- chore(i18n): update POT/PO; compile .mo during packaging if available

## [1.3.0-alpha.1] — 2025-09-08

Added: SMTP Diagnostics panel; deterministic admin CSS vars; Entry/GDPR gated render; AttendanceRepo BC shims.

Fixed: Capability self-heal & resolver; dashboard export nonce/cap flow; diagnostics POST handling; test stubs for filter_input/get_current_screen/roles.

Security: Consistent sanitization & 255-char clamp for email settings; strict nonces + caps on handlers.

Docs-Revision: 2025-09-08 (Wave U2)

## [1.2.16] - 2025-09-07
- End-to-end form submissions with schema validation, consent hashing, safe uploads, email send & log, and success reference.
- Canonical WP stubs with deterministic shims keep PHPStan and tests green.
- Shortcode assets load only when the shortcode appears; `[fbm_dashboard]` gated by capability.
- Diagnostics Cron Health panel lists hooks with last/next runs, overdue flags, and run-now/dry-run controls.

## [1.2.15] - 2025-09-07
- PHPStan green
- Settings/Theme sanitize + deterministic CSS
- Diagnostics Cron panel
- Forms MVP e2e
- no regressions
## [1.2.14] - 2025-09-06
- packaging guard: enforce single `foodbank-manager/` slug
- diagnostics: duplicate-install detector with one-click consolidate panel
- test-harness URL shims
- no schema changes

## [1.2.13] - 2025-09-06
### Frontend
- Dashboard UX polish: empty-states, a11y labels, focus rings, skeleton loader; admin-only shortcode hint.
### Admin
- Email Templates: live preview with whitelisted tokens; reset to defaults; a11y labels.
- Design & Theme: sanitized token schema, scoped CSS variables, live preview (dark-mode toggle).
- GDPR SAR: streaming ZIP with fallback HTML; masked by default; chunking + README.txt.
### Diagnostics
- RenderOnce badge + per-screen render counts; menu-parents count remains 1.
- Install health detects duplicate plugin copies with one-click consolidation.
### Infra
- Parallel-safe waves with file fences; docs merged from fragments; no runtime loosening.

## [1.2.12] - 2025-09-06
- Trace comments on all admin templates; Diagnostics RenderOnce badge; no behavior changes.

## [1.2.11] - 2025-09-05
### Fixed
- Admin menu de-dup: fallback parent only when core boot/menu not registered; emergency notice suppressed after boot.
### Added
- Diagnostics: "Menu parents registered" row for quick duplicate detection.
### Infra
- RBAC test harness utilities (admin-only vs FBM caps) – ongoing.
### Known
- PHPUnit still has permission-alignment failures in a subset of suites (tracked in Docs/ISSUES - see RBAC alignment items).

## 1.2.10 — 2025-09-05
- feat(bootstrap): boot watchdog and parent menu failsafe with Diagnostics link

## 1.2.9 — 2025-09-05
- test(harness): add WP helpers (transients/options/nonces), reset globals, gate ext branches
- test(notices/menu): align with cap self-heal + admin fallback; de-dup verified
- chore(release): metadata bump only

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
