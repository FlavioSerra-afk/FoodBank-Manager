# C3 — Unit Base

A shared `BaseTestCase` resets globals, seeds deterministic nonces and grants
minimal viewer caps for every test. Escalate permissions in individual tests
when needed.

```php
final class ExampleTest extends BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_admin(); // escalate as required
    }
}
```

UI denial paths assert the friendly message. Handler tests expect the stubbed
`wp_die` exception. Redirects are checked via `$GLOBALS['__last_redirect']`.
