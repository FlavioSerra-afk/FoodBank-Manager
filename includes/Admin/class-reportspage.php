<?php
/**
 * Reports admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceReportService;
use FoodBankManager\Attendance\AttendanceRepository;
use wpdb;
use function __;
use function add_action;
use function add_menu_page;
use function array_key_exists;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function fclose;
use function filter_input;
use function fopen;
use function fputcsv;
use function header;
use function is_readable;
use function sanitize_file_name;
use function sanitize_key;
use function sanitize_text_field;
use function sprintf;
use function wp_die;
use function wp_nonce_field;
use function wp_unslash;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;

/**
 * Presents attendance summaries and CSV export controls.
 */
final class ReportsPage {
        private const MENU_SLUG     = 'fbm-reports';
        private const TEMPLATE      = 'templates/admin/reports-page.php';
        private const START_PARAM   = 'fbm_report_start';
        private const END_PARAM     = 'fbm_report_end';
        private const ACTION_PARAM  = 'fbm_report_action';
        private const ACTION_EXPORT = 'export';
        private const NONCE_FIELD   = 'fbm_report_nonce';

        /**
         * Register WordPress hooks.
         */
        public static function register(): void {
                add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
                add_action( 'admin_init', array( __CLASS__, 'handle_actions' ) );
        }

        /**
         * Register the admin menu entry.
         */
        public static function register_menu(): void {
                add_menu_page(
                        __( 'Food Bank Reports', 'foodbank-manager' ),
                        __( 'Food Bank Reports', 'foodbank-manager' ),
                        'fbm_export', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered during activation.
                        self::MENU_SLUG,
                        array( __CLASS__, 'render' ),
                        'dashicons-chart-area'
                );
        }

