<?php
/**
 * Single entry view handler.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Database\ApplicationsRepo;
use FoodBankManager\Exports\PdfExporter;
use FoodBankManager\Security\Helpers;

/**
 * Entry page controller.
 */
class EntryPage {
		/**
		 * Register handler on current_screen.
		 *
		 * @return void
		 */
	public static function register(): void {
		if ( function_exists( 'add_action' ) ) {
				\add_action( 'current_screen', array( self::class, 'handle' ) );
		}
	}

		/**
		 * Handle entry view actions.
		 *
		 * @return void
		 */
	public static function handle(): void {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'foodbank_page_fbm_database' !== $screen->id ) {
				return;
		}

			$action = isset( $_GET['fbm_action'] ) ? sanitize_key( wp_unslash( (string) $_GET['fbm_action'] ) ) : '';
		if ( 'view_entry' !== $action ) {
				return;
		}

			( new self() )->render();
	}

		/**
		 * Render entry view.
		 *
		 * @return void
		 */
	public function render(): void {
			$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) ) : '';
		if ( ! current_user_can( 'fb_manage_database' ) ) {
			if ( 'POST' === $method ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
			}
				echo '<div class="wrap fbm-admin">'
						. '<div class="fbm-notice fbm-notice--error">You do not have permission to access this page.</div>'
						. '</div>';
				return;
		}

			$id = isset( $_GET['entry_id'] ) ? absint( $_GET['entry_id'] ) : 0;
		if ( ! $id ) {
				wp_die( esc_html__( 'Invalid entry ID.', 'foodbank-manager' ), '', array( 'response' => 400 ) );
		}

			check_admin_referer( 'fbm_entry_view' );

			$unmask = false;
		if ( 'POST' === $method ) {
				$post_action = isset( $_POST['fbm_action'] ) ? sanitize_key( wp_unslash( $_POST['fbm_action'] ) ) : '';
			if ( 'unmask_entry' === $post_action && current_user_can( 'fb_view_sensitive' ) ) {
					check_admin_referer( 'fbm_entry_unmask', 'fbm_nonce' );
					$unmask = true;
			} elseif ( 'entry_pdf' === $post_action ) {
					check_admin_referer( 'fbm_entry_pdf', 'fbm_nonce' );
					$entry = ApplicationsRepo::get_entry( $id );
				if ( ! $entry ) {
						wp_die( esc_html__( 'Entry not found.', 'foodbank-manager' ), '', array( 'response' => 404 ) );
				}
					$result = PdfExporter::render_entry( $entry, array() );
				if ( ! headers_sent() ) {
						nocache_headers();
						header( 'Content-Type: ' . $result['content_type'] );
						header( 'Content-Disposition: attachment; filename="' . $result['filename'] . '"' );
				}
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- raw PDF/HTML output
					echo $result['body'];
					return;
			}
		}

			$entry = ApplicationsRepo::get_entry( $id );
		if ( ! $entry ) {
				wp_die( esc_html__( 'Entry not found.', 'foodbank-manager' ), '', array( 'response' => 404 ) );
		}

		if ( ! $unmask ) {
				$entry['pii']['email'] = Helpers::mask_email( $entry['pii']['email'] ?? '' );
		}

			$can_sensitive = current_user_can( 'fb_view_sensitive' );
			require FBM_PATH . 'templates/admin/entry.php';
	}
}

\class_alias( __NAMESPACE__ . '\\EntryPage', 'FBM\\Admin\\EntryPage' );
