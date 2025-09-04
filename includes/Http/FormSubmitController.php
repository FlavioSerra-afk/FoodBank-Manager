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
use function sanitize_key;
use function sanitize_text_field;

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
		$slug   = sanitize_key( wp_unslash( (string) ( $_POST['preset'] ?? '' ) ) );
		$schema = PresetsRepo::get_by_slug( $slug );
		if ( ! $schema ) {
			wp_die( esc_html__( 'Invalid form.', 'foodbank-manager' ) );
		}
		try {
			self::validate_against_schema( $schema, $_POST );
		} catch ( \RuntimeException $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}
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
			if ( ! in_array( (string) $key, $allowed, true ) && ! in_array( (string) $key, array( '_fbm_nonce', 'preset', 'action', 'captcha' ), true ) ) {
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
}
