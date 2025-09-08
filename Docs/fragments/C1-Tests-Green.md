Dashboard, Settings, and Theme tests now stable with deterministic caps, sanitized inputs, and scoped CSS variables.

**Patterns**
- Call `fbm_test_reset_globals()` in `setUp()`.
- Grant minimal capabilities with `fbm_grant_viewer()`, `fbm_grant_manager()`, or `fbm_grant_admin()`.
- Seed deterministic nonces via `fbm_test_set_request_nonce()` when exercising handlers.
- UI tests assert the friendly `wp_die` message; handler tests `expectException` for the stubbed die.
- Assert redirects from `$GLOBALS['__last_redirect']`.

