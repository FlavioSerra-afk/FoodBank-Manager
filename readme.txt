=== FoodBank Manager ===
Contributors: portuguese-community-centre-london
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.9.2
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

= 1.9.0 =
* Added an admin-only import diff viewer that previews incoming rules, resolved mappings, and skip reasons before applying changes.
* Improved the registration editor debugger with an optional performance trace toggle and JSON export for timing samples.
* Documented the lightweight npm build workflow (`npm run build`) which now no-ops to keep CI green without bundling.

= 1.8.0 =
* Added JSON import/export workflow with schema validation, REST-backed mapping preview, and sanitized server-side imports.
* Introduced six guided rule presets with placeholder prompts, keyboard navigation, and aria-live announcements.
* Debounced rule evaluation in the editor and public form with batched DOM updates plus refreshed error summary focus handling.
= 1.7.0 =
* Conditional visibility phase-2 adds grouped AND/OR logic, numeric/date operators, and a rule debugger shared between the editor and preview.
* Registration editor now autosaves every 30 seconds, keeps the last five revisions with restore controls, and exposes keyboard shortcuts for save/preview.
* Public form gains an accessible error summary with focus management and aria-live status updates; server validation mirrors the new rule evaluator.
= 1.6.0 =
* Added phase-1 conditional visibility builder for the registration editor with a help link to the template tag matrix.
* Registration submissions now enforce visibility rules server-side and clean hidden uploads while the public form toggles fields without custom code.
* Polished the preview modal and rule editor for accessibility with improved focus handling and status messaging.
= 1.5.0 =
* Registration editor toolbar inserts validated CF7 tags for each supported field type and preview responses return sanitized markup with modal nonces.
* Front-end submissions clamp household size to template bounds, treat any checked consent value as affirmative, and enforce single sanitized uploads via WordPress APIs.
* Hardened welcome and notification emails and refreshed reports member detail view with fallback context and reverse-chronological history.
= 1.4.0 =
* Registration editor preview now opens in an accessible modal with sanitized markup and improved toolbar snippets.
* Hardened registration submissions with consent timestamp persistence, stricter household size clamps, and secured upload handling.
* Streamlined admin notifications and member history links from reports for quicker follow-up.
= 1.3.0 =
* Added versioned AES-256-GCM envelope encryption for member names and diagnostics mail log emails with HKDF-derived master key wrapping.
* Introduced Diagnostics admin controls and `wp fbm crypto` WP-CLI commands for status, migration, rotation, and verification (dry-run + batching).
* Added "Encrypt new writes" setting (enabled by default on fresh installs) so new PII rows are stored encrypted immediately.
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
