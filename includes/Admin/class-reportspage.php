<?php
/**
 * Reports admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use FoodBankManager\Core\Cache;
use FoodBankManager\Core\Schedule;
use FoodBankManager\Reports\CsvExporter;
use FoodBankManager\Reports\ReportsRepository;
use FoodBankManager\Reports\SummaryBuilder;
use wpdb;
use function absint;
use function array_merge;
use function add_action;
use function add_menu_page;
use function add_query_arg;
use function admin_url;
use function ceil;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function is_array;
use function is_readable;
use function in_array;
use function max;
use function min;
use function number_format_i18n;
use function sanitize_key;
use function sanitize_text_field;
use function sort;
use function sprintf;
use function time;
use function wp_create_nonce;
use function wp_die;
use function wp_nonce_field;
use function wp_safe_redirect;
use function wp_unslash;
use function wp_verify_nonce;
use function count;

/**
 * Presents attendance summaries and CSV export controls.
 */
final class ReportsPage {
	private const MENU_SLUG              = 'fbm-reports';
	private const TEMPLATE               = 'templates/admin/reports.php';
	private const START_PARAM            = 'fbm_report_start';
	private const END_PARAM              = 'fbm_report_end';
	private const QUICK_RANGE_PARAM      = 'fbm_quick_range';
	private const PAGE_PARAM             = 'fbm_report_page';
	private const PER_PAGE_PARAM         = 'fbm_report_per_page';
	private const ACTION_PARAM           = 'fbm_report_action';
	private const ACTION_EXPORT          = 'export';
	private const NONCE_FIELD            = 'fbm_report_nonce';
	private const REFRESH_PARAM          = 'fbm_report_refresh';
	private const REFRESH_NONCE_FIELD    = 'fbm_report_refresh_nonce';
	private const INVALIDATE_ACTION      = 'fbm_reports_invalidate';
	private const INVALIDATE_NONCE_FIELD = 'fbm_reports_invalidate_nonce';
	private const MESSAGE_PARAM          = 'fbm_report_message';
	private const RANGE_LAST7            = 'last7';
	private const RANGE_LAST30           = 'last30';
	private const RANGE_CUSTOM           = 'custom';
	private const DEFAULT_PER_PAGE       = 25;
	private const MAX_PER_PAGE           = 500;
	private const MAX_RANGE_DAYS         = 180;

		/**
		 * Register WordPress hooks.
		 */
	public static function register(): void {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
			add_action( 'admin_init', array( __CLASS__, 'handle_export' ) );
			add_action( 'admin_post_' . self::INVALIDATE_ACTION, array( __CLASS__, 'handle_invalidate' ) );
	}

