<?php
// phpcs:ignoreFile
/**
 * Public REST API endpoints.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use FoodBankManager\Security\Helpers;
use FoodBankManager\Security\Crypto;
use FBM\Rest\ErrorHelper;

/**
 * Core REST API endpoints.
 */
class Api {

	/**
	 * Register public API routes.
	 */
	public static function register_routes(): void {
                register_rest_route(
                        'pcc-fb/v1',
                        '/applications',
                        array(
                                'methods'             => 'POST',
                                'callback'            => array( self::class, 'submit_application' ),
                                'permission_callback' => '__return_true',
                                'args'                => array(
                                        'form_id'     => \FoodBankManager\Rest\ArgHelper::id( false ),
                                        'first_name'  => array(
                                                'type'              => 'string',
                                                'required'          => true,
                                                'sanitize_callback' => array( Helpers::class, 'sanitize_text' ),
                                                'validate_callback' => static fn( $v ): bool => is_string( $v ) && $v !== '',
                                        ),
                                        'last_name'   => array(
                                                'type'              => 'string',
                                                'required'          => true,
                                                'sanitize_callback' => array( Helpers::class, 'sanitize_text' ),
                                                'validate_callback' => static fn( $v ): bool => is_string( $v ) && $v !== '',
                                        ),
                                        'email'       => \FoodBankManager\Rest\ArgHelper::email(),
                                        'postcode'    => array(
                                                'type'              => 'string',
                                                'required'          => true,
                                                'sanitize_callback' => array( Helpers::class, 'sanitize_text' ),
                                                'validate_callback' => static fn( $v ): bool => is_string( $v ) && $v !== '',
                                        ),
                                        'consent'     => array(
                                                'type'              => 'string',
                                                'required'          => true,
                                                'sanitize_callback' => array( Helpers::class, 'sanitize_text' ),
                                                'validate_callback' => static fn( $v ): bool => is_string( $v ) && $v !== '',
                                        ),
                                ),
                        )
                );
                ( new AttendanceController() )->register_routes();
                ( new \FBM\Rest\JobsController() )->register_routes();
                ( new \FBM\Rest\ThrottleController() )->register_routes();
	}

