<?php
/**
 * Schedule settings admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use FoodBankManager\Core\Schedule;
use WP_Error;
use function __;
use function add_action;
use function add_query_arg;
use function add_submenu_page;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function filter_input;
use function is_array;
use function is_readable;
use function sanitize_key;
use function sanitize_text_field;
use function trim;
use function update_option;
use function wp_die;
use function wp_safe_redirect;
use function wp_unslash;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;

/**
 * Provides the Food Bank schedule settings page.
 */
final class SchedulePage {

	private const MENU_SLUG     = 'fbm-schedule';
	private const PARENT_SLUG   = Menu::SLUG;
	private const TEMPLATE      = 'templates/admin/schedule-page.php';
	private const OPTION_NAME   = 'fbm_schedule_window';
	private const FORM_ACTION   = 'fbm_schedule_save';
	private const NONCE_NAME    = 'fbm_schedule_nonce';
	private const STATUS_PARAM  = 'fbm_schedule_status';
	private const MESSAGE_PARAM = 'fbm_schedule_message';

		/**
		 * Register WordPress hooks.
		 */
	public static function register(): void {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
			add_action( 'admin_post_' . self::FORM_ACTION, array( __CLASS__, 'handle_save' ) );
	}

		/**
		 * Register the admin menu entry.
		 */
	public static function register_menu(): void {
			add_submenu_page(
				self::PARENT_SLUG,
				__( 'Food Bank Schedule', 'foodbank-manager' ),
				__( 'Schedule', 'foodbank-manager' ),
				'fbm_manage', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered during activation.
				self::MENU_SLUG,
				array( __CLASS__, 'render' )
			);
	}

		/**
		 * Render the schedule page.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

			$template = FBM_PATH . self::TEMPLATE;

		if ( ! is_readable( $template ) ) {
				wp_die( esc_html__( 'Schedule admin template is missing.', 'foodbank-manager' ) );
		}

			$schedule = new Schedule();
			$window   = $schedule->current_window();

			$status_input  = filter_input( INPUT_GET, self::STATUS_PARAM, FILTER_UNSAFE_RAW );
			$message_input = filter_input( INPUT_GET, self::MESSAGE_PARAM, FILTER_UNSAFE_RAW );

			$status  = is_string( $status_input ) ? sanitize_key( $status_input ) : '';
			$message = is_string( $message_input ) ? sanitize_text_field( $message_input ) : '';

			$context = array(
				'window'       => $window,
				'status'       => $status,
				'message'      => $message,
				'form_action'  => self::FORM_ACTION,
				'nonce_action' => self::FORM_ACTION,
				'nonce_name'   => self::NONCE_NAME,
				'menu_slug'    => self::MENU_SLUG,
				'day_choices'  => self::day_choices(),
			);

			$data = $context;
			include $template;
	}

		/**
		 * Handle saving the schedule settings.
		 */
	public static function handle_save(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability defined on activation.
				wp_die( esc_html__( 'You do not have permission to save the schedule.', 'foodbank-manager' ) );
		}

			check_admin_referer( self::FORM_ACTION, self::NONCE_NAME );

			$raw = array();

