<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Database\ApplicationsRepo;
use FoodBankManager\Exports\CsvExporter;
use FoodBankManager\Security\Crypto;
use FoodBankManager\Security\Helpers;

class DatabasePage {
	public static function route(): void {
		if ( ! current_user_can( 'fb_read_entries' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : '';
		if ( $action === 'fbm_export_entries' ) {
			self::handleExportList();
			return;
		}
		if ( $action === 'fbm_export_single' ) {
			self::handleExportSingle();
			return;
		}
		if ( $action === 'fbm_delete_entry' ) {
			self::handleDelete();
			return;
		}

		if ( isset( $_GET['view'] ) ) {
			self::renderSingle();
		} else {
			self::renderList();
		}
	}

	private static function parseFilters(): array {
		$args              = array();
		$args['status']    = isset( $_GET['status'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['status'] ) ) : '';
		$args['date_from'] = isset( $_GET['date_from'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['date_from'] ) ) : '';
		$args['date_to']   = isset( $_GET['date_to'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['date_to'] ) ) : '';
		$args['city']      = isset( $_GET['city'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['city'] ) ) : '';
		$args['postcode']  = isset( $_GET['postcode'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['postcode'] ) ) : '';
		$args['search']    = isset( $_GET['search'] ) ? Helpers::sanitize_text( substr( wp_unslash( (string) $_GET['search'] ), 0, 64 ) ) : '';
		$args['has_file']  = isset( $_GET['has_file'] ) ? (bool) $_GET['has_file'] : null;
		$args['consent']   = isset( $_GET['consent'] ) ? (bool) $_GET['consent'] : null;
		$args['page']      = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : ( isset( $_GET['page'] ) ? max( 1, (int) $_GET['page'] ) : 1 );
		$user_per_page     = (int) get_user_meta( get_current_user_id(), 'fbm_db_per_page', true );
		$args['per_page']  = $user_per_page > 0 ? $user_per_page : 25;
		if ( isset( $_GET['per_page'] ) ) {
			$per = (int) $_GET['per_page'];
			if ( in_array( $per, array( 25, 50, 100 ), true ) ) {
				$args['per_page'] = $per;
				update_user_meta( get_current_user_id(), 'fbm_db_per_page', $per );
			}
		}
		$args['orderby'] = isset( $_GET['orderby'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['orderby'] ) ) : 'created_at';
		$args['order']   = isset( $_GET['order'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['order'] ) ) : 'DESC';
		return $args;
	}

	private static function handleExportList(): void {
		if ( ! current_user_can( 'fb_export_entries' ) ) {
			wp_die( '', '', array( 'response' => 403 ) );
		}
		Helpers::require_nonce( 'fbm_db_export' );
		$filters       = self::parseFilters();
		$data          = ApplicationsRepo::list( $filters );
		$rows          = array();
		$can_sensitive = current_user_can( 'read_sensitive' );
		foreach ( $data['rows'] as $row ) {
			$rows[] = self::normalizeExportRow( $row, $can_sensitive );
		}
		$mask = ! ( $can_sensitive && isset( $_GET['unmask'] ) && Helpers::verify_nonce( 'fbm_db_export' ) );
		CsvExporter::streamList( $rows, $mask );
		exit;
	}

	private static function handleExportSingle(): void {
		if ( ! current_user_can( 'fb_export_entries' ) ) {
			wp_die( '', '', array( 'response' => 403 ) );
		}
		$id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : 0;
		Helpers::require_nonce( 'fbm_db_single_export_' . $id );
		$entry = ApplicationsRepo::get( $id );
		if ( $entry ) {
			$can_sensitive = current_user_can( 'read_sensitive' );
			$row           = self::normalizeExportRow( $entry, $can_sensitive );
			$mask          = ! ( $can_sensitive && isset( $_REQUEST['unmask'] ) );
			CsvExporter::streamList( array( $row ), $mask, 'fbm-entry-' . $id . '.csv' );
		}
		exit;
	}

	private static function handleDelete(): void {
		if ( ! current_user_can( 'fb_delete_entries' ) ) {
			wp_die( '', '', array( 'response' => 403 ) );
		}
		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		Helpers::require_nonce( 'fbm_db_delete_' . $id );
		ApplicationsRepo::softDelete( $id );
		wp_safe_redirect( remove_query_arg( array( 'action', 'id' ) ) );
		exit;
	}

	private static function renderList(): void {
		$filters       = self::parseFilters();
		$data          = ApplicationsRepo::list( $filters );
		$rows          = $data['rows'];
		$total         = $data['total'];
		$page          = $filters['page'];
		$per_page      = $filters['per_page'];
		$can_sensitive = current_user_can( 'read_sensitive' );
		$unmask        = $can_sensitive && isset( $_GET['unmask'] ) && Helpers::verify_nonce( 'fbm_db_unmask' );
		require FBM_PATH . 'templates/admin/database.php';
	}

	private static function renderSingle(): void {
		$id    = (int) $_GET['view'];
		$entry = ApplicationsRepo::get( $id );
		if ( ! $entry ) {
			wp_die( esc_html__( 'Entry not found', 'foodbank-manager' ) );
		}
		$can_sensitive = current_user_can( 'read_sensitive' );
		require FBM_PATH . 'templates/admin/database-view.php';
	}

	/**
	 * @param array $row DB row from repo
	 * @param bool  $can_sensitive whether user can view unmasked
	 * @return array
	 */
	private static function normalizeExportRow( array $row, bool $can_sensitive ): array {
		$data     = json_decode( (string) ( $row['data_json'] ?? '' ), true ) ?: array();
		$pii      = Crypto::decryptSensitive( (string) ( $row['pii_encrypted_blob'] ?? '' ) );
		$email    = $pii['email'] ?? '';
		$postcode = $data['postcode'] ?? '';
		return array(
			'id'         => (int) $row['id'],
			'created_at' => (string) $row['created_at'],
			'name'       => trim( ( $data['first_name'] ?? '' ) . ' ' . ( $pii['last_name'] ?? '' ) ),
			'email'      => $email,
			'postcode'   => $postcode,
			'status'     => (string) $row['status'],
		);
	}
}