	/**
	 * Submit an application.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public static function submit_application( WP_REST_Request $request ): WP_REST_Response {
                if ( ! Helpers::verify_nonce( 'wp_rest', '_wpnonce' ) ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'foodbank-manager' ), array( 'status' => 401 ) )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }

                $form_id  = (int) $request->get_param( 'form_id' ); // @phpstan-ignore-line
                $first    = Helpers::sanitize_text( (string) $request->get_param( 'first_name' ) ); // @phpstan-ignore-line
                $last     = Helpers::sanitize_text( (string) $request->get_param( 'last_name' ) ); // @phpstan-ignore-line
                $email    = sanitize_email( (string) $request->get_param( 'email' ) ); // @phpstan-ignore-line
                $postcode = Helpers::sanitize_text( (string) $request->get_param( 'postcode' ) ); // @phpstan-ignore-line
                $consent  = Helpers::sanitize_text( (string) $request->get_param( 'consent' ) ); // @phpstan-ignore-line

                if ( $first === '' || $last === '' || $email === '' || $postcode === '' || $consent === '' ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error( 'invalid_param', __( 'Required fields missing', 'foodbank-manager' ), array( 'status' => 422 ) )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }

                                $file_ids = array();
                                $files    = $request->get_file_params(); // @phpstan-ignore-line
				$allowed  = (array) \FoodBankManager\Core\Options::get(
					'upload_mimes',
					array(
						'jpg' => 'image/jpeg',
						'png' => 'image/png',
						'pdf' => 'application/pdf',
					)
				);
				$max_size = (int) \FoodBankManager\Core\Options::get( 'upload_max_bytes', 2 * 1024 * 1024 );
				$offroot  = (string) \FoodBankManager\Core\Options::get( 'upload_offroot_path', '' );
		foreach ( $files as $file ) {
			if ( $file['error'] !== UPLOAD_ERR_OK || $file['size'] > $max_size ) {
						continue;
			}
				$type = wp_check_filetype( $file['name'], $allowed );
			if ( empty( $type['ext'] ) || empty( $type['type'] ) ) {
							continue;
			}
							$override = array(
								'test_form'                => false,
								'unique_filename_callback' => function ( $dir, $name, $ext ) {
										return wp_unique_filename( $dir, wp_generate_password( 12, false ) . $ext );
								},
							);
							if ( $offroot && is_dir( $offroot ) ) {
								$filter = static function ( $dirs ) use ( $offroot ) {
										$dirs['path']    = $offroot;
										$dirs['basedir'] = $offroot;
										$dirs['url']     = $offroot;
										$dirs['baseurl'] = $offroot;
										return $dirs;
								};
								add_filter( 'upload_dir', $filter );
							}
							$uploaded = wp_handle_upload( $file, $override );
							if ( isset( $filter ) ) {
								remove_filter( 'upload_dir', $filter );
							}
							if ( isset( $uploaded['file'] ) ) {
								global $wpdb;
								$sha = hash_file( 'sha256', $uploaded['file'] );
								$wpdb->insert(
									$wpdb->prefix . 'fb_files',
									array(
										'application_id' => 0,
										'stored_path'    => $uploaded['file'],
										'original_name'  => $file['name'],
										'mime'           => $uploaded['type'] ?? '',
										'size_bytes'     => (int) $file['size'],
										'sha256'         => $sha,
										'created_at'     => current_time( 'mysql' ),
									),
									array( '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
								);
									$file_ids[] = (int) $wpdb->insert_id;
							}
		}

				global $wpdb;
				$table = $wpdb->prefix . 'fb_applications';
		$data_json     = wp_json_encode(
			array(
				'first_name' => $first,
				'postcode'   => $postcode,
			)
		);
		$pii_blob      = Crypto::encryptSensitive(
			array(
				'last_name' => $last,
				'email'     => $email,
			)
		);
		$consent_hash  = hash( 'sha256', $consent );
		$ip            = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : '';
		$ip_bin        = $ip !== '' ? inet_pton( $ip ) : null;
		$now           = current_time( 'mysql' );
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
		if ( $file_ids ) {
			$wpdb->update(
				$wpdb->prefix . 'fb_files',
				array( 'application_id' => $app_id ),
				array( 'id' => $file_ids ),
				array( '%d' ),
				array( '%d' )
			);
		}

				self::send_applicant_email( $email, $app_id, $first );
				self::send_admin_email( $app_id, $first, $last, $email, $postcode );

				return new WP_REST_Response(
					array(
						'id'         => $app_id,
						'reference'  => (string) $app_id,
						'created_at' => $now,
						'file_ids'   => $file_ids,
					),
					201
				);
	}

	/**
	 * Send confirmation email to applicant.
	 *
	 * @param string $to         Recipient email.
	 * @param int    $app_id     Application ID.
	 * @param string $first_name Applicant first name.
	 *
	 * @return void
	 */
	private static function send_applicant_email( string $to, int $app_id, string $first_name ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Used in included template.
		/* translators: %d: application ID. */
		$subject         = sprintf( __( 'We received your application — Ref %d', 'foodbank-manager' ), $app_id );
			$token       = \FoodBankManager\Attendance\TokenService::generate( $app_id );
			$qr_code_url = '';
		if ( \FoodBankManager\Core\Options::get( 'email_qr_enabled', true ) ) {
			if ( class_exists( \Endroid\QrCode\QrCode::class ) && class_exists( \Endroid\QrCode\Writer\PngWriter::class ) ) {
				$qr          = new \Endroid\QrCode\QrCode( $token );
				$writer      = new \Endroid\QrCode\Writer\PngWriter();
				$qr_code_url = 'data:image/png;base64,' . base64_encode( $writer->write( $qr )->getString() ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Embedding QR code.
			} else {
					$qr_code_url = $token;
			}
				$qr_code_url = apply_filters( 'fbm_qr_code_url', $qr_code_url, $app_id, $token );
		}
			ob_start();
			include dirname( __DIR__, 2 ) . '/templates/emails/applicant-confirmation.php';
			$message = ob_get_clean();
			wp_mail( $to, $subject, $message, array( 'Content-Type: text/html; charset=UTF-8' ) );
	}

	/**
	 * Notify admin of new application.
	 *
	 * @param int    $app_id   Application ID.
	 * @param string $first    First name.
	 * @param string $last     Last name.
	 * @param string $email    Email address.
	 * @param string $postcode Postcode.
	 *
	 * @return void
	 */
	private static function send_admin_email( int $app_id, string $first, string $last, string $email, string $postcode ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Used in included template.
		$to = (string) get_option( 'admin_email' );
		/* translators: %d: application ID. */
		$subject = sprintf( __( 'New application received (Ref %d)', 'foodbank-manager' ), $app_id );
		ob_start();
		include dirname( __DIR__, 2 ) . '/templates/emails/admin-notification.php';
		$message = ob_get_clean();
		wp_mail( $to, $subject, $message, array( 'Content-Type: text/html; charset=UTF-8' ) );
	}
}
