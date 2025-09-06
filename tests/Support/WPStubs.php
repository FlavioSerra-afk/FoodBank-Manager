<?php declare(strict_types=1);

// Always-initialized globals
$GLOBALS['fbm_options'] = $GLOBALS['fbm_options'] ?? [];
$GLOBALS['fbm_test_trust_nonces'] = $GLOBALS['fbm_test_trust_nonces'] ?? true;

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
