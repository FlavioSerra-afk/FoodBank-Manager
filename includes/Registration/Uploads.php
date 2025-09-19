<?php // phpcs:ignoreFile WordPress.Files.FileName
/**
 * Registration upload helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration;

use function __;
use function array_key_exists;
use function array_unique;
use function array_values;
use function function_exists;
use function in_array;
use function is_array;
use function is_string;
use function sanitize_file_name;
use function strtolower;
use function trim;
use function update_post_meta;
use function wp_check_filetype_and_ext;
use function wp_delete_attachment;
use function wp_delete_file;
use function wp_generate_attachment_metadata;
use function wp_handle_upload;
use function wp_insert_attachment;
use function wp_update_attachment_metadata;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

/**
 * Coordinates persistence of uploaded registration files.
 */
final class Uploads {
	private const META_MEMBER_REFERENCE = 'fbm_member_reference';

	private const DEFAULT_MAX_SIZE = 5242880; // 5MB.

	private const DEFAULT_ALLOWED_MIMES = array(
		'application/pdf',
		'image/jpeg',
		'image/png',
	);

	private const MIME_EXTENSION_MAP = array(
		'application/pdf' => array( 'pdf' ),
		'image/jpeg'      => array( 'jpg', 'jpeg' ),
		'image/png'       => array( 'png' ),
	);

		/**
		 * Normalize upload settings with defaults.
		 *
		 * @param array<string,mixed> $settings Raw settings.
		 *
		 * @return array{max_size:int,allowed_mime_types:array<int,string>}
		 */
	public static function normalize_settings( array $settings ): array {
			$max_size = isset( $settings['max_size'] ) ? (int) $settings['max_size'] : self::DEFAULT_MAX_SIZE;

		if ( $max_size <= 0 ) {
				$max_size = self::DEFAULT_MAX_SIZE;
		}

			$allowed = array();

		if ( isset( $settings['allowed_mime_types'] ) && is_array( $settings['allowed_mime_types'] ) ) {
			foreach ( $settings['allowed_mime_types'] as $mime ) {
				if ( is_string( $mime ) && '' !== trim( $mime ) ) {
					$allowed[] = strtolower( trim( $mime ) );
				}
			}
		}

		if ( empty( $allowed ) ) {
				$allowed = self::DEFAULT_ALLOWED_MIMES;
		}

			$allowed = array_values( array_unique( $allowed ) );

			return array(
				'max_size'           => $max_size,
				'allowed_mime_types' => $allowed,
			);
	}

		/**
		 * Handle an uploaded file for the provided field.
		 *
		 * @param string              $field_name Field identifier.
		 * @param array<string,mixed> $file       File payload from $_FILES.
		 * @param array<string,mixed> $settings   Upload settings.
		 *
		 * @return array{status:string,attachment_id:int,url:string,type:string,path:string,error?:string}
		 */
	public static function process( string $field_name, array $file, array $settings ): array {
			$normalized = self::normalize_settings( $settings );

		if ( empty( $file ) || ! array_key_exists( 'error', $file ) ) {
				return array(
					'status'        => 'empty',
					'attachment_id' => 0,
					'url'           => '',
					'type'          => '',
					'path'          => '',
				);
		}

		if ( isset( $file['name'] ) && is_array( $file['name'] ) ) {
				return array(
					'status'        => 'error',
					'attachment_id' => 0,
					'url'           => '',
					'type'          => '',
					'path'          => '',
					'error'         => __( 'Only one file may be uploaded.', 'foodbank-manager' ),
				);
		}

		if ( isset( $file['tmp_name'] ) && is_array( $file['tmp_name'] ) ) {
				return array(
					'status'        => 'error',
					'attachment_id' => 0,
					'url'           => '',
					'type'          => '',
					'path'          => '',
					'error'         => __( 'Only one file may be uploaded.', 'foodbank-manager' ),
				);
		}

		if ( isset( $file['name'] ) && is_string( $file['name'] ) ) {
				$file['name'] = sanitize_file_name( $file['name'] );
		}

			$error = (int) $file['error'];

		if ( UPLOAD_ERR_NO_FILE === $error ) {
				return array(
					'status'        => 'empty',
					'attachment_id' => 0,
					'url'           => '',
					'type'          => '',
					'path'          => '',
				);
		}

		if ( UPLOAD_ERR_OK !== $error ) {
				return array(
					'status'        => 'error',
					'attachment_id' => 0,
					'url'           => '',
					'type'          => '',
					'path'          => '',
					'error'         => self::error_message_for_code( $error ),
				);
		}

			$size = isset( $file['size'] ) ? (int) $file['size'] : 0;

		if ( $size <= 0 || $size > $normalized['max_size'] ) {
				return array(
					'status'        => 'error',
					'attachment_id' => 0,
					'url'           => '',
					'type'          => '',
					'path'          => '',
					'error'         => __( 'Uploaded file exceeds the allowed size.', 'foodbank-manager' ),
				);
		}

			$allowed_map = self::build_allowed_map( $normalized['allowed_mime_types'] );

		if ( function_exists( 'wp_check_filetype_and_ext' ) && isset( $file['tmp_name'], $file['name'] ) ) {
				$check = wp_check_filetype_and_ext( (string) $file['tmp_name'], (string) $file['name'], $allowed_map );

				$type = '';
			if ( is_array( $check ) ) {
					$type_value = $check['type'];
				if ( is_string( $type_value ) && '' !== $type_value ) {
					$type = $type_value;
				}
			}

			if ( '' === $type || ! in_array( $type, $normalized['allowed_mime_types'], true ) ) {
					return array(
						'status'        => 'error',
						'attachment_id' => 0,
						'url'           => '',
						'type'          => '',
						'path'          => '',
						'error'         => __( 'File type is not permitted.', 'foodbank-manager' ),
					);
			}
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
				return array(
					'status'        => 'stored',
					'attachment_id' => 0,
					'url'           => '',
					'type'          => isset( $file['type'] ) ? (string) $file['type'] : '',
					'path'          => '',
				);
		}

			$overrides = array(
				'test_form' => false,
				'mimes'     => $allowed_map,
			);

			$result = wp_handle_upload( $file, $overrides );

			if ( ! is_array( $result ) || isset( $result['error'] ) ) {
					return array(
						'status'        => 'error',
						'attachment_id' => 0,
						'url'           => '',
						'type'          => '',
						'path'          => '',
						'error'         => is_array( $result ) && isset( $result['error'] ) ? (string) $result['error'] : __( 'Unable to store uploaded file.', 'foodbank-manager' ),
					);
			}

			$file_path = isset( $result['file'] ) ? (string) $result['file'] : '';
			$file_url  = isset( $result['url'] ) ? (string) $result['url'] : '';
			$mime_type = isset( $result['type'] ) ? (string) $result['type'] : '';

			if ( '' === $mime_type && isset( $file['type'] ) ) {
					$mime_type = (string) $file['type'];
			}

			if ( ! function_exists( 'wp_insert_attachment' ) ) {
					return array(
						'status'        => 'stored',
						'attachment_id' => 0,
						'url'           => $file_url,
						'type'          => $mime_type,
						'path'          => $file_path,
					);
			}

			$attachment = array(
				'post_mime_type' => $mime_type,
				'post_title'     => sanitize_file_name( (string) ( $file['name'] ?? $field_name ) ),
				'post_status'    => 'private',
			);

			$attachment_id = (int) wp_insert_attachment( $attachment, $file_path );

			if ( $attachment_id > 0 && function_exists( 'wp_generate_attachment_metadata' ) && function_exists( 'wp_update_attachment_metadata' ) ) {
					$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
				if ( ! empty( $metadata ) ) {
						wp_update_attachment_metadata( $attachment_id, $metadata );
				}
			}

			return array(
				'status'        => 'stored',
				'attachment_id' => $attachment_id,
				'url'           => $file_url,
				'type'          => $mime_type,
				'path'          => $file_path,
			);
	}

