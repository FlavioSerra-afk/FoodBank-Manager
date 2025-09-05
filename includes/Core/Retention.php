<?php
/**
 * Data retention and anonymisation routines.
 *
 * @package FBM\Core
 */

declare(strict_types=1);

namespace FBM\Core;

use FoodBankManager\Core\Options;
use FoodBankManager\Logging\Audit;
use function absint;
use function array_fill;
use function get_current_user_id;
use function update_option;
use function wp_next_scheduled;
use function wp_schedule_event;
use wpdb;

/**
 * Retention handler.
 */
final class Retention {
    public const EVENT = 'fbm_retention_tick';
    private const BATCH = 200;
    private const OPT_LAST_RUN = 'fbm_retention_tick_last_run';
    private const OPT_LAST_SUMMARY = 'fbm_retention_tick_last_summary';

    /**
     * Register cron hook.
     */
    public static function init(): void {
        add_action( self::EVENT, [ self::class, 'tick' ] );
    }

    /**
     * Cron tick handler.
     */
    public static function tick(): void {
        self::run( false );
    }

    /**
     * Schedule cron event if missing.
     */
    public static function schedule(): void {
        if ( ! wp_next_scheduled( self::EVENT ) ) {
            wp_schedule_event( time() + 300, 'daily', self::EVENT );
        }
    }

    /**
     * Execute retention policies.
     *
     * @param bool $dry_run Whether to simulate.
     * @return array<string,array<string,int>> Summary counts.
     */
    public static function run( bool $dry_run = false ): array {
        global $wpdb;
        $summary = [
            'applications' => [ 'deleted' => 0, 'anonymised' => 0 ],
            'attendance'   => [ 'deleted' => 0, 'anonymised' => 0 ],
            'mail_log'     => [ 'deleted' => 0, 'anonymised' => 0 ],
        ];

        /** @var array<string,array<string,mixed>> $cfg */
        $cfg = (array) Options::get( 'privacy.retention', [] );

        $day = defined( 'DAY_IN_SECONDS' ) ? DAY_IN_SECONDS : 86400;

        $tables = [
            'applications' => [
                'table'   => $wpdb->prefix . 'fb_applications',
                'column'  => 'created_at',
                'repo'    => '\\FoodBankManager\\Database\\ApplicationsRepo',
            ],
            'attendance' => [
                'table'   => $wpdb->prefix . 'fb_attendance',
                'column'  => 'attendance_at',
                'repo'    => '\\FoodBankManager\\Attendance\\AttendanceRepo',
            ],
            'mail_log' => [
                'table'   => $wpdb->prefix . 'fb_mail_log',
                'column'  => 'timestamp',
                'repo'    => '\\FBM\\Mail\\LogRepo',
            ],
        ];

        foreach ( $tables as $key => $info ) {
            $days   = absint( $cfg[ $key ]['days'] ?? 0 );
            $policy = (string) ( $cfg[ $key ]['policy'] ?? 'delete' );
            if ( $days <= 0 ) {
                continue;
            }
            $cutoff = gmdate( 'Y-m-d H:i:s', time() - $days * $day );
            $ids    = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$info['table']} WHERE {$info['column']} <= %s LIMIT %d", $cutoff, self::BATCH ) );
            if ( empty( $ids ) ) {
                continue;
            }
            if ( 'delete' === $policy ) {
                if ( ! $dry_run ) {
                    $ph = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
                    $sql = "DELETE FROM {$info['table']} WHERE id IN ($ph)";
                    $wpdb->query( $wpdb->prepare( $sql, $ids ) );
                }
                $summary[ $key ]['deleted'] += count( $ids );
            } else {
                if ( ! $dry_run ) {
                    $repo = $info['repo'];
                    if ( method_exists( $repo, 'anonymise_batch' ) ) {
                        $repo::anonymise_batch( $ids );
                    }
                }
                $summary[ $key ]['anonymised'] += count( $ids );
            }
        }

        if ( ! $dry_run ) {
            update_option( self::OPT_LAST_SUMMARY, $summary );
            update_option( self::OPT_LAST_RUN, time() );
            Audit::log( 'retention_run', 'system', 0, get_current_user_id() ?: 0, $summary );
        }

        return $summary;
    }
}
