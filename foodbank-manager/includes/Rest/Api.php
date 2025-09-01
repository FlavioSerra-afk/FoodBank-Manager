<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Rest;

use WP_REST_Request;
use WP_REST_Response;
use FoodBankManager\Security\Helpers;
use FoodBankManager\Security\Crypto;

class Api {

	public static function register_routes(): void {
		register_rest_route(
			'pcc-fb/v1',
			'/applications',
			array(
				'methods'             => 'POST',
				'callback'            => array( self::class, 'submit_application' ),
				'permission_callback' => '__return_true',
			)
		);
		( new AttendanceController() )->register_routes();
	}

	public static function submit_application( WP_REST_Request $request ): WP_REST_Response {
		if ( ! Helpers::verify_nonce( 'wp_rest', '_wpnonce' ) ) {
			return new WP_REST_Response(
				array(
					'error' => array(
						'code'    => 'fbm_invalid_nonce',
						'message' => __( 'Invalid nonce', 'foodbank-manager' ),
					),
				),
				403
			);
		}

		$form_id  = (int) $request->get_param( 'form_id' );
		$first    = Helpers::sanitize_text( (string) $request->get_param( 'first_name' ) );
		$last     = Helpers::sanitize_text( (string) $request->get_param( 'last_name' ) );
		$email    = sanitize_email( (string) $request->get_param( 'email' ) );
		$postcode = Helpers::sanitize_text( (string) $request->get_param( 'postcode' ) );
		$consent  = Helpers::sanitize_text( (string) $request->get_param( 'consent' ) );

		if ( $first === '' || $last === '' || $email === '' || $postcode === '' || $consent === '' ) {
			return new WP_REST_Response(
				array(
					'error' => array(
						'code'    => 'fbm_missing_fields',
						'message' => __( 'Required fields missing', 'foodbank-manager' ),
					),
				),
				400
			);
		}

		// TODO(PRD §5.3): validate files via wp_handle_upload().

		global $wpdb;
		$table        = $wpdb->prefix . 'fb_applications';
		$data_json    = wp_json_encode(
			array(
				'first_name' => $first,
				'postcode'   => $postcode,
			)
		);
		$pii_blob     = Crypto::encryptSensitive(
			array(
				'last_name' => $last,
				'email'     => $email,
			)
		);
		$consent_hash = hash( 'sha256', $consent );
		$ip           = $_SERVER['REMOTE_ADDR'] ?? '';
		$ip_bin       = $ip !== '' ? @inet_pton( $ip ) : null; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$now          = current_time( 'mysql' );
		$wpdb->insert(
			$table,
			array(
				'form_id'            => $form_id,
				'data_json'          => $data_json,
				'pii_encrypted_blob' => $pii_blob,
				'consent_text_hash'  => $consent_hash,
				'consent_timestamp'  => $now,
				'consent_ip'         => $ip_bin,
				'created_at'         => $now,
				'updated_at'         => $now,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		$app_id = (int) $wpdb->insert_id;

		self::send_applicant_email( $email, $app_id, $first );
		self::send_admin_email( $app_id, $first, $last, $email, $postcode );

		return new WP_REST_Response(
			array(
				'application_id' => $app_id,
			),
			201
		);
	}

	private static function send_applicant_email( string $to, int $app_id, string $first_name ): void {
		$subject = sprintf( __( 'We received your application — Ref %d', 'foodbank-manager' ), $app_id );
		ob_start();
		include dirname( __DIR__, 2 ) . '/templates/emails/applicant-confirmation.php';
		$message = ob_get_clean();
		wp_mail( $to, $subject, $message, array( 'Content-Type: text/html; charset=UTF-8' ) );
	}

	private static function send_admin_email( int $app_id, string $first, string $last, string $email, string $postcode ): void {
		$to      = (string) get_option( 'admin_email' );
		$subject = sprintf( __( 'New application received (Ref %d)', 'foodbank-manager' ), $app_id );
		ob_start();
		include dirname( __DIR__, 2 ) . '/templates/emails/admin-notification.php';
		$message = ob_get_clean();
		wp_mail( $to, $subject, $message, array( 'Content-Type: text/html; charset=UTF-8' ) );
	}
}
