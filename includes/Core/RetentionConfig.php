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

                $mail_raw = array();
                if ( isset( $raw['mail'] ) && is_array( $raw['mail'] ) ) {
                        $mail_raw = $raw['mail'];
                } elseif ( isset( $raw['mail_log'] ) && is_array( $raw['mail_log'] ) ) {
                        $mail_raw = $raw['mail_log'];
                }

                $sections = array(
                        'applications' => isset( $raw['applications'] ) && is_array( $raw['applications'] ) ? $raw['applications'] : array(),
                        'attendance'   => isset( $raw['attendance'] ) && is_array( $raw['attendance'] ) ? $raw['attendance'] : array(),
                        'mail'         => $mail_raw,
                );

                $out = array();
                foreach ( $sections as $out_key => $section ) {
                        $days   = isset( $section['days'] ) ? max( 0, (int) $section['days'] ) : 0;
                        $policy = isset( $section['policy'] ) && in_array( $section['policy'], array( 'delete', 'anonymise' ), true )
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
