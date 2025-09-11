<?php
/**
 * Diagnostics privacy panel controller.
 *
 * @package FoodBankManager\Admin
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use function add_settings_error;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function filter_input;
use function sanitize_email;
use function sanitize_key;
use function wp_die;
use function wp_unslash;

/**
 * Controller for Diagnostics → Privacy panel.
 */
final class DiagnosticsPrivacy {
    private const ACTION_PREVIEW   = 'fbm_privacy_preview';
    private const ACTION_ERASE_DRY = 'fbm_privacy_erase_dry';
    private const ACTION_ERASE     = 'fbm_privacy_erase';

    /** @var array<string,int> */
    private static array $preview = array();
    /** @var array<string,mixed> */
    private static array $erasure = array();

    /**
     * Handle panel actions.
     */
    public static function handle_actions(): void {
        $action_raw = $_POST['fbm_privacy_action'] ?? null;
        if ( null === $action_raw ) {
            return;
        }
        $action = sanitize_key( (string) wp_unslash( $action_raw ) );
        $email_raw = $_POST['email'] ?? null;
        $email = sanitize_email( (string) wp_unslash( (string) $email_raw ) );
        if ( '' === $email ) {
            return;
        }

        if ( self::ACTION_PREVIEW === $action ) {
            check_admin_referer( self::ACTION_PREVIEW );
            if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
                wp_die( esc_html__( 'Insufficient permissions', 'foodbank-manager' ) );
            }
            $result = \FBM\Privacy\Exporter::export( $email, 1 );
            $counts = array();
            foreach ( $result['data'] as $row ) {
                $counts[ $row['group_id'] ] = ( $counts[ $row['group_id'] ] ?? 0 ) + 1;
            }
            self::$preview = $counts;
            add_settings_error( 'fbm_diagnostics', 'fbm_privacy_preview', __( 'Preview generated.', 'foodbank-manager' ), 'updated' );
            return;
        }

        if ( self::ACTION_ERASE_DRY === $action || self::ACTION_ERASE === $action ) {
            $nonce_action = self::ACTION_ERASE_DRY === $action ? self::ACTION_ERASE_DRY : self::ACTION_ERASE;
            check_admin_referer( $nonce_action );
            if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
                wp_die( esc_html__( 'Insufficient permissions', 'foodbank-manager' ) );
            }
            $dry           = self::ACTION_ERASE_DRY === $action;
            $result        = \FBM\Privacy\Eraser::run( $email, 1, $dry );
            self::$erasure = $result;
            $msg           = $dry ? __( 'Dry-run complete.', 'foodbank-manager' ) : __( 'Erasure complete.', 'foodbank-manager' );
            add_settings_error( 'fbm_diagnostics', 'fbm_privacy_erase', $msg, 'updated' );
        }
    }

    /**
     * Render panel template.
     */
    public static function render_panel(): void {
        /* @psalm-suppress UnresolvableInclude */
        require FBM_PATH . 'templates/admin/diagnostics-privacy.php';
    }

    /**
     * Get last preview summary.
     *
     * @return array<string,int>
     */
    public static function preview_summary(): array {
        return self::$preview;
    }

    /**
     * Get last erasure summary.
     *
     * @return array<string,mixed>
     */
    public static function erasure_summary(): array {
        return self::$erasure;
    }
}
