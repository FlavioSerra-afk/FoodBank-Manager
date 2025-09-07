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
use FoodBankManager\Core\Options;
use FoodBankManager\Core\Install;
use function sanitize_text_field;
use function sanitize_key;
use function sanitize_email;
use function wp_unslash;
use function filter_input;
use function get_option;
use function get_transient;
use function wp_next_scheduled;
use function wp_get_schedule;
use function wp_get_schedules;
use function add_query_arg;
use function menu_page_url;
use function wp_safe_redirect;
use function esc_url_raw;
use function current_user_can;
use function wp_die;
use function esc_html__;
use function add_settings_error;
use function is_email;
use function add_filter;
use function remove_filter;
use function wp_mail;

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
		 * Route the diagnostics page.
		 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

			$method = strtoupper( sanitize_text_field( (string) filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_UNSAFE_RAW ) ) );
		if ( 'POST' !== $method ) {
				return;
		}

			$action_raw = filter_input( INPUT_POST, 'fbm_action', FILTER_UNSAFE_RAW );
			$action     = sanitize_key( (string) wp_unslash( $action_raw ?? '' ) );
		if ( 'mail_test' === $action ) {
				check_admin_referer( 'fbm_diag_mail_test', '_fbm_nonce' );
				self::send_test_email();
		}
	}

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

			$stored = get_option( 'fbm_retention_tick_last_summary', array() );
			return is_array( $stored ) ? $stored : array();
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

	/**
	 * Send a test email to the current user.
	 */
	private static function send_test_email(): void {
					$to         = sanitize_email( (string) get_option( 'admin_email' ) );
					$from_name  = sanitize_text_field( (string) Options::get( 'emails.from_name' ) );
					$from_email = sanitize_email( (string) Options::get( 'emails.from_email' ) );

		$from_filter     = static function () use ( $from_email ): string {
			return $from_email;
		};
			$name_filter = static function () use ( $from_name ): string {
				return $from_name;
			};

		if ( is_email( $from_email ) ) {
			add_filter( 'wp_mail_from', $from_filter );
		}
		if ( '' !== $from_name ) {
			add_filter( 'wp_mail_from_name', $name_filter );
		}

			$sent = wp_mail(
				$to,
				__( 'FoodBank Manager test email', 'foodbank-manager' ),
				__( 'This is a test email from FoodBank Manager.', 'foodbank-manager' )
			);

		if ( is_email( $from_email ) ) {
			remove_filter( 'wp_mail_from', $from_filter );
		}
		if ( '' !== $from_name ) {
			remove_filter( 'wp_mail_from_name', $name_filter );
		}

			$notice = $sent ? 'sent' : 'error';
			$url    = add_query_arg( array( 'notice' => $notice ), menu_page_url( 'fbm_diagnostics', false ) );
			wp_safe_redirect( esc_url_raw( $url ), 303 );
			exit;
	}
}
