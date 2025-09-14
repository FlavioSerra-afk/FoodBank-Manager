<?php
/**
 * Manager dashboard shortcode.
 *
 * @package FBM\Shortcodes
 */

declare(strict_types=1);

namespace FBM\Shortcodes;

use FoodBankManager\Attendance\AttendanceRepo;
use FoodBankManager\UI\Theme;
use FoodBankManager\Core\Plugin;
use FBM\Core\UserPrefs;
use DateInterval;
use DateTimeImmutable;
use function apply_filters;
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
use function filter_input;
use function wp_unslash;
use function wp_register_style;
use function wp_add_inline_style;
use function wp_enqueue_style;
use function add_filter;

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
				return '<div class="fbm-no-permission">'
					. esc_html__( 'You do not have permission to view the dashboard.', 'foodbank-manager' )
					. '</div>';
		}

				wp_enqueue_style( 'fbm-public', plugins_url( 'assets/css/public.css', FBM_FILE ), array(), Plugin::VERSION ); // @phpstan-ignore-line
												wp_add_inline_style( 'fbm-public', Theme::css_variables_scoped() );
												wp_register_style( 'fbm-frontend-dashboard', plugins_url( 'assets/css/frontend-dashboard.css', FBM_FILE ), array(), Plugin::VERSION );
												wp_enqueue_style( 'fbm-frontend-dashboard' );

					$atts         = shortcode_atts(
						array(
							'period'      => '7d',
							'compare'     => '0',
							'sparkline'   => '1',
							'event'       => '',
							'type'        => 'all',
							'policy_only' => '0',
						),
						$atts,
						'fbm_dashboard'
					);
					$period       = self::sanitize_period( $atts['period'] );
					$do_sparkline = self::sanitize_flag( $atts['sparkline'] );

					$incoming  = array();
					$get_event = filter_input( INPUT_GET, 'fbm_event', FILTER_UNSAFE_RAW );
		if ( null !== $get_event ) {
				$incoming['event'] = self::sanitize_event( (string) wp_unslash( $get_event ) );
		}
					$get_type = filter_input( INPUT_GET, 'fbm_type', FILTER_UNSAFE_RAW );
		if ( null !== $get_type ) {
				$incoming['type'] = self::sanitize_type( (string) wp_unslash( $get_type ) );
		}
					$get_policy = filter_input( INPUT_GET, 'fbm_policy_only', FILTER_UNSAFE_RAW );
		if ( null !== $get_policy ) {
				$incoming['policy_only'] = self::sanitize_flag( (string) wp_unslash( $get_policy ) ) ? 1 : 0;
		}
		if ( isset( $_GET['preset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$incoming['preset'] = sanitize_key( (string) wp_unslash( $_GET['preset'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Recommended
		}
		if ( isset( $_GET['tags'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$incoming['tags'] = array_map( 'sanitize_key', (array) wp_unslash( $_GET['tags'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Recommended
		}
		if ( isset( $_GET['compare'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$incoming['compare'] = self::sanitize_flag( (string) wp_unslash( $_GET['compare'] ) ) ? 1 : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Recommended
		}

					$user        = get_current_user_id();
					$saved       = UserPrefs::get_dashboard_filters( $user );
					$merged      = array_merge( $saved, $incoming );
					$event       = self::sanitize_event( (string) ( $merged['event'] ?? $atts['event'] ) );
					$type        = self::sanitize_type( (string) ( $merged['type'] ?? $atts['type'] ) );
					$policy_only = isset( $merged['policy_only'] ) ? (bool) $merged['policy_only'] : self::sanitize_flag( $atts['policy_only'] );
					$preset      = isset( $merged['preset'] ) ? sanitize_key( (string) $merged['preset'] ) : '';
					$tags        = isset( $merged['tags'] ) && is_array( $merged['tags'] ) ? array_map( 'sanitize_key', $merged['tags'] ) : array();
					$do_compare  = ! empty( $merged['compare'] ?? $atts['compare'] );
					UserPrefs::set_dashboard_filters(
						$user,
						array(
							'event'       => $event,
							'type'        => $type,
							'policy_only' => $policy_only ? 1 : 0,
							'preset'      => $preset,
							'tags'        => $tags,
							'compare'     => $do_compare ? 1 : 0,
						)
					);

					$since = self::since_from_period( $period );

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

					$summary_delta = 0;
					$deltas        = array();
					if ( $do_compare ) {
							$now        = (int) apply_filters( 'fbm_now', time() );
							$to_ts      = $now;
							$from_ts    = (int) $since->getTimestamp();
							$window     = $to_ts - $from_ts;
							$prev_to    = $from_ts - 1;
							$prev_from  = $prev_to - $window;
							$prev_since = new DateTimeImmutable( '@' . $prev_from );
							$prev_since = $prev_since->setTimezone( new \DateTimeZone( 'UTC' ) );
							$prev       = get_transient( $base . 'prev' );
						if ( ! is_array( $prev ) ) {
								$prev = AttendanceRepo::period_totals( $prev_since, $filters );
								set_transient( $base . 'prev', $prev, 60 );
						}
							$prev_window = array();
						foreach ( $prev as $k => $v ) {
								$prev_window[ $k ] = max( 0, (int) $v - (int) ( $totals[ $k ] ?? 0 ) );
						}
						foreach ( $totals as $k => $v ) {
								$deltas[ $k ] = self::delta( (int) $v, (int) ( $prev_window[ $k ] ?? 0 ) );
						}
							$summary_delta = (int) $totals['present'] - (int) ( $prev_window['present'] ?? 0 );
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
					$counts             = $totals; // local vars for template.
					$series_attr        = $do_sparkline ? $series : array();
					$deltas_attr        = $deltas;
					$period_attr        = $period;
					$updated            = $updated_at;
					$summary_delta_attr = $summary_delta;
					$filters_attr       = array(
						'event'       => $event ? (string) $event : '',
						'type'        => $type,
						'policy_only' => $policy_only,
						'preset'      => $preset,
						'tags'        => $tags,
						'compare'     => $do_compare,
					);
					$csv_url_attr       = $csv_url;
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
				return '' !== $event ? $event : null;
	}

		/**
		 * Convert period to since timestamp.
		 *
		 * @param string $period Period key.
		 * @return DateTimeImmutable
		 */
	public static function since_from_period( string $period ): DateTimeImmutable {
						$day = defined( 'DAY_IN_SECONDS' ) ? (int) DAY_IN_SECONDS : 86400;
						$now = (int) apply_filters( 'fbm_now', time() );
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
