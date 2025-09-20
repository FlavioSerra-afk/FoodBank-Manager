<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName,WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Registration conditional rule helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration\Editor;

use function array_filter;
use function array_unique;
use function array_values;
use function count;
use function gmdate;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function json_decode;
use function preg_replace;
use function sanitize_key;
use function sanitize_text_field;
use function strtolower;
use function trim;

/**
 * Normalize, export, and import conditional visibility rules.
 */
final class Conditions {
	private const MAX_GROUPS          = 25;
	private const MAX_ITEMS_PER_GROUP = 10;

		/**
		 * Current schema version for rule exports.
		 */
	public const SCHEMA_VERSION = 1;

		/**
		 * Sanitize conditional groups from arbitrary payloads.
		 *
		 * @param mixed $groups Raw group payload (array or JSON string).
		 *
		 * @return array<int,array<string,mixed>>
		 */
	public static function sanitize_groups( $groups ): array {
		if ( is_string( $groups ) ) {
				$decoded = json_decode( $groups, true );
				$groups  = is_array( $decoded ) ? $decoded : array();
		}

		if ( ! is_array( $groups ) ) {
				return array();
		}

			$allowed_conditions = array( 'equals', 'not_equals', 'contains', 'empty', 'not_empty', 'lt', 'lte', 'gt', 'gte' );
			$allowed_actions    = array( 'show', 'hide', 'require', 'optional' );
			$sanitized          = array();

		foreach ( $groups as $group ) {
			if ( ! is_array( $group ) ) {
					continue;
			}

			// Legacy flat payload support.
			if ( isset( $group['field'], $group['operator'], $group['action'], $group['target'] ) && ! isset( $group['conditions'], $group['actions'] ) ) {
				$legacy_operator = isset( $group['logic'] ) ? (string) $group['logic'] : (string) $group['operator'];
				$legacy_value    = isset( $group['value'] ) ? (string) $group['value'] : '';

				$group = array(
					'operator'   => $legacy_operator,
					'conditions' => array(
						array(
							'field'    => (string) $group['field'],
							'operator' => (string) $group['operator'],
							'value'    => $legacy_value,
						),
					),
					'actions'    => array(
						array(
							'type'   => (string) $group['action'],
							'target' => (string) $group['target'],
						),
					),
				);
			}

				$operator = isset( $group['operator'] ) ? sanitize_key( (string) $group['operator'] ) : 'and';
			if ( ! in_array( $operator, array( 'and', 'or' ), true ) ) {
					$operator = 'and';
			}

				$conditions_raw = $group['conditions'] ?? array();
			if ( is_string( $conditions_raw ) ) {
					$decoded        = json_decode( $conditions_raw, true );
					$conditions_raw = is_array( $decoded ) ? $decoded : array();
			}

				$actions_raw = $group['actions'] ?? array();
			if ( is_string( $actions_raw ) ) {
					$decoded     = json_decode( $actions_raw, true );
					$actions_raw = is_array( $decoded ) ? $decoded : array();
			}

			if ( ! is_array( $conditions_raw ) || ! is_array( $actions_raw ) ) {
					continue;
			}

				$conditions = array();
			foreach ( $conditions_raw as $condition ) {
				if ( ! is_array( $condition ) ) {
						continue;
				}

					$field        = isset( $condition['field'] ) ? sanitize_key( (string) $condition['field'] ) : '';
					$operator_key = isset( $condition['operator'] ) ? sanitize_key( (string) $condition['operator'] ) : '';
					$value        = isset( $condition['value'] ) ? sanitize_text_field( (string) $condition['value'] ) : '';

				if ( '' === $field || ! in_array( $operator_key, $allowed_conditions, true ) ) {
						continue;
				}

				if ( in_array( $operator_key, array( 'empty', 'not_empty' ), true ) ) {
						$value = '';
				}

				if ( in_array( $operator_key, array( 'equals', 'not_equals', 'contains', 'lt', 'lte', 'gt', 'gte' ), true ) && '' === trim( $value ) ) {
						continue;
				}

					$conditions[] = array(
						'field'      => $field,
						'operator'   => $operator_key,
						'value'      => $value,
						'field_type' => isset( $condition['field_type'] ) ? sanitize_key( (string) $condition['field_type'] ) : '',
					);

					if ( count( $conditions ) >= self::MAX_ITEMS_PER_GROUP ) {
							break;
					}
			}

				$actions = array();
			foreach ( $actions_raw as $action ) {
				if ( ! is_array( $action ) ) {
						continue;
				}

					$type   = isset( $action['type'] ) ? sanitize_key( (string) $action['type'] ) : '';
					$target = isset( $action['target'] ) ? sanitize_key( (string) $action['target'] ) : '';

				if ( '' === $type || '' === $target ) {
						continue;
				}

				if ( ! in_array( $type, $allowed_actions, true ) ) {
						continue;
				}

					$actions[] = array(
						'type'   => $type,
						'target' => $target,
					);

					if ( count( $actions ) >= self::MAX_ITEMS_PER_GROUP ) {
							break;
					}
			}

			if ( empty( $conditions ) || empty( $actions ) ) {
					continue;
			}

				$sanitized[] = array(
					'operator'   => $operator,
					'conditions' => $conditions,
					'actions'    => $actions,
				);

				if ( count( $sanitized ) >= self::MAX_GROUPS ) {
						break;
				}
		}

			return $sanitized;
	}

