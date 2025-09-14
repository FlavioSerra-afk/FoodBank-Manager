<?php
if (!function_exists('settings_fields')) { function settings_fields($g){ echo '<input type="hidden" name="option_page" value="'.esc_attr($g).'" />'; } }
if (!function_exists('submit_button')) { function submit_button($t='Save',$type='primary',$n='submit',$w=true){ /* no-op */ } }
if (!function_exists('esc_html_e')) { function esc_html_e($t,$d=null){ echo htmlspecialchars((string)$t,ENT_QUOTES,'UTF-8'); } }
if (!function_exists('esc_html')) { function esc_html($t){ return htmlspecialchars((string)$t,ENT_QUOTES,'UTF-8'); } }
if (!function_exists('esc_attr')) { function esc_attr($t){ return htmlspecialchars((string)$t,ENT_QUOTES,'UTF-8'); } }
if (!function_exists('wp_add_inline_style')) { function wp_add_inline_style($h,$c){ /* no-op */ } }
if (!function_exists('get_current_screen')) { function get_current_screen(){ return (object)['id'=>null]; } }
