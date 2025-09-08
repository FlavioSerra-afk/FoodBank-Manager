# A3 – Complete WP Stubs Coverage

## Added helpers
- `wp_nonce_field`
- `wp_verify_nonce`
- `wp_create_nonce`
- `admin_url`
- `plugins_url`
- `site_url`
- `add_query_arg`
- `remove_query_arg`
- `wp_nonce_url`
- `current_time`
- `wp_date`
- `sanitize_title`
- `esc_js`
- `checked`
- `selected`
- `shortcode_atts`
- `wp_enqueue_style`
- `wp_enqueue_script`
- `wp_mail`

## Die handling
`wp_die()` now throws `FbmDieException`, a named and serializable exception, allowing tests to expect application exits without anonymous classes.