		/**
		 * Register the admin menu entry.
		 */
	public static function register_menu(): void {
			add_menu_page(
				esc_html__( 'Food Bank Reports', 'foodbank-manager' ),
				esc_html__( 'Food Bank Reports', 'foodbank-manager' ),
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

			$request = self::parse_request();
			$notices = $request['notices'];

			$per_page = max( 1, (int) $request['per_page'] );

		if ( $per_page > self::MAX_PER_PAGE ) {
				$per_page  = self::MAX_PER_PAGE;
				$notices[] = array(
					'type' => 'info',
					'text' => esc_html__( 'Rows per page capped at 500.', 'foodbank-manager' ),
				);
		}

			$page = max( 1, (int) $request['page'] );

			$schedule = new Schedule();
			$window   = $schedule->current_window();

			$range = self::resolve_range( $request['start'], $request['end'], $request['quick_range'], $window );

			$notices        = array_merge( $notices, $range['notices'] );
			$selected_range = $range['quick_range'];
			$start_display  = $range['start_display'];
			$end_display    = $range['end_display'];
			$start          = $range['start'];
			$end            = $range['end'];

			$force_refresh = (bool) $request['refresh'];

		if ( $force_refresh ) {
				$notices[] = array(
					'type' => 'info',
					'text' => esc_html__( 'Cache bypass requested; latest data loaded.', 'foodbank-manager' ),
				);
		}

			$repository   = new ReportsRepository( $wpdb );
			$builder      = new SummaryBuilder( $repository );
			$filters      = array();
			$summary_info = $builder->get_summary( $start, $end, $filters, $force_refresh );
			$total_info   = $builder->get_total( $start, $end, $filters, $force_refresh );
			$total_rows   = max( 0, (int) $total_info['total'] );
			$total_pages  = $total_rows > 0 ? (int) ceil( $total_rows / $per_page ) : 1;

		if ( $page > $total_pages && $total_rows > 0 ) {
				$page      = $total_pages;
				$notices[] = array(
					'type' => 'info',
					'text' => esc_html__( 'Page reset to the last available page for this range.', 'foodbank-manager' ),
				);
		}

			$rows_info = $builder->get_rows( $start, $end, $filters, $page, $per_page, $force_refresh );
			$rows      = $rows_info['rows'];

			$cache_hit    = $summary_info['cache_hit'] || $total_info['cache_hit'] || $rows_info['cache_hit'];
			$generated_at = max( $summary_info['generated_at'], $total_info['generated_at'], $rows_info['generated_at'] );
			$age_seconds  = max( 0, time() - $generated_at );

		if ( $cache_hit ) {
				$cache_message = sprintf(
						/* translators: %s: Number of seconds since the cache was generated. */
					esc_html__( 'Using cached results (updated %s seconds ago).', 'foodbank-manager' ),
					number_format_i18n( $age_seconds )
				);
		} else {
				$cache_message = esc_html__( 'Fresh results generated moments ago.', 'foodbank-manager' );
		}

			$pagination_base = array(
				'page'                  => self::MENU_SLUG,
				self::QUICK_RANGE_PARAM => $selected_range,
				self::START_PARAM       => $start_display,
				self::END_PARAM         => $end_display,
				self::PER_PAGE_PARAM    => $per_page,
			);

			$prev_url = '';
			$next_url = '';

			if ( $page > 1 ) {
					$prev_url = add_query_arg( array_merge( $pagination_base, array( self::PAGE_PARAM => $page - 1 ) ), admin_url( 'admin.php' ) );
			}

			if ( $page < $total_pages ) {
					$next_url = add_query_arg( array_merge( $pagination_base, array( self::PAGE_PARAM => $page + 1 ) ), admin_url( 'admin.php' ) );
			}

			$refresh_url = add_query_arg(
				array_merge(
					$pagination_base,
					array(
						self::PAGE_PARAM          => $page,
						self::REFRESH_PARAM       => '1',
						self::REFRESH_NONCE_FIELD => wp_create_nonce( 'fbm_report_refresh' ),
					)
				),
				admin_url( 'admin.php' )
			);

			$message_code = self::message_code();

		if ( 'cache_cleared' === $message_code ) {
				$notices[] = array(
					'type' => 'success',
					'text' => esc_html__( 'Report cache cleared.', 'foodbank-manager' ),
				);
		}

			$per_page_options = self::per_page_options();

		if ( ! in_array( $per_page, $per_page_options, true ) ) {
				$per_page_options[] = $per_page;
				sort( $per_page_options );
		}

			$context = array(
				'page_slug'              => self::MENU_SLUG,
				'start_param'            => self::START_PARAM,
				'end_param'              => self::END_PARAM,
				'quick_range_param'      => self::QUICK_RANGE_PARAM,
				'page_param'             => self::PAGE_PARAM,
				'per_page_param'         => self::PER_PAGE_PARAM,
				'start_value'            => $start_display,
				'end_value'              => $end_display,
				'quick_range'            => $selected_range,
				'per_page'               => $per_page,
				'per_page_options'       => $per_page_options,
				'summary'                => $summary_info['data'],
				'rows'                   => $rows,
				'total_rows'             => $total_rows,
				'pagination'             => array(
						'current'  => $page,
						'total'    => $total_pages,
						'from'     => $total_rows > 0 ? ( ( ( $page - 1 ) * $per_page ) + 1 ) : 0,
						'to'       => $total_rows > 0 ? ( ( ( $page - 1 ) * $per_page ) + count( $rows ) ) : 0,
						'has_prev' => $page > 1,
						'has_next' => $page < $total_pages,
						'prev_url' => $prev_url,
						'next_url' => $next_url,
				),
				'cache_message'          => $cache_message,
				'cache_hit'              => $cache_hit,
				'cache_age_seconds'      => $age_seconds,
				'cache_bypass'           => $force_refresh,
				'refresh_url'            => $refresh_url,
				'notices'                => $notices,
				'schedule_notice'        => Schedule::window_notice( $window ),
				'window_labels'          => Schedule::window_labels( $window ),
				'export_action_param'    => self::ACTION_PARAM,
				'export_action_value'    => self::ACTION_EXPORT,
				'nonce_field'            => wp_nonce_field( self::nonce_action( $start_display, $end_display, $selected_range ), self::NONCE_FIELD, true, false ),
				'export_params'          => array(
						'page'                  => self::MENU_SLUG,
						self::ACTION_PARAM      => self::ACTION_EXPORT,
						self::START_PARAM       => $start_display,
						self::END_PARAM         => $end_display,
						self::QUICK_RANGE_PARAM => $selected_range,
						self::PER_PAGE_PARAM    => $per_page,
				),
				'filter_action'          => admin_url( 'admin.php' ),
				'per_page_max'           => self::MAX_PER_PAGE,
				'manager_can_invalidate' => current_user_can( 'fbm_manage' ), // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
					'invalidate_form'    => array(
							'action'      => admin_url( 'admin-post.php' ),
							'nonce_field' => wp_nonce_field( 'fbm_reports_invalidate', self::INVALIDATE_NONCE_FIELD, true, false ),
					),
					'invalidate_action'  => self::INVALIDATE_ACTION,
					'quick_ranges'       => array(
							array(
									'value' => self::RANGE_LAST7,
									'label' => esc_html__( 'Last 7 days', 'foodbank-manager' ),
							),
							array(
									'value' => self::RANGE_LAST30,
									'label' => esc_html__( 'Last 30 days', 'foodbank-manager' ),
							),
					),
					'custom_range_label' => esc_html__( 'Custom range', 'foodbank-manager' ),
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
	public static function handle_export(): void {
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

			$start_input = isset( $_GET[ self::START_PARAM ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ self::START_PARAM ] ) ) : '';
			$end_input   = isset( $_GET[ self::END_PARAM ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ self::END_PARAM ] ) ) : '';
			$range_input = isset( $_GET[ self::QUICK_RANGE_PARAM ] ) ? sanitize_key( wp_unslash( (string) $_GET[ self::QUICK_RANGE_PARAM ] ) ) : self::RANGE_LAST7;

			$allowed_ranges = array( self::RANGE_LAST7, self::RANGE_LAST30, self::RANGE_CUSTOM );

		if ( ! in_array( $range_input, $allowed_ranges, true ) ) {
				$range_input = self::RANGE_LAST7;
		}

			check_admin_referer( self::nonce_action( $start_input, $end_input, $range_input ), self::NONCE_FIELD );

			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				wp_die( esc_html__( 'Database connection unavailable.', 'foodbank-manager' ) );
		}

			$schedule = new Schedule();
			$window   = $schedule->current_window();
			$range    = self::resolve_range( $start_input, $end_input, $range_input, $window );

			$repository = new ReportsRepository( $wpdb );
			$exporter   = new CsvExporter( $repository );

			$filename = sprintf( 'attendance-%s-%s', $range['start']->format( 'Ymd' ), $range['end']->format( 'Ymd' ) );

			$exporter->stream( $range['start'], $range['end'], array(), $filename );
	}

