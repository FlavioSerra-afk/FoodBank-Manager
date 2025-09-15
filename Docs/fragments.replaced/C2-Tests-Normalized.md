# C2 — Tests normalized

Each unit test should reset global state, grant only the capabilities required and seed deterministic nonces.

```php
protected function setUp(): void {
    parent::setUp();
    fbm_test_reset_globals();
    fbm_grant_viewer(); // or fbm_grant_manager()/fbm_grant_admin()
    fbm_test_set_request_nonce('action');
}
```

UI denial paths assert the friendly message, while handler tests expect the `wp_die` exception.
