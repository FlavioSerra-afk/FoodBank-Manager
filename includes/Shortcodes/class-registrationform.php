<?php
/**
 * Registration form shortcode handler.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Email\RegistrationNotificationMailer;
use FoodBankManager\Email\WelcomeMailer;
use FoodBankManager\Registration\Editor\TemplateDefaults;
use FoodBankManager\Registration\Editor\TemplateRenderer;
use FoodBankManager\Registration\Editor\TagParser;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Registration\RegistrationService;
use FoodBankManager\Registration\RegistrationSettings;
use FoodBankManager\Registration\Uploads;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use wpdb;
use function add_shortcode;
use function apply_filters;
use function array_key_exists;
use function array_merge;
use function array_unique;
use function delete_transient;
use function esc_html__;
use function filter_input;
use function filter_var;
use function get_option;
use function get_transient;
use function is_array;
use function is_email;
use function is_numeric;
use function is_readable;
use function is_string;
use function md5;
use function ob_get_clean;
use function ob_start;
use function preg_match;
use function preg_replace;
use function sanitize_email;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function set_transient;
use function strtolower;
use function substr;
use function time;
use function trim;
use function wp_nonce_field;
use function wp_verify_nonce;
use const FILTER_SANITIZE_EMAIL;
use const FILTER_SANITIZE_FULL_SPECIAL_CHARS;
use const FILTER_SANITIZE_NUMBER_INT;
use const FILTER_UNSAFE_RAW;
use const INPUT_POST;
use const INPUT_SERVER;

/**
 * Renders the registration shortcode.
 */
final class RegistrationForm {
	private const SHORTCODE                   = 'fbm_registration_form';
	private const NONCE_ACTION                = 'fbm_registration_submit';
	private const NONCE_FIELD                 = 'fbm_registration_nonce';
	private const HONEYPOT_FIELD              = 'fbm_registration_hp';
	private const TIME_TRAP_FIELD             = 'fbm_registration_time';
	private const SUBMIT_FIELD                = 'fbm_registration_submitted';
	private const TEMPLATE_OPTION             = 'fbm_registration_template';
	private const SETTINGS_OPTION             = 'fbm_registration_settings';
	private const DEFAULT_HOUSEHOLD_SIZE      = 1;
	private const HOUSEHOLD_SIZE_CAP          = 20;
	private const MIN_TIME_TRAP_THRESHOLD     = 5;
	private const LAST_INITIAL_PATTERN        = '/^[A-Z]$/';
	private const SUBMISSION_COOLDOWN_DEFAULT = 120;
	private const SUBMISSION_COOLDOWN_FILTER  = 'fbm_registration_submission_cooldown';

		/**
		 * Optional welcome mailer factory override for testing.
		 *
		 * @var callable|null
		 */
	private static $mailer_factory = null;

		/**
		 * Optional notification mailer factory for testing.
		 *
		 * @var callable|null
		 */
	private static $notification_factory = null;

		/**
		 * Cached template renderer instance.
		 *
		 * @var TemplateRenderer|null
		 */
	private static ?TemplateRenderer $renderer = null;

		/**
		 * Cached tag parser instance.
		 *
		 * @var TagParser|null
		 */
	private static ?TagParser $parser = null;

		/**
		 * Override the welcome mailer dependency.
		 *
		 * @internal
		 *
		 * @param callable|null $factory Factory returning a mailer instance for test scenarios.
		 */
	public static function set_mailer_override( ?callable $factory ): void {
			self::$mailer_factory = $factory;
	}

		/**
		 * Override the notification mailer dependency.
		 *
		 * @internal
		 *
		 * @param callable|null $factory Factory returning a notification mailer instance.
		 */
	public static function set_notification_override( ?callable $factory ): void {
			self::$notification_factory = $factory;
	}

		/**
		 * Register the shortcode handler.
		 */
	public static function register(): void {
			add_shortcode( self::SHORTCODE, array( self::class, 'render' ) );
	}

