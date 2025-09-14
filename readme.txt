=== FoodBank Manager ===
Contributors: portuguese-community-centre-london
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1
$12.2.10
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

FoodBank Manager provides secure forms, encrypted storage, dashboards, and attendance tracking for food banks.

== Description ==
FoodBank Manager is a volunteer-friendly toolkit for small organisations to handle registration, distributions, and reporting.
It stores data in encrypted tables, enforces least-privilege access, and offers dashboards and exports for auditors.

== Features ==
* Secure intake forms with encrypted storage
* Dashboard KPIs for attendance and inventory
* QR-based ticketing and check-in
* Role-based permissions with per-user overrides
* CSV and PDF exports with masked personal data
* GDPR export and erasure helpers
* Customisable email templates and themes

== Multisite ==
Network administrators receive `fbm_manage_jobs` on activation; a migration flag prevents re-granting on upgrade. Cron hooks such as retention are idempotent and safe per site.

== Support & Troubleshooting ==
* **Logs & debug**: PHP errors are recorded in `wp-content/debug.log`. Mail activity appears under Diagnostics → Mail Log.
* **System report**: Diagnostics → Report offers a "Copy report" button for a JSON system report.
* **Cron runs**: Diagnostics → Cron Health lists plugin cron hooks with last and next run times.
* See [Docs/API.md](Docs/API.md) for the error contract and [Docs/Diagnostics.md](Docs/Diagnostics.md) for rate-limit and multisite notes.

== Upgrade Notice ==
= 1.5.0 =
First stable cut with readme polish and release checksums.

== Changelog ==
= 2.2.3 =
* release sync
* header test hardening
* vertical tabs polish
= 2.2.2 =
* New two-pane Theme page with accordion controls & style-book preview
* Live CSS variable preview + JSON Import/Export/Defaults
* Fixed settings save (register_setting for fbm_theme)
* Tight Theme-screen asset gating
* No new PHPCS/PHPStan issues; tests green
