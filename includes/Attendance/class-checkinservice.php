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

	public const STATUS_SUCCESS   = 'success';
	public const STATUS_DUPLICATE = 'duplicate';
	public const STATUS_ERROR     = 'error';

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
                $normalized_method = $this->normalize_method( $method );
                $now               = new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );

                if ( $this->repository->has_checked_in_for_date( $member_reference, $now ) ) {
                        return array(
                                'status'    => self::STATUS_DUPLICATE,
                                'message'   => esc_html__( 'Member already collected today.', 'foodbank-manager' ),
                                'member_ref' => $member_reference,
                                'time'      => $now->format( DATE_ATOM ),
                        );
                }

                $recorded = $this->repository->record( $member_reference, $normalized_method, $user_id, $now, $note );
                if ( ! $recorded ) {
                        return array(
                                'status'    => self::STATUS_ERROR,
                                'message'   => esc_html__( 'Unable to record collection. Please try again.', 'foodbank-manager' ),
                                'member_ref' => $member_reference,
                                'time'      => null,
                        );
                }

                return array(
                        'status'    => self::STATUS_SUCCESS,
                        'message'   => esc_html__( 'Collection recorded.', 'foodbank-manager' ),
                        'member_ref' => $member_reference,
                        'time'      => $now->format( DATE_ATOM ),
                );
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