		/**
		 * Render the shortcode output.
		 *
		 * @param array<string, mixed> $atts Shortcode attributes (unused).
		 */
	public static function render( array $atts = array() ): string {
			unset( $atts );

			$template   = self::current_template();
			$schema     = self::resolve_schema( $template );
			$settings   = self::registration_settings();
			$submission = self::handle_submission( $schema['fields'], $settings );

			$renderer      = self::renderer();
			$rendered_form = $renderer->render( $schema['template'], $submission['values'], $submission['field_errors'] );

			$context = array(
				'success'        => $submission['success'],
				'errors'         => $submission['errors'],
				'message'        => $submission['message'],
				'field_errors'   => $submission['field_errors'],
				'form_html'      => $rendered_form['html'],
				'nonce_field'    => wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD, false, false ),
				'honeypot_field' => self::HONEYPOT_FIELD,
				'time_field'     => self::TIME_TRAP_FIELD,
				'timestamp'      => time(),
				'action'         => self::current_action_url(),
				'submit_field'   => self::SUBMIT_FIELD,
				'settings'       => array(
					'honeypot'          => ! empty( $settings['honeypot'] ),
					'auto_approve'      => ( new RegistrationSettings() )->auto_approve(),
					'template_warnings' => array_merge( $schema['warnings'], $rendered_form['warnings'] ),
				),
				'variant'        => $submission['variant'],
			);

