<?php
/**
 * Form schema validation and normalization.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Forms;

use InvalidArgumentException;
use function sanitize_key;
use function sanitize_text_field;

/**
 * Schema validator.
 */
final class Schema {

	/**
	 * Normalize and validate a schema.
	 *
	 * @param array<string,mixed> $schema Raw schema.
	 * @return array{meta:array{name:string,slug:string,captcha:bool},fields:array<int,array<string,mixed>>}
	 * @throws InvalidArgumentException When schema is invalid.
	 */
	public static function normalize( array $schema ): array {
		$meta_raw = $schema['meta'] ?? array();
		$name     = sanitize_text_field( (string) ( $meta_raw['name'] ?? '' ) );
		$slug     = sanitize_key( (string) ( $meta_raw['slug'] ?? '' ) );
		$captcha  = ! empty( $meta_raw['captcha'] );
		if ( '' === $name || '' === $slug ) {
			throw new InvalidArgumentException( 'meta' );
		}
		$fields_raw = $schema['fields'] ?? array();
		if ( ! is_array( $fields_raw ) ) {
			throw new InvalidArgumentException( 'fields' );
		}
		$ids    = array();
		$fields = array();
		$types  = FieldTypes::all();
		foreach ( $fields_raw as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$id       = sanitize_key( (string) ( $field['id'] ?? '' ) );
			$type     = sanitize_key( (string) ( $field['type'] ?? '' ) );
			$label    = sanitize_text_field( (string) ( $field['label'] ?? '' ) );
			$required = ! empty( $field['required'] );
			if ( '' === $id || '' === $type || ! isset( $types[ $type ] ) ) {
				throw new InvalidArgumentException( 'field' );
			}
			if ( in_array( $id, $ids, true ) ) {
				throw new InvalidArgumentException( 'duplicate' );
			}
			$ids[] = $id;
			$item  = array(
				'id'       => $id,
				'type'     => $type,
				'label'    => $label,
				'required' => $required,
			);
			if ( FieldTypes::has_options( $type ) ) {
				$opts_in = $field['options'] ?? array();
				$opts    = array();
				if ( is_array( $opts_in ) ) {
					foreach ( $opts_in as $opt ) {
						$opt = sanitize_text_field( (string) $opt );
						if ( '' !== $opt ) {
							$opts[] = $opt;
						}
					}
				}
				$item['options'] = $opts;
			}
			$fields[] = $item;
		}
		if ( ! $fields ) {
			throw new InvalidArgumentException( 'fields' );
		}
		return array(
			'meta'   => array(
				'name'    => $name,
				'slug'    => $slug,
				'captcha' => $captcha,
			),
			'fields' => $fields,
		);
	}
}
