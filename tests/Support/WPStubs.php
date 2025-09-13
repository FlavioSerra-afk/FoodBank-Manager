<?php declare(strict_types=1);

namespace {

if (defined('FBM_WPSTUBS_LOADED')) {
    return;
}
define('FBM_WPSTUBS_LOADED', true);

use Tests\Support\Exceptions\FbmDieException;

// When running static analysis, load the official WordPress stubs instead of
// our test implementations to avoid function redeclarations and maintain
// accurate signatures.
if (defined('PHPSTAN_RUNNING') || defined('__PHPSTAN_RUNNING__')) {
    require_once __DIR__ . '/../../vendor/php-stubs/wordpress-stubs/wordpress-stubs.php';
    // Provide signatures for functions used with extended parameters.
    if (false) {
        function wp_die($message = '', $title = '', $args = []) {}
        function wp_safe_redirect($location, int $status = 302) {}
        function add_action($hook, $callback, int $priority = 10, int $accepted_args = 1) {}
        function add_filter($hook, $callback, int $priority = 10, int $accepted_args = 1) {}
        function wp_register_style($handle, $src = '', array $deps = [], $ver = false, $media = 'all') {}
        function wp_enqueue_script($handle, $src = '', array $deps = [], $ver = false, $in_footer = false) {}
        function wp_json_encode($data, $options = 0, $depth = 512) {}
        function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {}
        class WP_REST_Request {
            public function __construct(string $method = 'GET', string $route = '') {}
            /** @return mixed */
            public function get_param(string $key) {}
            /** @return void */
            public function set_param(string $key, $value) {}
            /** @return string|null */
            public function get_header(string $key) {}
            /** @return void */
            public function set_header(string $key, $value) {}
            /** @return array */
            public function get_file_params() {}
        }
        class WP_REST_Response {
            /** @param array $data */
            public function __construct(array $data = array(), int $status = 200) {}
            /** @return array */
            public function get_data() {}
            public function get_status(): int {}
        }
    }
    return;
}

if (!defined('ABSPATH') && getenv('FBM_SKIP_ABSPATH') !== '1') {
    define('ABSPATH', __DIR__ . '/../../');
}

// Options
$GLOBALS['fbm_options']    = $GLOBALS['fbm_options']    ?? [];
$GLOBALS['fbm_transients'] = $GLOBALS['fbm_transients'] ?? [];
$GLOBALS['fbm_templates']  = $GLOBALS['fbm_templates']  ?? [];
$GLOBALS['fbm_posts'] = $GLOBALS['fbm_posts'] ?? [];
$GLOBALS['fbm_post_meta'] = $GLOBALS['fbm_post_meta'] ?? [];
$GLOBALS['fbm_registered_post_types'] = $GLOBALS['fbm_registered_post_types'] ?? [];
$GLOBALS['fbm_wp_mail_result'] = $GLOBALS['fbm_wp_mail_result'] ?? true;
$GLOBALS['fbm_site_options'] = $GLOBALS['fbm_site_options'] ?? [];
$GLOBALS['fbm_is_multisite'] = $GLOBALS['fbm_is_multisite'] ?? false;
$GLOBALS['fbm_sites'] = $GLOBALS['fbm_sites'] ?? [];
$GLOBALS['fbm_current_blog'] = $GLOBALS['fbm_current_blog'] ?? 1;
if (!function_exists('get_option')) {
  function get_option($name, $default=false){
    $blog = $GLOBALS['fbm_current_blog'] ?? 1;
    return $GLOBALS['fbm_options'][$blog][$name] ?? ($GLOBALS['fbm_options'][$name] ?? $default);
  }
}
if (!function_exists('update_option')){
  function update_option($name, $value, $autoload = true){
    $blog = $GLOBALS['fbm_current_blog'] ?? 1;
    $GLOBALS['fbm_options'][$blog][$name] = $value;
    $GLOBALS['fbm_options'][$name]       = $value;
    return true;
  }
}
if (!function_exists('delete_option')){
  function delete_option($name){
    $blog = $GLOBALS['fbm_current_blog'] ?? 1;
    unset($GLOBALS['fbm_options'][$blog][$name], $GLOBALS['fbm_options'][$name]);
    return true;
  }
}
if (!function_exists('set_transient')) {
  function set_transient($key, $value, $expiration = 0){
    $blog = $GLOBALS['fbm_current_blog'] ?? 1;
    $GLOBALS['fbm_transients'][$blog][$key] = $value;
    $GLOBALS['fbm_transients'][$key]        = $value;
    update_option('_transient_timeout_' . $key, time() + (int) $expiration);
    return true;
  }
}
if (!function_exists('get_transient')) {
  function get_transient($key){
    $blog = $GLOBALS['fbm_current_blog'] ?? 1;
    return $GLOBALS['fbm_transients'][$blog][$key] ?? ($GLOBALS['fbm_transients'][$key] ?? false);
  }
}
if (!function_exists('delete_transient')) {
  function delete_transient($key){
    $blog = $GLOBALS['fbm_current_blog'] ?? 1;
    unset($GLOBALS['fbm_transients'][$blog][$key], $GLOBALS['fbm_transients'][$key]);
    delete_option('_transient_timeout_' . $key);
    return true;
  }
}
if (!function_exists('is_multisite')) {
  function is_multisite(): bool { return (bool) ($GLOBALS['fbm_is_multisite'] ?? false); }
}
if (!function_exists('get_site_option')) {
  function get_site_option($name, $default=false){ return $GLOBALS['fbm_site_options'][$name] ?? $default; }
}
if (!function_exists('update_site_option')) {
  function update_site_option($name, $value){ $GLOBALS['fbm_site_options'][$name] = $value; return true; }
}
if (!function_exists('delete_site_option')) {
  function delete_site_option($name){ unset($GLOBALS['fbm_site_options'][$name]); return true; }
}
if (!function_exists('get_sites')) {
  function get_sites($args=array()){ return $GLOBALS['fbm_sites'] ?? []; }
}
if (!function_exists('switch_to_blog')) {
  function switch_to_blog($blog_id){
    $GLOBALS['fbm_current_blog'] = (int) $blog_id;
    $GLOBALS['fbm_switched_to'][] = (int) $blog_id;
  }
}
if (!function_exists('restore_current_blog')) {
  function restore_current_blog(){ $GLOBALS['fbm_current_blog'] = 1; }
}
if (!function_exists('is_plugin_active_for_network')) {
  function is_plugin_active_for_network($basename){
    return in_array($basename, (array)($GLOBALS['fbm_network_active_plugins'] ?? []), true);
  }
}
if (!function_exists('is_super_admin')) {
  function is_super_admin($user_id=null){ return current_user_can('manage_options'); }
}

// Database stub with prefix
global $wpdb;
if (!isset($wpdb) || !is_object($wpdb)) {
  $wpdb = new \stdClass();
}
if (empty($wpdb->prefix)) {
  $wpdb->prefix = 'wp_';
}
$wpdb->options   = $wpdb->options ?? 'wp_options';
$wpdb->sitemeta  = $wpdb->sitemeta ?? 'wp_sitemeta';
$wpdb->query = $wpdb->query ?? function (string $sql) {
    if (preg_match("/DELETE FROM `[^`]+` WHERE option_name LIKE '([^']+)'/", $sql, $m)) {
        $prefix = str_replace('%', '', $m[1]);
        $blog   = $GLOBALS['fbm_current_blog'] ?? 1;
        foreach (($GLOBALS['fbm_options'][$blog] ?? []) as $name => $_) {
            if (strpos($name, $prefix) === 0) {
                unset($GLOBALS['fbm_options'][$blog][$name], $GLOBALS['fbm_options'][$name]);
            }
        }
    } elseif (preg_match("/DELETE FROM `[^`]+` WHERE meta_key LIKE '([^']+)'/", $sql, $m)) {
        $prefix = str_replace('%', '', $m[1]);
        foreach ($GLOBALS['fbm_site_options'] as $name => $_) {
            if (strpos($name, $prefix) === 0) {
                unset($GLOBALS['fbm_site_options'][$name]);
            }
        }
    }
    return true;
};

if (!defined('ARRAY_A')) define('ARRAY_A', 'ARRAY_A');
// Plugins API (and tracking globals)
$GLOBALS['fbm_plugins']        = $GLOBALS['fbm_plugins']        ?? [];
$GLOBALS['fbm_active_plugins']  = $GLOBALS['fbm_active_plugins']  ?? [];
$GLOBALS['fbm_deactivated']     = $GLOBALS['fbm_deactivated']     ?? [];
$GLOBALS['fbm_deleted_plugins'] = $GLOBALS['fbm_deleted_plugins'] ?? [];
$GLOBALS['__deactivated_plugins'] = $GLOBALS['__deactivated_plugins'] ?? [];
$GLOBALS['__last_redirect']     = $GLOBALS['__last_redirect']     ?? null;
$GLOBALS['fbm_test_plugins']    =& $GLOBALS['fbm_plugins'];
$GLOBALS['fbm_test_deactivated'] =& $GLOBALS['fbm_deactivated'];
$GLOBALS['fbm_test_deleted']     =& $GLOBALS['fbm_deleted_plugins'];
$GLOBALS['fbm_test_redirect']    =& $GLOBALS['__last_redirect'];
if (!function_exists('get_plugins')) {
  function get_plugins(): array { return $GLOBALS['fbm_plugins'] ?? []; }
}
if (!function_exists('is_plugin_active')) {
  function is_plugin_active($basename){ return in_array($basename, (array) $GLOBALS['fbm_active_plugins'], true); }
}
if (!function_exists('deactivate_plugins')) {
  function deactivate_plugins($plugins, $silent = false, $network_wide = null) {
    $list = (array) $plugins;
    foreach ($list as $p) {
      $GLOBALS['fbm_deactivated'][] = $p;
    }
    $existing = $GLOBALS['__deactivated_plugins'] ?? [];
    $GLOBALS['__deactivated_plugins'] = array_values(array_unique(array_merge($existing, $list)));
    $GLOBALS['fbm_active_plugins'] = array_values(array_diff($GLOBALS['fbm_active_plugins'], $list));
    return null;
  }
}
if (!function_exists('plugin_basename')) {
  function plugin_basename($file) { return basename((string) $file); }
}
if (!function_exists('delete_plugins')) {
  function delete_plugins($plugins){ foreach ((array)$plugins as $p){ $GLOBALS['fbm_deleted_plugins'][]=$p; } return true; }
}

// Caps & nonces (deterministic)
$GLOBALS['fbm_user_caps']         = $GLOBALS['fbm_user_caps']         ?? [];
$GLOBALS['fbm_current_user_roles'] = $GLOBALS['fbm_current_user_roles'] ?? [];
$GLOBALS['fbm_current_user']       = $GLOBALS['fbm_current_user']       ?? 0;
if (!function_exists('current_user_can')) {
  function current_user_can($cap){
    $all = $GLOBALS['fbm_user_caps'];
    foreach($GLOBALS['fbm_current_user_roles'] as $role){
      if(isset($GLOBALS['fbm_roles'][$role])){
        $all += $GLOBALS['fbm_roles'][$role]->caps;
      }
    }
    $user = (object)['ID'=>$GLOBALS['fbm_current_user'], 'roles'=>$GLOBALS['fbm_current_user_roles']];
    $all = apply_filters('user_has_cap', $all, [(string)$cap], [(string)$cap, $user->ID], $user);
    return !empty($all[(string)$cap]);
  }
}

$GLOBALS['fbm_test_nonce_secret'] = $GLOBALS['fbm_test_nonce_secret'] ?? 'fbm-test';
$GLOBALS['fbm_test_trust_nonces'] = $GLOBALS['fbm_test_trust_nonces'] ?? true;
if (!function_exists('wp_create_nonce')) { function wp_create_nonce($a=-1){ return hash_hmac('sha256', (string)$a, $GLOBALS['fbm_test_nonce_secret']); } }
if (!function_exists('wp_verify_nonce')) { function wp_verify_nonce($n,$a=-1){ return !empty($GLOBALS['fbm_test_trust_nonces']) ? 1 : (hash_equals($n ?? '', wp_create_nonce($a)) ? 1 : false); } }
if (!function_exists('check_admin_referer')) {
    function check_admin_referer($a=-1, $name='_wpnonce'){
      $n = $_REQUEST[$name] ?? '';
      if (!wp_verify_nonce($n, $a)) {
        throw new FbmDieException('bad nonce');
      }
      return true;
    }
  }
if (!function_exists('fbm_test_trust_nonces')) { function fbm_test_trust_nonces(bool $t){ $GLOBALS['fbm_test_trust_nonces']=$t; } }
if (!function_exists('fbm_seed_nonce')) {
  function fbm_seed_nonce(string $seed): void { $GLOBALS['fbm_test_nonce_secret'] = $seed; }
}
if (!function_exists('check_ajax_referer')) {
  function check_ajax_referer($action = -1, $query_arg = false, $die = true) {
    $field = $query_arg ?: '_ajax_nonce';
    $nonce = $_REQUEST[$field] ?? '';
    if (!wp_verify_nonce($nonce, $action)) {
      if ($die) {
        throw new FbmDieException('bad nonce');
      }
      return false;
    }
    return 1;
  }
}
if (!function_exists('fbm_nonce')) {
  function fbm_nonce(string $action): string { return wp_create_nonce($action); }
}
if (!function_exists('fbm_test_set_request_nonce')) { function fbm_test_set_request_nonce(string $a='fbm', string $f='_wpnonce'){ $_REQUEST[$f]=wp_create_nonce($a); $_POST[$f]=$_REQUEST[$f]; } }
if (!function_exists('wp_nonce_field')) {
  function wp_nonce_field($a=-1,$name='_wpnonce',$referer=true,$echo=true){
    $n=wp_create_nonce($a);
    $f='<input type="hidden" name="'.$name.'" value="'.$n.'" />';
    if($echo) echo $f;
    return $f;
  }
}

// URLs (flexible add_query_arg poly) & admin helpers
if (!function_exists('add_query_arg')) {
  function add_query_arg(...$args){
    if (!$args) return '';
    if (is_array($args[0])) { $params=$args[0]; $url=$args[1]??''; }
    else { $params=[(string)$args[0] => $args[1] ?? '']; $url=$args[2]??''; }
    $parts=parse_url($url); $query=[];
    if (!empty($parts['query'])) parse_str($parts['query'],$query);
    foreach($params as $k=>$v){ $query[$k]=$v; }
    $parts['query']=http_build_query($query);
    $scheme=isset($parts['scheme'])?$parts['scheme'].'://':''; $host=$parts['host']??''; $port=isset($parts['port'])?':'.$parts['port']:''; $path=$parts['path']??'';
    $q=$parts['query']?('?'.$parts['query']):''; $frag=isset($parts['fragment'])?('#'.$parts['fragment']):'';
    return $scheme.$host.$port.$path.$q.$frag;
  }
}
if (!function_exists('remove_query_arg')) {
  function remove_query_arg($keys, string $url=''){
    $keys=(array)$keys; $parts=parse_url($url); $query=[];
    if(!empty($parts['query'])) parse_str($parts['query'],$query);
    foreach($keys as $k){ unset($query[$k]); }
    $parts['query']=http_build_query($query);
    $scheme=isset($parts['scheme'])?$parts['scheme'].'://':''; $host=$parts['host']??''; $port=isset($parts['port'])?':'.$parts['port']:''; $path=$parts['path']??'';
    $q=$parts['query']?('?'.$parts['query']):''; $frag=isset($parts['fragment'])?('#'.$parts['fragment']):'';
    return $scheme.$host.$port.$path.$q.$frag;
  }
}

if (!function_exists('is_admin')) {
  function is_admin(){ return (bool)($GLOBALS['fbm_is_admin'] ?? false); }
}

if (!function_exists('admin_url')) { function admin_url($p=''){ return 'https://example.test/wp-admin/'.ltrim($p,'/'); } }
if (!isset($GLOBALS['fbm_current_action'])) $GLOBALS['fbm_current_action'] = 'admin_menu';
if (!function_exists('current_action')) { function current_action(){ return $GLOBALS['fbm_current_action']; } }
if (!function_exists('menu_page_url')) { function menu_page_url(string $slug, bool $echo=true){ $u='admin.php?page='.$slug; if($echo) echo $u; return $u; } }
if (!function_exists('plugins_url')) { function plugins_url($p=''){ return 'https://example.test/wp-content/plugins/'.ltrim($p,'/'); } }
if (!function_exists('site_url')) { function site_url($p='', $scheme = null){ return 'https://example.test/'.ltrim($p,'/'); } }
if (!function_exists('wp_nonce_url')) { function wp_nonce_url($u,$a=-1,$n='_wpnonce'){ return add_query_arg([$n=>wp_create_nonce($a)],$u); } }
if (!function_exists('wp_safe_redirect')) { /** @return void */ function wp_safe_redirect($u, $status = 302){ $GLOBALS['__last_redirect']=(string)$u; throw new FbmDieException('redirect'); } }
if (!function_exists('wp_redirect')) { /** @return void */ function wp_redirect($u){ $GLOBALS['__last_redirect']=(string)$u; throw new FbmDieException('redirect'); } }
if (!function_exists('wp_die')) { /** @return void */ function wp_die($m=''){ throw new FbmDieException((string)$m); } }
if (!function_exists('headers_sent')) { function headers_sent(){ return false; } }

if (!function_exists('header_remove')) {
    function header_remove(?string $name = null): void {}
}

if (!function_exists('header')) {
    /**
     * Capture headers instead of sending them.
     */
    function header(string $header, bool $replace = true, int $response_code = 0): void {
        $GLOBALS['__fbm_sent_headers'][] = $header;
    }
}

// Basic escaping/sanitizing
if (!function_exists('esc_attr')){ function esc_attr($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); } }
if (!function_exists('esc_html')){ function esc_html($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); } }
if (!function_exists('esc_url')){ function esc_url($s){ return (string)$s; } }
if (!function_exists('esc_url_raw')){ function esc_url_raw($s){ return (string)$s; } }
if (!function_exists('wp_kses_post')){
  function wp_kses_post($s){
    return preg_replace('#<script[^>]*>.*?</script>#si', '', (string)$s);
  }
}
if (!function_exists('sanitize_text_field')){ function sanitize_text_field($s){ return trim(strip_tags((string)$s)); } }
if (!function_exists('sanitize_textarea_field')){ function sanitize_textarea_field($s){ return trim(strip_tags((string)$s)); } }
if (!function_exists('esc_like')){ function esc_like($s){ return addslashes((string)$s); } }
if (!function_exists('sanitize_key')){ function sanitize_key($s){ return preg_replace('/[^a-z0-9_\-]/i','', (string)$s); } }

