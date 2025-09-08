<?php
/**
 * Form submission controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Http;

use FoodBankManager\Forms\PresetsRepo;
use FoodBankManager\Forms\FieldTypes;
use FoodBankManager\Forms\Schema;
use FoodBankManager\Database\ApplicationsRepo;
use FoodBankManager\Mail\Templates;
use FBM\Mail\LogRepo;
use FoodBankManager\Core\Options;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_file_name;
use function filter_input;
use function filter_input_array;
use function wp_unslash;

/**
 * Minimal form submission controller.
 */
final class FormSubmitController {
	/**
	 * Handle submission.
	 *
	 * @return void
	 */
	public static function handle(): void {
				check_admin_referer( 'fbm_submit_form', '_fbm_nonce' );
				$post_raw = filter_input_array( INPUT_POST, FILTER_UNSAFE_RAW ) ?? array();
				$post     = wp_unslash( $post_raw );
				$slug     = sanitize_key( (string) ( $post['preset'] ?? '' ) );
				$schema   = PresetsRepo::get_by_slug( $slug );
		if ( ! $schema ) {
				wp_die( esc_html__( 'Invalid form.', 'foodbank-manager' ) );
		}
		try {
				$schema    = Schema::normalize( $schema );
				$sanitized = self::validate_against_schema( $schema, $post );
				$files     = self::process_uploads( $schema );
		} catch ( \InvalidArgumentException | \RuntimeException $e ) {
				wp_die( esc_html( $e->getMessage() ) );
		}

				$data    = array();
				$pii     = array();
				$consent = array();
		foreach ( $schema['fields'] as $field ) {
				$id   = (string) $field['id'];
				$type = (string) $field['type'];
			if ( 'consent' === $type ) {
						$consent = array(
							'text_hash' => hash( 'sha256', (string) ( $field['label'] ?? '' ) ),
							'timestamp' => gmdate( 'Y-m-d H:i:s' ),
							'ip'        => self::hash_ip(),
						);
						continue;
			}
			if ( 'file' === $type ) {
							continue;
			}
							$value = (string) ( $sanitized[ $id ] ?? '' );
			if ( '' === $value ) {
				continue;
			}
			if ( in_array( $type, array( 'email', 'tel' ), true ) ) {
				$pii[ $id ] = $value;
			} else {
				$data[ $id ] = $value;
			}
		}

				$app_id = ApplicationsRepo::insert( 0, $data, $pii, $consent, $files );

		if ( ! empty( $pii['email'] ) ) {
				$vars   = array(
					'first_name'       => $data['first'] ?? '',
					'last_name'        => $pii['last'] ?? '',
					'application_id'   => (string) $app_id,
					'site_name'        => (string) get_option( 'blogname' ),
					'appointment_time' => $data['appointment_time'] ?? '',
				);
				$tpl    = Templates::render( 'applicant_confirmation', $vars );
				$sent   = wp_mail( $pii['email'], $tpl['subject'], $tpl['body_html'], array( 'Content-Type: text/html; charset=UTF-8' ) );
				$status = $sent ? 'succeeded' : 'failed';
				LogRepo::insert( $app_id, $pii['email'], $tpl['subject'], hash( 'sha256', $tpl['body_html'] ), $status, $sent ? '' : 'send_failed' );
		}

				$summary   = self::build_summary( $schema['fields'], $sanitized );
				$reference = (string) $app_id;
				include FBM_PATH . 'templates/public/form-success.php';
	}

	/**
	 * Validate data against schema and return sanitized array.
	 *
	 * @param array<string,mixed> $schema Schema.
	 * @param array<string,mixed> $post   Raw post.
	 * @return array<string,mixed>
	 * @throws \RuntimeException On validation failure.
	 */
	public static function validate_against_schema( array $schema, array $post ): array {
			$types   = FieldTypes::all();
			$data    = array();
			$allowed = array();
		foreach ( $schema['fields'] as $field ) {
				$id       = (string) $field['id'];
				$type     = (string) $field['type'];
				$required = ! empty( $field['required'] );
				$def      = $types[ $type ] ?? null;
			if ( ! $def ) {
				throw new \RuntimeException( 'field' );
			}
			if ( 'file' === $type ) {
					$allowed[] = $id;
					continue;
			}
				$raw       = $post[ $id ] ?? '';
				$sanitize  = $def['sanitize'];
				$value     = is_callable( $sanitize ) ? (string) call_user_func( $sanitize, $raw ) : (string) $raw;
				$validator = $def['validate'] ?? null;
			if ( $required && '' === $value ) {
					throw new \RuntimeException( 'required' );
			}
			if ( $validator && ! call_user_func( $validator, $value, $field ) ) {
					throw new \RuntimeException( 'invalid' );
			}
				$data[ $id ] = $value;
				$allowed[]   = $id;
		}
		foreach ( $post as $key => $v ) {
			if (
						! in_array( (string) $key, (array) $allowed, true )
						&& ! in_array( (string) $key, array( '_fbm_nonce', 'preset', 'action', 'captcha' ), true )
				) {
						throw new \RuntimeException( 'unknown' );
			}
		}
		if ( ! empty( $schema['meta']['captcha'] ) ) {
			$token = sanitize_text_field( (string) ( $post['captcha'] ?? '' ) );
			if ( '' === $token ) {
				throw new \RuntimeException( 'captcha' );
			}
		}
			return $data;
	}

