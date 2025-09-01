# Security Policy — PCC FoodBank Manager

We take security seriously. Please follow the guidelines below for reporting vulnerabilities.

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
- Front‑end submission and dashboard flows
- Database migrations and encryption code

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
- Set **PCC_FBM_KEK_BASE64** in `wp-config.php` (don’t commit secrets)
- Enable CAPTCHA and server‑side verification
- Keep your WordPress, themes, plugins, and this plugin **up to date**
