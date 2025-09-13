Docs-Revision: 2025-09-12 (v1.11.3 patch finalize)
# Diagnostics Hub

The diagnostics hub surfaces operational information for administrators. It
covers mail transport details, recent mail failures with retry, cron event
telemetry, export job status, retention sweeps, and basic environment checks.

## Jobs access

The Jobs page is read-only unless a user has the `fbm_manage_jobs` capability.
Administrators can grant it via **Diagnostics → Permissions**.

## Multisite notes

`fbm_manage_jobs` is granted to network administrators on activation so job
dashboards work across subsites. The retention cron is idempotent and safe to
run on each site. Last and next run times for cron hooks appear in the System
Report.

Scan and test endpoints are rate limited per user with a configurable window
to mitigate abuse. Administrators are exempt from these limits by default.

Rate-limited responses include standard `RateLimit-*` headers
(`RateLimit-Limit`, `RateLimit-Remaining`, `RateLimit-Reset`) and `Retry-After`
so clients can respect cooldowns.

## Security & Throttling

The Diagnostics hub exposes **Security & Throttling** settings to control scan
rates.

- **Window** – rolling window in seconds (clamped between 5 and 300).
- **Base limit** – allowed scans per window before throttling (1–120).
- **Role multipliers** – per‑role adjustment where `1.0` uses the base limit,
  values above increase allowance, and `0` disables throttling for the role.

Example CLI usage:

```bash
$ wp fbm throttle show
window=30 base=6
administrator=unlimited
editor=12

$ wp fbm throttle set --window=60 --base=8 --role=editor:2
```

Responses include standard `RateLimit-*` headers and `Retry-After` so clients
can respect cooldowns.

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
payload and a text summary on the clipboard. Cron lines include the last and
next run timestamps for each event.

## WP-CLI

```bash
$ wp fbm version
1.6.1
$ wp fbm jobs list --limit=10
$ wp fbm jobs retry 42
$ wp fbm retention run --dry-run
$ wp fbm privacy preview user@example.com
$ wp fbm mail test --to=admin@example.org
```

Commands are registered during bootstrap with `WP_CLI::add_command()`.
