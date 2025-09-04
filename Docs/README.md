Docs-Revision: 2025-09-04 (Wave v1.1.7 – Database UX P1)
# FoodBank Manager (WordPress Plugin)

A secure, mobile‑first WordPress plugin for Food Bank applications, encrypted data storage, operational dashboards, and **attendance tracking** with QR codes. Administrators can configure branding and default email senders via a validated settings page. The Shortcodes page includes a builder with masked, nonce‑protected previews.

> Full PRD: [`Docs/PRD-foodbank-manager.md`](Docs/PRD-foodbank-manager.md)
> Companion issues list: [`ISSUES-foodbank-manager.md`](ISSUES-foodbank-manager.md)

## Start here
- [`Docs/Architecture.md`](Docs/Architecture.md)
- [`Docs/Agents.md`](Docs/Agents.md)
- [`Docs/API.md`](Docs/API.md)
- [`Docs/DB_SCHEMA.md`](Docs/DB_SCHEMA.md)
- [`Docs/LocalDev.md`](Docs/LocalDev.md)
- [PHPCS Ignores & Suppressed Issues](./PHPCS-Ignores.md)

```bash
composer phpcs && composer phpstan -- --memory-limit=1G && composer test
```

---

## ✨ Features
- **Multi‑form builder** (visual): fields, steps, conditional logic, theming (light/dark/high‑contrast).
- **Exact Food Bank form preset** matching the live site.
- **Read-only Presets Library** with copyable `[fbm_form preset="…"]` shortcodes.
- **Email confirmations & admin notifications** (HTML), with **logging** and resend.
- **Email templates admin page** (list with edit skeleton).
- **Custom database** with **field‑level encryption** (libsodium AEAD, envelope keys).
- **Front‑end dashboard** (Viewer/Manager) with sanitized filters and CSV/XLSX/PDF exports that sanitize filenames, include a UTF‑8 BOM with translated headers, and mask sensitive fields unless the user has `fb_view_sensitive`.
- **Database filter presets and per-user column toggles** on the admin Database page.
- **Attendance module**: events (optional), admin‑only QR check‑in links (REST nonce, no PII), override reasons, scan/manual check‑in, policy rules, reports. Attendance repository queries use `$wpdb->prepare()` with strict placeholders, mask PII by default, and are covered by unit tests.
- **GDPR helpers**: consent logs, SAR export, retention/anonymisation.
- **Diagnostics**: mail failures, environment checks, test email, repair capabilities.
- Accessible, responsive, EN/PT translations.

## ✅ Requirements
- WordPress **6.x+**
- PHP **8.1+**
- PHP **libsodium** (bundled in PHP 7.2+)
- Database: MySQL/MariaDB (utf8mb4)

## Installation

Do not use “Code → Download ZIP”.

Instead, go to Releases and download foodbank-manager.zip.

Alternatively build locally with `composer build:zip` and upload that ZIP.

## 🚀 Install (dev)
1. Clone into `wp-content/plugins/foodbank-manager`.
2. Install PHP deps:
   ```bash
   composer install
   ```
3. (Optional) Install JS deps for builder/dashboard assets:
   ```bash
   npm install
   ```
4. Activate in **WP Admin → Plugins**.

## 🔐 Configure
- **Encryption KEK** (add to `wp-config.php`; replace with your base64 32‑byte key):
  ```php
  define('FBM_KEK_BASE64', 'BASE64_ENCODED_32_BYTE_KEY');
  ```
- **SMTP**: configure a site‑wide SMTP plugin (e.g., WP Mail SMTP).
- **CAPTCHA**: add Cloudflare Turnstile or reCAPTCHA keys in **FoodBank → Settings**.

## 🧪 Quick start
1. **Create a Form** (FoodBank → Forms) or use the **“Food Bank Intake”** preset.
2. Drop the shortcode on a page:
   ```
   [fbm_form id="123"]
   ```
3. Submit a test entry → verify applicant & admin emails appear under **Diagnostics**.
4. Add the front‑end dashboard for team members:
   ```
   [fbm_entries]
   ```
5. Try **Attendance → Scan** (QR embedded in confirmation email).

## Shortcodes

| Shortcode | Attributes (default) |
| --- | --- |
| `[fbm_form]` | `id` (string, default "1") |
| `[fbm_entries]` | _None_ |
| `[fbm_attendance_manager]` | _None_ |

## 📚 Docs
- **PRD:** [`Docs/PRD-foodbank-manager.md`](Docs/PRD-foodbank-manager.md)
- **Issues plan:** [`ISSUES-foodbank-manager.md`](ISSUES-foodbank-manager.md)
- **Contributing:** [`CONTRIBUTING.md`](CONTRIBUTING.md)
- **Security policy:** [`SECURITY.md`](SECURITY.md)

## 🤝 Contributing
PRs welcome! Please read [`CONTRIBUTING.md`](CONTRIBUTING.md).  
We follow Conventional Commits and WordPress coding standards.

## 🛡️ Security
Please report vulnerabilities privately — see [`SECURITY.md`](SECURITY.md).

## 📝 License
This plugin is intended for release under **GPL‑2.0‑or‑later** (typical for WordPress).  
If your organization requires a different license, update this section before publishing.

## 🗺️ Roadmap (high‑level)
- M1 Foundations • M2 Forms • M3 Dashboards • M4 Attendance • M5 GDPR/Diagnostics • M6 Polish

## 🙌 Acknowledgements
- WordPress community & security handbook
- Action Scheduler, DataTables, and the maintainers of PHP PDF libraries
