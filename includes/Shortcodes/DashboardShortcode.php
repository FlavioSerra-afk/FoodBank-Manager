<?php
/**
 * Manager dashboard shortcode.
 *
 * @package FBM\Shortcodes
 */

declare(strict_types=1);

namespace FBM\Shortcodes;

use FoodBankManager\Attendance\AttendanceRepo;
use DateInterval;
use DateTimeImmutable;
use function current_time;
use function current_user_can;
use function esc_html__;
use function get_current_user_id;
use function get_transient;
use function sanitize_key;
use function sanitize_text_field;
use function set_transient;
use function shortcode_atts;
use function admin_url;
use function add_query_arg;
use function wp_nonce_url;

/**
 * Dashboard shortcode.
 */
final class DashboardShortcode {
        /**
         * Render dashboard cards.
         *
         * @param array<string,string> $atts Attributes.
         * @return string
         */
        public static function render( array $atts = array() ): string {
                if ( ! current_user_can( 'fb_manage_dashboard' ) && ! current_user_can( 'fb_view_dashboard' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
                        return '<div class="fbm-no-permission">' . esc_html__( 'You do not have permission to view the dashboard.', 'foodbank-manager' ) . '</div>';
                }

                $atts     = shortcode_atts(
                        array(
                                'period'      => '7d',
                                'compare'     => '1',
                                'sparkline'   => '1',
                                'event'       => '',
                                'type'        => 'all',
                                'policy_only' => '0',
                        ),
                        $atts,
                        'fbm_dashboard'
                );
                $period      = self::sanitize_period( $atts['period'] );
                $do_compare   = self::sanitize_flag( $atts['compare'] );
                $do_sparkline = self::sanitize_flag( $atts['sparkline'] );
                $event       = self::sanitize_event( $_GET['fbm_event'] ?? $atts['event'] );
                $type        = self::sanitize_type( $_GET['fbm_type'] ?? $atts['type'] );
                $policy_only = self::sanitize_flag( $_GET['fbm_policy_only'] ?? $atts['policy_only'] );
                $since       = self::since_from_period( $period );

                $filters = array(
                        'since'       => $since,
                        'event'       => $event,
                        'type'        => $type,
                        'policy_only' => $policy_only,
                );

                $hash = md5( $period . '|' . (string) $event . '|' . $type . '|' . ( $policy_only ? '1' : '0' ) );
                $user = get_current_user_id();
                $base = 'fbm_dash_' . $user . '_' . $period . '_' . $hash . '_';

                $series = get_transient( $base . 'series' );
                if ( ! is_array( $series ) ) {
                        $series = AttendanceRepo::daily_present_counts( $since, $filters );
                        set_transient( $base . 'series', $series, 60 );
                }

                $totals = get_transient( $base . 'totals' );
                if ( ! is_array( $totals ) ) {
                        $totals = AttendanceRepo::period_totals( $since, $filters );
                        set_transient( $base . 'totals', $totals, 60 );
                }

                $deltas = array();
                if ( $do_compare ) {
                        switch ( $period ) {
                                case 'today':
                                        $prev_since = $since->sub( new DateInterval( 'P1D' ) );
                                        break;
                                case '30d':
                                        $prev_since = $since->sub( new DateInterval( 'P30D' ) );
                                        break;
                                default:
                                        $prev_since = $since->sub( new DateInterval( 'P7D' ) );
                        }
                        $prev = get_transient( $base . 'prev' );
                        if ( ! is_array( $prev ) ) {
                                $prev = AttendanceRepo::period_totals( $prev_since, $filters );
                                set_transient( $base . 'prev', $prev, 60 );
                        }
                        foreach ( $totals as $k => $v ) {
                                $deltas[ $k ] = self::delta( (int) $v, (int) ( $prev[ $k ] ?? 0 ) );
                        }
                }

                $updated_at = current_time( 'mysql', true );

                $csv_url = wp_nonce_url(
                        add_query_arg(
                                array(
                                        'action'      => 'fbm_dash_export',
                                        'period'      => $period,
                                        'event'       => (string) $event,
                                        'type'        => $type,
                                        'policy_only' => $policy_only ? '1' : '0',
                                ),
                                admin_url( 'admin-post.php' )
                        ),
                        'fbm_dash_export'
                );

                ob_start();
                $counts       = $totals; // local vars for template.
                $series_attr  = $do_sparkline ? $series : array();
                $deltas_attr  = $deltas;
                $period_attr  = $period;
                $updated      = $updated_at;
                $filters_attr = array(
                        'event'       => $event ? (string) $event : '',
                        'type'        => $type,
                        'policy_only' => $policy_only,
                );
                $csv_url_attr = $csv_url;
                include dirname( __DIR__, 2 ) . '/templates/public/dashboard.php';
                return (string) ob_get_clean();
        }

        /**
         * Sanitize period string.
         *
         * @param string $period Raw period.
         * @return string
         */
        public static function sanitize_period( string $period ): string {
                $period = sanitize_key( $period );
                return in_array( $period, array( 'today', '7d', '30d' ), true ) ? $period : '7d';
        }

        /**
         * Sanitize boolean-like flag.
         *
         * @param string $flag Raw flag.
         * @return bool
         */
        public static function sanitize_flag( string $flag ): bool {
                return '1' === $flag;
        }

        /**
         * Sanitize type filter.
         *
         * @param string $type Raw type.
         * @return string
         */
        public static function sanitize_type( string $type ): string {
                $type = sanitize_key( $type );
                return in_array( $type, array( 'in_person', 'delivery', 'all' ), true ) ? $type : 'all';
        }

        /**
         * Sanitize event filter.
         *
         * @param string $event Raw event.
         * @return string|null
         */
        public static function sanitize_event( string $event ): ?string {
                $event = sanitize_text_field( $event );
                $event = trim( $event );
                return $event !== '' ? $event : null;
        }

        /**
         * Convert period to since timestamp.
         *
         * @param string $period Period key.
         * @return DateTimeImmutable
         */
        public static function since_from_period( string $period ): DateTimeImmutable {
                $day = defined( 'DAY_IN_SECONDS' ) ? (int) DAY_IN_SECONDS : 86400;
                $now = time();
                switch ( $period ) {
                        case 'today':
                                $ts = strtotime( 'today UTC', $now );
                                break;
                        case '30d':
                                $ts = strtotime( 'today UTC', $now - ( 29 * $day ) );
                                break;
                        default:
                                $ts = strtotime( 'today UTC', $now - ( 6 * $day ) );
                }
                return new DateTimeImmutable( gmdate( 'Y-m-d H:i:s', $ts ), new \DateTimeZone( 'UTC' ) );
        }

        /**
         * Calculate percentage delta.
         *
         * @param int $current Current value.
         * @param int $previous Previous value.
         * @return int|null
         */
        public static function delta( int $current, int $previous ): ?int {
                if ( $previous <= 0 ) {
                        return null;
                }
                return (int) round( ( ( $current - $previous ) / $previous ) * 100 );
        }
}
