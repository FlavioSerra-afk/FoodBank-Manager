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
if (!function_exists('wp_salt')) {
    function wp_salt($scheme = 'auth') { return 'fbm-salt'; }
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
if (!function_exists('fbm_test_trust_nonces')) {
    function fbm_test_trust_nonces(bool $trust): void { $GLOBALS['fbm_test_trust_nonces'] = $trust; }
}
function fbm_test_set_request_nonce(string $action = 'fbm', string $field = '_wpnonce'): void {
    $_REQUEST[$field] = wp_create_nonce($action);
    $_POST[$field] = $_REQUEST[$field];
}

if (!function_exists('fbm_url_sanitize')) {
    function fbm_url_sanitize(string $url): string {
        $clean = filter_var($url, FILTER_SANITIZE_URL);
        return is_string($clean) ? $clean : $url;
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg(...$args) {
        $url = '';
        $params = [];
        $argc = count($args);
        if ($argc === 1) {
            $params = is_array($args[0]) ? $args[0] : [$args[0] => null];
        } elseif ($argc === 2) {
            if (is_array($args[0])) {
                $params = $args[0];
                $url = (string) $args[1];
            } else {
                $params = [$args[0] => $args[1]];
            }
        } elseif ($argc >= 3) {
            $params = is_array($args[0]) ? $args[0] : [$args[0] => $args[1]];
            $url = (string) $args[2];
        }

        $fragment = '';
        if (false !== ($p = strpos($url, '#'))) {
            $fragment = substr($url, $p);
            $url = substr($url, 0, $p);
        }

        $parts = parse_url($url);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host   = $parts['host'] ?? '';
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path   = $parts['path'] ?? '';

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        foreach ($params as $k => $v) {
            $query[$k] = $v;
        }
        $query = array_filter($query, static fn($v) => $v !== null);
        $queryString = http_build_query($query);

        $base = $scheme . $host . $port . $path;
        if ($queryString !== '') {
            $base .= '?' . $queryString;
        }
        return fbm_url_sanitize($base . $fragment);
    }
}

if (!function_exists('remove_query_arg')) {
    function remove_query_arg($keys, string $url = ''): string {
        $keys = (array) $keys;
        $fragment = '';
        if (false !== ($p = strpos($url, '#'))) {
            $fragment = substr($url, $p);
            $url = substr($url, 0, $p);
        }

        $parts = parse_url($url);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host   = $parts['host'] ?? '';
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path   = $parts['path'] ?? '';

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        foreach ($keys as $k) {
            unset($query[$k]);
        }
        $queryString = http_build_query($query);

        $base = $scheme . $host . $port . $path;
        if ($queryString !== '') {
            $base .= '?' . $queryString;
        }
        return fbm_url_sanitize($base . $fragment);
    }
}

if (!function_exists('admin_url')) {
    function admin_url(string $path = ''): string {
        return fbm_url_sanitize('https://example.test/wp-admin/' . ltrim($path, '/'));
    }
}

if (!function_exists('network_admin_url')) {
    function network_admin_url(string $path = ''): string {
        return admin_url($path);
    }
}

if (!function_exists('self_admin_url')) {
    function self_admin_url(string $path = ''): string {
        return admin_url($path);
    }
}

if (!function_exists('esc_url')) {
    function esc_url(string $url, ?array $protocols = null, string $context = ''): string {
        return fbm_url_sanitize($url);
    }
}

if (!function_exists('esc_url_raw')) {
    function esc_url_raw(string $url, ?array $protocols = null): string {
        return fbm_url_sanitize($url);
    }
}

if (!function_exists('wp_nonce_url')) {
    function wp_nonce_url(string $url, $action, string $name = '_wpnonce'): string {
        return add_query_arg($name, wp_create_nonce($action), $url);
    }
}
