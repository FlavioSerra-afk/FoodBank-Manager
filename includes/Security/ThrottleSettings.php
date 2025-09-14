<?php
/**
 * Throttle settings helper.
 *
 * @package FBM\Security
 */

declare(strict_types=1);

namespace FBM\Security {

	use function add_settings_error;
	use function get_editable_roles;
	use function get_option;
	use function sanitize_key;

	/**
	 * Manage scan throttle settings.
	 */
	final class ThrottleSettings {
		/**
		 * Default settings.
		 *
		 * @return array{window_seconds:int,base_limit:int,role_multipliers:array<string,float>}
		 */
		public static function defaults(): array {
			return array(
				'window_seconds'   => 30,
				'base_limit'       => 6,
				'role_multipliers' => array(
					'administrator' => 0.0,
				),
			);
		}

		/**
		 * Retrieve settings.
		 *
		 * @return array{window_seconds:int,base_limit:int,role_multipliers:array<string,float>}
		 */
		public static function get(): array {
			$raw = get_option( 'fbm_throttle', array() );
			if ( ! is_array( $raw ) ) {
				$raw = array();
			}
			$defaults = self::defaults();
			$out      = array(
				'window_seconds'   => (int) ( $raw['window_seconds'] ?? $defaults['window_seconds'] ),
				'base_limit'       => (int) ( $raw['base_limit'] ?? $defaults['base_limit'] ),
				'role_multipliers' => is_array( $raw['role_multipliers'] ?? null ) ? $raw['role_multipliers'] : array(),
			);
			// Ensure administrator default.
			if ( ! array_key_exists( 'administrator', $out['role_multipliers'] ) ) {
				$out['role_multipliers']['administrator'] = 0.0;
			}
			return $out;
		}

		/**
		 * Sanitize settings from input.
		 *
		 * @param mixed $input Raw input.
		 * @return array{window_seconds:int,base_limit:int,role_multipliers:array<string,float>}
		 */
		public static function sanitize( $input ): array {
			$defaults = self::defaults();
			if ( ! is_array( $input ) ) {
				add_settings_error( 'fbm_security', 'fbm_throttle_invalid', __( 'Invalid throttle settings', 'foodbank-manager' ) );
				return $defaults;
			}
			$window = isset( $input['window_seconds'] ) ? (int) $input['window_seconds'] : $defaults['window_seconds'];
			$base   = isset( $input['base_limit'] ) ? (int) $input['base_limit'] : $defaults['base_limit'];
			$window = max( 5, min( 300, $window ) );
			if ( $window !== (int) ( $input['window_seconds'] ?? $window ) ) {
				add_settings_error( 'fbm_security', 'fbm_throttle_window', __( 'Window seconds out of range', 'foodbank-manager' ) );
			}
			$base = max( 1, min( 120, $base ) );
			if ( $base !== (int) ( $input['base_limit'] ?? $base ) ) {
				add_settings_error( 'fbm_security', 'fbm_throttle_base', __( 'Base limit out of range', 'foodbank-manager' ) );
			}
			$roles    = array();
			$editable = get_editable_roles();
			$raw_mult = $input['role_multipliers'] ?? array();
			if ( is_array( $raw_mult ) ) {
				foreach ( $raw_mult as $role => $mult ) {
					$role_key = sanitize_key( (string) $role );
					if ( ! isset( $editable[ $role_key ] ) ) {
						add_settings_error( 'fbm_security', 'fbm_throttle_role_' . $role_key, __( 'Invalid role', 'foodbank-manager' ) );
						continue;
					}
					if ( ! is_numeric( $mult ) ) {
						add_settings_error( 'fbm_security', 'fbm_throttle_mult_' . $role_key, __( 'Invalid multiplier', 'foodbank-manager' ) );
						continue;
					}
					$m = (float) $mult;
					if ( $m < 0 ) {
						add_settings_error( 'fbm_security', 'fbm_throttle_mult_' . $role_key, __( 'Multiplier out of range', 'foodbank-manager' ) );
						$m = 0.0;
					}
					if ( $m > 10 ) {
						add_settings_error( 'fbm_security', 'fbm_throttle_mult_' . $role_key, __( 'Multiplier out of range', 'foodbank-manager' ) );
						$m = 10.0;
					}
					$roles[ $role_key ] = $m;
				}
			}
			if ( ! array_key_exists( 'administrator', $roles ) ) {
				$roles['administrator'] = 0.0;
			}
			return array(
				'window_seconds'   => $window,
				'base_limit'       => $base,
				'role_multipliers' => $roles,
			);
		}

		/**
		 * Effective limit for a role.
		 */
		public static function limit_for_role( string $role ): int {
			$settings = self::get();
			$mult     = (float) ( $settings['role_multipliers'][ $role ] ?? 1.0 );
			if ( 0.0 === $mult ) {
				return 0;
			}
			$limit = (int) ceil( $settings['base_limit'] * max( 0.1, $mult ) );
			return max( 1, $limit );
		}

		/**
		 * Effective limits for all roles.
		 *
		 * @return array<string,int>
		 */
		public static function limits(): array {
			$roles  = array_keys( get_editable_roles() );
			$limits = array();
			foreach ( $roles as $r ) {
				$limits[ $r ] = self::limit_for_role( $r );
			}
			return $limits;
		}
	}
}

// Global namespace wrapper for Settings API.
namespace {
	/** Sanitize throttle settings. */
	function fbm_throttle_sanitize( mixed $input ): array {
		return \FBM\Security\ThrottleSettings::sanitize( $input );
	}
}
