<?php
/**
 * Jobs admin list table.
 *
 * @package FBM\Admin
 */

declare(strict_types=1);

namespace FBM\Admin;

use FBM\Core\Jobs\JobsRepo;
use WP_List_Table;
use function absint;
use function check_admin_referer;
use function current_user_can;
use function add_query_arg;
use function admin_url;
use function esc_html;
use function esc_url;
use function wp_nonce_url;
use function add_action;
use function sanitize_key;
use function sanitize_text_field;
use function wp_unslash;
use function str_contains;
use function in_array;

// Ensure WP_List_Table exists when running in WordPress.
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List export jobs in admin.
 */
final class JobsTable extends WP_List_Table {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            array(
                'singular' => 'job',
                'plural'   => 'jobs',
                'ajax'     => false,
            )
        );
    }

    /**
     * Get column headers.
     *
     * @return array<string,string>
     */
    public function get_columns(): array {
        return array(
            'cb'        => '<input type="checkbox" />',
            'id'        => 'ID',
            'type'      => 'Type',
            'format'    => 'Format',
            'status'    => 'Status',
            'created_at'=> 'Created',
        );
    }

    /**
     * Get sortable columns.
     *
     * @return array<string,array<int,string>>
     */
    protected function get_sortable_columns(): array {
        return array(
            'id'        => array( 'id', false ),
            'status'    => array( 'status', false ),
            'created_at'=> array( 'created_at', true ),
        );
    }

    /**
     * Bulk actions.
     *
     * @return array<string,string>
     */
    protected function get_bulk_actions(): array {
        return array(
            'retry'  => 'Retry',
            'cancel' => 'Cancel',
        );
    }

    /**
     * Default column renderer.
     *
     * @param array<string,mixed> $item Row item.
     * @param string              $column_name Column name.
     * @return string
     */
    protected function column_default( $item, $column_name ): string { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
        return isset( $item[ $column_name ] ) ? (string) $item[ $column_name ] : '';
    }

    /**
     * Checkbox column renderer.
     *
     * @param array<string,mixed> $item Row item.
     * @return string
     */
    protected function column_cb( $item ): string { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
        return '<input type="checkbox" name="job[]" value="' . (int) $item['id'] . '" />';
    }

    /**
     * Type column with row actions.
     *
     * @param array<string,mixed> $item Row item.
     * @return string
     */
    protected function column_type( $item ): string { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
        $actions = array();
        if ( current_user_can( 'fbm_manage_jobs' ) ) {
            $id    = (int) $item['id'];
            $base  = add_query_arg( array( 'page' => 'fbm_jobs', 'job' => $id ), admin_url( 'admin.php' ) );
            if ( 'failed' === $item['status'] ) {
                $actions['retry'] = '<a href="' . esc_url( wp_nonce_url( $base . '&action=retry', 'fbm_job_action_' . $id ) ) . '">Retry</a>';
            }
            if ( in_array( $item['status'], array( 'pending', 'running' ), true ) ) {
                $actions['cancel'] = '<a href="' . esc_url( wp_nonce_url( $base . '&action=cancel', 'fbm_job_action_' . $id ) ) . '">Cancel</a>';
            }
        }
        return esc_html( (string) $item['type'] ) . $this->row_actions( $actions ); // @phpstan-ignore-line
    }

    /**
     * Views for status filters.
     *
     * @return array<string,string>
     */
    protected function get_views(): array { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
        $statuses = array( 'all' => 'All', 'pending' => 'Pending', 'running' => 'Running', 'failed' => 'Failed', 'done' => 'Done', 'cancelled' => 'Cancelled' );
        $current  = isset( $_GET['status'] ) ? sanitize_key( (string) $_GET['status'] ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View only
        $views    = array();
        foreach ( $statuses as $key => $label ) {
            $url          = esc_url( add_query_arg( array( 'status' => $key === 'all' ? false : $key ) ) );
            $class        = $current === $key ? 'class="current"' : '';
            $views[ $key ] = '<a href="' . $url . '" ' . $class . '>' . esc_html( $label ) . '</a>';
        }
        return $views;
    }

    /**
     * Prepare list items.
     */
    public function prepare_items(): void {
        $items = JobsRepo::list( array( 'limit' => 1000 ) );

        $status = isset( $_GET['status'] ) ? sanitize_key( (string) $_GET['status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( $status ) {
            $items = array_values( array_filter( $items, static fn( $r ) => $r['status'] === $status ) );
        }
        $search = isset( $_REQUEST['s'] ) ? strtolower( sanitize_text_field( wp_unslash( (string) $_REQUEST['s'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( $search ) {
            $items = array_values( array_filter( $items, static fn( $r ) => str_contains( strtolower( (string) $r['type'] ), $search ) ) );
        }
        $orderby = isset( $_GET['orderby'] ) ? sanitize_key( (string) $_GET['orderby'] ) : 'id'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $order   = isset( $_GET['order'] ) && 'asc' === strtolower( (string) $_GET['order'] ) ? 'asc' : 'desc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        usort(
            $items,
            static function ( $a, $b ) use ( $orderby, $order ) {
                $av = $a[ $orderby ] ?? '';
                $bv = $b[ $orderby ] ?? '';
                if ( $av === $bv ) {
                    return 0;
                }
                return ( $av <=> $bv ) * ( 'asc' === $order ? 1 : -1 );
            }
        );
        $per_page     = 20;
        $current_page = max( 1, absint( $_GET['paged'] ?? 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $total_items  = count( $items );
        $items        = array_slice( $items, ( $current_page - 1 ) * $per_page, $per_page );
        $this->items  = $items;
        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil( $total_items / $per_page ),
        ) );
    }

    /**
     * Current action.
     *
     * @return string
     */
    public function current_action(): string { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( $action && '-1' !== $action ) {
            return $action;
        }
        $action = isset( $_REQUEST['action2'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action2'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return $action && '-1' !== $action ? $action : '';
    }

    /**
     * Handle bulk actions.
     */
    public function process_bulk_action(): void { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
        $action = $this->current_action();
        if ( ! in_array( $action, array( 'retry', 'cancel' ), true ) ) {
            return;
        }
        check_admin_referer( 'bulk-jobs' );
        if ( ! current_user_can( 'fbm_manage_jobs' ) ) {
            wp_die( 'forbidden' );
        }
        $ids = isset( $_POST['job'] ) ? array_map( 'absint', (array) $_POST['job'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        foreach ( $ids as $id ) {
            if ( 'retry' === $action ) {
                JobsRepo::retry( $id );
            } else {
                JobsRepo::cancel( $id );
            }
        }
        if ( $ids ) {
            add_action(
                'admin_notices',
                static function () use ( $action, $ids ): void {
                    $verb = 'retry' === $action ? 'retried' : 'cancelled';
                    echo '<div class="notice notice-success"><p>' . esc_html( sprintf( '%d jobs %s.', count( $ids ), $verb ) ) . '</p></div>';
                }
            );
        }
    }
}
