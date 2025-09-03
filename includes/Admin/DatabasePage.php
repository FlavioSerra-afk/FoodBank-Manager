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

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing param; sanitized here.
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['action'] ) ) : 'list';

		// Map legacy action names.
		if ( 'fbm_export_entries' === $action || 'fbm_export_single' === $action ) {
			$action = 'export';
		} elseif ( 'fbm_delete_entry' === $action ) {
			$action = 'delete';
		}

		// Support legacy `view` query param.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only ID parameter.
		if ( 'view' === $action || isset( $_GET['view'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- IDs sanitized below.
			$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : absint( $_GET['view'] ?? 0 );
			self::render_view( $id );
			return;
		}

		if ( 'delete' === $action ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- ID sanitized below.
			$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
			self::do_delete( $id );
			return;
		}

		if ( 'export' === $action ) {
			$filters = self::get_filters();
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only param; sanitized here.
			$unmask = isset( $_GET['unmask'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['unmask'] ) );
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- ID sanitized here.
			$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
			if ( $id ) {
				$filters['id'] = $id;
			}
			self::do_export( $filters, $unmask );
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

		check_admin_referer( 'fbm_db_delete_' . $id );
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
	 * @param bool                 $unmask Whether to unmask PII.
	 *
	 * @return void
	 */
	private static function do_export( array $filters, bool $unmask ): void {
		if ( ! current_user_can( 'fb_manage_database' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

		$rows     = array();
		$filename = 'fbm-entries.csv';

		if ( isset( $filters['id'] ) ) {
			check_admin_referer( 'fbm_db_single_export_' . (int) $filters['id'] );
			$entry = ApplicationsRepo::get( (int) $filters['id'] );
			if ( $entry ) {
				$rows[]   = self::normalize_export_row( $entry );
				$filename = 'fbm-entry-' . (int) $filters['id'] . '.csv';
			}
		} else {
			check_admin_referer( 'fbm_db_export' );
			$data = ApplicationsRepo::list( $filters );
			foreach ( $data['rows'] as $row ) {
				$rows[] = self::normalize_export_row( $row );
			}
		}

		$can_sensitive = current_user_can( 'fb_view_sensitive' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
		$mask          = ! ( $unmask && $can_sensitive );
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
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified below.
		$filter_nonce = isset( $_GET['_fbm_filter_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_fbm_filter_nonce'] ) ) : '';
		if ( $filter_nonce && ! wp_verify_nonce( $filter_nonce, 'fbm_filters' ) ) {
			add_settings_error(
				'fbm',
				'fbm_bad_filter_nonce',
				esc_html__( 'Filter token expired. Showing unfiltered results.', 'foodbank-manager' )
			);

			return array(
				'search'    => '',
				'status'    => '',
				'has_file'  => null,
				'date_from' => '',
				'date_to'   => '',
				'page'      => 1,
				'per_page'  => 20,
				'orderby'   => 'created_at',
				'order'     => 'DESC',
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
		$s = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
		$hasfile = isset( $_GET['hasfile'] ) ? (int) ( sanitize_text_field( wp_unslash( $_GET['hasfile'] ) ) ? 1 : 0 ) : null;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
		$date_from = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
		$date_to = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
		$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
		$perpage = isset( $_GET['per_page'] ) ? min( 100, max( 10, absint( $_GET['per_page'] ) ) ) : 20;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters; sanitized immediately.
		$order = ( isset( $_GET['order'] ) && 'ASC' === strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) ) ? 'ASC' : 'DESC';

		$allowed = array( 'created_at', 'status', 'id', 'last_attended', 'visits_range', 'noshows_range', 'visits_12m' );
		if ( ! in_array( $orderby, $allowed, true ) ) {
			$orderby = 'created_at';
		}

		return array(
			'search'    => $s,
			'status'    => $status,
			'has_file'  => is_null( $hasfile ) ? null : (bool) $hasfile,
			'date_from' => $date_from,
			'date_to'   => $date_to,
			'page'      => $paged,
			'per_page'  => $perpage,
			'orderby'   => $orderby,
			'order'     => $order,
		);
	}
}
