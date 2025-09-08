# Staging Smoke Test Report

## Environment
- WP version: N/A (wp-cli could not locate WordPress installation)
- Theme: N/A
- Active plugins: N/A

## Test Results
| Step | Description | Result | Notes |
| ---- | ----------- | ------ | ----- |
| 4 | Admin CSS vars on FBM screens | FAIL | wp-admin inaccessible; no output to verify |
| 5 | Diagnostics → SMTP test | FAIL | Unable to reach Diagnostics screen |
| 6 | Capability self-heal | FAIL | `wp role` command failed (no WordPress install) |
| 7 | Frontend shortcodes render | FAIL | Draft page creation/view blocked; site unreachable |
| 8 | Form submission | FAIL | Unable to submit synthetic data |
| 9 | Dashboard export | FAIL | Cannot access dashboard |
| 10 | SAR export | FAIL | SAR workflow not executed |
| 11 | Editor user gating | FAIL | Could not create user or access FBM admin |

## Commands
```
php wp-cli.phar db export fbm_prealpha1_$(date +%F).sql --allow-root
# Error: This does not seem to be a WordPress installation.

php wp-cli.phar plugin list --format=json --allow-root
# Error: This does not seem to be a WordPress installation.

php wp-cli.phar plugin install dist/foodbank-manager.zip --force --allow-root
# Error: This does not seem to be a WordPress installation.

php wp-cli.phar plugin activate foodbank-manager --allow-root
# Error: This does not seem to be a WordPress installation.

php wp-cli.phar plugin get foodbank-manager --fields=name,status,version --allow-root
# Error: This does not seem to be a WordPress installation.
```

## Next Actions
- [P0] Obtain valid staging WordPress environment and rerun deployment tests (`PRD §5.3`, §10 for capability checks).
- [P0] Verify Diagnostics SMTP test once environment accessible (PRD §5.4 Email).
- [P0] Validate SAR export flow (PRD §8 GDPR).

