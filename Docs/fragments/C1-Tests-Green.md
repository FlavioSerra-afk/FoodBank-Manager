Dashboard, Settings, and Theme tests now stable with deterministic caps, sanitized inputs, and scoped CSS variables.

**Patterns**
- Call `fbm_test_reset_globals()` in `setUp()`.
- Grant minimal capabilities with `fbm_grant_for_page()` or specific helpers.
- Seed deterministic nonces via `fbm_test_set_request_nonce()` when exercising handlers.
- Assert redirects from `$GLOBALS['__last_redirect']` and expect `RuntimeException` when stubs call `wp_die`.

