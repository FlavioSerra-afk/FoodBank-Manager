<?php
// phpcs:ignoreFile
/**
 * Options storage and helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Mail\Templates as MailTemplates;
use FoodBankManager\Forms\Presets as FormPresets;
use FoodBankManager\Security\CssSanitizer;

/**
 * Plugin options utility.
 */
class Options {
        private const KEY = 'fbm_settings';

       /**
        * Validation schema for typed settings.
        *
        * @var array<string,array<string,array<string,mixed>>>
        */
       private const SCHEMA = array(
               'branding' => array(
                       'site_name' => array(
                               'type'    => 'string',
                               'default' => '',
                       ),
                       'logo_url'  => array(
                               'type'    => 'url',
                               'default' => '',
                       ),
                       'color'     => array(
                               'type'    => 'enum',
                               'enum'    => array( 'default', 'blue', 'green', 'red', 'orange', 'purple' ),
                               'default' => 'default',
                       ),
               ),
               'emails'   => array(
                       'from_name'  => array(
                               'type'    => 'string',
                               'default' => '',
                       ),
                       'from_email' => array(
                               'type'    => 'email',
                               'default' => '',
                       ),
                       'reply_to'   => array(
                               'type'    => 'email',
                               'default' => '',
                       ),
               ),
               'theme'    => array(
                       'primary_color'    => array(
                               'type'    => 'string',
                               'regex'   => '/^#([0-9a-fA-F]{6})$/',
                               'default' => '#3b82f6',
                               'max'     => 7,
                       ),
                       'density'          => array(
                               'type'    => 'enum',
                               'enum'    => array( 'compact', 'comfortable' ),
                               'default' => 'comfortable',
                       ),
                       'font_family'      => array(
                               'type'    => 'enum',
                               'enum'    => array( 'system', 'inter', 'roboto', 'open-sans' ),
                               'default' => 'system',
                       ),
                       'dark_mode_default' => array(
                               'type'    => 'bool',
                               'default' => false,
                       ),
                       'custom_css'       => array(
                               'type'              => 'raw',
                               'sanitize_callback' => array( CssSanitizer::class, 'sanitize' ),
                               'default'           => '',
                               'max'               => 8192,
                               'truncate'          => true,
                       ),
               ),
       );

       /**
        * Ensure options are loaded early.
        */
       public static function boot(): void {
               self::all();
       }

	/**
	 * Default settings structure.
	 *
	 * @return array<string,mixed>
	 */
	private static function defaults(): array {
               return array(
                       'branding'   => array(
                               'site_name' => '',
                               'logo_url'  => '',
                               'color'     => 'default',
                       ),
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
                        'email_templates' => array(),
                        'attendance' => array(
                                'policy_days' => 7,
                                'types'       => array( 'in_person', 'delivery', 'other' ),
                        ),
                        'privacy'    => array(
                                'retention_months' => 24,
                                'anonymise_files'  => 'delete',
                                'retention'        => array(),
                        ),
                       'theme'      => self::theme_defaults(),
                       'encryption' => array(),
                       'form_presets_custom' => array(),
                       'db_filter_presets'   => array(),
               );
       }

