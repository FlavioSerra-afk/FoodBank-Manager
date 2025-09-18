<?php
/**
 * Staff dashboard shortcode handler.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Attendance\CheckinService;
use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Schedule;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Token\Token;
use FoodBankManager\Token\TokenRepository;
use function add_shortcode;
use function current_user_can;
use function esc_html__;
use function function_exists;
use function is_readable;
use function is_user_logged_in;
use function is_string;
use function ob_get_clean;
use function ob_start;
use function get_current_user_id;
use function status_header;
use function strtoupper;
use function wp_unslash;
use function wp_verify_nonce;

/**
 * Renders the staff dashboard shortcode.
 */
final class StaffDashboard {

        private const SHORTCODE = 'fbm_staff_dashboard';
        private const MANUAL_NONCE_ACTION = 'fbm_staff_manual_entry';
        private const MANUAL_NONCE_FIELD  = 'fbm_staff_manual_nonce';

        /**
         * Register the shortcode with WordPress.
         */
        public static function register(): void {
		add_shortcode( self::SHORTCODE, array( self::class, 'render' ) );
	}

	/**
	 * Render the staff dashboard view.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes.
	 */
	public static function render( array $atts = array() ): string {
			unset( $atts );

                if ( ! is_user_logged_in() || ! current_user_can( 'fbm_view' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
                        if ( function_exists( 'status_header' ) ) {
                                status_header( 403 );
                        }

                        return '<div class="fbm-staff-dashboard fbm-staff-dashboard--denied">'
				. esc_html__( 'Staff dashboard is available to authorised team members only.', 'foodbank-manager' )
				. '</div>';
		}

                Assets::mark_staff_dashboard();

                $manual_entry = self::maybe_handle_manual_entry();

                ob_start();
                $template = FBM_PATH . 'templates/public/staff-dashboard.php';
                if ( is_readable( $template ) ) {
                        include $template;
		}

			$output = ob_get_clean();

                return is_string( $output ) ? $output : '';
        }

        /**
         * Handle manual code submissions when JavaScript is unavailable.
         *
         * @return array{status:string,message:string}|null
         */
        private static function maybe_handle_manual_entry(): ?array {
                if ( ! current_user_can( 'fbm_checkin' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
                        return null;
                }

                if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
                        return null;
                }

                $nonce = $_POST[ self::MANUAL_NONCE_FIELD ] ?? null; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked below.
                if ( ! is_string( $nonce ) ) {
                        return self::manual_response( 'invalid', esc_html__( 'Security check failed. Please try again.', 'foodbank-manager' ) );
                }

                $nonce = wp_unslash( $nonce );

                if ( ! wp_verify_nonce( $nonce, self::MANUAL_NONCE_ACTION ) ) {
                        return self::manual_response( 'invalid', esc_html__( 'Security check failed. Please try again.', 'foodbank-manager' ) );
                }

                $raw_code = $_POST['code'] ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Intentional manual submission handler.
                if ( ! is_string( $raw_code ) ) {
                        return self::manual_response( 'invalid', esc_html__( 'Enter a valid collection code.', 'foodbank-manager' ) );
                }

                $raw_code   = wp_unslash( $raw_code );
                $canonical  = Token::canonicalize( $raw_code );
                $invalid    = esc_html__( 'Enter a valid collection code.', 'foodbank-manager' );
                $manual_ref = null;

                if ( null === $canonical ) {
                        return self::manual_response( 'invalid', $invalid );
                }

                global $wpdb;

                $token_repository = new TokenRepository( $wpdb );
                $token            = new Token( $token_repository );
                $verification     = $token->verify( $canonical );

                if ( ! $verification['ok'] ) {
                        if ( 'revoked' === $verification['reason'] ) {
                                return self::manual_response( 'revoked', esc_html__( 'This code has been revoked.', 'foodbank-manager' ) );
                        }

                        return self::manual_response( 'invalid', $invalid );
                }

                $member_id = $verification['member_id'];

                if ( null === $member_id ) {
                        return self::manual_response( 'invalid', $invalid );
                }

                $members_repository = new MembersRepository( $wpdb );
                $member             = $members_repository->find( $member_id );

                if ( null === $member ) {
                        return self::manual_response( 'invalid', $invalid );
                }

                if ( MembersRepository::STATUS_ACTIVE !== $member['status'] ) {
                        return self::manual_response( 'invalid', esc_html__( 'This member is not currently active.', 'foodbank-manager' ) );
                }

                if ( empty( $member['member_reference'] ) ) {
                        return self::manual_response( 'invalid', $invalid );
                }

                $manual_ref            = (string) $member['member_reference'];
                $attendance_repository = new AttendanceRepository( $wpdb );
                $schedule              = new Schedule();
                $service               = new CheckinService( $attendance_repository, $schedule );

                $result = $service->record( $manual_ref, 'manual', get_current_user_id() );
                $status = (string) $result['status'];

                if ( CheckinService::STATUS_SUCCESS === $status ) {
                        return self::manual_response( 'success', (string) $result['message'] );
                }

                if ( CheckinService::STATUS_DUPLICATE_DAY === $status ) {
                        return self::manual_response( 'already', (string) $result['message'] );
                }

                if ( CheckinService::STATUS_THROTTLED === $status ) {
                        return self::manual_response( 'throttled', (string) $result['message'] );
                }

                $message = (string) $result['message'];

                if ( '' !== $message ) {
                        return self::manual_response( 'invalid', $message );
                }

                return self::manual_response( 'invalid', $invalid );
        }

        /**
         * Compose a manual entry response payload.
         *
         * @param string $status  Response status identifier.
         * @param string $message Localized response message.
         *
         * @return array{status:string,message:string}
         */
        private static function manual_response( string $status, string $message ): array {
                return array(
                        'status'  => $status,
                        'message' => $message,
                );
        }
}
