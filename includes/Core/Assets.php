<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

class Assets {

	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
	}

	public function enqueue_front(): void {
		// Placeholder for front-end assets.
	}

        public function enqueue_admin( string $hook ): void {
                if ( $hook !== 'foodbank-manager_page_fbm-attendance' ) {
                        return;
                }
                if ( ! current_user_can( 'fb_manage_attendance' ) ) {
                        return;
                }
                wp_enqueue_script(
                        'fbm-qrcode',
                        FBM_URL . 'assets/js/qrcode.min.js',
                        array(),
                        Plugin::FBM_VERSION,
                        true
                );
        }
}
