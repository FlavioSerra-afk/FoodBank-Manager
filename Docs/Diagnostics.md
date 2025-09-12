Docs-Revision: 2025-09-11 (Wave RC7.5 — License align/i18n build/release provenance)
# Diagnostics Hub

The diagnostics hub surfaces operational information for administrators. It
covers mail transport details, recent mail failures with retry, cron event
telemetry, export job status, retention sweeps, and basic environment checks.

## SMTP seam

FoodBank Manager does not force a particular mail transport. Administrators may
hook into `phpmailer_init` to configure SMTP servers, authentication, or
character sets. The plugin leaves this hook untouched by default so site owners
can wire external providers safely.

## Retention runner

The Cron Health panel exposes **Run now** and **Dry-run** controls for data
retention. Results list total records affected and anonymised along with error
counts. All actions are protected by nonces and the `fb_manage_diagnostics`
capability.

## Privacy panel
The Diagnostics hub exposes a Privacy panel to preview Subject Access Request data and execute dry-run or real erasure. Actions require a nonce and the `fb_manage_diagnostics` capability.

## System Report

Administrators can generate a system report that lists plugin, PHP and WordPress
versions, active panels, cron event timings, recent mail failures and job queue
counts. The report includes a **Copy report** button that places the JSON
payload and a text summary on the clipboard.

## WP-CLI

```bash
$ wp fbm version
Success: FoodBank Manager 1.6.0
$ wp fbm jobs list
$ wp fbm retention run --dry-run
```

Registering commands via `WP_CLI::add_command()` is the canonical method.