		/**
		 * Handle cache invalidation requests.
		 */
	public static function handle_invalidate(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				wp_die( esc_html__( 'You do not have permission to invalidate the report cache.', 'foodbank-manager' ) );
		}

			check_admin_referer( 'fbm_reports_invalidate', self::INVALIDATE_NONCE_FIELD );

			Cache::purge_group( 'reports' );

			$redirect = add_query_arg(
				array(
					'page'              => self::MENU_SLUG,
					self::MESSAGE_PARAM => 'cache_cleared',
				),
				admin_url( 'admin.php' )
			);

			wp_safe_redirect( $redirect );
			exit;
	}

		/**
		 * Parse incoming request parameters.
		 *
		 * @return array{
		 *     start:string,
		 *     end:string,
		 *     quick_range:string,
		 *     page:int,
		 *     per_page:int,
		 *     refresh:bool,
		 *     notices:array<int,array{type:string,text:string}>
		 * }
		 */
	private static function parse_request(): array {
			$notices = array();

			$start = isset( $_GET[ self::START_PARAM ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ self::START_PARAM ] ) ) : '';
			$end   = isset( $_GET[ self::END_PARAM ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ self::END_PARAM ] ) ) : '';

			$quick_range = isset( $_GET[ self::QUICK_RANGE_PARAM ] ) ? sanitize_key( wp_unslash( (string) $_GET[ self::QUICK_RANGE_PARAM ] ) ) : self::RANGE_LAST7;
			$allowed     = array( self::RANGE_LAST7, self::RANGE_LAST30, self::RANGE_CUSTOM );

		if ( ! in_array( $quick_range, $allowed, true ) ) {
				$quick_range = self::RANGE_LAST7;
		}

			$page     = isset( $_GET[ self::PAGE_PARAM ] ) ? absint( $_GET[ self::PAGE_PARAM ] ) : 1;
			$per_page = isset( $_GET[ self::PER_PAGE_PARAM ] ) ? absint( $_GET[ self::PER_PAGE_PARAM ] ) : self::DEFAULT_PER_PAGE;

			$refresh = false;

		if ( isset( $_GET[ self::REFRESH_PARAM ] ) ) {
				$nonce = isset( $_GET[ self::REFRESH_NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ self::REFRESH_NONCE_FIELD ] ) ) : '';

			if ( '' !== $nonce && wp_verify_nonce( $nonce, 'fbm_report_refresh' ) ) {
					$refresh = true;
			} else {
					$notices[] = array(
						'type' => 'error',
						'text' => esc_html__( 'The refresh link has expired. Showing cached data instead.', 'foodbank-manager' ),
					);
			}
		}

			return array(
				'start'       => $start,
				'end'         => $end,
				'quick_range' => $quick_range,
				'page'        => $page > 0 ? $page : 1,
				'per_page'    => $per_page > 0 ? $per_page : self::DEFAULT_PER_PAGE,
				'refresh'     => $refresh,
				'notices'     => $notices,
			);
	}

		/**
		 * Resolve the normalized date range and related metadata.
		 *
		 * @param string               $start_input  Start date input (Y-m-d).
		 * @param string               $end_input    End date input (Y-m-d).
		 * @param string               $quick_range  Requested range key.
		 * @param array<string,string> $window       Weekly window configuration.
		 *
		 * @return array{
		 *     start:DateTimeImmutable,
		 *     end:DateTimeImmutable,
		 *     start_display:string,
		 *     end_display:string,
		 *     quick_range:string,
		 *     notices:array<int,array{type:string,text:string}>
		 * }
		 */
	private static function resolve_range( string $start_input, string $end_input, string $quick_range, array $window ): array {
			$notices      = array();
			$selected     = $quick_range;
			$utc_timezone = new DateTimeZone( 'UTC' );

		try {
				$timezone = new DateTimeZone( $window['timezone'] ?? 'UTC' );
		} catch ( Exception ) {
				$timezone = new DateTimeZone( 'UTC' );
		}

			$expected_day = Schedule::day_to_index( $window['day'] ?? 'thursday' );
			$now_local    = new DateTimeImmutable( 'now', $timezone );
			$end_local    = self::align_to_window_day( $now_local, $expected_day );

		switch ( $quick_range ) {
			case self::RANGE_LAST30:
					$start_local = $end_local->modify( '-29 days' );
				break;
			case self::RANGE_CUSTOM:
					$parsed_start = self::parse_local_date( $start_input, $timezone );
					$parsed_end   = self::parse_local_date( $end_input, $timezone );

				if ( ! $parsed_start || ! $parsed_end ) {
						$selected    = self::RANGE_LAST7;
						$notices[]   = array(
							'type' => 'error',
							'text' => esc_html__( 'Invalid custom dates. Showing the last seven days instead.', 'foodbank-manager' ),
						);
						$start_local = $end_local->modify( '-6 days' );
						break;
				}

					$start_local = self::align_to_window_day( $parsed_start, $expected_day );
					$end_local   = self::align_to_window_day( $parsed_end, $expected_day );

				if ( $end_local > self::align_to_window_day( $now_local, $expected_day ) ) {
						$end_local = self::align_to_window_day( $now_local, $expected_day );
						$notices[] = array(
							'type' => 'info',
							'text' => esc_html__( 'End date adjusted to the most recent collection day.', 'foodbank-manager' ),
						);
				}

				if ( $end_local < $start_local ) {
						$start_local = $end_local;
						$notices[]   = array(
							'type' => 'info',
							'text' => esc_html__( 'Start date adjusted to match the end date.', 'foodbank-manager' ),
						);
				}

					$diff_days = (int) $end_local->diff( $start_local )->days;

				if ( $diff_days > self::MAX_RANGE_DAYS ) {
						$start_local = $end_local->modify( sprintf( '-%d days', self::MAX_RANGE_DAYS ) );
						$notices[]   = array(
							'type' => 'info',
							'text' => esc_html__( 'Date range shortened to the most recent 180 days.', 'foodbank-manager' ),
						);
				}

				break;
			case self::RANGE_LAST7:
			default:
					$selected    = self::RANGE_LAST7;
					$start_local = $end_local->modify( '-6 days' );
				break;
		}

			$start_local = $start_local->setTime( 0, 0, 0 );
			$end_local   = $end_local->setTime( 0, 0, 0 );

		if ( $end_local < $start_local ) {
				$end_local = $start_local;
		}

			$start_utc = $start_local->setTimezone( $utc_timezone );
			$end_utc   = $end_local->setTimezone( $utc_timezone );

			return array(
				'start'         => $start_utc,
				'end'           => $end_utc,
				'start_display' => $start_local->format( 'Y-m-d' ),
				'end_display'   => $end_local->format( 'Y-m-d' ),
				'quick_range'   => $selected,
				'notices'       => $notices,
			);
	}

		/**
		 * Align a date to the most recent scheduled collection day.
		 *
		 * @param DateTimeImmutable $date         Date to align.
		 * @param int               $expected_day Day-of-week index (1 = Monday).
		 */
	private static function align_to_window_day( DateTimeImmutable $date, int $expected_day ): DateTimeImmutable {
			$aligned     = $date->setTime( 0, 0, 0 );
			$current_day = (int) $aligned->format( 'N' );
			$offset      = ( $current_day - $expected_day + 7 ) % 7;

		if ( $offset > 0 ) {
				$aligned = $aligned->modify( sprintf( '-%d days', $offset ) );
		}

			return $aligned;
	}

		/**
		 * Parse a local date string into a DateTimeImmutable instance.
		 *
		 * @param string       $value    Input value.
		 * @param DateTimeZone $timezone Target timezone.
		 */
	private static function parse_local_date( string $value, DateTimeZone $timezone ): ?DateTimeImmutable {
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
		 * Compose the nonce action string for exports.
		 *
		 * @param string $start Start date string.
		 * @param string $end   End date string.
		 * @param string $range Selected range key.
		 */
	private static function nonce_action( string $start, string $end, string $range ): string {
			return sprintf( 'fbm_reports_export_%s_%s_%s', $start, $end, $range );
	}

		/**
		 * Determine the current message code.
		 */
	private static function message_code(): string {
		if ( ! isset( $_GET[ self::MESSAGE_PARAM ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only context.
			return '';
		}

		return sanitize_key( wp_unslash( (string) $_GET[ self::MESSAGE_PARAM ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only context.
	}

		/**
		 * Provide the selectable rows-per-page options.
		 *
		 * @return array<int,int>
		 */
	private static function per_page_options(): array {
			return array( 25, 50, 100, 250, 500 );
	}
}
