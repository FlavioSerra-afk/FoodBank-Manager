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
        return \check_admin_referer($action, $name);
    }
    function wp_verify_nonce($nonce, $action = -1) { return \wp_verify_nonce($nonce, $action); }
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) { return \wp_nonce_field($action, $name, $referer, $echo); }
    function current_user_can($cap) { return \current_user_can($cap); }
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
    // Always-initialized globals
    $GLOBALS['fbm_user_caps']   = $GLOBALS['fbm_user_caps']   ?? [];
    $GLOBALS['fbm_transients']  = $GLOBALS['fbm_transients']  ?? [];
    $GLOBALS['fbm_options']     = $GLOBALS['fbm_options']     ?? [];
    $GLOBALS['fbm_test_calls']  = $GLOBALS['fbm_test_calls']  ?? ['add_menu_page'=>[], 'add_submenu_page'=>[]];

    // === Nonce + request ===
    $GLOBALS['fbm_test_nonce_secret'] = 'fbm-test-secret';
    $GLOBALS['fbm_test_trust_nonces'] = true; // default ON for unit tests

    function wp_create_nonce($action = -1) {
        // deterministic for tests
        return hash_hmac('sha256', (string)$action, $GLOBALS['fbm_test_nonce_secret']);
    }

    function wp_verify_nonce($nonce, $action = -1) {
        if (!empty($GLOBALS['fbm_test_trust_nonces'])) return 1;
        return hash_equals($nonce ?? '', wp_create_nonce($action)) ? 1 : false;
    }

    function check_admin_referer($action = -1, $name = '_wpnonce') {
        $nonce = $_REQUEST[$name] ?? '';
        if (wp_verify_nonce($nonce, $action)) return 1;
        // mirror WP behavior: die with message; in tests we throw to keep it visible
        throw new \RuntimeException('bad nonce');
    }

    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
        $nonce = wp_create_nonce($action);
        $html = '<input type="hidden" name="'.esc_attr($name).'" value="'.esc_attr($nonce).'">';
        if ($echo) { echo $html; return ''; }
        return $html;
    }

    // === Minimal sanitizers (guarded) ===
    if (!function_exists('sanitize_text_field')) {
        function sanitize_text_field($str) { return is_string($str) ? trim($str) : $str; }
    }
    if (!function_exists('wp_unslash')) {
        function wp_unslash($value) { return $value; }
    }

    // Cap check (reads from simulated map)
    if (!function_exists('current_user_can')) {
        function current_user_can($cap) {
            $caps = $GLOBALS['fbm_user_caps'] ?? [];
            return !empty($caps[(string)$cap]);
        }
    }

    // Menu registration capture (for assertions)
    if (!function_exists('add_menu_page')) {
        function add_menu_page($page_title, $menu_title, $cap, $slug, $cb = null, $icon = null, $pos = null) {
            $GLOBALS['fbm_test_calls']['add_menu_page'][] = compact('page_title','menu_title','cap','slug');
            return $slug;
        }
    }
    if (!function_exists('add_submenu_page')) {
        function add_submenu_page($parent_slug, $page_title, $menu_title, $cap, $slug, $cb = null) {
            $GLOBALS['fbm_test_calls']['add_submenu_page'][] = compact('parent_slug','page_title','menu_title','cap','slug');
            return $slug;
        }
    }

    // Minimal get_role so Capabilities::ensure_for_admin() works in tests
    if (!function_exists('get_role')) {
        function get_role($role) {
            return new class {
                /** @var array<string,bool> */
                public array $caps = [];
                public function add_cap($cap) { $this->caps[$cap] = true; }
                public function has_cap($cap) { return isset($this->caps[$cap]); }
            };
        }
    }

    // Transients (null-safe)
    if (!function_exists('set_transient')) {
        function set_transient($key, $value, $expiration = 0) { $GLOBALS['fbm_transients'][(string)$key] = $value; return true; }
    }
    if (!function_exists('get_transient')) {
        function get_transient($key) { return $GLOBALS['fbm_transients'][(string)$key] ?? false; }
    }
    if (!function_exists('delete_transient')) {
        function delete_transient($key) { unset($GLOBALS['fbm_transients'][(string)$key]); return true; }
    }

    // Options (null-safe)
    if (!function_exists('get_option')) {
        function get_option($name, $default = false) { return $GLOBALS['fbm_options'][(string)$name] ?? $default; }
    }
    if (!function_exists('update_option')) {
        function update_option($name, $value) { $GLOBALS['fbm_options'][(string)$name] = $value; return true; }
    }
    if (!function_exists('delete_option')) {
        function delete_option($name) { unset($GLOBALS['fbm_options'][(string)$name]); return true; }
    }

    if (!function_exists('user_can')) {
        function user_can($user, $cap) { return current_user_can($cap); }
    }
    if (!function_exists('manage_options')) {
        // Not a WP function, but we’ll simulate by setting a flag in tests via current_user_can('manage_options')
    }

    // Admin/context
    if (!function_exists('is_admin')) {
        function is_admin() { return true; }
    }
    if (!function_exists('get_current_screen')) {
        function get_current_screen() {
            $id = $GLOBALS['fbm_test_screen_id'] ?? null;
            return (object) ['id' => $id];
        }
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
    if (!function_exists('esc_html__')) {
        function esc_html__($text, $domain = 'default') { return (string)$text; }
    }
    if (!function_exists('esc_attr')) {
        function esc_attr($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
    }
    if (!function_exists('esc_url')) {
        function esc_url($url) { return (string) $url; }
    }
    if (!function_exists('esc_url_raw')) {
        function esc_url_raw($url) { return trim((string)$url); }
    }
    if (!function_exists('wp_kses_post')) {
        function wp_kses_post($html) { return (string)$html; }
    }
    if (!function_exists('settings_errors')) {
        function settings_errors($setting = '', $sanitize = false, $type = '') { return ''; }
    }
    if (!function_exists('__')) { function __($text, $domain = 'default') { return (string)$text; } }
    if (!function_exists('_e')) { function _e($text, $domain = 'default') { echo (string)$text; } }
    if (!function_exists('header')) {
        function header($string, $replace = true, $http_response_code = 0) {
            $GLOBALS['fbm_headers'][] = [$string, $replace, $http_response_code];
        }
    }
    if (!function_exists('wp_safe_redirect')) {
        function wp_safe_redirect($loc, $status = 302) { $GLOBALS['fbm_redirect_to'] = (string) $loc; return true; }
    }
    if (!function_exists('wp_die')) {
        function wp_die($message = '') { throw new \RuntimeException((string)$message ?: 'wp_die'); }
    }
    if (!function_exists('submit_button')) {
        function submit_button($text = '', $type = 'primary', $name = 'submit', $wrap = true) {
            echo '<button type="submit" name="'.esc_attr($name).'">'.esc_html($text).'</button>';
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
    if (!function_exists('get_current_user_id')) {
        function get_current_user_id() { return 1; }
    }
    if (!function_exists('absint')) {
        function absint($maybeint) {
            return abs((int) $maybeint);
        }
    }
    if (!function_exists('shortcode_atts')) {
        function shortcode_atts($pairs, $atts, $shortcode = '') {
            $atts = (array) $atts;
            return array_merge($pairs, array_intersect_key($atts, $pairs));
        }
    }
    if (!function_exists('wp_parse_args')) {
        function wp_parse_args($args, $defaults = array()) {
            if (is_object($args)) {
                $args = get_object_vars($args);
            } elseif (is_string($args)) {
                parse_str($args, $args);
            }
            if (!is_array($args)) {
                $args = array();
            }
            return array_merge($defaults, $args);
        }
    }
    if (!function_exists('add_query_arg')) {
        function add_query_arg(...$args) {
            if (isset($args[0]) && is_array($args[0])) {
                $params = $args[0];
                $url    = $args[1] ?? '';
            } else {
                $params = array($args[0] => $args[1] ?? null);
                $url    = $args[2] ?? '';
            }
            $parts = parse_url((string) $url);
            $query = array();
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
            }
            foreach ($params as $k => $v) {
                $query[$k] = $v;
            }
            $parts['query'] = http_build_query($query);
            $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
            $host   = $parts['host'] ?? '';
            $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
            $path   = $parts['path'] ?? '';
            $query_str = $parts['query'] ? '?' . $parts['query'] : '';
            return $scheme . $host . $port . $path . $query_str;
        }
    }
    if (!function_exists('is_email')) {
        function is_email($email) {
            return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
        }
    }
    if (!function_exists('wp_json_encode')) {
        function wp_json_encode($data, $options = 0, $depth = 512) {
            return json_encode($data, $options | JSON_UNESCAPED_UNICODE, $depth);
        }
    }

    function fbm_test_trust_nonces(bool $trust): void {
        $GLOBALS['fbm_test_trust_nonces'] = $trust;
    }
    function fbm_test_set_request_nonce(int|string $action = -1, string $field = '_wpnonce'): void {
        $_REQUEST[$field] = wp_create_nonce($action);
        $_POST[$field]    = $_REQUEST[$field];
    }
}

namespace FBM\Admin {
    function absint($maybeint) { return \absint($maybeint); }
    function add_query_arg(...$args) { return \add_query_arg(...$args); }
}

namespace FoodBankManager\Admin {
    function absint($maybeint) { return \absint($maybeint); }
    function add_query_arg(...$args) { return \add_query_arg(...$args); }
    function add_menu_page(...$args) { return \FBM\Admin\add_menu_page(...$args); }
    function add_submenu_page(...$args) { return \FBM\Admin\add_submenu_page(...$args); }
}

namespace FoodBankManager\Attendance {
    function wp_salt($scheme = 'auth') { return \wp_salt($scheme); }
}

namespace FoodBankManager\Exports {
    function headers_sent() { return false; }
    function header($string, $replace = true, $http_response_code = 0) { $GLOBALS['fbm_headers'][] = [$string, $replace, $http_response_code]; }
}
