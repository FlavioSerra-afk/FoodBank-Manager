Docs-Revision: 2025-09-04 (Wave v1.2.2 – Frontend Dashboard P3)
# Local Development

This project supports two easy setups.

## Option A: @wordpress/env (zero-config)
Requirements: Node 18+

```bash
npm i -g @wordpress/env
npx @wordpress/env start
```

Admin URL: <http://localhost:8889/wp-admin>

DB resets:

```bash
npx @wordpress/env destroy && npx @wordpress/env start
```

## Option B: WP-CLI + Docker (quick sketch)

Use `wordpress:6.8-php8.1` and `mysql:8` containers, mount `./foodbank-manager` into `/var/www/html/wp-content/plugins/`.

Run `wp core install`, then `wp plugin activate foodbank-manager`.

Admin pages render inside `.fbm-admin`; when tweaking CSS, enqueue only `assets/css/admin.css` on FoodBank Manager screens.

### Secrets / Keys

Set `FBM_KEK_BASE64` in `wp-config.php` (base64-encoded key). Example (DEMO ONLY):

```php
if ( getenv('FBM_KEK_BASE64') ) {
    define('FBM_KEK_BASE64', getenv('FBM_KEK_BASE64'));
}
```

Never commit real keys. See `.env.example`.

Use wp-admin → FoodBank → Diagnostics to verify environment checks, send a test email, or repair capabilities.

### QA one-liners

```bash
composer phpcs && composer phpstan -- --memory-limit=1G && composer test
composer build:zip
```
