# B3 PHPCS Delta

- Added `phpcs:lanes` Composer script to lint the active lanes and the two quick-win files.
- CI runs `composer phpcs:lanes` to keep modified files green.
- A separate "Strict Guard Green" milestone will enforce PHPCS across the remaining legacy codebase.
