<?php
/**
 * Form presets library.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Forms;

use FoodBankManager\Core\Options;
use function sanitize_key;
use function sanitize_text_field;

/**
 * Preset definitions and helpers.
 */
final class Presets {
	/**
	 * Allowed field types.
	 *
	 * @var string[]
	 */
	private const TYPES = array( 'text', 'email', 'tel', 'select', 'textarea', 'checkbox' );

	/**
	 * Built-in presets.
	 *
	 * @return array<string,array<int,array<string,mixed>>>
	 */
	public static function builtins(): array {
		return array(
			'basic_intake'   => array(
				array(
					'name'     => 'first_name',
					'type'     => 'text',
					'label'    => 'First Name',
					'required' => true,
				),
				array(
					'name'     => 'last_name',
					'type'     => 'text',
					'label'    => 'Last Name',
					'required' => true,
				),
				array(
					'name'     => 'email',
					'type'     => 'email',
					'label'    => 'Email',
					'required' => true,
				),
				array(
					'name'     => 'phone',
					'type'     => 'tel',
					'label'    => 'Phone',
					'required' => false,
				),
				array(
					'name'     => 'notes',
					'type'     => 'textarea',
					'label'    => 'Notes',
					'required' => false,
				),
				array(
					'name'     => 'consent',
					'type'     => 'checkbox',
					'label'    => 'I consent to the processing of my data as described.',
					'required' => true,
				),
			),
			'compact_intake' => array(
				array(
					'name'     => 'name',
					'type'     => 'text',
					'label'    => 'Full Name',
					'required' => true,
				),
				array(
					'name'     => 'email',
					'type'     => 'email',
					'label'    => 'Email',
					'required' => true,
				),
				array(
					'name'     => 'consent',
					'type'     => 'checkbox',
					'label'    => 'I consent to the processing of my data as described.',
					'required' => true,
				),
			),
			'fallback'       => array(
				array(
					'name'     => 'name',
					'type'     => 'text',
					'label'    => 'Name',
					'required' => true,
				),
				array(
					'name'     => 'email',
					'type'     => 'email',
					'label'    => 'Email',
					'required' => true,
				),
			),
		);
	}

	/**
	 * Resolve a preset by ID.
	 *
	 * @param string              $id        Preset ID.
	 * @param array<string,mixed> $overrides Field overrides (unused).
	 * @return array<int,array<string,mixed>>
	 */
	public static function resolve( string $id, array $overrides = array() ): array {
		unset( $overrides );
		$id   = sanitize_key( $id );
		$all  = self::all();
		$list = $all[ $id ] ?? self::builtins()['fallback'];
		return self::sanitize_fields( $list );
	}

	/**
	 * Return all presets (built-in + custom).
	 *
	 * @return array<string,array<int,array<string,mixed>>>
	 */
	public static function all(): array {
		$custom = Options::get_form_presets_custom();
		if ( ! is_array( $custom ) ) {
			$custom = array();
		}
		return array_merge( self::builtins(), $custom );
	}

	/**
	 * Determine whether a preset ID exists.
	 *
	 * @param string $id Preset ID.
	 * @return bool
	 */
	public static function exists( string $id ): bool {
		$id  = sanitize_key( $id );
		$all = self::all();
		return isset( $all[ $id ] );
	}

	/**
	 * Sanitize many presets.
	 *
	 * @param array<string,mixed> $presets Raw presets.
	 * @return array<string,array<int,array<string,mixed>>>
	 */
	public static function sanitize_all( array $presets ): array {
		$out = array();
		foreach ( $presets as $id => $fields ) {
			$id = sanitize_key( (string) $id );
			if ( '' === $id ) {
				continue;
			}
			$sanitized  = self::sanitize_fields( $fields );
			$out[ $id ] = $sanitized;
		}
		return $out;
	}

	/**
	 * Sanitize a list of fields.
	 *
	 * @param mixed $fields Raw fields.
	 * @return array<int,array<string,mixed>>
	 */
	private static function sanitize_fields( $fields ): array {
		$out = array();
		if ( ! is_array( $fields ) ) {
			return $out;
		}
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$type = sanitize_key( (string) ( $field['type'] ?? '' ) );
			$name = sanitize_key( (string) ( $field['name'] ?? '' ) );
			if ( '' === $name || ! in_array( $type, self::TYPES, true ) ) {
				continue;
			}
			$item = array(
				'name'     => $name,
				'type'     => $type,
				'label'    => sanitize_text_field( (string) ( $field['label'] ?? '' ) ),
				'required' => ! empty( $field['required'] ),
			);
			if ( 'select' === $type ) {
				$opts = array();
				$raw  = $field['options'] ?? array();
				if ( is_array( $raw ) ) {
					foreach ( $raw as $opt ) {
						$opt = sanitize_text_field( (string) $opt );
						if ( '' !== $opt ) {
							$opts[] = $opt;
						}
					}
				}
				$item['options'] = $opts;
			}
			$out[] = $item;
		}
		return $out;
	}
}
