<?php // phpcs:ignoreFile
/**
 * Design & Theme admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Options;

/**
 * Theme admin page.
 */
class ThemePage {
        /**
         * Route the theme page.
         */
        public static function route(): void {
                if ( ! current_user_can( 'fb_manage_theme' ) ) {
                        wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
                }
                $method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) );
                if ( 'POST' === $method ) {
                        self::handle_post();
                }
                $theme = Options::get( 'theme', array() );
                require FBM_PATH . 'templates/admin/theme.php';
        }

        /**
         * Handle form submission.
         */
        private static function handle_post(): void {
                check_admin_referer( 'fbm_theme_save' );
                if ( ! current_user_can( 'fb_manage_theme' ) ) {
                        wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
                }
                $data = array();
                if ( isset( $_POST['fbm_theme'] ) && is_array( $_POST['fbm_theme'] ) ) {
                        $data = map_deep( wp_unslash( $_POST['fbm_theme'] ), 'sanitize_text_field' );
                }
                Options::update( array( 'theme' => $data ) );
                $url = add_query_arg( 'notice', 'saved', menu_page_url( 'fbm_theme', false ) );
                wp_safe_redirect( esc_url_raw( $url ) );
                exit;
        }
}