		/**
		 * Link stored uploads to a member reference for auditing.
		 *
		 * @param int    $attachment_id   Attachment identifier.
		 * @param string $member_reference Canonical member reference.
		 */
	public static function link_to_member( int $attachment_id, string $member_reference ): void {
		if ( $attachment_id <= 0 || '' === $member_reference ) {
				return;
		}

		if ( ! function_exists( 'update_post_meta' ) ) {
				return;
		}

			update_post_meta( $attachment_id, self::META_MEMBER_REFERENCE, $member_reference );
	}

		/**
		 * Remove stored uploads when registration fails.
		 *
		 * @param array<int,array<string,mixed>> $uploads Upload payloads from ::process().
		 */
	public static function cleanup( array $uploads ): void {
		if ( empty( $uploads ) ) {
				return;
		}

		if ( ! function_exists( 'wp_delete_attachment' ) ) {
				return;
		}

		foreach ( $uploads as $upload ) {
			if ( ! is_array( $upload ) ) {
					continue;
			}

				$attachment_id = isset( $upload['attachment_id'] ) ? (int) $upload['attachment_id'] : 0;

			if ( $attachment_id > 0 ) {
					wp_delete_attachment( $attachment_id, true );
					continue;
			}

				$path = '';

			if ( isset( $upload['path'] ) && is_string( $upload['path'] ) ) {
					$path = trim( (string) $upload['path'] );
			}

			if ( '' !== $path && function_exists( 'wp_delete_file' ) ) {
					wp_delete_file( $path );
			}
		}
	}

		/**
		 * Translate PHP upload error codes to friendly messages.
		 *
		 * @param int $code PHP upload error code.
		 */
	private static function error_message_for_code( int $code ): string {
		switch ( $code ) {
			case 1:
			case 2:
				return __( 'Uploaded file exceeds the allowed size.', 'foodbank-manager' );
			case 3:
				return __( 'File upload was interrupted. Please try again.', 'foodbank-manager' );
			case 4:
				return __( 'No file was uploaded.', 'foodbank-manager' );
			case 6:
				return __( 'Missing temporary folder. Contact the administrator.', 'foodbank-manager' );
			case 7:
				return __( 'Failed to write uploaded file to disk.', 'foodbank-manager' );
			case 8:
				return __( 'File upload stopped by extension.', 'foodbank-manager' );
			default:
				return __( 'Unable to process the uploaded file.', 'foodbank-manager' );
		}
	}

		/**
		 * Build an allowed mime map for WordPress upload handlers.
		 *
		 * @param array<int,string> $allowed Allowed mime list.
		 *
		 * @return array<string,string>
		 */
	private static function build_allowed_map( array $allowed ): array {
			$map = array();

		foreach ( $allowed as $mime ) {
			if ( ! isset( self::MIME_EXTENSION_MAP[ $mime ] ) ) {
				continue;
			}

			foreach ( self::MIME_EXTENSION_MAP[ $mime ] as $extension ) {
					$map[ $extension ] = $mime;
			}
		}

			return $map;
	}
}
