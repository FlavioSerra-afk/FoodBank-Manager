Docs-Revision: 2025-09-09 (Wave RC3 Fix Pack)
# Security Policy — PCC FoodBank Manager

We take security seriously. Please follow the guidelines below for reporting vulnerabilities.

Custom CSS supplied via the Design & Theme page is strictly sanitised: `@import`, `@font-face`, `@keyframes`, `url()`, `expression()` and angle brackets are removed, only a small set of style properties is allowed, and `!important` is stripped.

## 📬 Reporting
- **Do not** open a public GitHub Issue for security findings.
- Email the maintainers privately at: **security@pcclondon.uk** (replace if different).
- Optionally use GitHub Security Advisories for a private report.

Include:
- A clear description of the issue and potential impact
- Steps to reproduce / proof of concept (PoC)
- Affected versions and environment (WP/PHP/DB)
- Any logs or screenshots (scrub PII)

We aim to acknowledge reports **within 2 working days**.

## 🔒 Scope
- Plugin code in this repository
- Exposed endpoints under the plugin’s REST namespace (`pcc-fb/v1`)
- Front‑end submission and dashboard flows (card dashboard shows aggregated, non‑PII counts with optional trend deltas, filters, and nonce-protected CSV export; requires `fb_manage_dashboard`)
- Database migrations and encryption code
- Admin QR check-in URLs include only IDs + REST nonces and require `fb_manage_attendance`.
- Admin shortcode previews enforce capability checks, nonces, attribute whitelists with `mask_sensitive=true`, and `wp_kses_post` filtering.
- Forms presets are sanitized server-side; unknown preset IDs fall back to a minimal safe form without exposing errors to regular users.
- Database filter presets and per-user column selections require `fb_manage_database`, use nonces, and whitelist allowed query/column keys.
- Admin UI is wrapped in `.fbm-admin` with screen-gated CSS/notices to prevent cross-plugin CSS or markup bleed.

## 🔐 Capabilities Matrix

| Capability | Purpose |
| --- | --- |
| `fb_manage_dashboard` | View dashboard metrics |
| `fb_manage_attendance` | Record and manage attendance |
| `fb_manage_database` | View submissions database |
| `fb_manage_forms` | Manage forms and templates |
| `fbm_manage_forms` | Access form builder |
| `fb_manage_emails` | Manage email templates and logs |
| `fb_manage_reports` | Access reports module |
| `fb_manage_settings` | Change plugin settings |
| `fb_manage_diagnostics` | Diagnostics and troubleshooting |
| `fb_manage_permissions` | Manage plugin capabilities |
| `fb_manage_theme` | Design & theme controls |
| `fbm_manage_events` | Manage events and tickets |
| `fb_view_sensitive` | View sensitive data unmasked |

### Requesting Access

Need additional access? Contact a site Administrator or email the security team at `security@pcclondon.uk` with justification. Changes are audited.

## RC3 Guardrails
- Default masked: all exports/detail views masked unless `fbm_view_sensitive`.
- Mutations gated: capability + nonce on every POST/GET action that mutates.
- SQL: prepared with strict whitelists for IN/ORDER/LIMIT.
- Tickets: KEK-backed HMAC; base64url; replay protection.
- Headers seam: no native `header()` in tests; use `fbm_send_headers`.
- Packaging: slug must be `foodbank-manager/`; guard checks in CI; compiled `.mo` not tracked in VCS.
- Stubs discipline: single WP stubs source; `FbmDieException` replaces `wp_die` in tests.

## 🔁 Coordinated Disclosure
- We will validate, develop a fix, and prepare a security release.
- We’ll credit reporters (if desired) after users have a reasonable time to update.

## 🧰 Supported Versions
- Latest **minor version** (e.g., v1.x) receives security patches.
- Older minors may receive backports at our discretion for severe issues.

## ✅ Hardening Checklist (for site owners)
- Run WordPress 6.x+ and PHP 8.1+
- Enforce **HTTPS** and strong admin passwords, enable **2FA**
- Configure **SMTP** and monitor **Diagnostics → Email**
- Set **FBM_KEK_BASE64** in `wp-config.php` (don’t commit secrets) If you previously set `PCC_FBM_KEK_BASE64` in `wp-config.php`, rename it to `FBM_KEK_BASE64`.
- Use **Diagnostics → Repair Capabilities** after role or plugin updates.
- Enable CAPTCHA and server‑side verification
- Keep your WordPress, themes, plugins, and this plugin **up to date**
