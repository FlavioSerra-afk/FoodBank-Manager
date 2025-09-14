<?php
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
use FoodBankManager\Database\Columns;
use FoodBankManager\Core\Options;
use FoodBankManager\Admin\UsersMeta;

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
		if ( ! current_user_can( 'fb_manage_database' ) ) {
			wp_die(
				esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ),
				'',
				array(
					'response' => 403,
				)
			);
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) : '';
		if ( 'POST' === strtoupper( $method ) ) {
				$action = isset( $_POST['fbm_action'] ) ? sanitize_key( wp_unslash( $_POST['fbm_action'] ) ) : '';
			switch ( $action ) {
				case 'export_entries':
					check_admin_referer( 'fbm_export_entries', 'fbm_nonce' );
										$filters = self::get_filters();
										$id      = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
					if ( $id ) {
											$filters['id'] = $id;
					}
										$columns = UsersMeta::get_db_columns( get_current_user_id() );
										$unmask  = false;
					if ( current_user_can( 'fb_view_sensitive' ) && isset( $_POST['unmask'] ) ) {
							$nonce  = isset( $_POST['_wpnonce_unmask'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce_unmask'] ) ) : '';
							$unmask = (bool) wp_verify_nonce( $nonce, 'fbm_export_unmask' );
					}
										self::do_export( $filters, $columns, ! $unmask );
					return;
				case 'delete_entry':
					$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
					check_admin_referer( 'fbm_delete_entry_' . $id, 'fbm_nonce' );
					self::do_delete( $id );
					return;
				case 'export_single':
						// Legacy single export support.
												$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
												check_admin_referer( 'fbm_export_single_' . $id, 'fbm_nonce' );
												$filters = self::get_filters();
					if ( $id ) {
									$filters['id'] = $id;
					}
												$columns = UsersMeta::get_db_columns( get_current_user_id() );
												$unmask  = false;
					if ( current_user_can( 'fb_view_sensitive' ) && isset( $_POST['unmask'] ) ) {
							$nonce  = isset( $_POST['_wpnonce_unmask'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce_unmask'] ) ) : '';
							$unmask = (bool) wp_verify_nonce( $nonce, 'fbm_export_unmask' );
					}
												self::do_export( $filters, $columns, ! $unmask );
					return;
				case 'db_presets_save':
								check_admin_referer( 'fbm_database_db_presets_save' );
								$name = isset( $_POST['preset_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['preset_name'] ) ) : '';
								self::handle_preset_save( $name );
					return;
				case 'db_presets_delete':
								check_admin_referer( 'fbm_database_db_presets_delete' );
								$name = isset( $_POST['preset_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['preset_name'] ) ) : '';
								self::handle_preset_delete( $name );
					return;
				case 'db_columns_save':
								check_admin_referer( 'fbm_database_db_columns_save' );
								$cols = isset( $_POST['columns'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['columns'] ) ) : array();
								self::handle_columns_save( $cols );
					return;
			}
		}

								$query_string = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['QUERY_STRING'] ) ) : '';
								parse_str( $query_string, $query_vars );

				$fbm_action = isset( $query_vars['fbm_action'] ) ? sanitize_key( $query_vars['fbm_action'] ) : '';
		if ( 'view_entry' === $fbm_action ) {
				return;
		}

								$preset_name = isset( $query_vars['preset'] ) ? sanitize_text_field( $query_vars['preset'] ) : '';
		if ( $preset_name ) {
						$preset_query = self::get_preset_query( $preset_name );
			if ( $preset_query ) {
										$query_vars = array_merge( $preset_query, $query_vars );
			}
		}
								$filters = self::get_filters( $query_vars );
				$presets                 = Options::get_db_filter_presets();
								$columns = UsersMeta::get_db_columns( get_current_user_id() );
								self::render_list( $filters, $presets, $preset_name, $columns );
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
		/**
		 * Render list view.
		 *
		 * @param array<string,mixed>                                      $filters      Filters.
		 * @param array<int,array{name:string,query:array<string,string>}> $presets Presets.
		 * @param string                                                   $preset_name  Current preset name.
		 * @param array<int,string>                                        $columns      Visible columns.
		 *
		 * @return void
		 */
	private static function render_list( array $filters, array $presets, string $preset_name, array $columns ): void {
			$can_sensitive = current_user_can( 'fb_view_sensitive' );
			$unmask        = false;
			$query_string  = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['QUERY_STRING'] ) ) : '';
			parse_str( $query_string, $query_vars );
		if ( $can_sensitive && isset( $query_vars['unmask'] ) ) {
				$nonce  = isset( $query_vars['_wpnonce'] ) ? sanitize_text_field( $query_vars['_wpnonce'] ) : '';
				$unmask = $nonce && wp_verify_nonce( $nonce, 'fbm_db_unmask' );
		}

			$defs  = Columns::for_admin_list( $unmask );
			$table = new DatabaseTable( $filters, $columns, $unmask );
			$table->prepare_items();

			$current_preset = $preset_name;
			$columns        = $columns;
			$column_defs    = $defs;
			require FBM_PATH . 'templates/admin/database.php';
	}

		/**
		 * Save a filter preset.
		 *
		 * @param string $name Preset name.
		 *
		 * @return void
		 */
	private static function handle_preset_save( string $name ): void {
		$name = trim( $name );
		if ( '' === $name || strlen( $name ) > 50 ) {
					self::redirect_list( array( 'preset_error' => 1 ) );
		}
		$presets = Options::get_db_filter_presets();
		foreach ( $presets as $p ) {
			if ( strtolower( $p['name'] ) === strtolower( $name ) ) {
				self::redirect_list( array( 'preset_error' => 1 ) );
			}
		}
		$filters    = self::get_filters();
		$allowed    = Options::db_filter_allowed_keys();
		$query_save = array();
		foreach ( $allowed as $k ) {
			if ( isset( $filters[ $k ] ) && '' !== $filters[ $k ] && null !== $filters[ $k ] ) {
					$query_save[ $k ] = $filters[ $k ];
			}
		}
		$presets[] = array(
			'name'  => $name,
			'query' => $query_save,
		);
		if ( count( $presets ) > 20 ) {
				$presets = array_slice( $presets, -20 );
		}
		Options::set_db_filter_presets( $presets );
		self::redirect_list( array( 'preset_saved' => 1 ) );
	}

		/**
		 * Delete a filter preset.
		 *
		 * @param string $name Preset name.
		 *
		 * @return void
		 */
	private static function handle_preset_delete( string $name ): void {
		$presets = Options::get_db_filter_presets();
		$presets = array_filter(
			$presets,
			static function ( $p ) use ( $name ) {
					return strtolower( $p['name'] ) !== strtolower( $name );
			}
		);
		Options::set_db_filter_presets( array_values( $presets ) );
		self::redirect_list( array( 'preset_deleted' => 1 ) );
	}

		/**
		 * Save column preferences.
		 *
		 * @param array<int,string> $cols Column IDs.
		 *
		 * @return void
		 */
	private static function handle_columns_save( array $cols ): void {
		UsersMeta::set_db_columns( get_current_user_id(), $cols );
		self::redirect_list( array( 'columns_saved' => 1 ) );
	}

		/**
		 * Retrieve preset query by name.
		 *
		 * @param string $name Preset name.
		 * @return array<string,string>
		 */
	private static function get_preset_query( string $name ): array {
			$presets = Options::get_db_filter_presets();
		foreach ( $presets as $p ) {
			if ( strtolower( $p['name'] ) === strtolower( $name ) ) {
					return $p['query'];
			}
		}
			return array();
	}

		/**
		 * Redirect back to list view.
		 *
		 * @param array<string,mixed> $args Args.
		 * @return void
		 */
	private static function redirect_list( array $args = array() ): void {
					$url = add_query_arg( $args, menu_page_url( 'fbm_database', false ) );
			wp_safe_redirect( esc_url_raw( $url ) );
			exit;
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
		if ( ! current_user_can( 'fb_manage_database' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

		check_admin_referer( 'fbm_delete_entry_' . $id, 'fbm_nonce' );
		ApplicationsRepo::softDelete( $id );

		$url = add_query_arg(
			array( 'deleted' => 1 ),
			menu_page_url( 'fbm_database', false )
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
	private static function do_export( array $filters, array $columns, bool $mask ): void {
		if ( ! current_user_can( 'fb_manage_database' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- custom cap
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

			$rows     = array();
			$filename = 'fbm-entries.csv';

		if ( isset( $filters['id'] ) ) {
				$entry = ApplicationsRepo::get( (int) $filters['id'] );
			if ( $entry ) {
					$rows[]   = $entry;
					$filename = 'fbm-entry-' . (int) $filters['id'] . '.csv';
			}
		} else {
				$data = ApplicationsRepo::list( $filters );
				$rows = $data['rows'];
		}

			$defs     = Columns::for_admin_list( ! $mask );
			$sel_defs = array();
		foreach ( $columns as $col ) {
			if ( isset( $defs[ $col ] ) ) {
					$sel_defs[ $col ] = $defs[ $col ];
			}
		}
			$export_rows = self::build_export_rows( $rows, $sel_defs );
			$labels      = array();
		foreach ( $sel_defs as $k => $d ) {
				$labels[ $k ] = (string) $d['label'];
		}

			CsvExporter::stream_list( $export_rows, $labels, $mask, $filename );
			exit;
	}

		/**
		 * Build export rows from raw DB rows and column definitions.
		 *
		 * @param array<int,array<string,mixed>> $rows Raw rows.
		 * @param array<string,array<string,mixed>> $defs Column definitions.
		 * @return array<int,array<string,string>>
		 */
	private static function build_export_rows( array $rows, array $defs ): array {
			$out = array();
		foreach ( $rows as $row ) {
				$line = array();
			foreach ( $defs as $key => $def ) {
				$cb           = $def['value'];
				$line[ $key ] = is_callable( $cb ) ? (string) $cb( $row ) : '';
			}
				$out[] = $line;
		}
			return $out;
	}

	/**
	 * Parse filters from the request.
	 *
	 * @since 0.1.x
	 *
	 * @return array<string, mixed>
	 */
	/**
	 * Parse filters from the request.
	 *
	 * @param array<string, mixed>|null $query_vars Optional parsed query variables.
	 *
	 * @since 0.1.x
	 *
	 * @return array<string, mixed>
	 */
	private static function get_filters( ?array $query_vars = null ): array {
		$query_vars = $query_vars ?? array();
		if ( empty( $query_vars ) && isset( $_SERVER['QUERY_STRING'] ) ) {
			parse_str( sanitize_text_field( wp_unslash( (string) $_SERVER['QUERY_STRING'] ) ), $query_vars );
		}

		$search = isset( $query_vars['search'] ) ? sanitize_text_field( $query_vars['search'] ) : '';

		$status_raw     = isset( $query_vars['status'] ) ? sanitize_key( $query_vars['status'] ) : '';
		$allowed_status = array( 'new', 'approved', 'archived' );
		$status         = in_array( $status_raw, $allowed_status, true ) ? $status_raw : '';

		$has_file = isset( $query_vars['has_file'] ) ? true : null;
		$consent  = isset( $query_vars['consent'] ) ? true : null;

		$date_from = isset( $query_vars['date_from'] ) ? self::sanitize_date( sanitize_text_field( $query_vars['date_from'] ) ) : '';
		$date_to   = isset( $query_vars['date_to'] ) ? self::sanitize_date( sanitize_text_field( $query_vars['date_to'] ) ) : '';

		$page     = isset( $query_vars['paged'] ) ? max( 1, absint( $query_vars['paged'] ) ) : 1;
		$per_page = isset( $query_vars['per_page'] ) ? min( 500, max( 1, absint( $query_vars['per_page'] ) ) ) : 20;

		$orderby_key     = isset( $query_vars['orderby'] ) ? sanitize_key( $query_vars['orderby'] ) : '';
		$allowed_orderby = array( 'created_at', 'status', 'form_id', 'id' );
		$orderby         = in_array( $orderby_key, $allowed_orderby, true ) ? $orderby_key : 'created_at';

		$order_key = isset( $query_vars['order'] ) ? strtoupper( sanitize_key( $query_vars['order'] ) ) : '';
		$order     = in_array( $order_key, array( 'ASC', 'DESC' ), true ) ? $order_key : 'DESC';

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