        /**
         * Render the reports page.
         */
        public static function render(): void {
                if ( ! current_user_can( 'fbm_export' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
                        wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
                }

                global $wpdb;

                if ( ! $wpdb instanceof wpdb ) {
                        wp_die( esc_html__( 'Database connection unavailable.', 'foodbank-manager' ) );
                }

                $start_input = filter_input( INPUT_GET, self::START_PARAM, FILTER_UNSAFE_RAW );
                $end_input   = filter_input( INPUT_GET, self::END_PARAM, FILTER_UNSAFE_RAW );

                $start_string = is_string( $start_input ) ? sanitize_text_field( wp_unslash( $start_input ) ) : '';
                $end_string   = is_string( $end_input ) ? sanitize_text_field( wp_unslash( $end_input ) ) : '';

                $range = self::determine_range( $start_string, $end_string );
                $start = $range['start'];
                $end   = $range['end'];

                $repository = new AttendanceRepository( $wpdb );
                $service    = new AttendanceReportService( $repository );
                $summary    = $service->summarize( $start, $end );

                $start_value = $start->format( 'Y-m-d' );
                $end_value   = $end->format( 'Y-m-d' );

                $context = array(
                        'summary'       => $summary,
                        'start'         => $start_value,
                        'end'           => $end_value,
                        'page_slug'     => self::MENU_SLUG,
                        'start_param'   => self::START_PARAM,
                        'end_param'     => self::END_PARAM,
                        'action_param'  => self::ACTION_PARAM,
                        'action_value'  => self::ACTION_EXPORT,
                        'nonce_field'   => wp_nonce_field( self::nonce_action( $start_value, $end_value ), self::NONCE_FIELD, true, false ),
                );

                $template = FBM_PATH . self::TEMPLATE;

                if ( ! is_readable( $template ) ) {
                        wp_die( esc_html__( 'Reports admin template is missing.', 'foodbank-manager' ) );
                }

                $data = $context;
                include $template;
        }

        /**
         * Handle CSV export requests.
         */
        public static function handle_actions(): void {
                if ( ! current_user_can( 'fbm_export' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
                        return;
                }

                $page = isset( $_GET['page'] )
                        ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Action validated below.
                        : '';

                if ( self::MENU_SLUG !== $page ) {
                        return;
                }

                $action = isset( $_GET[ self::ACTION_PARAM ] )
                        ? sanitize_key( wp_unslash( (string) $_GET[ self::ACTION_PARAM ] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Action validated below.
                        : '';

                if ( self::ACTION_EXPORT !== $action ) {
                        return;
                }

                $start_raw = isset( $_GET[ self::START_PARAM ] ) ? wp_unslash( (string) $_GET[ self::START_PARAM ] ) : '';
                $end_raw   = isset( $_GET[ self::END_PARAM ] ) ? wp_unslash( (string) $_GET[ self::END_PARAM ] ) : '';

                $start_string = sanitize_text_field( $start_raw );
                $end_string   = sanitize_text_field( $end_raw );

                check_admin_referer( self::nonce_action( $start_string, $end_string ), self::NONCE_FIELD );

                $range = self::determine_range( $start_string, $end_string );
                $start = $range['start'];
                $end   = $range['end'];

                global $wpdb;

                if ( ! $wpdb instanceof wpdb ) {
                        wp_die( esc_html__( 'Database connection unavailable.', 'foodbank-manager' ) );
                }

                $repository = new AttendanceRepository( $wpdb );
                $service    = new AttendanceReportService( $repository );
                $rows       = $service->export( $start, $end );

                self::stream_csv( $rows, $start, $end );
        }

        /**
         * Resolve the date range based on the provided values.
         *
         * @param string $start_string Start date input.
         * @param string $end_string   End date input.
         *
         * @return array{start:DateTimeImmutable,end:DateTimeImmutable}
         */
        private static function determine_range( string $start_string, string $end_string ): array {
                $timezone = new DateTimeZone( 'UTC' );
                $start    = self::parse_date( $start_string, $timezone );
                $end      = self::parse_date( $end_string, $timezone );

                if ( ! $start || ! $end ) {
                        $now   = new DateTimeImmutable( 'now', $timezone );
                        $end   = $now->setTime( 0, 0, 0 );
                        $start = $end->sub( new DateInterval( 'P6D' ) );
                }

                if ( $end < $start ) {
                        $tmp   = $start;
                        $start = $end;
                        $end   = $tmp;
                }

                return array(
                        'start' => $start,
                        'end'   => $end,
                );
        }

        /**
         * Parse a YYYY-MM-DD date string into a DateTimeImmutable instance.
         *
         * @param string          $value    Raw date string.
         * @param DateTimeZone    $timezone Target timezone.
         */
        private static function parse_date( string $value, DateTimeZone $timezone ): ?DateTimeImmutable {
                if ( '' === $value ) {
                        return null;
                }

                $date = DateTimeImmutable::createFromFormat( 'Y-m-d', $value, $timezone );

                if ( ! $date instanceof DateTimeImmutable ) {
                        return null;
                }

                return $date->setTime( 0, 0, 0 );
        }

        /**
         * Compute the nonce action for the provided range.
         *
         * @param string $start Start date string.
         * @param string $end   End date string.
         */
        private static function nonce_action( string $start, string $end ): string {
                return sprintf( 'fbm_reports_export_%s_%s', $start, $end );
        }

        /**
         * Stream a UTF-8 BOM prefixed CSV response.
         *
         * @param array<int,array<string,mixed>> $rows   Attendance rows.
         * @param DateTimeImmutable              $start  Range start.
         * @param DateTimeImmutable              $end    Range end.
         */
        private static function stream_csv( array $rows, DateTimeImmutable $start, DateTimeImmutable $end ): void {
                $filename = sprintf( 'attendance-%s-%s.csv', $start->format( 'Ymd' ), $end->format( 'Ymd' ) );
                $filename = sanitize_file_name( $filename );

                header( 'Content-Type: text/csv; charset=utf-8' );
                header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
                header( 'Pragma: no-cache' );
                header( 'Expires: 0' );

                echo "\xEF\xBB\xBF"; // UTF-8 BOM.

                $output = fopen( 'php://output', 'wb' );

                if ( false === $output ) {
                        wp_die( esc_html__( 'Unable to open export stream.', 'foodbank-manager' ) );
                }

                fputcsv(
                        $output,
                        array(
                                __( 'Collected Date', 'foodbank-manager' ),
                                __( 'Collected Time', 'foodbank-manager' ),
                                __( 'Member Reference', 'foodbank-manager' ),
                                __( 'Member Status', 'foodbank-manager' ),
                                __( 'Method', 'foodbank-manager' ),
                                __( 'Note', 'foodbank-manager' ),
                                __( 'Recorded By', 'foodbank-manager' ),
                        )
                );

                foreach ( $rows as $row ) {
                        $date   = isset( $row['collected_date'] ) ? (string) $row['collected_date'] : '';
                        $time   = isset( $row['collected_at'] ) ? (string) $row['collected_at'] : '';
                        $ref    = isset( $row['member_reference'] ) ? (string) $row['member_reference'] : '';
                        $status = isset( $row['status'] ) ? (string) $row['status'] : '';
                        $method = isset( $row['method'] ) ? (string) $row['method'] : '';
                        $note   = array_key_exists( 'note', $row ) && null !== $row['note'] ? (string) $row['note'] : '';
                        $user   = array_key_exists( 'recorded_by', $row ) && null !== $row['recorded_by'] ? (string) $row['recorded_by'] : '';

                        fputcsv( $output, array( $date, $time, $ref, $status, $method, $note, $user ) );
                }

                fclose( $output );

                if ( ! defined( 'FBM_TESTING' ) || ! FBM_TESTING ) {
                        exit;
                }
        }
}