if (!class_exists('WP_REST_Request')) {
  class WP_REST_Request {
    private array $params = [];
    private array $headers = [];
    public function __construct(string $method = 'GET', string $route = '') {}
    public function get_param(string $key) { return $this->params[$key] ?? null; }
    public function set_param(string $key, $value): void { $this->params[$key] = $value; }
    public function get_header(string $key) { return $this->headers[$key] ?? null; }
    public function set_header(string $key, $value): void { $this->headers[$key] = $value; }
    public function get_file_params(): array { return []; }
  }
}
if (!class_exists('WP_REST_Response')) {
  class WP_REST_Response {
    private array $data; private int $status;
    public function __construct(array $data = array(), int $status = 200) { $this->data = $data; $this->status = $status; }
    public function get_data(): array { return $this->data; }
    public function get_status(): int { return $this->status; }
  }
}
if (!function_exists('sanitize_hex_color')){ function sanitize_hex_color($c){ $c=is_string($c)?trim($c):''; return preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/',$c)?strtolower($c):''; } }
if (!function_exists('sanitize_title')){ function sanitize_title($t){ $t=strtolower((string)$t); $t=preg_replace('/[^a-z0-9]+/','-',$t); return trim($t,'-'); } }
if (!function_exists('selected')){ function selected($a,$b,$echo=false){ $o=($a==$b)?' selected="selected"':''; if($echo) echo $o; return $o; } }
if (!function_exists('checked')){ function checked($a,$b=true,$echo=false){ $o=($a==$b)?' checked="checked"':''; if($echo) echo $o; return $o; } }
if (!function_exists('fbm_send_headers')) {
  function fbm_send_headers(array $headers): void {
    $GLOBALS['__fbm_sent_headers'] = $headers;
  }
}
if (!function_exists('esc_html__')){ function esc_html__($t, $d='default'){ return $t; } }
if (!function_exists('__')){ function __($t, $d='default'){ return $t; } }
if (!function_exists('esc_html_e')){ function esc_html_e($t, $d='default'){ echo esc_html($t); } }
if (!function_exists('esc_attr__')){ function esc_attr__($t, $d='default'){ return esc_attr($t); } }
if (!function_exists('esc_attr_e')){ function esc_attr_e($t, $d='default'){ echo esc_attr($t); } }
if (!function_exists('esc_js')){ function esc_js($t){ return addslashes((string)$t); } }
if (!function_exists('esc_textarea')){ function esc_textarea($t){ return htmlspecialchars((string)$t, ENT_NOQUOTES,'UTF-8'); } }
if (!function_exists('wp_strip_all_tags')){ function wp_strip_all_tags($t){ return strip_tags((string)$t); } }
if (!function_exists('wp_json_encode')){ function wp_json_encode($d){ return json_encode($d); } }
if (!function_exists('wp_send_json')){ function wp_send_json($d){ echo wp_json_encode($d); wp_die(); } }
if (!function_exists('wp_send_json_success')) {
  function wp_send_json_success($data = null, $status = 200) {
    return new WP_REST_Response(array('success' => true, 'data' => $data), $status);
  }
}
if (!function_exists('wp_send_json_error')) {
  function wp_send_json_error($data = null, $status = 400) {
    return new WP_REST_Response(array('success' => false, 'data' => $data), $status);
  }
}
if (!function_exists('is_email')){ function is_email($e){ return (bool)filter_var($e, FILTER_VALIDATE_EMAIL); } }
if (!function_exists('absint')){ function absint($n){ return abs((int)$n); } }
if (!function_exists('sanitize_email')){ function sanitize_email($e){ return filter_var($e, FILTER_SANITIZE_EMAIL); } }
if (!function_exists('number_format_i18n')){ function number_format_i18n($n){ return number_format($n, 0, '.', ','); } }
$GLOBALS['fbm_styles'] = $GLOBALS['fbm_styles'] ?? [];
$GLOBALS['fbm_scripts'] = $GLOBALS['fbm_scripts'] ?? [];
$GLOBALS['fbm_inline_styles'] = $GLOBALS['fbm_inline_styles'] ?? [];
if (!function_exists('current_time')){ function current_time($t, $gmt=false){ return date('Y-m-d H:i:s'); } }
if (!function_exists('wp_date')){ function wp_date($f, $ts = null, $tz = null){ $ts = $ts ?? time(); if($tz instanceof \DateTimeZone){ $d = new \DateTime('@'.$ts); $d->setTimezone($tz); return $d->format($f); } return date($f,$ts); } }
if (!function_exists('wp_enqueue_style')){ function wp_enqueue_style($h,$s=''){ $GLOBALS['fbm_styles'][$h]=$s; } }
if (!function_exists('wp_enqueue_script')){ function wp_enqueue_script($h,$s=''){ $GLOBALS['fbm_scripts'][$h]=$s; } }
if (!function_exists('wp_localize_script')){ function wp_localize_script($h,$o,$l){ $GLOBALS['fbm_localized'][$h]=$l; return true; } }
if (!function_exists('wp_add_inline_style')){ function wp_add_inline_style($h,$c){ $GLOBALS['fbm_inline_styles'][$h]=$c; } }
if (!function_exists('wp_register_style')){ function wp_register_style($h,$s=''){ $GLOBALS['fbm_styles'][$h]=$s; } }
if (!function_exists('wp_delete_file')){ function wp_delete_file($f){ return @unlink($f); } }
if (!function_exists('sanitize_file_name')){ function sanitize_file_name($f){ return preg_replace('/[^A-Za-z0-9\._-]/','', (string)$f); } }
if (!function_exists('get_bloginfo')){ function get_bloginfo($show='', $filter='raw'){ if($show==='version') return '6.5.0'; return ''; } }
if (!function_exists('get_current_user_id')){
  function get_current_user_id(){
    $id = $GLOBALS['fbm_current_user'] ?? 1;
    return $id ?: 1;
  }
}
if (!function_exists('nocache_headers')){ function nocache_headers(){} }
if (!function_exists('wp_mail')) {
    function wp_mail($to, $sub, $msg, $headers = '', $attachments = []) {
        $GLOBALS['fbm_last_mail'] = [$to, $sub, $msg, $headers, $attachments];
        return $GLOBALS['fbm_wp_mail_result'] ?? true;
    }
}
if (!function_exists('fbm_test_set_wp_mail_result')) { function fbm_test_set_wp_mail_result(bool $r): void { $GLOBALS['fbm_wp_mail_result']=$r; } }
if (!isset($GLOBALS['fbm_cron'])) { $GLOBALS['fbm_cron'] = []; }
if (!function_exists('_get_cron_array')) {
  function _get_cron_array() {
    return $GLOBALS['fbm_cron'] ?? [];
  }
}
if (!function_exists('wp_next_scheduled')) {
  function wp_next_scheduled($hook) {
    foreach (_get_cron_array() as $ts => $events) {
      if (isset($events[$hook])) {
        return $ts;
      }
    }
    return false;
  }
}
if (!function_exists('wp_schedule_event')) {
  function wp_schedule_event($timestamp, $recurrence, $hook, $args = array()) {
    $cron = _get_cron_array();
    $cron[$timestamp][$hook][] = $args;
    $GLOBALS['fbm_cron'] = $cron;
    return true;
  }
}
if (!function_exists('wp_clear_scheduled_hook')) {
  function wp_clear_scheduled_hook($hook) {
    $cron = _get_cron_array();
    foreach ($cron as $ts => $events) {
      if (isset($events[$hook])) {
        unset($cron[$ts][$hook]);
        if (!$cron[$ts]) {
          unset($cron[$ts]);
        }
      }
    }
    $GLOBALS['fbm_cron'] = $cron;
    return true;
  }
}
if (!function_exists('wp_get_schedule')) {
  function wp_get_schedule($hook) {
    foreach (_get_cron_array() as $ts => $events) {
      if (isset($events[$hook])) {
        return 'daily';
      }
    }
    return false;
  }
}
if (!function_exists('wp_get_schedules')) {
  function wp_get_schedules() {
    return array('daily' => array('interval' => 86400, 'display' => 'Daily'));
  }
}
if (!function_exists('remove_all_actions')) {
  function remove_all_actions($hook) {
    $GLOBALS['fbm_actions'][$hook] = [];
  }
}
if (!function_exists('remove_all_filters')) {
  function remove_all_filters($hook) {
    $GLOBALS['fbm_filters'][$hook] = [];
  }
}
if (!function_exists('add_action')){ function add_action($hook, $cb, $prio=10, $args=1){ $GLOBALS['fbm_actions'][$hook][]=$cb; } }
if (!function_exists('do_action')){ function do_action($hook, ...$args){ foreach($GLOBALS['fbm_actions'][$hook]??[] as $cb){ call_user_func_array($cb,$args); } } }
if (!function_exists('add_filter')){ function add_filter($hook, $cb, $prio=10){ $GLOBALS['fbm_filters'][$hook][]=$cb; } }
if (!function_exists('remove_filter')){ function remove_filter($hook, $cb){ if(isset($GLOBALS['fbm_filters'][$hook])){ $GLOBALS['fbm_filters'][$hook]=array_filter($GLOBALS['fbm_filters'][$hook], fn($c)=>$c!==$cb); } } }
if (!function_exists('apply_filters')){ function apply_filters($hook, $val, ...$args){ foreach($GLOBALS['fbm_filters'][$hook]??[] as $cb){ $val=call_user_func_array($cb,array_merge([$val],$args)); } return $val; } }
if (!function_exists('add_settings_error')){ function add_settings_error($setting,$code,$message,$type='error'){ $GLOBALS['fbm_settings_errors'][]=[$setting,$code,$message,$type]; } }
if (!function_exists('settings_errors')){ function settings_errors($setting='', $sanitize=false, $hide_on_update=false){ return $GLOBALS['fbm_settings_errors']??[]; } }
if (!function_exists('submit_button')){ function submit_button($text='', $type='primary', $name='submit', $wrap=true){ echo '<button type="submit" class="button">'.esc_html($text ?: 'Submit').'</button>'; } }
if (!defined('INPUT_POST')) {
  define('INPUT_POST', 0);
}
if (!defined('INPUT_GET')) {
  define('INPUT_GET', 1);
}

if (!function_exists('filter_input')) {
  function filter_input($type, $var, $filter = FILTER_DEFAULT, $options = []) {
    $src = ($type === INPUT_POST) ? $_POST : (($type === INPUT_GET) ? $_GET : (($type === INPUT_SERVER) ? $_SERVER : null));
    if ($src === null) {
      return null;
    }
    return $src[$var] ?? null;
  }
}
if (!function_exists('filter_input_array')) {
  function filter_input_array(int $type, $definition = FILTER_DEFAULT, bool $add_empty = true) {
    if ($type === INPUT_POST) { return $_POST; }
    if ($type === INPUT_GET) { return $_GET; }
    if ($type === INPUT_SERVER) { return $_SERVER; }
    return null;
  }
}
if (!isset($GLOBALS['fbm_user_meta'])) $GLOBALS['fbm_user_meta']=[];
if (!function_exists('get_user_meta')){ function get_user_meta($id,$key,$single=false){ $v=$GLOBALS['fbm_user_meta'][$id][$key]??null; return $single?($v[0]??null):($v??[]); } }
if (!function_exists('update_user_meta')){ function update_user_meta($id,$key,$val){ $GLOBALS['fbm_user_meta'][$id][$key]=[$val]; return true; } }
if (!function_exists('delete_user_meta')){ function delete_user_meta($id,$key){ unset($GLOBALS['fbm_user_meta'][$id][$key]); return true; } }
if (!isset($GLOBALS['fbm_users'])) $GLOBALS['fbm_users']=[];
if (!function_exists('get_user_by')){ function get_user_by($field,$value){ foreach($GLOBALS['fbm_users'] as $u){ if($u[$field]==$value) return (object)$u; } return false; } }
if (!function_exists('get_users')){ function get_users($args=[]){ return array_map(fn($u)=>(object)$u, $GLOBALS['fbm_users']); } }
if (!isset($GLOBALS['fbm_roles'])) $GLOBALS['fbm_roles']=[];
if (!class_exists('WP_Role')){
  class WP_Role{
    public array $caps;
    public function __construct(array $caps = []){ $this->caps=$caps; }
    public function add_cap($cap, $grant = true){ if($grant){ $this->caps[$cap]=true; } else { unset($this->caps[$cap]); } }
    public function remove_cap($cap){ unset($this->caps[$cap]); }
    public function has_cap($cap){ return !empty($this->caps[$cap]); }
  }
}
if (!function_exists('get_role')){
  function get_role($role){ return $GLOBALS['fbm_roles'][$role] ?? null; }
}
if (!function_exists('add_role')){
  function add_role($role, $display_name = '', $caps = [] ){ $GLOBALS['fbm_roles'][$role]=new WP_Role($caps); return $GLOBALS['fbm_roles'][$role]; }
}
if (!function_exists('remove_role')){
  function remove_role($role){ unset($GLOBALS['fbm_roles'][$role]); }
}
if (!function_exists('get_editable_roles')){ function get_editable_roles(){ $out=[]; foreach($GLOBALS['fbm_roles'] as $k=>$r){ $out[$k]=['name'=>$k]; } return $out; } }
if (!function_exists('wp_unslash')){ function wp_unslash($s){ return $s; } }
if (!function_exists('shortcode_atts')){ function shortcode_atts(array $pairs, array $atts, string $shortcode=''){ return array_merge($pairs, $atts); } }
if (!function_exists('map_deep')) { function map_deep($v, $cb){ if(is_array($v)) return array_map(fn($x)=>map_deep($x,$cb), $v); return $cb($v); } }
if (!function_exists('wp_salt')){ function wp_salt($scheme='auth'){ return hash('sha256',(string)$scheme); } }
if (!function_exists('wp_enqueue_scripts')) { /** @return void */ function wp_enqueue_scripts(){} }
if (!isset($GLOBALS['fbm_shortcodes'])) $GLOBALS['fbm_shortcodes']=[];
if (!function_exists('add_shortcode')) { /** @return void */ function add_shortcode(string $tag, callable $cb){ $GLOBALS['fbm_shortcodes'][$tag]=$cb; } }
if (!function_exists('do_shortcode')) {
  function do_shortcode(string $text): string {
    return preg_replace_callback('/\[([a-z0-9_]+)([^\]]*)\]/i', function($m){
      $tag=$m[1]; $atts=[];
      if(preg_match_all('/(\w+)="([^"]*)"/',$m[2],$am,PREG_SET_ORDER)){
        foreach($am as $a){ $atts[$a[1]]=$a[2]; }
      }
      $cb=$GLOBALS['fbm_shortcodes'][$tag]??null;
      return $cb ? (string)call_user_func($cb,$atts) : '';
    }, $text);
  }
}
if (!function_exists('has_shortcode')) {
  function has_shortcode($content, $tag) {
    return false !== strpos((string)$content, '[' . $tag);
  }
}
if (!function_exists('is_singular')) {
  function is_singular() {
    return !empty($GLOBALS['fbm_is_singular']);
  }
}
if (!function_exists('get_post')) {
  function get_post($id = null) {
    if ($id) {
      return $GLOBALS['fbm_posts'][$id] ?? null;
    }
    $content = $GLOBALS['fbm_post_content'] ?? '';
    return (object)['post_content' => $content];
  }
}
if (!function_exists('get_current_screen')) {
  function get_current_screen() {
    $id = $GLOBALS['fbm_test_screen_id'] ?? '';
    if ($id === '') {
      return null;
    }
    return (object)['id' => $id];
  }
}

if (!function_exists('register_post_type')) {
  function register_post_type($post_type, $args = []) {
    $GLOBALS['fbm_registered_post_types'][$post_type] = $args;
    return $post_type;
  }
}
if (!function_exists('wp_insert_post')) {
  function wp_insert_post($data) {
    $id = count($GLOBALS['fbm_posts']) + 1;
    $obj = (object)array_merge(['ID'=>$id], $data);
    $obj->post_type = $data['post_type'] ?? 'post';
    $obj->post_title = $data['post_title'] ?? '';
    $GLOBALS['fbm_posts'][$id] = $obj;
    return $id;
  }
}
if (!function_exists('wp_update_post')) {
  function wp_update_post($data) {
    $id = $data['ID'];
    if (isset($GLOBALS['fbm_posts'][$id])) {
      foreach ($data as $k=>$v) {
        $GLOBALS['fbm_posts'][$id]->$k = $v;
      }
    }
    return $id;
  }
}
if (!function_exists('wp_delete_post')) {
  function wp_delete_post($id) { unset($GLOBALS['fbm_posts'][$id], $GLOBALS['fbm_post_meta'][$id]); }
}
if (!function_exists('get_post_type')) {
  function get_post_type($id) { return $GLOBALS['fbm_posts'][$id]->post_type ?? null; }
}
if (!function_exists('update_post_meta')) {
  function update_post_meta($id,$key,$value){ $GLOBALS['fbm_post_meta'][$id][$key]=$value; return true; }
}
if (!function_exists('get_post_meta')) {
  function get_post_meta($id,$key,$single=false){ $v=$GLOBALS['fbm_post_meta'][$id][$key]??null; return $single?($v):[$v]; }
}
if (!function_exists('get_posts')) {
  function get_posts($args=[]){
    return array_values(array_filter($GLOBALS['fbm_posts'], fn($p)=>($args['post_type']??'')===''||$p->post_type===$args['post_type']));
  }
}

// Global reset (used by bootstrap)
if (!function_exists('fbm_test_reset_globals')) {
  function fbm_test_reset_globals(): void {
    $GLOBALS['fbm_options'] = [];
    $GLOBALS['fbm_plugins'] = [];
    $GLOBALS['fbm_active_plugins']  = [];
    $GLOBALS['fbm_deactivated']     = [];
    $GLOBALS['fbm_deleted_plugins'] = [];
    $GLOBALS['__deactivated_plugins'] = [];
    $GLOBALS['fbm_user_caps'] = [];
    $GLOBALS['fbm_roles'] = [];
    $GLOBALS['fbm_user_meta'] = [];
    $GLOBALS['fbm_users'] = [];
    $GLOBALS['fbm_current_user_roles'] = [];
    $GLOBALS['fbm_current_user'] = 0;
    add_role('administrator', 'Administrator');
    $GLOBALS['fbm_actions'] = [];
    $GLOBALS['fbm_filters'] = [];
    $GLOBALS['fbm_shortcodes'] = [];
    $GLOBALS['fbm_styles'] = [];
    $GLOBALS['fbm_scripts'] = [];
    $GLOBALS['fbm_inline_styles'] = [];
    $GLOBALS['fbm_posts'] = [];
    $GLOBALS['fbm_post_meta'] = [];
    $GLOBALS['fbm_registered_post_types'] = [];
    $GLOBALS['fbm_post_content'] = '';
    $GLOBALS['fbm_is_singular'] = false;
    $GLOBALS['fbm_test_screen_id'] = null;
    $GLOBALS['fbm_test_nonce_secret'] = 'fbm-test';
    $GLOBALS['fbm_site_options'] = [];
    $GLOBALS['fbm_is_multisite'] = false;
    $GLOBALS['fbm_sites'] = [];
    $GLOBALS['fbm_current_blog'] = 1;
    $GLOBALS['fbm_switched_to'] = [];
    $_GET = $_POST = $_REQUEST = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $GLOBALS['__last_redirect'] = null;
  }
}

if ( ! class_exists( 'WP_List_Table', false ) ) {
    abstract class WP_List_Table {
        /** @var array<int,array<string,mixed>> */
        public $items = [];
        /** @var array{0:array,1:array,2:array} */
        protected $_column_headers = [ [], [], [] ];
        public function __construct( array $args = [] ) {}
        /** @param array<string,int> $args */
        protected function set_pagination_args( array $args ): void {}
        public function display(): void {}
    }
}

}

namespace FoodBankManager\Admin {
  if (!function_exists(__NAMESPACE__ . '\\filter_input')) {
    function filter_input($type, $var, $filter = FILTER_DEFAULT, $options = []) {
      $src = ($type === \INPUT_POST) ? $_POST : (($type === \INPUT_GET) ? $_GET : (($type === \INPUT_SERVER) ? $_SERVER : null));
      if ($src === null) {
        return null;
      }
      return $src[$var] ?? null;
    }
  }
}

