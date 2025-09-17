Docs-Revision: 2025-09-21 (v2.2.26 admin summaries + CLI parity)
# FoodBank Manager Plugin

Stable tag: 2.2.26
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1

See [Docs/Specs.md](Docs/Specs.md) for the canonical product requirements.

## Theme & Design

Theme & Design only affects FoodBank Manager pages and shortcodes; WordPress admin chrome remains unchanged.

## Shortcodes

FoodBank Manager registers two shortcodes only:

* `[fbm_registration_form]` — public registration form with validation, nonce enforcement, and anti-spam traps.
* `[fbm_staff_dashboard]` — staff-facing dashboard (login + FBM capability required) for scanning QR codes or recording manual collections within the configured window.

## Multisite notes

Capabilities are granted per site on activation: administrators retain all FBM capabilities while custom FBM Manager/Staff roles receive their mapped bundles. Options such as theme, schedule, and migration markers store per site, and destructive uninstall requires explicit opt-in. See [Docs/Plan.md](Docs/Plan.md) for rate-limit and multisite notes.

## API errors

FoodBank Manager normalizes error responses across REST and AJAX (see [Docs/Specs.md](Docs/Specs.md) for policy details):

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

- **Logs & debug**: PHP errors are recorded in `wp-content/debug.log`. Mail activity appears under Diagnostics → Mail Log with throttled resend controls.
- **Health**: Diagnostics → System Health shows badge indicators for mail transport keys and signing secrets.
- **Docs**: See [Docs/Specs.md](Docs/Specs.md) and [Docs/Plan.md](Docs/Plan.md) for policy, rate-limit, and multisite notes.

## CLI

FoodBank Manager exposes a lightweight WP-CLI command to surface the currently installed version:

```bash
wp fbm version
```

The command returns the `FoodBankManager\Core\Plugin::VERSION` string so automation can confirm deployed builds.

## Manual release steps

1. `composer i18n:build -- --allow-root`
2. `composer build:zip` (runs `bin/package.sh`, enforcing version alignment and generating `dist/foodbank-manager-manifest.txt`)
3. `sha256sum dist/foodbank-manager.zip > SHA256SUMS`
4. Upload the ZIP, include the manifest/checksum in the release, and publish after verifying the sums.
