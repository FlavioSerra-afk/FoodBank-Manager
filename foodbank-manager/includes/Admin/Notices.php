<?php // phpcs:ignoreFile
/**
 * Admin notices.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

/**
 * Notices helper.
 */
class Notices {

        /**
         * Show missing KEK notice.
         */
        public static function missing_kek(): void {
                if ( ! current_user_can( 'manage_options' ) ) {
                        return;
                }
                add_action(
                        'admin_notices',
                        function (): void {
                                echo '<div class="notice notice-error"><p>' . \esc_html__( 'FoodBank Manager encryption key is not configured.', 'foodbank-manager' ) . '</p></div>';
                        }
                );
        }
}
