=== FoodBank Manager ===
Contributors: portuguese-community-centre-london
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 2.2.26-rc.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

FoodBank Manager provides secure forms, encrypted storage, dashboards, and attendance tracking for food banks.

Canonical requirements live in Docs/Specs.md, Docs/Plan.md, Docs/Tasks.md, and Docs/Matrix.md inside the plugin repository.

== Description ==
FoodBank Manager is a volunteer-friendly toolkit for small organisations to handle registration, distributions, and reporting.
It stores data in encrypted tables, enforces least-privilege access, and offers dashboards and exports for auditors.

== Features ==
* `[fbm_registration_form]` shortcode — secure public registration with validation, nonce checks, and anti-spam traps.
* `[fbm_staff_dashboard]` shortcode — staff dashboard (login + FBM capability required) for scanning QR codes or recording manual attendance within the configured window.
* Admin-only attendance summaries and CSV exports with sanitized filenames, UTF-8 BOM, and localized headers (nonce + capability enforced).

== Multisite ==
FBM capabilities are granted per site via activation (Administrators retain full access). Options such as theme, schedule, and migration markers store per site. Destructive uninstall remains opt-in so reinstalls can reuse existing data unless explicitly dropped.

== Support & Troubleshooting ==
* **Logs & debug**: PHP errors are recorded in `wp-content/debug.log`. Mail activity appears under Diagnostics → Mail Log with rate-limited resend controls.
* **Health**: Diagnostics → System Health displays badge indicators for mail keys and signing secrets.
* **Docs**: See [Docs/Specs.md](Docs/Specs.md), [Docs/Plan.md](Docs/Plan.md), [Docs/Tasks.md](Docs/Tasks.md), and [Docs/Matrix.md](Docs/Matrix.md) for policy, milestones, and governance notes.

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
