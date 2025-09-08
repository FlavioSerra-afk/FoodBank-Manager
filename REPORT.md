# FoodBank Manager — Errors-First Health Report (current repo)

## 0) Docs consulted
- PRD-foodbank-manager.md
- Architecture.md
- SECURITY.md
- DB_SCHEMA.md
- API.md
- ISSUES-foodbank-manager.md
- fragments/*

## 1) Environment
- Composer: 2.8.11
- PHP: 8.4.12

## 2) PHPUnit (Unit) — Summary
- Tests: 135  Assertions: 242  Errors: 28  Failures: 18
### Top failing tests (first 10)
1. DiagnosticsPageTest::testSendTestEmailSuccess — Failed asserting that '' contains "notice=sent".
2. DiagnosticsPageTest::testSendTestEmailFailure — Failed asserting that '' contains "notice=error".
3. DiagnosticsPageTest::testCronTableShowsOverdue — Failed asserting that '<div class="wrap fbm-admin">\\n' contains expected data.
4. DiagnosticsPageTest::testRetentionRunOutputsSummary — Failed asserting that '<div class="wrap fbm-admin">\\n' contains expected summary.
5. DiagnosticsPageTest::testRetentionDryRunOutputsSummary — Failed asserting that '<div class="wrap fbm-admin">\\n' contains expected summary.
6. EntryPageTest::testViewMasksEmailWithoutCapability — Fatal error: Uncaught FbmDieException: You do not have permission to access this page.
7. EntryPageTest::testUnmaskShowsPlaintextWithCapability — Fatal error: Uncaught FbmDieException: You do not have permission to access this page.
8. EntryPageTest::testUnmaskDeniedWithoutNonce — Fatal error: Uncaught FbmDieException: You do not have permission to access this page.
9. EntryPageTest::testPdfDeniedWithoutNonce — Fatal error: Uncaught FbmDieException: You do not have permission to access this page.
10. EntryPageTest::testPdfExportHandlesEngines — Fatal error: Uncaught FbmDieException: You do not have permission to access this page.
### Failure taxonomy & hints
- Handler Denial: 8
- Gated UI / Escaping mismatches: 40

## 3) PHPStan — Summary
- Fast: 0
- Full: 0
### Top 10 files by error count
- N/A (no errors)
### Most common messages
- N/A (no errors)

## 4) PHPCS (lanes) — Summary
- Errors: 0  Warnings: 0
### Top 10 files
- includes/Http/FormSubmitController.php — E:0 W:0
- includes/Database/ApplicationsRepo.php — E:0 W:0
- includes/Mail/LogRepo.php — E:0 W:0
- includes/Shortcodes/Shortcodes.php — E:0 W:0
- includes/Shortcodes/FormShortcode.php — E:0 W:0
- includes/Shortcodes/DashboardShortcode.php — E:0 W:0
- includes/Core/Assets.php — E:0 W:0
- includes/Admin/DiagnosticsPage.php — E:0 W:0
- includes/Admin/SettingsPage.php — E:0 W:0
- includes/Core/Retention.php — E:0 W:0

## 5) Packaging Guards
- ZIP root: `foodbank-manager/` (expect `foodbank-manager/`) — PASS
- Main file present: PASS

## 6) Prioritized next actions (errors-first)
1) **P0 Unit** — Normalize tests onto BaseTestCase; fix denial policies; seed nonces.
2) **P1 PHPStan** — Composer-only bootstrap; remove duplicate stubs; fix signatures/docblocks.
3) **P2 PHPCS (lanes)** — Keep curated set green.
4) **Release (text-only)** once green; bump versions; package.

## 7) Appendix — Raw artifact paths
- build/phpunit-junit.xml
- build/phpstan-fast.json
- build/phpstan.json
- build/phpcs-lanes.json
- build/zip-root.txt
- build/zip-main.txt
