<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName
/**
 * Database admin page controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Database\ApplicationsRepo;
use FoodBankManager\Exports\CsvExporter;
use FoodBankManager\Security\Crypto;

/**
 * Database admin page.
 *
 * @since 0.1.x
 */
final class DatabasePage {
	/**
	 * Route the database page.
	 *
	 * @since 0.1.x
	 *
	 * @return void
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_database' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

				$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) : '';
		if ( 'POST' === strtoupper( $method ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized and checked below.
			$action = isset( $_POST['fbm_action'] ) ? sanitize_key( wp_unslash( $_POST['fbm_action'] ) ) : '';
			switch ( $action ) {
				case 'export_entries':
					check_admin_referer( 'fbm_export_entries', 'fbm_nonce' );
					$filters = self::get_filters();
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- IDs sanitized below.
					if ( isset( $_POST['id'] ) ) {
								$filters['id'] = absint( wp_unslash( $_POST['id'] ) );
					}
					$mask = ! current_user_can( 'fb_view_sensitive' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
					self::do_export( $filters, $mask );
					return;
				case 'delete_entry':
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- IDs sanitized below.
					$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
					check_admin_referer( 'fbm_delete_entry_' . $id, 'fbm_nonce' );
					self::do_delete( $id );
					return;
				case 'export_single':
						// Legacy single export support.
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- IDs sanitized below.
						$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
						check_admin_referer( 'fbm_export_single_' . $id, 'fbm_nonce' );
						$filters = self::get_filters();
					if ( $id ) {
							$filters['id'] = $id;
					}
						$mask = ! current_user_can( 'fb_view_sensitive' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
						self::do_export( $filters, $mask );
					return;
			}
		}

				// Support legacy `view` query param.
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only ID parameter.
				$view_id = isset( $_GET['view'] ) ? absint( $_GET['view'] ) : 0;
		if ( $view_id ) {
				self::render_view( $view_id );
				return;
		}

				$filters = self::get_filters();
				self::render_list( $filters );
	}

	/**
	 * Render list view.
	 *
	 * @since 0.1.x
	 *
	 * @param array<string, mixed> $filters Filters.
	 *
	 * @return void
	 */
	private static function render_list( array $filters ): void {
		$data     = ApplicationsRepo::list( $filters );
		$rows     = $data['rows'];
		$total    = $data['total'];
		$page     = $filters['page'];
		$per_page = $filters['per_page'];

		$can_sensitive = current_user_can( 'fb_view_sensitive' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
		$unmask        = false;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce validated below.
		if ( $can_sensitive && isset( $_GET['unmask'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only nonce param; sanitized here.
			$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			$unmask = $nonce && wp_verify_nonce( $nonce, 'fbm_db_unmask' );
		}

		require FBM_PATH . 'templates/admin/database.php';
	}

	/**
	 * Render a single application.
	 *
	 * @since 0.1.x
	 *
	 * @param int $id Application ID.
	 *
	 * @return void
	 */
	private static function render_view( int $id ): void {
		$entry = ApplicationsRepo::get( $id );
		if ( ! $entry ) {
			wp_die( esc_html__( 'Entry not found.', 'foodbank-manager' ) );
		}
		$can_sensitive = current_user_can( 'fb_view_sensitive' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
		require FBM_PATH . 'templates/admin/database-view.php';
	}

	/**
	 * Delete an application.
	 *
	 * @since 0.1.x
	 *
	 * @param int $id Application ID.
	 *
	 * @return void
	 */
	private static function do_delete( int $id ): void {
		if ( ! current_user_can( 'fb_manage_database' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

				check_admin_referer( 'fbm_delete_entry_' . $id, 'fbm_nonce' );
		ApplicationsRepo::softDelete( $id );

		$url = add_query_arg(
			array( 'deleted' => 1 ),
			menu_page_url( 'fbm-database', false )
		);
		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * Stream CSV export.
	 *
	 * @since 0.1.x
	 *
	 * @param array<string, mixed> $filters Filters.
	 * @param bool                 $mask   Whether to mask sensitive fields.
	 *
	 * @return void
	 */
	private static function do_export( array $filters, bool $mask ): void {
		if ( ! current_user_can( 'fb_manage_database' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

			$rows     = array();
			$filename = 'fbm-entries.csv';

		if ( isset( $filters['id'] ) ) {
				$entry = ApplicationsRepo::get( (int) $filters['id'] );
			if ( $entry ) {
				$rows[]   = self::normalize_export_row( $entry );
				$filename = 'fbm-entry-' . (int) $filters['id'] . '.csv';
			}
		} else {
				$data = ApplicationsRepo::list( $filters );
			foreach ( $data['rows'] as $row ) {
					$rows[] = self::normalize_export_row( $row );
			}
		}

			CsvExporter::streamList( $rows, $mask, $filename );
			exit;
	}

	/**
	 * Normalize a DB row for export.
	 *
	 * @since 0.1.x
	 *
	 * @param array<string, mixed> $row Row from repo.
	 *
	 * @return array<string, mixed>
	 */
	private static function normalize_export_row( array $row ): array {
		$data = json_decode( (string) ( $row['data_json'] ?? '' ), true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		$pii      = Crypto::decryptSensitive( (string) ( $row['pii_encrypted_blob'] ?? '' ) );
		$email    = $pii['email'] ?? '';
		$postcode = $data['postcode'] ?? '';

		return array(
			'id'         => (int) ( $row['id'] ?? 0 ),
			'created_at' => (string) ( $row['created_at'] ?? '' ),
			'name'       => trim( ( $data['first_name'] ?? '' ) . ' ' . ( $pii['last_name'] ?? '' ) ),
			'email'      => $email,
			'postcode'   => $postcode,
			'status'     => (string) ( $row['status'] ?? '' ),
		);
	}

	/**
	 * Parse filters from the request.
	 *
	 * @since 0.1.x
	 *
	 * @return array<string, mixed>
	 */
	private static function get_filters(): array {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
			$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
			$status         = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
			$allowed_status = array( 'new', 'approved', 'archived' );
		if ( ! in_array( $status, $allowed_status, true ) ) {
				$status = '';
		}

				$has_file = isset( $_GET['has_file'] ) ? true : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.
				$consent  = isset( $_GET['consent'] ) ? true : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.

				$date_from = isset( $_GET['date_from'] ) ? self::sanitize_date( wp_unslash( $_GET['date_from'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by sanitize_date().
				$date_to   = isset( $_GET['date_to'] ) ? self::sanitize_date( wp_unslash( $_GET['date_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by sanitize_date().

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
			$page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
			$per_page = isset( $_GET['per_page'] ) ? min( 100, max( 10, absint( $_GET['per_page'] ) ) ) : 20;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
			$orderby         = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'created_at';
			$allowed_orderby = array( 'created_at', 'status', 'id' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
				$orderby = 'created_at';
		}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
			$order = isset( $_GET['order'] ) ? strtoupper( sanitize_key( wp_unslash( $_GET['order'] ) ) ) : 'DESC';
		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
				$order = 'DESC';
		}

			return array(
				'search'    => $search,
				'status'    => $status,
				'has_file'  => is_null( $has_file ) ? null : (bool) $has_file,
				'consent'   => is_null( $consent ) ? null : (bool) $consent,
				'date_from' => $date_from,
				'date_to'   => $date_to,
				'page'      => $page,
				'per_page'  => $per_page,
				'orderby'   => $orderby,
				'order'     => $order,
			);
	}

		/**
		 * Validate and sanitize an ISO date (Y-m-d).
		 *
		 * @param string $raw Raw date string.
		 *
		 * @return string
		 */
	private static function sanitize_date( string $raw ): string {
			$raw = sanitize_text_field( $raw );
			return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
	}
}
