<?php
/**
 * Common REST argument schemas.
 *
 * @package FoodBankManager\Rest
 */

declare(strict_types=1);

namespace FoodBankManager\Rest;

use function absint;
use function sanitize_email;
use function is_email;

/**
 * Helpers for REST request argument rules.
 */
final class ArgHelper {
	/**
	 * Integer ID argument.
	 *
	 * @param bool $required Whether the argument is required.
	 * @return array<string,mixed>
	 */
	public static function id( bool $required = true ): array {
		return array(
			'type'              => 'integer',
			'required'          => $required,
			'sanitize_callback' => 'absint',
			'validate_callback' => static fn( $v ): bool => is_int( $v ) && $v > 0,
		);
	}

	/**
	 * Email argument.
	 *
	 * @param bool $required Whether the argument is required.
	 * @return array<string,mixed>
	 */
	public static function email( bool $required = true ): array {
		return array(
			'type'              => 'string',
			'required'          => $required,
			'sanitize_callback' => 'sanitize_email',
			'validate_callback' => static fn( $v ): bool => is_string( $v ) && is_email( $v ),
		);
	}

	/**
	 * Paging arguments.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function paging(): array {
		return array(
			'page'     => array(
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => static fn( $v ): bool => is_int( $v ) && $v >= 1,
			),
			'per_page' => array(
				'type'              => 'integer',
				'default'           => 20,
				'sanitize_callback' => 'absint',
				'validate_callback' => static fn( $v ): bool => is_int( $v ) && $v >= 1 && $v <= 100,
			),
		);
	}
}
