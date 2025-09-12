<?php
/**
 * Jobs admin page controller.
 *
 * @package FBM\Admin
 */

declare(strict_types=1);

namespace FBM\Admin;

use FBM\Core\Jobs\JobsRepo;
use function absint;
use function add_query_arg;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function esc_url_raw;
use function in_array;
use function sanitize_key;
use function wp_die;
use function wp_safe_redirect;
use function wp_unslash;

/**
 * Render jobs admin page.
 */
final class JobsPage {
    /**
     * Route and render the page.
     */
    public static function route(): void {
        if ( ! current_user_can( 'fbm_manage_jobs' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
        }

        self::handle_row_action();

        $table = new JobsTable();
        $table->process_bulk_action();
        $table->prepare_items();

        echo '<div class="wrap fbm-admin"><h1>' . esc_html__( 'Jobs', 'foodbank-manager' ) . '</h1>';
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="fbm_jobs" />';
        $table->search_box( esc_html__( 'Search Jobs', 'foodbank-manager' ), 'jobs' ); // @phpstan-ignore-line
        $table->display();
        echo '</form></div>';
    }

    /**
     * Handle row retry/cancel actions.
     */
    private static function handle_row_action(): void {
        $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( (string) $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $id     = absint( $_GET['job'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! $action || ! $id || ! in_array( $action, array( 'retry', 'cancel' ), true ) ) {
            return;
        }
        check_admin_referer( 'fbm_job_action_' . $id );
        if ( ! current_user_can( 'fbm_manage_jobs' ) ) {
            wp_die( esc_html__( 'Insufficient permissions', 'foodbank-manager' ) );
        }
        if ( 'retry' === $action ) {
            JobsRepo::retry( $id );
        } else {
            JobsRepo::cancel( $id );
        }
        $url = add_query_arg( array( 'page' => 'fbm_jobs', 'notice' => 'done' ), admin_url( 'admin.php' ) );
        wp_safe_redirect( esc_url_raw( $url ) );
        exit;
    }
}
