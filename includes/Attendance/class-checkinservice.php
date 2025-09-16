<?php
/**
 * Check-in domain service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Attendance;

use DateTimeImmutable;
use DateTimeZone;
use function esc_html__;
use function in_array;
use function strtolower;

/**
 * Coordinates attendance check-in writes.
 */
final class CheckinService {

	public const STATUS_SUCCESS       = 'success';
	public const STATUS_DUPLICATE_DAY = 'duplicate_day';
	public const STATUS_ERROR         = 'error';
	public const STATUS_OUT_OF_WINDOW = 'out_of_window';

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
		 * Class constructor.
		 *
		 * @param AttendanceRepository $repository Attendance repository instance.
		 */
	public function __construct( AttendanceRepository $repository ) {
			$this->repository = $repository;
	}

	/**
	 * Attempt to record a check-in.
	 *
	 * @param string      $member_reference Canonical member reference.
	 * @param string      $method           Raw method string.
	 * @param int|null    $user_id          Acting user ID.
	 * @param string|null $note             Optional note.
	 *
	 * @return array{status:string,message:string,member_ref:string,time:?string}
	 */
	public function record( string $member_reference, string $method, ?int $user_id, ?string $note = null ): array {
		$utc_timezone    = new DateTimeZone( 'UTC' );
		$now_utc         = self::$current_time_override instanceof DateTimeImmutable
			? self::$current_time_override->setTimezone( $utc_timezone )
			: new DateTimeImmutable( 'now', $utc_timezone );
		$london_timezone = new DateTimeZone( 'Europe/London' );
		$now_london      = $now_utc->setTimezone( $london_timezone );

		$window_start = $now_london->setTime( 11, 0, 0 );
		$window_end   = $now_london->setTime( 14, 30, 0 );

		if ( '4' !== $now_london->format( 'N' ) || $now_london < $window_start || $now_london > $window_end ) {
			return array(
				'status'     => self::STATUS_OUT_OF_WINDOW,
				'message'    => esc_html__( 'Collections are only available on Thursdays between 11:00 and 14:30.', 'foodbank-manager' ),
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

		$recorded = $this->repository->record( $member_reference, $normalized_method, $user_id, $now_utc, $note );
		if ( ! $recorded ) {
			return array(
				'status'     => self::STATUS_ERROR,
				'message'    => esc_html__( 'Unable to record collection. Please try again.', 'foodbank-manager' ),
				'member_ref' => $member_reference,
				'time'       => null,
			);
		}

		return array(
			'status'     => self::STATUS_SUCCESS,
			'message'    => esc_html__( 'Collection recorded.', 'foodbank-manager' ),
			'member_ref' => $member_reference,
			'time'       => $now_utc->format( DATE_ATOM ),
		);
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
