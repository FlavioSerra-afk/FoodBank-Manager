<?php
/**
 * Diagnostics admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Diagnostics\HealthStatus;
use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Diagnostics\TokenProbeService;
use FoodBankManager\Email\WelcomeMailer;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Token\Token;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use RuntimeException;
use wpdb;
use function __;
use function add_action;
use function add_menu_page;
use function add_query_arg;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function filter_input;
use function array_key_exists;
use function array_slice;
use function gmdate;
use function is_array;
use function is_scalar;
use function is_readable;
use function sanitize_textarea_field;
use function sanitize_text_field;
use function sprintf;
use function strtoupper;
use function str_repeat;
use function strlen;
use function substr;
use function wp_die;
use function wp_nonce_url;
use function wp_safe_redirect;
use function wp_unslash;
use function wp_verify_nonce;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;
use const INPUT_SERVER;
use const INPUT_POST;

/**
 * Presents diagnostic information including mail failures.
 */
final class DiagnosticsPage {
	private const MENU_SLUG            = 'fbm-diagnostics';
	private const TEMPLATE             = 'templates/admin/diagnostics-page.php';
	private const ACTION_PARAM         = 'fbm_diag_action';
	private const ENTRY_PARAM          = 'fbm_diag_entry';
	private const STATUS_PARAM         = 'fbm_diag_status';
	private const CODE_PARAM           = 'fbm_diag_code';
	private const MEMBER_PARAM         = 'fbm_diag_member';
	private const ACTION_RESEND        = 'resend';
	private const TOKEN_PROBE_FIELD    = 'fbm_token_probe_payload';
	private const TOKEN_PROBE_NONCE    = 'fbm_token_probe_nonce';
	private const TOKEN_PROBE_ACTION   = 'fbm_token_probe';
	private const TOKEN_FAILURES_LIMIT = 10;

