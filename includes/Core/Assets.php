<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Core\Options;

class Assets {
        public function register(): void {
                add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
        }

        public function enqueue_front(): void {
                wp_register_style( 'fbm-theme-frontend', FBM_URL . 'assets/css/theme-frontend.css', array(), Plugin::FBM_VERSION );
                wp_add_inline_style( 'fbm-theme-frontend', self::theme_css() );
                wp_enqueue_style( 'fbm-theme-frontend' );
        }

        public function enqueue_admin( string $hook ): void {
                wp_register_style( 'fbm-theme-admin', FBM_URL . 'assets/css/theme-admin.css', array(), Plugin::FBM_VERSION );
                wp_add_inline_style( 'fbm-theme-admin', self::theme_css() );
                wp_enqueue_style( 'fbm-theme-admin' );

                if ( $hook === 'foodbank-manager_page_fbm-attendance' && current_user_can( 'fb_manage_attendance' ) ) {
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
