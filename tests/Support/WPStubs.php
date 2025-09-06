<?php declare(strict_types=1);

// When running static analysis, load the official WordPress stubs instead of
// our test implementations to avoid function redeclarations and maintain
// accurate signatures.
if (getenv('FBM_PHPSTAN')) {
    require_once __DIR__ . '/../../vendor/php-stubs/wordpress-stubs/wordpress-stubs.php';
    // Provide signatures for functions used with extended parameters.
    if (false) {
        function wp_die($message = '', $title = '', $args = []) {}
        function wp_safe_redirect($location, int $status = 302) {}
    }
    return;
}

// Options
$GLOBALS['fbm_options']    = $GLOBALS['fbm_options']    ?? [];
$GLOBALS['fbm_transients'] = $GLOBALS['fbm_transients'] ?? [];
if (!function_exists('get_option')) {
  function get_option($name, $default=false){ return $GLOBALS['fbm_options'][$name] ?? $default; }
}
if (!function_exists('update_option')){
  function update_option($name, $value){ $GLOBALS['fbm_options'][$name] = $value; return true; }
}
if (!function_exists('delete_option')){
  function delete_option($name){ unset($GLOBALS['fbm_options'][$name]); return true; }
}
if (!function_exists('set_transient')) {
  function set_transient($key, $value, $expiration = 0){ $GLOBALS['fbm_transients'][$key] = $value; return true; }
}
if (!function_exists('get_transient')) {
  function get_transient($key){ return $GLOBALS['fbm_transients'][$key] ?? false; }
}
if (!function_exists('delete_transient')) {
  function delete_transient($key){ unset($GLOBALS['fbm_transients'][$key]); return true; }
}

// Plugins API (and tracking globals)
$GLOBALS['fbm_plugins']        = $GLOBALS['fbm_plugins']        ?? [];
$GLOBALS['fbm_active_plugins']  = $GLOBALS['fbm_active_plugins']  ?? [];
$GLOBALS['fbm_deactivated']     = $GLOBALS['fbm_deactivated']     ?? [];
$GLOBALS['fbm_deleted_plugins'] = $GLOBALS['fbm_deleted_plugins'] ?? [];
$GLOBALS['__last_redirect']     = $GLOBALS['__last_redirect']     ?? null;
$GLOBALS['fbm_test_plugins']    =& $GLOBALS['fbm_plugins'];
$GLOBALS['fbm_test_deactivated'] =& $GLOBALS['fbm_deactivated'];
$GLOBALS['fbm_test_deleted']     =& $GLOBALS['fbm_deleted_plugins'];
$GLOBALS['fbm_test_redirect']    =& $GLOBALS['__last_redirect'];
if (!function_exists('get_plugins')) {
  function get_plugins(){ return $GLOBALS['fbm_plugins'] ?? []; }
}
if (!function_exists('is_plugin_active')) {
  function is_plugin_active($basename){ return in_array($basename, $GLOBALS['fbm_active_plugins'], true); }
}
if (!function_exists('deactivate_plugins')) {
  function deactivate_plugins($plugins){ foreach ((array)$plugins as $p){ $GLOBALS['fbm_deactivated'][]=$p; $GLOBALS['fbm_active_plugins']=array_values(array_diff($GLOBALS['fbm_active_plugins'],[$p])); } }
}
if (!function_exists('delete_plugins')) {
  function delete_plugins($plugins){ foreach ((array)$plugins as $p){ $GLOBALS['fbm_deleted_plugins'][]=$p; } return true; }
}

// Caps & nonces (deterministic)
$GLOBALS['fbm_user_caps'] = $GLOBALS['fbm_user_caps'] ?? [];
if (!function_exists('current_user_can')) { function current_user_can($cap){ return !empty($GLOBALS['fbm_user_caps'][(string)$cap]); } }