		/**
		 * Export sanitized payload for download.
		 *
		 * @param array<int,array<string,string>> $fields     Current field catalogue.
		 * @param array<string,mixed>             $conditions Settings conditions payload.
		 */
	public static function export_payload( array $fields, array $conditions ): array {
			$enabled = ! empty( $conditions['enabled'] );
			$groups  = self::sanitize_groups( $conditions['groups'] ?? array() );

			return array(
				'schema'     => array(
					'version'      => self::SCHEMA_VERSION,
					'generated_at' => gmdate( 'c' ),
				),
				'fields'     => array_values( self::normalize_fields( $fields ) ),
				'conditions' => array(
					'enabled' => $enabled && ! empty( $groups ),
					'groups'  => $groups,
				),
			);
	}

		/**
		 * Normalize field definitions to name/label/type.
		 *
		 * @param mixed $fields Raw field catalogue.
		 *
		 * @return array<int,array<string,string>>
		 */
	public static function normalize_fields( $fields ): array {
		if ( ! is_array( $fields ) ) {
				return array();
		}

			$normalized = array();
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
					continue;
			}

				$name = isset( $field['name'] ) ? sanitize_key( (string) $field['name'] ) : '';
			if ( '' === $name ) {
					continue;
			}

				$normalized[] = array(
					'name'  => $name,
					'label' => isset( $field['label'] ) ? sanitize_text_field( (string) $field['label'] ) : $name,
					'type'  => isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text',
				);
		}

			return $normalized;
	}

		/**
		 * Prepare import preview payload.
		 *
		 * @param array<string,mixed>             $payload        Raw decoded JSON payload.
		 * @param array<int,array<string,string>> $current_fields Current field catalogue.
		 */
	public static function preview_import( array $payload, array $current_fields ): array {
			$schema  = isset( $payload['schema'] ) && is_array( $payload['schema'] ) ? $payload['schema'] : array();
			$version = isset( $schema['version'] ) && is_numeric( $schema['version'] ) ? (int) $schema['version'] : 0;

			$conditions_raw = isset( $payload['conditions'] ) && is_array( $payload['conditions'] ) ? $payload['conditions'] : array();
			$groups         = self::sanitize_groups( $conditions_raw['groups'] ?? array() );
			$enabled        = ! empty( $conditions_raw['enabled'] );

			$incoming_fields = self::normalize_fields( $payload['fields'] ?? array() );
			$current_normal  = self::normalize_fields( $current_fields );

			$suggested = self::suggested_mappings( $incoming_fields, $current_normal );

			$group_analysis = array();
		foreach ( $groups as $index => $group ) {
				$missing = array();
			foreach ( $group['conditions'] as $condition ) {
				$field = $condition['field'];
				if ( '' === $field ) {
						$missing[] = $field;
						continue;
				}

				if ( '' === ( $suggested[ $field ] ?? '' ) ) {
						$missing[] = $field;
				}
			}

			foreach ( $group['actions'] as $action ) {
					$target = $action['target'];
				if ( '' === $target ) {
					$missing[] = $target;
					continue;
				}

				if ( '' === ( $suggested[ $target ] ?? '' ) ) {
						$missing[] = $target;
				}
			}

				$missing = self::clean_missing( $missing );

				$group_analysis[] = array(
					'index'   => $index,
					'missing' => $missing,
				);
		}

			return array(
				'schemaVersion' => $version,
				'enabled'       => $enabled,
				'groups'        => $groups,
				'fields'        => array(
					'incoming'  => $incoming_fields,
					'current'   => $current_normal,
					'suggested' => $suggested,
				),
				'analysis'      => $group_analysis,
			);
	}

	/**
	 * Apply mappings to imported payload.
	 *
	 * @param array<string,mixed>             $payload        Raw decoded JSON payload.
	 * @param array<string,string>            $mapping        Incoming field => current field mapping.
	 * @param array<int,array<string,string>> $current_fields Current field catalogue.
	 *
	 * @return array{
	 *     enabled: bool,
	 *     groups: array<int, array{
	 *         operator: string,
	 *         conditions: array<int, array{field: string, operator: string, value: string, field_type: string}>,
	 *         actions: array<int, array{type: string, target: string}>
	 *     }>,
	 *     skipped: array<int, array{position: string, reason: string, missing: array<int, string>}>}
	 */
	public static function apply_import( array $payload, array $mapping, array $current_fields ): array {
		$conditions_raw = isset( $payload['conditions'] ) && is_array( $payload['conditions'] ) ? $payload['conditions'] : array();
		$groups         = self::sanitize_groups( $conditions_raw['groups'] ?? array() );
		$enabled        = ! empty( $conditions_raw['enabled'] );

		$current_map = array();
		foreach ( self::normalize_fields( $current_fields ) as $field ) {
			$current_map[ $field['name'] ] = $field;
		}

		$mapped   = array();
		$skipped  = array();
		$position = 1;

		foreach ( $groups as $group ) {
			$missing    = array();
			$conditions = array();
			$actions    = array();
			$skip       = false;

			foreach ( $group['conditions'] as $condition ) {
				$source = isset( $condition['field'] ) ? sanitize_key( (string) $condition['field'] ) : '';
				$target = isset( $mapping[ $source ] ) ? sanitize_key( (string) $mapping[ $source ] ) : '';

				if ( '' === $source || '' === $target || ! isset( $current_map[ $target ] ) ) {
					$missing[] = $source;
					$skip      = true;
					break;
				}

				$conditions[] = array(
					'field'      => $target,
					'operator'   => isset( $condition['operator'] ) ? (string) $condition['operator'] : '',
					'value'      => isset( $condition['value'] ) ? (string) $condition['value'] : '',
					'field_type' => $current_map[ $target ]['type'] ?? '',
				);
			}

			if ( $skip ) {
				$skipped[] = array(
					'position' => (string) $position,
					'reason'   => 'missing_field',
					'missing'  => self::clean_missing( $missing ),
				);
				++$position;
				continue;
			}

			foreach ( $group['actions'] as $action ) {
				$source = isset( $action['target'] ) ? sanitize_key( (string) $action['target'] ) : '';
				$target = isset( $mapping[ $source ] ) ? sanitize_key( (string) $mapping[ $source ] ) : '';

				if ( '' === $source || '' === $target || ! isset( $current_map[ $target ] ) ) {
					$missing[] = $source;
					$skip      = true;
					break;
				}

				$actions[] = array(
					'type'   => isset( $action['type'] ) ? (string) $action['type'] : '',
					'target' => $target,
				);
			}

			if ( $skip || empty( $conditions ) || empty( $actions ) ) {
				$skipped[] = array(
					'position' => (string) $position,
					'reason'   => $skip ? 'missing_field' : 'empty',
					'missing'  => self::clean_missing( $missing ),
				);
				++$position;
				continue;
			}

			$mapped[] = array(
				'operator'   => isset( $group['operator'] ) ? (string) $group['operator'] : 'and',
				'conditions' => $conditions,
				'actions'    => $actions,
			);

			++$position;
		}

		$enabled = $enabled && ! empty( $mapped );

		return array(
			'enabled' => $enabled,
			'groups'  => $mapped,
			'skipped' => $skipped,
		);
	}


		/**
		 * Normalize an array of missing identifiers.
		 *
		 * @param array<int, string> $missing Raw missing field identifiers.
		 *
		 * @return array<int, string>
		 */
	private static function clean_missing( array $missing ): array {
		$normalized = array();

		foreach ( $missing as $entry ) {
			$candidate = sanitize_key( (string) $entry );
			if ( '' !== $candidate ) {
				$normalized[ $candidate ] = $candidate;
			}
		}

		return array_values( $normalized );
	}

		/**
		 * Produce suggested mapping array keyed by incoming field name.
		 *
		 * @param array<int,array<string,string>> $incoming_fields Incoming fields.
		 * @param array<int,array<string,string>> $current_fields  Current field catalogue.
		 *
		 * @return array<string,string>
		 */
	private static function suggested_mappings( array $incoming_fields, array $current_fields ): array {
			$suggested        = array();
			$current_by_name  = array();
			$current_by_label = array();

		foreach ( $current_fields as $field ) {
				$current_by_name[ $field['name'] ] = $field['name'];
				$label_key                         = strtolower( preg_replace( '/\s+/', ' ', $field['label'] ) ?? '' );
			if ( '' !== $label_key && ! isset( $current_by_label[ $label_key ] ) ) {
				$current_by_label[ $label_key ] = $field['name'];
			}
		}

		foreach ( $incoming_fields as $field ) {
				$name  = $field['name'];
				$label = strtolower( preg_replace( '/\s+/', ' ', $field['label'] ) ?? '' );

			if ( isset( $current_by_name[ $name ] ) ) {
					$suggested[ $name ] = $current_by_name[ $name ];
					continue;
			}

			if ( '' !== $label && isset( $current_by_label[ $label ] ) ) {
					$suggested[ $name ] = $current_by_label[ $label ];
					continue;
			}

				$suggested[ $name ] = '';
		}

			return $suggested;
	}
}

// phpcs:enable WordPress.Files.FileName.InvalidClassFileName,WordPress.Files.FileName.NotHyphenatedLowercase
