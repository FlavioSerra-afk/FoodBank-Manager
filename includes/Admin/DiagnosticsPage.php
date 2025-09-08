<?php
/**
 * Diagnostics admin page controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FBM\Auth\Capabilities;
use FBM\Core\Retention;
use FoodBankManager\Core\Install;
use FoodBankManager\Security\Helpers;
use FoodBankManager\Http\DiagnosticsController;
use function sanitize_key;
use function wp_unslash;
use function get_option;
use function get_transient;
use function wp_next_scheduled;
use function wp_get_schedule;
use function wp_get_schedules;
use function current_user_can;
use function wp_die;
use function esc_html__;
use function add_settings_error;
use function filter_input;

/**
 * Diagnostics admin page.
 */
class DiagnosticsPage {
	private const ACTION_REPAIR_CAPS   = 'fbm_repair_caps';
	private const ACTION_RETENTION_RUN = 'fbm_retention_run';
	private const ACTION_RETENTION_DRY = 'fbm_retention_dry_run';
	private const OVERDUE_GRACE        = 300;

		/**
		 * Cached retention summary.
		 *
		 * @var array<string,array<string,int>>
		 */
	private static array $retention_summary = array();

		/**
		 * Handle POST actions.
		 *
		 * @return void
		 */
	private static function handle_actions(): void {
		$action_raw = filter_input( INPUT_POST, 'fbm_action', FILTER_UNSAFE_RAW );
		if ( null === $action_raw ) {
			return;
		}
		$action = sanitize_key( (string) wp_unslash( $action_raw ) );
		if ( self::ACTION_REPAIR_CAPS === $action ) {
					check_admin_referer( self::ACTION_REPAIR_CAPS );
			if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
						wp_die( esc_html__( 'Insufficient permissions', 'foodbank-manager' ) );
			}

					Capabilities::ensure_for_admin();

					add_settings_error(
						'fbm_diagnostics',
						'fbm_caps_repaired',
						__( 'FBM capabilities repaired for Administrator.', 'foodbank-manager' ),
						'updated'
					);
			return;
		}

		if ( self::ACTION_RETENTION_RUN === $action ) {
				check_admin_referer( self::ACTION_RETENTION_RUN );
				self::$retention_summary = Retention::run_now();
				add_settings_error( 'fbm_diagnostics', 'fbm_retention_run', __( 'Retention run executed.', 'foodbank-manager' ), 'updated' );
			return;
		}

		if ( self::ACTION_RETENTION_DRY === $action ) {
				check_admin_referer( self::ACTION_RETENTION_DRY );
				self::$retention_summary = Retention::dry_run();
				add_settings_error( 'fbm_diagnostics', 'fbm_retention_dry', __( 'Retention dry-run complete.', 'foodbank-manager' ), 'updated' );
		}
	}

		/**
		 * Render diagnostics template.
		 *
		 * @return void
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

			self::handle_actions();

			$notices_render_count = Notices::getRenderCount();
			$boot_ts              = (int) get_transient( 'fbm_boot_ok' );
			$boot_status          = $boot_ts > 0 ? gmdate( 'Y-m-d H:i:s', $boot_ts ) : 'not recorded';
			$caps                 = Capabilities::all();
			$owned                = array_filter( $caps, static fn( $c ) => current_user_can( $c ) );
			$caps_count           = count( $owned ) . ' / ' . count( $caps );

			$rows = array();
			global $menu;
		$count = 0;
		if ( is_array( $menu ) ) {
			foreach ( $menu as $it ) {
				if ( isset( $it[2] ) && 'fbm' === $it[2] ) {
					++$count;
				}
			}
		}
				$rows[] = array( 'Menu parents registered', (string) $count, 1 === $count ? 'ok' : 'warn' );
			$counts     = \FBM\Core\Trace::counts();
			$dupes      = array_filter( $counts, static fn( $c ) => $c > 1 );
			$render_ok  = empty( $dupes );

						$install_scan                  = Install::getCachedScan();
						$last_consolidation            = (array) get_option( 'fbm_last_consolidation', array() );
						$last_activation_consolidation = (array) get_option( 'fbm_last_activation_consolidation', array() );
						$smtp                          = DiagnosticsController::transport_info();
						$test_to                       = Helpers::mask_email( (string) get_option( 'admin_email' ) );
						$notice                        = sanitize_key( (string) filter_input( INPUT_GET, 'notice', FILTER_UNSAFE_RAW ) );
						/* @psalm-suppress UnresolvableInclude */
						require FBM_PATH . 'templates/admin/diagnostics.php';
	}

		/**
		 * Get summary from last retention run in this request or option.
		 *
		 * @return array<string,array<string,int>>
		 */
	public static function retention_summary(): array {
		if ( ! empty( self::$retention_summary ) ) {
			return self::$retention_summary;
		}
			$stored  = get_option( 'fbm_retention_tick_last_summary', array() );
			$summary = is_array( $stored ) ? $stored : array();
			$summary = apply_filters( 'fbm_retention_summary', $summary );
			return $summary;
	}

		/**
		 * Get cron event diagnostics.
		 *
		 * @return array<int,array{hook:string,schedule:string,next_run:int,last_run:int,overdue:bool}>
		 */
	public static function cron_status(): array {
			$schedules = wp_get_schedules();
			$now       = time();
			$cron      = (array) get_option( 'cron', array() );
			$hooks     = Retention::events();
		foreach ( $cron as $events ) {
			foreach ( $events as $hook => $details ) {
				if ( str_starts_with( (string) $hook, 'fbm_' ) ) {
						$hooks[] = (string) $hook;
				}
			}
		}
			$hooks = array_unique( $hooks );
			$out   = array();
		foreach ( $hooks as $hook ) {
					$next     = (int) wp_next_scheduled( $hook );
					$schedule = (string) wp_get_schedule( $hook );
					$interval = isset( $schedules[ $schedule ]['interval'] ) ? (int) $schedules[ $schedule ]['interval'] : self::OVERDUE_GRACE;
					$last     = (int) get_option( $hook . '_last_run', 0 );
					$grace    = min( self::OVERDUE_GRACE, $interval );
					$out[]    = array(
						'hook'     => (string) $hook,
						'schedule' => $schedule,
						'next_run' => $next,
						'last_run' => $last,
						'overdue'  => $next > 0 && $now > $next + $grace,
					);
		}
			return $out;
	}
}
