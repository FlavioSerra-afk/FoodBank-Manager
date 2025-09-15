Docs-Revision: 2025-09-05 (v1.2.9 QA sweep)
# Contributing to PCC FoodBank Manager

Thanks for helping build a secure and accessible tool for the community!  
This document explains how to set up your environment, coding standards, and the PR process.

Releases are manual. Always run a version bump with:
  composer ver:bump          # patch
  composer ver:bump:minor
  composer ver:bump:major
CI and a pre-commit hook enforce that version files are updated.

## Table of contents
- [Project goals](#project-goals)
- [Code of Conduct](#code-of-conduct)
- [Tech stack](#tech-stack)
- [Local development](#local-development)
- [Configuration & secrets](#configuration--secrets)
- [Coding standards](#coding-standards)
- [Commits & branches](#commits--branches)
- [Testing](#testing)
- [Security expectations](#security-expectations)
- [i18n & a11y](#i18n--a11y)
- [Docs & PR checklist](#docs--pr-checklist)
- [Releases](#releases)
- [How-tos](#how-tos)
- [Security reporting](#security-reporting)

## Project goals
- Secure, mobile-first forms and dashboards
- Field-level encryption at rest
- Attendance tracking with QR
- GDPR-aligned consent, SAR, and retention tooling

Refer to the PRD at `Docs/PRD-pcc-foodbank-manager.md`.

## Code of Conduct
We recommend adopting the [Contributor Covenant](https://www.contributor-covenant.org/) (add `CODE_OF_CONDUCT.md`).

## Tech stack
- WordPress 6.x+, PHP 8.1+
- PHP libsodium (AEAD XChaCha20-Poly1305)
- Composer for PHP deps; PHPCS (WordPress), PHPStan
- Action Scheduler (bundled)
- Optional: Node 18+ & npm for asset builds (if/when needed)

## Local development
1. Clone the repo into your WordPress `wp-content/plugins/` folder (or use Docker/wp-env).
2. Install PHP deps:
   ```bash
   composer install
   ```
3. (Optional) Install JS deps for builder/dashboard assets:
   ```bash
   npm install
   ```
4. Activate the plugin in WP Admin.

### Using wp-env (Docker) — optional
```bash
npm -g i @wordpress/env
wp-env start
# WordPress available at http://localhost:8888
```

## Configuration & secrets
- **Encryption KEK**: Set via environment or `wp-config.php`:
  ```php
  // wp-config.php
  define('FBM_KEK_BASE64', 'base64-encoded-32-byte-key');
  ```
  Do **not** commit keys. Rotate via admin tool (see PRD §10.2).

- **SMTP**: Configure your site-wide SMTP plugin (e.g., WP Mail SMTP).

- **CAPTCHA**: Add Cloudflare Turnstile or reCAPTCHA keys in plugin Settings.

## Coding standards
- **PHP**: WordPress Coding Standards via PHPCS.
- **Static analysis**: PHPStan (level 8 where feasible).
- **JS/TS** (if present): ESLint + Prettier.
- **Security**: Sanitize, validate, and escape; capability checks; nonces on all actions.

Run linters:
```bash
composer phpcs
composer phpstan
npm run lint
```

## Commits & branches
- **Conventional Commits**:
  - `feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `perf:`, `test:`
- **Branches**: `feat/<slug>`, `fix/<slug>`, `chore/<slug>`
- Link issues in commit messages or PR description (`Closes #123`).

## Testing
- **Unit**: PHPUnit with WP core test suite.
- **Integration**: REST endpoints, DB writes, encryption/decryption.
- **Manual**: Forms, uploads, emails, dashboard filters, attendance scan/manual.
- **A11y**: Keyboard nav, labels, color contrast, screen reader checks.
- **AttendanceRepo**: unit tests cover check-in, no-show, void/unvoid, timeline queries, and SQL injection edges.

Example:
```bash
composer test
```

## Security expectations
- No PII in logs (use body hashes).
- Sensitive fields go into the encrypted blob; never index plaintext.
- All POST/REST actions require nonces + capability checks.
- File uploads: strict MIME/size validation, randomized names, no direct web execution.
- Rate-limit public endpoints; verify CAPTCHA server-side.
- Use prepared statements with `$wpdb`.
- AttendanceRepo must use strict `$wpdb->prepare()` placeholders and return masked data unless `fb_view_sensitive` is granted.

## i18n & a11y
- Wrap user-facing strings in `__()`/`_e()` and update the `.pot` (`npm run make-pot` or WP-CLI).
- Ensure all controls have labels/ARIA; provide error summaries and focus management.
- Test mobile: small screens and touch targets.

## Docs & PR checklist
Every PR should:
- Reference PRD section(s)
- Include tests (or justify why not)
- Update docs if behavior changes
- Pass CI (lint, static analysis, tests)

**PR checklist:**
- [ ] Linked Issue & Milestone set
- [ ] Security reviewed (inputs, caps, nonces, uploads)
- [ ] Encryption path reviewed (if touching PII)
- [ ] A11y reviewed
- [ ] i18n updated (.pot)
- [ ] Performance checked (queries indexed/paginated)
- [ ] Docs updated (README/PRD/Guides)

## Releases
- Semantic Versioning (SemVer).
- Tag releases: `vX.Y.Z`.
- Update `CHANGELOG.md` (keepers: Features, Fixes, Security, Docs).

## How-tos
### Add a new field type
1. Extend field registry with render, validate, sanitize, and JSON schema.
2. Add builder UI component + server render template.
3. Add tests, docs, and i18n strings.

### Add a REST endpoint
1. Register route under `pcc-fb/v1`.
2. Add capability + nonce checks; rate-limit if public.
3. Validate inputs and return typed responses.
4. Tests & docs.

### Add a report
1. Define filters and SQL with indexes.
2. Build server-side pagination/export.
3. Add a11y-friendly UI (tables/charts).

## Security reporting
If you believe you’ve found a vulnerability, **do not open a public issue**.  
Email the maintainers (replace with your address) or use GitHub’s private security advisories.  
We aim to acknowledge within 2 working days.
