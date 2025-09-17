<?php
/**
 * Attendance check-in service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Attendance;

use DateTimeImmutable;
use FoodBankManager\Core\Schedule;
use DateTimeZone;
use function esc_html__;
use function explode;
use function get_transient;
use function in_array;
use function is_array;
use function md5;
use function sprintf;
use function sanitize_text_field;
use function set_transient;
use function strtolower;
use function ucfirst;
use function trim;
use function wp_unslash;

/**
 * Coordinates attendance check-in writes.
 */
final class CheckinService {
	public const STATUS_SUCCESS        = 'success';
    public const STATUS_DUPLICATE_DAY  = 'duplicate_day';
    public const STATUS_ERROR          = 'error';
    public const STATUS_OUT_OF_WINDOW  = 'out_of_window';
    public const STATUS_RECENT_WARNING = 'recent_warning';
    public const STATUS_THROTTLED      = 'throttled';

    private const WEEK_IN_SECONDS = 604800;
    private const THROTTLE_MAX_ATTEMPTS = 5;
    private const THROTTLE_WINDOW       = 60;

	/**
	 * Optional override for the current time, primarily used by tests.
	 *
	 * @var DateTimeImmutable|null
	 */
	private static ?DateTimeImmutable $current_time_override = null;

	/**
	 * Attendance repository dependency.
	 *
	 * @var AttendanceRepository
	 */
	private AttendanceRepository $repository;

	/**
	 * Schedule helper.
	 *
	 * @var Schedule
	 */
	private Schedule $schedule;

	/**
	 * Class constructor.
	 *
	 * @param AttendanceRepository $repository Attendance repository instance.
	 * @param Schedule|null        $schedule   Optional schedule helper.
	 */
	public function __construct( AttendanceRepository $repository, ?Schedule $schedule = null ) {
		$this->repository = $repository;
		$this->schedule   = $schedule ?? new Schedule();
	}

	/**
	 * Attempt to record a check-in.
	 *
	 * @param string      $member_reference Canonical member reference.
	 * @param string      $method           Raw method string.
	 * @param int|null    $user_id          Acting user ID.
	 * @param string|null $note             Optional note.
     * @param bool        $override         Whether an override was requested.
     * @param string|null $override_note    Justification for the override.
     * @param string|null $fingerprint      Request fingerprint for throttling (e.g. IP address).
	 *
         * @return array{
         *     status:string,
         *     message:string,
         *     member_ref:string,
         *     time:?string,
         *     duplicate?:bool,
         *     requires_override?:bool,
         *     window?:array{day:string,start:string,end:string,timezone:string}
         * }
         */
    public function record( string $member_reference, string $method, ?int $user_id, ?string $note = null, bool $override = false, ?string $override_note = null, ?string $fingerprint = null ): array {
		$utc_timezone    = new DateTimeZone( 'UTC' );
		$now_utc         = self::$current_time_override instanceof DateTimeImmutable
		? self::$current_time_override->setTimezone( $utc_timezone )
		: new DateTimeImmutable( 'now', $utc_timezone );
		$window          = $this->schedule->current_window();
		$window_timezone = new DateTimeZone( $window['timezone'] );
		$now_window      = $now_utc->setTimezone( $window_timezone );

		$window_start_parts = explode( ':', $window['start'] );
		$window_end_parts   = explode( ':', $window['end'] );
		$window_start       = $now_window->setTime( (int) $window_start_parts[0], (int) $window_start_parts[1], 0 );
		$window_end         = $now_window->setTime( (int) $window_end_parts[0], (int) $window_end_parts[1], 0 );
		$expected_day       = Schedule::day_to_index( $window['day'] );

        if ( $expected_day !== (int) $now_window->format( 'N' ) || $now_window < $window_start || $now_window > $window_end ) {
            $window_message = sprintf(
                /* translators: 1: Day of week, 2: Start time, 3: End time, 4: Timezone identifier. */
                esc_html__( 'Collections can only be recorded on %1$s between %2$s and %3$s (%4$s).', 'foodbank-manager' ),
                ucfirst( $window['day'] ),
                $window['start'],
                $window['end'],
                $window['timezone']
            );

            return array(
                'status'     => self::STATUS_OUT_OF_WINDOW,
                'message'    => $window_message,
                'member_ref' => $member_reference,
                'time'       => null,
                'window'     => $window,
            );
        }

        $throttle_key = $this->build_throttle_key( $user_id, $fingerprint );

        if ( null !== $throttle_key && ! $this->register_attempt( $throttle_key ) ) {
            return array(
                'status'     => self::STATUS_THROTTLED,
                'message'    => esc_html__( 'Please wait a moment before trying again.', 'foodbank-manager' ),
                'member_ref' => $member_reference,
                'time'       => null,
            );
        }

        $normalized_method = $this->normalize_method( $method );

		if ( $this->repository->has_checked_in_for_date( $member_reference, $now_utc ) ) {
			return array(
				'status'     => self::STATUS_DUPLICATE_DAY,
				'message'    => esc_html__( 'Member already collected today.', 'foodbank-manager' ),
				'member_ref' => $member_reference,
				'time'       => $now_utc->format( DATE_ATOM ),
			);
		}

				$previous_collection = $this->repository->latest_for_member( $member_reference );

				$override_note   = $override && is_string( $override_note ) ? $override_note : null;
				$should_override = $override && null !== $user_id && null !== $override_note && '' !== $override_note;

		if ( $previous_collection instanceof DateTimeImmutable ) {
				$seconds_since_last = $now_utc->getTimestamp() - $previous_collection->getTimestamp();

                        if ( $seconds_since_last >= 0 && $seconds_since_last < self::WEEK_IN_SECONDS && ! $should_override ) {
                                                return array(
                                                        'status'     => self::STATUS_RECENT_WARNING,
                                                        'message'    => esc_html__( 'Member collected less than a week ago. Only managers can continue with a justified override.', 'foodbank-manager' ),
                                                        'member_ref' => $member_reference,
                                                        'time'       => $previous_collection->format( DATE_ATOM ),
                                                );
			}
		}

				$note_to_store = $note;
		if ( $should_override && ( null === $note_to_store || '' === $note_to_store ) ) {
				$note_to_store = $override_note;
		}

				$attendance_id = $this->repository->record( $member_reference, $normalized_method, $user_id, $now_utc, $note_to_store );
		if ( null === $attendance_id ) {
			return array(
				'status'     => self::STATUS_ERROR,
				'message'    => esc_html__( 'Unable to record collection. Please try again.', 'foodbank-manager' ),
				'member_ref' => $member_reference,
				'time'       => null,
			);
		}

		if ( $should_override ) {
			if ( ! $this->repository->record_override_audit( $attendance_id, $member_reference, $user_id, $now_utc, $override_note ) ) {
				$this->repository->delete_attendance_record( $attendance_id );

				return array(
					'status'     => self::STATUS_ERROR,
					'message'    => esc_html__( 'Unable to record override. Please try again.', 'foodbank-manager' ),
					'member_ref' => $member_reference,
					'time'       => null,
				);
			}
		}

		$status  = self::STATUS_SUCCESS;
		$message = esc_html__( 'Collection recorded.', 'foodbank-manager' );

		if ( $previous_collection instanceof DateTimeImmutable ) {
				$seconds_since_last = $now_utc->getTimestamp() - $previous_collection->getTimestamp();
                        if ( $seconds_since_last >= 0 && $seconds_since_last < self::WEEK_IN_SECONDS && ! $should_override ) {
                                                $status  = self::STATUS_RECENT_WARNING;
                                                $message = esc_html__( 'Member collected less than a week ago. Only managers can continue with a justified override.', 'foodbank-manager' );
                        }
                }

        return array(
                'status'     => $status,
                'message'    => $message,
                'member_ref' => $member_reference,
                'time'       => $now_utc->format( DATE_ATOM ),
        );
    }

