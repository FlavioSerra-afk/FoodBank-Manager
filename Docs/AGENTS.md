# Agents Playbook — How to work on FoodBank Manager (Codex/Copilot)

## Before you code
Read:
- [Docs/PRD-foodbank-manager.md](Docs/PRD-foodbank-manager.md)
- [Docs/Architecture.md](Docs/Architecture.md)
- [Docs/SECURITY.md](Docs/SECURITY.md)
- [Docs/CONTRIBUTING.md](Docs/CONTRIBUTING.md)
- [Docs/ISSUES-foodbank-manager.md](Docs/ISSUES-foodbank-manager.md)

## Workflow (the "Wave" pattern)
- Work in small, shippable waves focused on one feature or area.
- Always run QA:
  ```bash
  composer lint && composer phpcs && composer phpstan && composer test
  ```
- For releases:
  ```bash
  composer build:zip && composer release:*
  ```

## Checklists (copy/paste)
**Security checklist:** Nonces on POST, `permission_callback` on REST, `$wpdb->prepare()` everywhere, sanitize input, escape output, mask PII in lists/exports, never log plaintext PII, use UTC timestamps.

**PHPCS checklist:** `esc_html`/`esc_attr`/`esc_url`, `wp_kses_post` for safe HTML, i18n `foodbank-manager`, justified one-line ignores only.

**PHPStan checklist:** keep bootstrap constants; return types and docblocks on public APIs.

## Capabilities & roles
- Central caps list (copy from `includes/Auth/Capabilities.php`).
- Administrator guarantee; role mapping via Permissions tab; per-user overrides via `fbm_user_caps`.

## Where to put things
- Admin pages: `includes/Admin/*Page.php` + `templates/admin/*`.
- Shortcodes: `includes/Shortcodes/*` + `templates/public/*`.
- Queries: `includes/*/Repo.php`.
- REST: `includes/Rest/*Controller.php`.
- Security helpers: `includes/Security/*`.
- Theming: `includes/UI/Theme.php`, `assets/css/theme-*.css`.
- Exports: `includes/Exports/*`.
- QA config: `phpcs.xml`, `phpstan.neon`.

## Conventional commits (examples)
- `feat(attendance): timeline actions (void/unvoid, notes) with audit`
- `fix(bootstrap): no-fatal activation with PSR-4 fallback`
- `chore(cs): PHPCS to 0 errors in templates`
- `docs(architecture): add data model diagram`

## Execution rules for Codex
- Never widen scope: one wave at a time.
- If many files fail PHPCS, prefer templates → handlers → services order.
- Don't introduce optional deps without graceful fallback & notices.
- Keep Administrator always fully capable.

## Runbooks (snippets)
**PHPCS pass (templates only):**
```bash
composer phpcs -- templates || true
```

**Full QA loop:**
```bash
composer phpcs && composer phpstan -- --memory-limit=1G && composer test
```

