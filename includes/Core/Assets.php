<?php // phpcs:ignoreFile
/**
 * Asset loader.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\UI\Theme;
use function add_action;
use function add_filter;
use function wp_enqueue_style;
use function wp_register_style;
use function wp_add_inline_style;
use function wp_enqueue_script;
use function wp_localize_script;
use function admin_url;
use function wp_create_nonce;
use function current_user_can;
use function get_current_screen;
use function get_option;
use function esc_html;

/**
 * Manages script and style loading.
 */
class Assets {
    /**
     * Register hooks.
     *
     * @return void
     */
    public function register(): void {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin'], 10);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_theme_page'], 10);
        if (defined('FBM_DEBUG_THEME') && FBM_DEBUG_THEME) {
            add_action('admin_notices', [self::class, 'debug_notice']);
        }
        $theme = Theme::get();
        if (!is_admin() && !empty($theme['apply_front_menus'])) {
            add_filter('body_class', [Theme::class, 'body_class']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_front_menus']);
        }
    }

    /**
     * Enqueue admin assets when on plugin screens.
     */
    public function enqueue_admin(string $hook_suffix = ''): void {
        $GLOBALS['hook_suffix'] = $hook_suffix;
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $id     = $screen ? $screen->id : $hook_suffix;
        if (strpos((string) $id, 'foodbank_page_fbm') !== 0) {
            return;
        }
        $opt = get_option('fbm_theme', Theme::defaults());
        if ('foodbank_page_fbm_theme' !== $hook_suffix && empty($opt['apply_admin'])) {
            return;
        }

        wp_register_style('fbm-admin', plugins_url('assets/css/admin.css', FBM_FILE), [], Plugin::VERSION);
        wp_enqueue_style('fbm-admin');
        wp_add_inline_style('fbm-admin', Theme::css_variables_scoped());

        wp_register_style('fbm-admin-tables', plugins_url('assets/css/admin-tables.css', FBM_FILE), [], Plugin::VERSION);
        wp_enqueue_style('fbm-admin-tables');

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if ($screen && 'foodbank_page_fbm_attendance' === $screen->id && current_user_can('fb_manage_attendance')) {
            wp_enqueue_script('fbm-qrcode', plugins_url('assets/js/qrcode.min.js', FBM_FILE), [], Plugin::VERSION, true);
        }
        if ($screen && 'foodbank_page_fbm_form_builder' === $screen->id && current_user_can('fbm_manage_forms')) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
            wp_enqueue_script('fbm-form-builder', plugins_url('assets/js/fbm-form-builder.js', FBM_FILE), [], Plugin::VERSION, true);
        }
        if ($screen && 'foodbank_page_fbm_diagnostics' === $screen->id && current_user_can('fb_manage_diagnostics')) {
            wp_enqueue_script('fbm-admin-diagnostics', plugins_url('assets/js/admin-diagnostics.js', FBM_FILE), [], Plugin::VERSION, true);
        }
        if ($screen && 'foodbank_page_fbm_permissions' === $screen->id && current_user_can('fb_manage_permissions')) {
            wp_enqueue_script('fbm-admin-permissions', plugins_url('assets/js/admin-permissions.js', FBM_FILE), [], Plugin::VERSION, true);
            wp_localize_script('fbm-admin-permissions', 'fbmPerms', [
                'url'   => admin_url('admin-post.php'),
                'nonce' => wp_create_nonce('fbm_perms_role_toggle'),
            ]);
        }
        if ($screen && 'foodbank_page_fbm_shortcodes' === $screen->id && current_user_can('fbm_manage_forms')) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
            wp_enqueue_script('fbm-admin-shortcodes', plugins_url('assets/js/admin-shortcodes.js', FBM_FILE), [], Plugin::VERSION, true);
        }
    }

    /**
     * Theme page specific assets.
     */
    public function enqueue_theme_page(string $hook_suffix = ''): void {
        $GLOBALS['hook_suffix'] = $hook_suffix;
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || 'foodbank_page_fbm_theme' !== $screen->id) {
            return;
        }
        $css = '.fbm-grid{margin-top:16px}';
        $css .= <<<CSS
/* Two-pane grid: LEFT 260px | RIGHT 1fr */
.fbm-grid--vtabs{ display:grid; grid-template-columns: 260px minmax(0,1fr); gap:16px; align-items:start; }

/* RIGHT itself: fields | preview side-by-side */
.fbm-right{ display:grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap:16px; align-items:start; }
@media (max-width:1280px){ .fbm-right{ grid-template-columns: 1fr; } }

.fbm-right-fields{ min-width:0; }
.fbm-preview{ min-width:0; }

/* Keep non-FBM admin notices from pushing layout around on this page only */
.wrap.fbm-theme .notice, .wrap.fbm-theme .update-nag{ display:none !important; }