			return self::render_template( $context );
	}

	/**
	 * Resolve the stored registration template.
	 */
	private static function current_template(): string {
		$stored = get_option( self::TEMPLATE_OPTION, '' );

		return is_string( $stored ) && '' !== trim( $stored ) ? $stored : TemplateDefaults::template();
	}

		/**
		 * Merge stored settings with defaults.
		 *
		 * @return array<string,mixed>
		 */
	private static function registration_settings(): array {
			$defaults = TemplateDefaults::settings();
			$stored   = get_option( self::SETTINGS_OPTION, array() );

		if ( ! is_array( $stored ) ) {
				$stored = array();
		}

			$settings = array_merge( $defaults, $stored );

		if ( ! isset( $settings['messages'] ) || ! is_array( $settings['messages'] ) ) {
				$settings['messages'] = $defaults['messages'];
		} else {
				$settings['messages'] = array_merge( $defaults['messages'], $settings['messages'] );
		}

		if ( ! isset( $settings['uploads'] ) || ! is_array( $settings['uploads'] ) ) {
				$settings['uploads'] = $defaults['uploads'];
		}

		if ( ! isset( $settings['editor'] ) || ! is_array( $settings['editor'] ) ) {
				$settings['editor'] = $defaults['editor'];
		} else {
				$settings['editor'] = array_merge( $defaults['editor'], $settings['editor'] );
		}

		if ( ! isset( $settings['conditions'] ) || ! is_array( $settings['conditions'] ) ) {
				$settings['conditions'] = $defaults['conditions'];
		}

			$settings['uploads']  = Uploads::normalize_settings( $settings['uploads'] );
			$settings['honeypot'] = isset( $settings['honeypot'] ) ? (bool) $settings['honeypot'] : true;

			return $settings;
	}

		/**
		 * Handle registration submission.
		 *
		 * @param array<string,array<string,mixed>> $fields   Field schema parsed from the template.
		 * @param array<string,mixed>               $settings Registration settings.
		 *
		 * @return array{success:bool,errors:array<int,string>,message:string,field_errors:array<string,array<int,string>>,values:array<string,mixed>,variant:string}
		 */
	private static function handle_submission( array $fields, array $settings ): array {
			$values = array();
				/**
				 * Collected validation errors keyed by field name.
				 *
				 * @var array<string,array<int,string>> $field_errors
				 */
				$field_errors = array();

		foreach ( $fields as $name => $definition ) {
				$values[ $name ]       = self::default_value_for_field( $definition );
				$field_errors[ $name ] = array();
		}

				/**
				 * Aggregated submission outcome state.
				 *
				 * @var array{
				 *     success:bool,
				 *     errors:array<int,string>,
				 *     message:string,
				 *     field_errors:array<string,array<int,string>>,
				 *     values:array<string,mixed>,
				 *     variant:string
				 * } $result
				 */
			$result = array(
				'success'      => false,
				'errors'       => array(),
				'message'      => '',
				'field_errors' => $field_errors,
				'values'       => $values,
				'variant'      => ( new RegistrationSettings() )->auto_approve() ? 'auto' : 'pending',
			);

			$method = self::read_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			if ( ! is_string( $method ) || 'POST' !== strtoupper( $method ) ) {
					return $result;
			}

			$submitted = self::read_input( INPUT_POST, self::SUBMIT_FIELD, FILTER_SANITIZE_NUMBER_INT );
			if ( null === $submitted ) {
					return $result;
			}

			$nonce = self::read_input( INPUT_POST, self::NONCE_FIELD, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
					$result['errors'][] = esc_html__( 'Security check failed. Please try again.', 'foodbank-manager' );

					return $result;
			}

			if ( ! empty( $settings['honeypot'] ) ) {
					$honeypot = self::read_input( INPUT_POST, self::HONEYPOT_FIELD, FILTER_UNSAFE_RAW );
				if ( is_string( $honeypot ) && '' !== trim( $honeypot ) ) {
						$result['errors'][] = esc_html__( 'Invalid submission. Please try again.', 'foodbank-manager' );

						return $result;
				}
			}

			$submitted_at_raw = self::read_input( INPUT_POST, self::TIME_TRAP_FIELD, FILTER_SANITIZE_NUMBER_INT );
			$submitted_at     = is_string( $submitted_at_raw ) ? (int) $submitted_at_raw : 0;

			if ( $submitted_at <= 0 || ( time() - $submitted_at ) < self::MIN_TIME_TRAP_THRESHOLD ) {
					$result['errors'][] = esc_html__( 'Invalid submission. Please try again.', 'foodbank-manager' );

					return $result;
			}

			$normalized = array(
				'first_name'     => '',
				'last_initial'   => '',
				'email'          => '',
				'household_size' => self::DEFAULT_HOUSEHOLD_SIZE,
				'consent'        => false,
			);

			$uploads     = array();
			$stored_meta = array();

			foreach ( $fields as $name => $definition ) {
					$type = isset( $definition['type'] ) ? (string) $definition['type'] : 'text';

				if ( 'file' === $type ) {
						$file = self::read_file_input( $name );
					if ( null === $file ) {
						if ( ! empty( $definition['required'] ) ) {
								$result['field_errors'][ $name ][] = esc_html__( 'This file is required.', 'foodbank-manager' );
						}

						continue;
					}

						$upload = Uploads::process( $name, $file, $settings['uploads'] );

					if ( 'error' === $upload['status'] ) {
							$result['field_errors'][ $name ][] = isset( $upload['error'] ) ? (string) $upload['error'] : esc_html__( 'Unable to process the uploaded file.', 'foodbank-manager' );
					} elseif ( 'stored' === $upload['status'] ) {
							$uploads[]     = array(
								'attachment_id' => (int) $upload['attachment_id'],
								'path'          => (string) $upload['path'],
							);
							$stored_meta[] = array(
								'attachment_id' => (int) $upload['attachment_id'],
								'url'           => (string) $upload['url'],
								'type'          => (string) $upload['type'],
							);
					}

						continue;
				}

					$raw_value = self::read_input( INPUT_POST, $name, FILTER_UNSAFE_RAW );

					$sanitized       = self::sanitize_field_value( $definition, $raw_value );
					$values[ $name ] = $sanitized['value'];

				if ( ! empty( $sanitized['errors'] ) ) {
						$result['field_errors'][ $name ] = array_unique( array_merge( $result['field_errors'][ $name ], $sanitized['errors'] ) );
				}

				if ( empty( $definition['required'] ) && empty( $sanitized['value'] ) ) {
						continue;
				}

				if ( empty( $definition['required'] ) && empty( $sanitized['errors'] ) ) {
						self::maybe_map_canonical( $normalized, $definition, $sanitized['value'] );
						continue;
				}

				if ( empty( $definition['required'] ) ) {
						continue;
				}

				if ( empty( $sanitized['value'] ) ) {
						$result['field_errors'][ $name ][] = esc_html__( 'This field is required.', 'foodbank-manager' );
						continue;
				}

					self::maybe_map_canonical( $normalized, $definition, $sanitized['value'] );
			}

			if ( ! empty( $result['errors'] ) ) {
					Uploads::cleanup( $uploads );

					return $result;
			}

			$has_field_errors = false;

			foreach ( $result['field_errors'] as $field_name => $field_error ) {
				if ( empty( $field_error ) ) {
						continue;
				}

					$has_field_errors = true;
					$canonical        = self::canonical_key( $field_name );

				switch ( $canonical ) {
					case 'first_name':
							$result['errors'][] = esc_html__( 'First name is required.', 'foodbank-manager' );
						break;
					case 'last_initial':
							$result['errors'][] = esc_html__( 'Last initial must be a single letter.', 'foodbank-manager' );
						break;
					case 'email':
							$result['errors'][] = esc_html__( 'A valid email address is required.', 'foodbank-manager' );
						break;
					default:
						foreach ( $field_error as $message ) {
							if ( is_string( $message ) && '' !== $message ) {
								$result['errors'][] = $message;
							}
						}
				}
			}

			if ( $has_field_errors ) {
				if ( empty( $result['message'] ) ) {
						$result['message'] = esc_html__( 'Please correct the errors below and try again.', 'foodbank-manager' );
				}

				if ( ! empty( $result['errors'] ) ) {
						$result['errors'] = array_values( array_unique( $result['errors'] ) );
				}

					Uploads::cleanup( $uploads );

					return $result;
			}

			if ( '' === $normalized['first_name'] ) {
					$result['errors'][] = esc_html__( 'First name is required.', 'foodbank-manager' );
			}

			if ( '' === $normalized['last_initial'] || 1 !== preg_match( self::LAST_INITIAL_PATTERN, $normalized['last_initial'] ) ) {
					$result['errors'][] = esc_html__( 'Last initial must be a single letter.', 'foodbank-manager' );
			}

			if ( '' === $normalized['email'] || ! is_email( $normalized['email'] ) ) {
					$result['errors'][] = esc_html__( 'A valid email address is required.', 'foodbank-manager' );
			}

			if ( $normalized['household_size'] < 1 ) {
					$definition = self::find_canonical_definition( $fields, 'household_size' );

				if ( is_array( $definition ) ) {
						$normalized['household_size'] = self::clamp_household_size( (int) $normalized['household_size'], $definition );
				} else {
						$normalized['household_size'] = max( self::DEFAULT_HOUSEHOLD_SIZE, min( self::HOUSEHOLD_SIZE_CAP, (int) $normalized['household_size'] ) );
				}
			}

			if ( ! empty( $result['errors'] ) ) {
					Uploads::cleanup( $uploads );

					return $result;
			}

			$throttle_key = null;
			$cooldown     = self::get_submission_cooldown();
			$fingerprint  = self::build_submission_fingerprint( $normalized['email'] );

			if ( $cooldown > 0 && null !== $fingerprint ) {
					$throttle_key    = 'fbm_registration_cooldown_' . md5( $fingerprint );
					$last_submission = get_transient( $throttle_key );
				if ( is_numeric( $last_submission ) && ( time() - (int) $last_submission ) < $cooldown ) {
						$result['errors'][] = esc_html__( 'Please wait before submitting again.', 'foodbank-manager' );
						$result['message']  = esc_html__( 'Please wait before submitting again.', 'foodbank-manager' );
						Uploads::cleanup( $uploads );

						return $result;
				}

					set_transient( $throttle_key, time(), $cooldown );
			}

			global $wpdb;

			if ( ! $wpdb instanceof wpdb ) {
					$result['errors'][] = esc_html__( 'Service temporarily unavailable. Please try again later.', 'foodbank-manager' );
					Uploads::cleanup( $uploads );

					return $result;
			}

			$repository       = new MembersRepository( $wpdb );
			$token_repository = new TokenRepository( $wpdb );
			$token_service    = new TokenService( $token_repository );
			$service          = new RegistrationService( $repository, $token_service );

			$consent_timestamp = $normalized['consent'] ? time() : null;

			$outcome = $service->register(
				$normalized['first_name'],
				$normalized['last_initial'],
				$normalized['email'],
				(int) $normalized['household_size'],
				$consent_timestamp
			);

		if ( null === $outcome ) {
				$result['errors'][] = esc_html__( 'We could not save your registration. Please try again later.', 'foodbank-manager' );
				Uploads::cleanup( $uploads );

				return $result;
		}

			$status    = (string) ( $outcome['status'] ?? MembersRepository::STATUS_ACTIVE );
			$token_raw = $outcome['token'] ?? null;
			$token     = is_string( $token_raw ) ? $token_raw : '';

		if ( MembersRepository::STATUS_ACTIVE === $status ) {
				$service->ensure_foodbank_member_user( $normalized['email'], $normalized['first_name'], $normalized['last_initial'] );

			if ( '' !== $token ) {
					$mailer = is_callable( self::$mailer_factory ) ? call_user_func( self::$mailer_factory ) : new WelcomeMailer();
				if ( ! $mailer->send( $normalized['email'], $normalized['first_name'], $outcome['member_reference'], $token ) ) {
					$log = new MailFailureLog();
					$log->record_failure(
						(int) $outcome['member_id'],
						$outcome['member_reference'],
						$normalized['email'],
						MailFailureLog::CONTEXT_REGISTRATION,
						MailFailureLog::ERROR_MAIL
					);
				}
			}

				$message           = isset( $settings['messages']['success_auto'] ) ? (string) $settings['messages']['success_auto'] : esc_html__( 'Thank you for registering. We have emailed your check-in QR code.', 'foodbank-manager' );
				$result['message'] = $message;
				$result['variant'] = 'auto';
		} else {
				$message           = isset( $settings['messages']['success_pending'] ) ? (string) $settings['messages']['success_pending'] : esc_html__( 'Thank you for registering. Our team will review your application and send your QR code once approved.', 'foodbank-manager' );
				$result['message'] = $message;
				$result['variant'] = 'pending';
		}

			$notification = is_callable( self::$notification_factory ) ? call_user_func( self::$notification_factory ) : new RegistrationNotificationMailer();
			$notification->send( $outcome['member_reference'], $normalized['first_name'], $normalized['last_initial'], $normalized['email'], $status );

		foreach ( $stored_meta as $upload ) {
				$attachment_id = (int) $upload['attachment_id'];
			if ( $attachment_id > 0 ) {
					Uploads::link_to_member( $attachment_id, $outcome['member_reference'] );
			}
		}

		if ( null !== $throttle_key ) {
				delete_transient( $throttle_key );
		}

			$result['success'] = true;
			$result['values']  = array();
		foreach ( $fields as $name => $definition ) {
				$result['values'][ $name ]       = self::default_value_for_field( $definition );
				$result['field_errors'][ $name ] = array();
		}

			return $result;
	}

		/**
		 * Determine default value for the provided field definition.
		 *
		 * @param array<string,mixed> $definition Field definition array.
		 *
		 * @return mixed
		 */
	private static function default_value_for_field( array $definition ) {
			$type = isset( $definition['type'] ) ? (string) $definition['type'] : 'text';

		switch ( $type ) {
			case 'checkbox':
			case 'select':
				return array();
			default:
				return '';
		}
	}

		/**
		 * Sanitize a field value based on its definition.
		 *
		 * @param array<string,mixed> $definition Field definition.
		 * @param mixed               $raw        Raw submitted value.
		 *
		 * @return array{value:mixed,errors:array<int,string>}
		 */
	private static function sanitize_field_value( array $definition, $raw ): array {
			$type    = isset( $definition['type'] ) ? (string) $definition['type'] : 'text';
			$options = isset( $definition['options'] ) && is_array( $definition['options'] ) ? $definition['options'] : array();
			$errors  = array();

		switch ( $type ) {
			case 'email':
				$value = is_string( $raw ) ? sanitize_email( $raw ) : '';
				if ( '' !== $value && ! is_email( $value ) ) {
						$errors[] = esc_html__( 'Enter a valid email address.', 'foodbank-manager' );
				}
				break;
			case 'tel':
			case 'text':
					$value = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
				break;
			case 'textarea':
					$value = is_string( $raw ) ? sanitize_textarea_field( $raw ) : '';
				break;
			case 'number':
					$value = null;

				if ( is_string( $raw ) && '' !== trim( $raw ) ) {
						$value = (int) $raw;
				} elseif ( is_numeric( $raw ) ) {
						$value = (int) $raw;
				}

				if ( null === $value ) {
						$value    = '';
						$errors[] = esc_html__( 'Enter a valid number.', 'foodbank-manager' );
				} else {
						$value = self::clamp_numeric_value( $value, $definition );
				}

				break;
			case 'date':
					$value = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
				break;
			case 'select':
			case 'radio':
					$allowed = array();
				foreach ( $options as $index => $option ) {
					if ( ! is_array( $option ) ) {
						continue;
					}
						$allowed[] = isset( $option['value'] ) ? (string) $option['value'] : (string) $index;
				}
					$value = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
				if ( '' !== $value && ! in_array( $value, $allowed, true ) ) {
						$value    = '';
						$errors[] = esc_html__( 'Choose a valid option.', 'foodbank-manager' );
				}
				break;
			case 'checkbox':
					$allowed = array();
				foreach ( $options as $index => $option ) {
					if ( ! is_array( $option ) ) {
						continue;
					}
						$allowed[] = isset( $option['value'] ) ? (string) $option['value'] : (string) $index;
				}
				if ( is_array( $raw ) ) {
						$value = array();
					foreach ( $raw as $item ) {
						if ( is_string( $item ) ) {
							$item = sanitize_text_field( $item );
							if ( empty( $allowed ) || in_array( $item, $allowed, true ) ) {
											$value[] = $item;
							}
						}
					}
				} else {
						$value = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
					if ( '' !== $value && ( ! empty( $allowed ) && ! in_array( $value, $allowed, true ) ) ) {
							$value = '';
					}
				}
				break;
			default:
					$value = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
				break;
		}

			return array(
				'value'  => $value,
				'errors' => $errors,
			);
	}

		/**
		 * Attempt to map a field value to canonical registration fields.
		 *
		 * @param array<string,mixed> $normalized Canonical values reference.
		 * @param array<string,mixed> $definition Field definition.
		 * @param mixed               $value      Sanitized value.
		 */
	private static function maybe_map_canonical( array &$normalized, array $definition, $value ): void {
			$name = isset( $definition['name'] ) ? (string) $definition['name'] : '';
			$key  = self::canonical_key( $name );

		if ( null === $key ) {
				return;
		}

		switch ( $key ) {
			case 'first_name':
				if ( is_string( $value ) ) {
					$normalized['first_name'] = $value;
				}
				break;
			case 'last_initial':
				if ( is_string( $value ) ) {
						$normalized['last_initial'] = strtoupper( substr( preg_replace( '/[^A-Za-z]/', '', $value ) ?? '', 0, 1 ) );
				}
				break;
			case 'email':
				if ( is_string( $value ) ) {
						$normalized['email'] = sanitize_email( $value );
				}
				break;
			case 'household_size':
				if ( is_numeric( $value ) ) {
						$normalized['household_size'] = self::clamp_household_size( (int) $value, $definition );
				}
				break;
			case 'consent':
				if ( is_array( $value ) ) {
						$normalized['consent'] = self::array_has_truthy_checkbox_value( $value );

						break;
				}

				if ( is_string( $value ) ) {
						$normalized['consent'] = self::is_truthy_checkbox_value( $value );

						break;
				}

					$normalized['consent'] = (bool) $value;
				break;
		}
	}

		/**
		 * Clamp numeric values to the configured range, guarding negatives.
		 *
		 * @param int                 $value      Raw numeric value.
		 * @param array<string,mixed> $definition Field definition containing an optional range map.
		 */
	private static function clamp_numeric_value( int $value, array $definition ): int {
			$range = isset( $definition['range'] ) && is_array( $definition['range'] ) ? $definition['range'] : array();

		if ( $value < 0 ) {
				$value = 0;
		}

		if ( isset( $range['min'] ) && is_numeric( $range['min'] ) ) {
				$value = max( $value, (int) $range['min'] );
		}

		if ( isset( $range['max'] ) && is_numeric( $range['max'] ) ) {
				$value = min( $value, (int) $range['max'] );
		}

			return $value;
	}

		/**
		 * Clamp household size to schema or fallback limits.
		 *
		 * @param int                 $value      Raw household size value.
		 * @param array<string,mixed> $definition Field definition containing range metadata.
		 */
	private static function clamp_household_size( int $value, array $definition ): int {
			$range = isset( $definition['range'] ) && is_array( $definition['range'] ) ? $definition['range'] : array();

			$minimum = self::DEFAULT_HOUSEHOLD_SIZE;
		if ( isset( $range['min'] ) && is_numeric( $range['min'] ) ) {
				$minimum = max( self::DEFAULT_HOUSEHOLD_SIZE, (int) $range['min'] );
		}

			$size = max( $minimum, $value );

			$maximum = null;
		if ( isset( $range['max'] ) && is_numeric( $range['max'] ) ) {
				$candidate = (int) $range['max'];
			if ( $candidate >= $minimum ) {
					$maximum = $candidate;
			}
		}

		if ( null !== $maximum ) {
				$size = min( $size, $maximum );
		} else {
				$size = min( $size, self::HOUSEHOLD_SIZE_CAP );
		}

			return $size;
	}

		/**
		 * Determine whether any value in the checkbox payload is truthy.
		 *
		 * @param array<int,mixed> $values Checkbox submission payload.
		 */
	private static function array_has_truthy_checkbox_value( array $values ): bool {
		foreach ( $values as $value ) {
			if ( is_string( $value ) ) {
				if ( self::is_truthy_checkbox_value( $value ) ) {
						return true;
				}

				continue;
			}

			if ( is_numeric( $value ) ) {
				if ( self::is_truthy_checkbox_value( (string) $value ) ) {
					return true;
				}

					continue;
			}

			if ( ! empty( $value ) ) {
					return true;
			}
		}

			return false;
	}

		/**
		 * Normalize a checkbox value into a boolean.
		 *
		 * @param string $value Checkbox value payload.
		 */
	private static function is_truthy_checkbox_value( string $value ): bool {
			return '' !== trim( $value );
	}

		/**
		 * Map field names to canonical registration keys.
		 *
		 * @param string $name Field name.
		 */
	private static function canonical_key( string $name ): ?string {
			$normalized = strtolower( preg_replace( '/[^a-z0-9]+/', '_', $name ) ?? '' );

		switch ( $normalized ) {
			case 'fbm_first_name':
			case 'first_name':
				return 'first_name';
			case 'fbm_last_initial':
			case 'last_initial':
				return 'last_initial';
			case 'fbm_email':
			case 'email_address':
			case 'email':
				return 'email';
			case 'fbm_household_size':
			case 'household_size':
			case 'household':
				return 'household_size';
			case 'fbm_registration_consent':
			case 'consent':
			case 'email_opt_in':
				return 'consent';
			default:
				return null;
		}
	}

		/**
		 * Locate the field definition for a canonical key.
		 *
		 * @param array<string,array<string,mixed>> $fields    Field definitions keyed by name.
		 * @param string                            $canonical Canonical field identifier.
		 */
	private static function find_canonical_definition( array $fields, string $canonical ): ?array {
		foreach ( $fields as $definition ) {
			if ( ! is_array( $definition ) ) {
				continue;
			}

				$name = isset( $definition['name'] ) ? (string) $definition['name'] : '';

			if ( '' === $name ) {
					continue;
			}

			if ( self::canonical_key( $name ) === $canonical ) {
				return $definition;
			}
		}

			return null;
	}

		/**
		 * Resolve schema information for the provided template.
		 *
		 * @param string $template Template markup.
		 *
		 * @return array{template:string,fields:array<string,array<string,mixed>>,warnings:array<int,string>}
		 */
	private static function resolve_schema( string $template ): array {
			$sanitized = TemplateRenderer::sanitize_template( $template );
			$parsed    = self::parser()->parse( $sanitized );

			return array(
				'template' => $sanitized,
				'fields'   => $parsed['fields'],
				'warnings' => $parsed['warnings'],
			);
	}

		/**
		 * Read filtered input values with CLI-safe fallback.
		 *
		 * @param int    $type     Input type constant.
		 * @param string $variable Variable name to resolve.
		 * @param int    $filter   Filter identifier.
		 *
		 * @return mixed Filtered value or null when unavailable.
		 */
	private static function read_input( int $type, string $variable, int $filter ) {
			$value = filter_input( $type, $variable, $filter );

		if ( null !== $value && false !== $value ) {
				return $value;
		}

		switch ( $type ) {
			case INPUT_POST:
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- CLI fallback mirrors filter_input() for tests.
					$source = (array) $_POST;
				break;
			case INPUT_SERVER:
					$source = (array) $_SERVER;
				break;
			default:
					$source = array();
		}

		if ( ! array_key_exists( $variable, $source ) ) {
				return null;
		}

			$raw_value = $source[ $variable ];

		if ( FILTER_UNSAFE_RAW === $filter ) {
				return $raw_value;
		}

			$filtered = filter_var( $raw_value, $filter );

		if ( false === $filtered || null === $filtered ) {
				return null;
		}

			return $filtered;
	}

		/**
		 * Normalize uploaded file payload for a field.
		 *
		 * Nonce verification occurs in handle_submission().
		 *
		 * @param string $field Field name.
		 *
		 * @return array<string,mixed>|null
		 *
		 * @phpcsSuppress WordPress.Security.NonceVerification.Missing --
		 *     Nonce verified before file handling in handle_submission().
		 * @phpcsSuppress WordPress.Security.NonceVerification.NoNonceVerification --
		 *     Nonce verified before file handling in handle_submission().
		 */
	private static function read_file_input( string $field ): ?array {
		if ( empty( $_FILES[ $field ] ) || ! is_array( $_FILES[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.NoNonceVerification,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in Uploads::process().
				return null;
		}

		$file = $_FILES[ $field ]; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.NoNonceVerification,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in Uploads::process().

		if ( isset( $file['tmp_name'] ) && is_array( $file['tmp_name'] ) ) {
				return null;
		}

		return $file;
	}

		/**
		 * Resolve the current page URL for the form action.
		 */
	private static function current_action_url(): string {
			$uri = self::read_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			return is_string( $uri ) ? $uri : '';
	}

		/**
		 * Resolve the submission cooldown duration.
		 */
	private static function get_submission_cooldown(): int {
			$cooldown = (int) apply_filters( self::SUBMISSION_COOLDOWN_FILTER, self::SUBMISSION_COOLDOWN_DEFAULT );

			return $cooldown > 0 ? $cooldown : 0;
	}

		/**
		 * Build a repeatable fingerprint for throttling submissions.
		 *
		 * @param string $email Email address used for the submission.
		 */
	private static function build_submission_fingerprint( string $email ): ?string {
			$email = strtolower( trim( $email ) );

		if ( '' === $email ) {
				return null;
		}

			$remote = self::read_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$remote = is_string( $remote ) ? trim( sanitize_text_field( $remote ) ) : '';

		if ( '' === $remote ) {
				return null;
		}

			return $email . '|' . $remote;
	}

		/**
		 * Render the view template safely.
		 *
		 * @param array<string, mixed> $context Template context.
		 */
	private static function render_template( array $context ): string {
			$template = FBM_PATH . 'templates/public/registration-form.php';

		if ( ! is_readable( $template ) ) {
				return '';
		}

			ob_start();

			$data = $context;
			include $template;

			$output = ob_get_clean();

			return is_string( $output ) ? $output : '';
	}

		/**
		 * Resolve a shared template renderer instance.
		 */
	private static function renderer(): TemplateRenderer {
		if ( null === self::$renderer ) {
				self::$renderer = new TemplateRenderer( self::parser() );
		}

			return self::$renderer;
	}

		/**
		 * Resolve a shared tag parser instance.
		 */
	private static function parser(): TagParser {
		if ( null === self::$parser ) {
				self::$parser = new TagParser();
		}

			return self::$parser;
	}
}
