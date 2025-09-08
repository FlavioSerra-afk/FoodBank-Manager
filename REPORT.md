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
- Tests: 135  Assertions: 231  Errors: 28  Failures: 20
### Top failing tests (first 10)
1. ShortcodesPageTest::testCapabilityRequired — FbmDieException: You do not have permission to access this page.
2. DiagnosticsPageTest::testSendTestEmailSuccess — Failed asserting that '' contains "notice=sent".
3. DiagnosticsPageTest::testSendTestEmailFailure — Failed asserting that '' contains "notice=error".
4. DiagnosticsPageTest::testRetentionRunOutputsSummary — Failed asserting that '<div class="wrap fbm-admin">\n'
5. DiagnosticsPageTest::testRetentionDryRunOutputsSummary — Failed asserting that '<div class="wrap fbm-admin">\n'
6. DiagnosticsPageTest::testRepairCapsActionEnsuresCaps — Failed asserting that false is true.
7. EntryPageTest::testViewMasksEmailWithoutCapability — PHPUnit\Framework\Exception: Fatal error: Uncaught FbmDieException: You do not have permission to access this page.
8. EntryPageTest::testUnmaskShowsPlaintextWithCapability — PHPUnit\Framework\Exception: Fatal error: Uncaught FbmDieException: You do not have permission to access this page.
9. EntryPageTest::testUnmaskDeniedWithoutNonce — PHPUnit\Framework\Exception: Fatal error: Uncaught FbmDieException: You do not have permission to access this page.
10. EntryPageTest::testPdfDeniedWithoutNonce — PHPUnit\Framework\Exception: Fatal error: Uncaught FbmDieException: You do not have permission to access this page.
### Failure taxonomy & hints
- Handler Denial: 1 — Use `expectException(FbmDieException::class)` or grant caps/nonce.
- Gated UI / Escaping mismatches: 11 — Assert escaped output; set viewer/manager caps.

## 3) PHPStan — Summary
- Fast: 0 (analysis failed: Cannot redeclare function get_plugins)
- Full: 0 (analysis failed: Cannot redeclare function get_plugins)
### Top 10 files by error count
- N/A (run failed)
### Most common messages
- N/A (run failed)

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
3) **P2 PHPCS (lanes)** — Keep curated set green; do not broaden scope.
4) **Release (text-only)** once green; bump versions; package.

## 7) Appendix — Raw artifact paths
- build/phpunit-junit.xml
- build/phpstan-fast.json
- build/phpstan.json
- build/phpcs-lanes.json
- build/zip-root.txt
- build/zip-main.txt
