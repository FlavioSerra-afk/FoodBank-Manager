<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName

/**
 * Form submission controller entry file.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Http;

use FoodBankManager\Core\Options;
use FoodBankManager\Mail\Templates;
use FoodBankManager\Security\Crypto;

/**
 * Form submission controller.
 *
 * @package FoodBankManager
 */
final class FormSubmitController {
	/**
	 * Entry point for admin-post (public & logged-in).
	 *
	 * @since 0.1.x
	 * @return void
	 */
	public static function handle(): void {
		$method = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) );
		if ( 'POST' !== strtoupper( $method ) ) {
			$url = self::redirect_url(
				'',
				array(
					'fbm'  => 'error',
					'code' => 'method',
				)
			);
			wp_safe_redirect( esc_url_raw( $url ) );
			exit;
		}

		check_admin_referer( 'fbm_submit_form', '_fbm_nonce' );

		try {
			$request = self::parse_request();

			$errors = array();
			if ( '' === $request['first_name'] ) {
				$errors[] = 'first_name';
			}
			if ( '' === $request['last_name'] ) {
				$errors[] = 'last_name';
			}
			if ( '' === $request['email'] || ! is_email( $request['email'] ) ) {
				$errors[] = 'email';
			}
			if ( '' === $request['postcode'] ) {
				$errors[] = 'postcode';
			}
			if ( ! $request['consent'] ) {
				$errors[] = 'consent';
			}

			$provider = Options::get( 'forms.captcha_provider', 'off' );
			if ( 'off' !== $provider ) {
				$token_key = 'turnstile' === $provider ? 'cf-turnstile-response' : 'g-recaptcha-response';
				$token     = sanitize_text_field( wp_unslash( $_POST[ $token_key ] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above
				$secret    = Options::get( 'forms.captcha_secret' );
				if ( $secret && $token ) {
					$endpoint = 'turnstile' === $provider ? 'https://challenges.cloudflare.com/turnstile/v0/siteverify' : 'https://www.google.com/recaptcha/api/siteverify'; // phpcs:ignore Generic.Files.LineLength.TooLong
					$remote   = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
					$verify   = wp_remote_post(
						$endpoint,
						array(
							'body' => array(
								'secret'   => $secret,
								'response' => $token,
								'remoteip' => $remote,
							),
						)
					);
					$body     = wp_remote_retrieve_body( $verify );
					$ok       = false;
					if ( $body ) {
						$json = json_decode( $body, true );
						$ok   = is_array( $json ) && ! empty( $json['success'] );
					}
					if ( ! $ok ) {
						$errors[] = 'captcha';
					}
				}
			}

			if ( $errors ) {
				$url = self::redirect_url(
					$request['redirect'],
					array(
						'fbm'  => 'error',
						'code' => implode( ',', $errors ),
					)
				);
				wp_safe_redirect( esc_url_raw( $url ) );
				exit;
			}

			$policy  = array(
				'max_size'  => (int) Options::get( 'files.max_size', 2 * 1024 * 1024 ),
				'mimes'     => (array) Options::get( 'files.allowed_mimes', array( 'application/pdf', 'image/jpeg', 'image/png' ) ),
				'max_files' => (int) Options::get( 'files.max_files', 3 ),
			);
			$uploads = self::process_uploads( $_FILES, $policy ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above

			$data         = array(
				'first_name' => $request['first_name'],
				'last_name'  => $request['last_name'],
				'postcode'   => $request['postcode'],
				'notes'      => $request['notes'],
			);
			$pii_blob     = Crypto::encryptSensitive(
				array(
					'email' => $request['email'],
					'phone' => $request['phone'],
				)
			);
			$consent_text = self::consent_text();
			$consent_hash = hash( 'sha256', $consent_text );
			$ip           = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
			$ip_bin       = '' !== $ip ? @inet_pton( $ip ) : null; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$now          = current_time( 'mysql', true );

			global $wpdb;
			$wpdb->insert(
				$wpdb->prefix . 'fb_applications',
				array(
					'form_id'            => $request['form_id'],
					'status'             => 'new',
					'data_json'          => wp_json_encode( $data ),
					'pii_encrypted_blob' => $pii_blob,
					'consent_text_hash'  => $consent_hash,
					'consent_timestamp'  => $now,
					'consent_ip'         => $ip_bin,
					'created_at'         => $now,
					'updated_at'         => $now,
				),
				array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);
			$app_id = (int) $wpdb->insert_id;

			foreach ( $uploads as $meta ) {
				$wpdb->insert(
					$wpdb->prefix . 'fb_files',
					array(
						'application_id' => $app_id,
						'stored_path'    => $meta['path'],
						'original_name'  => $meta['name'],
						'mime'           => $meta['type'],
						'size_bytes'     => $meta['size'],
						'sha256'         => $meta['sha256'],
						'created_at'     => $now,
					),
					array( '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
				);
			}

			$summary_table = '<table><tbody>';
			foreach ( array(
				'first_name' => $request['first_name'],
				'last_name'  => $request['last_name'],
				'postcode'   => $request['postcode'],
				'notes'      => $request['notes'],
			) as $label => $val ) {
				$summary_table .= '<tr><th>' . esc_html( ucfirst( str_replace( '_', ' ', $label ) ) ) . '</th><td>' . esc_html( $val ) . '</td></tr>';
			}
			$summary_table .= '</tbody></table>';

			$tokens = array(
				'application_id' => $app_id,
				'first_name'     => $request['first_name'],
				'last_name'      => $request['last_name'],
				'created_at'     => $now,
				'summary_table'  => $summary_table,
				'reference'      => 'FBM-' . $app_id,
			);

			if ( class_exists( '\\FoodBankManager\\Attendance\\TokenService' ) ) {
				$token = \FoodBankManager\Attendance\TokenService::generate( $app_id );
				if ( class_exists( '\\Endroid\\QrCode\\QrCode' ) && class_exists( '\\Endroid\\QrCode\\Writer\\PngWriter' ) ) {
					$qr                    = new \Endroid\QrCode\QrCode( $token );
					$writer                = new \Endroid\QrCode\Writer\PngWriter();
					$tokens['qr_code_url'] = 'data:image/png;base64,' . base64_encode( $writer->write( $qr )->getString() ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- output data URI
				} else {
					$tokens['qr_code_url'] = $token;
				}
			}

			$admin_tokens                     = $tokens;
			$admin_tokens['application_link'] = admin_url( 'admin.php?page=fbm_application&id=' . $app_id );

			$headers    = array( 'Content-Type: text/html; charset=UTF-8' );
			$from_email = Options::get( 'emails.from_email' );
			if ( $from_email ) {
				$from_name = Options::get( 'emails.from_name' );
				$headers[] = 'From: ' . ( $from_name ? $from_name : $from_email ) . ' <' . $from_email . '>';
			}
			$reply = Options::get( 'emails.reply_to' );
			if ( $reply ) {
				$headers[] = 'Reply-To: ' . $reply;
			}

			$rendered = Templates::render( 'applicant_confirmation', $tokens );
			wp_mail( $request['email'], $rendered['subject'], $rendered['body_html'], $headers );

			$admin_rendered = Templates::render( 'admin_notification', $admin_tokens );
			$recipients     = Options::get( 'emails.admin_recipients' );
			$to_admin       = '' !== $recipients ? array_map( 'trim', explode( ',', $recipients ) ) : (string) get_option( 'admin_email' );
			wp_mail( $to_admin, $admin_rendered['subject'], $admin_rendered['body_html'], $headers );

			$url = self::redirect_url(
				$request['redirect'],
				array(
					'fbm'     => 'ok',
					'fbm_ref' => $app_id,
				)
			);
			wp_safe_redirect( esc_url_raw( $url ) );
			exit;
		} catch ( \Throwable $e ) {
			$expiry = defined( 'MINUTE_IN_SECONDS' ) ? (int) MINUTE_IN_SECONDS : 60;
			set_transient( 'fbm_form_error', 1, $expiry );
			$url = self::redirect_url( '', array( 'fbm' => 'error' ) );
			wp_safe_redirect( esc_url_raw( $url ) );
			exit;
		}
	}

	/**
	 * Parse and sanitize request data.
	 *
	 * @return array{form_id:int,redirect:string,first_name:string,last_name:string,email:string,phone:string,postcode:string,notes:string,consent:bool}
	 */
	private static function parse_request(): array {
		$raw = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked in handle()

		return array(
			'form_id'    => (int) sanitize_text_field( wp_unslash( $raw['form_id'] ?? '' ) ),
			'redirect'   => sanitize_text_field( wp_unslash( $raw['_redirect'] ?? '' ) ),
			'first_name' => sanitize_text_field( wp_unslash( $raw['first_name'] ?? '' ) ),
			'last_name'  => sanitize_text_field( wp_unslash( $raw['last_name'] ?? '' ) ),
			'email'      => sanitize_email( wp_unslash( $raw['email'] ?? '' ) ),
			'phone'      => sanitize_text_field( wp_unslash( $raw['phone'] ?? '' ) ),
			'postcode'   => sanitize_text_field( wp_unslash( $raw['postcode'] ?? '' ) ),
			'notes'      => sanitize_textarea_field( wp_unslash( $raw['notes'] ?? '' ) ),
			'consent'    => ! empty( $raw['consent'] ),
		);
	}

		/**
		 * Handle uploads with policy enforcement.
		 *
		 * @param array $files  Uploaded files.
		 * @param array $policy File policy.
		 * @return array<int,array{field:string,path:string,name:string,type:string,size:int,sha256:string}>
		 * @throws \RuntimeException On policy violation.
		 */
	private static function process_uploads( array $files, array $policy ): array {
		$handled   = array();
		$overrides = array(
			'test_form' => false,
		);
		$allowed   = $policy['mimes'];
		$max_size  = (int) $policy['max_size'];
		$max_files = (int) $policy['max_files'];

		$flat = array();
		foreach ( $files as $field => $payload ) {
			if ( ! is_array( $payload ) ) {
				continue;
			}
			if ( is_array( $payload['name'] ?? null ) ) {
				$count = count( $payload['name'] );
				for ( $i = 0; $i < $count; $i++ ) {
					$flat[] = array(
						'field'    => $field,
						'name'     => $payload['name'][ $i ],
						'type'     => $payload['type'][ $i ],
						'tmp_name' => $payload['tmp_name'][ $i ],
						'error'    => $payload['error'][ $i ],
						'size'     => $payload['size'][ $i ],
					);
				}
			} else {
				$payload['field'] = $field;
				$flat[]           = $payload;
			}
		}

		if ( count( $flat ) > $max_files ) {
			throw new \RuntimeException( 'file_count' );
		}

		foreach ( $flat as $file ) {
			if ( UPLOAD_ERR_OK !== (int) $file['error'] ) {
				throw new \RuntimeException( 'upload_error' );
			}
			if ( (int) $file['size'] > $max_size ) {
				throw new \RuntimeException( 'file_size' );
			}
			$type = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed );
			if ( empty( $type['type'] ) || ! in_array( $type['type'], $allowed, true ) ) {
				throw new \RuntimeException( 'file_type' );
			}
			$result = wp_handle_upload( $file, $overrides );
			if ( isset( $result['error'] ) ) {
				throw new \RuntimeException( 'upload_error' );
			}
			$handled[] = array(
				'field'  => $file['field'],
				'path'   => $result['file'],
				'name'   => wp_basename( $file['name'] ),
				'type'   => $result['type'],
				'size'   => (int) $file['size'],
				'sha256' => hash_file( 'sha256', $result['file'] ),
			);
		}

		return $handled;
	}

	/**
	 * Compute a safe redirect URL.
	 *
	 * @param string $fallback Fallback URL.
	 * @param array  $extra    Extra query args.
	 * @return string
	 */
	private static function redirect_url( string $fallback, array $extra = array() ): string {
			$ref = wp_get_referer();
			$url = wp_validate_redirect( $fallback, $ref ? $ref : home_url( '/' ) );
		if ( $extra ) {
				$url = add_query_arg( $extra, $url );
		}
			return $url;
	}

	/**
	 * Retrieve consent text.
	 *
	 * @return string
	 */
	private static function consent_text(): string {
		$text = (string) Options::get( 'forms.consent_text', __( 'I consent to the processing of my data as described.', 'foodbank-manager' ) );
		$text = sanitize_text_field( $text );
		return '' !== $text ? $text : __( 'I consent to the processing of my data as described.', 'foodbank-manager' );
	}
}
