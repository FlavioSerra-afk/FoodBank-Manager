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
use function add_action;
use function add_settings_error;
use function __;
use function check_admin_referer;
use function current_user_can;
use function menu_page_url;
use function nocache_headers;
use function register_setting;
use function sanitize_key;
use function wp_die;
use function wp_json_encode;
use function wp_safe_redirect;
use function wp_unslash;

/**
 * Theme admin page.
 */
class ThemePage {
    /**
     * Boot settings registration.
     */
    public static function boot(): void {
        add_action(
            'admin_init',
            static function (): void {
                register_setting(
                    'fbm_theme',
                    'fbm_settings',
                    array(
                        'sanitize_callback' => '\\FBM\\Core\\Options::sanitize_all',
                        'type'              => 'array',
                    )
                );
            }
        );
        add_action( 'admin_post_fbm_theme_export', array( __CLASS__, 'export' ) );
        add_action( 'admin_post_fbm_theme_import', array( __CLASS__, 'import' ) );
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
     * Handle theme export.
     */
    public static function export(): void {
        if ( ! current_user_can( 'fb_manage_theme' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
        }
        $section = sanitize_key( (string) ( $_GET['section'] ?? 'admin' ) );
        if ( ! in_array( $section, array( 'admin', 'front' ), true ) ) {
            $section = 'admin';
        }
        $data = self::export_json( $section );
        nocache_headers();
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename=fbm-' . $section . '-theme.json' );
        echo wp_json_encode( $data );
        exit;
    }

    /**
     * Handle theme import from uploaded JSON.
     */
    public static function import(): void {
        if ( ! current_user_can( 'fb_manage_theme' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
        }
        check_admin_referer( 'fbm_theme_import' );
        $section = sanitize_key( (string) ( $_POST['section'] ?? 'admin' ) );
        if ( ! in_array( $section, array( 'admin', 'front' ), true ) ) {
            $section = 'admin';
        }
        $file = $_FILES['theme_json']['tmp_name'] ?? '';
        if ( ! $file || ! is_file( $file ) ) {
            add_settings_error( 'fbm_theme', 'fbm_theme', __( 'Invalid file.', 'foodbank-manager' ), 'error' );
            wp_safe_redirect( add_query_arg( 'tab', $section, menu_page_url( 'fbm_theme', false ) ) );
            exit;
        }
        $json = file_get_contents( $file );
        $data = json_decode( (string) $json, true );
        if ( ! is_array( $data ) || (int) ( $data['version'] ?? 0 ) !== 1 ) {
            add_settings_error( 'fbm_theme', 'fbm_theme', __( 'Invalid theme JSON.', 'foodbank-manager' ), 'error' );
            wp_safe_redirect( add_query_arg( 'tab', $section, menu_page_url( 'fbm_theme', false ) ) );
            exit;
        }
        self::import_json( $section, $data );
        add_settings_error( 'fbm_theme', 'fbm_theme', __( 'Theme imported.', 'foodbank-manager' ), 'updated' );
        wp_safe_redirect( add_query_arg( 'tab', $section, menu_page_url( 'fbm_theme', false ) ) );
        exit;
    }

    /**
     * Import theme data into options.
     *
     * @param string $section Section key.
     * @param array<string,mixed> $data Theme data.
     * @return bool
     */
    public static function import_json( string $section, array $data ): bool {
        if ( ! in_array( $section, array( 'admin', 'front' ), true ) ) {
            return false;
        }
        $san   = Theme::sanitize( array( $section => $data ) );
        $theme = Theme::get();
        $theme[ $section ] = $san[ $section ];
        if ( 'admin' === $section && ! empty( $theme['match_front_to_admin'] ) ) {
            $enabled            = $theme['front']['enabled'];
            $theme['front']     = array_merge( $san[ $section ], array( 'enabled' => $enabled ) );
        }
        Options::update( 'theme', $theme );
        return true;
    }

    /**
     * Export theme section as array.
     *
     * @param string $section Section key.
     * @return array<string,mixed>
     */
    public static function export_json( string $section ): array {
        $theme = Theme::get();
        $data  = $theme[ $section ] ?? array();
        return array( 'version' => 1 ) + $data;
    }
}
