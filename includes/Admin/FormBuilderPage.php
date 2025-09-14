<?php
/**
 * Form builder admin page.
 *
 * @package FBM\Admin
 */

declare(strict_types=1);

namespace FBM\Admin;

use FBM\Forms\FormRepo;
use function current_user_can;
use function esc_html__;
use function check_admin_referer;
use function sanitize_text_field;
use function wp_unslash;
use function absint;
use function json_decode;
use function add_query_arg;
use function admin_url;
use function wp_safe_redirect;
use function wp_delete_post;

/**
 * Controller for the form builder page.
 */
final class FormBuilderPage {
	/**
	 * Route handler.
	 *
	 * @return void
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fbm_manage_forms' ) ) {
			echo '<div class="wrap fbm-admin"><p>' . esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) . '</p></div>';
			return;
		}
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET';
		if ( 'POST' === $method ) {
			$form_id = absint( $_POST['form_id'] ?? 0 );
			if ( isset( $_POST['delete_form'] ) ) {
				check_admin_referer( 'fbm_form_delete', '_fbm_nonce' );
				if ( $form_id ) {
					wp_delete_post( $form_id );
				}
				wp_safe_redirect(
					add_query_arg(
						array(
							'page'   => 'fbm_form_builder',
							'notice' => 'form_deleted',
						),
						admin_url( 'admin.php' )
					)
				);
				return;
			}
			check_admin_referer( 'fbm_form_save', '_fbm_nonce' );
			$title  = sanitize_text_field( wp_unslash( (string) ( $_POST['title'] ?? '' ) ) );
			$schema = json_decode( (string) wp_unslash( $_POST['schema_json'] ?? '[]' ), true );
			$data   = array(
				'title'          => $title,
				'schema'         => is_array( $schema ) ? $schema : array(),
				'mask_sensitive' => ! empty( $_POST['mask_sensitive'] ),
			);
			if ( $form_id ) {
				FormRepo::update( $form_id, $data );
			} else {
				$form_id = FormRepo::create( $data );
			}
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => 'fbm_form_builder',
						'form_id' => $form_id,
						'notice'  => 'form_saved',
					),
					admin_url( 'admin.php' )
				)
			);
			return;
		}
		$forms   = FormRepo::list();
		$current = null;
		$id      = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		if ( $id ) {
			$current = FormRepo::get( $id );
		} elseif ( isset( $_GET['new'] ) ) {
			$current = array(
				'id'             => 0,
				'title'          => '',
				'schema'         => array( 'fields' => array() ),
				'mask_sensitive' => true,
				'version'        => 1,
			);
		}
		require FBM_PATH . 'templates/admin/form-builder.php';
	}
}
