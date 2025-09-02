<?php
/**
 * Options storage and helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

class Options {
	private const KEY = 'fbm_settings';

	/**
	 * Default settings structure.
	 *
	 * @return array<string,mixed>
	 */
	private static function defaults(): array {
		return array(
			'general'    => array(
				'org_name'    => '',
				'logo_id'     => 0,
				'date_format' => '',
			),
			'forms'      => array(
				'captcha_provider'         => 'off',
				'captcha_site_key'         => '',
				'captcha_secret'           => '',
				'honeypot'                 => true,
				'rate_limit_per_ip'        => 60,
				'consent_text'             => 'I agree to ...',
				'success_redirect_page_id' => 0,
			),
			'files'      => array(
				'max_size_mb'   => 5,
				'allowed_mimes' => array( 'pdf', 'jpg', 'jpeg', 'png' ),
				'storage'       => 'uploads',
				'local_path'    => '',
			),
			'emails'     => array(
				'from_name'        => '',
				'from_email'       => '',
				'reply_to'         => '',
				'admin_recipients' => '',
			),
			'attendance' => array(
				'policy_days' => 7,
				'types'       => array( 'in_person', 'delivery', 'other' ),
			),
			'privacy'    => array(
				'retention_months' => 24,
				'anonymise_files'  => 'delete',
			),
			'encryption' => array(),
		);
	}

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function all(): array {
		$saved = get_option( self::KEY, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_replace_recursive( self::defaults(), $saved );
	}

	/**
	 * Retrieve a setting using dot notation path.
	 *
	 * @param string $path    Dot notation path.
	 * @param mixed  $default Default value if missing.
	 * @return mixed
	 */
	public static function get( string $path, $default = null ) {
		$settings = self::all();
		foreach ( explode( '.', $path ) as $segment ) {
			if ( ! is_array( $settings ) || ! array_key_exists( $segment, $settings ) ) {
				return $default;
			}
			$settings = $settings[ $segment ];
		}
		return $settings;
	}

	/**
	 * Set a value using dot notation and persist.
	 *
	 * @param string $path  Path like "emails.from_email".
	 * @param mixed  $value Value to set.
	 */
	public static function set( string $path, $value ): bool {
		$settings = self::all();
		$ref      =& $settings;
		$segments = explode( '.', $path );
		foreach ( $segments as $seg ) {
			if ( ! isset( $ref[ $seg ] ) || ! is_array( $ref[ $seg ] ) ) {
				$ref[ $seg ] = array();
			}
			$ref =& $ref[ $seg ];
		}
		$ref = $value;
		return update_option( self::KEY, $settings );
	}

	/**
	 * Back-compat alias for set().
	 */
	public static function update( string $path, $value ): bool {
		return self::set( $path, $value );
	}

	/**
	 * Persist an array of settings, merged with defaults.
	 * Values are sanitized based on known structure.
	 *
	 * @param array<string,mixed> $new Settings from request.
	 */
	public static function saveAll( array $new ): bool {
		$defaults = self::defaults();
		$merged   = array_replace_recursive( $defaults, $new );

		// basic sanitization
		$merged['general']['org_name']    = sanitize_text_field( (string) ( $merged['general']['org_name'] ?? '' ) );
		$merged['general']['logo_id']     = (int) ( $merged['general']['logo_id'] ?? 0 );
		$merged['general']['date_format'] = sanitize_text_field( (string) ( $merged['general']['date_format'] ?? '' ) );

		$merged['forms']['captcha_provider']         = in_array( $merged['forms']['captcha_provider'], array( 'off', 'recaptcha', 'turnstile' ), true ) ? $merged['forms']['captcha_provider'] : 'off';
		$merged['forms']['captcha_site_key']         = sanitize_text_field( (string) ( $merged['forms']['captcha_site_key'] ?? '' ) );
		$merged['forms']['captcha_secret']           = sanitize_text_field( (string) ( $merged['forms']['captcha_secret'] ?? '' ) );
		$merged['forms']['honeypot']                 = ! empty( $merged['forms']['honeypot'] );
		$merged['forms']['rate_limit_per_ip']        = max( 0, (int) ( $merged['forms']['rate_limit_per_ip'] ?? 60 ) );
		$merged['forms']['consent_text']             = wp_kses_post( (string) ( $merged['forms']['consent_text'] ?? '' ) );
		$merged['forms']['success_redirect_page_id'] = (int) ( $merged['forms']['success_redirect_page_id'] ?? 0 );

		$merged['files']['max_size_mb'] = max( 1, (int) ( $merged['files']['max_size_mb'] ?? 5 ) );
		$mimes                          = $merged['files']['allowed_mimes'];
		if ( ! is_array( $mimes ) ) {
			$mimes = array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', (string) $mimes ) ) ) );
		}
		$merged['files']['allowed_mimes'] = $mimes;
		$merged['files']['storage']       = $merged['files']['storage'] === 'local' ? 'local' : 'uploads';
		$merged['files']['local_path']    = rtrim( sanitize_text_field( (string) ( $merged['files']['local_path'] ?? '' ) ) );

		$merged['emails']['from_name']        = sanitize_text_field( (string) ( $merged['emails']['from_name'] ?? '' ) );
		$email                                = sanitize_email( (string) ( $merged['emails']['from_email'] ?? '' ) );
		$merged['emails']['from_email']       = is_email( $email ) ? $email : '';
		$merged['emails']['reply_to']         = sanitize_email( (string) ( $merged['emails']['reply_to'] ?? '' ) );
		$merged['emails']['admin_recipients'] = sanitize_text_field( (string) ( $merged['emails']['admin_recipients'] ?? '' ) );

		$merged['attendance']['policy_days'] = max( 1, (int) ( $merged['attendance']['policy_days'] ?? 7 ) );
		$types                               = $merged['attendance']['types'];
		if ( ! is_array( $types ) ) {
			$types = array_filter( array_map( 'sanitize_key', explode( ',', (string) $types ) ) );
		} else {
			$types = array_map( 'sanitize_key', $types );
		}
		$merged['attendance']['types'] = $types ?: array( 'in_person' );

		$merged['privacy']['retention_months'] = max( 0, (int) ( $merged['privacy']['retention_months'] ?? 24 ) );
		$merged['privacy']['anonymise_files']  = in_array( $merged['privacy']['anonymise_files'], array( 'delete', 'keep', 'move' ), true ) ? $merged['privacy']['anonymise_files'] : 'delete';

		unset( $merged['encryption'] );

		return update_option( self::KEY, $merged );
	}
}
