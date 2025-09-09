<?php // phpcs:ignoreFile
/**
 * Design & Theme admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Options;
use FoodBankManager\UI\Theme;
use function add_query_arg;
use function sanitize_key;
use function wp_unslash;

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
        $tab    = sanitize_key( (string) ( $_GET['tab'] ?? 'admin' ) );
        if ( ! in_array( $tab, array( 'admin', 'front' ), true ) ) {
            $tab = 'admin';
        }
        if ( 'POST' === $method ) {
            self::handle_post( $tab );
        }
        $theme = Theme::get();
        require FBM_PATH . 'templates/admin/theme.php';
    }

    /**
     * Handle form submission.
     */
    private static function handle_post( string $tab ): void {
        check_admin_referer( 'fbm_theme_save' );
        if ( ! current_user_can( 'fb_manage_theme' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
        }
        $existing = Theme::get();
        $raw      = $existing;
        if ( isset( $_POST['fbm_theme'] ) && is_array( $_POST['fbm_theme'] ) ) {
            $raw = array_replace_recursive( $raw, wp_unslash( $_POST['fbm_theme'] ) );
        }
        $sanitized = Theme::sanitize( $raw );
        Options::update( array( 'theme' => $sanitized ) );
        $url = add_query_arg( array( 'notice' => 'saved', 'tab' => $tab ), menu_page_url( 'fbm_theme', false ) );
        wp_safe_redirect( esc_url_raw( $url ) );
        exit;
    }
}