		if ( isset( $_POST['fbm_schedule'] ) && is_array( $_POST['fbm_schedule'] ) ) {
				$raw = wp_unslash( $_POST['fbm_schedule'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in sanitize().
		}

			$result = self::sanitize( $raw );

			$status  = 'error';
			$message = esc_html__( 'Schedule could not be saved.', 'foodbank-manager' );

		if ( $result instanceof WP_Error ) {
				$error_message = $result->get_error_message();

			if ( '' !== $error_message ) {
					$message = $error_message;
			}
		} else {
				update_option( self::OPTION_NAME, $result );
				$status  = 'success';
				$message = esc_html__( 'Schedule saved.', 'foodbank-manager' );
		}

			$message = sanitize_text_field( $message );

			$redirect = add_query_arg(
				array(
					'page'              => self::MENU_SLUG,
					self::STATUS_PARAM  => $status,
					self::MESSAGE_PARAM => $message,
				),
				admin_url( 'admin.php' )
			);

			wp_safe_redirect( $redirect );

		if ( defined( 'FBM_TESTING' ) && FBM_TESTING ) {
				return;
		}

			exit;
	}

		/**
		 * Sanitize the schedule payload.
		 *
		 * @param mixed $value Raw schedule value.
		 *
		 * @return array{day:string,start:string,end:string,timezone:string}|WP_Error
		 */
	public static function sanitize( $value ) {
		if ( ! is_array( $value ) ) {
				return new WP_Error( 'fbm_schedule_invalid_payload', __( 'Schedule payload must be an array.', 'foodbank-manager' ) );
		}

			$day = null;

		if ( isset( $value['day'] ) ) {
				$day = self::sanitize_day( $value['day'] );

			if ( $day instanceof WP_Error ) {
					return $day;
			}
		}

		if ( ! is_string( $day ) || '' === $day ) {
				return new WP_Error( 'fbm_schedule_invalid_day', __( 'A valid schedule day must be selected.', 'foodbank-manager' ) );
		}

			$start = null;

		if ( isset( $value['start'] ) ) {
				$start = self::sanitize_time( $value['start'], 'fbm_schedule_invalid_start', __( 'Start time must be a valid 24-hour time.', 'foodbank-manager' ) );

			if ( $start instanceof WP_Error ) {
					return $start;
			}
		}

		if ( ! is_string( $start ) || '' === $start ) {
				return new WP_Error( 'fbm_schedule_invalid_start', __( 'Start time must be a valid 24-hour time.', 'foodbank-manager' ) );
		}

			$end = null;

		if ( isset( $value['end'] ) ) {
				$end = self::sanitize_time( $value['end'], 'fbm_schedule_invalid_end', __( 'End time must be a valid 24-hour time.', 'foodbank-manager' ) );

			if ( $end instanceof WP_Error ) {
					return $end;
			}
		}

		if ( ! is_string( $end ) || '' === $end ) {
				return new WP_Error( 'fbm_schedule_invalid_end', __( 'End time must be a valid 24-hour time.', 'foodbank-manager' ) );
		}

			$timezone = null;

		if ( isset( $value['timezone'] ) ) {
				$timezone = self::sanitize_timezone( $value['timezone'] );

			if ( $timezone instanceof WP_Error ) {
					return $timezone;
			}
		}

		if ( ! is_string( $timezone ) || '' === $timezone ) {
				return new WP_Error( 'fbm_schedule_invalid_timezone', __( 'Timezone must be a valid identifier.', 'foodbank-manager' ) );
		}

			$start_time = DateTimeImmutable::createFromFormat( '!H:i', $start, new DateTimeZone( 'UTC' ) );
			$end_time   = DateTimeImmutable::createFromFormat( '!H:i', $end, new DateTimeZone( 'UTC' ) );

		if ( $start_time instanceof DateTimeImmutable && $end_time instanceof DateTimeImmutable ) {
			if ( $end_time <= $start_time ) {
					return new WP_Error( 'fbm_schedule_invalid_range', __( 'End time must be after the start time.', 'foodbank-manager' ) );
			}
		}

			return array(
				'day'      => $day,
				'start'    => $start,
				'end'      => $end,
				'timezone' => $timezone,
			);
	}

		/**
		 * Provide the canonical day choices.
		 *
		 * @return array<string,string>
		 */
	public static function day_choices(): array {
			return array(
				'monday'    => __( 'Monday', 'foodbank-manager' ),
				'tuesday'   => __( 'Tuesday', 'foodbank-manager' ),
				'wednesday' => __( 'Wednesday', 'foodbank-manager' ),
				'thursday'  => __( 'Thursday', 'foodbank-manager' ),
				'friday'    => __( 'Friday', 'foodbank-manager' ),
				'saturday'  => __( 'Saturday', 'foodbank-manager' ),
				'sunday'    => __( 'Sunday', 'foodbank-manager' ),
			);
	}

		/**
		 * Validate and normalize the provided day value.
		 *
		 * @param mixed $day Raw day value.
		 *
		 * @return string|WP_Error
		 */
	private static function sanitize_day( $day ) {
		if ( is_string( $day ) || is_int( $day ) ) {
				$value = sanitize_key( (string) $day );

			if ( isset( self::day_choices()[ $value ] ) ) {
				return $value;
			}
		}

			return new WP_Error( 'fbm_schedule_invalid_day', __( 'A valid schedule day must be selected.', 'foodbank-manager' ) );
	}

		/**
		 * Validate and normalize a schedule time.
		 *
		 * @param mixed  $time        Raw time value.
		 * @param string $error_code  Error code for failures.
		 * @param string $error_label Error label for failures.
		 *
		 * @return string|WP_Error
		 */
	private static function sanitize_time( $time, string $error_code, string $error_label ) {
		if ( is_string( $time ) || is_int( $time ) ) {
				$raw = trim( (string) $time );

			if ( '' !== $raw ) {
				$timezone = new DateTimeZone( 'UTC' );
				$formats  = array( '!H:i', '!H:i:s' );

				foreach ( $formats as $format ) {
						$date = DateTimeImmutable::createFromFormat( $format, $raw, $timezone );

					if ( $date instanceof DateTimeImmutable ) {
						return $date->format( 'H:i' );
					}
				}
			}
		}

			return new WP_Error( $error_code, $error_label );
	}

		/**
		 * Validate and normalize the timezone identifier.
		 *
		 * @param mixed $timezone Raw timezone value.
		 *
		 * @return string|WP_Error
		 */
	private static function sanitize_timezone( $timezone ) {
		if ( is_string( $timezone ) ) {
				$value = trim( $timezone );

			if ( '' !== $value ) {
				try {
						$tz = new DateTimeZone( $value );

						return $tz->getName();
				} catch ( Exception $exception ) {
						unset( $exception );
				}
			}
		}

			return new WP_Error( 'fbm_schedule_invalid_timezone', __( 'Timezone must be a valid identifier.', 'foodbank-manager' ) );
	}
}
