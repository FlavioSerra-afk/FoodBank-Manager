<?php declare(strict_types=1);

// Always-initialized globals
$GLOBALS['fbm_options']        = $GLOBALS['fbm_options']        ?? [];
$GLOBALS['fbm_test_trust_nonces'] = $GLOBALS['fbm_test_trust_nonces'] ?? true;
$GLOBALS['fbm_transients']     = $GLOBALS['fbm_transients']     ?? [];
$GLOBALS['fbm_test_plugins']   = $GLOBALS['fbm_test_plugins']   ?? [];
// Globals used by tests to assert side-effects
$GLOBALS['fbm_active_plugins']  = $GLOBALS['fbm_active_plugins']  ?? [];
$GLOBALS['fbm_deactivated']     = $GLOBALS['fbm_deactivated']     ?? [];
$GLOBALS['fbm_deleted_plugins'] = $GLOBALS['fbm_deleted_plugins'] ?? [];
$GLOBALS['__last_redirect']     = $GLOBALS['__last_redirect']     ?? '';
// Back-compat aliases
$GLOBALS['fbm_test_deactivated'] =& $GLOBALS['fbm_deactivated'];
$GLOBALS['fbm_test_deleted']     =& $GLOBALS['fbm_deleted_plugins'];
$GLOBALS['fbm_test_redirect']    =& $GLOBALS['__last_redirect'];

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
if (!function_exists('current_user_can')) {
    function current_user_can($cap) {
        $caps = $GLOBALS['fbm_user_caps'] ?? [];
        if ($caps) {
            return !empty($caps[$cap]) || in_array($cap, $caps, true);
        }
        return true;
    }
}
if (!function_exists('get_plugins')) {
    function get_plugins() { return $GLOBALS['fbm_test_plugins']; }
}
if (!function_exists('is_plugin_active')) {
    function is_plugin_active($basename) {
        return in_array($basename, $GLOBALS['fbm_active_plugins'], true);
    }
}
if (!function_exists('deactivate_plugins')) {
    function deactivate_plugins($plugins) {
        foreach ((array)$plugins as $p) {
            $GLOBALS['fbm_deactivated'][] = $p;
            $GLOBALS['fbm_active_plugins'] = array_values(array_diff($GLOBALS['fbm_active_plugins'], [$p]));
        }
    }
}
if (!function_exists('delete_plugins')) {
    function delete_plugins($plugins) {
        foreach ((array)$plugins as $p) {
            $GLOBALS['fbm_deleted_plugins'][] = $p;
        }
        return true;
    }
}
if (!function_exists('wp_safe_redirect')) {
    function wp_safe_redirect($url) {
        $GLOBALS['__last_redirect'] = (string)$url;
        return true;
    }
}
if (!function_exists('wp_redirect')) {
    function wp_redirect($url) {
        $GLOBALS['__last_redirect'] = (string)$url;
        return true;
    }
}
if (!function_exists('wp_die')) {
    function wp_die($msg = '') {
        throw new RuntimeException('wp_die: ' . $msg);
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

// Reset helper
if (!function_exists('fbm_test_reset_globals')) {
    function fbm_test_reset_globals() {
        $GLOBALS['fbm_active_plugins']  = [];
        $GLOBALS['fbm_deactivated']     = [];
        $GLOBALS['fbm_deleted_plugins'] = [];
        // keep existing nonce & cap maps initialised by AX
    }
}

// ------- Basic escaping / sanitising
if (!function_exists('esc_attr')) {
    function esc_attr($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }
}
if (!function_exists('esc_html')) {
    function esc_html($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }
}
if (!function_exists('wp_kses_post')) {
    function wp_kses_post($s){ return (string)$s; }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($s){ return trim((string)$s); }
}
if (!function_exists('sanitize_key')) {
    function sanitize_key($s){ return preg_replace('/[^a-z0-9_\-]/i','', (string)$s); }
}
if (!function_exists('selected')) {
    function selected($a,$b,$echo=false){ $out = ($a==$b)?' selected="selected"':''; if($echo) echo $out; return $out; }
}
if (!function_exists('checked')) {
    function checked($a,$b=true,$echo=false){ $out = ($a==$b)?' checked="checked"':''; if($echo) echo $out; return $out; }
}

if (!function_exists('fbm_url_sanitize')) {
    function fbm_url_sanitize(string $url): string {
        $clean = filter_var($url, FILTER_SANITIZE_URL);
        return is_string($clean) ? $clean : $url;
    }
}

if (!function_exists('add_query_arg')) {
    // Accept add_query_arg( array $args, string $url='' )
    // OR add_query_arg( string $key, string|int $value, string $url='' )
    // Returns a string URL with encoded query args.
    function add_query_arg(...$args) {
        if (count($args) === 0) return '';
        // Normalise inputs
        if (is_array($args[0])) {
            $params = $args[0];
            $url = $args[1] ?? '';
        } else {
            $key = (string)$args[0];
            $val = $args[1] ?? '';
            $url = $args[2] ?? '';
            $params = [$key => $val];
        }
        $parts = parse_url($url);
        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        foreach ($params as $k => $v) {
            $query[$k] = $v;
        }
        $parts['query'] = http_build_query($query);
        // Rebuild
        $scheme   = isset($parts['scheme']) ? $parts['scheme'].'://' : '';
        $host     = $parts['host'] ?? '';
        $port     = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path     = $parts['path'] ?? '';
        $queryStr = $parts['query'] ? '?'.$parts['query'] : '';
        $frag     = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';
        return $scheme.$host.$port.$path.$queryStr.$frag;
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
    function admin_url($path = '') { return 'https://example.test/wp-admin/' . ltrim($path, '/'); }
}

if (!function_exists('plugins_url')) {
    function plugins_url($path = '') { return 'https://example.test/wp-content/plugins/' . ltrim($path, '/'); }
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
    function esc_url($s){ return (string)$s; }
}

if (!function_exists('esc_url_raw')) {
    function esc_url_raw(string $url, ?array $protocols = null): string {
        return fbm_url_sanitize($url);
    }
}

if (!function_exists('wp_nonce_url')) {
    function wp_nonce_url($url, $action = -1, $name = '_wpnonce') {
        $nonce = wp_create_nonce($action);
        return add_query_arg([$name => $nonce], $url);
    }
}
