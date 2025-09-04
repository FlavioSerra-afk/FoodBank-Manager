<?php
/**
 * Forms builder admin page controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Forms\PresetsRepo;
use FoodBankManager\Forms\Schema;
use function sanitize_key;
use function sanitize_text_field;

/**
 * Forms builder page.
 */
final class FormsBuilderPage {
	/**
	 * Render the page and handle submissions.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'fb_manage_forms' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			wp_die( esc_html__( 'Access denied', 'foodbank-manager' ) );
		}
		$action = isset( $_POST['fbm_action'] ) ? sanitize_key( wp_unslash( (string) $_POST['fbm_action'] ) ) : '';
		if ( 'save' === $action ) {
				check_admin_referer( 'fbm_forms_builder_save' );
				$raw = sanitize_textarea_field( wp_unslash( $_POST['schema'] ?? '' ) );
				$arr = json_decode( $raw, true );
			if ( is_array( $arr ) ) {
				$schema = Schema::normalize( $arr );
				PresetsRepo::upsert( $schema );
			}
		} elseif ( 'delete' === $action ) {
			check_admin_referer( 'fbm_forms_builder_delete' );
			$slug = sanitize_key( wp_unslash( (string) ( $_POST['slug'] ?? '' ) ) );
			if ( $slug ) {
				PresetsRepo::delete( $slug );
			}
		}
		$presets = PresetsRepo::list();
		$current = null;
		$edit    = isset( $_GET['slug'] ) ? sanitize_key( wp_unslash( (string) $_GET['slug'] ) ) : '';
		if ( $edit ) {
			$current = PresetsRepo::get_by_slug( $edit );
		}
		$nonce_save   = wp_create_nonce( 'fbm_forms_builder_save' );
		$nonce_delete = wp_create_nonce( 'fbm_forms_builder_delete' );
		include FBM_PATH . 'templates/admin/forms-builder.php';
	}
}
