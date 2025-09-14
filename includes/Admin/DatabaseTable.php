<?php
/**
 * Database list table.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Database\ApplicationsRepo;
use FoodBankManager\Security\Helpers;
use FoodBankManager\Database\Columns;
use FoodBankManager\Security\Crypto;
use WP_List_Table;

// Ensure WP_List_Table is loaded when running inside WordPress.
if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Applications database table.
 *
 * @since 0.1.x
 */
final class DatabaseTable extends WP_List_Table {
		/**
		 * Selected column slugs.
		 *
		 * @var array<int,string>
		 */
	private array $selected;

		/**
		 * Filters for the query.
		 *
		 * @var array<string,mixed>
		 */
	private array $filters;


		/**
		 * Column definitions.
		 *
		 * @var array<string,array<string,mixed>>
		 */
	private array $defs;

		/**
		 * Constructor.
		 *
		 * @param array<string,mixed> $filters  Filter arguments for ApplicationsRepo::list.
		 * @param array<int,string>   $selected Selected column slugs.
		 * @param bool                $unmask   Whether to display sensitive data.
		 */
	public function __construct( array $filters, array $selected, bool $unmask ) {
			parent::__construct(
				array(
					'singular' => 'application',
					'plural'   => 'applications',
					'ajax'     => false,
				)
			);
			$this->filters  = $filters;
			$this->selected = $selected;
			$this->defs     = Columns::for_admin_list( $unmask );
	}

		/**
		 * Retrieve column headers.
		 *
		 * @return array<string,string>
		 */
	public function get_columns(): array {
			$cols = array();
		foreach ( $this->defs as $key => $def ) {
				$cols[ $key ] = (string) $def['label'];
		}
			$cols['actions'] = __( 'Actions', 'foodbank-manager' );
			return $cols;
	}

		/**
		 * Columns that are hidden.
		 *
		 * @return array<int,string>
		 */
	protected function get_hidden_columns(): array {
			$allowed = array_keys( $this->defs );
			$hidden  = array();
		foreach ( $allowed as $slug ) {
			if ( ! in_array( $slug, $this->selected, true ) ) {
				$hidden[] = $slug;
			}
		}
			return $hidden;
	}

		/**
		 * Sortable columns.
		 *
		 * @return array<string,array{0:string,1:bool}>
		 */
	protected function get_sortable_columns(): array {
			$out = array();
		foreach ( $this->defs as $key => $def ) {
			if ( isset( $def['sortable'] ) ) {
				$out[ $key ] = array( $def['sortable'], false );
			}
		}
			return $out;
	}

		/**
		 * Prepare table items.
		 *
		 * @return void
		 */
	public function prepare_items(): void {
			$data                  = ApplicationsRepo::list( $this->filters );
			$this->items           = $data['rows'];
			$hidden                = $this->get_hidden_columns();
			$this->_column_headers = array( $this->get_columns(), $hidden, $this->get_sortable_columns() );
			$total                 = (int) $data['total'];
			$per_page              = (int) $this->filters['per_page'];
			$this->set_pagination_args(
				array(
					'total_items' => $total,
					'per_page'    => $per_page,
					'total_pages' => max( 1, (int) ceil( $total / $per_page ) ),
				)
			);
	}

		/**
		 * Default column renderer.
		 *
		 * @param array<string,mixed> $item  Current row.
		 * @param string              $column Column name.
		 *
		 * @return string
		 */
	public function column_default( $item, $column ): string { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		if ( 'actions' === $column ) {
				return $this->column_actions( $item );
		}
		if ( ! isset( $this->defs[ $column ] ) ) {
				return '';
		}
			$val_cb = $this->defs[ $column ]['value'];
			$value  = '';
		if ( is_callable( $val_cb ) ) {
				$value = (string) $val_cb( $item );
		}
			return esc_html( $value );
	}

		/**
		 * Render the actions column.
		 *
		 * @param array<string,mixed> $item Row data.
		 *
		 * @return string
		 */
	private function column_actions( array $item ): string {
			$id        = (int) ( $item['id'] ?? 0 );
			$view_url  = wp_nonce_url(
				admin_url( 'admin.php?page=fbm_database&fbm_action=view_entry&entry_id=' . $id ),
				'fbm_entry_view'
			);
			$actions   = array();
			$actions[] = '<a href="' . esc_url( $view_url ) . '">' . esc_html__( 'View', 'foodbank-manager' ) . '</a>';

		if ( current_user_can( 'fb_manage_database' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- custom cap
				$export    = '<form method="post" style="display:inline">'
						. '<input type="hidden" name="fbm_action" value="export_single" />'
						. '<input type="hidden" name="id" value="' . esc_attr( (string) $id ) . '" />'
						. wp_nonce_field( 'fbm_export_single_' . $id, 'fbm_nonce', true, false )
						. '<button type="submit" class="button-link">' . esc_html__( 'CSV', 'foodbank-manager' ) . '</button>'
						. '</form>';
				$actions[] = $export;
				$delete    = '<form method="post" style="display:inline" onsubmit="return confirm(\'' .
						esc_js( __( 'Are you sure?', 'foodbank-manager' ) ) . '\');">'
						. '<input type="hidden" name="fbm_action" value="delete_entry" />'
						. '<input type="hidden" name="id" value="' . esc_attr( (string) $id ) . '" />'
						. wp_nonce_field( 'fbm_delete_entry_' . $id, 'fbm_nonce', true, false )
						. '<button type="submit" class="button-link">' . esc_html__( 'Delete', 'foodbank-manager' ) . '</button>'
						. '</form>';
				$actions[] = $delete;
		}

			return implode( ' | ', $actions );
	}

		/**
		 * Message shown when no items.
		 *
		 * @return void
		 */
	public function no_items(): void {
			esc_html_e( 'No entries found.', 'foodbank-manager' );
	}
}