/* ---------- Vertical tabs (left) ---------- */
.fbm-vtabs { display:flex; flex-direction:column; gap:8px; margin-bottom:12px; }
.fbm-vtab {
  display:flex; align-items:center; width:100%;
  height:38px; padding:0 12px; border-radius:10px; border:1px solid #e6e7ea;
  background:#fff; color:#111827; font-weight:600; text-align:left; cursor:pointer;
  box-shadow: 0 1px 0 rgba(22,27,29,.02);
}
.fbm-vtab[aria-selected="true"]{
  background:#f0f5ff; border-color:#c7d2fe; color:#1f2937;
  box-shadow: 0 0 0 2px rgba(59,130,246,.15) inset;
}
.fbm-vtab:hover{ background:#f8fafc; }

/* ---------- Right side: group panel + preview ---------- */
.fbm-group-panel{ border:1px solid #e6e7ea; border-radius:12px; background:#fff; }
.fbm-panel-inner{ padding:14px; }
.fbm-panel-title{ margin:0 0 8px; font-size:14px; text-transform:uppercase; letter-spacing:.02em; color:#374151; }
.fbm-group-grid{ display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:12px 16px; }
@media (max-width:1200px){ .fbm-group-grid{ grid-template-columns:1fr; } }

.fbm-preview{ background:#fff; border:1px solid #e6e7ea; border-radius: 12px; overflow:hidden; box-shadow: 0 1px 0 rgba(22,27,29,.02), 0 8px 24px rgba(22,27,29,.06); }
.fbm-preview__bar{ display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border-bottom:1px solid #eef0f2; background:#fafbfc; }
.fbm-catalog{ padding:18px; background:
  linear-gradient(90deg, #f7f8fa 20px, transparent 1px) center/21px 21px,
  linear-gradient(#f7f8fa 20px, transparent 1px) center/21px 21px; }
.fbm-catalog > .fbm-scope{ background:#fff; border:1px solid #eef0f2; border-radius:10px; padding:18px; }

/* Field tweaks for compact two-column */
.fbm-field{ display:grid; gap:6px; }
.fbm-field > label{ font-weight:600; color:#111827; }
.fbm-field input[type="number"]{ max-width:140px; }
.fbm-field input[type="color"]{ inline-size:48px; block-size:32px; padding:0; border-radius:6px; }
CSS;
        wp_add_inline_style('fbm-admin', $css);

        wp_enqueue_script('fbm-theme-admin', plugins_url('assets/js/theme-admin.js', FBM_FILE), ['jquery'], Plugin::VERSION, true);
        wp_localize_script('fbm-theme-admin', 'fbmTheme', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('fbm_theme'),
        ]);
    }

    /**
     * Optional debug overlay.
     */
    public static function debug_notice(): void {
        if (!\FBM\Core\AdminScope::is_fbm_admin()) {
            return;
        }
        $screenId = (function_exists('get_current_screen') && get_current_screen()) ? get_current_screen()->id : '(unavailable)';
        $enq      = !empty($GLOBALS['fbm_styles']['fbm-admin']) ? 'yes' : 'no';
        $inline   = !empty($GLOBALS['fbm_inline_styles']['fbm-admin']) ? 'yes' : 'no';
        echo '<div class="notice notice-info"><p><strong>FBM Theme Debug</strong><br>' .
            'hook_suffix: ' . esc_html($GLOBALS['hook_suffix'] ?? '(none)') . '<br>' .
            'screen->id: ' . esc_html($screenId) . '<br>' .
            'page: ' . esc_html(\FBM\Core\AdminScope::slug()) . '<br>' .
            'fbm-admin enqueued: ' . esc_html($enq) . '<br>' .
            'inline vars: ' . esc_html($inline) .
            '</p></div>';
    }

    /**
     * Legacy front-end enqueue for tests.
     *
     * @deprecated
     */
    public function enqueue_front(): void {
        $content = (string) ($GLOBALS['fbm_post_content'] ?? '');
        if (str_contains($content, '[fbm_dashboard]')) {
            $GLOBALS['fbm_styles']['fbm-frontend-dashboard'] = true;
        }
    }

    /**
     * Enqueue front-end menu styles when enabled.
     */
    public function enqueue_front_menus(): void {
        $theme = Theme::get();
        if (empty($theme['apply_front_menus'])) {
            return;
        }
        wp_register_style('fbm-menus', plugins_url('assets/css/menus.css', FBM_FILE), [], Plugin::VERSION);
        wp_enqueue_style('fbm-menus');
    }

    /**
     * Legacy helper removed.
     */
    public static function print_admin_head(): void {}
}

namespace FBM\Core;

final class Assets {
    /** @deprecated Use AdminScope::is_fbm_admin(). */
    public static function is_fbm_screen(?string $hook = null): bool { // phpcs:ignore Squiz.Commenting.FunctionComment.WrongStyle
        return AdminScope::is_fbm_admin();
    }
}
