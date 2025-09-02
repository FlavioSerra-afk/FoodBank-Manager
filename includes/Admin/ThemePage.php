<?php
/**
 * Design & Theme admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Options;
use FoodBankManager\Security\Helpers;

class ThemePage {
        public static function route(): void {
                if ( ! current_user_can( 'fb_manage_settings' ) && ! current_user_can( 'manage_options' ) ) {
                        wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
                }
                if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
                        self::handle_post();
                }
        }

        private static function handle_post(): void {
                if ( isset( $_POST['fbm_theme_export'] ) ) {
                        if ( Helpers::verify_nonce( 'fbm_theme_export', 'fbm_theme_export_nonce' ) ) {
                                $theme = Options::get( 'theme', array() );
                                nocache_headers();
                                header( 'Content-Type: application/json' );
                                header( 'Content-Disposition: attachment; filename=fbm-theme.json' );
                                echo wp_json_encode( $theme );
                                exit;
                        }
                }

                if ( isset( $_POST['fbm_theme_import'] ) ) {
                        if ( ! Helpers::verify_nonce( 'fbm_theme_import', 'fbm_theme_import_nonce' ) ) {
                                wp_die( esc_html__( 'Invalid nonce', 'foodbank-manager' ), '', array( 'response' => 403 ) );
                        }
                        $file = $_FILES['theme_file']['tmp_name'] ?? '';
                        if ( $file && is_readable( $file ) ) {
                                $json = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                                $data = json_decode( (string) $json, true );
                                if ( is_array( $data ) ) {
                                        $settings            = Options::all();
                                        $settings['theme']   = $data;
                                        Options::saveAll( $settings );
                                        add_settings_error( 'fbm-theme', 'fbm_theme_imported', esc_html__( 'Theme imported.', 'foodbank-manager' ), 'updated' );
                                }
                        }
                        return;
                }

                if ( ! Helpers::verify_nonce( 'fbm_theme_save', 'fbm_theme_nonce' ) ) {
                        wp_die( esc_html__( 'Invalid nonce', 'foodbank-manager' ), '', array( 'response' => 403 ) );
                }
                $data     = isset( $_POST['fbm_theme'] ) && is_array( $_POST['fbm_theme'] ) ? wp_unslash( $_POST['fbm_theme'] ) : array();
                $settings = Options::all();
                $settings['theme'] = $data;
                Options::saveAll( $settings );
                add_settings_error( 'fbm-theme', 'fbm_theme_saved', esc_html__( 'Theme saved.', 'foodbank-manager' ), 'updated' );
        }
}
