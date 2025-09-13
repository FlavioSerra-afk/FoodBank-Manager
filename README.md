Docs-Revision: 2025-09-12 (v1.11.5 patch finalize)
# FoodBank Manager Plugin

Stable tag: 1.11.5
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1

See [Docs/PRD-foodbank-manager.md](../Docs/PRD-foodbank-manager.md) for the full product requirements and specifications.

## CLI

FoodBank Manager exposes a parent command via `WP_CLI::add_command()`. After
activation you can run:

```bash
wp fbm version
```

## Jobs access

The Jobs page is read-only unless a user has the `fbm_manage_jobs` capability.
On multisite, network administrators receive this capability on activation and a
migration flag prevents re-granting on upgrade.

## Multisite notes

Network administrators receive `fbm_manage_jobs` on activation; a migration flag prevents re-granting on upgrade. Cron hooks such as retention are idempotent and safe to run per site. See [Docs/Diagnostics.md](../Docs/Diagnostics.md) for rate-limit and multisite notes.

## API errors

FoodBank Manager normalizes error responses across REST and AJAX (see [Docs/API.md](../Docs/API.md) for a detailed cheat-sheet):

| Code | Meaning |
| ---- | ------- |
| 400 | Bad request |
| 401 | Invalid or missing nonce |
| 403 | Capability check failed |
| 404 | Not found |
| 409 | Conflict |
| 422 | Validation failed |
| 429 | Rate limited |

Rate-limited responses include `RateLimit-Limit`, `RateLimit-Remaining`, and `RateLimit-Reset` headers; 429 responses also send `Retry-After` so clients know when to retry.

## Support & Troubleshooting

- **Logs & debug**: PHP errors are recorded in `wp-content/debug.log`. Mail activity appears under Diagnostics → Mail Log.
- **System report**: Diagnostics → Report offers a "Copy report" button for a JSON system report.
- **Cron runs**: Diagnostics → Cron Health lists plugin cron hooks with last and next run times.
- See [Docs/API.md](../Docs/API.md) for the error contract and [Docs/Diagnostics.md](../Docs/Diagnostics.md) for rate-limit and multisite notes.

## Manual release steps

1. `composer i18n:build -- --allow-root`
2. `bash bin/package.sh`
3. `sha256sum dist/foodbank-manager.zip > SHA256SUMS`
4. Upload the ZIP and publish the release after verifying the checksum.
