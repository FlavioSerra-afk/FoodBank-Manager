=== FoodBank Manager ===
Contributors: portuguese-community-centre-london
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 2.2.26
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

FoodBank Manager provides secure forms, encrypted storage, dashboards, and attendance tracking for food banks.

== Description ==
FoodBank Manager is a volunteer-friendly toolkit for small organisations to handle registration, distributions, and reporting.
It stores data in encrypted tables, enforces least-privilege access, and offers dashboards and exports for auditors.

== Features ==
* Secure public registration with validation, nonce checks, and anti-spam traps.
* Persistent QR tokens delivered in the welcome email alongside a fallback code.
* Staff dashboard (login + FBM capability required) for scanning or manual check-ins.
* Attendance summaries with admin-only CSV exports (UTF-8 BOM, localized headers).
* Theme controls and design tokens scoped to FoodBank Manager screens.
* Diagnostics for mail delivery with throttled resend and health badges.

== Multisite ==
FBM capabilities are granted per site via activation (Administrators retain full access). Options such as theme, schedule, and migration markers store per site. Destructive uninstall remains opt-in so reinstalls can reuse existing data unless explicitly dropped.

== Support & Troubleshooting ==
* **Logs & debug**: PHP errors are recorded in `wp-content/debug.log`. Mail activity appears under Diagnostics → Mail Log with rate-limited resend controls.
* **Health**: Diagnostics → System Health displays badge indicators for mail keys and signing secrets.
* **Docs**: See [Docs/Specs.md](Docs/Specs.md) and [Docs/Plan.md](Docs/Plan.md) for policy, rate-limit, and multisite notes.

== CLI ==
Need to confirm what version of FoodBank Manager is active? Run:

```
wp fbm version
```

This surfaces the plugin version constant so deployment automation can assert the expected build is installed.

== Uninstall ==
By default uninstall leaves FoodBank Manager database tables in place so the plugin can be reinstalled without data loss.
Administrators who need a destructive uninstall can opt in by defining `FBM_ALLOW_DESTRUCTIVE_UNINSTALL` in `wp-config.php`:

```
define( 'FBM_ALLOW_DESTRUCTIVE_UNINSTALL', true );
```

Alternatively, return `true` from the `fbm_allow_destructive_uninstall` filter prior to deactivating the plugin.

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
