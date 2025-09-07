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
        function add_action($hook, $callback, int $priority = 10, int $accepted_args = 1) {}
        function add_filter($hook, $callback, int $priority = 10, int $accepted_args = 1) {}
        function wp_register_style($handle, $src = '', array $deps = [], $ver = false, $media = 'all') {}
        function wp_enqueue_script($handle, $src = '', array $deps = [], $ver = false, $in_footer = false) {}
        function wp_json_encode($data, $options = 0, $depth = 512) {}
        function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {}
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
if (!function_exists('admin_url')) { function admin_url($p=''){ return 'https://example.test/wp-admin/'.ltrim($p,'/'); } }
if (!function_exists('menu_page_url')) { function menu_page_url(string $slug, bool $echo=true){ $u='admin.php?page='.$slug; if($echo) echo $u; return $u; } }
if (!function_exists('plugins_url')) { function plugins_url($p=''){ return 'https://example.test/wp-content/plugins/'.ltrim($p,'/'); } }
if (!function_exists('wp_nonce_url')) { function wp_nonce_url($u,$a=-1,$n='_wpnonce'){ return add_query_arg([$n=>wp_create_nonce($a)],$u); } }
if (!function_exists('wp_safe_redirect')) { /** @return void */ function wp_safe_redirect($u){ $GLOBALS['__last_redirect']=(string)$u; throw new RuntimeException('redirect'); } }
if (!function_exists('wp_redirect')) { /** @return void */ function wp_redirect($u){ $GLOBALS['__last_redirect']=(string)$u; throw new RuntimeException('redirect'); } }
if (!function_exists('wp_die')) { /** @return void */ function wp_die($m=''){ throw new RuntimeException('wp_die: '.$m); } }

