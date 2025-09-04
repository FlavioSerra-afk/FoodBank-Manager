<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Core\Options;
use function get_post;
use function has_shortcode;
use function is_singular;
use function wp_enqueue_style;
use function wp_register_style;

class Assets {
        public function register(): void {
                add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ), 10, 0 );
        }

        public function enqueue_front(): void {
                if ( function_exists( 'has_shortcode' ) && is_singular() ) {
                        $post = get_post();
                        if ( $post && has_shortcode( (string) $post->post_content, 'fbm_dashboard' ) ) {
                                wp_register_style( 'fbm-frontend-dashboard', FBM_URL . 'assets/css/frontend-dashboard.css', array(), Plugin::FBM_VERSION );
                                wp_enqueue_style( 'fbm-frontend-dashboard' );
                        }
                }
        }

        public function enqueue_admin(): void {
                $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
                if ( ! $screen || ( strpos( $screen->id, 'toplevel_page_fbm' ) !== 0 && strpos( $screen->id, 'foodbank_page_fbm_' ) !== 0 ) ) {
                        return;
                }
                wp_register_style( 'fbm-admin', FBM_URL . 'assets/css/admin.css', array(), Plugin::FBM_VERSION );
                wp_add_inline_style( 'fbm-admin', self::theme_css() );
                wp_enqueue_style( 'fbm-admin' );

                if ( $screen->id === 'foodbank_page_fbm_attendance' && current_user_can( 'fb_manage_attendance' ) ) {
                        wp_enqueue_script( 'fbm-qrcode', FBM_URL . 'assets/js/qrcode.min.js', array(), Plugin::FBM_VERSION, true );
                }
        }

        private static function theme_css(): string {
                $opt     = Options::get( 'theme', array() );
                $primary = (string) ( $opt['primary_color'] ?? '#3b82f6' );
                $density = (string) ( $opt['density'] ?? 'comfortable' );
                $font_map = array(
                        'system'    => 'system-ui, sans-serif',
                        'inter'     => '"Inter", system-ui, sans-serif',
                        'roboto'    => '"Roboto", system-ui, sans-serif',
                        'open-sans' => '"Open Sans", system-ui, sans-serif',
                );
                $font = $font_map[ $opt['font_family'] ?? 'system' ] ?? $font_map['system'];
                $dark = ! empty( $opt['dark_mode_default'] ) ? '1' : '0';
                $css  = ':root{' .
                        '--fbm-primary:' . $primary . ';' .
                        '--fbm-density:' . $density . ';' .
                        '--fbm-font:' . $font . ';' .
                        '--fbm-dark:' . $dark . ';' .
                        '}';
                $custom = (string) ( $opt['custom_css'] ?? '' );
                if ( $custom !== '' ) {
                        $css .= "\n" . $custom;
                }
                return $css;
        }
}
