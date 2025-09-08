# QA — PHPStan with WP Stubs

PHPStan runs against the plugin code using the Composer dev autoloader to load the custom WordPress stubs once. The `tests/Support/WPStubs.php` file loads shim implementations at runtime and switches to the official `php-stubs/wordpress-stubs` signatures when the `FBM_PHPSTAN` environment variable is set. Stubs must come from a single source and be autoloaded only once.

Steps:

1. Install dependencies and dump the autoloader:
   ```bash
   composer dump-autoload -o
   ```
2. Run a fast analysis:
   ```bash
   FBM_PHPSTAN=1 composer phpstan:fast -- --memory-limit=1G
   ```
3. Run the full analysis:
   ```bash
   FBM_PHPSTAN=1 composer phpstan
   ```

PHPStan should report 0 errors. Because stubs load through the dev autoloader, no additional bootstrap entries are required.