				/**
				 * Register WordPress hooks.
				 */
	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_actions' ) );
	}

		/**
		 * Register the diagnostics admin menu entry.
		 */
	public static function register_menu(): void {
			add_menu_page(
				__( 'Food Bank Diagnostics', 'foodbank-manager' ),
				__( 'Food Bank Diagnostics', 'foodbank-manager' ),
				'fbm_manage', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered during activation.
				self::MENU_SLUG,
				array( __CLASS__, 'render' ),
				'dashicons-shield-alt'
			);
	}

		/**
		 * Render the diagnostics dashboard.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

			$log = new MailFailureLog();
		$raw     = $log->entries();
		$entries = array();
		$notices = self::collect_notices();
		$rate    = MailFailureLog::rate_limit_interval();
		$health  = new HealthStatus();
		$badges  = $health->badges();

		foreach ( $raw as $entry ) {
			if ( ! is_array( $entry ) ) {
								continue;
			}

					$entry_id    = isset( $entry['id'] ) ? (string) $entry['id'] : '';
					$recorded_at = isset( $entry['recorded_at'] ) ? (int) $entry['recorded_at'] : 0;
					$blocked     = $log->next_attempt_at( $entry );

					$entries[] = array(
						'id'               => $entry_id,
						'member_reference' => isset( $entry['member_reference'] ) ? (string) $entry['member_reference'] : '',
						'email'            => isset( $entry['email'] ) ? (string) $entry['email'] : '',
						'context'          => self::context_label( isset( $entry['context'] ) ? (string) $entry['context'] : '' ),
						'error'            => self::error_label( isset( $entry['error'] ) ? (string) $entry['error'] : '' ),
						'recorded_at'      => $recorded_at > 0 ? gmdate( 'Y-m-d H:i:s \U\T\C', $recorded_at ) : '',
						'attempts'         => isset( $entry['attempts'] ) ? (int) $entry['attempts'] : 0,
						'can_resend'       => '' !== $entry_id && $log->can_attempt( $entry ),
						'resend_url'       => '' !== $entry_id ? self::build_action_url( $entry_id ) : '',
						'blocked_until'    => is_int( $blocked ) && $blocked > 0 ? gmdate( 'Y-m-d H:i:s \U\T\C', $blocked ) : null,
					);
		}

				$token_probe    = self::prepare_token_probe();
				$token_failures = self::collect_token_failures( $raw );

						$template = FBM_PATH . self::TEMPLATE;

		if ( ! is_readable( $template ) ) {
						wp_die( esc_html__( 'Diagnostics template is missing.', 'foodbank-manager' ) );
		}

												$data = array(
													'entries'            => $entries,
													'notices'            => $notices,
													'rate_limit_seconds' => $rate,
													'page_slug'          => self::MENU_SLUG,
													'health_badges'      => $badges,
													'token_probe'        => array(
														'field'       => self::TOKEN_PROBE_FIELD,
														'nonce_field' => self::TOKEN_PROBE_NONCE,
														'nonce_action' => self::TOKEN_PROBE_ACTION,
														'payload'     => $token_probe['payload'],
														'submitted'   => $token_probe['submitted'],
														'result'      => $token_probe['result'],
														'error'       => $token_probe['error'],
													),
													'token_failures'     => $token_failures,
												);

												include $template;
	}

		/**
		 * Handle resend attempts triggered from the diagnostics view.
		 */
	public static function handle_actions(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			return;
		}

						$page_param = self::read_query_arg( 'page' );
						$page       = '' !== $page_param ? sanitize_key( wp_unslash( $page_param ) ) : '';

		if ( self::MENU_SLUG !== $page ) {
				return;
		}

						$action_param = self::read_query_arg( self::ACTION_PARAM );
						$action       = '' !== $action_param ? sanitize_key( wp_unslash( $action_param ) ) : '';

		if ( self::ACTION_RESEND !== $action ) {
				return;
		}

						$entry_param = self::read_query_arg( self::ENTRY_PARAM );
						$entry_id    = '' !== $entry_param ? sanitize_text_field( wp_unslash( $entry_param ) ) : '';

		if ( '' === $entry_id ) {
				self::redirect_with_outcome(
					array(
						'status' => false,
						'code'   => 'entry-missing',
					)
				);

				return;
		}

			check_admin_referer( self::resend_nonce_action( $entry_id ) );

			$outcome = self::process_resend( $entry_id );

			self::redirect_with_outcome( $outcome );
	}

		/**
		 * Collect queued notice messages from query parameters.
		 *
		 * @return array<int, array{type:string,message:string}>
		 */
	private static function collect_notices(): array {
		$notices = array();

					$status_param = self::read_query_arg( self::STATUS_PARAM );
					$code_param   = self::read_query_arg( self::CODE_PARAM );
					$member_param = self::read_query_arg( self::MEMBER_PARAM );

					$status = '' !== $status_param ? sanitize_key( wp_unslash( $status_param ) ) : '';
					$code   = '' !== $code_param ? sanitize_key( wp_unslash( $code_param ) ) : '';
					$member = '' !== $member_param ? sanitize_text_field( wp_unslash( $member_param ) ) : '';

		if ( '' === $status || '' === $code ) {
				return $notices;
		}

		switch ( $code ) {
			case 'resent':
					$message = '' !== $member
							? sprintf(
									/* translators: %s: Member reference. */
								__( 'Resent the welcome email for %s.', 'foodbank-manager' ),
								$member
							)
							: __( 'Resent the welcome email.', 'foodbank-manager' );
					$notices[] = array(
						'type'    => 'success',
						'message' => $message,
					);
				break;
			case 'rate-limited':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'Resend is temporarily rate-limited. Please try again later.', 'foodbank-manager' ),
					);
				break;
			case 'member-missing':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'The associated member record could not be found.', 'foodbank-manager' ),
					);
				break;
			case 'database':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'Database connection unavailable. Please retry later.', 'foodbank-manager' ),
					);
				break;
			case 'token':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'Unable to issue a new token for the member.', 'foodbank-manager' ),
					);
				break;
			case 'mail':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'The mail provider rejected the resend attempt.', 'foodbank-manager' ),
					);
				break;
			case 'entry-missing':
			default:
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'The selected mail failure entry could not be processed.', 'foodbank-manager' ),
					);
				break;
		}

			return $notices;
	}

		/**
		 * Process token probe submissions.
		 *
		 * @return array{payload:string,submitted:bool,result:?array{version:?string,hmac_match:bool,revoked:bool},error:?string}
		 */
	private static function prepare_token_probe(): array {
				$method_input = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_UNSAFE_RAW );
				$raw_method   = is_string( $method_input ) ? $method_input : '';
				$method       = strtoupper( sanitize_text_field( $raw_method ) );

		if ( 'POST' !== $method ) {
				return array(
					'payload'   => '',
					'submitted' => false,
					'result'    => null,
					'error'     => null,
				);
		}

		$payload   = self::read_post_field( self::TOKEN_PROBE_FIELD );
		$nonce_raw = filter_input( INPUT_POST, self::TOKEN_PROBE_NONCE, FILTER_UNSAFE_RAW );
		$nonce     = is_string( $nonce_raw ) ? (string) wp_unslash( $nonce_raw ) : '';

		if ( '' === $nonce || ! wp_verify_nonce( $nonce, self::TOKEN_PROBE_ACTION ) ) {
				return array(
					'payload'   => $payload,
					'submitted' => true,
					'result'    => null,
					'error'     => __( 'Security check failed. Please try again.', 'foodbank-manager' ),
				);
		}

		$service = self::build_token_probe_service();

		if ( null === $service ) {
				return array(
					'payload'   => $payload,
					'submitted' => true,
					'result'    => null,
					'error'     => __( 'Token diagnostics are temporarily unavailable.', 'foodbank-manager' ),
				);
		}

		return array(
			'payload'   => $payload,
			'submitted' => true,
			'result'    => self::filter_token_probe_result( $service->probe( $payload ) ),
			'error'     => null,
		);
	}

		/**
		 * Limit the token probe output to redacted fields.
		 *
		 * @param array<string,mixed> $result Raw probe result.
		 * @return array{version:?string,hmac_match:bool,revoked:bool}
		 */
	private static function filter_token_probe_result( array $result ): array {
			$version = null;

		if ( array_key_exists( 'version', $result ) && null !== $result['version'] ) {
				$version = (string) $result['version'];
		}

			return array(
				'version'    => $version,
				'hmac_match' => (bool) ( $result['hmac_match'] ?? false ),
				'revoked'    => (bool) ( $result['revoked'] ?? false ),
			);
	}

		/**
		 * Instantiate the token probe service when the database layer is available.
		 */
	private static function build_token_probe_service(): ?TokenProbeService {
			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				return null;
		}

			$token = new Token( new TokenRepository( $wpdb ) );

			return new TokenProbeService( $token );
	}

		/**
		 * Collect recent token resend failures for diagnostics display.
		 *
		 * @param array<int,mixed> $entries Raw mail failure entries.
		 *
		 * @return array<int,array<string,mixed>>
		 */
	private static function collect_token_failures( array $entries ): array {
			$failures = array();

		foreach ( $entries as $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}

			if ( ! isset( $entry['error'] ) || MailFailureLog::ERROR_TOKEN !== (string) $entry['error'] ) {
					continue;
			}

				$recorded_at = isset( $entry['recorded_at'] ) ? (int) $entry['recorded_at'] : 0;

				$failures[] = array(
					'member_reference' => self::redact_member_reference( isset( $entry['member_reference'] ) ? (string) $entry['member_reference'] : '' ),
					'recorded_at'      => $recorded_at > 0 ? gmdate( 'Y-m-d H:i:s \U\T\C', $recorded_at ) : '',
					'context'          => self::context_label( isset( $entry['context'] ) ? (string) $entry['context'] : '' ),
					'attempts'         => isset( $entry['attempts'] ) ? (int) $entry['attempts'] : 0,
				);
		}

		if ( empty( $failures ) ) {
				return array();
		}

			return array_slice( $failures, 0, self::TOKEN_FAILURES_LIMIT );
	}

				/**
				 * Process the resend action for a specific failure entry.
				 *
				 * @param string $entry_id Failure entry identifier.
				 *
				 * @return array{status:bool,code:string,member_reference?:string}
				 */
	private static function process_resend( string $entry_id ): array {
			$log   = new MailFailureLog();
			$entry = $log->find( $entry_id );

		if ( null === $entry ) {
				return array(
					'status' => false,
					'code'   => 'entry-missing',
				);
		}

		if ( ! $log->can_attempt( $entry ) ) {
				return array(
					'status' => false,
					'code'   => 'rate-limited',
				);
		}

			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				return array(
					'status' => false,
					'code'   => 'database',
				);
		}

			$repository = new MembersRepository( $wpdb );
			$member     = $repository->find( isset( $entry['member_id'] ) ? (int) $entry['member_id'] : 0 );

		if ( null === $member ) {
				$log->resolve( $entry_id );

				return array(
					'status' => false,
					'code'   => 'member-missing',
				);
		}

			$tokens = new TokenService( new TokenRepository( $wpdb ) );

		try {
				$token = $tokens->issue( (int) $member['id'] );
		} catch ( RuntimeException $exception ) {
				unset( $exception );

				return array(
					'status' => false,
					'code'   => 'token',
				);
		}

			$log->note_attempt( $entry_id );

			$mailer = new WelcomeMailer();

		if ( ! $mailer->send( $member['email'], $member['first_name'], $member['member_reference'], $token ) ) {
				$log->note_failure( $entry_id, MailFailureLog::ERROR_MAIL, MailFailureLog::CONTEXT_DIAGNOSTICS_RESEND );

				return array(
					'status' => false,
					'code'   => 'mail',
				);
		}

			$log->resolve( $entry_id );

			return array(
				'status'           => true,
				'code'             => 'resent',
				'member_reference' => $member['member_reference'],
			);
	}

		/**
		 * Redirect back to the diagnostics page with an encoded outcome.
		 *
		 * @param array{status:bool,code:string,member_reference?:string} $outcome Outcome descriptor.
		 */
	private static function redirect_with_outcome( array $outcome ): void {
			$args = array(
				'page'             => self::MENU_SLUG,
				self::STATUS_PARAM => $outcome['status'] ? 'success' : 'error',
				self::CODE_PARAM   => $outcome['code'],
			);

			if ( isset( $outcome['member_reference'] ) && '' !== $outcome['member_reference'] ) {
					$args[ self::MEMBER_PARAM ] = $outcome['member_reference'];
			}

			$location = add_query_arg( $args, admin_url( 'admin.php' ) );

			wp_safe_redirect( $location );
			exit;
	}

		/**
		 * Build a nonce-protected resend URL.
		 *
		 * @param string $entry_id Failure entry identifier.
		 */
	private static function build_action_url( string $entry_id ): string {
			$url = add_query_arg(
				array(
					'page'             => self::MENU_SLUG,
					self::ACTION_PARAM => self::ACTION_RESEND,
					self::ENTRY_PARAM  => $entry_id,
				),
				admin_url( 'admin.php' )
			);

			return wp_nonce_url( $url, self::resend_nonce_action( $entry_id ) );
	}

		/**
		 * Compute the nonce action string for a resend request.
		 *
		 * @param string $entry_id Failure entry identifier.
		 */
	private static function resend_nonce_action( string $entry_id ): string {
			return 'fbm_diag_resend_' . $entry_id;
	}

		/**
		 * Map context identifiers to localized labels.
		 *
		 * @param string $context Context key.
		 */
	private static function context_label( string $context ): string {
		switch ( $context ) {
			case MailFailureLog::CONTEXT_ADMIN_RESEND:
				return __( 'Admin resend', 'foodbank-manager' );
			case MailFailureLog::CONTEXT_DIAGNOSTICS_RESEND:
				return __( 'Diagnostics resend', 'foodbank-manager' );
			case MailFailureLog::CONTEXT_REGISTRATION:
				return __( 'Registration', 'foodbank-manager' );
			default:
				return __( 'Email delivery', 'foodbank-manager' );
		}
	}

		/**
		 * Map error identifiers to localized labels.
		 *
		 * @param string $error Error key.
		 */
	private static function error_label( string $error ): string {
		switch ( $error ) {
			case MailFailureLog::ERROR_TOKEN:
				return __( 'Token issuance failure', 'foodbank-manager' );
			case MailFailureLog::ERROR_MEMBER:
				return __( 'Missing member record', 'foodbank-manager' );
			case MailFailureLog::ERROR_MAIL:
				return __( 'Mail transport failure', 'foodbank-manager' );
			default:
				return __( 'Unknown error', 'foodbank-manager' );
		}
	}

		/**
		 * Retrieve a POSTed field with sanitization.
		 *
		 * @param string $field Field name to read.
		 * @return string Sanitized field value.
		 */
	private static function read_post_field( string $field ): string {
			$value = filter_input( INPUT_POST, $field, FILTER_UNSAFE_RAW );

		if ( null === $value ) {
				return '';
		}

		return sanitize_textarea_field( wp_unslash( (string) $value ) );
	}

		/**
		 * Redact a member reference to avoid exposing identifiers.
		 *
		 * @param string $reference Raw member reference value.
		 * @return string Redacted reference string.
		 */
	private static function redact_member_reference( string $reference ): string {
			$reference = trim( $reference );

		if ( '' === $reference ) {
				return '';
		}

		$length = strlen( $reference );

		if ( $length <= 4 ) {
				return str_repeat( '•', $length );
		}

		$prefix = substr( $reference, 0, 2 );
		$suffix = substr( $reference, -2 );

		return $prefix . '…' . $suffix;
	}
		/**
		 * Retrieve a query argument with CLI-compatible fallback.
		 *
		 * @param string $param Query parameter name.
		 * @return string Sanitized parameter value.
		 */
	private static function read_query_arg( string $param ): string {
			$value = filter_input( INPUT_GET, $param, FILTER_UNSAFE_RAW );

		if ( is_string( $value ) ) {
			return $value;
		}

		if ( isset( $_GET[ $param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- CLI fallback for tests.
			$raw = wp_unslash( $_GET[ $param ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- CLI fallback for tests.

			if ( is_scalar( $raw ) ) {
				return (string) $raw;
			}
		}

		return '';
	}
}
