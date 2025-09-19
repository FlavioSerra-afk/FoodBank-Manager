=== FoodBank Manager ===
Contributors: portuguese-community-centre-london
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.2.1
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
* Reports cache tools include a manager-only **Invalidate cache** button that clears all cached `fbm:reports` keys after exports or data corrections.
* Settings → Uninstall & Privacy: manager-controlled destructive uninstall opt-in plus shortcuts into WordPress privacy exporter/eraser tools.

== Performance ==
Staff dashboards and reports rely on time-boxed transients so pages stay fast without serving stale data. Scanner status and totals cache for 60–180 seconds, while CSV preview totals and paginated report pages cache for 300 seconds. Administrators can invalidate report caches on demand from Reports → Invalidate cache when new data lands.

== Screenshots ==
1. Registration form with validation and consent controls.
2. Staff dashboard showing QR scanner, manual entry, and session status.
3. Attendance reports with cached summaries, pagination, and CSV export.
4. Diagnostics page with mail log, health indicators, and cache tools.

== Multisite ==
FBM capabilities are granted per site via activation (Administrators retain full access). Options such as theme, schedule, and migration markers store per site. Destructive uninstall remains opt-in so reinstalls can reuse existing data unless explicitly dropped.

== Support & Troubleshooting ==
* **Logs & debug**: PHP errors are recorded in `wp-content/debug.log`. Mail activity appears under Diagnostics → Mail Log with rate-limited resend controls.
* **Health**: Diagnostics → System Health displays badge indicators for mail keys and signing secrets.
* **Docs**: See [Docs/Specs.md](Docs/Specs.md), [Docs/Plan.md](Docs/Plan.md), [Docs/Tasks.md](Docs/Tasks.md), and [Docs/Matrix.md](Docs/Matrix.md) for policy, milestones, and governance notes.

== CLI ==
Need to confirm what version of FoodBank Manager is active or probe a token payload? Run:

```
wp fbm version
wp fbm token probe 'FBM1:example...'
```

`wp fbm version` surfaces the plugin version constant so deployment automation can assert the expected build is installed. `wp fbm token probe` returns a redacted JSON payload describing the canonical version, HMAC validation, and revocation state without revealing the raw token.

== Uninstall ==
By default uninstall leaves FoodBank Manager database tables in place so the plugin can be reinstalled without data loss. Managers can opt into destructive uninstall via Settings → Uninstall & Privacy; when enabled (or when `FBM_ALLOW_DESTRUCTIVE_UNINSTALL` is defined in `wp-config.php`) uninstall removes FBM tables, options, caches, and scheduled events.

== Privacy ==
FoodBank Manager registers with the WordPress privacy exporter/eraser registry and surfaces policy text describing stored data and retention guarantees. Managers can enqueue a privacy eraser run for an email address or member reference from Settings → Uninstall & Privacy.

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
