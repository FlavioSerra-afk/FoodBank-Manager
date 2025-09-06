<?php declare(strict_types=1);

// Always-initialized globals
$GLOBALS['fbm_options'] = $GLOBALS['fbm_options'] ?? [];

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
    function wp_create_nonce($action = -1) { return 'nonce'; }
}
if (!function_exists('fbm_test_set_request_nonce')) {
    function fbm_test_set_request_nonce($action = -1, $field = '_wpnonce'): void {
        $_REQUEST[$field] = wp_create_nonce($action);
        $_POST[$field]    = $_REQUEST[$field];
    }
}
