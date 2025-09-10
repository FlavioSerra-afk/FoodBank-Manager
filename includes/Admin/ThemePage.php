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
use function register_setting;
use function sanitize_key;
use function wp_unslash;

/**
 * Theme admin page.
 */
class ThemePage {
    /**
     * Boot settings registration.
     */
    public static function boot(): void {
        register_setting( 'fbm_theme', 'fbm_settings', array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
    }

    /**
     * Route the theme page.
     */
    public static function route(): void {
        if ( ! current_user_can( 'fb_manage_theme' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
        }
        $tab = sanitize_key( (string) ( $_GET['tab'] ?? 'admin' ) );
        if ( ! in_array( $tab, array( 'admin', 'front' ), true ) ) {
            $tab = 'admin';
        }
        $theme = Theme::get();
        require FBM_PATH . 'templates/admin/theme.php';
    }

    /**
     * Sanitize and persist settings via Settings API.
     *
     * @param array<string,mixed> $input Raw input.
     * @return array<string,mixed>
     */
    public static function sanitize( $input ): array {
        $input = is_array( $input ) ? $input : array();
        $raw   = Options::all();
        $raw   = array_replace_recursive( $raw, $input );
        $theme = Theme::sanitize( $raw['theme'] ?? array() );
        Options::update( array( 'theme' => $theme ) );
        $raw['theme'] = $theme;
        return $raw;
    }
}
