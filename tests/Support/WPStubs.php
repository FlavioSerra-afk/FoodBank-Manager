<?php declare(strict_types=1);

// Always-initialized globals
$GLOBALS['fbm_options'] = $GLOBALS['fbm_options'] ?? [];
$GLOBALS['fbm_test_trust_nonces'] = $GLOBALS['fbm_test_trust_nonces'] ?? true;
$GLOBALS['fbm_transients'] = $GLOBALS['fbm_transients'] ?? [];
$GLOBALS['fbm_test_plugins'] = $GLOBALS['fbm_test_plugins'] ?? [];
$GLOBALS['fbm_test_deactivated'] = $GLOBALS['fbm_test_deactivated'] ?? [];
$GLOBALS['fbm_test_deleted'] = $GLOBALS['fbm_test_deleted'] ?? [];
$GLOBALS['fbm_test_redirect'] = $GLOBALS['fbm_test_redirect'] ?? '';

if (!function_exists('get_option')) {
    function get_option($name, $default = false) {
        $store = $GLOBALS['fbm_options'];
        return $store[$name] ?? $default;
    }
}
if (!function_exists('update_option')) {
    function update_option($name, $value) {
        $GLOBALS['fbm_options'][$name] = $value;
        return true;
    }
}
if (!function_exists('delete_option')) {
    function delete_option($name) { unset($GLOBALS['fbm_options'][$name]); return true; }
}
if (!function_exists('set_transient')) {
    function set_transient($key, $value, $expiration = 0) { $GLOBALS['fbm_transients'][$key] = $value; return true; }
}
if (!function_exists('get_transient')) {
    function get_transient($key) { return $GLOBALS['fbm_transients'][$key] ?? false; }
}
if (!function_exists('delete_transient')) {
    function delete_transient($key) { unset($GLOBALS['fbm_transients'][$key]); return true; }
}
if (!function_exists('get_plugins')) {
    function get_plugins() { return $GLOBALS['fbm_test_plugins']; }
}
if (!function_exists('deactivate_plugins')) {
    function deactivate_plugins($plugins) { $GLOBALS['fbm_test_deactivated'] = array_merge($GLOBALS['fbm_test_deactivated'], (array)$plugins); }
}
if (!function_exists('delete_plugins')) {
    function delete_plugins($plugins) { $GLOBALS['fbm_test_deleted'] = array_merge($GLOBALS['fbm_test_deleted'], (array)$plugins); }
}
if (!function_exists('wp_redirect')) {
    function wp_redirect($location, $status = 302) { $GLOBALS['fbm_test_redirect'] = $location; return true; }
}
if (!function_exists('add_query_arg')) {
    function add_query_arg($key, $value = false, $url = '') {
        if (is_array($key)) {
            $params = $key;
            $url    = (string) $value;
        } else {
            $params = [$key => $value];
        }
        $base = (string) $url;
        $sep  = str_contains($base, '?') ? '&' : '?';
        return $base . ($params ? $sep . http_build_query($params) : '');
    }
}
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) { return hash('sha256', 'fbm-' . $action); }
}
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($n, $a = -1) {
        return !empty($GLOBALS['fbm_test_trust_nonces'])
            ? 1
            : (hash_equals($n ?? '', wp_create_nonce($a)) ? 1 : false);
    }
}
if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
        $n = wp_create_nonce($action);
        $f = '<input type="hidden" name="' . $name . '" value="' . $n . '" />';
        if ($echo) {
            echo $f;
        }
        return $f;
    }
}
if (!function_exists('check_admin_referer')) {
    function check_admin_referer($action = -1, $name = '_wpnonce') {
        $n = $_REQUEST[$name] ?? '';
        if (!wp_verify_nonce($n, $action)) {
            throw new RuntimeException('bad nonce');
        }
        return true;
    }
}
if (!function_exists('fbm_test_trust_nonces')) {
    function fbm_test_trust_nonces(bool $trust): void { $GLOBALS['fbm_test_trust_nonces'] = $trust; }
}
function fbm_test_set_request_nonce(string $action = 'fbm', string $field = '_wpnonce'): void {
    $_REQUEST[$field] = wp_create_nonce($action);
    $_POST[$field] = $_REQUEST[$field];
}