// Basic escaping/sanitizing
if (!function_exists('esc_attr')){ function esc_attr($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); } }
if (!function_exists('esc_html')){ function esc_html($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); } }
if (!function_exists('esc_url')){ function esc_url($s){ return (string)$s; } }
if (!function_exists('esc_url_raw')){ function esc_url_raw($s){ return (string)$s; } }
if (!function_exists('wp_kses_post')){ function wp_kses_post($s){ return (string)$s; } }
if (!function_exists('sanitize_text_field')){ function sanitize_text_field($s){ return trim((string)$s); } }
if (!function_exists('sanitize_key')){ function sanitize_key($s){ return preg_replace('/[^a-z0-9_\-]/i','', (string)$s); } }
if (!function_exists('sanitize_hex_color')){ function sanitize_hex_color($c){ $c=is_string($c)?trim($c):''; return preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/',$c)?strtolower($c):''; } }
if (!function_exists('selected')){ function selected($a,$b,$echo=false){ $o=($a==$b)?' selected="selected"':''; if($echo) echo $o; return $o; } }
if (!function_exists('checked')){ function checked($a,$b=true,$echo=false){ $o=($a==$b)?' checked="checked"':''; if($echo) echo $o; return $o; } }
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
if (!function_exists('is_email')){ function is_email($e){ return (bool)filter_var($e, FILTER_VALIDATE_EMAIL); } }
if (!function_exists('absint')){ function absint($n){ return abs((int)$n); } }
if (!function_exists('sanitize_email')){ function sanitize_email($e){ return filter_var($e, FILTER_SANITIZE_EMAIL); } }
if (!function_exists('number_format_i18n')){ function number_format_i18n($n){ return number_format($n, 0, '.', ','); } }
if (!function_exists('current_time')){ function current_time($t, $gmt=false){ return date('Y-m-d H:i:s'); } }
if (!function_exists('wp_enqueue_style')){ function wp_enqueue_style($h,$s=''){ $GLOBALS['fbm_styles'][$h]=$s; } }
if (!function_exists('wp_enqueue_script')){ function wp_enqueue_script($h,$s=''){ $GLOBALS['fbm_scripts'][$h]=$s; } }
if (!function_exists('wp_add_inline_style')){ function wp_add_inline_style($h,$c){ $GLOBALS['fbm_inline_styles'][$h]=$c; } }
if (!function_exists('wp_register_style')){ function wp_register_style($h,$s=''){ $GLOBALS['fbm_styles'][$h]=$s; } }
if (!function_exists('wp_delete_file')){ function wp_delete_file($f){ return @unlink($f); } }
if (!function_exists('sanitize_file_name')){ function sanitize_file_name($f){ return preg_replace('/[^A-Za-z0-9\._-]/','', (string)$f); } }
if (!function_exists('get_bloginfo')){ function get_bloginfo($show='', $filter='raw'){ if($show==='version') return '6.5.0'; return ''; } }
if (!function_exists('get_current_user_id')){ function get_current_user_id(){ return 1; } }
if (!function_exists('nocache_headers')){ function nocache_headers(){} }
if (!function_exists('wp_mail')){ function wp_mail($to,$sub,$msg,$headers='',$attachments=[]){ $GLOBALS['fbm_last_mail']=[$to,$sub,$msg,$headers,$attachments]; return true; } }
if (!function_exists('wp_next_scheduled')){ function wp_next_scheduled($hook){ return false; } }
if (!function_exists('wp_get_schedule')){ function wp_get_schedule($hook){ return false; } }
if (!function_exists('wp_get_schedules')){ function wp_get_schedules(){ return []; } }
if (!function_exists('add_action')){ function add_action($hook, $cb, $prio=10, $args=1){ $GLOBALS['fbm_actions'][$hook][]=$cb; } }
if (!function_exists('do_action')){ function do_action($hook, ...$args){ foreach($GLOBALS['fbm_actions'][$hook]??[] as $cb){ call_user_func_array($cb,$args); } } }
if (!function_exists('add_filter')){ function add_filter($hook, $cb, $prio=10){ $GLOBALS['fbm_filters'][$hook][]=$cb; } }
if (!function_exists('remove_filter')){ function remove_filter($hook, $cb){ if(isset($GLOBALS['fbm_filters'][$hook])){ $GLOBALS['fbm_filters'][$hook]=array_filter($GLOBALS['fbm_filters'][$hook], fn($c)=>$c!==$cb); } } }
if (!function_exists('apply_filters')){ function apply_filters($hook, $val, ...$args){ foreach($GLOBALS['fbm_filters'][$hook]??[] as $cb){ $val=call_user_func_array($cb,array_merge([$val],$args)); } return $val; } }
if (!function_exists('add_settings_error')){ function add_settings_error($setting,$code,$message,$type='error'){ $GLOBALS['fbm_settings_errors'][]=[$setting,$code,$message,$type]; } }
if (!function_exists('settings_errors')){ function settings_errors($setting='', $sanitize=false, $hide_on_update=false){ return $GLOBALS['fbm_settings_errors']??[]; } }
if (!function_exists('submit_button')){ function submit_button($text='', $type='primary', $name='submit', $wrap=true){ echo '<button type="submit" class="button">'.esc_html($text ?: 'Submit').'</button>'; } }
if (!function_exists('filter_input')){ function filter_input($type,$var,$filter=FILTER_DEFAULT,$options=[]){ if($type===INPUT_POST){ return $_POST[$var]??null;} if($type===INPUT_GET){ return $_GET[$var]??null;} return null; } }
if (!isset($GLOBALS['fbm_user_meta'])) $GLOBALS['fbm_user_meta']=[];
if (!function_exists('get_user_meta')){ function get_user_meta($id,$key,$single=false){ $v=$GLOBALS['fbm_user_meta'][$id][$key]??null; return $single?($v[0]??null):($v??[]); } }
if (!function_exists('update_user_meta')){ function update_user_meta($id,$key,$val){ $GLOBALS['fbm_user_meta'][$id][$key]=[$val]; return true; } }
if (!function_exists('delete_user_meta')){ function delete_user_meta($id,$key){ unset($GLOBALS['fbm_user_meta'][$id][$key]); return true; } }
if (!isset($GLOBALS['fbm_users'])) $GLOBALS['fbm_users']=[];
if (!function_exists('get_user_by')){ function get_user_by($field,$value){ foreach($GLOBALS['fbm_users'] as $u){ if($u[$field]==$value) return (object)$u; } return false; } }
if (!function_exists('get_users')){ function get_users($args=[]){ return array_map(fn($u)=>(object)$u, $GLOBALS['fbm_users']); } }
if (!isset($GLOBALS['fbm_roles'])) $GLOBALS['fbm_roles']=[];
if (!class_exists('WP_Role')){ class WP_Role{ public $caps=[]; public function add_cap($c){$this->caps[$c]=true;} public function remove_cap($c){unset($this->caps[$c]);} public function has_cap($c){return isset($this->caps[$c]);} } }
if (!function_exists('get_role')){ function get_role($role){ if(!isset($GLOBALS['fbm_roles'][$role])) $GLOBALS['fbm_roles'][$role]=new WP_Role(); return $GLOBALS['fbm_roles'][$role]; } }
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

