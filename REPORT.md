TL;DR: Unit=27E/11F, PHPStan=0, PHPCS(Lanes)=0/0, Packaging=PASS

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
- Tests: 106  Errors: 27  Failures: 11  (suite aborted: Serialization of 'Closure' is not allowed)
### Top failing tests (first 10)
1. EntryPageTest::testViewMasksEmailWithoutCapability — FbmDieException: You do not have permission to access this page.
2. EntryPageTest::testUnmaskShowsPlaintextWithCapability — FbmDieException: You do not have permission to access this page.
3. EntryPageTest::testUnmaskDeniedWithoutNonce — FbmDieException: You do not have permission to access this page.
4. EntryPageTest::testPdfDeniedWithoutNonce — FbmDieException: You do not have permission to access this page.
5. EntryPageTest::testPdfExportHandlesEngines — FbmDieException: You do not have permission to access this page.
6. GDPRPageTest::testMaskedByDefault — Error: Call to undefined method AttendanceRepo::find_by_application_id().
7. GDPRPageTest::testUnmaskedWithCapability — Error: Call to undefined method AttendanceRepo::find_by_application_id().
8. AssetsTest::testThemeCssContainsVariables — Failed asserting that ':root{--fbm-primary:#3b82f6;--fbm-density:comfortable;--fbm-font:system-ui, sans-serif;--fbm-dark:0;}' contains '--fbm-primary:#010203'.
9. AttendanceRepoCountsTest::testCountsReturnInts — Error: Class "FBM\\Tests\\Support\\WPDBStub" not found.
10. AttendanceRepoDailyCountsTest::testCountsLength — Error: Class "FBM\\Tests\\Support\\WPDBStub" not found.
### Failure taxonomy & hints
- Handler denial and nonce/capability gaps remain.
- Missing repository stubs/methods in Attendance and GDPR tests.
- Theme CSS assertions expect specific tokens.

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
- N/A (no files)

## 5) Packaging Guards
- ZIP root: `foodbank-manager/` (expect `foodbank-manager/`) — PASS
- Main file present: PASS

## 6) Prioritized next actions (errors-first)
1) **P0 Unit** — Normalize tests onto BaseTestCase; fix denial policies; seed nonces.
2) **P1 PHPStan** — Composer-only bootstrap; remove duplicate stubs; fix signatures/docblocks.
3) **P2 PHPCS (lanes)** — Keep curated set green.
4) **Release (text-only)** once green; bump versions; package.

## 7) Appendix — Raw artifact paths
- build/phpunit.log
- build/phpunit-junit.xml
- build/phpstan-fast.json
- build/phpstan.json
- build/phpcs-lanes.json
- build/zip-root.txt
- build/zip-main.txt
