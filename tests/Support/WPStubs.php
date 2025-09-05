<?php
declare(strict_types=1);

/**
 * Deterministic WordPress stubs for unit tests.
 * Namespaced shims resolve BEFORE global WP functions used by namespaced plugin code.
 * Global fallbacks are GUARDed with function_exists() to avoid redeclare.
 */

/* ---------------------------
 * FBM\Admin stubs (menus, nonces, caps, redirects)
 * --------------------------- */
namespace FBM\Admin {
    function add_menu_page(...$args) {
        $GLOBALS['fbm_test_calls']['add_menu_page'][] = $args;
        return 'toplevel_page_fbm';
    }
    function add_submenu_page(...$args) {
        $GLOBALS['fbm_test_calls']['add_submenu_page'][] = $args;
        $slug = $args[4] ?? 'fbm_unknown'; // WP: 5th arg is slug
        return 'foodbank_page_' . $slug;
    }
    function check_admin_referer($action = -1, $name = '_wpnonce') {
        if (empty($_REQUEST[$name])) {
            throw new \RuntimeException('nonce');
        }
        return true;
    }
    function wp_verify_nonce($nonce, $action = -1) { return true; }
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) { return ''; }
    function current_user_can($cap) { return true; }
    function wp_safe_redirect($url, $status = 302) { $GLOBALS['fbm_redirect_to'] = (string)$url; return true; }
    function wp_die($message = '') { throw new \RuntimeException((string)$message ?: 'wp_die'); }
}

/* ---------------------------
 * FBM\Core stubs (screen, cron, options)
 * --------------------------- */
namespace FBM\Core {
    function get_current_screen() {
        $id = $GLOBALS['fbm_test_screen_id'] ?? null;
        if (!$id) return null;
        $o = new \stdClass(); $o->id = (string)$id; return $o;
    }
    function wp_next_scheduled($hook) { return time() + 300; }
    function is_admin() { return true; }
    function admin_url($path = '') { return '/wp-admin/' . ltrim((string)$path, '/'); }
    function get_option($name, $default = false) { return $GLOBALS['fbm_options'][$name] ?? $default; }
    function update_option($name, $value) { $GLOBALS['fbm_options'][$name] = $value; return true; }
    function add_option($name, $value) { if (!isset($GLOBALS['fbm_options'][$name])) { $GLOBALS['fbm_options'][$name] = $value; } return true; }
}

namespace FoodBankManager\Core {
    function get_current_screen() {
        $id = $GLOBALS['fbm_test_screen_id'] ?? null;
        if (!$id) return null;
        $o = new \stdClass(); $o->id = (string)$id; return $o;
    }
    function wp_next_scheduled($hook) { return time() + 300; }
    function is_admin() { return true; }
    function admin_url($path = '') { return '/wp-admin/' . ltrim((string)$path, '/'); }
    function get_option($name, $default = false) { return $GLOBALS['fbm_options'][$name] ?? $default; }
    function update_option($name, $value) { $GLOBALS['fbm_options'][$name] = $value; return true; }
    function add_option($name, $value) { if (!isset($GLOBALS['fbm_options'][$name])) { $GLOBALS['fbm_options'][$name] = $value; } return true; }
}

/* ---------------------------
 * FBM\Mail stubs (outbound)
 * --------------------------- */
namespace FBM\Mail {
    function wp_mail($to, $subject, $message, $headers = '', $attachments = []) { return true; }
}

/* ---------------------------
 * Guarded global fallbacks (only if tests call global functions)
 * --------------------------- */
namespace {
    if (!function_exists('check_admin_referer')) {
        function check_admin_referer($action = -1, $name = '_wpnonce') {
            if (empty($_REQUEST[$name])) {
                throw new \RuntimeException('nonce');
            }
            return true;
        }
    }
    if (!function_exists('wp_verify_nonce')) {
        function wp_verify_nonce($nonce, $action = -1) { return true; }
    }
    if (!function_exists('wp_nonce_field')) {
        function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) { return ''; }
    }
    if (!function_exists('sanitize_text_field')) {
        function sanitize_text_field($str) { return is_string($str) ? trim($str) : ''; }
    }
    if (!function_exists('sanitize_email')) {
        function sanitize_email($email) { return filter_var((string)$email, FILTER_SANITIZE_EMAIL); }
    }
    if (!function_exists('sanitize_key')) {
        function sanitize_key($key) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string)$key)); }
    }
    if (!function_exists('esc_html')) {
        function esc_html($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
    }
    if (!function_exists('esc_attr')) {
        function esc_attr($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
    }
    if (!function_exists('esc_url')) {
        function esc_url($url) { return filter_var((string)$url, FILTER_SANITIZE_URL); }
    }
    if (!function_exists('wp_kses_post')) {
        function wp_kses_post($html) { return (string)$html; }
    }
    if (!function_exists('__')) { function __($text, $domain = 'default') { return (string)$text; } }
    if (!function_exists('_e')) { function _e($text, $domain = 'default') { echo (string)$text; } }
    if (!function_exists('header')) {
        function header($string, $replace = true, $http_response_code = 0) {
            $GLOBALS['fbm_headers'][] = [$string, $replace, $http_response_code];
        }
    }
    if (!function_exists('add_shortcode')) {
        $GLOBALS['fbm_shortcodes'] = [];
        function add_shortcode($tag, $callback) { $GLOBALS['fbm_shortcodes'][$tag] = $callback; }
    }
    if (!function_exists('do_shortcode')) {
        function do_shortcode($content) {
            return preg_replace_callback('/\[(\w+)([^\]]*)\]/', function ($m) {
                $tag = $m[1];
                $atts = [];
                if (preg_match_all('/(\w+)="([^"]*)"/', $m[2], $am, PREG_SET_ORDER)) {
                    foreach ($am as $a) { $atts[$a[1]] = $a[2]; }
                }
                $cb = $GLOBALS['fbm_shortcodes'][$tag] ?? null;
                if (!$cb) return '';
                return call_user_func($cb, $atts, '', $tag);
            }, $content);
        }
    }
    if (!function_exists('wp_salt')) {
        function wp_salt($scheme = 'auth') { return 'testsalt'; }
    }
}