		/**
		 * Default theme values.
		 *
		 * @return array<string,mixed>
		 */
       private static function theme_defaults(): array {
               return array(
                       'primary_color'    => '#3b82f6',
                       'density'          => 'comfortable',
                       'font_family'      => 'system',
                       'dark_mode_default' => false,
                       'custom_css'       => '',
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
	 * @param string $path          Dot notation path.
	 * @param mixed  $default_value Default value if missing.
	 * @return mixed
	 */
	public static function get( string $path, $default_value = null ) {
			$settings = self::all();
		foreach ( explode( '.', $path ) as $segment ) {
			if ( ! is_array( $settings ) || ! array_key_exists( $segment, $settings ) ) {
				return $default_value;
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
		 * @return bool
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
        * Update options using schema validation.
        *
        * When called with a string path, acts as a back-compat wrapper for set().
        * When passed an array, validates and saves known groups/fields.
        *
        * @param string|array<string,mixed> $path  Option path or data array.
        * @param mixed                      $value Value to set when using path mode.
        * @return bool
        */
       public static function update( $path, $value = null ): bool {
               if ( is_array( $path ) && null === $value ) {
                       return self::update_many( $path );
               }

               return self::set( (string) $path, $value );
       }

       /**
        * Validate and persist multiple settings.
        *
        * @param array<string,mixed> $new_settings Incoming settings grouped by section.
        * @return bool
        */
       private static function update_many( array $new_settings ): bool {
               $saved = get_option( self::KEY, array() );
               if ( ! is_array( $saved ) ) {
                       $saved = array();
               }

               foreach ( self::SCHEMA as $group => $fields ) {
                       if ( ! isset( $new_settings[ $group ] ) || ! is_array( $new_settings[ $group ] ) ) {
                               continue;
                       }

                       $group_data = $saved[ $group ] ?? array();
                       foreach ( $fields as $key => $rules ) {
                               if ( ! array_key_exists( $key, $new_settings[ $group ] ) ) {
                                       continue;
                               }

                               $sanitized = self::sanitize_field( $new_settings[ $group ][ $key ], $rules );
                               if ( null === $sanitized ) {
                                       continue;
                               }

                               $group_data[ $key ] = $sanitized;
                       }

                       // Drop unknown keys.
                       $saved[ $group ] = array_intersect_key( $group_data, $fields );
               }

               return update_option( self::KEY, $saved );
       }

       /**
        * Sanitize a field based on schema rules.
        *
        * @param mixed                       $value Raw value.
        * @param array<string,mixed>         $rules Schema rules.
        * @return mixed|null Sanitized value or null if invalid/empty.
        */
       private static function sanitize_field( $value, array $rules ) {
               $max = isset( $rules['max'] ) ? (int) $rules['max'] : 1024;
               if ( is_string( $value ) && strlen( $value ) > $max ) {
                       if ( empty( $rules['truncate'] ) ) {
                               return null;
                       }
                       $value = substr( $value, 0, $max );
               }

               switch ( $rules['type'] ) {
                       case 'string':
                               $value = sanitize_text_field( (string) $value );
                               if ( isset( $rules['regex'] ) && ! preg_match( $rules['regex'], $value ) ) {
                                       $value = $rules['default'] ?? '';
                               }
                               break;
                       case 'raw':
                               $value = (string) $value;
                               break;
                       case 'url':
                               $value = esc_url_raw( (string) $value );
                               break;
                       case 'email':
                               $value = sanitize_email( (string) $value );
                               if ( $value === '' || ! is_email( $value ) ) {
                                       return null;
                               }
                               break;
                       case 'enum':
                               $value   = sanitize_text_field( (string) $value );
                               $allowed = $rules['enum'] ?? array();
                               if ( ! in_array( $value, $allowed, true ) ) {
                                       $value = $rules['default'] ?? '';
                               }
                               break;
                       case 'bool':
                               $value = ! empty( $value );
                               break;
                       default:
                               return null;
               }

               if ( isset( $rules['sanitize_callback'] ) && is_callable( $rules['sanitize_callback'] ) ) {
                       $value = call_user_func( $rules['sanitize_callback'], $value );
               }

               if ( is_string( $value ) && strlen( $value ) > $max ) {
                       if ( empty( $rules['truncate'] ) ) {
                               return null;
                       }
                       $value = substr( $value, 0, $max );
               }

               return $value;
       }

		/**
		 * Persist an array of settings, merged with defaults.
		 * Values are sanitized based on known structure.
		 *
		 * @param array<string,mixed> $new_settings Settings from request.
		 * @return bool
		 */
	public static function saveAll( array $new_settings ): bool {
			$defaults = self::defaults();
			$merged   = array_replace_recursive( $defaults, $new_settings );

       // Basic sanitization.
       $merged['branding']['site_name'] = sanitize_text_field( (string) ( $merged['branding']['site_name'] ?? '' ) );
       $merged['branding']['logo_url']  = esc_url_raw( (string) ( $merged['branding']['logo_url'] ?? '' ) );
       $colors                           = array( 'default', 'blue', 'green', 'red', 'orange', 'purple' );
       $merged['branding']['color']     = in_array( $merged['branding']['color'] ?? 'default', $colors, true ) ? $merged['branding']['color'] : 'default';
       $merged['branding']              = array_intersect_key(
               $merged['branding'],
               array( 'site_name' => true, 'logo_url' => true, 'color' => true )
       );

       $merged['general']['org_name']    = sanitize_text_field( (string) ( $merged['general']['org_name'] ?? '' ) );
       $merged['general']['logo_id']     = (int) ( $merged['general']['logo_id'] ?? 0 );
       $merged['general']['date_format'] = sanitize_text_field( (string) ( $merged['general']['date_format'] ?? '' ) );

			$merged['forms']['captcha_provider']     = in_array(
				$merged['forms']['captcha_provider'],
				array( 'off', 'recaptcha', 'turnstile' ),
				true
			) ? $merged['forms']['captcha_provider'] : 'off';
		$merged['forms']['captcha_site_key']         = sanitize_text_field( (string) ( $merged['forms']['captcha_site_key'] ?? '' ) );
		$merged['forms']['captcha_secret']           = sanitize_text_field( (string) ( $merged['forms']['captcha_secret'] ?? '' ) );
		$merged['forms']['honeypot']                 = ! empty( $merged['forms']['honeypot'] );
			$merged['forms']['rate_limit_per_ip']    = max( 0, (int) ( $merged['forms']['rate_limit_per_ip'] ?? 60 ) );
			$merged['forms']['consent_text']         = wp_kses_post( (string) ( $merged['forms']['consent_text'] ?? '' ) );
		$merged['forms']['success_redirect_page_id'] = (int) ( $merged['forms']['success_redirect_page_id'] ?? 0 );

		$merged['files']['max_size_mb'] = max( 1, (int) ( $merged['files']['max_size_mb'] ?? 5 ) );
		$mimes                          = $merged['files']['allowed_mimes'];
		if ( ! is_array( $mimes ) ) {
			$mimes = array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', (string) $mimes ) ) ) );
		}
		$merged['files']['allowed_mimes']  = $mimes;
			$merged['files']['storage']    = $merged['files']['storage'] === 'local' ? 'local' : 'uploads';
			$merged['files']['local_path'] = rtrim( sanitize_text_field( (string) ( $merged['files']['local_path'] ?? '' ) ) );

		$merged['emails']['from_name']        = sanitize_text_field( (string) ( $merged['emails']['from_name'] ?? '' ) );
		$email                                = sanitize_email( (string) ( $merged['emails']['from_email'] ?? '' ) );
		$merged['emails']['from_email']       = is_email( $email ) ? $email : '';
		$merged['emails']['reply_to']         = sanitize_email( (string) ( $merged['emails']['reply_to'] ?? '' ) );
		$merged['emails']['admin_recipients'] = sanitize_text_field( (string) ( $merged['emails']['admin_recipients'] ?? '' ) );

		$merged['attendance']['policy_days'] = max( 1, (int) ( $merged['attendance']['policy_days'] ?? 7 ) );
			$types                           = $merged['attendance']['types'];
		if ( ! is_array( $types ) ) {
				$types = array_filter( array_map( 'sanitize_key', explode( ',', (string) $types ) ) );
		} else {
				$types = array_map( 'sanitize_key', $types );
		}
			$merged['attendance']['types'] = $types ? $types : array( 'in_person' );

			$merged['privacy']['retention_months'] = max( 0, (int) ( $merged['privacy']['retention_months'] ?? 24 ) );
			$merged['privacy']['anonymise_files']  = in_array(
				$merged['privacy']['anonymise_files'],
				array( 'delete', 'keep', 'move' ),
				true
			) ? $merged['privacy']['anonymise_files'] : 'delete';

                       $merged['theme'] = self::sanitize_theme( (array) ( $merged['theme'] ?? array() ) );

			unset( $merged['encryption'] );

			return update_option( self::KEY, $merged );
	}

		/**
		 * Sanitize theme settings.
		 *
		 * @param array<string,mixed> $data Raw theme settings.
		 * @return array<string,mixed>
		 */
       public static function sanitize_theme( array $data ): array {
               $out   = self::theme_defaults();
               $color = (string) ( $data['primary_color'] ?? '' );
               if ( preg_match( '/^#([0-9a-fA-F]{6})$/', $color ) ) {
                       $out['primary_color'] = strtolower( $color );
               }
               $densities = array( 'compact', 'comfortable' );
               if ( isset( $data['density'] ) && in_array( $data['density'], $densities, true ) ) {
                       $out['density'] = $data['density'];
               }
               $fonts = array( 'system', 'inter', 'roboto', 'open-sans' );
               if ( isset( $data['font_family'] ) && in_array( $data['font_family'], $fonts, true ) ) {
                       $out['font_family'] = $data['font_family'];
               }
               $out['dark_mode_default'] = ! empty( $data['dark_mode_default'] );
               $css = (string) ( $data['custom_css'] ?? '' );
               if ( strlen( $css ) > 8192 ) {
                       $css = substr( $css, 0, 8192 );
               }
               $css = CssSanitizer::sanitize( $css );
               if ( $css !== '' ) {
                       $out['custom_css'] = $css;
               }
               return $out;
       }

       /**
        * Retrieve custom form presets.
        *
        * @return array<string,array<int,array<string,mixed>>>
        */
       public static function get_form_presets_custom(): array {
               $raw = self::get( 'form_presets_custom', array() );
               if ( ! is_array( $raw ) ) {
                       return array();
               }
               return FormPresets::sanitize_all( $raw );
       }

       /**
        * Save custom form presets.
        *
        * @param array<string,mixed> $presets Presets.
        * @return bool
        */
       public static function set_form_presets_custom( array $presets ): bool {
               $clean = FormPresets::sanitize_all( $presets );
               return self::set( 'form_presets_custom', $clean );
       }

       /**
        * Allowed query keys for DB presets.
        *
        * @return array<int,string>
        */
       public static function db_filter_allowed_keys(): array {
               return array(
                       'status',
                       'form_id',
                       'date_from',
                       'date_to',
                       'search',
                       'orderby',
                       'order',
                       'per_page',
                       'page',
                       'has_file',
                       'consent',
               );
       }

       /**
        * Get saved DB filter presets.
        *
        * @return array<int,array{name:string,query:array<string,string>}> Presets.
        */
       public static function get_db_filter_presets(): array {
               $raw = self::get( 'db_filter_presets', array() );
               if ( ! is_array( $raw ) ) {
                       return array();
               }
               $allowed = self::db_filter_allowed_keys();
               $out     = array();
               foreach ( $raw as $preset ) {
                       if ( ! is_array( $preset ) ) {
                               continue;
                       }
                       $name  = sanitize_text_field( (string) ( $preset['name'] ?? '' ) );
                       $query = array();
                       if ( isset( $preset['query'] ) && is_array( $preset['query'] ) ) {
                               foreach ( $preset['query'] as $k => $v ) {
                                       $k = sanitize_key( (string) $k );
                                       if ( ! in_array( $k, $allowed, true ) ) {
                                               continue;
                                       }
                                       $query[ $k ] = sanitize_text_field( (string) $v );
                               }
                       }
                       if ( '' !== $name && ! empty( $query ) ) {
                               $out[] = array(
                                       'name'  => $name,
                                       'query' => $query,
                               );
                       }
               }
               return $out;
       }

       /**
        * Save DB filter presets.
        *
        * @param array<int,array<string,mixed>> $presets Presets.
        * @return bool
        */
       public static function set_db_filter_presets( array $presets ): bool {
               $allowed = self::db_filter_allowed_keys();
               $clean   = array();
               $names   = array();
               foreach ( $presets as $preset ) {
                       if ( ! is_array( $preset ) ) {
                               continue;
                       }
                       $name = trim( sanitize_text_field( (string) ( $preset['name'] ?? '' ) ) );
                       if ( '' === $name || strlen( $name ) > 50 ) {
                               continue;
                       }
                       $name_lc = strtolower( $name );
                       if ( in_array( $name_lc, $names, true ) ) {
                               continue;
                       }
                       $query = array();
                       if ( isset( $preset['query'] ) && is_array( $preset['query'] ) ) {
                               foreach ( $preset['query'] as $k => $v ) {
                                       $k = sanitize_key( (string) $k );
                                       if ( ! in_array( $k, $allowed, true ) ) {
                                               continue;
                                       }
                                       $query[ $k ] = sanitize_text_field( (string) $v );
                               }
                       }
                       $clean[] = array(
                               'name'  => $name,
                               'query' => $query,
                       );
                       $names[] = $name_lc;
                       if ( count( $clean ) >= 20 ) {
                               break;
                       }
               }
               return self::set( 'db_filter_presets', $clean );
       }

       /**
        * Retrieve an email template override.
        *
        * @param string $id Template ID.
        * @return array{subject:string,body_html:string,updated_at:string}
        */
    public static function get_template( string $id ): array {
               if ( ! self::is_valid_template_id( $id ) ) {
                       return array(
                               'subject'   => '',
                               'body_html' => '',
                               'updated_at' => '',
                       );
               }

               $all = self::get( 'email_templates', array() );
               if ( ! is_array( $all ) ) {
                       $all = array();
               }
               $tpl = $all[ $id ] ?? array();

               return array(
                       'subject'   => (string) ( $tpl['subject'] ?? '' ),
                       'body_html' => (string) ( $tpl['body_html'] ?? '' ),
                       'updated_at' => (string) ( $tpl['updated_at'] ?? '' ),
               );
       }

       /**
        * Save an email template override.
        *
        * @param string               $id   Template ID.
        * @param array<string,string> $data Template data.
        * @return bool
        */
       public static function set_template( string $id, array $data ): bool {
               if ( ! self::is_valid_template_id( $id ) ) {
                       return false;
               }

               $subject = (string) ( $data['subject'] ?? '' );
               $subject = function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $subject ) : strip_tags( $subject ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Fallback when WordPress is unavailable.
               $subject = substr( $subject, 0, 255 );

               $body = (string) ( $data['body_html'] ?? '' );
               $body = function_exists( 'wp_kses_post' ) ? wp_kses_post( $body ) : $body;
               if ( strlen( $body ) > 32768 ) {
                       $body = substr( $body, 0, 32768 );
               }

               return self::set(
                       'email_templates.' . $id,
                       array(
                               'subject'    => $subject,
                               'body_html'  => $body,
                               'updated_at' => function_exists( 'current_time' ) ? current_time( 'mysql' ) : gmdate( 'Y-m-d H:i:s' ),
                       )
               );
       }

       /**
        * Reset an email template override.
        *
        * @param string $id Template ID.
        * @return bool
        */
       public static function reset_template( string $id ): bool {
               if ( ! self::is_valid_template_id( $id ) ) {
                       return false;
               }

               $settings = self::all();
               if ( isset( $settings['email_templates'][ $id ] ) ) {
                       unset( $settings['email_templates'][ $id ] );
                       return update_option( self::KEY, $settings );
               }

               return true;
       }

       /**
        * Determine whether a template ID is valid.
        *
        * @param string $id Template ID.
        * @return bool
        */
       private static function is_valid_template_id( string $id ): bool {
               $ids = array_keys( MailTemplates::defaults() );
               return in_array( $id, $ids, true );
       }
}
