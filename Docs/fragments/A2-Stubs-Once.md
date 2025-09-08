# A2-Stubs-Once

The test suite loads WordPress shims from a single source: `tests/Support/WPStubs.php`. This file provides lightweight implementations for runtime and switches to the official `php-stubs/wordpress-stubs` signatures when the `FBM_PHPSTAN` environment variable is set.

* `tests/bootstrap.php` only loads Composer's autoloader and resets globals via `fbm_test_reset_globals()`.
* PHPUnit tests must not declare WordPress functions inline; rely on the shared stubs instead.
* Run static analysis with `FBM_PHPSTAN=1 composer phpstan` so PHPStan bootstraps only `vendor/autoload.php`.
