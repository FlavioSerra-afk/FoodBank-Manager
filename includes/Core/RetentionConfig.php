<?php
/**
 * Retention configuration normalization helpers.
 *
 * @package FBM\Core
 */

declare(strict_types=1);

namespace FBM\Core;

/**
 * Normalizes retention configuration options into a strict shape.
 */
final class RetentionConfig {

	/**
	 * Normalize raw retention configuration.
	 *
	 * @param mixed $raw Array, JSON string, or null.
	 * @return array{
	 *     applications: array{days:int, policy:string},
	 *     attendance: array{days:int, policy:string},
	 *     mail: array{days:int, policy:string}
	 * }
	 */
	public static function normalize( mixed $raw ): array {
		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$raw     = is_array( $decoded ) ? $decoded : array();
		} elseif ( ! is_array( $raw ) ) {
			$raw = array();
		}

		if ( isset( $raw['retention'] ) && is_array( $raw['retention'] ) ) {
			$raw = $raw['retention'];
		}

                $map = array(
                        'applications' => 'applications',
                        'attendance'   => 'attendance',
                        'mail'         => 'mail',
                        'mail_log'     => 'mail',
                );

                $out = array();
                foreach ( $map as $in => $out_key ) {
                                if ( isset( $out[ $out_key ] ) ) {
                                        continue;
                                }
                                $section         = isset( $raw[ $in ] ) && is_array( $raw[ $in ] ) ? $raw[ $in ] : array();
                                $days            = isset( $section['days'] ) ? max( 0, (int) $section['days'] ) : 0;
                                $policy          = isset( $section['policy'] ) && in_array( $section['policy'], array( 'delete', 'anonymise' ), true )
                                                ? $section['policy']
                                                : 'delete';
                                $out[ $out_key ] = array(
                                        'days'   => $days,
                                        'policy' => $policy,
                                );
                }

		return $out;
	}
}