    /**
     * Register an attempt for a throttle key and determine if it is allowed.
     *
     * @param string $key Unique throttle key.
     */
    private function register_attempt( string $key ): bool {
        $record = get_transient( $key );
        $now    = time();

        if ( ! is_array( $record ) || ! isset( $record['attempts'], $record['first_at'] ) ) {
            set_transient(
                $key,
                array(
                    'attempts' => 1,
                    'first_at' => $now,
                ),
                self::THROTTLE_WINDOW
            );

            return true;
        }

        $attempts = (int) $record['attempts'];
        $first_at = (int) $record['first_at'];

        if ( $now - $first_at >= self::THROTTLE_WINDOW ) {
            set_transient(
                $key,
                array(
                    'attempts' => 1,
                    'first_at' => $now,
                ),
                self::THROTTLE_WINDOW
            );

            return true;
        }

        if ( $attempts >= self::THROTTLE_MAX_ATTEMPTS ) {
            set_transient(
                $key,
                array(
                    'attempts' => $attempts,
                    'first_at' => $first_at,
                ),
                self::THROTTLE_WINDOW
            );

            return false;
        }

        set_transient(
            $key,
            array(
                'attempts' => $attempts + 1,
                'first_at' => $first_at,
            ),
            self::THROTTLE_WINDOW
        );

        return true;
    }

    /**
     * Build a throttle key based on the acting user or request fingerprint.
     *
     * @param int|null    $user_id     Acting user identifier.
     * @param string|null $fingerprint Request fingerprint.
     */
    private function build_throttle_key( ?int $user_id, ?string $fingerprint ): ?string {
        if ( null !== $user_id && $user_id > 0 ) {
            return 'fbm_checkin_throttle_user_' . $user_id;
        }

        $fingerprint = $this->resolve_fingerprint( $fingerprint );

        if ( null === $fingerprint ) {
            return null;
        }

        return 'fbm_checkin_throttle_ip_' . md5( $fingerprint );
    }

    /**
     * Resolve the most appropriate fingerprint for the current request.
     *
     * @param string|null $fingerprint Provided fingerprint.
     */
    private function resolve_fingerprint( ?string $fingerprint ): ?string {
        if ( is_string( $fingerprint ) ) {
            $fingerprint = trim( $fingerprint );
        }

        if ( is_string( $fingerprint ) && '' !== $fingerprint ) {
            return $fingerprint;
        }

        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $remote_address = sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) );

            if ( '' !== $remote_address ) {
                return $remote_address;
            }
        }

        return null;
    }

    /**
     * Override the current time for deterministic testing.
     *
     * @internal
     *
     * @param DateTimeImmutable|null $override Custom "now" instance.
	 */
	public static function set_current_time_override( ?DateTimeImmutable $override ): void {
		self::$current_time_override = $override;
	}

	/**
	 * Ensure the method is one of the allowed values.
	 *
	 * @param string $method Raw method string.
	 */
	private function normalize_method( string $method ): string {
		$method   = strtolower( $method );
		$allowed  = array( 'qr', 'manual' );
		$fallback = 'qr';

		return in_array( $method, $allowed, true ) ? $method : $fallback;
	}
}
