<?php
/**
 * Presets repository stored in wp_options.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Forms;

use function sanitize_key;
use function wp_json_encode;

/**
 * Presets repository.
 */
final class PresetsRepo {
	private const INDEX_KEY = 'fbm_forms_index';

	/**
	 * List available presets.
	 *
	 * @return array<int,array{name:string,slug:string,updated_at:int}>
	 */
	public static function list(): array {
		$index = get_option( self::INDEX_KEY, array() );
		return is_array( $index ) ? $index : array();
	}

	/**
	 * Get preset by slug.
	 *
	 * @param string $slug Slug.
	 * @return array|null
	 */
	public static function get_by_slug( string $slug ): ?array {
		$slug = sanitize_key( $slug );
		if ( '' === $slug ) {
			return null;
		}
				$raw = get_option( 'fbm_form_' . $slug );
		if ( ! is_string( $raw ) ) {
				return null;
		}
				$decoded = json_decode( $raw, true );
		if ( ! is_array( $decoded ) ) {
				return null;
		}
		try {
				return Schema::normalize( $decoded );
		} catch ( \InvalidArgumentException $e ) {
				return null;
		}
	}

	/**
	 * Upsert a schema.
	 *
	 * @param array $schema Schema (normalized).
	 * @return int Timestamp.
	 */
	public static function upsert( array $schema ): int {
		$normalized = Schema::normalize( $schema );
		$slug       = $normalized['meta']['slug'];
		$key        = 'fbm_form_' . $slug;
				update_option( $key, wp_json_encode( $normalized ), false ); // @phpstan-ignore-line
		$index                  = self::list();
		$timestamp              = time();
				$index_filtered = array_filter(
					$index,
					static function ( $item ) use ( $slug ) {
								return is_array( $item ) && $item['slug'] !== $slug;
					}
				);
		$index_filtered[]       = array(
			'name'       => $normalized['meta']['name'],
			'slug'       => $slug,
			'updated_at' => $timestamp,
		);
				update_option( self::INDEX_KEY, $index_filtered, false ); // @phpstan-ignore-line
		return $timestamp;
	}

	/**
	 * Delete a preset by slug.
	 *
	 * @param string $slug Slug.
	 * @return bool
	 */
	public static function delete( string $slug ): bool {
		$slug  = sanitize_key( $slug );
		$key   = 'fbm_form_' . $slug;
		$index = self::list();
		$index = array_filter(
			$index,
			static function ( $item ) use ( $slug ) {
						return is_array( $item ) && $item['slug'] !== $slug;
			}
		);
				update_option( self::INDEX_KEY, $index, false ); // @phpstan-ignore-line
		if ( function_exists( 'delete_option' ) ) {
			return delete_option( $key );
		}
				update_option( $key, null, false ); // @phpstan-ignore-line
		return true;
	}
}
