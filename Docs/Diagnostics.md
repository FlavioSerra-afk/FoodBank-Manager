Docs-Revision: 2025-09-10 (Wave RC4.6.0 — Diagnostics hub)
# Diagnostics Hub

The diagnostics hub surfaces operational information for administrators. It
covers mail transport details, recent mail failures with retry, cron event
telemetry, export job status, and basic environment checks.

## SMTP seam

FoodBank Manager does not force a particular mail transport. Administrators may
hook into `phpmailer_init` to configure SMTP servers, authentication, or
character sets. The plugin leaves this hook untouched by default so site owners
can wire external providers safely.
