# Stubs — Single Source, Single Load

WordPress functions for tests and static analysis live in one place:
`tests/Support/WPStubs.php`. The Composer dev autoloader loads this file for
both PHPUnit and PHPStan.

* Runtime tests use the lightweight shim implementations.
* Static analysis sets `FBM_PHPSTAN=1` so the stubs file defers to the official
  `php-stubs/wordpress-stubs` package and avoids redeclarations.

To keep stubs centralized, the CI workflow fails if any test file other than
`tests/Support/WPStubs.php` declares a function. This guard prevents accidental
shadowing and ensures WordPress helpers remain single-source.

