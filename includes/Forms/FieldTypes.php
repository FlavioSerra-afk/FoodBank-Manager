<?php
/**
 * Field types registry.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Forms;

/**
 * Field types registry.
 */
final class FieldTypes {

	/**
	 * All supported field types.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function all(): array {
		return array(
			'text'           => array(
				'sanitize' => 'sanitize_text_field',
				'validate' => static function ( string $value ): bool {
					return '' !== $value;
				},
			),
			'textarea'       => array(
				'sanitize' => 'sanitize_textarea_field',
				'validate' => static function ( string $value ): bool {
					return '' !== $value;
				},
			),
			'email'          => array(
				'sanitize' => 'sanitize_email',
				'validate' => static function ( string $value ): bool {
					return '' === $value || is_email( $value );
				},
			),
			'tel'            => array(
				'sanitize' => 'sanitize_text_field',
				'validate' => static function ( string $value ): bool {
					return '' === $value || 1 === preg_match( '/^[0-9+\-()\s]+$/', $value );
				},
			),
			'date'           => array(
				'sanitize' => 'sanitize_text_field',
				'validate' => static function ( string $value ): bool {
					return '' === $value || 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value );
				},
			),
			'select'         => array(
				'sanitize' => 'sanitize_text_field',
				'validate' => static function ( string $value, array $field ): bool {
					$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
					return '' === $value || in_array( $value, $options, true );
				},
				'options'  => true,
			),
			'radio'          => array(
				'sanitize' => 'sanitize_text_field',
				'validate' => static function ( string $value, array $field ): bool {
					$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
					return '' === $value || in_array( $value, $options, true );
				},
				'options'  => true,
			),
			'checkbox'       => array(
				'sanitize' => static function ( $value ): string {
					return $value ? '1' : '';
				},
				'validate' => static function ( string $value ): bool {
					return in_array( $value, array( '1', '' ), true );
				},
			),
			'file'           => array(
				'sanitize' => 'sanitize_text_field',
				'validate' => static function ( string $value ): bool {
					return '' !== $value;
				},
			),
			'consent'        => array(
				'sanitize' => static function ( $value ): string {
					return $value ? '1' : '';
				},
				'validate' => static function ( string $value ): bool {
					return '1' === $value;
				},
			),
			'privacy_notice' => array(
				'sanitize' => 'sanitize_textarea_field',
				'validate' => static function (): bool {
					return true;
				},
			),
		);
	}

	/**
	 * Determine whether a field type supports options.
	 *
	 * @param string $type Field type.
	 * @return bool
	 */
	public static function has_options( string $type ): bool {
		$all = self::all();
		return ! empty( $all[ $type ]['options'] );
	}
}