		/**
		 * Handle uploaded files.
		 *
		 * @param array<string,mixed> $schema Schema.
		 * @return array<int,array{stored_path:string,original_name:string,mime:string,size:int,sha256:string}>
		 * @throws \RuntimeException When file validation fails.
		 */
	private static function process_uploads( array $schema ): array {
			$out  = array();
		$allowed  = (array) Options::get(
			'upload_mimes',
			array(
				'jpg' => 'image/jpeg',
				'png' => 'image/png',
				'pdf' => 'application/pdf',
			)
		);
		$max_size = (int) Options::get( 'upload_max_bytes', 2 * 1024 * 1024 );
		$file_ids = array();
		foreach ( $schema['fields'] as $field ) {
			if ( 'file' !== (string) $field['type'] ) {
						continue;
			}
				$id         = (string) $field['id'];
				$file_ids[] = $id;
				$file       = $_FILES[ $id ] ?? null; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) $file['error'] ) {
				if ( ! empty( $field['required'] ) ) {
						throw new \RuntimeException( 'file' );
				}
							continue;
			}
			if ( (int) $file['size'] > $max_size ) {
					throw new \RuntimeException( 'file' );
			}
							$ext  = strtolower( (string) pathinfo( (string) $file['name'], PATHINFO_EXTENSION ) );
							$mime = $allowed[ $ext ] ?? '';
			if ( '' === $mime || $mime !== (string) $file['type'] ) {
				throw new \RuntimeException( 'file' );
			}
							$dir    = sys_get_temp_dir();
							$name   = bin2hex( random_bytes( 16 ) ) . '.' . $ext;
							$target = $dir . '/' . $name;
			if ( ! move_uploaded_file( (string) $file['tmp_name'], $target ) ) {
				if ( ! rename( (string) $file['tmp_name'], $target ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
					throw new \RuntimeException( 'file' );
				}
			}
							$out[] = array(
								'stored_path'   => $target,
								'original_name' => sanitize_file_name( (string) $file['name'] ),
								'mime'          => $mime,
								'size'          => (int) $file['size'],
								'sha256'        => hash_file( 'sha256', $target ),
							);
		}
		foreach ( $_FILES as $key => $v ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! in_array( (string) $key, $file_ids, true ) ) {
					throw new \RuntimeException( 'unknown' );
			}
		}
		return $out;
	}

		/**
		 * Build masked summary for confirmation page.
		 *
		 * @param array<int,array<string,mixed>> $fields Fields.
		 * @param array<string,string>           $data   Sanitized data.
		 * @return array<string,string>
		 */
	private static function build_summary( array $fields, array $data ): array {
			$summary = array();
		foreach ( $fields as $field ) {
				$id    = (string) $field['id'];
				$type  = (string) $field['type'];
				$label = (string) $field['label'];
			if ( in_array( $type, array( 'file', 'consent' ), true ) ) {
				continue;
			}
				$value = (string) ( $data[ $id ] ?? '' );
			if ( '' === $value ) {
					continue;
			}
			if ( 'email' === $type ) {
					$summary[ $label ] = self::mask_email( $value );
			} elseif ( 'tel' === $type ) {
					$summary[ $label ] = self::mask_tel( $value );
			} else {
					$summary[ $label ] = $value;
			}
		}
			return $summary;
	}

		/**
		 * Mask an email address.
		 *
		 * @param string $email Email address.
		 * @return string Masked email.
		 */
	private static function mask_email( string $email ): string {
			$parts = explode( '@', $email );
		if ( 2 !== count( $parts ) ) {
				return $email;
		}
			$local = $parts[0];
			$local = substr( $local, 0, 1 ) . str_repeat( '*', max( strlen( $local ) - 1, 0 ) );
			return $local . '@' . $parts[1];
	}

		/**
		 * Mask a telephone number.
		 *
		 * @param string $tel Telephone number.
		 * @return string Masked telephone number.
		 */
	private static function mask_tel( string $tel ): string {
			$len = strlen( $tel );
		if ( $len <= 4 ) {
				return str_repeat( '*', $len );
		}
			return str_repeat( '*', $len - 4 ) . substr( $tel, -4 );
	}

		/**
		 * Hash IP address to 16-byte binary string.
		 *
		 * @return string Hashed IP.
		 */
	private static function hash_ip(): string {
			$ip = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
		if ( ! $ip ) {
				return '';
		}
			$hash = substr( hash( 'sha256', (string) $ip ), 0, 32 );
			return pack( 'H*', $hash );
	}
}