$GLOBALS['fbm_test_nonce_secret'] = $GLOBALS['fbm_test_nonce_secret'] ?? 'fbm-test';
$GLOBALS['fbm_test_trust_nonces'] = $GLOBALS['fbm_test_trust_nonces'] ?? true;
if (!function_exists('wp_create_nonce')) { function wp_create_nonce($a=-1){ return hash_hmac('sha256', (string)$a, $GLOBALS['fbm_test_nonce_secret']); } }
if (!function_exists('wp_verify_nonce')) { function wp_verify_nonce($n,$a=-1){ return !empty($GLOBALS['fbm_test_trust_nonces']) ? 1 : (hash_equals($n ?? '', wp_create_nonce($a)) ? 1 : false); } }
if (!function_exists('check_admin_referer')) { function check_admin_referer($a=-1,$name='_wpnonce'){ $n=$_REQUEST[$name]??''; if(!wp_verify_nonce($n,$a)) throw new RuntimeException('bad nonce'); return true; } }
if (!function_exists('fbm_test_trust_nonces')) { function fbm_test_trust_nonces(bool $t){ $GLOBALS['fbm_test_trust_nonces']=$t; } }
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
if (!function_exists('admin_url')) { function admin_url($p=''){ return 'https://example.test/wp-admin/'.ltrim($p,'/'); } }
if (!function_exists('plugins_url')) { function plugins_url($p=''){ return 'https://example.test/wp-content/plugins/'.ltrim($p,'/'); } }
if (!function_exists('wp_nonce_url')) { function wp_nonce_url($u,$a=-1,$n='_wpnonce'){ return add_query_arg([$n=>wp_create_nonce($a)],$u); } }
if (!function_exists('wp_safe_redirect')) { function wp_safe_redirect($u){ $GLOBALS['__last_redirect']=(string)$u; return true; } }
if (!function_exists('wp_redirect')) { function wp_redirect($u){ $GLOBALS['__last_redirect']=(string)$u; return true; } }
if (!function_exists('wp_die')) { function wp_die($m=''){ throw new RuntimeException('wp_die: '.$m); } }

// Basic escaping/sanitizing
if (!function_exists('esc_attr')){ function esc_attr($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); } }
if (!function_exists('esc_html')){ function esc_html($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); } }
if (!function_exists('esc_url')){ function esc_url($s){ return (string)$s; } }
if (!function_exists('wp_kses_post')){ function wp_kses_post($s){ return (string)$s; } }
if (!function_exists('sanitize_text_field')){ function sanitize_text_field($s){ return trim((string)$s); } }
if (!function_exists('sanitize_key')){ function sanitize_key($s){ return preg_replace('/[^a-z0-9_\-]/i','', (string)$s); } }
if (!function_exists('selected')){ function selected($a,$b,$echo=false){ $o=($a==$b)?' selected="selected"':''; if($echo) echo $o; return $o; } }
if (!function_exists('checked')){ function checked($a,$b=true,$echo=false){ $o=($a==$b)?' checked="checked"':''; if($echo) echo $o; return $o; } }
if (!function_exists('esc_html__')){ function esc_html__($t, $d='default'){ return $t; } }
if (!function_exists('wp_unslash')){ function wp_unslash($s){ return $s; } }
if (!function_exists('shortcode_atts')){ function shortcode_atts(array $pairs, array $atts, string $shortcode=''){ return array_merge($pairs, $atts); } }
if (!function_exists('get_current_screen')){ function get_current_screen(){ $id=$GLOBALS['fbm_test_screen_id']??''; if(!$id) return null; $o=new stdClass(); $o->id=$id; return $o; } }

// Global reset (used by bootstrap)
if (!function_exists('fbm_test_reset_globals')) {
  function fbm_test_reset_globals(): void {
    $GLOBALS['fbm_options'] = [];
    $GLOBALS['fbm_plugins'] = [];
    $GLOBALS['fbm_active_plugins']  = [];
    $GLOBALS['fbm_deactivated']     = [];
    $GLOBALS['fbm_deleted_plugins'] = [];
    $GLOBALS['fbm_user_caps'] = [];
    $_GET = $_POST = $_REQUEST = [];
    $GLOBALS['__last_redirect'] = null;
  }
}

