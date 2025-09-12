<?php
/**
 * System report provider for diagnostics.
 *
 * @package FoodBankManager\Admin
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Plugin;
use FoodBankManager\Admin\Menu;
use FBM\Core\Jobs\JobsRepo;
use FBM\Mail\LogRepo;
use function current_user_can;
use function esc_html__;
use function gmdate;
use function get_bloginfo;
use function menu_page_url;
use function wp_create_nonce;
use function wp_die;

final class DiagnosticsReport {
    private const ACTION = 'fbm_diag_report';

    /**
     * Render report panel.
     */
    public static function render(): void {
        if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
            wp_die( esc_html__( 'Insufficient permissions', 'foodbank-manager' ) );
        }
        $data  = self::data();
        $lines = self::lines( $data );
        $nonce = wp_create_nonce( self::ACTION );
        /* @psalm-suppress UnresolvableInclude */
        require FBM_PATH . 'templates/admin/diagnostics-report.php';
    }

    /**
     * Build report data.
     *
     * @return array<string,mixed>
     */
    public static function data(): array {
        $panels = array();
        foreach ( Menu::slugs() as $slug ) {
            if ( menu_page_url( $slug, false ) !== false ) {
                $panels[] = $slug;
            }
        }
        $cron     = DiagnosticsPage::cron_status();
        $wpdb     = $GLOBALS['wpdb'] ?? null;
        $failures = array();
        if ( is_object( $wpdb ) && method_exists( $wpdb, 'get_results' ) ) {
            $failures = array_slice( LogRepo::recent_failures( 5 ), 0, 5 );
        }
        $jobs = array( 'pending' => 0 );
        if ( is_object( $wpdb ) && method_exists( $wpdb, 'get_var' ) ) {
            $jobs['pending'] = JobsRepo::pending_count();
        }

        return array(
            'plugin'        => Plugin::VERSION,
            'php'           => PHP_VERSION,
            'wordpress'     => get_bloginfo( 'version' ),
            'panels'        => $panels,
            'cron'          => $cron,
            'mail_failures' => $failures,
            'jobs'          => $jobs,
        );
    }

    /**
     * Convert report data to text lines.
     *
     * @param array<string,mixed> $data Report data.
     * @return array<int,string>
     */
    private static function lines( array $data ): array {
        $out   = array();
        $out[] = 'plugin=' . $data['plugin'];
        $out[] = 'php=' . $data['php'];
        $out[] = 'wordpress=' . $data['wordpress'];
        $out[] = 'panels=' . implode( ',', $data['panels'] );
        $out[] = 'jobs_pending=' . $data['jobs']['pending'];
        foreach ( $data['cron'] as $row ) {
            $out[] = 'cron=' . $row['hook'] . '@' . ( $row['last_run'] ? gmdate( 'c', $row['last_run'] ) : '-' );
        }
        return $out;
    }
}
